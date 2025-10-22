<?php
namespace app\admin\model;

use think\Model;

class CustomerService extends Model
{
    protected $table = 'epay_customer_service';
    
    // 字段类型转换
    protected $type = [
        'tg_show' => 'integer',
        'group_show' => 'integer',
        'online_show' => 'integer',
    ];
    
    /**
     * 获取客服设置
     */
    public function getInfo()
    {
        return $this->find();
    }

    /**
     * 添加配置
     */
    public function add($data)
    {
        return $this->allowField(true)->save($data);
    }

    /**
     * 更新配置
     */
    public function updateConfig($data)
    {
        if (isset($data['id'])) {
            return $this->updateData($data, ['id' => $data['id']]);
        }
        return ['code' => 0, 'msg' => '缺少ID参数'];
    }
    
    /**
     * 获取前台显示的客服配置
     */
    public function getFrontendConfig()
    {
        $config = $this->find();
        if (!$config) {
            return [
                'tg_service_link' => '',
                'group_link' => '',
                'online_service_link' => '',
                'tg_show' => false,
                'group_show' => false,
                'online_show' => false
            ];
        }
        
        return [
            'tg_service_link' => isset($config['tg_service_link']) ? $config['tg_service_link'] : '',
            'group_link' => isset($config['group_link']) ? $config['group_link'] : '',
            'online_service_link' => isset($config['online_service_link']) ? $config['online_service_link'] : '',
            'tg_show' => isset($config['tg_show']) && $config['tg_show'] == 1,
            'group_show' => isset($config['group_show']) && $config['group_show'] == 1,
            'online_show' => isset($config['online_show']) && $config['online_show'] == 1
        ];
    }
} 