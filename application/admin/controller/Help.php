<?php
namespace app\admin\controller;

use app\common\traits\ApiResponse;

class Help extends Base
{
    use ApiResponse;

    /**
     * 获取文档分类列表
     */
    public function categories()
    {
        $list = model('DocumentCategory')
            ->field('id, name')
            ->where('status', 1)
            ->order('sort desc, id asc')
            ->select();
        
        $this->add_log('帮助中心', '获取文档分类列表', '成功');
        return $this->ajaxSuccess('获取成功', $list);
    }
} 