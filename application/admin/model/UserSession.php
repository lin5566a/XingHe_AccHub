<?php
namespace app\admin\model;

use think\Model;

class UserSession extends Model
{
    protected $table = 'epay_user_sessions';
    protected $pk = 'id';
    
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    
    // 指定时间字段类型
    protected $type = [
        'user_id' => 'integer',
        'login_time' => 'datetime',
        'last_activity' => 'datetime',
        'expire_time' => 'datetime',
        'is_active' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'max_sessions' => 'integer'
    ];
    
    // 设备类型常量
    const DEVICE_TYPE_WEB = 'web';
    const DEVICE_TYPE_MOBILE = 'mobile';
    const DEVICE_TYPE_DESKTOP = 'desktop';
    const DEVICE_TYPE_TABLET = 'tablet';
    const DEVICE_TYPE_UNKNOWN = 'unknown';
    
    /**
     * 创建新的用户会话
     * @param int $userId 用户ID
     * @param string $token 登录token
     * @param array $deviceInfo 设备信息
     * @param int $expireHours 过期小时数，默认2小时
     * @return UserSession|false
     */
    public static function createSession($userId, $token, $deviceInfo = [], $expireHours = 2)
    {
        $session = new self();
        $session->user_id = $userId;
        $session->token = substr($token, 0, 191); // 确保token长度不超过191字符
        $session->device_id = isset($deviceInfo['device_id']) ? $deviceInfo['device_id'] : null;
        $session->device_name = isset($deviceInfo['device_name']) ? $deviceInfo['device_name'] : null;
        $session->device_type = isset($deviceInfo['device_type']) ? $deviceInfo['device_type'] : self::DEVICE_TYPE_UNKNOWN;
        $session->ip_address = isset($deviceInfo['ip_address']) ? $deviceInfo['ip_address'] : request()->ip();
        $session->user_agent = isset($deviceInfo['user_agent']) ? $deviceInfo['user_agent'] : request()->header('user-agent', '');
        $session->login_time = date('Y-m-d H:i:s');
        $session->last_activity = date('Y-m-d H:i:s');
        $session->expire_time = date('Y-m-d H:i:s', strtotime("+{$expireHours} hours"));
        $session->is_active = 1;
        
        if ($session->save()) {
            return $session;
        }
        
        return false;
    }
    
    /**
     * 验证token是否有效
     * @param string $token
     * @return UserSession|null
     */
    public static function validateToken($token)
    {
        return self::where('token', $token)
            ->where('is_active', 1)
            ->where('expire_time', '>', date('Y-m-d H:i:s'))
            ->find();
    }
    
    /**
     * 更新最后活动时间
     * @param string $token
     * @return bool
     */
    public static function updateLastActivity($token)
    {
        return self::where('token', $token)
            ->where('is_active', 1)
            ->update(['last_activity' => date('Y-m-d H:i:s')]);
    }
    
    /**
     * 使会话失效
     * @param string $token
     * @return bool
     */
    public static function invalidateSession($token)
    {
        return self::where('token', $token)
            ->update(['is_active' => 0]);
    }
    
    /**
     * 使用户所有会话失效
     * @param int $userId
     * @return bool
     */
    public static function invalidateAllUserSessions($userId)
    {
        return self::where('user_id', $userId)
            ->where('is_active', 1)
            ->update(['is_active' => 0]);
    }
    
    /**
     * 获取用户的活跃会话列表
     * @param int $userId
     * @return array
     */
    public static function getUserActiveSessions($userId)
    {
        return self::where('user_id', $userId)
            ->where('is_active', 1)
            ->where('expire_time', '>', date('Y-m-d H:i:s'))
            ->order('last_activity', 'desc')
            ->select()
            ->toArray();
    }
    
    /**
     * 清理过期会话
     * @return int 清理的会话数量
     */
    public static function cleanExpiredSessions()
    {
        return self::where('expire_time', '<', date('Y-m-d H:i:s'))
            ->where('is_active', 1)
            ->update(['is_active' => 0]);
    }
    
    /**
     * 获取用户当前会话数量
     * @param int $userId
     * @return int
     */
    public static function getUserSessionCount($userId)
    {
        return self::where('user_id', $userId)
            ->where('is_active', 1)
            ->where('expire_time', '>', date('Y-m-d H:i:s'))
            ->count();
    }
    
    /**
     * 检查用户是否超过最大会话数
     * @param int $userId
     * @return bool true表示超过限制
     */
    public static function isExceedMaxSessions($userId)
    {
        // 获取用户的最大会话数设置
        $user = User::where('id', $userId)->find();
        $maxSessions = $user && $user->max_sessions ? $user->max_sessions : 5; // 默认5个
        
        $currentSessions = self::getUserSessionCount($userId);
        
        return $currentSessions >= $maxSessions;
    }
    
    /**
     * 自动清理用户最旧的会话
     * @param int $userId
     * @param int $maxSessions
     * @return bool
     */
    public static function cleanOldestSessions($userId, $maxSessions = 5)
    {
        $currentSessions = self::getUserSessionCount($userId);
        
        if ($currentSessions >= $maxSessions) {
            // 获取最旧的会话
            $oldestSessions = self::where('user_id', $userId)
                ->where('is_active', 1)
                ->where('expire_time', '>', date('Y-m-d H:i:s'))
                ->order('last_activity', 'asc')
                ->limit($currentSessions - $maxSessions + 1)
                ->select();
            
            foreach ($oldestSessions as $session) {
                $session->is_active = 0;
                $session->save();
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * 获取设备类型（从User-Agent分析）
     * @param string $userAgent
     * @return string
     */
    public static function getDeviceType($userAgent)
    {
        if (empty($userAgent)) {
            return self::DEVICE_TYPE_UNKNOWN;
        }
        
        $userAgent = strtolower($userAgent);
        
        if (strpos($userAgent, 'mobile') !== false || strpos($userAgent, 'android') !== false || strpos($userAgent, 'iphone') !== false) {
            return self::DEVICE_TYPE_MOBILE;
        }
        
        if (strpos($userAgent, 'tablet') !== false || strpos($userAgent, 'ipad') !== false) {
            return self::DEVICE_TYPE_TABLET;
        }
        
        if (strpos($userAgent, 'windows') !== false || strpos($userAgent, 'macintosh') !== false || strpos($userAgent, 'linux') !== false) {
            return self::DEVICE_TYPE_DESKTOP;
        }
        
        return self::DEVICE_TYPE_WEB;
    }
    
    /**
     * 获取设备名称（从User-Agent分析）
     * @param string $userAgent
     * @return string
     */
    public static function getDeviceName($userAgent)
    {
        if (empty($userAgent)) {
            return '未知设备';
        }
        
        // 提取浏览器信息
        if (preg_match('/Chrome\/([0-9\.]+)/', $userAgent, $matches)) {
            return 'Chrome ' . $matches[1];
        }
        
        if (preg_match('/Firefox\/([0-9\.]+)/', $userAgent, $matches)) {
            return 'Firefox ' . $matches[1];
        }
        
        if (preg_match('/Safari\/([0-9\.]+)/', $userAgent, $matches)) {
            return 'Safari ' . $matches[1];
        }
        
        if (preg_match('/Edge\/([0-9\.]+)/', $userAgent, $matches)) {
            return 'Edge ' . $matches[1];
        }
        
        // 移动设备
        if (preg_match('/iPhone OS ([0-9_]+)/', $userAgent, $matches)) {
            return 'iPhone iOS ' . str_replace('_', '.', $matches[1]);
        }
        
        if (preg_match('/Android ([0-9\.]+)/', $userAgent, $matches)) {
            return 'Android ' . $matches[1];
        }
        
        return '未知浏览器';
    }
    
    // 关联用户模型
    public function user()
    {
        return $this->belongsTo('User', 'user_id');
    }
}
