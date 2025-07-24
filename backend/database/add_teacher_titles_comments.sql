-- 为现有的 edu_teacher_titles 表添加注释
-- 如果表已存在且有数据，使用此脚本添加注释而不影响现有数据

-- 1. 添加表注释
ALTER TABLE `edu_teacher_titles` COMMENT='教师职称表，用于存储教师职称信息，包括职称名称、代码、级别等相关数据。系统通过此表实现教师职称的标准化管理。';

-- 2. 为各字段添加注释
ALTER TABLE `edu_teacher_titles` 
MODIFY COLUMN `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID，自增',
MODIFY COLUMN `name` varchar(50) NOT NULL COMMENT '职称名称，如：教授、副教授、讲师、助教、其他',
MODIFY COLUMN `code` varchar(30) NOT NULL COMMENT '职称代码，用于系统内部标识，如：professor、associate_professor等',
MODIFY COLUMN `sort` int(11) DEFAULT '0' COMMENT '排序权重，数值越小越靠前显示',
MODIFY COLUMN `level` int(11) DEFAULT '1' COMMENT '职称级别：1=正高级职称（教授），2=副高级职称（副教授），3=中级职称（讲师），4=初级职称（助教），5=其他职称',
MODIFY COLUMN `description` varchar(255) DEFAULT NULL COMMENT '职称描述或备注信息，存储职称的详细说明、要求、职责等',
MODIFY COLUMN `status` tinyint(1) DEFAULT '1' COMMENT '状态：0=禁用（停用，不在前端显示），1=启用（正常使用）',
MODIFY COLUMN `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间，系统自动生成',
MODIFY COLUMN `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间，系统自动维护';

-- 3. 为索引添加注释（MySQL 8.0+支持）
-- ALTER INDEX `idx_code` ON `edu_teacher_titles` COMMENT '职称代码唯一索引，确保职称代码的唯一性';

-- 4. 检查结果
-- 执行以下查询确认注释已添加成功
-- SHOW CREATE TABLE `edu_teacher_titles`;
-- 或者
-- SELECT 
--   COLUMN_NAME,
--   DATA_TYPE,
--   IS_NULLABLE,
--   COLUMN_DEFAULT,
--   COLUMN_COMMENT
-- FROM INFORMATION_SCHEMA.COLUMNS
-- WHERE TABLE_SCHEMA = DATABASE()
-- AND TABLE_NAME = 'edu_teacher_titles'
-- ORDER BY ORDINAL_POSITION; 