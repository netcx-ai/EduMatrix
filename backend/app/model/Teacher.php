<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

class Teacher extends Model
{
    // 设置表名
    protected $name = 'teacher';
    
    /**
     * 审核状态映射
     */
    const AUDIT_STATUS_MAP = [
        0 => 'rejected',  // 已拒绝
        1 => 'approved',  // 已通过
        2 => 'pending'    // 待审核
    ];

    /**
     * 启用状态映射
     */
    const ACTIVE_STATUS_MAP = [
        0 => 'inactive',  // 禁用
        1 => 'active',    // 启用
    ];
    
    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'school_id'         => 'int',
        'college_id'        => 'int',
        'user_id'           => 'int',
        'teacher_no'        => 'string',
        'real_name'         => 'string',
        'phone'             => 'string',
        'email'             => 'string',
        'avatar'            => 'string',
        'gender'            => 'int',
        'birthday'          => 'date',
        'title'             => 'string',
        'department'        => 'string',
        'position'          => 'string',
        'education'         => 'string',
        'major'             => 'string',
        'hire_date'         => 'date',
        'work_years'        => 'int',
        'teaching_subject'  => 'string',
        'research_direction'=> 'string',
        'status'            => 'int',
        'is_verified'       => 'int',
        'verified_time'     => 'datetime',
        'verified_by'       => 'int',
        'last_login_time'   => 'datetime',
        'create_time'       => 'datetime',
        'update_time'       => 'datetime',
    ];

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 关联学校
    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    // 关联学院
    public function college()
    {
        return $this->belongsTo(College::class, 'college_id');
    }

    // 关联用户
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // 关联认证人
    public function verifier()
    {
        return $this->belongsTo(SchoolAdmin::class, 'verified_by');
    }

    // 获取教师状态文本
    public function getStatusTextAttr($value, $data)
    {
        $statusMap = [
            0 => '禁用',
            1 => '启用',
            2 => '待审核'
        ];
        return $statusMap[$data['status']] ?? '未知';
    }

    // 获取认证状态文本
    public function getVerifiedTextAttr($value, $data)
    {
        $verifiedMap = [
            0 => '未认证',
            1 => '已认证'
        ];
        return $verifiedMap[$data['is_verified']] ?? '未知';
    }

    // 检查是否已认证
    public function isVerified()
    {
        return $this->is_verified == 1;
    }

    // 检查是否待审核
    public function isPending()
    {
        return $this->status == 2;
    }

    // 检查是否启用
    public function isActive()
    {
        return $this->status == 1;
    }

    // 更新登录时间
    public function updateLoginTime()
    {
        $this->save(['last_login_time' => date('Y-m-d H:i:s')]);
    }

    // 认证教师
    public function verify($adminId)
    {
        $this->save([
            'is_verified' => 1,
            'verified_time' => date('Y-m-d H:i:s'),
            'verified_by' => $adminId,
            'status' => 1
        ]);
    }

    // 拒绝认证
    public function reject($adminId, $reason = '')
    {
        $this->save([
            'is_verified' => 0,
            'verified_time' => date('Y-m-d H:i:s'),
            'verified_by' => $adminId,
            'status' => 0
        ]);
    }

    // 根据工号获取教师
    public static function getByTeacherNo($schoolId, $teacherNo)
    {
        return self::where('school_id', $schoolId)
            ->where('teacher_no', $teacherNo)
            ->find();
    }

    // 根据用户ID获取教师
    public static function getByUserId($userId)
    {
        return self::where('user_id', $userId)->find();
    }

    // 获取教师列表
    public static function getTeacherList($schoolId, $collegeId = null, $status = null, $page = 1, $limit = 20)
    {
        $query = self::where('school_id', $schoolId);
        
        if ($collegeId) {
            $query->where('college_id', $collegeId);
        }
        
        if ($status !== null) {
            $query->where('status', $status);
        }
        
        return $query->with(['school', 'college', 'user'])
            ->order('create_time', 'desc')
            ->paginate([
                'list_rows' => $limit,
                'page' => $page
            ]);
    }

    // 获取待审核教师列表
    public static function getPendingTeachers($schoolId, $page = 1, $limit = 20)
    {
        return self::where('school_id', $schoolId)
            ->where('status', 2)
            ->with(['school', 'college', 'user'])
            ->order('create_time', 'asc')
            ->paginate([
                'list_rows' => $limit,
                'page' => $page
            ]);
    }

    // 检查工号是否已存在
    public static function isTeacherNoExists($schoolId, $teacherNo, $excludeId = null)
    {
        $query = self::where('school_id', $schoolId)->where('teacher_no', $teacherNo);
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

    // 统计教师数量
    public static function countTeachers($schoolId, $collegeId = null, $status = null)
    {
        $query = self::where('school_id', $schoolId);
        
        if ($collegeId) {
            $query->where('college_id', $collegeId);
        }
        
        if ($status !== null) {
            $query->where('status', $status);
        }
        
        return $query->count();
    }

    // 获取教师统计信息
    public static function getTeacherStats($schoolId)
    {
        $total = self::where('school_id', $schoolId)->count();
        $active = self::where('school_id', $schoolId)->where('status', 1)->count();
        $pending = self::where('school_id', $schoolId)->where('status', 2)->count();
        $inactive = self::where('school_id', $schoolId)->where('status', 0)->count();
        
        return [
            'total' => $total,
            'active' => $active,
            'pending' => $pending,
            'inactive' => $inactive
        ];
    }

    /**
     * 获取审核状态名称
     * 
     * @param int $status 状态值
     * @return string 状态名称
     */
    public static function getAuditStatusName($status)
    {
        $status = (int)$status;
        return self::AUDIT_STATUS_MAP[$status] ?? 'unknown';
    }

    /**
     * 获取启用状态名称
     * 
     * @param int $status 状态值
     * @return string 状态名称
     */
    public static function getActiveStatusName($status)
    {
        $status = (int)$status;
        return self::ACTIVE_STATUS_MAP[$status] ?? 'unknown';
    }

    /**
     * 规范化状态值
     * 
     * @param string|int $status 前端传入的状态值
     * @return int 数据库状态值
     */
    public static function normalizeStatus($status)
    {
        if (is_numeric($status)) {
            return (int)$status;
        }
        switch ($status) {
            case 'active':
                return 1;
            case 'inactive':
                return 0;
            case 'pending':
                return 2;
            case 'approved':
                return 1;
            case 'rejected':
                return 0;
            default:
                return 1; // 默认启用
        }
    }
} 