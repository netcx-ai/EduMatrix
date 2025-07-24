<?php
/**
 * 数据库迁移和种子数据填充脚本
 * 用于修复学校管理员表结构并填充测试数据
 */

require __DIR__ . '/vendor/autoload.php';

use think\App;
use think\facade\Db;

// 初始化应用
$app = new App();
$app->initialize();

echo "🚀 开始执行数据库迁移和种子数据填充...\n\n";

try {
    // 1. 执行数据库迁移
    echo "📋 步骤1: 执行数据库迁移...\n";
    $migration = new \FixSchoolAdminUserRelation();
    $migration->change();
    echo "✅ 数据库迁移完成\n\n";
    
    // 2. 执行种子数据填充
    echo "🌱 步骤2: 填充测试数据...\n";
    $seeder = new \TestDataSeeder();
    $seeder->run();
    echo "✅ 测试数据填充完成\n\n";
    
    // 3. 验证数据
    echo "🔍 步骤3: 验证数据完整性...\n";
    
    $schoolCount = Db::name('school')->count();
    $collegeCount = Db::name('college')->count();
    $teacherCount = Db::name('teacher')->count();
    $userCount = Db::name('user')->where('user_type', 'teacher')->count();
    $adminCount = Db::name('school_admin')->count();
    $adminUserCount = Db::name('user')->where('user_type', 'school_admin')->count();
    $courseCount = Db::name('course')->count();
    
    echo "📊 数据统计:\n";
    echo "   - 学校数量: {$schoolCount}\n";
    echo "   - 学院数量: {$collegeCount}\n";
    echo "   - 教师数量: {$teacherCount}\n";
    echo "   - 教师用户数量: {$userCount}\n";
    echo "   - 学校管理员数量: {$adminCount}\n";
    echo "   - 管理员用户数量: {$adminUserCount}\n";
    echo "   - 课程数量: {$courseCount}\n\n";
    
    // 4. 验证关联关系
    echo "🔗 步骤4: 验证关联关系...\n";
    
    // 检查教师与用户的关联
    $teacherWithoutUser = Db::name('teacher')->where('user_id', 'null')->count();
    if ($teacherWithoutUser > 0) {
        echo "❌ 发现 {$teacherWithoutUser} 个教师没有关联用户\n";
    } else {
        echo "✅ 所有教师都已关联用户\n";
    }
    
    // 检查学校管理员与用户的关联
    $adminWithoutUser = Db::name('school_admin')->where('user_id', 'null')->count();
    if ($adminWithoutUser > 0) {
        echo "❌ 发现 {$adminWithoutUser} 个学校管理员没有关联用户\n";
    } else {
        echo "✅ 所有学校管理员都已关联用户\n";
    }
    
    echo "\n🎉 所有操作完成！\n";
    echo "\n📝 测试账号信息:\n";
    echo "   教师账号: teacher10001, 密码: 123456\n";
    echo "   学校管理员账号: schooladmin1, 密码: 123456\n";
    echo "   学校管理员账号: schooladmin2, 密码: 123456\n";
    echo "   ...\n";
    
} catch (\Exception $e) {
    echo "❌ 执行失败: " . $e->getMessage() . "\n";
    echo "错误文件: " . $e->getFile() . "\n";
    echo "错误行号: " . $e->getLine() . "\n";
    exit(1);
} 