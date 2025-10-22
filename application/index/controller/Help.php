<?php
namespace app\index\controller;

use app\admin\model\DocumentCategory as DocumentCategoryModel;
use app\admin\model\Documents as DocumentsModel;
use app\common\traits\ApiResponse;
use think\Log;

class Help extends Base
{
    use ApiResponse;

    /**
     * 获取帮助中心分类列表
     */
    public function categories()
    {
        try {
            // 查询所有启用的分类
            $categories = DocumentCategoryModel::where('status', 1)
                ->field('id,name,sort,create_time,update_time')
                ->order('sort asc')
                ->select();
                
            if (empty($categories)) {
                return $this->ajaxSuccess('暂无分类', []);
            }
            // dump($categories);
            // 格式化分类数据
            $result = [];
            foreach ($categories as $category) {
                $result[] = [
                    'id' => intval($category['id']),
                    'name' => strval($category['name']),
                    'sort' => intval($category['sort']),
                    'create_time' => $category['create_time'],
                    'update_time' => $category['update_time']
                ];
            }
            
            return $this->ajaxSuccess('获取成功', $result);
            
        } catch (\Exception $e) {
            Log::error('获取帮助中心分类失败：' . $e->getMessage());
            return $this->ajaxError('获取失败');
        }
    }
    
    /**
     * 获取文档列表
     */
    public function documents()
    {
        try {
            $data = $this->request->post();
            
            // 验证数据
            if (empty($data['category_id'])) {
                return $this->ajaxError('分类ID不能为空');
            }
            
            // 获取分类名称
            $category = DocumentCategoryModel::where('id', $data['category_id'])
                ->where('status', 1)
                ->value('name');
                
            if (!$category) {
                return $this->ajaxError('分类不存在或已禁用');
            }
            
            // 查询指定分类下的所有文档
            $documents = DocumentsModel::where('category', $category)
                ->where('status', 1)
                ->field('id,title,subtitle,created_at')
                ->order('sort_order desc, id desc')
                ->select();
                
            if (empty($documents)) {
                return $this->ajaxSuccess('暂无文档', []);
            }
            
            // 格式化文档数据
            $result = [];
            foreach ($documents as $document) {
                $result[] = [
                    'id' => intval($document['id']),
                    'title' => strval($document['title']),
                    'subtitle' => strval($document['subtitle']),
                    'created_at' => $document['created_at']
                ];
            }
            
            return $this->ajaxSuccess('获取成功', $result);
            
        } catch (\Exception $e) {
            Log::error('获取文档列表失败：' . $e->getMessage());
            return $this->ajaxError('获取失败');
        }
    }
    
    /**
     * 获取文档详情
     */
    public function detail()
    {
        try {
            $data = $this->request->post();
            
            // 验证数据
            if (empty($data['id'])) {
                return $this->ajaxError('文档ID不能为空');
            }
            
            // 查询文档详情
            $document = DocumentsModel::where('id', $data['id'])
                ->where('status', 1)
                ->field('id,title,subtitle,content,created_at')
                ->find();
                
            if (!$document) {
                return $this->ajaxError('文档不存在或已下架');
            }
            
            // 格式化文档数据
            $result = [
                'id' => intval($document['id']),
                'title' => strval($document['title']),
                'subtitle' => strval($document['subtitle']),
                'content' => strval($document['content']),
                'created_at' => $document['created_at']
            ];
            
            return $this->ajaxSuccess('获取成功', $result);
            
        } catch (\Exception $e) {
            Log::error('获取文档详情失败：' . $e->getMessage());
            return $this->ajaxError('获取失败');
        }
    }
} 