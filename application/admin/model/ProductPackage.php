<?php
namespace app\admin\model;

use think\Model;

class ProductPackage extends Model
{
    protected $name = 'product_packages';
    
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
    
    // 设置时间戳字段名
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    
    // 指定时间字段类型
    protected $type = [
        'id' => 'integer',
        'product_id' => 'integer',
        'type' => 'integer',
        'is_show' => 'integer',
        'enable_regional_restriction' => 'integer',
        'sort' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    // 类型常量
    const TYPE_FILE = 1;    // 文件类型
    const TYPE_LINK = 2;    // 链接类型
    
    // 获取类型文本
    public static function getTypeText($type)
    {
        $types = [
            self::TYPE_FILE => '文件',
            self::TYPE_LINK => '链接'
        ];
        return isset($types[$type]) ? $types[$type] : '未知';
    }
    
    // 关联商品
    public function product()
    {
        return $this->belongsTo('Product', 'product_id');
    }
    
    /**
     * 获取禁止的城市列表
     * @return array
     */
    public function getDisallowedCities()
    {
        if (empty($this->disallowed_cities)) {
            return [];
        }
        
        $cities = json_decode($this->disallowed_cities, true);
        return is_array($cities) ? $cities : [];
    }
    
    /**
     * 设置禁止的城市列表
     * @param array $cities
     * @return $this
     */
    public function setDisallowedCities($cities)
    {
        $this->disallowed_cities = json_encode($cities, JSON_UNESCAPED_UNICODE);
        return $this;
    }
    
    /**
     * 检查IP地址是否被限制
     * @param string $ip
     * @return array
     */
    public function checkIpRestriction($ip = null)
    {
        if (!$this->enable_regional_restriction) {
            return [
                'restricted' => false,
                'reason' => '',
                'location' => null
            ];
        }
        
        $disallowedCities = $this->getDisallowedCities();
        if (empty($disallowedCities)) {
            return [
                'restricted' => false,
                'reason' => '',
                'location' => null
            ];
        }
        
        if ($ip === null) {
            $ip = \app\common\service\IpLocationService::getClientIp();
        }
        
        return \app\common\service\IpLocationService::checkIpRestriction($ip, $disallowedCities);
    }
} 