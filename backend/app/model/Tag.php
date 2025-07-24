<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

class Tag extends Model
{
    // 设置表名
    protected $name = 'tag';
    
    // 设置字段信息
    protected $schema = [
        'id'          => 'int',
        'name'        => 'string',
        'code'        => 'string',
        'description' => 'string',
        'color'       => 'string',
        'sort'        => 'int',
        'status'      => 'int',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
    ];

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 关联文章
    public function articles()
    {
        return $this->belongsToMany(Article::class, 'article_tag', 'tag_id', 'article_id');
    }

    // 获取器 - 状态文字
    public function getStatusTextAttr($value, $data)
    {
        $status = [0 => '禁用', 1 => '启用'];
        return $status[$data['status']] ?? '未知';
    }

    // 获取器 - 文章数量
    public function getArticleCountAttr($value, $data)
    {
        return $this->articles()->count();
    }

    // 搜索器 - 名称和描述
    public function searchNameDescriptionAttr($query, $value)
    {
        if ($value) {
            $query->where('name|description', 'like', "%{$value}%");
        }
    }

    // 搜索器 - 状态
    public function searchStatusAttr($query, $value)
    {
        if ($value !== '') {
            $query->where('status', $value);
        }
    }

    // 获取热门标签
    public static function getHotTags($limit = 20)
    {
        return self::withCount('articles')
            ->where('status', 1)
            ->order('articles_count', 'desc')
            ->order('sort', 'asc')
            ->limit($limit)
            ->select();
    }

    // 根据文章ID获取标签
    public static function getTagsByArticleId($articleId)
    {
        return self::whereHas('articles', function($query) use ($articleId) {
            $query->where('article_id', $articleId);
        })->select();
    }

    // 获取标签云数据
    public static function getTagCloud($limit = 50)
    {
        $tags = self::withCount('articles')
            ->where('status', 1)
            ->where('articles_count', '>', 0)
            ->order('articles_count', 'desc')
            ->limit($limit)
            ->select();

        // 计算字体大小
        $maxCount = $tags->max('articles_count');
        $minCount = $tags->min('articles_count');
        
        foreach ($tags as $tag) {
            if ($maxCount == $minCount) {
                $tag->font_size = 14;
            } else {
                $tag->font_size = 12 + (($tag->articles_count - $minCount) / ($maxCount - $minCount)) * 8;
            }
        }

        return $tags;
    }

    // 批量创建标签
    public static function createTags($tagNames)
    {
        $tags = [];
        foreach ($tagNames as $name) {
            $name = trim($name);
            if (empty($name)) continue;
            
            // 检查标签是否已存在
            $tag = self::where('name', $name)->find();
            if (!$tag) {
                $tag = self::create([
                    'name' => $name,
                    'code' => self::generateCode($name),
                    'status' => 1,
                    'sort' => 0
                ]);
            }
            $tags[] = $tag;
        }
        return $tags;
    }

    // 生成标签代码
    public static function generateCode($name)
    {
        $code = strtolower(trim($name));
        $code = preg_replace('/[^a-z0-9]/', '_', $code);
        $code = preg_replace('/_+/', '_', $code);
        $code = trim($code, '_');
        
        // 确保代码唯一
        $originalCode = $code;
        $counter = 1;
        while (self::where('code', $code)->find()) {
            $code = $originalCode . '_' . $counter;
            $counter++;
        }
        
        return $code;
    }

    // 获取标签统计信息
    public static function getTagStats()
    {
        return [
            'total' => self::count(),
            'active' => self::where('status', 1)->count(),
            'with_articles' => self::whereHas('articles')->count(),
            'most_used' => self::withCount('articles')
                ->order('articles_count', 'desc')
                ->limit(1)
                ->find()
        ];
    }
} 