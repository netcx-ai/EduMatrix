<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;
use think\facade\Cache;

class Role extends Model
{
    // 设置表名（让ThinkPHP自动加前缀）
    protected $name = 'role';
    
    // 设置字段信息
    protected $schema = [
        'id'          => 'int',
        'name'        => 'string',
        'code'        => 'string',
        'description' => 'string',
        'status'      => 'int',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
    ];

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 关联管理员
    public function admins()
    {
        return $this->belongsToMany(Admin::class, 'admin_role', 'role_id', 'admin_id');
    }

    // 关联权限
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission', 'role_id', 'permission_id');
    }

    // 获取角色权限
    public function getPermissions()
    {
        $cacheKey = 'role_permissions:' . $this->id;
        return Cache::remember($cacheKey, function() {
            return $this->permissions()->select();
        }, 3600);
    }

    // 分配权限
    public function assignPermissions($permissionIds)
    {
        $this->permissions()->detach();
        if (!empty($permissionIds)) {
            $this->permissions()->attach($permissionIds);
        }
        // 清除缓存
        Cache::delete('role_permissions:' . $this->id);
    }

    // 检查是否有权限
    public function hasPermission($permissionCode)
    {
        $permissions = $this->getPermissions();
        foreach ($permissions as $permission) {
            if ($permission->code === $permissionCode) {
                return true;
            }
        }
        return false;
    }

    // 获取所有启用角色
    public static function getActiveRoles()
    {
        return self::where('status', 1)->select();
    }
} 