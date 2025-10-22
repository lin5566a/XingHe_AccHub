<?php
namespace app\admin\model;

use think\Model;

// 公告模型
class Announcement extends Model
{
    // 绑定表名
    protected $table = 'epay_announcement';
    // 主键
    protected $pk = 'id';
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    
    // 状态常量
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;
    
    // 状态文本
    public static $statusText = [
        self::STATUS_DISABLED => '禁用',
        self::STATUS_ENABLED => '启用'
    ];
    
    // 获取状态文本
    public function getStatusTextAttr($value, $data)
    {
        return isset(self::$statusText[$data['status']]) ? self::$statusText[$data['status']] : '未知';
    }
    
    // 生成token
    public function generateToken()
    {
        return md5(uniqid(mt_rand(), true));
    }
    
    // 启用时自动更新token
    public function onBeforeUpdate($data)
    {
        if (isset($data['status']) && $data['status'] == self::STATUS_ENABLED) {
            $data['token'] = $this->generateToken();
        }
        return $data;
    }
    
    // 新增时如果状态为启用，自动生成token
    public function onBeforeInsert($data)
    {
        if (isset($data['status']) && $data['status'] == self::STATUS_ENABLED) {
            $data['token'] = $this->generateToken();
        }
        return $data;
    }

    // 指定时间字段类型
    protected $type = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'publish_time' => 'datetime'
    ];
} 