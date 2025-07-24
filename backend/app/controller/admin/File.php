<?php
namespace app\controller\admin;

use app\BaseController;
use app\model\File as FileModel;
use app\model\FileCategory;
use app\model\FileTag;
use app\model\FilePermission;
use app\model\FileShare;
use app\model\FileLog;
use app\model\School;
use app\model\User;
use think\facade\View;
use think\Request;
use think\Validate;

/**
 * 平台端文件管理控制器
 */
class File extends BaseController
{
    /**
     * 文件管理首页
     */
    public function index(Request $request)
    {
        try {
            if ($request->isAjax()) {
                $page = $request->param('page', 1);
                $limit = $request->param('limit', 10);
                $category = $request->param('category', '');
                $schoolId = $request->param('school_id', '');
                $keyword = $request->param('keyword', '');
                
                $query = FileModel::with(['uploader', 'school']);
                
                // 分类筛选
                if ($category) {
                    $query->where('file_category', $category);
                }
                
                // 学校筛选
                if ($schoolId) {
                    $query->where('school_id', $schoolId);
                }
                
                // 关键词搜索
                if ($keyword) {
                    $query->where('file_name|original_name', 'like', "%{$keyword}%");
                }
                
                // 只显示正常状态的文件
                $query->where('status', FileModel::STATUS_NORMAL);
                
                $total = $query->count();
                $list = $query->order('create_time DESC')
                             ->page($page, $limit)
                             ->select()
                             ->toArray();
                
                // 处理数据格式
                foreach ($list as &$item) {
                    $item['file_size_text'] = $this->formatFileSize($item['file_size']);
                    $item['create_time_text'] = date('Y-m-d H:i:s', strtotime($item['create_time']));
                    $item['uploader_name'] = $item['uploader']['username'] ?? '-';
                    $item['school_name'] = $item['school']['name'] ?? '-';
                }
                
                return json([
                    'code' => 0,
                    'msg' => '',
                    'count' => $total,
                    'data' => $list
                ]);
            }
            
            // 获取分类列表和学校列表
            $categories = [
                'document' => '文档',
                'image' => '图片',
                'video' => '视频',
                'audio' => '音频',
                'other' => '其他'
            ];
            $schools = School::where('status', 1)->field('id,name')->select();
            
            return View::fetch('admin/file/index', [
                'categories' => $categories,
                'schools' => $schools
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
     * 获取文件列表
     */
    public function getList(Request $request)
    {
        return $this->index($request);
    }
    
    /**
     * 获取文件详情
     */
    public function show($id)
    {
        try {
            $file = FileModel::with(['uploader', 'school'])
                           ->find($id);
            
            if (!$file) {
                if (request()->isAjax()) {
                    return json(['code' => 1, 'msg' => '文件不存在']);
                } else {
                    return $this->error('文件不存在');
                }
            }
            
            // 处理数据格式
            $file->file_size_text = $this->formatFileSize($file->file_size);
            $file->create_time_text = date('Y-m-d H:i:s', strtotime($file->create_time));
            $file->update_time_text = date('Y-m-d H:i:s', strtotime($file->update_time));
            $file->uploader_name = $file->uploader->username ?? '-';
            $file->school_name = $file->school->name ?? '-';
            
            if (request()->isAjax()) {
                return json(['code' => 0, 'data' => $file]);
            } else {
                return View::fetch('admin/file/view', [
                    'file' => $file
                ]);
            }
            
        } catch (\Exception $e) {
            if (request()->isAjax()) {
                return json(['code' => 1, 'msg' => '获取文件详情失败：' . $e->getMessage()]);
            } else {
                return $this->error('获取文件详情失败：' . $e->getMessage());
            }
        }
    }
    
    /**
     * 文件详情页面（弹窗）
     */
    public function detail(Request $request)
    {
        try {
            $id = $request->param('id');
            
            if (!$id) {
                return $this->error('文件ID不能为空');
            }
            
            $file = FileModel::with(['uploader', 'school'])
                           ->find($id);
            
            if (!$file) {
                return $this->error('文件不存在');
            }
            
            // 处理数据格式
            $file->file_size_text = $this->formatFileSize($file->file_size);
            $file->create_time_text = date('Y-m-d H:i:s', strtotime($file->create_time));
            $file->update_time_text = date('Y-m-d H:i:s', strtotime($file->update_time));
            $file->uploader_name = $file->uploader->username ?? '-';
            $file->school_name = $file->school->name ?? '-';
            
            return View::fetch('admin/file/view', [
                'file' => $file
            ]);
            
        } catch (\Exception $e) {
            return $this->error('获取文件详情失败：' . $e->getMessage());
        }
    }
    
    /**
     * 创建文件记录
     */
    public function store(Request $request)
    {
        return json(['code' => 1, 'msg' => '后台不支持文件上传功能']);
    }
    
    /**
     * 上传文件
     */
    public function upload(Request $request)
    {
        return json(['code' => 1, 'msg' => '后台不支持文件上传功能']);
    }
    
    /**
     * 更新文件信息
     */
    public function update(Request $request, $id)
    {
        try {
            $file = FileModel::find($id);
            if (!$file) {
                return json(['code' => 1, 'msg' => '文件不存在']);
            }
            
            $data = $request->only(['file_name', 'description', 'file_category']);
            
            $file->save($data);
            
            return json(['code' => 0, 'msg' => '更新成功']);
            
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '更新失败：' . $e->getMessage()]);
        }
    }
    
    /**
     * 删除文件
     */
    public function destroy($id)
    {
        try {
            $file = FileModel::find($id);
            if (!$file) {
                return json(['code' => 1, 'msg' => '文件不存在']);
            }
            
            // 软删除文件
            $file->status = FileModel::STATUS_DELETED;
            
            if ($file->save()) {
                return json(['code' => 0, 'msg' => '文件删除成功']);
            } else {
                return json(['code' => 1, 'msg' => '文件删除失败']);
            }
            
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '删除文件失败：' . $e->getMessage()]);
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
                return json(['code' => 1, 'msg' => '请选择要操作的文件']);
            }
            
            switch ($action) {
                case 'delete':
                    FileModel::whereIn('id', $ids)->update(['status' => FileModel::STATUS_DELETED]);
                    return json(['code' => 0, 'msg' => '批量删除成功']);
                    
                case 'download':
                    // 批量下载逻辑
                    return json(['code' => 0, 'msg' => '批量下载功能待实现']);
                    
                default:
                    return json(['code' => 1, 'msg' => '无效的操作类型']);
            }
            
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '批量操作失败：' . $e->getMessage()]);
        }
    }
    
    /**
     * 获取文件分类列表
     */
    public function categories()
    {
        try {
            $categories = FileCategory::getEnabledCategories();
            
            // 获取每个分类的统计信息
            foreach ($categories as &$category) {
                $category->statistics = $category->getStatistics();
            }
            
            return json(['code' => 0, 'data' => $categories]);
            
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '获取分类列表失败：' . $e->getMessage()]);
        }
    }
    
    /**
     * 获取文件标签列表
     */
    public function tags(Request $request)
    {
        try {
            $schoolId = $request->param('school_id');
            
            if (!$schoolId) {
                return json(['code' => 1, 'msg' => '学校ID不能为空']);
            }
            
            $tags = FileTag::getSchoolTags($schoolId);
            
            // 获取每个标签的统计信息
            foreach ($tags as &$tag) {
                $tag->statistics = $tag->getStatistics();
            }
            
            return json(['code' => 0, 'data' => $tags]);
            
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '获取标签列表失败：' . $e->getMessage()]);
        }
    }
    
    /**
     * 设置文件权限
     */
    public function setPermission(Request $request)
    {
        try {
            $fileId = $request->param('file_id');
            $userIds = $request->param('user_ids', []);
            $roleIds = $request->param('role_ids', []);
            $permissionType = $request->param('permission_type');
            $schoolId = $request->param('school_id');
            
            if (!$fileId || !$schoolId || !$permissionType) {
                return json(['code' => 1, 'msg' => '参数错误']);
            }
            
            // 检查文件是否存在
            $file = FileModel::find($fileId);
            if (!$file) {
                return json(['code' => 1, 'msg' => '文件不存在']);
            }
            
            // 设置用户权限
            if (!empty($userIds)) {
                FilePermission::batchSetPermission($fileId, $userIds, $permissionType, $schoolId);
            }
            
            // 设置角色权限
            if (!empty($roleIds)) {
                FilePermission::batchSetRolePermission($fileId, $roleIds, $permissionType, $schoolId);
            }
            
            return json(['code' => 0, 'msg' => '权限设置成功']);
            
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '设置权限失败：' . $e->getMessage()]);
        }
    }
    
    /**
     * 获取文件权限列表
     */
    public function permissions($fileId)
    {
        try {
            $permissions = FilePermission::with(['user', 'role'])
                                        ->where('file_id', $fileId)
                                        ->where('status', FilePermission::STATUS_ENABLED)
                                        ->select();
            
            return json(['code' => 0, 'data' => $permissions]);
            
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '获取权限列表失败：' . $e->getMessage()]);
        }
    }
    
    /**
     * 删除文件权限
     */
    public function deletePermission(Request $request)
    {
        try {
            $fileId = $request->param('file_id');
            $userIds = $request->param('user_ids', []);
            $roleIds = $request->param('role_ids', []);
            
            if (!$fileId) {
                return json(['code' => 1, 'msg' => '文件ID不能为空']);
            }
            
            FilePermission::batchDeletePermission($fileId, $userIds, $roleIds);
            
            return json(['code' => 0, 'msg' => '权限删除成功']);
            
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '删除权限失败：' . $e->getMessage()]);
        }
    }
    
    /**
     * 获取文件分享列表
     */
    public function shares($fileId)
    {
        try {
            $shares = FileShare::with(['user'])
                              ->where('file_id', $fileId)
                              ->where('status', FileShare::STATUS_ENABLED)
                              ->select();
            
            return json(['code' => 0, 'data' => $shares]);
            
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '获取分享列表失败：' . $e->getMessage()]);
        }
    }
    
    /**
     * 获取文件操作日志
     */
    public function logs(Request $request)
    {
        try {
            $fileId = $request->param('file_id');
            $page = $request->param('page', 1);
            $limit = $request->param('limit', 10);
            
            if (!$fileId) {
                return json(['code' => 1, 'msg' => '文件ID不能为空']);
            }
            
            $query = FileLog::with(['user', 'file'])
                           ->where('file_id', $fileId);
            
            $total = $query->count();
            $list = $query->order('create_time DESC')
                         ->page($page, $limit)
                         ->select();
            
            return json([
                'code' => 0,
                'data' => [
                    'list' => $list,
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit
                ]
            ]);
            
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '获取操作日志失败：' . $e->getMessage()]);
        }
    }
    
    /**
     * 获取文件统计信息
     */
    public function statistics(Request $request)
    {
        try {
            $schoolId = $request->param('school_id');
            
            if (!$schoolId) {
                return json(['code' => 1, 'msg' => '学校ID不能为空']);
            }
            
            // 获取文件统计
            $fileStats = FileModel::where('school_id', $schoolId)
                                ->where('status', FileModel::STATUS_NORMAL)
                                ->field('COUNT(*) as total_files, SUM(file_size) as total_size')
                                ->find();
            
            return json([
                'code' => 0,
                'data' => [
                    'file_statistics' => [
                        'total_files' => $fileStats->total_files ?? 0,
                        'total_size' => $fileStats->total_size ?? 0,
                        'total_size_text' => $this->formatFileSize($fileStats->total_size ?? 0)
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '获取统计信息失败：' . $e->getMessage()]);
        }
    }
    
    /**
     * 获取用户列表（用于权限设置）
     */
    public function users(Request $request)
    {
        try {
            $schoolId = $request->param('school_id');
            
            if (!$schoolId) {
                return json(['code' => 1, 'msg' => '学校ID不能为空']);
            }
            
            $users = User::where('school_id', $schoolId)
                        ->where('status', User::STATUS_ENABLED)
                        ->field('id,username,real_name,email')
                        ->select();
            
            return json(['code' => 0, 'data' => $users]);
            
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '获取用户列表失败：' . $e->getMessage()]);
        }
    }
    
    /**
     * 下载文件（参照前端API方式）
     */
    public function download(Request $request)
    {
        try {
            $id = $request->param('id');
            $file = FileModel::find($id);
            
            if (!$file) {
                return abort(404, '文件不存在');
            }
            
            // 增加下载次数
            $file->incrementDownload();
            
            // 根据存储类型处理下载
            switch ($file->storage_type) {
                case FileModel::STORAGE_LOCAL:
                    // 本地文件直接下载
                    $absPath = \app\service\StorageService::getFilePath($file);
                    if (!is_file($absPath)) {
                        return abort(404, '物理文件不存在');
                    }
                    return download($absPath, $file->original_name);
                    
                case FileModel::STORAGE_OSS:
                case FileModel::STORAGE_COS:
                    // 云存储文件重定向到下载链接
                    $downloadUrl = \app\service\StorageService::getFileUrl($file);
                    return redirect($downloadUrl);
                    
                default:
                    return abort(500, '不支持的存储类型');
            }
            
        } catch (\Exception $e) {
            return abort(500, '下载失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 原始文件下载（带附件头）
     * 参照前端API的raw方法实现
     */
    public function raw(Request $request)
    {
        try {
            $id = $request->param('id');
            $file = FileModel::find($id);
            
            if (!$file) {
                return abort(404, '文件不存在');
            }
            
            // 增加下载次数
            $file->incrementDownload();
            
            // 根据存储类型处理下载
            switch ($file->storage_type) {
                case FileModel::STORAGE_LOCAL:
                    // 本地文件直接下载
                    $absPath = \app\service\StorageService::getFilePath($file);
                    if (!is_file($absPath)) {
                        return abort(404, '物理文件不存在');
                    }
                    return download($absPath, $file->original_name);
                    
                case FileModel::STORAGE_OSS:
                case FileModel::STORAGE_COS:
                    // 云存储文件返回JSON响应，包含下载链接
                    $downloadUrl = \app\service\StorageService::getFileUrl($file);
                    return json([
                        'code' => 0,
                        'data' => [
                            'download_url' => $downloadUrl,
                            'file_name' => $file->original_name,
                            'message' => '请使用以下链接下载文件'
                        ]
                    ]);
                    
                default:
                    return abort(500, '不支持的存储类型');
            }
            
        } catch (\Exception $e) {
            return abort(500, '下载失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 创建文件夹
     */
    public function createFolder(Request $request)
    {
        return json(['code' => 1, 'msg' => '后台不支持创建文件夹功能']);
    }
    
    /**
     * 格式化文件大小
     */
    private function formatFileSize($bytes)
    {
        if ($bytes == 0) {
            return '0 B';
        }
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = $bytes;
        $unit = 0;
        
        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }
        
        return round($size, 2) . ' ' . $units[$unit];
    }
} 