<?php
namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use app\admin\model\ProductOrder as ProductOrderModel;
use app\admin\model\ProductStock as ProductStockModel;
use app\admin\model\SystemInfo;
use app\common\constants\OrderStatus;
use app\common\constants\StockStatus;
use think\Log;
use think\Db;

class CheckOrder extends Command
{
    protected function configure()
    {
        $this->setName('CheckOrder')
             ->setDescription('检查订单超时');
    }

    protected function execute(Input $input, Output $output)
    {
        // 获取系统配置的订单过期时间
        $systemInfo = SystemInfo::find();
        $orderTimeout = $systemInfo ? intval($systemInfo['order_timeout']) : 15; // 默认15分钟
        
        try {
            // 获取所有待付款且创建时间超过5分钟的订单
            $orders = ProductOrderModel::where('status', OrderStatus::PENDING)
                ->where('created_at', '<', date('Y-m-d H:i:s', time() - $orderTimeout*60))
                ->select();
                
            if (empty($orders)) {
                // $output->writeln('没有需要处理的超时订单');
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
                    $updateResult = ProductOrderModel::where('id', $order->id)
                        ->where('status', OrderStatus::PENDING)
                        ->update([
                            'status' => OrderStatus::CANCELLED,
                            'card_info' => null  // 清除卡密信息
                        ]);
                    
                    if (!$updateResult) {
                        $errorMessage = sprintf('订单%s更新状态失败', $order->order_number);
                        $output->writeln($errorMessage);
                        // Log::error($errorMessage);
                        $errorOrders[] = [
                            'order_number' => $order->order_number,
                            'order_id' => $order->id,
                            'error' => '更新订单状态失败'
                        ];
                        $failCount++;
                        continue;
                    }
                    
                    // 根据发货方式处理库存
                    if ($order->delivery_method == 'auto') {
                        // 自动发货：更新卡密状态
                        $cardInfo = json_decode($order->card_info, true);
                        if (!empty($cardInfo)) {
                            // 获取卡密ID列表
                            $cardIds = array_column($cardInfo, 'id');
                            
                            // 批量更新卡密状态为未使用
                            $stockUpdateResult = ProductStockModel::where('id', 'in', $cardIds)
                                ->where('status', StockStatus::LOCKED)
                                ->where('order_id', $order->order_number)
                                ->update([
                                    'status' => StockStatus::UNUSED,
                                    'order_id' => 0
                                ]);
                                
                            if ($stockUpdateResult === false) {
                                $errorMessage = sprintf('订单%s更新卡密状态失败', $order->order_number);
                                $output->writeln($errorMessage);
                                // Log::error($errorMessage);
                                $errorOrders[] = [
                                    'order_number' => $order->order_number,
                                    'order_id' => $order->id,
                                    'error' => '更新卡密状态失败'
                                ];
                                $failCount++;
                                continue;
                            }
                        }
                    } else {
                        // 手动发货：恢复商品库存
                        $stockUpdateResult = \app\admin\model\Product::where('id', $order->product_id)
                            ->update([
                                'stock' => Db::raw('stock + ' . $order->quantity)
                            ]);
                            
                        if (!$stockUpdateResult) {
                            $errorMessage = sprintf('订单%s恢复商品库存失败', $order->order_number);
                            $output->writeln($errorMessage);
                            Log::error($errorMessage);
                            $errorOrders[] = [
                                'order_number' => $order->order_number,
                                'order_id' => $order->id,
                                'error' => '恢复商品库存失败'
                            ];
                            $failCount++;
                            continue;
                        }
                    }
                    
                    Db::commit();
                    $successCount++;
                    
                    $output->writeln(sprintf('订单超时处理成功：%s', $order->order_number));
                    // Log::info('订单超时处理成功', [
                    //     'order_number' => $order->order_number,
                    //     'order_id' => $order->id
                    // ]);
                    
                } catch (\Exception $e) {
                    Db::rollback();
                    $failCount++;
                    
                    $errorMessage = sprintf('订单超时处理失败：%s，错误：%s', $order->order_number, $e->getMessage());
                    $output->writeln($errorMessage);
                    // Log::error('订单超时处理失败', [
                    //     'order_number' => $order->order_number,
                    //     'order_id' => $order->id,
                    //     'error' => $e->getMessage()
                    // ]);
                    
                    $errorOrders[] = [
                        'order_number' => $order->order_number,
                        'order_id' => $order->id,
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            $message = sprintf('超时检查完成，成功处理%d个订单，失败%d个订单', $successCount, $failCount);
            $output->writeln($message);
            // Log::info($message);
            
            // 如果有失败的订单，记录详细信息
            if (!empty($errorOrders)) {
                $errorMessage = '处理失败的订单详情：' . json_encode($errorOrders, JSON_UNESCAPED_UNICODE);
                $output->writeln($errorMessage);
                // Log::error($errorMessage);
            }
            
        } catch (\Exception $e) {
            // Log::error('检查订单超时失败：' . $e->getMessage().'|'.$e->getLine());
            $output->writeln('检查订单超时失败：' . $e->getMessage().'|'.$e->getLine());
        }
    }
} 