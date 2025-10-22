<?php
namespace app\admin\model;

use think\Model;

class ProductDescriptionTemplate extends Model
{
    protected $name = 'product_description_templates';
    protected $pk = 'id';
    
    // 自动完成
    protected $auto = ['updated_at'];
    
    // 允许写入的字段
    protected $allowField = [
        'template_name',
        'category_id',
        'content'
    ];
    
    protected function setUpdatedAtAttr()
    {
        return date('Y-m-d H:i:s');
    }
    
    // 关联商品分类
    public function category()
    {
        return $this->belongsTo('ProductCategory', 'category_id', 'id');
    }
    
    // 获取分类名称
    public function getCategoryNameAttr($value, $data)
    {
        $category = $this->category()->find();
        return $category ? $category['name'] : '';
    }
} 