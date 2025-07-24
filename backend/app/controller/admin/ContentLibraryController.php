<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\BaseController;
use app\model\ContentLibrary;
use app\model\School;
use app\model\College;
use app\model\User;
use app\model\ContentTag;
use app\model\ContentSpace;
use think\facade\View;
use think\Request;
use think\facade\Db;
use app\model\Course;

class ContentLibraryController extends BaseController
{
    /**
     * 内容库管理首页
     */
    public function index(Request $request)
    {
        try {
            if ($request->isAjax()) {
                $page = (int)$request->param('page', 1);
                $limit = (int)$request->param('limit', 10);
                $keyword = $request->param('keyword', '');
                $file_type = $request->param('file_type', '');
                $source_type = $request->param('source_type', '');
                $status = $request->param('status', '');
                $school_id = $request->param('school_id', '');
                $creator_id = $request->param('creator_id', '');

                $query = ContentLibrary::with(['creator', 'school', 'college', 'course', 'files', 'auditUser'])
                    ->where('is_deleted', 0);

                // 关键词搜索
                if (!empty($keyword)) {
                    $query->where('name|content', 'like', "%{$keyword}%");
                }

                // 课程筛选
                if (!empty($request->param('course_id'))) {
                    $query->where('course_id', (int)$request->param('course_id'));
                }

                // 来源类型筛选
                if ($source_type !== '' && $source_type !== null) {
                    $query->where('source_type', $source_type);
                }

                // 状态筛选
                if ($status !== '' && $status !== null) {
                    $query->where('status', $status);
                }

                // 学校筛选
                if ($school_id !== '' && $school_id !== null) {
                    $query->where('school_id', (int)$school_id);
                }

                // 创建者筛选
                if ($creator_id !== '' && $creator_id !== null) {
                    $query->where('creator_id', (int)$creator_id);
                }

                $countQuery = clone $query;
                $total = $countQuery->count();

                $list = $query->order('id', 'desc')
                    ->page($page, $limit)
                    ->select()
                    ->toArray();

                // 处理数据格式
                foreach ($list as &$item) {
                    $item['creator_name'] = $item['creator']['username'] ?? '-';
                    $item['school_name'] = $item['school']['name'] ?? '-';
                    $item['college_name'] = $item['college']['name'] ?? '-';
                    $item['course_name'] = $item['course']['name'] ?? '-';
                    // 多文件处理
                    $item['file_list'] = [];
                    if (!empty($item['files'])) {
                        foreach ($item['files'] as $file) {
                            // 检查文件数据是否有效
                            if (empty($file) || !isset($file['id']) || !isset($file['file_path'])) {
                                continue;
                            }
                            
                            $fileObj = new \app\model\File();
                            $fileObj->setAttrs($file);
                            
                            $item['file_list'][] = [
                                'id' => $file['id'],
                                'file_name' => $file['file_name'] ?? '',
                                'file_size_text' => $fileObj->formatFileSize($file['file_size'] ?? 0),
                                'file_type' => $file['file_type'] ?? '',
                                'url' => \app\service\StorageService::getFileUrl($fileObj),
                            ];
                        }
                    }
                    $item['audit_user_name'] = $item['audit_user']['username'] ?? '-';
                    $item['create_time_text'] = date('Y-m-d H:i:s', strtotime($item['create_time']));
                    $item['update_time_text'] = date('Y-m-d H:i:s', strtotime($item['update_time']));
                }

                return json([
                    'code' => 0,
                    'msg' => '',
                    'count' => $total,
                    'data' => $list
                ]);
            }

            // 获取筛选选项
            $sourceTypes = ContentLibrary::getSourceTypeList();
            $statuses = ContentLibrary::getStatusList();
            $schools = School::where('status', 1)->select()->toArray();
            $creators = User::where('role', 'teacher')->select()->toArray();
            $courses = Course::where('status', 1)->field('id,name')->order('name', 'asc')->select()->toArray();

            return View::fetch('admin/content_library/index', [
                'sourceTypes' => $sourceTypes,
                'statuses' => $statuses,
                'schools' => $schools,
                'creators' => $creators,
                'courses' => $courses
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
     * 内容审核页面
     */
    public function audit(Request $request)
    {
        try {
            if ($request->isAjax()) {
                $page = (int)$request->param('page', 1);
                $limit = (int)$request->param('limit', 10);
                $status = $request->param('status', ContentLibrary::STATUS_PENDING);
                $school_id = $request->param('school_id', '');
                $creator_id = $request->param('creator_id', '');

                $query = ContentLibrary::with(['creator', 'school', 'college', 'course', 'files'])
                    ->where('is_deleted', 0)
                    ->where('status', $status);

                // 学校筛选
                if ($school_id !== '' && $school_id !== null) {
                    $query->where('school_id', (int)$school_id);
                }

                // 创建者筛选
                if ($creator_id !== '' && $creator_id !== null) {
                    $query->where('creator_id', (int)$creator_id);
                }

                $countQuery = clone $query;
                $total = $countQuery->count();

                $list = $query->order('id', 'desc')
                    ->page($page, $limit)
                    ->select()
                    ->toArray();

                // 处理数据格式
                foreach ($list as &$item) {
                    $item['creator_name'] = $item['creator']['username'] ?? '-';
                    $item['school_name'] = $item['school']['name'] ?? '-';
                    $item['college_name'] = $item['college']['name'] ?? '-';
                    // 多文件处理
                    $item['file_list'] = [];
                    if (!empty($item['files'])) {
                        foreach ($item['files'] as $file) {
                            // 检查文件数据是否有效
                            if (empty($file) || !isset($file['id']) || !isset($file['file_path'])) {
                                continue;
                            }
                            
                            $fileObj = new \app\model\File();
                            $fileObj->setAttrs($file);
                            
                            $item['file_list'][] = [
                                'id' => $file['id'],
                                'file_name' => $file['file_name'] ?? '',
                                'file_size_text' => $fileObj->formatFileSize($file['file_size'] ?? 0),
                                'file_type' => $file['file_type'] ?? '',
                                'url' => \app\service\StorageService::getFileUrl($fileObj),
                            ];
                        }
                    }
                    $item['create_time_text'] = date('Y-m-d H:i:s', strtotime($item['create_time']));
                    $item['update_time_text'] = date('Y-m-d H:i:s', strtotime($item['update_time']));
                }

                return json([
                    'code' => 0,
                    'msg' => '',
                    'count' => $total,
                    'data' => $list
                ]);
            }

            // 获取筛选选项
            $schools = School::where('status', 1)->select()->toArray();
            $creators = User::where('role', 'teacher')->select()->toArray();

            return View::fetch('admin/content_library/audit', [
                'schools' => $schools,
                'creators' => $creators
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
     * 删除内容
     */
    public function delete(Request $request)
    {
        $id = $request->param('id/d');
        $content = ContentLibrary::find($id);
        
        if (!$content) {
            return json(['code' => 1, 'msg' => '内容不存在']);
        }
        
        // 软删除
        $content->is_deleted = 1;
        $content->save();
        
        return json(['code' => 0, 'msg' => '删除成功']);
    }

    /**
     * 查看内容详情
     */
    public function view(Request $request)
    {
        try {
            $id = $request->param('id/d');
            $content = ContentLibrary::with(['creator', 'school', 'college', 'course', 'files', 'auditUser', 'statistics'])
                ->where('is_deleted', 0)
                ->find($id);
            
            if (!$content) {
                return $this->error('内容不存在');
            }
            
            // 处理数据格式
            $content->file_size_text = $this->formatFileSize($content->file_size ?? 0);
            $content->create_time_text = $content->create_time ? date('Y-m-d H:i:s', strtotime($content->create_time)) : '-';
            $content->update_time_text = $content->update_time ? date('Y-m-d H:i:s', strtotime($content->update_time)) : '-';
            $content->audit_time_text = $content->audit_time ? date('Y-m-d H:i:s', strtotime($content->audit_time)) : '-';
            
            // 处理关联数据
            $content->creator_name = $content->creator->username ?? '-';
            $content->school_name = $content->school->name ?? '-';
            $content->college_name = $content->college->name ?? '-';
            $content->audit_user_name = $content->auditUser->username ?? '-';
            
            // 处理文件URL（用于模板显示）
            if (!empty($content->files) && count($content->files) > 0) {
                $file = $content->files[0]; // 取第一个文件作为主要文件
                if ($file && isset($file->file_path)) {
                    $content->file_url = \app\service\StorageService::getFileUrl($file);
                } else {
                    $content->file_url = '';
                }
            } else {
                $content->file_url = '';
            }
            
            // 处理文件列表，为每个文件添加格式化信息
            if (!empty($content->files)) {
                foreach ($content->files as &$file) {
                    if ($file) {
                        $file->file_size_text = $this->formatFileSize($file->file_size ?? 0);
                        if (isset($file->file_path)) {
                            $file->file_url = \app\service\StorageService::getFileUrl($file);
                        } else {
                            $file->file_url = '';
                        }
                    }
                }
            }
            
            // 检测是否为JSON内容
            if ($content->file_type === 'text' && !empty($content->content)) {
                $content->is_json = false;
                $json = @json_decode($content->content);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $content->is_json = true;
                }
            } else {
                $content->is_json = false;
            }
            
            return View::fetch('admin/content_library/view', [
                'content' => $content
            ]);
            
        } catch (\Exception $e) {
            return $this->error('页面加载失败：' . $e->getMessage());
        }
    }

    /**
     * 审核内容操作
     */
    public function auditAction(Request $request)
    {
        $id = $request->param('id/d');
        $action = $request->param('action'); // approve 或 reject
        $remark = $request->param('remark', '');
        
        $content = ContentLibrary::where('is_deleted', 0)->find($id);
        
        if (!$content) {
            return json(['code' => 1, 'msg' => '内容不存在']);
        }
        
        if ($content->status !== ContentLibrary::STATUS_PENDING) {
            return json(['code' => 1, 'msg' => '内容状态不允许审核']);
        }
        
        // 从session或cookie中获取管理员ID
        $adminId = session('admin_id');
        if (!$adminId) {
            $adminId = cookie('admin_id');
        }
        
        if (!$adminId) {
            return json(['code' => 1, 'msg' => '未找到管理员信息，请重新登录']);
        }
        
        try {
            if ($action === 'approve') {
                $content->approve($adminId, $remark);
                $msg = '审核通过成功';
            } elseif ($action === 'reject') {
                $content->reject($adminId, $remark);
                $msg = '审核驳回成功';
            } else {
                return json(['code' => 1, 'msg' => '无效的审核操作']);
            }
            
            return json(['code' => 0, 'msg' => $msg]);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '审核失败：' . $e->getMessage()]);
        }
    }

    /**
     * 内容统计
     */
    public function statistics(Request $request)
    {
        try {
            // 总体统计
            $totalCount = ContentLibrary::where('is_deleted', 0)->count();
            $draftCount = ContentLibrary::where('is_deleted', 0)->where('status', ContentLibrary::STATUS_DRAFT)->count();
            $pendingCount = ContentLibrary::where('is_deleted', 0)->where('status', ContentLibrary::STATUS_PENDING)->count();
            $approvedCount = ContentLibrary::where('is_deleted', 0)->where('status', ContentLibrary::STATUS_APPROVED)->count();
            $rejectedCount = ContentLibrary::where('is_deleted', 0)->where('status', ContentLibrary::STATUS_REJECTED)->count();
            
            // 按来源类型统计（替代文件类型）
            $sourceTypeStats = ContentLibrary::where('is_deleted', 0)
                ->field('source_type, count(*) as count')
                ->group('source_type')
                ->select()
                ->toArray();
            
            // 按状态统计
            $statusStats = ContentLibrary::where('is_deleted', 0)
                ->field('status, count(*) as count')
                ->group('status')
                ->select()
                ->toArray();
            
            // 按学校统计
            $schoolStats = ContentLibrary::alias('cl')
                ->join('edu_school s', 'cl.school_id = s.id')
                ->where('cl.is_deleted', 0)
                ->field('s.name as school_name, count(*) as count')
                ->group('cl.school_id')
                ->order('count', 'desc')
                ->limit(10)
                ->select()
                ->toArray();
            
            // 最近7天创建统计
            $recentStats = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-{$i} days"));
                $count = ContentLibrary::where('is_deleted', 0)
                    ->whereTime('create_time', 'between', [$date . ' 00:00:00', $date . ' 23:59:59'])
                    ->count();
                $recentStats[] = [
                    'date' => $date,
                    'count' => $count
                ];
            }
            
            if ($request->isAjax()) {
                return json([
                    'code' => 0,
                    'data' => [
                        'overview' => [
                            'total' => $totalCount,
                            'draft' => $draftCount,
                            'pending' => $pendingCount,
                            'approved' => $approvedCount,
                            'rejected' => $rejectedCount
                        ],
                        'sourceTypeStats' => $sourceTypeStats,
                        'statusStats' => $statusStats,
                        'schoolStats' => $schoolStats,
                        'recentStats' => $recentStats
                    ]
                ]);
            }
            
            return View::fetch('admin/content_library/statistics', [
                'overview' => [
                    'total' => $totalCount,
                    'draft' => $draftCount,
                    'pending' => $pendingCount,
                    'approved' => $approvedCount,
                    'rejected' => $rejectedCount
                ],
                'sourceTypeStats' => $sourceTypeStats,
                'statusStats' => $statusStats,
                'schoolStats' => $schoolStats,
                'recentStats' => $recentStats
            ]);
            
        } catch (\Exception $e) {
            if ($request->isAjax()) {
                return json(['code' => 1, 'msg' => '统计失败：' . $e->getMessage()]);
            } else {
                return $this->error('统计页面加载失败：' . $e->getMessage());
            }
        }
    }

    /**
     * 批量操作
     */
    public function batch(Request $request)
    {
        $action = $request->param('action');
        $ids = $request->param('ids/a');
        
        if (empty($ids)) {
            return json(['code' => 1, 'msg' => '请选择要操作的内容']);
        }
        
        // 从session或cookie中获取管理员ID
        $adminId = session('admin_id');
        if (!$adminId) {
            $adminId = cookie('admin_id');
        }
        
        if (!$adminId) {
            return json(['code' => 1, 'msg' => '未找到管理员信息，请重新登录']);
        }
        
        try {
            switch ($action) {
                case 'delete':
                    ContentLibrary::whereIn('id', $ids)->update(['is_deleted' => 1]);
                    $msg = '批量删除成功';
                    break;
                    
                case 'approve':
                    foreach ($ids as $id) {
                        $content = ContentLibrary::find($id);
                        if ($content && $content->status === ContentLibrary::STATUS_PENDING) {
                            $content->approve($adminId, '批量审核通过');
                        }
                    }
                    $msg = '批量审核通过成功';
                    break;
                    
                case 'reject':
                    $remark = $request->param('remark', '批量审核驳回');
                    foreach ($ids as $id) {
                        $content = ContentLibrary::find($id);
                        if ($content && $content->status === ContentLibrary::STATUS_PENDING) {
                            $content->reject($adminId, $remark);
                        }
                    }
                    $msg = '批量审核驳回成功';
                    break;
                    
                default:
                    return json(['code' => 1, 'msg' => '无效的操作类型']);
            }
            
            return json(['code' => 0, 'msg' => $msg]);
            
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '操作失败：' . $e->getMessage()]);
        }
    }

    /**
     * 格式化文件大小
     */
    private function formatFileSize($size)
    {
        if (empty($size) || $size === null) {
            return '0 B';
        }
        
        $size = (int)$size;
        if ($size < 1024) {
            return $size . ' B';
        } elseif ($size < 1024 * 1024) {
            return round($size / 1024, 2) . ' KB';
        } elseif ($size < 1024 * 1024 * 1024) {
            return round($size / (1024 * 1024), 2) . ' MB';
        } else {
            return round($size / (1024 * 1024 * 1024), 2) . ' GB';
        }
    }
}
 