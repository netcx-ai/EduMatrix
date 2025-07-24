<?php
declare (strict_types = 1);

namespace app\service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use think\facade\Config;
use think\facade\Log;

class EmailService
{
    protected $mailer;
    protected $config;

    public function __construct()
    {
        try {
            Log::info('开始初始化邮件服务...');

            // 获取邮件配置
            $mailConfig = Config::get('mail.connections.smtp');
            
            
            // 检查配置是否为空
            if (empty($mailConfig)) {
                Log::error('邮件配置为空');
                throw new \Exception('邮件配置为空，请检查配置文件');
            }

            $this->config = [
                'host' => $mailConfig['host'] ?? '',
                'port' => $mailConfig['port'] ?? 465,
                'encryption' => $mailConfig['encryption'] ?? 'ssl',
                'username' => $mailConfig['username'] ?? '',
                'password' => $mailConfig['password'] ?? '',
                'from_address' => $mailConfig['from']['address'] ?? '',
                'from_name' => $mailConfig['from']['name'] ?? 'EduMatrix'
            ];

            // 检查必要的配置
            if (empty($this->config['username'])) {
                Log::error('邮件用户名配置为空，当前配置：' . json_encode($this->config, JSON_UNESCAPED_UNICODE));
                throw new \Exception('邮件用户名配置为空，请检查 .env 文件中的 MAIL_USERNAME 配置');
            }
            if (empty($this->config['password'])) {
                Log::error('邮件密码配置为空，当前配置：' . json_encode($this->config, JSON_UNESCAPED_UNICODE));
                throw new \Exception('邮件密码配置为空，请检查 .env 文件中的 MAIL_PASSWORD 配置');
            }
            if (empty($this->config['from_address'])) {
                Log::error('发件人地址配置为空，当前配置：' . json_encode($this->config, JSON_UNESCAPED_UNICODE));
                throw new \Exception('发件人地址配置为空，请检查 .env 文件中的 MAIL_FROM_ADDRESS 配置');
            }

            Log::info('开始初始化 PHPMailer...');
            $this->mailer = new PHPMailer(true);
            
            // 服务器设置
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->config['host'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->config['username'];
            $this->mailer->Password = $this->config['password'];
            $this->mailer->SMTPSecure = $this->config['encryption'];
            $this->mailer->Port = $this->config['port'];
            $this->mailer->CharSet = 'UTF-8';
            
            // 发件人
            $this->mailer->setFrom(
                $this->config['from_address'],
                $this->config['from_name']
            );

            // SSL 证书验证设置
            $this->mailer->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];

            // 开启调试模式
            $this->mailer->SMTPDebug = 2;
            $this->mailer->Debugoutput = function($str, $level) {
                Log::info("SMTP Debug: $str");
            };

            Log::info('邮件服务初始化完成');

        } catch (\Exception $e) {
            Log::error('邮件服务初始化失败：' . $e->getMessage());
            Log::error('错误堆栈：' . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * 发送注册成功邮件
     * @param string $email 收件人邮箱
     * @param string $username 用户名
     * @return bool
     */
    public function sendRegisterSuccess($email, $username)
    {
        try {
            Log::info('开始发送注册成功邮件...');
            Log::info('收件人邮箱：' . $email);
            Log::info('用户名：' . $username);

            // 检查收件人邮箱
            if (empty($email)) {
                throw new Exception('收件人邮箱不能为空');
            }

            // 检查用户名
            if (empty($username)) {
                throw new Exception('用户名不能为空');
            }

            // 清除之前的收件人
            $this->mailer->clearAddresses();
            
            // 添加收件人
            $this->mailer->addAddress($email);
            
            // 邮件内容
            $this->mailer->isHTML(true);
            $this->mailer->Subject = '注册成功通知';
            
            // 邮件正文
            $content = $this->getRegisterSuccessTemplate($username);
            $this->mailer->Body = $content;
            $this->mailer->AltBody = strip_tags($content);
            
            Log::info('准备发送邮件...');
            // 发送邮件
            $result = $this->mailer->send();
            Log::info('邮件发送成功');
            return true;
        } catch (Exception $e) {
            // 记录错误日志
            Log::error('发送注册成功邮件失败：' . $e->getMessage());
            Log::error('错误详情：' . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * 获取注册成功邮件模板
     * @param string $username 用户名
     * @return string
     */
    protected function getRegisterSuccessTemplate($username)
    {
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>注册成功通知</title>
        </head>
        <body>
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <h2>欢迎加入 EduMatrix</h2>
                <p>亲爱的 {$username}：</p>
                <p>感谢您注册 EduMatrix 平台！您的账号已经成功创建。</p>
                <p>注册时间：{$this->getCurrentTime()}</p>
                <p>您现在可以使用手机号和密码登录我们的平台。</p>
                <p>如果您有任何问题，请随时联系我们。</p>
                <br>
                <p>祝您使用愉快！</p>
                <p>EduMatrix 团队</p>
            </div>
        </body>
        </html>
        HTML;
    }

    /**
     * 获取当前时间
     * @return string
     */
    protected function getCurrentTime()
    {
        return date('Y-m-d H:i:s');
    }
} 