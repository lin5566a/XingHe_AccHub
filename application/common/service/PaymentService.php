<?php
namespace app\common\service;

use app\common\service\payment\PaymentFactory;
use app\common\service\payment\PaymentChannelSelector;
use app\common\constants\OrderStatus;
use app\admin\model\ProductOrder;
use app\admin\model\RechargeOrder;
use app\admin\model\PaymentChannel;
use app\admin\model\User as UserModel;
use app\admin\model\UserBalanceLog;
use think\Log;
use think\Db;

class PaymentService
{
    /**
     * 创建支付订单（使用已选择的通道）
     * @param array|object $order 订单信息
     * @param string $paymentMethod 支付方式
     * @param array $channelInfo 已选择的通道信息
     * @return array
     */
    public function createPaymentWithChannel($order, $paymentMethod, $channelInfo)
    {
        try {
            // 获取支付渠道实例
            $paymentChannel = PaymentFactory::getChannel($channelInfo['abbr']);
            
            // 使用数据库中的配置信息
            $config = [
                'merchant_id' => $channelInfo['merchant_id'],
                'merchant_key' => $channelInfo['merchant_key'],
                'create_order_url' => $channelInfo['create_order_url'],
                'query_order_url' => $channelInfo['query_order_url'],
                'balance_query_url' => $channelInfo['balance_query_url'],
                'notify_url' => $channelInfo['notify_url'],
                'return_url' => $channelInfo['return_url'],
                'product_code' => $channelInfo['product_code']
            ];
            
            // 调用渠道创建支付，使用数据库配置
            $result = $paymentChannel->createPayment($order, $config);
            
            if ($result['success']) {
                return [
                    'code' => 200,
                    'message' => '创建成功',
                    'data' => [
                        'payment_url' => $result['data']['pay_url'],
                        'order_no' => $result['data']['order_number']
                    ]
                ];
            } else {
                return [
                    'code' => 500,
                    'message' => $result['message']
                ];
            }
        } catch (\Exception $e) {
            Log::error('创建支付订单失败：' . $e->getMessage());
            return [
                'code' => 500,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * 创建支付订单（多通道选择逻辑）
     * @param array|object $order 订单信息
     * @param string $paymentMethod 支付方式
     * @return array
     */
    public function createPayment($order, $paymentMethod)
    {
        try {
            // 获取订单金额
            $amount = is_array($order) ? $order['total_price'] : $order->total_price;
            
            // 选择支付通道
            $channelInfo = PaymentChannelSelector::selectChannel($paymentMethod, $amount);
            if (!$channelInfo) {
                throw new \Exception('没有可用的支付通道');
            }
            
            // 获取支付渠道实例
            $paymentChannel = PaymentFactory::getChannel($channelInfo['abbr']);
            
            // 使用数据库中的配置信息
            $config = [
                'merchant_id' => $channelInfo['merchant_id'],
                'merchant_key' => $channelInfo['merchant_key'],
                'create_order_url' => $channelInfo['create_order_url'],
                'query_order_url' => $channelInfo['query_order_url'],
                'balance_query_url' => $channelInfo['balance_query_url'],
                'notify_url' => $channelInfo['notify_url'],
                'return_url' => $channelInfo['return_url'],
                'product_code' => $channelInfo['product_code']
            ];
            
            // 调用渠道创建支付，使用数据库配置
            $result = $paymentChannel->createPayment($order, $config);
            
            if ($result['success']) {
                // 更新订单状态和通道信息
                $orderId = is_array($order) ? $order['id'] : $order->id;
                ProductOrder::where('id', $orderId)->update([
                    'status' => OrderStatus::PENDING, // 待付款
                    'payment_method' => $paymentMethod,
                    'channel_id' => $channelInfo['channel_id'],
                    'channel_name' => $channelInfo['channel_name'],
                    'fee_rate' => $channelInfo['fee_rate'],
                    'fee' => $channelInfo['fee']
                ]);

                return [
                    'code' => 200,
                    'message' => '创建成功',
                    'data' => [
                        'payment_url' => $result['data']['pay_url'],
                        'order_no' => $result['data']['order_number']
                    ]
                ];
            } else {
                return [
                    'code' => 500,
                    'message' => $result['message']
                ];
            }
        } catch (\Exception $e) {
            Log::error('创建支付订单失败：' . $e->getMessage());
            return [
                'code' => 500,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * 创建充值支付订单
     * @param array|object $order 充值订单信息
     * @param string $paymentMethod 支付方式
     * @return array
     */
    public function createRechargePayment($order, $paymentMethod)
    {
        try {
            // 获取订单金额
            $amount = is_array($order) ? $order['recharge_amount'] : $order->recharge_amount;
            
            // 选择支付通道
            $channelInfo = PaymentChannelSelector::selectChannel($paymentMethod, $amount);
            if (!$channelInfo) {
                throw new \Exception('没有可用的支付通道');
            }
            
            // 获取支付渠道实例
            $paymentChannel = PaymentFactory::getChannel($channelInfo['abbr']);
            
            // 使用数据库中的配置信息
            $config = [
                'merchant_id' => $channelInfo['merchant_id'],
                'merchant_key' => $channelInfo['merchant_key'],
                'create_order_url' => $channelInfo['create_order_url'],
                'query_order_url' => $channelInfo['query_order_url'],
                'balance_query_url' => $channelInfo['balance_query_url'],
                'notify_url' => $channelInfo['notify_url'],
                'return_url' => $channelInfo['return_url'],
                'product_code' => $channelInfo['product_code']
            ];
            
            // 准备订单数据，添加支付方式并统一字段名
            $orderData = is_array($order) ? $order : $order->toArray();
            $orderData['payment_method'] = $paymentMethod;
            $orderData['total_price'] = $amount; // 充值订单使用recharge_amount作为total_price
            $orderData['order_number'] = $orderData['order_no']; // 统一字段名映射
            
            // 调用渠道创建支付，使用数据库配置
            $result = $paymentChannel->createPayment($orderData, $config);
            
            if ($result['success']) {
                return [
                    'code' => 200,
                    'message' => '创建成功',
                    'data' => [
                        'payment_url' => $result['data']['pay_url'],
                        'order_no' => $result['data']['order_number'],
                        'channel_id' => $channelInfo['channel_id'],
                        'channel_name' => $channelInfo['channel_name'],
                        'fee_rate' => $channelInfo['fee_rate']
                    ]
                ];
            } else {
                return [
                    'code' => 500,
                    'message' => $result['message']
                ];
            }
        } catch (\Exception $e) {
            Log::error('创建充值支付订单失败：' . $e->getMessage());
            return [
                'code' => 500,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * 查询支付订单
     * @param string $orderNo 订单号
     * @return array
     */
    public function queryPayment($orderNo)
    {
        try {
            // 获取订单信息
            $order = ProductOrder::where('order_number', $orderNo)->find();
            if (!$order) {
                throw new \Exception('订单不存在');
            }

            // 获取支付渠道实例
            $paymentChannel = PaymentFactory::getChannel($order['channel_name']);
            
            // 获取渠道配置（根据渠道ID获取，而不是重新选择）
            $channelInfo = $this->getChannelConfigById($order['channel_id'], $order['payment_method']);
            if (!$channelInfo) {
                throw new \Exception('支付通道配置不存在');
            }
            
            // 使用数据库中的配置信息
            $config = [
                'merchant_id' => $channelInfo['merchant_id'],
                'merchant_key' => $channelInfo['merchant_key'],
                'create_order_url' => $channelInfo['create_order_url'],
                'query_order_url' => $channelInfo['query_order_url'],
                'balance_query_url' => $channelInfo['balance_query_url'],
                'notify_url' => $channelInfo['notify_url'],
                'return_url' => $channelInfo['return_url'],
                'product_code' => $channelInfo['product_code']
            ];
            
            // 调用渠道查询支付，使用数据库配置
            $result = $paymentChannel->queryPayment($orderNo, $config);
            
            if ($result['success']) {
                if ($result['data']['status'] == 'SUCCESS') {
                    // 更新订单状态
                    ProductOrder::where('id', $order['id'])->update([
                        'status' => OrderStatus::COMPLETED // 已完成
                    ]);
                }

                return [
                    'code' => 200,
                    'message' => '查询成功',
                    'data' => [
                        'status' => $result['data']['status'],
                        'order_no' => $result['data']['order_number']
                    ]
                ];
            } else {
                return [
                    'code' => 500,
                    'message' => $result['message']
                ];
            }
        } catch (\Exception $e) {
            Log::error('查询支付订单失败：' . $e->getMessage());
            return [
                'code' => 500,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * 查询充值支付订单
     * @param string $orderNo 订单号
     * @return array
     */
    public function queryRechargePayment($orderNo)
    {
        try {
            // 获取充值订单信息
            $order = RechargeOrder::where('order_no', $orderNo)->find();
            if (!$order) {
                throw new \Exception('充值订单不存在');
            }

            // 获取支付渠道实例
            $paymentChannel = PaymentFactory::getChannel($order['channel_name']);
            
            // 获取渠道配置（根据渠道ID获取，而不是重新选择）
            $channelInfo = $this->getChannelConfigById($order['channel_id'], $order['payment_method']);
            if (!$channelInfo) {
                throw new \Exception('支付通道配置不存在');
            }
            
            // 使用数据库中的配置信息
            $config = [
                'merchant_id' => $channelInfo['merchant_id'],
                'merchant_key' => $channelInfo['merchant_key'],
                'create_order_url' => $channelInfo['create_order_url'],
                'query_order_url' => $channelInfo['query_order_url'],
                'balance_query_url' => $channelInfo['balance_query_url'],
                'notify_url' => $channelInfo['notify_url'],
                'return_url' => $channelInfo['return_url'],
                'product_code' => $channelInfo['product_code']
            ];
            
            // 准备订单数据，统一字段名
            $orderData = $order->toArray();
            $orderData['order_number'] = $orderData['order_no']; // 统一字段名映射
            
            // 调用渠道查询支付，使用数据库配置
            $result = $paymentChannel->queryPayment($orderNo, $config);
            
            if ($result['success']) {
                if ($result['data']['status'] == 'SUCCESS') {
                    // 更新订单状态
                    RechargeOrder::where('id', $order['id'])->update([
                        'status' => 1 // 已完成
                    ]);
                }

                return [
                    'code' => 200,
                    'message' => '查询成功',
                    'data' => [
                        'status' => $result['data']['status'],
                        'order_no' => $result['data']['order_number']
                    ]
                ];
            } else {
                return [
                    'code' => 500,
                    'message' => $result['message']
                ];
            }
        } catch (\Exception $e) {
            Log::error('查询充值支付订单失败：' . $e->getMessage());
            return [
                'code' => 500,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * 处理支付回调
     * @param array $data 回调数据
     * @return array
     */
    public function handleNotify($data, $orderId)
    {
        try {
            // 根据订单号前缀判断订单类型
            if (strpos($orderId, 'R') === 0) {
                // 充值订单
                return $this->handleRechargeNotify($data, $orderId);
            } else {
                // 商品订单
                return $this->handleProductNotify($data, $orderId);
            }
        } catch (\Exception $e) {
            Log::error('处理支付回调失败：' .  $e->getMessage().' | '.$e->getFile().' | '.$e->getLine());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * 处理商品订单支付回调
     * @param array $data 回调数据
     * @param string $orderId 订单号
     * @return array
     */
    private function handleProductNotify($data, $orderId)
    {
        try {
            // 获取订单信息
            $order = ProductOrder::where('order_number', $orderId)
                ->where('status', OrderStatus::PENDING)
                ->find();
                
            if (!$order) {
                return [
                    'success' => false,
                    'message' => '订单不存在或状态错误'
                ];
            }

            // 获取渠道信息
            $channel = PaymentChannel::where('id', $order['channel_id'])
                ->find();
                
            if (!$channel) {
                return [
                    'success' => false,
                    'message' => '支付渠道不存在或已禁用'
                ];
            }

            // 获取支付渠道实例
            $paymentChannel = PaymentFactory::getChannel($channel['abbr']);
            if (!$paymentChannel) {
                write_log('payment_notify', '支付渠道实例获取失败', json_encode([
                    'channel_abbr' => $channel['abbr']
                ], JSON_UNESCAPED_UNICODE));
                return [
                    'success' => false,
                    'message' => '支付渠道实例获取失败'
                ];
            }

            // 获取渠道配置（根据渠道ID获取，而不是重新选择）
            $channelInfo = $this->getChannelConfigById($order['channel_id'], $order['payment_method']);
            if (!$channelInfo) {
                return [
                    'success' => false,
                    'message' => '支付通道配置不存在'
                ];
            }
            
            // 使用数据库中的配置信息
            $config = [
                'merchant_id' => $channelInfo['merchant_id'],
                'merchant_key' => $channelInfo['merchant_key'],
                'create_order_url' => $channelInfo['create_order_url'],
                'query_order_url' => $channelInfo['query_order_url'],
                'balance_query_url' => $channelInfo['balance_query_url'],
                'notify_url' => $channelInfo['notify_url'],
                'return_url' => $channelInfo['return_url'],
                'product_code' => $channelInfo['product_code']
            ];
            
            // 调用渠道处理回调，使用数据库配置
            $result = $paymentChannel->handleNotify($data, $config);
            
            if (!$result['success']) {
                write_log('payment_notify', '渠道回调处理失败', json_encode([
                    'channel' => $channel['abbr'],
                    'result' => $result
                ], JSON_UNESCAPED_UNICODE));
                return [
                    'success' => false,
                    'message' => $result['message']
                ];
            }

            // 验证金额
            if (bccomp($order['total_price'], $result['data']['amount'], 2) != 0) {
                write_log('payment_notify', '支付金额不匹配', json_encode([
                    'order_amount' => $order['total_price'],
                    'pay_amount' => $result['data']['amount']
                ], JSON_UNESCAPED_UNICODE));
                return [
                    'success' => false,
                    'message' => '支付金额不匹配'
                ];
            }

            return [
                'success' => true,
                'data' => $result['data']
            ];
        } catch (\Exception $e) {
            Log::error('处理商品订单支付回调失败：' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * 处理充值订单支付回调
     * @param array $data 回调数据
     * @param string $orderId 订单号
     * @return array
     */
    private function handleRechargeNotify($data, $orderId)
    {
        try {
            // 获取充值订单信息
            $order = RechargeOrder::where('order_no', $orderId)
                ->where('status', 0) // 待支付
                ->find();
                
            if (!$order) {
                return [
                    'success' => false,
                    'message' => '充值订单不存在或状态错误'
                ];
            }

            // 获取渠道信息
            $channel = PaymentChannel::where('id', $order['channel_id'])
                ->find();
                
            if (!$channel) {
                return [
                    'success' => false,
                    'message' => '支付渠道不存在或已禁用'
                ];
            }

            // 获取支付渠道实例
            $paymentChannel = PaymentFactory::getChannel($channel['abbr']);
            if (!$paymentChannel) {
                write_log('payment_notify', '支付渠道实例获取失败', json_encode([
                    'channel_abbr' => $channel['abbr']
                ], JSON_UNESCAPED_UNICODE));
                return [
                    'success' => false,
                    'message' => '支付渠道实例获取失败'
                ];
            }

            // 获取渠道配置（根据渠道ID获取，而不是重新选择）
            $channelInfo = $this->getChannelConfigById($order['channel_id'], $order['payment_method']);
            if (!$channelInfo) {
                return [
                    'success' => false,
                    'message' => '支付通道配置不存在'
                ];
            }
            
            // 使用数据库中的配置信息
            $config = [
                'merchant_id' => $channelInfo['merchant_id'],
                'merchant_key' => $channelInfo['merchant_key'],
                'create_order_url' => $channelInfo['create_order_url'],
                'query_order_url' => $channelInfo['query_order_url'],
                'balance_query_url' => $channelInfo['balance_query_url'],
                'notify_url' => $channelInfo['notify_url'],
                'return_url' => $channelInfo['return_url'],
                'product_code' => $channelInfo['product_code']
            ];
            
            // 调用渠道处理回调，使用数据库配置
            $result = $paymentChannel->handleNotify($data, $config);
            
            if (!$result['success']) {
                write_log('payment_notify', '渠道回调处理失败', json_encode([
                    'channel' => $channel['abbr'],
                    'result' => $result
                ], JSON_UNESCAPED_UNICODE));
                return [
                    'success' => false,
                    'message' => $result['message']
                ];
            }

            // 验证金额
            if (bccomp($order['recharge_amount'], $result['data']['amount'], 2) != 0) {
                write_log('payment_notify', '支付金额不匹配', json_encode([
                    'order_amount' => $order['recharge_amount'],
                    'pay_amount' => $result['data']['amount']
                ], JSON_UNESCAPED_UNICODE));
                return [
                    'success' => false,
                    'message' => '支付金额不匹配'
                ];
            }

            // 返回验证成功的数据，事务处理由Payment.php负责
            return [
                'success' => true,
                'data' => [
                    'order_number' => $order['order_no'],
                    'trade_no' => $result['data']['trade_no'],
                    'amount' => $result['data']['amount']
                ]
            ];
        } catch (\Exception $e) {
            Log::error('处理充值订单支付回调失败：' . $e->getMessage().' | '.$e->getFile().' | '.$e->getLine());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * 根据渠道ID和支付方式获取渠道配置
     * @param int $channelId 渠道ID
     * @param string $paymentMethod 支付方式
     * @return array|null
     */
    private function getChannelConfigById($channelId, $paymentMethod)
    {
        try {
            // 直接从数据库获取渠道和费率配置
            $channel = PaymentChannel::where('id', $channelId)->find();
            if (!$channel) {
                return null;
            }
            
            $rate = \app\admin\model\PaymentChannelRate::where('channel_id', $channelId)
                ->where('payment_method', $paymentMethod)
                ->where('status', 1)
                ->find();
                
            if (!$rate) {
                return null;
            }
            
            return [
                'channel_id' => $channel->id,
                'channel_name' => $channel->name,
                'abbr' => $channel->abbr,
                'merchant_id' => $channel->merchant_id,
                'merchant_key' => $channel->merchant_key,
                'create_order_url' => $channel->create_order_url,
                'query_order_url' => $channel->query_order_url,
                'balance_query_url' => $channel->balance_query_url,
                'notify_url' => $channel->notify_url,
                'return_url' => $channel->return_url,
                'fee_rate' => $rate->fee_rate,
                'min_amount' => $rate->min_amount,
                'max_amount' => $rate->max_amount,
                'product_code' => $rate->product_code
            ];
        } catch (\Exception $e) {
            Log::error('获取渠道配置失败：' . $e->getMessage());
            return null;
        }
    }

    /**
     * 生成签名
     * @param array $params 参数
     * @param string $secret 密钥
     * @return string
     */
    private function generateSign($params, $secret)
    {
        ksort($params);
        $string = '';
        foreach ($params as $key => $value) {
            if ($key != 'sign' && $value !== '') {
                $string .= $key . '=' . $value . '&';
            }
        }
        $string .= 'key=' . $secret;
        write_log('payment_request', 'md5', $string);
        return strtoupper(md5($string));
    }

    /**
     * 验证签名
     * @param array $data 数据
     * @return bool
     */
    private function verifySign($data)
    {
        $sign = $data['sign'];
        unset($data['sign']);
        return $sign === $this->generateSign($data, $this->channels['alipay']['app_secret']);
    }

    /**
     * 发送HTTP请求
     * @param string $url 请求地址
     * @param array $params 请求参数
     * @return array
     */
    private function sendRequest($url, $params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    /**
     * 获取客户端IP
     * @return string
     */
    private function getClientIp()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) {
                unset($arr[$pos]);
            }
            $ip = trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = '0.0.0.0';
        }
        return $ip;
    }
} 