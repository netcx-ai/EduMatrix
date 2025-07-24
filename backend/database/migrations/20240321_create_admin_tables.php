<?php
declare (strict_types = 1);

use think\migration\Migrator;
use think\migration\db\Column;

class CreateAdminTables extends Migrator
{
    /**
     * 创建管理员表
     */
    public function change()
    {
        // 创建管理员表
        $this->table('admin', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci'])
            ->addColumn('username', 'string', ['limit' => 50, 'null' => false, 'comment' => '用户名'])
            ->addColumn('password', 'string', ['limit' => 255, 'null' => false, 'comment' => '密码'])
            ->addColumn('real_name', 'string', ['limit' => 50, 'null' => false, 'comment' => '真实姓名'])
            ->addColumn('phone', 'string', ['limit' => 20, 'null' => true, 'comment' => '手机号'])
            ->addColumn('email', 'string', ['limit' => 100, 'null' => true, 'comment' => '邮箱'])
            ->addColumn('avatar', 'string', ['limit' => 255, 'null' => true, 'comment' => '头像'])
            ->addColumn('role', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '角色ID'])
            ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '状态：0禁用，1启用'])
            ->addColumn('last_login_time', 'datetime', ['null' => true, 'comment' => '最后登录时间'])
            ->addColumn('last_login_ip', 'string', ['limit' => 50, 'null' => true, 'comment' => '最后登录IP'])
            ->addColumn('create_time', 'datetime', ['null' => true])
            ->addColumn('update_time', 'datetime', ['null' => true])
            ->addIndex(['username'], ['unique' => true])
            ->addIndex(['phone'])
            ->addIndex(['email'])
            ->create();

        // 创建角色表
        $this->table('role', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci'])
            ->addColumn('name', 'string', ['limit' => 50, 'null' => false, 'comment' => '角色名称'])
            ->addColumn('description', 'string', ['limit' => 255, 'null' => true, 'comment' => '角色描述'])
            ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '状态：0禁用，1启用'])
            ->addColumn('create_time', 'datetime', ['null' => true])
            ->addColumn('update_time', 'datetime', ['null' => true])
            ->addIndex(['name'], ['unique' => true])
            ->create();

        // 创建权限表
        $this->table('permission', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci'])
            ->addColumn('name', 'string', ['limit' => 50, 'null' => false, 'comment' => '权限名称'])
            ->addColumn('code', 'string', ['limit' => 100, 'null' => false, 'comment' => '权限代码'])
            ->addColumn('type', 'string', ['limit' => 20, 'default' => 'menu', 'comment' => '权限类型：menu菜单，button按钮'])
            ->addColumn('parent_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '父级ID'])
            ->addColumn('path', 'string', ['limit' => 255, 'null' => true, 'comment' => '路径'])
            ->addColumn('icon', 'string', ['limit' => 50, 'null' => true, 'comment' => '图标'])
            ->addColumn('sort', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '排序'])
            ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '状态：0禁用，1启用'])
            ->addColumn('create_time', 'datetime', ['null' => true])
            ->addColumn('update_time', 'datetime', ['null' => true])
            ->addIndex(['code'], ['unique' => true])
            ->addIndex(['parent_id'])
            ->create();

        // 创建管理员角色关联表
        $this->table('admin_role', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci'])
            ->addColumn('admin_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '管理员ID'])
            ->addColumn('role_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '角色ID'])
            ->addIndex(['admin_id', 'role_id'], ['unique' => true])
            ->create();

        // 创建角色权限关联表
        $this->table('role_permission', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci'])
            ->addColumn('role_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '角色ID'])
            ->addColumn('permission_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '权限ID'])
            ->addIndex(['role_id', 'permission_id'], ['unique' => true])
            ->create();

        // 创建管理员操作日志表
        $this->table('admin_log', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci'])
            ->addColumn('admin_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '管理员ID'])
            ->addColumn('action', 'string', ['limit' => 100, 'null' => false, 'comment' => '操作'])
            ->addColumn('url', 'string', ['limit' => 255, 'null' => true, 'comment' => '请求URL'])
            ->addColumn('method', 'string', ['limit' => 10, 'null' => true, 'comment' => '请求方法'])
            ->addColumn('params', 'text', ['null' => true, 'comment' => '请求参数'])
            ->addColumn('ip', 'string', ['limit' => 50, 'null' => true, 'comment' => 'IP地址'])
            ->addColumn('user_agent', 'string', ['limit' => 255, 'null' => true, 'comment' => '用户代理'])
            ->addColumn('create_time', 'datetime', ['null' => true])
            ->addIndex(['admin_id'])
            ->addIndex(['create_time'])
            ->create();

        // 插入默认数据
        $this->insertDefaultData();
    }

    /**
     * 插入默认数据
     */
    protected function insertDefaultData()
    {
        // 插入默认管理员
        $this->insert('admin', [
            'username' => 'admin',
            'password' => password_hash('123456', PASSWORD_DEFAULT),
            'real_name' => '超级管理员',
            'phone' => '13800138000',
            'email' => 'admin@example.com',
            'role' => 1,
            'status' => 1,
            'create_time' => date('Y-m-d H:i:s'),
            'update_time' => date('Y-m-d H:i:s')
        ]);

        // 插入默认角色
        $this->insert('role', [
            'name' => '超级管理员',
            'description' => '拥有所有权限',
            'status' => 1,
            'create_time' => date('Y-m-d H:i:s'),
            'update_time' => date('Y-m-d H:i:s')
        ]);

        $this->insert('role', [
            'name' => '普通管理员',
            'description' => '普通管理权限',
            'status' => 1,
            'create_time' => date('Y-m-d H:i:s'),
            'update_time' => date('Y-m-d H:i:s')
        ]);

        // 插入基础权限
        $permissions = [
            ['name' => '系统管理', 'code' => 'system', 'type' => 'menu', 'parent_id' => 0, 'path' => '', 'icon' => 'layui-icon-set', 'sort' => 1],
            ['name' => '管理员管理', 'code' => 'admin', 'type' => 'menu', 'parent_id' => 1, 'path' => '/admin/admin/index', 'icon' => '', 'sort' => 1],
            ['name' => '系统设置', 'code' => 'system_setting', 'type' => 'menu', 'parent_id' => 1, 'path' => '/admin/system_setting/index', 'icon' => '', 'sort' => 2],
            ['name' => '系统配置', 'code' => 'system_config', 'type' => 'menu', 'parent_id' => 1, 'path' => '/admin/system_config/index', 'icon' => '', 'sort' => 3],
            ['name' => '用户管理', 'code' => 'user', 'type' => 'menu', 'parent_id' => 0, 'path' => '', 'icon' => 'layui-icon-user', 'sort' => 2],
            ['name' => '内容管理', 'code' => 'content', 'type' => 'menu', 'parent_id' => 0, 'path' => '', 'icon' => 'layui-icon-file', 'sort' => 3],
        ];

        foreach ($permissions as $permission) {
            $permission['status'] = 1;
            $permission['create_time'] = date('Y-m-d H:i:s');
            $permission['update_time'] = date('Y-m-d H:i:s');
            $this->insert('permission', $permission);
        }

        // 为超级管理员分配所有权限
        $this->insert('admin_role', [
            'admin_id' => 1,
            'role_id' => 1
        ]);

        // 为超级管理员角色分配所有权限
        for ($i = 1; $i <= count($permissions); $i++) {
            $this->insert('role_permission', [
                'role_id' => 1,
                'permission_id' => $i
            ]);
        }
    }

    /**
     * 删除表
     */
    public function down()
    {
        $this->dropTable('admin_log');
        $this->dropTable('role_permission');
        $this->dropTable('admin_role');
        $this->dropTable('permission');
        $this->dropTable('role');
        $this->dropTable('admin');
    }
} 