<?php
namespace app\model;

use think\Model;

/**
 * 内容空间模型
 * 用于管理内容在不同空间的分布和权限
 */
class ContentSpace extends Model
{
    protected $name = 'content_space';
    protected $pk = 'id';
    
    // 自动时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    // 字段类型转换
    protected $type = [
        'id' => 'integer',
        'content_id' => 'integer',
        'space_id' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'create_time' => 'datetime',
        'update_time' => 'datetime'
    ];
    
    // 空间类型常量
    const SPACE_TYPE_PERSONAL = 'personal';   // 个人空间
    const SPACE_TYPE_COURSE = 'course';       // 课程空间
    const SPACE_TYPE_SCHOOL = 'school';       // 学校空间
    const SPACE_TYPE_PLATFORM = 'platform';   // 平台空间
    
    // 可见性常量
    const VISIBILITY_PRIVATE = 'private';     // 私有
    const VISIBILITY_LEADER = 'leader';       // 负责人可见
    const VISIBILITY_PUBLIC = 'public';       // 全员可见
    
    // 权限级别常量
    const PERMISSION_READ = 'read';           // 只读
    const PERMISSION_EDIT = 'edit';           // 编辑
    const PERMISSION_ADMIN = 'admin';         // 管理
    
    /**
     * 获取空间类型列表
     */
    public static function getSpaceTypeList()
    {
        return [
            self::SPACE_TYPE_PERSONAL => '个人空间',
            self::SPACE_TYPE_COURSE => '课程空间',
            self::SPACE_TYPE_SCHOOL => '学校空间',
            self::SPACE_TYPE_PLATFORM => '平台空间'
        ];
    }
    
    /**
     * 获取可见性列表
     */
    public static function getVisibilityList()
    {
        return [
            self::VISIBILITY_PRIVATE => '私有',
            self::VISIBILITY_LEADER => '负责人可见',
            self::VISIBILITY_PUBLIC => '全员可见'
        ];
    }
    
    /**
     * 获取权限级别列表
     */
    public static function getPermissionLevelList()
    {
        return [
            self::PERMISSION_READ => '只读',
            self::PERMISSION_EDIT => '编辑',
            self::PERMISSION_ADMIN => '管理'
        ];
    }
    
    /**
     * 获取空间类型名称
     */
    public function getSpaceTypeTextAttr($value, $data)
    {
        $types = self::getSpaceTypeList();
        return isset($types[$data['space_type']]) ? $types[$data['space_type']] : '';
    }
    
    /**
     * 获取可见性名称
     */
    public function getVisibilityTextAttr($value, $data)
    {
        $visibilities = self::getVisibilityList();
        return isset($visibilities[$data['visibility']]) ? $visibilities[$data['visibility']] : '';
    }
    
    /**
     * 获取权限级别名称
     */
    public function getPermissionLevelTextAttr($value, $data)
    {
        $permissions = self::getPermissionLevelList();
        return isset($permissions[$data['permission_level']]) ? $permissions[$data['permission_level']] : '';
    }
    
    /**
     * 关联内容
     */
    public function content()
    {
        return $this->belongsTo(ContentLibrary::class, 'content_id', 'id');
    }
    
    /**
     * 关联课程（如果是课程空间）
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'space_id', 'id');
    }
    
    /**
     * 关联学校（如果是学校空间）
     */
    public function school()
    {
        return $this->belongsTo(School::class, 'space_id', 'id');
    }
    
    /**
     * 添加内容到指定空间
     */
    public static function addToSpace($contentId, $spaceType, $spaceId = null, $userId = null, $visibility = 'private', $permissionLevel = 'read')
    {
        // 检查是否已存在
        $existing = self::where('content_id', $contentId)
                       ->where('space_type', $spaceType)
                       ->where('space_id', $spaceId)
                       ->find();
        
        if ($existing) {
            return $existing;
        }
        
        $space = new self();
        $space->content_id = $contentId;
        $space->space_type = $spaceType;
        $space->space_id = $spaceId;
        $space->visibility = $visibility;
        $space->permission_level = $permissionLevel;
        $space->is_active = 1;
        $space->save();
        
        return $space;
    }
    
    /**
     * 从空间移除内容
     */
    public static function removeFromSpace($contentId, $spaceType, $spaceId = null)
    {
        return self::where('content_id', $contentId)
                  ->where('space_type', $spaceType)
                  ->where('space_id', $spaceId)
                  ->delete();
    }
    
    /**
     * 移动内容到其他空间
     */
    public static function moveContent($contentId, $fromSpaceType, $toSpaceType, $fromSpaceId = null, $toSpaceId = null, $visibility = 'private', $permissionLevel = 'read')
    {
        // 从原空间移除
        self::removeFromSpace($contentId, $fromSpaceType, $fromSpaceId);
        
        // 添加到新空间
        return self::addToSpace($contentId, $toSpaceType, $toSpaceId, null, $visibility, $permissionLevel);
    }
    
    /**
     * 复制内容到其他空间
     */
    public static function copyContent($contentId, $toSpaceType, $toSpaceId = null, $visibility = 'private', $permissionLevel = 'read')
    {
        return self::addToSpace($contentId, $toSpaceType, $toSpaceId, null, $visibility, $permissionLevel);
    }
    
    /**
     * 获取用户在指定空间的内容列表
     */
    public static function getUserContentInSpace($userId, $schoolId, $spaceType, $spaceId = null, $page = 1, $limit = 20)
    {
        $query = self::with(['content.creator', 'content.tags', 'content.statistics'])
                    ->where('space_type', $spaceType)
                    ->where('is_active', 1);
        
        if ($spaceId) {
            $query->where('space_id', $spaceId);
        }
        
        // 根据权限过滤
        $query->where(function($q) use ($userId) {
            $q->where('visibility', self::VISIBILITY_PUBLIC)
              ->whereOr('visibility', self::VISIBILITY_LEADER)
              ->whereOr('permission_level', 'like', '%' . $userId . '%');
        });
        
        return $query->page($page, $limit)
                    ->order('sort_order asc, create_time desc')
                    ->select();
    }
    
    /**
     * 获取课程空间的内容列表
     */
    public static function getCourseContent($courseId, $userId, $schoolId, $page = 1, $limit = 20)
    {
        return self::getUserContentInSpace($userId, $schoolId, self::SPACE_TYPE_COURSE, $courseId, $page, $limit);
    }
    
    /**
     * 获取个人空间的内容列表
     */
    public static function getPersonalContent($userId, $schoolId, $page = 1, $limit = 20)
    {
        return self::getUserContentInSpace($userId, $schoolId, self::SPACE_TYPE_PERSONAL, null, $page, $limit);
    }
    
    /**
     * 检查用户是否有权限访问内容
     */
    public function checkAccess($userId)
    {
        // 公开内容所有人都可以访问
        if ($this->visibility == self::VISIBILITY_PUBLIC) {
            return true;
        }
        
        // 负责人可见的内容需要检查是否为负责人
        if ($this->visibility == self::VISIBILITY_LEADER) {
            return $this->isSpaceLeader($userId);
        }
        
        // 私有内容只有指定用户可以访问
        if ($this->visibility == self::VISIBILITY_PRIVATE) {
            return $this->checkPermissionLevel($userId);
        }
        
        return false;
    }
    
    /**
     * 检查是否为空间负责人
     */
    private function isSpaceLeader($userId)
    {
        switch ($this->space_type) {
            case self::SPACE_TYPE_COURSE:
                // 检查是否为课程负责人
                return Course::isCourseLeader($this->space_id, $userId);
            case self::SPACE_TYPE_SCHOOL:
                // 检查是否为学校管理员
                return SchoolAdmin::isSchoolAdmin($this->space_id, $userId);
            default:
                return false;
        }
    }
    
    /**
     * 检查权限级别
     */
    private function checkPermissionLevel($userId)
    {
        // 这里可以根据permission_level字段的具体实现来判断
        // 暂时返回false，需要根据实际业务逻辑实现
        return false;
    }
    
    /**
     * 更新内容在空间中的可见性
     */
    public function updateVisibility($visibility)
    {
        $this->visibility = $visibility;
        $this->save();
        
        return $this;
    }
    
    /**
     * 更新内容在空间中的权限级别
     */
    public function updatePermissionLevel($permissionLevel)
    {
        $this->permission_level = $permissionLevel;
        $this->save();
        
        return $this;
    }
    
    /**
     * 获取空间统计信息
     */
    public static function getSpaceStatistics($userId, $schoolId)
    {
        $personalCount = self::where('space_type', self::SPACE_TYPE_PERSONAL)
                            ->where('is_active', 1)
                            ->whereExists(function($query) use ($userId) {
                                $query->table('edu_content_library')
                                      ->where('id = edu_content_space.content_id')
                                      ->where('creator_id', $userId);
                            })
                            ->count();
        
        $courseCount = self::where('space_type', self::SPACE_TYPE_COURSE)
                          ->where('is_active', 1)
                          ->whereExists(function($query) use ($userId) {
                              $query->table('edu_content_library')
                                    ->where('id = edu_content_space.content_id')
                                    ->where('creator_id', $userId);
                          })
                          ->count();
        
        return [
            'personal_count' => $personalCount,
            'course_count' => $courseCount,
            'total_count' => $personalCount + $courseCount
        ];
    }
} 