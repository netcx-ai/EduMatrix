<?php
declare (strict_types = 1);

namespace app\controller\admin;

use app\BaseController;
use app\model\Teacher;
use app\model\School;
use app\model\College;
use app\model\TeacherTitle;
use think\facade\Validate;
use think\facade\Log;
use think\facade\View;
use think\Request;

class TeacherController extends BaseController
{
    /**
     * 教师管理页面
     */
    public function index()
    {
        try {
            if ($this->request->isAjax()) {
                $page = $this->request->param('page', 1);
                $limit = $this->request->param('limit', 20);
                $school_id = $this->request->param('school_id', '');
                $college_id = $this->request->param('college_id', '');
                $keyword = $this->request->param('keyword', '');
                $status = $this->request->param('status', '');
                $title = $this->request->param('title', '');

                $query = Teacher::with(['school', 'college']);
                if ($school_id) {
                    $query->where('school_id', $school_id);
                }
                if ($college_id) {
                    $query->where('college_id', $college_id);
                }
                if ($keyword) {
                    $query->where('real_name|teacher_no|phone|email', 'like', "%{$keyword}%");
                }
                if ($status !== '') {
                    $query->where('status', $status);
                }
                if ($title !== '') {
                    $query->where('title', $title);
                }
                $list = $query->order('create_time', 'desc')
                    ->paginate([
                        'list_rows' => $limit,
                        'page' => $page
                    ]);
                $items = $list->items();
                return json([
                    'code' => 0,
                    'msg' => '',
                    'count' => $list->total(),
                    'data' => $items
                ]);
            }

            // 获取学校列表（用于下拉选择）
            $schools = School::where('status', 1)
                ->field('id,name,code,short_name')
                ->order('name', 'asc')
                ->select();

            return View::fetch('admin/teacher/index', [
                'schools' => $schools
            ]);
        } catch (\Exception $e) {
            Log::error("获取教师列表失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '获取教师列表失败']);
        }
    }

    /**
     * 获取教师详情
     */
    public function show($id)
    {
        try {
            $teacher = Teacher::with(['school', 'college'])->find($id);
            if (!$teacher) {
                return json(['code' => 404, 'message' => '教师不存在']);
            }
            return json(['code' => 0, 'data' => $teacher]);
        } catch (\Exception $e) {
            Log::error("获取教师详情失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '获取教师详情失败']);
        }
    }
    
    /**
     * 添加教师页面
     */
    public function add()
    {
        try {
            // 获取学校列表
            $schools = School::where('status', 1)
                ->field('id,name,code,short_name')
                ->order('name', 'asc')
                ->select();
            
            // 获取职称选项
            $titleOptions = TeacherTitle::getTitleOptions();
            
            return View::fetch('admin/teacher/add', [
                'schools' => $schools,
                'titleOptions' => $titleOptions
            ]);
        } catch (\Exception $e) {
            Log::error("获取添加教师页面失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '页面加载失败']);
        }
    }
    
    /**
     * 编辑教师页面
     */
    public function edit()
    {
        $id = $this->request->param('id');
        $teacher = Teacher::find($id);
        if (!$teacher) {
            $this->error('教师不存在');
        }
        
        // 获取学校列表
        $schools = School::where('status', 1)
            ->field('id,name,code,short_name')
            ->order('name', 'asc')
            ->select();
            
        // 获取学院列表
        $colleges = [];
        if ($teacher->school_id) {
            $colleges = College::where('school_id', $teacher->school_id)
                ->where('status', 1)
                ->field('id,name,code,short_name')
                ->order('name', 'asc')
                ->select();
        }
        
        // 获取职称选项
        $titleOptions = TeacherTitle::getTitleOptions();
        
        View::assign([
            'teacher' => $teacher,
            'schools' => $schools,
            'colleges' => $colleges,
            'titleOptions' => $titleOptions
        ]);
        return View::fetch('admin/teacher/edit');
    }
    
    /**
     * 教师详情页面
     */
    public function detail()
    {
        $id = $this->request->param('id');
        $teacher = Teacher::with(['school', 'college'])->find($id);
        if (!$teacher) {
            $this->error('教师不存在');
        }
        View::assign('teacher', $teacher);
        return View::fetch('admin/teacher/detail');
    }

    /**
     * 创建教师
     */
    public function store()
    {
        $data = $this->request->post();
        $validate = Validate::rule([
            'school_id' => 'require|integer',
            'college_id' => 'integer',
            'name' => 'require|length:2,50',
            'teacher_no' => 'require|length:2,50',
            'phone' => 'mobile',
            'email' => 'email',
            'gender' => 'in:1,2',
            'title' => 'length:0,50',
            'department' => 'length:0,100',
            'specialty' => 'length:0,100',
            'education' => 'length:0,50',
            'degree' => 'length:0,50',
            'status' => 'in:0,1'
        ])->message([
            'school_id.require' => '学校ID不能为空',
            'school_id.integer' => '学校ID格式不正确',
            'college_id.integer' => '学院ID格式不正确',
            'name.require' => '教师姓名不能为空',
            'name.length' => '教师姓名长度必须在2-50个字符之间',
            'teacher_no.require' => '工号不能为空',
            'teacher_no.length' => '工号长度必须在2-50个字符之间',
            'phone.mobile' => '手机号格式不正确',
            'email.email' => '邮箱格式不正确',
            'gender.in' => '性别值不正确',
            'title.length' => '职称长度不能超过50个字符',
            'department.length' => '部门长度不能超过100个字符',
            'specialty.length' => '专业长度不能超过100个字符',
            'education.length' => '学历长度不能超过50个字符',
            'degree.length' => '学位长度不能超过50个字符',
            'status.in' => '状态值不正确'
        ]);
        if (!$validate->check($data)) {
            return json(['code' => 400, 'message' => $validate->getError()]);
        }
        try {
            if (Teacher::isTeacherNoExists($data['school_id'], $data['teacher_no'])) {
                return json(['code' => 400, 'message' => '工号已存在']);
            }
            if (!empty($data['phone']) && Teacher::isPhoneExists($data['school_id'], $data['phone'])) {
                return json(['code' => 400, 'message' => '手机号已存在']);
            }
            if (!empty($data['email']) && Teacher::isEmailExists($data['school_id'], $data['email'])) {
                return json(['code' => 400, 'message' => '邮箱已存在']);
            }
            $teacher = new Teacher();
            $teacher->school_id = $data['school_id'];
            $teacher->college_id = $data['college_id'] ?? null;
            $teacher->name = $data['name'];
            $teacher->teacher_no = $data['teacher_no'];
            $teacher->phone = $data['phone'] ?? '';
            $teacher->email = $data['email'] ?? '';
            $teacher->gender = $data['gender'] ?? 1;
            $teacher->title = $data['title'] ?? '';
            $teacher->department = $data['department'] ?? '';
            $teacher->specialty = $data['specialty'] ?? '';
            $teacher->education = $data['education'] ?? '';
            $teacher->degree = $data['degree'] ?? '';
            $teacher->status = $data['status'] ?? 1;
            $teacher->save();
            return json(['code' => 0, 'message' => '教师创建成功', 'data' => $teacher]);
        } catch (\Exception $e) {
            Log::error("创建教师失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '教师创建失败']);
        }
    }

    /**
     * 更新教师
     */
    public function update($id)
    {
        $data = $this->request->post();
        try {
            $teacher = Teacher::find($id);
            if (!$teacher) {
                return json(['code' => 404, 'message' => '教师不存在']);
            }
            $validate = Validate::rule([
                'real_name' => 'require|length:2,50',
                'teacher_no' => 'require|length:2,50',
                'phone' => 'mobile',
                'email' => 'email',
                'gender' => 'in:1,2',
                'title' => 'length:0,50',
                'department' => 'length:0,100',
                'specialty' => 'length:0,100',
                'education' => 'length:0,50',
                'degree' => 'length:0,50',
                'status' => 'in:0,1'
            ])->message([
                'real_name.require' => '教师姓名不能为空',
                'real_name.length' => '教师姓名长度必须在2-50个字符之间',
                'teacher_no.require' => '工号不能为空',
                'teacher_no.length' => '工号长度必须在2-50个字符之间',
                'phone.mobile' => '手机号格式不正确',
                'email.email' => '邮箱格式不正确',
                'gender.in' => '性别值不正确',
                'title.length' => '职称长度不能超过50个字符',
                'department.length' => '部门长度不能超过100个字符',
                'specialty.length' => '专业长度不能超过100个字符',
                'education.length' => '学历长度不能超过50个字符',
                'degree.length' => '学位长度不能超过50个字符',
                'status.in' => '状态值不正确'
            ]);
            if (!$validate->check($data)) {
                return json(['code' => 400, 'message' => $validate->getError()]);
            }
            if (Teacher::isTeacherNoExists($teacher->school_id, $data['teacher_no'], $id)) {
                return json(['code' => 400, 'message' => '工号已存在']);
            }
            if (!empty($data['phone']) && Teacher::isPhoneExists($teacher->school_id, $data['phone'], $id)) {
                return json(['code' => 400, 'message' => '手机号已存在']);
            }
            if (!empty($data['email']) && Teacher::isEmailExists($teacher->school_id, $data['email'], $id)) {
                return json(['code' => 400, 'message' => '邮箱已存在']);
            }
            $teacher->college_id = $data['college_id'] ?? null;
            $teacher->real_name = $data['real_name'];
            $teacher->teacher_no = $data['teacher_no'];
            $teacher->phone = $data['phone'] ?? '';
            $teacher->email = $data['email'] ?? '';
            $teacher->gender = $data['gender'] ?? 1;
            $teacher->title = $data['title'] ?? '';
            $teacher->department = $data['department'] ?? '';
            $teacher->specialty = $data['specialty'] ?? '';
            $teacher->education = $data['education'] ?? '';
            $teacher->degree = $data['degree'] ?? '';
            $teacher->status = $data['status'] ?? 1;
            $teacher->save();
            return json(['code' => 0, 'message' => '教师更新成功', 'data' => $teacher]);
        } catch (\Exception $e) {
            Log::error("更新教师失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '教师更新失败']);
        }
    }

    /**
     * 删除教师
     */
    public function destroy($id)
    {
        try {
            $teacher = Teacher::find($id);
            if (!$teacher) {
                return json(['code' => 404, 'message' => '教师不存在']);
            }
            $teacher->delete();
            return json(['code' => 0, 'message' => '教师删除成功']);
        } catch (\Exception $e) {
            Log::error("删除教师失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '教师删除失败']);
        }
    }
    
    /**
     * 删除教师（页面方法）
     */
    public function delete()
    {
        $id = $this->request->param('id');
        return $this->destroy($id);
    }
    
    /**
     * 修改教师状态（页面方法）
     */
    public function changeStatus()
    {
        $id = $this->request->param('id');
        $status = $this->request->param('status');
        
        try {
            $teacher = Teacher::find($id);
            if (!$teacher) {
                return json(['code' => 404, 'message' => '教师不存在']);
            }
            
            $teacher->status = $status;
            $teacher->save();
            return json(['code' => 0, 'message' => '状态修改成功']);
            
        } catch (\Exception $e) {
            Log::error("修改教师状态失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '状态修改失败']);
        }
    }
    
    /**
     * 获取教师列表（API）
     */
    public function getList()
    {
        try {
            $school_id = $this->request->param('school_id', '');
            $college_id = $this->request->param('college_id', '');
            
            $query = Teacher::where('status', 1);
            if ($school_id) {
                $query->where('school_id', $school_id);
            }
            if ($college_id) {
                $query->where('college_id', $college_id);
            }
            
            $teachers = $query->field('id,name,teacher_no,title')
                ->order('name', 'asc')
                ->select();
            
            return json([
                'code' => 200,
                'data' => [
                    'list' => $teachers
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("获取教师列表失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '获取教师列表失败']);
        }
    }
    
    /**
     * 获取教师详情（API）
     */
    public function getDetail()
    {
        $id = $this->request->param('id');
        return $this->show($id);
    }

    /**
     * 审核教师
     */
    public function verify($id)
    {
        $data = $this->request->post();
        $validate = Validate::rule([
            'status' => 'require|in:0,1',
            'remark' => 'length:0,500'
        ])->message([
            'status.require' => '审核状态不能为空',
            'status.in' => '审核状态值不正确',
            'remark.length' => '备注长度不能超过500个字符'
        ]);
        if (!$validate->check($data)) {
            return json(['code' => 400, 'message' => $validate->getError()]);
        }
        try {
            $teacher = Teacher::find($id);
            if (!$teacher) {
                return json(['code' => 404, 'message' => '教师不存在']);
            }
            $teacher->status = $data['status'];
            $teacher->verify_time = date('Y-m-d H:i:s');
            $teacher->verify_remark = $data['remark'] ?? '';
            $teacher->save();
            return json(['code' => 0, 'message' => '审核成功']);
        } catch (\Exception $e) {
            Log::error("审核教师失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '审核失败']);
        }
    }

    /**
     * 批量审核教师
     */
    public function batchVerify()
    {
        $data = $this->request->post();
        $validate = Validate::rule([
            'ids' => 'require|array',
            'status' => 'require|in:0,1',
            'remark' => 'length:0,500'
        ])->message([
            'ids.require' => '教师ID列表不能为空',
            'ids.array' => '教师ID列表格式不正确',
            'status.require' => '审核状态不能为空',
            'status.in' => '审核状态值不正确',
            'remark.length' => '备注长度不能超过500个字符'
        ]);
        if (!$validate->check($data)) {
            return json(['code' => 400, 'message' => $validate->getError()]);
        }
        try {
            $count = Teacher::whereIn('id', $data['ids'])
                ->update([
                    'status' => $data['status'],
                    'verify_time' => date('Y-m-d H:i:s'),
                    'verify_remark' => $data['remark'] ?? ''
                ]);
            return json(['code' => 0, 'message' => "批量审核成功，共处理{$count}条记录"]);
        } catch (\Exception $e) {
            Log::error("批量审核教师失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '批量审核失败']);
        }
    }

    /**
     * 更新教师状态
     */
    public function updateStatus($id)
    {
        $data = $this->request->post();
        $validate = Validate::rule([
            'status' => 'require|in:0,1'
        ])->message([
            'status.require' => '状态不能为空',
            'status.in' => '状态值不正确'
        ]);
        if (!$validate->check($data)) {
            return json(['code' => 400, 'message' => $validate->getError()]);
        }
        try {
            $teacher = Teacher::find($id);
            if (!$teacher) {
                return json(['code' => 404, 'message' => '教师不存在']);
            }
            $teacher->status = $data['status'];
            $teacher->save();
            return json(['code' => 0, 'message' => '状态更新成功']);
        } catch (\Exception $e) {
            Log::error("更新教师状态失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '状态更新失败']);
        }
    }

    /**
     * 获取待审核教师列表
     */
    public function pending()
    {
        $page = $this->request->param('page', 1);
        $limit = $this->request->param('limit', 20);
        $school_id = $this->request->param('school_id', '');

        try {
            $query = Teacher::with(['school', 'college'])->where('status', 0);
            if ($school_id) {
                $query->where('school_id', $school_id);
            }
            $list = $query->order('create_time', 'desc')
                ->paginate([
                    'list_rows' => $limit,
                    'page' => $page
                ]);
            return json([
                'code' => 0,
                'data' => [
                    'list' => $list->items(),
                    'total' => $list->total(),
                    'page' => $list->currentPage(),
                    'limit' => $list->listRows()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("获取待审核教师列表失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '获取待审核教师列表失败']);
        }
    }

    /**
     * 获取教师统计
     */
    public function stats()
    {
        try {
            $totalCount = Teacher::count();
            $bySchool = Teacher::field('school_id, COUNT(*) as count')->group('school_id')->select();
            $byStatus = Teacher::field('status, COUNT(*) as count')->group('status')->select();
            $byGender = Teacher::field('gender, COUNT(*) as count')->group('gender')->select();
            $pendingCount = Teacher::where('status', 0)->count();
            $activeCount = Teacher::where('status', 1)->count();
            return json([
                'code' => 0,
                'data' => [
                    'total' => $totalCount,
                    'by_school' => $bySchool,
                    'by_status' => $byStatus,
                    'by_gender' => $byGender,
                    'pending' => $pendingCount,
                    'active' => $activeCount
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("获取教师统计失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '获取教师统计失败']);
        }
    }
}
