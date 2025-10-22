<?php
namespace app\admin\model;

use think\Model;

class PaymentChannelRate extends Model
{
    protected $name = 'payment_channel_rates';
    
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
    
    // 关联通道表
    public function channel()
    {
        return $this->belongsTo('PaymentChannel', 'channel_id');
    }
    
    // 获取状态文本
    public static function getStatusText($status)
    {
        return $status == 1 ? '启用' : '禁用';
    }
    
    /**
     * 获取产品编号文本
     * @param string $productCode 产品编号
     * @return string
     */
    public static function getProductCodeText($productCode)
    {
        if (empty($productCode)) {
            return '未设置';
        }
        return $productCode;
    }
} 