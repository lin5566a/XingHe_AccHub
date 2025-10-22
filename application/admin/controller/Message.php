<?php
namespace app\admin\controller;

use app\common\traits\ApiResponse;
use app\admin\model\UserRequest;
use think\Request;

class Message extends Base
{
    use ApiResponse;

    /**
     * 获取站内信列表
     */
    public function index()
    {
        // 获取分页参数
        $page = $this->request->param('page/d', 1);
        $limit = $this->request->param('limit/d', 10);
        
        // 获取筛选条件
        $email = trim($this->request->param('email'));
        $product = trim($this->request->param('product'));
        $status = $this->request->param('status');
        $start_time = $this->request->param('start_time');
        $end_time = $this->request->param('end_time');
        
        // 构建查询条件
        $where = array();
        
        // 邮箱模糊查询
        if ($email !== '' && $email !== null) {
            $where['user_email'] = ['like', '%'.$email.'%'];
        }
        
        // 商品名称模糊查询
        if ($product !== '' && $product !== null) {
            $where['product_name'] = ['like', '%'.$product.'%'];
        }
        
        // 状态精确查询
        if ($status !== '' && $status !== null) {
            $where['status'] = intval($status);
        }
        
        // 时间范围查询
        if ($start_time && $end_time) {
            $where['sent_at'] = ['between', [$start_time . ' 00:00:00', $end_time . ' 23:59:59']];
        } elseif ($start_time) {
            $where['sent_at'] = ['>=', $start_time . ' 00:00:00'];
        } elseif ($end_time) {
            $where['sent_at'] = ['<=', $end_time . ' 23:59:59'];
        }
        
        // 查询总数
        $total = model('UserRequest')->where($where)->count();
        
        // 重新构建查询获取列表数据
        $list = model('UserRequest')
            ->where($where)
            ->page($page, $limit)
            ->order('id desc')
            ->select();
        
        $this->add_log('站内信管理', '获取站内信列表', '成功');
        return $this->ajaxSuccess('获取成功', array(
            'total' => $total,
            'list' => $list,
            'page' => $page,
            'limit' => $limit
        ));
    }

    /**
     * 标记为已解决
     */
    public function solve()
    {
        if (!$this->request->isPost()) {
            $this->add_log('站内信管理', '标记消息已解决：请求方式错误', '失败');
            return $this->ajaxError('请求方式错误');
        }

        $id = $this->request->post('id');
        if (empty($id)) {
            $this->add_log('站内信管理', '标记消息已解决：参数错误', '失败');
            return $this->ajaxError('参数错误');
        }

        // 验证消息是否存在
        $message = model('UserRequest')->find($id);
        if (!$message) {
            $this->add_log('站内信管理', '标记消息已解决：消息不存在', '失败');
            return $this->ajaxError('消息不存在');
        }

        // 如果已经是已解决状态
        if ($message['status'] === UserRequest::STATUS_SOLVED) {
            $this->add_log('站内信管理', '标记消息已解决：该消息已经解决', '失败');
            return $this->ajaxError('该消息已经解决');
        }

        // 更新状态
        $result = model('UserRequest')->where('id', $id)->update(['status' => UserRequest::STATUS_SOLVED]);
        if ($result !== false) {
            $this->add_log('站内信管理', '标记消息已解决：ID-' . $id, '成功');
            return $this->ajaxSuccess('标记成功');
        }
        $this->add_log('站内信管理', '标记消息已解决：ID-' . $id, '失败');
        return $this->ajaxError('标记失败');
    }

    /**
     * 删除消息
     */
    public function delete()
    {
        if (!$this->request->isPost()) {
            $this->add_log('站内信管理', '删除消息：请求方式错误', '失败');
            return $this->ajaxError('请求方式错误');
        }

        $id = $this->request->post('id');
        if (empty($id)) {
            $this->add_log('站内信管理', '删除消息：参数错误', '失败');
            return $this->ajaxError('参数错误');
        }

        $message = model('UserRequest')->find($id);
        if (!$message) {
            $this->add_log('站内信管理', '删除消息：消息不存在', '失败');
            return $this->ajaxError('消息不存在');
        }

        if (model('UserRequest')->where('id', $id)->delete()) {
            $this->add_log('站内信管理', '删除消息：ID-' . $id, '成功');
            return $this->ajaxSuccess('删除成功');
        }
        $this->add_log('站内信管理', '删除消息：ID-' . $id, '失败');
        return $this->ajaxError('删除失败');
    }

    /**
     * 批量标记为已解决
     */
    public function batchSolve()
    {
        if (!$this->request->isPost()) {
            $this->add_log('站内信管理', '批量标记消息已解决：请求方式错误', '失败');
            return $this->ajaxError('请求方式错误');
        }

        $ids = $this->request->post('ids/a');
        if (empty($ids) || !is_array($ids)) {
            $this->add_log('站内信管理', '批量标记消息已解决：参数错误', '失败');
            return $this->ajaxError('参数错误');
        }

        // 验证所有消息是否存在
        $messages = model('UserRequest')->where('id', 'in', $ids)->select();
        if (count($messages) != count($ids)) {
            $this->add_log('站内信管理', '批量标记消息已解决：部分消息不存在', '失败');
            return $this->ajaxError('部分消息不存在');
        }

        // 更新状态
        $result = model('UserRequest')->where('id', 'in', $ids)->update(['status' => UserRequest::STATUS_SOLVED]);
        
        if ($result !== false) {
            $this->add_log('站内信管理', '批量标记消息已解决：ID-' . implode(',', $ids), '成功');
            return $this->ajaxSuccess('批量标记成功');
        }
        $this->add_log('站内信管理', '批量标记消息已解决：ID-' . implode(',', $ids), '失败');
        return $this->ajaxError('批量标记失败');
    }
} 