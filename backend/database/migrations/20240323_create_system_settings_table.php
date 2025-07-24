<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateSystemSettingsTable extends Migrator
{
    public function change()
    {
        $table = $this->table('edu_system_settings', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table
            ->addColumn('group', 'string', ['limit' => 50, 'null' => false, 'comment' => '配置分组：basic, contact, seo, payment, etc'])
            ->addColumn('key', 'string', ['limit' => 100, 'null' => false, 'comment' => '配置键名'])
            ->addColumn('value', 'text', ['null' => true, 'comment' => '配置值'])
            ->addColumn('type', 'string', ['limit' => 20, 'default' => 'text', 'comment' => '配置类型：text, textarea, image, select, switch, number'])
            ->addColumn('title', 'string', ['limit' => 100, 'null' => false, 'comment' => '配置标题'])
            ->addColumn('description', 'string', ['limit' => 255, 'null' => true, 'comment' => '配置描述'])
            ->addColumn('options', 'json', ['null' => true, 'comment' => '选项配置（用于select类型）'])
            ->addColumn('sort', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '排序'])
            ->addColumn('status', 'boolean', ['signed' => false, 'default' => 1, 'comment' => '状态：0禁用，1启用'])
            ->addColumn('create_time', 'integer', ['signed' => false, 'null' => false, 'default' => 0, 'comment' => '创建时间'])
            ->addColumn('update_time', 'integer', ['signed' => false, 'null' => false, 'default' => 0, 'comment' => '更新时间'])
            ->addIndex(['group', 'key'], ['unique' => true])
            ->addIndex(['group'])
            ->addIndex(['status'])
            ->create();

        // 插入基础配置数据
        $this->execute("INSERT INTO edu_system_settings (group, `key`, value, type, title, description, sort, status, create_time, update_time) VALUES 
            ('basic', 'site_name', 'EduMatrix教育平台', 'text', '平台名称', '网站/平台显示名称', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('basic', 'site_title', 'EduMatrix - 智能教育管理系统', 'text', '网站标题', '浏览器标签页显示的标题', 2, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('basic', 'site_keywords', '教育,在线学习,课程管理,学生管理', 'textarea', '网站关键词', 'SEO关键词，多个用逗号分隔', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('basic', 'site_description', 'EduMatrix是一个现代化的教育管理平台，提供课程管理、学生管理、在线学习等功能', 'textarea', '网站描述', 'SEO描述信息', 4, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('basic', 'site_logo', '', 'image', '平台Logo', '平台Logo图片', 5, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('basic', 'site_favicon', '', 'image', '网站图标', '浏览器标签页显示的图标', 6, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('basic', 'site_url', 'https://edumatrix.com', 'text', '网站地址', '网站完整访问地址', 7, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('basic', 'site_version', '1.0.0', 'text', '系统版本', '当前系统版本号', 8, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('basic', 'site_icp', '', 'text', 'ICP备案号', '网站ICP备案信息', 9, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('basic', 'site_copyright', 'Copyright © 2024 EduMatrix. All rights reserved.', 'text', '版权信息', '网站版权声明', 10, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('basic', 'site_timezone', 'Asia/Shanghai', 'select', '时区设置', '系统时区配置', 11, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('basic', 'site_language', 'zh-cn', 'select', '默认语言', '系统默认语言', 12, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('basic', 'maintenance_mode', '0', 'switch', '维护模式', '开启后网站将显示维护页面', 13, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('basic', 'maintenance_message', '系统维护中，请稍后再试...', 'textarea', '维护信息', '维护模式下显示的信息', 14, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
        ");

        // 插入联系方式配置
        $this->execute("INSERT INTO edu_system_settings (group, `key`, value, type, title, description, sort, status, create_time, update_time) VALUES 
            ('contact', 'contact_email', 'support@edumatrix.com', 'text', '联系邮箱', '客服联系邮箱', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('contact', 'contact_phone', '400-123-4567', 'text', '联系电话', '客服联系电话', 2, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('contact', 'contact_qq', '123456789', 'text', 'QQ客服', 'QQ客服号码', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('contact', 'contact_wechat', 'edumatrix_support', 'text', '微信客服', '微信客服账号', 4, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('contact', 'contact_address', '北京市朝阳区xxx街道xxx号', 'textarea', '联系地址', '公司联系地址', 5, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('contact', 'business_hours', '周一至周五 9:00-18:00', 'text', '营业时间', '客服营业时间', 6, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
        ");

        // 插入支付配置
        $this->execute("INSERT INTO edu_system_settings (group, `key`, value, type, title, description, sort, status, create_time, update_time) VALUES 
            ('payment', 'currency', 'CNY', 'select', '默认货币', '系统默认货币单位', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('payment', 'payment_methods', 'alipay,wechat,bank', 'select', '支付方式', '支持的支付方式', 2, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('payment', 'auto_confirm_days', '7', 'number', '自动确认天数', '订单自动确认收货天数', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('payment', 'refund_days', '15', 'number', '退款期限', '订单退款期限天数', 4, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
        ");

        // 插入邮件配置
        $this->execute("INSERT INTO edu_system_settings (group, `key`, value, type, title, description, sort, status, create_time, update_time) VALUES 
            ('email', 'smtp_host', 'smtp.163.com', 'text', 'SMTP服务器', '邮件服务器地址', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('email', 'smtp_port', '465', 'number', 'SMTP端口', '邮件服务器端口', 2, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('email', 'smtp_username', '13878125908@163.com', 'text', '邮箱账号', '发件邮箱账号', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('email', 'smtp_password', 'KIYDCJXXGMJXEQNH', 'text', '邮箱密码', '发件邮箱密码或授权码', 4, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('email', 'smtp_encryption', 'ssl', 'select', '加密方式', 'SMTP加密方式', 5, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('email', 'from_address', '13878125908@163.com', 'text', '发件人邮箱', '发件人邮箱地址', 6, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('email', 'from_name', 'EduMatrix', 'text', '发件人名称', '发件人显示名称', 7, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
        ");

        // 插入安全配置
        $this->execute("INSERT INTO edu_system_settings (group, `key`, value, type, title, description, sort, status, create_time, update_time) VALUES 
            ('security', 'login_attempts', '5', 'number', '登录尝试次数', '允许的最大登录失败次数', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('security', 'lockout_time', '15', 'number', '锁定时间', '账号锁定时间（分钟）', 2, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('security', 'password_min_length', '6', 'number', '密码最小长度', '用户密码最小长度要求', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('security', 'password_complexity', '1', 'switch', '密码复杂度', '是否要求密码包含字母和数字', 4, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('security', 'session_timeout', '7200', 'number', '会话超时', '用户会话超时时间（秒）', 5, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('security', 'enable_captcha', '1', 'switch', '启用验证码', '是否启用登录验证码', 6, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
        ");

        // 插入上传配置
        $this->execute("INSERT INTO edu_system_settings (group, `key`, value, type, title, description, sort, status, create_time, update_time) VALUES 
            ('upload', 'max_file_size', '10485760', 'number', '最大文件大小', '允许上传的最大文件大小（字节）', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('upload', 'allowed_extensions', 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,zip,rar', 'textarea', '允许的文件类型', '允许上传的文件扩展名', 2, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('upload', 'image_quality', '80', 'number', '图片质量', '图片压缩质量（1-100）', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('upload', 'watermark_enable', '0', 'switch', '启用水印', '是否在图片上添加水印', 4, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('upload', 'watermark_text', 'EduMatrix', 'text', '水印文字', '水印显示的文字', 5, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
        ");

        // 插入缓存配置
        $this->execute("INSERT INTO edu_system_settings (group, `key`, value, type, title, description, sort, status, create_time, update_time) VALUES 
            ('cache', 'cache_driver', 'file', 'select', '缓存驱动', '缓存存储方式', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('cache', 'cache_prefix', 'edumatrix_', 'text', '缓存前缀', '缓存键名前缀', 2, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('cache', 'cache_ttl', '3600', 'number', '缓存时间', '默认缓存时间（秒）', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
        ");

        // 插入通知配置
        $this->execute("INSERT INTO edu_system_settings (group, `key`, value, type, title, description, sort, status, create_time, update_time) VALUES 
            ('notification', 'email_notification', '1', 'switch', '邮件通知', '是否启用邮件通知', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('notification', 'sms_notification', '0', 'switch', '短信通知', '是否启用短信通知', 2, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('notification', 'push_notification', '0', 'switch', '推送通知', '是否启用推送通知', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
            ('notification', 'notification_sound', '1', 'switch', '通知声音', '是否启用通知声音', 4, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
        ");
    }
} 