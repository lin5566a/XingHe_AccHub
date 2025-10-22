<?php
namespace app\admin\controller;

use app\admin\model\RechargeOrder as RechargeOrderModel;
use app\admin\model\User as UserModel;
use app\admin\model\UserBalanceLog;
use think\Db;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RechargeOrder extends Base
{
    // 充值订单列表
    public function index()
    {
        $where = [];
        $order_no = input('order_no', '');
        $nickname = input('nickname', '');
        $channel_name = input('channel_name', '');
        $payment_method = input('payment_method', '');
        $status = input('status', '');
        $start_time = input('start_time', '');
        $end_time = input('end_time', '');
        if ($order_no !== '') {
            $where['order_no'] = ['like', "%$order_no%"];
        }
        if ($nickname !== '') {
            $where['nickname'] = ['like', "%$nickname%"];
        }
        if ($channel_name !== '') {
            $where['channel_name'] = ['like', "%$channel_name%"];
        }
        if ($payment_method !== '') {
            $where['payment_method'] = $payment_method;
        }
        if ($status !== '') {
            $where['status'] = $status;
        }
        if ($start_time && $end_time) {
            $where['created_at'] = ['between', [$start_time . ' 00:00:00', $end_time . ' 23:59:59']];
        } elseif ($start_time) {
            $where['created_at'] = ['>=', $start_time . ' 00:00:00'];
        } elseif ($end_time) {
            $where['created_at'] = ['<=', $end_time . ' 23:59:59'];
        }
        $page = input('page/d', 1);
        $limit = input('limit/d', 10);
        $total = RechargeOrderModel::where($where)->count();
        $list = RechargeOrderModel::where($where)->order('id desc')->page($page, $limit)->select();
        // 增加status_text字段
        foreach ($list as &$item) {
            $item['status_text'] = $item->getStatusTextAttr('', $item->toArray());
        }
        // 统计
        $total_amount = RechargeOrderModel::where($where)->sum('recharge_amount');
        $total_fee = RechargeOrderModel::where($where)->sum('fee');
        $total_arrive = RechargeOrderModel::where($where)->sum('arrive_amount');
        // 总订单数（不受筛选条件影响）
        $all_total = RechargeOrderModel::count();
        // 成功订单数和金额统计逻辑
        if ($status === '') {
            // 没有状态筛选时，统计所有订单中的成功订单
            $success_total = RechargeOrderModel::where('status', 1)->count();
            $success_amount = RechargeOrderModel::where('status', 1)->sum('recharge_amount');
        } elseif ($status == 1) {
            // 筛选已完成时，成功订单数等于当前筛选结果
            $success_total = $total;
            $success_amount = RechargeOrderModel::where($where)->sum('recharge_amount');
        } else {
            // 其他状态，成功订单为0
            $success_total = 0;
            $success_amount = 0;
        }
        return $this->ajaxSuccess('获取成功', [
            'total' => $total,
            'list' => $list,
            'total_amount' => $total_amount,
            'total_fee' => $total_fee,
            'total_arrive' => $total_arrive,
            'all_total' => $all_total,
            'success_total' => $success_total,
            'success_amount' => $success_amount
        ]);
    }

    // 导出订单（全部/选中）
    public function export()
    {
        if (!$this->request->isPost()) {
            return $this->ajaxError('请求方式错误');
        }
        $data = $this->request->post();
        $where = [];
        // 新增：如果type=selected且有ids，则只导出选中订单，否则按筛选条件导出
        if (isset($data['type']) && $data['type'] === 'selected' && !empty($data['ids'])) {
            $where['id'] = ['in', $data['ids']];
        } else {
            $order_no = isset($data['order_no']) ? $data['order_no'] : '';
            $nickname = isset($data['nickname']) ? $data['nickname'] : '';
            $channel_name = isset($data['channel_name']) ? $data['channel_name'] : '';
            $payment_method = isset($data['payment_method']) ? $data['payment_method'] : '';
            $status = isset($data['status']) ? $data['status'] : '';
            $start_time = isset($data['start_time']) ? $data['start_time'] : '';
            $end_time = isset($data['end_time']) ? $data['end_time'] : '';
            if ($order_no !== '') {
                $where['order_no'] = ['like', "%$order_no%"];
            }
            if ($nickname !== '') {
                $where['nickname'] = ['like', "%$nickname%"];
            }
            if ($channel_name !== '') {
                $where['channel_name'] = ['like', "%$channel_name%"];
            }
            if ($payment_method !== '') {
                $where['payment_method'] = $payment_method;
            }
            if ($status !== '') {
                $where['status'] = $status;
            }
            if ($start_time && $end_time) {
                $where['created_at'] = ['between', [$start_time . ' 00:00:00', $end_time . ' 23:59:59']];
            } elseif ($start_time) {
                $where['created_at'] = ['>=', $start_time . ' 00:00:00'];
            } elseif ($end_time) {
                $where['created_at'] = ['<=', $end_time . ' 23:59:59'];
            }
        }
        // 获取总数，限制导出数量
        $total = RechargeOrderModel::where($where)->count();
        if ($total > 10000) {
            return $this->ajaxError('数据量过大，请缩小查询范围或使用分页导出');
        }
        
        $list = RechargeOrderModel::where($where)->order('id desc')->limit(10000)->select();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', '订单号');
        $sheet->setCellValue('B1', '用户昵称');
        $sheet->setCellValue('C1', '用户邮箱');
        $sheet->setCellValue('D1', '充值金额');
        $sheet->setCellValue('E1', '手续费');
        $sheet->setCellValue('F1', '到账金额');
        $sheet->setCellValue('G1', '通道名称');
        $sheet->setCellValue('H1', '支付方式');
        $sheet->setCellValue('I1', '订单状态');
        $sheet->setCellValue('J1', '创建时间');
        $sheet->setCellValue('K1', '完成时间');
        $row = 2;
        foreach ($list as $item) {
            $sheet->setCellValue('A' . $row, $item['order_no']);
            $sheet->setCellValue('B' . $row, $item['nickname']);
            $sheet->setCellValue('C' . $row, $item['user_email']);
            $sheet->setCellValue('D' . $row, $item['recharge_amount']);
            $sheet->setCellValue('E' . $row, $item['fee']);
            $sheet->setCellValue('F' . $row, $item['arrive_amount']);
            $sheet->setCellValue('G' . $row, $item['channel_name']);
            $sheet->setCellValue('H' . $row, $item['payment_method']);
            $sheet->setCellValue('I' . $row, $item['status']);
            $sheet->setCellValue('J' . $row, $item['created_at']);
            $sheet->setCellValue('K' . $row, $item['finished_at']);
            $row++;
        }
        $filename = '充值订单_' . date('YmdHis') . '.xlsx';
        // 保存路径与商品订单一致
        $exportDir = ROOT_PATH . 'uploads' . DS . 'export';
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0777, true);
        }
        $filepath = $exportDir . DS . $filename;
        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);
        return $this->ajaxSuccess('导出成功', ['url' => '/uploads/export/' . $filename]);
    }

    // 订单退款
    public function refund()
    {
        if (!$this->request->isPost()) {
            return $this->ajaxError('请求方式错误');
        }
        $id = input('id/d', 0);
        $refund_amount = floatval(input('refund_amount', 0));
        $refund_remark = input('refund_remark', '');
        if (!$id || $refund_amount <= 0) {
            return $this->ajaxError('参数错误');
        }
        $order = RechargeOrderModel::find($id);
        if (!$order || $order['status'] != 1) {
            return $this->ajaxError('订单不存在或状态不允许退款');
        }
        if ($refund_amount > $order['recharge_amount']) {
            return $this->ajaxError('退款金额不能大于到账金额');
        }
        $user = UserModel::find($order['user_id']);
        if (!$user) {
            return $this->ajaxError('用户不存在');
        }
        // 操作人
        $token = $this->request->header('authorization');
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }
        $manager = model('Manager')->where(['token' => $token, 'status' => 1])->find();
        $operator = $manager ? $manager['username'] : 'unknown';
        Db::startTrans();
        try {
            // 使用原子操作扣减用户余额和累计充值
            $balanceUpdateResult = UserModel::where('id', $user['id'])
                ->where('balance', '>=', $refund_amount) // 确保余额足够
                ->update([
                    'balance' => Db::raw('balance - ' . $refund_amount),
                    'total_recharge' => Db::raw('total_recharge - ' . $refund_amount)
                ]);
            
            if (!$balanceUpdateResult) {
                throw new \Exception('用户余额不足或更新失败');
            }
            
            // 自动调整VIP等级
            UserModel::autoUpdateVipLevel($user['id']);
            
            // 更新订单状态
            $order->status = 2;
            $order->refund_amount = $refund_amount;
            $order->refund_remark = $refund_remark;
            $order->refund_operator = $operator;
            $order->save();
            
            // 写入余额日志
            $before = $user['balance'] + $refund_amount; // 退款前余额
            $after = $user['balance']; // 退款后余额
            UserBalanceLog::create([
                'user_id' => $user['id'],
                'type' => '退款',
                'amount' => $refund_amount,
                'before_balance' => $before,
                'after_balance' => $after,
                'direction' => 'out',
                'order_no' => $order['order_no'],
                'remark' => $refund_remark,
                'operator' => $operator
            ]);
            
            Db::commit();
            $this->add_log('充值订单', '订单退款：' . $order['order_no'], '成功');
            return $this->ajaxSuccess('退款成功');
        } catch (\Exception $e) {
            Db::rollback();
            $this->add_log('充值订单', '订单退款：' . $order['order_no'] . ' - ' . $e->getMessage(), '失败');
            return $this->ajaxError('退款失败：' . $e->getMessage());
        }
    }

    // 确认订单接口
    public function confirm()
    {
        if (!$this->request->isPost()) {
            return $this->ajaxError('请求方式错误');
        }
        
        $id = input('id/d', 0);
        
        if (!$id) {
            return $this->ajaxError('参数错误');
        }
        
        // 开启事务并加锁，防止并发问题
        Db::startTrans();
        try {
            // 使用lock(true)加锁查询，防止并发修改
            $order = RechargeOrderModel::lock(true)->find($id);
            if (!$order) {
                Db::rollback();
                return $this->ajaxError('订单不存在');
            }
            
            // 检查订单状态
            if ($order['status'] != 0) {
                Db::rollback();
                return $this->ajaxError('订单状态不正确，只能确认待支付的订单');
            }
            
            // 查找用户
            $user = UserModel::lock(true)->find($order['user_id']);
            if (!$user) {
                Db::rollback();
                return $this->ajaxError('用户不存在');
            }
            
            // 获取操作人信息
            $token = $this->request->header('authorization');
            if (strpos($token, 'Bearer ') === 0) {
                $token = substr($token, 7);
            }
            $manager = model('Manager')->where(['token' => $token, 'status' => 1])->find();
            $operator = $manager ? $manager['username'] : 'unknown';
            
            // 使用原子操作增加用户余额和累计充值（充值金额）
            $balanceUpdateResult = UserModel::where('id', $user['id'])
                ->update([
                    'balance' => Db::raw('balance + ' . $order['recharge_amount']),
                    'total_recharge' => Db::raw('total_recharge + ' . $order['recharge_amount'])
                ]);
            
            // 自动调整VIP等级
            UserModel::autoUpdateVipLevel($user['id']);
            
            if (!$balanceUpdateResult) {
                throw new \Exception('更新用户余额失败');
            }
            
            // 更新订单状态
            $order->status = 1; // 已完成
            $order->finished_at = date('Y-m-d H:i:s');
            $order->confirm_operator = $operator;
            $order->save();
            
            // 写入余额日志
            $before = $user['balance'] - $order['recharge_amount']; // 充值前余额
            $after = $user['balance']; // 充值后余额
            UserBalanceLog::create([
                'user_id' => $user['id'],
                'type' => '充值',
                'amount' => $order['recharge_amount'],
                'before_balance' => $before,
                'after_balance' => $after,
                'direction' => 'in',
                'order_no' => $order['order_no'],
                'remark' => '管理员确认充值',
                'operator' => $operator
            ]);
            
            Db::commit();
            $this->add_log('充值订单', '确认订单：' . $order['order_no'], '成功');
            return $this->ajaxSuccess('确认成功');
        } catch (\Exception $e) {
            Db::rollback();
            $this->add_log('充值订单', '确认订单：' . $e->getMessage(), '失败');
            return $this->ajaxError('确认失败：' . $e->getMessage());
        }
    }

    /**
     * 获取充值订单筛选选项
     */
    public function getOptions()
    {
        if (!$this->request->isGet()) {
            return $this->ajaxError('请求方式错误');
        }
        $options = [];
        // 充值订单状态筛选项
        $options['status'] = [
            ['id' => 0, 'name' => '待支付'],
            ['id' => 1, 'name' => '已完成'],
            ['id' => 2, 'name' => '已退款'],
            ['id' => 3, 'name' => '已取消']
        ];
        // 支付方式筛选项
        $options['payment_methods'] = [
            ['id' => 'alipay', 'name' => '支付宝'],
            ['id' => 'wechat', 'name' => '微信支付'],
            ['id' => 'bank', 'name' => '银行卡']
        ];
        return $this->ajaxSuccess('获取成功', $options);
    }
} 