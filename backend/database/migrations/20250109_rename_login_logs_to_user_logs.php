<?php

use think\migration\Migrator;
use think\migration\db\Column;

class RenameLoginLogsToUserLogs extends Migrator
{
    public function change()
    {
        // 检查表是否存在，如果存在则重命名
        if ($this->hasTable('login_logs')) {
            // 使用SQL语句直接重命名表
            $this->execute('RENAME TABLE `login_logs` TO `user_logs`');
            $this->output->writeln('<info>表 login_logs 已重命名为 user_logs</info>');
        } else {
            $this->output->writeln('<comment>表 login_logs 不存在，跳过重命名</comment>');
        }
    }
    
    public function down()
    {
        // 回滚时重命名回来
        if ($this->hasTable('user_logs')) {
            // 使用SQL语句直接重命名表
            $this->execute('RENAME TABLE `user_logs` TO `login_logs`');
            $this->output->writeln('<info>表 user_logs 已重命名为 login_logs</info>');
        }
    }
} 