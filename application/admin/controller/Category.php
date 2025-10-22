<?php
namespace app\admin\controller;

use app\admin\model\ProductCategory;
use think\Request;

class Category extends Base
{
    /**
     * 获取分类列表
     */
    public function index()
    {
        $where = [];
        
        // 分类名称筛选
        $name = input('name/s', '');
        if ($name !== '') {
            $where['name'] = ['like', "%{$name}%"];
        }
        
        // 状态筛选
        $status = input('status/d', '');
        if ($status !== '') {
            $where['status'] = $status;
        }
        
        // 获取总数
        $total = ProductCategory::where($where)->count();
        
        // 分页查询
        $page = input('page/d', 1);
        $limit = input('limit/d', 10);
        
        // 获取列表数据
        $list = ProductCategory::where($where)
            ->order('sort_order asc, id asc')
            ->page($page, $limit)
            ->select();
            
        $this->add_log('商品分类', '获取分类列表', '成功');
        return $this->ajaxSuccess('获取成功', [
            'total' => $total,
            'list' => $list
        ]);
    }
    
    /**
     * 新增分类
     */
    public function add()
    {
        if (!$this->request->isPost()) {
            $this->add_log('商品分类', '新增分类：请求方式错误', '失败');
            return $this->ajaxError('请求方式错误');
        }
        
        $data = [
            'name' => input('name/s', ''),
            'description' => input('description/s', ''),
            'sort_order' => input('sort_order/d', 0),
            'status' => input('status/d', ProductCategory::STATUS_ENABLED)
        ];
        
        // 验证
        if (empty($data['name'])) {
            $this->add_log('商品分类', '新增分类：分类名称不能为空', '失败');
            return $this->ajaxError('分类名称不能为空');
        }
        
        // 检查名称是否重复
        if (ProductCategory::where('name', $data['name'])->find()) {
            $this->add_log('商品分类', '新增分类：分类名称已存在', '失败');
            return $this->ajaxError('分类名称已存在');
        }
        
        if ($category = ProductCategory::create($data)) {
            $this->add_log('商品分类', '新增分类：' . $data['name'], '成功');
            return $this->ajaxSuccess('添加成功', $category);
        }
        
        $this->add_log('商品分类', '新增分类：' . $data['name'], '失败');
        return $this->ajaxError('添加失败');
    }
    
    /**
     * 编辑分类
     */
    public function edit()
    {
        if (!$this->request->isPost()) {
            $this->add_log('商品分类', '编辑分类：请求方式错误', '失败');
            return $this->ajaxError('请求方式错误');
        }
        
        $id = input('id/d', 0);
        if (!$id) {
            $this->add_log('商品分类', '编辑分类：参数错误', '失败');
            return $this->ajaxError('参数错误');
        }
        
        $category = ProductCategory::find($id);
        if (!$category) {
            $this->add_log('商品分类', '编辑分类：分类不存在', '失败');
            return $this->ajaxError('分类不存在');
        }
        
        $data = [
            'name' => input('name/s', ''),
            'description' => input('description/s', ''),
            'sort_order' => input('sort_order/d', 0),
            'status' => input('status/d', ProductCategory::STATUS_ENABLED)
        ];
        
        // 验证
        if (empty($data['name'])) {
            $this->add_log('商品分类', '编辑分类：分类名称不能为空', '失败');
            return $this->ajaxError('分类名称不能为空');
        }
        
        // 检查名称是否重复(排除自身)
        if (ProductCategory::where('name', $data['name'])->where('id', '<>', $id)->find()) {
            $this->add_log('商品分类', '编辑分类：分类名称已存在', '失败');
            return $this->ajaxError('分类名称已存在');
        }
        
        if ($category->save($data) !== false) {
            $this->add_log('商品分类', '编辑分类：' . $data['name'], '成功');
            return $this->ajaxSuccess('更新成功');
        }
        
        $this->add_log('商品分类', '编辑分类：' . $data['name'], '失败');
        return $this->ajaxError('更新失败');
    }
    
    /**
     * 删除分类
     */
    public function delete()
    {
        if (!$this->request->isPost()) {
            $this->add_log('商品分类', '删除分类：请求方式错误', '失败');
            return $this->ajaxError('请求方式错误');
        }
        
        $id = input('id/d', 0);
        if (!$id) {
            $this->add_log('商品分类', '删除分类：参数错误', '失败');
            return $this->ajaxError('参数错误');
        }
        
        $category = ProductCategory::find($id);
        if (!$category) {
            $this->add_log('商品分类', '删除分类：分类不存在', '失败');
            return $this->ajaxError('分类不存在');
        }
        
        // 检查是否有商品使用该分类
        $count = model('Product')->where('category_id', $id)->count();
        if ($count > 0) {
            $this->add_log('商品分类', '删除分类：该分类下有商品，无法删除', '失败');
            return $this->ajaxError('该分类下有商品，无法删除');
        }
        
        if ($category->delete()) {
            $this->add_log('商品分类', '删除分类：' . $category['name'], '成功');
            return $this->ajaxSuccess('删除成功');
        }
        
        $this->add_log('商品分类', '删除分类：' . $category['name'], '失败');
        return $this->ajaxError('删除失败');
    }
} 