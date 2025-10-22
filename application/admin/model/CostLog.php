<?php
namespace app\admin\model;

use think\Model;

class CostLog extends Model
{
    // 自动写入时间戳
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = false;

    // 成本类型常量
    const TYPE_BATCH = 1; // 批次成本
    const TYPE_MANUAL_DELIVERY = 2; // 人工发货
    const TYPE_MANUAL_INPUT = 3; // 人工录入成本
    const TYPE_BATCH_MODIFY = 4; // 批次成本修改
    const TYPE_MANUAL_DELIVERY_MODIFY = 5; // 手动发货成本修改
    const TYPE_EDIT_CARD_COST = 6; // 编辑卡密成本修改

    // 金额类型
    const AMOUNT_ADD = 1; // 增加
    const AMOUNT_SUB = 2; // 减少
} 