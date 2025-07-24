<?php
// 文章独立控制器，迁移自Content.php

declare (strict_types = 1);

namespace app\controller\admin;

use app\BaseController;
use app\model\Article;
use app\model\Category;
use app\model\Tag;
use think\facade\View;
use think\Request;
use think\Db;

class ArticleController extends BaseController
{
    /**
     * 文章管理
     */
    public function article(Request $request)
    {
        try {
            // 如果是AJAX请求，返回JSON
            if ($request->isAjax()) {
                $page = (int)$request->param('page', 1);
                $limit = (int)$request->param('limit', 10);
                $keyword = $request->param('keyword', '');
                $category_id = $request->param('category_id', '');
                $status = $request->param('status', '');

                $query = Article::with(['category', 'tags', 'author']);

                // 关键词搜索
                if (!empty($keyword)) {
                    $query->where('title|content', 'like', "%{$keyword}%");
                }

                // 分类筛选
                if ($category_id !== '') {
                    $query->where('category_id', $category_id);
                }

                // 状态筛选
                if ($status !== '') {
                    $query->where('status', $status);
                }

                // 克隆一份用于统计总数
                $countQuery = clone $query;
                $total = $countQuery->count();

                // 查分页数据并转为数组
                $list = $query->order('sort', 'desc')
                    ->order('id', 'desc')
                    ->page($page, $limit)
                    ->select()
                    ->toArray();

                return json([
                    'code' => 0,
                    'msg' => '',
                    'count' => $total,
                    'data' => $list
                ]);
            }

            // 获取分类列表
            $categories = Category::where('status', 1)->select();

            // 非AJAX请求，返回页面
            return View::fetch('admin/article/index', [
                'categories' => $categories
            ]);
            
        } catch (\Exception $e) {
            if ($request->isAjax()) {
                return json([
                    'code' => 1,
                    'msg' => '请求异常：' . $e->getMessage(),
                    'count' => 0,
                    'data' => []
                ]);
            } else {
                return $this->error('页面加载失败：' . $e->getMessage());
            }
        }
    }

    /**
     * 添加文章
     */
    public function addArticle(Request $request)
    {
        if ($request->isPost()) {
            $data = $request->post();
            
            // 验证数据
            try {
                validate([
                    'title' => 'require|max:200',
                    'content' => 'require',
                    'category_id' => 'require|number',
                    'status' => 'require|in:0,1,2',
                ])->check($data);
            } catch (\Exception $e) {
                return json(['code' => 1, 'msg' => $e->getMessage()]);
            }
            
            // 创建文章
            $article = new Article;
            $article->title = $data['title'];
            $article->content = $data['content'];
            $article->summary = $data['summary'] ?? '';
            $article->cover_image = $data['cover_image'] ?? '';
            $article->category_id = $data['category_id'];
            $article->status = $data['status'];
            $article->sort = $data['sort'] ?? 0;
            $article->is_top = $data['is_top'] ?? 0;
            $article->is_recommend = $data['is_recommend'] ?? 0;
            $article->author_id = session('admin_id');
            $article->save();
            
            // 处理标签
            if (!empty($data['tag_ids'])) {
                $article->tags()->attach($data['tag_ids']);
            }
            
            return json(['code' => 0, 'msg' => '添加成功']);
        }
        
        // 获取分类和标签列表
        $categories = Category::where('status', 1)->select();
        $tags = Tag::where('status', 1)->select();
        
        return View::fetch('admin/article/add', [
            'categories' => $categories,
            'tags' => $tags
        ]);
    }

    /**
     * 编辑文章
     */
    public function editArticle(Request $request)
    {
        $id = $request->param('id/d');
        $article = Article::with(['category', 'tags'])->find($id);
        
        if (!$article) {
            return $this->error('文章不存在');
        }
        
        if ($request->isPost()) {
            $data = $request->post();
            
            // 验证数据
            try {
                validate([
                    'title' => 'require|max:200',
                    'content' => 'require',
                    'category_id' => 'require|number',
                    'status' => 'require|in:0,1,2',
                ])->check($data);
            } catch (\Exception $e) {
                return json(['code' => 1, 'msg' => $e->getMessage()]);
            }
            
            // 更新文章
            $article->title = $data['title'];
            $article->content = $data['content'];
            $article->summary = $data['summary'] ?? '';
            $article->cover_image = $data['cover_image'] ?? '';
            $article->category_id = $data['category_id'];
            $article->status = $data['status'];
            $article->sort = $data['sort'] ?? 0;
            $article->is_top = $data['is_top'] ?? 0;
            $article->is_recommend = $data['is_recommend'] ?? 0;
            $article->save();
            
            // 更新标签
            $article->tags()->detach();
            if (!empty($data['tag_ids'])) {
                $article->tags()->attach($data['tag_ids']);
            }
            
            return json(['code' => 0, 'msg' => '更新成功']);
        }
        
        // 获取分类和标签列表
        $categories = Category::where('status', 1)->select();
        $tags = Tag::where('status', 1)->select();
        
        // 处理文章标签ID数组
        $article = $article->toArray();
        $article['tag_ids'] = [];
        if (!empty($article['tags'])) {
            foreach ($article['tags'] as $tag) {
                $article['tag_ids'][] = $tag['id'];
            }
        }
        
        return View::fetch('admin/article/edit', [
            'article' => $article,
            'categories' => $categories,
            'tags' => $tags
        ]);
    }

    /**
     * 删除文章
     */
    public function deleteArticle(Request $request)
    {
        $id = $request->param('id/d');
        $article = Article::find($id);
        
        if (!$article) {
            return json(['code' => 1, 'msg' => '文章不存在']);
        }
        
        // 删除关联的标签
        $article->tags()->detach();
        
        $article->delete();
        
        return json(['code' => 0, 'msg' => '删除成功']);
    }

    /**
     * 批量操作文章
     */
    public function batchArticle(Request $request)
    {
        $action = $request->param('action');
        $ids = $request->param('ids/a', []);
        
        if (empty($ids)) {
            return json(['code' => 1, 'msg' => '请选择要操作的文章']);
        }
        
        try {
            switch ($action) {
                case 'publish':
                    Article::whereIn('id', $ids)->update(['status' => 1]);
                    $msg = '批量发布成功';
                    break;
                case 'unpublish':
                    Article::whereIn('id', $ids)->update(['status' => 2]);
                    $msg = '批量下架成功';
                    break;
                case 'delete':
                    // 删除关联的标签
                    foreach ($ids as $id) {
                        $article = Article::find($id);
                        if ($article) {
                            $article->tags()->detach();
                        }
                    }
                    Article::whereIn('id', $ids)->delete();
                    $msg = '批量删除成功';
                    break;
                default:
                    return json(['code' => 1, 'msg' => '未知操作']);
            }
            
            return json(['code' => 0, 'msg' => $msg]);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '操作失败：' . $e->getMessage()]);
        }
    }

    /**
     * 发布文章
     */
    public function publishArticle(Request $request)
    {
        $id = $request->param('id/d');
        $article = Article::find($id);
        
        if (!$article) {
            return json(['code' => 1, 'msg' => '文章不存在']);
        }
        
        $article->status = 1; // 发布
        $article->save();
        
        return json(['code' => 0, 'msg' => '发布成功']);
    }

    /**
     * 下架文章
     */
    public function unpublishArticle(Request $request)
    {
        $id = $request->param('id/d');
        $article = Article::find($id);
        
        if (!$article) {
            return json(['code' => 1, 'msg' => '文章不存在']);
        }
        
        $article->status = 2; // 下架
        $article->save();
        
        return json(['code' => 0, 'msg' => '下架成功']);
    }

    /**
     * 查看文章
     */
    public function viewArticle(Request $request)
    {
        $id = $request->param('id/d');
        $article = Article::with(['category', 'tags', 'author'])->find($id);
        
        if (!$article) {
            return $this->error('文章不存在');
        }
        
        return View::fetch('admin/article/view', [
            'article' => $article
        ]);
    }

    /**
     * 复制文章
     */
    public function copy(Request $request)
    {
        $id = $request->param('id/d');
        $article = Article::with(['tags'])->find($id);
        
        if (!$article) {
            return json(['code' => 1, 'msg' => '文章不存在']);
        }
        
        // 复制文章
        $newArticle = new Article;
        $newArticle->title = $article->title . '_副本';
        $newArticle->content = $article->content;
        $newArticle->summary = $article->summary;
        $newArticle->cover_image = $article->cover_image;
        $newArticle->category_id = $article->category_id;
        $newArticle->status = 0; // 草稿状态
        $newArticle->sort = $article->sort;
        $newArticle->is_top = 0;
        $newArticle->is_recommend = 0;
        $newArticle->author_id = session('admin_id');
        $newArticle->save();
        
        // 复制标签
        if (!empty($article->tags)) {
            $tagIds = [];
            foreach ($article->tags as $tag) {
                $tagIds[] = $tag->id;
            }
            $newArticle->tags()->attach($tagIds);
        }
        
        return json(['code' => 0, 'msg' => '复制成功']);
    }

    /**
     * 移动文章
     */
    public function move(Request $request)
    {
        $id = $request->param('id/d');
        $category_id = $request->param('category_id/d');
        
        $article = Article::find($id);
        if (!$article) {
            return json(['code' => 1, 'msg' => '文章不存在']);
        }
        
        $article->category_id = $category_id;
        $article->save();
        
        return json(['code' => 0, 'msg' => '移动成功']);
    }

    /**
     * 排序文章
     */
    public function sort(Request $request)
    {
        $id = $request->param('id/d');
        $sort = $request->param('sort/d', 0);
        
        $article = Article::find($id);
        if (!$article) {
            return json(['code' => 1, 'msg' => '文章不存在']);
        }
        
        $article->sort = $sort;
        $article->save();
        
        return json(['code' => 0, 'msg' => '排序更新成功']);
    }

    /**
     * 预览文章
     */
    public function preview(Request $request)
    {
        $id = $request->param('id/d');
        $article = Article::with(['category', 'tags', 'author'])->find($id);
        
        if (!$article) {
            return $this->error('文章不存在');
        }
        
        return View::fetch('admin/article/preview', [
            'article' => $article
        ]);
    }

    /**
     * 搜索文章
     */
    public function search(Request $request)
    {
        $keyword = $request->param('keyword', '');
        $limit = (int)$request->param('limit', 10);
        
        if (empty($keyword)) {
            return json(['code' => 1, 'msg' => '请输入搜索关键词']);
        }
        
        $articles = Article::with(['category', 'tags'])
            ->where('title|content', 'like', "%{$keyword}%")
            ->where('status', 1)
            ->order('sort', 'desc')
            ->order('id', 'desc')
            ->limit($limit)
            ->select();
        
        return json(['code' => 0, 'msg' => '', 'data' => $articles]);
    }

    /**
     * 文章分类管理
     */
    public function category(Request $request)
    {
        try {
            if ($request->isAjax()) {
                $page = (int)$request->param('page', 1);
                $limit = (int)$request->param('limit', 10);
                $keyword = $request->param('keyword', '');

                $query = Category::where('parent_id', 0);

                if (!empty($keyword)) {
                    $query->where('name|description', 'like', "%{$keyword}%");
                }

                $countQuery = clone $query;
                $total = $countQuery->count();

                $list = $query->order('sort', 'asc')
                    ->order('id', 'asc')
                    ->page($page, $limit)
                    ->select()
                    ->toArray();

                return json([
                    'code' => 0,
                    'msg' => '',
                    'count' => $total,
                    'data' => $list
                ]);
            }

            return View::fetch('admin/article/category');
            
        } catch (\Exception $e) {
            if ($request->isAjax()) {
                return json([
                    'code' => 1,
                    'msg' => '请求异常：' . $e->getMessage(),
                    'count' => 0,
                    'data' => []
                ]);
            } else {
                return $this->error('页面加载失败：' . $e->getMessage());
            }
        }
    }

    /**
     * 添加文章分类
     */
    public function addCategory(Request $request)
    {
        if ($request->isPost()) {
            $data = $request->post();
            
            try {
                validate([
                    'name' => 'require|max:100',
                    'code' => 'require|max:50|unique:category',
                    'parent_id' => 'number',
                    'status' => 'require|in:0,1',
                ])->check($data);
            } catch (\Exception $e) {
                return json(['code' => 1, 'msg' => $e->getMessage()]);
            }
            
            $category = new Category;
            $category->name = $data['name'];
            $category->code = $data['code'];
            $category->description = $data['description'] ?? '';
            $category->parent_id = $data['parent_id'] ?? 0;
            $category->icon = $data['icon'] ?? '';
            $category->sort = $data['sort'] ?? 0;
            $category->status = $data['status'];
            $category->save();
            
            return json(['code' => 0, 'msg' => '添加成功']);
        }
        
        $categories = Category::where('status', 1)->select();
        
        return View::fetch('admin/article/add_category', [
            'categories' => $categories
        ]);
    }

    /**
     * 编辑文章分类
     */
    public function editCategory(Request $request)
    {
        $id = $request->param('id/d');
        $category = Category::find($id);
        
        if (!$category) {
            return $this->error('分类不存在');
        }
        
        if ($request->isPost()) {
            $data = $request->post();
            
            try {
                validate([
                    'name' => 'require|max:100',
                    'code' => 'require|max:50|unique:category,code,' . $id,
                    'parent_id' => 'number',
                    'status' => 'require|in:0,1',
                ])->check($data);
            } catch (\Exception $e) {
                return json(['code' => 1, 'msg' => $e->getMessage()]);
            }
            
            if ($data['parent_id'] == $id) {
                return json(['code' => 1, 'msg' => '不能将分类设置为自己的子分类']);
            }
            
            $category->name = $data['name'];
            $category->code = $data['code'];
            $category->description = $data['description'] ?? '';
            $category->parent_id = $data['parent_id'] ?? 0;
            $category->icon = $data['icon'] ?? '';
            $category->sort = $data['sort'] ?? 0;
            $category->status = $data['status'];
            $category->save();
            
            return json(['code' => 0, 'msg' => '更新成功']);
        }
        
        $categories = Category::where('status', 1)
            ->where('id', '<>', $id)
            ->select();
        
        return View::fetch('admin/article/edit_category', [
            'category' => $category,
            'categories' => $categories
        ]);
    }

    /**
     * 删除文章分类
     */
    public function deleteCategory(Request $request)
    {
        $id = $request->param('id/d');
        $category = Category::find($id);
        
        if (!$category) {
            return json(['code' => 1, 'msg' => '分类不存在']);
        }
        
        if ($category->hasChildren()) {
            return json(['code' => 1, 'msg' => '该分类下有子分类，不能删除']);
        }
        
        if ($category->hasArticles()) {
            return json(['code' => 1, 'msg' => '该分类下有关联文章，不能删除']);
        }
        
        $category->delete();
        
        return json(['code' => 0, 'msg' => '删除成功']);
    }

    /**
     * 文章标签管理
     */
    public function tag(Request $request)
    {
        try {
            if ($request->isAjax()) {
                $page = (int)$request->param('page', 1);
                $limit = (int)$request->param('limit', 10);
                $keyword = $request->param('keyword', '');

                $query = Tag::with(['articles']);

                if (!empty($keyword)) {
                    $query->where('name|description', 'like', "%{$keyword}%");
                }

                $countQuery = clone $query;
                $total = $countQuery->count();

                $list = $query->order('sort', 'asc')
                    ->order('id', 'asc')
                    ->page($page, $limit)
                    ->select()
                    ->toArray();

                return json([
                    'code' => 0,
                    'msg' => '',
                    'count' => $total,
                    'data' => $list
                ]);
            }

            return View::fetch('admin/article/tag');
            
        } catch (\Exception $e) {
            if ($request->isAjax()) {
                return json([
                    'code' => 1,
                    'msg' => '请求异常：' . $e->getMessage(),
                    'count' => 0,
                    'data' => []
                ]);
            } else {
                return $this->error('页面加载失败：' . $e->getMessage());
            }
        }
    }

    /**
     * 添加文章标签
     */
    public function addTag(Request $request)
    {
        if ($request->isPost()) {
            $data = $request->post();
            
            try {
                validate([
                    'name' => 'require|max:50|unique:tag',
                    'status' => 'require|in:0,1',
                ])->check($data);
            } catch (\Exception $e) {
                return json(['code' => 1, 'msg' => $e->getMessage()]);
            }
            
            $tag = new Tag;
            $tag->name = $data['name'];
            $tag->description = $data['description'] ?? '';
            $tag->color = $data['color'] ?? '#666';
            $tag->sort = $data['sort'] ?? 0;
            $tag->status = $data['status'];
            $tag->save();
            
            return json(['code' => 0, 'msg' => '添加成功']);
        }
        
        return View::fetch('admin/article/add_tag');
    }

    /**
     * 编辑文章标签
     */
    public function editTag(Request $request)
    {
        $id = $request->param('id/d');
        $tag = Tag::find($id);
        
        if (!$tag) {
            return $this->error('标签不存在');
        }
        
        if ($request->isPost()) {
            $data = $request->post();
            
            try {
                validate([
                    'name' => 'require|max:50|unique:tag,name,' . $id,
                    'status' => 'require|in:0,1',
                ])->check($data);
            } catch (\Exception $e) {
                return json(['code' => 1, 'msg' => $e->getMessage()]);
            }
            
            $tag->name = $data['name'];
            $tag->description = $data['description'] ?? '';
            $tag->color = $data['color'] ?? '#666';
            $tag->sort = $data['sort'] ?? 0;
            $tag->status = $data['status'];
            $tag->save();
            
            return json(['code' => 0, 'msg' => '更新成功']);
        }
        
        return View::fetch('admin/article/edit_tag', [
            'tag' => $tag
        ]);
    }

    /**
     * 删除文章标签
     */
    public function deleteTag(Request $request)
    {
        $id = $request->param('id/d');
        $tag = Tag::find($id);
        
        if (!$tag) {
            return json(['code' => 1, 'msg' => '标签不存在']);
        }
        
        if ($tag->hasArticles()) {
            return json(['code' => 1, 'msg' => '该标签下有关联文章，不能删除']);
        }
        
        $tag->delete();
        
        return json(['code' => 0, 'msg' => '删除成功']);
    }

    /**
     * 获取分类树形结构
     */
    public function getCategoryTree(Request $request)
    {
        try {
            $categories = Category::where('status', 1)
                ->order('sort', 'asc')
                ->order('id', 'asc')
                ->select()
                ->toArray();
            
            return json(['code' => 0, 'data' => $categories]);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取标签列表
     */
    public function getTagList(Request $request)
    {
        try {
            $tags = Tag::where('status', 1)
                ->order('sort', 'asc')
                ->order('id', 'asc')
                ->select()
                ->toArray();
            
            return json(['code' => 0, 'data' => $tags]);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => $e->getMessage()]);
        }
    }
} 