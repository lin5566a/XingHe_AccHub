<?php

namespace app\admin\model;

use think\Model;

class ChannelConfig extends Model
{
    protected $table = 'epay_channel_configs';
    
    // 自动时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    
    // 字段类型转换
    protected $type = [
        'status' => 'boolean',
        'is_default' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * 获取渠道列表
     * @param array $where 查询条件
     * @param int $limit 限制数量
     * @return array
     */
    public static function getChannelList($where = [], $limit = 0)
    {
        $query = self::order('is_default desc, created_at desc');
        
        if (!empty($where)) {
            $query->where($where);
        }
        
        if ($limit > 0) {
            $query->limit($limit);
        }
        
        return $query->select();
    }
    
    /**
     * 获取启用的渠道列表
     * @return array
     */
    public static function getActiveChannels()
    {
        return self::where('status', 1)->order('is_default desc, created_at desc')->select();
    }
    
    /**
     * 获取默认渠道
     * @return object|null
     */
    public static function getDefaultChannel()
    {
        return self::where('is_default', 1)->find();
    }
    
    /**
     * 根据渠道代码获取渠道信息
     * @param string $channelCode 渠道代码
     * @return object|null
     */
    public static function getChannelByCode($channelCode)
    {
        return self::where('channel_code', $channelCode)->find();
    }
    
    /**
     * 生成渠道代码
     * @param string $name 渠道名称
     * @return string
     */
    public static function generateChannelCode($name)
    {
        // 生成安全的渠道代码格式：CH_ + 8位随机字符（字母数字混合）
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = 'CH_';
        
        // 生成8位随机字符
        for ($i = 0; $i < 8; $i++) {
            $code .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        
        // 检查是否已存在，如果存在则重新生成
        while (self::where('channel_code', $code)->find()) {
            $code = 'CH_';
            for ($i = 0; $i < 8; $i++) {
                $code .= $chars[mt_rand(0, strlen($chars) - 1)];
            }
        }
        
        return $code;
    }
    
    /**
     * 生成推广链接
     * @param string $channelCode 渠道代码
     * @return string
     * @throws \Exception
     */
    public static function generatePromotionLink($channelCode)
    {
        // 获取默认渠道的推广链接作为基础域名
        $defaultChannel = self::getDefaultChannel();
        
        if (!$defaultChannel || empty($defaultChannel->promotion_link)) {
            throw new \Exception('默认渠道不存在或推广链接为空，无法生成新渠道链接');
        }
        
        // 从默认渠道的推广链接中提取域名
        $parsedUrl = parse_url($defaultChannel->promotion_link);
        if (!$parsedUrl || !isset($parsedUrl['scheme']) || !isset($parsedUrl['host'])) {
            throw new \Exception('默认渠道推广链接格式错误，无法解析域名');
        }
        
        $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
        
        // 移除末尾的斜杠
        $baseUrl = rtrim($baseUrl, '/');
        
        return $baseUrl . '?channel=' . $channelCode;
    }
    
    /**
     * 创建渠道
     * @param array $data 渠道数据
     * @return bool|object
     */
    public static function createChannel($data)
    {
        try {
            // 生成渠道代码
            $channelCode = self::generateChannelCode($data['name']);
            
            // 生成推广链接
            $promotionLink = self::generatePromotionLink($channelCode);
            
            $channelData = [
                'name' => $data['name'],
                'channel_code' => $channelCode,
                'promotion_link' => $promotionLink,
                'status' => isset($data['status']) ? $data['status'] : 1,
                'is_default' => 0
            ];
            
            $channel = new self();
            return $channel->save($channelData) ? $channel : false;
        } catch (\Exception $e) {
            \think\Log::error('创建渠道失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 更新渠道
     * @param int $id 渠道ID
     * @param array $data 更新数据
     * @return bool
     */
    public static function updateChannel($id, $data)
    {
        try {
            $channel = self::find($id);
            if (!$channel) {
                return false;
            }
            
            // 默认站不允许修改名称和代码
            if ($channel->is_default) {
                unset($data['name']);
                unset($data['channel_code']);
            }
            
            // 编辑时只允许修改渠道名称，不允许修改渠道代码和推广链接
            // 移除可能存在的渠道代码和推广链接字段，确保不会被修改
            unset($data['channel_code']);
            unset($data['promotion_link']);
            
            return $channel->save($data);
        } catch (\Exception $e) {
            \think\Log::error('更新渠道失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 删除渠道
     * @param int $id 渠道ID
     * @return bool
     */
    public static function deleteChannel($id)
    {
        try {
            $channel = self::find($id);
            if (!$channel) {
                return false;
            }
            
            // 默认站不允许删除
            if ($channel->is_default) {
                return false;
            }
            
            return $channel->delete();
        } catch (\Exception $e) {
            \think\Log::error('删除渠道失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 设置默认渠道
     * @param int $id 渠道ID
     * @return bool
     */
    public static function setDefaultChannel($id)
    {
        try {
            // 开启事务
            \think\Db::startTrans();
            
            // 清除所有默认标记
            self::where('is_default', 1)->update(['is_default' => 0]);
            
            // 设置新的默认渠道
            $result = self::where('id', $id)->update(['is_default' => 1]);
            
            \think\Db::commit();
            return $result > 0;
        } catch (\Exception $e) {
            \think\Db::rollback();
            \think\Log::error('设置默认渠道失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 获取状态文本
     * @param int $status 状态值
     * @return string
     */
    public static function getStatusText($status)
    {
        return $status == 1 ? '启用' : '禁用';
    }
    
    /**
     * 获取状态选项
     * @return array
     */
    public static function getStatusOptions()
    {
        return [
            0 => '禁用',
            1 => '启用'
        ];
    }
}
