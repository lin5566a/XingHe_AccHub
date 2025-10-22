<?php
namespace app\common\service;

use think\captcha\Captcha;
use think\Response;
use think\Cache;
use think\facade\Config;

class CaptchaService
{
    /**
     * 默认配置
     */
    protected $defaultConfig = [
        'fontSize' => 5,     // 验证码字体大小
        'length'  => 4,      // 验证码位数
        'useCurve' => false, // 是否使用混淆曲线
        'useNoise' => false, // 是否添加杂点
    ];

    /**
     * 验证码缓存前缀
     */
    const CACHE_PREFIX = 'captcha_';

    /**
     * 验证码有效期（秒）
     */
    const EXPIRE_TIME = 300;

    /**
     * 生成随机验证码
     * @param int $length 验证码长度
     * @return string
     */
    protected function generateCode($length = 4)
    {
        $codeSet = '2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY';
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $codeSet[mt_rand(0, strlen($codeSet) - 1)];
        }
        return $code;
    }

    /**
     * 生成验证码
     * @param array $config 自定义配置
     * @return array 返回验证码图片和token
     */
    public function generate($config = [])
    {
        // 合并配置
        $config = array_merge($this->defaultConfig, $config);
        
        // 生成验证码
        $code = $this->generateCode($config['length']);
        
        // 生成token
        $token = md5(uniqid(mt_rand(), true));
        
        // 将验证码存入缓存
        $cacheKey = self::CACHE_PREFIX . $token;
        Cache::set($cacheKey, strtolower($code), self::EXPIRE_TIME);
        
        // 创建图片
        $image = imagecreatetruecolor(120, 40);
        $bg = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $bg);
        
        // 添加干扰线
        if ($config['useCurve']) {
            for ($i = 0; $i < 6; $i++) {
                $color = imagecolorallocate($image, mt_rand(0, 156), mt_rand(0, 156), mt_rand(0, 156));
                imageline($image, mt_rand(0, 120), mt_rand(0, 40), mt_rand(0, 120), mt_rand(0, 40), $color);
            }
        }
        
        // 添加干扰点
        if ($config['useNoise']) {
            for ($i = 0; $i < 100; $i++) {
                $color = imagecolorallocate($image, mt_rand(0, 156), mt_rand(0, 156), mt_rand(0, 156));
                imagesetpixel($image, mt_rand(0, 120), mt_rand(0, 40), $color);
            }
        }
        
        // 写入验证码
        for ($i = 0; $i < strlen($code); $i++) {
            $color = imagecolorallocate($image, mt_rand(0, 156), mt_rand(0, 156), mt_rand(0, 156));
            imagestring($image, $config['fontSize'], 20 + $i * 20, 10, $code[$i], $color);
        }
        
        // 输出图片
        ob_start();
        imagepng($image);
        $imageContent = ob_get_clean();
        imagedestroy($image);
        
        // 转换为base64
        $base64 = 'data:image/png;base64,' . base64_encode($imageContent);
        
        return [
            'code_token' => $token,
            'image' => $base64
        ];
    }

    /**
     * 验证验证码
     * @param string $code 验证码
     * @param string $token 验证码token
     * @return bool
     */
    public function check($code, $token)
    {
        if (empty($code) || empty($token)) {
            return false;
        }
        
        $cacheKey = self::CACHE_PREFIX . $token;
        $cachedCode = Cache::get($cacheKey);
        
        // 验证成功后删除缓存
        if ($cachedCode && strtolower($code) === $cachedCode) {
            Cache::rm($cacheKey);
            return true;
        }
        
        return false;
    }
} 