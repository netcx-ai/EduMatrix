<?php
declare(strict_types=1);

namespace app\controller\api\school;

use app\controller\api\BaseController;
use app\model\ContentAudit;
use app\model\Teacher;
use think\Request;
use think\facade\Validate;

/**
 * 学校侧审核管理控制器
 */
class AuditController extends BaseController
{
    /**
     * 待审核教师列表
     */
    public function teachers(Request $request)
    {
        try {
            $page = $request->param('page', 1);
            $limit = $request->param('limit', 15);
            $keyword = $request->param('keyword', '');
            $status = $request->param('status', 'pending');
            
            $schoolAdmin = $request->user;
            
            $query = Teacher::with(['school', 'college'])
                ->where('school_id', $schoolAdmin->school_id);
            
            // 状态筛选
            if ($status === 'pending') {
                $query->where('status', 0);  // 待审核
            } elseif ($status === 'approved') {
                $query->where('status', 1);  // 已通过
            } elseif ($status === 'rejected') {
                $query->where('status', 2);  // 已驳回
            }
            
            // 关键词搜索
            if ($keyword) {
                $query->where('name|phone|email', 'like', "%{$keyword}%");
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
            return $this->error('获取教师列表失败：' . $e->getMessage());
        }
    }
    
    /**
     * 文件审核列表
     */
    public function files(Request $request)
    {
        try {
            $page = $request->param('page', 1);
            $limit = $request->param('limit', 15);
            $keyword = $request->param('keyword', '');
            $status = $request->param('status', ContentAudit::STATUS_PENDING);
            $content_type = $request->param('content_type', '');
            
            $schoolAdmin = $request->user;
            
            $query = ContentAudit::with(['file', 'course', 'submitter', 'reviewer'])
                ->where('school_id', $schoolAdmin->school_id);
            
            // 状态筛选
            if ($status) {
                $query->where('status', $status);
            }
            
            // 内容类型筛选
            if ($content_type) {
                $query->where('content_type', $content_type);
            }
            
            // 关键词搜索
            if ($keyword) {
                $query->where('content_title|content_description', 'like', "%{$keyword}%");
            }
            
            $total = $query->count();
            $list = $query->order('priority DESC, create_time DESC')
                         ->page($page, $limit)
                         ->select();
            
            return $this->success([
                'list' => $list,
                'total' => $total,
                'page' => $page,
                'limit' => $limit
            ]);
            
        } catch (\Exception $e) {
            return $this->error('获取审核列表失败：' . $e->getMessage());
        }
    }
    
    /**
     * 审核详情
     */
    public function show(Request $request, $id)
    {
        try {
            $schoolAdmin = $request->user;
            
            $audit = ContentAudit::with(['file', 'course', 'submitter', 'reviewer'])
                ->where('school_id', $schoolAdmin->school_id)
                ->find($id);
            
            if (!$audit) {
                return $this->error('审核记录不存在');
            }
            
            return $this->success($audit);
            
        } catch (\Exception $e) {
            return $this->error('获取审核详情失败：' . $e->getMessage());
        }
    }
    
    /**
     * 审核操作
     */
    public function review(Request $request, $id)
    {
        try {
            $data = $request->post();
            
            // 验证数据
            $validate = Validate::rule([
                'action' => 'require|in:approve,reject',
                'remark' => 'max:500'
            ]);
            
            if (!$validate->check($data)) {
                return $this->error($validate->getError());
            }
            
            $schoolAdmin = $request->user;
            
            $audit = ContentAudit::where('school_id', $schoolAdmin->school_id)
                ->where('status', ContentAudit::STATUS_PENDING)
                ->find($id);
            
            if (!$audit) {
                return $this->error('审核记录不存在或已处理');
            }
            
            $action = $data['action'];
            $remark = $data['remark'] ?? '';
            
            if ($action === 'approve') {
                $result = $audit->approve($schoolAdmin->id, $remark);
                $message = '审核通过';
            } else {
                $result = $audit->reject($schoolAdmin->id, $remark);
                $message = '审核驳回';
            }
            
            if (!$result) {
                return $this->error('审核操作失败');
            }
            
            return $this->success($audit, $message);
            
        } catch (\Exception $e) {
            return $this->error('审核操作失败：' . $e->getMessage());
        }
    }
    
    /**
     * 批量审核
     */
    public function batchReview(Request $request)
    {
        try {
            $data = $request->post();
            
            // 验证数据
            $validate = Validate::rule([
                'ids' => 'require|array',
                'action' => 'require|in:approve,reject',
                'remark' => 'max:500'
            ]);
            
            if (!$validate->check($data)) {
                return $this->error($validate->getError());
            }
            
            $schoolAdmin = $request->user;
            $ids = $data['ids'];
            $action = $data['action'];
            $remark = $data['remark'] ?? '';
            
            $audits = ContentAudit::where('school_id', $schoolAdmin->school_id)
                ->where('status', ContentAudit::STATUS_PENDING)
                ->where('id', 'in', $ids)
                ->select();
            
            if ($audits->isEmpty()) {
                return $this->error('没有找到待审核的记录');
            }
            
            $successCount = 0;
            foreach ($audits as $audit) {
                if ($action === 'approve') {
                    $result = $audit->approve($schoolAdmin->id, $remark);
                } else {
                    $result = $audit->reject($schoolAdmin->id, $remark);
                }
                
                if ($result) {
                    $successCount++;
                }
            }
            
            $message = $action === 'approve' ? '批量审核通过' : '批量审核驳回';
            return $this->success([
                'success_count' => $successCount,
                'total_count' => count($ids)
            ], $message . "成功 {$successCount} 条");
            
        } catch (\Exception $e) {
            return $this->error('批量审核失败：' . $e->getMessage());
        }
    }
    
    /**
     * 审核统计
     */
    public function statistics(Request $request)
    {
        try {
            $schoolAdmin = $request->user;
            
            // 总体统计
            $totalCount = ContentAudit::where('school_id', $schoolAdmin->school_id)->count();
            $pendingCount = ContentAudit::where('school_id', $schoolAdmin->school_id)
                ->where('status', ContentAudit::STATUS_PENDING)->count();
            $approvedCount = ContentAudit::where('school_id', $schoolAdmin->school_id)
                ->where('status', ContentAudit::STATUS_APPROVED)->count();
            $rejectedCount = ContentAudit::where('school_id', $schoolAdmin->school_id)
                ->where('status', ContentAudit::STATUS_REJECTED)->count();
            
            // 按类型统计
            $fileCount = ContentAudit::where('school_id', $schoolAdmin->school_id)
                ->where('content_type', ContentAudit::TYPE_FILE)->count();
            $courseCount = ContentAudit::where('school_id', $schoolAdmin->school_id)
                ->where('content_type', ContentAudit::TYPE_COURSE)->count();
            
            // 今日统计
            $todayCount = ContentAudit::where('school_id', $schoolAdmin->school_id)
                ->whereTime('create_time', 'today')->count();
            $todayPendingCount = ContentAudit::where('school_id', $schoolAdmin->school_id)
                ->where('status', ContentAudit::STATUS_PENDING)
                ->whereTime('create_time', 'today')->count();
            
            return $this->success([
                'total' => [
                    'total_count' => $totalCount,
                    'pending_count' => $pendingCount,
                    'approved_count' => $approvedCount,
                    'rejected_count' => $rejectedCount
                ],
                'by_type' => [
                    'file_count' => $fileCount,
                    'course_count' => $courseCount
                ],
                'today' => [
                    'today_count' => $todayCount,
                    'today_pending_count' => $todayPendingCount
                ]
            ]);
            
        } catch (\Exception $e) {
            return $this->error('获取审核统计失败：' . $e->getMessage());
        }
    }
    
    /**
     * 获取审核状态选项
     */
    public function statusOptions(Request $request)
    {
        try {
            $statusList = ContentAudit::getStatusList();
            $options = [];
            
            foreach ($statusList as $key => $value) {
                $options[] = [
                    'value' => $key,
                    'label' => $value
                ];
            }
            
            return $this->success($options);
            
        } catch (\Exception $e) {
            return $this->error('获取状态选项失败：' . $e->getMessage());
        }
    }
    
    /**
     * 获取内容类型选项
     */
    public function typeOptions(Request $request)
    {
        try {
            $typeList = ContentAudit::getTypeList();
            $options = [];
            
            foreach ($typeList as $key => $value) {
                $options[] = [
                    'value' => $key,
                    'label' => $value
                ];
            }
            
            return $this->success($options);
            
        } catch (\Exception $e) {
            return $this->error('获取类型选项失败：' . $e->getMessage());
        }
    }
} 