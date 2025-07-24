<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

class College extends Model
{
    // 设置表名
    protected $name = 'college';
    protected $table = 'edu_college';
    
    // 设置字段信息
    protected $schema = [
        'id'              => 'int',
        'school_id'       => 'int',
        'name'            => 'string',
        'code'            => 'string',
        'short_name'      => 'string',
        'description'     => 'string',
        'dean'            => 'string',
        'phone'           => 'string',
        'email'           => 'string',
        'address'         => 'string',
        'teacher_count'   => 'int',
        'student_count'   => 'int',
        'status'          => 'int',
        'sort'            => 'int',
        'create_time'     => 'datetime',
        'update_time'     => 'datetime',
    ];

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 关联学校
    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    // 关联教师
    public function teachers()
    {
        return $this->hasMany(Teacher::class, 'college_id');
    }

    // 获取学院状态文本
    public function getStatusTextAttr($value, $data)
    {
        $statusMap = [
            0 => '禁用',
            1 => '启用'
        ];
        return $statusMap[$data['status']] ?? '未知';
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

    // 获取活跃教师
    public function getActiveTeachers()
    {
        return $this->teachers()->where('status', 1)->select();
    }

    // 根据编码获取学院
    public static function getByCode($schoolId, $code)
    {
        return self::where('school_id', $schoolId)
            ->where('code', $code)
            ->where('status', 1)
            ->find();
    }

    // 获取学院列表（用于下拉选择）
    public static function getCollegeList($schoolId = null)
    {
        $query = self::where('status', 1);
        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }
        return $query->field('id,name,code,short_name,school_id')
            ->order('sort', 'asc')
            ->order('name', 'asc')
            ->select();
    }
} 