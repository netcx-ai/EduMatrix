# EduMatrix 项目规则

## 重要开发规范

### 1. PowerShell 兼容性规则 ⚠️ 重要

**问题：** PowerShell 不支持 Unix/Linux 风格的 `&&` 和 `||` 语法

**错误示例：**
```bash
# ❌ 这在 PowerShell 中会报错
cd backend && php run_migration_and_seed.php
```

**正确做法：**

#### 方案1：使用分号分隔
```powershell
# ✅ PowerShell 兼容
cd backend; php run_migration_and_seed.php
```

#### 方案2：分别执行命令
```powershell
# ✅ 最安全的方式
cd backend
php run_migration_and_seed.php
```

#### 方案3：使用条件执行
```powershell
# ✅ 带错误检查
cd backend; if ($?) { php run_migration_and_seed.php }
```

**规则：** 在 Windows 环境下，始终使用 PowerShell 兼容的语法，避免使用 `&&` 和 `||` 连接符。

### 2. 迁移文件时间戳冲突规避 ⚠️ 重要

**问题：** 迁移文件时间戳重复导致 "Duplicate migration" 错误

**错误示例：**
```
[InvalidArgumentException]
Duplicate migration - "20250110_fix_school_admin_user_relation.php" has the same version as "20250110"
```

**解决方案：**

#### 方案1：使用精确到秒的时间戳
```bash
# ✅ 推荐格式：YYYYMMDD_HHMMSS_描述.php
20250715_094824_fix_school_admin_user_relation.php
```

#### 方案2：检查现有迁移文件
```bash
# 新建迁移前先检查
ls database/migrations | grep "20250110"
```

#### 方案3：自动化脚本生成
```powershell
# 自动生成唯一时间戳
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$filename = "${timestamp}_migration_name.php"
```

#### 方案4：类名与文件名对应规则
- 文件名：`20250715_094824_fix_school_admin_user_relation.php`
- 类名：`FixSchoolAdminUserRelation` （去掉时间戳部分）
- **重要：** 类名不能包含时间戳后缀

**规则：** 
1. 迁移文件时间戳必须唯一
2. 使用精确到秒的时间戳格式
3. 类名与文件名（去掉时间戳）严格对应
4. 新建迁移前检查现有文件

### 3. 数据库操作规范

#### 迁移文件命名
- 使用时间戳前缀：`YYYYMMDD_HHMMSS_描述.php`
- 示例：`20250715_094824_fix_school_admin_user_relation.php`

#### 种子数据规范
- 测试数据应该包含完整的关联关系
- 确保外键约束正确
- 使用事务确保数据一致性
- **避免复杂的数组索引操作**
- 使用时间戳生成唯一标识符

#### 字段存在性检查
```php
// ✅ 推荐：检查字段是否存在
if (!$table->hasColumn('field_name')) {
    $table->addColumn('field_name', 'string', [...]);
}

// ❌ 避免：直接添加字段
$table->addColumn('field_name', 'string', [...]);
```

### 4. 用户系统设计规范

#### 统一身份认证
- 所有用户类型（教师、学生、管理员）都关联到 `edu_user` 表
- 用户表存储基础信息：用户名、密码、邮箱、用户类型等
- 具体角色表存储特有信息并关联用户表

#### 关联关系
```sql
edu_user (用户基础信息)
├── edu_teacher (教师信息) - user_id
├── edu_student (学生信息) - user_id  
├── edu_school_admin (学校管理员) - user_id
└── edu_system_admin (系统管理员) - user_id
```

### 5. 测试数据规范

#### 数据完整性
- 确保所有外键关联正确
- 测试数据应该覆盖各种场景
- 包含边界条件和异常情况

#### 数据量控制
- 开发环境：适量数据便于调试
- 测试环境：充足数据测试性能
- 生产环境：真实数据

#### 唯一性保证
```php
// ✅ 使用时间戳生成唯一标识
$timestamp = time();
$username = 'teacher' . $timestamp;
$email = 'teacher' . $timestamp . '@example.com';
```

### 6. 文件组织规范

#### 目录结构
```
backend/
├── app/
│   ├── model/          # 模型文件
│   ├── controller/     # 控制器
│   └── middleware/     # 中间件
├── database/
│   ├── migrations/     # 数据库迁移
│   └── seeds/         # 种子数据
├── config/            # 配置文件
└── route/             # 路由文件
```

#### 文档规范
- 重要更改需要更新相关文档
- 创建升级说明文档
- 记录数据库结构变更

### 7. 错误处理规范

#### 数据库操作
- 使用事务确保数据一致性
- 捕获并记录异常
- 提供回滚机制

#### 用户反馈
- 提供清晰的错误信息
- 记录操作日志
- 支持操作撤销

### 8. 安全规范

#### 数据验证
- 前端和后端双重验证
- 防止 SQL 注入
- 防止 XSS 攻击

#### 权限控制
- 基于角色的访问控制
- 最小权限原则
- 定期权限审计

## 执行命令时的注意事项

### Windows 环境
1. **避免使用 `&&` 语法**
2. **使用 PowerShell 兼容的命令**
3. **检查命令执行结果**

### 跨平台兼容
1. **创建多个版本的脚本**
2. **使用高级语言编写跨平台脚本**
3. **测试所有目标平台**

## 常见问题解决

### PowerShell 语法错误
- 问题：`&&` 不是有效语句分隔符
- 解决：使用 `;` 或分别执行命令

### 迁移文件时间戳冲突
- 问题：`Duplicate migration` 错误
- 解决：使用精确到秒的时间戳，检查现有文件

### 数据库连接问题
- 检查配置文件
- 确认数据库服务运行
- 验证连接参数

### 权限问题
- 检查文件权限
- 确认用户权限
- 验证数据库权限

### 种子数据错误
- 问题：`Undefined array key` 或字段重复
- 解决：使用简单逻辑，避免复杂数组操作，添加字段存在性检查

## 项目特定规则

### EduMatrix 教育管理系统
1. **多租户架构**：支持多所学校独立管理
2. **角色分离**：教师、学生、管理员权限分离
3. **数据隔离**：学校间数据完全隔离
4. **扩展性**：支持功能模块扩展

### 开发流程
1. **需求分析** → **设计** → **开发** → **测试** → **部署**
2. **代码审查**：重要更改需要审查
3. **测试覆盖**：核心功能必须有测试
4. **文档更新**：及时更新相关文档

### 测试数据管理
1. **简单优先**：优先使用简单的测试数据脚本
2. **唯一性保证**：使用时间戳等确保数据唯一性
3. **事务保护**：所有数据操作使用事务
4. **错误处理**：完善的异常捕获和回滚机制

---

**重要提醒：** 这些规则是项目开发的基础，必须严格遵守。特别是 PowerShell 兼容性规则和迁移文件时间戳规则，在 Windows 环境下开发时必须时刻注意。 