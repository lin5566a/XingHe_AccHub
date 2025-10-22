<?php
namespace app\common\service\payment;

use think\Log;

class SDPayChannel implements PaymentChannelInterface
{
    public function getConfig()
    {
        return [
            'alipay' => [
                'name' => '支付宝',
                'api_url' => 'https://api.sdpay.org/api/pay',
                'mid' => '0430759607',
                'channel_code' => '0000',
                'app_secret' => 'ff6dfbb7a918e458bcf9f7acf3a1124e'
            ],
            'wechat' => [
                'name' => '微信支付',
                'api_url' => 'https://api.sdpay.org/api/pay',
                'mid' => '0430759607',
                'channel_code' => '0000',
                'app_secret' => 'ff6dfbb7a918e458bcf9f7acf3a1124e'
            ]
        ];
    }

    public function createPayment($order, $config)
    {
        try {
            $channel = $order['payment_method'];
            if (!isset($config[$channel])) {
                throw new \Exception('不支持的支付渠道');
            }

            $channelConfig = $config[$channel];
            
            // 构建请求参数
            $params = [
                'merchant_no' => $channelConfig['mid'],
                'order_sn' => is_array($order) ? $order['order_number'] : $order->order_number,
                'order_amount' => is_array($order) ? $order['total_price'] : $order->total_price,
                'code' => $channelConfig['channel_code'],
                'notify_url' => url('v1/payment/notify', '', true, true),
            ];

            // 生成签名
            $params['sign'] = $this->generateSign($params, $channelConfig['app_secret']);

            // 发送请求
            write_log('payment_request', '支付请求开始', json_encode([
                'url' => $channelConfig['api_url'],
                'params' => $params
            ], JSON_UNESCAPED_UNICODE));
            
            $response = $this->sendRequest($channelConfig['api_url'], $params);
            
            write_log('payment_request', '支付请求完成', json_encode([
                'url' => $channelConfig['api_url'],
                'response' => $response
            ], JSON_UNESCAPED_UNICODE));
            
            if ($response['code'] != 0) {
                throw new \Exception($response['msg']);
            }

            return [
                'success' => true,
                'data' => [
                    'pay_url' => $response['data']['pay_url'],
                    'order_number' => is_array($order) ? $order['order_number'] : $order->order_number
                ]
            ];

        } catch (\Exception $e) {
            Log::error('创建支付订单失败：' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function queryPayment($orderNo, $config)
    {
        try {
            $params = [
                'app_id' => $config['app_id'],
                'order_no' => $orderNo,
                'timestamp' => time(),
                'sign_type' => 'MD5'
            ];

            $params['sign'] = $this->generateSign($params, $config['app_secret']);
            $response = $this->sendRequest($config['api_url'] . '/QueryPayOrder', $params);
            
            if ($response['code'] != 200) {
                throw new \Exception($response['message']);
            }

            return [
                'success' => true,
                'data' => [
                    'status' => $response['data']['status'],
                    'order_number' => $orderNo
                ]
            ];

        } catch (\Exception $e) {
            Log::error('查询支付订单失败：' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function handleNotify($data, $config)
    {
        try {
            if (!$this->verifySign($data, $config['app_secret'])) {
                throw new \Exception('签名验证失败');
            }
            return true;
        } catch (\Exception $e) {
            Log::error('处理支付回调失败：' . $e->getMessage());
            return false;
        }
    }

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

    private function verifySign($data, $secret)
    {
        $sign = $data['sign'];
        unset($data['sign']);
        return $sign === $this->generateSign($data, $secret);
    }

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
} 