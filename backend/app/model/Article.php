<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

class Article extends Model
{
    // 设置表名
    protected $name = 'article';
    
    // 设置字段信息
    protected $schema = [
        'id'          => 'int',
        'title'       => 'string',
        'content'     => 'text',
        'summary'     => 'string',
        'cover_image' => 'string',
        'category_id' => 'int',
        'author_id'   => 'int',
        'status'      => 'int',
        'sort'        => 'int',
        'view_count'  => 'int',
        'is_top'      => 'int',
        'is_recommend' => 'int',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
    ];

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 关联分类
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    // 关联作者
    public function author()
    {
        return $this->belongsTo(Admin::class, 'author_id');
    }

    // 关联标签
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'article_tag', 'article_id', 'tag_id');
    }

    // 获取器 - 状态文字
    public function getStatusTextAttr($value, $data)
    {
        $status = [0 => '草稿', 1 => '已发布', 2 => '已下架'];
        return $status[$data['status']] ?? '未知';
    }

    // 获取器 - 是否置顶文字
    public function getIsTopTextAttr($value, $data)
    {
        return $data['is_top'] ? '是' : '否';
    }

    // 获取器 - 是否推荐文字
    public function getIsRecommendTextAttr($value, $data)
    {
        return $data['is_recommend'] ? '是' : '否';
    }

    // 搜索器 - 标题和内容
    public function searchTitleContentAttr($query, $value)
    {
        if ($value) {
            $query->where('title|content', 'like', "%{$value}%");
        }
    }

    // 搜索器 - 分类
    public function searchCategoryIdAttr($query, $value)
    {
        if ($value !== '') {
            $query->where('category_id', $value);
        }
    }

    // 搜索器 - 状态
    public function searchStatusAttr($query, $value)
    {
        if ($value !== '') {
            $query->where('status', $value);
        }
    }

    // 增加浏览次数
    public function incrementViewCount()
    {
        $this->increment('view_count');
    }

    // 获取推荐文章
    public static function getRecommendArticles($limit = 10)
    {
        return self::where('status', 1)
            ->where('is_recommend', 1)
            ->order('sort', 'desc')
            ->order('id', 'desc')
            ->limit($limit)
            ->select();
    }

    // 获取置顶文章
    public static function getTopArticles($limit = 5)
    {
        return self::where('status', 1)
            ->where('is_top', 1)
            ->order('sort', 'desc')
            ->order('id', 'desc')
            ->limit($limit)
            ->select();
    }
} 