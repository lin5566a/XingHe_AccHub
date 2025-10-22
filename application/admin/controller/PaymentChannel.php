<?php
namespace app\admin\controller;

use app\admin\model\PaymentChannel as ChannelModel;
use app\admin\model\PaymentChannelRate as RateModel;
use think\Db;

class PaymentChannel extends Base
{
    /**
     * 获取支付通道列表
     */
    public function index()
    {
        try {
            // 获取所有支付方式
            $payment_methods = ChannelModel::getPaymentMethods();
            
            // 获取所有通道信息
            $channels = ChannelModel::order('id', 'asc')->select();
            
            // 获取所有费率信息
            $rates = RateModel::order('channel_id', 'asc')->order('payment_method', 'asc')->select();
            
            // 构建返回数据 - 按通道分组
            $list = [];
            foreach ($channels as $channel) {
                $channelData = [
                    'id' => $channel->id,
                    'name' => $channel->name,
                    'abbr' => $channel->abbr,
                    'merchant_id' => $channel->merchant_id,
                    'merchant_key' => $channel->merchant_key,
                    'create_order_url' => $channel->create_order_url,
                    'query_order_url' => $channel->query_order_url,
                    'balance_query_url' => $channel->balance_query_url,
                    'notify_url' => $channel->notify_url,
                    'return_url' => $channel->return_url,
                    'create_time' => $channel->create_time,
                    'update_time' => $channel->update_time,
                    'rates' => []
                ];
                
                // 为该通道添加费率信息
                foreach ($rates as $rate) {
                    if ($rate->channel_id == $channel->id) {
                        $channelData['rates'][] = [
                            'id' => $rate->id,
                            'payment_method' => $rate->payment_method,
                            'payment_method_text' => ChannelModel::getPaymentMethodText($rate->payment_method),
                            'weight' => $rate->weight,
                            'fee_rate' => $rate->fee_rate,
                            'min_amount' => $rate->min_amount,
                            'max_amount' => $rate->max_amount,
                            'product_code' => $rate->product_code,
                            'status' => $rate->status,
                            'status_text' => RateModel::getStatusText($rate->status),
                            'create_time' => $rate->create_time,
                            'update_time' => $rate->update_time
                        ];
                    }
                }
                
                // 按支付方式排序费率
                usort($channelData['rates'], function($a, $b) {
                    $order = ['alipay', 'wechat', 'usdt'];
                    $aIndex = array_search($a['payment_method'], $order);
                    $bIndex = array_search($b['payment_method'], $order);
                    if ($aIndex === false) $aIndex = 999;
                    if ($bIndex === false) $bIndex = 999;
                    return $aIndex - $bIndex;
                });
                
                $list[] = $channelData;
            }
            
            return $this->ajaxSuccess('获取成功', [
                'list' => $list,
                'payment_methods' => $payment_methods
            ]);
            
        } catch (\Exception $e) {
            return $this->ajaxError('获取失败：' . $e->getMessage());
        }
    }
    
    /**
     * 添加支付通道
     */
    public function add()
    {
        if (!$this->request->isPost()) {
            return $this->ajaxError('请求方式错误');
        }
        
        $data = $this->request->post();
        
        // 验证必填字段
        if (empty($data['name']) || empty($data['rates'])) {
            return $this->ajaxError('缺少必要参数');
        }
        
        // 验证费率数据
        if (!is_array($data['rates'])) {
            return $this->ajaxError('费率数据格式错误');
        }
        
        // 开启事务
        Db::startTrans();
        try {
            // 创建通道
            $channel = new ChannelModel;
            $channel->name = $data['name'];
            $channel->abbr = isset($data['abbr']) ? $data['abbr'] : '';
            $channel->merchant_id = isset($data['merchant_id']) ? $data['merchant_id'] : '';
            $channel->merchant_key = isset($data['merchant_key']) ? $data['merchant_key'] : '';
            $channel->create_order_url = isset($data['create_order_url']) ? $data['create_order_url'] : '';
            $channel->query_order_url = isset($data['query_order_url']) ? $data['query_order_url'] : '';
            $channel->balance_query_url = isset($data['balance_query_url']) ? $data['balance_query_url'] : '';
            $channel->notify_url = isset($data['notify_url']) ? $data['notify_url'] : '';
            $channel->return_url = isset($data['return_url']) ? $data['return_url'] : '';
            
            if (!$channel->save()) {
                throw new \Exception('创建通道失败');
            }
            
            // 创建费率记录
            foreach ($data['rates'] as $rate) {
                if (empty($rate['payment_method']) || !isset($rate['weight']) || !isset($rate['fee_rate'])) {
                    throw new \Exception('费率数据不完整');
                }
                
                // 验证支付方式
                if (!array_key_exists($rate['payment_method'], ChannelModel::getPaymentMethods())) {
                    throw new \Exception('无效的支付方式');
                }
                
                // 验证权重
                if (!is_numeric($rate['weight']) || $rate['weight'] < 0) {
                    throw new \Exception('权重必须是非负整数');
                }
                
                // 验证费率
                if (!is_numeric($rate['fee_rate']) || $rate['fee_rate'] < 0 || $rate['fee_rate'] > 100) {
                    throw new \Exception('费率必须在0-100之间');
                }
                
                // 验证单笔限额
                if (!isset($rate['min_amount']) || !isset($rate['max_amount'])) {
                    throw new \Exception('单笔限额数据不完整');
                }
                
                if (!is_numeric($rate['min_amount']) || $rate['min_amount'] < 0) {
                    throw new \Exception('最小金额必须是非负数');
                }
                
                if (!is_numeric($rate['max_amount']) || $rate['max_amount'] < 0) {
                    throw new \Exception('最大金额必须是非负数');
                }
                
                if ($rate['max_amount'] > 0 && $rate['min_amount'] > $rate['max_amount']) {
                    throw new \Exception('最小金额不能大于最大金额');
                }
                
                $rateModel = new RateModel;
                $rateModel->channel_id = $channel->id;
                $rateModel->payment_method = $rate['payment_method'];
                $rateModel->weight = intval($rate['weight']);
                $rateModel->fee_rate = round($rate['fee_rate'], 2);
                $rateModel->min_amount = round($rate['min_amount'], 2);
                $rateModel->max_amount = round($rate['max_amount'], 2);
                $rateModel->status = isset($rate['status']) ? intval($rate['status']) : 1;
                $rateModel->product_code = isset($rate['product_code']) ? $rate['product_code'] : '';
                
                if (!$rateModel->save()) {
                    throw new \Exception('创建费率记录失败');
                }
            }
            
            Db::commit();
            $this->add_log('支付通道管理', '添加支付通道：' . $data['name'], '成功');
            return $this->ajaxSuccess('添加成功');
            
        } catch (\Exception $e) {
            Db::rollback();
            $this->add_log('支付通道管理', '添加支付通道：' . $data['name'], '失败');
            return $this->ajaxError($e->getMessage());
        }
    }
    
    /**
     * 编辑支付通道
     */
    public function edit()
    {
        if (!$this->request->isPost()) {
            return $this->ajaxError('请求方式错误');
        }
        
        $data = $this->request->post();
        
        // 验证必填字段
        if (empty($data['id']) || empty($data['name']) || empty($data['rates'])) {
            return $this->ajaxError('缺少必要参数');
        }
        
        // 验证通道是否存在
        $channel = ChannelModel::find($data['id']);
        if (!$channel) {
            return $this->ajaxError('支付通道不存在');
        }
        
        // 验证费率数据
        if (!is_array($data['rates'])) {
            return $this->ajaxError('费率数据格式错误');
        }
        
        // 开启事务
        Db::startTrans();
        try {
            // 更新通道信息
            $channel->name = $data['name'];
            $channel->abbr = isset($data['abbr']) ? $data['abbr'] : '';
            $channel->merchant_id = isset($data['merchant_id']) ? $data['merchant_id'] : '';
            $channel->merchant_key = isset($data['merchant_key']) ? $data['merchant_key'] : '';
            $channel->create_order_url = isset($data['create_order_url']) ? $data['create_order_url'] : '';
            $channel->query_order_url = isset($data['query_order_url']) ? $data['query_order_url'] : '';
            $channel->balance_query_url = isset($data['balance_query_url']) ? $data['balance_query_url'] : '';
            $channel->notify_url = isset($data['notify_url']) ? $data['notify_url'] : '';
            $channel->return_url = isset($data['return_url']) ? $data['return_url'] : '';
            
            if ($channel->save() === false) {
                throw new \Exception('更新通道失败');
            }
            
            // 更新费率记录
            foreach ($data['rates'] as $rate) {
                if (empty($rate['id']) || empty($rate['payment_method']) || !isset($rate['weight']) || !isset($rate['fee_rate'])) {
                    throw new \Exception('费率数据不完整');
                }
                
                // 验证费率记录是否存在
                $rateModel = RateModel::where('id', $rate['id'])
                    ->where('channel_id', $channel->id)
                    ->find();
                    
                if (!$rateModel) {
                    throw new \Exception('费率记录不存在');
                }
                
                // 验证支付方式
                if (!array_key_exists($rate['payment_method'], ChannelModel::getPaymentMethods())) {
                    throw new \Exception('无效的支付方式');
                }
                
                // 验证权重
                if (!is_numeric($rate['weight']) || $rate['weight'] < 0) {
                    throw new \Exception('权重必须是非负整数');
                }
                
                // 验证费率
                if (!is_numeric($rate['fee_rate']) || $rate['fee_rate'] < 0 || $rate['fee_rate'] > 100) {
                    throw new \Exception('费率必须在0-100之间');
                }
                
                // 验证单笔限额
                if (!isset($rate['min_amount']) || !isset($rate['max_amount'])) {
                    throw new \Exception('单笔限额数据不完整');
                }
                
                if (!is_numeric($rate['min_amount']) || $rate['min_amount'] < 0) {
                    throw new \Exception('最小金额必须是非负数');
                }
                
                if (!is_numeric($rate['max_amount']) || $rate['max_amount'] < 0) {
                    throw new \Exception('最大金额必须是非负数');
                }
                
                if ($rate['max_amount'] > 0 && $rate['min_amount'] > $rate['max_amount']) {
                    throw new \Exception('最小金额不能大于最大金额');
                }
                
                // 更新费率记录
                $rateModel->weight = intval($rate['weight']);
                $rateModel->fee_rate = round($rate['fee_rate'], 2);
                $rateModel->min_amount = round($rate['min_amount'], 2);
                $rateModel->max_amount = round($rate['max_amount'], 2);
                $rateModel->status = isset($rate['status']) ? intval($rate['status']) : 1;
                $rateModel->product_code = isset($rate['product_code']) ? $rate['product_code'] : '';
                
                if ($rateModel->save() === false) {
                    throw new \Exception('更新费率记录失败');
                }
            }
            
            Db::commit();
            $this->add_log('支付通道管理', '编辑支付通道：' . $data['name'], '成功');
            return $this->ajaxSuccess('更新成功');
            
        } catch (\Exception $e) {
            Db::rollback();
            $this->add_log('支付通道管理', '编辑支付通道：' . $data['name'], '失败');
            return $this->ajaxError($e->getMessage());
        }
    }
    
    /**
     * 删除支付通道
     */
    public function delete()
    {
        $id = input('id/d');
        if (!$id) {
            return $this->ajaxError('参数错误');
        }
        
        $channel = ChannelModel::find($id);
        if (!$channel) {
            return $this->ajaxError('支付通道不存在');
        }
        
        // 开启事务
        Db::startTrans();
        try {
            // 删除通道（关联的费率记录会自动删除，因为有外键约束）
            if (!$channel->delete()) {
                throw new \Exception('删除失败');
            }
            
            Db::commit();
            $this->add_log('支付通道管理', '删除支付通道：' . $channel->name, '成功');
            return $this->ajaxSuccess('删除成功');
            
        } catch (\Exception $e) {
            Db::rollback();
            $this->add_log('支付通道管理', '删除支付通道：' . $channel->name, '失败');
            return $this->ajaxError($e->getMessage());
        }
    }
    
    /**
     * 更新费率状态
     */
    public function updateRateStatus()
    {
        if (!$this->request->isPost()) {
            return $this->ajaxError('请求方式错误');
        }
        
        $id = input('id/d');
        $status = input('status/d');
        
        if (!$id || !isset($status)) {
            return $this->ajaxError('参数错误');
        }
        
        $rate = RateModel::find($id);
        if (!$rate) {
            return $this->ajaxError('费率记录不存在');
        }
        
        // 检查状态是否发生变化
        if ($rate->status == $status) {
            return $this->ajaxError('状态未发生变化');
        }
        
        // 更新状态
        $rate->status = $status;
        if ($rate->save() === false) {
            $this->add_log('支付通道管理', '更新费率状态：ID=' . $id, '失败');
            return $this->ajaxError('更新失败');
        }
        
        $this->add_log('支付通道管理', '更新费率状态：ID=' . $id, '成功');
        return $this->ajaxSuccess('更新成功');
    }

    /**
     * 同时编辑渠道信息和所有支付方式费率
     * @return \think\response\Json
     */
    public function editChannelWithRates()
    {
        if ($this->request->isPost()) {
            try {
                $data = $this->request->post();
                
                // 验证必填字段
                if (empty($data['channel_id'])) {
                    return $this->ajaxError('渠道ID不能为空');
                }
                
                if (empty($data['name'])) {
                    return $this->ajaxError('渠道名称不能为空');
                }
                
                if (empty($data['abbr'])) {
                    return $this->ajaxError('渠道标识不能为空');
                }
                
                // 开始事务
                Db::startTrans();
                
                try {
                    // 1. 更新渠道基本信息
                    $channelData = [
                        'name' => $data['name'],
                        'abbr' => $data['abbr'],
                        'merchant_id' => isset($data['merchant_id']) ? $data['merchant_id'] : '',
                        'merchant_key' => isset($data['merchant_key']) ? $data['merchant_key'] : '',
                        'create_order_url' => isset($data['create_order_url']) ? $data['create_order_url'] : '',
                        'query_order_url' => isset($data['query_order_url']) ? $data['query_order_url'] : '',
                        'balance_query_url' => isset($data['balance_query_url']) ? $data['balance_query_url'] : '',
                        'notify_url' => isset($data['notify_url']) ? $data['notify_url'] : '',
                        'return_url' => isset($data['return_url']) ? $data['return_url'] : '',
                        'update_time' => date('Y-m-d H:i:s')
                    ];
                    
                    $channelResult = ChannelModel::where('id', $data['channel_id'])->update($channelData);
                    if ($channelResult === false) {
                        throw new \Exception('更新渠道信息失败');
                    }
                    
                    // 2. 更新或创建支付方式费率
                    if (!empty($data['rates']) && is_array($data['rates'])) {
                        foreach ($data['rates'] as $rateData) {
                            if (empty($rateData['payment_method'])) {
                                continue;
                            }
                            
                            // 检查是否已存在该支付方式的费率记录
                            $existingRate = RateModel::where('channel_id', $data['channel_id'])
                                ->where('payment_method', $rateData['payment_method'])
                                ->find();
                            
                            $rateUpdateData = [
                                'channel_id' => $data['channel_id'],
                                'payment_method' => $rateData['payment_method'],
                                                        'weight' => intval(isset($rateData['weight']) ? $rateData['weight'] : 0),
                        'fee_rate' => floatval(isset($rateData['fee_rate']) ? $rateData['fee_rate'] : 0),
                        'min_amount' => floatval(isset($rateData['min_amount']) ? $rateData['min_amount'] : 0),
                        'max_amount' => floatval(isset($rateData['max_amount']) ? $rateData['max_amount'] : 0),
                        'status' => intval(isset($rateData['status']) ? $rateData['status'] : 1),
                        'product_code' => isset($rateData['product_code']) ? $rateData['product_code'] : '',
                                'update_time' => date('Y-m-d H:i:s')
                            ];
                            
                            if ($existingRate) {
                                // 更新现有记录
                                $rateResult = RateModel::where('id', $existingRate['id'])->update($rateUpdateData);
                                if ($rateResult === false) {
                                    throw new \Exception('更新费率信息失败');
                                }
                            } else {
                                // 创建新记录
                                $rateUpdateData['create_time'] = date('Y-m-d H:i:s');
                                $rateResult = RateModel::create($rateUpdateData);
                                if (!$rateResult) {
                                    throw new \Exception('创建费率信息失败');
                                }
                            }
                        }
                    }
                    
                    // 提交事务
                    Db::commit();
                    
                    $this->add_log('支付通道管理', '编辑渠道信息和费率：' . $data['name'], '成功');
                    return $this->ajaxSuccess('更新成功');
                    
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                    throw $e;
                }
                
            } catch (\Exception $e) {
                $this->add_log('支付通道管理', '编辑渠道信息和费率失败', '失败');
                return $this->ajaxError('更新失败：' . $e->getMessage());
            }
        }
        
        // GET请求，返回编辑页面数据
        $channelId = $this->request->get('id');
        if (empty($channelId)) {
            return $this->ajaxError('渠道ID不能为空');
        }
        
        try {
            // 获取渠道信息
            $channel = ChannelModel::find($channelId);
            if (!$channel) {
                return $this->ajaxError('渠道不存在');
            }
            
            // 获取所有支付方式费率
            $rates = RateModel::where('channel_id', $channelId)->select();
            
            // 构建返回数据
            $result = [
                'channel' => [
                    'id' => $channel->id,
                    'name' => $channel->name,
                    'abbr' => $channel->abbr,
                    'merchant_id' => $channel->merchant_id,
                    'merchant_key' => $channel->merchant_key,
                    'create_order_url' => $channel->create_order_url,
                    'query_order_url' => $channel->query_order_url,
                    'balance_query_url' => $channel->balance_query_url,
                    'notify_url' => $channel->notify_url,
                    'return_url' => $channel->return_url
                ],
                'rates' => []
            ];
            
            // 处理费率数据
            foreach ($rates as $rate) {
                $result['rates'][] = [
                    'id' => $rate->id,
                    'payment_method' => $rate->payment_method,
                    'weight' => $rate->weight,
                    'fee_rate' => $rate->fee_rate,
                    'min_amount' => $rate->min_amount,
                    'max_amount' => $rate->max_amount,
                    'status' => $rate->status,
                    'product_code' => $rate->product_code
                ];
            }
            
            return $this->ajaxSuccess('获取成功', $result);
            
        } catch (\Exception $e) {
            return $this->ajaxError('获取数据失败：' . $e->getMessage());
        }
    }

    /**
     * 根据渠道ID查询渠道信息（包括费率、产品编号、开关等）
     * @return \think\response\Json
     */
    public function getChannelInfo()
    {
        $channelId = $this->request->get('id');
        if (empty($channelId)) {
            return $this->ajaxError('渠道ID不能为空');
        }
        
        try {
            // 获取渠道信息
            $channel = ChannelModel::find($channelId);
            if (!$channel) {
                return $this->ajaxError('渠道不存在');
            }
            
            // 获取所有支付方式费率
            $rates = RateModel::where('channel_id', $channelId)
                ->order('payment_method', 'asc')
                ->select();
            
            // 构建返回数据
            $result = [
                'channel' => [
                    'id' => $channel->id,
                    'name' => $channel->name,
                    'abbr' => $channel->abbr,
                    'merchant_id' => $channel->merchant_id,
                    'merchant_key' => $channel->merchant_key,
                    'create_order_url' => $channel->create_order_url,
                    'query_order_url' => $channel->query_order_url,
                    'balance_query_url' => $channel->balance_query_url,
                    'notify_url' => $channel->notify_url,
                    'return_url' => $channel->return_url,
                    'create_time' => $channel->create_time,
                    'update_time' => $channel->update_time
                ],
                'rates' => [],
                'payment_methods' => ChannelModel::getPaymentMethods()
            ];
            
            // 处理费率数据
            foreach ($rates as $rate) {
                $result['rates'][] = [
                    'id' => $rate->id,
                    'payment_method' => $rate->payment_method,
                                            'payment_method_text' => isset($result['payment_methods'][$rate->payment_method]) ? $result['payment_methods'][$rate->payment_method] : $rate->payment_method,
                    'weight' => $rate->weight,
                    'fee_rate' => $rate->fee_rate,
                    'min_amount' => $rate->min_amount,
                    'max_amount' => $rate->max_amount,
                    'status' => $rate->status,
                    'status_text' => RateModel::getStatusText($rate->status),
                    'product_code' => $rate->product_code,
                    'create_time' => $rate->create_time,
                    'update_time' => $rate->update_time
                ];
            }
            
            // 按支付方式分组统计
            $summary = [
                'total_rates' => count($result['rates']),
                'enabled_rates' => 0,
                'disabled_rates' => 0,
                'payment_methods_count' => 0
            ];
            
            foreach ($result['rates'] as $rate) {
                if ($rate['status'] == 1) {
                    $summary['enabled_rates']++;
                } else {
                    $summary['disabled_rates']++;
                }
            }
            
            $summary['payment_methods_count'] = count(array_unique(array_column($result['rates'], 'payment_method')));
            $result['summary'] = $summary;
            
            return $this->ajaxSuccess('获取成功', $result);
            
        } catch (\Exception $e) {
            return $this->ajaxError('获取数据失败：' . $e->getMessage());
        }
    }
} 