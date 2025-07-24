<?php

use think\migration\Migrator;
use think\migration\db\Column;

class AddHireDateToTeacherTable extends Migrator
{
    /**
     * Change Method.
     */
    public function change()
    {
        $table = $this->table('teacher');
        
        // 添加入职时间字段（去掉after选项，兼容所有MySQL版本）
        if (!$table->hasColumn('hire_date')) {
            $table->addColumn('hire_date', 'date', [
                'null' => true, 
                'comment' => '入职日期'
            ])->update();
        }
    }
} 