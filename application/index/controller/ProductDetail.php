<?php
namespace app\index\controller;

use app\admin\model\Product as ProductModel;
use app\admin\model\ProductCategory as CategoryModel;
use app\admin\model\ProductStock as ProductStockModel;
use app\admin\model\UserRequest as UserRequestModel;
use app\admin\model\Notification;
use think\Log;
use app\common\traits\ApiResponse;
use app\admin\model\ProductPackage;

class ProductDetail extends Base
{
    use ApiResponse;

    /**
     * 商品详情
     */
    public function index()
    {
        try {
            $id = $this->request->param('id', 0);
            if (!$id) {
                return $this->ajaxError('商品ID不能为空');
            }
            
            // 查询商品详情（包含折扣字段）
            $product = ProductModel::where('id', $id)
                ->where('status=1')
                ->field('id,name,image,price,original_price,delivery_method,category_id,description,description_template_id,stock,enable_purchase_limit,purchase_limit_type,purchase_limit_value,discount_enabled,discount_percentage,discount_start_time,discount_end_time,discount_set_time')
                ->with([
                    'category' => function($query) {
                        $query->field('id,name');
                    },
                    'descriptionTemplate' => function($query) {
                        $query->field('id,content');
                    }
                ])
                ->find();
                
            if (!$product) {
                return $this->ajaxError('商品不存在或已下架');
            }
            
            // 根据发货方式获取库存
            $stock = 0;
            if ($product['delivery_method'] == 'auto') {
                // 自动发货：统计库存表
                $stock = ProductStockModel::where('product_id', $id)
                    ->where('status=0')
                    ->count();
            } else {
                // 手动发货：使用商品表stock字段
                $stock = $product['stock'];
            }
                
            // 计算折扣价格和折扣状态
            $discountedPrice = $product->getDiscountedPrice();
            $discountStatus = $product->getDiscountStatus();
            
            // 组装返回数据
            $data = [
                'id' => $product['id'],
                'name' => $product['name'],
                'category_name' => $product['category']['name'],
                'delivery_method' => $product['delivery_method'],
                'price' => $discountedPrice, // 显示折扣后的价格
                'original_price' => $product['original_price'],
                'enable_purchase_limit' => $product['enable_purchase_limit'],
                'purchase_limit_type' => $product['purchase_limit_type'],
                'purchase_limit_value' => $product['purchase_limit_value'],
                'stock' => $stock,
                'image' => $product['image'],
                'description' => $product['description_template_id'] ? $product['descriptionTemplate']['content'] : $product['description'],
                'discount_status' => $discountStatus // 折扣状态
            ];
            
            // 记录商品浏览
            \app\common\service\VisitorTrackingService::trackProductView(
                $product['id'], 
                $product['name'], 
                'detail'
            );
            
            return $this->ajaxSuccess('获取成功', $data);
            
        } catch (\Exception $e) {
            Log::error('商品详情获取失败：' . $e->getMessage());
            return $this->ajaxError('获取失败');
        }
    }

    /**
     * 通知补货
     */
    public function notifyReplenish()
    {
        try {
            $data = $this->request->post();
            
            // 验证数据
            if (empty($data['product_name'])) {
                return $this->ajaxError('商品名称不能为空');
            }
            if (empty($data['email'])) {
                return $this->ajaxError('邮箱不能为空');
            }
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return $this->ajaxError('邮箱格式不正确');
            }
            if (empty($data['quantity'])) {
                return $this->ajaxError('需要数量不能为空');
            }
            if (!is_numeric($data['quantity']) || $data['quantity'] <= 0) {
                return $this->ajaxError('需要数量必须大于0');
            }
            
            // 获取用户IP地址
            $clientIp = \app\common\service\IpLocationService::getClientIp();
            
            // 检查IP今日提交次数限制
            if (UserRequestModel::checkIpLimit($clientIp, 10)) {
                return $this->ajaxError('当前请求过多，请隔日尝试');
            }
            
            // 创建站内信
            $message = new UserRequestModel();
            $message->user_email = $data['email'];
            $message->product_name = isset($data['product_name']) ? $data['product_name'] : '';
            $message->quantity = $data['quantity'];
            $message->remarks = isset($data['remarks']) ? $data['remarks'] : '';
            $message->status = 0; // 0表示未读
            $message->sent_at = date('Y-m-d H:i:s');
            $message->ip_address = $clientIp; // 记录IP地址
            
            if ($message->save()) {
                // 创建补货提醒通知
                Notification::createReplenishmentNotification(
                    $data['email'],
                    $data['product_name'],
                    $data['quantity'],
                    $message->id
                );
                
                return $this->ajaxSuccess('补货通知已提交');
            }
            
            return $this->ajaxError('提交失败');
            
        } catch (\Exception $e) {
            Log::error('补货通知提交失败：' . $e->getMessage());
            return $this->ajaxError('提交失败');
        }
    }

    /**
     * 获取商品安装包列表
     */
    public function getPackages()
    {
        try {
            $id = $this->request->param('id', 0);
            if (!$id) {
                return $this->ajaxError('商品ID不能为空');
            }
            
            // 检查商品是否存在且上架
            $product = ProductModel::where('id', $id)
                ->where('status', 1)
                ->field('id,name')
                ->find();
                
            if (!$product) {
                return $this->ajaxError('商品不存在或已下架');
            }
            
            // 获取安装包列表（只获取显示状态的）
            $packages = ProductPackage::where('product_id', $id)
                ->where('is_show', 1)
                ->field('id,name,type,file_path,download_url,icon,enable_regional_restriction,disallowed_cities')
                ->order('sort asc, id desc')
                ->select();
                
            // 获取用户IP地址
            $clientIp = \app\common\service\IpLocationService::getClientIp();
            
            // 处理安装包数据
            $list = [];
            foreach ($packages as $package) {
                $item = [
                    'id' => $package['id'],
                    'name' => $package['name'],
                    'type' => $package['type'],
                    'type_text' => ProductPackage::getTypeText($package['type']),
                    'icon' => $package['icon'],
                    'download_url' => '',
                    'can_download' => true,
                    'restriction_reason' => ''
                ];
                
                // 检查地域限制
                if ($package['enable_regional_restriction']) {
                    $restrictionResult = $package->checkIpRestriction($clientIp);
                    
                    if ($restrictionResult['restricted']) {
                        $item['can_download'] = false;
                        $item['restriction_reason'] = $restrictionResult['reason'];
                    } else {
                        // 根据类型返回不同的下载地址
                        if ($package['type'] == ProductPackage::TYPE_FILE) {
                            $item['download_url'] = $package['file_path'];
                        } else {
                            $item['download_url'] = $package['download_url'];
                        }
                    }
                } else {
                    // 没有地域限制，直接返回下载地址
                    if ($package['type'] == ProductPackage::TYPE_FILE) {
                        $item['download_url'] = $package['file_path'];
                    } else {
                        $item['download_url'] = $package['download_url'];
                    }
                }
                
                $list[] = $item;
            }
            
            return $this->ajaxSuccess('获取成功', [
                'product_name' => $product['name'],
                'list' => $list
            ]);
            
        } catch (\Exception $e) {
            Log::error('获取商品安装包列表失败：' . $e->getMessage());
            return $this->ajaxError('获取失败');
        }
    }
    
    /**
     * 测试IP地址查询（仅用于调试）
     */
    public function testIpLocation()
    {
        try {
            $ip = $this->request->param('ip', '');
            if (empty($ip)) {
                $ip = \app\common\service\IpLocationService::getClientIp();
            }
            
            $result = \app\common\service\IpLocationService::getLocationByIp($ip);
            
            return $this->ajaxSuccess('查询成功', [
                'ip' => $ip,
                'result' => $result
            ]);
            
        } catch (\Exception $e) {
            return $this->ajaxError('查询失败：' . $e->getMessage());
        }
    }
} 