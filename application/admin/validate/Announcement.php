<?php
namespace app\admin\validate;

use think\Validate;

class Announcement extends Validate
{
    protected $rule = [
        'title' => 'require|max:100',
        'content' => 'require',
        'status' => 'require|in:0,1',
        'sort' => 'number'
    ];
    
    protected $message = [
        'title.require' => '公告标题不能为空',
        'title.max' => '公告标题最多100个字符',
        'content.require' => '公告内容不能为空',
        'status.require' => '状态不能为空',
        'status.in' => '状态值不正确',
        'sort.number' => '排序值必须为数字'
    ];
} 