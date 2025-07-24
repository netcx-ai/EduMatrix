<?php
declare (strict_types = 1);

namespace app\service;

use AlibabaCloud\Client\AlibabaCloud;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Log;
use app\model\SystemConfig;

class SmsService
{
    protected $config;
    protected $client;
    protected $isTestMode;

    // 验证码缓存前缀
    private const CACHE_PREFIX = 'sms_code:';
    // 发送时间缓存前缀
    private const SEND_TIME_PREFIX = 'sms_send_time:';
    // 冷却时间（秒）
    private const COOLING_TIME = 60;
    // 验证码有效期（秒）
    private const CODE_EXPIRE_TIME = 300;

    public function __construct()
    {
        // 从环境变量读取测试模式配置
        $this->isTestMode = env('SMS.TEST_MODE', false);
                
        // 从数据库获取配置
        $smsConfig = SystemConfig::where('type', 'sms')
            ->where('is_default', 1)
            ->where('status', 1)
            ->find();
            
        if (!$smsConfig) {
            throw new \Exception('未找到默认短信配置');
        }
        
        // 设置配置
        $driverConfig = $smsConfig->config;
        
        $this->config = [
            'driver' => $smsConfig->driver,
            $smsConfig->driver => $driverConfig
        ];
        
        Log::info('SmsService 初始化配置：' . json_encode($this->config, JSON_UNESCAPED_UNICODE));
        
        // 验证配置
        if (empty($this->config)) {
            throw new \Exception('短信配置未初始化');
        }
        
        // 验证驱动配置
        $driver = $this->config['driver'] ?? '';
        if (empty($driver)) {
            throw new \Exception('未设置短信驱动');
        }
        
        // 验证对应驱动的配置
        $driverConfig = $this->config[$driver] ?? [];
        if (empty($driverConfig)) {
            throw new \Exception("{$driver} 驱动配置未初始化");
        }
        
    }

    /**
     * 发送验证码
     * @param string $phone 手机号
     * @return array 返回发送结果，测试模式下会包含验证码
     */
    public function sendCode(string $phone): array
    {
        try {

            // 检查是否在冷却时间内
            if ($this->checkCooling($phone)) {
                throw new \Exception('发送太频繁，请稍后再试');
            }

            // 生成6位随机验证码
            $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // 获取当前使用的驱动
            $driver = $this->config['driver'];
            Log::info('当前使用的驱动：' . $driver);
            
            // 如果是测试模式，直接返回验证码
            if ($this->isTestMode) {
                Log::info('测试模式：准备返回验证码');
                // 将验证码保存到缓存，有效期5分钟
                Cache::set(self::CACHE_PREFIX . $phone, $code, self::CODE_EXPIRE_TIME);
                // 记录发送时间
                $this->recordSendTime($phone);
                $result = [
                    'code' => 200,
                    'message' => '测试模式：验证码发送成功',
                    'data' => [
                        'code' => $code  // 在测试模式下返回验证码
                    ]
                ];
                Log::info('测试模式：返回结果：' . json_encode($result, JSON_UNESCAPED_UNICODE));
                return $result;
            }
            
            // 发送短信
            Log::info('非测试模式：准备发送实际短信');
            $result = $this->sendByDriver($driver, $phone, $code);
            
            if ($result['success']) {
                // 将验证码保存到缓存，有效期5分钟
                Cache::set(self::CACHE_PREFIX . $phone, $code, self::CODE_EXPIRE_TIME);
                // 记录发送时间
                $this->recordSendTime($phone);
                $result = [
                    'code' => 200,
                    'message' => '验证码发送成功'
                ];
                Log::info('非测试模式：返回结果：' . json_encode($result, JSON_UNESCAPED_UNICODE));
                return $result;
            }
            
            throw new \Exception($result['message'] ?? '验证码发送失败');
        } catch (\Exception $e) {
            Log::error('短信发送异常：' . $e->getMessage());
            throw new \Exception('短信服务异常：' . $e->getMessage());
        }
    }

    /**
     * 验证验证码
     * @param string $phone 手机号
     * @param string $code 验证码
     * @return bool
     */
    public function verifyCode(string $phone, string $code): bool
    {
        $cachedCode = Cache::get(self::CACHE_PREFIX . $phone);
        if (!$cachedCode) {
            return false;
        }
        
        if ($cachedCode === $code) {
            // 验证成功后删除验证码
            Cache::delete(self::CACHE_PREFIX . $phone);
            return true;
        }
        
        return false;
    }

    /**
     * 检查是否在冷却时间内
     * @param string $phone 手机号
     * @return bool
     */
    private function checkCooling(string $phone): bool
    {
        $lastSendTime = Cache::get(self::SEND_TIME_PREFIX . $phone);
        if (!$lastSendTime) {
            return false;
        }
        return time() - $lastSendTime < self::COOLING_TIME;
    }

    /**
     * 记录发送时间
     * @param string $phone 手机号
     */
    private function recordSendTime(string $phone): void
    {
        Cache::set(self::SEND_TIME_PREFIX . $phone, time(), self::COOLING_TIME);
    }

    /**
     * 根据驱动发送短信
     * @param string $driver 驱动名称
     * @param string $phone 手机号
     * @param string $code 验证码
     * @return array
     */
    protected function sendByDriver(string $driver, string $phone, string $code): array
    {
        switch ($driver) {
            case 'aliyun':
                return $this->sendByAliyun($phone, $code);
            case 'tencent':
                return $this->sendByTencent($phone, $code);
            case 'huawei':
                return $this->sendByHuawei($phone, $code);
            default:
                throw new \Exception('不支持的短信服务商：' . $driver);
        }
    }

    /**
     * 阿里云发送短信
     * @param string $phone 手机号
     * @param string $code 验证码
     * @return array
     */
    protected function sendByAliyun(string $phone, string $code): array
    {
        $config = $this->config['aliyun'];
        
        // 检查配置
        if (empty($config['access_key_id']) || empty($config['access_key_secret'])) {
            throw new \Exception('阿里云短信配置不完整：缺少 access_key_id 或 access_key_secret');
        }

        if (empty($config['sign_name']) || empty($config['template_code'])) {
            throw new \Exception('阿里云短信配置不完整：缺少 sign_name 或 template_code');
        }

        if (empty($config['region_id'])) {
            throw new \Exception('阿里云短信配置不完整：缺少 region_id');
        }

        try {
            // 创建客户端
            AlibabaCloud::accessKeyClient(
                $config['access_key_id'],
                $config['access_key_secret']
            )->regionId($config['region_id'])->asDefaultClient();

            // 准备发送参数
            $params = [
                'PhoneNumbers' => $phone,
                'SignName' => $config['sign_name'],
                'TemplateCode' => $config['template_code'],
                'TemplateParam' => json_encode(['code' => $code])
            ];
            Log::info('阿里云短信发送参数：' . json_encode($params, JSON_UNESCAPED_UNICODE));

            // 发送短信
            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->options([
                    'query' => $params,
                ])
                ->request();

            // 记录响应结果
            Log::info('阿里云短信发送结果：' . json_encode($result, JSON_UNESCAPED_UNICODE));

            if (!isset($result['Code'])) {
                throw new \Exception('阿里云短信响应格式错误：' . json_encode($result, JSON_UNESCAPED_UNICODE));
            }

            if ($result['Code'] === 'OK') {
            return [
                    'success' => true,
                    'message' => '发送成功'
            ];
            }

            throw new \Exception('阿里云短信发送失败：' . ($result['Message'] ?? '未知错误'));
        } catch (\Exception $e) {
            Log::error('阿里云短信发送异常：' . $e->getMessage());
            throw new \Exception('阿里云短信发送失败：' . $e->getMessage());
        }
    }

    /**
     * 腾讯云发送短信
     * @param string $phone 手机号
     * @param string $code 验证码
     * @return array
     */
    protected function sendByTencent(string $phone, string $code): array
    {
        $config = $this->config['tencent'];
        
        // 检查配置
        if (empty($config['secret_id']) || empty($config['secret_key'])) {
            throw new \Exception('腾讯云短信配置不完整：缺少 secret_id 或 secret_key');
        }

        if (empty($config['sdk_app_id']) || empty($config['sign_name']) || empty($config['template_id'])) {
            throw new \Exception('腾讯云短信配置不完整：缺少 sdk_app_id、sign_name 或 template_id');
        }

        // TODO: 实现腾讯云短信发送
            return [
            'success' => false,
            'message' => '腾讯云短信发送功能尚未实现'
        ];
    }

    /**
     * 华为云发送短信
     * @param string $phone 手机号
     * @param string $code 验证码
     * @return array
     */
    protected function sendByHuawei(string $phone, string $code): array
    {
        $config = $this->config['huawei'];
        
        // 检查配置
        if (empty($config['app_key']) || empty($config['app_secret'])) {
            throw new \Exception('华为云短信配置不完整：缺少 app_key 或 app_secret');
        }

        if (empty($config['sender']) || empty($config['template_id'])) {
            throw new \Exception('华为云短信配置不完整：缺少 sender 或 template_id');
        }

        // TODO: 实现华为云短信发送
        return [
            'success' => false,
            'message' => '华为云短信发送功能尚未实现'
        ];
    }
} 