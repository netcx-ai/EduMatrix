<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 课程模型
 */
class Course extends Model
{
    protected $name = 'course';    
    // 设置字段信息
    protected $schema = [
        'id' => 'int',
        'name' => 'string',
        'description' => 'text',
        'teacher_id' => 'int',
        'price' => 'decimal',
        'status' => 'int',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
        'responsible_teacher_id' => 'int',
        'school_id' => 'int',
        'course_code' => 'string',
        'college_id' => 'int',
        'credits' => 'decimal',
        'hours' => 'int',
        'semester' => 'string',
        'academic_year' => 'string',
        'is_public' => 'int',
        'sort' => 'int',
        'view_count' => 'int',
        'create_count' => 'int',
    ];
    
    // 自动时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    // 类型转换
    protected $type = [
        'id' => 'integer',
        'teacher_id' => 'integer',
        'price' => 'float',
        'status' => 'integer',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
        'responsible_teacher_id' => 'integer',
        'school_id' => 'integer',
        'college_id' => 'integer',
        'credits' => 'float',
        'hours' => 'integer',
        'is_public' => 'integer',
        'sort' => 'integer',
        'view_count' => 'integer',
        'create_count' => 'integer',
    ];
    
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
    
    // 关联负责教师
    public function responsibleTeacher()
    {
        return $this->belongsTo(Teacher::class, 'responsible_teacher_id');
    }
    
    // 关联教师（多对多）
    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'course_teacher', 'course_id', 'teacher_id');
    }
    
    // 关联内容库（替换原来的contents关联）
    public function contents()
    {
        return $this->hasMany(ContentLibrary::class, 'course_id');
    }
    
    // 关联标签（多对多）
    public function tags()
    {
        return $this->belongsToMany(CourseTag::class, 'course_tag_relation', 'course_id', 'tag_id');
    }
    
    /**
     * 获取课程列表
     */
    public static function getCourseList($schoolId = null, $status = 1)
    {
        $query = self::where('status', $status);
        
        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }
        
        return $query->order('sort', 'desc')->order('name', 'asc')->select();
    }
    
    /**
     * 获取课程选项
     */
    public static function getCourseOptions($schoolId = null, $status = 1)
    {
        $courses = self::getCourseList($schoolId, $status);
        $options = [];
        
        foreach ($courses as $course) {
            $options[] = [
                'value' => $course->id,
                'text' => $course->name
            ];
        }
        
        return $options;
    }
    
    /**
     * 检查课程编码是否已存在
     */
    public static function isCodeExists($schoolId, $code, $excludeId = null)
    {
        $query = self::where('school_id', $schoolId)->where('course_code', $code);
        if ($excludeId) {
            $query->where('id', '<>', $excludeId);
        }
        return $query->count() > 0;
    }

    /**
     * 检查课程名称是否已存在
     */
    public static function isNameExists($schoolId, $name, $excludeId = null)
    {
        $query = self::where('school_id', $schoolId)->where('name', $name);
        if ($excludeId) {
            $query->where('id', '<>', $excludeId);
        }
        return $query->count() > 0;
    }
    
    /**
     * 更新课程内容数量
     */
    public function updateContentCount()
    {
        $count = ContentLibrary::where('course_id', $this->id)->count();
        $this->create_count = $count;
        $this->save();
    }
    
    /**
     * 增加浏览次数
     */
    public function incrementViewCount()
    {
        $this->view_count = $this->view_count + 1;
        $this->save();
    }
} 
 
 