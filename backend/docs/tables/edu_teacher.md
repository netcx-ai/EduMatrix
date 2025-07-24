# edu_teacher 表结构说明

## 表基本信息

| 属性 | 值 |
|------|-----|
| 表名 | `edu_teacher` |
| 说明 | 教师信息表 |
| 引擎 | InnoDB |
| 字符集 | utf8mb4_unicode_ci |

## 表用途说明

教师信息表，存储教师的详细信息，包括基本信息、职称、所属学校/学院等。教师是教育系统的核心角色，负责教学、科研等工作。

## 字段结构

| 字段名 | 类型 | 是否为空 | 默认值 | 键类型 | 额外属性 | 说明 |
|--------|------|----------|--------|--------|----------|------|
| id | int(11) | 否 | NULL | 主键 | AUTO_INCREMENT | 主键ID |
| user_id | int(11) | 是 | NULL | 索引键 | - | 用户ID |
| school_id | int(11) | 否 | NULL | 索引键 | - | 学校ID |
| college_id | int(11) | 是 | NULL | 索引键 | - | 学院ID |
| teacher_no | varchar(50) | 否 | NULL | 唯一键 | - | 教师工号 |
| name | varchar(50) | 否 | NULL | - | - | 教师姓名 |
| title | varchar(50) | 是 | NULL | - | - | 职称 |
| department | varchar(100) | 是 | NULL | - | - | 部门 |
| position | varchar(50) | 是 | NULL | - | - | 职位 |
| phone | varchar(20) | 是 | NULL | - | - | 手机号 |
| email | varchar(100) | 是 | NULL | - | - | 邮箱 |
| gender | tinyint(1) | 是 | 0 | - | - | 性别 |
| birthday | date | 是 | NULL | - | - | 生日 |
| id_card | varchar(18) | 是 | NULL | - | - | 身份证号 |
| education | varchar(50) | 是 | NULL | - | - | 学历 |
| major | varchar(100) | 是 | NULL | - | - | 专业 |
| graduation_school | varchar(100) | 是 | NULL | - | - | 毕业学校 |
| teaching_subject | varchar(100) | 是 | NULL | - | - | 教学科目 |
| research_direction | varchar(200) | 是 | NULL | - | - | 研究方向 |
| join_date | date | 是 | NULL | - | - | 入职日期 |
| avatar | varchar(255) | 是 | NULL | - | - | 头像 |
| introduction | text | 是 | NULL | - | - | 个人介绍 |
| status | tinyint(1) | 是 | 1 | 索引键 | - | 状态 |
| is_verified | tinyint(1) | 是 | 0 | - | - | 是否认证 |
| verified_time | datetime | 是 | NULL | - | - | 认证时间 |
| create_time | datetime | 是 | NULL | - | - | 创建时间 |
| update_time | datetime | 是 | NULL | - | - | 更新时间 |

## 详细字段说明

### id
- **类型**: int(11)
- **是否为空**: 否
- **默认值**: NULL
- **键类型**: 主键
- **额外属性**: AUTO_INCREMENT
- **说明**: 主键ID，自动递增

### user_id
- **类型**: int(11)
- **是否为空**: 是
- **默认值**: NULL
- **键类型**: 索引键
- **说明**: 用户ID，关联edu_user表

### school_id
- **类型**: int(11)
- **是否为空**: 否
- **默认值**: NULL
- **键类型**: 索引键
- **说明**: 学校ID，关联edu_school表

### college_id
- **类型**: int(11)
- **是否为空**: 是
- **默认值**: NULL
- **键类型**: 索引键
- **说明**: 学院ID，关联edu_college表

### teacher_no
- **类型**: varchar(50)
- **是否为空**: 否
- **默认值**: NULL
- **键类型**: 唯一键
- **说明**: 教师工号，全局唯一标识

### name
- **类型**: varchar(50)
- **是否为空**: 否
- **默认值**: NULL
- **说明**: 教师真实姓名

### title
- **类型**: varchar(50)
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 职称，如教授、副教授、讲师等

### department
- **类型**: varchar(100)
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 所属部门

### position
- **类型**: varchar(50)
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 职位，如系主任、教研室主任等

### phone
- **类型**: varchar(20)
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 手机号码

### email
- **类型**: varchar(100)
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 邮箱地址

### gender
- **类型**: tinyint(1)
- **是否为空**: 是
- **默认值**: 0
- **说明**: 性别：0=未知，1=男，2=女

### birthday
- **类型**: date
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 出生日期

### id_card
- **类型**: varchar(18)
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 身份证号码

### education
- **类型**: varchar(50)
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 学历，如本科、硕士、博士等

### major
- **类型**: varchar(100)
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 专业领域

### graduation_school
- **类型**: varchar(100)
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 毕业学校

### teaching_subject
- **类型**: varchar(100)
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 主要教学科目

### research_direction
- **类型**: varchar(200)
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 研究方向

### join_date
- **类型**: date
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 入职日期

### avatar
- **类型**: varchar(255)
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 头像文件路径

### introduction
- **类型**: text
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 个人介绍或简历

### status
- **类型**: tinyint(1)
- **是否为空**: 是
- **默认值**: 1
- **键类型**: 索引键
- **说明**: 状态：0=禁用，1=启用，2=待审核

### is_verified
- **类型**: tinyint(1)
- **是否为空**: 是
- **默认值**: 0
- **说明**: 是否认证：0=未认证，1=已认证

### verified_time
- **类型**: datetime
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 认证时间

### create_time
- **类型**: datetime
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 记录创建时间

### update_time
- **类型**: datetime
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 记录更新时间

## 索引信息

### PRIMARY
- **字段**: id
- **类型**: BTREE
- **唯一**: 是

### idx_user_id
- **字段**: user_id
- **类型**: BTREE
- **唯一**: 否

### idx_school_id
- **字段**: school_id
- **类型**: BTREE
- **唯一**: 否

### idx_college_id
- **字段**: college_id
- **类型**: BTREE
- **唯一**: 否

### idx_teacher_no
- **字段**: teacher_no
- **类型**: BTREE
- **唯一**: 是

### idx_status
- **字段**: status
- **类型**: BTREE
- **唯一**: 否

## 使用建议

1. **状态管理**: 该表包含status字段，建议使用软删除而非物理删除
2. **时间管理**: 系统自动维护创建时间和更新时间，无需手动设置
3. **关联查询**: 查询时通过school_id、college_id关联学校和学院信息
4. **唯一性**: teacher_no字段具有唯一性约束，确保教师工号唯一
5. **认证状态**: 通过is_verified字段管理教师认证状态
6. **性能优化**: 查询时尽量使用索引字段作为查询条件

## 相关文件

**模型文件**: `app/model/Teacher.php`
**控制器文件**: `app/controller/admin/teacher.php`
**API控制器**: `app/controller/api/teacher.php`
**视图文件**: `view/admin/teacher/` 