<?php

return [
    // 默认使用的邮件连接配置
    'default'         => env('MAIL.MAIL_DRIVER', 'smtp'),

    // 邮件连接配置
    'connections'     => [
        'smtp' => [
            // 邮件传输协议
            'type'       => 'smtp',
            // 服务器地址
            'host'       => env('MAIL.MAIL_HOST', ''),
            // 服务器端口
            'port'       => env('MAIL.MAIL_PORT', ''),
            // 加密方式
            'encryption' => env('MAIL.MAIL_ENCRYPTION', ''),
            // 用户名
            'username'   => env('MAIL.MAIL_USERNAME', ''),
            // 密码
            'password'   => env('MAIL.MAIL_PASSWORD', ''),
            // 超时时间
            'timeout'    => env('MAIL.MAIL_TIMEOUT', 30),
            // 发件人
            'from'       => [
                'address' => env('MAIL.MAIL_FROM_ADDRESS', ''),
                'name'    => env('MAIL.MAIL_FROM_NAME', ''),
            ],
        ],
    ],
]; 