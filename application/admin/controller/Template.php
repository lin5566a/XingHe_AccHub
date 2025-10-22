<?php
namespace app\admin\controller;

use app\admin\model\ProductDescriptionTemplate;
use app\admin\model\ProductCategory;

class Template extends Base
{
    /**
     * 获取模板列表
     */
    public function index()
    {
        $where = [];
        
        // 模板名称筛选
        $template_name = input('template_name/s', '');
        if ($template_name !== '') {
            $where['template_name'] = ['like', "%{$template_name}%"];
        }
        
        // 分类筛选
        $category_id = input('category_id/d', 0);
        if ($category_id > 0) {
            $where['category_id'] = $category_id;
        }
        
        // 获取总数
        $total = ProductDescriptionTemplate::where($where)->count();
        
        // 分页查询
        $page = input('page/d', 1);
        $limit = input('limit/d', 10);
        
        // 获取列表数据
        $list = ProductDescriptionTemplate::where($where)
            ->with('category')
            ->order('id asc')
            ->page($page, $limit)
            ->select();
            
        $this->add_log('商品描述模板', '获取模板列表', '成功');
        return $this->ajaxSuccess('获取成功', [
            'total' => $total,
            'list' => $list
        ]);
    }
    
    /**
     * 获取商品分类列表（用于下拉选择）
     */
    public function categories()
    {
        $list = ProductCategory::where('status', ProductCategory::STATUS_ENABLED)
            ->field('id, name')
            ->order('sort_order asc')
            ->select();
            
        $this->add_log('商品描述模板', '获取分类列表', '成功');
        return $this->ajaxSuccess('获取成功', $list);
    }
    
    /**
     * 新增模板
     */
    public function add()
    {
        if (!$this->request->isPost()) {
            $this->add_log('商品描述模板', '新增模板：请求方式错误', '失败');
            return $this->ajaxError('请求方式错误');
        }
        
        $data = [
            'template_name' => input('template_name/s', ''),
            'category_id' => input('category_id/d', 0),
            'content' => input('content/s', '')
        ];
        
        // 验证
        if (empty($data['template_name'])) {
            $this->add_log('商品描述模板', '新增模板：模板名称不能为空', '失败');
            return $this->ajaxError('模板名称不能为空');
        }
        
        if (empty($data['category_id'])) {
            $this->add_log('商品描述模板', '新增模板：请选择适用分类', '失败');
            return $this->ajaxError('请选择适用分类');
        }
        
        if (empty($data['content'])) {
            $this->add_log('商品描述模板', '新增模板：模板内容不能为空', '失败');
            return $this->ajaxError('模板内容不能为空');
        }
        
        // 检查名称是否重复
        if (ProductDescriptionTemplate::where('template_name', $data['template_name'])->find()) {
            $this->add_log('商品描述模板', '新增模板：模板名称已存在', '失败');
            return $this->ajaxError('模板名称已存在');
        }
        
        // 验证分类是否存在
        if (!ProductCategory::where('id', $data['category_id'])->find()) {
            $this->add_log('商品描述模板', '新增模板：选择的分类不存在', '失败');
            return $this->ajaxError('选择的分类不存在');
        }
        
        if ($template = ProductDescriptionTemplate::create($data)) {
            $this->add_log('商品描述模板', '新增模板：' . $data['template_name'], '成功');
            return $this->ajaxSuccess('添加成功', $template);
        }
        
        $this->add_log('商品描述模板', '新增模板：' . $data['template_name'], '失败');
        return $this->ajaxError('添加失败');
    }
    
    /**
     * 编辑模板
     */
    public function edit()
    {
        if (!$this->request->isPost()) {
            $this->add_log('商品描述模板', '编辑模板：请求方式错误', '失败');
            return $this->ajaxError('请求方式错误');
        }
        
        $id = input('id/d', 0);
        if (!$id) {
            $this->add_log('商品描述模板', '编辑模板：参数错误', '失败');
            return $this->ajaxError('参数错误');
        }
        
        $template = ProductDescriptionTemplate::find($id);
        if (!$template) {
            $this->add_log('商品描述模板', '编辑模板：模板不存在', '失败');
            return $this->ajaxError('模板不存在');
        }
        
        $data = [
            'template_name' => input('template_name/s', ''),
            'category_id' => input('category_id/d', 0),
            'content' => input('content/s', '')
        ];
        
        // 验证
        if (empty($data['template_name'])) {
            $this->add_log('商品描述模板', '编辑模板：模板名称不能为空', '失败');
            return $this->ajaxError('模板名称不能为空');
        }
        
        if (empty($data['category_id'])) {
            $this->add_log('商品描述模板', '编辑模板：请选择适用分类', '失败');
            return $this->ajaxError('请选择适用分类');
        }
        
        if (empty($data['content'])) {
            $this->add_log('商品描述模板', '编辑模板：模板内容不能为空', '失败');
            return $this->ajaxError('模板内容不能为空');
        }
        
        // 检查名称是否重复(排除自身)
        if (ProductDescriptionTemplate::where('template_name', $data['template_name'])->where('id', '<>', $id)->find()) {
            $this->add_log('商品描述模板', '编辑模板：模板名称已存在', '失败');
            return $this->ajaxError('模板名称已存在');
        }
        
        // 验证分类是否存在
        if (!ProductCategory::where('id', $data['category_id'])->find()) {
            $this->add_log('商品描述模板', '编辑模板：选择的分类不存在', '失败');
            return $this->ajaxError('选择的分类不存在');
        }
        
        if ($template->save($data) !== false) {
            $this->add_log('商品描述模板', '编辑模板：' . $data['template_name'], '成功');
            return $this->ajaxSuccess('更新成功');
        }
        
        $this->add_log('商品描述模板', '编辑模板：' . $data['template_name'], '失败');
        return $this->ajaxError('更新失败');
    }
    
    /**
     * 删除模板
     */
    public function delete()
    {
        if (!$this->request->isPost()) {
            $this->add_log('商品描述模板', '删除模板：请求方式错误', '失败');
            return $this->ajaxError('请求方式错误');
        }
        
        $id = input('id/d', 0);
        if (!$id) {
            $this->add_log('商品描述模板', '删除模板：参数错误', '失败');
            return $this->ajaxError('参数错误');
        }
        
        $template = ProductDescriptionTemplate::find($id);
        if (!$template) {
            $this->add_log('商品描述模板', '删除模板：模板不存在', '失败');
            return $this->ajaxError('模板不存在');
        }
        
        if ($template->delete()) {
            $this->add_log('商品描述模板', '删除模板：' . $template['template_name'], '成功');
            return $this->ajaxSuccess('删除成功');
        }
        
        $this->add_log('商品描述模板', '删除模板：' . $template['template_name'], '失败');
        return $this->ajaxError('删除失败');
    }
} 