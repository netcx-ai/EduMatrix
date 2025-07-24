<?php
declare (strict_types = 1);

namespace app\helper;

use app\model\SystemSetting;

/**
 * 系统设置助手类
 * 提供便捷的系统配置获取方法
 */
class SystemHelper
{
    /**
     * 获取平台名称
     */
    public static function getSiteName()
    {
        return SystemSetting::getSettingValue('site_name', 'EduMatrix教育平台');
    }

    /**
     * 获取网站标题
     */
    public static function getSiteTitle()
    {
        return SystemSetting::getSettingValue('site_title', 'EduMatrix - 智能教育管理系统');
    }

    /**
     * 获取网站Logo
     */
    public static function getSiteLogo()
    {
        return SystemSetting::getSettingValue('site_logo', '');
    }

    /**
     * 获取网站图标
     */
    public static function getSiteFavicon()
    {
        return SystemSetting::getSettingValue('site_favicon', '');
    }

    /**
     * 获取网站地址
     */
    public static function getSiteUrl()
    {
        return SystemSetting::getSettingValue('site_url', 'https://edumatrix.com');
    }

    /**
     * 获取版权信息
     */
    public static function getCopyright()
    {
        return SystemSetting::getSettingValue('site_copyright', 'Copyright © 2024 EduMatrix. All rights reserved.');
    }

    /**
     * 获取ICP备案号
     */
    public static function getIcp()
    {
        return SystemSetting::getSettingValue('site_icp', '');
    }

    /**
     * 获取联系邮箱
     */
    public static function getContactEmail()
    {
        return SystemSetting::getSettingValue('contact_email', 'support@edumatrix.com');
    }

    /**
     * 获取联系电话
     */
    public static function getContactPhone()
    {
        return SystemSetting::getSettingValue('contact_phone', '400-123-4567');
    }

    /**
     * 获取QQ客服
     */
    public static function getContactQQ()
    {
        return SystemSetting::getSettingValue('contact_qq', '');
    }

    /**
     * 获取微信客服
     */
    public static function getContactWechat()
    {
        return SystemSetting::getSettingValue('contact_wechat', '');
    }

    /**
     * 获取联系地址
     */
    public static function getContactAddress()
    {
        return SystemSetting::getSettingValue('contact_address', '');
    }

    /**
     * 获取营业时间
     */
    public static function getBusinessHours()
    {
        return SystemSetting::getSettingValue('business_hours', '');
    }

    /**
     * 获取SMTP配置
     */
    public static function getSmtpConfig()
    {
        return [
            'host' => SystemSetting::getSettingValue('smtp_host', 'smtp.163.com'),
            'port' => SystemSetting::getSettingValue('smtp_port', '465'),
            'username' => SystemSetting::getSettingValue('smtp_username', ''),
            'password' => SystemSetting::getSettingValue('smtp_password', ''),
            'encryption' => SystemSetting::getSettingValue('smtp_encryption', 'ssl'),
            'from_address' => SystemSetting::getSettingValue('from_address', ''),
            'from_name' => SystemSetting::getSettingValue('from_name', 'EduMatrix')
        ];
    }

    /**
     * 获取安全配置
     */
    public static function getSecurityConfig()
    {
        return [
            'login_attempts' => (int)SystemSetting::getSettingValue('login_attempts', 5),
            'lockout_time' => (int)SystemSetting::getSettingValue('lockout_time', 15),
            'password_min_length' => (int)SystemSetting::getSettingValue('password_min_length', 6),
            'password_complexity' => (bool)SystemSetting::getSettingValue('password_complexity', true),
            'session_timeout' => (int)SystemSetting::getSettingValue('session_timeout', 7200),
            'enable_captcha' => (bool)SystemSetting::getSettingValue('enable_captcha', true)
        ];
    }

    /**
     * 获取上传配置
     */
    public static function getUploadConfig()
    {
        return [
            'max_file_size' => (int)SystemSetting::getSettingValue('max_file_size', 10485760),
            'allowed_extensions' => SystemSetting::getSettingValue('allowed_extensions', 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,zip,rar'),
            'image_quality' => (int)SystemSetting::getSettingValue('image_quality', 80),
            'watermark_enable' => (bool)SystemSetting::getSettingValue('watermark_enable', false),
            'watermark_text' => SystemSetting::getSettingValue('watermark_text', 'EduMatrix'),
            'storage_driver' => SystemSetting::getSettingValue('storage_driver', 'local'),
        ];
    }

    /**
     * 获取当前存储驱动
     */
    public static function getStorageDriver(): string
    {
        $uploadConfig = self::getUploadConfig();
        return $uploadConfig['storage_driver'] ?? 'local';
    }

    /**
     * 检查是否启用维护模式
     */
    public static function isMaintenanceMode()
    {
        return (bool)SystemSetting::getSettingValue('maintenance_mode', false);
    }

    /**
     * 获取维护信息
     */
    public static function getMaintenanceMessage()
    {
        return SystemSetting::getSettingValue('maintenance_message', '系统维护中，请稍后再试...');
    }

    /**
     * 获取系统版本
     */
    public static function getVersion()
    {
        return SystemSetting::getSettingValue('site_version', '1.0.0');
    }

    /**
     * 获取时区设置
     */
    public static function getTimezone()
    {
        return SystemSetting::getSettingValue('site_timezone', 'Asia/Shanghai');
    }

    /**
     * 获取默认语言
     */
    public static function getLanguage()
    {
        return SystemSetting::getSettingValue('site_language', 'zh-cn');
    }

    /**
     * 获取SEO信息
     */
    public static function getSeoInfo()
    {
        return [
            'keywords' => SystemSetting::getSettingValue('site_keywords', ''),
            'description' => SystemSetting::getSettingValue('site_description', '')
        ];
    }

    /**
     * 获取所有基础配置
     */
    public static function getBasicConfig()
    {
        return [
            'site_name' => self::getSiteName(),
            'site_title' => self::getSiteTitle(),
            'site_logo' => self::getSiteLogo(),
            'site_favicon' => self::getSiteFavicon(),
            'site_url' => self::getSiteUrl(),
            'copyright' => self::getCopyright(),
            'icp' => self::getIcp(),
            'version' => self::getVersion(),
            'timezone' => self::getTimezone(),
            'language' => self::getLanguage(),
            'maintenance_mode' => self::isMaintenanceMode(),
            'maintenance_message' => self::getMaintenanceMessage()
        ];
    }

    /**
     * 获取所有联系方式
     */
    public static function getContactInfo()
    {
        return [
            'email' => self::getContactEmail(),
            'phone' => self::getContactPhone(),
            'qq' => self::getContactQQ(),
            'wechat' => self::getContactWechat(),
            'address' => self::getContactAddress(),
            'business_hours' => self::getBusinessHours()
        ];
    }
} 