<?php
namespace app\common\constants;

class ProductStatus
{
    const ACTIVE = 1;     // 上架
    const INACTIVE = 0;   // 下架
    const DELETED = -1;   // 已删除

    /**
     * 获取状态文本
     * @param int $status
     * @return string
     */
    public static function getStatusText($status)
    {
        $statusMap = [
            self::ACTIVE => '上架',
            self::INACTIVE => '下架',
            self::DELETED => '已删除'
        ];
        
        return isset($statusMap[$status]) ? $statusMap[$status] : '未知状态';
    }
} 