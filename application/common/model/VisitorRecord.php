<?php

namespace app\common\model;

use think\Model;

class VisitorRecord extends Model
{
    protected $table = 'epay_visitor_records';
    
    // 自动时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    
    // 字段类型转换
    protected $type = [
        'is_mobile' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * 记录访问信息
     * @param array $data 访问数据
     * @return bool
     */
    public static function recordVisit($data)
    {
        try {
            $record = new self();
            $result = $record->save($data);
            
            // 记录调试信息
            if ($result) {
                \think\Log::info('访问记录插入成功 - ID: ' . $record->id . ', 数据: ' . json_encode($data, JSON_UNESCAPED_UNICODE));
            } else {
                \think\Log::error('访问记录插入失败 - 数据: ' . json_encode($data, JSON_UNESCAPED_UNICODE) . ', 错误: ' . json_encode($record->getError(), JSON_UNESCAPED_UNICODE));
            }
            
            return $result;
        } catch (\Exception $e) {
            // 记录详细错误信息
            \think\Log::error('访问记录插入异常 - 错误: ' . $e->getMessage() . ', 文件: ' . $e->getFile() . ':' . $e->getLine() . ', 数据: ' . json_encode($data, JSON_UNESCAPED_UNICODE));
            return false;
        }
    }
    
    /**
     * 获取指定时间范围内的唯一访客数
     * @param string $startTime 开始时间
     * @param string $endTime 结束时间
     * @return int
     */
    public static function getUniqueVisitors($startTime, $endTime)
    {
        return self::where('created_at', 'between', [$startTime, $endTime])
            ->group('ip_address')
            ->count();
    }
    
    /**
     * 获取指定时间范围内的唯一访客数（基于UUID）
     * @param string $startTime 开始时间
     * @param string $endTime 结束时间
     * @return int
     */
    public static function getUniqueVisitorsByUuid($startTime, $endTime)
    {
        return self::where('created_at', 'between', [$startTime, $endTime])
            ->where('visitor_uuid', '<>', '')
            ->group('visitor_uuid')
            ->count();
    }
    
    /**
     * 获取指定时间范围内的访客数（按小时）
     * @param string $startTime 开始时间
     * @param string $endTime 结束时间
     * @return array
     */
    public static function getVisitorsByHour($startTime, $endTime)
    {
        $data = [];
        
        // 获取日期
        $date = date('Y-m-d', strtotime($startTime));
        
        for ($hour = 0; $hour < 24; $hour++) {
            $hourStart = $date . ' ' . sprintf('%02d:00:00', $hour);
            $hourEnd = $date . ' ' . sprintf('%02d:59:59', $hour);
            
            $count = self::where('created_at', 'between', [$hourStart, $hourEnd])
                ->group('ip_address')
                ->count();
            
            $data[] = [
                'time' => $hour . ':00',
                'value' => $count
            ];
        }
        
        return $data;
    }
    
    /**
     * 获取指定时间范围内的访客数（按小时，基于UUID）
     * @param string $startTime 开始时间
     * @param string $endTime 结束时间
     * @return array
     */
    public static function getVisitorsByHourUuid($startTime, $endTime)
    {
        $data = [];
        
        // 获取日期
        $date = date('Y-m-d', strtotime($startTime));
        
        for ($hour = 0; $hour < 24; $hour++) {
            $hourStart = $date . ' ' . sprintf('%02d:00:00', $hour);
            $hourEnd = $date . ' ' . sprintf('%02d:59:59', $hour);
            
            $count = self::where('created_at', 'between', [$hourStart, $hourEnd])
                ->where('visitor_uuid', '<>', '')
                ->group('visitor_uuid')
                ->count();
            
            $data[] = [
                'time' => $hour . ':00',
                'value' => $count
            ];
        }
        
        return $data;
    }
    
    /**
     * 获取指定时间范围内的访客数（按天）
     * @param string $startTime 开始时间
     * @param string $endTime 结束时间
     * @return array
     */
    public static function getVisitorsByDay($startTime, $endTime)
    {
        $data = [];
        $currentDate = strtotime($startTime);
        $endTimestamp = strtotime($endTime);
        
        while ($currentDate <= $endTimestamp) {
            $dayStart = date('Y-m-d 00:00:00', $currentDate);
            $dayEnd = date('Y-m-d 23:59:59', $currentDate);
            
            $count = self::where('created_at', 'between', [$dayStart, $dayEnd])
                ->group('ip_address')
                ->count();
            
            $data[] = [
                'time' => date('m-d', $currentDate),
                'value' => $count
            ];
            
            $currentDate += 86400; // 加一天
        }
        
        return $data;
    }
    
    /**
     * 获取指定时间范围内的访客数（按天，基于UUID）
     * @param string $startTime 开始时间
     * @param string $endTime 结束时间
     * @return array
     */
    public static function getVisitorsByDayUuid($startTime, $endTime)
    {
        $data = [];
        $currentDate = strtotime($startTime);
        $endTimestamp = strtotime($endTime);
        
        while ($currentDate <= $endTimestamp) {
            $dayStart = date('Y-m-d 00:00:00', $currentDate);
            $dayEnd = date('Y-m-d 23:59:59', $currentDate);
            
            $count = self::where('created_at', 'between', [$dayStart, $dayEnd])
                ->where('visitor_uuid', '<>', '')
                ->group('visitor_uuid')
                ->count();
            
            $data[] = [
                'time' => date('m-d', $currentDate),
                'value' => $count
            ];
            
            $currentDate += 86400; // 加一天
        }
        
        return $data;
    }
    
    /**
     * 获取访客地理分布统计
     * @param string $startTime 开始时间
     * @param string $endTime 结束时间
     * @return array
     */
    public static function getVisitorGeoStats($startTime, $endTime)
    {
        return self::where('created_at', 'between', [$startTime, $endTime])
            ->group('province, city')
            ->field('province, city, count(DISTINCT ip_address) as visitor_count')
            ->order('visitor_count desc')
            ->select()
            ->toArray();
    }
    
    /**
     * 清理过期访问记录（保留30天）
     * @return int 删除的记录数
     */
    public static function cleanExpiredRecords()
    {
        $expiredTime = date('Y-m-d H:i:s', strtotime('-60 days'));
        return self::where('created_at', '<', $expiredTime)->delete();
    }
}
