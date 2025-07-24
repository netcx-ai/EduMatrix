<?php
// 手动执行修复迁移
require_once __DIR__ . '/vendor/autoload.php';

// 初始化应用
$app = new think\App();
$app->initialize();

use think\facade\Db;

try {
    echo "开始执行修复迁移...\n";
    
    // 为学校管理员表添加user_id字段
    $sql = "ALTER TABLE `edu_school_admin` ADD COLUMN `user_id` int(11) NULL COMMENT '关联用户ID' AFTER `school_id`";
    Db::execute($sql);
    
    // 添加索引
    $sql = "ALTER TABLE `edu_school_admin` ADD INDEX `idx_user_id` (`user_id`)";
    Db::execute($sql);
    
    echo "修复迁移执行成功！\n";
    
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
} 