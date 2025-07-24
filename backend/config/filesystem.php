<?php

return [
    // 默认磁盘
    'default' => env('filesystem.driver', 'local'),
    // 磁盘列表
    'disks'   => [
        'local'  => [
            'type' => 'local',
            'root' => app()->getRuntimePath() . 'storage',
        ],
        'public' => [
            // 磁盘类型
            'type'       => 'local',
            // 磁盘路径
            'root'       => app()->getRootPath() . 'public/uploads',
            // 磁盘路径对应的外部URL路径
            'url'        => '/uploads',
            // 可见性
            'visibility' => 'public',
        ],
        // 阿里云 OSS（从.env文件读取配置）
        'oss' => [
            'type'       => \app\filesystem\driver\Oss::class,
            'accessKeyId' => env('oss.access_id', ''),
            'accessKeySecret' => env('oss.access_secret', ''),
            'bucket'     => env('oss.bucket', ''),
            'endpoint'   => env('oss.endpoint', ''),
            'url'        => env('oss.url', ''),
        ],
        // 腾讯云 COS（从.env文件读取配置）
        'cos' => [
            'type'       => \app\filesystem\driver\Cos::class,
            'secretId'   => env('cos.secret_id', env('COS_SECRET_ID', '')),
            'secretKey'  => env('cos.secret_key', env('COS_SECRET_KEY', '')),
            'bucket'     => env('cos.bucket', env('COS_BUCKET', '')),
            'region'     => env('cos.region', env('COS_REGION', '')),
            'url'        => env('cos.url', env('COS_URL', '')),
            // 方案：本地开发若无正确 CA，使用 http 或者设 verify=false
            'scheme'     => env('cos.scheme', env('COS_SCHEME', 'http')),
            'verify'     => env('cos.verify', env('COS_VERIFY', false)),
        ],
    ],
];
