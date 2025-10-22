<?php
namespace app\index\controller;

use app\admin\model\User as UserModel;
use app\admin\model\UserSession;
use app\index\controller\Base;
use think\Cache;
use think\Controller;
use think\Response; // 引入 Response 类
use think\facade\Log;
use app\common\traits\ApiResponse;
use app\common\service\CaptchaService;
use app\common\utils\Email;
use think\captcha\Captcha;  // 直接使用自动加载的 Captcha 类

class User extends Base
{
    use ApiResponse;

    protected $captchaService;

    protected function _initialize()
    {
        parent::_initialize();
        $this->captchaService = new CaptchaService();
    }

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
    }

    /**
     * 登录 - 支持多端登录
     */
    public function login()
    {
        if ($this->request->isPost()) {
            $data = input('post.');
            
            // 验证数据
            if (empty($data['email'])) {
                return $this->ajaxError('邮箱不能为空');
            }
            if (empty($data['password'])) {
                return $this->ajaxError('密码不能为空');
            }
            if (empty($data['captcha'])) {
                return $this->ajaxError('验证码不能为空');
            }
            
            // 验证验证码
            if (!$this->captchaService->check($data['captcha'], $data['code_token'])) {
                return $this->ajaxError('验证码错误');
            }
            
            // 验证用户
            $user = UserModel::where('email', $data['email'])
                ->where('password', md5($data['password']))
                ->where('status', 1)
                ->find();
                
            if (!$user) {
                return $this->ajaxError('邮箱或密码错误');
            }
            
            // 检查用户是否允许多端登录
            $allowMultiLogin = isset($user->allow_multi_login) ? $user->allow_multi_login : 1;
            
            if (!$allowMultiLogin) {
                // 如果不允许多端登录，先清除所有现有会话
                UserSession::invalidateAllUserSessions($user->id);
            } else {
                // 检查是否超过最大会话数限制
                if (UserSession::isExceedMaxSessions($user->id)) {
                    // 自动清理最旧的会话
                    $maxSessions = isset($user->max_sessions) ? $user->max_sessions : 5;
                    UserSession::cleanOldestSessions($user->id, $maxSessions);
                }
            }
            
            // 生成新的token
            $token = $this->generateToken($user->id);
            
            // 收集设备信息
            $deviceInfo = $this->getDeviceInfo();
            
            // 创建新的会话记录
            $session = UserSession::createSession($user->id, $token, $deviceInfo, 2); // 2小时过期
            
            if (!$session) {
                return $this->ajaxError('登录失败，请稍后重试');
            }
            
            // 更新用户登录信息（保留原有逻辑）
            $user->last_login_time = date('Y-m-d H:i:s');
            $user->last_login_ip = $this->request->ip();
            $user->save();
            
            // 清理过期会话
            UserSession::cleanExpiredSessions();
            
            return $this->ajaxSuccess('登录成功', [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'balance' => $user->balance,
                    'membership_level' => $user->membership_level
                ],
                'session_info' => [
                    'device_type' => $deviceInfo['device_type'],
                    'device_name' => $deviceInfo['device_name'],
                    'login_time' => $session->login_time,
                    'expire_time' => $session->expire_time
                ]
            ]);
        }
        
        return $this->ajaxError('非法请求');
    }

    /**
     * 注册
     */
    public function register()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            
            // 验证数据
            if (empty($data['email'])) {
                return $this->ajaxError('邮箱不能为空');
            }
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return $this->ajaxError('邮箱格式不正确');
            }
            if (empty($data['password'])) {
                return $this->ajaxError('密码不能为空');
            }
            if (strlen($data['password']) < 6) {
                return $this->ajaxError('密码长度不能小于6位');
            }
            if (empty($data['email_captcha'])) {
                return $this->ajaxError('邮箱验证码不能为空');
            }
            if (empty($data['captcha'])) {
                return $this->ajaxError('图形验证码不能为空');
            }
            
            // 验证图形验证码
            if (!$this->captchaService->check($data['captcha'], $data['code_token'])) {
                return $this->ajaxError('图形验证码错误');
            }
            
            // 验证邮箱验证码
            $cacheKey = 'register_captcha_' . $data['email'];
            $cachedCaptcha = Cache::get($cacheKey);
            // 确保验证码比较时类型一致（都转为字符串比较）
            if (!$cachedCaptcha || (string)$cachedCaptcha !== (string)$data['email_captcha']) {
                return $this->ajaxError('邮箱验证码错误或已过期');
            }
            
            // 验证邮箱是否已注册
            if (UserModel::where('email', $data['email'])->find()) {
                return $this->ajaxError('邮箱已被注册');
            }
            
            // 创建用户
            $userModel = new UserModel();
            $result = $userModel->register($data);
            if ($result['code'] === 1) {
                // 删除验证码缓存
                Cache::rm($cacheKey);
                
                // 生成token
                $token = $this->generateToken($result['data']->id);
                return $this->ajaxSuccess('注册成功', ['token' => $token]);
            }
            
            return $this->ajaxError($result['msg']);
        }
        
        return $this->ajaxError('非法请求');
    }

    /**
     * 获取验证码
     */
    public function captcha()
    {
        return $this->ajaxSuccess('获取成功', $this->captchaService->generate());
    }

    /**
     * 发送注册验证码
     */
    public function sendRegisterCaptcha()
    {
        $email = $this->request->post('email');
        if (!$email) {
            return $this->ajaxError('邮箱不能为空');
        }

        // 检查邮箱格式
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->ajaxError('邮箱格式不正确');
        }

        // 检查邮箱是否已注册
        if (UserModel::where('email', $email)->find()) {
            return $this->ajaxError('邮箱已被注册');
        }

        // 检查发送频率限制（1分钟内只能发送一次）
        $rateLimitKey = 'email_rate_limit_' . $email;
        if (Cache::get($rateLimitKey)) {
            return $this->ajaxError('发送过于频繁，请稍后再试');
        }

        // 生成验证码（转为字符串类型）
        $captcha = (string)rand(100000, 999999);
        
        // 保存验证码到缓存（5分钟有效期）
        Cache::set('register_captcha_' . $email, $captcha, 300);
        
        // 设置发送频率限制（1分钟）
        Cache::set($rateLimitKey, 1, 60);

        try {
            // 使用EmailService发送邮件
            $emailService = new \app\common\service\EmailService();
            $result = $emailService->sendRegisterCaptcha($email, $captcha);
            
            if ($result['code'] == 200) {
                return $this->ajaxSuccess('验证码已发送到您的邮箱');
            } else {
                // 发送失败，删除缓存
                Cache::rm('register_captcha_' . $email);
                Cache::rm($rateLimitKey);
                return $this->ajaxError('验证码发送失败：' . $result['message']);
            }
            
        } catch (\Exception $e) {
            // 发送异常，删除缓存
            Cache::rm('register_captcha_' . $email);
            Cache::rm($rateLimitKey);
            Log::error('发送注册验证码失败：' . $e->getMessage());
            return $this->ajaxError('验证码发送失败，请稍后再试');
        }
    }

    /**
     * 发送登录验证码（可选功能）
     */
    public function sendLoginCaptcha()
    {
        $email = $this->request->post('email');
        if (!$email) {
            return $this->ajaxError('邮箱不能为空');
        }

        // 检查邮箱格式
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->ajaxError('邮箱格式不正确');
        }

        // 检查用户是否存在
        $user = UserModel::where('email', $email)->where('status', 1)->find();
        if (!$user) {
            return $this->ajaxError('用户不存在或已被禁用');
        }

        // 检查发送频率限制（1分钟内只能发送一次）
        $rateLimitKey = 'login_email_rate_limit_' . $email;
        if (Cache::get($rateLimitKey)) {
            return $this->ajaxError('发送过于频繁，请稍后再试');
        }

        // 生成验证码（转为字符串类型）
        $captcha = (string)rand(100000, 999999);
        
        // 保存验证码到缓存（5分钟有效期）
        Cache::set('login_captcha_' . $email, $captcha, 300);
        
        // 设置发送频率限制（1分钟）
        Cache::set($rateLimitKey, 1, 60);

        try {
            // 使用EmailService发送邮件
            $emailService = new \app\common\service\EmailService();
            $result = $emailService->sendLoginCaptcha($email, $captcha, $user->nickname);
            
            if ($result['code'] == 200) {
                return $this->ajaxSuccess('验证码已发送到您的邮箱');
            } else {
                // 发送失败，删除缓存
                Cache::rm('login_captcha_' . $email);
                Cache::rm($rateLimitKey);
                return $this->ajaxError('验证码发送失败：' . $result['message']);
            }
            
        } catch (\Exception $e) {
            // 发送异常，删除缓存
            Cache::rm('login_captcha_' . $email);
            Cache::rm($rateLimitKey);
            Log::error('发送登录验证码失败：' . $e->getMessage());
            return $this->ajaxError('验证码发送失败，请稍后再试');
        }
    }

    /**
     * 获取设备信息
     * @return array
     */
    private function getDeviceInfo()
    {
        $userAgent = $this->request->header('user-agent', '');
        $deviceId = $this->request->post('device_id', ''); // 前端可传递设备ID
        
        return [
            'device_id' => $deviceId,
            'device_name' => UserSession::getDeviceName($userAgent),
            'device_type' => UserSession::getDeviceType($userAgent),
            'ip_address' => $this->request->ip(),
            'user_agent' => $userAgent
        ];
    }

    /**
     * 退出登录 - 支持多端登录
     */
    public function logout()
    {
        $token = $this->request->header('authorization');
        if (empty($token)) {
            return $this->ajaxError('未登录');
        }
        
        $token = str_replace('Bearer ', '', $token);
        
        // 使当前会话失效
        $result = UserSession::invalidateSession($token);
        
        if ($result) {
            return $this->ajaxSuccess('退出登录成功');
        } else {
            return $this->ajaxError('退出登录失败');
        }
    }

    /**
     * 退出所有设备登录
     */
    public function logoutAll()
    {
        $token = $this->request->header('authorization');
        if (empty($token)) {
            return $this->ajaxError('未登录');
        }
        
        $token = str_replace('Bearer ', '', $token);
        
        // 先验证当前token获取用户ID
        $session = UserSession::validateToken($token);
        if (!$session) {
            return $this->ajaxError('无效的登录状态');
        }
        
        // 使用户所有会话失效
        $result = UserSession::invalidateAllUserSessions($session->user_id);
        
        if ($result) {
            return $this->ajaxSuccess('已退出所有设备登录');
        } else {
            return $this->ajaxError('退出失败');
        }
    }

    /**
     * 获取在线设备列表
     */
    public function getOnlineDevices()
    {
        $token = $this->request->header('authorization');
        if (empty($token)) {
            return $this->ajaxError('未登录');
        }
        
        $token = str_replace('Bearer ', '', $token);
        
        // 先验证当前token获取用户ID
        $session = UserSession::validateToken($token);
        if (!$session) {
            return $this->ajaxError('无效的登录状态');
        }
        
        // 获取用户所有活跃会话
        $sessions = UserSession::getUserActiveSessions($session->user_id);
        
        // 格式化返回数据
        $devices = [];
        foreach ($sessions as $sessionData) {
            $devices[] = [
                'session_id' => $sessionData['id'],
                'device_name' => $sessionData['device_name'],
                'device_type' => $sessionData['device_type'],
                'ip_address' => $sessionData['ip_address'],
                'login_time' => $sessionData['login_time'],
                'last_activity' => $sessionData['last_activity'],
                'expire_time' => $sessionData['expire_time'],
                'is_current' => $sessionData['token'] === $token // 标记当前设备
            ];
        }
        
        return $this->ajaxSuccess('获取成功', [
            'devices' => $devices,
            'total_count' => count($devices)
        ]);
    }

    /**
     * 踢出指定设备
     */
    public function kickDevice()
    {
        $token = $this->request->header('authorization');
        if (empty($token)) {
            return $this->ajaxError('未登录');
        }
        
        $token = str_replace('Bearer ', '', $token);
        $sessionId = $this->request->post('session_id');
        
        if (empty($sessionId)) {
            return $this->ajaxError('会话ID不能为空');
        }
        
        // 先验证当前token获取用户ID
        $currentSession = UserSession::validateToken($token);
        if (!$currentSession) {
            return $this->ajaxError('无效的登录状态');
        }
        
        // 验证要踢出的会话是否属于当前用户
        $targetSession = UserSession::where('id', $sessionId)
            ->where('user_id', $currentSession->user_id)
            ->where('is_active', 1)
            ->find();
            
        if (!$targetSession) {
            return $this->ajaxError('会话不存在或不属于当前用户');
        }
        
        // 不能踢出自己当前的会话
        if ($targetSession->token === $token) {
            return $this->ajaxError('不能踢出当前设备');
        }
        
        // 使指定会话失效
        $result = UserSession::invalidateSession($targetSession->token);
        
        if ($result) {
            return $this->ajaxSuccess('设备已踢出');
        } else {
            return $this->ajaxError('踢出设备失败');
        }
    }
} 