<?php
namespace app\index\controller;

use app\common\service\PaymentService;
use app\common\traits\ApiResponse;
use app\common\constants\OrderStatus;
use app\common\constants\StockStatus;
use app\common\constants\PaymentMethod;
use app\common\constants\PaymentStatus;
use app\admin\model\Notification;
use think\Log;
use think\Db;

class Payment extends Base
{
    use ApiResponse;

    protected $paymentService;

    public function _initialize()
    {
        parent::_initialize();
        $this->paymentService = new PaymentService();
    }


    /**
     * 查询支付订单
     */
    public function query()
    {
        try {
            $orderNo = $this->request->param('order_no');
            if (empty($orderNo)) {
                return $this->ajaxError('参数错误');
            }

            // 根据订单号前缀判断订单类型
            if (strpos($orderNo, 'R') === 0) {
                // 充值订单
                $result = $this->paymentService->queryRechargePayment($orderNo);
            } else {
                // 商品订单
                $result = $this->paymentService->queryPayment($orderNo);
            }

            if ($result['code'] != 200) {
                return $this->ajaxError($result['message']);
            }

            return $this->ajaxSuccess('查询成功', $result['data']);
            
        } catch (\Exception $e) {
            Log::error('查询支付订单失败：' . $e->getMessage());
            return $this->ajaxError('查询失败');
        }
    }
    
    public function speedNotify(){
        $data = $this->request->param();
        write_log('notify', 'speed-回调参数', json_encode($data, JSON_UNESCAPED_UNICODE));
        
        $orderId = $data['out_trade_no'];
        
        return $this->notify($data, $orderId);
    }
    
    public function k4Notify(){
        $data = $this->request->param();
        write_log('notify', 'k4-回调参数', json_encode($data, JSON_UNESCAPED_UNICODE));
        
        $orderId = $data['merOrderTid'];
        
        return $this->notify($data, $orderId);
    }
    
    /**
     * PmpPay支付回调通知
     */
    public function pmpPayNotify(){
        $data = $this->request->param();
        write_log('notify', 'pmppay-回调参数', json_encode($data, JSON_UNESCAPED_UNICODE));
        
        $orderId = $data['order_sn'];
        
        return $this->notify($data, $orderId);
    }

    /**
     * 支付回调通知
     */
    public function notify($data, $orderId)
    {
        try {
            write_log('notify', '回调参数', json_encode($data, JSON_UNESCAPED_UNICODE));
            
            // 处理回调
            $result = $this->paymentService->handleNotify($data, $orderId);
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }
            
            
            $order_number = $result['data']['order_number'];

            // 根据订单号前缀判断订单类型
            if (strpos($orderId, 'R') === 0) {
                // 充值订单回调处理
                $this->handleRechargeNotify($order_number, $result['data']);
            } else {
                // 商品订单回调处理
                $this->handleProductNotify($order_number, $result['data']);
            }
            
        } catch (\Exception $e) {
            Log::error('支付回调处理失败：' . $e->getMessage());
            write_log('notify', '支付回调处理失败', $e->getMessage().' | '.$e->getFile().' | '.$e->getLine());
            echo 'SUCCESS';
        }
    }

    /**
     * 处理商品订单回调
     */
    private function handleProductNotify($order_number, $resultData)
    {
        // 获取订单信息
        $order = \app\admin\model\ProductOrder::where('order_number', $order_number)
            ->where('status', OrderStatus::PENDING)
            ->find();
        if (!$order) {
            throw new \Exception('订单不存在或状态错误');
        }
        
        // 更新订单状态为待发货
        $updateResult = \app\admin\model\ProductOrder::where('id', $order->id)
            ->where('status', OrderStatus::PENDING)
            ->update([
                'status' => OrderStatus::PAID,
                'trade_no' => $resultData['trade_no']
            ]);
        
        if (!$updateResult) {
            throw new \Exception('更新订单状态失败');
        }

        // 开启事务
        Db::startTrans();
        try {
            // 获取商品信息
            $product = \app\admin\model\Product::where('id', $order->product_id)->find();
            if (!$product) {
                throw new \Exception('商品不存在');
            }

            // 累计消费统计（只要支付成功就加）
            if (!empty($order['user_email'])) {
                $user = \app\admin\model\User::where('email', $order['user_email'])->find();
                if ($user) {
                    \app\admin\model\User::where('id', $user['id'])->setInc('total_spent', $order['total_price']);
                }
            }

            // 如果是自动发货商品，直接发送卡密
            if ($product->delivery_method == 'auto') {
                // 从订单的card_info字段获取卡密信息
                $cardInfo = json_decode($order->card_info, true);
                if (empty($cardInfo)) {
                    throw new \Exception('卡密信息不存在');
                }

                // 更新卡密状态为已售出
                foreach ($cardInfo as $card) {
                    \app\admin\model\ProductStock::where('id', $card['id'])
                        ->update([
                            'status' => StockStatus::SOLD,
                            'order_id' => $order->id
                        ]);
                }
                if(!empty($order->user_email)){
                    // 发送邮件
                    $emailService = new \app\common\service\EmailService();
                    $emailResult = $emailService->sendCardInfo($order->user_email, [
                        'order_no' => $order->order_number,
                        'product_name' => $order->product_name,
                        'card_info' => "<br>" . implode("<br>", array_column($cardInfo, 'card_info')),
                        'query_password' => $order->query_password
                    ]);

                    if ($emailResult['code'] != 200) {
                        // 发送邮件失败，更新订单状态为已取消
                        \app\admin\model\ProductOrder::where('id', $order->id)
                            ->where('status', OrderStatus::PAID)
                            ->update(['status' => OrderStatus::DELIVERY_FAILED, 'remark' => '发送卡密邮件失败：' . $emailResult['message']]);
                    }else{
                        // 发送邮件成功，更新订单状态为已完成
                        \app\admin\model\ProductOrder::where('id', $order->id)
                            ->where('status', OrderStatus::PAID)
                            ->update(['status' => OrderStatus::COMPLETED, 'finished_at' => date('Y-m-d H:i:s')]);
                    }
                }else{
                    // 发送邮件成功，更新订单状态为已完成
                    \app\admin\model\ProductOrder::where('id', $order->id)
                        ->where('status', OrderStatus::PAID)
                        ->update(['status' => OrderStatus::COMPLETED, 'finished_at' => date('Y-m-d H:i:s')]);
                }
            } else {
                // 手动发货商品，创建通知
                Notification::createManualShipmentNotification($order->order_number, $order->id);
            }
        
            write_log('notify', '商品订单支付回调处理成功', $order_number);

            Db::commit();
            echo 'SUCCESS';
            
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }

    /**
     * 处理充值订单回调
     */
    private function handleRechargeNotify($order_number, $resultData)
    {
        // 开启事务
        Db::startTrans();
        try {
            // 查找充值订单，状态为待支付
            $order = \app\admin\model\RechargeOrder::where('order_no', $order_number)
                ->where('status', 0)
                ->lock(true)
                ->find();
            if (!$order) {
                throw new \Exception('充值订单不存在或状态错误');
            }
            
            // 查找用户
            $user = \app\admin\model\User::where('id', $order['user_id'])->lock(true)->find();
            if (!$user) {
                throw new \Exception('用户不存在');
            }
            
            // 给用户加余额和累计充值（充值金额recharge_amount）
            $updateResult = \app\admin\model\User::where('id', $user['id'])
                ->update([
                    'balance' => Db::raw('balance + ' . $order['recharge_amount']),
                    'total_recharge' => Db::raw('total_recharge + ' . $order['recharge_amount'])
                ]);
            
            if (!$updateResult) {
                throw new \Exception('更新用户余额失败');
            }
            
            // 自动调整VIP等级
            \app\admin\model\User::autoUpdateVipLevel($user['id']);
            
            // 更新订单状态
            $order->status = 1; // 已完成
            $order->finished_at = date('Y-m-d H:i:s');
            $order->trade_no = isset($resultData['trade_no']) ? $resultData['trade_no'] : '';
            $order->save();
            
            // 写入余额日志
            $before = $user['balance'];
            $after = $user['balance'] + $order['recharge_amount'];
            \app\admin\model\UserBalanceLog::create([
                'user_id' => $user['id'],
                'type' => '充值',
                'amount' => $order['recharge_amount'],
                'before_balance' => $before,
                'after_balance' => $after,
                'direction' => 'in',
                'order_no' => $order['order_no'],
                'remark' => '在线充值',
                'operator' => 'system'
            ]);
            
            write_log('notify', '充值订单支付回调处理成功', $order_number);
            Db::commit();
            echo 'SUCCESS';
        } catch (\Exception $e) {
            Db::rollback();
            write_log('notify1', '充值订单支付回调处理失败', $e->getMessage().' | '.$e->getFile().' | '.$e->getLine());
            echo 'SUCCESS'; // 保证回调响应
        }
    }

    /**
     * 支付同步返回
     */
    public function returnUrl()
    {
        try {
            $data = $this->request->param();
            
            // 处理返回
            $result = $this->paymentService->handleNotify($data);
            if (!$result) {
                throw new \Exception('处理返回失败');
            }

            // 跳转到订单详情页
            $this->redirect(url('index/Order/detail', ['order_no' => $data['order_no']]));
            
        } catch (\Exception $e) {
            Log::error('支付返回处理失败：' . $e->getMessage());
            $this->error('支付返回处理失败');
        }
    }

    /**
     * 创建支付
     */
    public function createPayment()
    {
        try {
            $data = $this->request->post();
            
            // 验证必填字段
            if (empty($data['order_no']) || empty($data['payment_method'])) {
                return $this->ajaxError('缺少必要参数');
            }
            
            // 验证订单是否存在
            $order = \app\admin\model\ProductOrder::where('order_number', $data['order_no'])
                ->where('status', OrderStatus::PENDING)
                ->find();
                
            if (!$order) {
                return $this->ajaxError('订单不存在或状态错误');
            }
            
            // 检查是否为0元订单
            $isZeroAmount = floatval($order['total_price']) == 0;
            
            // 检查是否已有支付方式
            if (!empty($order->payment_method) && $order->payment_method != $data['payment_method']) {
                return $this->ajaxError('该订单已绑定其他支付方式，无法更改');
            }
            
            // 0元订单直接成功处理
            if ($isZeroAmount) {
                return $this->handleZeroAmountPayment($order, $data['payment_method']);
            }
            
            // 检查是否已有支付链接
            $paymentUrl = '';
            switch ($data['payment_method']) {
                case PaymentMethod::ALIPAY:
                    $paymentUrl = $order->alipay_url;
                    break;
                case PaymentMethod::WECHAT:
                    $paymentUrl = $order->wechat_url;
                    break;
                case PaymentMethod::USDT:
                    $paymentUrl = $order->usdt_url;
                    break;
            }
            
            // 如果已有支付链接，直接返回
            if (!empty($paymentUrl)) {
                return $this->ajaxSuccess('请尽快付款', [
                    'order_no' => $order->order_number,
                    'payment_url' => $paymentUrl
                ]);
            }
            
            // 获取支付配置
            $paymentConfig = \app\admin\model\PaymentConfig::find(1);
            if (!$paymentConfig) {
                throw new \Exception('支付配置不存在');
            }
            
            // 开启事务
            Db::startTrans();
            try {
                // 重新查询订单，确保数据最新
                $order = \app\admin\model\ProductOrder::where('order_number', $data['order_no'])
                    ->where('status', OrderStatus::PENDING)
                    ->lock(true) // 添加行锁，防止并发
                    ->find();
                    
                if (!$order) {
                    throw new \Exception('订单不存在或状态错误');
                }
                
                // 再次检查是否已有支付方式
                if (!empty($order->payment_method) && $order->payment_method != $data['payment_method']) {
                    throw new \Exception('该订单已绑定其他支付方式，无法更改');
                }
                
                // 在事务内选择支付通道
                $paymentService = new PaymentService();
                $channelInfo = \app\common\service\payment\PaymentChannelSelector::selectChannel($data['payment_method'], $order['total_price']);
                if (!$channelInfo) {
                    throw new \Exception('没有可用的支付通道');
                }
                
                // 计算所有支付方式的手续费
                $fees = [
                    PaymentMethod::ALIPAY => bcmul($order['total_price'], bcdiv($paymentConfig->alipay_fee, 100, 4), 2),
                    PaymentMethod::WECHAT => bcmul($order['total_price'], bcdiv($paymentConfig->wechat_fee, 100, 4), 2),
                    PaymentMethod::USDT => bcmul($order['total_price'], bcdiv($paymentConfig->usdt_fee, 100, 4), 2)
                ];
                
                // 使用WHERE条件判断，只有在channel_id为null时才更新
                $updateData = [
                    'payment_method' => $data['payment_method'],
                    'channel_id' => $channelInfo['channel_id'],
                    'channel_name' => $channelInfo['channel_name'],
                    'fee_rate' => $channelInfo['fee_rate'],
                    'fee' => $channelInfo['fee'],
                    'usdt_rate' => $paymentConfig->usdt_rate,
                    'usdt_fee' => $fees[PaymentMethod::USDT],
                    'usdt_amount' => $data['payment_method'] == PaymentMethod::USDT ? bcdiv($order['total_price'], $paymentConfig->usdt_rate, 4) : 0
                ];
                
                // 使用WHERE条件确保只有channel_id为null时才能更新
                $updateResult = \app\admin\model\ProductOrder::where('id', $order->id)
                    ->where('status', OrderStatus::PENDING)
                    ->where('channel_id', null) // 关键：只有channel_id为null时才能更新
                    ->update($updateData);
                
                if (!$updateResult) {
                    // 如果更新失败，说明订单已经被其他请求处理了，直接抛出异常
                    throw new \Exception('订单已被其他请求处理，请稍后重试');
                }
                
                // 重新查询获取最新的订单数据
                $order = \app\admin\model\ProductOrder::where('id', $order->id)->find();
                
                // 创建支付订单（传入已选择的通道信息）
                $paymentResult = $paymentService->createPaymentWithChannel($order, $data['payment_method'], $channelInfo);
                
                if ($paymentResult['code'] != 200) {
                    throw new \Exception($paymentResult['message']);
                }
                
                // 保存支付链接（使用WHERE条件确保原子性）
                $paymentUrlField = '';
                switch ($data['payment_method']) {
                    case PaymentMethod::ALIPAY:
                        $paymentUrlField = 'alipay_url';
                        break;
                    case PaymentMethod::WECHAT:
                        $paymentUrlField = 'wechat_url';
                        break;
                    case PaymentMethod::USDT:
                        $paymentUrlField = 'usdt_url';
                        break;
                }
                
                // 更新支付链接，确保原子性
                $urlUpdateResult = \app\admin\model\ProductOrder::where('id', $order->id)
                    ->where('channel_id', $channelInfo['channel_id']) // 确保是同一个通道
                    ->where($paymentUrlField, null) // 关键：只有支付链接为null时才能更新
                    ->update([$paymentUrlField => $paymentResult['data']['payment_url']]);
                
                if (!$urlUpdateResult) {
                    throw new \Exception('更新支付链接失败');
                }
                
                Db::commit();
                
                return $this->ajaxSuccess('请尽快付款', [
                    'order_no' => $order->order_number,
                    'payment_url' => $paymentResult['data']['payment_url']
                ]);
                
            } catch (\Exception $e) {
                Db::rollback();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('创建支付失败：' . $e->getMessage());
            return $this->ajaxError('创建支付失败：' . $e->getMessage());
        }
    }

    /**
     * 获取支付方式
     */
    public function getPaymentMethods()
    {
        try {
            // 获取支付配置
            $paymentConfig = \app\admin\model\PaymentConfig::find(1);
            if (!$paymentConfig) {
                return $this->ajaxError('支付配置不存在');
            }

            // 构建支付方式列表
            $paymentMethods = [];

            // 从header获取token，判断用户是否登录
            $token = $this->request->header('Authorization');
            $isLoggedIn = false;
            if ($token) {
                // 如果是 Bearer token，需要去掉 'Bearer ' 前缀
                if (strpos($token, 'Bearer ') === 0) {
                    $token = substr($token, 7);
                }
                
                // 通过新的会话系统验证token
                $session = \app\admin\model\UserSession::validateToken($token);
                if ($session) {
                    $user = \app\admin\model\User::where('id', $session['user_id'])
                        ->where('status', 1)
                        ->find();
                    if ($user) {
                        $isLoggedIn = true;
                        // 更新会话的最后活动时间
                        \app\admin\model\UserSession::updateLastActivity($token);
                    }
                }
            }

            // 余额支付（仅登录用户可见）
            if ($isLoggedIn) {
                $paymentMethods[] = [
                    'code' => 'balance',
                    'name' => '余额支付',
                    'icon' => 'icon-yinxingqia',
                    'description' => '使用账户余额支付',
                    'fee' => 0
                ];
            }


            // 余额支付（仅登录用户可见）
            // $userInfo = $this->getUserInfo();
            // if ($userInfo) {
            //     $paymentMethods[] = [
            //         'code' => 'balance',
            //         'name' => '余额支付',
            //         'icon' => 'icon-yinxingqia',
            //         'description' => '使用账户余额支付',
            //         'fee' => 0
            //     ];
            // }

            // 支付宝支付 - 检查是否有启用的支付宝通道
            $alipayChannels = \app\admin\model\PaymentChannelRate::where('payment_method', 'alipay')
                ->where('status', 1)
                ->count();
            if ($alipayChannels > 0) {
                $paymentMethods[] = [
                    'code' => 'alipay',
                    'name' => '支付宝支付',
                    'icon' => 'icon-alipay',
                    'description' => '使用支付宝扫码支付',
                    'fee' => $paymentConfig->alipay_fee
                ];
            }

            // 微信支付 - 检查是否有启用的微信通道
            $wechatChannels = \app\admin\model\PaymentChannelRate::where('payment_method', 'wechat')
                ->where('status', 1)
                ->count();
            if ($wechatChannels > 0) {
                $paymentMethods[] = [
                    'code' => 'wechat',
                    'name' => '微信支付',
                    'icon' => 'icon-wechat',
                    'description' => '使用微信扫码支付',
                    'fee' => $paymentConfig->wechat_fee
                ];
            }

            // USDT支付 - 检查是否有启用的USDT通道
            $usdtChannels = \app\admin\model\PaymentChannelRate::where('payment_method', 'usdt')
                ->where('status', 1)
                ->count();
            if ($usdtChannels > 0) {
                $paymentMethods[] = [
                    'code' => 'usdt',
                    'name' => 'USDT支付',
                    'icon' => 'icon-wallet',
                    'description' => '使用USDT支付',
                    'fee' => $paymentConfig->usdt_fee,
                    'rate' => $paymentConfig->usdt_rate
                ];
            }

            return $this->ajaxSuccess('获取成功', [
                'payment_methods' => $paymentMethods
            ]);
            
        } catch (\Exception $e) {
            Log::error('获取支付方式失败：' . $e->getMessage());
            return $this->ajaxError('获取失败');
        }
    }

    /**
     * 获取商品教程视频
     */
    public function getProductVideo()
    {
        try {
            $orderNo = $this->request->param('order_no');
            if (empty($orderNo)) {
                return $this->ajaxError('订单号不能为空');
            }
            
            // 获取订单信息
            $order = \app\admin\model\ProductOrder::where('order_number', $orderNo)->find();
            if (!$order) {
                return $this->ajaxError('订单不存在');
            }
            
            // 获取商品信息
            $product = \app\admin\model\Product::find($order['product_id']);
            if (!$product) {
                return $this->ajaxError('商品不存在');
            }
            
            // 检查商品是否有教程视频（需要有URL且状态为启用）
            $hasVideo = !empty($product['tutorial_video']) && $product['tutorial_video_status'] == 1;
            
            $result = [
                'has_video' => $hasVideo,
                'video_url' => $hasVideo ? $product['tutorial_video'] : '',
                'video_name' => $hasVideo ? $product['tutorial_video_name'] : '',
            ];
            
            return $this->ajaxSuccess('获取成功', $result);
            
        } catch (\Exception $e) {
            Log::error('获取商品视频失败：' . $e->getMessage());
            return $this->ajaxError('获取失败');
        }
    }

    /**
     * 获取支付方式列表（完整版本）
     */
    public function getPaymentMethodsFull()
    {
        try {
            // 获取支付配置
            $paymentConfig = \app\admin\model\PaymentConfig::find(1);
            if (!$paymentConfig) {
                return $this->ajaxError('支付配置不存在');
            }

            // 构建支付方式列表
            $paymentMethods = [];

            // 从header获取token，判断用户是否登录
            $token = $this->request->header('Authorization');
            $isLoggedIn = false;
            if ($token) {
                // 如果是 Bearer token，需要去掉 'Bearer ' 前缀
                if (strpos($token, 'Bearer ') === 0) {
                    $token = substr($token, 7);
                }
                
                // 通过新的会话系统验证token
                $session = \app\admin\model\UserSession::validateToken($token);
                if ($session) {
                    $user = \app\admin\model\User::where('id', $session['user_id'])
                        ->where('status', 1)
                        ->find();
                    if ($user) {
                        $isLoggedIn = true;
                        // 更新会话的最后活动时间
                        \app\admin\model\UserSession::updateLastActivity($token);
                    }
                }
            }

            // 余额支付（仅登录用户可见）
            // $userInfo = $this->getUserInfo();
            // if ($userInfo) {
            //     $paymentMethods[] = [
            //         'code' => 'balance',
            //         'name' => '余额支付',
            //         'icon' => 'icon-yinxingqia',
            //         'description' => '使用账户余额支付',
            //         'fee' => 0
            //     ];
            // }

            // 支付宝支付 - 检查是否有启用的支付宝通道
            $alipayChannels = \app\admin\model\PaymentChannelRate::where('payment_method', 'alipay')
                ->where('status', 1)
                ->count();
            if ($alipayChannels > 0) {
                $paymentMethods[] = [
                    'code' => 'alipay',
                    'name' => '支付宝支付',
                    'icon' => 'icon-alipay',
                    'description' => '使用支付宝扫码支付',
                    'fee' => $paymentConfig->alipay_fee
                ];
            }

            // 微信支付 - 检查是否有启用的微信通道
            $wechatChannels = \app\admin\model\PaymentChannelRate::where('payment_method', 'wechat')
                ->where('status', 1)
                ->count();
            if ($wechatChannels > 0) {
                $paymentMethods[] = [
                    'code' => 'wechat',
                    'name' => '微信支付',
                    'icon' => 'icon-wechat',
                    'description' => '使用微信扫码支付',
                    'fee' => $paymentConfig->wechat_fee
                ];
            }

            // USDT支付 - 检查是否有启用的USDT通道
            $usdtChannels = \app\admin\model\PaymentChannelRate::where('payment_method', 'usdt')
                ->where('status', 1)
                ->count();
            if ($usdtChannels > 0) {
                $paymentMethods[] = [
                    'code' => 'usdt',
                    'name' => 'USDT支付',
                    'icon' => 'icon-wallet',
                    'description' => '使用USDT支付',
                    'fee' => $paymentConfig->usdt_fee,
                    'rate' => $paymentConfig->usdt_rate
                ];
            }

            return $this->ajaxSuccess('获取成功', [
                'payment_methods' => $paymentMethods
            ]);
            
        } catch (\Exception $e) {
            Log::error('获取支付方式失败：' . $e->getMessage());
            return $this->ajaxError('获取失败');
        }
    }
    
    /**
     * 处理0元订单支付
     * @param object $order 订单对象
     * @param string $paymentMethod 支付方式
     * @return array
     */
    private function handleZeroAmountPayment($order, $paymentMethod)
    {
        try {
            // 开启事务
            Db::startTrans();
            
            try {
                // 重新查询订单，确保数据最新
                $order = \app\admin\model\ProductOrder::where('order_number', $order->order_number)
                    ->where('status', OrderStatus::PENDING)
                    ->lock(true) // 添加行锁，防止并发
                    ->find();
                    
                if (!$order) {
                    throw new \Exception('订单不存在或状态错误');
                }
                
                // 处理余额支付的特殊逻辑
                if ($paymentMethod === 'balance') {
                    // 余额支付0元订单，直接扣减0元
                    $userInfo = $this->getUserInfo();
                    if (!$userInfo) {
                        throw new \Exception('请先登录');
                    }
                    
                    $user = \app\admin\model\User::where('id', $userInfo['id'])->find();
                    if (!$user) {
                        throw new \Exception('用户不存在');
                    }
                    
                    // 记录余额变动日志（0元变动）
                    \app\admin\model\UserBalanceLog::create([
                        'user_id' => $userInfo['id'],
                        'type' => 'consume',
                        'amount' => 0,
                        'before_balance' => $user['balance'],
                        'after_balance' => $user['balance'],
                        'order_no' => $order->order_number,
                        'remark' => '商品订单余额支付（0元）',
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                    
                    // 增加累计消费（0元）
                    \app\admin\model\User::where('id', $userInfo['id'])->setInc('total_spent', 0);
                }
                
                // 更新订单状态为已支付
                $order->status = OrderStatus::PAID;
                $order->payment_method = $paymentMethod;
                $order->channel_id = 0; // 0元订单不需要通道
                $order->channel_name = '免费商品';
                $order->fee_rate = 0;
                $order->fee = 0;
                $order->save();
                
                // 处理库存扣减
                $this->handleStockDeduction($order);
                
                // 处理手动发货通知
                $this->handleManualShipmentNotification($order);
                
                Db::commit();
                
                return $this->ajaxSuccess('支付成功', [
                    'order_no' => $order->order_number,
                    'payment_url' => '', // 0元订单不需要支付链接
                    'is_success' => true,
                    'message' => '支付成功，请前往订单页面查看'
                ]);
                
            } catch (\Exception $e) {
                Db::rollback();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('0元订单支付处理失败：' . $e->getMessage());
            return $this->ajaxError('支付失败：' . $e->getMessage());
        }
    }
    
    /**
     * 处理库存扣减
     * @param object $order 订单对象
     */
    private function handleStockDeduction($order)
    {
        try {
            // 扣减库存 - 将指定数量的卡密状态从UNUSED改为SOLD
            $stocks = \app\admin\model\ProductStock::where('product_id', $order->product_id)
                ->where('status', StockStatus::UNUSED)
                ->limit($order->quantity)
                ->select();
                
            if ($stocks && count($stocks) >= $order->quantity) {
                foreach ($stocks as $stock) {
                    $stock->status = StockStatus::SOLD;
                    $stock->order_id = $order->id;
                    $stock->save();
                }
            }
        } catch (\Exception $e) {
            Log::error('库存扣减失败：' . $e->getMessage());
            // 库存扣减失败不影响支付流程
        }
    }
    
    /**
     * 处理手动发货通知
     * @param object $order 订单对象
     */
    private function handleManualShipmentNotification($order)
    {
        try {
            // 获取商品信息
            $product = \app\admin\model\Product::find($order->product_id);
            if ($product && $product->delivery_method === 'manual') {
                // 手动发货商品，创建通知
                Notification::createManualShipmentNotification($order->order_number, $order->id);
            }
        } catch (\Exception $e) {
            Log::error('创建手动发货通知失败：' . $e->getMessage());
            // 通知创建失败不影响支付流程
        }
    }
} 