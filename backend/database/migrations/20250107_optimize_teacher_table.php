<?php

use think\migration\Migrator;
use think\migration\db\Column;

class OptimizeTeacherTable extends Migrator
{
    public function change()
    {
        // 优化教师表，采用与用户表关联的设计
        $this->optimizeTeacherTable();
        
        // 创建教师用户关联表（用于多对多关系）
        $this->createTeacherUserRelationTable();
        
        // 添加教师特有字段到用户表
        $this->addTeacherFieldsToUserTable();
    }
    
    /**
     * 优化教师表设计
     */
    private function optimizeTeacherTable()
    {
        // 如果教师表已存在，先删除
        if ($this->hasTable('edu_teacher')) {
            $this->dropTable('edu_teacher');
        }
        
        $table = $this->table('edu_teacher', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('school_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '学校ID'])
              ->addColumn('college_id', 'integer', ['limit' => 11, 'null' => true, 'comment' => '学院ID'])
              ->addColumn('user_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '关联用户ID'])
              ->addColumn('teacher_no', 'string', ['limit' => 50, 'null' => false, 'comment' => '教师工号'])
              ->addColumn('title', 'string', ['limit' => 50, 'null' => true, 'comment' => '职称'])
              ->addColumn('department', 'string', ['limit' => 100, 'null' => true, 'comment' => '所属部门'])
              ->addColumn('position', 'string', ['limit' => 100, 'null' => true, 'comment' => '职位'])
              ->addColumn('education', 'string', ['limit' => 50, 'null' => true, 'comment' => '学历'])
              ->addColumn('major', 'string', ['limit' => 100, 'null' => true, 'comment' => '专业'])
              ->addColumn('hire_date', 'date', ['null' => true, 'comment' => '入职日期'])
              ->addColumn('work_years', 'integer', ['default' => 0, 'comment' => '工作年限'])
              ->addColumn('teaching_subject', 'string', ['limit' => 100, 'null' => true, 'comment' => '任教科目'])
              ->addColumn('research_direction', 'string', ['limit' => 200, 'null' => true, 'comment' => '研究方向'])
              ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '状态：0禁用，1启用，2待审核'])
              ->addColumn('is_verified', 'integer', ['limit' => 1, 'default' => 0, 'comment' => '是否认证：0未认证，1已认证'])
              ->addColumn('verified_time', 'datetime', ['null' => true, 'comment' => '认证时间'])
              ->addColumn('verified_by', 'integer', ['limit' => 11, 'null' => true, 'comment' => '认证人ID'])
              ->addColumn('last_login_time', 'datetime', ['null' => true, 'comment' => '最后登录时间'])
              ->addColumn('create_time', 'datetime', ['null' => true, 'comment' => '创建时间'])
              ->addColumn('update_time', 'datetime', ['null' => true, 'comment' => '更新时间'])
              ->addIndex(['school_id', 'teacher_no'], ['unique' => true])
              ->addIndex(['school_id'])
              ->addIndex(['college_id'])
              ->addIndex(['user_id'])
              ->addIndex(['status'])
              ->addIndex(['is_verified'])
              ->create();
    }
    
    /**
     * 创建教师用户关联表（支持一个用户关联多个学校）
     */
    private function createTeacherUserRelationTable()
    {
        $table = $this->table('edu_teacher_user_relation', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('teacher_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '教师ID'])
              ->addColumn('user_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '用户ID'])
              ->addColumn('school_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '学校ID'])
              ->addColumn('is_primary', 'integer', ['limit' => 1, 'default' => 0, 'comment' => '是否主要关联：0否，1是'])
              ->addColumn('create_time', 'datetime', ['null' => true, 'comment' => '创建时间'])
              ->addIndex(['teacher_id', 'user_id', 'school_id'], ['unique' => true])
              ->addIndex(['teacher_id'])
              ->addIndex(['user_id'])
              ->addIndex(['school_id'])
              ->create();
    }
    
    /**
     * 为用户表添加教师相关字段
     */
    private function addTeacherFieldsToUserTable()
    {
        if ($this->hasTable('edu_user')) {
            $table = $this->table('edu_user');
            
            // 添加用户类型字段
            if (!$table->hasColumn('user_type')) {
                $table->addColumn('user_type', 'string', ['limit' => 20, 'default' => 'member', 'comment' => '用户类型：member会员，teacher教师，admin管理员，school_admin学校管理员']);
            }
            
            // 添加学校ID字段（用于快速查询）
            if (!$table->hasColumn('primary_school_id')) {
                $table->addColumn('primary_school_id', 'integer', ['limit' => 11, 'null' => true, 'comment' => '主要学校ID']);
            }
            
            // 添加教师工号字段
            if (!$table->hasColumn('teacher_no')) {
                $table->addColumn('teacher_no', 'string', ['limit' => 50, 'null' => true, 'comment' => '教师工号']);
            }
            
            $table->update();
        }
    }
} 