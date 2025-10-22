<?php
namespace app\admin\controller;

use app\admin\model\User as UserModel;
use think\Db;
use app\admin\model\MemberLevel;

class User extends Base
{
    /**
     * 获取用户列表
     */
    public function index()
    {
        $where = [];
        
        // 邮箱筛选
        $email = input('email/s', '');
        if ($email !== '') {
            $where['email'] = ['like', "%{$email}%"];
        }
        
        // 昵称筛选
        $nickname = input('nickname/s', '');
        if ($nickname !== '') {
            $where['nickname'] = ['like', "%{$nickname}%"];
        }
        
        // VIP等级筛选
        $membership_level = input('membership_level/d', '');
        if ($membership_level !== '') {
            $where['membership_level'] = $membership_level;
        }
        
        // 状态筛选
        $status = input('status/d', '');
        if ($status !== '') {
            $where['status'] = $status;
        }
        
        // 时间范围筛选
        $start_time = input('start_time/s', '');
        $end_time = input('end_time/s', '');
        
        if ($start_time && $end_time) {
            $where['created_at'] = ['between', [$start_time . ' 00:00:00', $end_time . ' 23:59:59']];
        } elseif ($start_time) {
            $where['created_at'] = ['>=', $start_time . ' 00:00:00'];
        } elseif ($end_time) {
            $where['created_at'] = ['<=', $end_time . ' 23:59:59'];
        }
        
        // 获取总数
        $total = UserModel::where($where)->count();
        
        // 分页查询
        $page = input('page/d', 1);
        $limit = input('limit/d', 10);
        
        // 获取列表数据，with会员等级
        $list = UserModel::where($where)
            ->with(['memberLevel'])
            ->order('id desc')
            ->page($page, $limit)
            ->select();
            
        // 处理脱敏密码、VIP等级名称
        foreach ($list as &$item) {
            $item['password'] = '******'; // 脱敏
            $item['vip_level_name'] = isset($item['memberLevel']['name']) ? $item['memberLevel']['name'] : '';
            // 添加折扣显示
            if ($item['membership_level'] == MemberLevel::SUPER_LEVEL_ID && $item['custom_discount'] > 0) {
                $item['discount'] = $item['custom_discount'];
            } else {
                $item['discount'] = isset($item['memberLevel']['discount']) ? $item['memberLevel']['discount'] : 100;
            }
        }
        
        return $this->ajaxSuccess('获取成功', [
            'total' => $total,
            'list' => $list
        ]);
    }
    
    /**
     * 新增用户
     */
    public function add()
    {
        if (!$this->request->isPost()) {
            return $this->ajaxError('请求方式错误');
        }
        
        $data = [
            'email' => input('email/s', ''),
            'nickname' => input('nickname/s', ''),
            'password' => input('password/s', ''),
            'confirm_password' => input('confirm_password/s', ''),
            'status' => input('status/d', UserModel::STATUS_NORMAL),
            'membership_level' => input('membership_level/d', 0),
            'custom_discount' => round(input('custom_discount/f', 0), 2)
        ];
        
        // 验证
        if (empty($data['email'])) {
            return $this->ajaxError('邮箱不能为空');
        }
        
        if (empty($data['password'])) {
            return $this->ajaxError('密码不能为空');
        }
        
        if (empty($data['confirm_password'])) {
            return $this->ajaxError('确认密码不能为空');
        }
        
        // 验证两次密码是否一致
        if ($data['password'] !== $data['confirm_password']) {
            return $this->ajaxError('两次密码不一致');
        }
        
        // 验证邮箱格式
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->ajaxError('邮箱格式不正确');
        }
        
        // 检查邮箱是否已存在
        if (UserModel::where('email', $data['email'])->find()) {
            return $this->ajaxError('邮箱已存在');
        }
        
        
        // 验证状态值
        if (!in_array($data['status'], [UserModel::STATUS_NORMAL, UserModel::STATUS_DISABLED])) {
            return $this->ajaxError('状态值错误');
        }

        // 验证自定义折扣（仅当会员等级为超级会员时）
        if ($data['membership_level'] == MemberLevel::SUPER_LEVEL_ID) {
            if ($data['custom_discount'] < 0.01 || $data['custom_discount'] > 100) {
                return $this->ajaxError('自定义折扣必须为0.01-100之间，允许两位小数');
            }
            // 校验只能两位小数
            if (floor($data['custom_discount'] * 100) != $data['custom_discount'] * 100) {
                return $this->ajaxError('自定义折扣最多只能有两位小数');
            }
        } else {
            $data['custom_discount'] = 0;
        }
        
        // 处理数据
        unset($data['confirm_password']); // 移除确认密码字段
        
        // 初始化其他字段
        $data['balance'] = 0;
        $data['total_recharge'] = 0;
        $data['total_spent'] = 0;
        
        if ($user = UserModel::create($data)) {
            $this->add_log('用户管理', '新增用户：' . $data['email'], '成功');
            return $this->ajaxSuccess('添加成功', $user);
        }
        
        $this->add_log('用户管理', '新增用户：' . $data['email'], '失败');
        return $this->ajaxError('添加失败');
    }
    
    /**
     * 编辑用户
     */
    public function edit()
    {
        if (!$this->request->isPost()) {
            return $this->ajaxError('请求方式错误');
        }
        
        $id = input('id/d', 0);
        if (!$id) {
            return $this->ajaxError('参数错误');
        }
        
        $user = UserModel::find($id);
        if (!$user) {
            return $this->ajaxError('用户不存在');
        }
        
        $data = [
            'email' => input('email/s', ''),
            'nickname' => input('nickname/s', ''),
            'status' => input('status/d', UserModel::STATUS_NORMAL),
            'membership_level' => input('membership_level/d', $user['membership_level']),
            'custom_discount' => round(input('custom_discount/f', $user['custom_discount']), 2)
        ];
        
        // 验证
        if (empty($data['email'])) {
            return $this->ajaxError('邮箱不能为空');
        }
        
        // 验证邮箱格式
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->ajaxError('邮箱格式不正确');
        }
        
        // 检查邮箱是否已存在(排除自身)
        if (UserModel::where('email', $data['email'])->where('id', '<>', $id)->find()) {
            return $this->ajaxError('邮箱已存在');
        }
        
        
        // 验证状态值
        if (!in_array($data['status'], [UserModel::STATUS_NORMAL, UserModel::STATUS_DISABLED])) {
            return $this->ajaxError('状态值错误');
        }

        // 验证自定义折扣（仅当会员等级为超级会员时）
        if ($data['membership_level'] == MemberLevel::SUPER_LEVEL_ID) {
            if ($data['custom_discount'] < 0.01 || $data['custom_discount'] > 100) {
                return $this->ajaxError('自定义折扣必须为0.01-100之间，允许两位小数');
            }
            if (floor($data['custom_discount'] * 100) != $data['custom_discount'] * 100) {
                return $this->ajaxError('自定义折扣最多只能有两位小数');
            }
        } else {
            $data['custom_discount'] = 0;
        }
        
        if ($user->save($data) !== false) {
            $this->add_log('用户管理', '更新用户：' . $user['email'], '成功');
            return $this->ajaxSuccess('更新成功');
        }
        
        $this->add_log('用户管理', '更新用户：' . $user['email'], '失败');
        return $this->ajaxError('更新失败');
    }
    
    /**
     * 删除用户
     */
    public function delete()
    {
        if (!$this->request->isPost()) {
            return $this->ajaxError('请求方式错误');
        }
        
        $id = input('id/d', 0);
        if (!$id) {
            return $this->ajaxError('参数错误');
        }
        
        $user = UserModel::find($id);
        if (!$user) {
            return $this->ajaxError('用户不存在');
        }
        
        if ($user->delete()) {
            $this->add_log('用户管理', '删除用户：' . $user['email'], '成功');
            return $this->ajaxSuccess('删除成功');
        }
        
        $this->add_log('用户管理', '删除用户：' . $user['email'], '失败');
        return $this->ajaxError('删除失败');
    }

    /**
     * 余额操作（人工增加/扣减）
     */
    public function balanceOperate()
    {
        if (!$this->request->isPost()) {
            return $this->ajaxError('请求方式错误');
        }
        $user_id = input('user_id/d', 0);
        $type = input('type/s', ''); // add/reduce
        $amount = floatval(input('amount', 0));
        $remark = input('remark/s', '');
        if (!$user_id || !in_array($type, ['add', 'reduce']) || $amount <= 0) {
            return $this->ajaxError('参数错误');
        }
        $user = UserModel::find($user_id);
        if (!$user) {
            return $this->ajaxError('用户不存在');
        }
        // 操作人
        $token = $this->request->header('authorization');
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }
        $manager = model('Manager')->where(['token' => $token, 'status' => 1])->find();
        $operator = $manager ? $manager['username'] : 'unknown';
        // 余额变动
        if ($type == 'add') {
            $before = $user['balance'];
            $res = UserModel::where('id', $user_id)
                ->update([
                    'balance' => Db::raw('balance + ' . $amount),
                    'total_recharge' => Db::raw('total_recharge + ' . $amount)  // 同时增加累计充值额度
                ]);
            // 自动调整VIP等级
            UserModel::autoUpdateVipLevel($user_id);
            $after = $before + $amount;
            $logType = '人工增加';
            $direction = 'in';
        } else {
            $before = $user['balance'];
            // 先判断余额是否足够
            $currentBalance = UserModel::where('id', $user_id)->value('balance');
            if ($currentBalance < $amount) {
                return $this->ajaxError('余额不足');
            }
            // 同时扣减余额和累计充值额度
            $res = UserModel::where('id', $user_id)
                ->where('balance', '>=', $amount)
                ->update([
                    'balance' => Db::raw('balance - ' . $amount),
                    'total_recharge' => Db::raw('total_recharge - ' . $amount)  // 同时扣减累计充值额度
                ]);
            // 自动调整VIP等级
            UserModel::autoUpdateVipLevel($user_id);
            $after = $before - $amount;
            $logType = '人工扣减';
            $direction = 'out';
        }
        // 写入日志
        \app\admin\model\UserBalanceLog::create([
            'user_id' => $user_id,
            'type' => $logType,
            'amount' => $amount,
            'before_balance' => $before,
            'after_balance' => $after,
            'direction' => $direction,
            'order_no' => '',
            'remark' => $remark,
            'operator' => $operator
        ]);
        $this->add_log('用户管理', $logType . '余额：' . $user['email'], '成功');
        return $this->ajaxSuccess('操作成功');
    }

    /**
     * 余额变动日志查询
     */
    public function balanceLog()
    {
        $user_id = input('user_id/d', 0);
        $type = input('type/s', '');
        if (!$user_id) {
            return $this->ajaxError('参数错误');
        }
        $where = ['user_id' => $user_id];
        if ($type !== '') {
            $where['type'] = $type;
        }

        // 分页参数
        $page = input('page/d', 1);
        $limit = input('limit/d', 10);

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
    }

    /**
     * 重置用户密码
     */
    public function resetPassword()
    {
        if (!$this->request->isPost()) {
            return $this->ajaxError('请求方式错误');
        }
        $id = input('id/d', 0);
        $password = input('password/s', '');
        $confirm_password = input('confirm_password/s', '');
        if (!$id) {
            return $this->ajaxError('参数错误');
        }
        if (empty($password) || empty($confirm_password)) {
            return $this->ajaxError('密码和确认密码不能为空');
        }
        if ($password !== $confirm_password) {
            return $this->ajaxError('两次密码不一致');
        }
        if (strlen($password) < 6) {
            return $this->ajaxError('密码长度不能小于6位');
        }
        $user = UserModel::find($id);
        if (!$user) {
            return $this->ajaxError('用户不存在');
        }
        $user->password = md5($password);
        if ($user->save() !== false) {
            $this->add_log('用户管理', '重置密码：' . $user['email'], '成功');
            return $this->ajaxSuccess('密码重置成功');
        }
        $this->add_log('用户管理', '重置密码：' . $user['email'], '失败');
        return $this->ajaxError('密码重置失败');
    }

    /**
     * 余额变动类型（供后台筛选项）
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