<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 课程标签关联模型
 */
class CourseTagRelation extends Model
{
    protected $name = 'course_tag_relation';
    protected $pk = 'id';
    
    // 自动时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = false;
    
    // 字段类型转换
    protected $type = [
        'id' => 'integer',
        'course_id' => 'integer',
        'tag_id' => 'integer',
        'create_time' => 'datetime'
    ];
    
    /**
     * 关联课程
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'id');
    }
    
    /**
     * 关联标签
     */
    public function tag()
    {
        return $this->belongsTo(CourseTag::class, 'tag_id', 'id');
    }
    
    /**
     * 批量保存课程标签关联
     */
    public static function saveCourseTags($courseId, $tagIds)
    {
        // 删除原有关联
        self::where('course_id', $courseId)->delete();
        
        // 添加新关联
        $data = [];
        foreach ($tagIds as $tagId) {
            $data[] = [
                'course_id' => $courseId,
                'tag_id' => $tagId,
                'create_time' => date('Y-m-d H:i:s')
            ];
        }
        
        if (!empty($data)) {
            return self::insertAll($data);
        }
        
        return true;
    }
    
    /**
     * 获取课程的标签ID列表
     */
    public static function getCourseTagIds($courseId)
    {
        return self::where('course_id', $courseId)
            ->column('tag_id');
    }
    
    /**
     * 获取标签的课程ID列表
     */
    public static function getTagCourseIds($tagId)
    {
        return self::where('tag_id', $tagId)
            ->column('course_id');
    }
} 