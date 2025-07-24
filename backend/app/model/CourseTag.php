<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

class CourseTag extends Model
{
    protected $name = 'course_tag';
    protected $pk = 'id';
    
    // 自动时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    // 字段类型转换
    protected $type = [
        'id' => 'integer',
        'sort' => 'integer',
        'status' => 'integer',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
    ];
    
    // 关联关系 - 多对多关联课程
    public function courses()
    {
        return $this->belongsToMany(\app\model\Course::class, 'course_tag_relation', 'tag_id', 'course_id');
    }
    
    /**
     * 获取标签列表
     */
    public static function getTagList()
    {
        try {
            return self::where('status', 1)
                ->order('sort', 'asc')
                ->order('id', 'asc')
                ->select()
                ->toArray();
        } catch (\Exception $e) {
            // 如果查询失败，返回空数组
            return [];
        }
    }
    
    /**
     * 获取热门标签
     */
    public static function getHotTags($limit = 10)
    {
        return self::withCount('courses')
            ->where('status', 1)
            ->order('courses_count', 'desc')
            ->order('sort', 'asc')
            ->limit($limit)
            ->select()
            ->toArray();
    }
    
    /**
     * 根据名称获取或创建标签
     */
    public static function findOrCreateByName($name, $color = '#1E9FFF')
    {
        $tag = self::where('name', $name)->find();
        
        if (!$tag) {
            $tag = new self;
            $tag->name = $name;
            $tag->color = $color;
            $tag->status = 1;
            $tag->save();
        }
        
        return $tag;
    }
    
    /**
     * 批量创建标签
     */
    public static function createBatch($tagNames, $color = '#1E9FFF')
    {
        $tags = [];
        
        foreach ($tagNames as $name) {
            $name = trim($name);
            if (!empty($name)) {
                $tag = self::findOrCreateByName($name, $color);
                $tags[] = $tag;
            }
        }
        
        return $tags;
    }
} 