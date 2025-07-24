<?php
namespace app\controller\api\teacher;

use app\controller\api\BaseController;
use app\model\AiTool;
use app\model\ContentLibrary;
use app\model\ContentSpace;
use app\service\AiService;
use app\service\AiToolConfigService;
use think\Request;
use think\facade\Log;
use think\facade\Db;
use think\App;
use app\model\File;
use app\service\DocumentExportService;

/**
 * 教师端AI工具控制器
 */
class AiToolController extends BaseController
{
    protected $aiService;
    
    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->aiService = new AiService();
    }
    
    /**
     * 获取AI工具列表
     */
    public function getList(Request $request)
    {
        try {
            $page = $request->param('page', 1);
            $limit = $request->param('limit', 10);
            $category = $request->param('category', '');
            $keyword = $request->param('keyword', '');
            
            $query = AiTool::where('status', AiTool::STATUS_ENABLED)
                            ->order('sort ASC, id ASC');
            
            Log::info("AiToolController getList: AiTool::STATUS_ENABLED value: " . AiTool::STATUS_ENABLED);
            Log::info("AiToolController getList: Querying for enabled tools.");

            // 分类筛选
            if ($category) {
                $query->where('category', $category);
                Log::info("AiToolController getList: Filtering by category: " . $category);
            }
            
            // 关键词搜索
            if ($keyword) {
                $query->where('name|description', 'like', "%{$keyword}%");
                Log::info("AiToolController getList: Filtering by keyword: " . $keyword);
            }
            
            $list = $query->select();
            
            Log::info("AiToolController getList: Raw list from DB (before processing): " . json_encode($list->toArray()));

            $total = count($list);

            // 处理数据
            $processedList = [];
            foreach ($list as $item) {
                $itemData = $item->toArray();

                // Explicit conversion for debugging
                $originalStatusInItem = $item->status;
                $expectedEnabledConstant = AiTool::STATUS_ENABLED;
                $convertedStatusString = '';

                if ($originalStatusInItem === $expectedEnabledConstant) {
                    $convertedStatusString = 'enabled';
                } else {
                    $convertedStatusString = 'inactive';
                }

                $itemData['status'] = $convertedStatusString;

                // Add other derived data
                $itemData['category_text'] = $item->category_text;
                $itemData['params'] = json_decode($item->params, true);

                Log::info("AiToolController getList: Tool ID {$item->id}, Original status (from \$item): {$originalStatusInItem}, Converted status (in \$itemData): {$itemData['status']}, Comparison result (original === constant): " . var_export($originalStatusInItem === $expectedEnabledConstant, true));
                
                $processedList[] = $itemData;
            }
            
            Log::info("AiToolController getList: Final processed list before JSON encode: " . json_encode($processedList));

            return $this->success([
                'list' => $processedList,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'categories' => AiTool::getCategoryList(),
                'statuses' => AiTool::getStatusList()
            ], 'success', 200);
            
        } catch (\Exception $e) {
            Log::error("获取AI工具列表失败: " . $e->getMessage());
            return $this->error('获取AI工具列表失败：' . $e->getMessage());
        }
    }
    
    /**
     * 获取AI工具详情
     */
    public function getDetail(Request $request)
    {
        try {
            $id = $request->param('id');
            $tool = AiTool::find($id);
            
            if (!$tool) {
                return $this->error('AI工具不存在', 404);
            }
            
            $tool->category_text = $tool->category_text;
            $tool->params = json_decode($tool->params, true);
            // 将状态从整数转换为字符串，匹配前端期望
            $tool->status = $tool->status == AiTool::STATUS_ENABLED ? 'enabled' : 'inactive';
            
            return $this->success(['tool' => $tool], 'success', 200);
            
        } catch (\Exception $e) {
            Log::error("获取AI工具详情失败: " . $e->getMessage());
            return $this->error('获取AI工具详情失败：' . $e->getMessage());
        }
    }
    
    /**
     * 生成AI内容
     */
    public function generate(Request $request)
    {
        try {
            $toolCode = $request->param('tool_code');
            $promptParams = $request->param('prompt_params', []);
            $title = $request->param('title', '');
            
            // 验证工具是否存在
            $tool = AiTool::where('code', $toolCode)->where('status', 1)->find();
            if (!$tool) {
                return $this->error('AI工具不存在或已禁用', 400);
            }
            
            // 验证参数
            $validationResult = AiToolConfigService::validateParams($toolCode, $promptParams);
            if (!$validationResult['valid']) {
                return $this->error('参数验证失败：' . implode(', ', $validationResult['errors']), 500);
            }
            
            // 处理course_id参数，转换为课程相关信息
            $processedParams = $this->processCourseParams($validationResult['params']);
            
            // 调用AI服务生成内容
            $result = $this->aiService->generateContent($toolCode, $processedParams, $this->getUserId(), $this->getSchoolId());
            
            if (!$result['success']) {
                return $this->error('生成内容失败：' . $result['message'], 500);
            }
            
            // 保存到内容库
            $content = new ContentLibrary();
            $content->name = $title ?: $tool->name . '生成的内容';
            $content->content = $result['content'];
            $content->file_type = ContentLibrary::FILE_TYPE_TEXT;
            $content->source_type = ContentLibrary::SOURCE_TYPE_AI_GENERATE;
            $content->ai_tool_code = $tool->code;
            $content->creator_id = $this->getTeacherId();
            $content->school_id = $this->getSchoolId();
            $content->status = ContentLibrary::STATUS_DRAFT; // 保存为草稿
            $content->save();
            
            return $this->success([
                'content' => $result['content'],
                'content_id' => $content->id,
                'message' => '内容生成成功，已保存为草稿'
            ], 'success', 200);
            
        } catch (\Exception $e) {
            Log::error("生成AI内容失败: " . $e->getMessage());
            return $this->error('生成内容失败：' . $e->getMessage());
        }
    }
    
    /**
     * 保存内容到内容库
     */
    public function saveContent(Request $request)
    {
        try {
            $contentId = $request->param('content_id');
            $title = $request->param('title');
            $content = $request->param('content');
            $spaceId = $request->param('space_id');
            $tags = $request->param('tags', '');
            $status = $request->param('status', ContentLibrary::STATUS_PENDING);
            
            if ($contentId) {
                // 更新现有内容
                $contentModel = ContentLibrary::where('id', $contentId)
                    ->where('creator_id', $this->getTeacherId())
                    ->find();
                    
                if (!$contentModel) {
                    return $this->error('内容不存在', 404);
                }
            } else {
                // 创建新内容
                $contentModel = new ContentLibrary();
                $contentModel->creator_id = $this->getTeacherId();
                $contentModel->school_id = $this->getSchoolId();
            }
            
            $contentModel->name = $title;
            $contentModel->content = $content;
            $contentModel->status = $status;
            $contentModel->save();
            
            // 如果指定了空间，添加到空间
            if ($spaceId) {
                ContentSpace::create([
                    'content_id' => $contentModel->id,
                    'space_id' => $spaceId
                ]);
            }
            
            return $this->success([
                'content_id' => $contentModel->id,
                'message' => '内容保存成功'
            ], 'success', 200);
            
        } catch (\Exception $e) {
            Log::error("保存内容失败: " . $e->getMessage());
            return $this->error('保存内容失败：' . $e->getMessage());
        }
    }
    
    /**
     * 获取内容分类列表
     */
    public function getCategories(Request $request)
    {
        try {
            // 返回简单的分类列表
            $categories = [
                ['id' => 1, 'name' => '教案', 'type' => 'lesson_plan'],
                ['id' => 2, 'name' => '课件', 'type' => 'courseware'],
                ['id' => 3, 'name' => '作业', 'type' => 'homework'],
                ['id' => 4, 'name' => '试题', 'type' => 'question'],
                ['id' => 5, 'name' => '讲稿', 'type' => 'script'],
                ['id' => 6, 'name' => '反思', 'type' => 'reflection']
            ];
                
            return $this->success(['categories' => $categories], 'success', 200);
            
        } catch (\Exception $e) {
            Log::error("获取内容分类失败: " . $e->getMessage());
            return $this->error('获取内容分类失败：' . $e->getMessage());
        }
    }
    
    /**
     * 导出Word文档
     */
    public function exportWord(Request $request)
    {
        try {
            $contentId = $request->param('content_id');
            $format = $request->param('format', 'docx');
            
            $content = ContentLibrary::where('id', $contentId)
                ->where('creator_id', $this->getTeacherId())
                ->find();
                
            if (!$content) {
                return $this->error('内容不存在', 404);
            }
            
            // 调用文档导出服务生成Word文档
            $fileInfo = DocumentExportService::exportToWord($contentId, $format);
            
            return $this->success([
                'file_path' => $fileInfo['file_path'],
                'file_name' => $fileInfo['file_name'],
                'file_size' => $fileInfo['file_size'],
                'mime_type' => $fileInfo['mime_type'],
                'message' => '文档导出成功'
            ], 'success', 200);
            
        } catch (\Exception $e) {
            Log::error("导出Word文档失败: " . $e->getMessage());
            return $this->error('导出文档失败：' . $e->getMessage());
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
                    'ai_tool_code' => 'require',
                    'content_id' => 'integer'
                ])->check($data);
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
            $file->file_path = $data['file_url']; // 这里保存的是实际文件路径
            $file->file_size = $data['file_size'] ?? 0;
            $file->file_type = $data['file_type'];
            $file->mime_type = $data['mime_type'];
            $file->file_category = $this->getFileCategoryByType($data['file_type']);
            $file->storage_type = 'local';
            $file->is_public = 0;
            $file->status = 1;
            
            // 添加AI生成相关元数据
            $file->ai_tool_code = $data['ai_tool_code'];
            $file->content_id = $data['content_id'] ?? null;
            $file->source_type = $data['source_type'];
            
            if ($file->save()) {
                // 如果有关联的内容，更新内容的文件关联
                if (!empty($data['content_id'])) {
                    $content = ContentLibrary::find($data['content_id']);
                    if ($content && $content->creator_id == $teacherId) {
                        $content->file_path = $data['file_url'];
                        $content->file_size = $data['file_size'] ?? 0;
                        $content->save();
                    }
                }
                
                return $this->success([
                    'file_id' => $file->id,
                    'message' => '文件已保存到文件中心'
                ], 'success', 200);
            } else {
                return $this->error('保存文件失败');
            }
            
        } catch (\Exception $e) {
            Log::error("保存到文件中心失败: " . $e->getMessage());
            return $this->error('保存到文件中心失败：' . $e->getMessage());
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
            'text' => 'document'
        ];
        
        return $categoryMap[$fileType] ?? 'other';
    }
    
    /**
     * 处理课程相关参数，将course_id转换为课程详细信息
     */
    protected function processCourseParams(array $params): array
    {
        if (isset($params['course_id']) && is_numeric($params['course_id'])) {
            $courseId = (int)$params['course_id'];
            $teacherId = $this->getUserId();
            
            // 获取课程信息并验证权限
            $course = \app\model\Course::with(['college', 'school'])
                ->where('id', $courseId)
                ->where('school_id', $this->getSchoolId())
                ->where(function($q) use ($teacherId) {
                    $q->where('responsible_teacher_id', $teacherId)
                      ->whereOr('id', 'in', function($subQuery) use ($teacherId) {
                          $subQuery->table('edu_course_teacher')
                                   ->where('teacher_id', $teacherId)
                                   ->field('course_id');
                      });
                })
                ->find();
            
            if ($course) {
                // 添加课程相关信息到参数中
                $params['course_name'] = $course->course_name;
                $params['course_code'] = $course->course_code;
                $params['college_name'] = $course->college->name ?? '';
                $params['school_name'] = $course->school->name ?? '';
                $params['credits'] = $course->credits ?? 0;
                $params['hours'] = $course->hours ?? 0;
                $params['semester'] = $course->semester ?? '';
                $params['academic_year'] = $course->academic_year ?? '';
            } else {
                // 课程不存在或无权限访问，记录日志但不影响流程
                Log::warning("无法获取课程信息：courseId={$courseId}, teacherId={$teacherId}");
            }
        }
        
        return $params;
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

    /**
     * 获取工具表单配置
     */
    public function getToolFormConfig(Request $request)
    {
        try {
            $toolCode = $request->param('tool_code');
            if (!$toolCode) {
                return $this->error('工具编码不能为空');
            }

            $formConfig = AiToolConfigService::getFormConfig($toolCode);
            if (empty($formConfig)) {
                return $this->error('工具不存在或配置错误');
            }

            return $this->success($formConfig);
        } catch (\Exception $e) {
            return $this->error('获取工具配置失败：' . $e->getMessage());
        }
    }

    /**
     * 获取所有工具的表单配置
     */
    public function getAllToolsFormConfig(Request $request)
    {
        try {
            $schoolId = $this->getSchoolId();
            $configs = AiToolConfigService::getAllToolsFormConfig($schoolId);
            
            return $this->success($configs);
        } catch (\Exception $e) {
            return $this->error('获取工具配置失败：' . $e->getMessage());
        }
    }
} 