<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateToolTable extends Migrator
{
    public function change()
    {
        // 如果表已存在，则跳过创建
        if ($this->hasTable('tool')) {
            return;
        }

        $table = $this->table('tool', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn(Column::string('name', 100)->setComment('配置名称'))
              ->addColumn(Column::string('type', 20)->setDefault('other')->setComment('配置类型：api=API配置,system=系统配置,other=其他配置'))
              ->addColumn(Column::text('value')->setComment('配置值'))
              ->addColumn(Column::string('description', 500)->setNullable()->setComment('配置描述'))
              ->addColumn(Column::integer('status')->setDefault(1)->setComment('状态：0=停用,1=启用'))
              ->addColumn(Column::timestamp('create_time')->setNullable()->setComment('创建时间'))
              ->addColumn(Column::timestamp('update_time')->setNullable()->setComment('更新时间'))
              ->addIndex(['name'], ['unique' => true])
              ->addIndex(['type'])
              ->addIndex(['status'])
              ->create();
    }

    /**
     * 禁用回滚，防止表不存在时报错
     */
    public function down()
    {
        // 不执行任何操作，防止回滚时出错
        $this->output->writeln('<comment>Down migration disabled for CreateToolTable to prevent errors.</comment>');
    }
} 