<?php
namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use app\admin\model\RechargeOrder as RechargeOrderModel;
use app\admin\model\SystemInfo;
use think\Log;
use think\Db;

class CheckRechargeOrder extends Command
{
    protected function configure()
    {
        $this->setName('CheckRechargeOrder')
             ->setDescription('检查充值订单超时');
    }

    protected function execute(Input $input, Output $output)
    {
        // 获取系统配置的订单过期时间
        $systemInfo = SystemInfo::find();
        $orderTimeout = $systemInfo ? intval($systemInfo['order_timeout']) : 15; // 默认15分钟
        
        try {
            // 获取所有待支付且创建时间超过配置时间的充值订单
            $orders = RechargeOrderModel::where('status', 0) // 待支付
                ->where('created_at', '<', date('Y-m-d H:i:s', time() - $orderTimeout*60))
                ->select();
                
            if (empty($orders)) {
                // $output->writeln('没有需要处理的超时充值订单');
                return;
            }
            
            $successCount = 0;
            $failCount = 0;
            $errorOrders = [];
            
            foreach ($orders as $order) {
                // 开启事务
                Db::startTrans();
                try {
                    
                    // 更新订单状态为已取消
                    $updateResult = RechargeOrderModel::where('id', $order->id)
                        ->where('status', 0) // 确保状态还是待支付
                        ->update([
                            'status' => 3, // 已取消
                            'cancel_remark' => '系统自动取消超时订单'
                        ]);
                    
                    if (!$updateResult) {
                        $errorMessage = sprintf('充值订单%s更新状态失败', $order->order_no);
                        $output->writeln($errorMessage);
                        $errorOrders[] = [
                            'order_no' => $order->order_no,
                            'order_id' => $order->id,
                            'error' => '更新订单状态失败'
                        ];
                        $failCount++;
                        continue;
                    }
                    
                    Db::commit();
                    $successCount++;
                    
                    $output->writeln(sprintf('充值订单超时处理成功：%s', $order->order_no));
                    
                } catch (\Exception $e) {
                    Db::rollback();
                    $failCount++;
                    
                    $errorMessage = sprintf('充值订单超时处理失败：%s，错误：%s', $order->order_no, $e->getMessage());
                    $output->writeln($errorMessage);
                    
                    $errorOrders[] = [
                        'order_no' => $order->order_no,
                        'order_id' => $order->id,
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            $message = sprintf('充值订单超时检查完成，成功处理%d个订单，失败%d个订单', $successCount, $failCount);
            $output->writeln($message);
            
            // 如果有失败的订单，记录详细信息
            if (!empty($errorOrders)) {
                $errorMessage = '处理失败的充值订单详情：' . json_encode($errorOrders, JSON_UNESCAPED_UNICODE);
                $output->writeln($errorMessage);
            }
            
        } catch (\Exception $e) {
            $output->writeln('检查充值订单超时失败：' . $e->getMessage().'|'.$e->getLine());
        }
    }
} 