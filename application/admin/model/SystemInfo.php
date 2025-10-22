<?php
namespace app\admin\model;

use think\Model;

class SystemInfo extends Model
{
    // 设置表名
    protected $name = 'system_info';
    
    // 设置主键
    protected $pk = 'id';
    
    // 关闭自动写入时间戳
    protected $autoWriteTimestamp = false;

    /**
     * 获取系统基本信息
     */
    public function getInfo()
    {
        return $this->find();
    }

    /**
     * 更新系统基本信息
     */
    public function updateInfo($data)
    {
        $info = $this->find();
        if ($info) {
            return $this->where('id', $info['id'])->update($data);
        }
        return $this->insert($data);
    }
} 