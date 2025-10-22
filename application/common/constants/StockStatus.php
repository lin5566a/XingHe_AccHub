<?php
namespace app\common\constants;

class StockStatus
{
    // 卡密状态常量
    const UNUSED = 0;     // 未使用
    const LOCKED = 1;     // 已锁定
    const SOLD = 2;       // 已售出
    const VOID = 3;       // 已作废

    /**
     * 获取状态文本
     * @param int $status
     * @return string
     */
    public static function getStatusText($status)
    {
        $statusMap = [
            self::UNUSED => '未使用',
            self::LOCKED => '已锁定',
            self::SOLD => '已售出',
            self::VOID => '已作废'
        ];
        
        return isset($statusMap[$status]) ? $statusMap[$status] : '未知状态';
    }
} 