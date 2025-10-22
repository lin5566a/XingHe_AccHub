<?php
namespace app\common\utils;

class IpUtils
{
    /**
     * 获取真实IP地址
     * @return string
     */
    public static function getRealIp()
    {
        // dump($_SERVER);
        
        // 如果REMOTE_ADDR不可用，尝试从代理获取
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            // 取第一个非unknown的IP
            foreach ($ips as $ip) {
                $ip = trim($ip);
                if ($ip != 'unknown' && filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        // 如果代理IP不可用，尝试获取HTTP_CLIENT_IP
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
        
        // 优先获取REMOTE_ADDR，这是最可靠的
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
            // 验证IP格式
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
        
        return '0.0.0.0';
    }
} 