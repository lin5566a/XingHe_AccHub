<?php
namespace app\index\controller;

use app\admin\model\Announcement as AnnouncementModel;
use think\Controller;
use app\index\controller\Base;

class Announcement extends Base
{
    // 查询当前启用公告
    public function get()
    {
        $announcement = AnnouncementModel::where('status', 1)->order('id desc')->find();
        if ($announcement) {
            return $this->ajaxSuccess('获取成功', [
                'id' => $announcement['id'],
                'title' => $announcement['title'],
                'content' => $announcement['content'],
                'token' => $announcement['token'],
                'publish_time' => $announcement['publish_time']
            ]);
        } else {
            return $this->ajaxSuccess('暂无公告', null);
        }
    }
} 