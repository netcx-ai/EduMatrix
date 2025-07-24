-- 教师职称表
CREATE TABLE IF NOT EXISTS `teacher_titles` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(50) NOT NULL COMMENT '职称名称',
  `code` varchar(30) NOT NULL COMMENT '职称代码',
  `sort` int(11) DEFAULT 0 COMMENT '排序权重',
  `level` int(11) DEFAULT 1 COMMENT '职称等级 1-5',
  `description` varchar(255) DEFAULT NULL COMMENT '职称描述',
  `status` tinyint(1) DEFAULT 1 COMMENT '状态 0-禁用 1-启用',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_code` (`code`),
  KEY `idx_status` (`status`),
  KEY `idx_sort` (`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='教师职称表';

-- 插入默认职称数据（包含英文代码以保证兼容性）
INSERT INTO `teacher_titles` (`id`, `name`, `code`, `sort`, `level`, `description`, `status`) VALUES
(1, '教授', 'professor', 10, 5, '高级职称，具有最高学术权威', 1),
(2, '副教授', 'associate_professor', 20, 4, '高级职称，具有较高学术水平', 1),
(3, '讲师', 'lecturer', 30, 3, '中级职称，具有一定教学经验', 1),
(4, '助教', 'assistant', 40, 2, '初级职称，协助教学工作', 1),
(5, '其他', 'other', 50, 1, '其他类型职称', 1); 