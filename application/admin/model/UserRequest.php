<?php
namespace app\admin\model;

use think\Model;

class UserRequest extends Model
{
    protected $name = 'user_requests';
    
    // 自动写入时间戳
    protected $autoWriteTimestamp = false;
    
    // 状态常量
    const STATUS_UNSOLVED = 0;  // 未解决
    const STATUS_SOLVED = 1;    // 已解决

    // 状态获取器
    public function getStatusTextAttr($value, $data)
    {
        $status = array(
            self::STATUS_UNSOLVED => '未解决',
            self::STATUS_SOLVED => '已解决'
        );
        return isset($status[$data['status']]) ? $status[$data['status']] : '';
    }
    
    /**
     * 检查IP今日提交次数
     * @param string $ip IP地址
     * @param int $limit 限制次数，默认10次
     * @return bool 是否超过限制
     */
    public static function checkIpLimit($ip, $limit = 10)
    {
        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd = date('Y-m-d 23:59:59');
        
        $count = self::where('ip_address', $ip)
            ->where('sent_at', 'between', [$todayStart, $todayEnd])
            ->count();
            
        return $count >= $limit;
    }
} 