<?php

use think\migration\Migrator;
use think\migration\db\Column;

class AddRegionIdToSystemConfig extends Migrator
{
    public function change()
    {
        $table = $this->table('system_config');
        $table->addColumn('region_id', 'string', [
            'null' => true,
            'default' => 'cn-hangzhou',
            'comment' => '区域ID（阿里云专用）',
            'after' => 'config'
        ])->update();
    }
} 