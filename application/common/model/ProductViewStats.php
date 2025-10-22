<?php

namespace app\common\model;

use think\Model;
use think\Db;

class ProductViewStats extends Model
{
    protected $table = 'epay_visitor_records';
    
    /**
     * 获取商品浏览人数（基于UUID去重）
     * @param int $productId 商品ID
     * @param string $startTime 开始时间
     * @param string $endTime 结束时间
     * @return int
     */
    public static function getProductViewers($productId, $startTime, $endTime)
    {
        return self::where('product_id', $productId)
            ->where('view_type', 'detail')
            ->where('visitor_uuid', '<>', '')
            ->where('created_at', 'between', [$startTime, $endTime])
            ->group('visitor_uuid')
            ->count();
    }
    
    /**
     * 获取商品付款人数（基于UUID去重）
     * @param int $productId 商品ID
     * @param string $startTime 开始时间
     * @param string $endTime 结束时间
     * @return int
     */
    public static function getProductBuyers($productId, $startTime, $endTime)
    {
        return Db::name('product_orders')
            ->alias('o')
            ->join('visitor_records v', 'o.visitor_uuid = v.visitor_uuid', 'left')
            ->where('o.product_id', $productId)
            ->where('o.status', 'in', [2, 3, 4]) // 已支付、已完成、已退款
            ->where('o.visitor_uuid', '<>', '')
            ->where('o.created_at', 'between', [$startTime, $endTime])
            ->group('o.visitor_uuid')
            ->count();
    }
    
    /**
     * 获取商品转化率
     * @param int $productId 商品ID
     * @param string $startTime 开始时间
     * @param string $endTime 结束时间
     * @return float
     */
    public static function getProductConversionRate($productId, $startTime, $endTime)
    {
        $viewers = self::getProductViewers($productId, $startTime, $endTime);
        $buyers = self::getProductBuyers($productId, $startTime, $endTime);
        
        if ($viewers == 0) {
            return 0.00;
        }
        
        return round(($buyers / $viewers) * 100, 2);
    }
    
    /**
     * 获取商品浏览统计（包含浏览人数、付款人数、转化率）
     * @param int $productId 商品ID
     * @param string $startTime 开始时间
     * @param string $endTime 结束时间
     * @return array
     */
    public static function getProductStats($productId, $startTime, $endTime)
    {
        $viewers = self::getProductViewers($productId, $startTime, $endTime);
        $buyers = self::getProductBuyers($productId, $startTime, $endTime);
        $conversionRate = self::getProductConversionRate($productId, $startTime, $endTime);
        
        return [
            'viewers' => $viewers,
            'buyers' => $buyers,
            'conversion_rate' => $conversionRate
        ];
    }
    
    /**
     * 获取多个商品的浏览统计
     * @param array $productIds 商品ID数组
     * @param string $startTime 开始时间
     * @param string $endTime 结束时间
     * @return array
     */
    public static function getMultipleProductStats($productIds, $startTime, $endTime)
    {
        $result = [];
        
        foreach ($productIds as $productId) {
            $result[$productId] = self::getProductStats($productId, $startTime, $endTime);
        }
        
        return $result;
    }
    
    /**
     * 获取商品浏览趋势数据（按小时）
     * @param int $productId 商品ID
     * @param string $startTime 开始时间
     * @param string $endTime 结束时间
     * @return array
     */
    public static function getProductViewTrendByHour($productId, $startTime, $endTime)
    {
        $data = [];
        
        // 获取24小时数据
        for ($i = 0; $i < 24; $i++) {
            $hourStart = date('Y-m-d H:i:s', strtotime($startTime) + $i * 3600);
            $hourEnd = date('Y-m-d H:i:s', strtotime($startTime) + ($i + 1) * 3600 - 1);
            
            $count = self::where('product_id', $productId)
                ->where('view_type', 'detail')
                ->where('visitor_uuid', '<>', '')
                ->where('created_at', 'between', [$hourStart, $hourEnd])
                ->group('visitor_uuid')
                ->count();
            
            $data[] = [
                'time' => sprintf('%02d:00', $i),
                'value' => $count
            ];
        }
        
        return $data;
    }
    
    /**
     * 获取商品浏览趋势数据（按天）
     * @param int $productId 商品ID
     * @param string $startTime 开始时间
     * @param string $endTime 结束时间
     * @return array
     */
    public static function getProductViewTrendByDay($productId, $startTime, $endTime)
    {
        $data = [];
        $start = strtotime($startTime);
        $end = strtotime($endTime);
        
        // 按天统计
        for ($timestamp = $start; $timestamp <= $end; $timestamp += 86400) {
            $dayStart = date('Y-m-d 00:00:00', $timestamp);
            $dayEnd = date('Y-m-d 23:59:59', $timestamp);
            
            $count = self::where('product_id', $productId)
                ->where('view_type', 'detail')
                ->where('visitor_uuid', '<>', '')
                ->where('created_at', 'between', [$dayStart, $dayEnd])
                ->group('visitor_uuid')
                ->count();
            
            $data[] = [
                'time' => date('m-d', $timestamp),
                'value' => $count
            ];
        }
        
        return $data;
    }
    
    /**
     * 获取商品浏览趋势数据
     * @param int $productId 商品ID
     * @param string $startTime 开始时间
     * @param string $endTime 结束时间
     * @param string $dateType 日期类型：realtime, yesterday, 7days, 30days
     * @return array
     */
    public static function getProductViewTrend($productId, $startTime, $endTime, $dateType)
    {
        if ($dateType === 'realtime' || $dateType === 'yesterday') {
            return self::getProductViewTrendByHour($productId, $startTime, $endTime);
        } else {
            return self::getProductViewTrendByDay($productId, $startTime, $endTime);
        }
    }
}
