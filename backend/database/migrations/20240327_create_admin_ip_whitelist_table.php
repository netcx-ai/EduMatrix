<?php
use think\migration\Migrator;
use think\migration\db\Column;

class CreateAdminIpWhitelistTable extends Migrator
{
    public function change()
    {
        $table = $this->table('admin_ip_whitelist', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table
            ->addColumn('ip', 'string', ['limit' => 50, 'null' => false, 'comment' => 'IP地址'])
            ->addColumn('description', 'string', ['limit' => 255, 'null' => true, 'comment' => '描述'])
            ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '状态：0=禁用，1=启用'])
            ->addColumn('create_time', 'datetime', ['null' => false, 'comment' => '创建时间'])
            ->addColumn('update_time', 'datetime', ['null' => false, 'comment' => '更新时间'])
            ->addIndex(['ip'], ['unique' => true])
            ->create();
    }
} 