<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateStatisticsTables extends Migrator
{
    public function change()
    {
        // 创建学校统计表
        $this->createSchoolStatisticsTable();
        
        // 创建教师统计表
        $this->createTeacherStatisticsTable();
        
        // 创建课程统计表
        $this->createCourseStatisticsTable();
        
        // 创建AI使用统计表
        $this->createAiStatisticsTable();
        
        // 创建活跃度统计表
        $this->createActivityStatisticsTable();
    }
    
    /**
     * 创建学校统计表
     */
    private function createSchoolStatisticsTable()
    {
        $table = $this->table('edu_school_statistics', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('school_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '学校ID'])
              ->addColumn('date', 'date', ['null' => false, 'comment' => '统计日期'])
              ->addColumn('teacher_count', 'integer', ['default' => 0, 'comment' => '教师总数'])
              ->addColumn('active_teacher_count', 'integer', ['default' => 0, 'comment' => '活跃教师数'])
              ->addColumn('course_count', 'integer', ['default' => 0, 'comment' => '课程总数'])
              ->addColumn('active_course_count', 'integer', ['default' => 0, 'comment' => '活跃课程数'])
              ->addColumn('file_count', 'integer', ['default' => 0, 'comment' => '文件总数'])
              ->addColumn('file_size_total', 'bigint', ['limit' => 20, 'default' => 0, 'comment' => '文件总大小'])
              ->addColumn('ai_usage_count', 'integer', ['default' => 0, 'comment' => 'AI使用次数'])
              ->addColumn('ai_cost_total', 'decimal', ['precision' => 10, 'scale' => 4, 'default' => 0, 'comment' => 'AI使用总成本'])
              ->addColumn('login_count', 'integer', ['default' => 0, 'comment' => '登录次数'])
              ->addColumn('unique_login_count', 'integer', ['default' => 0, 'comment' => '独立登录用户数'])
              ->addColumn('page_view_count', 'integer', ['default' => 0, 'comment' => '页面浏览量'])
              ->addColumn('unique_visitor_count', 'integer', ['default' => 0, 'comment' => '独立访客数'])
              ->addColumn('create_time', 'datetime', ['null' => true, 'comment' => '创建时间'])
              ->addColumn('update_time', 'datetime', ['null' => true, 'comment' => '更新时间'])
              ->addIndex(['school_id', 'date'], ['unique' => true])
              ->addIndex(['school_id'])
              ->addIndex(['date'])
              ->create();
    }
    
    /**
     * 创建教师统计表
     */
    private function createTeacherStatisticsTable()
    {
        $table = $this->table('edu_teacher_statistics', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('school_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '学校ID'])
              ->addColumn('teacher_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '教师ID'])
              ->addColumn('date', 'date', ['null' => false, 'comment' => '统计日期'])
              ->addColumn('login_count', 'integer', ['default' => 0, 'comment' => '登录次数'])
              ->addColumn('online_duration', 'integer', ['default' => 0, 'comment' => '在线时长(分钟)'])
              ->addColumn('course_count', 'integer', ['default' => 0, 'comment' => '课程数量'])
              ->addColumn('file_upload_count', 'integer', ['default' => 0, 'comment' => '文件上传数量'])
              ->addColumn('file_size_total', 'bigint', ['limit' => 20, 'default' => 0, 'comment' => '文件总大小'])
              ->addColumn('ai_usage_count', 'integer', ['default' => 0, 'comment' => 'AI使用次数'])
              ->addColumn('ai_cost_total', 'decimal', ['precision' => 10, 'scale' => 4, 'default' => 0, 'comment' => 'AI使用成本'])
              ->addColumn('content_create_count', 'integer', ['default' => 0, 'comment' => '内容创建数量'])
              ->addColumn('approval_submit_count', 'integer', ['default' => 0, 'comment' => '审批提交数量'])
              ->addColumn('approval_approved_count', 'integer', ['default' => 0, 'comment' => '审批通过数量'])
              ->addColumn('approval_rejected_count', 'integer', ['default' => 0, 'comment' => '审批驳回数量'])
              ->addColumn('page_view_count', 'integer', ['default' => 0, 'comment' => '页面浏览量'])
              ->addColumn('create_time', 'datetime', ['null' => true, 'comment' => '创建时间'])
              ->addColumn('update_time', 'datetime', ['null' => true, 'comment' => '更新时间'])
              ->addIndex(['school_id', 'teacher_id', 'date'], ['unique' => true])
              ->addIndex(['school_id'])
              ->addIndex(['teacher_id'])
              ->addIndex(['date'])
              ->create();
    }
    
    /**
     * 创建课程统计表
     */
    private function createCourseStatisticsTable()
    {
        $table = $this->table('edu_course_statistics', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('school_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '学校ID'])
              ->addColumn('course_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '课程ID'])
              ->addColumn('date', 'date', ['null' => false, 'comment' => '统计日期'])
              ->addColumn('teacher_count', 'integer', ['default' => 0, 'comment' => '教师数量'])
              ->addColumn('file_count', 'integer', ['default' => 0, 'comment' => '文件数量'])
              ->addColumn('file_size_total', 'bigint', ['limit' => 20, 'default' => 0, 'comment' => '文件总大小'])
              ->addColumn('ai_content_count', 'integer', ['default' => 0, 'comment' => 'AI生成内容数量'])
              ->addColumn('view_count', 'integer', ['default' => 0, 'comment' => '查看次数'])
              ->addColumn('download_count', 'integer', ['default' => 0, 'comment' => '下载次数'])
              ->addColumn('create_time', 'datetime', ['null' => true, 'comment' => '创建时间'])
              ->addColumn('update_time', 'datetime', ['null' => true, 'comment' => '更新时间'])
              ->addIndex(['school_id', 'course_id', 'date'], ['unique' => true])
              ->addIndex(['school_id'])
              ->addIndex(['course_id'])
              ->addIndex(['date'])
              ->create();
    }
    
    /**
     * 创建AI使用统计表
     */
    private function createAiStatisticsTable()
    {
        $table = $this->table('edu_ai_statistics', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('school_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '学校ID'])
              ->addColumn('date', 'date', ['null' => false, 'comment' => '统计日期'])
              ->addColumn('tool_type', 'string', ['limit' => 50, 'null' => false, 'comment' => '工具类型'])
              ->addColumn('usage_count', 'integer', ['default' => 0, 'comment' => '使用次数'])
              ->addColumn('success_count', 'integer', ['default' => 0, 'comment' => '成功次数'])
              ->addColumn('fail_count', 'integer', ['default' => 0, 'comment' => '失败次数'])
              ->addColumn('token_used_total', 'integer', ['default' => 0, 'comment' => 'Token使用总量'])
              ->addColumn('cost_total', 'decimal', ['precision' => 10, 'scale' => 4, 'default' => 0, 'comment' => '总成本'])
              ->addColumn('avg_response_time', 'integer', ['default' => 0, 'comment' => '平均响应时间(毫秒)'])
              ->addColumn('unique_user_count', 'integer', ['default' => 0, 'comment' => '独立用户数'])
              ->addColumn('create_time', 'datetime', ['null' => true, 'comment' => '创建时间'])
              ->addColumn('update_time', 'datetime', ['null' => true, 'comment' => '更新时间'])
              ->addIndex(['school_id', 'date', 'tool_type'], ['unique' => true])
              ->addIndex(['school_id'])
              ->addIndex(['date'])
              ->addIndex(['tool_type'])
              ->create();
    }
    
    /**
     * 创建活跃度统计表
     */
    private function createActivityStatisticsTable()
    {
        $table = $this->table('edu_activity_statistics', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('school_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '学校ID'])
              ->addColumn('date', 'date', ['null' => false, 'comment' => '统计日期'])
              ->addColumn('hour', 'integer', ['limit' => 2, 'null' => false, 'comment' => '小时(0-23)'])
              ->addColumn('user_type', 'string', ['limit' => 20, 'default' => 'teacher', 'comment' => '用户类型：teacher教师，admin管理员'])
              ->addColumn('active_user_count', 'integer', ['default' => 0, 'comment' => '活跃用户数'])
              ->addColumn('login_count', 'integer', ['default' => 0, 'comment' => '登录次数'])
              ->addColumn('page_view_count', 'integer', ['default' => 0, 'comment' => '页面浏览量'])
              ->addColumn('file_upload_count', 'integer', ['default' => 0, 'comment' => '文件上传次数'])
              ->addColumn('ai_usage_count', 'integer', ['default' => 0, 'comment' => 'AI使用次数'])
              ->addColumn('create_time', 'datetime', ['null' => true, 'comment' => '创建时间'])
              ->addColumn('update_time', 'datetime', ['null' => true, 'comment' => '更新时间'])
              ->addIndex(['school_id', 'date', 'hour', 'user_type'], ['unique' => true])
              ->addIndex(['school_id'])
              ->addIndex(['date'])
              ->addIndex(['hour'])
              ->addIndex(['user_type'])
              ->create();
    }
} 