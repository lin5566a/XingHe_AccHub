<?php
namespace app\admin\validate;

use think\Validate;

class Manager extends Validate
{
    protected $rule = [
        'username' => 'require|length:4,32',
        'password' => 'require|length:6,32',
        'truename' => 'require|length:2,32',
        'status' => 'require|in:0,1'
    ];

    protected $message = [
        'username.require' => '用户名不能为空',
        'username.length' => '用户名长度为4-32个字符',
        'password.require' => '密码不能为空',
        'password.length' => '密码长度为6-32个字符',
        'truename.require' => '真实姓名不能为空',
        'truename.length' => '真实姓名长度为2-32个字符',
        'status.require' => '状态不能为空',
        'status.in' => '状态值不正确'
    ];

    protected $scene = [
        'edit' => ['truename', 'status'],
    ];
} 