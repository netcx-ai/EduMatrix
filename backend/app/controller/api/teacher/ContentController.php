<?php
declare(strict_types=1);

namespace app\controller\api\teacher;

use app\controller\api\BaseController;
use app\model\ContentLibrary;
use app\model\File;
use app\service\ContentService;
use app\service\AiService;
use app\service\DocumentExportService;
use think\Request;
use think\Log;

class ContentController extends BaseController
{
    /**
     * 获取内容库列表
     * GET /api/teacher/content/list
     */
    public function getList(Request $request)
    {
        $params = $request->get();
        $userInfo = $request->user;
        
        $queryParams = [
            'school_id' => $userInfo->primary_school_id,
            'creator_id' => $userInfo->id,
            'page' => $params['page'] ?? 1,
            'limit' => $params['limit'] ?? 20,
            'keyword' => $params['keyword'] ?? '',
            'file_type' => $params['file_type'] ?? '',
            'status' => $params['status'] ?? '',
            'source_type' => $params['source_type'] ?? ''
        ];
        
        $list = ContentLibrary::getContentList($queryParams);
        
        // 为每个内容添加关联的文件信息
        $contentList = [];
        foreach ($list as $content) {
            $contentData = $content->toArray();
            
            // 查询关联的文件
            $relatedFiles = \app\model\File::where('content_id', $content->id)
                ->where('status', \app\model\File::STATUS_NORMAL)
                ->field('id, file_name, original_name, file_path, file_size, file_type, mime_type, source_type, ai_tool_code')
                ->select();
            
            $contentData['related_files'] = $relatedFiles ? $relatedFiles->toArray() : [];
            
            // 调试信息
            if (count($contentData['related_files']) > 0) {
                error_log("内容ID {$content->id} 关联了 " . count($contentData['related_files']) . " 个文件");
            }
            
            $contentList[] = $contentData;
        }
        
        // 计算总数时也要过滤已删除的内容
        $totalQuery = ContentLibrary::where('is_deleted', 0);
        if (!empty($queryParams['school_id'])) {
            $totalQuery->where('school_id', $queryParams['school_id']);
        }
        if (!empty($queryParams['creator_id'])) {
            $totalQuery->where('creator_id', $queryParams['creator_id']);
        }
        if (!empty($queryParams['status'])) {
            $totalQuery->where('status', $queryParams['status']);
        }
        if (!empty($queryParams['file_type'])) {
            $totalQuery->where('file_type', $queryParams['file_type']);
        }
        if (!empty($queryParams['source_type'])) {
            $totalQuery->where('source_type', $queryParams['source_type']);
        }
        if (!empty($queryParams['keyword'])) {
            $totalQuery->where('name', 'like', '%' . $queryParams['keyword'] . '%');
        }
        
        return $this->success([
            'list' => $contentList,
            'total' => $totalQuery->count(),
            'file_types' => ContentLibrary::getFileTypeList(),
            'source_types' => ContentLibrary::getSourceTypeList(),
            'statuses' => ContentLibrary::getStatusList()
        ]);
    }
    
    /**
     * 获取内容详情
     * GET /api/teacher/content/detail/{id}
     */
    public function getDetail(Request $request, $id)
    {
        $content = ContentLibrary::with(['creator', 'school', 'college', 'statistics'])
                                ->field('id, name, content, source_type, ai_tool_code, creator_id, school_id, college_id, status, audit_user_id, audit_time, audit_remark, is_deleted, create_time, update_time')
                                ->where('id', $id)
                                ->where('creator_id', $request->user->id)
                                ->find();
        
        if (!$content) {
            return $this->error('内容不存在');
        }
        
        // 查询关联的文件
        $relatedFiles = \app\model\File::where('content_id', $content->id)
            ->where('status', \app\model\File::STATUS_NORMAL)
            ->field('id, file_name, original_name, file_path, file_size, file_type, mime_type, source_type, ai_tool_code')
            ->select();
        
        $content->related_files = $relatedFiles;
        
        return $this->success($content);
    }
    
    /**
     * 导出Word文档
     * POST /api/teacher/content/export
     */
    public function exportDocument(Request $request)
    {
        $data = $request->post();
        
        try {
            validate([
                'content_id' => 'require|integer',
                'format' => 'in:docx,pdf'
            ])->check($data);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
        
        try {
            $contentId = $data['content_id'];
            $format = $data['format'] ?? 'docx';
            
            // 检查权限
            $content = ContentLibrary::where('id', $contentId)
                                   ->where('creator_id', $request->user->id)
                                   ->find();
            
            if (!$content) {
                return $this->error('内容不存在或无权限访问');
            }
            
            // 导出文档
            $fileInfo = DocumentExportService::exportToWord($contentId, $format);
            
            // 根据存储类型生成下载URL
            $storageType = \app\helper\SystemHelper::getStorageDriver();
            $downloadUrl = '';
            
            switch (strtolower($storageType)) {
                case 'oss':
                case 'cos':
                    // 云存储返回直接访问链接
                    $disk = \think\facade\Filesystem::disk($storageType);
                    $downloadUrl = $disk->url($fileInfo['file_path']);
                    break;
                    
                case 'local':
                default:
                    // 本地存储返回下载接口
                    $downloadUrl = '/api/teacher/content/download/' . basename($fileInfo['file_path']);
                    break;
            }
            
            return $this->success([
                'download_url' => $downloadUrl,
                'file_name' => $fileInfo['file_name'],
                'file_size' => $fileInfo['file_size'],
                'mime_type' => $fileInfo['mime_type']
            ], '导出成功');
            
        } catch (\Exception $e) {
            return $this->error('导出失败：' . $e->getMessage());
        }
    }
    
    /**
     * 下载导出的文档
     * GET /api/teacher/content/download/{filename}
     */
    public function downloadDocument(Request $request, $filename)
    {
        // 获取系统存储配置
        $storageType = \app\helper\SystemHelper::getStorageDriver();
        
        switch (strtolower($storageType)) {
            case 'oss':
            case 'cos':
                // 云存储直接返回下载链接
                $filePath = 'documents/' . $filename;
                $disk = \think\facade\Filesystem::disk($storageType);
                $url = $disk->url($filePath);
                
                return $this->success([
                    'download_url' => $url,
                    'file_name' => $filename
                ], '获取下载链接成功');
                
            case 'local':
            default:
                // 本地文件直接下载
                $filePath = root_path() . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'documents' . DIRECTORY_SEPARATOR . $filename;
                
                if (!file_exists($filePath)) {
                    return $this->error('文件不存在');
                }
                
                // 设置响应头
                $mimeType = pathinfo($filename, PATHINFO_EXTENSION) == 'docx' 
                    ? 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                    : 'application/pdf';
                    
                header('Content-Type: ' . $mimeType);
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . filesize($filePath));
                header('Cache-Control: no-cache, must-revalidate');
                header('Pragma: no-cache');
                
                // 输出文件内容
                readfile($filePath);
                exit;
        }
    }
    
    /**
     * 提交审核
     * POST /api/teacher/content/submit-audit
     */
    public function submitAudit(Request $request)
    {
        $data = $request->post();
        
        try {
            validate([
                'content_id' => 'require|integer'
            ])->check($data);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
        
        try {
            $content = ContentLibrary::where('id', $data['content_id'])
                                   ->where('creator_id', $request->user->id)
                                   ->find();
            
            if (!$content) {
                return $this->error('内容不存在');
            }
            
            if ($content->status !== ContentLibrary::STATUS_DRAFT) {
                return $this->error('只有草稿状态的内容可以提交审核');
            }
            
            $content->submitForAudit();
            
            return $this->success(null, '提交审核成功');
            
        } catch (\Exception $e) {
            return $this->error('提交失败：' . $e->getMessage());
        }
    }
    
    /**
     * 删除内容
     * DELETE /api/teacher/content/delete/{id}
     */
    public function deleteContent(Request $request, $id)
    {
        try {
            $content = ContentLibrary::where('id', $id)
                                   ->where('creator_id', $request->user->id)
                                   ->find();
            
            if (!$content) {
                return $this->error('内容不存在');
            }
            
            // 软删除
            $content->is_deleted = 1;
            $content->save();
            
            return $this->success(null, '删除成功');
            
        } catch (\Exception $e) {
            return $this->error('删除失败：' . $e->getMessage());
        }
    }
    
    /**
     * 获取内容统计
     * GET /api/teacher/content/statistics
     */
    public function getStatistics(Request $request)
    {
        $userInfo = $request->user;
        
        $statistics = [
            'total_count' => ContentLibrary::where('creator_id', $userInfo->id)
                                         ->where('is_deleted', 0)
                                         ->count(),
            'draft_count' => ContentLibrary::where('creator_id', $userInfo->id)
                                         ->where('status', ContentLibrary::STATUS_DRAFT)
                                         ->where('is_deleted', 0)
                                         ->count(),
            'pending_count' => ContentLibrary::where('creator_id', $userInfo->id)
                                           ->where('status', ContentLibrary::STATUS_PENDING)
                                           ->where('is_deleted', 0)
                                           ->count(),
            'approved_count' => ContentLibrary::where('creator_id', $userInfo->id)
                                            ->where('status', ContentLibrary::STATUS_APPROVED)
                                            ->where('is_deleted', 0)
                                            ->count(),
            'ai_generated_count' => ContentLibrary::where('creator_id', $userInfo->id)
                                                ->where('source_type', ContentLibrary::SOURCE_TYPE_AI_GENERATE)
                                                ->where('is_deleted', 0)
                                                ->count()
        ];
        
        return $this->success($statistics);
    }

    /**
     * 上传或生成内容 -> 保存到个人空间，返回content信息
     */
    public function upload(Request $request)
    {
        try {
            $data = $request->post();
            $validate = validate([
                'name'        => 'require|max:255',
                'content'     => 'max:65535',
                'source_type' => 'in:upload,ai_generate',
            ]);
            if (!$validate->check($data)) {
                return $this->error($validate->getError());
            }
            $user = $request->user;            
            $schoolId = $user->primary_school_id ?: ($user->teacher->school_id ?? 0);
            if (!$schoolId) return $this->error('用户未关联学校');

            $content = ContentService::createPersonalContent(
                $data,
                $request->userId,
                (int)$schoolId
            );
            return $this->success($content, '保存成功');
        } catch (\Exception $e) {
            return $this->error('保存失败：' . $e->getMessage());
        }
    }

    /**
     * 提交审核
     */
    public function submit(Request $request)
    {
        try {
            $contentId = $request->post('content_id');
            $courseId  = $request->post('course_id');
            $visibility = $request->post('visibility','public');
            if (!$contentId) return $this->error('缺少content_id');
            ContentService::submitForAudit((int)$contentId, $request->userId, (int)$courseId, $visibility);
            return $this->success(null, '已提交审核');
        } catch (\Exception $e) {
            return $this->error('提交失败：' . $e->getMessage());
        }
    }

    /**
     * 修改可见性
     */
    public function visibility(Request $request)
    {
        try {
            $contentId  = $request->post('content_id');
            $visibility = $request->post('visibility');
            if (!$contentId || !$visibility) return $this->error('参数不完整');
            ContentService::changeVisibility((int)$contentId, $request->userId, $visibility);
            return $this->success(null, '可见性已更新');
        } catch (\Exception $e) {
            return $this->error('更新失败：' . $e->getMessage());
        }
    }

    /**
     * 个人空间列表
     */
    public function personalList(Request $request)
    {
        $page  = (int)$request->param('page', 1);
        $limit = (int)$request->param('limit', 15);
        $data  = ContentService::personalList($request->userId, $page, $limit);
        return $this->success($data);
    }

    /**
     * 课程空间列表
     */
    public function courseList(Request $request)
    {
        $page  = (int)$request->param('page', 1);
        $limit = (int)$request->param('limit', 15);
        $data  = ContentService::courseList($request->userId, $page, $limit);
        return $this->success($data);
    }

    /**
     * 内容详情（预览）
     */
    public function show(Request $request, $id)
    {
        try {
            $userId = $request->userId;
            $content = \app\model\ContentLibrary::find($id);
            if (!$content) {
                return $this->error('内容不存在');
            }

            // 权限：创建者可看；或在课程空间且可见；或公开内容
            if ($content->creator_id != $userId) {
                // 检查课程空间可见性
                $hasAccess = \think\facade\Db::name('content_space')
                    ->where('content_id', $id)
                    ->where('space_type', 'course')
                    ->where('is_active', 1)
                    ->where(function($q) use ($userId){
                        // 成员 或 负责人 或 公开
                        $q->where('visibility', 'public');
                    })
                    ->count() > 0;
                if (!$hasAccess) {
                    return $this->error('无权限查看');
                }
            }

            return $this->success($content);
        } catch (\Exception $e) {
            return $this->error('获取失败：'.$e->getMessage());
        }
    }

    /**
     * 创建内容
     * POST /api/teacher/content/create
     */
    public function createContent(Request $request)
    {
        $data = $request->post();
        
        try {
            validate([
                'name' => 'require|max:200',
                'content' => 'require',
                'file_type' => 'require|in:text,document,image,video',
                'visibility' => 'require|in:private,public,leader'
            ])->check($data);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
        
        try {
            $content = new ContentLibrary();
            $content->name = $data['name'];
            $content->content = $data['content'];
            $content->source_type = $data['source_type'] ?? 'upload';
            $content->ai_tool_code = $data['ai_tool_code'] ?? '';
            $content->creator_id = $request->user->id;
            $content->school_id = $request->user->primary_school_id;
            $content->status = ContentLibrary::STATUS_DRAFT;
            
            if ($content->save()) {
                // 自动添加到个人空间
                \app\model\ContentSpace::addToSpace($content->id, 'personal', null, $request->user->id, $data['visibility'], 'edit');
                
                return $this->success([
                    'id' => $content->id,
                    'message' => '内容创建成功'
                ], '创建成功');
            } else {
                return $this->error('创建失败');
            }
            
        } catch (\Exception $e) {
            return $this->error('创建失败：' . $e->getMessage());
        }
    }
    
    /**
     * 更新内容
     * PUT /api/teacher/content/update/{id}
     */
    public function updateContent(Request $request, $id)
    {
        $data = $request->post();
        
        try {
            validate([
                'name' => 'require|max:200',
                'content' => 'require',
                'file_type' => 'require|in:text,document,image,video',
                'visibility' => 'require|in:private,public,leader'
            ])->check($data);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
        
        try {
            $content = ContentLibrary::where('id', $id)
                                   ->where('creator_id', $request->user->id)
                                   ->find();
            
            if (!$content) {
                return $this->error('内容不存在或无权限编辑');
            }
            
            // 只有草稿状态的内容可以编辑
            if ($content->status !== ContentLibrary::STATUS_DRAFT) {
                return $this->error('只有草稿状态的内容可以编辑');
            }
            
            $content->name = $data['name'];
            $content->content = $data['content'];
            
            if ($content->save()) {
                // 更新空间权限
                $space = \app\model\ContentSpace::where('content_id', $content->id)
                    ->where('space_type', 'personal')
                    ->find();
                if ($space) {
                    $space->visibility = $data['visibility'];
                    $space->save();
                }
                
                return $this->success([
                    'id' => $content->id,
                    'message' => '内容更新成功'
                ], '更新成功');
            } else {
                return $this->error('更新失败');
            }
            
        } catch (\Exception $e) {
            return $this->error('更新失败：' . $e->getMessage());
        }
    }

    /**
     * 获取可用文件列表（未关联到内容的文件）
     * GET /api/teacher/content/available-files
     */
    public function getAvailableFiles(Request $request)
    {
        try {
            $params = $request->get();
            $userInfo = $request->user;
            
            $query = \app\model\File::where('school_id', $userInfo->primary_school_id)
                ->where('uploader_id', $userInfo->id)
                ->where('uploader_type', 'teacher')
                ->where('status', \app\model\File::STATUS_NORMAL)
                ->whereNull('content_id'); // 未关联到内容的文件
            
            // 支持按文件类型筛选
            if (!empty($params['file_category'])) {
                $query->where('file_category', $params['file_category']);
            }
            
            // 支持按文件名搜索
            if (!empty($params['keyword'])) {
                $query->where('original_name|file_name', 'like', '%' . $params['keyword'] . '%');
            }
            
            $files = $query->field('id, file_name, original_name, file_path, file_size, file_type, mime_type, source_type, ai_tool_code, create_time')
                ->order('create_time desc')
                ->select();
            
            return $this->success($files);
            
        } catch (\Exception $e) {
            return $this->error('获取可用文件失败：' . $e->getMessage());
        }
    }
    
    /**
     * 关联文件到内容
     * POST /api/teacher/content/associate-files
     */
    public function associateFiles(Request $request)
    {
        $data = $request->post();
        
        try {
            validate([
                'content_id' => 'require|integer',
                'file_ids' => 'require|array'
            ])->check($data);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
        
        try {
            $contentId = $data['content_id'];
            $fileIds = $data['file_ids'];
            $userInfo = $request->user;
            
            // 检查内容是否存在且属于当前用户
            $content = ContentLibrary::where('id', $contentId)
                                   ->where('creator_id', $userInfo->id)
                                   ->find();
            
            if (!$content) {
                return $this->error('内容不存在或无权限操作');
            }
            
            // 检查文件是否存在且属于当前用户
            $files = \app\model\File::where('id', 'in', $fileIds)
                ->where('school_id', $userInfo->primary_school_id)
                ->where('uploader_id', $userInfo->id)
                ->where('uploader_type', 'teacher')
                ->where('status', \app\model\File::STATUS_NORMAL)
                ->select();
            
            if (count($files) !== count($fileIds)) {
                return $this->error('部分文件不存在或无权限操作');
            }
            
            // 更新文件的content_id
            $successCount = 0;
            foreach ($files as $file) {
                if ($file->content_id) {
                    continue; // 跳过已关联的文件
                }
                
                $file->content_id = $contentId;
                if ($file->save()) {
                    $successCount++;
                }
            }
            
            return $this->success([
                'associated_count' => $successCount,
                'message' => "成功关联 {$successCount} 个文件"
            ], '关联成功');
            
        } catch (\Exception $e) {
            return $this->error('关联文件失败：' . $e->getMessage());
        }
    }

    /**
     * 重新生成AI内容
     */
    public function regenerate(Request $request)
    {
        try {
            $data = $request->post();
            
            // 验证数据
            try {
                validate([
                    'content_id' => 'require|integer',
                    'tool_code' => 'require',
                    'prompt_params' => 'array',
                    'provider' => 'alphaDash'
                ])->check($data);
            } catch (\Exception $e) {
                return $this->error($e->getMessage());
            }
            
            $contentId = $data['content_id'];
            $teacherId = $this->getTeacherId();
            
            // 检查内容是否存在且属于当前用户
            $content = ContentLibrary::where('id', $contentId)
                ->where('creator_id', $teacherId)
                ->where('source_type', 'ai_generate')
                ->find();
                
            if (!$content) {
                return $this->error('内容不存在或无权限重新生成');
            }
            
            // 调用AI服务重新生成
            $aiService = new AiService();
            $result = $aiService->generateContent($data['tool_code'], $data['prompt_params'] ?? [], $teacherId, $this->getSchoolId());
            
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
            Log::error("重新生成AI内容失败: " . $e->getMessage());
            return $this->error('重新生成失败：' . $e->getMessage());
        }
    }
    
    /**
     * 导出Word文档
     */
    public function exportWord(Request $request)
    {
        try {
            $data = $request->post();
            
            // 验证数据
            try {
                validate([
                    'content_id' => 'require|integer',
                    'format' => 'in:docx,pdf'
                ])->check($data);
            } catch (\Exception $e) {
                return $this->error($e->getMessage());
            }
            
            $contentId = $data['content_id'];
            $format = $data['format'] ?? 'docx';
            $teacherId = $this->getTeacherId();
            
            // 检查内容是否存在且属于当前用户
            $content = ContentLibrary::where('id', $contentId)
                ->where('creator_id', $teacherId)
                ->find();
                
            if (!$content) {
                return $this->error('内容不存在或无权限导出');
            }
            
            // 导出Word文档
            $fileInfo = DocumentExportService::exportToWord($contentId, $format);
            
            return $this->success($fileInfo, '导出成功');
            
        } catch (\Exception $e) {
            Log::error("导出Word文档失败: " . $e->getMessage());
            return $this->error('导出失败：' . $e->getMessage());
        }
    }
    
    /**
     * 保存到文件中心
     */
    public function saveToFileCenter(Request $request)
    {
        try {
            $data = $request->post();
            
            // 验证数据
            try {
                validate([
                    'file_url' => 'require',
                    'file_name' => 'require',
                    'original_name' => 'require',
                    'file_size' => 'integer',
                    'file_type' => 'require',
                    'mime_type' => 'require',
                    'source_type' => 'require',
                    'content_id' => 'integer'
                ])->check($data);
                
                // 如果是AI生成的文件，ai_tool_code是必需的
                if ($data['source_type'] === 'ai_generate') {
                    if (empty($data['ai_tool_code'])) {
                        // 如果没有ai_tool_code，尝试从内容库中获取
                        if (!empty($data['content_id'])) {
                            $content = ContentLibrary::find($data['content_id']);
                            if ($content && $content->ai_tool_code) {
                                $data['ai_tool_code'] = $content->ai_tool_code;
                            } else {
                                \think\facade\Log::warning('AI生成文件缺少ai_tool_code，且内容库也无此字段', $data);
                                return $this->error('AI生成文件必须指定ai_tool_code');
                            }
                        } else {
                            \think\facade\Log::warning('AI生成文件缺少ai_tool_code和content_id', $data);
                            return $this->error('AI生成文件必须指定ai_tool_code');
                        }
                    }
                }
            } catch (\Exception $e) {
                return $this->error($e->getMessage());
            }
            
            $teacherId = $this->getTeacherId();
            $schoolId = $this->getSchoolId();
            
            // 创建文件记录
            $file = new File();
            $file->school_id = $schoolId;
            $file->uploader_id = $teacherId;
            $file->uploader_type = 'teacher';
            $file->file_name = $data['file_name'];
            $file->original_name = $data['original_name'];
            $file->file_path = $data['file_url'];
            $file->file_size = $data['file_size'] ?? 0;
            $file->file_type = $data['file_type'];
            $file->mime_type = $data['mime_type'];
            $file->file_category = $this->getFileCategoryByType($data['file_type']);
            
            // 根据系统配置设置存储类型
            $storageDriver = \app\helper\SystemHelper::getStorageDriver();
            switch (strtolower($storageDriver)) {
                case 'oss':
                    $file->storage_type = File::STORAGE_OSS;
                    break;
                case 'cos':
                    $file->storage_type = File::STORAGE_COS;
                    break;
                case 'local':
                default:
                    $file->storage_type = File::STORAGE_LOCAL;
                    break;
            }
            
            $file->is_public = 0;
            $file->status = 1;
            
            // 添加AI生成相关元数据
            $file->ai_tool_code = $data['ai_tool_code'] ?? null;
            $file->content_id = $data['content_id'] ?? null;
            $file->source_type = $data['source_type'];
            
            if (!$file->save()) {
                return $this->error('保存到文件中心失败');
            }
            
            return $this->success([
                'file_id' => $file->id,
                'file_name' => $file->file_name
            ], '保存到文件中心成功');
            
        } catch (\Exception $e) {
            Log::error("保存到文件中心失败: " . $e->getMessage());
            return $this->error('保存失败：' . $e->getMessage());
        }
    }
    
    /**
     * 根据文件类型获取分类
     */
    private function getFileCategoryByType($fileType)
    {
        $categoryMap = [
            'document' => 'document',
            'image' => 'image',
            'video' => 'video',
            'audio' => 'audio',
            'archive' => 'archive',
            'other' => 'other'
        ];
        
        return $categoryMap[$fileType] ?? 'other';
    }
    
    /**
     * 获取教师ID（从JWT token中获取）
     */
    protected function getTeacherId()
    {
        return request()->user->id ?? 1;
    }
    
    /**
     * 获取用户ID（从JWT token中获取）
     */
    protected function getUserId()
    {
        return request()->user->id ?? 3;
    }
    
    /**
     * 获取学校ID（从JWT token中获取）
     */
    protected function getSchoolId()
    {
        return request()->user->primary_school_id ?? 1;
    }
} 