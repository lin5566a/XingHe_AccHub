<?php
namespace app\admin\model;

use think\Model;
use app\common\constants\StockStatus;
use think\Db;

class ProductStock extends Model
{
    protected $name = 'product_stock';
    protected $table = 'epay_product_stock';
    
    // 定义字段类型
    protected $type = [
        'id' => 'integer',
        'product_id' => 'integer',
        'order_id' => 'integer',
        'status' => 'integer',
        'cost_price' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    // 状态获取器
    public function getStatusTextAttr($value, $data)
    {
        return StockStatus::getStatusText($data['status']);
    }
    
    // 关联商品
    public function product()
    {
        return $this->belongsTo('Product', 'product_id');
    }
    
    /**
     * 生成批次ID（考虑并发问题）
     * 格式：P + 日期(20250523) + 当天的第几批(001)
     * @return string
     */
    public static function generateBatchId()
    {
        // 开启事务确保原子性
        Db::startTrans();
        try {
            $date = date('Ymd'); // 当前日期，如20250523
            $prefix = 'P' . $date;
            
            // 查询今天已创建的批次数量（使用行锁防止并发）
            $count = self::where('batch_id', 'like', $prefix . '%')
                ->whereTime('created_at', 'today')
                ->lock(true)
                ->count();
            
            // 批次号从001开始
            $batchNumber = str_pad($count + 1, 3, '0', STR_PAD_LEFT);
            $batchId = $prefix . $batchNumber;
            
            // 验证批次ID是否已存在（双重检查）
            $exists = self::where('batch_id', $batchId)->find();
            if ($exists) {
                throw new \Exception('批次ID已存在，请重试');
            }
            
            Db::commit();
            return $batchId;
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }
    
    /**
     * 检查批次ID是否存在
     * @param string $batchId
     * @return bool
     */
    public static function checkBatchIdExists($batchId)
    {
        return self::where('batch_id', $batchId)->count() > 0;
    }
    
    /**
     * 获取批次统计信息
     * @param string $batchId
     * @return array
     */
    public static function getBatchStats($batchId)
    {
        $stats = self::where('batch_id', $batchId)->field([
            'COUNT(*) as total',
            'SUM(CASE WHEN status = ' . StockStatus::UNUSED . ' THEN 1 ELSE 0 END) as unused',
            'SUM(CASE WHEN status = ' . StockStatus::LOCKED . ' THEN 1 ELSE 0 END) as locked',
            'SUM(CASE WHEN status = ' . StockStatus::SOLD . ' THEN 1 ELSE 0 END) as sold',
            'SUM(CASE WHEN status = ' . StockStatus::VOID . ' THEN 1 ELSE 0 END) as void'
        ])->find();
        
        return $stats ? $stats->toArray() : [
            'total' => 0,
            'unused' => 0,
            'locked' => 0,
            'sold' => 0,
            'void' => 0
        ];
    }
    
    /**
     * 模糊查询批次ID
     * @param string $keyword
     * @return array
     */
    public static function searchBatchIds($keyword)
    {
        $batchIds = self::where('batch_id', 'like', '%' . $keyword . '%')
            ->field('batch_id, COUNT(*) as count')
            ->group('batch_id')
            ->select();
            
        $result = [];
        foreach ($batchIds as $item) {
            $result[] = [
                'batch_id' => $item['batch_id'],
                'count' => $item['count']
            ];
        }
        
        return $result;
    }

    /**
     * 获取批次卡密数量
     */
    public static function getBatchCardCount($batch_id)
    {
        // 查询该批次下的卡密数量
        return self::where('batch_id', $batch_id)->count();
    }
} 