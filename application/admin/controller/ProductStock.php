<?php
namespace app\admin\controller;

use app\admin\model\ProductStock as ProductStockModel;
use app\admin\model\Product;
use app\admin\model\CostLog;
use app\common\constants\StockStatus;
use think\Db;

class ProductStock extends Base
{
    /**
     * 获取库存列表
     */
    public function index()
    {
        $where = [];
        
        // ID筛选
        $stock_id = input('stock_id/d', '');
        if ($stock_id !== '') {
            $where['id'] = $stock_id;
        }
        
        // 商品ID筛选
        $product_id = input('product_id/d', '');
        if ($product_id !== '') {
            $where['product_id'] = $product_id;
        }
        
        // 批次ID筛选
        $batch_id = input('batch_id/s', '');
        if ($batch_id !== '') {
            $where['batch_id'] = $batch_id;
        }
        
        // 状态筛选
        $status = input('status/d', '');
        if ($status !== '') {
            $where['status'] = $status;
        }
        
        // 获取总数
        $total = ProductStockModel::where($where)->count();
        
        // 分页查询
        $page = input('page/d', 1);
        $limit = input('limit/d', 10);
        
        // 获取列表数据
        $list = ProductStockModel::where($where)
            ->with('product')
            ->order('id desc')
            ->page($page, $limit)
            ->select();
            
        return $this->ajaxSuccess('获取成功', [
            'total' => $total,
            'list' => $list
        ]);
    }
    
    /**
     * 生成批次ID接口
     */
    public function generateBatchId()
    {
        try {
            $batchId = ProductStockModel::generateBatchId();
            return $this->ajaxSuccess('生成成功', ['batch_id' => $batchId]);
        } catch (\Exception $e) {
            return $this->ajaxError('生成失败：' . $e->getMessage());
        }
    }
    
    /**
     * 新增卡密
     */
    public function add()
    {
        $data = input('post.');
        
        // 验证数据
        if (empty($data['product_id'])) {
            return $this->ajaxError('请选择商品');
        }
        
        if (empty($data['card_info'])) {
            return $this->ajaxError('请输入卡密信息');
        }
        
        if (empty($data['batch_id'])) {
            return $this->ajaxError('请提供批次ID');
        }
        
        // 检查商品是否存在
        $product = Product::find($data['product_id']);
        if (!$product) {
            return $this->ajaxError('商品不存在');
        }
        
        // 检查批次ID是否已存在
        if (ProductStockModel::checkBatchIdExists($data['batch_id'])) {
            return $this->ajaxError('批次ID已存在，请重新生成');
        }
        
        // 处理卡密信息（按行分割）
        $cardInfos = explode("\n", $data['card_info']);
        $cardInfos = array_filter(array_map('trim', $cardInfos));
        
        if (empty($cardInfos)) {
            return $this->ajaxError('请输入有效的卡密信息');
        }
        
        // 检查是否有重复的卡密
        $exists = ProductStockModel::where([
            'product_id' => $data['product_id'],
            'card_info' => ['in', $cardInfos]
        ])->column('card_info');
        
        if (!empty($exists)) {
            return $this->ajaxError('以下卡密已存在：' . implode("\n", $exists));
        }
        
        // 开启事务
        Db::startTrans();
        try {
            // 再次检查批次ID是否已存在（防止并发）
            if (ProductStockModel::checkBatchIdExists($data['batch_id'])) {
                throw new \Exception('批次ID已存在，请重新生成');
            }
            
            // 批量插入数据
            $insertData = [];
            foreach ($cardInfos as $cardInfo) {
                $insertData[] = [
                    'product_id' => $data['product_id'],
                    'batch_id' => $data['batch_id'],
                    'card_info' => $cardInfo,
                    'cost_price' => isset($data['cost_price']) ? floatval($data['cost_price']) : 0.00,
                    'status' => isset($data['status']) ? $data['status'] : StockStatus::UNUSED,
                    'remark' => isset($data['remark']) ? $data['remark'] : '',
                    'created_at' => date('Y-m-d H:i:s')
                ];
            }
            
            if (!ProductStockModel::insertAll($insertData)) {
                throw new \Exception('添加失败');
            }
            
            // 获取商品分类信息
            $categoryId = null;
            $productName = '';
            if (!empty($data['product_id'])) {
                $product = \app\admin\model\Product::where('id', $data['product_id'])->find();
                if ($product) {
                    $categoryId = $product['category_id'];
                    $productName = $product['name'];
                }
            }
            
            // 写入批次成本流水
            CostLog::create([
                'type' => CostLog::TYPE_BATCH,
                'relate_id' => $data['batch_id'],
                'amount' => (isset($data['cost_price']) ? floatval($data['cost_price']) : 0.00) * count($cardInfos),
                'amount_type' => CostLog::AMOUNT_ADD,
                'operator' => $this->adminInfo['username'],
                'remark' => '新增卡密',
                'batch_card_count' => count($cardInfos),
                'category_id' => $categoryId,
                'product_name' => $productName,
                'quantity' => count($cardInfos),
            ]);
            
            Db::commit();
            $this->add_log('卡密管理', '新增卡密：' . count($cardInfos) . '个，批次：' . $data['batch_id'], '成功');
            return $this->ajaxSuccess('添加成功');
        } catch (\Exception $e) {
            Db::rollback();
            $this->add_log('卡密管理', '新增卡密：' . count($cardInfos) . '个', '失败');
            return $this->ajaxError($e->getMessage());
        }
    }
    
    /**
     * 编辑卡密
     */
    public function edit()
    {
        $data = input('post.');
        
        // 验证数据
        if (empty($data['id'])) {
            return $this->ajaxError('参数错误');
        }
        
        if (empty($data['card_info'])) {
            return $this->ajaxError('请输入卡密信息');
        }
        
        // 检查卡密是否存在
        $stock = ProductStockModel::find($data['id']);
        if (!$stock) {
            return $this->ajaxError('卡密不存在');
        }
        $oldCost = $stock['cost_price'];
        $newCost = isset($data['cost_price']) ? floatval($data['cost_price']) : $oldCost;
        \think\Db::startTrans();
        try {
            // 检查卡密是否重复
            $exists = ProductStockModel::where([
                'product_id' => $stock['product_id'],
                'card_info' => $data['card_info'],
                'id' => ['neq', $data['id']]
            ])->find();
            if ($exists) {
                \think\Db::rollback();
                return $this->ajaxError('卡密信息已存在');
            }
            // 准备更新数据
            $updateData = [
                'card_info' => $data['card_info'],
                'cost_price' => $newCost,
                'status' => isset($data['status']) ? $data['status'] : $stock['status'],
                'remark' => isset($data['remark']) ? $data['remark'] : $stock['remark']
            ];
            // 用where更新
            $result = ProductStockModel::where('id', $data['id'])->update($updateData);
            if ($result === false) {
                \think\Db::rollback();
                $this->add_log('卡密管理', '更新卡密：' . $stock['card_info'], '失败');
                return $this->ajaxError('更新失败');
            }
            // 成本价变动写入成本中心
            if ($newCost != $oldCost) {
                // 获取商品分类信息
                $categoryId = null;
                $productName = '';
                $product = \app\admin\model\Product::where('id', $stock['product_id'])->find();
                if ($product) {
                    $categoryId = $product['category_id'];
                    $productName = $product['name'];
                }
                
                $diff = $newCost - $oldCost;
                $costLog = \app\admin\model\CostLog::create([
                    'type' => \app\admin\model\CostLog::TYPE_EDIT_CARD_COST,
                    'relate_id' => $stock['id'],
                    'amount' => abs($diff),
                    'amount_type' => $diff > 0 ? \app\admin\model\CostLog::AMOUNT_ADD : \app\admin\model\CostLog::AMOUNT_SUB,
                    'operator' => $this->adminInfo['username'],
                    'detail' => '成本价：¥' . number_format($oldCost, 2) . ' → ¥' . number_format($newCost, 2),
                    'remark' => '单个卡密成本价变动',
                    'batch_card_count' => 1,
                    'category_id' => $categoryId,
                    'product_name' => $productName,
                    'quantity' => 1,
                ], true);
                if (!$costLog) {
                    \think\Db::rollback();
                    return $this->ajaxError('成本中心写入失败');
                }
            }
            \think\Db::commit();
        } catch (\Exception $e) {
            \think\Db::rollback();
            $this->add_log('卡密管理', '更新卡密异常：' . $e->getMessage(), '失败');
            return $this->ajaxError('更新异常：' . $e->getMessage());
        }
        $this->add_log('卡密管理', '更新卡密：' . $stock['card_info'], '成功');
        return $this->ajaxSuccess('更新成功');
    }
    
    /**
     * 删除卡密
     */
    public function delete()
    {
        $id = input('id/d');
        if (!$id) {
            return $this->ajaxError('参数错误');
        }
        
        $stock = ProductStockModel::find($id);
        if (!$stock) {
            return $this->ajaxError('卡密不存在');
        }
        
        $cost = $stock['cost_price'];
        $batch_id = $stock['batch_id'];
        
        if (!$stock->delete()) {
            $this->add_log('卡密管理', '删除卡密：' . $stock['card_info'], '失败');
            return $this->ajaxError('删除失败');
        }
        
        // 获取商品分类信息
        $categoryId = null;
        $productName = '';
        $product = \app\admin\model\Product::where('id', $stock['product_id'])->find();
        if ($product) {
            $categoryId = $product['category_id'];
            $productName = $product['name'];
        }
        
        // 写入批次成本减少流水
        CostLog::create([
            'type' => CostLog::TYPE_BATCH_MODIFY,
            'relate_id' => $batch_id,
            'amount' => $cost,
            'amount_type' => CostLog::AMOUNT_SUB,
            'operator' => $this->adminInfo['username'],
            'remark' => '删除卡密',
            'batch_card_count' => 1,
            'category_id' => $categoryId,
            'product_name' => $productName,
            'quantity' => 1,
        ]);
        
        $this->add_log('卡密管理', '删除卡密：' . $stock['card_info'], '成功');
        return $this->ajaxSuccess('删除成功');
    }
    
    /**
     * 批量删除
     */
    public function batchDelete()
    {
        $ids = input('ids/a');
        if (empty($ids)) {
            return $this->ajaxError('请选择要删除的卡密');
        }
        
        // 使用数据库聚合查询获取总成本和批次ID
        $stockInfo = ProductStockModel::where('id', 'in', $ids)
            ->field('SUM(cost_price) as total_cost, batch_id, product_id')
            ->group('batch_id')
            ->find();
        
        if (!$stockInfo) {
            return $this->ajaxError('卡密不存在');
        }
        
        // 获取商品分类信息
        $categoryId = null;
        $productName = '';
        $product = \app\admin\model\Product::where('id', $stockInfo['product_id'])->find();
        if ($product) {
            $categoryId = $product['category_id'];
            $productName = $product['name'];
        }
        
        if (!ProductStockModel::where('id', 'in', $ids)->delete()) {
            $this->add_log('卡密管理', '批量删除卡密：' . count($ids) . '个', '失败');
            return $this->ajaxError('删除失败');
        }
        
        // 写入批次成本减少流水
        CostLog::create([
            'type' => CostLog::TYPE_BATCH_MODIFY,
            'relate_id' => $stockInfo['batch_id'],
            'amount' => $stockInfo['total_cost'],
            'amount_type' => CostLog::AMOUNT_SUB,
            'operator' => $this->adminInfo['username'],
            'remark' => '批量删除卡密',
            'batch_card_count' => count($ids),
            'category_id' => $categoryId,
            'product_name' => $productName,
            'quantity' => count($ids),
        ]);
        
        $this->add_log('卡密管理', '批量删除卡密：' . count($ids) . '个', '成功');
        return $this->ajaxSuccess('删除成功');
    }
    
    /**
     * 批量导入
     */
    public function import()
    {
        if (!$this->request->isPost()) {
            return $this->ajaxError('请求方式错误');
        }

        $file = $this->request->file('file');
        $productId = $this->request->post('product_id/d', 0);
        $batchId = $this->request->post('batch_id/s', '');
        $costPrice = $this->request->post('cost_price/f', 0.00);
        $remark = $this->request->post('remark/s', '');

        if (!$file) {
            return $this->ajaxError('请上传文件');
        }

        if (!$productId) {
            return $this->ajaxError('请选择商品');
        }
        
        if (empty($batchId)) {
            return $this->ajaxError('请提供批次ID');
        }

        // 检查商品是否存在
        $product = model('Product')->find($productId);
        if (!$product) {
            return $this->ajaxError('商品不存在');
        }
        
        // 检查批次ID是否已存在
        if (ProductStockModel::checkBatchIdExists($batchId)) {
            return $this->ajaxError('批次ID已存在，请重新生成');
        }

        // 获取文件后缀
        $ext = strtolower(pathinfo($file->getInfo('name'), PATHINFO_EXTENSION));
        if (!in_array($ext, ['txt', 'csv', 'xlsx', 'xls'])) {
            return $this->ajaxError('仅支持txt、csv、xlsx、xls格式文件');
        }

        try {
            if ($ext == 'txt' || $ext == 'csv') {
                // 读取文本文件
                $content = file_get_contents($file->getPathname());
                $content = str_replace("\r\n", "\n", $content);
                $content = str_replace("\r", "\n", $content);
                $cards = explode("\n", trim($content));
            } else {
                // 读取Excel文件
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader(ucfirst($ext));
                $spreadsheet = $reader->load($file->getPathname());
                $sheet = $spreadsheet->getActiveSheet();
                $cards = [];
                
                // 获取最后一行的行号
                $highestRow = $sheet->getHighestRow();
                
                // 读取A列的所有数据
                for ($row = 1; $row <= $highestRow; $row++) {
                    $value = $sheet->getCellByColumnAndRow(1, $row)->getValue();
                    if ($value) {
                        $cards[] = $value;
                    }
                }
            }

            // 过滤空行
            $cards = array_filter($cards);
            if (empty($cards)) {
                return $this->ajaxError('文件内容为空');
            }

            // 开启事务
            model('ProductStock')->startTrans();

            // 再次检查批次ID是否已存在（防止并发）
            if (ProductStockModel::checkBatchIdExists($batchId)) {
                throw new \Exception('批次ID已存在，请重新生成');
            }

            // 批量插入数据
            $data = [];
            foreach ($cards as $card) {
                $data[] = [
                    'product_id' => $productId,
                    'batch_id' => $batchId,
                    'card_info' => trim($card),
                    'cost_price' => $costPrice,
                    'status' => StockStatus::UNUSED,
                    'remark' => $remark,
                    'created_at' => date('Y-m-d H:i:s')
                ];
            }

            model('ProductStock')->insertAll($data);

            // 获取商品分类信息
            $categoryId = null;
            $productName = '';
            if ($product) {
                $categoryId = $product['category_id'];
                $productName = $product['name'];
            }

            // 写入批次成本流水
            CostLog::create([
                'type' => CostLog::TYPE_BATCH,
                'relate_id' => $batchId,
                'amount' => $costPrice * count($cards),
                'amount_type' => CostLog::AMOUNT_ADD,
                'operator' => $this->adminInfo['username'],
                'remark' => '批量导入卡密',
                'batch_card_count' => count($cards),
                'category_id' => $categoryId,
                'product_name' => $productName,
                'quantity' => count($cards),
            ]);

            // 提交事务
            model('ProductStock')->commit();

            // 记录日志
            $this->add_log('商品库存', '批量导入卡密：' . count($cards) . '条，批次：' . $batchId, '成功');

            return $this->ajaxSuccess('导入成功');
        } catch (\Exception $e) {
            // 回滚事务
            model('ProductStock')->rollback();
            
            // 记录日志
            $this->add_log('商品库存', '批量导入卡密失败：' . $e->getMessage(), '失败');
            
            return $this->ajaxError('导入失败：' . $e->getMessage());
        }
    }
    
    /**
     * 模糊查询批次ID
     */
    public function searchBatchIds()
    {
        $keyword = input('keyword/s', '');
        $product_id = input('product_id/d', 0);
        if (empty($keyword) || empty($product_id)) {
            return $this->ajaxError('请输入搜索关键词和商品ID');
        }
        // 只查对应商品下的批次
        $batchIds = ProductStockModel::where('batch_id', 'like', '%' . $keyword . '%')
            ->where('product_id', $product_id)
            ->field('batch_id, COUNT(*) as count')
            ->group('batch_id')
            ->select();
        $result = [];
        foreach ($batchIds as $item) {
            $unused_count = ProductStockModel::where('batch_id', $item['batch_id'])
                ->where('product_id', $product_id)
                ->where('status', \app\common\constants\StockStatus::UNUSED)
                ->count();
            $result[] = [
                'batch_id' => $item['batch_id'],
                'count' => $item['count'],
                'unused_count' => $unused_count
            ];
        }
        return $this->ajaxSuccess('查询成功', ['list' => $result]);
    }
    
    /**
     * 批量导出
     */
    public function export()
    {
        $ids = input('ids/a');
        if (empty($ids)) {
            $this->add_log('卡密管理', '批量导出卡密：未选择卡密', '失败');
            return $this->ajaxError('请选择要导出的卡密');
        }
        
        // 使用数据库聚合查询直接获取卡密信息
        $cardInfos = ProductStockModel::where('id', 'in', $ids)
            ->field('card_info')
            ->column('card_info');
            
        if (empty($cardInfos)) {
            $this->add_log('卡密管理', '批量导出卡密：没有数据可导出', '失败');
            return $this->ajaxError('没有数据可导出');
        }
        
        // 生成文件名
        $filename = 'card_info_' . date('YmdHis');
        
        // 根据请求头判断文件类型
        $accept = request()->header('accept');
        if (strpos($accept, 'application/vnd.ms-excel') !== false || strpos($accept, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') !== false) {
            // Excel文件
            $filename .= '.xlsx';
            $filepath = ROOT_PATH . 'uploads/export/' . $filename;
            
            // 创建目录
            if (!is_dir(dirname($filepath))) {
                if (!mkdir(dirname($filepath), 0777, true)) {
                    $this->add_log('卡密管理', '批量导出卡密：创建目录失败', '失败');
                    return $this->ajaxError('创建目录失败');
                }
            }
            
            try {
                // 创建Excel文件
                $objPHPExcel = new \PHPExcel();
                $objPHPExcel->setActiveSheetIndex(0);
                $sheet = $objPHPExcel->getActiveSheet();
                
                // 写入数据
                foreach ($cardInfos as $index => $cardInfo) {
                    $sheet->setCellValue('A' . ($index + 1), $cardInfo);
                }
                
                // 保存文件
                $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
                $objWriter->save($filepath);
                
                $this->add_log('卡密管理', '批量导出卡密：' . count($cardInfos) . '个', '成功');
                return $this->ajaxSuccess('导出成功', [
                    'url' => '/uploads/export/' . $filename,
                    'filename' => $filename
                ]);
            } catch (\Exception $e) {
                $this->add_log('卡密管理', '批量导出卡密：Excel文件生成失败', '失败');
                return $this->ajaxError('Excel文件生成失败');
            }
        } else {
            // 文本文件
            $filename .= '.txt';
            $filepath = ROOT_PATH . 'uploads/export/' . $filename;
            
            // 创建目录
            if (!is_dir(dirname($filepath))) {
                if (!mkdir(dirname($filepath), 0777, true)) {
                    $this->add_log('卡密管理', '批量导出卡密：创建目录失败', '失败');
                    return $this->ajaxError('创建目录失败');
                }
            }
            
            try {
                // 写入文本文件
                $content = implode("\n", $cardInfos);
                if (file_put_contents($filepath, $content) === false) {
                    $this->add_log('卡密管理', '批量导出卡密：文件写入失败', '失败');
                    return $this->ajaxError('文件写入失败');
                }
                
                $this->add_log('卡密管理', '批量导出卡密：' . count($cardInfos) . '个', '成功');
                return $this->ajaxSuccess('导出成功', [
                    'url' => '/uploads/export/' . $filename,
                    'filename' => $filename
                ]);
            } catch (\Exception $e) {
                $this->add_log('卡密管理', '批量导出卡密：文本文件生成失败', '失败');
                return $this->ajaxError('文本文件生成失败');
            }
        }
    }
    
    /**
     * 批量修改卡密成本价
     */
    public function batchUpdateCostPrice()
    {
        $batchId = input('batch_id/s', '');
        $costPrice = input('cost_price/f', 0.00);
        $remark = input('remark/s', '');
        
        if (empty($batchId)) {
            return $this->ajaxError('请提供批次ID');
        }
        
        if ($costPrice < 0) {
            return $this->ajaxError('成本价不能为负数');
        }
        
        // 检查批次是否存在
        $count = ProductStockModel::where('batch_id', $batchId)->count();
        if ($count == 0) {
            return $this->ajaxError('批次不存在');
        }
        
        // 记录批次成本修改流水
        $oldTotal = ProductStockModel::where('batch_id', $batchId)->sum('cost_price');
        $batchCardCount = ProductStockModel::where('batch_id', $batchId)->count();
        
        // 获取商品分类信息
        $stockInfo = ProductStockModel::where('batch_id', $batchId)->field('product_id')->find();
        $categoryId = null;
        $productName = '';
        if ($stockInfo) {
            $product = \app\admin\model\Product::where('id', $stockInfo['product_id'])->find();
            if ($product) {
                $categoryId = $product['category_id'];
                $productName = $product['name'];
            }
        }
        
        $result = ProductStockModel::where('batch_id', $batchId)->update([
            'cost_price' => $costPrice,
            'remark' => $remark
        ]);
        $newTotal = $costPrice * $batchCardCount;
        $diff = $newTotal - $oldTotal;
        if ($diff != 0) {
            CostLog::create([
                'type' => CostLog::TYPE_BATCH_MODIFY,
                'relate_id' => $batchId,
                'amount' => abs($diff),
                'amount_type' => $diff > 0 ? CostLog::AMOUNT_ADD : CostLog::AMOUNT_SUB,
                'operator' => $this->adminInfo['username'],
                'detail' => "原成本：{$oldTotal} — 新成本：{$newTotal}",
                'remark' => '批量修改卡密成本价',
                'batch_card_count' => $batchCardCount,
                'category_id' => $categoryId,
                'product_name' => $productName,
                'quantity' => $batchCardCount,
            ]);
        }
        
        if ($result === false) {
            $this->add_log('卡密管理', '批量更新成本价：批次' . $batchId, '失败');
            return $this->ajaxError('更新失败');
        }
        
        $this->add_log('卡密管理', '批量更新成本价：批次' . $batchId . '，成本价：' . $costPrice, '成功');
        return $this->ajaxSuccess('更新成功', ['updated_count' => $result]);
    }
    
    /**
     * 获取库存统计
     */
    public function getStockStats()
    {
        $productId = input('product_id/d', '');
        
        $where = [];
        if ($productId) {
            $where['product_id'] = $productId;
        }
        
        $stats = ProductStockModel::where($where)->field([
            'COUNT(*) as total',
            'SUM(CASE WHEN status = ' . StockStatus::UNUSED . ' THEN 1 ELSE 0 END) as unused',
            'SUM(CASE WHEN status = ' . StockStatus::LOCKED . ' THEN 1 ELSE 0 END) as locked',
            'SUM(CASE WHEN status = ' . StockStatus::SOLD . ' THEN 1 ELSE 0 END) as sold',
            'SUM(CASE WHEN status = ' . StockStatus::VOID . ' THEN 1 ELSE 0 END) as void',
            'SUM(cost_price) as total_cost'
        ])->find();
        
        return $this->ajaxSuccess('获取成功', $stats ? $stats->toArray() : [
            'total' => 0,
            'unused' => 0,
            'locked' => 0,
            'sold' => 0,
            'void' => 0,
            'total_cost' => 0.00
        ]);
    }
} 