<?php
namespace app\admin\model;

use think\Model;

class Product extends Model
{
    protected $name = 'products';
    
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = false;
    
    // 指定时间字段类型
    protected $type = [
        'created_at' => 'datetime',
        'enable_discount' => 'integer',
        'tutorial_video_size' => 'integer',
        'tutorial_video_status' => 'integer',
        'discount_enabled' => 'integer',
        'discount_percentage' => 'float',
        'discount_start_time' => 'datetime',
        'discount_end_time' => 'datetime',
        'discount_set_time' => 'datetime',
    ];
    
    // 允许批量赋值的字段（白名单模式，更安全）
    // 如果不需要限制，可以使用 protected $field = true; 允许所有字段
    protected $field = true; // 允许所有字段批量赋值
    
    // 关联商品分类
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }
    
    // 关联商品详情模板
    public function descriptionTemplate()
    {
        return $this->belongsTo(ProductDescriptionTemplate::class, 'description_template_id');
    }
    
    // 关联库存
    public function stock()
    {
        return $this->hasMany('ProductStock', 'product_id');
    }
    
    /**
     * 获取视频大小文本
     */
    public function getTutorialVideoSizeTextAttr($value, $data)
    {
        if (empty($data['tutorial_video_size'])) {
            return '未知';
        }
        
        $size = $data['tutorial_video_size'];
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        
        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }
        
        return round($size, 2) . ' ' . $units[$unitIndex];
    }
    
    /**
     * 获取视频状态文本
     */
    public function getTutorialVideoStatusTextAttr($value, $data)
    {
        return isset($data['tutorial_video_status']) && $data['tutorial_video_status'] == 1 ? '启用' : '停用';
    }

    /**
     * 检查是否有教程视频
     */
    public function hasTutorialVideo()
    {
        return !empty($this->data['tutorial_video']) && isset($this->data['tutorial_video_status']) && $this->data['tutorial_video_status'] == 1;
    }

    /**
     * 获取折扣状态
     * @return string 折扣状态：无折扣、未开始、进行中、已过期
     */
    public function getDiscountStatus()
    {
        if (!$this->data['discount_enabled']) {
            return '无折扣';
        }

        $now = date('Y-m-d H:i:s');
        $startTime = $this->data['discount_start_time'];
        $endTime = $this->data['discount_end_time'];

        if ($now < $startTime) {
            return '未开始';
        } elseif ($now >= $startTime && $now <= $endTime) {
            return '进行中';
        } else {
            return '已过期';
        }
    }

    /**
     * 获取折扣状态文本
     */
    public function getDiscountStatusTextAttr($value, $data)
    {
        return $this->getDiscountStatus();
    }

    /**
     * 计算折扣后的价格（前台逻辑：只有进行中才显示折扣价）
     * @param float $originalPrice 原价
     * @return float 折扣后价格
     */
    public function getDiscountedPrice($originalPrice = null)
    {
        if (!$this->data['discount_enabled']) {
            return $originalPrice ?: $this->data['price'];
        }

        $status = $this->getDiscountStatus();
        if ($status !== '进行中') {
            return $originalPrice ?: $this->data['price'];
        }

        $price = $originalPrice ?: $this->data['price'];
        $discountPercentage = $this->data['discount_percentage'];
        
        return round($price * ($discountPercentage / 100), 2);
    }

    /**
     * 计算折扣后的价格（后台逻辑：未开始和进行中都显示折扣价）
     * @param float $originalPrice 原价
     * @return float 折扣后价格
     */
    public function getAdminDiscountedPrice($originalPrice = null)
    {
        if (!$this->data['discount_enabled']) {
            return $originalPrice ?: $this->data['price'];
        }

        $status = $this->getDiscountStatus();
        // 只有在"无折扣"或"已过期"状态下才返回原价，其他状态都计算折扣价格
        if ($status === '无折扣' || $status === '已过期') {
            return $originalPrice ?: $this->data['price'];
        }

        $price = $originalPrice ?: $this->data['price'];
        $discountPercentage = $this->data['discount_percentage'];
        
        return round($price * ($discountPercentage / 100), 2);
    }

    /**
     * 获取折扣信息
     * @return array
     */
    public function getDiscountInfo()
    {
        $status = $this->getDiscountStatus();
        
        $info = [
            'enabled' => $this->data['discount_enabled'],
            'status' => $status,
            'percentage' => $this->data['discount_percentage'],
            'start_time' => $this->data['discount_start_time'],
            'end_time' => $this->data['discount_end_time'],
            'set_time' => $this->data['discount_set_time'],
            'discounted_price' => $this->getDiscountedPrice()
        ];

        // 格式化时间显示
        if ($info['start_time'] && $info['end_time']) {
            $info['time_range'] = date('m/d H:i', strtotime($info['start_time'])) . ' - ' . date('m/d H:i', strtotime($info['end_time']));
        }

        return $info;
    }

    /**
     * 设置商品折扣
     * @param array $discountData 折扣数据
     * @return bool
     */
    public function setDiscount($discountData)
    {
        $data = [
            'discount_enabled' => isset($discountData['enabled']) ? intval($discountData['enabled']) : 0,
            'discount_percentage' => isset($discountData['percentage']) ? floatval($discountData['percentage']) : 0,
            'discount_start_time' => isset($discountData['start_time']) ? $discountData['start_time'] : null,
            'discount_end_time' => isset($discountData['end_time']) ? $discountData['end_time'] : null,
            'discount_set_time' => date('Y-m-d H:i:s')
        ];

        return $this->save($data);
    }

    /**
     * 清除商品折扣
     * @return bool
     */
    public function clearDiscount()
    {
        $data = [
            'discount_enabled' => 0,
            'discount_percentage' => 0,
            'discount_start_time' => null,
            'discount_end_time' => null,
            'discount_set_time' => null
        ];

        return $this->save($data);
    }
    
    /**
     * 获取库存数量
     */
    public function getStockCount()
    {
        return $this->stock()->where('status', 0)->count();
    }
    
    /**
     * 获取已售出数量
     */
    public function getSoldCount()
    {
        return $this->stock()->where('status', 1)->count();
    }
} 