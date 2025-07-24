<?php
namespace app\service;

use app\model\AiTool;
use app\model\AiUsage;
use app\model\AiToolSchool;
use app\service\ai\AiProviderInterface;
use app\service\ai\DeepSeekProvider;
use app\service\ai\MockProvider;
use app\service\AiToolConfigService;
use think\facade\Log;

class AiService
{
    protected static array $providers = [
        'deepseek' => DeepSeekProvider::class,
        'mock' => MockProvider::class,
    ];

    /**
     * 生成AI内容并记录使用
     * @param string $toolCode AI工具的编码
     * @param array $params 用户输入的参数
     * @param int $userId 用户ID
     * @param int $schoolId 学校ID
     * @return array 包含生成内容和状态的数组
     */
    public function generateContent(string $toolCode, array $params, int $userId = 0, int $schoolId = 0): array
    {
        try {
            // 1. 获取AI工具配置
            $tool = AiTool::where('code', $toolCode)->where('status', AiTool::STATUS_ENABLED)->find();
            if (!$tool) {
                return ['success' => false, 'message' => 'AI工具不存在或已禁用'];
            }

            // 2. 验证输入参数（使用新的配置化系统）
            $validation = AiToolConfigService::validateParams($toolCode, $params);
            if (!$validation['valid']) {
                return ['success' => false, 'message' => '参数验证失败：' . implode(', ', $validation['errors'])];
            }

            $validatedParams = $validation['params'];

            // 3. 检查学校权限
            $hasPermission = $tool->schoolPermissions()
                ->where('school_id', $schoolId)
                ->where('status', 1)
                ->count() > 0;

            if (!$hasPermission) {
                return ['success' => false, 'message' => '学校无权限使用此工具'];
            }

            // 4. 检查使用限制
            $usageCheck = $this->checkUsageLimit($tool->id, $userId, $schoolId);
            if (!$usageCheck['success']) {
                return $usageCheck;
            }

            // 5. 获取API配置
            $apiConfig = $tool->api_config;
            $providerName = $apiConfig['provider'] ?? 'deepseek';
            $apiKey = $apiConfig['api_key'] ?? '';
            $apiUrl = $apiConfig['api_url'] ?? '';
            $model = $apiConfig['model'] ?? 'deepseek-chat';
            $maxTokens = $apiConfig['max_tokens'] ?? 2000;
            $temperature = $apiConfig['temperature'] ?? 0.7;

            if (!isset(self::$providers[$providerName])) {
                return ['success' => false, 'message' => '不支持的 AI 提供商: ' . $providerName];
            }

            // 6. 构建提示词（使用新的配置化系统）
            $systemPrompt = AiToolConfigService::buildSystemPrompt($toolCode, $validatedParams);
            $userPrompt = AiToolConfigService::buildUserPrompt($toolCode, $validatedParams);

            // 7. 实例化 AI Provider
            $providerClass = self::$providers[$providerName];
            /** @var AiProviderInterface $providerInstance */
            $providerInstance = new $providerClass($apiKey, $apiUrl, $model);

            // 8. 调用 AI 生成内容
            $generatedContent = $providerInstance->generate($toolCode, [
                'system_prompt' => $systemPrompt,
                'user_prompt' => $userPrompt,
                'max_tokens' => $maxTokens,
                'temperature' => $temperature
            ]);

            // 9. 记录使用情况
            $usage = new AiUsage();
            $usage->tool_id = $tool->id;
            $usage->user_id = $userId;
            $usage->school_id = $schoolId;
            $usage->request_data = json_encode([
                'system_prompt' => $systemPrompt,
                'user_prompt' => $userPrompt,
                'params' => $validatedParams
            ], JSON_UNESCAPED_UNICODE);
            $usage->response_data = $generatedContent;
            $usage->status = AiUsage::STATUS_SUCCESS;
            $usage->cost = $apiConfig['cost_per_request'] ?? 0;
            $usage->save();

            return [
                'success' => true, 
                'content' => $generatedContent,
                'usage_info' => $usageCheck['usage_info'] ?? null
            ];

        } catch (\Exception $e) {
            Log::error("AI内容生成服务异常: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            // 记录失败的使用情况
            if (isset($tool) && $tool) {
                $usage = new AiUsage();
                $usage->tool_id = $tool->id;
                $usage->user_id = $userId;
                $usage->school_id = $schoolId;
                $usage->request_data = json_encode([
                    'error' => $e->getMessage(),
                    'params' => $params ?? []
                ], JSON_UNESCAPED_UNICODE);
                $usage->response_data = 'Error: ' . $e->getMessage();
                $usage->status = AiUsage::STATUS_FAILED;
                $usage->save();
            }
            
            return ['success' => false, 'message' => '内容生成服务异常：' . $e->getMessage()];
        }
    }

    /**
     * 检查使用限制
     * @param int $toolId 工具ID
     * @param int $userId 用户ID
     * @param int $schoolId 学校ID
     * @return array 检查结果
     */
    protected function checkUsageLimit(int $toolId, int $userId, int $schoolId): array
    {
        try {
            // 获取学校对该工具的使用限制
            $schoolPermission = AiToolSchool::where('tool_id', $toolId)
                ->where('school_id', $schoolId)
                ->where('status', 1)
                ->find();

            if (!$schoolPermission) {
                return ['success' => false, 'message' => '学校无权限使用此工具'];
            }

            $dailyLimit = $schoolPermission->daily_limit ?? 0;
            $monthlyLimit = $schoolPermission->monthly_limit ?? 0;

            // 检查每日使用限制
            if ($dailyLimit > 0) {
                $todayUsage = AiUsage::where('tool_id', $toolId)
                    ->where('school_id', $schoolId)
                    ->whereTime('create_time', 'today')
                    ->count();

                if ($todayUsage >= $dailyLimit) {
                    return ['success' => false, 'message' => '今日使用次数已达上限'];
                }
            }

            // 检查每月使用限制
            if ($monthlyLimit > 0) {
                $monthlyUsage = AiUsage::where('tool_id', $toolId)
                    ->where('school_id', $schoolId)
                    ->whereTime('create_time', 'month')
                    ->count();

                if ($monthlyUsage >= $monthlyLimit) {
                    return ['success' => false, 'message' => '本月使用次数已达上限'];
                }
            }

            return [
                'success' => true,
                'usage_info' => [
                    'daily_limit' => $dailyLimit,
                    'monthly_limit' => $monthlyLimit,
                    'daily_used' => $todayUsage ?? 0,
                    'monthly_used' => $monthlyUsage ?? 0
                ]
            ];

        } catch (\Exception $e) {
            Log::error("检查使用限制失败: " . $e->getMessage());
            return ['success' => false, 'message' => '检查使用限制失败'];
        }
    }

    /**
     * 渲染 Prompt 模板（保留向后兼容）
     * @param string $template 模板字符串
     * @param array $params 参数数组
     * @return string 渲染后的 Prompt
     */
    protected function renderPrompt(string $template, array $params): string
    {
        foreach ($params as $key => $value) {
            $template = str_replace("{\e}" . $key . "{\e}", $value, $template); // 使用{\e}作为占位符边界
        }
        return $template;
    }

    /**
     * 获取可用的AI工具列表（此方法已不再从这里返回模拟数据，改为从数据库获取）
     */
    public static function getAvailableTools(int $schoolId, int $userId): array
    {
        // 此方法不再直接返回模拟数据，现在由 AiToolController 直接查询数据库
        return [];
    }

    /**
     * 获取AI工具使用历史（此方法改为从数据库获取）
     */
    public static function getUsageHistory(array $params): array
    {
        // 实际应该从数据库中获取 AiUsage 记录
        $query = AiUsage::with(['tool', 'user'])
            ->order('create_time', 'desc');

        // 根据参数进行筛选
        if (isset($params['tool_id']) && $params['tool_id']) {
            $query->where('tool_id', $params['tool_id']);
        }
        if (isset($params['tool_code']) && $params['tool_code']) {
            // 使用子查询来筛选工具代码
            $toolIds = AiTool::where('code', $params['tool_code'])->column('id');
            if (!empty($toolIds)) {
                $query->whereIn('tool_id', $toolIds);
            } else {
                // 如果没有找到对应的工具，返回空结果
                return [
                    'list' => [],
                    'total' => 0,
                    'page' => $params['page'] ?? 1,
                    'limit' => $params['limit'] ?? 10
                ];
            }
        }
        if (isset($params['user_id']) && $params['user_id']) {
            $query->where('user_id', $params['user_id']);
        }
        if (isset($params['school_id']) && $params['school_id']) {
            $query->where('school_id', $params['school_id']);
        }
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', $params['status']);
        }
        if (isset($params['start_date']) && $params['start_date']) {
            $query->whereTime('create_time', '>=', $params['start_date']);
        }
        if (isset($params['end_date']) && $params['end_date']) {
            $query->whereTime('create_time', '<=', $params['end_date'] . ' 23:59:59');
        }

        $total = $query->count();
        $list = $query->limit($params['limit'] ?? 10)
            ->page($params['page'] ?? 1)
            ->select();

        $formattedList = [];
        foreach ($list as $item) {
            $formattedList[] = [
                'id' => $item->id,
                'tool_name' => $item->tool->name ?? '未知工具',
                'tool_code' => $item->tool->code ?? 'unknown',
                'user_name' => $item->user->username ?? '未知用户',
                'status' => $item->status,
                'created_at' => $item->create_time,
                'content_preview' => mb_substr($item->response_data, 0, 100) . '...', // 截取部分内容作为预览
                'prompt_preview' => mb_substr($item->request_data, 0, 100) . '...',
                'request_data' => $item->request_data,
                'response_data' => $item->response_data,
                'error_message' => $item->error_message ?? null
            ];
        }

        return [
            'list' => $formattedList,
            'total' => $total,
            'page' => $params['page'] ?? 1,
            'limit' => $params['limit'] ?? 10
        ];
    }

    /**
     * 静态方法包装器，用于向后兼容
     * @param string $provider 提供商名称
     * @param string $toolCode AI工具编码
     * @param array $params 参数
     * @return string 生成的内容
     */
    public static function generate(string $provider, string $toolCode, array $params = []): string
    {
        $aiService = new self();
        $result = $aiService->generateContent($toolCode, $params);
        
        if (!$result['success']) {
            throw new \Exception($result['message']);
        }
        
        return $result['content'];
    }

    /**
     * 获取AI工具使用统计（此方法改为从数据库获取）
     */
    public static function getUsageStatistics(int $userId, int $schoolId): array
    {
        // 实际应该从数据库中获取 AiUsage 记录并进行统计
        $totalUsage = AiUsage::where('user_id', $userId)
            ->where('school_id', $schoolId)
            ->where('status', AiUsage::STATUS_SUCCESS)
            ->count();

        $monthlyUsage = AiUsage::where('user_id', $userId)
            ->where('school_id', $schoolId)
            ->where('status', AiUsage::STATUS_SUCCESS)
            ->whereTime('create_time', 'month')
            ->count();

        $availableToolsCount = AiTool::where('status', AiTool::STATUS_ENABLED)->count();

        $mostUsedTool = AiUsage::with(['tool'])
            ->where('user_id', $userId)
            ->where('school_id', $schoolId)
            ->where('status', AiUsage::STATUS_SUCCESS)
            ->group('tool_id')
            ->field('tool_id, count(*) as total')
            ->order('total', 'desc')
            ->find();

        return [
            'total_usage' => $totalUsage,
            'monthly_usage' => $monthlyUsage,
            'available_tools' => $availableToolsCount,
            'most_used_tool' => $mostUsedTool->tool->name ?? '无',
            'success_rate' => 100.0 // 暂时假设，实际需计算
        ];
    }
} 