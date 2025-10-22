<?php
namespace app\admin\controller;

use app\admin\model\PopupConfig as PopupConfigModel;
use app\common\traits\ApiResponse;

class PopupConfig extends Base
{
    use ApiResponse;

    /**
     * 获取弹窗配置
     */
    public function getConfig()
    {
        try {
            $config = PopupConfigModel::getConfig();
            if (!$config) {
                // 如果没有配置，返回默认值
                $config = [
                    'id' => 0,
                    'title' => '',
                    'content' => '',
                    'status' => 0
                ];
            }
            
            return $this->ajaxSuccess('获取成功', $config);
            
        } catch (\Exception $e) {
            $this->add_log('弹窗管理', '获取弹窗配置失败：' . $e->getMessage(), '失败');
            return $this->ajaxError('获取失败');
        }
    }

    /**
     * 更新弹窗配置
     */
    public function updateConfig()
    {
        if (!$this->request->isPost()) {
            $this->add_log('弹窗管理', '更新弹窗配置：请求方式错误', '失败');
            return $this->ajaxError('请求方式错误');
        }

        try {
            $data = $this->request->post();
            
            // 验证必填字段
            if (empty($data['title'])) {
                $this->add_log('弹窗管理', '更新弹窗配置：弹窗标题不能为空', '失败');
                return $this->ajaxError('弹窗标题不能为空');
            }
            
            if (empty($data['content'])) {
                $this->add_log('弹窗管理', '更新弹窗配置：弹窗内容不能为空', '失败');
                return $this->ajaxError('弹窗内容不能为空');
            }
            
            // 验证状态字段
            if (!isset($data['status']) || !in_array($data['status'], [0, 1])) {
                $data['status'] = 0; // 默认停用
            }
            
            // 更新配置
            $result = PopupConfigModel::updateConfig($data);
            
            if ($result) {
                $this->add_log('弹窗管理', '更新弹窗配置', '成功');
                return $this->ajaxSuccess('保存成功');
            } else {
                $this->add_log('弹窗管理', '更新弹窗配置', '失败');
                return $this->ajaxError('保存失败');
            }
            
        } catch (\Exception $e) {
            $this->add_log('弹窗管理', '更新弹窗配置失败：' . $e->getMessage(), '失败');
            return $this->ajaxError('保存失败');
        }
    }

    /**
     * 更新弹窗状态
     */
    public function updateStatus()
    {
        if (!$this->request->isPost()) {
            $this->add_log('弹窗管理', '更新弹窗状态：请求方式错误', '失败');
            return $this->ajaxError('请求方式错误');
        }

        try {
            $status = $this->request->post('status');
            
            if (!in_array($status, [0, 1])) {
                $this->add_log('弹窗管理', '更新弹窗状态：状态参数错误', '失败');
                return $this->ajaxError('状态参数错误');
            }
            
            // 获取现有配置
            $config = PopupConfigModel::getConfig();
            if (!$config) {
                $this->add_log('弹窗管理', '更新弹窗状态：配置不存在', '失败');
                return $this->ajaxError('配置不存在');
            }
            
            // 更新状态
            $result = $config->save(['status' => $status]);
            
            if ($result !== false) {
                $statusText = $status ? '启用' : '停用';
                $this->add_log('弹窗管理', '更新弹窗状态：' . $statusText, '成功');
                return $this->ajaxSuccess($statusText . '成功');
            } else {
                $this->add_log('弹窗管理', '更新弹窗状态', '失败');
                return $this->ajaxError('更新失败');
            }
            
        } catch (\Exception $e) {
            $this->add_log('弹窗管理', '更新弹窗状态失败：' . $e->getMessage(), '失败');
            return $this->ajaxError('更新失败');
        }
    }

    /**
     * 重置弹窗配置
     */
    public function resetConfig()
    {
        if (!$this->request->isPost()) {
            $this->add_log('弹窗管理', '重置弹窗配置：请求方式错误', '失败');
            return $this->ajaxError('请求方式错误');
        }

        try {
            // 获取现有配置
            $config = PopupConfigModel::getConfig();
            if (!$config) {
                $this->add_log('弹窗管理', '重置弹窗配置：配置不存在', '失败');
                return $this->ajaxError('配置不存在');
            }
            
            // 重置为默认内容
            $defaultData = [
                'title' => '欢迎来到星河账号商城',
                'content' => '<p>欢迎您的到来！</p><p>星河账号商城是一个值得信赖的数字账号交易平台，致力于为用户提供安全、便捷、高质量的账号购买服务。</p>',
                'status' => 0
            ];
            
            $result = $config->save($defaultData);
            
            if ($result !== false) {
                $this->add_log('弹窗管理', '重置弹窗配置', '成功');
                return $this->ajaxSuccess('重置成功');
            } else {
                $this->add_log('弹窗管理', '重置弹窗配置', '失败');
                return $this->ajaxError('重置失败');
            }
            
        } catch (\Exception $e) {
            $this->add_log('弹窗管理', '重置弹窗配置失败：' . $e->getMessage(), '失败');
            return $this->ajaxError('重置失败');
        }
    }
}
