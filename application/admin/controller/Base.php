<?php
namespace app\admin\controller;
use think\Controller;
use think\Cache;
use app\common\traits\ApiResponse;

class Base extends Controller
{
    use ApiResponse;

    // token过期时间（1个月）
    const TOKEN_EXPIRE_TIME = 7200; // 1天 = 24 * 60 * 60

    protected $adminInfo;

    public function __construct()
    {
        parent::__construct();
        
        // 允许跨域请求
        $origin = request()->header('Origin');
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-CSRF-TOKEN, X-Requested-With, X-Visitor-UUID, X-Channel-Code');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        
        // 如果是OPTIONS请求，直接返回
        if (request()->isOptions()) {
            exit;
        }
        
        // 不需要验证token的方法
        $noNeedToken = [
            'admin/login/login',
            'admin/login/logout',
            'admin/log/download'
        ];
        
        // 当前请求路由
        $route = strtolower($this->request->module() . '/' . $this->request->controller() . '/' . $this->request->action());
        // 验证token
        if (!in_array($route, $noNeedToken)) {
            $this->checkToken();
        }
        
    }

    // protected function initialize()
    // {
    //     // 允许跨域请求
    //     $origin = request()->header('Origin');
    //     header('Access-Control-Allow-Origin: ' . $origin);
    //     header('Access-Control-Allow-Credentials: true');
    //     header('Access-Control-Allow-Headers: Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-CSRF-TOKEN, X-Requested-With');
    //     header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        
        
    //     parent::initialize();
        
    //     // 不需要验证token的方法
    //     $noNeedToken = [
    //         'admin/login/login',
    //         'admin/login/logout'
    //     ];
        
    //     // 当前请求路由
    //     $route = strtolower($this->request->module() . '/' . $this->request->controller() . '/' . $this->request->action());
    //     dump($route);
    //     // 验证token
    //     if (!in_array($route, $noNeedToken)) {
    //         dump(123416546);
    //         $this->checkToken();
    //     }
    // }
    
    /**
     * 验证token
     */
    protected function checkToken()
    {
        $token = $this->request->header('token') ?: $this->request->header('Authorization');
        if (!$token) {
            return $this->ajaxError('token不能为空', [], 401);
        }

        // 如果是 Bearer token，需要去掉 'Bearer ' 前缀
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }
        
        // 验证token
        $manager = model('Manager')->where('token', $token)->find();
        
        if (!$manager) {
            return $this->ajaxError('token不存在', [], 401);
        }

        // 检查token是否过期（超过2小时无操作）
        $lastOperationTime = $manager['token_expire_time'];
        $currentTime = time();
        if ($currentTime - $lastOperationTime > self::TOKEN_EXPIRE_TIME) { // 2小时 = 7200秒
            return $this->ajaxError('token已过期', [], 401);
        }

        if ($manager['status'] != 1) {
            return $this->ajaxError('账户已被禁用', [], 401);
        }
        
        // 更新token过期时间
        model('Manager')->where('id', $manager['id'])->update([
            'token_expire_time' => $currentTime
        ]);
        
        $this->adminInfo = $manager;
    }

    /**
     * 返回JSON数据
     * @param int $code 状态码
     * @param string $msg 提示信息
     * @param array $data 返回数据
     */
    protected function jsonReturn($code, $msg = '', $data = '')
    {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data
        ];
        
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($result));
    }

    /**
     * 统一返回json
     */
    protected function ajaxReturn($data)
    {
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    /**
     * 记录操作日志
     * @param string $type 操作类型
     * @param string $description 操作描述
     * @param string $status 状态：成功|失败
     */
    protected function add_log($type, $description, $status = '成功')
    {
        $token = $this->request->header('authorization');
        
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }
        
        $manager = model('Manager')->where([
            'token' => $token,
            'status' => 1
        ])->find();
        
        $logData = [
            'admin_id' => $manager ? $manager['id'] : 0,
            'operator' => $manager ? $manager['username'] : 'unknown',
            'operation_type' => $type,
            'module' => 'system',
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('user-agent'),
            'device_info' => request()->header('user-agent'), // 使用user-agent作为设备信息
            'operation_description' => $description,
            'status' => $status,
            'operation_time' => date('Y-m-d H:i:s')
        ];

        model('AdminLog')->add($logData);
    }
} 