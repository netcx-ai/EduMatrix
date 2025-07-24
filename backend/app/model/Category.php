<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

class Category extends Model
{
    // 设置表名
    protected $name = 'category';
    
    // 设置字段信息
    protected $schema = [
        'id'          => 'int',
        'name'        => 'string',
        'code'        => 'string',
        'description' => 'string',
        'parent_id'   => 'int',
        'icon'        => 'string',
        'sort'        => 'int',
        'status'      => 'int',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
    ];

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 关联子分类
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // 关联父分类
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // 关联文章
    public function articles()
    {
        return $this->hasMany(Article::class, 'category_id');
    }

    // 获取器 - 状态文字
    public function getStatusTextAttr($value, $data)
    {
        $status = [0 => '禁用', 1 => '启用'];
        return $status[$data['status']] ?? '未知';
    }

    // 获取器 - 层级显示
    public function getLevelTextAttr($value, $data)
    {
        $level = $this->getLevel($data['id']);
        return str_repeat('├─', $level) . $data['name'];
    }

    // 获取分类层级
    public function getLevel($id)
    {
        $level = 0;
        $category = $this->find($id);
        while ($category && $category->parent_id > 0) {
            $level++;
            $category = $this->find($category->parent_id);
        }
        return $level;
    }

    // 获取所有子分类ID
    public function getChildrenIds($id)
    {
        $ids = [$id];
        $children = $this->where('parent_id', $id)->select();
        foreach ($children as $child) {
            $ids = array_merge($ids, $this->getChildrenIds($child->id));
        }
        return $ids;
    }

    // 获取分类树
    public static function getCategoryTree($parentId = 0)
    {
        $categories = self::where('parent_id', $parentId)
            ->where('status', 1)
            ->order('sort', 'asc')
            ->order('id', 'asc')
            ->select();

        foreach ($categories as $category) {
            $category->children = self::getCategoryTree($category->id);
        }

        return $categories;
    }

    // 获取分类选项（用于下拉选择）
    public static function getCategoryOptions($parentId = 0, $level = 0)
    {
        $options = [];
        $categories = self::where('parent_id', $parentId)
            ->where('status', 1)
            ->order('sort', 'asc')
            ->order('id', 'asc')
            ->select();

        foreach ($categories as $category) {
            $prefix = str_repeat('　', $level);
            $options[$category->id] = $prefix . $category->name;
            
            // 递归获取子分类
            $children = self::getCategoryOptions($category->id, $level + 1);
            $options = array_merge($options, $children);
        }

        return $options;
    }

    // 检查是否有子分类
    public function hasChildren()
    {
        return $this->children()->count() > 0;
    }

    // 检查是否有关联文章
    public function hasArticles()
    {
        return $this->articles()->count() > 0;
    }

    // 获取分类路径
    public function getCategoryPath()
    {
        $path = [$this];
        $parent = $this->parent;
        
        while ($parent) {
            array_unshift($path, $parent);
            $parent = $parent->parent;
        }
        
        return $path;
    }
} 