<?php
namespace app\admin\model;

use think\Model;

class SystemConfig extends Model
{
    protected $table = 'epay_system_config';
    
    protected $autoWriteTimestamp = true;
    protected $updateTime = 'update_time';
    protected $createTime = 'create_time';

    /**
     * 更新配置
     */
    public function updateConfig($data)
    {
        return $this->updateData($data, ['id' => $data['id']]);
    }
} 