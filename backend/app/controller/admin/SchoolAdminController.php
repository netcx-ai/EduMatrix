<?php
declare (strict_types = 1);

namespace app\controller\admin;

use app\BaseController;
use app\model\SchoolAdmin;
use app\model\School;
use think\facade\Validate;
use think\facade\Log;
use think\facade\View;
use think\Request;

class SchoolAdminController extends BaseController
{
    /**
     * 管理员管理页面
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            $page = $this->request->param('page', 1);
            $limit = $this->request->param('limit', 20);
            $school_id = $this->request->param('school_id', '');
            $keyword = $this->request->param('keyword', '');
            $role = $this->request->param('role', '');
            $status = $this->request->param('status', '');

            $query = SchoolAdmin::with(['school']);
            if ($school_id) {
                $query->where('school_id', $school_id);
            }
            if ($keyword) {
                $query->where('username|real_name|phone|email', 'like', "%{$keyword}%");
            }
            if ($role) {
                $query->where('role', $role);
            }
            if ($status !== '') {
                $query->where('status', $status);
            }
            $list = $query->order('create_time', 'desc')
                ->paginate([
                    'list_rows' => $limit,
                    'page' => $page
                ]);
            
            return json([
                'code' => 0,
                'msg' => '',
                'count' => $list->total(),
                'data' => $list->items()
            ]);
        }
        
        // 获取学校列表（用于下拉选择）
        $schools = School::where('status', 1)
            ->field('id,name,code,short_name')
            ->order('name', 'asc')
            ->select();

        return View::fetch('admin/school_admin/index', [
            'schools' => $schools
        ]);
    }

    /**
     * 添加管理员页面
     */
    public function add()
    {
        // 获取学校列表
        $schools = School::where('status', 1)
            ->field('id,name,code,short_name')
            ->order('name', 'asc')
            ->select();
            
        View::assign('schools', $schools);
        return View::fetch('admin/school_admin/add');
    }

    /**
     * 编辑管理员页面
     */
    public function edit()
    {
        $id = $this->request->param('id');
        $admin = SchoolAdmin::with(['school'])->find($id);
        if (!$admin) {
            $this->error('管理员不存在');
        }
        
        // 获取学校列表
        $schools = School::where('status', 1)
            ->field('id,name,code,short_name')
            ->order('name', 'asc')
            ->select();
            
        View::assign([
            'admin' => $admin,
            'schools' => $schools
        ]);
        return View::fetch('admin/school_admin/edit');
    }

    /**
     * 管理员详情页面
     */
    public function detail()
    {
        $id = $this->request->param('id');
        $admin = SchoolAdmin::with(['school'])->find($id);
        if (!$admin) {
            $this->error('管理员不存在');
        }
        View::assign('admin', $admin);
        return View::fetch('admin/school_admin/detail');
    }

    /**
     * 获取管理员详情（API）
     */
    public function getDetail()
    {
        $id = $this->request->param('id');
        try {
            $admin = SchoolAdmin::with(['school'])->find($id);
            if (!$admin) {
                return json(['code' => 404, 'message' => '管理员不存在']);
            }
            return json(['code' => 0, 'msg' => '', 'data' => $admin]);
        } catch (\Exception $e) {
            Log::error("获取管理员详情失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '获取管理员详情失败']);
        }
    }

    /**
     * 获取管理员详情
     */
    public function show($id)
    {
        try {
            $admin = SchoolAdmin::with(['school'])->find($id);
            if (!$admin) {
                return json(['code' => 404, 'message' => '管理员不存在']);
            }
            return json(['code' => 200, 'data' => $admin]);
        } catch (\Exception $e) {
            Log::error("获取管理员详情失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '获取管理员详情失败']);
        }
    }

    /**
     * 创建管理员
     */
    public function store()
    {
        $data = $this->request->post();
        $validate = Validate::rule([
            'school_id' => 'require|integer',
            'username' => 'require|length:3,50',
            'password' => 'require|length:6,50',
            'real_name' => 'require|length:2,50',
            'phone' => 'mobile',
            'email' => 'email',
            'role' => 'require|in:admin,dean,director',
            'department' => 'length:0,100',
            'position' => 'length:0,100'
        ])->message([
            'school_id.require' => '学校ID不能为空',
            'school_id.integer' => '学校ID格式不正确',
            'username.require' => '用户名不能为空',
            'username.length' => '用户名长度必须在3-50个字符之间',
            'password.require' => '密码不能为空',
            'password.length' => '密码长度必须在6-50个字符之间',
            'real_name.require' => '真实姓名不能为空',
            'real_name.length' => '真实姓名长度必须在2-50个字符之间',
            'phone.mobile' => '手机号格式不正确',
            'email.email' => '邮箱格式不正确',
            'role.require' => '角色不能为空',
            'role.in' => '角色值不正确',
            'department.length' => '部门名称长度不能超过100个字符',
            'position.length' => '职位名称长度不能超过100个字符'
        ]);
        if (!$validate->check($data)) {
            return json(['code' => 400, 'message' => $validate->getError()]);
        }
        try {
            if (SchoolAdmin::isUsernameExists($data['school_id'], $data['username'])) {
                return json(['code' => 400, 'message' => '用户名已存在']);
            }
            if (!empty($data['phone']) && SchoolAdmin::isPhoneExists($data['school_id'], $data['phone'])) {
                return json(['code' => 400, 'message' => '手机号已存在']);
            }
            if (!empty($data['email']) && SchoolAdmin::isEmailExists($data['school_id'], $data['email'])) {
                return json(['code' => 400, 'message' => '邮箱已存在']);
            }
            $admin = new SchoolAdmin();
            $admin->school_id = $data['school_id'];
            $admin->username = $data['username'];
            $admin->password = $data['password'];
            $admin->real_name = $data['real_name'];
            $admin->phone = $data['phone'] ?? '';
            $admin->email = $data['email'] ?? '';
            $admin->role = $data['role'];
            $admin->department = $data['department'] ?? '';
            $admin->position = $data['position'] ?? '';
            $admin->status = 1;
            $admin->save();
            return json(['code' => 200, 'message' => '管理员创建成功', 'data' => $admin]);
        } catch (\Exception $e) {
            Log::error("创建管理员失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '管理员创建失败']);
        }
    }

    /**
     * 更新管理员
     */
    public function update($id)
    {
        $data = $this->request->post();
        try {
            $admin = SchoolAdmin::find($id);
            if (!$admin) {
                return json(['code' => 404, 'message' => '管理员不存在']);
            }
            $validate = Validate::rule([
                'username' => 'require|length:3,50',
                'real_name' => 'require|length:2,50',
                'phone' => 'mobile',
                'email' => 'email',
                'role' => 'require|in:admin,dean,director',
                'department' => 'length:0,100',
                'position' => 'length:0,100',
                'status' => 'in:0,1'
            ])->message([
                'username.require' => '用户名不能为空',
                'username.length' => '用户名长度必须在3-50个字符之间',
                'real_name.require' => '真实姓名不能为空',
                'real_name.length' => '真实姓名长度必须在2-50个字符之间',
                'phone.mobile' => '手机号格式不正确',
                'email.email' => '邮箱格式不正确',
                'role.require' => '角色不能为空',
                'role.in' => '角色值不正确',
                'department.length' => '部门名称长度不能超过100个字符',
                'position.length' => '职位名称长度不能超过100个字符',
                'status.in' => '状态值不正确'
            ]);
            if (!$validate->check($data)) {
                return json(['code' => 400, 'message' => $validate->getError()]);
            }
            if (SchoolAdmin::isUsernameExists($admin->school_id, $data['username'], $id)) {
                return json(['code' => 400, 'message' => '用户名已存在']);
            }
            if (!empty($data['phone']) && SchoolAdmin::isPhoneExists($admin->school_id, $data['phone'], $id)) {
                return json(['code' => 400, 'message' => '手机号已存在']);
            }
            if (!empty($data['email']) && SchoolAdmin::isEmailExists($admin->school_id, $data['email'], $id)) {
                return json(['code' => 400, 'message' => '邮箱已存在']);
            }
            $admin->username = $data['username'];
            $admin->real_name = $data['real_name'];
            $admin->phone = $data['phone'] ?? '';
            $admin->email = $data['email'] ?? '';
            $admin->role = $data['role'];
            $admin->department = $data['department'] ?? '';
            $admin->position = $data['position'] ?? '';
            $admin->status = $data['status'] ?? 1;
            $admin->save();
            if (!empty($data['password'])) {
                $admin->password = $data['password'];
                $admin->save();
            }
            return json(['code' => 200, 'message' => '管理员更新成功', 'data' => $admin]);
        } catch (\Exception $e) {
            Log::error("更新管理员失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '管理员更新失败']);
        }
    }

    /**
     * 删除管理员
     */
    public function destroy($id)
    {
        try {
            $admin = SchoolAdmin::find($id);
            if (!$admin) {
                return json(['code' => 404, 'message' => '管理员不存在']);
            }
            if ($admin->role === 'admin' && $admin->username === 'admin') {
                return json(['code' => 400, 'message' => '不能删除超级管理员']);
            }
            $admin->delete();
            return json(['code' => 200, 'message' => '管理员删除成功']);
        } catch (\Exception $e) {
            Log::error("删除管理员失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '管理员删除失败']);
        }
    }

    /**
     * 更新管理员状态
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
            $admin = SchoolAdmin::find($id);
            if (!$admin) {
                return json(['code' => 404, 'message' => '管理员不存在']);
            }
            $admin->status = $data['status'];
            $admin->save();
            return json(['code' => 200, 'message' => '状态更新成功']);
        } catch (\Exception $e) {
            Log::error("更新管理员状态失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '状态更新失败']);
        }
    }

    /**
     * 重置管理员密码
     */
    public function resetPassword($id)
    {
        $data = $this->request->post();
        $validate = Validate::rule([
            'new_password' => 'require|length:6,50'
        ])->message([
            'new_password.require' => '新密码不能为空',
            'new_password.length' => '密码长度必须在6-50个字符之间'
        ]);
        if (!$validate->check($data)) {
            return json(['code' => 400, 'message' => $validate->getError()]);
        }
        try {
            $admin = SchoolAdmin::find($id);
            if (!$admin) {
                return json(['code' => 404, 'message' => '管理员不存在']);
            }
            $admin->password = $data['new_password'];
            $admin->save();
            return json(['code' => 200, 'message' => '密码重置成功']);
        } catch (\Exception $e) {
            Log::error("重置管理员密码失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '密码重置失败']);
        }
    }

    /**
     * 获取角色列表
     */
    public function roles()
    {
        $roles = [
            ['value' => 'admin', 'label' => '管理员'],
            ['value' => 'dean', 'label' => '院长'],
            ['value' => 'director', 'label' => '主任']
        ];
        return json(['code' => 200, 'data' => $roles]);
    }

    /**
     * 获取管理员统计
     */
    public function stats()
    {
        try {
            $totalCount = SchoolAdmin::count();
            $bySchool = SchoolAdmin::field('school_id, COUNT(*) as count')->group('school_id')->select();
            $byRole = SchoolAdmin::field('role, COUNT(*) as count')->group('role')->select();
            $byStatus = SchoolAdmin::field('status, COUNT(*) as count')->group('status')->select();
            return json([
                'code' => 200,
                'data' => [
                    'total' => $totalCount,
                    'by_school' => $bySchool,
                    'by_role' => $byRole,
                    'by_status' => $byStatus
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("获取管理员列表失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '获取管理员列表失败']);
        }
    }
} 