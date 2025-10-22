<?php
namespace app\index\controller;

use app\admin\model\RechargeOrder as RechargeOrderModel;
use app\admin\model\User as UserModel;
use app\admin\model\UserBalanceLog;
use app\common\traits\ApiResponse;
use app\common\service\PaymentService;
use app\common\constants\OrderStatus;
use app\common\constants\PaymentMethod;
use think\Log;
use think\Db;

class Recharge extends Base
{
    use ApiResponse;

    protected $paymentService;

    protected function _initialize()
    {
        parent::_initialize();
        $this->paymentService = new PaymentService();
    }

    /**
     * 创建充值订单
     */
    public function create()
    {
        try {
            // 验证用户登录
            $userInfo = $this->getUserInfo();
            if (!$userInfo) {
                return $this->ajaxError('请先登录');
            }

            $data = $this->request->post();
            
            // 验证必填字段
            if (empty($data['amount']) || $data['amount'] <= 0) {
                return $this->ajaxError('充值金额必须大于0');
            }

            // 验证充值金额范围
            $minAmount = 1; // 最小充值金额
            $maxAmount = 50000; // 最大充值金额
            if ($data['amount'] < $minAmount || $data['amount'] > $maxAmount) {
                return $this->ajaxError("充值金额必须在{$minAmount}-{$maxAmount}元之间");
            }

            // 生成订单号（以R开头）
            $orderNumber = 'R' . date('YmdHis') . mt_rand(1000, 9999);

            // 开启事务
            Db::startTrans();
            try {
                // 创建充值订单
                $order = RechargeOrderModel::create([
                    'order_no' => $orderNumber,
                    'user_id' => $userInfo['id'],
                    'nickname' => $userInfo['nickname'],
                    'user_email' => $userInfo['email'],
                    'recharge_amount' => $data['amount'],
                    'fee' => 0, // 手续费，支付时计算
                    'arrive_amount' => $data['amount'], // 到账金额，支付时计算
                    'status' => 0 // 待支付
                ]);
                
                Db::commit();
                
                return $this->ajaxSuccess('创建成功', [
                    'order_no' => $order->order_no,
                    'amount' => $order->recharge_amount,
                    'created_at' => $order->created_at
                ]);
                
            } catch (\Exception $e) {
                Db::rollback();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('创建充值订单失败：' . $e->getMessage());
            return $this->ajaxError('创建充值订单失败：' . $e->getMessage());
        }
    }

    /**
     * 支付充值订单
     */
    public function pay()
    {
        try {
            $data = $this->request->post();
            
            // 验证必填字段
            if (empty($data['order_no']) || empty($data['payment_method'])) {
                return $this->ajaxError('缺少必要参数');
            }
            
            // 验证订单是否存在
            $order = RechargeOrderModel::where('order_no', $data['order_no'])
                ->where('status', 0) // 待支付
                ->find();
                
            if (!$order) {
                return $this->ajaxError('订单不存在或状态错误');
            }

            // 验证用户权限
            $userInfo = $this->getUserInfo();
            if (!$userInfo || $order['user_id'] != $userInfo['id']) {
                return $this->ajaxError('无权限操作此订单');
            }

            // 检查是否已有支付方式
            if (!empty($order->payment_method) && $order->payment_method != $data['payment_method']) {
                return $this->ajaxError('该订单已绑定其他支付方式，无法更改');
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
                    'order_no' => $order->order_no,
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
                // 计算所有支付方式的手续费
                $fees = [
                    PaymentMethod::ALIPAY => bcmul($order['recharge_amount'], bcdiv($paymentConfig->alipay_fee, 100, 4), 2),
                    PaymentMethod::WECHAT => bcmul($order['recharge_amount'], bcdiv($paymentConfig->wechat_fee, 100, 4), 2),
                    PaymentMethod::USDT => bcmul($order['recharge_amount'], bcdiv($paymentConfig->usdt_fee, 100, 4), 2)
                ];
                
                // 更新订单支付信息
                $order->payment_method = $data['payment_method'];
                $order->fee = $fees[$data['payment_method']];
                $order->arrive_amount = bcsub($order['recharge_amount'], $fees[$data['payment_method']], 2);
                $order->usdt_rate = $paymentConfig->usdt_rate;
                $order->usdt_fee = $fees[PaymentMethod::USDT];
                $order->usdt_amount = $data['payment_method'] == PaymentMethod::USDT ? bcdiv($order['recharge_amount'], $paymentConfig->usdt_rate, 4) : 0;
                $order->save();
                
                // 创建支付订单
                $paymentResult = $this->paymentService->createRechargePayment($order, $data['payment_method']);
                
                if ($paymentResult['code'] != 200) {
                    throw new \Exception($paymentResult['message']);
                }
                
                // 保存支付链接和渠道信息
                switch ($data['payment_method']) {
                    case PaymentMethod::ALIPAY:
                        $order->alipay_url = $paymentResult['data']['payment_url'];
                        break;
                    case PaymentMethod::WECHAT:
                        $order->wechat_url = $paymentResult['data']['payment_url'];
                        break;
                    case PaymentMethod::USDT:
                        $order->usdt_url = $paymentResult['data']['payment_url'];
                        break;
                }
                
                // 更新渠道信息
                $order->channel_id = $paymentResult['data']['channel_id'];
                $order->channel_name = $paymentResult['data']['channel_name'];
                $order->fee_rate = $paymentResult['data']['fee_rate'];
                $order->save();
                
                Db::commit();
                
                return $this->ajaxSuccess('请尽快付款', [
                    'order_no' => $order->order_no,
                    'payment_url' => $paymentResult['data']['payment_url']
                ]);
                
            } catch (\Exception $e) {
                Db::rollback();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('创建充值支付失败：' . $e->getMessage());
            return $this->ajaxError('创建充值支付失败：' . $e->getMessage());
        }
    }

    /**
     * 查询充值订单列表
     */
    public function query()
    {
        try {
            // 验证用户登录
            $userInfo = $this->getUserInfo();
            if (!$userInfo) {
                return $this->ajaxError('请先登录');
            }

            $page = $this->request->param('page/d', 1);
            $limit = $this->request->param('limit/d', 10);
            $status = $this->request->param('status', '');

            $where = ['user_id' => $userInfo['id']];
            if ($status !== '') {
                $where['status'] = $status;
            }

            $total = RechargeOrderModel::where($where)->count();
            $list = RechargeOrderModel::where($where)
                ->order('id desc')
                ->page($page, $limit)
                ->select();

            // 为每条订单增加支付方式中文
            foreach ($list as &$item) {
                $item['payment_method_text'] = \app\common\constants\PaymentMethod::getMethodText($item['payment_method']);
            }

            return $this->ajaxSuccess('获取成功', [
                'total' => $total,
                'list' => $list,
                'page' => $page,
                'limit' => $limit
            ]);
            
        } catch (\Exception $e) {
            Log::error('查询充值订单失败：' . $e->getMessage());
            return $this->ajaxError('查询失败');
        }
    }

    /**
     * 查询充值订单详情
     */
    public function detail()
    {
        try {
            // 验证用户登录
            $userInfo = $this->getUserInfo();
            if (!$userInfo) {
                return $this->ajaxError('请先登录');
            }

            $orderNo = $this->request->param('order_no');
            if (empty($orderNo)) {
                return $this->ajaxError('参数错误');
            }

            $order = RechargeOrderModel::where('order_no', $orderNo)
                ->where('user_id', $userInfo['id'])
                ->find();

            if (!$order) {
                return $this->ajaxError('订单不存在');
            }

            // 获取系统配置的订单过期时间
            $systemInfo = \app\admin\model\SystemInfo::find();
            $orderTimeout = $systemInfo ? intval($systemInfo['order_timeout']) : 15; // 默认15分钟

            // 计算订单过期时间
            $createTime = strtotime($order['created_at']);
            $expireTime = $createTime + ($orderTimeout * 60); // 转换为秒
            $currentTime = time();
            $remainingSeconds = max(0, $expireTime - $currentTime); // 剩余秒数，如果已过期则返回0

            // 格式化返回数据
            $orderInfo = [
                'order_no' => $order['order_no'],
                'user_email' => $order['user_email'],
                'nickname' => $order['nickname'],
                'recharge_amount' => $order['recharge_amount'],
                'fee' => $order['fee'],
                'arrive_amount' => $order['arrive_amount'],
                'refund_amount' => $order['refund_amount'],
                'status' => $order['status'],
                'status_text' => $order->getStatusTextAttr('', $order->toArray()),
                'created_at' => $order['created_at'],
                'finished_at' => $order['finished_at'],
                'payment_info' => [
                    'payment_method' => $order['payment_method'],
                    'payment_method_text' => \app\common\constants\PaymentMethod::getMethodText($order['payment_method']),
                    'fee' => $order['fee'],
                    'usdt_rate' => $order['usdt_rate'],
                    'usdt_amount' => $order['usdt_amount']
                ],
                'order_timeout' => $remainingSeconds // 剩余付款秒数
            ];

            return $this->ajaxSuccess('获取成功', $orderInfo);
            
        } catch (\Exception $e) {
            Log::error('查询充值订单详情失败：' . $e->getMessage());
            return $this->ajaxError('查询失败');
        }
    }
} 