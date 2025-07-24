<?php
namespace app\controller;

use app\BaseController;
use app\model\AiTool;
use app\model\AiToolSchool;
use app\model\AiUsage;
use app\model\User;
use think\Request;
use think\Validate;

/**
 * AI工具调用服务控制器
 */
class AiService extends BaseController
{
    /**
     * 获取可用工具列表
     */
    public function getTools(Request $request)
    {
        try {
            $schoolId = $request->param('school_id');
            $category = $request->param('category', '');
            
            if (!$schoolId) {
                return $this->error('学校ID不能为空');
            }
            
            // 获取学校可用的工具
            $availableTools = AiToolSchool::getSchoolAvailableTools($schoolId);
            
            $tools = [];
            foreach ($availableTools as $permission) {
                $tool = $permission->tool;
                if (!$tool) continue;
                
                // 分类筛选
                if ($category && $tool->category !== $category) {
                    continue;
                }
                
                // 获取使用统计
                $usageStats = $permission->getUsageStatistics();
                
                $tools[] = [
                    'id' => $tool->id,
                    'name' => $tool->name,
                    'code' => $tool->code,
                    'description' => $tool->description,
                    'category' => $tool->category,
                    'category_text' => $tool->category_text,
                    'icon' => $tool->icon,
                    'prompt_template' => $tool->prompt_template,
                    'usage_limits' => [
                        'daily_limit' => $permission->daily_limit,
                        'monthly_limit' => $permission->monthly_limit,
                        'today_usage' => $usageStats['today_usage'],
                        'month_usage' => $usageStats['month_usage'],
                        'daily_remaining' => $usageStats['daily_remaining'],
                        'monthly_remaining' => $usageStats['monthly_remaining']
                    ]
                ];
            }
            
            return $this->success(['tools' => $tools]);
            
        } catch (\Exception $e) {
            return $this->error('获取工具列表失败：' . $e->getMessage());
        }
    }
    
    /**
     * 调用AI工具
     */
    public function call(Request $request)
    {
        try {
            $data = $request->post();
            
            // 验证数据
            $validate = new Validate([
                'tool_code' => 'require',
                'school_id' => 'require|integer',
                'user_id' => 'require|integer',
                'params' => 'array'
            ]);
            
            if (!$validate->check($data)) {
                return $this->error($validate->getError());
            }
            
            $toolCode = $data['tool_code'];
            $schoolId = $data['school_id'];
            $userId = $data['user_id'];
            $params = $data['params'] ?? [];
            
            // 获取工具信息
            $tool = AiTool::getByCode($toolCode);
            if (!$tool) {
                return $this->error('AI工具不存在或已禁用');
            }
            
            // 检查权限
            $permissionCheck = AiToolSchool::checkUsageLimit($schoolId, $tool->id);
            if (!$permissionCheck['allowed']) {
                return $this->error($permissionCheck['message']);
            }
            
            // 检查用户是否存在
            $user = User::find($userId);
            if (!$user) {
                return $this->error('用户不存在');
            }
            
            // 构建提示词
            $prompt = $this->buildPrompt($tool->prompt_template, $params);
            
            // 调用AI服务（这里需要集成具体的AI API）
            $result = $this->callAiService($tool, $prompt);
            
            // 记录使用情况
            $usageData = [
                'tool_id' => $tool->id,
                'user_id' => $userId,
                'school_id' => $schoolId,
                'request_data' => [
                    'tool_code' => $toolCode,
                    'params' => $params,
                    'prompt' => $prompt
                ],
                'response_data' => $result,
                'tokens_used' => $result['tokens_used'] ?? 0,
                'cost' => $result['cost'] ?? 0,
                'status' => $result['success'] ? AiUsage::STATUS_SUCCESS : AiUsage::STATUS_FAILED,
                'error_message' => $result['error'] ?? null
            ];
            
            AiUsage::recordUsage($usageData);
            
            if ($result['success']) {
                return $this->success('调用成功', [
                    'result' => $result['data'],
                    'tokens_used' => $result['tokens_used'] ?? 0,
                    'cost' => $result['cost'] ?? 0
                ]);
            } else {
                return $this->error('调用失败：' . ($result['error'] ?? '未知错误'));
            }
            
        } catch (\Exception $e) {
            return $this->error('调用AI工具失败：' . $e->getMessage());
        }
    }
    
    /**
     * 构建提示词
     */
    private function buildPrompt($template, $params)
    {
        $prompt = $template;
        
        foreach ($params as $key => $value) {
            $prompt = str_replace('{' . $key . '}', $value, $prompt);
        }
        
        return $prompt;
    }
    
    /**
     * 调用AI服务
     */
    private function callAiService($tool, $prompt)
    {
        try {
            // 这里需要集成具体的AI API，比如OpenAI、百度文心等
            // 目前返回模拟数据
            
            $apiConfig = $tool->api_config ?? [];
            $model = $apiConfig['model'] ?? 'gpt-3.5-turbo';
            $temperature = $apiConfig['temperature'] ?? 0.7;
            $maxTokens = $apiConfig['max_tokens'] ?? 2000;
            
            // 模拟AI调用
            $response = $this->mockAiResponse($tool->code, $prompt);
            
            return [
                'success' => true,
                'data' => $response,
                'tokens_used' => rand(100, 500),
                'cost' => round(rand(1, 10) / 1000, 4)
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 模拟AI响应
     */
    private function mockAiResponse($toolCode, $prompt)
    {
        $responses = [
            'lecture_generator' => '根据您提供的课程信息，我为您生成了一份详细的教学讲稿...',
            'homework_generator' => '基于课程内容，我生成了以下练习题：\n1. 选择题...\n2. 填空题...\n3. 简答题...',
            'question_bank' => '根据知识点要求，我生成了包含多种题型的题库：\n选择题：10道\n填空题：8道\n简答题：5道',
            'course_analysis' => '课程分析结果：\n1. 教学内容设计合理\n2. 建议增加互动环节\n3. 可以适当调整难度梯度',
            'lesson_plan' => '教案生成完成：\n教学目标：...\n教学重点：...\n教学过程：...\n教学反思：...',
            'teaching_reflection' => '教学反思：\n1. 本节课的优点：...\n2. 需要改进的地方：...\n3. 下次教学建议：...'
        ];
        
        return $responses[$toolCode] ?? 'AI工具响应内容';
    }
    
    /**
     * 获取使用记录
     */
    public function getUsageHistory(Request $request)
    {
        try {
            $userId = $request->param('user_id');
            $schoolId = $request->param('school_id');
            $toolId = $request->param('tool_id');
            $page = $request->param('page', 1);
            $limit = $request->param('limit', 10);
            
            if (!$userId || !$schoolId) {
                return $this->error('用户ID和学校ID不能为空');
            }
            
            $query = AiUsage::with(['tool'])
                           ->where('user_id', $userId)
                           ->where('school_id', $schoolId);
            
            if ($toolId) {
                $query->where('tool_id', $toolId);
            }
            
            $total = $query->count();
            $list = $query->order('create_time DESC')
                         ->page($page, $limit)
                         ->select();
            
            return $this->success([
                'list' => $list,
                'total' => $total,
                'page' => $page,
                'limit' => $limit
            ]);
            
        } catch (\Exception $e) {
            return $this->error('获取使用记录失败：' . $e->getMessage());
        }
    }
    
    /**
     * 获取使用统计
     */
    public function getUsageStatistics(Request $request)
    {
        try {
            $userId = $request->param('user_id');
            $schoolId = $request->param('school_id');
            
            if (!$userId || !$schoolId) {
                return $this->error('用户ID和学校ID不能为空');
            }
            
            // 获取用户统计
            $userStats = [
                'today_usage' => AiUsage::getUserTodayUsage($userId),
                'month_usage' => AiUsage::getUserMonthUsage($userId),
                'total_usage' => AiUsage::where('user_id', $userId)->count()
            ];
            
            // 获取学校统计
            $schoolStats = [
                'today_usage' => AiUsage::getSchoolTodayUsage($schoolId),
                'month_usage' => AiUsage::getSchoolMonthUsage($schoolId),
                'total_usage' => AiUsage::where('school_id', $schoolId)->count()
            ];
            
            return $this->success([
                'user_statistics' => $userStats,
                'school_statistics' => $schoolStats
            ]);
            
        } catch (\Exception $e) {
            return $this->error('获取统计信息失败：' . $e->getMessage());
        }
    }
} 