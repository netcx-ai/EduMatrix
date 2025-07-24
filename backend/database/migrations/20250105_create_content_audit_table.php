<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateContentAuditTable extends Migrator
{
    /**
     * Change Method.
     */
    public function change()
    {
        $table = $this->table('content_audit', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('school_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '学校ID'])
              ->addColumn('file_id', 'integer', ['limit' => 11, 'null' => true, 'comment' => '文件ID'])
              ->addColumn('course_id', 'integer', ['limit' => 11, 'null' => true, 'comment' => '课程ID'])
              ->addColumn('content_type', 'string', ['limit' => 20, 'default' => 'file', 'comment' => '内容类型：file文件，course课程'])
              ->addColumn('content_title', 'string', ['limit' => 255, 'null' => false, 'comment' => '内容标题'])
              ->addColumn('content_description', 'text', ['null' => true, 'comment' => '内容描述'])
              ->addColumn('submitter_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '提交者ID'])
              ->addColumn('submitter_type', 'string', ['limit' => 20, 'default' => 'teacher', 'comment' => '提交者类型：teacher教师'])
              ->addColumn('status', 'string', ['limit' => 20, 'default' => 'pending', 'comment' => '审核状态：pending待审核，approved通过，rejected驳回'])
              ->addColumn('reviewer_id', 'integer', ['limit' => 11, 'null' => true, 'comment' => '审核人ID'])
              ->addColumn('review_time', 'datetime', ['null' => true, 'comment' => '审核时间'])
              ->addColumn('review_remark', 'text', ['null' => true, 'comment' => '审核备注'])
              ->addColumn('priority', 'integer', ['limit' => 1, 'default' => 0, 'comment' => '优先级：0普通，1重要，2紧急'])
              ->addColumn('create_time', 'datetime', ['null' => true, 'comment' => '创建时间'])
              ->addColumn('update_time', 'datetime', ['null' => true, 'comment' => '更新时间'])
              ->addIndex(['school_id'])
              ->addIndex(['file_id'])
              ->addIndex(['course_id'])
              ->addIndex(['submitter_id', 'submitter_type'])
              ->addIndex(['reviewer_id'])
              ->addIndex(['status'])
              ->addIndex(['content_type'])
              ->addIndex(['create_time'])
              ->create();
    }
} 