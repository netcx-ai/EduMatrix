<?php
namespace app\model;

use think\Model;

/**
 * 内容标签关联模型
 */
class ContentTagRelation extends Model
{
    protected $name = 'content_tag_relation';
    protected $pk = 'id';
    
    // 自动时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    
    // 字段类型转换
    protected $type = [
        'id' => 'integer',
        'content_id' => 'integer',
        'tag_id' => 'integer',
        'create_time' => 'datetime'
    ];
    
    /**
     * 关联内容
     */
    public function content()
    {
        return $this->belongsTo(ContentLibrary::class, 'content_id', 'id');
    }
    
    /**
     * 关联标签
     */
    public function tag()
    {
        return $this->belongsTo(ContentTag::class, 'tag_id', 'id');
    }
    
    /**
     * 为内容添加标签
     */
    public static function addTagsToContent($contentId, $tagIds)
    {
        $relations = [];
        foreach ($tagIds as $tagId) {
            // 检查是否已存在
            $existing = self::where('content_id', $contentId)
                           ->where('tag_id', $tagId)
                           ->find();
            
            if (!$existing) {
                $relation = new self();
                $relation->content_id = $contentId;
                $relation->tag_id = $tagId;
                $relation->save();
                $relations[] = $relation;
            }
        }
        
        // 更新标签使用次数
        foreach ($tagIds as $tagId) {
            $tag = ContentTag::find($tagId);
            if ($tag) {
                $tag->updateUsageCount();
            }
        }
        
        return $relations;
    }
    
    /**
     * 为内容移除标签
     */
    public static function removeTagsFromContent($contentId, $tagIds)
    {
        $result = self::where('content_id', $contentId)
                     ->whereIn('tag_id', $tagIds)
                     ->delete();
        
        // 更新标签使用次数
        foreach ($tagIds as $tagId) {
            $tag = ContentTag::find($tagId);
            if ($tag) {
                $tag->updateUsageCount();
            }
        }
        
        return $result;
    }
    
    /**
     * 更新内容的标签
     */
    public static function updateContentTags($contentId, $tagIds)
    {
        // 删除原有标签
        self::where('content_id', $contentId)->delete();
        
        // 添加新标签
        return self::addTagsToContent($contentId, $tagIds);
    }
    
    /**
     * 获取内容的标签
     */
    public static function getContentTags($contentId)
    {
        return self::with('tag')
                  ->where('content_id', $contentId)
                  ->select();
    }
    
    /**
     * 获取标签下的内容
     */
    public static function getTagContents($tagId, $page = 1, $limit = 20)
    {
        return self::with('content')
                  ->where('tag_id', $tagId)
                  ->page($page, $limit)
                  ->order('create_time desc')
                  ->select();
    }
    
    /**
     * 获取多个标签下的内容（交集）
     */
    public static function getContentsByTags($tagIds, $page = 1, $limit = 20)
    {
        $contentIds = self::whereIn('tag_id', $tagIds)
                         ->group('content_id')
                         ->having('COUNT(DISTINCT tag_id) = ' . count($tagIds))
                         ->column('content_id');
        
        return ContentLibrary::whereIn('id', $contentIds)
                           ->page($page, $limit)
                           ->order('create_time desc')
                           ->select();
    }
    
    /**
     * 获取标签使用统计
     */
    public static function getTagUsageStatistics($schoolId)
    {
        return self::alias('r')
                  ->join('edu_content_tag t', 'r.tag_id = t.id')
                  ->where('t.school_id', $schoolId)
                  ->group('r.tag_id')
                  ->field('r.tag_id, t.name, COUNT(*) as usage_count')
                  ->order('usage_count desc')
                  ->select();
    }
    
    /**
     * 批量删除内容的标签关联
     */
    public static function deleteContentRelations($contentId)
    {
        // 获取要更新的标签ID
        $tagIds = self::where('content_id', $contentId)->column('tag_id');
        
        // 删除关联
        $result = self::where('content_id', $contentId)->delete();
        
        // 更新标签使用次数
        foreach ($tagIds as $tagId) {
            $tag = ContentTag::find($tagId);
            if ($tag) {
                $tag->updateUsageCount();
            }
        }
        
        return $result;
    }
    
    /**
     * 批量删除标签的关联
     */
    public static function deleteTagRelations($tagId)
    {
        return self::where('tag_id', $tagId)->delete();
    }
    
    /**
     * 获取内容标签统计
     */
    public static function getContentTagCount($contentId)
    {
        return self::where('content_id', $contentId)->count();
    }
    
    /**
     * 获取标签内容统计
     */
    public static function getTagContentCount($tagId)
    {
        return self::where('tag_id', $tagId)->count();
    }
} 