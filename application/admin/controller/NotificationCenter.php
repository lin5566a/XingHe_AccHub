<?php

namespace app\admin\controller;

use app\admin\model\Notification;
use app\admin\model\SystemInfo;

class NotificationCenter extends Base
{
    /**
     * 获取通知中心数据
     * @return \think\response\Json
     */
    public function getNotifications()
    {
        try {
            // 获取通知统计信息
            $stats = Notification::getNotificationStats();
            // 获取手动发货通知列表
            $manualShipmentList = Notification::getNotificationList(Notification::TYPE_MANUAL_SHIPMENT, 10);
            
            $manualShipmentList = array_map(function($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'content' => $notification->content,
                    'order_no' => $notification->order_no,
                    'is_read' => $notification->is_read,
                    'time_ago' => $notification->getTimeAgo()
                ];
            }, $manualShipmentList);
            
            // 获取补货提醒通知列表
            $replenishmentList = Notification::getNotificationList(Notification::TYPE_REPLENISHMENT, 10);
            $replenishmentList = array_map(function($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'content' => $notification->content,
                    'is_read' => $notification->is_read,
                    'time_ago' => $notification->getTimeAgo()
                ];
            }, $replenishmentList);
            
            // 获取提示音设置
            $soundSettings = $this->getSoundSettings();
            
            return $this->ajaxSuccess('获取成功', [
                'stats' => $stats,
                'manual_shipment' => $manualShipmentList,
                'replenishment' => $replenishmentList,
                'sound_settings' => $soundSettings
            ]);
            
        } catch (\Exception $e) {
            return $this->ajaxError('获取通知失败：' . $e->getMessage());
        }
    }
    
    /**
     * 标记单个通知为已读
     * @return \think\response\Json
     */
    public function markAsRead()
    {
        try {
            $id = input('id');
            
            if (empty($id)) {
                return $this->ajaxError('通知ID不能为空');
            }
            
            $result = Notification::markAsRead($id);
            
            if ($result) {
                // 重新获取统计信息
                $stats = Notification::getNotificationStats();
                return $this->ajaxSuccess('标记成功', ['stats' => $stats]);
            } else {
                return $this->ajaxError('标记失败');
            }
            
        } catch (\Exception $e) {
            return $this->ajaxError('标记失败：' . $e->getMessage());
        }
    }
    
    /**
     * 标记指定类型的所有通知为已读
     * @return \think\response\Json
     */
    public function markAllAsReadByType()
    {
        try {
            $type = input('type');
            
            if (empty($type)) {
                return $this->ajaxError('通知类型不能为空');
            }
            
            $result = Notification::markAllAsReadByType($type);
            
            if ($result) {
                // 重新获取统计信息
                $stats = Notification::getNotificationStats();
                return $this->ajaxSuccess('标记成功', ['stats' => $stats]);
            } else {
                return $this->ajaxError('标记失败');
            }
            
        } catch (\Exception $e) {
            return $this->ajaxError('标记失败：' . $e->getMessage());
        }
    }
    
    /**
     * 标记所有通知为已读
     * @return \think\response\Json
     */
    public function markAllAsRead()
    {
        try {
            $result = Notification::markAllAsRead();
            
            if ($result) {
                // 重新获取统计信息
                $stats = Notification::getNotificationStats();
                return $this->ajaxSuccess('标记成功', ['stats' => $stats]);
            } else {
                return $this->ajaxError('标记失败');
            }
            
        } catch (\Exception $e) {
            return $this->ajaxError('标记失败：' . $e->getMessage());
        }
    }
    
    /**
     * 获取通知统计信息
     * @return \think\response\Json
     */
    public function getStats()
    {
        try {
            $stats = Notification::getNotificationStats();
            return $this->ajaxSuccess('获取成功', $stats);
            
        } catch (\Exception $e) {
            return $this->ajaxError('获取统计失败：' . $e->getMessage());
        }
    }
    
    /**
     * 获取提示音设置
     * @return array
     */
    private function getSoundSettings()
    {
        try {
            $systemInfo = SystemInfo::find();
            if (!$systemInfo) {
                // 如果系统信息不存在，返回默认值
                return [
                    'manual_shipment_sound' => true,
                    'replenishment_sound' => true
                ];
            }
            
            return [
                'manual_shipment_sound' => (bool)$systemInfo->manual_shipment_sound,
                'replenishment_sound' => (bool)$systemInfo->replenishment_sound
            ];
        } catch (\Exception $e) {
            // 出错时返回默认值
            return [
                'manual_shipment_sound' => true,
                'replenishment_sound' => true
            ];
        }
    }
    
}
