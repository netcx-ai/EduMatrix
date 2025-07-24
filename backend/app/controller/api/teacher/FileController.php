<?php
declare(strict_types=1);

namespace app\controller\api\teacher;

use app\controller\api\BaseController;
use app\model\File;
use app\model\Course;
use app\service\StorageService;
use think\Request;
use think\facade\Validate;
use think\facade\Filesystem;

/**
 * 教师侧文件管理控制器
 */
class FileController extends BaseController
{
    /**
     * 文件列表
     */
    public function index(Request $request)
    {
        try {
            // 分页参数统一转为整数，兼容 pageSize 名称
            $page = (int)$request->param('page', 1);
            $limit = (int)$request->param('limit', $request->param('pageSize', 15));
            $keyword = $request->param('keyword', '');
            $category = $request->param('category', '');
            $course_id = $request->param('course_id', '');
            
            $teacherId = $request->userId;
            $teacher = $request->user;
            $schoolId = $teacher->school_id ?: ($teacher->teacher->school_id ?? 0);
            
            $query = File::with(['course', 'school'])
                ->where('school_id', $schoolId)
                ->where('uploader_id', $teacherId)
                ->where('uploader_type', 'teacher')
                ->where('status', File::STATUS_NORMAL);
            
            // 关键词搜索
            if ($keyword) {
                $query->where('file_name|original_name', 'like', "%{$keyword}%");
            }
            
            // 文件分类筛选
            if ($category) {
                $query->where('file_category', $category);
            }
            
            // 课程筛选
            if ($course_id) {
                $query->where('course_id', $course_id);
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
            return $this->error('获取文件列表失败：' . $e->getMessage());
        }
    }
    
    /**
     * 文件上传
     */
    public function upload(Request $request)
    {
        try {
            $file = $request->file('file');
            if (!$file) {
                return $this->error('请选择文件');
            }
            
            $teacher = $request->user;
            $schoolId = $teacher->school_id ?: ($teacher->teacher->school_id ?? 0);
            if (!$schoolId) {
                return $this->error('用户未关联学校，无法上传');
            }
            
            $course_id = $request->param('course_id', 0);
            $file_category = $request->param('file_category', File::CATEGORY_OTHER);
            
            // 验证文件
            $validate = Validate::rule([
                'file' => 'fileSize:104857600|fileExt:jpg,jpeg,png,gif,bmp,webp,doc,docx,xls,xlsx,ppt,pptx,pdf,txt,zip,rar,7z,mp4,avi,mov,wmv,flv,mp3,wav,wma'
            ]);
            
            if (!$validate->check(['file' => $file])) {
                return $this->error($validate->getError());
            }
            
            // 验证课程权限
            if ($course_id > 0) {
                $course = Course::where('school_id', $schoolId)
                    ->where(function($q) use ($request) {
                        $q->where('responsible_teacher_id', $request->userId)
                          ->whereOr('id', 'in', function($subQuery) use ($request) {
                              $subQuery->table('edu_course_teacher')
                                       ->where('teacher_id', $request->userId)
                                       ->field('course_id');
                          });
                    })
                    ->find($course_id);
                
                if (!$course) {
                    return $this->error('无权限上传到该课程');
                }
            }
            
            // 根据系统配置自动选择存储驱动并保存文件
            $savename = StorageService::uploadFile($file, 'teacher/' . date('Y/m'));
            
            if (!$savename) {
                return $this->error('文件上传失败');
            }
            
            // 解析当前存储驱动
            $storageDriver = \app\helper\SystemHelper::getStorageDriver();
            switch (strtolower($storageDriver)) {
                case 'oss':
                    $currentStorageType = File::STORAGE_OSS;
                    break;
                case 'cos':
                    $currentStorageType = File::STORAGE_COS;
                    break;
                case 'local':
                default:
                    $currentStorageType = File::STORAGE_LOCAL;
            }
            
            // 确定文件分类
            $extension = strtolower($file->getOriginalExtension());
            if (empty($file_category) || $file_category === File::CATEGORY_OTHER) {
                $file_category = $this->getFileCategoryByExtension($extension);
            }
            
            // 创建文件记录
            $fileModel = new File();
            $fileModel->school_id = $schoolId;
            $fileModel->uploader_id = $request->userId;
            $fileModel->uploader_type = 'teacher';
            $fileModel->course_id = $course_id ?: null;
            $fileModel->file_name = $file->getOriginalName();
            $fileModel->original_name = $file->getOriginalName();
            $fileModel->file_path = $savename;
            $fileModel->file_size = $file->getSize();
            $fileModel->file_type = $extension;
            $fileModel->mime_type = $file->getMime();
            $fileModel->file_category = $file_category;
            $fileModel->storage_type = $currentStorageType;
            $fileModel->is_public = 0;  // 默认私有
            $fileModel->status = File::STATUS_NORMAL;
            
            $fileModel->save();
            
            return $this->success([
                'file' => $fileModel,
                'url' => $fileModel->full_url
            ], '文件上传成功');
            
        } catch (\Exception $e) {
            return $this->error('文件上传失败：' . $e->getMessage());
        }
    }
    
    /**
     * 文件详情
     */
    public function show(Request $request, $id)
    {
        try {
            $teacherId = $request->userId;
            $teacher = $request->user;
            
            $file = File::with(['course', 'school'])
                ->where('school_id', $teacher->school_id)
                ->where('uploader_id', $teacherId)
                ->where('uploader_type', 'teacher')
                ->find($id);
            
            if (!$file) {
                return $this->error('文件不存在或无权限访问');
            }
            
            // 增加查看次数
            $file->incrementView();
            
            return $this->success($file);
            
        } catch (\Exception $e) {
            return $this->error('获取文件详情失败：' . $e->getMessage());
        }
    }
    
    /**
     * 更新文件信息
     */
    public function update(Request $request, $id)
    {
        try {
            $data = $request->post();
            
            // 验证数据
            $validate = Validate::rule([
                'file_name' => 'max:255',
                'file_category' => 'in:document,image,video,audio,other',
                'course_id' => 'integer',
                'is_public' => 'in:0,1'
            ]);
            
            if (!$validate->check($data)) {
                return $this->error($validate->getError());
            }
            
            $teacherId = $request->userId;
            $teacher = $request->user;
            
            $file = File::where('school_id', $teacher->school_id)
                ->where('uploader_id', $teacherId)
                ->where('uploader_type', 'teacher')
                ->find($id);
            
            if (!$file) {
                return $this->error('文件不存在或无权限编辑');
            }
            
            // 验证课程权限
            if (isset($data['course_id']) && $data['course_id'] > 0) {
                $course = Course::where('school_id', $teacher->school_id)
                    ->where(function($q) use ($request) {
                        $q->where('responsible_teacher_id', $request->userId)
                          ->whereOr('id', 'in', function($subQuery) use ($request) {
                              $subQuery->table('edu_course_teacher')
                                       ->where('teacher_id', $request->userId)
                                       ->field('course_id');
                          });
                    })
                    ->find($data['course_id']);
                
                if (!$course) {
                    return $this->error('无权限关联到该课程');
                }
            }
            
            // 更新文件信息
            if (isset($data['file_name'])) {
                $file->file_name = $data['file_name'];
            }
            if (isset($data['file_category'])) {
                $file->file_category = $data['file_category'];
            }
            if (isset($data['course_id'])) {
                $file->course_id = $data['course_id'] ?: null;
            }
            if (isset($data['is_public'])) {
                $file->is_public = $data['is_public'];
            }
            
            $file->save();
            
            return $this->success($file, '文件信息更新成功');
            
        } catch (\Exception $e) {
            return $this->error('更新文件信息失败：' . $e->getMessage());
        }
    }
    
    /**
     * 删除文件
     */
    public function delete(Request $request, $id)
    {
        try {
            $teacherId = $request->userId;
            $teacher = $request->user;
            $schoolId = $teacher->school_id ?: ($teacher->teacher->school_id ?? 0);

            $file = File::where('school_id', $schoolId)
                ->where('uploader_id', $teacherId)
                ->where('uploader_type', 'teacher')
                ->find($id);

            if (!$file) {
                return $this->error('文件不存在或无权限删除');
            }
            
            // 删除物理文件
            StorageService::deleteFile($file);
            
            // 删除数据库记录
            $file->delete();
            
            return $this->success(null, '文件删除成功');
            
        } catch (\Exception $e) {
            return $this->error('删除文件失败：' . $e->getMessage());
        }
    }
    
    /**
     * 下载文件
     */
    public function download(Request $request, $id)
    {
        try {
            $teacherId = $request->userId;
            $teacher = $request->user;
            
            $file = File::where('school_id', $teacher->school_id)
                ->where('uploader_id', $teacherId)
                ->where('uploader_type', 'teacher')
                ->find($id);
            
            if (!$file) {
                return $this->error('文件不存在或无权限下载');
            }
            
            // 增加下载次数
            $file->incrementDownload();
            
            // 根据存储类型返回不同的下载方式
            switch ($file->storage_type) {
                case File::STORAGE_LOCAL:
                    // 本地文件返回相对路径
                    return $this->success([
                        'download_url' => '/api/teacher/files/' . $id . '/raw',
                        'file_name' => $file->original_name,
                        'type' => 'local'
                    ]);
                    
                case File::STORAGE_OSS:
                case File::STORAGE_COS:
                    // 云存储文件返回直接下载链接
                    return $this->success([
                        'download_url' => StorageService::getFileUrl($file),
                        'file_name' => $file->original_name,
                        'type' => 'cloud'
                    ]);
                    
                default:
                    return $this->error('不支持的存储类型');
            }
            
        } catch (\Exception $e) {
            return $this->error('获取下载链接失败：' . $e->getMessage());
        }
    }
    
    /**
     * 获取文件分类列表
     */
    public function categories(Request $request)
    {
        try {
            $categories = File::getCategoryList();
            
            return $this->success($categories);
            
        } catch (\Exception $e) {
            return $this->error('获取文件分类失败：' . $e->getMessage());
        }
    }
    
    /**
     * 获取教师的课程列表（用于关联文件）
     */
    public function courses(Request $request)
    {
        try {
            $teacherId = $request->userId;
            $teacher = $request->user;
            
            $courses = Course::where('school_id', $teacher->school_id)
                ->where(function($q) use ($teacherId) {
                    $q->where('responsible_teacher_id', $teacherId)
                      ->whereOr('id', 'in', function($subQuery) use ($teacherId) {
                          $subQuery->table('edu_course_teacher')
                                   ->where('teacher_id', $teacherId)
                                   ->field('course_id');
                      });
                })
                ->where('status', 1)
                ->field('id,course_name,course_code')
                ->select();
            
            return $this->success($courses);
            
        } catch (\Exception $e) {
            return $this->error('获取课程列表失败：' . $e->getMessage());
        }
    }
    
    /**
     * 根据文件扩展名确定分类
     */
    private function getFileCategoryByExtension($extension)
    {
        $categoryMap = [
            // 文档类
            'doc' => File::CATEGORY_DOCUMENT,
            'docx' => File::CATEGORY_DOCUMENT,
            'xls' => File::CATEGORY_DOCUMENT,
            'xlsx' => File::CATEGORY_DOCUMENT,
            'ppt' => File::CATEGORY_DOCUMENT,
            'pptx' => File::CATEGORY_DOCUMENT,
            'pdf' => File::CATEGORY_DOCUMENT,
            'txt' => File::CATEGORY_DOCUMENT,
            
            // 图片类
            'jpg' => File::CATEGORY_IMAGE,
            'jpeg' => File::CATEGORY_IMAGE,
            'png' => File::CATEGORY_IMAGE,
            'gif' => File::CATEGORY_IMAGE,
            'bmp' => File::CATEGORY_IMAGE,
            'webp' => File::CATEGORY_IMAGE,
            
            // 视频类
            'mp4' => File::CATEGORY_VIDEO,
            'avi' => File::CATEGORY_VIDEO,
            'mov' => File::CATEGORY_VIDEO,
            'wmv' => File::CATEGORY_VIDEO,
            'flv' => File::CATEGORY_VIDEO,
            
            // 音频类
            'mp3' => File::CATEGORY_AUDIO,
            'wav' => File::CATEGORY_AUDIO,
            'wma' => File::CATEGORY_AUDIO,
        ];
        
        return $categoryMap[$extension] ?? File::CATEGORY_OTHER;
    }
    
    /**
     * 带附件头真实下载
     */
    public function raw(Request $request, $id)
    {
        try {
            $teacherId = $request->userId;
            $teacher    = $request->user;
            $schoolId   = $teacher->school_id ?: ($teacher->teacher->school_id ?? 0);

            $file = File::where('school_id', $schoolId)
                ->where('uploader_id', $teacherId)
                ->where('uploader_type', 'teacher')
                ->find($id);

            if (!$file) {
                return abort(404, '文件不存在或无权限');
            }

            // 记录下载次数
            $file->incrementDownload();

            // 根据存储类型处理下载
            switch ($file->storage_type) {
                case File::STORAGE_LOCAL:
                    // 本地文件直接下载
                    $absPath = StorageService::getFilePath($file);
                    if (!is_file($absPath)) {
                        return abort(404, '物理文件不存在');
                    }
                    return download($absPath, $file->original_name);
                    
                case File::STORAGE_OSS:
                case File::STORAGE_COS:
                    // 云存储文件返回JSON响应，包含下载链接
                    $downloadUrl = StorageService::getFileUrl($file);
                    return $this->success([
                        'download_url' => $downloadUrl,
                        'file_name' => $file->original_name,
                        'message' => '请使用以下链接下载文件'
                    ]);
                    
                default:
                    return abort(500, '不支持的存储类型');
            }

        } catch (\Exception $e) {
            return abort(500, '下载失败: ' . $e->getMessage());
        }
    }
} 