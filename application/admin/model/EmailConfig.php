<?php
namespace app\admin\model;
use app\common\model\Base;

use think\Model;

class EmailConfig extends Model
{
    protected $table = 'epay_email_config';
    protected $pk = 'id';

    /**
     * 获取邮件配置
     */
    public function getInfo()
    {
        return $this->find();
    }

    /**
     * 更新邮件配置
     */
    public function updateInfo($data)
    {
        $info = $this->find();
        if ($info) {
            return $this->where('id', $info['id'])->update($data);
        }
        return $this->insert($data);
    }

    /**
     * 更新配置
     */
    public function updateConfig($data)
    {
        return $this->updateData($data, ['id' => $data['id']]);
    }
} 