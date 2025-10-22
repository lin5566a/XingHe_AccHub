<?php
namespace app\common\service\payment\channels;

use app\common\service\payment\PaymentChannelInterface;
use think\Log;
use app\common\utils\IpUtils;

class PmpPayChannel implements PaymentChannelInterface
{
    /**
     * 获取回调地址
     * @return array
     */
    private function getCallbackUrls()
    {
        return [
            'notify' => url('v1/payment/pmpPayNotify', '', true, true),
            'return' => url('v1/payment/pmpPayReturn', '', true, true)
        ];
    }
    
    /**
     * 获取渠道配置
     * @return array
     */
    public function getConfig()
    {
        // 从数据库获取配置，这里返回空数组，实际配置由PaymentService传入
        return [];
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
            
            // 构建请求参数
            $params = [
                'merchant_no' => $config['merchant_id'],
                'order_sn' => $orderData['order_number'],
                'order_amount' => (string)$orderData['total_price'],
                'notify_url' => $callbackUrls['notify'],
                'code' => $config['product_code'],
            ];

            // 生成签名
            $params['sign'] = $this->generateSign($params, $config['merchant_key']);

            // 记录请求开始日志
            write_log('payment_request', 'PmpPay支付请求开始', json_encode([
                'url' => $config['create_order_url'],
                'params' => $params
            ], JSON_UNESCAPED_UNICODE));
            
            // 发送请求
            $response = $this->sendRequest($config['create_order_url'], $params);
            
            // 记录请求完成日志
            write_log('payment_request', 'PmpPay支付请求完成', json_encode([
                'url' => $config['create_order_url'],
                'response' => $response
            ], JSON_UNESCAPED_UNICODE));
            
            if (!$response['status'] || $response['code'] != 0) {
                throw new \Exception(isset($response['msg']) ? $response['msg'] : '支付请求失败');
            }

            return [
                'success' => true,
                'data' => [
                    'pay_url' => $response['data']['pay_url'],
                    'order_number' => $orderData['order_number'],
                    'trade_num' => isset($response['data']['trade_num']) ? $response['data']['trade_num'] : ''
                ]
            ];

        } catch (\Exception $e) {
            Log::error('PmpPay支付创建失败：' . $e->getMessage());
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
            // 需要从订单中获取渠道订单号，这里暂时使用订单号
            $params = [
                'trade_num' => $orderNo, // 实际应该从订单中获取渠道订单号
                'order_sn' => $orderNo,
                'merchant_no' => $config['merchant_id']
            ];

            $params['sign'] = $this->generateSign($params, $config['merchant_key']);
            
            // 记录查询请求日志
            write_log('payment_request', 'PmpPay查询请求开始', json_encode([
                'url' => $config['query_order_url'],
                'params' => $params
            ], JSON_UNESCAPED_UNICODE));
            
            $response = $this->sendRequest($config['query_order_url'], $params);
            
            // 记录查询响应日志
            write_log('payment_request', 'PmpPay查询请求完成', json_encode([
                'url' => $config['query_order_url'],
                'response' => $response
            ], JSON_UNESCAPED_UNICODE));
            
            if (!$response['status'] || $response['code'] != 2000) {
                throw new \Exception(isset($response['msg']) ? $response['msg'] : '查询失败');
            }

            // 转换状态码
            $status = $this->convertStatus($response['data']['order_status']);

            return [
                'success' => true,
                'data' => [
                    'status' => $status,
                    'order_number' => $orderNo,
                    'trade_num' => isset($response['data']['trade_num']) ? $response['data']['trade_num'] : ''
                ]
            ];

        } catch (\Exception $e) {
            Log::error('PmpPay查询支付订单失败：' . $e->getMessage());
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
            write_log('payment_notify', 'PmpPay支付回调开始', json_encode([
                'data' => $data
            ], JSON_UNESCAPED_UNICODE));
            
            // 验证回调IP（如果配置了IP白名单）
            if (!empty($config['notify_ips'])) {
                $clientIp = IpUtils::getRealIp();
                if (!in_array($clientIp, $config['notify_ips'])) {
                    write_log('payment_notify', 'PmpPay支付回调IP验证失败', json_encode([
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
            $sign = isset($data['sign']) ? $data['sign'] : '';
            unset($data['sign']);
            
            $expectedSign = $this->generateSign($data, $config['merchant_key']);
            if ($sign !== $expectedSign) {
                write_log('payment_notify', 'PmpPay支付回调签名验证失败', json_encode([
                    'expected' => $expectedSign,
                    'actual' => $sign,
                    'data' => $data
                ], JSON_UNESCAPED_UNICODE));
                return [
                    'success' => false,
                    'message' => '签名验证失败'
                ];
            }
            
            // 验证订单状态
            $orderStatus = isset($data['order_status']) ? $data['order_status'] : '';
            if (!in_array($orderStatus, ['1', '8'])) { // 1:交易成功, 8:补单成功
                write_log('payment_notify', 'PmpPay支付回调状态不正确', json_encode([
                    'order_status' => $orderStatus,
                    'data' => $data
                ], JSON_UNESCAPED_UNICODE));
                return [
                    'success' => false,
                    'message' => '订单状态不正确'
                ];
            }
            
            // 转换状态
            $status = $this->convertStatus($orderStatus);
            
            // 记录回调成功日志
            write_log('payment_notify', 'PmpPay支付回调成功', json_encode([
                'order_sn' => $data['order_sn'],
                'status' => $status,
                'data' => $data
            ], JSON_UNESCAPED_UNICODE));
            
            return [
                'success' => true,
                'data' => [
                    'order_number' => $data['order_sn'],
                    'amount' => floatval($data['order_amount']),
                    'trade_no' => $data['trade_num'],
                    'status' => $status
                ]
            ];

        } catch (\Exception $e) {
            Log::error('PmpPay支付回调处理失败：' . $e->getMessage());
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
        // 1. 筛选：获取所有不为空的请求参数
        $filteredParams = [];
        foreach ($params as $key => $value) {
            if ($value !== '' && $value !== null) {
                $filteredParams[$key] = $value;
            }
        }
        
        // 2. 排序：按照键名ASCII码递增排序
        ksort($filteredParams);
        
        // 3. 拼接：参数=参数值&参数=参数值...
        $paramString = '';
        foreach ($filteredParams as $key => $value) {
            $paramString .= $key . '=' . $value . '&';
        }
        $paramString = rtrim($paramString, '&');
        
        // 4. 拼接密钥
        $paramString .= '&key=' . $secret;
        
        // 5. MD5签名并转大写
        return strtoupper(md5($paramString));
    }
    
    /**
     * 发送HTTP请求
     * @param string $url 请求URL
     * @param array $params 请求参数
     * @return array
     */
    private function sendRequest($url, $params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new \Exception('CURL错误：' . $error);
        }
        
        if ($httpCode !== 200) {
            throw new \Exception('HTTP请求失败，状态码：' . $httpCode);
        }
        
        $result = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('响应数据格式错误：' . $response);
        }
        
        return $result;
    }
    
    /**
     * 转换订单状态
     * @param string $channelStatus 渠道状态
     * @return string
     */
    private function convertStatus($channelStatus)
    {
        $statusMap = [
            '0' => 'pending',    // 订单创建
            '1' => 'success',    // 交易成功
            '2' => 'pending',    // 待付款
            '3' => 'processing', // 交易中
            '4' => 'failed',     // 交易失败
            '5' => 'dispute',    // 申诉中
            '6' => 'failed',     // 拉单失败
            '7' => 'cancelled',  // 交易撤销
            '8' => 'success'     // 补单成功
        ];
        
        return isset($statusMap[$channelStatus]) ? $statusMap[$channelStatus] : 'unknown';
    }
} 