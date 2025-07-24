<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateUserTable extends Migrator
{
    /**
     * Change Method.
     */
    public function change()
    {
        // 创建用户表（会员表）
        $table = $this->table('edu_user', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('username', 'string', ['limit' => 50, 'comment' => '用户名'])
              ->addColumn('password', 'string', ['limit' => 255, 'comment' => '密码'])
              ->addColumn('real_name', 'string', ['limit' => 50, 'null' => true, 'comment' => '真实姓名'])
              ->addColumn('phone', 'string', ['limit' => 20, 'null' => true, 'comment' => '手机号'])
              ->addColumn('email', 'string', ['limit' => 100, 'null' => true, 'comment' => '邮箱'])
              ->addColumn('member_level', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '会员等级：1普通会员，2VIP会员，3SVIP会员'])
              ->addColumn('member_expire_time', 'datetime', ['null' => true, 'comment' => '会员到期时间'])
              ->addColumn('points', 'integer', ['default' => 0, 'comment' => '积分'])
              ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '状态：0禁用，1启用'])
              ->addColumn('gender', 'integer', ['limit' => 1, 'default' => 0, 'comment' => '性别：0未知，1男，2女'])
              ->addColumn('birthday', 'date', ['null' => true, 'comment' => '生日'])
              ->addColumn('avatar', 'string', ['limit' => 255, 'null' => true, 'comment' => '头像'])
              ->addColumn('register_ip', 'string', ['limit' => 45, 'null' => true, 'comment' => '注册IP'])
              ->addColumn('last_visit_time', 'datetime', ['null' => true, 'comment' => '最后访问时间'])
              ->addColumn('visit_count', 'integer', ['default' => 0, 'comment' => '访问次数'])
              ->addColumn('last_password_change', 'datetime', ['null' => true, 'comment' => '最后密码修改时间'])
              ->addColumn('password_error_count', 'integer', ['default' => 0, 'comment' => '密码错误次数'])
              ->addColumn('password_error_time', 'datetime', ['null' => true, 'comment' => '密码错误时间'])
              ->addColumn('create_time', 'datetime', ['comment' => '创建时间'])
              ->addColumn('update_time', 'datetime', ['comment' => '更新时间'])
              ->addIndex(['username'], ['unique' => true])
              ->addIndex(['phone'])
              ->addIndex(['email'])
              ->addIndex(['status'])
              ->addIndex(['member_level'])
              ->create();
    }
    
    /**
     * 添加种子数据
     */
    public function up()
    {
        // 创建表
        $this->change();
        
        // 插入测试数据
        $this->table('edu_user')->insert([
            [
                'username' => 'testuser',
                'password' => password_hash('123456', PASSWORD_DEFAULT),
                'real_name' => '测试用户',
                'phone' => '13800138000',
                'email' => 'test@example.com',
                'member_level' => 1,
                'points' => 100,
                'status' => 1,
                'gender' => 1,
                'birthday' => '1990-01-01',
                'register_ip' => '127.0.0.1',
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s'),
            ],
            [
                'username' => 'vipuser',
                'password' => password_hash('123456', PASSWORD_DEFAULT),
                'real_name' => 'VIP用户',
                'phone' => '13900139000',
                'email' => 'vip@example.com',
                'member_level' => 2,
                'member_expire_time' => date('Y-m-d H:i:s', strtotime('+365 days')),
                'points' => 500,
                'status' => 1,
                'gender' => 2,
                'birthday' => '1995-05-15',
                'register_ip' => '127.0.0.1',
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s'),
            ]
        ])->save();
    }
    
    /**
     * 删除表
     */
    public function down()
    {
        $this->dropTable('edu_user');
    }
} 