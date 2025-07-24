<?php
declare (strict_types = 1);

namespace app\provider;

use app\model\SmsConfig;
use think\Service;
use think\facade\Config;
use think\facade\Log;

class SmsConfigProvider extends Service
{
    public function register()
    {
        // 注册短信配置
        $this->app->bind('sms_config', function () {
            // 获取短信配置
            $smsConfig = SmsConfig::where('status', 1)->find();
            if (!$smsConfig) {
                throw new \Exception('短信配置不存在或未启用');
            }

            // 解析配置
            $config = $smsConfig->config;
            
            return [
                'driver' => $smsConfig->driver,
                'config' => $config
            ];
        });
    }

    public function boot()
    {
        try {
            Log::info('开始加载短信配置...');
            
            // 从数据库获取配置
            $smsConfig = SmsConfig::where('status', 1)->find();
                
            if (!$smsConfig) {
                Log::warning('未找到短信配置');
                return;
            }
            
            Log::info('从数据库获取到的配置：' . json_encode($smsConfig->toArray(), JSON_UNESCAPED_UNICODE));
            
            // 解析配置
            $config = $smsConfig->config;
            
            // 设置配置
            Config::set([
                'sms' => [
                    'driver' => $smsConfig->driver,
                    $smsConfig->driver => $config
                ]
            ]);
            
            Log::info('当前系统配置：' . json_encode(Config::get('sms'), JSON_UNESCAPED_UNICODE));
            
        } catch (\Exception $e) {
            Log::error('加载短信配置失败：' . $e->getMessage());
        }
    }
} 