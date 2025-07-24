<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateSchoolTables extends Migrator
{
    public function change()
    {
        // 创建学校表
        $this->createSchoolTable();
        
        // 创建学院表
        $this->createCollegeTable();
        
        // 创建学校管理员表
        $this->createSchoolAdminTable();
        
        // 创建教师表
        $this->createTeacherTable();
        
        // 创建课程表
        $this->createCourseTable();
        
        // 创建课程教师关联表
        $this->createCourseTeacherTable();
        
        // 创建文件表
        $this->createFileTable();
        
        // 创建审批表
        $this->createApprovalTable();
        
        // 创建通知表
        $this->createNotificationTable();
    }
    
    /**
     * 创建学校表
     */
    private function createSchoolTable()
    {
        $table = $this->table('edu_school', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('name', 'string', ['limit' => 100, 'null' => false, 'comment' => '学校名称'])
              ->addColumn('code', 'string', ['limit' => 50, 'null' => false, 'comment' => '学校编码'])
              ->addColumn('short_name', 'string', ['limit' => 50, 'null' => true, 'comment' => '学校简称'])
              ->addColumn('description', 'text', ['null' => true, 'comment' => '学校描述'])
              ->addColumn('logo', 'string', ['limit' => 255, 'null' => true, 'comment' => '学校Logo'])
              ->addColumn('website', 'string', ['limit' => 255, 'null' => true, 'comment' => '学校官网'])
              ->addColumn('address', 'string', ['limit' => 255, 'null' => true, 'comment' => '学校地址'])
              ->addColumn('phone', 'string', ['limit' => 20, 'null' => true, 'comment' => '联系电话'])
              ->addColumn('email', 'string', ['limit' => 100, 'null' => true, 'comment' => '联系邮箱'])
              ->addColumn('contact_person', 'string', ['limit' => 50, 'null' => true, 'comment' => '联系人'])
              ->addColumn('contact_phone', 'string', ['limit' => 20, 'null' => true, 'comment' => '联系人电话'])
              ->addColumn('province', 'string', ['limit' => 50, 'null' => true, 'comment' => '省份'])
              ->addColumn('city', 'string', ['limit' => 50, 'null' => true, 'comment' => '城市'])
              ->addColumn('district', 'string', ['limit' => 50, 'null' => true, 'comment' => '区县'])
              ->addColumn('school_type', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '学校类型：1大学，2学院，3专科，4中学，5小学'])
              ->addColumn('student_count', 'integer', ['default' => 0, 'comment' => '学生总数'])
              ->addColumn('teacher_count', 'integer', ['default' => 0, 'comment' => '教师总数'])
              ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '状态：0禁用，1启用，2待审核'])
              ->addColumn('expire_time', 'datetime', ['null' => true, 'comment' => '服务到期时间'])
              ->addColumn('max_teacher_count', 'integer', ['default' => 1000, 'comment' => '最大教师数量'])
              ->addColumn('max_student_count', 'integer', ['default' => 50000, 'comment' => '最大学生数量'])
              ->addColumn('create_time', 'datetime', ['null' => true, 'comment' => '创建时间'])
              ->addColumn('update_time', 'datetime', ['null' => true, 'comment' => '更新时间'])
              ->addIndex(['code'], ['unique' => true])
              ->addIndex(['status'])
              ->addIndex(['province', 'city'])
              ->create();
    }
    
    /**
     * 创建学院表
     */
    private function createCollegeTable()
    {
        $table = $this->table('edu_college', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('school_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '学校ID'])
              ->addColumn('name', 'string', ['limit' => 100, 'null' => false, 'comment' => '学院名称'])
              ->addColumn('code', 'string', ['limit' => 50, 'null' => false, 'comment' => '学院编码'])
              ->addColumn('short_name', 'string', ['limit' => 50, 'null' => true, 'comment' => '学院简称'])
              ->addColumn('description', 'text', ['null' => true, 'comment' => '学院描述'])
              ->addColumn('dean', 'string', ['limit' => 50, 'null' => true, 'comment' => '院长'])
              ->addColumn('phone', 'string', ['limit' => 20, 'null' => true, 'comment' => '联系电话'])
              ->addColumn('email', 'string', ['limit' => 100, 'null' => true, 'comment' => '联系邮箱'])
              ->addColumn('address', 'string', ['limit' => 255, 'null' => true, 'comment' => '学院地址'])
              ->addColumn('teacher_count', 'integer', ['default' => 0, 'comment' => '教师数量'])
              ->addColumn('student_count', 'integer', ['default' => 0, 'comment' => '学生数量'])
              ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '状态：0禁用，1启用'])
              ->addColumn('sort', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '排序'])
              ->addColumn('create_time', 'datetime', ['null' => true, 'comment' => '创建时间'])
              ->addColumn('update_time', 'datetime', ['null' => true, 'comment' => '更新时间'])
              ->addIndex(['school_id', 'code'], ['unique' => true])
              ->addIndex(['school_id'])
              ->addIndex(['status'])
              ->create();
    }
    
    /**
     * 创建学校管理员表
     */
    private function createSchoolAdminTable()
    {
        $table = $this->table('edu_school_admin', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('school_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '学校ID'])
              ->addColumn('username', 'string', ['limit' => 50, 'null' => false, 'comment' => '用户名'])
              ->addColumn('password', 'string', ['limit' => 255, 'null' => false, 'comment' => '密码'])
              ->addColumn('real_name', 'string', ['limit' => 50, 'null' => false, 'comment' => '真实姓名'])
              ->addColumn('phone', 'string', ['limit' => 20, 'null' => true, 'comment' => '手机号'])
              ->addColumn('email', 'string', ['limit' => 100, 'null' => true, 'comment' => '邮箱'])
              ->addColumn('avatar', 'string', ['limit' => 255, 'null' => true, 'comment' => '头像'])
              ->addColumn('role', 'string', ['limit' => 50, 'default' => 'admin', 'comment' => '角色：admin管理员，dean院长，director主任'])
              ->addColumn('department', 'string', ['limit' => 100, 'null' => true, 'comment' => '所属部门'])
              ->addColumn('position', 'string', ['limit' => 100, 'null' => true, 'comment' => '职位'])
              ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '状态：0禁用，1启用'])
              ->addColumn('last_login_time', 'datetime', ['null' => true, 'comment' => '最后登录时间'])
              ->addColumn('last_login_ip', 'string', ['limit' => 50, 'null' => true, 'comment' => '最后登录IP'])
              ->addColumn('login_count', 'integer', ['default' => 0, 'comment' => '登录次数'])
              ->addColumn('create_time', 'datetime', ['null' => true, 'comment' => '创建时间'])
              ->addColumn('update_time', 'datetime', ['null' => true, 'comment' => '更新时间'])
              ->addIndex(['school_id', 'username'], ['unique' => true])
              ->addIndex(['school_id'])
              ->addIndex(['username'])
              ->addIndex(['phone'])
              ->addIndex(['email'])
              ->addIndex(['status'])
              ->create();
    }
    
    /**
     * 创建教师表
     */
    private function createTeacherTable()
    {
        $table = $this->table('edu_teacher', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('school_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '学校ID'])
              ->addColumn('college_id', 'integer', ['limit' => 11, 'null' => true, 'comment' => '学院ID'])
              ->addColumn('user_id', 'integer', ['limit' => 11, 'null' => true, 'comment' => '关联用户ID'])
              ->addColumn('teacher_no', 'string', ['limit' => 50, 'null' => false, 'comment' => '教师工号'])
              ->addColumn('real_name', 'string', ['limit' => 50, 'null' => false, 'comment' => '真实姓名'])
              ->addColumn('phone', 'string', ['limit' => 20, 'null' => true, 'comment' => '手机号'])
              ->addColumn('email', 'string', ['limit' => 100, 'null' => true, 'comment' => '邮箱'])
              ->addColumn('avatar', 'string', ['limit' => 255, 'null' => true, 'comment' => '头像'])
              ->addColumn('gender', 'integer', ['limit' => 1, 'default' => 0, 'comment' => '性别：0未知，1男，2女'])
              ->addColumn('birthday', 'date', ['null' => true, 'comment' => '生日'])
              ->addColumn('title', 'string', ['limit' => 50, 'null' => true, 'comment' => '职称'])
              ->addColumn('department', 'string', ['limit' => 100, 'null' => true, 'comment' => '所属部门'])
              ->addColumn('position', 'string', ['limit' => 100, 'null' => true, 'comment' => '职位'])
              ->addColumn('education', 'string', ['limit' => 50, 'null' => true, 'comment' => '学历'])
              ->addColumn('major', 'string', ['limit' => 100, 'null' => true, 'comment' => '专业'])
              ->addColumn('hire_date', 'date', ['null' => true, 'comment' => '入职日期'])
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
              ->addIndex(['phone'])
              ->addIndex(['email'])
              ->addIndex(['status'])
              ->addIndex(['is_verified'])
              ->create();
    }
    
    /**
     * 创建课程表
     */
    private function createCourseTable()
    {
        $table = $this->table('edu_course', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('school_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '学校ID'])
              ->addColumn('college_id', 'integer', ['limit' => 11, 'null' => true, 'comment' => '学院ID'])
              ->addColumn('course_code', 'string', ['limit' => 50, 'null' => false, 'comment' => '课程代码'])
              ->addColumn('course_name', 'string', ['limit' => 100, 'null' => false, 'comment' => '课程名称'])
              ->addColumn('course_type', 'string', ['limit' => 50, 'null' => true, 'comment' => '课程类型'])
              ->addColumn('description', 'text', ['null' => true, 'comment' => '课程描述'])
              ->addColumn('credits', 'decimal', ['precision' => 3, 'scale' => 1, 'default' => 0, 'comment' => '学分'])
              ->addColumn('hours', 'integer', ['default' => 0, 'comment' => '学时'])
              ->addColumn('semester', 'string', ['limit' => 20, 'null' => true, 'comment' => '学期'])
              ->addColumn('academic_year', 'string', ['limit' => 20, 'null' => true, 'comment' => '学年'])
              ->addColumn('responsible_teacher_id', 'integer', ['limit' => 11, 'null' => true, 'comment' => '负责教师ID'])
              ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '状态：0禁用，1启用'])
              ->addColumn('is_public', 'integer', ['limit' => 1, 'default' => 0, 'comment' => '是否公开：0私有，1公开'])
              ->addColumn('create_time', 'datetime', ['null' => true, 'comment' => '创建时间'])
              ->addColumn('update_time', 'datetime', ['null' => true, 'comment' => '更新时间'])
              ->addIndex(['school_id', 'course_code'], ['unique' => true])
              ->addIndex(['school_id'])
              ->addIndex(['college_id'])
              ->addIndex(['responsible_teacher_id'])
              ->addIndex(['status'])
              ->create();
    }
    
    /**
     * 创建课程教师关联表
     */
    private function createCourseTeacherTable()
    {
        $table = $this->table('edu_course_teacher', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('course_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '课程ID'])
              ->addColumn('teacher_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '教师ID'])
              ->addColumn('role', 'string', ['limit' => 20, 'default' => 'teacher', 'comment' => '角色：teacher教师，assistant助教'])
              ->addColumn('create_time', 'datetime', ['null' => true, 'comment' => '创建时间'])
              ->addIndex(['course_id', 'teacher_id'], ['unique' => true])
              ->addIndex(['course_id'])
              ->addIndex(['teacher_id'])
              ->create();
    }
    
    /**
     * 创建文件表
     */
    private function createFileTable()
    {
        $table = $this->table('edu_file', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('school_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '学校ID'])
              ->addColumn('uploader_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '上传者ID'])
              ->addColumn('uploader_type', 'string', ['limit' => 20, 'default' => 'teacher', 'comment' => '上传者类型：teacher教师，admin管理员'])
              ->addColumn('course_id', 'integer', ['limit' => 11, 'null' => true, 'comment' => '课程ID'])
              ->addColumn('file_name', 'string', ['limit' => 255, 'null' => false, 'comment' => '文件名'])
              ->addColumn('original_name', 'string', ['limit' => 255, 'null' => false, 'comment' => '原始文件名'])
              ->addColumn('file_path', 'string', ['limit' => 500, 'null' => false, 'comment' => '文件路径'])
              ->addColumn('file_size', 'bigint', ['limit' => 20, 'default' => 0, 'comment' => '文件大小(字节)'])
              ->addColumn('file_type', 'string', ['limit' => 50, 'null' => true, 'comment' => '文件类型'])
              ->addColumn('mime_type', 'string', ['limit' => 100, 'null' => true, 'comment' => 'MIME类型'])
              ->addColumn('file_category', 'string', ['limit' => 50, 'default' => 'other', 'comment' => '文件分类：document文档，image图片，video视频，audio音频，other其他'])
              ->addColumn('storage_type', 'string', ['limit' => 20, 'default' => 'local', 'comment' => '存储类型：local本地，oss阿里云，cos腾讯云'])
              ->addColumn('is_public', 'integer', ['limit' => 1, 'default' => 0, 'comment' => '是否公开：0私有，1公开'])
              ->addColumn('download_count', 'integer', ['default' => 0, 'comment' => '下载次数'])
              ->addColumn('view_count', 'integer', ['default' => 0, 'comment' => '查看次数'])
              ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '状态：0禁用，1启用'])
              ->addColumn('create_time', 'datetime', ['null' => true, 'comment' => '创建时间'])
              ->addColumn('update_time', 'datetime', ['null' => true, 'comment' => '更新时间'])
              ->addIndex(['school_id'])
              ->addIndex(['uploader_id', 'uploader_type'])
              ->addIndex(['course_id'])
              ->addIndex(['file_category'])
              ->addIndex(['status'])
              ->addIndex(['create_time'])
              ->create();
    }
    
    /**
     * 创建审批表
     */
    private function createApprovalTable()
    {
        $table = $this->table('edu_approval', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('school_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '学校ID'])
              ->addColumn('applicant_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '申请人ID'])
              ->addColumn('applicant_type', 'string', ['limit' => 20, 'default' => 'teacher', 'comment' => '申请人类型：teacher教师，admin管理员'])
              ->addColumn('approver_id', 'integer', ['limit' => 11, 'null' => true, 'comment' => '审批人ID'])
              ->addColumn('approval_type', 'string', ['limit' => 50, 'null' => false, 'comment' => '审批类型：file_upload文件上传，content_publish内容发布'])
              ->addColumn('title', 'string', ['limit' => 200, 'null' => false, 'comment' => '审批标题'])
              ->addColumn('content', 'text', ['null' => true, 'comment' => '审批内容'])
              ->addColumn('related_id', 'integer', ['limit' => 11, 'null' => true, 'comment' => '关联ID'])
              ->addColumn('related_type', 'string', ['limit' => 50, 'null' => true, 'comment' => '关联类型'])
              ->addColumn('status', 'integer', ['limit' => 1, 'default' => 0, 'comment' => '状态：0待审批，1通过，2驳回'])
              ->addColumn('approval_time', 'datetime', ['null' => true, 'comment' => '审批时间'])
              ->addColumn('approval_comment', 'text', ['null' => true, 'comment' => '审批意见'])
              ->addColumn('create_time', 'datetime', ['null' => true, 'comment' => '创建时间'])
              ->addColumn('update_time', 'datetime', ['null' => true, 'comment' => '更新时间'])
              ->addIndex(['school_id'])
              ->addIndex(['applicant_id', 'applicant_type'])
              ->addIndex(['approver_id'])
              ->addIndex(['approval_type'])
              ->addIndex(['status'])
              ->addIndex(['create_time'])
              ->create();
    }
    
    /**
     * 创建通知表
     */
    private function createNotificationTable()
    {
        $table = $this->table('edu_notification', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('school_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '学校ID'])
              ->addColumn('sender_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '发送者ID'])
              ->addColumn('sender_type', 'string', ['limit' => 20, 'default' => 'admin', 'comment' => '发送者类型：admin管理员，teacher教师，system系统'])
              ->addColumn('receiver_id', 'integer', ['limit' => 11, 'null' => true, 'comment' => '接收者ID'])
              ->addColumn('receiver_type', 'string', ['limit' => 20, 'default' => 'teacher', 'comment' => '接收者类型：teacher教师，admin管理员，all全部'])
              ->addColumn('title', 'string', ['limit' => 200, 'null' => false, 'comment' => '通知标题'])
              ->addColumn('content', 'text', ['null' => true, 'comment' => '通知内容'])
              ->addColumn('type', 'string', ['limit' => 50, 'default' => 'system', 'comment' => '通知类型：system系统，approval审批，course课程'])
              ->addColumn('is_read', 'integer', ['limit' => 1, 'default' => 0, 'comment' => '是否已读：0未读，1已读'])
              ->addColumn('read_time', 'datetime', ['null' => true, 'comment' => '阅读时间'])
              ->addColumn('create_time', 'datetime', ['null' => true, 'comment' => '创建时间'])
              ->addColumn('update_time', 'datetime', ['null' => true, 'comment' => '更新时间'])
              ->addIndex(['school_id'])
              ->addIndex(['sender_id', 'sender_type'])
              ->addIndex(['receiver_id', 'receiver_type'])
              ->addIndex(['type'])
              ->addIndex(['is_read'])
              ->addIndex(['create_time'])
              ->create();
    }
} 