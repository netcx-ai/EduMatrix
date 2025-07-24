# edu_admin 表结构说明

## 表基本信息

| 属性 | 值 |
|------|-----|
| 表名 | `edu_admin` |
| 说明 | 系统管理员表 |
| 引擎 | InnoDB |
| 字符集 | utf8mb4_unicode_ci |

## 表用途说明

系统管理员表，用于存储后台管理系统的管理员账号信息，包括登录认证、权限管理、操作日志等功能。管理员可以管理整个系统的各种资源和配置。

## 字段结构

| 字段名 | 类型 | 是否为空 | 默认值 | 键类型 | 额外属性 | 说明 |
|--------|------|----------|--------|--------|----------|------|
| id | int(11) unsigned | 否 | NULL | 主键 | AUTO_INCREMENT | 主键ID |
| username | varchar(50) | 否 | NULL | 唯一键 | - | 用户名 |
| password | varchar(255) | 否 | NULL | - | - | 密码（加密） |
| real_name | varchar(50) | 是 | NULL | - | - | 真实姓名 |
| phone | varchar(20) | 是 | NULL | 唯一键 | - | 手机号 |
| email | varchar(100) | 是 | NULL | 唯一键 | - | 邮箱 |
| avatar | varchar(255) | 是 | NULL | - | - | 头像路径 |
| role | int(1) | 是 | 1 | - | - | 角色类型 |
| status | int(1) | 是 | 1 | - | - | 状态 |
| last_login_time | datetime | 是 | NULL | - | - | 最后登录时间 |
| last_login_ip | varchar(50) | 是 | NULL | - | - | 最后登录IP |
| create_time | datetime | 否 | NULL | - | - | 创建时间 |
| update_time | datetime | 否 | NULL | - | - | 更新时间 |

## 详细字段说明

### id
- **类型**: int(11) unsigned
- **是否为空**: 否
- **默认值**: NULL
- **键类型**: 主键
- **额外属性**: AUTO_INCREMENT
- **说明**: 主键ID，自动递增

### username
- **类型**: varchar(50)
- **是否为空**: 否
- **默认值**: NULL
- **键类型**: 唯一键
- **说明**: 管理员用户名，用于登录认证，全局唯一

### password
- **类型**: varchar(255)
- **是否为空**: 否
- **默认值**: NULL
- **说明**: 管理员密码，使用password_hash()函数加密存储

### real_name
- **类型**: varchar(50)
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 管理员真实姓名，用于显示和识别

### phone
- **类型**: varchar(20)
- **是否为空**: 是
- **默认值**: NULL
- **键类型**: 唯一键
- **说明**: 手机号码，用于联系和身份验证

### email
- **类型**: varchar(100)
- **是否为空**: 是
- **默认值**: NULL
- **键类型**: 唯一键
- **说明**: 邮箱地址，用于通知和密码重置

### avatar
- **类型**: varchar(255)
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 头像文件路径，用于个人资料显示

### role
- **类型**: int(1)
- **是否为空**: 是
- **默认值**: 1
- **说明**: 角色类型：1=超级管理员，2=普通管理员

### status
- **类型**: int(1)
- **是否为空**: 是
- **默认值**: 1
- **说明**: 状态：0=禁用，1=启用

### last_login_time
- **类型**: datetime
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 最后登录时间，用于统计和安全监控

### last_login_ip
- **类型**: varchar(50)
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 最后登录IP地址，用于安全监控

### create_time
- **类型**: datetime
- **是否为空**: 否
- **默认值**: NULL
- **说明**: 记录创建时间，系统自动生成

### update_time
- **类型**: datetime
- **是否为空**: 否
- **默认值**: NULL
- **说明**: 记录更新时间，系统自动维护

## 索引信息

### PRIMARY
- **字段**: id
- **类型**: BTREE
- **唯一**: 是

### username
- **字段**: username
- **类型**: BTREE
- **唯一**: 是

### phone
- **字段**: phone
- **类型**: BTREE
- **唯一**: 是

### email
- **字段**: email
- **类型**: BTREE
- **唯一**: 是

## 使用建议

1. **状态管理**: 该表包含status字段，建议使用软删除而非物理删除
2. **时间管理**: 系统自动维护创建时间和更新时间，无需手动设置
3. **安全性**: 密码字段使用加密存储，不要直接存储明文密码
4. **唯一性**: username、phone、email字段具有唯一性约束
5. **性能优化**: 查询时尽量使用索引字段作为查询条件
6. **数据备份**: 重要数据表建议定期备份

## 相关文件

**模型文件**: `app/model/Admin.php`
**控制器文件**: `app/controller/admin/admin.php`
**API控制器**: `app/controller/api/admin.php`
**视图文件**: `view/admin/admin/` 