<?php
namespace app\index\controller;

use app\admin\model\SystemContent;
use app\admin\model\SystemInfo;
use app\admin\model\PopupConfig;
use app\admin\model\CustomerService;
use app\common\traits\ApiResponse;
use think\Log;

class System extends Base
{
    use ApiResponse;

    /**
     * 获取系统内容
     */
    public function getContent()
    {
        try {
            $type = $this->request->param('type');
            if (empty($type)) {
                return $this->ajaxError('参数错误');
            }

            // 查询系统内容
            $content = SystemContent::where('type', $type)->find();
            if (!$content) {
                return $this->ajaxError('内容不存在');
            }

            // 格式化返回数据
            $result = [
                'id' => intval($content['id']),
                'type' => strval($content['type']),
                'title' => strval($content['title']),
                'content' => strval($content['content']),
                'create_time' => $content['create_time'],
                'update_time' => $content['update_time']
            ];

            return $this->ajaxSuccess('获取成功', $result);
            
        } catch (\Exception $e) {
            Log::error('获取系统内容失败：' . $e->getMessage());
            return $this->ajaxError('获取失败');
        }
    }

    /**
     * 获取系统配置
     */
    public function getSystemInfo()
    {
        try {
            // 查询系统配置
            $info = SystemInfo::find();
            if (!$info) {
                return $this->ajaxError('系统配置不存在');
            }

            // 格式化返回数据
            $result = [
                'id' => intval($info['id']),
                'system_name' => strval($info['system_name']),
                'system_logo' => strval($info['system_logo']),
                'copyright_info' => strval($info['copyright_info']),
                'create_time' => $info['create_time'],
                'update_time' => $info['update_time']
            ];

            return $this->ajaxSuccess('获取成功', $result);
            
        } catch (\Exception $e) {
            Log::error('获取系统配置失败：' . $e->getMessage());
            return $this->ajaxError('获取失败');
        }
    }

    /**
     * 获取弹窗内容
     */
    public function getPopupContent()
    {
        try {
            // 获取启用的弹窗配置
            $popup = PopupConfig::getActiveConfig();
            
            if (!$popup) {
                // 如果没有启用的弹窗，返回空数据
                return $this->ajaxSuccess('获取成功', [
                    'show_popup' => false,
                    'title' => '',
                    'content' => ''
                ]);
            }

            // 格式化返回数据
            $result = [
                'show_popup' => true,
                'title' => strval($popup['title']),
                'content' => strval($popup['content']),
                'updated_at' => $popup['updated_at']
            ];

            return $this->ajaxSuccess('获取成功', $result);
            
        } catch (\Exception $e) {
            Log::error('获取弹窗内容失败：' . $e->getMessage());
            return $this->ajaxError('获取失败');
        }
    }

} 