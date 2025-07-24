<?php
namespace app\controller\api\teacher;

use app\controller\api\BaseController;
use app\service\AiService;
use app\service\ContentService;
use app\model\ContentLibrary;
use think\Request;
use think\facade\Validate;

class AiController extends BaseController
{
    /**
     * 生成 AI 内容并保存草稿
     * POST /api/teacher/ai/generate
     */
    public function generate(Request $request)
    {
        $data = $request->post();
        Validate::rule([
            'tool_code'     => 'require',
            'prompt_params' => 'array',
            'provider'      => 'alphaDash',
            'save_to_library' => 'boolean'
        ])->check($data);

        $aiService = new AiService();
        $result = $aiService->generateContent($data['tool_code'], $data['prompt_params'] ?? [], $request->user->id, $request->user->primary_school_id);
        
        if (!$result['success']) {
            return $this->error($result['message']);
        }
        
        $content = $result['content'];
        
        $result = [
            'content' => $content
        ];

        // 如果要求保存到内容库
        if (!empty($data['save_to_library'])) {
            $draft = ContentLibrary::createFromAi(
                $data['tool_code'] . '草稿_' . date('YmdHis'),
                $content,
                $data['tool_code'],
                $request->userId,
                $request->user->primary_school_id
            );

            $result['content_id'] = $draft->id;
            $result['content_name'] = $draft->name;
        }

        return $this->success($result, '生成成功');
    }

    /**
     * 获取可用AI工具列表
     * GET /api/teacher/ai/tools
     */
    public function getTools(Request $request)
    {
        $userInfo = $request->user;
        
        $tools = AiService::getAvailableTools($userInfo->primary_school_id, $userInfo->id);
        
        return $this->success([
            'tools' => $tools,
            'categories' => [
                'content' => '内容生成',
                'analysis' => '分析工具',
                'assessment' => '评估工具'
            ]
        ]);
    }

    /**
     * 获取AI工具使用历史
     * GET /api/teacher/ai/history
     */
    public function getHistory(Request $request)
    {
        $params = $request->get();
        $userInfo = $request->user;
        
        $history = AiService::getUsageHistory([
            'user_id' => $userInfo->id,
            'school_id' => $userInfo->primary_school_id,
            'tool_code' => $params['tool_code'] ?? '',
            'status' => $params['status'] ?? '',
            'page' => $params['page'] ?? 1,
            'limit' => $params['limit'] ?? 20
        ]);
        
        return $this->success($history);
    }

    /**
     * 获取AI工具使用统计
     * GET /api/teacher/ai/statistics
     */
    public function getStatistics(Request $request)
    {
        $userInfo = $request->user;
        
        $statistics = AiService::getUsageStatistics($userInfo->id, $userInfo->primary_school_id);
        
        return $this->success($statistics);
    }

    /**
     * 批量生成并保存到内容库
     * POST /api/teacher/ai/batch-generate
     */
    public function batchGenerate(Request $request)
    {
        $data = $request->post();
        Validate::rule([
            'tool_code'     => 'require',
            'prompt_params' => 'array',
            'batch_count'   => 'integer|between:1,10',
            'provider'      => 'alphaDash'
        ])->check($data);

        $provider = $data['provider'] ?? 'deepseek';
        $batchCount = $data['batch_count'] ?? 1;
        $results = [];

        for ($i = 0; $i < $batchCount; $i++) {
            try {
                $aiService = new AiService();
            $result = $aiService->generateContent($data['tool_code'], $data['prompt_params'] ?? [], $request->user->id, $request->user->primary_school_id);
            
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }
            
            $content = $result['content'];
                
                $draft = ContentLibrary::createFromAi(
                    $data['tool_code'] . '草稿_' . date('YmdHis') . '_' . ($i + 1),
                    $content,
                    $data['tool_code'],
                    $request->userId,
                    $request->user->primary_school_id
                );

                $results[] = [
                    'success' => true,
                    'content_id' => $draft->id,
                    'content_name' => $draft->name,
                    'content' => $content
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $this->success([
            'results' => $results,
            'total_count' => $batchCount,
            'success_count' => count(array_filter($results, fn($r) => $r['success']))
        ], '批量生成完成');
    }

    /**
     * 从内容库重新生成AI内容
     * POST /api/teacher/ai/regenerate
     */
    public function regenerate(Request $request)
    {
        $data = $request->post();
        Validate::rule([
            'content_id'    => 'require|integer',
            'tool_code'     => 'require',
            'prompt_params' => 'array',
            'provider'      => 'alphaDash'
        ])->check($data);

        // 检查内容是否存在且属于当前用户
        $content = ContentLibrary::where('id', $data['content_id'])
                                ->where('creator_id', $request->user->id)
                                ->where('source_type', 'ai_generate')
                                ->find();

        if (!$content) {
            return $this->error('内容不存在或无权限重新生成');
        }

        try {
            $aiService = new AiService();
            $result = $aiService->generateContent($data['tool_code'], $data['prompt_params'] ?? [], $request->user->id, $request->user->primary_school_id);
            
            if (!$result['success']) {
                return $this->error($result['message']);
            }
            
            $newContent = $result['content'];
            
            // 更新内容
            $content->content = $newContent;
            $content->ai_tool_code = $data['tool_code'];
            $content->save();

            return $this->success([
                'content_id' => $content->id,
                'content' => $newContent
            ], '重新生成成功');

        } catch (\Exception $e) {
            return $this->error('重新生成失败：' . $e->getMessage());
        }
    }
} 