<?php

use think\migration\Migrator;
use think\migration\db\Column;

class FixSchoolAdminUserRelation extends Migrator
{
    public function change()
    {
        // 为学校管理员表添加user_id字段
        $this->table('school_admin')
            ->addColumn('user_id', 'integer', [
                'limit' => 11,
                'null' => true,
                'comment' => '关联用户ID',
                'after' => 'school_id'
            ])
            ->addIndex(['user_id'], ['name' => 'idx_user_id'])
            ->update();
    }
} 