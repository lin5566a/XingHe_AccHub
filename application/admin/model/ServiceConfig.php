<?php
namespace app\admin\model;

use app\common\model\Base;

class ServiceConfig extends Base
{
    protected $autoWriteTimestamp = true;
    protected $updateTime = 'update_time';
    protected $createTime = 'create_time';

    /**
     * 获取配置信息
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
        try {
            // 打印添加数据
            trace('添加客服配置数据：' . json_encode($data), 'debug');
            
            if ($this->allowField(true)->save($data)) {
                return ['code' => 1, 'msg' => '添加成功'];
            }
            return ['code' => 0, 'msg' => '添加失败'];
        } catch (\Exception $e) {
            trace('添加客服配置异常：' . $e->getMessage(), 'error');
            return ['code' => 0, 'msg' => '添加失败：' . $e->getMessage()];
        }
    }

    /**
     * 更新配置
     */
    public function updateConfig($data)
    {
        try {
            // 打印更新数据
            trace('更新客服配置数据：' . json_encode($data), 'debug');
            
            // 确保有 id
            if (empty($data['id'])) {
                return ['code' => 0, 'msg' => '缺少ID参数'];
            }

            $result = $this->allowField(true)->save($data, ['id' => $data['id']]);
            
            // 打印更新结果
            trace('更新结果：' . var_export($result, true), 'debug');
            
            // 不管是否有实际更新，只要不是 false 就返回成功
            if ($result !== false) {
                return ['code' => 1, 'msg' => '更新成功'];
            }
            
            // 如果有错误信息，返回具体错误
            $error = $this->getError();
            return ['code' => 0, 'msg' => $error ? '更新失败：'.$error : '更新失败'];
            
        } catch (\Exception $e) {
            trace('更新客服配置异常：' . $e->getMessage(), 'error');
            return ['code' => 0, 'msg' => '更新失败：' . $e->getMessage()];
        }
    }
} 