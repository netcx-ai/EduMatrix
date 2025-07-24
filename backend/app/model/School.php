<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;
use think\facade\Cache;
use app\model\College;
use app\model\Teacher;
use app\model\SchoolAdmin;

class School extends Model
{
    // 设置表名
    protected $name = 'school';
    
    // 设置字段信息
    protected $schema = [
        'id'              => 'int',
        'name'            => 'string',
        'code'            => 'string',
        'short_name'      => 'string',
        'description'     => 'string',
        'logo'            => 'string',
        'website'         => 'string',
        'address'         => 'string',
        'phone'           => 'string',
        'email'           => 'string',
        'contact_person'  => 'string',
        'contact_phone'   => 'string',
        'province'        => 'string',
        'city'            => 'string',
        'district'        => 'string',
        'school_type'     => 'int',
        'student_count'   => 'int',
        'teacher_count'   => 'int',
        'status'          => 'int',
        'expire_time'     => 'datetime',
        'max_teacher_count' => 'int',
        'max_student_count' => 'int',
        'create_time'     => 'datetime',
        'update_time'     => 'datetime',
    ];

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 关联学院
    public function colleges()
    {
        return $this->hasMany(College::class, 'school_id');
    }

    // 关联学校管理员
    public function admins()
    {
        return $this->hasMany(SchoolAdmin::class, 'school_id');
    }

    // 关联教师
    public function teachers()
    {
        return $this->hasMany(Teacher::class, 'school_id');
    }

    // 获取学校状态文本
    public function getStatusTextAttr($value, $data)
    {
        $statusMap = [
            0 => '禁用',
            1 => '启用',
            2 => '待审核'
        ];
        return $statusMap[$data['status']] ?? '未知';
    }

    // 获取学校类型文本
    public function getSchoolTypeTextAttr($value, $data)
    {
        $typeMap = [
            1 => '大学',
            2 => '学院',
            3 => '专科',
            4 => '中学',
            5 => '小学'
        ];
        return $typeMap[$data['school_type']] ?? '未知';
    }

    // 检查学校是否过期
    public function isExpired()
    {
        if (empty($this->expire_time)) {
            return false;
        }
        return strtotime($this->expire_time) < time();
    }

    // 检查是否达到教师数量限制
    public function isTeacherLimitReached()
    {
        return $this->teacher_count >= $this->max_teacher_count;
    }

    // 检查是否达到学生数量限制
    public function isStudentLimitReached()
    {
        return $this->student_count >= $this->max_student_count;
    }

    // 更新教师数量
    public function updateTeacherCount()
    {
        $count = $this->teachers()->where('status', 1)->count();
        $this->save(['teacher_count' => $count]);
    }

    // 更新学生数量
    public function updateStudentCount()
    {
        // 这里可以根据实际需求实现学生数量统计
        // 暂时返回当前值
        return $this->student_count;
    }

    // 获取活跃学院
    public function getActiveColleges()
    {
        return $this->colleges()->where('status', 1)->order('sort', 'asc')->select();
    }

    // 获取活跃教师
    public function getActiveTeachers()
    {
        return $this->teachers()->where('status', 1)->select();
    }

    // 根据编码获取学校
    public static function getByCode($code)
    {
        return self::where('code', $code)->where('status', 1)->find();
    }

    // 获取学校列表（用于下拉选择）
    public static function getSchoolList()
    {
        return self::where('status', 1)
            ->field('id,name,code,short_name')
            ->order('name', 'asc')
            ->select();
    }
} 