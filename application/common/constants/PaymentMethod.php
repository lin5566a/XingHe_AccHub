<?php
namespace app\common\constants;

class PaymentMethod
{
    // 支付方式常量
    const ALIPAY = 'alipay';    // 支付宝
    const WECHAT = 'wechat';    // 微信支付
    const USDT = 'usdt';        // USDT

    /**
     * 获取支付方式文本
     * @param string $method
     * @return string
     */
    public static function getMethodText($method)
    {
        $methodMap = [
            self::ALIPAY => '支付宝',
            self::WECHAT => '微信',
            self::USDT => 'USDT'
        ];
        
        return isset($methodMap[$method]) ? $methodMap[$method] : '未知支付方式';
    }
} 