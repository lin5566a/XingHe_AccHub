<?php
namespace app\admin\model;

use think\Model;

class Documents extends Model
{
    protected $name = 'documents';
    
    // 关闭自动时间戳
    protected $autoWriteTimestamp = false;

    // 添加隐藏字段
    protected $hidden = [];

    // 状态常量
    const STATUS_UNPUBLISHED = 0;  // 未发布
    const STATUS_PUBLISHED = 1;     // 已发布

    // 状态获取器
    public function getStatusTextAttr($value, $data)
    {
        $status = [
            self::STATUS_UNPUBLISHED => '未发布',
            self::STATUS_PUBLISHED => '已发布'
        ];
        return isset($status[$data['status']]) ? $status[$data['status']] : '';
    }
} 