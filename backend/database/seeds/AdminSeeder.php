<?php

use Phinx\Seed\AbstractSeed;
use think\facade\Db;

class AdminSeeder extends AbstractSeed
{
    public function run(): void
    {
        // 检查是否已存在超级管理员角色
        $existingRole = Db::name('role')->where('code', 'super_admin')->find();
        if (!$existingRole) {
            // 添加超级管理员角色
            $roleId = Db::name('role')->insertGetId([
                'name' => '超级管理员',
                'code' => 'super_admin',
                'description' => '系统超级管理员',
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s')
            ]);
        } else {
            $roleId = $existingRole['id'];
        }

        // 检查是否已存在管理员权限
        $existingPermission = Db::name('permission')->where('code', 'admin_manage')->find();
        if (!$existingPermission) {
            // 添加管理员权限
            $permissionId = Db::name('permission')->insertGetId([
                'name' => '管理员管理',
                'code' => 'admin_manage',
                'module' => 'admin',
                'description' => '管理员相关操作权限',
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s')
            ]);
        } else {
            $permissionId = $existingPermission['id'];
        }

        // 检查角色权限关联是否已存在
        $existingRolePermission = Db::name('role_permission')
            ->where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->find();
        
        if (!$existingRolePermission) {
            // 关联角色和权限
            Db::name('role_permission')->insert([
                'role_id' => $roleId,
                'permission_id' => $permissionId,
                'create_time' => date('Y-m-d H:i:s')
            ]);
        }

        // 检查是否已存在管理员账号
        $existingAdmin = Db::name('admin')->where('username', 'admin')->find();
        if (!$existingAdmin) {
            // 添加测试管理员账号
            $adminId = Db::name('admin')->insertGetId([
                'username' => 'admin',
                'password' => password_hash('123456', PASSWORD_DEFAULT),
                'real_name' => '系统管理员',
                'phone' => '13800138000',
                'email' => 'admin@example.com',
                'role' => 1,
                'status' => 1,
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s')
            ]);
        } else {
            $adminId = $existingAdmin['id'];
            // 更新密码为默认密码
            Db::name('admin')->where('id', $adminId)->update([
                'password' => password_hash('123456', PASSWORD_DEFAULT),
                'update_time' => date('Y-m-d H:i:s')
            ]);
        }

        // 检查管理员角色关联是否已存在
        $existingAdminRole = Db::name('admin_role')
            ->where('admin_id', $adminId)
            ->where('role_id', $roleId)
            ->find();
        
        if (!$existingAdminRole) {
            // 关联管理员和角色
            Db::name('admin_role')->insert([
                'admin_id' => $adminId,
                'role_id' => $roleId,
                'create_time' => date('Y-m-d H:i:s')
            ]);
        }

        // 检查IP白名单是否已存在
        $existingIp = Db::name('admin_ip_whitelist')->where('ip', '127.0.0.1')->find();
        if (!$existingIp) {
            // 添加IP白名单
            Db::name('admin_ip_whitelist')->insert([
                'ip' => '127.0.0.1',
                'description' => '本地测试',
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s')
            ]);
        }
    }
} 