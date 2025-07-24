<?php
namespace app\model;

use think\Model;

/**
 * 内容库主模型
 * 设计理念：内容库为总集合，空间为分类/视图
 */
class ContentLibrary extends Model
{
    protected $name = 'content_library';
    protected $pk = 'id';
    
    // 自动时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    // 允许写入的字段
    protected $field = [
        'name',
        'content',
        'course_id',
        'source_type',
        'ai_tool_code',
        'creator_id',
        'school_id',
        'college_id',
        'status',
        'audit_user_id',
        'audit_time',
        'audit_remark',
        'is_deleted',
        'create_time',
        'update_time'
    ];
    
    // 字段类型转换
    protected $type = [
        'id' => 'integer',
        'course_id' => 'integer',
        'creator_id' => 'integer',
        'school_id' => 'integer',
        'college_id' => 'integer',
        'audit_user_id' => 'integer',
        'audit_time' => 'datetime',
        'is_deleted' => 'integer',
        'ai_tool_code' => 'string',
        'create_time' => 'datetime',
        'update_time' => 'datetime'
    ];
    
    // 来源类型常量
    const SOURCE_TYPE_UPLOAD = 'upload';       // 上传
    const SOURCE_TYPE_AI_GENERATE = 'ai_generate'; // AI生成
    
    // 状态常量
    const STATUS_DRAFT = 'draft';       // 草稿
    const STATUS_PENDING = 'pending';   // 待审核
    const STATUS_APPROVED = 'approved'; // 已通过
    const STATUS_REJECTED = 'rejected'; // 已驳回
    
    // 文件类型常量
    const FILE_TYPE_TEXT = 'text';         // 文本
    const FILE_TYPE_DOCUMENT = 'document'; // 文档
    const FILE_TYPE_IMAGE = 'image';       // 图片
    const FILE_TYPE_VIDEO = 'video';       // 视频
    const FILE_TYPE_AUDIO = 'audio';       // 音频
    
    /**
     * 获取来源类型列表
     */
    public static function getSourceTypeList()
    {
        return [
            self::SOURCE_TYPE_UPLOAD => '文件上传',
            self::SOURCE_TYPE_AI_GENERATE => 'AI生成'
        ];
    }
    
    /**
     * 获取状态列表
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_DRAFT => '草稿',
            self::STATUS_PENDING => '待审核',
            self::STATUS_APPROVED => '已通过',
            self::STATUS_REJECTED => '已驳回'
        ];
    }
    
    /**
     * 获取文件类型列表
     */
    public static function getFileTypeList()
    {
        return [
            self::FILE_TYPE_TEXT => '文本',
            self::FILE_TYPE_DOCUMENT => '文档',
            self::FILE_TYPE_IMAGE => '图片',
            self::FILE_TYPE_VIDEO => '视频',
            self::FILE_TYPE_AUDIO => '音频'
        ];
    }
    
    /**
     * 获取来源类型名称
     */
    public function getSourceTypeTextAttr($value, $data)
    {
        $types = self::getSourceTypeList();
        return isset($types[$data['source_type']]) ? $types[$data['source_type']] : '';
    }
    
    /**
     * 获取状态名称
     */
    public function getStatusTextAttr($value, $data)
    {
        $statuses = self::getStatusList();
        return isset($statuses[$data['status']]) ? $statuses[$data['status']] : '';
    }
    
    /**
     * 关联创建用户
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id', 'id');
    }
    
    /**
     * 关联学校
     */
    public function school()
    {
        return $this->belongsTo(School::class, 'school_id', 'id');
    }
    
    /**
     * 关联学院
     */
    public function college()
    {
        return $this->belongsTo(College::class, 'college_id', 'id');
    }
    
    /**
     * 关联课程
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'id');
    }
    
    /**
     * 关联文件（一个内容多个文件）
     */
    public function files()
    {
        return $this->hasMany(File::class, 'content_id', 'id');
    }
    
    /**
     * 关联审核用户
     */
    public function auditUser()
    {
        return $this->belongsTo(User::class, 'audit_user_id', 'id');
    }
    
    /**
     * 关联内容空间
     */
    public function spaces()
    {
        return $this->hasMany(ContentSpace::class, 'content_id', 'id');
    }
    
    /**
     * 关联内容标签
     */
    public function tags()
    {
        return $this->belongsToMany(ContentTag::class, 'edu_content_tag_relation', 'content_id', 'tag_id');
    }
    
    /**
     * 关联内容分享
     */
    public function shares()
    {
        return $this->hasMany(ContentShare::class, 'content_id', 'id');
    }
    
    /**
     * 关联内容日志
     */
    public function logs()
    {
        return $this->hasMany(ContentLog::class, 'content_id', 'id');
    }
    
    /**
     * 关联内容统计
     */
    public function statistics()
    {
        return $this->hasOne(ContentStatistics::class, 'content_id', 'id');
    }
    
    /**
     * 格式化文件大小
     */
    public function formatFileSize($size)
    {
        if ($size < 1024) {
            return $size . ' B';
        } elseif ($size < 1024 * 1024) {
            return round($size / 1024, 2) . ' KB';
        } elseif ($size < 1024 * 1024 * 1024) {
            return round($size / (1024 * 1024), 2) . ' MB';
        } else {
            return round($size / (1024 * 1024 * 1024), 2) . ' GB';
        }
    }
    
    /**
     * 获取内容列表
     */
    public static function getContentList($params = [])
    {
        $query = self::where('is_deleted', 0);
        
        // 应用筛选条件
        self::applyContentFilters($query, $params);
        
        // 排序
        $orderBy = $params['order_by'] ?? 'create_time';
        $orderDir = $params['order_dir'] ?? 'desc';
        $query->order($orderBy, $orderDir);
        
        // 分页
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 20;
        
        return $query->with(['creator', 'school', 'college', 'course', 'files'])->paginate([
            'list_rows' => $limit,
            'page' => $page
        ]);
    }
    
    /**
     * 获取用户可访问的内容
     */
    public static function getUserAccessibleContent($userId, $schoolId, $params = [])
    {
        $query = self::where('is_deleted', 0)
                    ->where('school_id', $schoolId)
                    ->where(function($q) use ($userId) {
                        $q->where('creator_id', $userId)
                          ->whereOr('status', self::STATUS_APPROVED);
                    });
        
        // 应用筛选条件
        self::applyContentFilters($query, $params);
        
        // 排序
        $orderBy = $params['order_by'] ?? 'create_time';
        $orderDir = $params['order_dir'] ?? 'desc';
        $query->order($orderBy, $orderDir);
        
        // 分页
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 20;
        
        return $query->with(['creator', 'school', 'college', 'course', 'files'])->paginate([
            'list_rows' => $limit,
            'page' => $page
        ]);
    }
    
    /**
     * 应用内容筛选条件
     */
    private static function applyContentFilters($query, $params)
    {
        // 按名称搜索
        if (!empty($params['name'])) {
            $query->where('name', 'like', '%' . $params['name'] . '%');
        }
        
        // 按来源类型筛选
        if (!empty($params['source_type'])) {
            $query->where('source_type', $params['source_type']);
        }
        
        // 按状态筛选
        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }
        
        // 按创建者筛选
        if (!empty($params['creator_id'])) {
            $query->where('creator_id', $params['creator_id']);
        }
        
        // 按学院筛选
        if (!empty($params['college_id'])) {
            $query->where('college_id', $params['college_id']);
        }
        
        // 按课程筛选
        if (!empty($params['course_id'])) {
            $query->where('course_id', $params['course_id']);
        }
        
        // 按AI工具筛选
        if (!empty($params['ai_tool_code'])) {
            $query->where('ai_tool_code', $params['ai_tool_code']);
        }
    }
    
    /**
     * 从AI生成创建内容
     */
    public static function createFromAi($name, $contentText, $aiToolCode, $userId, $schoolId, $collegeId = null, $courseId = null)
    {
        $content = new self();
        $content->name = $name;
        $content->content = $contentText;
        $content->source_type = self::SOURCE_TYPE_AI_GENERATE;
        $content->ai_tool_code = $aiToolCode;
        $content->creator_id = $userId;
        $content->school_id = $schoolId;
        $content->college_id = $collegeId;
        $content->course_id = $courseId;
        $content->status = self::STATUS_DRAFT;
        $content->is_deleted = 0;
        
        if ($content->save()) {
            // 更新课程内容数量
            if ($courseId) {
                $course = Course::find($courseId);
                if ($course) {
                    $course->updateContentCount();
                }
            }
            return $content;
        }
        
        return false;
    }
    
    /**
     * 提交审核
     */
    public function submitForAudit()
    {
        $this->status = self::STATUS_PENDING;
        return $this->save();
    }
    
    /**
     * 审核通过
     */
    public function approve($auditUserId, $remark = '')
    {
        $this->status = self::STATUS_APPROVED;
        $this->audit_user_id = $auditUserId;
        $this->audit_time = date('Y-m-d H:i:s');
        $this->audit_remark = $remark;
        
        return $this->save();
    }
    
    /**
     * 审核驳回
     */
    public function reject($auditUserId, $remark = '')
    {
        $this->status = self::STATUS_REJECTED;
        $this->audit_user_id = $auditUserId;
        $this->audit_time = date('Y-m-d H:i:s');
        $this->audit_remark = $remark;
        
        return $this->save();
    }
    
    /**
     * 获取内容统计
     */
    public static function getContentStatistics($schoolId)
    {
        $stats = [
            'total' => self::where('school_id', $schoolId)->where('is_deleted', 0)->count(),
            'draft' => self::where('school_id', $schoolId)->where('status', self::STATUS_DRAFT)->where('is_deleted', 0)->count(),
            'pending' => self::where('school_id', $schoolId)->where('status', self::STATUS_PENDING)->where('is_deleted', 0)->count(),
            'approved' => self::where('school_id', $schoolId)->where('status', self::STATUS_APPROVED)->where('is_deleted', 0)->count(),
            'rejected' => self::where('school_id', $schoolId)->where('status', self::STATUS_REJECTED)->where('is_deleted', 0)->count(),
            'ai_generated' => self::where('school_id', $schoolId)->where('source_type', self::SOURCE_TYPE_AI_GENERATE)->where('is_deleted', 0)->count(),
        ];
        
        return $stats;
    }
} 