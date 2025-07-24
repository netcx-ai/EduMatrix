<?php
declare (strict_types = 1);

namespace app\controller\admin;

use app\BaseController;
use app\model\Tool;
use app\model\School;
use think\facade\View;
use think\facade\Log;
use think\facade\Validate;
use think\Request;

class ToolController extends BaseController
{
    /**
     * 获取工具列表
     */
    public function index()
    {
        try {
            if ($this->request->isAjax()) {
                $page = $this->request->param('page', 1);
                $limit = $this->request->param('limit', 20);
                $school_id = $this->request->param('school_id', '');
                $keyword = $this->request->param('keyword', '');
                $status = $this->request->param('status', '');
                $category = $this->request->param('category', '');

                $query = Tool::with(['schools']);
                if ($school_id) {
                    $query->where('school_id', $school_id);
                }
                if ($keyword) {
                    $query->where('name|code|description', 'like', "%{$keyword}%");
                }
                if ($status !== '') {
                    $query->where('status', $status);
                }
                if ($category) {
                    $query->where('category', $category);
                }
                $list = $query->order('create_time', 'desc')
                    ->paginate([
                        'list_rows' => $limit,
                        'page' => $page
                    ]);
                return $this->success([
                    'count' => $list->total(),
                    'data' => $list->items()
                ]);
            }

            return View::fetch('admin/tool/index');
        } catch (\Exception $e) {
            Log::error("获取工具列表失败: " . $e->getMessage());
            if ($this->request->isAjax()) {
                return $this->error('获取工具列表失败');
            } else {
                $this->error('获取工具列表失败');
            }
        }
    }

    /**
     * 获取工具详情
     */
    public function show($id)
    {
        try {
            $tool = Tool::with(['schools'])->find($id);
            if (!$tool) {
                return $this->error('工具不存在', 404);
            }
            return $this->success($tool);
        } catch (\Exception $e) {
            Log::error("获取工具详情失败: " . $e->getMessage());
            return $this->error('获取工具详情失败');
        }
    }

    /**
     * 创建工具
     */
    public function store()
    {
        $data = $this->request->post();
        $validate = Validate::rule([
            'name' => 'require|length:2,100',
            'code' => 'require|length:2,50|unique:tool,code',
            'short_name' => 'length:0,50',
            'description' => 'length:0,500',
            'category' => 'require|in:ai,resource,utility',
            'icon' => 'length:0,255',
            'url' => 'length:0,255',
            'api_key' => 'length:0,255',
            'config' => 'json',
            'status' => 'in:0,1'
        ])->message([
            'name.require' => '工具名称不能为空',
            'name.length' => '工具名称长度必须在2-100个字符之间',
            'code.require' => '工具编码不能为空',
            'code.length' => '工具编码长度必须在2-50个字符之间',
            'code.unique' => '工具编码已存在',
            'short_name.length' => '工具简称长度不能超过50个字符',
            'description.length' => '工具描述长度不能超过500个字符',
            'category.require' => '工具分类不能为空',
            'category.in' => '工具分类值不正确',
            'icon.length' => '图标路径长度不能超过255个字符',
            'url.length' => '工具URL长度不能超过255个字符',
            'api_key.length' => 'API密钥长度不能超过255个字符',
            'config.json' => '配置信息格式不正确',
            'status.in' => '状态值不正确'
        ]);
        if (!$validate->check($data)) {
            return $this->error($validate->getError(), 400);
        }
        try {
            $tool = new Tool();
            $tool->name = $data['name'];
            $tool->code = $data['code'];
            $tool->short_name = $data['short_name'] ?? '';
            $tool->description = $data['description'] ?? '';
            $tool->category = $data['category'];
            $tool->icon = $data['icon'] ?? '';
            $tool->url = $data['url'] ?? '';
            $tool->api_key = $data['api_key'] ?? '';
            $tool->config = $data['config'] ?? '{}';
            $tool->status = $data['status'] ?? 1;
            $tool->save();
            return $this->success($tool, '工具创建成功');
        } catch (\Exception $e) {
            Log::error("创建工具失败: " . $e->getMessage());
            return $this->error('工具创建失败');
        }
    }

    /**
     * 更新工具
     */
    public function update($id)
    {
        $data = $this->request->post();
        try {
            $tool = Tool::find($id);
            if (!$tool) {
                return $this->error('工具不存在', 404);
            }
            $validate = Validate::rule([
                'name' => 'require|length:2,100',
                'code' => 'require|length:2,50|unique:tool,code,' . $id,
                'short_name' => 'length:0,50',
                'description' => 'length:0,500',
                'category' => 'require|in:ai,resource,utility',
                'icon' => 'length:0,255',
                'url' => 'length:0,255',
                'api_key' => 'length:0,255',
                'config' => 'json',
                'status' => 'in:0,1'
            ])->message([
                'name.require' => '工具名称不能为空',
                'name.length' => '工具名称长度必须在2-100个字符之间',
                'code.require' => '工具编码不能为空',
                'code.length' => '工具编码长度必须在2-50个字符之间',
                'code.unique' => '工具编码已存在',
                'short_name.length' => '工具简称长度不能超过50个字符',
                'description.length' => '工具描述长度不能超过500个字符',
                'category.require' => '工具分类不能为空',
                'category.in' => '工具分类值不正确',
                'icon.length' => '图标路径长度不能超过255个字符',
                'url.length' => '工具URL长度不能超过255个字符',
                'api_key.length' => 'API密钥长度不能超过255个字符',
                'config.json' => '配置信息格式不正确',
                'status.in' => '状态值不正确'
            ]);
            if (!$validate->check($data)) {
                return $this->error($validate->getError(), 400);
            }
            $tool->name = $data['name'];
            $tool->code = $data['code'];
            $tool->short_name = $data['short_name'] ?? '';
            $tool->description = $data['description'] ?? '';
            $tool->category = $data['category'];
            $tool->icon = $data['icon'] ?? '';
            $tool->url = $data['url'] ?? '';
            $tool->api_key = $data['api_key'] ?? '';
            $tool->config = $data['config'] ?? '{}';
            $tool->status = $data['status'] ?? 1;
            $tool->save();
            return $this->success($tool, '工具更新成功');
        } catch (\Exception $e) {
            Log::error("更新工具失败: " . $e->getMessage());
            return $this->error('工具更新失败');
        }
    }

    /**
     * 删除工具
     */
    public function destroy($id)
    {
        try {
            $tool = Tool::find($id);
            if (!$tool) {
                return $this->error('工具不存在', 404);
            }
            $tool->delete();
            return $this->success(null, '工具删除成功');
        } catch (\Exception $e) {
            Log::error("删除工具失败: " . $e->getMessage());
            return $this->error('工具删除失败');
        }
    }

    /**
     * 工具权限分配（批量分配学校）
     */
    public function assignSchools($id)
    {
        $data = $this->request->post();
        $validate = Validate::rule([
            'school_ids' => 'require|array'
        ])->message([
            'school_ids.require' => '学校ID列表不能为空',
            'school_ids.array' => '学校ID列表格式不正确'
        ]);
        if (!$validate->check($data)) {
            return $this->error($validate->getError(), 400);
        }
        try {
            $tool = Tool::find($id);
            if (!$tool) {
                return $this->error('工具不存在', 404);
            }
            $tool->schools()->sync($data['school_ids']);
            return $this->success(null, '学校分配成功');
        } catch (\Exception $e) {
            Log::error("分配学校失败: " . $e->getMessage());
            return $this->error('分配学校失败');
        }
    }

    /**
     * 获取工具分类列表
     */
    public function categories()
    {
        $categories = [
            ['value' => 'ai', 'label' => 'AI工具'],
            ['value' => 'resource', 'label' => '教学资源'],
            ['value' => 'utility', 'label' => '实用工具']
        ];
        return $this->success($categories);
    }

    /**
     * 获取工具统计
     */
    public function stats()
    {
        try {
            $totalCount = Tool::count();
            $byCategory = Tool::field('category, COUNT(*) as count')->group('category')->select();
            $byStatus = Tool::field('status, COUNT(*) as count')->group('status')->select();
            return $this->success([
                'total' => $totalCount,
                'by_category' => $byCategory,
                'by_status' => $byStatus
            ]);
        } catch (\Exception $e) {
            Log::error("获取工具统计失败: " . $e->getMessage());
            return $this->error('获取工具统计失败');
        }
    }
} 