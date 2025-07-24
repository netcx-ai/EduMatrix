# 系统配置架构说明

## 📋 概述

系统配置分为两个独立的部分，避免功能重复：

1. **系统设置 (SystemSetting)** - 管理平台基础配置
2. **系统配置 (SystemConfig)** - 管理第三方服务接口

## 🎯 系统设置 (SystemSetting)

### 功能描述
管理平台的基础配置信息，包括平台名称、Logo、联系方式、安全设置等。

### 配置分组
- **基础设置**: 平台名称、Logo、版权、ICP备案等
- **联系方式**: 邮箱、电话、QQ、微信、地址等
- **邮件设置**: SMTP服务器配置
- **安全设置**: 登录限制、密码策略、会话超时等
- **上传设置**: 文件大小限制、类型限制、水印等
- **支付设置**: 货币、支付方式、退款期限等
- **缓存设置**: 缓存驱动、前缀、过期时间等
- **通知设置**: 邮件、短信、推送通知开关等

### 访问路径
- 管理页面: `/admin/system_setting/index`
- 添加配置: `/admin/system_setting/add`
- 编辑配置: `/admin/system_setting/edit`

### 使用示例
```php
use app\helper\SystemHelper;

// 获取平台名称
$siteName = SystemHelper::getSiteName();

// 获取SMTP配置
$smtpConfig = SystemHelper::getSmtpConfig();

// 获取安全配置
$securityConfig = SystemHelper::getSecurityConfig();
```

## 🔧 系统配置 (SystemConfig)

### 功能描述
管理各种第三方服务的连接配置，支持多种服务商和配置类型。

### 配置类型
- **短信配置**: 阿里云、腾讯云等短信服务
- **支付配置**: 支付宝、微信支付等
- **邮箱配置**: 各种邮箱服务商
- **对象存储**: 阿里云OSS、腾讯云COS等
- **单点登录**: OAuth、SAML等SSO配置
- **缓存配置**: Redis、Memcache等
- **队列配置**: RabbitMQ、Redis队列等
- **日志配置**: 日志存储、监控等
- **监控配置**: 系统监控、告警等
- **安全配置**: 防火墙、WAF等
- **第三方服务**: 地图、翻译、AI等

### 访问路径
- 管理页面: `/admin/system_config/index`
- 添加配置: `/admin/system_config/add`
- 编辑配置: `/admin/system_config/edit`

### 功能特性
- ✅ 支持多配置切换
- ✅ 默认配置设置
- ✅ 连接测试功能
- ✅ 配置状态管理
- ✅ JSON格式配置参数

## 🗄️ 数据库结构

### 系统设置表 (edu_system_settings)
```sql
- id: 主键
- group: 配置分组
- key: 配置键名
- value: 配置值
- type: 配置类型 (text/textarea/image/select/switch/number)
- title: 配置标题
- description: 配置描述
- options: 选项配置 (JSON)
- sort: 排序
- status: 状态
- create_time: 创建时间
- update_time: 更新时间
```

### 系统配置表 (edu_system_configs)
```sql
- id: 主键
- type: 配置类型
- name: 配置名称
- driver: 服务商
- config: 配置参数 (JSON)
- status: 状态
- is_default: 是否默认
- remark: 备注
- create_time: 创建时间
- update_time: 更新时间
```

## 🧹 清理说明

### 已删除的重复功能
- ❌ ApiConfig 控制器
- ❌ api_config 视图目录
- ❌ api_config 路由组
- ❌ 导航菜单中的"接口管理"链接

### 保留的功能
- ✅ SystemConfig 控制器 (功能完整)
- ✅ system_config 视图
- ✅ system_config 路由组
- ✅ 导航菜单中的"系统配置"链接

## 🎨 界面导航

### 左侧导航菜单
```
系统管理
├── 系统设置 (平台基础配置)
├── 系统配置 (第三方服务接口)
└── 管理员 (账户管理)
```

### 快捷操作
- 系统设置
- 系统配置
- 管理员管理

## 📝 使用建议

1. **系统设置**: 用于配置平台的基础信息，如Logo、联系方式等
2. **系统配置**: 用于配置第三方服务，如短信、支付、存储等
3. 两个功能分工明确，避免重复，便于维护

## 🔄 更新历史

- 2024-03-23: 创建系统设置功能
- 2024-03-23: 删除重复的接口管理功能
- 2024-03-23: 完善导航菜单和路由配置 