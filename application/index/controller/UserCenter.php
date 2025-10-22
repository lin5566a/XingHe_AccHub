<?php
namespace app\index\controller;

use app\admin\model\ProductOrder;
use app\admin\model\User;
use app\admin\model\UserSession;
use app\common\traits\ApiResponse;
use think\Log;
use app\admin\model\MemberLevel;

class UserCenter extends Base
{
    use ApiResponse;

    /**
     * 获取个人信息
     */
    public function getInfo()
    {
        try {
            // 从请求头获取token
            $token = $this->request->header('authorization');
            if (empty($token)) {
                return $this->ajaxError('未登录', null, 401);
            }
            $token = str_replace('Bearer ', '', $token);

            // 验证token并获取会话信息
            $session = UserSession::validateToken($token);
            if (!$session) {
                return $this->ajaxError('未登录或登录已过期', null, 401);
            }

            // 查询用户信息，包含会员等级
            $user = User::with('memberLevel')->where('id', $session->user_id)->find();
            if (!$user) {
                return $this->ajaxError('用户不存在', null, 401);
            }
            
            // 更新最后活动时间
            UserSession::updateLastActivity($token);

            // 格式化返回数据
            $result = [
                'id' => intval($user['id']),
                'email' => strval($user['email']),
                'balance' => floatval($user['balance']),
                'total_recharge' => floatval($user['total_recharge']),
                'total_spent' => floatval($user['total_spent']),
                'status' => intval($user['status']),
                'status_text' => $user->status_text,
                'created_at' => $user['created_at']
            ];

            // 添加会员等级信息
            if ($user['membership_level'] > 0 && $user['memberLevel']) {
                $result['member_level'] = [
                    'id' => intval($user['memberLevel']['id']),
                    'name' => strval($user['memberLevel']['name']),
                    'discount' => $user['membership_level'] == MemberLevel::SUPER_LEVEL_ID && $user['custom_discount'] > 0
                        ? intval($user['custom_discount'])
                        : intval($user['memberLevel']['discount']),
                    'description' => strval($user['memberLevel']['description']),
                    'upgrade_amount' => strval($user['memberLevel']['upgrade_amount'])
                ];
            } else {
                $result['member_level'] = null;
            }

            return $this->ajaxSuccess('获取成功', $result);
            
        } catch (\Exception $e) {
            Log::error('获取个人信息失败：' . $e->getMessage());
            return $this->ajaxError('获取失败');
        }
    }



    /**
     * 修改密码
     */
    public function updatePassword()
    {
        try {
            // 从请求头获取token
            $token = $this->request->header('authorization');
            if (empty($token)) {
                return $this->ajaxError('未登录');
            }
            $token = str_replace('Bearer ', '', $token);

            $data = $this->request->post();
            if (empty($data['old_password']) || empty($data['new_password']) || empty($data['confirm_password'])) {
                return $this->ajaxError('参数不能为空');
            }

            if ($data['new_password'] !== $data['confirm_password']) {
                return $this->ajaxError('两次输入的新密码不一致');
            }

            // 通过新的会话系统验证token并获取用户信息
            $session = \app\admin\model\UserSession::validateToken($token);
            if (!$session) {
                return $this->ajaxError('用户不存在或token已过期');
            }
            
            $user = User::where('id', $session['user_id'])->find();
            if (!$user) {
                return $this->ajaxError('用户不存在');
            }
            
            // 更新会话的最后活动时间
            \app\admin\model\UserSession::updateLastActivity($token);

            // 验证旧密码
            if (md5($data['old_password']) !== $user['password']) {
                return $this->ajaxError('旧密码错误');
            }

            // 更新密码
            $result = User::where('id', $user['id'])->update(['password' => md5($data['new_password'])]);
            if ($result === false) {
                return $this->ajaxError('修改失败');
            }

            return $this->ajaxSuccess('修改成功');
            
        } catch (\Exception $e) {
            Log::error('修改密码失败：' . $e->getMessage());
            return $this->ajaxError('修改失败');
        }
    }

    /**
     * 获取用户订单列表
     */
    public function getOrders()
    {
        try {
            $userInfo = $this->getUserInfo();
            if (!$userInfo) {
                return $this->ajaxError('请先登录');
            }

            // 添加分页参数
            $page = $this->request->param('page/d', 1);
            $limit = $this->request->param('limit/d', 20); // 限制每页20条
            
            // 查询用户的所有订单（添加分页）
            $total = ProductOrder::where('user_email', $userInfo['email'])->count();
            $orders = ProductOrder::where('user_email', $userInfo['email'])
                ->field('id,order_number,product_name,online_price,quantity,total_price,created_at,status,card_info')
                ->order('id desc')
                ->page($page, $limit)
                ->select();

            if (empty($orders)) {
                return $this->ajaxSuccess('暂无订单', [
                    'total' => 0,
                    'list' => [],
                    'page' => $page,
                    'limit' => $limit
                ]);
            }

            // 格式化订单数据
            $result = [];
            foreach ($orders as $order) {
                $orderData = [
                    'id' => intval($order['id']),
                    'order_number' => strval($order['order_number']),
                    'product_name' => strval($order['product_name']),
                    'price' => floatval($order['online_price']),
                    'quantity' => intval($order['quantity']),
                    'total_price' => floatval($order['total_price']),
                    'created_at' => $order['created_at'],
                    'status' => intval($order['status']),
                    'status_text' => $this->getOrderStatusText($order['status'])
                ];

                // 只有已完成的订单才返回卡密信息
                if ($order['status'] == 3 && !empty($order['card_info'])) {
                    $orderData['card_info'] = json_decode($order['card_info'], true);
                }

                $result[] = $orderData;
            }

            return $this->ajaxSuccess('获取成功', [
                'total' => $total,
                'list' => $result,
                'page' => $page,
                'limit' => $limit
            ]);
            
        } catch (\Exception $e) {
            Log::error('获取订单列表失败：' . $e->getMessage());
            return $this->ajaxError('获取失败');
        }
    }

    /**
     * 获取订单状态文本
     */
    private function getOrderStatusText($status)
    {
        $statusMap = [
            1 => '待付款',
            2 => '待发货',
            3 => '已完成',
            4 => '已取消'
        ];
        return isset($statusMap[$status]) ? $statusMap[$status] : '未知状态';
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        try {
            // 从请求头获取token
            $token = $this->request->header('authorization');
            if (empty($token)) {
                return $this->ajaxError('未登录');
            }
            $token = str_replace('Bearer ', '', $token);

            // 通过新的会话系统验证token
            $session = \app\admin\model\UserSession::validateToken($token);
            if (!$session) {
                return $this->ajaxError('用户不存在或token已过期');
            }

            // 使用新的会话系统退出登录
            $result = \app\admin\model\UserSession::invalidateToken($token);
            if (!$result) {
                return $this->ajaxError('退出失败');
            }

            return $this->ajaxSuccess('退出成功');
            
        } catch (\Exception $e) {
            Log::error('退出登录失败：' . $e->getMessage());
            return $this->ajaxError('退出失败');
        }
    }

    /**
     * 查询用户余额
     */
    public function getBalance()
    {
        try {
            // 从header获取token，判断用户是否登录
            $token = $this->request->header('Authorization');
            $balance = 0;

            if ($token) {
                // 如果是 Bearer token，需要去掉 'Bearer ' 前缀
                if (strpos($token, 'Bearer ') === 0) {
                    $token = substr($token, 7);
                }
                
                // 验证token并获取会话信息
                $session = UserSession::validateToken($token);
                if ($session) {
                    // 通过用户ID查询用户信息
                    $user = \app\admin\model\User::where('id', $session->user_id)
                        ->where('status', 1)
                        ->find();
                    
                    if ($user) {
                        $balance = $user['balance'];
                        // 更新最后活动时间
                        UserSession::updateLastActivity($token);
                    }
                }
            }

            return $this->ajaxSuccess('获取成功', [
                'balance' => $balance
            ]);
        } catch (\Exception $e) {
            return $this->ajaxError('获取失败');
        }
    }

    /**
     * 账变记录（余额变动日志）
     */
    public function balanceLog()
    {
        try {
            $userInfo = $this->getUserInfo();
            if (!$userInfo) {
                return $this->ajaxError('请先登录');
            }
            $page = $this->request->param('page/d', 1);
            $limit = $this->request->param('limit/d', 10);
            $type = $this->request->param('type', '');
            $where = ['user_id' => $userInfo['id']];
            if ($type !== '') {
                $where['type'] = $type;
            }
            // 查询总数
            $total = \app\admin\model\UserBalanceLog::where($where)->count();
            // 查询分页数据
            $list = \app\admin\model\UserBalanceLog::where($where)
                ->order('id desc')
                ->page($page, $limit)
                ->select();
            return $this->ajaxSuccess('获取成功', [
                'total' => $total,
                'list' => $list,
                'page' => $page,
                'limit' => $limit
            ]);
        } catch (\Exception $e) {
            return $this->ajaxError('获取失败');
        }
    }

    /**
     * 余额变动类型（供前端筛选项）
     */
    public function balanceLogTypes()
    {
        $types = [
            ['value' => '充值', 'label' => '充值'],
            ['value' => '消费', 'label' => '消费'],
            ['value' => '退款', 'label' => '退款'],
            ['value' => '人工增加', 'label' => '人工增加'],
            ['value' => '人工扣减', 'label' => '人工扣减'],
        ];
        return $this->ajaxSuccess('获取成功', $types);
    }
} 