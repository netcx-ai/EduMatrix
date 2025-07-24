# 用户表设计说明

## 📋 用户表用途分析

### 🎯 用户表（edu_user）的定位

用户表设计为**统一身份认证中心**，主要用途：

#### ✅ **保留用户表的原因**
1. **统一登录认证**
   - 用户名/密码管理
   - 登录状态维护
   - 安全机制（密码错误次数、锁定等）

2. **基础信息管理**
   - 个人基本信息（姓名、手机、邮箱）
   - 头像、性别、生日等通用字段
   - 访问统计、登录记录

3. **跨角色支持**
   - 一个用户可以是教师、管理员、普通会员
   - 支持多学校关联
   - 统一的权限管理

4. **会员体系**
   - 会员等级管理
   - 积分系统
   - 到期时间控制

#### 🔄 **用户表与教师表的关系**

```
edu_user (用户表)
├── 基础信息：username, password, real_name, phone, email
├── 会员信息：member_level, points, member_expire_time
├── 安全信息：password_error_count, last_password_change
├── 统计信息：visit_count, last_visit_time
└── 类型标识：user_type, primary_school_id, teacher_no

edu_teacher (教师表)
├── 学校信息：school_id, college_id
├── 关联用户：user_id -> edu_user.id
├── 教师信息：teacher_no, title, department, position
├── 专业信息：education, major, teaching_subject
└── 状态管理：status, is_verified, verified_time
```

### 🏗️ 数据设计策略

#### **方案一：关联模式（推荐）**
```sql
-- 用户表：统一身份认证
edu_user
├── id (主键)
├── username (用户名)
├── password (密码)
├── real_name (真实姓名)
├── phone (手机号)
├── email (邮箱)
├── user_type (用户类型：member/teacher/admin/school_admin)
├── primary_school_id (主要学校ID)
├── teacher_no (教师工号，仅教师用户)
└── ... (其他通用字段)

-- 教师表：教师特有信息
edu_teacher
├── id (主键)
├── user_id (关联用户ID)
├── school_id (学校ID)
├── college_id (学院ID)
├── teacher_no (教师工号)
├── title (职称)
├── department (部门)
└── ... (其他教师特有字段)
```

#### **优势分析**
1. **数据一致性**：用户基础信息统一管理
2. **权限统一**：登录认证、安全机制统一
3. **扩展性强**：支持用户多角色、多学校
4. **维护简单**：减少数据冗余，便于维护

### 📊 使用场景

#### **场景1：教师注册**
```php
// 1. 创建用户记录
$user = User::create([
    'username' => 'teacher001',
    'password' => 'hashed_password',
    'real_name' => '张老师',
    'phone' => '13800138000',
    'user_type' => 'teacher',
    'teacher_no' => 'T2024001'
]);

// 2. 创建教师记录
$teacher = Teacher::create([
    'user_id' => $user->id,
    'school_id' => 1,
    'college_id' => 1,
    'teacher_no' => 'T2024001',
    'title' => '副教授',
    'department' => '计算机学院'
]);
```

#### **场景2：教师登录**
```php
// 通过用户表验证登录
$user = User::where('username', $username)->first();
if ($user && $user->verifyPassword($password)) {
    // 获取教师信息
    $teacher = Teacher::where('user_id', $user->id)->first();
    // 设置登录状态
    $user->updateLoginInfo();
}
```

#### **场景3：跨学校教师**
```php
// 一个用户可以在多个学校任教
$user = User::find(1);
$teachers = Teacher::where('user_id', $user->id)->get();
// 返回该用户在多个学校的教师记录
```

### 🔧 迁移策略

#### **现有数据迁移**
如果已有教师数据，需要：

1. **创建用户记录**
```sql
INSERT INTO edu_user (username, password, real_name, phone, email, user_type, teacher_no)
SELECT teacher_no, password, real_name, phone, email, 'teacher', teacher_no
FROM old_teacher_table;
```

2. **更新教师表**
```sql
UPDATE edu_teacher SET user_id = (
    SELECT id FROM edu_user WHERE teacher_no = edu_teacher.teacher_no
);
```

### 📝 总结

**用户表仍然重要**，原因：
- ✅ 统一身份认证
- ✅ 基础信息管理
- ✅ 跨角色支持
- ✅ 会员体系
- ✅ 安全机制

**教师表的作用**：
- 🎯 存储教师特有信息
- 🎯 学校关联管理
- 🎯 专业信息维护
- 🎯 状态管理

这种设计既保持了数据的统一性，又满足了业务的专业性需求。 