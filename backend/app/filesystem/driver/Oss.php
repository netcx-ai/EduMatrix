<?php

namespace app\filesystem\driver;

use think\filesystem\Driver;
use filesystem\OssAdapter;
use app\model\SystemConfig;

class Oss extends Driver
{
    protected function createAdapter(): \League\Flysystem\AdapterInterface
    {
        // 优先从数据库读取配置
        $dbConfig = SystemConfig::getDriverConfig('storage', 'oss');
        if ($dbConfig && $dbConfig->config) {
            // 检查 config 是字符串还是数组
            $config = is_string($dbConfig->config) ? json_decode($dbConfig->config, true) : $dbConfig->config;
            return new OssAdapter([
                'accessKeyId' => $config['access_id'] ?? $this->config['accessKeyId'],
                'accessKeySecret' => $config['access_secret'] ?? $this->config['accessKeySecret'],
                'bucket' => $config['bucket'] ?? $this->config['bucket'],
                'endpoint' => $config['endpoint'] ?? $this->config['endpoint'],
                'url' => $config['url'] ?? $this->config['url'] ?? '',
            ]);
        }
        
        // 回退到文件配置
        return new OssAdapter([
            'accessKeyId' => $this->config['accessKeyId'],
            'accessKeySecret' => $this->config['accessKeySecret'],
            'bucket' => $this->config['bucket'],
            'endpoint' => $this->config['endpoint'],
            'url' => $this->config['url'] ?? '',
        ]);
    }
} 