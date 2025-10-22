<?php
namespace app\common\constants;

class PaymentStatus
{
    // 支付状态常量
    const PENDING = 0;    // 待支付
    const PAID = 1;       // 已支付
    const FAILED = 2;     // 支付失败
    const EXPIRED = 3;    // 已过期

    // 获取支付状态文本
    public static function getStatusText($status)
    {
        $statusMap = [
            self::PENDING => '待支付',
            self::PAID => '已支付',
            self::FAILED => '支付失败',
            self::EXPIRED => '已过期'
        ];
        
        return isset($statusMap[$status]) ? $statusMap[$status] : '未知状态';
    }
} 