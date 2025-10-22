<?php
namespace app\admin\controller;

use app\admin\model\ProductOrder as OrderModel;
use app\admin\model\ProductCategory;
use app\common\constants\OrderStatus;
use think\Db;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use app\admin\model\CostLog;

class ProductOrder extends Base
{
    /**
     * 获取支付通道列表
     */
    public function getChannels()
    {
        if (!$this->request->isGet()) {
            return $this->ajaxError('请求方式错误');
        }

        try {
            // 获取所有支付通道
            $channels = \app\admin\model\PaymentChannel::field('id, name, abbr')
                ->order('id', 'asc')
                ->select();

            // 构建通道选项
            $channelOptions = [];
            foreach ($channels as $channel) {
                $channelOptions[] = [
                    'id' => $channel['id'],
                    'value' => $channel['abbr'],
                    'label' => $channel['name']
                ];
            }

            return $this->ajaxSuccess('获取成功', [
                'channels' => $channelOptions
            ]);
        } catch (\Exception $e) {
            Log::error('获取支付通道列表失败：' . $e->getMessage());
            return $this->ajaxError('获取失败');
        }
    }
    
    /**
     * 获取订单列表
     */
    public function index()
    {
        if (!$this->request->isGet()) {
            return $this->ajaxError('请求方式错误');
        }
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 10);
        $order_number = $this->request->get('order_number', '');
        $user_email = $this->request->get('user_email', '');
        $nickname = $this->request->get('nickname', '');
        $user_role = $this->request->get('user_role', '');
        $category_id = $this->request->get('category_id', '');
        $channel_id = $this->request->get('channel_id', '');
        $payment_method = $this->request->get('payment_method', '');
        $delivery_method = $this->request->get('delivery_method', '');
        $status = $this->request->get('status', '');
        $product_name = $this->request->get('product_name', '');
        $start_time = $this->request->get('start_time', '');
        $end_time = $this->request->get('end_time', '');
        $finished_start_time = $this->request->get('finished_start_time', '');
        $finished_end_time = $this->request->get('finished_end_time', '');

        // 构建基础查询条件
        $where = [];
        if ($order_number !== '') {
            $where['o.order_number'] = ['like', "%{$order_number}%"];
        }
        if ($user_email !== '') {
            $where['o.user_email'] = ['like', "%{$user_email}%"];
        }
        if ($product_name !== '') {
            $where['o.product_name'] = ['like', "%{$product_name}%"];
        }
        if ($category_id !== '') {
            $where['o.category_id'] = $category_id;
        }
        if ($channel_id !== '') {
            $where['o.channel_id'] = $channel_id;
        }
        if ($payment_method !== '') {
            $where['o.payment_method'] = $payment_method;
        }
        if ($delivery_method !== '') {
            $where['o.delivery_method'] = $delivery_method;
        }
        if ($status !== '') {
            $where['o.status'] = $status;
        }
        if ($start_time && $end_time) {
            $where['o.created_at'] = ['between', [$start_time . ' 00:00:00', $end_time . ' 23:59:59']];
        } elseif ($start_time) {
            $where['o.created_at'] = ['>=', $start_time . ' 00:00:00'];
        } elseif ($end_time) {
            $where['o.created_at'] = ['<=', $end_time . ' 23:59:59'];
        }
        if ($finished_start_time && $finished_end_time) {
            $where['o.finished_at'] = ['between', [$finished_start_time . ' 00:00:00', $finished_end_time . ' 23:59:59']];
        } elseif ($finished_start_time) {
            $where['o.finished_at'] = ['>=', $finished_start_time . ' 00:00:00'];
        } elseif ($finished_end_time) {
            $where['o.finished_at'] = ['<=', $finished_end_time . ' 23:59:59'];
        }

        // 处理用户相关筛选
        if ($nickname !== '') {
            // 昵称从用户表查询（实时昵称）
            $where['u.nickname'] = ['like', "%{$nickname}%"];
        }
        if ($user_role !== '') {
            if ($user_role === '-1') {
                // 查询游客用户（user_role_id = 0）
                $where['o.user_role_id'] = 0;
            } else {
                // 使用会员等级ID查询订单表
                $where['o.user_role_id'] = intval($user_role);
            }
        }
        
        // 查询总数
        $totalQuery = model('ProductOrder')->alias('o');
        if ($nickname !== '') {
            $totalQuery = $totalQuery->join('epay_users u', 'o.user_email = u.email', 'LEFT');
        }
        $totalQuery = $totalQuery->where($where);
        $total = $totalQuery->count('o.id');
        
        // 查询列表
        $listQuery = model('ProductOrder')->alias('o');
        if ($nickname !== '') {
            $listQuery = $listQuery->join('epay_users u', 'o.user_email = u.email', 'LEFT');
        }
        $list = $listQuery->field('o.*')
            ->where($where)
            ->with(['category'])
            ->order('o.id desc')
            ->page($page, $limit)
            ->select();

        // 统计项（基于当前筛选条件）
        $stat = [];
        $stat['total'] = $total;
        
        // 成功订单统计 - 根据筛选状态判断
        if ($status === '') {
            // 没有状态筛选时，统计所有订单中的成功订单
            $successWhere = $where;
            $successWhere['o.status'] = OrderStatus::COMPLETED;
            $successQuery = model('ProductOrder')->alias('o');
            if ($nickname !== '') {
                $successQuery = $successQuery->join('epay_users u', 'o.user_email = u.email', 'LEFT');
            }
            $stat['success'] = $successQuery->where($successWhere)->count('o.id');
            
            // 总金额统计
            $amountQuery = model('ProductOrder')->alias('o');
            if ($nickname !== '') {
                $amountQuery = $amountQuery->join('epay_users u', 'o.user_email = u.email', 'LEFT');
            }
            $stat['total_amount'] = $amountQuery->where($successWhere)->sum('o.total_price');
            
            // 手续费统计
            $feeQuery = model('ProductOrder')->alias('o');
            if ($nickname !== '') {
                $feeQuery = $feeQuery->join('epay_users u', 'o.user_email = u.email', 'LEFT');
            }
            $stat['total_fee'] = $feeQuery->where($successWhere)->sum('o.fee');
        } elseif ($status == OrderStatus::PAID || $status == OrderStatus::COMPLETED) {
            // 筛选已支付或已完成状态时，成功订单数等于筛选结果数
            $stat['success'] = $total;
            
            // 总金额统计
            $amountQuery = model('ProductOrder')->alias('o');
            if ($nickname !== '') {
                $amountQuery = $amountQuery->join('epay_users u', 'o.user_email = u.email', 'LEFT');
            }
            $stat['total_amount'] = $amountQuery->where($where)->sum('o.total_price');
            
            // 手续费统计
            $feeQuery = model('ProductOrder')->alias('o');
            if ($nickname !== '') {
                $feeQuery = $feeQuery->join('epay_users u', 'o.user_email = u.email', 'LEFT');
            }
            $stat['total_fee'] = $feeQuery->where($where)->sum('o.fee');
        } else {
            // 筛选其他状态时（待支付、已取消、发货失败、已退款），成功订单数为0
            $stat['success'] = 0;
            $stat['total_amount'] = 0;
            $stat['total_fee'] = 0;
        }
        
        $stat['total_arrive'] = $stat['total_amount'] - $stat['total_fee'];

        // 获取USDT汇率
        $paymentConfig = model('PaymentConfig')->find();
        $usdtRate = $paymentConfig ? $paymentConfig['usdt_rate'] : 0;

        foreach ($list as &$item) {
            $item['status_text'] = OrderStatus::getStatusText($item['status']);
            $item['usdt_price'] = $usdtRate > 0 ? round($item['purchase_price'] / $usdtRate, 2) : 0;
            $item['usdt_total_price'] = $usdtRate > 0 ? round($item['total_price'] / $usdtRate, 2) : 0;
            $item['arrive_amount'] = $item['total_price'] - (isset($item['fee']) ? $item['fee'] : 0);
            $item['cost_price'] = isset($item['cost_price']) ? $item['cost_price'] : 0;
            // 中文注释：如果完成时间为空、为'0000-00-00 00:00:00'、为'-0001-11-30 00:00:00'、为'1970-01-01 08:00:00'、为null，或无法转为有效时间戳，则显示'-'
            $invalidTimes = ['', '0000-00-00 00:00:00', '-0001-11-30 00:00:00', null, '1970-01-01 08:00:00'];
            $finishedAt = isset($item['finished_at']) ? (string)$item['finished_at'] : '';
            if (in_array($finishedAt, $invalidTimes, true) || strtotime($finishedAt) === false || strtotime($finishedAt) === 0) {
                $item['finished_at'] = '-';
            } else {
                $item['finished_at'] = $finishedAt;
            }
            
            // 通过用户邮箱获取用户表的实时昵称
            if (!empty($item['user_email'])) {
                $user = model('User')->where('email', $item['user_email'])->find();
                if ($user) {
                    $item['user_nickname'] = $user['nickname'] ?: '游客';
                } else {
                    $item['user_nickname'] = '游客';
                }
            } else {
                $item['user_nickname'] = '游客';
            }
            
            // 通过user_role_id获取最新的会员等级名称
            if ($item['user_role_id'] > 0) {
                $memberLevel = model('MemberLevel')->where('id', $item['user_role_id'])->find();
                if ($memberLevel) {
                    $item['user_role'] = $memberLevel['name'];  // 使用最新的会员等级名称
                } else {
                    $item['user_role'] = '未知等级';  // 如果会员等级被删除
                }
            } else {
                $item['user_role'] = '游客';
            }
            
            $item['discount'] = $item['user_discount'] ?: '100';
        }

        return $this->ajaxSuccess('获取成功', [
            'total' => $total,
            'list' => $list,
            'stat' => $stat
        ]);
    }
    
    /**
     * 导出订单
     */
    public function export()
    {
        if (!$this->request->isPost()) {
            return $this->ajaxError('请求方式错误');
        }

        $data = $this->request->post();
        if (empty($data)) {
            return $this->ajaxError('参数错误');
        }
        
        $order_number = isset($data['order_number'])? $data['order_number']: '';
        $user_email = isset($data['user_email'])? $data['user_email']: '';
        $product_name = isset($data['product_name'])? $data['product_name']: '';
        $category_id = isset($data['category_id'])? $data['category_id']: '';
        $channel_id = isset($data['channel_id'])? $data['channel_id']: '';
        $payment_method = isset($data['payment_method'])? $data['payment_method']: '';
        $delivery_method = isset($data['delivery_method'])? $data['delivery_method']: '';
        $status = isset($data['status'])? $data['status']: '';
        $start_time = isset($data['start_time'])? $data['start_time']: '';
        $end_time = isset($data['end_time'])? $data['end_time']: '';

        $where = [];
        
        // 订单号筛选
        if ($order_number !== '') {
            $where['order_number'] = ['like', "%{$order_number}%"];
        }
        
        // 用户邮箱筛选
        if ($user_email !== '') {
            $where['user_email'] = ['like', "%{$user_email}%"];
        }
        
        // 商品名称筛选
        if ($product_name !== '') {
            $where['product_name'] = ['like', "%{$product_name}%"];
        }
        
        // 商品分类筛选
        if ($category_id !== '') {
            $where['category_id'] = $category_id;
        }
        
        // 通道筛选
        if ($channel_id !== '') {
            $where['channel_id'] = $channel_id;
        }
        
        // 支付方式筛选
        if ($payment_method !== '') {
            $where['payment_method'] = $payment_method;
        }
        
        // 发货方式筛选
        if ($delivery_method !== '') {
            $where['delivery_method'] = $delivery_method;
        }
        
        // 订单状态筛选
        if ($status !== '') {
            $where['status'] = $status;
        }
        
        // 下单时间范围筛选
        if ($start_time && $end_time) {
            $where['created_at'] = ['between', [$start_time . ' 00:00:00', $end_time . ' 23:59:59']];
        } elseif ($start_time) {
            $where['created_at'] = ['>=', $start_time . ' 00:00:00'];
        } elseif ($end_time) {
            $where['created_at'] = ['<=', $end_time . ' 23:59:59'];
        }
        

        // 获取USDT汇率
        $paymentConfig = model('PaymentConfig')->find();
        $usdtRate = $paymentConfig ? $paymentConfig['usdt_rate'] : 0;

        // 构建查询条件
        if ($data['type'] === 'selected' && !empty($data['ids'])) {
            $where = [];
            $where['id'] = ['in', $data['ids']];
        }

        // 获取订单数据
        $list = model('ProductOrder')
            ->where($where)
            ->order('id', 'desc')
            ->select();

        // 创建Excel对象
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 设置表头
        $sheet->setCellValue('A1', '订单号');
        $sheet->setCellValue('B1', '商品名称');
        $sheet->setCellValue('C1', '分类');
        $sheet->setCellValue('D1', '用户邮箱');
        $sheet->setCellValue('E1', '通道名称');
        $sheet->setCellValue('F1', '支付方式');
        $sheet->setCellValue('G1', '支付金额');
        $sheet->setCellValue('H1', 'USDT价格');
        $sheet->setCellValue('I1', '数量');
        $sheet->setCellValue('J1', '总价');
        $sheet->setCellValue('K1', 'USDT总价');
        $sheet->setCellValue('L1', '手续费');
        $sheet->setCellValue('M1', '发货方式');
        $sheet->setCellValue('N1', '订单状态');
        $sheet->setCellValue('O1', '下单时间');
        $sheet->setCellValue('P1', '发货时间');
        $sheet->setCellValue('Q1', '卡密信息');
        $sheet->setCellValue('R1', '备注');

        // 设置列宽
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(10);
        $sheet->getColumnDimension('J')->setWidth(15);
        $sheet->getColumnDimension('K')->setWidth(15);
        $sheet->getColumnDimension('L')->setWidth(15);
        $sheet->getColumnDimension('M')->setWidth(15);
        $sheet->getColumnDimension('N')->setWidth(15);
        $sheet->getColumnDimension('O')->setWidth(20);
        $sheet->getColumnDimension('P')->setWidth(20);
        $sheet->getColumnDimension('Q')->setWidth(30);
        $sheet->getColumnDimension('R')->setWidth(30);

        // 设置表头样式
        $sheet->getStyle('A1:R1')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E0E0E0']
            ]
        ]);

        // 填充数据
        $row = 2;
        foreach ($list as $item) {
            // 计算USDT价格
            $usdtPrice = $usdtRate > 0 ? round($item['purchase_price'] / $usdtRate, 2) : 0;
            $usdtTotal = $usdtRate > 0 ? round($item['total_price'] / $usdtRate, 2) : 0;

            $sheet->setCellValue('A' . $row, $item['order_number']);
            $sheet->setCellValue('B' . $row, $item['product_name']);
            $sheet->setCellValue('C' . $row, isset($item['category']['name']) ? $item['category']['name'] : '');
            $sheet->setCellValue('D' . $row, $item['user_email']);
            $sheet->setCellValue('E' . $row, $item['channel_name']);
            $sheet->setCellValue('F' . $row, $item['payment_method']);
            $sheet->setCellValue('G' . $row, $item['purchase_price']);
            $sheet->setCellValue('H' . $row, $usdtPrice);
            $sheet->setCellValue('I' . $row, $item['quantity']);
            $sheet->setCellValue('J' . $row, $item['total_price']);
            $sheet->setCellValue('K' . $row, $usdtTotal);
            $sheet->setCellValue('L' . $row, $item['fee']);
            $sheet->setCellValue('M' . $row, $item['delivery_method']);
            $sheet->setCellValue('N' . $row, $item['status_text']);
            $sheet->setCellValue('O' . $row, $item['created_at']);
            $sheet->setCellValue('P' . $row, $item['created_at']);
            $sheet->setCellValue('Q' . $row, $item['card_info']);
            $sheet->setCellValue('R' . $row, $item['remark']);

            // 设置数据行样式
            $sheet->getStyle('A' . $row . ':R' . $row)->applyFromArray([
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ]);

            $row++;
        }

        // 设置数字列格式
        $sheet->getStyle('G2:K' . ($row-1))->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('I2:I' . ($row-1))->getNumberFormat()->setFormatCode('#,##0');

        // 生成文件名
        $filename = '订单列表_' . date('YmdHis') . '.xlsx';

        // 确保导出目录存在
        $exportDir = ROOT_PATH . 'uploads' . DS . 'export';
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0777, true);
        }

        // 保存文件
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filepath = $exportDir . DS . $filename;
        $writer->save($filepath);

        // 检查文件是否成功创建
        if (!file_exists($filepath)) {
            return $this->ajaxError('文件保存失败');
        }

        return $this->ajaxSuccess('导出成功', [
            'url' => '/uploads/export/' . $filename
        ]);
    }
    
    /**
     * 删除订单
     */
    // public function delete()
    // {
    //     $id = input('id/d');
    //     if (!$id) {
    //         $this->add_log('订单管理', '删除订单：参数错误', '失败');
    //         return $this->ajaxError('参数错误');
    //     }
        
    //     $order = OrderModel::find($id);
    //     if (!$order) {
    //         $this->add_log('订单管理', '删除订单：订单不存在', '失败');
    //         return $this->ajaxError('订单不存在');
    //     }
        
    //     if (!$order->delete()) {
    //         $this->add_log('订单管理', '删除订单：' . $order['order_number'], '失败');
    //         return $this->ajaxError('删除失败');
    //     }
        
    //     $this->add_log('订单管理', '删除订单：' . $order['order_number'], '成功');
    //     return $this->ajaxSuccess('删除成功');
    // }
    
    /**
     * 手动发货接口（前端提交成本价、卡密、备注，发货后发送邮件）
     */
    public function delivery()
    {
        if (!$this->request->isPost()) {
            $this->add_log('订单管理', '手动发货：请求方式错误', '失败');
            return $this->ajaxError('请求方式错误');
        }
        $data = input('post.');
        if (empty($data['order_number']) || empty($data['card_info'])) {
            $this->add_log('订单管理', '手动发货：参数错误', '失败');
            return $this->ajaxError('参数错误');
        }
        $cost_price = isset($data['cost_price']) ? floatval($data['cost_price']) : 0.00;
        $remark = isset($data['remark']) ? $data['remark'] : '';
        $cardInfo = [];
        $cardLines = explode("\n", str_replace("\r", "", $data['card_info']));
        foreach ($cardLines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                $cardInfo[] = [
                    'id' => 0,
                    'card_info' => $line
                ];
            }
        }
        if (empty($cardInfo)) {
            $this->add_log('订单管理', '手动发货：卡密信息格式错误', '失败');
            return $this->ajaxError('卡密信息格式错误');
        }
        // 只允许未完成订单发货，防并发
        $where = [
            'order_number' => $data['order_number'],
            'status' => ['in', [OrderStatus::PAID, OrderStatus::PENDING, OrderStatus::DELIVERY_FAILED, OrderStatus::CANCELLED]]
        ];
        $updateData = [
            'status' => OrderStatus::COMPLETED,
            'card_info' => json_encode($cardInfo, JSON_UNESCAPED_UNICODE),
            'remark' => $remark,
            'finished_at' => date('Y-m-d H:i:s'),
            'cost_price' => $cost_price
        ];
        $result = OrderModel::where($where)->update($updateData);
        if (!$result) {
            $this->add_log('订单管理', '手动发货：订单状态不允许或已完成', '失败');
            return $this->ajaxError('订单状态不允许或已完成');
        }
        // 获取最新订单信息（用于邮件发送）
        $order = OrderModel::where('order_number', $data['order_number'])->find();
        
        // 成本流水
        CostLog::create([
            'type' => CostLog::TYPE_MANUAL_DELIVERY,
            'relate_id' => $data['order_number'],
            'amount' => $cost_price,
            'amount_type' => CostLog::AMOUNT_ADD,
            'operator' => $this->adminInfo['username'],
            'remark' => $remark,
            'category_id' => $order ? $order['category_id'] : null,
            'product_name' => $order ? $order['product_name'] : '',
            'quantity' => $order ? $order['quantity'] : 0,
        ]);
        // 发货后发送邮件（如有邮箱）
        if (!empty($order['user_email'])) {
            try {
                $emailService = new \app\common\service\EmailService();
                $emailResult = $emailService->sendCardInfo($order['user_email'], [
                    'order_no' => $order['order_number'],
                    'product_name' => $order['product_name'],
                    'card_info' => "<br>" . implode("<br>", array_column($cardInfo, 'card_info')),
                    'query_password' => $order['query_password']
                ]);
                if ($emailResult['code'] != 200) {
                    // 邮件发送失败，订单状态改为发货失败
                    OrderModel::where(['order_number'=>$order['order_number'],'status'=>OrderStatus::COMPLETED])->update(['status'=>OrderStatus::DELIVERY_FAILED]);
                    $this->add_log('订单管理', '手动发货：发送邮件失败 - ' . $emailResult['message'], '失败');
                    return $this->ajaxSuccess('发货成功，但邮件发送失败，订单已标记为发货失败：' . $emailResult['message']);
                }
            } catch (\Exception $e) {
                // 邮件发送异常，订单状态改为发货失败
                OrderModel::where(['order_number'=>$order['order_number'],'status'=>OrderStatus::COMPLETED])->update(['status'=>OrderStatus::DELIVERY_FAILED]);
                $this->add_log('订单管理', '手动发货：发送邮件异常 - ' . $e->getMessage(), '失败');
                return $this->ajaxSuccess('发货成功，但邮件发送异常，订单已标记为发货失败：' . $e->getMessage());
            }
        }
        $this->add_log('订单管理', '手动发货：' . $data['order_number'], '成功');
        return $this->ajaxSuccess('发货成功');
    }

    /**
     * 数据概览
     */
    public function overview()
    {
        if (!$this->request->isGet()) {
            return $this->ajaxError('请求方式错误');
        }

        // 获取USDT汇率
        $paymentConfig = model('PaymentConfig')->find();
        $usdtRate = $paymentConfig ? $paymentConfig['usdt_rate'] : 0;

        // 时间范围
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $todayStart = $today . ' 00:00:00';
        $todayEnd = $today . ' 23:59:59';
        $yesterdayStart = $yesterday . ' 00:00:00';
        $yesterdayEnd = $yesterday . ' 23:59:59';
        $monthStart = date('Y-m-01 00:00:00');
        $monthEnd = date('Y-m-t 23:59:59');
        $lastMonthStart = date('Y-m-01 00:00:00', strtotime('-1 month'));
        $lastMonthEnd = date('Y-m-t 23:59:59', strtotime('-1 month'));

        // 商品销售额统计 - 使用数据库聚合查询（状态2=已支付，3=已完成）- 按订单完成时间统计
        $product_sales = array(
            'total' => model('ProductOrder')->where('status', 'in', [OrderStatus::PAID, OrderStatus::COMPLETED])->sum('total_price'),
            'today' => model('ProductOrder')->where('status', 'in', [OrderStatus::PAID, OrderStatus::COMPLETED])->where('finished_at', 'between', [$todayStart, $todayEnd])->sum('total_price'),
            'yesterday' => model('ProductOrder')->where('status', 'in', [OrderStatus::PAID, OrderStatus::COMPLETED])->where('finished_at', 'between', [$yesterdayStart, $yesterdayEnd])->sum('total_price'),
            'month' => model('ProductOrder')->where('status', 'in', [OrderStatus::PAID, OrderStatus::COMPLETED])->where('finished_at', 'between', [$monthStart, $monthEnd])->sum('total_price'),
            'last_month' => model('ProductOrder')->where('status', 'in', [OrderStatus::PAID, OrderStatus::COMPLETED])->where('finished_at', 'between', [$lastMonthStart, $lastMonthEnd])->sum('total_price')
        );

        // 支付单数统计 - 使用数据库聚合查询（状态2=已支付，3=已完成）- 按订单完成时间统计
        $pay_count = array(
            'total' => model('ProductOrder')->where('status', 'in', [OrderStatus::PAID, OrderStatus::COMPLETED])->count(),
            'today' => model('ProductOrder')->where('status', 'in', [OrderStatus::PAID, OrderStatus::COMPLETED])->where('finished_at', 'between', [$todayStart, $todayEnd])->count(),
            'yesterday' => model('ProductOrder')->where('status', 'in', [OrderStatus::PAID, OrderStatus::COMPLETED])->where('finished_at', 'between', [$yesterdayStart, $yesterdayEnd])->count()
        );

        // 商品成本统计 - 使用数据库聚合查询（状态2=已支付，3=已完成）- 按订单完成时间统计
        $product_cost = array(
            'total' => model('ProductOrder')->where('status', 'in', [OrderStatus::PAID, OrderStatus::COMPLETED])->sum('cost_price'),
            'today' => model('ProductOrder')->where('status', 'in', [OrderStatus::PAID, OrderStatus::COMPLETED])->where('finished_at', 'between', [$todayStart, $todayEnd])->sum('cost_price'),
            'yesterday' => model('ProductOrder')->where('status', 'in', [OrderStatus::PAID, OrderStatus::COMPLETED])->where('finished_at', 'between', [$yesterdayStart, $yesterdayEnd])->sum('cost_price')
        );

        // 结算金额统计 - 基于销售额和成本计算
        $settle_amount = array(
            'total' => $product_sales['total'] - $product_cost['total'],
            'today' => $product_sales['today'] - $product_cost['today'],
            'yesterday' => $product_sales['yesterday'] - $product_cost['yesterday']
        );

        // 退款金额统计 - 使用数据库聚合查询
        $refund_amount = array(
            'total' => model('ProductOrder')->where('status', OrderStatus::REFUNDED)->sum('total_price'),
            'today' => model('ProductOrder')->where('status', OrderStatus::REFUNDED)->where('finished_at', 'between', [$todayStart, $todayEnd])->sum('total_price'),
            'yesterday' => model('ProductOrder')->where('status', OrderStatus::REFUNDED)->where('finished_at', 'between', [$yesterdayStart, $yesterdayEnd])->sum('total_price')
        );

        // 平均消费统计 - 基于销售额和订单数计算
        $avg_amount = array(
            'total' => $pay_count['total'] > 0 ? round($product_sales['total'] / $pay_count['total'], 2) : 0,
            'today' => $pay_count['today'] > 0 ? round($product_sales['today'] / $pay_count['today'], 2) : 0,
            'yesterday' => $pay_count['yesterday'] > 0 ? round($product_sales['yesterday'] / $pay_count['yesterday'], 2) : 0
        );

        // 充值统计 - 使用数据库聚合查询
        $recharge_count = array(
            'total' => model('RechargeOrder')->where('status', 1)->count(),
            'today' => model('RechargeOrder')->where('status', 1)->where('created_at', 'between', array($todayStart, $todayEnd))->count(),
            'yesterday' => model('RechargeOrder')->where('status', 1)->where('created_at', 'between', array($yesterdayStart, $yesterdayEnd))->count()
        );
        $recharge_amount = array(
            'total' => model('RechargeOrder')->where('status', 1)->sum('recharge_amount'),
            'today' => model('RechargeOrder')->where('status', 1)->where('created_at', 'between', array($todayStart, $todayEnd))->sum('recharge_amount'),
            'yesterday' => model('RechargeOrder')->where('status', 1)->where('created_at', 'between', array($yesterdayStart, $yesterdayEnd))->sum('recharge_amount')
        );
        
        // 注册用户数量 - 使用数据库聚合查询
        $user_count = array(
            'total' => model('User')->count(),
            'today' => model('User')->where('created_at', 'between', array($todayStart, $todayEnd))->count(),
            'yesterday' => model('User')->where('created_at', 'between', array($yesterdayStart, $yesterdayEnd))->count()
        );

        return $this->ajaxSuccess('获取成功', array(
            'product_sales' => $product_sales,
            'pay_count' => $pay_count,
            'product_cost' => $product_cost,
            'settle_amount' => $settle_amount,
            'refund_amount' => $refund_amount,
            'avg_amount' => $avg_amount,
            'recharge_count' => $recharge_count,
            'recharge_amount' => $recharge_amount,
            'user_count' => $user_count
        ));
    }

    /**
     * 获取筛选选项
     */
    public function getOptions()
    {
        if (!$this->request->isGet()) {
            return $this->ajaxError('请求方式错误');
        }

        $options = [];
        
        // 获取商品分类
        $options['categories'] = model('ProductCategory')->field('id,name')->select();
        
        // 获取支付通道
        $options['channels'] = model('PaymentChannel')->field('id,name')->select();
        
        // 获取会员等级选项（包含游客）
        $memberLevels = model('MemberLevel')->field('id,name')->order('sort asc, id asc')->select();
        $options['user_roles'] = [
            ['id' => '-1', 'name' => '游客']
        ];
        foreach ($memberLevels as $level) {
            $options['user_roles'][] = [
                'id' => $level['id'],
                'name' => $level['name']
        ];
        }

        // 获取订单状态
        $options['status'] = [
            ['id' => OrderStatus::PENDING, 'name' => '待支付'],
            ['id' => OrderStatus::PAID, 'name' => '已支付'],
            ['id' => OrderStatus::COMPLETED, 'name' => '已完成'],
            ['id' => OrderStatus::CANCELLED, 'name' => '已取消'],
            ['id' => OrderStatus::REFUNDED, 'name' => '已退款']
        ];

        // 获取支付方式
        $options['payment_methods'] = [
            ['id' => 'alipay', 'name' => '支付宝'],
            ['id' => 'wechat', 'name' => '微信支付'],
            ['id' => 'bank', 'name' => '银行卡']
        ];
        
        // 获取发货方式
        $options['delivery_methods'] = [
            ['id' => 'auto', 'name' => '自动发货'],
            ['id' => 'manual', 'name' => '手动发货']
        ];
        
        return $this->ajaxSuccess('获取成功', $options);
    }

    /**
     * 重发卡密信息邮件
     */
    public function resendEmail()
    {
        if (!$this->request->isPost()) {
            $this->add_log('订单管理', '重发邮件：请求方式错误', '失败');
            return $this->ajaxError('请求方式错误');
        }

        $orderId = input('id/d');
        if (!$orderId) {
            $this->add_log('订单管理', '重发邮件：参数错误', '失败');
            return $this->ajaxError('参数错误');
        }

        // 查找订单
        $order = OrderModel::find($orderId);
        if (!$order) {
            $this->add_log('订单管理', '重发邮件：订单不存在', '失败');
            return $this->ajaxError('订单不存在');
        }

        // 检查订单状态
        if (!in_array($order['status'], [OrderStatus::COMPLETED, OrderStatus::DELIVERY_FAILED])) {
            $this->add_log('订单管理', '重发邮件：订单状态不正确', '失败');
            return $this->ajaxError('只有已完成或发货失败的订单才能重发邮件');
        }

        // 检查卡密信息
        if (empty($order['card_info'])) {
            $this->add_log('订单管理', '重发邮件：卡密信息为空', '失败');
            return $this->ajaxError('卡密信息为空');
        }

        // 检查用户邮箱
        if (empty($order['user_email'])) {
            $this->add_log('订单管理', '重发邮件：用户邮箱为空', '失败');
            return $this->ajaxError('用户邮箱为空');
        }

        // 开启事务
        Db::startTrans();
        try {
            // 解析卡密信息
            $cardInfo = json_decode($order->card_info, true);
            if (empty($cardInfo)) {
                throw new \Exception('卡密信息格式错误');
            }

            // 发送邮件
            $emailService = new \app\common\service\EmailService();
            $emailResult = $emailService->sendCardInfo($order->user_email, [
                'order_no' => $order->order_number,
                'product_name' => $order->product_name,
                'card_info' => "<br>" . implode("<br>", array_column($cardInfo, 'card_info')),
                'query_password' => $order->query_password
            ]);

            if ($emailResult['code'] != 200) {
                throw new \Exception('发送卡密邮件失败：' . $emailResult['message']);
            }

            // 如果订单状态为发货失败，更新为已完成
            if ($order['status'] == OrderStatus::DELIVERY_FAILED) {
                $updateResult = OrderModel::where('id', $order->id)
                    ->where('status', OrderStatus::DELIVERY_FAILED)
                    ->update(['status' => OrderStatus::COMPLETED, 'finished_at' => date('Y-m-d H:i:s')]);
                
                if (!$updateResult) {
                    throw new \Exception('更新订单状态失败');
                }
            }

            $this->add_log('订单管理', '重发邮件：' . $order['order_number'], '成功');
            Db::commit();
            return $this->ajaxSuccess('邮件发送成功');
        } catch (\Exception $e) {
            Db::rollback();
            $this->add_log('订单管理', '重发邮件：' . $e->getMessage(), '失败');
            return $this->ajaxError('邮件发送失败：' . $e->getMessage());
        }
    }

    // 新增：手动发货接口
    public function deliveryManual()
    {
        $id = input('id/d', 0);
        if (!$id) {
            return $this->ajaxError('参数错误');
        }
        Db::startTrans();
        try {
            $order = model('ProductOrder')->lock(true)->where('id', $id)->find();
            if (!$order) {
                Db::rollback();
                return $this->ajaxError('订单不存在');
            }
            if (!in_array($order['status'], [OrderStatus::PENDING, OrderStatus::PAID, OrderStatus::CANCELLED, OrderStatus::DELIVERY_FAILED])) {
                Db::rollback();
                return $this->ajaxError('当前订单状态不可发货');
            }
            $order->status = OrderStatus::COMPLETED;
            $order->finished_at = date('Y-m-d H:i:s');
            $order->save();
            // 增加累计消费
            if (!empty($order['user_email'])) {
                $user = model('User')->where('email', $order['user_email'])->find();
                if ($user) {
                    model('User')->where('id', $user['id'])->setInc('total_spent', $order['total_price']);
                }
            }
            Db::commit();
            $this->add_log('商品订单', '手动发货：' . $order['order_number'], '成功');
            return $this->ajaxSuccess('发货成功');
        } catch (\Exception $e) {
            Db::rollback();
            return $this->ajaxError('发货失败：' . $e->getMessage());
        }
    }

    // 新增：退款接口
    public function refund()
    {
        $id = input('id/d', 0);
        $refund_remark = input('refund_remark', '');
        if (!$id) {
            return $this->ajaxError('参数错误');
        }
        Db::startTrans();
        try {
            $order = model('ProductOrder')->lock(true)->where('id', $id)->find();
            // 中文注释：只允许已完成、发货失败、已支付状态的订单退款，防止并发多次退款
            if (!$order || !in_array($order['status'], [OrderStatus::COMPLETED, OrderStatus::DELIVERY_FAILED, OrderStatus::PAID])) {
                Db::rollback();
                return $this->ajaxError('订单不存在或状态不允许退款');
            }
            $user = model('User')->where('email', $order['user_email'])->find();
            if ($user) {
                // 操作人
                $token = $this->request->header('authorization');
                if (strpos($token, 'Bearer ') === 0) {
                    $token = substr($token, 7);
                }
                $manager = model('Manager')->where(['token' => $token, 'status' => 1])->find();
                $operator = $manager ? $manager['username'] : 'unknown';
                // 退款应该增加用户余额
                model('User')->where('id', $user['id'])->setInc('balance', $order['total_price']);
                // 扣除累计消费
                model('User')->where('id', $user['id'])->setDec('total_spent', $order['total_price']);
                $before = $user['balance'];
                $after = $user['balance'] + $order['total_price'];
                \app\admin\model\UserBalanceLog::create([
                    'user_id' => $user['id'],
                    'type' => '商品订单退款',
                    'amount' => $order['total_price'],
                    'before_balance' => $before,
                    'after_balance' => $after,
                    'direction' => 'in',
                    'order_no' => $order['order_number'],
                    'remark' => $refund_remark,
                    'operator' => $operator
                ]);
            }
            // 用where条件防止并发多次退款
            $where = [
                'id' => $id,
                'status' => ['in', [OrderStatus::COMPLETED, OrderStatus::DELIVERY_FAILED, OrderStatus::PAID]]
            ];
            $updateData = [
                'status' => OrderStatus::REFUNDED,
                'cost_price' => 0,
                'finished_at' => date('Y-m-d H:i:s')
            ];
            $result = model('ProductOrder')->where($where)->update($updateData);
            if (!$result) {
                Db::rollback();
                return $this->ajaxError('订单已被处理或状态异常，无法重复退款');
            }

            // 如果订单有卡密信息，将对应的库存卡密状态改为作废
            if (!empty($order['card_info'])) {
                $cardInfo = json_decode($order['card_info'], true);
                if (is_array($cardInfo)) {
                    foreach ($cardInfo as $card) {
                        if (!empty($card['id'])) {
                            // 将卡密状态改为作废
                            \app\admin\model\ProductStock::where('id', $card['id'])
                                ->update(['status' => \app\common\constants\StockStatus::VOID]);
                        }
                    }
                }
            }

            Db::commit();
            $this->add_log('商品订单', '订单退款：' . $order['order_number'], '成功');
            return $this->ajaxSuccess('退款成功');
        } catch (\Exception $e) {
            Db::rollback();
            $this->add_log('商品订单', '订单退款异常：' . $e->getMessage(), '失败');
            return $this->ajaxError('退款失败：' . $e->getMessage());
        }
    }

    /**
     * 商品销售明细接口
     * 参数：date_type= today/yesterday/month/last_month，默认today
     */
    public function productSalesDetail()
    {
        if (!$this->request->isGet()) {
            return $this->ajaxError('请求方式错误');
        }
        $date_type = input('date_type', 'today');
        $dateMap = array(
            'today' => array(date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')),
            'yesterday' => array(date('Y-m-d 00:00:00', strtotime('-1 day')), date('Y-m-d 23:59:59', strtotime('-1 day'))),
            'month' => array(date('Y-m-01 00:00:00'), date('Y-m-t 23:59:59')),
            'last_month' => array(date('Y-m-01 00:00:00', strtotime('-1 month')), date('Y-m-t 23:59:59', strtotime('-1 month')))
        );
        $range = isset($dateMap[$date_type]) ? $dateMap[$date_type] : $dateMap['today'];
        $start = $range[0];
        $end = $range[1];

        // 获取所有商品
        $products = model('Product')->select();
        $result = array();
        foreach ($products as $product) {
            $product_id = $product['id'];
            $name = $product['name'];
            
            // 使用数据库聚合查询替代PHP循环
            $orderWhere = array(
                'product_id' => $product_id,
                'status' => OrderStatus::COMPLETED,
                'finished_at' => array('between', array($start, $end))
            );
            
            // 销售额统计
            $sales = model('ProductOrder')->where($orderWhere)->sum('total_price');
            
            // 支付单数统计（去重订单号）
            $pay_count = model('ProductOrder')->where($orderWhere)->group('order_number')->count();
            
            // 销量统计（商品销售数量总和）
            $sales_quantity = model('ProductOrder')->where($orderWhere)->sum('quantity');
            
            // 平均消费
            $avg_amount = $pay_count > 0 ? round($sales / $pay_count, 2) : 0;
            
            // 商品成本统计
            $cost = model('ProductOrder')->where($orderWhere)->sum('cost_price');
            
            // 退款金额统计
            $refund = model('ProductOrder')->where(array(
                'product_id' => $product_id,
                'status' => OrderStatus::REFUNDED,
                'finished_at' => array('between', array($start, $end))
            ))->sum('total_price');
            
            // 结算金额
            $settle = $sales - $cost;
            
            // 购买人数统计（基于visitor_uuid去重，只统计已完成订单）
            $buyers = model('ProductOrder')
                ->where($orderWhere)
                ->where('visitor_uuid', '<>', '')
                ->group('visitor_uuid')
                ->count();
            
            // 浏览人数统计
            $viewers = \app\common\model\ProductViewStats::getProductViewers($product_id, $start, $end);
            
            // 转化率计算
            $conversion_rate = $viewers > 0 ? round(($buyers / $viewers) * 100, 2) : 0.00;
            
            $result[] = array(
                'product_name' => $name,
                'sales' => round($sales, 2),
                'pay_count' => $pay_count,
                'sales_quantity' => intval($sales_quantity), // 销量
                'avg_amount' => $avg_amount,
                'cost' => round($cost, 2),
                'refund' => round($refund, 2),
                'settle' => round($settle, 2),
                'buyers' => $buyers, // 商品付款人数
                'viewers' => $viewers, // 商品浏览人数
                'conversion_rate' => $conversion_rate // 转化率
            );
        }
        return $this->ajaxSuccess('ok', $result);
    }

    /**
     * 商品销售明细导出接口
     * POST /api/admin/order/productSalesDetailExport
     * 参数：date_type= today/month/last_month，默认today
     */
    public function productSalesDetailExport()
    {
        if (!$this->request->isPost()) {
            return $this->ajaxError('请求方式错误');
        }
        $date_type = input('date_type', 'today');
        $dateMap = array(
            'today' => array(date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')),
            'yesterday' => array(date('Y-m-d 00:00:00', strtotime('-1 day')), date('Y-m-d 23:59:59', strtotime('-1 day'))),
            'month' => array(date('Y-m-01 00:00:00'), date('Y-m-t 23:59:59')),
            'last_month' => array(date('Y-m-01 00:00:00', strtotime('-1 month')), date('Y-m-t 23:59:59', strtotime('-1 month')))
        );
        $range = isset($dateMap[$date_type]) ? $dateMap[$date_type] : $dateMap['today'];
        $start = $range[0];
        $end = $range[1];

        // 获取所有商品
        $products = model('Product')->select();
        $result = array();
        foreach ($products as $product) {
            $product_id = $product['id'];
            $name = $product['name'];
            
            // 使用数据库聚合查询替代PHP循环
            $orderWhere = array(
                'product_id' => $product_id,
                'status' => OrderStatus::COMPLETED,
                'finished_at' => array('between', array($start, $end))
            );
            
            // 销售额统计
            $sales = model('ProductOrder')->where($orderWhere)->sum('total_price');
            
            // 支付单数统计（去重订单号）
            $pay_count = model('ProductOrder')->where($orderWhere)->group('order_number')->count();
            
            // 销量统计（商品销售数量总和）
            $sales_quantity = model('ProductOrder')->where($orderWhere)->sum('quantity');
            
            // 平均消费
            $avg_amount = $pay_count > 0 ? round($sales / $pay_count, 2) : 0;
            
            // 商品成本统计 - 直接使用订单表cost_price字段
            $cost = model('ProductOrder')->where($orderWhere)->sum('cost_price');
            
            // 退款金额统计
            $refund = model('ProductOrder')->where(array(
                'product_id' => $product_id,
                'status' => OrderStatus::REFUNDED,
                'finished_at' => array('between', array($start, $end))
            ))->sum('total_price');
            
            // 结算金额
            $settle = $sales - $cost;
            
            // 购买人数统计（基于visitor_uuid去重，只统计已完成订单）
            $buyers = model('ProductOrder')
                ->where($orderWhere)
                ->where('visitor_uuid', '<>', '')
                ->group('visitor_uuid')
                ->count();
            
            // 浏览人数统计
            $viewers = \app\common\model\ProductViewStats::getProductViewers($product_id, $start, $end);
            
            // 转化率计算
            $conversion_rate = $viewers > 0 ? round(($buyers / $viewers) * 100, 2) : 0.00;
            
            $result[] = array(
                'product_name' => $name,
                'sales' => round($sales, 2),
                'pay_count' => $pay_count,
                'sales_quantity' => intval($sales_quantity), // 销量
                'avg_amount' => $avg_amount,
                'cost' => round($cost, 2),
                'refund' => round($refund, 2),
                'settle' => round($settle, 2),
                'buyers' => $buyers, // 商品付款人数
                'viewers' => $viewers, // 商品浏览人数
                'conversion_rate' => $conversion_rate // 转化率
            );
        }

        // 生成Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // 设置表头
        $sheet->setCellValue('A1', '商品名称');
        $sheet->setCellValue('B1', '销售额');
        $sheet->setCellValue('C1', '支付单数');
        $sheet->setCellValue('D1', '销量');
        $sheet->setCellValue('E1', '平均消费');
        $sheet->setCellValue('F1', '成本');
        $sheet->setCellValue('G1', '退款金额');
        $sheet->setCellValue('H1', '结算金额');
        $sheet->setCellValue('I1', '商品付款人数');
        $sheet->setCellValue('J1', '商品浏览人数');
        $sheet->setCellValue('K1', '转化率');
        // 设置列宽
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(15);
        $sheet->getColumnDimension('J')->setWidth(15);
        $sheet->getColumnDimension('K')->setWidth(15);
        // 设置表头样式
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E0E0E0']
            ]
        ]);
        // 填充数据
        $row = 2;
        foreach ($result as $item) {
            $sheet->setCellValue('A' . $row, $item['product_name']);
            $sheet->setCellValue('B' . $row, $item['sales']);
            $sheet->setCellValue('C' . $row, $item['pay_count']);
            $sheet->setCellValue('D' . $row, $item['sales_quantity']);
            $sheet->setCellValue('E' . $row, $item['avg_amount']);
            $sheet->setCellValue('F' . $row, $item['cost']);
            $sheet->setCellValue('G' . $row, $item['refund']);
            $sheet->setCellValue('H' . $row, $item['settle']);
            $sheet->setCellValue('I' . $row, $item['buyers']);
            $sheet->setCellValue('J' . $row, $item['viewers']);
            $sheet->setCellValue('K' . $row, $item['conversion_rate'].'%');
            $sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray([
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ]);
            $row++;
        }
        // 设置数字列格式
        $sheet->getStyle('B2:K' . ($row-1))->getNumberFormat()->setFormatCode('#,##0.00');
        // 生成文件名
        $filename = '商品销售明细_' . date('YmdHis') . '.xlsx';
        // 确保导出目录存在
        $exportDir = ROOT_PATH . 'uploads' . DS . 'export';
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0777, true);
        }
        // 保存文件
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filepath = $exportDir . DS . $filename;
        $writer->save($filepath);
        // 检查文件是否成功创建
        if (!file_exists($filepath)) {
            return $this->ajaxError('文件保存失败');
        }
        return $this->ajaxSuccess('导出成功', [
            'url' => '/uploads/export/' . $filename
        ]);
    }

    /**
     * 修改已完成订单的卡密信息（支持变动成本价增量，原子更新，防并发）
     */
    public function updateCardInfo()
    {
        $order_id = input('order_id/d');
        $new_card_info = input('new_card_info/s', '');
        $remark = input('remark/s', '');
        $cost_price_delta = input('cost_price_delta/f', 0.00); // 变动成本价（增量）
        if (!$order_id || $new_card_info === '') {
            return $this->ajaxError('参数错误');
        }
        $order = \app\admin\model\ProductOrder::find($order_id);
        if (!$order) {
            return $this->ajaxError('订单不存在');
        }
        if ($order['status'] != \app\common\constants\OrderStatus::COMPLETED) {
            return $this->ajaxError('仅允许已完成订单修改卡密');
        }
        // 自动发货订单，作废原卡密
        if ($order['delivery_method'] == 'auto' && !empty($order['card_info'])) {
            $oldCards = json_decode($order['card_info'], true);
            if (is_array($oldCards)) {
                foreach ($oldCards as $card) {
                    if (!empty($card['id'])) {
                        \app\admin\model\ProductStock::where('id', $card['id'])
                            ->update(['status' => \app\common\constants\StockStatus::VOID]);
                    }
                }
            }
        }
        // 原子更新成本价（防并发）
        if ($cost_price_delta != 0) {
            \app\admin\model\ProductOrder::where(['id' => $order_id, 'status' => \app\common\constants\OrderStatus::COMPLETED])->setInc('cost_price', $cost_price_delta);
        }
        // 更新卡密、备注、完成时间
        $order->card_info = $new_card_info;
        if ($remark !== '') {
            $order->remark = $remark;
        }
        $order->finished_at = date('Y-m-d H:i:s');
        $order->save();
        // 写入成本流水
        if ($cost_price_delta != 0) {
            \app\admin\model\CostLog::create([
                'type' => \app\admin\model\CostLog::TYPE_MANUAL_DELIVERY_MODIFY,
                'relate_id' => $order['order_number'],
                'amount' => abs($cost_price_delta),
                'amount_type' => $cost_price_delta > 0 ? \app\admin\model\CostLog::AMOUNT_ADD : \app\admin\model\CostLog::AMOUNT_SUB,
                'operator' => $this->adminInfo['username'],
                'remark' => '修改卡密信息变动成本',
                'batch_card_count' => 1,
                'category_id' => $order['category_id'],
                'product_name' => $order['product_name'],
                'quantity' => $order['quantity'],
            ]);
        }
        $this->add_log('订单管理', '修改已完成订单卡密：' . $order['order_number'], '成功');
        return $this->ajaxSuccess('修改成功');
    }
} 