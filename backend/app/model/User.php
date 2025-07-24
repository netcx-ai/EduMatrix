<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;
use think\facade\Cache;
use think\facade\Log;

class User extends Model
{
    // 设置表名
    protected $name = 'user';
    protected $table = 'edu_user';
    
    // 设置字段信息
    protected $schema = [
        'id'          => 'int',
        'username'    => 'string',
        'password'    => 'string',
        'real_name'   => 'string',
        'phone'       => 'string',
        'email'       => 'string',
        'member_level'=> 'int',      // 会员等级
        'member_expire_time' => 'datetime', // 会员到期时间
        'points'      => 'int',      // 积分
        'status'      => 'int',
        'gender'      => 'int',      // 性别
        'birthday'    => 'date',     // 生日
        'avatar'      => 'string',   // 头像
        'register_ip' => 'string',   // 注册IP
        'last_visit_time' => 'datetime', // 最后访问时间
        'visit_count' => 'int',      // 访问次数
        'create_time' => 'datetime',
        'update_time' => 'datetime',
        'last_password_change' => 'datetime', // 添加最后密码修改时间
        'password_error_count' => 'int',      // 添加密码错误次数
        'password_error_time'  => 'datetime', // 添加密码错误时间
        'user_type'   => 'string',   // 用户类型：member会员，teacher教师，admin管理员，school_admin学校管理员
        'primary_school_id' => 'int', // 主要学校ID
        'teacher_no'  => 'string',   // 教师工号
    ];

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 密码加密
    public function setPasswordAttr($value)
    {
        // 检查密码函数是否可用
        if (!function_exists('password_hash') || !function_exists('password_verify')) {
            throw new \Exception('密码函数不可用，请检查PHP版本和扩展');
        }
        
        // 如果密码为空，返回原密码
        if (empty($value)) {
            return $this->password;
        }
        
        // 确保密码是字符串
        if (!is_string($value)) {
            throw new \Exception('密码必须是字符串类型');
        }
        
        // 基本密码长度检查
        if (strlen($value) < 6) {
            throw new \Exception('密码长度不能少于6位');
        }
        
        // 记录密码修改时间
        $this->last_password_change = date('Y-m-d H:i:s');
        
        return password_hash($value, PASSWORD_DEFAULT);
    }

    // 验证密码
    public function verifyPassword($password)
    {
        // 检查密码函数是否可用
        if (!function_exists('password_verify')) {
            throw new \Exception('密码函数不可用，请检查PHP版本和扩展');
        }
        
        // 确保密码是字符串
        if (!is_string($password)) {
            throw new \Exception('密码必须是字符串类型');
        }
        
        // 检查密码是否为空
        if (empty($this->password)) {
            throw new \Exception('数据库中密码为空');
        }
        
        // 检查输入的密码是否为空
        if (empty($password)) {
            throw new \Exception('输入的密码为空');
        }

        // 检查账号是否被锁定
        if ($this->isAccountLocked()) {
            throw new \Exception('账号已被锁定，请稍后再试');
        }
        
        // 验证密码
        $result = password_verify($password, $this->password);
        
        if (!$result) {
            // 密码错误，增加错误次数
            $this->incrementPasswordError();
            throw new \Exception('密码错误');
        } else {
            // 密码正确，重置错误次数
            $this->resetPasswordError();
        }
        
        return true;
    }

    // 验证密码强度
    protected function validatePasswordStrength($password)
    {
        // 确保密码是字符串
        if (!is_string($password)) {
            return false;
        }
        
        // 密码长度至少8位
        if (strlen($password) < 8) {
            return false;
        }

        // 必须包含大写字母
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }

        // 必须包含小写字母
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }

        // 必须包含数字
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }

        // 必须包含特殊字符
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            return false;
        }

        return true;
    }

    // 增加密码错误次数
    protected function incrementPasswordError()
    {
        $this->password_error_count = $this->password_error_count + 1;
        $this->password_error_time = date('Y-m-d H:i:s');
        $this->save();
        
        // 记录错误日志
        Log::warning("用户 {$this->username} 密码错误，当前错误次数：{$this->password_error_count}");
    }

    // 重置密码错误次数
    protected function resetPasswordError()
    {
        if ($this->password_error_count > 0) {
            $this->password_error_count = 0;
            $this->password_error_time = null;
            $this->save();
        }
    }

    // 检查账号是否被锁定
    public function isAccountLocked()
    {
        // 如果错误次数达到5次，且最后一次错误在30分钟内
        if ($this->password_error_count >= 5) {
            $lastErrorTime = strtotime($this->password_error_time);
            $now = time();
            if ($now - $lastErrorTime < 1800) { // 30分钟 = 1800秒
                return true;
            } else {
                // 超过30分钟，重置错误次数
                $this->resetPasswordError();
            }
        }
        return false;
    }

    // 检查密码是否需要更新
    public function needPasswordUpdate()
    {
        // 如果密码超过90天未修改，建议更新
        if ($this->last_password_change) {
            $lastChange = strtotime($this->last_password_change);
            $now = time();
            return ($now - $lastChange) > (90 * 24 * 3600);
        }
        return false;
    }

    /**
     * 会员等级常量
     */
    const MEMBER_LEVEL_NORMAL = 1;  // 普通会员
    const MEMBER_LEVEL_VIP = 2;     // VIP会员
    const MEMBER_LEVEL_SVIP = 3;    // SVIP会员
    
    /**
     * 性别常量
     */
    const GENDER_UNKNOWN = 0;  // 未知
    const GENDER_MALE = 1;     // 男
    const GENDER_FEMALE = 2;   // 女
    
    /**
     * 获取会员等级文本
     */
    public function getMemberLevelText()
    {
        $levels = [
            self::MEMBER_LEVEL_NORMAL => '普通会员',
            self::MEMBER_LEVEL_VIP => 'VIP会员',
            self::MEMBER_LEVEL_SVIP => 'SVIP会员',
        ];
        
        return $levels[$this->member_level] ?? '未知';
    }
    
    /**
     * 获取性别文本
     */
    public function getGenderText()
    {
        $genders = [
            self::GENDER_UNKNOWN => '未知',
            self::GENDER_MALE => '男',
            self::GENDER_FEMALE => '女',
        ];
        
        return $genders[$this->gender] ?? '未知';
    }
    
    /**
     * 检查是否为VIP会员
     */
    public function isVip()
    {
        return $this->member_level >= self::MEMBER_LEVEL_VIP;
    }
    
    /**
     * 检查会员是否过期
     */
    public function isMemberExpired()
    {
        if (!$this->member_expire_time) {
            return false; // 永久会员
        }
        
        return strtotime($this->member_expire_time) < time();
    }
    
    /**
     * 获取剩余会员天数
     */
    public function getRemainingMemberDays()
    {
        if (!$this->member_expire_time) {
            return -1; // 永久会员
        }
        
        $remaining = strtotime($this->member_expire_time) - time();
        return max(0, ceil($remaining / 86400));
    }
    
    /**
     * 升级会员
     */
    public function upgradeMember($level, $days = null)
    {
        $this->member_level = $level;
        
        if ($days !== null) {
            if ($this->member_expire_time && !$this->isMemberExpired()) {
                // 如果当前会员未过期，在现有基础上延长
                $this->member_expire_time = date('Y-m-d H:i:s', strtotime($this->member_expire_time . " +{$days} days"));
            } else {
                // 从当前时间开始计算
                $this->member_expire_time = date('Y-m-d H:i:s', strtotime("+{$days} days"));
            }
        } else {
            // 永久会员
            $this->member_expire_time = null;
        }
        
        return $this->save();
    }
    
    /**
     * 增加积分
     */
    public function addPoints($points, $reason = '')
    {
        $this->points += $points;
        $this->save();
        
        // 这里可以记录积分变动日志
        // PointsLog::create([
        //     'user_id' => $this->id,
        //     'points' => $points,
        //     'reason' => $reason,
        //     'balance' => $this->points
        // ]);
        
        return $this;
    }
    
    /**
     * 扣除积分
     */
    public function deductPoints($points, $reason = '')
    {
        if ($this->points < $points) {
            throw new \Exception('积分不足');
        }
        
        $this->points -= $points;
        $this->save();
        
        // 这里可以记录积分变动日志
        // PointsLog::create([
        //     'user_id' => $this->id,
        //     'points' => -$points,
        //     'reason' => $reason,
        //     'balance' => $this->points
        // ]);
        
        return $this;
    }
    
    /**
     * 获取年龄
     */
    public function getAge()
    {
        if (!$this->birthday) {
            return null;
        }
        
        $birthDate = new \DateTime($this->birthday);
        $today = new \DateTime();
        $age = $today->diff($birthDate);
        
        return $age->y;
    }

    // ==================== 教师相关方法 ====================
    
    /**
     * 用户类型常量
     */
    const USER_TYPE_MEMBER = 'member';           // 会员
    const USER_TYPE_TEACHER = 'teacher';         // 教师
    const USER_TYPE_ADMIN = 'admin';             // 管理员
    const USER_TYPE_SCHOOL_ADMIN = 'school_admin'; // 学校管理员

    /**
     * 关联教师信息
     */
    public function teacher()
    {
        return $this->hasOne(Teacher::class, 'user_id');
    }

    /**
     * 关联主要学校
     */
    public function primarySchool()
    {
        return $this->belongsTo(School::class, 'primary_school_id');
    }

    /**
     * 获取用户类型文本
     */
    public function getUserTypeTextAttr($value, $data)
    {
        $typeMap = [
            self::USER_TYPE_MEMBER => '会员',
            self::USER_TYPE_TEACHER => '教师',
            self::USER_TYPE_ADMIN => '管理员',
            self::USER_TYPE_SCHOOL_ADMIN => '学校管理员'
        ];
        return $typeMap[$data['user_type']] ?? '未知';
    }

    /**
     * 检查是否为教师
     */
    public function isTeacher()
    {
        return $this->user_type === self::USER_TYPE_TEACHER;
    }

    /**
     * 检查是否为学校管理员
     */
    public function isSchoolAdmin()
    {
        return $this->user_type === self::USER_TYPE_SCHOOL_ADMIN;
    }

    /**
     * 检查是否为平台管理员
     */
    public function isAdmin()
    {
        return $this->user_type === self::USER_TYPE_ADMIN;
    }

    /**
     * 检查是否为普通会员
     */
    public function isMember()
    {
        return $this->user_type === self::USER_TYPE_MEMBER;
    }

    /**
     * 转换为教师
     */
    public function convertToTeacher($schoolId, $teacherNo = null)
    {
        $this->user_type = self::USER_TYPE_TEACHER;
        $this->primary_school_id = $schoolId;
        if ($teacherNo) {
            $this->teacher_no = $teacherNo;
        }
        return $this->save();
    }

    /**
     * 转换为学校管理员
     */
    public function convertToSchoolAdmin($schoolId)
    {
        $this->user_type = self::USER_TYPE_SCHOOL_ADMIN;
        $this->primary_school_id = $schoolId;
        return $this->save();
    }

    /**
     * 转换为平台管理员
     */
    public function convertToAdmin()
    {
        $this->user_type = self::USER_TYPE_ADMIN;
        return $this->save();
    }

    /**
     * 转换为普通会员
     */
    public function convertToMember()
    {
        $this->user_type = self::USER_TYPE_MEMBER;
        $this->primary_school_id = null;
        $this->teacher_no = null;
        return $this->save();
    }

    /**
     * 获取教师信息
     */
    public function getTeacherInfo()
    {
        if (!$this->isTeacher()) {
            return null;
        }
        return $this->teacher;
    }

    /**
     * 获取学校信息
     */
    public function getSchoolInfo()
    {
        if (!$this->primary_school_id) {
            return null;
        }
        return $this->primarySchool;
    }

    /**
     * 检查是否有权限访问指定学校
     */
    public function canAccessSchool($schoolId)
    {
        // 平台管理员可以访问所有学校
        if ($this->isAdmin()) {
            return true;
        }

        // 学校管理员和教师只能访问自己的学校
        if ($this->primary_school_id == $schoolId) {
            return true;
        }

        return false;
    }

    /**
     * 根据用户类型获取用户列表
     */
    public static function getUsersByType($userType, $page = 1, $limit = 20)
    {
        return self::where('user_type', $userType)
            ->order('create_time', 'desc')
            ->paginate([
                'list_rows' => $limit,
                'page' => $page
            ]);
    }

    /**
     * 获取学校的所有教师用户
     */
    public static function getSchoolTeachers($schoolId, $page = 1, $limit = 20)
    {
        return self::where('user_type', self::USER_TYPE_TEACHER)
            ->where('primary_school_id', $schoolId)
            ->with(['teacher', 'primarySchool'])
            ->order('create_time', 'desc')
            ->paginate([
                'list_rows' => $limit,
                'page' => $page
            ]);
    }

    /**
     * 获取学校的所有管理员用户
     */
    public static function getSchoolAdmins($schoolId, $page = 1, $limit = 20)
    {
        return self::where('user_type', self::USER_TYPE_SCHOOL_ADMIN)
            ->where('primary_school_id', $schoolId)
            ->with(['primarySchool'])
            ->order('create_time', 'desc')
            ->paginate([
                'list_rows' => $limit,
                'page' => $page
            ]);
    }
} 