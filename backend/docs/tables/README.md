# 数据库表结构文档索引

本文档包含了EduMatrix系统中所有数据库表的详细结构说明。

## 表列表

### 用户管理
- [edu_user](edu_user.md) - 用户表（统一身份认证）
- [edu_admin](edu_admin.md) - 系统管理员表
- [edu_teacher](edu_teacher.md) - 教师信息表
- [edu_student](edu_student.md) - 学生信息表

### 学校管理
- [edu_school](edu_school.md) - 学校信息表
- [edu_college](edu_college.md) - 学院信息表

### 内容管理
- [edu_content_library](edu_content_library.md) - 内容库表
- [edu_file](edu_file.md) - 文件管理表
- [edu_article](edu_article.md) - 文章表
- [edu_category](edu_category.md) - 分类表

### 课程管理
- [edu_course](edu_course.md) - 课程信息表
- [edu_course_teacher](edu_course_teacher.md) - 课程教师关联表

### AI工具
- [edu_ai_tool](edu_ai_tool.md) - AI工具配置表
- [edu_ai_tool_school](edu_ai_tool_school.md) - AI工具学校权限表
- [edu_ai_usage](edu_ai_usage.md) - AI使用记录表

### 系统管理
- [edu_admin_log](edu_admin_log.md) - 管理员操作日志表
- [edu_admin_role](edu_admin_role.md) - 管理员角色关联表
- [edu_admin_ip_whitelist](edu_admin_ip_whitelist.md) - IP白名单表

### 基础数据
- [edu_teacher_titles](edu_teacher_titles.md) - 教师职称表
- [edu_tag](edu_tag.md) - 标签表
- [edu_system_config](edu_system_config.md) - 系统配置表

### 统计分析
- [edu_visit_log](edu_visit_log.md) - 访问日志表
- [edu_visit_stats](edu_visit_stats.md) - 访问统计表

## 核心表关系

### 用户体系
```
edu_user (用户基础信息)
├── edu_admin (管理员扩展信息)
├── edu_teacher (教师扩展信息)
└── edu_student (学生扩展信息)
```

### 组织结构
```
edu_school (学校)
└── edu_college (学院)
    ├── edu_teacher (教师)
    └── edu_student (学生)
```

### 课程体系
```
edu_course (课程)
├── edu_course_teacher (课程教师关联)
└── edu_content_library (课程内容)
```

### AI工具体系
```
edu_ai_tool (AI工具配置)
├── edu_ai_tool_school (学校权限)
└── edu_ai_usage (使用记录)
```

## 数据库设计原则

1. **统一前缀**: 所有表都以`edu_`作为前缀
2. **字符集**: 统一使用`utf8mb4_unicode_ci`字符集
3. **时间字段**: 统一使用`create_time`和`update_time`
4. **状态管理**: 使用`status`字段实现软删除
5. **主键设计**: 统一使用自增整型主键`id`

## 索引设计规范

1. **主键索引**: 所有表都有主键索引
2. **唯一索引**: 关键业务字段如编码、工号等设置唯一索引
3. **普通索引**: 频繁查询的字段设置普通索引
4. **外键索引**: 关联字段设置索引提高查询效率

## 字段命名规范

1. **ID字段**: 主键统一命名为`id`
2. **关联字段**: 外键命名为`表名_id`，如`school_id`
3. **时间字段**: 统一使用`create_time`、`update_time`
4. **状态字段**: 统一使用`status`
5. **排序字段**: 统一使用`sort`

## 生成说明

- 生成时间: 2025-01-09 
- 生成表数: 40+
- 生成工具: 手动创建核心表文档
- 维护方式: 根据数据库变更及时更新文档

## 使用说明

1. 点击表名链接查看详细的表结构说明
2. 每个表文档包含字段说明、索引信息、使用建议等
3. 开发时参考相关文件路径快速定位代码
4. 遵循数据库设计规范进行开发

## 更新日志

- 2025-01-09: 创建核心表文档（admin、school、college、teacher、teacher_titles）
- 待完善: 其他表的详细文档 