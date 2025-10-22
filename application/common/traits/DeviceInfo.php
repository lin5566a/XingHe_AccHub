<?php
namespace app\common\traits;

trait DeviceInfo
{
    /**
     * 获取浏览器信息
     */
    private function getBrowser()
    {
        $userAgent = request()->header('user-agent');
        if (strpos($userAgent, 'Edge') !== false) {
            return 'Edge';
        } elseif (strpos($userAgent, 'Chrome') !== false) {
            return 'Chrome';
        } elseif (strpos($userAgent, 'Firefox') !== false) {
            return 'Firefox';
        } elseif (strpos($userAgent, 'Safari') !== false) {
            return 'Safari';
        } elseif (strpos($userAgent, 'Opera') !== false) {
            return 'Opera';
        } elseif (strpos($userAgent, 'MSIE') !== false || strpos($userAgent, 'Trident/') !== false) {
            return 'IE';
        } else {
            return 'Unknown';
        }
    }
    
    /**
     * 获取操作系统信息
     */
    private function getOS()
    {
        $userAgent = request()->header('user-agent');
        if (strpos($userAgent, 'Windows') !== false) {
            return 'Windows';
        } elseif (strpos($userAgent, 'Mac') !== false) {
            return 'MacOS';
        } elseif (strpos($userAgent, 'Linux') !== false) {
            return 'Linux';
        } elseif (strpos($userAgent, 'Android') !== false) {
            return 'Android';
        } elseif (strpos($userAgent, 'iOS') !== false) {
            return 'iOS';
        } else {
            return 'Unknown';
        }
    }
    
    /**
     * 获取设备类型
     */
    private function getDevice()
    {
        $userAgent = request()->header('user-agent');
        if (strpos($userAgent, 'Mobile') !== false) {
            return 'Mobile';
        } elseif (strpos($userAgent, 'Tablet') !== false) {
            return 'Tablet';
        } else {
            return 'Desktop';
        }
    }
    
    /**
     * 获取IP地理位置
     */
    private function getIpLocation($ip)
    {
        // 内网IP直接返回
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return '内网IP';
        }
        
        try {
            // 设置超时时间
            $opts = array(
                'http' => array(
                    'timeout' => 2 // 2秒超时
                )
            );
            
            // 调用 ipapi.co API
            $url = "http://ip-api.com/json/" . $ip . "?lang=zh-CN";
            $result = @file_get_contents($url, false, stream_context_create($opts));
            
            if ($result === false) {
                return '归属地查询失败';
            }
            
            $result = json_decode($result, true);
            
            if ($result && $result['status'] === 'success') {
                return $result['country'] . ' ' . $result['regionName'] . ' ' . $result['city'] . ' ' . $result['isp'];
            }
            
            return '归属地查询失败';
            
        } catch (\Exception $e) {
            return '归属地查询失败';
        }
    }
} 