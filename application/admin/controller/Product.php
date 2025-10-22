<?php
namespace app\admin\controller;

use app\admin\model\Product as ProductModel;
use app\admin\model\ProductCategory;
use app\admin\model\ProductDescriptionTemplate;
use app\admin\model\ProductPackage;
use think\Db;
use think\facade\Filesystem;

class Product extends Base
{
    /**
     * 获取商品列表
     */
    public function index()
    {
        $where = [];
        
        // 商品名称筛选
        $name = input('name/s', '');
        if ($name !== '') {
            $where['name'] = ['like', "%{$name}%"];
        }
        
        // 商品分类筛选
        $category_id = input('category_id/d', '');
        if ($category_id !== '') {
            $where['category_id'] = $category_id;
        }
        
        // 发货方式筛选
        $delivery_method = input('delivery_method/s', '');
        if ($delivery_method !== '') {
            $where['delivery_method'] = $delivery_method;
        }
        
        // 状态筛选
        $status = input('status/d', '');
        if ($status !== '') {
            $where['status'] = $status;
        }
        
        // 库存预警筛选
        $stock_warning = input('stock_warning/d', '');
        if ($stock_warning !== '') {
            if ($stock_warning == 1) {
                $where['stock'] = ['<=', Db::raw('stock_warning')];
            } else {
                $where['stock'] = ['>', Db::raw('stock_warning')];
            }
        }
        
        // 获取总数
        $total = ProductModel::where($where)->count();
        
        // 分页查询
        $page = input('page/d', 1);
        $limit = input('limit/d', 10);
        
        // 获取列表数据（包含折扣字段）
        $list = ProductModel::where($where)
            ->field('*') // 获取所有字段，包括折扣相关字段
            ->with(['category', 'descriptionTemplate'])
            ->order('sort asc, id desc')
            ->page($page, $limit)
            ->select();
            
        // 获取USDT汇率
        $paymentConfig = model('PaymentConfig')->find();
        $usdtRate = $paymentConfig ? $paymentConfig['usdt_rate'] : 0;
            
        // 添加库存统计、USDT价格和折扣信息
        foreach ($list as &$item) {
            // 根据发货方式统计库存
            if ($item['delivery_method'] == 'auto') {
                // 自动发货：统计库存表
                $item['stock_count'] = $item->getStockCount();
                $item['sold_count'] = $item->getSoldCount();
            } else {
                // 手动发货：使用产品表stock字段
                $item['stock_count'] = $item['stock'];
                $item['sold_count'] = 0; // 手动发货不统计已售数量
            }
            
            // 获取折扣信息和折扣后价格（后台逻辑）
            $discountedPrice = $item->getAdminDiscountedPrice();
            $discountStatus = $item->getDiscountStatus();
            
            // 计算USDT价格（原价）
            $item['usdt_price'] = $usdtRate > 0 ? round($item['price'] / $usdtRate, 2) : 0;
            $item['usdt_original_price'] = $usdtRate > 0 ? round($item['original_price'] / $usdtRate, 2) : 0;
            
            // 计算折扣后的USDT价格
            $item['usdt_discounted_price'] = $usdtRate > 0 ? round($discountedPrice / $usdtRate, 2) : 0;
            
            // 添加折扣相关字段
            $item['discount_status'] = $discountStatus; // 折扣状态：无折扣、未开始、进行中、已过期
            $item['discounted_price'] = $discountedPrice; // 折扣后价格
            
            $item['enable_discount'] = isset($item['enable_discount']) ? intval($item['enable_discount']) : 1;
        }
            
        return $this->ajaxSuccess('获取成功', [
            'total' => $total,
            'list' => $list
        ]);
    }
    
    /**
     * 添加商品
     */
    public function add()
    {
        if (!$this->request->isPost()) {
            return $this->ajaxError('请求方式错误');
        }

        $data = $this->request->post();
        
        // 验证必填字段
        if (empty($data['name']) || empty($data['category_id']) || !isset($data['price'])) {
            return $this->ajaxError('缺少必要参数');
        }
        
        // 验证购买限制
        if (!empty($data['enable_purchase_limit'])) {
            if (empty($data['purchase_limit_type']) || empty($data['purchase_limit_value'])) {
                return $this->ajaxError('请设置购买限制类型和限制值');
            }
            
            // 验证限制值
            if ($data['purchase_limit_type'] == 1) { // 固定数量
                if (!is_numeric($data['purchase_limit_value']) || $data['purchase_limit_value'] <= 0) {
                    return $this->ajaxError('固定数量限制值必须大于0');
                }
            } else { // 百分比
                if (!is_numeric($data['purchase_limit_value']) || $data['purchase_limit_value'] <= 0 || $data['purchase_limit_value'] > 100) {
                    return $this->ajaxError('百分比限制值必须在1-100之间');
                }
            }
        } else {
            // 如果未启用限制，清空相关字段
            $data['purchase_limit_type'] = 1;
            $data['purchase_limit_value'] = 0;
        }
        
        // 如果使用自定义详情，设置template_id为null
        // if (empty($data['template_id']) && !empty($data['description'])) {
        //     $data['template_id'] = null;
        // }
        
        // // 验证模板ID是否存在
        // if (!empty($data['template_id'])) {
        //     $template = model('Template')->where('id', $data['template_id'])->find();
        //     if (!$template) {
        //         return $this->ajaxError('商品模板不存在');
        //     }
        // }

        // 验证数据
        $validate = validate('Product');
        if (!$validate->scene('add')->check($data)) {
            $this->add_log('商品管理', '新增商品：' . $data['name'] . ' - 验证失败', '失败');
            return $this->ajaxError($validate->getError());
        }
        
        // 检查分类是否存在
        $category = ProductCategory::find($data['category_id']);
        if (!$category) {
            $this->add_log('商品管理', '新增商品：' . $data['name'] . ' - 分类不存在', '失败');
            return $this->ajaxError('商品分类不存在');
        }
        
        // 处理商品详情
        if (isset($data['use_template']) && $data['use_template'] == 1) {
            if (empty($data['description_template_id'])) {
                $this->add_log('商品管理', '新增商品：' . $data['name'] . ' - 未选择详情模板', '失败');
                return $this->ajaxError('请选择商品详情模板');
            }
            // 检查模板是否存在
            $template = ProductDescriptionTemplate::find($data['description_template_id']);
            if (!$template) {
                $this->add_log('商品管理', '新增商品：' . $data['name'] . ' - 详情模板不存在', '失败');
                return $this->ajaxError('商品详情模板不存在');
            }
            $data['description'] = null;
        } else {
            if (empty($data['description'])) {
                $this->add_log('商品管理', '新增商品：' . $data['name'] . ' - 未填写商品详情', '失败');
                return $this->ajaxError('请输入商品详情');
            }
            $data['description_template_id'] = null;
        }
        
        // 处理库存
        if ($data['delivery_method'] == 'manual') {
            // 手动发货：设置库存字段
            if (!isset($data['stock']) || $data['stock'] < 0) {
                $this->add_log('商品管理', '新增商品：' . $data['name'] . ' - 库存数量不正确', '失败');
                return $this->ajaxError('库存数量必须大于或等于0');
            }
        } else {
            // 自动发货：库存字段设为0
            $data['stock'] = 0;
        }
        
        // 验证图片链接
        if (isset($data['image']) && !empty($data['image'])) {
            if (!preg_match('/^\/uploads\/product\/.*\.(jpg|jpeg|png|gif)$/', $data['image'])) {
                $this->add_log('商品管理', '新增商品：' . $data['name'] . ' - 图片格式不正确', '失败');
                return $this->ajaxError('商品图片格式不正确');
            }
            
            // 检查图片文件是否存在
            $imagePath = ROOT_PATH . $data['image'];
            if (!file_exists($imagePath)) {
                $this->add_log('商品管理', '新增商品：' . $data['name'] . ' - 图片不存在', '失败');
                return $this->ajaxError('商品图片不存在');
            }
        }
        
        // 新增：支持enable_discount字段，默认为1
        $data['enable_discount'] = isset($data['enable_discount']) ? intval($data['enable_discount']) : 1;
        
        // 开启事务
        Db::startTrans();
        try {
            // 创建商品
            $product = ProductModel::create($data);
            
            if (!$product) {
                throw new \Exception('创建商品失败');
            }
            
            Db::commit();
            $this->add_log('商品管理', '新增商品：' . $data['name'], '成功');
            return $this->ajaxSuccess('添加成功');
            
        } catch (\Exception $e) {
            Db::rollback();
            $this->add_log('商品管理', '新增商品：' . $data['name'] . ' - ' . $e->getMessage(), '失败');
            return $this->ajaxError('添加失败：' . $e->getMessage().'|'.$e->getLine().'|'.$e->getFile());
        }
    }
    
    /**
     * 编辑商品
     */
    public function edit()
    {
        if (!$this->request->isPost()) {
            return $this->ajaxError('请求方式错误');
        }

        $data = $this->request->post();
        
        // 验证必填字段
        if (empty($data['id']) || empty($data['name']) || empty($data['category_id']) || !isset($data['price'])) {
            return $this->ajaxError('缺少必要参数');
        }
        
        // 验证商品是否存在
        $product = ProductModel::find($data['id']);
        if (!$product) {
            return $this->ajaxError('商品不存在');
        }
        
        // 验证购买限制
        if (!empty($data['enable_purchase_limit'])) {
            if (empty($data['purchase_limit_type']) || empty($data['purchase_limit_value'])) {
                return $this->ajaxError('请设置购买限制类型和限制值');
            }
            
            // 验证限制值
            if ($data['purchase_limit_type'] == 1) { // 固定数量
                if (!is_numeric($data['purchase_limit_value']) || $data['purchase_limit_value'] <= 0) {
                    return $this->ajaxError('固定数量限制值必须大于0');
                }
            } else { // 百分比
                if (!is_numeric($data['purchase_limit_value']) || $data['purchase_limit_value'] <= 0 || $data['purchase_limit_value'] > 100) {
                    return $this->ajaxError('百分比限制值必须在1-100之间');
                }
            }
        } else {
            // 如果未启用限制，清空相关字段
            $data['purchase_limit_type'] = 1;
            $data['purchase_limit_value'] = 0;
        }
        
        // 如果使用自定义详情，设置template_id为null
        if (empty($data['template_id']) && !empty($data['description'])) {
            $data['template_id'] = null;
        }
        
        // 验证模板ID是否存在
        if (!empty($data['template_id'])) {
            $template = model('Template')->where('id', $data['template_id'])->find();
            if (!$template) {
                return $this->ajaxError('商品模板不存在');
            }
        }

        // 验证数据
        $validate = validate('Product');
        if (!$validate->scene('edit')->check($data)) {
            $this->add_log('商品管理', '编辑商品：' . $data['name'] . ' - 验证失败', '失败');
            return $this->ajaxError($validate->getError());
        }
        
        // 检查分类是否存在
        $category = ProductCategory::find($data['category_id']);
        if (!$category) {
            $this->add_log('商品管理', '编辑商品：' . $data['name'] . ' - 分类不存在', '失败');
            return $this->ajaxError('商品分类不存在');
        }
        
        // 验证图片链接
        if (!empty($data['image'])) {
            if (!preg_match('/^\/uploads\/product\/.*\.(jpg|jpeg|png|gif)$/', $data['image'])) {
                $this->add_log('商品管理', '编辑商品：' . $data['name'] . ' - 图片格式不正确', '失败');
                return $this->ajaxError('商品图片格式不正确');
            }
            
            // 检查图片文件是否存在
            $imagePath = ROOT_PATH . $data['image'];
            if (!file_exists($imagePath)) {
                $this->add_log('商品管理', '编辑商品：' . $data['name'] . ' - 图片不存在', '失败');
                return $this->ajaxError('商品图片不存在');
            }
        }
        
        // 处理商品详情
        if (isset($data['use_template']) && $data['use_template'] == 1) {
            if (empty($data['description_template_id'])) {
                $this->add_log('商品管理', '编辑商品：' . $data['name'] . ' - 未选择详情模板', '失败');
                return $this->ajaxError('请选择商品详情模板');
            }
            $data['description'] = '';
        } else {
            $data['use_template'] = 0;
            $data['description_template_id'] = 0;
        }
        
        // 处理库存
        if ($data['delivery_method'] == 'manual') {
            // 手动发货：设置库存字段
            if (!isset($data['stock']) || $data['stock'] < 0) {
                $this->add_log('商品管理', '编辑商品：' . $data['name'] . ' - 库存数量不正确', '失败');
                return $this->ajaxError('库存数量必须大于或等于0');
            }
        } else {
            // 自动发货：库存字段设为0
            $data['stock'] = 0;
        }
        
        // 设置enable_discount字段
        $data['enable_discount'] = isset($data['enable_discount']) ? intval($data['enable_discount']) : 1;
        
        // 开启事务
        Db::startTrans();
        try {
            // 更新商品
            $result = $product->allowField(true)->save($data);
            
            if ($result === false) {
                throw new \Exception('更新商品失败');
            }
            
            Db::commit();
            $this->add_log('商品管理', '编辑商品：' . $data['name'], '成功');
            return $this->ajaxSuccess('更新成功');
            
        } catch (\Exception $e) {
            Db::rollback();
            $this->add_log('商品管理', '编辑商品：' . $data['name'] . ' - ' . $e->getMessage(), '失败');
            return $this->ajaxError('更新失败：' . $e->getMessage());
        }
    }
    
    /**
     * 删除商品
     */
    public function delete()
    {
        $id = input('id/d');
        if (!$id) {
            $this->add_log('商品管理', '删除商品：参数错误', '失败');
            return $this->ajaxError('参数错误');
        }
        
        $product = ProductModel::find($id);
        if (!$product) {
            $this->add_log('商品管理', '删除商品：商品不存在', '失败');
            return $this->ajaxError('商品不存在');
        }
        
        if (!$product->delete()) {
            $this->add_log('商品管理', '删除商品：' . $product['name'], '失败');
            return $this->ajaxError('删除失败');
        }
        
        $this->add_log('商品管理', '删除商品：' . $product['name'], '成功');
        return $this->ajaxSuccess('删除成功');
    }
    
    /**
     * 上传商品图片
     */
    public function uploadImage()
    {
        if (!$this->request->isPost()) {
            return $this->ajaxError('请求方式错误');
        }

        try {
            $file = request()->file('image');
            if (!$file) {
                return $this->ajaxError('请选择要上传的图片');
            }

            // 使用utils中的上传功能
            $result = \app\common\utils\Upload::image($file, 'product', [
                'size' => 5242880, // 5M
                'ext' => 'jpg,jpeg,png,gif'
            ]);
            
            if ($result['code'] === 0) {
                $this->add_log('商品管理', '上传商品图片', '失败');
                return $this->ajaxError($result['msg']);
            }

            // 记录操作日志
            $this->add_log('商品管理', '上传商品图片', '成功');
            
            return $this->ajaxSuccess('上传成功', ['url' => $result['data']['file_path']]);
        } catch (\Exception $e) {
            $this->add_log('商品管理', '上传商品图片', '失败');
            return $this->ajaxError($e->getMessage());
        }
    }

    /**
     * 获取商品选项数据
     */
    public function getOptions()
    {
        if (!$this->request->isGet()) {
            return $this->ajaxError('请求方式错误');
        }

        // 商品状态选项
        $statusOptions = [
            ['value' => 1, 'label' => '上架中'],
            ['value' => 0, 'label' => '已下架']
        ];

        // 库存预警选项
        $stockWarningOptions = [
            ['value' => 1, 'label' => '已达预警'],
            ['value' => 0, 'label' => '未达预警']
        ];

        // 发货方式选项
        $deliveryMethods = [
            ['value' => 'auto', 'label' => '自动发货'],
            ['value' => 'manual', 'label' => '手动发货']
        ];

        return $this->ajaxSuccess('获取成功', [
            'status_options' => $statusOptions,
            'stock_warning_options' => $stockWarningOptions,
            'delivery_methods' => $deliveryMethods
        ]);
    }

    /**
     * 修改商品状态
     */
    public function updateStatus()
    {
        if (!$this->request->isPost()) {
            return $this->ajaxError('请求方式错误');
        }

        $id = $this->request->post('id/d');
        $status = $this->request->post('status/d');

        if (!$id) {
            return $this->ajaxError('参数错误');
        }

        if (!in_array($status, [0, 1])) {
            return $this->ajaxError('状态值错误');
        }

        $product = model('Product')->find($id);
        if (!$product) {
            return $this->ajaxError('商品不存在');
        }

        try {
            $product->status = $status;
            if ($product->save() === false) {
                throw new \Exception('修改失败');
            }

            // 记录日志
            $statusText = $status == 1 ? '上架' : '下架';
            $this->add_log('商品管理', '修改商品状态：' . $product['name'] . ' - ' . $statusText, '成功');

            return $this->ajaxSuccess('修改成功');
        } catch (\Exception $e) {
            // 记录日志
            $statusText = $status == 1 ? '上架' : '下架';
            $this->add_log('商品管理', '修改商品状态：' . $product['name'] . ' - ' . $statusText, '失败');

            return $this->ajaxError('修改失败：' . $e->getMessage());
        }
    }

    /**
     * 获取商品视频信息
     */
    public function getVideoInfo()
    {
        $productId = input('product_id/d');
        if (!$productId) {
            return $this->ajaxError('商品ID不能为空');
        }
        
        try {
            $product = ProductModel::find($productId);
            if (!$product) {
                return $this->ajaxError('商品不存在');
            }
            
            $videoInfo = [
                'has_video' => $product->hasTutorialVideo(),
                'video_url' => isset($product['tutorial_video']) ? $product['tutorial_video'] : '',
                'video_name' => isset($product['tutorial_video_name']) ? $product['tutorial_video_name'] : '',
                'video_size' => isset($product['tutorial_video_size']) ? $product['tutorial_video_size'] : 0,
                'video_size_text' => $product->getTutorialVideoSizeTextAttr(null, $product->data),
                'video_status' => isset($product['tutorial_video_status']) ? $product['tutorial_video_status'] : 0,
                'video_status_text' => $product->getTutorialVideoStatusTextAttr(null, $product->data)
            ];
                
            return $this->ajaxSuccess('获取成功', $videoInfo);
            
        } catch (\Exception $e) {
            $this->add_log('商品管理', '获取商品视频信息失败：' . $e->getMessage(), '失败');
            return $this->ajaxError('获取失败');
        }
    }
    
    /**
     * 上传商品视频
     */
    public function uploadVideo()
    {
        if (!$this->request->isPost()) {
            return $this->ajaxError('请求方式错误');
        }
        
        $productId = input('product_id/d');
        if (!$productId) {
            return $this->ajaxError('商品ID不能为空');
        }
        
        try {
            $file = request()->file('video');
            if (!$file) {
                return $this->ajaxError('请选择视频文件');
            }
            
            // 检查商品是否存在
            $product = ProductModel::find($productId);
            if (!$product) {
                return $this->ajaxError('商品不存在');
            }
            
            // 检查是否已有视频（每个商品最多1个视频）
            if (!empty($product['tutorial_video'])) {
                return $this->ajaxError('该商品已有教程视频，请先删除现有视频再上传新视频');
            }
            
            // 先获取文件信息（在文件被移动前）
            $fileName = $file->getInfo('name');
            $fileSize = $file->getSize();
            
            // 使用utils中的上传功能
            $result = \app\common\utils\Upload::secure($file, 'product/videos', [
                'size' => 209715200, // 200MB
                'ext' => 'mp4,avi,mov,wmv,flv,webm',
                'path' => 'uploads/product/videos/' . date('Y/m/d')
            ]);
            
            if ($result['code'] === 0) {
                $this->add_log('商品管理', '上传商品视频失败：' . $result['msg'], '失败');
                return $this->ajaxError($result['msg']);
            }
            
            // 开启事务
            Db::startTrans();
            try {
                // 更新商品视频信息
                $updateData = [
                    'tutorial_video' => $result['data']['file_path'],
                    'tutorial_video_name' => $fileName,
                    'tutorial_video_size' => $fileSize,
                    'tutorial_video_status' => 1
                ];
                
                $saveResult = $product->save($updateData);
                if ($saveResult === false) {
                    throw new \Exception('保存视频信息失败');
                }
                
                Db::commit();
                
                $this->add_log('商品管理', '上传商品视频：' . $product['name'], '成功');
                return $this->ajaxSuccess('视频上传成功', [
                    'video_url' => $result['data']['file_path'],
                    'video_name' => $fileName,
                    'video_size' => $fileSize,
                    'video_size_text' => $this->formatFileSize($fileSize)
                ]);
                
            } catch (\Exception $e) {
                Db::rollback();
                // 删除已上传的文件
                $uploadedFile = ROOT_PATH . 'public' . ltrim($result['data']['file_path'], '/');
                if (file_exists($uploadedFile)) {
                    @unlink($uploadedFile);
                }
                throw $e;
            }
            
        } catch (\Exception $e) {
            $this->add_log('商品管理', '上传商品视频失败：' . $e->getMessage(), '失败');
            return $this->ajaxError('上传失败：' . $e->getMessage());
        }
    }
    
    /**
     * 删除商品视频
     */
    public function deleteVideo()
    {
        if (!$this->request->isPost()) {
            return $this->ajaxError('请求方式错误');
        }
        
        $productId = input('product_id/d');
        if (!$productId) {
            return $this->ajaxError('商品ID不能为空');
        }
        
        try {
            $product = ProductModel::find($productId);
            if (!$product) {
                return $this->ajaxError('商品不存在');
            }
            
            if (empty($product['tutorial_video'])) {
                return $this->ajaxError('该商品没有视频');
            }
            
            // 先保存文件路径（在更新数据库之前）
            $videoPath = $product['tutorial_video'];
            
            // 开启事务
            Db::startTrans();
            try {
                // 清空商品视频信息
                $updateData = [
                    'tutorial_video' => '',
                    'tutorial_video_name' => '',
                    'tutorial_video_size' => 0,
                    'tutorial_video_status' => 0
                ];
                
                $result = $product->save($updateData);
                if (!$result) {
                    throw new \Exception('更新商品信息失败');
                }
                
                Db::commit();
                // 删除文件（数据库更新成功后）
                $filePath = ROOT_PATH . DS . ltrim($videoPath, '/');
                
                if (file_exists($filePath)) {
                    if (!@unlink($filePath)) {
                        // 记录文件删除失败但不影响操作结果
                        \think\Log::warning('视频文件删除失败：' . $filePath);
                    }
                }
                
                $this->add_log('商品管理', '删除商品视频：' . $product['name'], '成功');
                return $this->ajaxSuccess('删除成功');
                
            } catch (\Exception $e) {
                Db::rollback();
                throw $e;
            }
            
        } catch (\Exception $e) {
            $this->add_log('商品管理', '删除商品视频失败：' . $e->getMessage(), '失败');
            return $this->ajaxError('删除失败：' . $e->getMessage());
        }
    }
    
    /**
     * 更新视频状态
     */
    public function updateVideoStatus()
    {
        if (!$this->request->isPost()) {
            return $this->ajaxError('请求方式错误');
        }
        
        $productId = input('product_id/d');
        $status = input('status/d');
        
        if (!$productId) {
            return $this->ajaxError('商品ID不能为空');
        }
        
        if (!in_array($status, [0, 1])) {
            return $this->ajaxError('状态值错误');
        }
        
        try {
            $product = ProductModel::find($productId);
            if (!$product) {
                return $this->ajaxError('商品不存在');
            }
            
            if (empty($product['tutorial_video'])) {
                return $this->ajaxError('该商品没有视频');
            }
            
            $product->tutorial_video_status = $status;
            if ($product->save()) {
                $statusText = $status == 1 ? '启用' : '停用';
                $this->add_log('商品管理', '更新视频状态：' . $product['name'] . ' - ' . $statusText, '成功');
                return $this->ajaxSuccess('更新成功');
            } else {
                return $this->ajaxError('更新失败');
            }
            
        } catch (\Exception $e) {
            $this->add_log('商品管理', '更新视频状态失败：' . $e->getMessage(), '失败');
            return $this->ajaxError('更新失败：' . $e->getMessage());
        }
    }
    
    /**
     * 格式化文件大小
     */
    private function formatFileSize($size)
    {
        if (empty($size)) {
            return '未知';
        }
        
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        
        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }
        
        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * 获取商品安装包列表
     */
    public function getPackages()
    {
        $product_id = input('product_id/d');
        if (!$product_id) {
            return $this->ajaxError('参数错误');
        }
        
        // 获取安装包列表
        $list = ProductPackage::where('product_id', $product_id)
            ->order('sort asc, id desc')
            ->select();
            
        // 处理安装包数据，包含地域限制信息
        foreach ($list as &$item) {
            $item['disallowed_cities'] = $item->getDisallowedCities();
            $item['disallowed_cities_count'] = count($item['disallowed_cities']);
        }
            
        return $this->ajaxSuccess('获取成功', ['list' => $list]);
    }
    
    /**
     * 添加商品安装包
     */
    public function addPackage()
    {
        if (!$this->request->isPost()) {
            return $this->ajaxError('请求方式错误');
        }
        
        $data = $this->request->post();
        
        // 验证必填字段
        if (empty($data['product_id']) || empty($data['name']) || empty($data['type'])) {
            return $this->ajaxError('缺少必要参数');
        }
        
        // 处理地域限制设置
        $data['enable_regional_restriction'] = isset($data['enable_regional_restriction']) ? intval($data['enable_regional_restriction']) : 0;
        if ($data['enable_regional_restriction'] && !empty($data['disallowed_cities'])) {
            if (is_string($data['disallowed_cities'])) {
                $data['disallowed_cities'] = json_decode($data['disallowed_cities'], true);
            }
            if (!is_array($data['disallowed_cities'])) {
                return $this->ajaxError('禁止城市数据格式错误');
            }
        } else {
            $data['disallowed_cities'] = null;
        }
        
        // 验证商品是否存在
        $product = ProductModel::find($data['product_id']);
        if (!$product) {
            return $this->ajaxError('商品不存在');
        }
        
        // 验证图标
        if (empty($data['icon'])) {
            return $this->ajaxError('请上传安装包图标');
        }
        if (!preg_match('/^\/uploads\/package\/icon\/.*\.(jpg|jpeg|png|gif)$/', $data['icon'])) {
            return $this->ajaxError('安装包图标格式不正确');
        }
        
        // 根据类型验证
        if ($data['type'] == ProductPackage::TYPE_FILE) {
            if (empty($data['file_path'])) {
                return $this->ajaxError('请上传安装包文件');
            }
            $data['download_url'] = null;
        } else {
            if (empty($data['download_url'])) {
                return $this->ajaxError('请输入下载链接');
            }
            $data['file_path'] = null;
        }
        
        // 创建安装包记录
        $package = new ProductPackage;
        
        // 处理禁止城市数据
        if ($data['enable_regional_restriction'] && !empty($data['disallowed_cities'])) {
            $package->setDisallowedCities($data['disallowed_cities']);
            unset($data['disallowed_cities']);
        }
        
        if (!$package->allowField(true)->save($data)) {
            $this->add_log('商品管理', '添加安装包：' . $data['name'], '失败');
            return $this->ajaxError('添加失败');
        }
        
        $this->add_log('商品管理', '添加安装包：' . $data['name'], '成功');
        return $this->ajaxSuccess('添加成功');
    }
    
    /**
     * 编辑商品安装包
     */
    public function editPackage()
    {
        if (!$this->request->isPost()) {
            return $this->ajaxError('请求方式错误');
        }
        
        $data = $this->request->post();
        
        // 验证必填字段
        if (empty($data['id']) || empty($data['name']) || empty($data['type'])) {
            return $this->ajaxError('缺少必要参数');
        }
        
        // 处理地域限制设置
        $data['enable_regional_restriction'] = isset($data['enable_regional_restriction']) ? intval($data['enable_regional_restriction']) : 0;
        if ($data['enable_regional_restriction'] && !empty($data['disallowed_cities'])) {
            if (is_string($data['disallowed_cities'])) {
                $data['disallowed_cities'] = json_decode($data['disallowed_cities'], true);
            }
            if (!is_array($data['disallowed_cities'])) {
                return $this->ajaxError('禁止城市数据格式错误');
            }
        } else {
            $data['disallowed_cities'] = null;
        }
        
        // 验证安装包是否存在
        $package = ProductPackage::find($data['id']);
        if (!$package) {
            return $this->ajaxError('安装包不存在');
        }
        
        // 验证图标
        if (empty($data['icon'])) {
            return $this->ajaxError('请上传安装包图标');
        }
        if (!preg_match('/^\/uploads\/package\/icon\/.*\.(jpg|jpeg|png|gif)$/', $data['icon'])) {
            return $this->ajaxError('安装包图标格式不正确');
        }
        
        // 根据类型验证
        if ($data['type'] == ProductPackage::TYPE_FILE) {
            if (empty($data['file_path'])) {
                return $this->ajaxError('请上传安装包文件');
            }
            $data['download_url'] = null;
        } else {
            if (empty($data['download_url'])) {
                return $this->ajaxError('请输入下载链接');
            }
            $data['file_path'] = null;
        }
        
        // 处理禁止城市数据
        if ($data['enable_regional_restriction'] && !empty($data['disallowed_cities'])) {
            $package->setDisallowedCities($data['disallowed_cities']);
            unset($data['disallowed_cities']);
        } else {
            $package->disallowed_cities = null;
        }
        
        // 更新安装包记录
        if (!$package->allowField(true)->save($data)) {
            $this->add_log('商品管理', '编辑安装包：' . $data['name'], '失败');
            return $this->ajaxError('更新失败');
        }
        
        $this->add_log('商品管理', '编辑安装包：' . $data['name'], '成功');
        return $this->ajaxSuccess('更新成功');
    }
    
    /**
     * 删除商品安装包
     */
    public function deletePackage()
    {
        $id = input('id/d');
        if (!$id) {
            return $this->ajaxError('参数错误');
        }
        
        $package = ProductPackage::find($id);
        if (!$package) {
            return $this->ajaxError('安装包不存在');
        }
        
        // 如果是文件类型，删除文件
        if ($package['type'] == ProductPackage::TYPE_FILE && !empty($package['file_path'])) {
            $filePath = ROOT_PATH . $package['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        
        if (!$package->delete()) {
            $this->add_log('商品管理', '删除安装包：' . $package['name'], '失败');
            return $this->ajaxError('删除失败');
        }
        
        $this->add_log('商品管理', '删除安装包：' . $package['name'], '成功');
        return $this->ajaxSuccess('删除成功');
    }
    
    /**
     * 上传安装包文件
     */
    public function uploadPackage()
    {
        if (!$this->request->isPost()) {
            return $this->ajaxError('请求方式错误');
        }

        try {
            $file = request()->file('file');
            if (!$file) {
                return $this->ajaxError('请选择要上传的文件');
            }

            // 使用utils中的上传功能
            $result = \app\common\utils\Upload::secure($file, 'package', [
                'size' => 524288000, // 500M
                'ext' => 'zip,rar,7z,exe,msi,apk,ipa,dmg,deb,rpm,appx,appxbundle,xapk,aab',
                'path' => 'uploads/package/' . date('Ymd')
            ]);
            
            if ($result['code'] === 0) {
                $this->add_log('商品管理', '上传安装包文件', '失败');
                return $this->ajaxError($result['msg']);
            }

            // 记录操作日志
            $this->add_log('商品管理', '上传安装包文件', '成功');
            
            return $this->ajaxSuccess('上传成功', ['url' => $result['data']['file_path']]);
        } catch (\Exception $e) {
            $this->add_log('商品管理', '上传安装包文件', '失败');
            return $this->ajaxError($e->getMessage());
        }
    }
    
    /**
     * 更新安装包显示状态
     */
    public function updatePackageStatus()
    {
        if (!$this->request->isPost()) {
            return $this->ajaxError('请求方式错误');
        }
        
        $id = input('id/d');
        $is_show = input('is_show/d');
        
        if (!$id || !isset($is_show)) {
            return $this->ajaxError('参数错误');
        }
        
        $package = ProductPackage::find($id);
        if (!$package) {
            return $this->ajaxError('安装包不存在');
        }
        
        // 检查状态是否发生变化
        if ($package['is_show'] == $is_show) {
            return $this->ajaxError('状态未发生变化');
        }
        
        // 更新状态
        $result = $package->save(['is_show' => $is_show]);
        if ($result === false) {
            $this->add_log('商品管理', '更新安装包状态：' . $package['name'], '失败');
            return $this->ajaxError('更新失败');
        }
        
        $this->add_log('商品管理', '更新安装包状态：' . $package['name'], '成功');
        return $this->ajaxSuccess('更新成功');
    }
    
    /**
     * 更新安装包排序
     */
    public function updatePackageSort()
    {
        if (!$this->request->isPost()) {
            return $this->ajaxError('请求方式错误');
        }
        
        $sorts = $this->request->post('sorts');
        if (empty($sorts)) {
            return $this->ajaxError('参数错误');
        }
        
        // 确保sorts是数组
        if (!is_array($sorts)) {
            // 尝试将JSON字符串转换为数组
            if (is_string($sorts)) {
                $sorts = json_decode($sorts, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return $this->ajaxError('排序数据格式错误');
                }
            } else {
                return $this->ajaxError('排序数据格式错误');
            }
        }
        
        // 验证数组格式
        if (empty($sorts) || !is_array($sorts)) {
            return $this->ajaxError('排序数据不能为空');
        }
        
        Db::startTrans();
        try {
            foreach ($sorts as $item) {
                if (!isset($item['id']) || !isset($item['sort'])) {
                    throw new \Exception('参数错误：缺少id或sort字段');
                }
                
                // 确保id和sort是整数
                $id = intval($item['id']);
                $sort = intval($item['sort']);
                
                if ($id <= 0) {
                    throw new \Exception('安装包ID必须大于0');
                }
                
                $package = ProductPackage::find($id);
                if (!$package) {
                    throw new \Exception('安装包不存在：ID=' . $id);
                }
                
                if ($package->save(['sort' => $sort]) === false) {
                    throw new \Exception('更新失败：ID=' . $id);
                }
            }
            
            Db::commit();
            $this->add_log('商品管理', '更新安装包排序', '成功');
            return $this->ajaxSuccess('更新成功');
        } catch (\Exception $e) {
            Db::rollback();
            $this->add_log('商品管理', '更新安装包排序：' . $e->getMessage(), '失败');
            return $this->ajaxError($e->getMessage());
        }
    }

    /**
     * 上传安装包图标
     */
    public function uploadPackageIcon()
    {
        if (!$this->request->isPost()) {
            return $this->ajaxError('请求方式错误');
        }

        try {
            $file = request()->file('icon');
            if (!$file) {
                return $this->ajaxError('请选择要上传的图标');
            }

            // 使用utils中的上传功能
            $result = \app\common\utils\Upload::image($file, 'package/icon', [
                'size' => 2097152, // 2M
                'ext' => 'jpg,jpeg,png,gif'
            ]);
            
            if ($result['code'] === 0) {
                $this->add_log('商品管理', '上传安装包图标', '失败');
                return $this->ajaxError($result['msg']);
            }

            // 记录操作日志
            $this->add_log('商品管理', '上传安装包图标', '成功');
            
            return $this->ajaxSuccess('上传成功', ['url' => $result['data']['file_path']]);
        } catch (\Exception $e) {
            $this->add_log('商品管理', '上传安装包图标', '失败');
            return $this->ajaxError($e->getMessage());
        }
    }

    /**
     * 修改商品排序
     */
    public function updateSort()
    {
        if (!$this->request->isPost()) {
            return $this->ajaxError('请求方式错误');
        }

        $id = $this->request->post('id/d');
        $sort = $this->request->post('sort/d');

        if (!$id) {
            return $this->ajaxError('商品ID不能为空');
        }

        if (!is_numeric($sort) || $sort < 0) {
            return $this->ajaxError('排序值必须为非负整数');
        }

        // 检查商品是否存在
        $product = ProductModel::find($id);
        if (!$product) {
            return $this->ajaxError('商品不存在');
        }

        // 检查排序是否已被占用（排除当前商品，且只检查同一分类内）
        $existingProduct = ProductModel::where('sort', $sort)
            ->where('id', '<>', $id)
            ->where('category_id', $product['category_id'])
            ->find();
        
        if ($existingProduct) {
            return $this->ajaxError('该排序已被占用');
        }

        try {
            // 更新排序
            $product->sort = $sort;
            if ($product->save() === false) {
                throw new \Exception('更新排序失败');
            }

            // 记录操作日志
            $this->add_log('商品管理', '修改商品排序：' . $product['name'] . ' - 排序值：' . $sort, '成功');

            return $this->ajaxSuccess('排序修改成功');
        } catch (\Exception $e) {
            // 记录操作日志
            $this->add_log('商品管理', '修改商品排序：' . $product['name'] . ' - ' . $e->getMessage(), '失败');

            return $this->ajaxError('排序修改失败：' . $e->getMessage());
        }
    }

    /**
     * 设置商品折扣
     */
    public function setDiscount()
    {
        if (!$this->request->isPost()) {
            return $this->ajaxError('请求方式错误');
        }

        $data = $this->request->post();
        
        // 验证必填字段
        if (empty($data['id'])) {
            return $this->ajaxError('商品ID不能为空');
        }

        // 验证商品是否存在
        $product = ProductModel::find($data['id']);
        if (!$product) {
            return $this->ajaxError('商品不存在');
        }

        // 验证折扣数据
        if (isset($data['enabled']) && $data['enabled']) {
            if (empty($data['percentage']) || !is_numeric($data['percentage']) || $data['percentage'] <= 0 || $data['percentage'] > 100) {
                return $this->ajaxError('折扣百分比必须在1-100之间');
            }
            
            if (empty($data['start_time']) || empty($data['end_time'])) {
                return $this->ajaxError('请设置折扣开始时间和结束时间');
            }
            
            // 验证时间
            $startTime = strtotime($data['start_time']);
            $endTime = strtotime($data['end_time']);
            $currentTime = time();
            
            if ($startTime < $currentTime) {
                return $this->ajaxError('开始时间不能早于当前时间');
            }
            
            if ($endTime <= $startTime) {
                return $this->ajaxError('结束时间必须晚于开始时间');
            }
        }

        try {
            // 设置折扣数据
            $discountData = [
                'enabled' => isset($data['enabled']) ? intval($data['enabled']) : 0,
                'percentage' => isset($data['percentage']) ? floatval($data['percentage']) : 0,
                'start_time' => isset($data['start_time']) ? $data['start_time'] : null,
                'end_time' => isset($data['end_time']) ? $data['end_time'] : null
            ];

            $result = $product->setDiscount($discountData);
            
            if ($result) {
                $status = $discountData['enabled'] ? '启用折扣' : '关闭折扣';
                $this->add_log('商品管理', $status . '：' . $product['name'], '成功');
                return $this->ajaxSuccess($status . '成功');
            } else {
                throw new \Exception('设置失败');
            }
            
        } catch (\Exception $e) {
            $this->add_log('商品管理', '设置商品折扣：' . $product['name'] . ' - ' . $e->getMessage(), '失败');
            return $this->ajaxError('设置失败：' . $e->getMessage());
        }
    }

    /**
     * 批量设置商品折扣
     */
    public function batchSetDiscount()
    {
        if (!$this->request->isPost()) {
            return $this->ajaxError('请求方式错误');
        }

        $data = $this->request->post();
        
        // 验证必填字段
        if (empty($data['product_ids']) || !is_array($data['product_ids'])) {
            return $this->ajaxError('请选择要设置折扣的商品');
        }

        // 验证折扣数据
        if (isset($data['enabled']) && $data['enabled']) {
            if (empty($data['percentage']) || !is_numeric($data['percentage']) || $data['percentage'] <= 0 || $data['percentage'] > 100) {
                return $this->ajaxError('折扣百分比必须在1-100之间');
            }
            
            if (empty($data['start_time']) || empty($data['end_time'])) {
                return $this->ajaxError('请设置折扣开始时间和结束时间');
            }
            
            // 验证时间
            $startTime = strtotime($data['start_time']);
            $endTime = strtotime($data['end_time']);
            $currentTime = time();
            
            if ($startTime < $currentTime) {
                return $this->ajaxError('开始时间不能早于当前时间');
            }
            
            if ($endTime <= $startTime) {
                return $this->ajaxError('结束时间必须晚于开始时间');
            }
        }

        try {
            // 开启事务
            Db::startTrans();

            $successCount = 0;
            $failCount = 0;
            $productNames = [];

            foreach ($data['product_ids'] as $productId) {
                $product = ProductModel::find($productId);
                if (!$product) {
                    $failCount++;
                    continue;
                }

                // 设置折扣数据
                $discountData = [
                    'enabled' => isset($data['enabled']) ? intval($data['enabled']) : 0,
                    'percentage' => isset($data['percentage']) ? floatval($data['percentage']) : 0,
                    'start_time' => isset($data['start_time']) ? $data['start_time'] : null,
                    'end_time' => isset($data['end_time']) ? $data['end_time'] : null
                ];

                $result = $product->setDiscount($discountData);
                
                if ($result) {
                    $successCount++;
                    $productNames[] = $product['name'];
                } else {
                    $failCount++;
                }
            }

            if ($successCount > 0) {
                Db::commit();
                $status = isset($data['enabled']) && $data['enabled'] ? '批量启用折扣' : '批量关闭折扣';
                $this->add_log('商品管理', $status . '：成功设置' . $successCount . '个商品', '成功');
                
                $message = $status . '成功，共处理' . count($data['product_ids']) . '个商品，成功' . $successCount . '个';
                if ($failCount > 0) {
                    $message .= '，失败' . $failCount . '个';
                }
                
                return $this->ajaxSuccess($message);
            } else {
                Db::rollback();
                $this->add_log('商品管理', '批量设置商品折扣：全部失败', '失败');
                return $this->ajaxError('批量设置失败，请检查商品是否存在');
            }
            
        } catch (\Exception $e) {
            Db::rollback();
            $this->add_log('商品管理', '批量设置商品折扣：' . $e->getMessage(), '失败');
            return $this->ajaxError('批量设置失败：' . $e->getMessage());
        }
    }

    /**
     * 获取商品折扣信息
     */
    public function getDiscountInfo()
    {
        $id = $this->request->param('id', 0);
        if (!$id) {
            return $this->ajaxError('商品ID不能为空');
        }

        $product = ProductModel::find($id);
        if (!$product) {
            return $this->ajaxError('商品不存在');
        }

        $discountInfo = $product->getDiscountInfo();
        
        return $this->ajaxSuccess('获取成功', $discountInfo);
    }

    /**
     * 搜索商品（用于批量折扣设置）
     */
    public function searchProducts()
    {
        $keyword = $this->request->param('keyword', '');
        $page = $this->request->param('page', 1);
        $limit = $this->request->param('limit', 20);

        $query = ProductModel::where('status', 1);
        
        if (!empty($keyword)) {
            $query->where('name', 'like', '%' . $keyword . '%');
        }

        $products = $query->field('id,name,price,image')
            ->order('sort', 'asc')
            ->page($page, $limit)
            ->select();

        $total = $query->count();

        return $this->ajaxSuccess('获取成功', [
            'products' => $products,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ]);
    }
} 