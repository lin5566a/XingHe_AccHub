<?php

namespace app\admin\controller;

use app\admin\model\ChannelConfig as ChannelConfigModel;

class ChannelConfig extends Base
{
    /**
     * 获取渠道列表
     * @return \think\response\Json
     */
    public function index()
    {
        try {
            $channels = ChannelConfigModel::getChannelList();
            
            $list = [];
            foreach ($channels as $channel) {
                $list[] = [
                    'id' => $channel->id,
                    'name' => $channel->name,
                    'channel_code' => $channel->channel_code,
                    'promotion_link' => $channel->promotion_link,
                    'status' => $channel->status,
                    'status_text' => ChannelConfigModel::getStatusText($channel->status),
                    'is_default' => $channel->is_default,
                    'created_at' => $channel->created_at,
                    'updated_at' => $channel->updated_at
                ];
            }
            
            return $this->ajaxSuccess('获取成功', [
                'list' => $list
            ]);
            
        } catch (\Exception $e) {
            return $this->ajaxError('获取失败：' . $e->getMessage());
        }
    }
    
    /**
     * 添加渠道
     * @return \think\response\Json
     */
    public function add()
    {
        if ($this->request->isPost()) {
            try {
                $data = $this->request->post();
                
                // 验证必填字段
                if (empty($data['name'])) {
                    return $this->ajaxError('渠道名称不能为空');
                }
                
                // 检查渠道名称是否已存在
                $existingChannel = ChannelConfigModel::where('name', $data['name'])->find();
                if ($existingChannel) {
                    return $this->ajaxError('渠道名称已存在');
                }
                
                // 创建渠道
                $channel = ChannelConfigModel::createChannel($data);
                if (!$channel) {
                    return $this->ajaxError('创建失败');
                }
                
                $this->add_log('渠道配置管理', '新增渠道：' . $data['name'], '成功');
                return $this->ajaxSuccess('创建成功');
                
            } catch (\Exception $e) {
                $this->add_log('渠道配置管理', '新增渠道失败', '失败');
                return $this->ajaxError('创建失败：' . $e->getMessage());
            }
        }
        
        // GET请求，返回添加页面数据
        return $this->ajaxSuccess('获取成功', [
            'status_options' => ChannelConfigModel::getStatusOptions()
        ]);
    }
    
    /**
     * 编辑渠道
     * @return \think\response\Json
     */
    public function edit()
    {
        if ($this->request->isPost()) {
            try {
                $data = $this->request->post();
                
                // 验证必填字段
                if (empty($data['id'])) {
                    return $this->ajaxError('渠道ID不能为空');
                }
                
                if (empty($data['name'])) {
                    return $this->ajaxError('渠道名称不能为空');
                }
                
                // 检查渠道是否存在
                $channel = ChannelConfigModel::find($data['id']);
                if (!$channel) {
                    return $this->ajaxError('渠道不存在');
                }
                
                // 检查渠道名称是否已存在（排除当前渠道）
                $existingChannel = ChannelConfigModel::where('name', $data['name'])
                    ->where('id', '<>', $data['id'])
                    ->find();
                if ($existingChannel) {
                    return $this->ajaxError('渠道名称已存在');
                }
                
                // 更新渠道
                $result = ChannelConfigModel::updateChannel($data['id'], $data);
                if (!$result) {
                    return $this->ajaxError('更新失败');
                }
                
                $this->add_log('渠道配置管理', '编辑渠道：' . $data['name'], '成功');
                return $this->ajaxSuccess('更新成功');
                
            } catch (\Exception $e) {
                $this->add_log('渠道配置管理', '编辑渠道失败', '失败');
                return $this->ajaxError('更新失败：' . $e->getMessage());
            }
        }
        
        // GET请求，返回编辑页面数据
        $id = $this->request->get('id');
        if (empty($id)) {
            return $this->ajaxError('渠道ID不能为空');
        }
        
        try {
            $channel = ChannelConfigModel::find($id);
            if (!$channel) {
                return $this->ajaxError('渠道不存在');
            }
            
            return $this->ajaxSuccess('获取成功', [
                'channel' => [
                    'id' => $channel->id,
                    'name' => $channel->name,
                    'channel_code' => $channel->channel_code,
                    'promotion_link' => $channel->promotion_link,
                    'status' => $channel->status,
                    'is_default' => $channel->is_default
                ],
                'status_options' => ChannelConfigModel::getStatusOptions()
            ]);
            
        } catch (\Exception $e) {
            return $this->ajaxError('获取数据失败：' . $e->getMessage());
        }
    }
    
    /**
     * 删除渠道
     * @return \think\response\Json
     */
    public function delete()
    {
        try {
            $id = $this->request->post('id');
            
            if (empty($id)) {
                return $this->ajaxError('渠道ID不能为空');
            }
            
            // 检查渠道是否存在
            $channel = ChannelConfigModel::find($id);
            if (!$channel) {
                return $this->ajaxError('渠道不存在');
            }
            
            // 默认站不允许删除
            if ($channel->is_default) {
                return $this->ajaxError('默认站不允许删除');
            }
            
            // 删除渠道
            $result = ChannelConfigModel::deleteChannel($id);
            if (!$result) {
                return $this->ajaxError('删除失败');
            }
            
            $this->add_log('渠道配置管理', '删除渠道：' . $channel->name, '成功');
            return $this->ajaxSuccess('删除成功');
            
        } catch (\Exception $e) {
            $this->add_log('渠道配置管理', '删除渠道失败', '失败');
            return $this->ajaxError('删除失败：' . $e->getMessage());
        }
    }
    
    /**
     * 设置默认渠道
     * @return \think\response\Json
     */
    public function setDefault()
    {
        try {
            $id = $this->request->post('id');
            
            if (empty($id)) {
                return $this->ajaxError('渠道ID不能为空');
            }
            
            // 检查渠道是否存在
            $channel = ChannelConfigModel::find($id);
            if (!$channel) {
                return $this->ajaxError('渠道不存在');
            }
            
            // 设置默认渠道
            $result = ChannelConfigModel::setDefaultChannel($id);
            if (!$result) {
                return $this->ajaxError('设置失败');
            }
            
            $this->add_log('渠道配置管理', '设置默认渠道：' . $channel->name, '成功');
            return $this->ajaxSuccess('设置成功');
            
        } catch (\Exception $e) {
            $this->add_log('渠道配置管理', '设置默认渠道失败', '失败');
            return $this->ajaxError('设置失败：' . $e->getMessage());
        }
    }
    
    /**
     * 更新渠道状态
     * @return \think\response\Json
     */
    public function updateStatus()
    {
        try {
            $id = $this->request->post('id');
            $status = $this->request->post('status');
            
            if (empty($id)) {
                return $this->ajaxError('渠道ID不能为空');
            }
            
            if (!in_array($status, [0, 1])) {
                return $this->ajaxError('状态值无效');
            }
            
            // 检查渠道是否存在
            $channel = ChannelConfigModel::find($id);
            if (!$channel) {
                return $this->ajaxError('渠道不存在');
            }
            
            // 更新状态
            $result = $channel->save(['status' => $status]);
            if (!$result) {
                return $this->ajaxError('更新失败');
            }
            
            $statusText = $status == 1 ? '启用' : '禁用';
            $this->add_log('渠道配置管理', $statusText . '渠道：' . $channel->name, '成功');
            return $this->ajaxSuccess('更新成功');
            
        } catch (\Exception $e) {
            $this->add_log('渠道配置管理', '更新渠道状态失败', '失败');
            return $this->ajaxError('更新失败：' . $e->getMessage());
        }
    }
    
    /**
     * 复制推广链接
     * @return \think\response\Json
     */
    public function copyLink()
    {
        try {
            $id = $this->request->post('id');
            
            if (empty($id)) {
                return $this->ajaxError('渠道ID不能为空');
            }
            
            // 检查渠道是否存在
            $channel = ChannelConfigModel::find($id);
            if (!$channel) {
                return $this->ajaxError('渠道不存在');
            }
            
            return $this->ajaxSuccess('获取成功', [
                'promotion_link' => $channel->promotion_link
            ]);
            
        } catch (\Exception $e) {
            return $this->ajaxError('获取失败：' . $e->getMessage());
        }
    }
}
