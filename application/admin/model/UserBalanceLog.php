<?php
namespace app\admin\model;

use think\Model;

class UserBalanceLog extends Model
{
    protected $table = 'epay_user_balance_log';
    protected $pk = 'id';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = false;
    protected $type = [
        'amount' => 'float',
        'before_balance' => 'float',
        'after_balance' => 'float',
        'created_at' => 'datetime'
    ];
} 