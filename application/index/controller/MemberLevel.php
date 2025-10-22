<?php
namespace app\index\controller;

use app\admin\model\MemberLevel as MemberLevelModel;
use think\Controller;

class MemberLevel extends Base
{
    // 前台获取会员等级列表
    public function getList()
    {
        $list = MemberLevelModel::order('sort asc, id asc')->select();
        return $this->ajaxSuccess('获取成功', $list);
    }
} 