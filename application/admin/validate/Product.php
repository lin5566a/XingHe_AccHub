<?php
namespace app\admin\validate;

use think\Validate;

class Product extends Validate
{
    protected $rule = [
        'name' => 'require|max:100',
        'category_id' => 'require|number',
        'price' => 'require|float|egt:0',
        'image' => 'require',
        'status' => 'require|in:0,1',
        'delivery_method' => 'require|in:auto,manual',
        'description' => 'requireIf:template_id,0',
        'template_id' => 'number'
    ];
    
    protected $message = [
        'name.require' => '商品名称不能为空',
        'name.max' => '商品名称不能超过100个字符',
        'name.unique' => '商品名称已存在',
        'category_id.require' => '商品分类不能为空',
        'category_id.number' => '商品分类格式错误',
        'price.require' => '商品价格不能为空',
        'price.float' => '商品价格格式错误',
        'price.egt' => '商品价格必须大于或等于0',
        'image.require' => '商品图片不能为空',
        'status.require' => '商品状态不能为空',
        'status.in' => '商品状态格式错误',
        'delivery_method.require' => '发货方式不能为空',
        'delivery_method.in' => '发货方式格式错误',
        'description.requireIf' => '商品详情不能为空',
        'template_id.number' => '商品模板格式错误'
    ];
    
    protected $scene = [
        'add' => ['name' => 'require|max:255|unique:products', 'category_id', 'price', 'image', 'status', 'delivery_method', 'description', 'template_id'],
        'edit' => ['name' => 'require|max:255|unique:products,name^id', 'category_id', 'price', 'image', 'status', 'delivery_method', 'description', 'template_id']
    ];
} 