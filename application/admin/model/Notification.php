<?php

namespace app\admin\model;

use think\Model;

class Notification extends Model
{
    protected $table = 'epay_notifications';
    
    // 自动时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    
    // 字段类型转换
    protected $type = [
        'is_read' => 'boolean',
        'related_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    // 通知类型常量
    const TYPE_MANUAL_SHIPMENT = 'manual_shipment';
    const TYPE_REPLENISHMENT = 'replenishment';
    
    /**
     * 创建手动发货通知
     * @param string $orderNo 订单号
     * @param int $orderId 订单ID
     * @return bool
     */
    public static function createManualShipmentNotification($orderNo, $orderId)
    {
        try {
            // 使用数组方式创建数据，确保所有字段都能正确设置
            $data = [
                'type' => self::TYPE_MANUAL_SHIPMENT,
                'title' => "订单 #{$orderNo}",
                'content' => "该订单已支付成功，请及时安排发货",
                'related_id' => $orderId,
                'order_no' => $orderNo,
                'is_read' => false,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $notification = new self();
            return $notification->allowField(true)->save($data);
        } catch (\Exception $e) {
            \think\Log::error('创建手动发货通知失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 创建补货提醒通知
     * @param string $email 用户邮箱
     * @param string $productName 商品名称
     * @param int $quantity 数量
     * @param int $requestId 补货请求ID
     * @return bool
     */
    public static function createReplenishmentNotification($email, $productName, $quantity, $requestId)
    {
        try {
            // 使用数组方式创建数据，确保所有字段都能正确设置
            $data = [
                'type' => self::TYPE_REPLENISHMENT,
                'title' => $email,
                'content' => "请求补货{$productName}{$quantity}个",
                'related_id' => $requestId,
                'is_read' => false,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // 调试信息
            \think\Log::info('创建补货通知数据: ' . json_encode($data));
            
            $notification = new self();
            $result = $notification->allowField(true)->save($data);
            
            // 保存后的调试信息
            \think\Log::info('保存后通知信息: ' . json_encode([
                'id' => $notification->id,
                'type' => $notification->type,
                'result' => $result
            ]));
            
            return $result;
        } catch (\Exception $e) {
            \think\Log::error('创建补货通知失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 获取通知列表
     * @param string $type 通知类型
     * @param int $limit 限制数量
     * @return array
     */
    public static function getNotificationList($type = '', $limit = 20)
    {
        $query = self::order('created_at desc');
        
        if (!empty($type)) {
            $query->where('type', $type);
        }
        
        return $query->limit($limit)->select();
    }
    
    /**
     * 获取未读通知数量
     * @param string $type 通知类型
     * @return int
     */
    public static function getUnreadCount($type = '')
    {
        $query = self::where('is_read', false);
        
        if (!empty($type)) {
            $query->where('type', $type);
        }
        
        return $query->count();
    }
    
    /**
     * 标记通知为已读
     * @param int $id 通知ID
     * @return bool
     */
    public static function markAsRead($id)
    {
        try {
            return self::where('id', $id)->update(['is_read' => true]) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * 标记指定类型的所有通知为已读
     * @param string $type 通知类型
     * @return bool
     */
    public static function markAllAsReadByType($type)
    {
        try {
            return self::where('type', $type)->update(['is_read' => true]) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * 标记所有通知为已读
     * @return bool
     */
    public static function markAllAsRead()
    {
        try {
            return self::where('is_read', false)->update(['is_read' => true]) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * 获取通知统计信息
     * @return array
     */
    public static function getNotificationStats()
    {
        $manualShipmentCount = self::where('type', self::TYPE_MANUAL_SHIPMENT)
            ->where('is_read', false)
            ->count();
            
        $replenishmentCount = self::where('type', self::TYPE_REPLENISHMENT)
            ->where('is_read', false)
            ->count();
            
        return [
            'manual_shipment' => $manualShipmentCount,
            'replenishment' => $replenishmentCount,
            'total' => $manualShipmentCount + $replenishmentCount
        ];
    }
    
    /**
     * 清理过期通知（保留30天）
     * @return int 删除的记录数
     */
    public static function cleanExpiredNotifications()
    {
        $expiredTime = date('Y-m-d H:i:s', strtotime('-30 days'));
        return self::where('created_at', '<', $expiredTime)->delete();
    }
    
    /**
     * 获取时间差描述
     * @return string
     */
    public function getTimeAgo()
    {
        // 检查时间戳是否有效
        if (empty($this->created_at) || $this->created_at == '0000-00-00 00:00:00') {
            return '未知时间';
        }
        
        $now = time();
        $created = strtotime($this->created_at);
        
        // 检查时间戳转换是否成功
        if ($created === false) {
            return '未知时间';
        }
        
        $diff = $now - $created;
        
        if ($diff < 60) {
            return '刚刚';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . '分钟前';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . '小时前';
        } else {
            return floor($diff / 86400) . '天前';
        }
    }
}
