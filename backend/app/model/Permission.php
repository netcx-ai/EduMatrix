<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;
use think\facade\Cache;

class Permission extends Model
{
    // 设置表名
    protected $name = 'permission';
    
    // 设置字段信息
    protected $schema = [
        'id'          => 'int',
        'name'        => 'string',
        'code'        => 'string',
        'module'      => 'string',
        'description' => 'string',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
    ];

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 关联角色
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission', 'role_id', 'permission_id');
    }

    // 获取子权限
    public function children()
    {
        return $this->hasMany(self::class, 'module', 'module')->where('module', $this->module);
    }

    // 获取父权限
    public function parent()
    {
        return $this->belongsTo(self::class, 'module', 'module');
    }

    // 获取权限树
    public static function getPermissionTree()
    {
        $cacheKey = 'permission_tree';
        return Cache::remember($cacheKey, function() {
            $permissions = self::order('id', 'asc')->select();
            return self::buildTree($permissions);
        }, 3600);
    }

    // 构建树形结构
    protected static function buildTree($permissions, $parentModule = '')
    {
        $tree = [];
        foreach ($permissions as $permission) {
            if ($permission->module == $parentModule) {
                $children = self::buildTree($permissions, $permission->module);
                if ($children) {
                    $permission->children = $children;
                }
                $tree[] = $permission;
            }
        }
        return $tree;
    }

    // 获取所有权限（扁平化）
    public static function getAllPermissions()
    {
        return self::order('id', 'asc')->select();
    }

    // 根据模块获取权限
    public static function getPermissionsByModule($module)
    {
        return self::where('module', $module)
            ->order('id', 'asc')
            ->select();
    }
} 