# 模型文件表名设置总结

所有模型文件都已使用 `$name` 属性，让 ThinkPHP 自动添加数据库前缀。

## 模型列表

| 模型文件 | 类名 | 表名设置 | 说明 |
|---------|------|----------|------|
| Admin.php | Admin | `protected $name = 'admin';` | 管理员模型 |
| AdminLog.php | AdminLog | `protected $name = 'admin_log';` | 管理员日志模型 |
| Permission.php | Permission | `protected $name = 'permission';` | 权限模型 |
| Role.php | Role | `protected $name = 'role';` | 角色模型 |
| SystemConfig.php | SystemConfig | `protected $name = 'system_config';` | 系统配置模型 |
| SystemSetting.php | SystemSetting | `protected $name = 'system_settings';` | 系统设置模型 |
| User.php | User | `protected $name = 'user';` | 用户模型 |

## 优势

1. **灵活性**：数据库前缀改变时，只需修改配置文件，无需修改模型文件
2. **一致性**：所有模型都使用统一的命名规范
3. **维护性**：代码更清晰，易于维护

## 注意事项

- 所有关联表名也不应该包含前缀，ThinkPHP 会自动添加
- 例如：`admin_role`、`role_permission` 等 