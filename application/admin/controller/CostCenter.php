<?php
namespace app\admin\controller;

use app\admin\model\CostLog;
use think\Request;

class CostCenter extends Base
{
    /**
     * 成本流水列表及统计
     */
    public function index(Request $request)
    {
        $params = $request->param();
        $where = [];
        // 成本类型筛选（单选）
        if (isset($params['type']) && $params['type'] !== '' && $params['type'] !== null) {
            $where['type'] = intval($params['type']);
        }
        // 金额类型筛选（单选）
        if (isset($params['amount_type']) && $params['amount_type'] !== '' && $params['amount_type'] !== null) {
            $where['amount_type'] = intval($params['amount_type']);
        }
        if (!empty($params['operator'])) {
            $where['operator'] = $params['operator'];
        }
        if (!empty($params['relate_id'])) {
            $where['relate_id'] = ['like', '%' . $params['relate_id'] . '%'];
        }
        if (!empty($params['start_time']) && !empty($params['end_time'])) {
            $where['create_time'] = ['between', [$params['start_time'] . ' 00:00:00', $params['end_time'] . ' 23:59:59']];
        }
        
        // 新增：商品分类筛选
        if (!empty($params['category_id'])) {
            $where['category_id'] = intval($params['category_id']);
        }
        
        // 新增：商品名称筛选（模糊查询）
        if (!empty($params['product_name'])) {
            $where['product_name'] = ['like', '%' . $params['product_name'] . '%'];
        }
        
        // 分页参数，兼容page和limit
        $page = isset($params['page']) ? intval($params['page']) : 1;
        $limit = isset($params['limit']) ? intval($params['limit']) : 20;
        // 排序处理
        if (!empty($params['amountSort'])) {
            // 按金额排序，orderAmount=asc/desc
            $order = ['amount' => strtolower($params['amountSort']) == 'asc' ? 'asc' : 'desc'];
        } else {
            // 默认按id倒序
            $order = ['id' => 'desc'];
        }
        // 获取总数
        $total = CostLog::where($where)->count();
        // 获取分页数据
        $list = CostLog::where($where)->order($order)->page($page, $limit)->select();
        
        // 为列表数据添加商品分类名称
        foreach ($list as &$item) {
            $item['category_name'] = '';
            
            // 获取商品分类名称
            if (!empty($item['category_id'])) {
                $category = \app\admin\model\ProductCategory::where('id', $item['category_id'])->find();
                if ($category) {
                    $item['category_name'] = $category['name'];
                }
            }
        }
        
        // 统计项
        // 总成本支出
        if (isset($where['amount_type'])) {
            // 如果已筛选金额类型，直接sum
            $total_cost = CostLog::where($where)->sum('amount');
        } else {
            // 否则分别统计增加和减少再做差
            $total_cost_add = CostLog::where($where)->where('amount_type', CostLog::AMOUNT_ADD)->sum('amount');
            $total_cost_sub = CostLog::where($where)->where('amount_type', CostLog::AMOUNT_SUB)->sum('amount');
            $total_cost = $total_cost_add - $total_cost_sub;
        }
        // 批次成本总额
        $batch_where = $where;
        unset($batch_where['type']);
        if (isset($batch_where['amount_type'])) {
            $batch_cost = CostLog::where($batch_where)
                ->where('type', 'in', [CostLog::TYPE_BATCH, CostLog::TYPE_BATCH_MODIFY])
                ->sum('amount');
        } else {
            $batch_add = CostLog::where($batch_where)
                ->where('type', 'in', [CostLog::TYPE_BATCH, CostLog::TYPE_BATCH_MODIFY])
                ->where('amount_type', CostLog::AMOUNT_ADD)
                ->sum('amount');
            $batch_sub = CostLog::where($batch_where)
                ->where('type', 'in', [CostLog::TYPE_BATCH, CostLog::TYPE_BATCH_MODIFY])
                ->where('amount_type', CostLog::AMOUNT_SUB)
                ->sum('amount');
            $batch_cost = $batch_add - $batch_sub;
        }
        // 手动发货成本
        $manual_where = $where;
        unset($manual_where['type']);
        if (isset($manual_where['amount_type'])) {
            $manual_delivery_cost = CostLog::where($manual_where)
                ->where('type', 'in', [CostLog::TYPE_MANUAL_DELIVERY, CostLog::TYPE_MANUAL_DELIVERY_MODIFY])
                ->sum('amount');
        } else {
            $manual_add = CostLog::where($manual_where)
                ->where('type', 'in', [CostLog::TYPE_MANUAL_DELIVERY, CostLog::TYPE_MANUAL_DELIVERY_MODIFY])
                ->where('amount_type', CostLog::AMOUNT_ADD)
                ->sum('amount');
            $manual_sub = CostLog::where($manual_where)
                ->where('type', 'in', [CostLog::TYPE_MANUAL_DELIVERY, CostLog::TYPE_MANUAL_DELIVERY_MODIFY])
                ->where('amount_type', CostLog::AMOUNT_SUB)
                ->sum('amount');
            $manual_delivery_cost = $manual_add - $manual_sub;
        }
        // 人工录入成本
        $manual_input_where = $where;
        unset($manual_input_where['type']);
        if (isset($manual_input_where['amount_type'])) {
            $manual_input_cost = CostLog::where($manual_input_where)
                ->where('type', CostLog::TYPE_MANUAL_INPUT)
                ->sum('amount');
        } else {
            $input_add = CostLog::where($manual_input_where)
                ->where('type', CostLog::TYPE_MANUAL_INPUT)
                ->where('amount_type', CostLog::AMOUNT_ADD)
                ->sum('amount');
            $input_sub = CostLog::where($manual_input_where)
                ->where('type', CostLog::TYPE_MANUAL_INPUT)
                ->where('amount_type', CostLog::AMOUNT_SUB)
                ->sum('amount');
            $manual_input_cost = $input_add - $input_sub;
        }
        return $this->ajaxSuccess('ok', [
            'total' => $total,
            'list' => $list,
            'page' => $page,
            'limit' => $limit,
            'stat' => [
                'total_cost' => $total_cost,
                'batch_cost' => $batch_cost,
                'manual_delivery_cost' => $manual_delivery_cost,
                'manual_input_cost' => $manual_input_cost,
            ]
        ]);
    }

    /**
     * 人工录入成本
     */
    public function addManualCost(Request $request)
    {
        // 接收 amount、remark、amount_type
        $data = $request->only(['amount', 'remark', 'amount_type']);
        if (empty($data['amount']) || $data['amount'] <= 0) {
            return $this->ajaxError('金额必须大于0');
        }
        if (empty($data['remark'])) {
            return $this->ajaxError('备注不能为空');
        }
        if (empty($data['amount_type'])) {
            return $this->ajaxError('金额类型不能为空');
        }
        if (!in_array($data['amount_type'], [1, 2])) {
            return $this->ajaxError('金额类型不合法');
        }
        $user = $this->adminInfo;
        $log = new CostLog([
            'type' => CostLog::TYPE_MANUAL_INPUT,
            'relate_id' => '',
            'amount' => $data['amount'],
            'amount_type' => $data['amount_type'], // 由前端传入
            'operator' => $user['username'],
            'remark' => $data['remark'],
        ]);
        $log->save();
        $this->add_log('成本中心', '人工录入成本', '成功');
        return $this->ajaxSuccess('录入成功');
    }

    /**
     * 导出成本流水（支持全部/选中）
     */
    public function export(Request $request)
    {
        $data = $request->param();
        $where = [];
        // 支持选中导出
        if (isset($data['type']) && $data['type'] === 'selected' && !empty($data['ids'])) {
            $where['id'] = ['in', $data['ids']];
        } else {
            if (!empty($data['type'])) {
                $where['type'] = intval($data['type']);
            }
            if (!empty($data['amount_type'])) {
                $where['amount_type'] = intval($data['amount_type']);
            }
            if (!empty($data['operator'])) {
                $where['operator'] = $data['operator'];
            }
            if (!empty($data['relate_id'])) {
                $where['relate_id'] = ['like', '%' . $data['relate_id'] . '%'];
            }
            if (!empty($data['start_time']) && !empty($data['end_time'])) {
                $where['create_time'] = ['between', [$data['start_time'], $data['end_time']]];
            }
            
            // 新增：商品分类筛选
            if (!empty($data['category_id'])) {
                $where['category_id'] = intval($data['category_id']);
            }
            
            // 新增：商品名称筛选（模糊查询）
            if (!empty($data['product_name'])) {
                $where['product_name'] = ['like', '%' . $data['product_name'] . '%'];
            }
        }
        
        // 排序
        $order = ['id' => 'desc'];
        
        // 获取总数，限制导出数量
        $total = CostLog::where($where)->count();
        if ($total > 10000) {
            return $this->ajaxError('数据量过大，请缩小查询范围或使用分页导出');
        }
        
        // 获取数据（限制最大导出10000条）
        $list = CostLog::where($where)->order($order)->limit(10000)->select();
        
        // 为导出数据添加商品分类名称
        foreach ($list as &$item) {
            $item['category_name'] = '';
            
            // 获取商品分类名称
            if (!empty($item['category_id'])) {
                $category = \app\admin\model\ProductCategory::where('id', $item['category_id'])->find();
                if ($category) {
                    $item['category_name'] = $category['name'];
                }
            }
        }
        
        // 引入PHPExcel
        if (!class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
            require_once ROOT_PATH . 'vendor/autoload.php';
        }
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // 设置表头
        $header = ['ID', '成本类型', '关联ID', '商品分类', '商品名称', '数量', '成本金额', '金额类型', '操作人', '详情', '备注', '批次卡密数量', '创建时间'];
        $col = 'A';
        foreach ($header as $h) {
            $sheet->setCellValue($col . '1', $h);
            $col++;
        }
        // 写入数据
        $row = 2;
        foreach ($list as $item) {
            $sheet->setCellValue('A' . $row, $item['id']);
            $sheet->setCellValue('B' . $row, $this->getTypeText($item['type']));
            $sheet->setCellValue('C' . $row, $item['relate_id']);
            $sheet->setCellValue('D' . $row, $item['category_name']);
            $sheet->setCellValue('E' . $row, $item['product_name']);
            $sheet->setCellValue('F' . $row, $item['quantity']);
            $sheet->setCellValue('G' . $row, $item['amount']);
            $sheet->setCellValue('H' . $row, $item['amount_type'] == 1 ? '增加' : '减少');
            $sheet->setCellValue('I' . $row, $item['operator']);
            $sheet->setCellValue('J' . $row, str_replace('→', '—', $item['detail']));
            $sheet->setCellValue('K' . $row, $item['remark']);
            $sheet->setCellValue('L' . $row, $item['batch_card_count']);
            $sheet->setCellValue('M' . $row, $item['create_time']);
            $row++;
        }
        // 生成文件名
        $filename = '成本流水_' . date('YmdHis') . '.xlsx';
        $exportDir = ROOT_PATH . 'uploads' . DS . 'export';
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0777, true);
        }
        $filepath = $exportDir . DS . $filename;
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($filepath);
        if (!file_exists($filepath)) {
            return $this->ajaxError('文件保存失败');
        }
        return $this->ajaxSuccess('导出成功', [
            'url' => '/uploads/export/' . $filename
        ]);
    }

    /**
     * 获取成本类型和金额类型选项，供前端下拉筛选
     */
    public function getCostTypeOptions()
    {
        // 成本类型映射
        $typeOptions = [
            ['value' => 1, 'label' => '批次成本'],
            ['value' => 2, 'label' => '人工发货'],
            ['value' => 3, 'label' => '人工录入成本'],
            ['value' => 4, 'label' => '批次成本修改'],
            ['value' => 5, 'label' => '手动发货成本修改'],
            ['value' => 6, 'label' => '编辑卡密成本修改'],
        ];
        // 金额类型映射
        $amountTypeOptions = [
            ['value' => 1, 'label' => '增加'],
            ['value' => 2, 'label' => '减少'],
        ];
        
        // 新增：获取商品分类选项
        $categoryOptions = [];
        $categories = \app\admin\model\ProductCategory::order('sort_order asc, id asc')->select();
        foreach ($categories as $category) {
            $categoryOptions[] = [
                'value' => $category['id'],
                'label' => $category['name']
            ];
        }
        
        // 返回数据
        return $this->ajaxSuccess('ok', [
            'type' => $typeOptions,
            'amount_type' => $amountTypeOptions,
            'category' => $categoryOptions
        ]);
    }

    private function getTypeText(
        $type
    )
    {
        $map = array(
            1 => '批次成本',
            2 => '人工发货',
            3 => '人工录入成本',
            4 => '批次成本修改',
            5 => '手动发货成本修改',
            6 => '编辑卡密成本修改',
        );
        return isset($map[$type]) ? $map[$type] : '';
    }
} 