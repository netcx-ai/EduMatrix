<?php

use think\migration\Migrator;
use think\migration\db\Column;

class AddStorageDriverSettingDirect extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        // 直接执行 SQL 插入 storage_driver 设置
        try {
            // 检查表是否存在
            $tableExists = $this->hasTable('system_settings');
            if (!$tableExists) {
                $this->output->writeln('<comment>Table system_settings does not exist. Creating it first.</comment>');
                
                // 创建表
                $table = $this->table('system_settings', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
                $table->addColumn('group', 'string', ['limit' => 50, 'null' => false, 'comment' => '配置组'])
                      ->addColumn('key', 'string', ['limit' => 100, 'null' => false, 'comment' => '配置键名'])
                      ->addColumn('value', 'text', ['null' => true, 'comment' => '配置值'])
                      ->addColumn('type', 'string', ['limit' => 20, 'default' => 'text', 'comment' => '表单类型'])
                      ->addColumn('title', 'string', ['limit' => 100, 'null' => false, 'comment' => '配置标题'])
                      ->addColumn('description', 'string', ['limit' => 255, 'null' => true, 'comment' => '配置描述'])
                      ->addColumn('sort', 'integer', ['default' => 0, 'comment' => '排序'])
                      ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '状态：0禁用，1启用'])
                      ->addColumn('create_time', 'integer', ['null' => true, 'comment' => '创建时间'])
                      ->addColumn('update_time', 'integer', ['null' => true, 'comment' => '更新时间'])
                      ->addIndex(['group', 'key'], ['unique' => true])
                      ->create();
            }
            
            // 检查记录是否存在
            $exists = $this->fetchRow("SELECT id FROM edu_system_settings WHERE `group` = 'upload' AND `key` = 'storage_driver' LIMIT 1");
            if (!$exists) {
                $time = time();
                $this->execute("INSERT INTO edu_system_settings (`group`, `key`, value, type, title, description, sort, status, create_time, update_time) VALUES ('upload', 'storage_driver', 'local', 'select', '存储驱动', '文件存储位置：local本地, oss阿里云, cos腾讯云', 0, 1, {$time}, {$time})");
                $this->output->writeln('<info>Successfully added storage_driver setting.</info>');
            } else {
                $this->output->writeln('<comment>storage_driver setting already exists. Skipping.</comment>');
            }
        } catch (\Exception $e) {
            $this->output->writeln('<error>Error: ' . $e->getMessage() . '</error>');
            // 不抛出异常，让迁移继续
        }
    }
}
