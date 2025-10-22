<?php
namespace app\common\service\payment;

use app\common\service\payment\channels\SDPayChannel;
use app\common\service\payment\channels\K4PayChannel;
use app\common\service\payment\channels\SpeedzfChannel;
use app\common\service\payment\channels\PmpPayChannel;

class PaymentFactory
{
    /**
     * 支付渠道映射
     * @var array
     */
    private static $channels = [
        'sdpay' => SDPayChannel::class,
        'k4pay' => K4PayChannel::class,
        'speedzf' => SpeedzfChannel::class,
        'pmppay' => PmpPayChannel::class
    ];

    /**
     * 获取支付渠道实例
     * @param string $channel 渠道标识
     * @return PaymentChannelInterface
     * @throws \Exception
     */
    public static function getChannel($channel)
    {
        if (!isset(self::$channels[$channel])) {
            throw new \Exception('不支持的支付渠道');
        }

        $class = self::$channels[$channel];
        return new $class();
    }
} 