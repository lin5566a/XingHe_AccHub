<?php
namespace app\common\service\payment;

use app\admin\model\PaymentChannel as ChannelModel;
use app\admin\model\PaymentChannelRate as RateModel;
use think\Db;
use think\Cache;
use think\Config;

class PaymentChannelSelector
{
    // Redis 键前缀
    const REDIS_KEY_PREFIX = 'payment_channel:';
    // 订单计数重置阈值
    const ORDER_COUNT_RESET = 3000;
    
    /**
     * 选择支付通道（多通道权重选择逻辑）
     * @param string $paymentMethod 支付方式
     * @param float $amount 订单金额
     * @return array|null 返回选中的通道和费率信息
     */
    public static function selectChannel($paymentMethod, $amount)
    {
        // 获取所有可用的通道费率配置
        $rates = RateModel::with(['channel'])
            ->where('payment_method', $paymentMethod)
            ->where('status', 1) // 只选择启用的通道
            ->where(function($query) use ($amount) {
                $query->where('min_amount', '<=', $amount)
                    ->where(function($q) use ($amount) {
                        $q->where('max_amount', '>=', $amount)
                            ->whereOr('max_amount', 0); // max_amount为0表示不限制最大金额
                    });
            })
            ->select();
            
        if (empty($rates)) {
            return null;
        }
        
        // 按权重分配订单
        $selectedRate = self::selectByWeight($rates, $paymentMethod);
        
        if (!$selectedRate) {
            return null;
        }
        
        // 计算手续费
        $fee = self::calculateFee($selectedRate);
        
        return [
            'channel_id' => $selectedRate->channel->id,
            'channel_name' => $selectedRate->channel->name,
            'abbr' => $selectedRate->channel->abbr,
            'merchant_id' => $selectedRate->channel->merchant_id,
            'merchant_key' => $selectedRate->channel->merchant_key,
            'create_order_url' => $selectedRate->channel->create_order_url,
            'query_order_url' => $selectedRate->channel->query_order_url,
            'balance_query_url' => $selectedRate->channel->balance_query_url,
            'notify_url' => $selectedRate->channel->notify_url,
            'return_url' => $selectedRate->channel->return_url,
            'fee_rate' => $selectedRate->fee_rate,
            'fee' => round($amount*$fee, 2),
            'min_amount' => $selectedRate->min_amount,
            'max_amount' => $selectedRate->max_amount,
            'product_code' => $selectedRate->product_code
        ];
    }
    
    /**
     * 按权重选择通道
     * @param \think\Collection $rates 费率列表
     * @param string $paymentMethod 支付方式
     * @return PaymentChannelRate|null
     */
    private static function selectByWeight($rates, $paymentMethod)
    {
        try {
            // 连接 Redis
            $redis = new \Redis();
            $redis->connect('127.0.0.1', 6379);
            
            // 生成 Redis 键
            $redisKey = self::REDIS_KEY_PREFIX . $paymentMethod;
            // 计算总权重
            $totalWeight = 0;
            $validRates = [];
            foreach ($rates as $rate) {
                $weight = intval($rate->weight);
                if ($weight > 0) {
                    $totalWeight += $weight;
                    $validRates[] = [
                        'rate' => $rate,
                        'weight' => $weight
                    ];
                    $redisKey .= '-'.$rate->channel_id;
                }
            }
            
            if ($totalWeight <= 0) {
                return null;
            }
            
            // 使用Redis的原子操作增加订单计数
            $orderCount = $redis->incr($redisKey);
            
            // 检查是否需要重置计数
            if (($orderCount / self::ORDER_COUNT_RESET) >= $totalWeight) {
                // 使用原子操作重置计数
                $redis->set($redisKey, 1);
                $orderCount = 1;
            }
            
            // 计算当前应该使用的权重位置
            $use = ($orderCount - 1) % $totalWeight + 1;
            
            // 根据权重选择通道
            $currentWeight = 0;
            foreach ($validRates as $item) {
                $currentWeight += $item['weight'];
                if ($use <= $currentWeight) {
                    return $item['rate'];
                }
            }
            
            // 如果出现异常情况，返回第一个有效通道
            return $validRates[0]['rate'];
            
        } catch (\Exception $e) {
            // Redis 连接失败时，使用随机选择作为备选方案
            write_log('payment_channel', 'Redis连接失败，使用随机选择', $e->getMessage());
            
            // 计算总权重
            $totalWeight = 0;
            $validRates = [];
            foreach ($rates as $rate) {
                $weight = intval($rate->weight);
                if ($weight > 0) {
                    $totalWeight += $weight;
                    $validRates[] = [
                        'rate' => $rate,
                        'weight' => $weight
                    ];
                }
            }
            
            if ($totalWeight <= 0) {
                return null;
            }
            
            // 随机选择一个权重位置
            $use = rand(1, $totalWeight);
            
            // 根据权重选择通道
            $currentWeight = 0;
            foreach ($validRates as $item) {
                $currentWeight += $item['weight'];
                if ($use <= $currentWeight) {
                    return $item['rate'];
                }
            }
            
            // 如果出现异常情况，返回第一个有效通道
            return $validRates[0]['rate'];
        }
    }
    
    /**
     * 计算手续费
     * @param PaymentChannelRate $rate 费率记录
     * @return float
     */
    private static function calculateFee($rate)
    {
        return round($rate->fee_rate / 100, 2);
    }
} 