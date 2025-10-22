<?php
namespace app\common\service;

// 不需要 use 语句，直接使用全局类

class IpLocationService
{
    private static $searcher = null;
    
    /**
     * 获取IP2Region实例
     */
    private static function getSearcher()
    {
        if (self::$searcher === null) {
            // 引入 Ip2Region 类
            require_once EXTEND_PATH . 'ip2region/Ip2Region.php';
            self::$searcher = new \Ip2Region();
        }
        return self::$searcher;
    }
    
    /**
     * 根据IP地址获取地理位置信息
     * @param string $ip IP地址
     * @return array 包含国家、省份、城市等信息
     */
    public static function getLocationByIp($ip)
    {
        try {
            // 验证IP格式
            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                return [
                    'success' => false,
                    'message' => '无效的IP地址',
                    'data' => null
                ];
            }
            
            // 获取IP查询实例
            $searcher = self::getSearcher();
            // 查询IP地址
            $result = $searcher->btreeSearch($ip);
            
            if (!$result || !isset($result['region'])) {
                return [
                    'success' => false,
                    'message' => '无法查询到IP地址信息',
                    'data' => null
                ];
            }
            
            // 解析结果，格式：国家|区域|省份|城市|ISP
            $parts = explode('|', $result['region']);
            
            $location = [
                'country' => isset($parts[0]) ? $parts[0] : '',
                'region' => isset($parts[1]) ? $parts[1] : '',
                'province' => isset($parts[1]) ? $parts[1] : '',
                'city' => isset($parts[2]) ? $parts[2] : '',
                'isp' => isset($parts[3]) ? $parts[3] : '',
                'raw' => $result['region']
            ];
            
            // 处理中国的省份和城市
            if ($location['country'] === '中国') {
                // 处理直辖市
                if (in_array($location['province'], ['北京市', '上海市', '天津市', '重庆市'])) {
                    $location['city'] = $location['province'];
                }
                
                // 处理省份名称，去掉"省"、"市"等后缀
                $location['province_clean'] = str_replace(['省', '市', '自治区', '特别行政区'], '', $location['province']);
                $location['city_clean'] = str_replace(['市', '区', '县'], '', $location['city']);
                
                // 生成完整地址
                $location['full_address'] = $location['province'] . '/' . $location['city'];
                $location['full_address_clean'] = $location['province_clean'] . '/' . $location['city_clean'];
            } else {
                // 处理其他国家，生成通用的地址格式
                $location['province_clean'] = $location['province'];
                $location['city_clean'] = $location['city'];
                
                // 生成完整地址
                if (!empty($location['province']) && !empty($location['city'])) {
                    $location['full_address'] = $location['province'] . '/' . $location['city'];
                    $location['full_address_clean'] = $location['province'] . '/' . $location['city'];
                } elseif (!empty($location['city'])) {
                    $location['full_address'] = $location['city'];
                    $location['full_address_clean'] = $location['city'];
                } else {
                    $location['full_address'] = $location['country'];
                    $location['full_address_clean'] = $location['country'];
                }
            }
            
            return [
                'success' => true,
                'message' => '查询成功',
                'data' => $location
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'IP地址查询失败：' . $e->getMessage(),
                'data' => null
            ];
        }
    }
    
    /**
     * 检查IP地址是否在禁止的城市列表中
     * @param string $ip IP地址
     * @param array $disallowedCities 禁止的城市列表
     * @return array 检查结果
     */
    public static function checkIpRestriction($ip, $disallowedCities = [])
    {
        if (empty($disallowedCities)) {
            return [
                'restricted' => false,
                'reason' => '',
                'location' => null
            ];
        }
        
        // 获取IP地址位置信息
        $locationResult = self::getLocationByIp($ip);
        
        if (!$locationResult['success']) {
            return [
                'restricted' => false,
                'reason' => 'IP地址查询失败，允许访问',
                'location' => null
            ];
        }
        
        $location = $locationResult['data'];
        
        // 检查是否在禁止列表中
        foreach ($disallowedCities as $disallowedCity) {
            // 支持多种格式匹配
            if ($location['full_address'] === $disallowedCity ||
                $location['full_address_clean'] === $disallowedCity ||
                $location['city'] === $disallowedCity ||
                strpos($location['full_address'], $disallowedCity) !== false) {
                
                return [
                    'restricted' => true,
                    'reason' => '您所在的地区暂不支持下载',
                    'location' => $location
                ];
            }
        }
        
        return [
            'restricted' => false,
            'reason' => '',
            'location' => $location
        ];
    }
    
    /**
     * 获取客户端真实IP地址
     * @return string IP地址
     */
    public static function getClientIp()
    {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                
                // 处理多个IP的情况，取第一个
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                // 验证IP格式
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        // 如果都获取不到，返回默认IP
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
    }
}
