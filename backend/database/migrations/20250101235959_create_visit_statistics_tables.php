<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateVisitStatisticsTables extends Migrator
{
    /**
     * Change Method.
     */
    public function change()
    {
        // 创建访问日志表
        $this->createVisitLogTable();
        
        // 创建访问统计表
        $this->createVisitStatsTable();
        
        // 添加用户统计相关字段
        $this->addUserStatisticsFields();
    }
    
    /**
     * 禁用回滚，防止表不存在时报错
     */
    public function down()
    {
        // 不执行任何操作，防止回滚时出错
        $this->output->writeln('<comment>Down migration disabled for CreateVisitStatisticsTables to prevent errors.</comment>');
    }
    
    private function createVisitLogTable()
    {
        // 如果表已存在，则跳过创建
        if ($this->hasTable('visit_log')) {
            return;
        }
        
        $table = $this->table('visit_log', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('ip', 'string', ['limit' => 45, 'comment' => 'IP地址'])
              ->addColumn('user_agent', 'text', ['null' => true, 'comment' => '用户代理'])
              ->addColumn('url', 'string', ['limit' => 500, 'comment' => '访问URL'])
              ->addColumn('referer', 'string', ['limit' => 500, 'null' => true, 'comment' => '来源URL'])
              ->addColumn('user_id', 'integer', ['null' => true, 'comment' => '用户ID'])
              ->addColumn('session_id', 'string', ['limit' => 100, 'null' => true, 'comment' => '会话ID'])
              ->addColumn('method', 'string', ['limit' => 10, 'default' => 'GET', 'comment' => '请求方法'])
              ->addColumn('response_time', 'integer', ['null' => true, 'comment' => '响应时间(毫秒)'])
              ->addColumn('status_code', 'integer', ['default' => 200, 'comment' => 'HTTP状态码'])
              ->addColumn('country', 'string', ['limit' => 50, 'null' => true, 'comment' => '国家'])
              ->addColumn('province', 'string', ['limit' => 50, 'null' => true, 'comment' => '省份'])
              ->addColumn('city', 'string', ['limit' => 50, 'null' => true, 'comment' => '城市'])
              ->addColumn('device_type', 'string', ['limit' => 20, 'null' => true, 'comment' => '设备类型'])
              ->addColumn('browser', 'string', ['limit' => 50, 'null' => true, 'comment' => '浏览器'])
              ->addColumn('os', 'string', ['limit' => 50, 'null' => true, 'comment' => '操作系统'])
              ->addColumn('visit_time', 'datetime', ['comment' => '访问时间'])
              ->addColumn('date', 'date', ['comment' => '访问日期'])
              ->addIndex(['date'])
              ->addIndex(['user_id'])
              ->addIndex(['ip'])
              ->addIndex(['url'])
              ->create();
    }
    
    private function createVisitStatsTable()
    {
        // 如果表已存在，则跳过创建
        if ($this->hasTable('visit_stats')) {
            return;
        }
        
        $table = $this->table('visit_stats', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('date', 'date', ['comment' => '统计日期'])
              ->addColumn('pv', 'integer', ['default' => 0, 'comment' => '页面浏览量'])
              ->addColumn('uv', 'integer', ['default' => 0, 'comment' => '独立访客数'])
              ->addColumn('ip_count', 'integer', ['default' => 0, 'comment' => '独立IP数'])
              ->addColumn('new_users', 'integer', ['default' => 0, 'comment' => '新用户数'])
              ->addColumn('bounce_rate', 'decimal', ['precision' => 5, 'scale' => 2, 'default' => 0, 'comment' => '跳出率'])
              ->addColumn('avg_visit_time', 'integer', ['default' => 0, 'comment' => '平均访问时长(秒)'])
              ->addColumn('create_time', 'datetime', ['comment' => '创建时间'])
              ->addColumn('update_time', 'datetime', ['comment' => '更新时间'])
              ->addIndex(['date'], ['unique' => true])
              ->create();
    }

    private function addUserStatisticsFields()
    {
        if ($this->hasTable('edu_user')) {
            $table = $this->table('edu_user');
            if (!$table->hasColumn('gender')) {
                $table->addColumn('gender', 'integer', ['limit' => 1, 'default' => 0, 'comment' => '性别']);
            }
            if (!$table->hasColumn('birthday')) {
                $table->addColumn('birthday', 'date', ['null' => true, 'comment' => '生日']);
            }
            if (!$table->hasColumn('last_visit_time')) {
                $table->addColumn('last_visit_time', 'datetime', ['null' => true, 'comment' => '最后访问时间']);
            }
            if (!$table->hasColumn('visit_count')) {
                $table->addColumn('visit_count', 'integer', ['default' => 0, 'comment' => '访问次数']);
            }
            if (!$table->hasColumn('avatar')) {
                $table->addColumn('avatar', 'string', ['limit' => 255, 'null' => true, 'comment' => '头像']);
            }
            if (!$table->hasColumn('register_ip')) {
                $table->addColumn('register_ip', 'string', ['limit' => 45, 'null' => true, 'comment' => '注册IP']);
            }
            $table->update();
        }
    }
} 