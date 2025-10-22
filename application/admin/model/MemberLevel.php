<?php
namespace app\admin\model;

use think\Model;

class MemberLevel extends Model
{
    // 绑定表名
    protected $table = 'epay_member_level';
    // 主键
    protected $pk = 'id';
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    // 类型转换
    protected $type = [
        // 'discount' => 'float', // 百分比，允许两位小数
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // 最高等级ID（超级会员）
    const SUPER_LEVEL_ID = 5;  // 假设超级会员的ID是3，请根据实际情况修改
} 