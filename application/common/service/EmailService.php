<?php
namespace app\common\service;

use think\Log;
use think\Config;
use app\common\utils\Email;
use app\admin\model\EmailConfig;

class EmailService
{
    /**
     * 发送卡密邮件
     * @param string $email 收件人邮箱
     * @param array $data 邮件数据
     * @return array
     */
    public function sendCardInfo($email, $data)
    {
        try {
            // 获取邮件配置
            $config = EmailConfig::find();
            if (empty($config)) {
                throw new \Exception('邮件配置不存在');
            }

            // 邮件主题
            $subject = '您的订单卡密信息 - ' . $data['order_no'];

            // 邮件内容
            $content = "尊敬的客户：<br><br>";
            $content .= "感谢您的购买！以下是您的订单信息：<br><br>";
            $content .= "订单号：{$data['order_no']}<br>";
            $content .= "商品名称：{$data['product_name']}<br>";
            $content .= "卡密信息(使用方法请查看商品页介绍)：{$data['card_info']}<br><br>";
            $content .= "查询密码：{$data['query_password']}<br><br>";
            $content .= "请妥善保管您的卡密信息，如有任何问题请联系客服。<br><br>";
            $content .= "祝您使用愉快！<br>";

            // 发送邮件
            $result = Email::send($email, $subject, $content, $config);
            
            if (!$result['code']) {
                throw new \Exception('发送邮件失败:'.$result['msg']);
            }

            return [
                'code' => 200,
                'message' => '发送成功'
            ];

        } catch (\Exception $e) {
            Log::error('发送卡密邮件失败：' . $e->getMessage());
            return [
                'code' => 400,
                'message' => '发送失败：' . $e->getMessage()
            ];
        }
    }

    /**
     * 发送注册验证码邮件
     * @param string $email 收件人邮箱
     * @param string $captcha 验证码
     * @return array
     */
    public function sendRegisterCaptcha($email, $captcha)
    {
        try {
            // 获取邮件配置
            $config = EmailConfig::find();
            if (empty($config)) {
                throw new \Exception('邮件配置不存在');
            }

            // 获取系统名称
            $systemInfo = \app\admin\model\SystemInfo::find();
            $systemName = $systemInfo ? $systemInfo['system_name'] : '';
            
            // 邮件主题
            $subject = '注册验证码' . ($systemName ? ' - ' . $systemName : '');

            // 邮件内容
            $content = "尊敬的用户：<br><br>";
            $content .= "您的注册验证码是：<strong style='font-size: 20px; color: #007bff;'>{$captcha}</strong><br><br>";
            $content .= "验证码有效期为5分钟，请及时使用。<br><br>";
            $content .= "如果这不是您的操作，请忽略此邮件。<br><br>";
            $content .= "祝您使用愉快！<br>";
            if ($systemName) {
                $content .= $systemName;
            }

            // 发送邮件
            $result = Email::send($email, $subject, $content, $config);
            
            if (!$result['code']) {
                throw new \Exception('发送邮件失败:'.$result['msg']);
            }

            return [
                'code' => 200,
                'message' => '发送成功'
            ];

        } catch (\Exception $e) {
            Log::error('发送注册验证码邮件失败：' . $e->getMessage());
            return [
                'code' => 400,
                'message' => '发送失败：' . $e->getMessage()
            ];
        }
    }

    /**
     * 发送登录验证码邮件
     * @param string $email 收件人邮箱
     * @param string $captcha 验证码
     * @param string $nickname 用户昵称（可选）
     * @return array
     */
    public function sendLoginCaptcha($email, $captcha, $nickname = '')
    {
        try {
            // 获取邮件配置
            $config = EmailConfig::find();
            if (empty($config)) {
                throw new \Exception('邮件配置不存在');
            }

            // 获取系统名称
            $systemInfo = \app\admin\model\SystemInfo::find();
            $systemName = $systemInfo ? $systemInfo['system_name'] : '';
            
            // 邮件主题
            $subject = '登录验证码' . ($systemName ? ' - ' . $systemName : '');

            // 邮件内容
            $userName = !empty($nickname) ? $nickname : $email;
            $content = "尊敬的 {$userName}：<br><br>";
            $content .= "您的登录验证码是：<strong style='font-size: 20px; color: #007bff;'>{$captcha}</strong><br><br>";
            $content .= "验证码有效期为5分钟，请及时使用。<br><br>";
            $content .= "如果这不是您的操作，请忽略此邮件。<br><br>";
            $content .= "祝您使用愉快！<br>";
            if ($systemName) {
                $content .= $systemName;
            }

            // 发送邮件
            $result = Email::send($email, $subject, $content, $config);
            
            if (!$result['code']) {
                throw new \Exception('发送邮件失败:'.$result['msg']);
            }

            return [
                'code' => 200,
                'message' => '发送成功'
            ];

        } catch (\Exception $e) {
            Log::error('发送登录验证码邮件失败：' . $e->getMessage());
            return [
                'code' => 400,
                'message' => '发送失败：' . $e->getMessage()
            ];
        }
    }

    /**
     * 发送订单查询验证码邮件
     * @param string $email 收件人邮箱
     * @param string $captcha 验证码
     * @return array
     */
    public function sendOrderQueryCaptcha($email, $captcha)
    {
        try {
            // 获取邮件配置
            $config = EmailConfig::find();
            if (empty($config)) {
                throw new \Exception('邮件配置不存在');
            }

            // 获取系统名称
            $systemInfo = \app\admin\model\SystemInfo::find();
            $systemName = $systemInfo ? $systemInfo['system_name'] : '';
            
            // 邮件主题
            $subject = '订单查询验证码' . ($systemName ? ' - ' . $systemName : '');

            // 邮件内容
            $content = "尊敬的用户：<br><br>";
            $content .= "您的订单查询验证码是：<strong style='font-size: 20px; color: #007bff;'>{$captcha}</strong><br><br>";
            $content .= "验证码有效期为5分钟，请及时使用。<br><br>";
            $content .= "如果这不是您的操作，请忽略此邮件。<br><br>";
            $content .= "祝您使用愉快！<br>";
            if ($systemName) {
                $content .= $systemName;
            }

            // 发送邮件
            $result = Email::send($email, $subject, $content, $config);
            
            if (!$result['code']) {
                throw new \Exception('发送邮件失败:'.$result['msg']);
            }

            return [
                'code' => 200,
                'message' => '发送成功'
            ];

        } catch (\Exception $e) {
            Log::error('发送订单查询验证码邮件失败：' . $e->getMessage());
            return [
                'code' => 400,
                'message' => '发送失败：' . $e->getMessage()
            ];
        }
    }

    /**
     * 发送邮件
     * @param string $to 收件人
     * @param string $subject 主题
     * @param string $content 内容
     * @param array $config 配置
     * @return bool
     */
    private function sendEmail($to, $subject, $content, $config)
    {
        // 这里使用 PHPMailer 发送邮件
        // 您需要先安装 PHPMailer 包：composer require phpmailer/phpmailer
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            // 服务器配置
            $mail->isSMTP();
            $mail->Host = $config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['username'];
            $mail->Password = $config['password'];
            $mail->SMTPSecure = $config['secure'];
            $mail->Port = $config['port'];
            $mail->CharSet = 'UTF-8';

            // 发件人
            $mail->setFrom($config['from_email'], $config['from_name']);

            // 收件人
            $mail->addAddress($to);

            // 内容
            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body = $content;

            // 发送
            return $mail->send();

        } catch (\Exception $e) {
            Log::error('发送邮件失败：' . $e->getMessage());
            return false;
        }
    }
} 