<?php

use think\migration\Migrator;
use think\migration\db\Column;

class AddPasswordToTeacherTable extends Migrator
{
    /**
     * Change Method.
     */
    public function change()
    {
        $table = $this->table('edu_teacher');
        
        // 添加密码字段
        if (!$table->hasColumn('password')) {
            $table->addColumn('password', 'string', ['limit' => 255, 'null' => true, 'comment' => '密码'])
                  ->addIndex(['phone'])
                  ->addIndex(['email'])
                  ->update();
        }
    }
} 