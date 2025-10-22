<?php

namespace app\admin\controller;

use think\Db;
use app\admin\controller\Base;
use app\common\service\VisitorTrackingService;

class TrafficStats extends Base
{
    /**
     * 获取全站流量统计数据
     * @return \think\response\Json
     */
    public function getStats()
    {
        try {
            $date_type = input('date_type', 'realtime'); // realtime, yesterday, 7days, 30days
            
            // 根据日期类型确定查询范围
            $dateRange = $this->getDateRange($date_type);
            $startDate = $dateRange['start'];
            $endDate = $dateRange['end'];
            
            // 获取统计数据（总计）
            $stats = $this->calculateStats($startDate, $endDate, $date_type);
            
            // 获取对比数据
            $comparisonStats = [];
            $comparisonRange = $this->getComparisonRange($date_type);
            if ($comparisonRange) {
                $comparisonStats = $this->calculateStats($comparisonRange['start'], $comparisonRange['end'], $date_type);
            }
            
            // 计算同比变化
            $comparison = $this->calculateComparison($stats, $comparisonStats);
            
            return $this->ajaxSuccess('获取成功', [
                'stats' => $stats,
                'comparison' => $comparison,
                'date_range' => [
                    'start' => $startDate,
                    'end' => $endDate,
                    'type' => $date_type
                ]
            ]);
            
        } catch (\Exception $e) {
            return $this->ajaxError('获取统计数据失败：' . $e->getMessage());
        }
    }
    
    /**
     * 添加渠道过滤条件
     * @param \think\db\Query $query 查询对象
     * @param string $channel_code 渠道代码
     * @return \think\db\Query
     */
    private function addChannelFilter($query, $channel_code)
    {
        if (!empty($channel_code)) {
            // 如果是默认渠道，查询没有渠道代码的记录
            if ($channel_code === 'default') {
                $query->where(function($q) {
                    $q->where('channel_code', '')
                      ->whereOr('channel_code', 'default')
                      ->whereOr('channel_code', null);
                });
            } else {
                // 查询指定渠道的记录
                $query->where('channel_code', $channel_code);
            }
        }
        // 如果channel_code为空，不添加任何过滤条件，返回所有渠道的数据
        return $query;
    }
    
    /**
     * 添加UUID去重条件（用于访客统计）
     * @param \think\db\Query $query 查询对象
     * @param bool $useUuid 是否使用UUID去重，默认为true
     * @return \think\db\Query
     */
    private function addUuidDistinct($query, $useUuid = true)
    {
        if ($useUuid) {
            return $query->where('visitor_uuid', '<>', '')->group('visitor_uuid');
        } else {
            // 如果不使用UUID，则按IP地址去重（用于没有UUID的记录）
            return $query->where('visitor_uuid', '')->group('ip_address');
        }
    }
    
    
    /**
     * 获取趋势图数据
     * @return \think\response\Json
     */
    public function getTrendData()
    {
        try {
            $date_type = input('date_type', 'realtime');
            $data_type = input('data_type', 'payment_amount'); // payment_amount, net_payment, visitors, paying_buyers, conversion_rate, refund_amount, refund_rate
            
            // 根据日期类型确定查询范围
            $dateRange = $this->getDateRange($date_type);
            $startDate = $dateRange['start'];
            $endDate = $dateRange['end'];
            
            // 获取总计趋势数据
            $totalTrendData = $this->getTrendDataByType($startDate, $endDate, $date_type, $data_type);
            
            // 获取所有渠道的趋势数据
            $channels = Db::name('channel_configs')
                ->where('status', 1)
                ->field('id, name, channel_code, is_default')
                ->order('is_default desc, id asc')
                ->select();
            
            $channelTrendData = [];
            foreach ($channels as $channel) {
                // 如果是默认渠道，使用 'default' 作为渠道代码
                $channelCode = $channel['is_default'] ? 'default' : $channel['channel_code'];
                $trendData = $this->getTrendDataByType($startDate, $endDate, $date_type, $data_type, $channelCode);
                $channelTrendData[] = [
                    'channel_id' => $channel['id'],
                    'channel_name' => $channel['name'],
                    'channel_code' => $channel['channel_code'],
                    'is_default' => $channel['is_default'],
                    'trend_data' => $trendData
                ];
            }
            
            return $this->ajaxSuccess('获取成功', [
                'total_trend_data' => $totalTrendData,
                'channel_trend_data' => $channelTrendData,
                'date_range' => [
                    'start' => $startDate,
                    'end' => $endDate,
                    'type' => $date_type
                ],
                'data_type' => $data_type
            ]);
            
        } catch (\Exception $e) {
            return $this->ajaxError('获取趋势数据失败：' . $e->getMessage());
        }
    }
    
    /**
     * 获取渠道列表
     * @return \think\response\Json
     */
    public function getChannelList()
    {
        try {
            // 获取所有渠道配置
            $channels = Db::name('channel_configs')
                ->where('status', 1)
                ->field('id, name, channel_code')
                ->order('is_default desc, id asc')
                ->select();
            
            // 添加总计选项
            $channelList = [
                [
                    'id' => 0,
                    'name' => '总计',
                    'channel_code' => ''
                ]
            ];
            
            foreach ($channels as $channel) {
                $channelList[] = [
                    'id' => $channel['id'],
                    'name' => $channel['name'],
                    'channel_code' => $channel['channel_code']
                ];
            }
            
            return $this->ajaxSuccess('获取渠道列表成功', $channelList);
            
        } catch (\Exception $e) {
            return $this->ajaxError('获取渠道列表失败：' . $e->getMessage());
        }
    }
    
    /**
     * 获取渠道统计数据
     * @return \think\response\Json
     */
    public function getChannelStats()
    {
        try {
            $date_type = input('date_type', 'realtime');
            $startDate = input('start_date', '');
            $endDate = input('end_date', '');
            
            // 如果没有指定日期范围，使用默认范围
            if (empty($startDate) || empty($endDate)) {
                $dateRange = $this->getDateRange($date_type);
                $startDate = $dateRange['start'];
                $endDate = $dateRange['end'];
            }
            
            // 获取所有渠道
            $channels = Db::name('channel_configs')
                ->where('status', 1)
                ->field('id, name, channel_code')
                ->order('is_default desc, id asc')
                ->select();
            
            $channelStats = [];
            
            // 计算每个渠道的统计数据
            foreach ($channels as $channel) {
                $stats = $this->calculateStats($startDate, $endDate, $date_type, $channel['channel_code']);
                $channelStats[] = [
                    'channel_id' => $channel['id'],
                    'channel_name' => $channel['name'],
                    'channel_code' => $channel['channel_code'],
                    'stats' => $stats
                ];
            }
            
            // 计算总计数据
            $totalStats = $this->calculateStats($startDate, $endDate, $date_type, '');
            $channelStats[] = [
                'channel_id' => 0,
                'channel_name' => '总计',
                'channel_code' => '',
                'stats' => $totalStats
            ];
            
            return $this->ajaxSuccess('获取渠道统计数据成功', [
                'channel_stats' => $channelStats,
                'date_range' => [
                    'start' => $startDate,
                    'end' => $endDate,
                    'type' => $date_type
                ]
            ]);
            
        } catch (\Exception $e) {
            return $this->ajaxError('获取渠道统计数据失败：' . $e->getMessage());
        }
    }
    
    /**
     * 根据日期类型获取查询范围
     * @param string $date_type
     * @return array
     */
    private function getDateRange($date_type)
    {
        switch ($date_type) {
            case 'realtime':
                // 当日
                return [
                    'start' => date('Y-m-d 00:00:00'),
                    'end' => date('Y-m-d 23:59:59')
                ];
            case 'yesterday':
                // 昨日
                $yesterday = date('Y-m-d', strtotime('-1 day'));
                return [
                    'start' => $yesterday . ' 00:00:00',
                    'end' => $yesterday . ' 23:59:59'
                ];
            case '7days':
                // 近7日
                return [
                    'start' => date('Y-m-d 00:00:00', strtotime('-6 days')),
                    'end' => date('Y-m-d 23:59:59')
                ];
            case '30days':
                // 近30日
                return [
                    'start' => date('Y-m-d 00:00:00', strtotime('-29 days')),
                    'end' => date('Y-m-d 23:59:59')
                ];
            default:
                return [
                    'start' => date('Y-m-d 00:00:00'),
                    'end' => date('Y-m-d 23:59:59')
                ];
        }
    }
    
    /**
     * 获取对比数据范围
     * @param string $date_type
     * @return array|null
     */
    private function getComparisonRange($date_type)
    {
        switch ($date_type) {
            case 'realtime':
                // 实时模式：对比昨天
                $yesterday = date('Y-m-d', strtotime('-1 day'));
                return [
                    'start' => $yesterday . ' 00:00:00',
                    'end' => $yesterday . ' 23:59:59'
                ];
            case 'yesterday':
                // 昨日模式：对比前天
                $dayBeforeYesterday = date('Y-m-d', strtotime('-2 days'));
                return [
                    'start' => $dayBeforeYesterday . ' 00:00:00',
                    'end' => $dayBeforeYesterday . ' 23:59:59'
                ];
            case '7days':
                // 近7日：对比上一个7日（前7-13天）
                return [
                    'start' => date('Y-m-d 00:00:00', strtotime('-13 days')),
                    'end' => date('Y-m-d 23:59:59', strtotime('-7 days'))
                ];
            case '30days':
                // 近30日：对比上一个30日（前30-59天）
                return [
                    'start' => date('Y-m-d 00:00:00', strtotime('-59 days')),
                    'end' => date('Y-m-d 23:59:59', strtotime('-30 days'))
                ];
            default:
                return null;
        }
    }
    
    /**
     * 计算统计数据
     * @param string $startDate
     * @param string $endDate
     * @param string $date_type
     * @param string $channel_code
     * @return array
     */
    private function calculateStats($startDate, $endDate, $date_type, $channel_code = '')
    {
        // 构建渠道查询条件
        $channelWhere = [];
        if (!empty($channel_code)) {
            $channelWhere['channel_code'] = $channel_code;
        }
        
        // 1. 支付金额（成功支付的金额）- 按订单完成时间统计
        $paymentQuery = Db::name('product_orders')
            ->where('status', 'in', [2, 3]) // 已支付或已完成
            ->where('finished_at', 'between', [$startDate, $endDate]);
        
        if (!empty($channelWhere)) {
            $paymentQuery->where($channelWhere);
        }
        
        $paymentAmount = $paymentQuery->sum('total_price');
        
        // 2. 净支付金额（支付金额-手续费）- 按订单完成时间统计
        $netPaymentQuery = Db::name('product_orders')
            ->where('status', 'in', [2, 3]) // 已支付或已完成
            ->where('finished_at', 'between', [$startDate, $endDate]);
        
        if (!empty($channelWhere)) {
            $netPaymentQuery->where($channelWhere);
        }
        
        $netPaymentResult = $netPaymentQuery->field('SUM(total_price) as total_amount, SUM(IFNULL(fee, 0)) as total_fee')->find();
        
        $netPaymentAmount = (isset($netPaymentResult['total_amount']) ? $netPaymentResult['total_amount'] : 0) - (isset($netPaymentResult['total_fee']) ? $netPaymentResult['total_fee'] : 0);
        
        // 3. 访客数（访问站点的人数统计，基于IP）
        $visitors = $this->getVisitorCount($startDate, $endDate, $channel_code);
        
        // 4. 支付买家数（支付成功的单数，同个IP算一单）
        $payingBuyers = $this->getPayingBuyerCount($startDate, $endDate, $channel_code);
        
        // 5. 支付转化率（支付成功笔数/访客数）
        $conversionRate = $visitors > 0 ? round(($payingBuyers / $visitors) * 100, 2) : 0;
        
        // 6. 退款金额（按退款完成时间统计）
        $refundQuery = Db::name('product_orders')
            ->where('status', 6) // 已退款
            ->where('finished_at', 'between', [$startDate, $endDate]);
        
        if (!empty($channelWhere)) {
            $refundQuery->where($channelWhere);
        }
        
        $refundAmount = $refundQuery->sum('total_price');
        
        // 7. 金额退款率（退款金额/支付金额）
        $refundRate = $paymentAmount > 0 ? round(($refundAmount / $paymentAmount) * 100, 2) : 0;
        
        return [
            'payment_amount' => round($paymentAmount, 2),
            'net_payment_amount' => round($netPaymentAmount, 2),
            'visitors' => $visitors,
            'paying_buyers' => $payingBuyers,
            'conversion_rate' => $conversionRate,
            'refund_amount' => round($refundAmount, 2),
            'refund_rate' => $refundRate
        ];
    }
    
    /**
     * 获取访客数（基于访问记录统计）
     * @param string $startDate
     * @param string $endDate
     * @param string $channel_code
     * @return int
     */
    private function getVisitorCount($startDate, $endDate, $channel_code = '')
    {
        // 构建渠道查询条件
        $channelWhere = [];
        if (!empty($channel_code)) {
            $channelWhere['channel_code'] = $channel_code;
        }
        
        // 1. 统计有UUID的访客数（按UUID去重）
        $uuidVisitors = Db::name('visitor_records')
            ->where('created_at', 'between', [$startDate, $endDate])
            ->where('visitor_uuid', '<>', '');
        
        if (!empty($channelWhere)) {
            $uuidVisitors->where($channelWhere);
        }
        
        $uuidCount = $uuidVisitors->group('visitor_uuid')->count();
        
        // 2. 统计没有UUID但有IP的访客数（按IP去重）
        $ipVisitors = Db::name('visitor_records')
            ->where('created_at', 'between', [$startDate, $endDate])
            ->where('visitor_uuid', '');
        
        if (!empty($channelWhere)) {
            $ipVisitors->where($channelWhere);
        }
        
        $ipCount = $ipVisitors->group('ip_address')->count();
        
        // 3. 返回UUID统计 + IP统计的总和
        return $uuidCount + $ipCount;
    }
    
    /**
     * 获取支付买家数（同个访客成功支付的单数）- 按订单完成时间统计
     * @param string $startDate
     * @param string $endDate
     * @param string $channel_code
     * @return int
     */
    private function getPayingBuyerCount($startDate, $endDate, $channel_code = '')
    {
        // 构建渠道查询条件
        $channelWhere = [];
        if (!empty($channel_code)) {
            $channelWhere['channel_code'] = $channel_code;
        }
        
        // 优先使用UUID统计，如果没有UUID则使用邮箱统计
        $buyerQuery = Db::name('product_orders')
            ->where('status', 'in', [2, 3]) // 已支付或已完成
            ->where('finished_at', 'between', [$startDate, $endDate])
            ->where('visitor_uuid', '<>', '');
        
        if (!empty($channelWhere)) {
            $buyerQuery->where($channelWhere);
        }
        
        $payingBuyers = $buyerQuery->group('visitor_uuid')->count();
        
        // 如果UUID统计为0，则使用邮箱统计作为备选
        if ($payingBuyers == 0) {
            $buyerQuery2 = Db::name('product_orders')
                ->where('status', 'in', [2, 3]) // 已支付或已完成
                ->where('finished_at', 'between', [$startDate, $endDate]);
            
            if (!empty($channelWhere)) {
                $buyerQuery2->where($channelWhere);
            }
            
            $payingBuyers = $buyerQuery2->group('user_email')->count();
        }
        
        return $payingBuyers;
    }
    
    /**
     * 计算同比变化
     * @param array $currentStats
     * @param array $yesterdayStats
     * @return array
     */
    private function calculateComparison($currentStats, $yesterdayStats)
    {
        $comparison = [];
        
        foreach ($currentStats as $key => $currentValue) {
            $yesterdayValue = isset($yesterdayStats[$key]) ? $yesterdayStats[$key] : 0;
            
            if ($yesterdayValue == 0) {
                // 当对比数据为0时，不显示百分比，而是显示特殊标识
                if ($currentValue > 0) {
                    $comparison[$key] = [
                        'value' => '新增',
                        'direction' => 'up'
                    ];
                } else {
                    $comparison[$key] = [
                        'value' => '无变化',
                        'direction' => 'stable'
                    ];
                }
            } else {
                $change = (($currentValue - $yesterdayValue) / $yesterdayValue) * 100;
                $comparison[$key] = [
                    'value' => abs(round($change, 1)),
                    'direction' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'stable')
                ];
            }
        }
        
        return $comparison;
    }
    
    /**
     * 根据类型获取趋势数据
     * @param string $startDate
     * @param string $endDate
     * @param string $date_type
     * @param string $data_type
     * @param string $channel_code
     * @return array
     */
    private function getTrendDataByType($startDate, $endDate, $date_type, $data_type, $channel_code = '')
    {
        switch ($data_type) {
            case 'payment_amount':
                return $this->getPaymentAmountTrend($startDate, $endDate, $date_type, $channel_code);
            case 'net_payment':
                return $this->getNetPaymentTrend($startDate, $endDate, $date_type, $channel_code);
            case 'visitors':
                return $this->getVisitorsTrend($startDate, $endDate, $date_type, $channel_code);
            case 'paying_buyers':
                return $this->getPayingBuyersTrend($startDate, $endDate, $date_type, $channel_code);
            case 'conversion_rate':
                return $this->getConversionRateTrend($startDate, $endDate, $date_type, $channel_code);
            case 'refund_amount':
                return $this->getRefundAmountTrend($startDate, $endDate, $date_type, $channel_code);
            case 'refund_rate':
                return $this->getRefundRateTrend($startDate, $endDate, $date_type, $channel_code);
            default:
                return $this->getPaymentAmountTrend($startDate, $endDate, $date_type, $channel_code);
        }
    }
    
    /**
     * 获取支付金额趋势数据 - 按订单完成时间统计
     * @param string $startDate
     * @param string $endDate
     * @param string $date_type
     * @return array
     */
    private function getPaymentAmountTrend($startDate, $endDate, $date_type, $channel_code = '')
    {
        if ($date_type === 'realtime' || $date_type === 'yesterday') {
            // 按小时统计
            $data = [];
            for ($hour = 0; $hour < 24; $hour++) {
                $hourStart = date('Y-m-d H:00:00', strtotime($startDate) + $hour * 3600);
                $hourEnd = date('Y-m-d H:59:59', strtotime($startDate) + $hour * 3600);
                
                $amount = $this->addChannelFilter(
                    Db::name('product_orders')
                        ->where('status', 'in', [2, 3])
                        ->where('finished_at', 'between', [$hourStart, $hourEnd]),
                    $channel_code
                )->sum('total_price');
                
                $data[] = [
                    'time' => $hour . ':00',
                    'value' => round($amount, 2)
                ];
            }
            return $data;
        } else {
            // 按天统计
            $data = [];
            $currentDate = strtotime($startDate);
            $endTimestamp = strtotime($endDate);
            
            while ($currentDate <= $endTimestamp) {
                $dayStart = date('Y-m-d 00:00:00', $currentDate);
                $dayEnd = date('Y-m-d 23:59:59', $currentDate);
                
                $amount = $this->addChannelFilter(
                    Db::name('product_orders')
                        ->where('status', 'in', [2, 3])
                        ->where('finished_at', 'between', [$dayStart, $dayEnd]),
                    $channel_code
                )->sum('total_price');
                
                $data[] = [
                    'time' => date('m-d', $currentDate),
                    'value' => round($amount, 2)
                ];
                
                $currentDate += 86400; // 加一天
            }
            return $data;
        }
    }
    
    /**
     * 获取净支付金额趋势数据
     * @param string $startDate
     * @param string $endDate
     * @param string $date_type
     * @return array
     */
    private function getNetPaymentTrend($startDate, $endDate, $date_type, $channel_code = '')
    {
        if ($date_type === 'realtime' || $date_type === 'yesterday') {
            // 按小时统计
            $data = [];
            for ($hour = 0; $hour < 24; $hour++) {
                $hourStart = date('Y-m-d H:00:00', strtotime($startDate) + $hour * 3600);
                $hourEnd = date('Y-m-d H:59:59', strtotime($startDate) + $hour * 3600);
                
                $amountResult = Db::name('product_orders')
                    ->where('status', 'in', [2, 3])
                    ->where('finished_at', 'between', [$hourStart, $hourEnd])
                    ->field('SUM(total_price) as total_amount, SUM(IFNULL(fee, 0)) as total_fee')
                    ->find();
                
                $amount = (isset($amountResult['total_amount']) ? $amountResult['total_amount'] : 0) - (isset($amountResult['total_fee']) ? $amountResult['total_fee'] : 0);
                
                $data[] = [
                    'time' => $hour . ':00',
                    'value' => round($amount, 2)
                ];
            }
            return $data;
        } else {
            // 按天统计
            $data = [];
            $currentDate = strtotime($startDate);
            $endTimestamp = strtotime($endDate);
            
            while ($currentDate <= $endTimestamp) {
                $dayStart = date('Y-m-d 00:00:00', $currentDate);
                $dayEnd = date('Y-m-d 23:59:59', $currentDate);
                
                $amountResult = Db::name('product_orders')
                    ->where('status', 'in', [2, 3])
                    ->where('finished_at', 'between', [$dayStart, $dayEnd])
                    ->field('SUM(total_price) as total_amount, SUM(IFNULL(fee, 0)) as total_fee')
                    ->find();
                
                $amount = (isset($amountResult['total_amount']) ? $amountResult['total_amount'] : 0) - (isset($amountResult['total_fee']) ? $amountResult['total_fee'] : 0);
                
                $data[] = [
                    'time' => date('m-d', $currentDate),
                    'value' => round($amount, 2)
                ];
                
                $currentDate += 86400; // 加一天
            }
            return $data;
        }
    }
    
    /**
     * 获取访客数趋势数据
     * @param string $startDate
     * @param string $endDate
     * @param string $date_type
     * @return array
     */
    private function getVisitorsTrend($startDate, $endDate, $date_type, $channel_code = '')
    {
        if ($date_type === 'realtime' || $date_type === 'yesterday') {
            // 按小时统计
            $data = [];
            for ($hour = 0; $hour < 24; $hour++) {
                $hourStart = date('Y-m-d H:00:00', strtotime($startDate) + $hour * 3600);
                $hourEnd = date('Y-m-d H:59:59', strtotime($startDate) + $hour * 3600);
                
                // 统计有UUID的访客数（按UUID去重）
                $uuidCount = $this->addUuidDistinct(
                    $this->addChannelFilter(
                        Db::name('visitor_records')
                            ->where('created_at', 'between', [$hourStart, $hourEnd]),
                        $channel_code
                    ),
                    true
                )->count();
                
                // 统计没有UUID但有IP的访客数（按IP去重）
                $ipCount = $this->addUuidDistinct(
                    $this->addChannelFilter(
                        Db::name('visitor_records')
                            ->where('created_at', 'between', [$hourStart, $hourEnd]),
                        $channel_code
                    ),
                    false
                )->count();
                
                // 合并统计：UUID统计 + IP统计
                $count = $uuidCount + $ipCount;
                
                $data[] = [
                    'time' => $hour . ':00',
                    'value' => $count
                ];
            }
            return $data;
        } else {
            // 按天统计
            $data = [];
            $currentDate = strtotime($startDate);
            $endTimestamp = strtotime($endDate);
            
            while ($currentDate <= $endTimestamp) {
                $dayStart = date('Y-m-d 00:00:00', $currentDate);
                $dayEnd = date('Y-m-d 23:59:59', $currentDate);
                
                // 统计有UUID的访客数（按UUID去重）
                $uuidCount = $this->addUuidDistinct(
                    $this->addChannelFilter(
                        Db::name('visitor_records')
                            ->where('created_at', 'between', [$dayStart, $dayEnd]),
                        $channel_code
                    ),
                    true
                )->count();
                
                // 统计没有UUID但有IP的访客数（按IP去重）
                $ipCount = $this->addUuidDistinct(
                    $this->addChannelFilter(
                        Db::name('visitor_records')
                            ->where('created_at', 'between', [$dayStart, $dayEnd]),
                        $channel_code
                    ),
                    false
                )->count();
                
                // 合并统计：UUID统计 + IP统计
                $count = $uuidCount + $ipCount;
                
                $data[] = [
                    'time' => date('m-d', $currentDate),
                    'value' => $count
                ];
                
                $currentDate += 86400; // 加一天
            }
            return $data;
        }
    }
    
    /**
     * 获取支付买家数趋势数据
     * @param string $startDate
     * @param string $endDate
     * @param string $date_type
     * @return array
     */
    private function getPayingBuyersTrend($startDate, $endDate, $date_type, $channel_code = '')
    {
        if ($date_type === 'realtime' || $date_type === 'yesterday') {
            // 按小时统计
            $data = [];
            for ($hour = 0; $hour < 24; $hour++) {
                $hourStart = date('Y-m-d H:00:00', strtotime($startDate) + $hour * 3600);
                $hourEnd = date('Y-m-d H:59:59', strtotime($startDate) + $hour * 3600);
                
                $count = $this->addChannelFilter(
                    Db::name('product_orders')
                        ->where('status', 'in', [2, 3])
                        ->where('finished_at', 'between', [$hourStart, $hourEnd])
                        ->where('visitor_uuid', '<>', '')
                        ->group('visitor_uuid'),
                    $channel_code
                )->count();
                
                $data[] = [
                    'time' => $hour . ':00',
                    'value' => $count
                ];
            }
            return $data;
        } else {
            // 按天统计
            $data = [];
            $currentDate = strtotime($startDate);
            $endTimestamp = strtotime($endDate);
            
            while ($currentDate <= $endTimestamp) {
                $dayStart = date('Y-m-d 00:00:00', $currentDate);
                $dayEnd = date('Y-m-d 23:59:59', $currentDate);
                
                $count = $this->addChannelFilter(
                    Db::name('product_orders')
                        ->where('status', 'in', [2, 3])
                        ->where('finished_at', 'between', [$dayStart, $dayEnd])
                        ->where('visitor_uuid', '<>', '')
                        ->group('visitor_uuid'),
                    $channel_code
                )->count();
                
                $data[] = [
                    'time' => date('m-d', $currentDate),
                    'value' => $count
                ];
                
                $currentDate += 86400; // 加一天
            }
            return $data;
        }
    }
    
    /**
     * 获取支付转化率趋势数据
     * @param string $startDate
     * @param string $endDate
     * @param string $date_type
     * @return array
     */
    private function getConversionRateTrend($startDate, $endDate, $date_type, $channel_code = '')
    {
        if ($date_type === 'realtime' || $date_type === 'yesterday') {
            // 按小时统计
            $data = [];
            for ($hour = 0; $hour < 24; $hour++) {
                $hourStart = date('Y-m-d H:00:00', strtotime($startDate) + $hour * 3600);
                $hourEnd = date('Y-m-d H:59:59', strtotime($startDate) + $hour * 3600);
                
                // 统计有UUID的访客数（按UUID去重）
                $uuidVisitors = $this->addUuidDistinct(
                    $this->addChannelFilter(
                        Db::name('visitor_records')
                            ->where('created_at', 'between', [$hourStart, $hourEnd]),
                        $channel_code
                    ),
                    true
                )->count();
                
                // 统计没有UUID但有IP的访客数（按IP去重）
                $ipVisitors = $this->addUuidDistinct(
                    $this->addChannelFilter(
                        Db::name('visitor_records')
                            ->where('created_at', 'between', [$hourStart, $hourEnd]),
                        $channel_code
                    ),
                    false
                )->count();
                
                // 合并统计：UUID统计 + IP统计
                $visitors = $uuidVisitors + $ipVisitors;
                
                $payingBuyers = $this->addChannelFilter(
                    Db::name('product_orders')
                        ->where('status', 'in', [2, 3])
                        ->where('finished_at', 'between', [$hourStart, $hourEnd])
                        ->where('visitor_uuid', '<>', '')
                        ->group('visitor_uuid'),
                    $channel_code
                )->count();
                
                // 如果UUID统计为0，则使用邮箱统计作为备选
                if ($payingBuyers == 0) {
                    $payingBuyers = $this->addChannelFilter(
                        Db::name('product_orders')
                            ->where('status', 'in', [2, 3])
                            ->where('finished_at', 'between', [$hourStart, $hourEnd])
                            ->group('user_email'),
                        $channel_code
                    )->count();
                }
                
                $rate = $visitors > 0 ? round(($payingBuyers / $visitors) * 100, 2) : 0;
                
                $data[] = [
                    'time' => $hour . ':00',
                    'value' => $rate
                ];
            }
            return $data;
        } else {
            // 按天统计
            $data = [];
            $currentDate = strtotime($startDate);
            $endTimestamp = strtotime($endDate);
            
            while ($currentDate <= $endTimestamp) {
                $dayStart = date('Y-m-d 00:00:00', $currentDate);
                $dayEnd = date('Y-m-d 23:59:59', $currentDate);
                
                // 统计有UUID的访客数（按UUID去重）
                $uuidVisitors = $this->addUuidDistinct(
                    $this->addChannelFilter(
                        Db::name('visitor_records')
                            ->where('created_at', 'between', [$dayStart, $dayEnd]),
                        $channel_code
                    ),
                    true
                )->count();
                
                // 统计没有UUID但有IP的访客数（按IP去重）
                $ipVisitors = $this->addUuidDistinct(
                    $this->addChannelFilter(
                        Db::name('visitor_records')
                            ->where('created_at', 'between', [$dayStart, $dayEnd]),
                        $channel_code
                    ),
                    false
                )->count();
                
                // 合并统计：UUID统计 + IP统计
                $visitors = $uuidVisitors + $ipVisitors;
                
                $payingBuyers = $this->addChannelFilter(
                    Db::name('product_orders')
                        ->where('status', 'in', [2, 3])
                        ->where('finished_at', 'between', [$dayStart, $dayEnd])
                        ->where('visitor_uuid', '<>', '')
                        ->group('visitor_uuid'),
                    $channel_code
                )->count();
                
                // 如果UUID统计为0，则使用邮箱统计作为备选
                if ($payingBuyers == 0) {
                    $payingBuyers = $this->addChannelFilter(
                        Db::name('product_orders')
                            ->where('status', 'in', [2, 3])
                            ->where('finished_at', 'between', [$dayStart, $dayEnd])
                            ->group('user_email'),
                        $channel_code
                    )->count();
                }
                
                $rate = $visitors > 0 ? round(($payingBuyers / $visitors) * 100, 2) : 0;
                
                $data[] = [
                    'time' => date('m-d', $currentDate),
                    'value' => $rate
                ];
                
                $currentDate += 86400; // 加一天
            }
            return $data;
        }
    }
    
    /**
     * 获取退款金额趋势数据
     * @param string $startDate
     * @param string $endDate
     * @param string $date_type
     * @return array
     */
    private function getRefundAmountTrend($startDate, $endDate, $date_type, $channel_code = '')
    {
        if ($date_type === 'realtime' || $date_type === 'yesterday') {
            // 按小时统计
            $data = [];
            for ($hour = 0; $hour < 24; $hour++) {
                $hourStart = date('Y-m-d H:00:00', strtotime($startDate) + $hour * 3600);
                $hourEnd = date('Y-m-d H:59:59', strtotime($startDate) + $hour * 3600);
                
                $amount = Db::name('product_orders')
                    ->where('status', 6)
                    ->where('finished_at', 'between', [$hourStart, $hourEnd])
                    ->sum('total_price');
                
                $data[] = [
                    'time' => $hour . ':00',
                    'value' => round($amount, 2)
                ];
            }
            return $data;
        } else {
            // 按天统计
            $data = [];
            $currentDate = strtotime($startDate);
            $endTimestamp = strtotime($endDate);
            
            while ($currentDate <= $endTimestamp) {
                $dayStart = date('Y-m-d 00:00:00', $currentDate);
                $dayEnd = date('Y-m-d 23:59:59', $currentDate);
                
                $amount = Db::name('product_orders')
                    ->where('status', 6)
                    ->where('finished_at', 'between', [$dayStart, $dayEnd])
                    ->sum('total_price');
                
                $data[] = [
                    'time' => date('m-d', $currentDate),
                    'value' => round($amount, 2)
                ];
                
                $currentDate += 86400; // 加一天
            }
            return $data;
        }
    }
    
    /**
     * 获取退款率趋势数据
     * @param string $startDate
     * @param string $endDate
     * @param string $date_type
     * @return array
     */
    private function getRefundRateTrend($startDate, $endDate, $date_type, $channel_code = '')
    {
        if ($date_type === 'realtime' || $date_type === 'yesterday') {
            // 按小时统计
            $data = [];
            for ($hour = 0; $hour < 24; $hour++) {
                $hourStart = date('Y-m-d H:00:00', strtotime($startDate) + $hour * 3600);
                $hourEnd = date('Y-m-d H:59:59', strtotime($startDate) + $hour * 3600);
                
                $paymentAmount = Db::name('product_orders')
                    ->where('status', 'in', [2, 3])
                    ->where('finished_at', 'between', [$hourStart, $hourEnd])
                    ->sum('total_price');
                
                $refundAmount = Db::name('product_orders')
                    ->where('status', 6)
                    ->where('finished_at', 'between', [$hourStart, $hourEnd])
                    ->sum('total_price');
                
                $rate = $paymentAmount > 0 ? round(($refundAmount / $paymentAmount) * 100, 2) : 0;
                
                $data[] = [
                    'time' => $hour . ':00',
                    'value' => $rate
                ];
            }
            return $data;
        } else {
            // 按天统计
            $data = [];
            $currentDate = strtotime($startDate);
            $endTimestamp = strtotime($endDate);
            
            while ($currentDate <= $endTimestamp) {
                $dayStart = date('Y-m-d 00:00:00', $currentDate);
                $dayEnd = date('Y-m-d 23:59:59', $currentDate);
                
                $paymentAmount = Db::name('product_orders')
                    ->where('status', 'in', [2, 3])
                    ->where('finished_at', 'between', [$dayStart, $dayEnd])
                    ->sum('total_price');
                
                $refundAmount = Db::name('product_orders')
                    ->where('status', 6)
                    ->where('finished_at', 'between', [$dayStart, $dayEnd])
                    ->sum('total_price');
                
                $rate = $paymentAmount > 0 ? round(($refundAmount / $paymentAmount) * 100, 2) : 0;
                
                $data[] = [
                    'time' => date('m-d', $currentDate),
                    'value' => $rate
                ];
                
                $currentDate += 86400; // 加一天
            }
            return $data;
        }
    }
    
}
