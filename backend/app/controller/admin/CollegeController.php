<?php
declare (strict_types = 1);

namespace app\controller\admin;

use app\BaseController;
use app\model\College;
use app\model\School;
use think\facade\Validate;
use think\facade\Log;
use think\facade\View;

class CollegeController extends BaseController
{
    /**
     * 学院管理页面
     */
    public function index()
    {
        try {
            if ($this->request->isAjax()) {
                $page = $this->request->param('page', 1);
                $limit = $this->request->param('limit', 20);
                $school_id = $this->request->param('school_id', '');
                $keyword = $this->request->param('keyword', '');
                $status = $this->request->param('status', '');

                $query = College::with(['school']);

                if ($school_id) {
                    $query->where('school_id', $school_id);
                }
                if ($keyword) {
                    $query->where('name|code|short_name|description', 'like', "%{$keyword}%");
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

            return View::fetch('admin/college/index', [
                'schools' => $schools
            ]);
        } catch (\Exception $e) {
            Log::error("获取学院列表失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '获取学院列表失败']);
        }
    }

    /**
     * 获取学院详情
     */
    public function show($id)
    {
        try {
            $college = College::with(['school'])->find($id);
            if (!$college) {
                return json(['code' => 404, 'message' => '学院不存在']);
            }
            return json(['code' => 0, 'data' => $college]);
        } catch (\Exception $e) {
            Log::error("获取学院详情失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '获取学院详情失败']);
        }
    }
    
    /**
     * 添加学院页面
     */
    public function add()
    {
        // 获取学校列表
        $schools = School::where('status', 1)
            ->field('id,name,code,short_name')
            ->order('name', 'asc')
            ->select();
        
        View::assign('schools', $schools);
        return View::fetch('admin/college/add');
    }
    
    /**
     * 编辑学院页面
     */
    public function edit()
    {
        if ($this->request->isPost()) {
            // 处理POST请求 - 更新学院
            $data = $this->request->post();
            $id = $data['id'] ?? null;
            
            if (!$id) {
                return json(['code' => 400, 'message' => '学院ID不能为空']);
            }
            
            return $this->update($id);
        } else {
            // 处理GET请求 - 显示编辑页面
            $id = $this->request->param('id');
            $college = College::find($id);
            if (!$college) {
                $this->error('学院不存在');
            }
            
            // 获取学校列表
            $schools = School::where('status', 1)
                ->field('id,name,code,short_name')
                ->order('name', 'asc')
                ->select();
            
            View::assign([
                'college' => $college,
                'schools' => $schools
            ]);
            return View::fetch('admin/college/edit');
        }
    }
    
    /**
     * 学院详情页面
     */
    public function detail()
    {
        $id = $this->request->param('id');
        $college = College::with(['school'])->find($id);
        if (!$college) {
            $this->error('学院不存在');
        }
        
        // 添加学校名称到数据中
        $college->school_name = $college->school ? $college->school->name : '';
        
        // 添加统计信息
        $college->teacher_count = $college->teachers()->where('status', 1)->count();
        $college->course_count = 0; // 这里可以根据实际需求统计课程数量
        $college->active_course_count = 0; // 这里可以根据实际需求统计活跃课程数量
        
        View::assign('college', $college);
        return View::fetch('admin/college/detail');
    }

    /**
     * 创建学院
     */
    public function store()
    {
        $data = $this->request->post();
        $validate = Validate::rule([
            'school_id' => 'require|integer',
            'name' => 'require|length:2,100',
            'code' => 'require|length:2,50',
            'short_name' => 'length:0,50',
            'description' => 'length:0,500',
            'dean' => 'length:0,50',
            'phone' => 'mobile',
            'email' => 'email',
            'address' => 'length:0,255',
            'status' => 'in:0,1'
        ])->message([
            'school_id.require' => '学校ID不能为空',
            'school_id.integer' => '学校ID格式不正确',
            'name.require' => '学院名称不能为空',
            'name.length' => '学院名称长度必须在2-100个字符之间',
            'code.require' => '学院编码不能为空',
            'code.length' => '学院编码长度必须在2-50个字符之间',
            'short_name.length' => '学院简称长度不能超过50个字符',
            'description.length' => '学院描述长度不能超过500个字符',
            'dean.length' => '院长姓名长度不能超过50个字符',
            'phone.mobile' => '联系电话格式不正确',
            'email.email' => '邮箱格式不正确',
            'address.length' => '地址长度不能超过255个字符',
            'status.in' => '状态值不正确'
        ]);
        if (!$validate->check($data)) {
            return json(['code' => 400, 'message' => $validate->getError()]);
        }
        try {
            $college = new College();
            $college->school_id = $data['school_id'];
            $college->name = $data['name'];
            $college->code = $data['code'];
            $college->short_name = $data['short_name'] ?? '';
            $college->description = $data['description'] ?? '';
            $college->dean = $data['dean'] ?? '';
            $college->phone = $data['phone'] ?? '';
            $college->email = $data['email'] ?? '';
            $college->address = $data['address'] ?? '';
            $college->status = $data['status'] ?? 1;
            $college->save();
            return json(['code' => 0, 'message' => '学院创建成功', 'data' => $college]);
        } catch (\Exception $e) {
            Log::error("创建学院失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '学院创建失败']);
        }
    }

    /**
     * 更新学院
     */
    public function update($id)
    {
        $data = $this->request->post();
        try {
            $college = College::find($id);
            if (!$college) {
                return json(['code' => 404, 'message' => '学院不存在']);
            }
            $validate = Validate::rule([
                'name' => 'require|length:2,100',
                'code' => 'require|length:2,50',
                'short_name' => 'length:0,50',
                'description' => 'length:0,500',
                'dean' => 'length:0,50',
                'phone' => 'mobile',
                'email' => 'email',
                'address' => 'length:0,255',
                'status' => 'in:0,1'
            ])->message([
                'name.require' => '学院名称不能为空',
                'name.length' => '学院名称长度必须在2-100个字符之间',
                'code.require' => '学院编码不能为空',
                'code.length' => '学院编码长度必须在2-50个字符之间',
                'short_name.length' => '学院简称长度不能超过50个字符',
                'description.length' => '学院描述长度不能超过500个字符',
                'dean.length' => '院长姓名长度不能超过50个字符',
                'phone.mobile' => '联系电话格式不正确',
                'email.email' => '邮箱格式不正确',
                'address.length' => '地址长度不能超过255个字符',
                'status.in' => '状态值不正确'
            ]);
            if (!$validate->check($data)) {
                return json(['code' => 400, 'message' => $validate->getError()]);
            }
            $college->name = $data['name'];
            $college->code = $data['code'];
            $college->short_name = $data['short_name'] ?? '';
            $college->description = $data['description'] ?? '';
            $college->dean = $data['dean'] ?? '';
            $college->phone = $data['phone'] ?? '';
            $college->email = $data['email'] ?? '';
            $college->address = $data['address'] ?? '';
            $college->teacher_count = $data['teacher_count'] ?? 0;
            $college->student_count = $data['student_count'] ?? 0;
            $college->sort = $data['sort'] ?? 0;
            $college->status = $data['status'] ?? 1;
            $college->save();
            return json(['code' => 0, 'message' => '学院更新成功', 'data' => $college]);
        } catch (\Exception $e) {
            Log::error("更新学院失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '学院更新失败']);
        }
    }

    /**
     * 删除学院
     */
    public function destroy($id)
    {
        try {
            $college = College::find($id);
            if (!$college) {
                return json(['code' => 404, 'message' => '学院不存在']);
            }
            $college->delete();
            return json(['code' => 0, 'message' => '学院删除成功']);
        } catch (\Exception $e) {
            Log::error("删除学院失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '学院删除失败']);
        }
    }
    
    /**
     * 删除学院（页面方法）
     */
    public function delete()
    {
        $id = $this->request->param('id');
        return $this->destroy($id);
    }
    
    /**
     * 修改学院状态（页面方法）
     */
    public function changeStatus()
    {
        $id = $this->request->param('id');
        $status = $this->request->param('status');
        
        try {
            $college = College::find($id);
            if (!$college) {
                return json(['code' => 404, 'message' => '学院不存在']);
            }
            
            $college->status = $status;
            $college->save();
            return json(['code' => 0, 'message' => '状态修改成功']);
            
        } catch (\Exception $e) {
            Log::error("修改学院状态失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '状态修改失败']);
        }
    }
    
    /**
     * 获取学院列表（API）
     */
    public function getList()
    {
        try {
            $school_id = $this->request->param('school_id', '');
            
            $query = College::where('status', 1);
            if ($school_id) {
                $query->where('school_id', $school_id);
            }
            
            $colleges = $query->field('id,name,code,short_name')
                ->order('name', 'asc')
                ->select();
            
            return json([
                'code' => 0,
                'data' => [
                    'list' => $colleges
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("获取学院列表失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '获取学院列表失败']);
        }
    }
    
    /**
     * 获取学院详情（API）
     */
    public function getDetail()
    {
        $id = $this->request->param('id');
        return $this->show($id);
    }

    /**
     * 获取学院统计
     */
    public function stats()
    {
        try {
            $totalCount = College::count();
            $bySchool = College::field('school_id, COUNT(*) as count')->group('school_id')->select();
            $byStatus = College::field('status, COUNT(*) as count')->group('status')->select();
            return json([
                'code' => 0,
                'data' => [
                    'total' => $totalCount,
                    'by_school' => $bySchool,
                    'by_status' => $byStatus
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("获取学院统计失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '获取学院统计失败']);
        }
    }
} 