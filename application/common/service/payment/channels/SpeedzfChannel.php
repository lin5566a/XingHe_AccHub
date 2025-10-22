<?php
namespace app\common\service\payment\channels;

use app\common\service\payment\PaymentChannelInterface;
use think\Log;

class SpeedzfChannel implements PaymentChannelInterface
{
    // 支付接口地址
    // const API_URL = 'https://zf.speedzf.com/mapi.php';
    const API_URL = 'https://apijh.chaowu.cc/mapi.php';
    const WX_API_URL = 'https://apijh.chaowu.cc/submit.php';
    
    // 渠道配置
    private $config = [
        'alipay' => [
            'pid' => '1019', // 替换为您的商户ID
            'key' => '1R91v9kRQRu5Fq55G54Hji15UggGk5Uq', // 替换为您的商户密钥
            'type' => 'alipay'
        ],
        'wechat' => [
            'pid' => '1019', // 替换为您的商户ID
            'key' => '1R91v9kRQRu5Fq55G54Hji15UggGk5Uq', // 替换为您的商户密钥
            'type' => 'wxpay'
        ]
    ];
    
    /**
     * 获取回调地址
     * @return array
     */
    private function getCallbackUrls()
    {
        return [
            'notify' => url('v1/payment/speedNotify', '', true, true),
            'return' => url('v1/payment/speedReturn', '', true, true)
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
            $orderData = is_array($order) ? $order : $order->toArray();
            $callbackUrls = $this->getCallbackUrls();
            
            // 根据订单号前缀判断订单类型，设置不同的跳转地址
            if (strpos($orderData['order_number'], 'R') === 0) {
                // 充值订单，跳转到充值页面
                $returnUrl = 'https://www.xinghai.shop/rechargePay?order_no=' . urlencode($orderData['order_number']);
            } else {
                // 商品订单，跳转到支付页面
                $returnUrl = 'https://www.xinghai.shop/payment?order_no=' . urlencode($orderData['order_number']);
            }
            
            // 判断支付方式，设置对应的type
            $paymentMethod = $orderData['payment_method'];
            $type = ($paymentMethod == 'wechat') ? 'wxpay' : 'alipay';
            
            // 构建请求参数
            $params = [
                'pid' => $config['merchant_id'],
                'type' => $type,
                'out_trade_no' => $orderData['order_number'],
                'notify_url' => $callbackUrls['notify'],
                'return_url' => $returnUrl,
                'name' => isset($orderData['product_name']) ? $orderData['product_name'] : '商品购买',
                'money' => number_format($orderData['total_price'], 2, '.', ''),
                'sign_type' => 'MD5'
            ];
            
            // 生成签名
            $params['sign'] = $this->generateSign($params, $config['merchant_key']);
            
            // 记录请求开始日志
            write_log('payment_request', 'Speedzf支付请求开始', json_encode([
                'url' => self::WX_API_URL,
                'params' => $params
            ], JSON_UNESCAPED_UNICODE));
            
            // 发送请求
            // $result = $this->sendRequest(self::WX_API_URL, $params);
            // 记录请求完成日志
            // write_log('payment_request', 'Speedzf支付请求完成', json_encode([
            //     'url' => self::WX_API_URL,
            //     'response' => $result
            // ], JSON_UNESCAPED_UNICODE));
            
            
            $fullUrl = self::WX_API_URL . '?' . http_build_query($params);
            
            // 记录请求完成日志
            write_log('payment_request', 'Speedzf支付请求完成', json_encode([
                'url' => self::WX_API_URL,
                'response' => $fullUrl
            ], JSON_UNESCAPED_UNICODE));
            
            return [
                    'success' => true,
                    'data' => [
                        'pay_url' => $fullUrl,
                        'order_number' => $orderData['order_number']
                    ]
                ];
            
            if ($result['code'] == 1) {
                if(isset($result['payurl'])) $payUrl = $result['payurl'];
                if(isset($result['qrcode'])) $payUrl = $result['qrcode'];
                return [
                    'success' => true,
                    'data' => [
                        'pay_url' => $payUrl,
                        'order_number' => $orderData['order_number']
                    ]
                ];
            } else {
                Log::error('Speedzf支付创建失败：' . $result['msg']);
                return [
                    'success' => false,
                    'message' => $result['msg'] ?: '创建支付失败'
                ];
            }
        } catch (\Exception $e) {
            Log::error('Speedzf支付创建异常：' . $e->getMessage());
            return [
                'success' => false,
                'message' => '创建支付异常【' . $e->getMessage(). '】'
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
        // Speedzf 不支持主动查询，返回订单状态未知
        return [
            'success' => true,
            'data' => [
                'status' => 'UNKNOWN',
                'order_number' => $orderNo
            ]
        ];
    }
    
    /**
     * 处理支付回调
     * @param array $data 回调数据
     * @param array $config 渠道配置
     * @return array
     */
    public function handleNotify($data, $channelConfig)
    {
        try {
            // dump($config);
            // $channel = $order['payment_method'];
            // if (!isset($config[$channel])) {
            //     throw new \Exception('不支持的支付渠道');
            // }
            
            // $channelConfig = $config[$channel];
            
            // 记录回调开始日志
            write_log('payment_notify', 'Speedzf支付回调开始', json_encode([
                'data' => $data
            ], JSON_UNESCAPED_UNICODE));
            
            // 验证签名
            $sign = $data['sign'];
            unset($data['sign'], $data['sign_type']);
            
            if ($sign !== $this->generateSign($data, $channelConfig['merchant_key'])) {
                write_log('payment_notify', 'Speedzf支付回调签名验证失败', json_encode([
                    'data' => $data,
                    'sign' => $sign,
                    'calculated_sign' => $this->generateSign($data, $channelConfig['merchant_key'])
                ], JSON_UNESCAPED_UNICODE));
                
                return [
                    'success' => false,
                    'message' => '签名验证失败'
                ];
            }
            
            // 验证支付状态
            if ($data['trade_status'] !== 'TRADE_SUCCESS') {
                write_log('payment_notify', 'Speedzf支付回调状态未完成', json_encode([
                    'data' => $data
                ], JSON_UNESCAPED_UNICODE));
                
                return [
                    'success' => false,
                    'message' => '支付未完成'
                ];
            }
            
            // 记录回调处理成功日志
            write_log('payment_notify', 'Speedzf支付回调处理成功', json_encode([
                'data' => $data
            ], JSON_UNESCAPED_UNICODE));
            
            return [
                'success' => true,
                'data' => [
                    'order_number' => $data['out_trade_no'],
                    'amount' => $data['money'],
                    'trade_no' => $data['trade_no']
                ]
            ];
        } catch (\Exception $e) {
            write_log('payment_notify', 'Speedzf支付回调处理异常', json_encode([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], JSON_UNESCAPED_UNICODE));
            
            Log::error('Speedzf支付回调处理异常：' . $e->getMessage());
            return [
                'success' => false,
                'message' => '回调处理异常:'.$e->getMessage().' | '.$e->getLine()
            ];
        }
    }
    
    /**
     * 生成签名
     * @param array $params 参数
     * @param string $key 密钥
     * @return string
     */
    private function generateSign($params, $key)
    {
        // 移除空值和签名相关参数
        foreach ($params as $k => $v) {
            if ($v === '' || $k === 'sign' || $k === 'sign_type') {
                unset($params[$k]);
            }
        }
        
        // 按参数名ASCII码从小到大排序
        ksort($params);
        
        // 拼接参数
        $string = '';
        foreach ($params as $k => $v) {
            $string .= $k . '=' . $v . '&';
        }
        $string = rtrim($string, '&');
        
        // 拼接密钥并MD5加密
        return md5($string . $key);
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
    
    private function sendFormRequest($url, $params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
    
        // multipart/form-data 自动处理
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    
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
