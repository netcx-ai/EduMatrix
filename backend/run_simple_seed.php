<?php
// 简单的测试数据插入脚本
require_once __DIR__ . '/vendor/autoload.php';

// 初始化应用
$app = new think\App();
$app->initialize();

use think\facade\Db;

try {
    echo "开始插入简单测试数据...\n";
    
    // 开启事务
    Db::startTrans();
    
    // 清空现有数据
    Db::name('school')->delete(true);
    Db::name('college')->delete(true);
    Db::name('teacher')->delete(true);
    Db::name('course')->delete(true);
    Db::name('user')->where('user_type', 'teacher')->delete(true);
    Db::name('user')->where('user_type', 'school_admin')->delete(true);
    Db::name('school_admin')->delete(true);
    echo "清空现有数据完成\n";
    
    // 生成唯一标识
    $timestamp = time();
    
    // 1. 插入学校数据
    $school = [
        'name' => '测试大学',
        'code' => 'TEST',
        'short_name' => '测试',
        'description' => '测试用大学',
        'province' => '北京市',
        'city' => '北京市',
        'district' => '海淀区',
        'school_type' => 1,
        'status' => 1,
        'create_time' => date('Y-m-d H:i:s'),
        'update_time' => date('Y-m-d H:i:s')
    ];
    
    $schoolId = Db::name('school')->insertGetId($school);
    echo "插入学校数据完成，ID: $schoolId\n";
    
    // 2. 插入学院数据
    $college = [
        'school_id' => $schoolId,
        'name' => '计算机学院',
        'code' => 'CS001',
        'short_name' => '计院',
        'description' => '计算机科学与技术学院',
        'status' => 1,
        'create_time' => date('Y-m-d H:i:s'),
        'update_time' => date('Y-m-d H:i:s')
    ];
    
    $collegeId = Db::name('college')->insertGetId($college);
    echo "插入学院数据完成，ID: $collegeId\n";
    
    // 3. 插入教师用户
    $teacherUser = [
        'username' => 'teacher' . $timestamp,
        'password' => password_hash('123456', PASSWORD_DEFAULT),
        'real_name' => '张老师',
        'phone' => '13800138001',
        'email' => 'teacher' . $timestamp . '@test.edu.cn',
        'user_type' => 'teacher',
        'primary_school_id' => $schoolId,
        'teacher_no' => 'T' . $timestamp,
        'status' => 1,
        'create_time' => date('Y-m-d H:i:s'),
        'update_time' => date('Y-m-d H:i:s')
    ];
    
    $teacherUserId = Db::name('user')->insertGetId($teacherUser);
    echo "插入教师用户完成，ID: $teacherUserId\n";
    
    // 4. 插入教师数据
    $teacher = [
        'school_id' => $schoolId,
        'college_id' => $collegeId,
        'user_id' => $teacherUserId,
        'teacher_no' => 'T' . $timestamp,
        'real_name' => '张老师',
        'phone' => '13800138001',
        'email' => 'teacher' . $timestamp . '@test.edu.cn',
        'gender' => 1,
        'title' => 1,
        'department' => '计算机学院',
        'position' => '讲师',
        'education' => '博士',
        'major' => '计算机科学',
        'hire_date' => '2020-09-01',
        'status' => 1,
        'is_verified' => 1,
        'create_time' => date('Y-m-d H:i:s'),
        'update_time' => date('Y-m-d H:i:s')
    ];
    
    $teacherId = Db::name('teacher')->insertGetId($teacher);
    echo "插入教师数据完成，ID: $teacherId\n";
    
    // 5. 插入课程数据
    $course = [
        'school_id' => $schoolId,
        'college_id' => $collegeId,
        'course_code' => 'C' . $timestamp,
        'name' => '计算机基础',
        'description' => '计算机基础课程',
        'credits' => 3,
        'hours' => 48,
        'semester' => '2024秋',
        'academic_year' => '2024-2025',
        'responsible_teacher_id' => $teacherId,
        'status' => 1,
        'is_public' => 1,
        'create_time' => date('Y-m-d H:i:s'),
        'update_time' => date('Y-m-d H:i:s')
    ];
    
    $courseId = Db::name('course')->insertGetId($course);
    echo "插入课程数据完成，ID: $courseId\n";
    
    // 6. 插入学校管理员用户
    $adminUser = [
        'username' => 'admin' . $timestamp,
        'password' => password_hash('123456', PASSWORD_DEFAULT),
        'real_name' => '管理员',
        'phone' => '13900139001',
        'email' => 'admin' . $timestamp . '@test.edu.cn',
        'user_type' => 'school_admin',
        'primary_school_id' => $schoolId,
        'status' => 1,
        'create_time' => date('Y-m-d H:i:s'),
        'update_time' => date('Y-m-d H:i:s')
    ];
    
    $adminUserId = Db::name('user')->insertGetId($adminUser);
    echo "插入管理员用户完成，ID: $adminUserId\n";
    
    // 7. 插入学校管理员数据
    $schoolAdmin = [
        'school_id' => $schoolId,
        'user_id' => $adminUserId,
        'username' => 'admin' . $timestamp,
        'password' => $adminUser['password'],
        'real_name' => '管理员',
        'phone' => '13900139001',
        'email' => 'admin' . $timestamp . '@test.edu.cn',
        'role' => 'admin',
        'department' => '信息中心',
        'position' => '系统管理员',
        'status' => 1,
        'create_time' => date('Y-m-d H:i:s'),
        'update_time' => date('Y-m-d H:i:s')
    ];
    
    $adminId = Db::name('school_admin')->insertGetId($schoolAdmin);
    echo "插入学校管理员数据完成，ID: $adminId\n";
    
    // 8. 插入AI工具权限
    $aiTools = Db::name('ai_tool')->where('status', 1)->column('id');
    if (!empty($aiTools)) {
        foreach ($aiTools as $toolId) {
            Db::name('ai_tool_school')->insert([
                'school_id' => $schoolId,
                'tool_id' => $toolId,
                'status' => 1,
                'daily_limit' => 200,
                'monthly_limit' => 5000,
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s')
            ]);
        }
        echo "插入AI工具权限数据完成\n";
    }
    
    // 提交事务
    Db::commit();
    echo "简单测试数据插入完成！\n";
    echo "测试账号信息：\n";
    echo "- 教师账号: teacher$timestamp / 123456\n";
    echo "- 管理员账号: admin$timestamp / 123456\n";
    
} catch (Exception $e) {
    // 回滚事务
    Db::rollback();
    echo "错误: " . $e->getMessage() . "\n";
} 