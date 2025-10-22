<?php
namespace app\admin\controller;

use app\common\traits\ApiResponse;
use app\admin\model\Documents;

class Document extends Base
{
    use ApiResponse;

    /**
     * 获取文档列表
     */
    public function index()
    {
        // 获取分页参数
        $page = $this->request->param('page/d', 1);
        $limit = $this->request->param('limit/d', 10);
        
        // 获取筛选条件
        $title = trim($this->request->param('title'));
        $category = trim($this->request->param('category'));
        $status = $this->request->param('status');
        
        // 构建查询条件
        $where = array();
        
        // 标题模糊查询
        if ($title !== '' && $title !== null) {
            $where['title'] = ['like', '%'.$title.'%'];
        }
        
        // 分类精确查询
        if ($category !== '' && $category !== null) {
            $where['category'] = $category;
        }
        
        // 状态精确查询
        if ($status !== '' && $status !== null) {
            $where['status'] = intval($status);
        }
        
        // 查询总数
        $total = model('Documents')->where($where)->count();
        
        // 重新构建查询获取列表数据
        $list = model('Documents')
            ->where($where)  // 重新应用查询条件
            ->page($page, $limit)
            ->order('sort_order desc, id desc')
            ->select();
        
        $this->add_log('文档管理', '获取文档列表', '成功');
        return $this->ajaxSuccess('获取成功', array(
            'total' => $total,
            'list' => $list,
            'page' => $page,
            'limit' => $limit
        ));
    }

    /**
     * 新增文档
     */
    public function add()
    {
        if (!$this->request->isPost()) {
            $this->add_log('文档管理', '新增文档：请求方式错误', '失败');
            return $this->ajaxError('请求方式错误');
        }

        $data = $this->request->post();

        // 验证必填字段
        if (empty($data['title']) || empty($data['subtitle']) || 
            empty($data['category']) || empty($data['content'])) {
            $this->add_log('文档管理', '新增文档：请填写完整信息', '失败');
            return $this->ajaxError('请填写完整信息');
        }

        // 验证标题唯一性
        if (model('Documents')->where('title', $data['title'])->find()) {
            $this->add_log('文档管理', '新增文档：文档标题已存在', '失败');
            return $this->ajaxError('文档标题已存在');
        }

        // 验证状态值
        if (isset($data['status'])) {
            $status = intval($data['status']); // 转换为整数
            if (!in_array($status, [
                Documents::STATUS_UNPUBLISHED,
                Documents::STATUS_PUBLISHED
            ])) {
                $this->add_log('文档管理', '新增文档：状态值错误', '失败');
                return $this->ajaxError('状态值错误');
            }
            $data['status'] = $status; // 使用转换后的值
        } else {
            $data['status'] = Documents::STATUS_UNPUBLISHED; // 默认未发布
        }

        // 处理排序值
        $data['sort_order'] = isset($data['sort_order']) ? intval($data['sort_order']) : 0;

        if (model('Documents')->save($data)) {
            $this->add_log('文档管理', '新增文档：' . $data['title'], '成功');
            return $this->ajaxSuccess('添加成功');
        }
        $this->add_log('文档管理', '新增文档：' . $data['title'], '失败');
        return $this->ajaxError('添加失败');
    }

    /**
     * 更新文档
     */
    public function update()
    {
        if (!$this->request->isPost()) {
            $this->add_log('文档管理', '更新文档：请求方式错误', '失败');
            return $this->ajaxError('请求方式错误');
        }

        $data = $this->request->post();

        if (empty($data['id'])) {
            $this->add_log('文档管理', '更新文档：参数错误', '失败');
            return $this->ajaxError('参数错误');
        }

        // 验证文档是否存在
        $document = model('Documents')->find($data['id']);
        if (!$document) {
            $this->add_log('文档管理', '更新文档：文档不存在', '失败');
            return $this->ajaxError('文档不存在');
        }

        // 验证必填字段
        if (empty($data['title']) || empty($data['subtitle']) || 
            empty($data['category']) || empty($data['content'])) {
            $this->add_log('文档管理', '更新文档：请填写完整信息', '失败');
            return $this->ajaxError('请填写完整信息');
        }

        // 验证标题唯一性（排除当前记录）
        $existTitle = model('Documents')
            ->where('title', $data['title'])
            ->where('id', '<>', $data['id'])
            ->find();
        if ($existTitle) {
            $this->add_log('文档管理', '更新文档：文档标题已存在', '失败');
            return $this->ajaxError('文档标题已存在');
        }

        // 验证状态值
        if (isset($data['status'])) {
            $status = intval($data['status']); // 转换为整数
            if (!in_array($status, [
                Documents::STATUS_UNPUBLISHED,
                Documents::STATUS_PUBLISHED
            ])) {
                $this->add_log('文档管理', '更新文档：状态值错误', '失败');
                return $this->ajaxError('状态值错误');
            }
            $data['status'] = $status; // 使用转换后的值
        }

        // 处理排序值
        $data['sort_order'] = isset($data['sort_order']) ? intval($data['sort_order']) : 0;

        $result = model('Documents')->save($data, array('id' => $data['id']));
        if ($result !== false) {
            $this->add_log('文档管理', '更新文档：' . $data['title'], '成功');
            return $this->ajaxSuccess('更新成功');
        }
        $this->add_log('文档管理', '更新文档：' . $data['title'], '失败');
        return $this->ajaxError('更新失败');
    }

    /**
     * 删除文档
     */
    public function delete()
    {
        if (!$this->request->isPost()) {
            $this->add_log('文档管理', '删除文档：请求方式错误', '失败');
            return $this->ajaxError('请求方式错误');
        }

        $id = $this->request->post('id');
        if (empty($id)) {
            $this->add_log('文档管理', '删除文档：参数错误', '失败');
            return $this->ajaxError('参数错误');
        }

        $document = model('Documents')->find($id);
        if (!$document) {
            $this->add_log('文档管理', '删除文档：文档不存在', '失败');
            return $this->ajaxError('文档不存在');
        }

        if (model('Documents')->where('id', $id)->delete()) {
            $this->add_log('文档管理', '删除文档：' . $document['title'], '成功');
            return $this->ajaxSuccess('删除成功');
        }
        $this->add_log('文档管理', '删除文档：' . $document['title'], '失败');
        return $this->ajaxError('删除失败');
    }
} 