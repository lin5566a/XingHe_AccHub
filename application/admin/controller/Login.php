<?php
namespace app\admin\controller;
use app\admin\controller\Base;
use think\Cache;
use think\Controller;
use think\Response; // 引入 Response 类
use app\common\traits\ApiResponse;
use app\admin\model\AdminLog as AdminLogModel;
use app\common\service\CaptchaService;
use think\captcha\Captcha;  // 直接使用自动加载的 Captcha 类
use app\common\traits\DeviceInfo;
use think\facade\Db;

class Login extends Controller
{
    use ApiResponse;
    use DeviceInfo;

    protected $adminLogModel;
    protected $captchaService;

    protected function _initialize()
    {
        parent::_initialize();
        $this->adminLogModel = model('AdminLog');
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
     * 添加日志
     * @param string $module 模块名称
     * @param string $description 操作描述
     * @param string $status 状态
     */
    protected function add_log($module, $description, $status, $manager)
    {
        
        $logData = [
            'admin_id' => $manager ? $manager['id'] : 0,
            'operator' => $manager ? $manager['username'] : 'unknown',
            'operation_type' => 'login',
            'module' => $module,
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('user-agent', ''),
            'device_info' => json_encode([
                'browser' => $this->getBrowser(),
                'os' => $this->getOS(),
                'device' => $this->getDevice(),
                'ip' => request()->ip(),
                'location' => $this->getIpLocation(request()->ip())
            ]),
            'operation_description' => $description,
            'status' => $status,
            'operation_time' => date('Y-m-d H:i:s')
        ];
        
        $this->adminLogModel->add($logData);
    }

    /**
     * 生成token
     * @param int $adminId 管理员ID
     * @return string
     */
    private function generateToken($adminId)
    {
        $token = md5($adminId . time() . rand(100000, 999999));
        $expireTime = time(); // 2小时后过期
        
        // 更新数据库中的token
        model('Manager')->where('id', $adminId)->update([
            'token' => $token,
            'token_expire_time' => $expireTime
        ]);
        
        return $token;
    }

    /**
     * 管理员登录
     */
    public function login()
    {
        if (!$this->request->isPost()) {
            return $this->ajaxError('请求方式错误');
        }

        $username = $this->request->post('username');
        $password = $this->request->post('password');
        $captcha = $this->request->post('captcha');
        $code_token = $this->request->post('code_token');

        if (empty($username) || empty($password)) {
            return $this->ajaxError('用户名和密码不能为空');
        }

        // 验证验证码
        if (!$this->captchaService->check($captcha, $code_token)) {
            return $this->ajaxError('验证码错误');
        }

        // 验证用户
        $manager = model('Manager')->where('username', $username)->find();
        if (!$manager) {
            return $this->ajaxError('用户名或密码错误');
        }

        if ($manager['password'] != $password) {
            return $this->ajaxError('用户名或密码错误');
        }

        if ($manager['status'] != 1) {
            return $this->ajaxError('账号已被禁用');
        }

        // 生成token
        $token = $this->generateToken($manager['id']);

        // 更新登录信息
        $manager->save([
            'last_login_time' => date('Y-m-d H:i:s'),
            'last_login_ip' => $this->request->ip()
        ]);

        // 记录登录日志
        $this->add_log('登录', '管理员登录', '成功', $manager);

        return $this->ajaxSuccess('登录成功', [
            'token' => $token,
            'userInfo' => [
                'id' => $manager['id'],
                'username' => $manager['username'],
                'email' => $manager['email'],
                'avatar' => $manager['avatar'],
                'last_login_time' => date('Y-m-d H:i:s'),
                'create_time' => $manager['create_time'],
                'status' => $manager['status']
            ]
        ]);
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        $token = $this->request->header('Authorization');
        if ($token) {
            // 如果是 Bearer token，需要去掉 'Bearer ' 前缀
            if (strpos($token, 'Bearer ') === 0) {
                $token = substr($token, 7);
            }
            
            // 清除数据库中的token
            model('Manager')->where('token', $token)->update([
                'token' => null,
                'token_expire_time' => null
            ]);
        }
        return $this->ajaxSuccess('退出成功');
    }

    /**
     * 获取当前用户登录日志
     */
    public function getLoginLogs()
    {
        $token = $this->request->header('Authorization');
        if (!$token) {
            return $this->ajaxError('请先登录');
        }

        // 如果是 Bearer token，需要去掉 'Bearer ' 前缀
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }
        
        // 从数据库获取管理员信息
        $manager = model('Manager')->where([
            'token' => $token,
            'token_expire_time' => ['>', time()-7200],
            'status' => 1
        ])->find();
        
        if (!$manager) {
            return $this->ajaxError('请先登录');
        }
        
        // 获取分页参数
        $page = input('page/d', 1);
        $limit = input('limit/d', 10);
        
        // 查询条件
        $where = [
            'admin_id' => $manager['id'],
            'operation_type' => 'login',
            'module' => 'admin'
        ];
        
        // 获取总数
        $total = $this->adminLogModel->where($where)->count();
        
        // 获取列表
        $list = $this->adminLogModel->where($where)
            ->field('log_id,operator,ip_address,user_agent,device_info,operation_time,status')
            ->order('log_id desc')
            ->page($page, $limit)
            ->select();
            
        // 处理设备信息
        foreach ($list as &$item) {
            $deviceInfo = json_decode($item['device_info'], true);
            $item['browser'] = isset($deviceInfo['browser']) ? $deviceInfo['browser'] : '';
            $item['os'] = isset($deviceInfo['os']) ? $deviceInfo['os'] : '';
            $item['device'] = isset($deviceInfo['device']) ? $deviceInfo['device'] : '';
            $item['location'] = isset($deviceInfo['location']) ? $deviceInfo['location'] : '';
            unset($item['device_info']);
        }
        
        return $this->ajaxSuccess('获取成功', [
            'total' => $total,
            'list' => $list
        ]);
    }

    /**
     * 生成验证码
     */
    public function captcha()
    {
        // return $this->captchaService->generate();
        return $this->ajaxSuccess('获取成功', $this->captchaService->generate());
    }

    /**
     * 第一步：验证原密码和验证码
     */
    public function verifyPassword()
    {
        $token = $this->request->header('Authorization');
        if (!$token) {
            return $this->ajaxError('请先登录', [], 401);
        }

        // 如果是 Bearer token，需要去掉 'Bearer ' 前缀
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }
        
        // 从数据库获取管理员信息
        $manager = model('Manager')->where([
            'token' => $token,
            'token_expire_time' => ['>', time()-7200],
            'status' => 1
        ])->find();
        
        if (!$manager) {
            return $this->ajaxError('请先登录', [], 401);
        }
        
        $data = input('post.');
        
        // 验证数据
        if (empty($data['old_password'])) {
            return $this->ajaxError('请输入原密码');
        }
        
        if (empty($data['captcha'])) {
            return $this->ajaxError('请输入验证码');
        }
        
        // 验证验证码
        if (!$this->captchaService->check($data['captcha'], $data['code_token'])) {
            return $this->ajaxError('验证码错误');
        }
        
        // 验证原密码
        if ($manager['password'] != md5($data['old_password'])) {
            return $this->ajaxError('原密码错误');
        }
        
        // 生成修改密码的临时token，有效期5分钟
        $tempToken = md5($manager['id'] . time() . rand(100000, 999999));
        $expireTime = time() + 300; // 5分钟后过期
        
        // 更新数据库中的临时token
        model('Manager')->where('id', $manager['id'])->update([
            'temp_token' => $tempToken,
            'temp_token_expire_time' => $expireTime
        ]);
        
        return $this->ajaxSuccess('验证成功', ['token' => $tempToken]);
    }
    
    /**
     * 第二步：修改新密码
     */
    public function changePassword()
    {
        $token = $this->request->header('Authorization');
        if (!$token) {
            return $this->ajaxError('请先登录', [], 401);
        }

        // 如果是 Bearer token，需要去掉 'Bearer ' 前缀
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }
        
        // 从数据库获取管理员信息
        $manager = model('Manager')->where([
            'token' => $token,
            'token_expire_time' => ['>', time()-7200],
            'status' => 1
        ])->find();
        
        
        if (!$manager) {
            return $this->ajaxError('请先登录', [], 401);
        }
        
        $data = input('post.');
        
        // 验证数据
        if (empty($data['token'])) {
            return $this->ajaxError('无效的请求');
        }
        
        if (empty($data['new_password'])) {
            return $this->ajaxError('请输入新密码');
        }
        
        if (empty($data['confirm_password'])) {
            return $this->ajaxError('请确认新密码');
        }
        
        if ($data['new_password'] !== $data['confirm_password']) {
            return $this->ajaxError('两次输入的密码不一致');
        }
        
        if (strlen($data['new_password']) < 6) {
            return $this->ajaxError('新密码长度不能小于6位');
        }
        
        // 验证临时token
        $checkManager = model('Manager')->where([
            'id' => $manager['id'],
            'temp_token' => $data['token'],
            'temp_token_expire_time' => ['>', time()]
        ])->find();
        
        if (!$checkManager) {
            return $this->ajaxError('验证已过期，请重新验证');
        }
        
        // 更新密码
        $manager->password = md5($data['new_password']);
        $manager->save();
        
        // 清除临时token
        model('Manager')->where('id', $manager['id'])->update([
            'temp_token' => null,
            'temp_token_expire_time' => null
        ]);
        
        // 记录操作日志
        $log = new AdminLogModel;
        $log->admin_id = $manager['id'];
        $log->operator = $manager['username'];
        $log->operation_type = 'change_password';
        $log->module = 'admin';
        $log->ip_address = request()->ip();
        $log->user_agent = request()->header('user-agent');
        $log->device_info = json_encode([
            'browser' => $this->getBrowser(),
            'os' => $this->getOS(),
            'device' => $this->getDevice(),
            'ip' => request()->ip(),
            'location' => $this->getIpLocation(request()->ip())
        ]);
        $log->operation_description = '修改登录密码';
        $log->status = '成功';
        $log->operation_time = date('Y-m-d H:i:s');
        $log->save();
        
        return $this->ajaxSuccess('密码修改成功');
    }
} 