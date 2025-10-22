<?php
namespace app\common\service\payment\channels;

use app\common\service\payment\PaymentChannelInterface;
use think\Log;
use app\common\utils\IpUtils;

class SDPayChannel implements PaymentChannelInterface
{
    // 支付接口地址
    const API_URL = 'https://api.sdpay.org/api/pay';
    
    // 渠道配置
    private $config = [
        'alipay' => [
            'name' => '支付宝',
            'api_url' => self::API_URL,
            'mid' => '0430759607',
            'channel_code' => '0000',
            'app_secret' => 'ff6dfbb7a918e458bcf9f7acf3a1124e',
            'notify_ips' => ['34.92.13.192', '34.92.13.193'] // 支持多个回调IP
        ],
        'wechat' => [
            'name' => '微信支付',
            'api_url' => self::API_URL,
            'mid' => '0430759607',
            'channel_code' => '0000',
            'app_secret' => 'ff6dfbb7a918e458bcf9f7acf3a1124e',
            'notify_ips' => ['34.92.13.192', '34.92.13.193'] // 支持多个回调IP
        ]
    ];
    
    /**
     * 获取回调地址
     * @return array
     */
    private function getCallbackUrls()
    {
        return [
            'notify' => url('payment/notify', '', true, true),
            'return' => url('payment/result', '', true, true)
        ];
    }
    
    /**
     * 获取渠道配置
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }
    
    /**
     * 创建支付
     * @param array|object $order 订单信息
     * @param array $config 渠道配置
     * @return array
     */
    public function createPayment($order, $config)
    {
        try {
            $channel = $order['payment_method'];
            if (!isset($config[$channel])) {
                throw new \Exception('不支持的支付渠道');
            }

            $channelConfig = $config[$channel];
            $orderData = is_array($order) ? $order : $order->toArray();
            $callbackUrls = $this->getCallbackUrls();
            
            // 构建请求参数
            $params = [
                'merchant_no' => $channelConfig['mid'],
                'order_sn' => $orderData['order_number'],
                'order_amount' => $orderData['total_price'],
                'code' => $channelConfig['channel_code'],
                'notify_url' => $callbackUrls['notify']
            ];

            // 生成签名
            $params['sign'] = $this->generateSign($params, $channelConfig['app_secret']);

            // 记录请求开始日志
            write_log('payment_request', 'SDPay支付请求开始', json_encode([
                'url' => $channelConfig['api_url'],
                'params' => $params
            ], JSON_UNESCAPED_UNICODE));
            
            // 发送请求
            $response = $this->sendRequest($channelConfig['api_url'], $params);
            
            // 记录请求完成日志
            write_log('payment_request', 'SDPay支付请求完成', json_encode([
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
                    'order_number' => $orderData['order_number']
                ]
            ];

        } catch (\Exception $e) {
            Log::error('SDPay支付创建失败：' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 查询支付
     * @param string $orderNo 订单号
     * @param array $config 渠道配置
     * @return array
     */
    public function queryPayment($orderNo, $config)
    {
        try {
            $params = [
                'app_id' => $config['mid'],
                'order_no' => $orderNo,
                'timestamp' => time(),
                'sign_type' => 'MD5'
            ];

            $params['sign'] = $this->generateSign($params, $config['app_secret']);
            
            // 记录查询请求日志
            write_log('payment_request', 'SDPay查询请求开始', json_encode([
                'url' => self::API_URL . '/QueryPayOrder',
                'params' => $params
            ], JSON_UNESCAPED_UNICODE));
            
            $response = $this->sendRequest(self::API_URL . '/QueryPayOrder', $params);
            
            // 记录查询响应日志
            write_log('payment_request', 'SDPay查询请求完成', json_encode([
                'url' => self::API_URL . '/QueryPayOrder',
                'response' => $response
            ], JSON_UNESCAPED_UNICODE));
            
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
            Log::error('SDPay查询支付订单失败：' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 处理支付回调
     * @param array $data 回调数据
     * @param array $config 渠道配置
     * @return array
     */
    public function handleNotify($data, $config)
    {
        try {
            // 记录回调开始日志
            write_log('payment_notify', 'SDPay支付回调开始', json_encode([
                'data' => $data
            ], JSON_UNESCAPED_UNICODE));
            
            // 验证回调IP
            if (!empty($config['notify_ips'])) {
                $clientIp = IpUtils::getRealIp();
                if (!in_array($clientIp, $config['notify_ips'])) {
                    write_log('payment_notify', 'SDPay支付回调IP验证失败', json_encode([
                        'expected' => $config['notify_ips'],
                        'actual' => $clientIp
                    ], JSON_UNESCAPED_UNICODE));
                    return [
                        'success' => false,
                        'message' => '回调IP验证失败'
                    ];
                }
            }

            // 验证签名
            if (!$this->verifySign($data, $config['app_secret'])) {
                write_log('payment_notify', 'SDPay支付回调签名验证失败', json_encode([
                    'data' => $data,
                    'sign' => $data['sign'],
                    'calculated_sign' => $this->generateSign($data, $config['app_secret'])
                ], JSON_UNESCAPED_UNICODE));
                return [
                    'success' => false,
                    'message' => '签名验证失败'
                ];
            }

            // 验证订单状态
            if ($data['order_status'] != '8') {
                write_log('payment_notify', 'SDPay支付回调状态未完成', json_encode([
                    'data' => $data
                ], JSON_UNESCAPED_UNICODE));
                return [
                    'success' => false,
                    'message' => '订单状态不正确'
                ];
            }

            // 记录回调处理成功日志
            write_log('payment_notify', 'SDPay支付回调处理成功', json_encode([
                'data' => $data
            ], JSON_UNESCAPED_UNICODE));

            return [
                'success' => true,
                'data' => [
                    'order_number' => $data['order_sn'],
                    'amount' => $data['order_amount'],
                    'trade_no' => $data['trade_num'],
                    'status' => 'SUCCESS'
                ]
            ];
        } catch (\Exception $e) {
            write_log('payment_notify', 'SDPay支付回调处理异常', json_encode([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], JSON_UNESCAPED_UNICODE));
            
            Log::error('SDPay支付回调处理失败：' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
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
        write_log('payment_request', 'SDPay签名原文', $string);
        return strtoupper(md5($string));
    }
    
    /**
     * 验证签名
     * @param array $data 数据
     * @param string $secret 密钥
     * @return bool
     */
    private function verifySign($data, $secret)
    {
        $sign = $data['sign'];
        unset($data['sign']);
        return $sign === $this->generateSign($data, $secret);
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
} 