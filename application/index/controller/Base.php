<?php
namespace app\index\controller;

use think\Controller;
use think\facade\Log;
use app\admin\model\User as UserModel;
use app\admin\model\UserSession;
use app\common\traits\ApiResponse;
use app\common\service\VisitorTrackingService;

class Base extends Controller
{
    use ApiResponse;
    
    protected $user = null;
    protected $token = null;
    
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
        
        // 记录访问（异步记录，不影响主业务流程）
        $this->recordVisitorAccess();
        
        // 获取token
        // $token = $this->request->header('Authorization');
        // if ($token) {
        //     $this->token = str_replace('Bearer ', '', $token);
        //     $this->checkToken();
        // }
        
    }

    // protected function initialize()
    // {
    //     // 允许跨域请求
    //     $origin = request()->header('Origin');
    //     header('Access-Control-Allow-Origin: ' . $origin);
    //     header('Access-Control-Allow-Credentials: true');
    //     header('Access-Control-Allow-Headers: Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-CSRF-TOKEN, X-Requested-With');
    //     header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        
    //     // 如果是OPTIONS请求，直接返回
    //     if (request()->isOptions()) {
    //         exit;
    //     }
    //     // 获取token
    //     $token = $this->request->header('Authorization');
    //     if ($token) {
    //         $this->token = str_replace('Bearer ', '', $token);
    //         $this->checkToken();
    //     }
    // }

    /**
     * 验证token - 支持多端登录
     */
    protected function checkToken()
    {
        if (!$this->token) {
            return;
        }

        // 从会话表验证token
        $session = UserSession::validateToken($this->token);
        
        if (!$session) {
            return;
        }
        
        // 获取用户信息
        $user = UserModel::where('id', $session->user_id)
            ->where('status', 1)
            ->find();
            
        if (!$user) {
            return;
        }

        // 更新最后活动时间
        UserSession::updateLastActivity($this->token);
        
        $this->user = $user;
    }

    /**
     * 生成token
     */
    protected function generateToken($userId)
    {
        $token = md5($userId . time() . rand(1000, 9999));
        $expireTime = date('Y-m-d H:i:s', strtotime('+2 hours')); // 2小时有效期
        
        UserModel::where('id', $userId)->update([
            'token' => $token,
            'token_expire_time' => $expireTime
        ]);
        
        return $token;
    }

    /**
     * 获取用户信息 - 支持多端登录
     */
    protected function getUserInfo()
    {
        // 从请求头获取token
        $token = $this->request->header('authorization');
        if (empty($token)) {
            return $this->ajaxError('未登录', null, 401);
        }
        
        // 去掉Bearer前缀
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }
        
        // 验证token并获取会话信息
        $session = UserSession::validateToken($token);
        if (!$session) {
            return $this->ajaxError('未登录或登录已过期', null, 401);
        }
        
        // 查询用户信息，包含会员等级
        $user = UserModel::with('memberLevel')->where('id', $session->user_id)
            ->where('status', 1)
            ->find();
            
        if (!$user) {
            return $this->ajaxError('用户不存在或已被禁用', null, 401);
        }
        
        // 更新最后活动时间
        UserSession::updateLastActivity($token);

        // 格式化返回数据
        $result = [
            'id' => intval($user['id']),
            'nickname' => strval($user['nickname']),
            'email' => strval($user['email']),
            'balance' => floatval($user['balance']),
            'total_recharge' => floatval($user['total_recharge']),
            'total_spent' => floatval($user['total_spent']),
            'status' => intval($user['status']),
            'created_at' => $user['created_at']
        ];

        // 添加会员等级信息
        if ($user['membership_level'] > 0 && $user['memberLevel']) {
            $result['member_level'] = [
                'id' => intval($user['memberLevel']['id']),
                'name' => strval($user['memberLevel']['name']),
                'discount' => floatval($user['memberLevel']['discount']),
                'description' => strval($user['memberLevel']['description'])
            ];
        } else {
            $result['member_level'] = null;
        }

        return $result;
    }
    
    /**
     * 记录访客访问
     */
    protected function recordVisitorAccess()
    {
        try {
            // 异步记录访问，不影响主业务流程
            VisitorTrackingService::trackVisit();
        } catch (\Exception $e) {
            // 记录访问失败不应该影响主业务流程
            // 可以记录日志但不抛出异常
        }
    }
} 