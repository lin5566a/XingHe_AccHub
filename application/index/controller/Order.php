<?php
namespace app\index\controller;

use app\admin\model\Product as ProductModel;
use app\admin\model\ProductStock as ProductStockModel;
use app\admin\model\ProductOrder as ProductOrderModel;
use app\admin\model\SystemInfo;
use app\common\traits\ApiResponse;
use app\common\service\PaymentService;
use app\common\constants\OrderStatus;
use app\common\constants\StockStatus;
use app\common\constants\PaymentMethod;
use app\common\service\CaptchaService;
use think\Log;
use think\Cache;
use think\Db;
use app\admin\model\MemberLevel;

class Order extends Base
{
    use ApiResponse;

    protected $captchaService;

    protected function _initialize()
    {
        parent::_initialize();
        $this->captchaService = new CaptchaService();
    }
    
    /**
     * 发送订单查询验证码
     */
    public function sendQueryCaptcha()
    {
        try {
            $data = $this->request->post();
            
            // 验证必填字段
            if (empty($data['email'])) {
                return $this->ajaxError('邮箱不能为空');
            }
            
            // 验证邮箱格式
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return $this->ajaxError('邮箱格式不正确');
            }
            
            // 检查发送频率限制
            $rateLimitKey = 'order_query_rate_limit_' . $data['email'];
            if (Cache::get($rateLimitKey)) {
                return $this->ajaxError('发送过于频繁，请1分钟后再试');
            }
            
            // 生成6位数字验证码（转为字符串类型）
            $captcha = (string)rand(100000, 999999);
            
            // 发送邮件验证码
            $emailService = new \app\common\service\EmailService();
            $result = $emailService->sendOrderQueryCaptcha($data['email'], $captcha);
            
            if ($result['code'] == 200) {
                // 保存验证码到缓存，有效期5分钟
                Cache::set('order_query_captcha_' . $data['email'], $captcha, 300);
                
                // 设置发送频率限制，1分钟内不能重复发送
                Cache::set($rateLimitKey, 1, 60);
                
                return $this->ajaxSuccess('验证码发送成功');
            } else {
                return $this->ajaxError('验证码发送失败：' . $result['message']);
            }
            
        } catch (\Exception $e) {
            Log::error('发送订单查询验证码失败：' . $e->getMessage());
            return $this->ajaxError('发送失败：' . $e->getMessage());
        }
    }
    /**
     * 创建订单
     */
    public function create()
    {
        try {
            $data = $this->request->post();
            
            // 验证必填字段
            if (empty($data['product_id']) || empty($data['quantity'])) {
                return $this->ajaxError('缺少必要参数');
            }
            
            // 验证邮箱字段（未登录用户必须填写邮箱）
            if (empty($data['email'])) {
                return $this->ajaxError('请填写邮箱地址');
            }
            
            // 验证邮箱格式
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return $this->ajaxError('邮箱格式不正确');
            }
            
            // 验证商品是否存在（包含所有必要字段）
            $product = ProductModel::where('id', $data['product_id'])
                ->where('status', 1)
                ->field('id,name,price,delivery_method,category_id,enable_discount,enable_purchase_limit,purchase_limit_type,purchase_limit_value,stock,discount_enabled,discount_percentage,discount_start_time,discount_end_time')
                ->find();
                
            if (!$product) {
                return $this->ajaxError('商品不存在或已下架');
            }
            
            // 验证购买数量限制
            if ($product['enable_purchase_limit']) {
                $maxQuantity = 0;
                if ($product['purchase_limit_type'] == 1) { // 固定数量
                    $maxQuantity = $product['purchase_limit_value'];
                } else { // 百分比
                    // 根据发货方式获取总库存
                    if ($product['delivery_method'] == 'auto') {
                        $totalStock = ProductStockModel::where('product_id', $data['product_id'])
                            ->where('status', StockStatus::UNUSED)
                            ->count();
                    } else {
                        $totalStock = $product['stock'];
                    }
                    $maxQuantity = ceil($totalStock * $product['purchase_limit_value'] / 100);
                }
                
                if ($data['quantity'] > $maxQuantity) {
                    return $this->ajaxError('超出单笔购买限制，最多可购买' . $maxQuantity . '件');
                }
            }
            
            // 开启事务
            Db::startTrans();
            try {
                // 生成订单号
                $orderNumber = date('YmdHis') . mt_rand(1000, 9999);
                // 根据发货方式处理库存
                if ($product['delivery_method'] == 'auto') {
                    // 自动发货：查询并锁定库存表
                    $stock = ProductStockModel::where('product_id', $data['product_id'])
                        ->where('status', StockStatus::UNUSED)
                        ->limit($data['quantity'])
                        ->select();
                        
                    if (count($stock) < $data['quantity']) {
                        throw new \Exception('库存不足');
                    }
                    
                    // 获取卡密信息
                    $cardInfo = [];
                    $totalCost = 0; // 累计成本价
                    foreach ($stock as $item) {
                        $cardInfo[] = [
                            'id' => $item->id,
                            'card_info' => $item->card_info
                        ];
                        $totalCost += floatval($item->cost_price);
                    }
                    
                    // 获取库存ID列表
                    $stockIds = [];
                    foreach($stock as $sk) {
                        $stockIds[] = $sk['id'];
                    }
                    
                    // 批量锁定库存
                    $updateResult = ProductStockModel::where('id', 'in', $stockIds)
                        ->where('status', StockStatus::UNUSED)
                        ->update([
                            'status' => StockStatus::LOCKED,
                            'order_id' => $orderNumber
                        ]);
                        
                    if ($updateResult < count($stockIds)) {
                        throw new \Exception('锁定库存失败，请重试');
                    }
                } else {
                    // 手动发货：检查并减少商品库存
                    if ($product['stock'] < $data['quantity']) {
                        throw new \Exception('库存不足');
                    }
                    
                    // 减少商品库存
                    $updateResult = ProductModel::where('id', $data['product_id'])
                        ->where('stock', '>=', $data['quantity'])
                        ->update([
                            'stock' => Db::raw('stock - ' . $data['quantity'])
                        ]);
                        
                    if ($updateResult === 0) {
                        throw new \Exception('库存不足');
                    }
                    
                    $cardInfo = []; // 手动发货不需要卡密信息
                }
                
                // 获取用户信息（通过token验证）
                $userInfo = [
                    'user_role' => '游客',
                    'user_role_id' => 0,  // 游客ID为0
                    'user_discount' => 100.00,
                    'user_nickname' => '',
                    'user_email' => ''
                ];
                
                // 从header获取token
                $token = $this->request->header('Authorization');
                if ($token) {
                    // 如果是 Bearer token，需要去掉 'Bearer ' 前缀
                    if (strpos($token, 'Bearer ') === 0) {
                        $token = substr($token, 7);
                    }
                    
                    // 通过新的会话系统验证token并获取用户信息
                    $session = \app\admin\model\UserSession::validateToken($token);
                    if ($session) {
                        // 根据会话中的user_id获取用户信息
                        $user = \app\admin\model\User::where('id', $session['user_id'])
                            ->where('status', 1)
                            ->find();
                            
                        if ($user) {
                            // 获取会员等级信息
                            $memberLevel = \app\admin\model\MemberLevel::where('id', $user['membership_level'])->find();
                            if ($memberLevel) {
                                $userInfo['user_role'] = $memberLevel['name'];
                                $userInfo['user_role_id'] = $memberLevel['id'];  // 存储会员等级ID
                                // 判断是否使用自定义折扣
                                if ($user['membership_level'] == MemberLevel::SUPER_LEVEL_ID && $user['custom_discount'] > 0) {
                                    $userInfo['user_discount'] = $user['custom_discount'];
                                } else {
                                    $userInfo['user_discount'] = $memberLevel['discount'];
                                }
                            }
                            $userInfo['user_nickname'] = $user['nickname'];
                            $userInfo['user_email'] = $user['email'];
                            $userInfo['id'] = $user['id'];
                            
                            // 更新会话的最后活动时间
                            \app\admin\model\UserSession::updateLastActivity($token);
                        }
                    }
                }
                
                // 设置用户邮箱（优先使用用户提供的邮箱，未登录用户必须提供）
                $userInfo['user_email'] = $data['email'];
                
                // 计算实际应付单价和总价（优先使用商品折扣，折扣未启用时使用会员折扣）
                $discount = isset($userInfo['user_discount']) ? $userInfo['user_discount'] : 100;
                
                // 获取折扣后的价格
                $discountedPrice = $product->getDiscountedPrice();
                
                // 如果商品有折扣且正在生效，使用折扣价格；否则使用会员折扣
                $discountStatus = $product->getDiscountStatus();
                if ($discountStatus === '进行中') {
                    // 使用商品折扣价格
                    $real_price = $discountedPrice;
                } else {
                    // 使用会员折扣
                    if (isset($product['enable_discount']) && intval($product['enable_discount']) === 0) {
                        $real_price = $product->price;
                    } else {
                        $real_price = bcmul($product->price, bcdiv($discount, 100, 2), 2);
                    }
                }
                $total_price = bcmul($real_price, $data['quantity'], 2);
                // $discount = isset($userInfo['user_discount']) ? $userInfo['user_discount'] : 100;
                // $real_price = bcmul($product->price, bcdiv($discount, 100, 2), 2);
                // $total_price = bcmul($real_price, $data['quantity'], 2);

                // 获取访客UUID和渠道代码
                $visitorUuid = ProductOrderModel::getVisitorUuid();
                $channelCode = ProductOrderModel::getChannelCode();
                
                // 创建订单
                $order = ProductOrderModel::create([
                    'order_number' => $orderNumber,
                    'product_id' => $data['product_id'],
                    'product_name' => $product->name,
                    'category_id' => $product->category_id,
                    'online_price' => $product->price,
                    'purchase_price' => $real_price, // 实际单价
                    'quantity' => $data['quantity'],
                    'total_price' => $total_price, // 实际总价
                    'card_info' => $product['delivery_method'] == 'auto' ? json_encode($cardInfo, JSON_UNESCAPED_UNICODE) : '',
                    'user_email' => $userInfo['user_email'],
                    'user_role' => $userInfo['user_role'],
                    'user_role_id' => $userInfo['user_role_id'],  // 存储会员等级ID
                    'user_discount' => $userInfo['user_discount'],
                    'user_nickname' => $userInfo['user_nickname'],
                    'delivery_method' => $product->delivery_method,
                    'status' => OrderStatus::PENDING,
                    'cost_price' => isset($totalCost) ? $totalCost : 0, // 自动发货写入成本价
                    'visitor_uuid' => $visitorUuid,
                    'channel_code' => $channelCode
                ]);
                
                Db::commit();
                
                return $this->ajaxSuccess('创建成功', [
                    'order_no' => $order->order_number,
                    'total_price' => $order->total_price,
                    'created_at' => $order->created_at
                ]);
                
            } catch (\Exception $e) {
                Db::rollback();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('创建订单失败：' . $e->getMessage());
            return $this->ajaxError('创建订单失败：' . $e->getMessage(). $e->getLine());
        }
    }
    
    /**
     * 查询订单列表
     */
    public function query()
    {
        try {
            $data = $this->request->post();

            // 验证必填字段
            if (empty($data['email'])) {
                return $this->ajaxError('邮箱不能为空');
            }
            
            if (empty($data['email_captcha'])) {
                return $this->ajaxError('邮箱验证码不能为空');
            }
            
            // 验证邮箱格式
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return $this->ajaxError('邮箱格式不正确');
            }
            
            // 验证邮箱验证码
            $cacheKey = 'order_query_captcha_' . $data['email'];
            $cachedCaptcha = Cache::get($cacheKey);
            
            // 确保验证码比较时类型一致（都转为字符串比较）
            if (!$cachedCaptcha || (string)$cachedCaptcha !== (string)$data['email_captcha']) {
                return $this->ajaxError('邮箱验证码错误或已过期');
            }
            
            // 验证成功后删除缓存
            Cache::rm($cacheKey);
            
            // 设置查询条件
            $where['user_email'] = $data['email'];
            
            // 查询订单列表
            $list = ProductOrderModel::where($where)
                ->order('id', 'desc')
                ->select();
                
            if (empty($list)) {
                return $this->ajaxSuccess('查询成功', []);
            }
            
            // 格式化订单信息
            $orders = [];
            foreach ($list as $item) {
                $orders[] = [
                    'order_number' => $item->order_number,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'total_price' => $item->total_price,
                    'created_at' => $item->created_at,
                    'status' => $item->status,
                    'status_text' => OrderStatus::getStatusText($item->status),
                    'finished_at' => $item->status == OrderStatus::COMPLETED ? $item->finished_at : '-',
                    'card_info' => $item->status == OrderStatus::COMPLETED ? json_decode($item->card_info, true) : null
                ];
            }
            
            return $this->ajaxSuccess('查询成功', $orders);
            
        } catch (\Exception $e) {
            Log::error('查询订单列表失败：' . $e->getMessage());
            return $this->ajaxError('查询失败：' . $e->getMessage());
        }
    }
    
    /**
     * 查询订单详情
     */
    public function detail()
    {
        try {
            $data = $this->request->post();
            
            // 验证必填字段
            if (empty($data['order_no'])) {
                return $this->ajaxError('缺少必要参数');
            }
            
            // 查询订单
            $order = ProductOrderModel::where('order_number', $data['order_no'])
                // ->where('user_email', $data['email'])
                ->find();
                
            if (!$order) {
                return $this->ajaxError('订单不存在');
            }

            // 获取系统配置的订单过期时间
            $systemInfo = SystemInfo::find();
            $orderTimeout = $systemInfo ? intval($systemInfo['order_timeout']) : 15; // 默认15分钟
            
            // 计算订单过期时间
            $createTime = strtotime($order->created_at);
            $expireTime = $createTime + ($orderTimeout * 60); // 转换为秒
            $currentTime = time();
            $remainingSeconds = max(0, $expireTime - $currentTime); // 剩余秒数，如果已过期则返回0
            
            // 格式化订单信息
            $orderInfo = [
                'order_number' => $order->order_number,
                'product_name' => $order->product_name,
                'unit_price' => $order->purchase_price,
                'quantity' => $order->quantity,
                'total_price' => $order->total_price,
                'created_at' => $order->created_at,
                'status' => $order->status,
                'email' => $order->user_email,
                'status_text' => OrderStatus::getStatusText($order->status),
                'finished_at' => $order->status == OrderStatus::COMPLETED ? $order->finished_at : '-',
                'card_info' => $order->status == OrderStatus::COMPLETED ? json_decode($order->card_info, true) : null,
                'payment_info' => [
                    'payment_method' => $order->payment_method,
                    'payment_method_text' => PaymentMethod::getMethodText($order->payment_method),
                    'fee' => $order->fee,
                    'usdt_rate' => $order->usdt_rate,
                    'usdt_amount' => $order->usdt_amount
                ],
                'order_timeout' => $remainingSeconds // 返回剩余秒数
            ];
            
            return $this->ajaxSuccess('查询成功', $orderInfo);
            
        } catch (\Exception $e) {
            Log::error('查询订单详情失败：' . $e->getMessage());
            return $this->ajaxError('查询失败');
        }
    }

    /**
     * 余额支付
     */
    public function payWithBalance()
    {
        try {
            $userInfo = $this->getUserInfo();
            if (!$userInfo) {
                return $this->ajaxError('请先登录');
            }
            $data = $this->request->post();
            if (empty($data['order_no'])) {
                return $this->ajaxError('缺少订单号');
            }
            $order = \app\admin\model\ProductOrder::where('order_number', $data['order_no'])
                ->where('status', OrderStatus::PENDING)
                ->find();
            if (!$order) {
                return $this->ajaxError('订单不存在或状态错误');
            }
            if ($order['user_email'] != $userInfo['email']) {
                return $this->ajaxError('无权限操作此订单');
            }

            Db::startTrans();
            try {
                // 并发安全：加锁查余额
                $user = \app\admin\model\User::lock(true)->where('id', $userInfo['id'])->find();
                if (!$user) {
                    throw new \Exception('用户不存在');
                }
                $before = $user['balance'];
                // 0元订单不需要检查余额
                if ($order['total_price'] > 0 && $before < $order['total_price']) {
                    throw new \Exception('余额不足');
                }
                $after = bcsub($before, $order['total_price'], 2);
                
                // 0元订单不需要扣减余额
                if ($order['total_price'] > 0) {
                    // 扣减余额
                    $res = \app\admin\model\User::where('id', $userInfo['id'])
                        ->where('balance', '>=', $order['total_price'])
                        ->setDec('balance', $order['total_price']);
                    if (!$res) {
                        throw new \Exception('扣减余额失败');
                    }
                }
                // 增加累计消费
                \app\admin\model\User::where('id', $userInfo['id'])->setInc('total_spent', $order['total_price']);
                // 更新订单状态
                $order->status = OrderStatus::PAID;
                $order->payment_method = 'balance';
                $order->save();
                // 写入余额日志
                \app\admin\model\UserBalanceLog::create([
                    'user_id' => $userInfo['id'],
                    'type' => '消费',
                    'amount' => $order['total_price'],
                    'before_balance' => $before,
                    'after_balance' => $after,
                    'direction' => 'out',
                    'order_no' => $order['order_number'],
                    'remark' => '商品订单余额支付',
                    'operator' => $userInfo['nickname']
                ]);
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                return $this->ajaxError('支付失败：' . $e->getMessage());
            }

            $this->handleProductOrderAfterPaid($order);

            // 删除重复的自动发货邮件逻辑，统一用handleProductOrderAfterPaid处理
            return $this->ajaxSuccess('支付成功');
        } catch (\Exception $e) {
            return $this->ajaxError('支付失败：' . $e->getMessage());
        }
    }

    /**
     * 商品订单支付成功后的后续处理（自动发货、卡密、发邮件等）
     */
    protected function handleProductOrderAfterPaid($order)
    {
        // 重新查一次，确保状态
        $order = \app\admin\model\ProductOrder::where('id', $order->id)->find();
        if (!$order) return;

        // 自动发货
        if ($order->delivery_method == 'auto' && $order->status == \app\common\constants\OrderStatus::PAID) {
            $cardInfo = json_decode($order->card_info, true);
            if (!empty($cardInfo)) {
                // 并发安全：用where条件将订单状态从PAID原子更新为COMPLETED
                $updateResult = \app\admin\model\ProductOrder::where('id', $order->id)
                    ->where('status', \app\common\constants\OrderStatus::PAID)
                    ->update(['status' => \app\common\constants\OrderStatus::COMPLETED, 'finished_at' => date('Y-m-d H:i:s')]);
                if ($updateResult) {
                    foreach ($cardInfo as $card) {
                        \app\admin\model\ProductStock::where('id', $card['id'])
                            ->update([
                                'status' => \app\common\constants\StockStatus::SOLD,
                                'order_id' => $order->id
                            ]);
                    }
                    // 发邮件
                    if (!empty($order->user_email)) {
                        $emailService = new \app\common\service\EmailService();
                        $emailResult = $emailService->sendCardInfo($order->user_email, [
                            'order_no' => $order->order_number,
                            'product_name' => $order->product_name,
                            'card_info' => "<br>" . implode("<br>", array_column($cardInfo, 'card_info')),
                            'query_password' => $order->query_password
                        ]);
                        if ($emailResult['code'] != 200) {
                            // 邮件发送失败，订单状态改为发货失败
                            \app\admin\model\ProductOrder::where('id', $order->id)
                                ->where('status', \app\common\constants\OrderStatus::COMPLETED)
                                ->update(['status' => \app\common\constants\OrderStatus::DELIVERY_FAILED, 'remark' => '发送卡密邮件失败：' . $emailResult['message']]);
                        }
                    }
                }
            }
        } 
        // 手动发货
        elseif ($order->delivery_method == 'manual' && $order->status == \app\common\constants\OrderStatus::PAID) {
            // 创建手动发货通知
            \app\admin\model\Notification::createManualShipmentNotification($order->order_number, $order->id);
        }
    }
} 