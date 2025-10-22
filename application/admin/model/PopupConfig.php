<?php
namespace app\admin\model;

use think\Model;

class PopupConfig extends Model
{
    protected $table = 'epay_popup_config';
    
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    
    // 字段类型转换
    protected $type = [
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * 获取弹窗配置
     */
    public static function getConfig()
    {
        return self::find();
    }
    
    /**
     * 更新弹窗配置
     */
    public static function updateConfig($data)
    {
        $config = self::find();
        if (!$config) {
            // 如果没有配置，创建新的
            return self::create($data);
        } else {
            // 更新现有配置
            return $config->save($data);
        }
    }
    
    /**
     * 获取启用的弹窗配置
     */
    public static function getActiveConfig()
    {
        return self::where('status', 1)->find();
    }
    
    /**
     * 状态文本
     */
    public function getStatusTextAttr($value, $data)
    {
        $status = [
            0 => '停用',
            1 => '启用'
        ];
        return isset($status[$data['status']]) ? $status[$data['status']] : '未知';
    }
}
