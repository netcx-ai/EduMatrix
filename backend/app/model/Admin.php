<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;
use think\facade\Cache;
use think\facade\Db;
use app\model\Role;

class Admin extends Model
{
    // 设置表名（让ThinkPHP自动加前缀）
    protected $name = 'admin';
    
    // 设置字段信息
    protected $schema = [
        'id'              => 'int',
        'username'        => 'string',
        'password'        => 'string',
        'real_name'       => 'string',
        'phone'          => 'string',
        'email'          => 'string',
        'avatar'         => 'string',
        'role'           => 'int',
        'status'         => 'int',
        'last_login_time' => 'datetime',
        'last_login_ip'   => 'string',
        'create_time'     => 'datetime',
        'update_time'     => 'datetime',
    ];

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 密码加密
    public function setPasswordAttr($value)
    {
        if (!function_exists('password_hash')) {
            throw new \Exception('密码函数不可用，请检查PHP版本和扩展');
        }
        return password_hash($value, PASSWORD_DEFAULT);
    }

    // 验证密码
    public function verifyPassword($password)
    {
        if (!function_exists('password_verify')) {
            throw new \Exception('密码函数不可用，请检查PHP版本和扩展');
        }
        return password_verify($password, $this->password);
    }

    // 更新登录信息
    public function updateLoginInfo()
    {
        $this->save([
            'last_login_time' => date('Y-m-d H:i:s'),
            'last_login_ip' => request()->ip()
        ]);
    }

    // 关联角色
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'admin_role', 'role_id', 'admin_id');
    }

    // 关联文章
    public function articles()
    {
        return $this->hasMany(\app\model\Article::class, 'author_id');
    }

    // 获取所有权限
    public function getPermissions()
    {
        $cacheKey = 'admin_permissions:' . $this->id;
        return Cache::remember($cacheKey, function() {
            $permissions = [];
            foreach ($this->roles as $role) {
                foreach ($role->permissions as $permission) {
                    $permissions[$permission->code] = $permission;
                }
            }
            return $permissions;
        }, 3600);
    }

    // 检查是否有权限
    public function hasPermission($permissionCode)
    {
        $permissions = $this->getPermissions();
        return isset($permissions[$permissionCode]);
    }

    // 检查IP是否在白名单中
    public static function checkIpWhitelist($ip)
    {
        $cacheKey = 'admin_ip_whitelist';
        $whitelist = Cache::remember($cacheKey, function() {
            return Db::name('admin_ip_whitelist')
                ->where('status', 1)
                ->column('ip');
        }, 3600);

        return in_array($ip, $whitelist);
    }

    // 检查登录失败次数
    public static function checkLoginAttempts($username)
    {
        $cacheKey = 'admin_login_attempts:' . $username;
        $attempts = Cache::get($cacheKey, 0);
        
        if ($attempts >= 5) {
            throw new \Exception('登录失败次数过多，请15分钟后再试');
        }
        
        return true;
    }

    // 增加登录失败次数
    public static function increaseLoginAttempts($username)
    {
        $cacheKey = 'admin_login_attempts:' . $username;
        $attempts = Cache::get($cacheKey, 0);
        Cache::set($cacheKey, $attempts + 1, 900); // 15分钟过期
    }

    // 清除登录失败次数
    public static function clearLoginAttempts($username)
    {
        $cacheKey = 'admin_login_attempts:' . $username;
        Cache::delete($cacheKey);
    }
} 