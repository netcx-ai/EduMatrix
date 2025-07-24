<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateUserLogsTable extends Migrator
{
    public function change()
    {
        $table = $this->table('user_logs', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table
            ->addColumn('user_id', 'integer', ['signed' => false, 'null' => false, 'comment' => '用户ID'])
            ->addColumn('login_time', 'datetime', ['null' => false, 'comment' => '操作时间'])
            ->addColumn('login_ip', 'string', ['limit' => 45, 'null' => true, 'comment' => '操作IP'])
            ->addColumn('login_device', 'string', ['limit' => 255, 'null' => true, 'comment' => '操作设备'])
            ->addColumn('login_status', 'boolean', ['default' => true, 'comment' => '操作状态：1成功，0失败'])
            ->addColumn('login_type', 'string', ['limit' => 20, 'null' => true, 'comment' => '操作类型'])
            ->addColumn('fail_reason', 'string', ['limit' => 255, 'null' => true, 'comment' => '失败原因'])
            ->addColumn('created_at', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP', 'comment' => '创建时间'])
            ->addIndex(['user_id'], ['name' => 'idx_user_id'])
            ->addIndex(['login_time'], ['name' => 'idx_login_time'])
            ->addIndex(['login_status'], ['name' => 'idx_login_status'])
            ->create();
    }
} 