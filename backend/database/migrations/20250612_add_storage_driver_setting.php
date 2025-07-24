<?php

use think\migration\Migrator;

class AddStorageDriverSetting extends Migrator
{
    public function change()
    {
        // 仅在不存在时插入，避免重复
        try {
            $row = $this->fetchRow("SELECT id FROM edu_system_settings WHERE `group` = 'upload' AND `key` = 'storage_driver' LIMIT 1");
            if (!$row) {
                $time = time();
                $this->execute("INSERT INTO edu_system_settings (`group`, `key`, value, type, title, description, sort, status, create_time, update_time) VALUES ('upload', 'storage_driver', 'local', 'select', '存储驱动', '文件存储位置：local本地, oss阿里云, cos腾讯云', 0, 1, {$time}, {$time})");
            }
        } catch (\Exception $e) {
            // 如果表不存在，则不执行任何操作
            $this->output->writeln('<comment>Warning: ' . $e->getMessage() . '</comment>');
            $this->output->writeln('<comment>Skipping storage_driver setting insertion.</comment>');
        }
    }
    
    /**
     * 禁用回滚，防止表不存在时报错
     */
    public function down()
    {
        // 不执行任何操作，防止回滚时出错
        $this->output->writeln('<comment>Down migration disabled for AddStorageDriverSetting to prevent errors.</comment>');
    }
} 