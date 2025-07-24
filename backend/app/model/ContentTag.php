<?php
namespace app\model;

use think\Model;

/**
 * 内容标签模型
 */
class ContentTag extends Model
{
    protected $name = 'content_tag';
    protected $pk = 'id';
    
    // 自动时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    // 字段类型转换
    protected $type = [
        'id' => 'integer',
        'school_id' => 'integer',
        'usage_count' => 'integer',
        'is_active' => 'boolean',
        'create_time' => 'datetime',
        'update_time' => 'datetime'
    ];
    
    /**
     * 关联学校
     */
    public function school()
    {
        return $this->belongsTo(School::class, 'school_id', 'id');
    }
    
    /**
     * 关联内容（多对多）
     */
    public function contents()
    {
        return $this->belongsToMany(ContentLibrary::class, 'edu_content_tag_relation', 'tag_id', 'content_id');
    }
    
    /**
     * 获取标签列表
     */
    public static function getTagList($schoolId, $page = 1, $limit = 20)
    {
        return self::where('school_id', $schoolId)
                  ->where('is_active', 1)
                  ->page($page, $limit)
                  ->order('usage_count desc, create_time desc')
                  ->select();
    }
    
    /**
     * 创建标签
     */
    public static function createTag($name, $color, $schoolId, $description = '')
    {
        // 检查是否已存在
        $existing = self::where('name', $name)
                       ->where('school_id', $schoolId)
                       ->find();
        
        if ($existing) {
            return $existing;
        }
        
        $tag = new self();
        $tag->name = $name;
        $tag->color = $color;
        $tag->description = $description;
        $tag->school_id = $schoolId;
        $tag->usage_count = 0;
        $tag->is_active = 1;
        $tag->save();
        
        return $tag;
    }
    
    /**
     * 更新标签使用次数
     */
    public function updateUsageCount()
    {
        $count = ContentTagRelation::where('tag_id', $this->id)->count();
        $this->usage_count = $count;
        $this->save();
        
        return $this;
    }
    
    /**
     * 获取热门标签
     */
    public static function getHotTags($schoolId, $limit = 10)
    {
        return self::where('school_id', $schoolId)
                  ->where('is_active', 1)
                  ->where('usage_count', '>', 0)
                  ->order('usage_count desc')
                  ->limit($limit)
                  ->select();
    }
    
    /**
     * 搜索标签
     */
    public static function searchTags($schoolId, $keyword, $limit = 10)
    {
        return self::where('school_id', $schoolId)
                  ->where('is_active', 1)
                  ->where('name', 'like', '%' . $keyword . '%')
                  ->order('usage_count desc')
                  ->limit($limit)
                  ->select();
    }
    
    /**
     * 批量创建标签
     */
    public static function batchCreateTags($tagNames, $schoolId, $defaultColor = '#1890ff')
    {
        $tags = [];
        foreach ($tagNames as $name) {
            $tag = self::createTag($name, $defaultColor, $schoolId);
            $tags[] = $tag;
        }
        return $tags;
    }
    
    /**
     * 获取标签统计信息
     */
    public static function getTagStatistics($schoolId)
    {
        $totalTags = self::where('school_id', $schoolId)->count();
        $activeTags = self::where('school_id', $schoolId)->where('is_active', 1)->count();
        $usedTags = self::where('school_id', $schoolId)->where('usage_count', '>', 0)->count();
        
        return [
            'total_tags' => $totalTags,
            'active_tags' => $activeTags,
            'used_tags' => $usedTags
        ];
    }
} 