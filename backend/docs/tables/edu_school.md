# edu_school 表结构说明

## 表基本信息

| 属性 | 值 |
|------|-----|
| 表名 | `edu_school` |
| 说明 | 学校信息表 |
| 引擎 | InnoDB |
| 字符集 | utf8mb4_unicode_ci |

## 表用途说明

学校信息表，存储学校的基本信息，包括学校名称、地址、联系方式、学校类型等。是整个教育管理系统的基础数据表，所有学院、教师、学生等都会关联到具体的学校。

## 字段结构

| 字段名 | 类型 | 是否为空 | 默认值 | 键类型 | 额外属性 | 说明 |
|--------|------|----------|--------|--------|----------|------|
| id | int(11) | 否 | NULL | 主键 | AUTO_INCREMENT | 主键ID |
| name | varchar(100) | 否 | NULL | - | - | 学校名称 |
| code | varchar(50) | 否 | NULL | 唯一键 | - | 学校编码 |
| short_name | varchar(50) | 是 | NULL | - | - | 学校简称 |
| description | text | 是 | NULL | - | - | 学校描述 |
| logo | varchar(255) | 是 | NULL | - | - | 学校Logo |
| website | varchar(255) | 是 | NULL | - | - | 学校官网 |
| address | varchar(255) | 是 | NULL | - | - | 学校地址 |
| phone | varchar(20) | 是 | NULL | - | - | 联系电话 |
| email | varchar(100) | 是 | NULL | - | - | 联系邮箱 |
| contact_person | varchar(50) | 是 | NULL | - | - | 联系人 |
| contact_phone | varchar(20) | 是 | NULL | - | - | 联系人电话 |
| province | varchar(50) | 是 | NULL | - | - | 省份 |
| city | varchar(50) | 是 | NULL | - | - | 城市 |
| district | varchar(50) | 是 | NULL | - | - | 区县 |
| school_type | tinyint(1) | 是 | 1 | - | - | 学校类型 |
| student_count | int(11) | 是 | 0 | - | - | 学生总数 |
| teacher_count | int(11) | 是 | 0 | - | - | 教师总数 |
| status | tinyint(1) | 是 | 1 | - | - | 状态 |
| expire_time | datetime | 是 | NULL | - | - | 服务到期时间 |
| max_teacher_count | int(11) | 是 | 1000 | - | - | 最大教师数量 |
| max_student_count | int(11) | 是 | 50000 | - | - | 最大学生数量 |
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

### name
- **类型**: varchar(100)
- **是否为空**: 否
- **默认值**: NULL
- **说明**: 学校全称，如"北京大学"

### code
- **类型**: varchar(50)
- **是否为空**: 否
- **默认值**: NULL
- **键类型**: 唯一键
- **说明**: 学校编码，用于系统内部标识，全局唯一

### short_name
- **类型**: varchar(50)
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 学校简称，如"北大"

### description
- **类型**: text
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 学校详细描述或介绍

### logo
- **类型**: varchar(255)
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 学校Logo图片路径

### website
- **类型**: varchar(255)
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 学校官方网站URL

### address
- **类型**: varchar(255)
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 学校详细地址

### phone
- **类型**: varchar(20)
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 学校联系电话

### email
- **类型**: varchar(100)
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 学校联系邮箱

### contact_person
- **类型**: varchar(50)
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 主要联系人姓名

### contact_phone
- **类型**: varchar(20)
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 联系人电话

### province
- **类型**: varchar(50)
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 所在省份

### city
- **类型**: varchar(50)
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 所在城市

### district
- **类型**: varchar(50)
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 所在区县

### school_type
- **类型**: tinyint(1)
- **是否为空**: 是
- **默认值**: 1
- **说明**: 学校类型：1=大学，2=学院，3=专科，4=中学，5=小学

### student_count
- **类型**: int(11)
- **是否为空**: 是
- **默认值**: 0
- **说明**: 学生总数，用于统计

### teacher_count
- **类型**: int(11)
- **是否为空**: 是
- **默认值**: 0
- **说明**: 教师总数，用于统计

### status
- **类型**: tinyint(1)
- **是否为空**: 是
- **默认值**: 1
- **说明**: 状态：0=禁用，1=启用，2=待审核

### expire_time
- **类型**: datetime
- **是否为空**: 是
- **默认值**: NULL
- **说明**: 服务到期时间，用于服务管理

### max_teacher_count
- **类型**: int(11)
- **是否为空**: 是
- **默认值**: 1000
- **说明**: 最大教师数量限制

### max_student_count
- **类型**: int(11)
- **是否为空**: 是
- **默认值**: 50000
- **说明**: 最大学生数量限制

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

### idx_code
- **字段**: code
- **类型**: BTREE
- **唯一**: 是

### idx_status
- **字段**: status
- **类型**: BTREE
- **唯一**: 否

### idx_province_city
- **字段**: province, city
- **类型**: BTREE
- **唯一**: 否

## 使用建议

1. **状态管理**: 该表包含status字段，建议使用软删除而非物理删除
2. **时间管理**: 系统自动维护创建时间和更新时间，无需手动设置
3. **唯一性**: code字段具有唯一性约束，确保学校编码唯一
4. **地区查询**: 可通过province、city字段进行地区筛选
5. **性能优化**: 查询时尽量使用索引字段作为查询条件
6. **数据备份**: 重要数据表建议定期备份

## 相关文件

**模型文件**: `app/model/School.php`
**控制器文件**: `app/controller/admin/school.php`
**API控制器**: `app/controller/api/school.php`
**视图文件**: `view/admin/school/` 