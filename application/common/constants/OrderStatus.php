<?php
namespace app\common\constants;

class OrderStatus
{
    // 订单状态常量
    const PENDING = 1;    // 待支付
    const PAID = 2;       // 已支付
    const COMPLETED = 3;  // 已完成
    const CANCELLED = 4;  // 已取消
    const DELIVERY_FAILED = 5;    // 发货失败
    const REFUNDED = 6; // 已退款

    // 获取订单状态文本
    public static function getStatusText($status)
    {
        $statusMap = [
            self::PENDING => '待支付',
            self::PAID => '已支付',
            self::COMPLETED => '已完成',
            self::CANCELLED => '已取消',
            self::DELIVERY_FAILED => '发货失败',
            self::REFUNDED => '已退款'
        ];
        
        return isset($statusMap[$status]) ? $statusMap[$status] : '未知状态';
    }
} 