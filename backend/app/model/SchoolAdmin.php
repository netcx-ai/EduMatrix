<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;
use think\facade\Cache;

class SchoolAdmin extends Model
{
    // 设置表名
    protected $name = 'school_admin';
    
    // 设置字段信息
    protected $schema = [
        'id'              => 'int',
        'school_id'       => 'int',
        'user_id'         => 'int',      // 新增：关联用户ID
        'username'        => 'string',
        'password'        => 'string',
        'real_name'       => 'string',
        'phone'           => 'string',
        'email'           => 'string',
        'avatar'          => 'string',
        'role'            => 'string',
        'department'      => 'string',
        'position'        => 'string',
        'status'          => 'int',
        'last_login_time' => 'datetime',
        'last_login_ip'   => 'string',
        'login_count'     => 'int',
        'create_time'     => 'datetime',
        'update_time'     => 'datetime',
    ];

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 密码加密
    public function setPasswordAttr($value)
    {
        return password_hash($value, PASSWORD_DEFAULT);
    }

    // 验证密码
    public function verifyPassword($password)
    {
        return password_verify($password, $this->password);
    }

    // 关联学校
    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    // 关联用户（新增）
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // 获取管理员状态文本
    public function getStatusTextAttr($value, $data)
    {
        $statusMap = [
            0 => '禁用',
            1 => '启用'
        ];
        return $statusMap[$data['status']] ?? '未知';
    }

    // 获取角色文本
    public function getRoleTextAttr($value, $data)
    {
        $roleMap = [
            'admin' => '管理员',
            'dean' => '院长',
            'director' => '主任'
        ];
        return $roleMap[$data['role']] ?? '未知';
    }

    // 更新登录信息
    public function updateLoginInfo()
    {
        $this->save([
            'last_login_time' => date('Y-m-d H:i:s'),
            'last_login_ip' => request()->ip(),
            'login_count' => $this->login_count + 1
        ]);
    }

    // 检查是否有权限
    public function hasPermission($permission)
    {
        // 这里可以根据实际需求实现权限检查
        // 暂时返回true
        return true;
    }

    // 根据用户名获取管理员
    public static function getByUsername($schoolId, $username)
    {
        return self::where('school_id', $schoolId)
            ->where('username', $username)
            ->where('status', 1)
            ->find();
    }

    // 根据手机号获取管理员
    public static function getByPhone($schoolId, $phone)
    {
        return self::where('school_id', $schoolId)
            ->where('phone', $phone)
            ->where('status', 1)
            ->find();
    }

    // 根据邮箱获取管理员
    public static function getByEmail($schoolId, $email)
    {
        return self::where('school_id', $schoolId)
            ->where('email', $email)
            ->where('status', 1)
            ->find();
    }

    // 根据用户ID获取管理员
    public static function getByUserId($userId)
    {
        return self::where('user_id', $userId)
            ->where('status', 1)
            ->find();
    }

    // 获取管理员列表
    public static function getAdminList($schoolId, $page = 1, $limit = 20)
    {
        return self::where('school_id', $schoolId)
            ->with(['school', 'user'])
            ->order('create_time', 'desc')
            ->paginate([
                'list_rows' => $limit,
                'page' => $page
            ]);
    }

    // 检查用户名是否已存在
    public static function isUsernameExists($schoolId, $username, $excludeId = null)
    {
        $query = self::where('school_id', $schoolId)->where('username', $username);
        if ($excludeId) {
            $query->where('id', '<>', $excludeId);
        }
        return $query->count() > 0;
    }

    // 检查手机号是否已存在
    public static function isPhoneExists($schoolId, $phone, $excludeId = null)
    {
        $query = self::where('school_id', $schoolId)->where('phone', $phone);
        if ($excludeId) {
            $query->where('id', '<>', $excludeId);
        }
        return $query->count() > 0;
    }

    // 检查邮箱是否已存在
    public static function isEmailExists($schoolId, $email, $excludeId = null)
    {
        $query = self::where('school_id', $schoolId)->where('email', $email);
        if ($excludeId) {
            $query->where('id', '<>', $excludeId);
        }
        return $query->count() > 0;
    }

    // 创建学校管理员（包含用户关联）
    public static function createWithUser($data)
    {
        return \think\facade\Db::transaction(function() use ($data) {
            // 1. 创建用户记录
            $user = new User();
            $user->username = $data['username'];
            $user->real_name = $data['real_name'];
            $user->phone = $data['phone'] ?? '';
            $user->email = $data['email'] ?? '';
            $user->password = $data['password'];
            $user->user_type = User::USER_TYPE_SCHOOL_ADMIN;
            $user->primary_school_id = $data['school_id'];
            $user->status = 1;
            $user->save();

            // 2. 创建学校管理员记录
            $admin = new self();
            $admin->school_id = $data['school_id'];
            $admin->user_id = $user->id;
            $admin->username = $data['username'];
            $admin->password = $user->password; // 使用加密后的密码
            $admin->real_name = $data['real_name'];
            $admin->phone = $data['phone'] ?? '';
            $admin->email = $data['email'] ?? '';
            $admin->role = $data['role'] ?? 'admin';
            $admin->department = $data['department'] ?? '';
            $admin->position = $data['position'] ?? '';
            $admin->status = 1;
            $admin->save();

            return $admin;
        });
    }
} 