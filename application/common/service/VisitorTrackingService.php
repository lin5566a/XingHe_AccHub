<?php

namespace app\common\service;

use app\common\model\VisitorRecord;
use app\common\service\IpLocationService;

class VisitorTrackingService
{
    /**
     * 记录访客访问
     * @param array $requestData 请求数据
     * @return bool
     */
    public static function trackVisit($requestData = [])
    {
        try {
            // 获取客户端IP
            $ipAddress = self::getClientIp();
            
            // 获取访客UUID
            $visitorUuid = self::getVisitorUuid();
            
            // 获取渠道代码
            $channelCode = self::getChannelCode();
            
            // 获取IP地理位置信息
            $locationResult = IpLocationService::getLocationByIp($ipAddress);
            $locationInfo = $locationResult['success'] ? $locationResult['data'] : null;
            
            // 安全获取请求信息
            $userAgent = self::getSafeServerVar('HTTP_USER_AGENT');
            $isMobile = self::isMobileDevice($userAgent);
            
            // 获取来源页面（过滤危险字符）
            $referer = self::getSafeServerVar('HTTP_REFERER');
            $referer = self::sanitizeUrl($referer);
            
            // 获取请求URI（过滤危险字符）
            $requestUri = self::getSafeServerVar('REQUEST_URI');
            $requestUri = self::sanitizeUrl($requestUri);
            
            // 检查是否需要记录（去重逻辑）
            $productId = isset($requestData['product_id']) ? (int)$requestData['product_id'] : null;
            if (!self::shouldRecordVisit($visitorUuid, $ipAddress, $channelCode, $requestUri, $productId)) {
                return true; // 跳过记录，但不影响业务流程
            }
            
            // 获取请求方法（限制为安全的方法）
            $requestMethod = self::getSafeServerVar('REQUEST_METHOD');
            $requestMethod = in_array($requestMethod, ['GET', 'POST', 'PUT', 'DELETE']) ? $requestMethod : 'GET';
            
            // 获取会话ID
            $sessionId = session_id();
            
            // 获取页面类型
            $pageType = self::getPageType($requestUri);
            
            // 准备记录数据
            $recordData = [
                'ip_address' => $ipAddress,
                'visitor_uuid' => $visitorUuid,
                'channel_code' => $channelCode,
                'user_agent' => $userAgent,
                'referer' => $referer,
                'request_uri' => $requestUri,
                'request_method' => $requestMethod,
                'session_id' => $sessionId,
                'is_mobile' => $isMobile ? 1 : 0, // 确保是整数
                'page_type' => $pageType,
                'country' => $locationInfo ? $locationInfo['country'] : '',
                'province' => $locationInfo ? $locationInfo['province'] : '',
                'city' => $locationInfo ? $locationInfo['city'] : '',
                'created_at' => date('Y-m-d H:i:s'), // 确保有时间戳
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // 合并传入的额外数据
            if (!empty($requestData)) {
                // 确保product_id是整数类型
                if (isset($requestData['product_id'])) {
                    $requestData['product_id'] = (int)$requestData['product_id'];
                }
                $recordData = array_merge($recordData, $requestData);
            }
            
            // 记录访问
            return VisitorRecord::recordVisit($recordData);
            
        } catch (\Exception $e) {
            // 记录访问失败不应该影响主业务流程
            return false;
        }
    }
    
    /**
     * 获取客户端真实IP地址
     * @return string
     */
    private static function getClientIp()
    {
        // 检查代理IP
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
        
        // 检查其他代理头
        if (isset($_SERVER['HTTP_X_REAL_IP']) && !empty($_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
        
        if (isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
        
        // 最后使用REMOTE_ADDR
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
    }
    
    /**
     * 检测是否为移动设备
     * @param string $userAgent
     * @return bool
     */
    private static function isMobileDevice($userAgent)
    {
        $mobileKeywords = [
            'Mobile', 'Android', 'iPhone', 'iPad', 'iPod', 
            'BlackBerry', 'Windows Phone', 'Opera Mini', 'IEMobile'
        ];
        
        foreach ($mobileKeywords as $keyword) {
            if (stripos($userAgent, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 获取访客统计信息
     * @param string $startTime 开始时间
     * @param string $endTime 结束时间
     * @return array
     */
    public static function getVisitorStats($startTime, $endTime)
    {
        return [
            'unique_visitors' => VisitorRecord::getUniqueVisitors($startTime, $endTime),
            'geo_distribution' => VisitorRecord::getVisitorGeoStats($startTime, $endTime)
        ];
    }
    
    /**
     * 获取访客趋势数据
     * @param string $startTime 开始时间
     * @param string $endTime 结束时间
     * @param string $dateType 日期类型
     * @return array
     */
    public static function getVisitorTrendData($startTime, $endTime, $dateType)
    {
        if ($dateType === 'realtime' || $dateType === 'yesterday') {
            // 按小时统计
            return VisitorRecord::getVisitorsByHour($startTime, $endTime);
        } else {
            // 按天统计
            return VisitorRecord::getVisitorsByDay($startTime, $endTime);
        }
    }
    
    /**
     * 追踪商品浏览
     * @param int $productId 商品ID
     * @param string $productName 商品名称
     * @param string $viewType 浏览类型：page=页面浏览，detail=详情浏览
     * @return bool
     */
    public static function trackProductView($productId, $productName = '', $viewType = 'detail')
    {
        try {
            // 获取基础访问数据
            $requestData = [
                'product_id' => $productId,
                'product_name' => $productName,
                'view_type' => $viewType
            ];
            
            // 调用通用追踪方法
            return self::trackVisit($requestData);
            
        } catch (\Exception $e) {
            // 记录错误日志，但不影响主流程
            \think\Log::error('商品浏览追踪失败：' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 清理过期访问记录
     * @return int 删除的记录数
     */
    public static function cleanExpiredRecords()
    {
        return VisitorRecord::cleanExpiredRecords();
    }
    
    /**
     * 获取访客UUID
     * @return string
     */
    private static function getVisitorUuid()
    {
        // 从请求头获取UUID
        $uuid = isset($_SERVER['HTTP_X_VISITOR_UUID']) ? $_SERVER['HTTP_X_VISITOR_UUID'] : '';
        
        // 验证UUID格式
        if (self::isValidUuid($uuid)) {
            return $uuid;
        }
        
        // 如果UUID无效，返回空字符串
        return '';
    }
    
    /**
     * 验证UUID格式
     * @param string $uuid
     * @return bool
     */
    private static function isValidUuid($uuid)
    {
        if (empty($uuid)) {
            return false;
        }
        
        // UUID v4格式验证：xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
        return preg_match($pattern, $uuid) === 1;
    }
    
    /**
     * 获取渠道代码
     * @return string
     */
    private static function getChannelCode()
    {
        // 优先从header获取渠道代码
        $channelCode = isset($_SERVER['HTTP_X_CHANNEL_CODE']) ? $_SERVER['HTTP_X_CHANNEL_CODE'] : '';
        
        // 如果header中没有，则从URL参数获取
        if (empty($channelCode)) {
            $channelCode = isset($_GET['channel']) ? $_GET['channel'] : '';
        }
        
        // 验证渠道代码格式
        // 支持格式：default 或 CH_XXXXXXXX（CH_ + 8位字母数字）
        if (preg_match('/^(default|CH_[A-Z0-9]{8})$/', $channelCode)) {
            // 验证渠道代码是否在数据库中存在
            if (self::isValidChannelCode($channelCode)) {
                return $channelCode;
            }
        }
        
        // 如果渠道代码无效或不存在，返回空字符串（将记录到默认渠道）
        return '';
    }
    
    /**
     * 验证渠道代码是否在数据库中存在
     * @param string $channelCode 渠道代码
     * @return bool
     */
    private static function isValidChannelCode($channelCode)
    {
        try {
            // 检查渠道是否存在于数据库中且状态为启用
            $channel = \think\Db::name('channel_configs')
                ->where('channel_code', $channelCode)
                ->where('status', 1)
                ->find();
            
            return $channel ? true : false;
        } catch (\Exception $e) {
            // 如果数据库查询失败，记录错误但不影响主流程
            \think\Log::error('渠道代码验证失败：' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 检查是否需要记录访问（去重逻辑）
     * @param string $visitorUuid 访客UUID
     * @param string $ipAddress IP地址
     * @param string $channelCode 渠道代码
     * @param string $requestUri 请求URI
     * @param int|null $productId 商品ID（用于商品详情页精确去重）
     * @return bool 是否需要记录
     */
    private static function shouldRecordVisit($visitorUuid, $ipAddress, $channelCode = '', $requestUri = '', $productId = null)
    {
        try {
            // 获取页面类型
            $pageType = self::getPageType($requestUri);
            
            // 不同页面类型的去重策略
            $interval = self::getDeduplicationInterval($pageType);
            $timeThreshold = time() - $interval;
            $timeStr = date('Y-m-d H:i:s', $timeThreshold);
            
            // 构建查询条件：访客标识 + 渠道代码 + 页面类型 + 时间范围
            $query = \think\Db::name('visitor_records')
                ->where('created_at', '>', $timeStr);
            
            // 添加访客标识条件
            if (!empty($visitorUuid)) {
                $query->where('visitor_uuid', $visitorUuid);
            } else {
                $query->where('ip_address', $ipAddress);
            }
            
            // 添加渠道代码条件
            if (!empty($channelCode)) {
                $query->where('channel_code', $channelCode);
            } else {
                $query->where(function($q) {
                    $q->where('channel_code', '')
                      ->whereOr('channel_code', 'default')
                      ->whereOr('channel_code', null);
                });
            }
            
            // 添加页面类型条件
            $query->where('page_type', $pageType);
            
            // 对于商品详情页，添加商品ID条件进行精确去重
            if ($pageType === 'product_detail' && !empty($productId)) {
                $query->where('product_id', $productId);
            }
            
            $lastRecord = $query->order('created_at desc')->find();
            
            // 如果没有最近记录则返回true（需要记录）
            return empty($lastRecord);
            
        } catch (\Exception $e) {
            // 如果检查失败，默认记录（保证不丢失数据）
            \think\Log::error('访问记录去重检查失败：' . $e->getMessage());
            return true;
        }
    }
    
    /**
     * 获取页面类型
     * @param string $requestUri 请求URI
     * @return string 页面类型
     */
    private static function getPageType($requestUri)
    {
        if (empty($requestUri)) {
            return 'unknown';
        }
        
        // 商品详情页
        if (strpos($requestUri, '/product/detail') !== false) {
            return 'product_detail';
        }
        
        // 其他商品页面（如商品列表）
        if (strpos($requestUri, '/product/') !== false) {
            return 'product';
        }
        
        // 首页
        if ($requestUri === '/' || 
            strpos($requestUri, '/index') !== false ||
            strpos($requestUri, '/home') !== false) {
            return 'homepage';
        }
        
        // 用户中心
        if (strpos($requestUri, '/user') !== false ||
            strpos($requestUri, '/profile') !== false) {
            return 'user_center';
        }
        
        // 订单相关
        if (strpos($requestUri, '/order') !== false) {
            return 'order';
        }
        
        // 支付相关
        if (strpos($requestUri, '/payment') !== false ||
            strpos($requestUri, '/pay') !== false) {
            return 'payment';
        }
        
        // API接口
        if (strpos($requestUri, '/api') !== false) {
            return 'api';
        }
        
        // 其他页面
        return 'other';
    }
    
    /**
     * 获取去重时间间隔
     * @param string $pageType 页面类型
     * @return int 时间间隔（秒）
     */
    private static function getDeduplicationInterval($pageType)
    {
        switch ($pageType) {
            case 'product_detail':
                // 商品详情页：5分钟去重（通过product_id字段精确去重）
                return 5 * 60;
            case 'product':
                // 其他商品页面：10分钟去重
                return 10 * 60;
            case 'homepage':
                // 首页：30分钟去重（减少重复访问记录）
                return 30 * 60;
            case 'user_center':
                // 用户中心：10分钟去重
                return 10 * 60;
            case 'order':
                // 订单页面：15分钟去重
                return 15 * 60;
            case 'payment':
                // 支付页面：不去重（每次支付都需要记录）
                return 0;
            case 'api':
                // API接口：60分钟去重（减少接口调用记录）
                return 60 * 60;
            default:
                // 其他页面：20分钟去重
                return 20 * 60;
        }
    }
    
    /**
     * 安全获取服务器变量
     * @param string $key
     * @return string
     */
    private static function getSafeServerVar($key)
    {
        if (!isset($_SERVER[$key])) {
            return '';
        }
        
        $value = $_SERVER[$key];
        // 过滤危险字符
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        // 限制长度
        $value = substr($value, 0, 1000);
        
        return $value;
    }
    
    /**
     * 清理URL，移除危险字符
     * @param string $url
     * @return string
     */
    private static function sanitizeUrl($url)
    {
        if (empty($url)) {
            return '';
        }
        
        // 移除危险字符
        $url = preg_replace('/[<>"\']/', '', $url);
        // 限制长度
        $url = substr($url, 0, 500);
        
        return $url;
    }
}
