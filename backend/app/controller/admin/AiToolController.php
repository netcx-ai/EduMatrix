<?php
namespace app\controller\admin;

use app\BaseController;
use app\model\AiTool;
use app\model\AiToolSchool;
use app\model\AiUsage;
use app\model\School;
use think\Request;
use think\Validate;
use think\facade\View;
use think\facade\Log;

/**
 * 平台端AI工具管理控制器
 */
class AiToolController extends BaseController
{
    /**
     * AI工具管理页面
     */
    public function index()
    {
        try {
            if ($this->request->isAjax()) {
                $page = $this->request->param('page', 1);
                $limit = $this->request->param('limit', 10);
                $category = $this->request->param('category', '');
                $status = $this->request->param('status', '');
                $keyword = $this->request->param('keyword', '');
                $name = $this->request->param('name', ''); // 前端使用name参数搜索
                $type = $this->request->param('type', ''); // 前端使用type参数
                
                $query = AiTool::with(['usageRecords', 'schoolPermissions']);
                
                // 分类筛选
                if ($category) {
                    $query->where('category', $category);
                }
                
                // 类型筛选（前端传type参数）
                if ($type) {
                    $query->where('category', $type);
                }
                
                // 状态筛选
                if ($status !== '') {
                    $query->where('status', $status);
                }
                
                // 关键词搜索
                if ($keyword) {
                    $query->where('name|code|description', 'like', "%{$keyword}%");
                }
                
                // 名称搜索（前端传name参数）
                if ($name) {
                    $query->where('name|code|description', 'like', "%{$name}%");
                }
                
                $total = $query->count();
                $list = $query->order('sort ASC, id DESC')
                             ->page($page, $limit)
                             ->select();
                
                // 处理数据
                $items = [];
                foreach ($list as $item) {
                    $itemData = $item->toArray();
                    $itemData['category_text'] = $item->category_text;
                    $itemData['status_text'] = $item->status_text;
                    $itemData['usage_count'] = $item->usageRecords->count();
                    $itemData['school_count'] = $item->schoolPermissions->where('status', AiToolSchool::STATUS_ENABLED)->count();
                    $itemData['statistics'] = $item->getStatistics();
                    // 保持status为数字，前端模板使用数字比较
                    $items[] = $itemData;
                }
                
                return json([
                    'code' => 0,
                    'msg' => '',
                    'count' => $total,
                    'data' => $items
                ]);
            }
            
            // 获取AI工具列表（用于下拉选择）
            $tools = AiTool::where('status', 1)
                ->field('id,name,code,category')
                ->order('sort ASC, name ASC')
                ->select();
            
            // 获取分类列表
            $categories = AiTool::getCategoryList();
            
            View::assign([
                'tools' => $tools,
                'categories' => $categories
            ]);
            return View::fetch('admin/ai_tool/index');
            
        } catch (\Exception $e) {
            Log::error("获取AI工具列表失败: " . $e->getMessage());
            if ($this->request->isAjax()) {
                return json(['code' => 500, 'msg' => '获取AI工具列表失败']);
            } else {
                $this->error('获取AI工具列表失败');
            }
        }
    }
    
    /**
     * 获取AI工具列表（API）
     */
    public function getList(Request $request)
    {
        try {
            $page = $request->param('page', 1);
            $limit = $request->param('limit', 10);
            $category = $request->param('category', '');
            $status = $request->param('status', '');
            $keyword = $request->param('keyword', '');
            
            $query = AiTool::with(['usageRecords', 'schoolPermissions']);
            
            // 分类筛选
            if ($category) {
                $query->where('category', $category);
            }
            
            // 状态筛选
            if ($status !== '') {
                $query->where('status', $status);
            }
            
            // 关键词搜索
            if ($keyword) {
                $query->where('name|code|description', 'like', "%{$keyword}%");
            }
            
            $total = $query->count();
            $list = $query->order('sort ASC, id DESC')
                         ->page($page, $limit)
                         ->select();
            
            // 处理数据
            foreach ($list as &$item) {
                $item->category_text = $item->category_text;
                $item->status_text = $item->status_text;
                $item->usage_count = $item->usageRecords->count();
                $item->school_count = $item->schoolPermissions->where('status', AiToolSchool::STATUS_ENABLED)->count();
                $item->statistics = $item->getStatistics();
                // 保持status为数字，前端模板使用数字比较
            }
            
            return $this->success([
                'list' => $list,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'categories' => AiTool::getCategoryList(),
                'statuses' => AiTool::getStatusList()
            ]);
            
        } catch (\Exception $e) {
            return $this->error('获取AI工具列表失败：' . $e->getMessage());
        }
    }
    
    /**
     * 获取AI工具详情
     */
    public function show($id)
    {
        try {
            $tool = AiTool::with(['usageRecords', 'schoolPermissions.school'])
                         ->find($id);
            
            if (!$tool) {
                return $this->error('AI工具不存在');
            }
            
            return $this->success(['tool' => $tool]);
            
        } catch (\Exception $e) {
            return $this->error('获取AI工具详情失败：' . $e->getMessage());
        }
    }
    
    /**
     * 获取AI工具详情（API）
     */
    public function getDetail()
    {
        $id = $this->request->param('id');
        return $this->show($id);
    }
    
    /**
     * 创建AI工具
     */
    public function store(Request $request)
    {
        try {
            $data = $request->post();
            
            // 验证数据
            $validate = new Validate([
                'name' => 'require|max:100',
                'code' => 'require|max:50|unique:ai_tool',
                'description' => 'max:500',
                'category' => 'require|in:content,analysis,assessment',
                'prompt_template' => 'max:2000',
                'api_config' => 'json',
                'icon' => 'max:255',
                'sort' => 'integer',
                'status' => 'in:0,1'
            ]);
            
            if (!$validate->check($data)) {
                return $this->error($validate->getError());
            }
            
            // 处理API配置
            $apiConfig = [];
            if (isset($data['provider'])) {
                $apiConfig['provider'] = $data['provider'];
            }
            if (isset($data['api_key'])) {
                $apiConfig['api_key'] = $data['api_key'];
            }
            if (isset($data['api_url'])) {
                $apiConfig['api_url'] = $data['api_url'];
            }
            if (isset($data['model'])) {
                $apiConfig['model'] = $data['model'];
            }
            if (isset($data['max_tokens'])) {
                $apiConfig['max_tokens'] = intval($data['max_tokens']);
            }
            if (isset($data['temperature'])) {
                $apiConfig['temperature'] = floatval($data['temperature']);
            }
            if (isset($data['system_prompt'])) {
                $apiConfig['system_prompt'] = $data['system_prompt'];
            }
            if (isset($data['output_format'])) {
                $apiConfig['output_format'] = json_decode($data['output_format'], true);
            }
            if (isset($data['input_params'])) {
                $apiConfig['input_params'] = json_decode($data['input_params'], true);
            }
            
            $tool = new AiTool();
            $tool->name = $data['name'];
            $tool->code = $data['code'];
            $tool->description = $data['description'] ?? '';
            $tool->category = $data['category'];
            $tool->prompt_template = $data['prompt_template'] ?? '';
            $tool->api_config = $apiConfig;
            $tool->icon = $data['icon'] ?? '';
            $tool->sort = $data['sort'] ?? 0;
            $tool->status = $data['status'] ?? AiTool::STATUS_ENABLED;
            
            if ($tool->save()) {
                return $this->success('AI工具创建成功', ['id' => $tool->id]);
            } else {
                return $this->error('AI工具创建失败');
            }
            
        } catch (\Exception $e) {
            return $this->error('创建AI工具失败：' . $e->getMessage());
        }
    }
    
    /**
     * 更新AI工具
     */
    public function update(Request $request, $id)
    {
        try {
            $tool = AiTool::find($id);
            if (!$tool) {
                return $this->error('AI工具不存在');
            }
            
            $data = $request->put();
            
            // 验证数据
            $validate = new Validate([
                'name' => 'require|max:100',
                'code' => 'require|max:50|unique:ai_tool,code,' . $id,
                'description' => 'max:500',
                'category' => 'require|in:content,analysis,assessment',
                'prompt_template' => 'max:2000',
                'api_config' => 'json',
                'icon' => 'max:255',
                'sort' => 'integer',
                'status' => 'in:0,1'
            ]);
            
            if (!$validate->check($data)) {
                return $this->error($validate->getError());
            }
            
            // 处理API配置
            $apiConfig = [];
            if (isset($data['provider'])) {
                $apiConfig['provider'] = $data['provider'];
            }
            if (isset($data['api_key'])) {
                $apiConfig['api_key'] = $data['api_key'];
            }
            if (isset($data['api_url'])) {
                $apiConfig['api_url'] = $data['api_url'];
            }
            if (isset($data['model'])) {
                $apiConfig['model'] = $data['model'];
            }
            if (isset($data['max_tokens'])) {
                $apiConfig['max_tokens'] = intval($data['max_tokens']);
            }
            if (isset($data['temperature'])) {
                $apiConfig['temperature'] = floatval($data['temperature']);
            }
            if (isset($data['system_prompt'])) {
                $apiConfig['system_prompt'] = $data['system_prompt'];
            }
            if (isset($data['output_format'])) {
                $apiConfig['output_format'] = json_decode($data['output_format'], true);
            }
            if (isset($data['input_params'])) {
                $apiConfig['input_params'] = json_decode($data['input_params'], true);
            }
            
            $tool->name = $data['name'];
            $tool->code = $data['code'];
            $tool->description = $data['description'] ?? '';
            $tool->category = $data['category'];
            $tool->prompt_template = $data['prompt_template'] ?? '';
            $tool->api_config = $apiConfig;
            $tool->icon = $data['icon'] ?? '';
            $tool->sort = $data['sort'] ?? 0;
            $tool->status = $data['status'] ?? AiTool::STATUS_ENABLED;
            
            if ($tool->save()) {
                return $this->success('AI工具更新成功');
            } else {
                return $this->error('AI工具更新失败');
            }
            
        } catch (\Exception $e) {
            return $this->error('更新AI工具失败：' . $e->getMessage());
        }
    }
    
    /**
     * 删除AI工具
     */
    public function destroy($id)
    {
        try {
            $tool = AiTool::find($id);
            if (!$tool) {
                return $this->error('AI工具不存在');
            }
            
            // 检查是否有使用记录
            if ($tool->usageRecords()->count() > 0) {
                return $this->error('该工具已有使用记录，无法删除');
            }
            
            // 删除学校权限关联
            AiToolSchool::where('tool_id', $id)->delete();
            
            if ($tool->delete()) {
                return $this->success('AI工具删除成功');
            } else {
                return $this->error('AI工具删除失败');
            }
            
        } catch (\Exception $e) {
            return $this->error('删除AI工具失败：' . $e->getMessage());
        }
    }
    
    /**
     * 批量操作
     */
    public function batch(Request $request)
    {
        try {
            $action = $request->param('action');
            $ids = $request->param('ids');
            
            if (!$ids || !is_array($ids)) {
                return $this->error('请选择要操作的工具');
            }
            
            switch ($action) {
                case 'enable':
                    AiTool::whereIn('id', $ids)->update(['status' => AiTool::STATUS_ENABLED]);
                    return $this->success('批量启用成功');
                    
                case 'disable':
                    AiTool::whereIn('id', $ids)->update(['status' => AiTool::STATUS_DISABLED]);
                    return $this->success('批量禁用成功');
                    
                case 'delete':
                    // 检查是否有使用记录
                    $hasUsage = AiUsage::whereIn('tool_id', $ids)->count();
                    if ($hasUsage > 0) {
                        return $this->error('选中的工具中有使用记录，无法删除');
                    }
                    
                    // 删除学校权限关联
                    AiToolSchool::whereIn('tool_id', $ids)->delete();
                    
                    AiTool::whereIn('id', $ids)->delete();
                    return $this->success('批量删除成功');
                    
                default:
                    return $this->error('无效的操作类型');
            }
            
        } catch (\Exception $e) {
            return $this->error('批量操作失败：' . $e->getMessage());
        }
    }
    
    /**
     * 获取学校列表（用于授权）
     */
    public function getSchools()
    {
        try {
            $schools = School::where('status', School::STATUS_ENABLED)
                           ->field('id,name,code')
                           ->select();
            
            return $this->success(['schools' => $schools]);
            
        } catch (\Exception $e) {
            return $this->error('获取学校列表失败：' . $e->getMessage());
        }
    }
    
    /**
     * 授权工具给学校
     */
    public function authorize(Request $request)
    {
        try {
            $toolId = $request->param('tool_id');
            $schoolIds = $request->param('school_ids');
            $dailyLimit = $request->param('daily_limit', 100);
            $monthlyLimit = $request->param('monthly_limit', 3000);
            
            if (!$toolId || !$schoolIds || !is_array($schoolIds)) {
                return $this->error('参数错误');
            }
            
            // 检查工具是否存在
            $tool = AiTool::find($toolId);
            if (!$tool) {
                return $this->error('AI工具不存在');
            }
            
            // 批量授权
            $result = AiToolSchool::batchAuthorize(
                [$toolId], 
                $schoolIds, 
                ['daily_limit' => $dailyLimit, 'monthly_limit' => $monthlyLimit]
            );
            
            if ($result) {
                return $this->success('授权成功');
            } else {
                return $this->error('授权失败');
            }
            
        } catch (\Exception $e) {
            return $this->error('授权失败：' . $e->getMessage());
        }
    }
    
    /**
     * 取消授权
     */
    public function revoke(Request $request)
    {
        try {
            $toolId = $request->param('tool_id');
            $schoolIds = $request->param('school_ids');
            
            if (!$toolId || !$schoolIds || !is_array($schoolIds)) {
                return $this->error('参数错误');
            }
            
            $result = AiToolSchool::batchRevoke([$toolId], $schoolIds);
            
            if ($result) {
                return $this->success('取消授权成功');
            } else {
                return $this->error('取消授权失败');
            }
            
        } catch (\Exception $e) {
            return $this->error('取消授权失败：' . $e->getMessage());
        }
    }
    
    /**
     * 获取使用统计
     */
    public function statistics(Request $request)
    {
        try {
            $toolId = $request->param('tool_id');
            $schoolId = $request->param('school_id');
            $dateRange = $request->param('date_range', 'month');
            
            $statistics = AiUsage::getUsageStatistics($schoolId, $toolId, $dateRange);
            
            return $this->success(['statistics' => $statistics]);
            
        } catch (\Exception $e) {
            return $this->error('获取统计信息失败：' . $e->getMessage());
        }
    }
    
    /**
     * 添加AI工具页面
     */
    public function add()
    {
        return View::fetch('admin/ai_tool/add');
    }
    
    /**
     * 编辑AI工具页面
     */
    public function edit()
    {
        $id = $this->request->param('id');
        $tool = AiTool::find($id);
        if (!$tool) {
            $this->error('AI工具不存在');
        }
        View::assign('tool', $tool);
        return View::fetch('admin/ai_tool/edit');
    }
    
    /**
     * 删除AI工具
     */
    public function delete()
    {
        $id = $this->request->param('id');
        try {
            $tool = AiTool::find($id);
            if (!$tool) {
                return $this->error('AI工具不存在');
            }
            
            if ($tool->delete()) {
                return $this->success('删除成功', '删除成功', 1);
            } else {
                return $this->error('删除失败');
            }
        } catch (\Exception $e) {
            return $this->error('删除失败：' . $e->getMessage());
        }
    }
    
    /**
     * 查看AI工具详情页面
     */
    public function detail()
    {
        $id = $this->request->param('id');
        $tool = AiTool::with(['usageRecords', 'schoolPermissions.school'])->find($id);
        if (!$tool) {
            $this->error('AI工具不存在');
        }
        View::assign('tool', $tool);
        return View::fetch('admin/ai_tool/detail');
    }
    
    /**
     * 修改AI工具状态
     */
    public function changeStatus()
    {
        $id = $this->request->param('id');
        $status = $this->request->param('status');
        
        try {
            $tool = AiTool::find($id);
            if (!$tool) {
                return $this->error('AI工具不存在');
            }
            
            $tool->status = $status;
            if ($tool->save()) {
                return $this->success('状态修改成功');
            } else {
                return $this->error('状态修改失败');
            }
        } catch (\Exception $e) {
            return $this->error('状态修改失败：' . $e->getMessage());
        }
    }
    
    /**
     * AI工具配置页面
     */
    public function config()
    {
        $id = $this->request->param('id');
        $tool = AiTool::find($id);
        if (!$tool) {
            $this->error('AI工具不存在');
        }
        View::assign('tool', $tool);
        return View::fetch('admin/ai_tool/config');
    }
    
    /**
     * 启用AI工具
     */
    public function enable()
    {
        $id = $this->request->param('id');
        if (!$id) {
            return $this->error('参数缺失');
        }
        $tool = \app\model\AiTool::find($id);
        if (!$tool) {
            return $this->error('AI工具不存在');
        }
        $tool->status = 1;
        if ($tool->save()) {
        return $this->success(null, '启用成功', 1);
        } else {
            return $this->error('启用失败');
        }
    }
    
    /**
     * 停用AI工具
     */
    public function disable()
    {
        $id = $this->request->param('id');
        if (!$id) {
            return $this->error('参数缺失');
        }
        $tool = \app\model\AiTool::find($id);
        if (!$tool) {
            return $this->error('AI工具不存在');
        }
        $tool->status = 0;
        if ($tool->save()) {
        return $this->success(null, '停用成功', 1);
        } else {
            return $this->error('停用失败');
        }
    }
} 