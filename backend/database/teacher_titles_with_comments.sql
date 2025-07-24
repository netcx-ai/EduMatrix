-- 教师职称表 - 带完整注释版本
-- 用于存储教师职称信息，包括职称名称、代码、级别等相关数据
-- 系统通过此表实现教师职称的标准化管理

DROP TABLE IF EXISTS `edu_teacher_titles`;

CREATE TABLE `edu_teacher_titles` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID，自增',
  `name` varchar(50) NOT NULL COMMENT '职称名称，如：教授、副教授、讲师、助教、其他',
  `code` varchar(30) NOT NULL COMMENT '职称代码，用于系统内部标识，如：professor、associate_professor等',
  `sort` int(11) DEFAULT '0' COMMENT '排序权重，数值越小越靠前显示',
  `level` int(11) DEFAULT '1' COMMENT '职称级别：1=正高级职称（教授），2=副高级职称（副教授），3=中级职称（讲师），4=初级职称（助教），5=其他职称',
  `description` varchar(255) DEFAULT NULL COMMENT '职称描述或备注信息，存储职称的详细说明、要求、职责等',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态：0=禁用（停用，不在前端显示），1=启用（正常使用）',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间，系统自动生成',
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间，系统自动维护',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_code` (`code`) COMMENT '职称代码唯一索引，确保职称代码的唯一性'
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COMMENT='教师职称表，用于存储教师职称信息，包括职称名称、代码、级别等相关数据。系统通过此表实现教师职称的标准化管理。';

-- 插入默认数据
INSERT INTO `edu_teacher_titles` (`id`, `name`, `code`, `sort`, `level`, `description`, `status`) VALUES
(1, '教授', 'professor', 10, 1, '正高级职称，具有博士学位或相当学术水平，在教学、科研方面有突出贡献', 1),
(2, '副教授', 'associate_professor', 20, 2, '副高级职称，具有硕士以上学位，在教学、科研方面有一定成就', 1),
(3, '讲师', 'lecturer', 30, 3, '中级职称，具有本科以上学位，承担日常教学工作', 1),
(4, '助教', 'assistant', 40, 4, '初级职称，协助教师完成教学工作，通常为刚入职的教师', 1),
(5, '其他', 'other', 50, 5, '其他类型的职称或临时性职称', 1);

-- 创建索引说明
-- PRIMARY KEY (`id`) - 主键索引，确保记录唯一性，提高查询效率
-- UNIQUE KEY `idx_code` (`code`) - 唯一索引，确保职称代码的唯一性，防止重复

-- 使用建议
-- 1. 新增职称：直接在表中插入新记录
-- 2. 修改职称：更新对应字段，update_time会自动更新
-- 3. 禁用职称：将status设置为0，不删除记录
-- 4. 排序调整：修改sort字段值来调整显示顺序
-- 5. 级别管理：通过level字段进行权限控制和统计分析

-- 注意事项
-- 1. code字段必须保持唯一性，建议使用英文标识符
-- 2. 删除操作建议使用软删除（status=0）而非物理删除
-- 3. 该表数据变化不频繁，建议启用缓存机制
-- 4. 重要基础数据，建议定期备份 