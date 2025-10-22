<?php
namespace app\admin\validate;

use think\Validate;

class PaymentConfig extends Validate
{
    protected $rule = [
        'usdt_fee' => 'require|float|min:0|max:100',
        'wechat_fee' => 'require|float|min:0|max:100',
        'alipay_fee' => 'require|float|min:0|max:100'
    ];
    
    protected $message = [
        'usdt_fee.require' => 'USDT手续费不能为空',
        'usdt_fee.float' => 'USDT手续费必须为数字',
        'usdt_fee.min' => 'USDT手续费不能小于0',
        'usdt_fee.max' => 'USDT手续费不能大于100',
        
        'wechat_fee.require' => '微信手续费不能为空',
        'wechat_fee.float' => '微信手续费必须为数字',
        'wechat_fee.min' => '微信手续费不能小于0',
        'wechat_fee.max' => '微信手续费不能大于100',
        
        'alipay_fee.require' => '支付宝手续费不能为空',
        'alipay_fee.float' => '支付宝手续费必须为数字',
        'alipay_fee.min' => '支付宝手续费不能小于0',
        'alipay_fee.max' => '支付宝手续费不能大于100'
    ];
} 