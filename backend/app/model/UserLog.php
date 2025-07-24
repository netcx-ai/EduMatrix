<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

class UserLog extends Model
{
    protected $name = 'user_log';
    
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = false;
    
    // 操作状态常量
    const STATUS_SUCCESS = 1;
    const STATUS_FAILED = 0;
    
    // 用户类型常量（用于login_type字段）
    const USER_TYPE_TEACHER = 'teacher';
    const USER_TYPE_STUDENT = 'student';
    const USER_TYPE_SCHOOL_ADMIN = 'school_admin';
    const USER_TYPE_MEMBER = 'member';
    
    // 操作类型常量（如果需要区分操作类型，可以新增字段）
    const ACTION_LOGIN = 'login';
    const ACTION_REGISTER = 'register';
    const ACTION_RESET_PASSWORD = 'reset_password';
    const ACTION_CHANGE_PASSWORD = 'change_password';
    const ACTION_LOGOUT = 'logout';
    
    // 关联用户
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // 快速创建用户操作日志
    public static function record($userId, $status = self::STATUS_SUCCESS, $failReason = null, $type = self::ACTION_LOGIN)
    {
        try {
            $request = request();
            
            return self::create([
                'user_id' => $userId,
                'login_time' => date('Y-m-d H:i:s'),
                'login_ip' => $request ? $request->ip() : null,
                'login_device' => $request ? $request->header('User-Agent') : null,
                'login_status' => $status,
                'login_type' => $type,
                'fail_reason' => $failReason
            ]);
        } catch (\Exception $e) {
            // 记录日志失败不应该影响主流程
            \think\facade\Log::error('用户操作日志记录失败：' . $e->getMessage());
            return null;
        }
    }
    
    // 记录登录日志（保持向后兼容）
    public static function recordLogin($userId, $status = self::STATUS_SUCCESS, $failReason = null)
    {
        return self::record($userId, $status, $failReason, self::ACTION_LOGIN);
    }
    
    // 记录注册日志
    public static function recordRegister($userId, $status = self::STATUS_SUCCESS, $failReason = null)
    {
        return self::record($userId, $status, $failReason, self::ACTION_REGISTER);
    }
    
    // 记录密码重置日志
    public static function recordResetPassword($userId, $status = self::STATUS_SUCCESS, $failReason = null)
    {
        return self::record($userId, $status, $failReason, self::ACTION_RESET_PASSWORD);
    }
    
    // 获取用户类型
    protected static function getUserType($userId)
    {
        if (!$userId) {
            return null;
        }
        $user = User::find($userId);
        return $user ? $user->user_type : null;
    }
    
    // 获取用户最近操作记录
    public static function getRecentByUser($userId, $limit = 10)
    {
        return self::where('user_id', $userId)
            ->order('login_time', 'desc')
            ->limit($limit)
            ->select();
    }
    
    // 获取操作类型列表
    public static function getActionTypeList()
    {
        return [
            self::ACTION_LOGIN => '登录',
            self::ACTION_REGISTER => '注册',
            self::ACTION_RESET_PASSWORD => '重置密码',
            self::ACTION_CHANGE_PASSWORD => '修改密码',
            self::ACTION_LOGOUT => '退出登录'
        ];
    }
    
    // 获取用户类型列表
    public static function getUserTypeList()
    {
        return [
            self::USER_TYPE_TEACHER => '教师',
            self::USER_TYPE_STUDENT => '学生',
            self::USER_TYPE_SCHOOL_ADMIN => '学校管理员',
            self::USER_TYPE_MEMBER => '普通会员'
        ];
    }
    
    // 获取操作类型文本
    public function getTypeTextAttr($value, $data)
    {
        $types = self::getActionTypeList();
        return isset($types[$data['login_type']]) ? $types[$data['login_type']] : $data['login_type'];
    }
    
    // 获取用户类型文本
    public function getUserTypeText()
    {
        $types = self::getUserTypeList();
        return isset($types[$this->login_type]) ? $types[$this->login_type] : $this->login_type;
    }
} 