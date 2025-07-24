<?php
namespace app\controller\api\teacher;

use app\controller\api\BaseController;
use app\model\ContentLibrary;
use think\Request;
use think\facade\Log;

/**
 * 教师端内容预览控制器
 */
class ContentPreviewController extends BaseController
{
    /**
     * 获取内容详情用于预览
     * GET /api/teacher/preview/content/:id
     */
    public function show(Request $request, $id)
    {
        try {
            // 验证ID
            if (empty($id)) {
                return $this->error('内容ID不能为空', 400);
            }

            // 获取内容（只获取已通过审核或属于当前用户的草稿）
            $content = ContentLibrary::where('id', $id)
                ->where(function($query) use ($request) {
                    // 如果是已发布内容，任何人可看（或根据具体可见性判断）
                    // 如果是草稿，则必须是创建者本人
                    $query->where('status', ContentLibrary::STATUS_APPROVED)
                          ->whereOr([
                              'creator_id' => $request->userId, // 从JWT获取的用户ID
                              'status' => ContentLibrary::STATUS_DRAFT
                          ]);
                })
                ->find();

            if (!$content) {
                return $this->error('内容不存在或无权限查看', 404);
            }

            // 返回内容详情
            return $this->success([
                'id' => $content->id,
                'name' => $content->name,
                'content' => $content->content,
                'file_type_text' => $content->file_type_text,
                'source_type_text' => $content->source_type_text,
                'create_time' => $content->create_time,
                // 可以根据需要返回更多字段
            ], '获取成功', 200);

        } catch (\Exception $e) {
            Log::error("获取内容预览失败: " . $e->getMessage());
            return $this->error('获取内容预览失败：' . $e->getMessage(), 500);
        }
    }
} 