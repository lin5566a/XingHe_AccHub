<?php
namespace app\admin\controller;

use think\Controller;
use app\common\traits\ApiResponse;
use app\common\utils\Upload;

class Manager extends Base
{
    use ApiResponse;

    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = model('Manager');
    }

    /**
     * 获取管理员列表
     * @return JSON
     */
    public function index()
    {
        // 获取分页参数
        $page = $this->request->param('page/d', 1);
        $limit = $this->request->param('limit/d', 10);
        
        // 获取筛选条件
        $username = $this->request->param('username');
        $email = $this->request->param('email');
        $status = $this->request->param('status');
        
        // 构建查询条件
        $where = array();
        
        // 用户名模糊查询
        if (!empty($username)) {
            $where['username'] = array('like', '%'.$username.'%');
        }
        
        // 邮箱模糊查询
        if (!empty($email)) {
            $where['email'] = array('like', '%'.$email.'%');
        }
        
        // 状态精确查询
        if (isset($status) && $status !== '') {
            $where['status'] = intval($status);
        }
        
        // 查询总数
        $total = model('Manager')->where($where)->count();
        
        // 获取分页数据
        $list = model('Manager')
            ->field('id,username,truename,email,status,remark,last_login_time,last_login_ip,create_time,update_time')
            ->where($where)
            ->page($page, $limit)
            ->select();
        
        return $this->ajaxSuccess('获取成功', array(
            'total' => $total,
            'list' => $list,
            'page' => $page,
            'limit' => $limit
        ));
    }

    /**
     * 获取管理员详情
     * @return JSON
     */
    public function info()
    {
        $id = $this->request->param('id');
        if (empty($id)) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        $info = $this->model->getInfo($id);
        if ($info) {
            return json(['code' => 1, 'msg' => '获取成功', 'data' => $info]);
        }
        return json(['code' => 0, 'msg' => '数据不存在']);
    }

    /**
     * 新增管理员
     */
    public function add()
    {
        if (!$this->request->isPost()) {
            return $this->ajaxError('请求方式错误');
        }

        $data = $this->request->post();

        // 验证数据
        if (empty($data['username']) || empty($data['password']) || empty($data['confirm_password'])) {
            return $this->ajaxError('请填写完整信息');
        }

        // 验证两次密码是否一致
        if ($data['password'] !== $data['confirm_password']) {
            return $this->ajaxError('两次密码不一致');
        }

        // 验证用户名是否存在
        if (model('Manager')->where('username', $data['username'])->find()) {
            return $this->ajaxError('用户名已存在');
        }

        // 验证邮箱格式
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->ajaxError('邮箱格式不正确');
        }

        // 验证邮箱是否存在
        if (!empty($data['email']) && model('Manager')->where('email', $data['email'])->find()) {
            return $this->ajaxError('邮箱已存在');
        }

        // 验证状态值
        if (isset($data['status']) && !in_array($data['status'], array(0, 1), true)) {
            return $this->ajaxError('状态值错误');
        }

        // 处理数据
        $saveData = array(
            'username' => $data['username'],
            'password' => md5($data['password']),
            'truename' => isset($data['truename']) ? $data['truename'] : '',
            'email' => isset($data['email']) ? $data['email'] : '',
            'status' => isset($data['status']) ? $data['status'] : 1,
            'remark' => isset($data['remark']) ? $data['remark'] : '',
            'create_time' => date('Y-m-d H:i:s'),
            'update_time' => date('Y-m-d H:i:s')
        );
        
        if (model('Manager')->save($saveData)) {
            $this->add_log('管理员管理', '新增管理员：' . $data['username'], '成功');
            return $this->ajaxSuccess('添加成功');
        }
        $this->add_log('管理员管理', '新增管理员：' . $data['username'], '失败');
        return $this->ajaxError('添加失败');
    }

    /**
     * 更新管理员信息
     */
    public function update()
    {
        if (!$this->request->isPost()) {
            return $this->ajaxError('请求方式错误');
        }

        $data = $this->request->post();

        if (empty($data['id'])) {
            return $this->ajaxError('参数错误');
        }

        // 不允许修改超级管理员
        if ($data['id'] == 1) {
            return $this->ajaxError('不能修改超级管理员');
        }

        // 验证用户是否存在
        $manager = model('Manager')->find($data['id']);
        if (!$manager) {
            return $this->ajaxError('用户不存在');
        }

        // 验证邮箱格式
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->ajaxError('邮箱格式不正确');
        }

        // 验证邮箱是否被其他用户使用
        if (!empty($data['email'])) {
            $existEmail = model('Manager')->where('email', $data['email'])->where('id', '<>', $data['id'])->find();
            if ($existEmail) {
                return $this->ajaxError('邮箱已被其他用户使用');
            }
        }

        // 验证状态值
        if (isset($data['status']) && !in_array($data['status'], array(0, 1))) {
            return $this->ajaxError('状态值错误');
        }

        // 验证头像URL格式
        if (!empty($data['avatar']) && !preg_match('/^\/uploads\/avatar\/.+\.(jpg|jpeg|png|gif)$/', $data['avatar'])) {
            return $this->ajaxError('头像URL格式不正确');
        }

        // 准备更新数据
        $updateData = array(
            'username' => isset($data['username']) ? $data['username'] : $manager['username'],
            'email' => isset($data['email']) ? $data['email'] : $manager['email'],
            'status' => isset($data['status']) ? $data['status'] : $manager['status'],
            'remark' => isset($data['remark']) ? $data['remark'] : $manager['remark'],
            'avatar' => isset($data['avatar']) ? $data['avatar'] : $manager['avatar'],
            'update_time' => date('Y-m-d H:i:s')
        );

        // 如果修改密码
        if (!empty($data['password'])) {
            if (empty($data['confirm_password'])) {
                return $this->ajaxError('请输入确认密码');
            }
            if ($data['password'] !== $data['confirm_password']) {
                return $this->ajaxError('两次密码不一致');
            }
            $updateData['password'] = md5($data['password']);
        }

        $result = model('Manager')->save($updateData, array('id' => $data['id']));
        if ($result !== false) {
            $this->add_log('管理员管理', '更新管理员：' . $manager['username'], '成功');
            return $this->ajaxSuccess('更新成功');
        }
        $this->add_log('管理员管理', '更新管理员：' . $manager['username'], '失败');
        return $this->ajaxError('更新失败');
    }

    /**
     * 删除管理员
     */
    public function delete()
    {
        if (!$this->request->isPost()) {
            return $this->ajaxError('请求方式错误');
        }

        $id = $this->request->post('id');
        if (empty($id)) {
            return $this->ajaxError('参数错误');
        }

        // 不允许删除超级管理员
        if ($id == 1) {
            return $this->ajaxError('不能删除超级管理员');
        }

        // 获取管理员信息用于日志
        $manager = model('Manager')->find($id);
        if (!$manager) {
            return $this->ajaxError('管理员不存在');
        }

        if (model('Manager')->where('id', $id)->delete()) {
            $this->add_log('管理员管理', '删除管理员：' . $manager['username'], '成功');
            return $this->ajaxSuccess('删除成功');
        }
        $this->add_log('管理员管理', '删除管理员：' . $manager['username'], '失败');
        return $this->ajaxError('删除失败');
    }

    /**
     * 修改个人头像
     */
    public function updateAvatar()
    {
        if (!$this->request->isPost()) {
            return $this->ajaxError('请求方式错误');
        }

        $file = $this->request->file('avatar');
        if (!$file) {
            return $this->ajaxError('请选择要上传的头像');
        }

        // 使用utils中的上传功能
        $result = \app\common\utils\Upload::image($file, 'avatar', [
            'size' => 2097152, // 2M
            'ext' => 'jpg,jpeg,png,gif'
        ]);
        
        if ($result['code'] === 0) {
            return $this->ajaxError($result['msg']);
        }

        // 更新管理员头像
        $upResult = $this->model->where('id', $this->adminInfo['id'])->update([
            'avatar' => $result['data']['file_path']
        ]);

        if ($upResult === false) {
            // 删除已上传的文件
            \app\common\utils\Upload::delete($result['data']['file_path']);
            return $this->ajaxError('头像更新失败');
        }

        // 记录操作日志
        $this->add_log('个人信息', '修改个人头像', '成功');
        
        return $this->ajaxSuccess('头像更新成功', [
            'avatar' => $result['data']['file_path']
        ]);
    }
} 