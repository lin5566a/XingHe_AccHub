<?php
namespace app\admin\model;

use think\Model;

class RechargeOrder extends Model
{
    protected $table = 'epay_recharge_order';
    protected $pk = 'id';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = false;
    protected $type = [
        'recharge_amount' => 'float',
        'fee' => 'float',
        'arrive_amount' => 'float',
        'refund_amount' => 'float',
        'fee_rate' => 'float',
        'channel_id' => 'integer',
        'usdt_rate' => 'float',
        'usdt_fee' => 'float',
        'usdt_amount' => 'float',
        'created_at' => 'datetime',
        'finished_at' => 'datetime'
    ];

    // 获取状态文本
    public function getStatusTextAttr($value, $data)
    {
        $statusMap = [
            0 => '待支付',
            1 => '已完成',
            2 => '已退款',
            3 => '已取消'
        ];
        return isset($statusMap[$data['status']]) ? $statusMap[$data['status']] : '未知状态';
    }

    // 关联用户
    public function user()
    {
        return $this->belongsTo('User', 'user_id');
    }
} 