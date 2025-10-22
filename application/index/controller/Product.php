<?php
namespace app\index\controller;

use app\admin\model\Product as ProductModel;
use app\admin\model\ProductCategory as CategoryModel;
use app\admin\model\CustomerService as CustomerServiceModel;
use think\Log;
use app\common\traits\ApiResponse;

class Product extends Base
{
    use ApiResponse;

    /**
     * 商品列表
     */
    public function index()
    {
        try {
            $categoryId = $this->request->param('category_id', 0);
            $keyword = $this->request->param('keyword', '');
            
            // 查询分类列表
            $categories = CategoryModel::where('status=1')
                ->order('sort_order', 'asc')
                ->select();
                
            // 构建查询条件
            $where = ['status' => 1];
            
            // 如果有分类ID，直接按分类查询
            if ($categoryId) {
                $where['category_id'] = $categoryId;
            } 
            // 如果有搜索关键词
            elseif (!empty($keyword)) {
                // 关键词分类映射
                $categoryKeywords = [
                    'Twitter' => ['推特', '推', 'tw', 'x', 'tt'],
                    'Instagram' => ['ig', 'ins'],
                    'Facebook' => ['脸书', 'fb', '脸'],
                    'TikTok' => ['tk', '抖音', '抖'],
                    '微软邮箱' => ['邮箱', '邮', 'outlook'],
                    '谷歌邮箱' => ['谷歌', '谷'],
                    'Telegram' => ['tg', '飞机', '电报', '飞', '电']
                ];
                
                // 查找匹配的分类
                $matchedCategory = null;
                foreach ($categoryKeywords as $categoryName => $keywords) {
                    if (in_array(strtolower($keyword), array_map('strtolower', $keywords))) {
                        $matchedCategory = CategoryModel::where('name', $categoryName)
                            ->where('status', 1)
                            ->find();
                        break;
                    }
                }
                
                if ($matchedCategory) {
                    // 如果找到匹配的分类，按分类查询
                    $where['category_id'] = $matchedCategory['id'];
                } else {
                    // 如果没有匹配的分类，按分类名称模糊搜索
                    $categoryIds = CategoryModel::where('name', 'like', '%' . $keyword . '%')
                        ->where('status', 1)
                        ->column('id');
                        
                    if (!empty($categoryIds)) {
                        $where['category_id'] = ['in', $categoryIds];
                    } else {
                        // 如果分类名称也没有匹配，按商品名称模糊搜索
                        $where['name'] = ['like', '%' . $keyword . '%'];
                    }
                }
            }
            
            // 查询商品列表（包含折扣字段）
            $products = ProductModel::where($where)
                ->field('id,name,image,price,original_price,delivery_method,category_id,sort,stock,discount_enabled,discount_percentage,discount_start_time,discount_end_time,discount_set_time')
                ->with(['category' => function($query) {
                    $query->field('id,name');
                }])
                ->order('category_id asc, sort asc, id desc')
                ->select();
                
            // 获取商品库存
            $productIds = [];
            foreach ($products as $product) {
                $productIds[] = $product['id'];
            }
            
            // 只获取自动发货商品的库存
            $stocks = \app\admin\model\ProductStock::where('product_id', 'in', $productIds)
                ->where('status', 0)  // 只统计未售出的库存
                ->field('product_id,COUNT(*) as total_stock')
                ->group('product_id')
                ->select();
                
            // 将库存数据转换为以商品ID为键的数组
            $stockMap = [];
            foreach ($stocks as $stock) {
                $stockMap[$stock['product_id']] = $stock['total_stock'];
            }
                
            // 按分类组织商品数据
            $categoryProducts = [];
            foreach ($categories as $category) {
                $categoryProducts[$category['id']] = [
                    'category' => [
                        'id' => $category['id'],
                        'name' => $category['name'],
                        'description' => $category['description'],
                    ],
                    'products' => []
                ];
            }
            
            // 将商品分配到对应分类
            foreach ($products as $product) {
                if (isset($categoryProducts[$product['category_id']])) {
                    // 根据发货方式获取库存
                    $stock = 0;
                    if ($product['delivery_method'] == 'auto') {
                        // 自动发货：从库存表获取
                        $stock = isset($stockMap[$product['id']]) ? $stockMap[$product['id']] : 0;
                    } else {
                        // 手动发货：从商品表获取
                        $stock = $product['stock'];
                    }
                    
                    // 计算折扣价格和折扣状态
                    $discountedPrice = $product->getDiscountedPrice();
                    $discountStatus = $product->getDiscountStatus();
                    
                    $categoryProducts[$product['category_id']]['products'][] = [
                        'id' => $product['id'],
                        'name' => $product['name'],
                        'image' => $product['image'],
                        'price' => $discountedPrice, // 显示折扣后的价格
                        'original_price' => $product['original_price'],
                        'stock' => $stock,
                        'delivery_method' => $product['delivery_method'],
                        'discount_status' => $discountStatus // 折扣状态
                    ];
                }
            }
            
            // 移除没有商品的分类
            foreach ($categoryProducts as $key => $value) {
                if (empty($value['products'])) {
                    unset($categoryProducts[$key]);
                }
            }
            
            // 重新索引数组并保持分类顺序
            $result = [];
            foreach ($categories as $category) {
                if (isset($categoryProducts[$category['id']])) {
                    $result[] = $categoryProducts[$category['id']];
                }
            }
            
            return $this->ajaxSuccess('获取成功', [
                'list' => $result
            ]);
            
        } catch (\Exception $e) {
            Log::error('商品列表获取失败：' . $e->getMessage());
            return $this->ajaxError('获取失败');
        }
    }

    /**
     * 获取客服链接
     */
    public function getCustomerLinks()
    {
        try {
            $customerService = CustomerServiceModel::get(1);
            if (!$customerService) {
                return $this->ajaxError('客服信息不存在');
            }
            
            $result = [];
            
            // TG客服
            if ($customerService['tg_show'] == 1 && !empty($customerService['tg_service_link'])) {
                $result['tg_service_link'] = 'https://t.me/' . $customerService['tg_service_link'];
            }
            
            // 在线客服
            if ($customerService['online_show'] == 1 && !empty($customerService['online_service_link'])) {
                $result['online_service_link'] = $customerService['online_service_link'];
            }
            
            // 群链接
            if ($customerService['group_show'] == 1 && !empty($customerService['group_link'])) {
                $result['group_link'] = $customerService['group_link'];
            }
            
            return $this->ajaxSuccess('获取成功', [
                'customer_links' => $result
            ]);
            
        } catch (\Exception $e) {
            Log::error('获取客服链接失败：' . $e->getMessage());
            return $this->ajaxError('获取失败');
        }
    }
} 