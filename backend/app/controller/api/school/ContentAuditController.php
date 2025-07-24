<?php
declare(strict_types=1);

namespace app\controller\api\school;

use app\controller\api\BaseController;
use app\model\ContentAudit;
use think\Request;
use think\facade\Validate;

class ContentAuditController extends BaseController
{
    /**
     * 待审核列表 (GET /api/school/audit/content)
     */
    public function pending(Request $request)
    {
        $user = $request->user;
        $schoolId = $user->primary_school_id;
        $page  = (int)$request->param('page',1);
        $limit = (int)$request->param('limit',15);

        $query = ContentAudit::with(['file'])
            ->where('school_id', $schoolId)
            ->where('status', ContentAudit::STATUS_PENDING)
            ->order('create_time','desc');
        $total = $query->count();
        $list  = $query->page($page,$limit)->select();
        return $this->success(compact('list','total','page','limit'));
    }

    /**
     * 审核通过/驳回 (POST /api/school/audit/content/:id)
     * 参数: result = approved|rejected, remark
     */
    public function review(Request $request, $id)
    {
        $data = $request->post();
        $validate = Validate::rule([
            'result' => 'require|in:approved,rejected',
            'remark' => 'max:255'
        ]);
        if (!$validate->check($data)) {
            return $this->error($validate->getError());
        }
        $audit = ContentAudit::find($id);
        if (!$audit || $audit->status !== ContentAudit::STATUS_PENDING) {
            return $this->error('记录不存在或已处理');
        }
        if ($data['result']==='approved') {
            $audit->approve($request->userId, $data['remark'] ?? '');
            // 更新内容主表状态
            \app\model\ContentLibrary::where('id', $audit->file_id)->update(['status'=>'approved']);
            // 若有指定课程，迁移到课程空间
            if ($audit->course_id) {
                \app\service\ContentService::moveToCourse($audit->file_id, $audit->course_id, 'public');
            }
        } else {
            $audit->reject($request->userId, $data['remark'] ?? '');
        }
        return $this->success($audit,'操作成功');
    }
} 