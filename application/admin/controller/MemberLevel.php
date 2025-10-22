<?php
namespace app\admin\controller;

use app\admin\model\MemberLevel as MemberLevelModel;

class MemberLevel extends Base
{
    // 获取会员等级列表
    public function getList()
    {
        $list = MemberLevelModel::order('sort asc, id asc')->select();
        $superId = \app\admin\model\MemberLevel::SUPER_LEVEL_ID;
        foreach ($list as &$item) {
            $item['can_assign'] = ($item['id'] == 1 || $item['id'] == $superId) ? true : false;
        }
        unset($item);
        return $this->ajaxSuccess('获取成功', ['list' => $list]);
    }

    // 编辑会员等级
    public function edit()
    {
        $id = input('id/d');
        $data = input('post.');
        if (!$id) {
            return $this->ajaxError('参数错误');
        }
        if (empty($data['name'])) {
            return $this->ajaxError('等级名称不能为空');
        }
        if (!isset($data['upgrade_amount'])) {
            return $this->ajaxError('累计升级条件不能为空');
        }
        if (!isset($data['discount'])) {
            return $this->ajaxError('会员折扣不能为空');
        }
        if (empty($data['description'])) {
            return $this->ajaxError('会员介绍不能为空');
        }
        // 只允许更新允许的字段
        $update = [
            'name' => $data['name'],
            'upgrade_amount' => $data['upgrade_amount'],
            'discount' => $data['discount'],
            'remark' => isset($data['remark']) ? $data['remark'] : '',
            'description' => $data['description'],
            'sort' => isset($data['sort']) ? intval($data['sort']) : 0
        ];
        $res = MemberLevelModel::where('id', $id)->update($update);
        if ($res !== false) {
            return $this->ajaxSuccess('编辑成功');
        } else {
            return $this->ajaxError('编辑失败');
        }
    }
} 