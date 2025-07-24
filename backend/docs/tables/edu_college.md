# edu_college 表结构说明

## 表基本信息

| 属性 | 值 |
|------|-----|
| 表名 | `edu_college` |
| 说明 | 学院信息表 |
| 引擎 | InnoDB |
| 字符集 | utf8mb4_unicode_ci |

## 表用途说明

学院信息表，存储学院的基本信息，隶属于具体的学校。学院是学校的下级组织结构，用于管理不同专业领域的教师和学生。

## 字段结构

| 字段名 | 类型 | 是否为空 | 默认值 | 键类型 | 额外属性 | 说明 |
|--------|------|----------|--------|--------|----------|------|
| id | int(11) | 否 | NULL | 主键 | AUTO_INCREMENT | 主键ID |
| school_id | int(11) | 否 | NULL | 索引键 | - | 学校ID |
| name | varchar(100) | 否 | NULL | - | - | 学院名称 |
| code | varchar(50) | 否 | NULL | - | - | 学院编码 |
| short_name | varchar(50) | 是 | NULL | - | - | 学院简称 |
| description | text | 是 | NULL | - | - | 学院描述 |
| dean | varchar(50) | 是 | NULL | - | - | 院长 |
| phone | varchar(20) | 是 | NULL | - | - | 联系电话 |
| email | varchar(100) | 是 | NULL | - | - | 联系邮箱 |
| address | varchar(255) | 是 | NULL | - | - | 学院地址 |
| teacher_count | int(11) | 是 | 0 | - | - | 教师数量 |
| student_count | int(11) | 是 | 0 | - | - | 学生数量 |
| status | tinyint(1) | 是 | 1 | 索引键 | - | 状态 |
| sort | int(11) | 是 | 0 | - | - | 排序 |
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

### school_id
- **类型**: int(11)
- **是否为空**: 否
- **默认值**: NULL
- **键类型**: 索引键
- **说明**: 学校ID，关联edu_school表

### name
- **类型**: varchar(100)
- **是否为空**: 否
- **默认值**: NULL
- **说明**: 学院全称，如"计算机学院"

### code
- **类型**: varchar(50)
- **是否为空**: 否
- **默认值**: NULL
- **说明**: 学院编码，在同一学校内唯一

### short_name
- **类型**: varchar(50)
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 学院简称，如"计院"

### description
- **类型**: text
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 学院详细描述或介绍

### dean
- **类型**: varchar(50)
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 院长姓名

### phone
- **类型**: varchar(20)
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 学院联系电话

### email
- **类型**: varchar(100)
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 学院联系邮箱

### address
- **类型**: varchar(255)
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 学院具体地址

### teacher_count
- **类型**: int(11)
- **是否为空**: 是
- **默认值**: 0
- **说明**: 教师数量，用于统计

### student_count
- **类型**: int(11)
- **是否为空**: 是
- **默认值**: 0
- **说明**: 学生数量，用于统计

### status
- **类型**: tinyint(1)
- **是否为空**: 是
- **默认值**: 1
- **键类型**: 索引键
- **说明**: 状态：0=禁用，1=启用

### sort
- **类型**: int(11)
- **是否为空**: 是
- **默认值**: 0
- **说明**: 排序权重，用于控制显示顺序

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

### idx_school_code
- **字段**: school_id, code
- **类型**: BTREE
- **唯一**: 是

### idx_school_id
- **字段**: school_id
- **类型**: BTREE
- **唯一**: 否

### idx_status
- **字段**: status
- **类型**: BTREE
- **唯一**: 否

## 使用建议

1. **状态管理**: 该表包含status字段，建议使用软删除而非物理删除
2. **时间管理**: 系统自动维护创建时间和更新时间，无需手动设置
3. **排序功能**: 通过sort字段控制显示顺序，数值越小越靠前
4. **关联查询**: 查询时通过school_id关联学校信息
5. **唯一性**: school_id+code组合唯一，确保同一学校内学院编码唯一
6. **性能优化**: 查询时尽量使用索引字段作为查询条件

## 相关文件

**模型文件**: `app/model/College.php`
**控制器文件**: `app/controller/admin/college.php`
**API控制器**: `app/controller/api/college.php`
**视图文件**: `view/admin/college/` 