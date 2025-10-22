<?php
namespace app\admin\model;

use think\Model;

class ProductCategory extends Model
{
    protected $name = 'product_categories';
    
    // 状态常量
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 2;
    
    // 状态获取器
    public function getStatusTextAttr($value, $data)
    {
        $status = [
            self::STATUS_ENABLED => '启用',
            self::STATUS_DISABLED => '禁用'
        ];
        return isset($status[$data['status']]) ? $status[$data['status']] : '';
    }
} 