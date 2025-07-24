<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 内容审核模型
 */
class ContentAudit extends Model
{
    protected $name = 'content_audit';
    
    // 自动时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    // 类型转换
    protected $type = [
        'id' => 'integer',
        'school_id' => 'integer',
        'file_id' => 'integer',
        'course_id' => 'integer',
        'submitter_id' => 'integer',
        'reviewer_id' => 'integer',
        'priority' => 'integer',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
        'review_time' => 'datetime',
    ];

    // 审核状态常量
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    
    // 内容类型常量
    const TYPE_FILE = 'file';
    const TYPE_COURSE = 'course';
    
    // 优先级常量
    const PRIORITY_NORMAL = 0;
    const PRIORITY_IMPORTANT = 1;
    const PRIORITY_URGENT = 2;
    
    // 关联学校
    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }
    
    // 关联文件
    public function file()
    {
        return $this->belongsTo(File::class, 'file_id');
    }
    
    // 关联课程
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
    
    // 关联提交者（教师）
    public function submitter()
    {
        return $this->belongsTo(Teacher::class, 'submitter_id');
    }
    
    // 关联审核者（学校管理员）
    public function reviewer()
    {
        return $this->belongsTo(SchoolAdmin::class, 'reviewer_id');
    }
    
    /**
     * 获取审核状态列表
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_PENDING => '待审核',
            self::STATUS_APPROVED => '已通过',
            self::STATUS_REJECTED => '已驳回',
        ];
    }
    
    /**
     * 获取内容类型列表
     */
    public static function getTypeList()
    {
        return [
            self::TYPE_FILE => '文件',
            self::TYPE_COURSE => '课程',
        ];
    }
    
    /**
     * 获取优先级列表
     */
    public static function getPriorityList()
    {
        return [
            self::PRIORITY_NORMAL => '普通',
            self::PRIORITY_IMPORTANT => '重要',
            self::PRIORITY_URGENT => '紧急',
        ];
    }
    
    /**
     * 获取状态文本
     */
    public function getStatusTextAttr($value, $data)
    {
        $statusList = self::getStatusList();
        return $statusList[$data['status']] ?? '';
    }
    
    /**
     * 获取类型文本
     */
    public function getTypeTextAttr($value, $data)
    {
        $typeList = self::getTypeList();
        return $typeList[$data['content_type']] ?? '';
    }
    
    /**
     * 获取优先级文本
     */
    public function getPriorityTextAttr($value, $data)
    {
        $priorityList = self::getPriorityList();
        return $priorityList[$data['priority']] ?? '';
    }
    
    /**
     * 审核通过
     */
    public function approve($reviewerId, $remark = '')
    {
        $this->status = self::STATUS_APPROVED;
        $this->reviewer_id = $reviewerId;
        $this->review_time = date('Y-m-d H:i:s');
        $this->review_remark = $remark;
        return $this->save();
    }
    
    /**
     * 审核驳回
     */
    public function reject($reviewerId, $remark = '')
    {
        $this->status = self::STATUS_REJECTED;
        $this->reviewer_id = $reviewerId;
        $this->review_time = date('Y-m-d H:i:s');
        $this->review_remark = $remark;
        return $this->save();
    }
    
    /**
     * 创建文件审核记录
     */
    public static function createFileAudit($file, $submitterId)
    {
        $audit = new self();
        $audit->school_id = $file->school_id;
        $audit->file_id = $file->id;
        $audit->course_id = $file->course_id;
        $audit->content_type = self::TYPE_FILE;
        $audit->content_title = $file->file_name;
        $audit->content_description = '文件审核：' . $file->original_name;
        $audit->submitter_id = $submitterId;
        $audit->submitter_type = 'teacher';
        $audit->status = self::STATUS_PENDING;
        $audit->priority = self::PRIORITY_NORMAL;
        
        return $audit->save() ? $audit : false;
    }
    
    /**
     * 创建课程审核记录
     */
    public static function createCourseAudit($course, $submitterId)
    {
        $audit = new self();
        $audit->school_id = $course->school_id;
        $audit->course_id = $course->id;
        $audit->content_type = self::TYPE_COURSE;
        $audit->content_title = $course->course_name;
        $audit->content_description = '课程审核：' . $course->course_code . ' - ' . $course->course_name;
        $audit->submitter_id = $submitterId;
        $audit->submitter_type = 'teacher';
        $audit->status = self::STATUS_PENDING;
        $audit->priority = self::PRIORITY_NORMAL;
        
        return $audit->save() ? $audit : false;
    }
} 