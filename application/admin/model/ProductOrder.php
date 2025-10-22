<?php
namespace app\admin\model;

use think\Model;
use app\common\constants\OrderStatus;

class ProductOrder extends Model
{
    protected $table = 'epay_product_orders';
    protected $pk = 'id';
    
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = false;
    
    // 指定时间字段类型
    protected $type = [
        'created_at' => 'datetime',
        // 'finished_at' => 'datetime', // 新增
        'user_role_id' => 'integer',
        'user_discount' => 'float', // 会员折扣百分比，允许两位小数
    ];
    
    /**
     * 获取访客UUID
     * @return string
     */
    public static function getVisitorUuid()
    {
        // 从请求头获取UUID
        $uuid = isset($_SERVER['HTTP_X_VISITOR_UUID']) ? $_SERVER['HTTP_X_VISITOR_UUID'] : '';
        
        // 验证UUID格式
        if (self::isValidUuid($uuid)) {
            return $uuid;
        }
        
        // 如果UUID无效，返回空字符串
        return '';
    }
    
    /**
     * 验证UUID格式
     * @param string $uuid
     * @return bool
     */
    public static function isValidUuid($uuid)
    {
        if (empty($uuid)) {
            return false;
        }
        
        // UUID v4格式验证：xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
        return preg_match($pattern, $uuid) === 1;
    }
    
    /**
     * 获取渠道代码
     * @return string
     */
    public static function getChannelCode()
    {
        // 优先从header获取渠道代码
        $channelCode = isset($_SERVER['HTTP_X_CHANNEL_CODE']) ? $_SERVER['HTTP_X_CHANNEL_CODE'] : '';
        
        // 如果header中没有，则从URL参数获取
        if (empty($channelCode)) {
            $channelCode = isset($_GET['channel']) ? $_GET['channel'] : '';
        }
        
        // 验证渠道代码格式
        // 支持格式：default 或 CH_XXXXXXXX（CH_ + 8位字母数字）
        if (preg_match('/^(default|CH_[A-Z0-9]{8})$/', $channelCode)) {
            // 验证渠道代码是否在数据库中存在
            if (self::isValidChannelCode($channelCode)) {
                return $channelCode;
            }
        }
        
        // 如果渠道代码无效或不存在，返回空字符串（将记录到默认渠道）
        return '';
    }
    
    /**
     * 验证渠道代码是否在数据库中存在
     * @param string $channelCode 渠道代码
     * @return bool
     */
    private static function isValidChannelCode($channelCode)
    {
        try {
            // 检查渠道是否存在于数据库中且状态为启用
            $channel = \think\Db::name('channel_configs')
                ->where('channel_code', $channelCode)
                ->where('status', 1)
                ->find();
            
            return $channel ? true : false;
        } catch (\Exception $e) {
            // 如果数据库查询失败，记录错误但不影响主流程
            \think\Log::error('渠道代码验证失败：' . $e->getMessage());
            return false;
        }
    }
    
    // 获取状态文本
    public function getStatusTextAttr($value, $data)
    {
        return OrderStatus::getStatusText($data['status']);
    }
    
    // 关联商品分类
    public function category()
    {
        return $this->belongsTo('ProductCategory', 'category_id');
    }
    
    // 关联用户
    public function user()
    {
        return $this->belongsTo('User', 'user_email', 'email');
    }
} 