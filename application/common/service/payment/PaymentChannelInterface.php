<?php
namespace app\common\service\payment;

interface PaymentChannelInterface
{
    /**
     * 获取渠道配置
     * @return array
     */
    public function getConfig();

    /**
     * 创建支付订单
     * @param array $order 订单信息
     * @param array $config 渠道配置
     * @return array
     */
    public function createPayment($order, $config);

    /**
     * 查询支付订单
     * @param string $orderNo 订单号
     * @param array $config 渠道配置
     * @return array
     */
    public function queryPayment($orderNo, $config);

    /**
     * 处理支付回调
     * @param array $data 回调数据
     * @param array $config 渠道配置
     * @return array 返回统一格式的回调数据
     * [
     *     'success' => true/false,
     *     'message' => '错误信息',
     *     'data' => [
     *         'order_number' => '订单号',
     *         'amount' => '支付金额',
     *         'trade_no' => '渠道交易号',
     *         'status' => '支付状态'
     *     ]
     * ]
     */
    public function handleNotify($data, $config);
} 