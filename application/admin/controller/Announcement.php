<?php
namespace app\admin\controller;

use app\admin\model\Announcement as AnnouncementModel;
use think\Db;
use think\Exception;

class Announcement extends Base
{
    // 公告列表
    public function index()
    {
        if (!$this->request->isGet()) {
            return $this->ajaxError('请求方式错误');
        }
        
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 10);
        $title = input('title', '');
        $status = input('status', '');
        
        $where = [];
        if ($title !== '') {
            $where['title'] = ['like', "%$title%"];
        }
        if ($status !== '') {
            $where['status'] = $status;
        }
        
        // 查询总数
        $total = AnnouncementModel::where($where)->count();
        
        // 查询列表（带分页）
        $list = AnnouncementModel::where($where)
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
    
    // 添加公告
    public function add()
    {
        $data = input('post.');
        if (empty($data['title']) || empty($data['content'])) {
            return $this->ajaxError('标题和内容不能为空');
        }
        
        // 发布时间由后台自动生成，不需要前端传递
        $data['publish_time'] = date('Y-m-d H:i:s');
        $data['status'] = intval($data['status']);
        
        // 如果启用，生成token并禁用其他公告
        if ($data['status'] == 1) {
            $data['token'] = md5(uniqid('', true));
            AnnouncementModel::where('status', 1)->update(['status' => 0]);
        } else {
            $data['token'] = '';
        }
        
        $res = AnnouncementModel::create($data);
        if ($res) {
            return $this->ajaxSuccess('新增成功');
        } else {
            return $this->ajaxError('新增失败');
        }
    }
    
    // 编辑公告
    public function edit()
    {
        $id = input('id/d');
        $data = input('post.');
        if (!$id) {
            return $this->ajaxError('参数错误');
        }
        if (empty($data['title']) || empty($data['content'])) {
            return $this->ajaxError('标题和内容不能为空');
        }
        
        // 发布时间由后台自动生成，不需要前端传递
        $data['publish_time'] = date('Y-m-d H:i:s');
        $data['status'] = intval($data['status']);
        
        // 如果启用，生成token并禁用其他公告
        if ($data['status'] == 1) {
            $data['token'] = md5(uniqid('', true));
            AnnouncementModel::where('status', 1)->where('id', '<>', $id)->update(['status' => 0]);
        } else {
            $data['token'] = '';
        }
        
        // 禁止手动传递created_at和updated_at，防止时间戳写入
        unset($data['created_at'], $data['updated_at']);
        $res = AnnouncementModel::where('id', $id)->update($data);
        if ($res !== false) {
            return $this->ajaxSuccess('编辑成功');
        } else {
            return $this->ajaxError('编辑失败');
        }
    }
    
    // 删除公告
    public function delete()
    {
        $id = input('id/d');
        if (!$id) {
            return $this->ajaxError('参数错误');
        }
        $res = AnnouncementModel::destroy($id);
        if ($res) {
            return $this->ajaxSuccess('删除成功');
        } else {
            return $this->ajaxError('删除失败');
        }
    }
    
    // 批量删除
    public function batchDelete()
    {
        $ids = input('ids/a');
        if (empty($ids)) {
            return $this->ajaxError('请选择要删除的公告');
        }
        $res = AnnouncementModel::destroy($ids);
        if ($res) {
            return $this->ajaxSuccess('批量删除成功');
        } else {
            return $this->ajaxError('批量删除失败');
        }
    }
    
    // 修改状态
    public function status($id)
    {
        $announcement = AnnouncementModel::get($id);
        if (!$announcement) {
            return json(['code' => 1, 'msg' => '公告不存在']);
        }
        
        $status = $announcement->status == AnnouncementModel::STATUS_ENABLED ? 
            AnnouncementModel::STATUS_DISABLED : AnnouncementModel::STATUS_ENABLED;
        
        Db::startTrans();
        try {
            $announcement->save(['status' => $status]);
            Db::commit();
            return json(['code' => 0, 'msg' => '状态修改成功']);
        } catch (Exception $e) {
            Db::rollback();
            return json(['code' => 1, 'msg' => '状态修改失败：' . $e->getMessage()]);
        }
    }
    
    // 修改排序
    public function sort($id)
    {
        $announcement = AnnouncementModel::get($id);
        if (!$announcement) {
            return json(['code' => 1, 'msg' => '公告不存在']);
        }
        
        $sort = $this->request->post('sort/d', 0);
        
        Db::startTrans();
        try {
            $announcement->save(['sort' => $sort]);
            Db::commit();
            return json(['code' => 0, 'msg' => '排序修改成功']);
        } catch (Exception $e) {
            Db::rollback();
            return json(['code' => 1, 'msg' => '排序修改失败：' . $e->getMessage()]);
        }
    }
} 