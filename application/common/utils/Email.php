<?php
namespace app\common\utils;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Email
{
    /**
     * 发送邮件
     * @param string $to 收件人
     * @param string $subject 主题
     * @param string $content 内容
     * @param array $config 邮件配置
     * @return array
     */
    public static function send($to, $subject, $content, $config = null)
    {
        try {
            // 如果没有传配置，从数据库获取
            if (empty($config)) {
                $emailConfig = model('EmailConfig')->getInfo();
                if (empty($emailConfig)) {
                    return ['code' => 0, 'msg' => '邮件配置不存在'];
                }
                $config = $emailConfig;
            }

            $mail = new PHPMailer(true);
            $mail->CharSet = 'UTF-8';           //设定邮件编码
            $mail->SMTPDebug = 0;               //调试模式输出2
            $mail->isSMTP();                    //使用SMTP
            $mail->Host = $config['smtp_server'];//SMTP服务器
            $mail->SMTPAuth = true;             //允许 SMTP 认证
            $mail->Username = $config['username'];//SMTP 用户名
            $mail->Password = $config['auth_code'];//SMTP 密码
            $mail->SMTPSecure = strtolower($config['security_protocol']);//允许 TLS 或者ssl协议
            $mail->Port = $config['port'];      //服务器端口 25 或者465 具体要看邮箱服务器支持

            // 添加连接超时设置
            // $mail->Timeout = 30;                // 设置 SMTP 超时时间
            // $mail->SMTPKeepAlive = true;        // 保持 SMTP 连接
            
            // 添加错误处理选项
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );


            $mail->setFrom($config['sender_email'], $config['sender_name']);  //发件人
            $mail->addAddress($to);  //收件人
            
            $mail->isHTML(true);  //允许HTML格式
            $mail->Subject = $subject; //邮件主题
            $mail->Body = $content;    //邮件内容

            if($mail->send()) {
                return ['code' => 1, 'msg' => '发送成功'];
            }
            return ['code' => 0, 'msg' => '发送失败：' . $mail->ErrorInfo];
        } catch (\Exception $e) {
            // 捕获所有异常，返回具体错误信息
            return ['code' => 0, 'msg' => '发送失败：' . $e->getMessage()];
        } finally {
            // 关闭SMTP连接
            if (isset($mail)) {
                $mail->smtpClose();
            }
        }
    }
} 