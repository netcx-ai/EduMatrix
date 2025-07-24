<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateContentTables extends Migrator
{
    public function change()
    {
        // 创建分类表
        $this->table('category', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci'])
            ->addColumn('name', 'string', ['limit' => 100, 'null' => false, 'comment' => '分类名称'])
            ->addColumn('code', 'string', ['limit' => 50, 'null' => false, 'comment' => '分类编码'])
            ->addColumn('description', 'string', ['limit' => 500, 'null' => true, 'comment' => '分类描述'])
            ->addColumn('parent_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '父级分类ID'])
            ->addColumn('icon', 'string', ['limit' => 100, 'null' => true, 'comment' => '分类图标'])
            ->addColumn('sort', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '排序'])
            ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '状态：0禁用，1启用'])
            ->addColumn('create_time', 'datetime', ['null' => true, 'comment' => '创建时间'])
            ->addColumn('update_time', 'datetime', ['null' => true, 'comment' => '更新时间'])
            ->addIndex(['code'], ['unique' => true])
            ->addIndex(['parent_id'])
            ->addIndex(['status'])
            ->create();

        // 创建标签表
        $this->table('tag', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci'])
            ->addColumn('name', 'string', ['limit' => 100, 'null' => false, 'comment' => '标签名称'])
            ->addColumn('code', 'string', ['limit' => 50, 'null' => false, 'comment' => '标签编码'])
            ->addColumn('description', 'string', ['limit' => 500, 'null' => true, 'comment' => '标签描述'])
            ->addColumn('color', 'string', ['limit' => 20, 'default' => '#666666', 'comment' => '标签颜色'])
            ->addColumn('sort', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '排序'])
            ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '状态：0禁用，1启用'])
            ->addColumn('create_time', 'datetime', ['null' => true, 'comment' => '创建时间'])
            ->addColumn('update_time', 'datetime', ['null' => true, 'comment' => '更新时间'])
            ->addIndex(['code'], ['unique' => true])
            ->addIndex(['status'])
            ->create();

        // 创建文章表
        $this->table('article', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci'])
            ->addColumn('title', 'string', ['limit' => 200, 'null' => false, 'comment' => '文章标题'])
            ->addColumn('content', 'longtext', ['null' => false, 'comment' => '文章内容'])
            ->addColumn('summary', 'string', ['limit' => 500, 'null' => true, 'comment' => '文章摘要'])
            ->addColumn('cover_image', 'string', ['limit' => 255, 'null' => true, 'comment' => '封面图片'])
            ->addColumn('category_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '分类ID'])
            ->addColumn('author_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '作者ID'])
            ->addColumn('status', 'integer', ['limit' => 1, 'default' => 0, 'comment' => '状态：0草稿，1已发布，2已下架'])
            ->addColumn('sort', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '排序'])
            ->addColumn('view_count', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '浏览次数'])
            ->addColumn('is_top', 'integer', ['limit' => 1, 'default' => 0, 'comment' => '是否置顶：0否，1是'])
            ->addColumn('is_recommend', 'integer', ['limit' => 1, 'default' => 0, 'comment' => '是否推荐：0否，1是'])
            ->addColumn('create_time', 'datetime', ['null' => true, 'comment' => '创建时间'])
            ->addColumn('update_time', 'datetime', ['null' => true, 'comment' => '更新时间'])
            ->addIndex(['category_id'])
            ->addIndex(['author_id'])
            ->addIndex(['status'])
            ->addIndex(['is_top'])
            ->addIndex(['is_recommend'])
            ->addIndex(['create_time'])
            ->create();

        // 创建文章标签关联表
        $this->table('article_tag', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci'])
            ->addColumn('article_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '文章ID'])
            ->addColumn('tag_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '标签ID'])
            ->addIndex(['article_id', 'tag_id'], ['unique' => true])
            ->addIndex(['article_id'])
            ->addIndex(['tag_id'])
            ->create();

        // 插入基础分类数据
        $this->insert('category', [
            'name' => '系统公告',
            'code' => 'system_notice',
            'description' => '系统相关公告信息',
            'parent_id' => 0,
            'icon' => 'layui-icon-notice',
            'sort' => 1,
            'status' => 1,
            'create_time' => date('Y-m-d H:i:s'),
            'update_time' => date('Y-m-d H:i:s')
        ]);

        $this->insert('category', [
            'name' => '帮助文档',
            'code' => 'help_docs',
            'description' => '系统使用帮助文档',
            'parent_id' => 0,
            'icon' => 'layui-icon-help',
            'sort' => 2,
            'status' => 1,
            'create_time' => date('Y-m-d H:i:s'),
            'update_time' => date('Y-m-d H:i:s')
        ]);

        $this->insert('category', [
            'name' => '新闻资讯',
            'code' => 'news',
            'description' => '行业新闻和资讯',
            'parent_id' => 0,
            'icon' => 'layui-icon-file',
            'sort' => 3,
            'status' => 1,
            'create_time' => date('Y-m-d H:i:s'),
            'update_time' => date('Y-m-d H:i:s')
        ]);

        // 插入基础标签数据
        $this->insert('tag', [
            'name' => '系统',
            'code' => 'system',
            'description' => '系统相关',
            'color' => '#FF5722',
            'sort' => 1,
            'status' => 1,
            'create_time' => date('Y-m-d H:i:s'),
            'update_time' => date('Y-m-d H:i:s')
        ]);

        $this->insert('tag', [
            'name' => '帮助',
            'code' => 'help',
            'description' => '帮助文档',
            'color' => '#009688',
            'sort' => 2,
            'status' => 1,
            'create_time' => date('Y-m-d H:i:s'),
            'update_time' => date('Y-m-d H:i:s')
        ]);

        $this->insert('tag', [
            'name' => '新闻',
            'code' => 'news',
            'description' => '新闻资讯',
            'color' => '#1E9FFF',
            'sort' => 3,
            'status' => 1,
            'create_time' => date('Y-m-d H:i:s'),
            'update_time' => date('Y-m-d H:i:s')
        ]);

        // 插入示例文章
        $this->insert('article', [
            'title' => '欢迎使用EduMatrix教育管理系统',
            'content' => '<p>欢迎使用EduMatrix教育管理系统！</p><p>这是一个功能强大的教育管理平台，提供完整的教育管理解决方案。</p>',
            'summary' => '欢迎使用EduMatrix教育管理系统，这是一个功能强大的教育管理平台。',
            'category_id' => 1,
            'author_id' => 1,
            'status' => 1,
            'sort' => 1,
            'view_count' => 0,
            'is_top' => 1,
            'is_recommend' => 1,
            'create_time' => date('Y-m-d H:i:s'),
            'update_time' => date('Y-m-d H:i:s')
        ]);

        // 为示例文章添加标签
        $this->insert('article_tag', [
            'article_id' => 1,
            'tag_id' => 1
        ]);
    }
} 