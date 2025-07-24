<?php

namespace app\filesystem\driver;

use think\filesystem\Driver;
use filesystem\CosAdapter;
use app\model\SystemConfig;

class Cos extends Driver
{
    protected function createAdapter(): \League\Flysystem\AdapterInterface
    {
        // 优先从数据库读取配置
        $dbConfig = SystemConfig::getDriverConfig('storage', 'cos');
        // 兼容旧数据：如果未找到 driver=cos，再尝试 driver=tencent
        if (!$dbConfig) {
            $dbConfig = SystemConfig::getDriverConfig('storage', 'tencent');
        }
        if ($dbConfig && $dbConfig->config) {
            // 检查 config 是字符串还是数组
            $config = is_string($dbConfig->config) ? json_decode($dbConfig->config, true) : $dbConfig->config;
            return new CosAdapter([
                'secretId' => $config['secret_id'] ?? $this->config['secretId'],
                'secretKey' => $config['secret_key'] ?? $this->config['secretKey'],
                'bucket' => $config['bucket'] ?? $this->config['bucket'],
                'region' => $config['region'] ?? $this->config['region'],
                'url' => $config['url'] ?? $this->config['url'] ?? '',
                'scheme' => $config['scheme'] ?? $this->config['scheme'] ?? 'https',
                'verify' => $config['verify'] ?? $this->config['verify'] ?? false,
            ]);
        }
        
        // 回退到文件配置
        return new CosAdapter([
            'secretId' => $this->config['secretId'],
            'secretKey' => $this->config['secretKey'],
            'bucket' => $this->config['bucket'],
            'region' => $this->config['region'],
            'url' => $this->config['url'] ?? '',
            'scheme' => $this->config['scheme'] ?? 'https',
            'verify' => $this->config['verify'] ?? false,
        ]);
    }
} 