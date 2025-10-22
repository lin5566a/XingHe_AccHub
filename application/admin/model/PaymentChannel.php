<?php
namespace app\admin\model;

use think\Model;

class PaymentChannel extends Model
{
    protected $name = 'payment_channels';
    
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
    
    // 设置时间字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    // 设置时间字段类型
    protected $type = [
        'create_time' => 'datetime',
        'update_time' => 'datetime'
    ];
    
    // 设置时间格式
    protected $dateFormat = 'Y-m-d H:i:s';
    
    // 关联费率表
    public function rates()
    {
        return $this->hasMany('PaymentChannelRate', 'channel_id');
    }
    
    // 获取支付方式文本
    public static function getPaymentMethodText($method)
    {
        $methods = [
            'wechat' => '微信支付',
            'alipay' => '支付宝',
            'usdt' => 'USDT'
        ];
        return isset($methods[$method]) ? $methods[$method] : $method;
    }
    
    // 获取所有支付方式
    public static function getPaymentMethods()
    {
        return [
            'wechat' => '微信支付',
            'alipay' => '支付宝',
            'usdt' => 'USDT'
        ];
    }
} 