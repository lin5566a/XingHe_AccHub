<?php
namespace app\admin\model;

use think\Model;
use think\facade\Log;

class User extends Model
{
    protected $name = 'users';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = false;

    // 设置字段类型
    protected $type = [
        'balance' => 'float',
        'total_recharge' => 'float',
        'total_spent' => 'float',
        'status' => 'integer',
        'membership_level' => 'integer',
        'custom_discount' => 'float', // 百分比，允许两位小数
        'created_at' => 'datetime'
    ];
    
    // 状态常量
    const STATUS_NORMAL = 1;
    const STATUS_DISABLED = 2;
    
    // 状态获取器
    public function getStatusTextAttr($value, $data)
    {
        $status = [
            self::STATUS_NORMAL => '正常',
            self::STATUS_DISABLED => '禁用'
        ];
        return isset($status[$data['status']]) ? $status[$data['status']] : '';
    }
    
    /**
     * 注册
     */
    public function register($data)
    {
        // 创建用户
        $user = $this->create([
            'email' => $data['email'],
            'password' => md5($data['password']),
            'balance' => 0,
            'total_recharge' => 0,
            'total_spent' => 0,
            'status' => 1
        ]);

        if ($user) {
            return ['code' => 1, 'msg' => '注册成功', 'data' => $user];
        }
        
        return ['code' => 0, 'msg' => '注册失败'];
    }

    // 会员等级关联
    public function memberLevel()
    {
        return $this->belongsTo('MemberLevel', 'membership_level');
    }

    public function toArray()
    {
        $data = parent::toArray();
        // 去除member_level字段，保留memberLevel
        if (isset($data['member_level'])) {
            unset($data['member_level']);
        }
        return $data;
    }

    /**
     * 累计充值变动后自动调整VIP等级（超级会员不受影响）
     */
    public static function autoUpdateVipLevel($userId)
    {
        write_log('vip_level', '开始自动调整VIP等级', "用户ID: {$userId}");
        
        // 使用正确的ThinkPHP 5.0语法查询用户
        $user = self::where('id', $userId)->find();
        if (!$user) {
            write_log('vip_level', '用户不存在', "用户ID: {$userId}");
            return;
        }
        
        write_log('vip_level', '用户信息', "用户ID: {$userId}, 当前等级: {$user['membership_level']}, 累计充值: {$user['total_recharge']}");
        
        // 如果已经是超级会员，直接返回
        if ($user['membership_level'] == \app\admin\model\MemberLevel::SUPER_LEVEL_ID) {
            write_log('vip_level', '用户已是超级会员', "用户ID: {$userId}, 无需调整");
            return;
        }
        
        // 获取所有非超级会员等级，按upgrade_amount升序排列
        $levels = \app\admin\model\MemberLevel::where('id', '<>', \app\admin\model\MemberLevel::SUPER_LEVEL_ID)
            ->order('id asc')->select();
            
        write_log('vip_level', '获取会员等级', "等级数量: " . count($levels));
        
        $newLevel = count($levels) > 0 ? $levels[0] : null;
        foreach ($levels as $level) {
            write_log('vip_level', '检查等级', "等级ID: {$level['id']}, 升级条件: {$level['upgrade_amount']}");
            if ($user['total_recharge'] >= $level['upgrade_amount']) {
                $newLevel = $level;
            } else {
                break; // 找到第一个不满足条件的等级就停止
            }
        }
        
        if ($newLevel && $user['membership_level'] != $newLevel['id']) {
            $oldLevel = $user['membership_level'];
            // 使用正确的更新方式
            $result = self::where('id', $userId)->update(['membership_level' => $newLevel['id']]);
            
            if ($result) {
                write_log('vip_level', 'VIP等级调整成功', "用户ID: {$userId}, 等级变更: {$oldLevel} -> {$newLevel['id']}");
            } else {
                write_log('vip_level', 'VIP等级调整失败', "用户ID: {$userId}, 等级变更: {$oldLevel} -> {$newLevel['id']}");
            }
        } else {
            write_log('vip_level', '无需调整VIP等级', "用户ID: {$userId}, 当前等级: {$user['membership_level']}");
        }
    }
} 