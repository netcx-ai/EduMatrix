<?php
declare (strict_types = 1);

namespace app\controller\admin;

use app\BaseController;
use app\model\School;
use app\model\College;
use app\model\SchoolAdmin;
use app\model\Teacher;
use think\facade\Validate;
use think\facade\Log;
use think\facade\Db;
use think\facade\View;
use think\Request;

class SchoolController extends BaseController
{
    /**
     * 学校管理页面
     */
    public function index()
    {
        try {
            if ($this->request->isAjax()) {
                $page = $this->request->param('page', 1);
                $limit = $this->request->param('limit', 20);
                $keyword = $this->request->param('keyword', '');
                $status = $this->request->param('status', '');
                $province = $this->request->param('province', '');
                $city = $this->request->param('city', '');
                $school_type = $this->request->param('school_type', '');
                
                $query = School::with(['colleges', 'admins', 'teachers']);
                
                // 关键词搜索
                if ($keyword) {
                    $query->where('name|code|short_name|description', 'like', "%{$keyword}%");
                }
                
                // 状态筛选
                if ($status !== '') {
                    $query->where('status', $status);
                }
                
                // 省份筛选
                if ($province) {
                    $query->where('province', $province);
                }
                
                // 城市筛选
                if ($city) {
                    $query->where('city', $city);
                }
                
                // 学校类型筛选
                if ($school_type !== '') {
                    $query->where('school_type', $school_type);
                }
                
                $list = $query->order('create_time', 'desc')
                    ->paginate([
                        'list_rows' => $limit,
                        'page' => $page
                    ]);
                
                // 处理数据
                $items = $list->items();
                foreach ($items as &$item) {
                    $item['college_count'] = count($item->colleges);
                    $item['admin_count'] = count($item->admins);
                    $item['teacher_count'] = count($item->teachers);
                    $item['is_expired'] = $item->isExpired();
                    unset($item['colleges'], $item['admins'], $item['teachers']);
                }
                
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

            View::assign('schools', $schools);
            return View::fetch('admin/school/index');
        } catch (\Exception $e) {
            Log::error("获取学校列表失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '获取学校列表失败']);
        }
    }
    
    /**
     * 获取学校详情
     */
    public function show($id)
    {
        try {
            $school = School::with(['colleges', 'admins', 'teachers'])
                ->find($id);
            
            if (!$school) {
                return json(['code' => 404, 'message' => '学校不存在']);
            }
            
            // 获取统计信息
            $stats = [
                'college_count' => count($school->colleges),
                'admin_count' => count($school->admins),
                'teacher_count' => count($school->teachers),
                'active_teacher_count' => count(array_filter($school->teachers, function($t) {
                    return $t->status == 1;
                })),
                'pending_teacher_count' => count(array_filter($school->teachers, function($t) {
                    return $t->status == 0;
                }))
            ];
            
            $school['stats'] = $stats;
            $school['is_expired'] = $school->isExpired();
            
            return json([
                'code' => 0,
                'data' => $school
            ]);
            
        } catch (\Exception $e) {
            Log::error("获取学校详情失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '获取学校详情失败']);
        }
    }
    
    /**
     * 添加学校页面
     */
    public function add()
    {
        return View::fetch('admin/school/add');
    }
    
    /**
     * 编辑学校页面
     */
    public function edit()
    {
        if ($this->request->isPost()) {
            // 处理POST请求 - 更新学校
            $data = $this->request->post();
            $id = $data['id'] ?? null;
            
            if (!$id) {
                return json(['code' => 400, 'message' => '学校ID不能为空']);
            }
            
            return $this->update($id);
        } else {
            // 处理GET请求 - 显示编辑页面
            $id = $this->request->param('id');
            $school = School::find($id);
            if (!$school) {
                $this->error('学校不存在');
            }
            
            // 获取省份列表
            $provinces = School::distinct(true)
                ->where('province', '<>', '')
                ->column('province');
            
            // 获取城市列表（基于当前学校的省份）
            $cities = [];
            if (!empty($school->province)) {
                $cities = School::distinct(true)
                    ->where('province', $school->province)
                    ->where('city', '<>', '')
                    ->column('city');
            }
            
            View::assign([
                'school' => $school,
                'provinces' => $provinces,
                'cities' => $cities
            ]);
            return View::fetch('admin/school/edit');
        }
    }
    
    /**
     * 学校详情页面
     */
    public function detail()
    {
        $id = $this->request->param('id');
        $school = School::with(['colleges', 'admins', 'teachers'])->find($id);
        if (!$school) {
            $this->error('学校不存在');
        }
        View::assign('school', $school);
        return View::fetch('admin/school/detail');
    }
    
    /**
     * 创建学校
     */
    public function store()
    {
        $data = $this->request->post();
        
        // 验证参数
        $validate = Validate::rule([
            'name' => 'require|length:2,100',
            'code' => 'require|length:2,50|unique:school,code',
            'short_name' => 'length:0,50',
            'description' => 'length:0,500',
            'phone' => 'mobile',
            'email' => 'email',
            'contact_person' => 'length:0,50',
            'contact_phone' => 'mobile',
            'province' => 'require|length:2,50',
            'city' => 'require|length:2,50',
            'district' => 'length:0,50',
            'school_type' => 'require|in:1,2,3,4,5',
            'max_teacher_count' => 'integer|egt:0',
            'max_student_count' => 'integer|egt:0',
            'expire_time' => 'date',
            'status' => 'in:0,1,2'
        ])->message([
            'name.require' => '学校名称不能为空',
            'name.length' => '学校名称长度必须在2-100个字符之间',
            'code.require' => '学校编码不能为空',
            'code.length' => '学校编码长度必须在2-50个字符之间',
            'code.unique' => '学校编码已存在',
            'short_name.length' => '学校简称长度不能超过50个字符',
            'description.length' => '学校描述长度不能超过500个字符',
            'phone.mobile' => '联系电话格式不正确',
            'email.email' => '邮箱格式不正确',
            'contact_person.length' => '联系人姓名长度不能超过50个字符',
            'contact_phone.mobile' => '联系人电话格式不正确',
            'province.require' => '省份不能为空',
            'province.length' => '省份长度必须在2-50个字符之间',
            'city.require' => '城市不能为空',
            'city.length' => '城市长度必须在2-50个字符之间',
            'district.length' => '区县长度不能超过50个字符',
            'school_type.require' => '学校类型不能为空',
            'school_type.in' => '学校类型值不正确',
            'max_teacher_count.integer' => '最大教师数量必须为整数',
            'max_teacher_count.egt' => '最大教师数量不能小于0',
            'max_student_count.integer' => '最大学生数量必须为整数',
            'max_student_count.egt' => '最大学生数量不能小于0',
            'expire_time.date' => '到期时间格式不正确',
            'status.in' => '状态值不正确'
        ]);
        
        if (!$validate->check($data)) {
            return json(['code' => 400, 'message' => $validate->getError()]);
        }
        
        try {
            // 创建学校
            $school = new School();
            $school->name = $data['name'];
            $school->code = $data['code'];
            $school->short_name = $data['short_name'] ?? '';
            $school->description = $data['description'] ?? '';
            $school->logo = $data['logo'] ?? '';
            $school->website = $data['website'] ?? '';
            $school->address = $data['address'] ?? '';
            $school->phone = $data['phone'] ?? '';
            $school->email = $data['email'] ?? '';
            $school->contact_person = $data['contact_person'] ?? '';
            $school->contact_phone = $data['contact_phone'] ?? '';
            $school->province = $data['province'];
            $school->city = $data['city'];
            $school->district = $data['district'] ?? '';
            $school->school_type = $data['school_type'];
            $school->max_teacher_count = $data['max_teacher_count'] ?? 0;
            $school->max_student_count = $data['max_student_count'] ?? 0;
            $school->expire_time = $data['expire_time'] ?? null;
            $school->status = $data['status'] ?? 1;
            
            $school->save();
            
            return json(['code' => 0, 'message' => '学校创建成功', 'data' => $school]);
            
        } catch (\Exception $e) {
            Log::error("创建学校失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '学校创建失败']);
        }
    }
    
    /**
     * 更新学校
     */
    public function update($id)
    {
        $data = $this->request->post();
        
        try {
            $school = School::find($id);
            
            if (!$school) {
                return json(['code' => 404, 'message' => '学校不存在']);
            }
            
            // 验证参数
            $validate = Validate::rule([
                'name' => 'require|length:2,100',
                'code' => 'require|length:2,50|unique:school,code,' . $id,
                'short_name' => 'length:0,50',
                'description' => 'length:0,500',
                'phone' => 'mobile',
                'email' => 'email',
                'contact_person' => 'length:0,50',
                'contact_phone' => 'mobile',
                'province' => 'require|length:2,50',
                'city' => 'require|length:2,50',
                'district' => 'length:0,50',
                'school_type' => 'require|in:1,2,3,4,5',
                'max_teacher_count' => 'integer|egt:0',
                'max_student_count' => 'integer|egt:0',
                'expire_time' => 'date',
                'status' => 'in:0,1,2'
            ])->message([
                'name.require' => '学校名称不能为空',
                'name.length' => '学校名称长度必须在2-100个字符之间',
                'code.require' => '学校编码不能为空',
                'code.length' => '学校编码长度必须在2-50个字符之间',
                'code.unique' => '学校编码已存在',
                'short_name.length' => '学校简称长度不能超过50个字符',
                'description.length' => '学校描述长度不能超过500个字符',
                'phone.mobile' => '联系电话格式不正确',
                'email.email' => '邮箱格式不正确',
                'contact_person.length' => '联系人姓名长度不能超过50个字符',
                'contact_phone.mobile' => '联系人电话格式不正确',
                'province.require' => '省份不能为空',
                'province.length' => '省份长度必须在2-50个字符之间',
                'city.require' => '城市不能为空',
                'city.length' => '城市长度必须在2-50个字符之间',
                'district.length' => '区县长度不能超过50个字符',
                'school_type.require' => '学校类型不能为空',
                'school_type.in' => '学校类型值不正确',
                'max_teacher_count.integer' => '最大教师数量必须为整数',
                'max_teacher_count.egt' => '最大教师数量不能小于0',
                'max_student_count.integer' => '最大学生数量必须为整数',
                'max_student_count.egt' => '最大学生数量不能小于0',
                'expire_time.date' => '到期时间格式不正确',
                'status.in' => '状态值不正确'
            ]);
            
            if (!$validate->check($data)) {
                return json(['code' => 400, 'message' => $validate->getError()]);
            }
            
            // 更新学校信息
            $school->name = $data['name'];
            $school->code = $data['code'];
            $school->short_name = $data['short_name'] ?? '';
            $school->description = $data['description'] ?? '';
            $school->logo = $data['logo'] ?? '';
            $school->website = $data['website'] ?? '';
            $school->address = $data['address'] ?? '';
            $school->phone = $data['phone'] ?? '';
            $school->email = $data['email'] ?? '';
            $school->contact_person = $data['contact_person'] ?? '';
            $school->contact_phone = $data['contact_phone'] ?? '';
            $school->province = $data['province'];
            $school->city = $data['city'];
            $school->district = $data['district'] ?? '';
            $school->school_type = $data['school_type'];
            $school->student_count = $data['student_count'] ?? 0;
            $school->teacher_count = $data['teacher_count'] ?? 0;
            $school->max_teacher_count = $data['max_teacher_count'] ?? 0;
            $school->max_student_count = $data['max_student_count'] ?? 0;
            $school->expire_time = $data['expire_time'] ?? null;
            $school->status = $data['status'] ?? 1;
            
            $school->save();
            
            return json(['code' => 0, 'message' => '学校更新成功', 'data' => $school]);
            
        } catch (\Exception $e) {
            Log::error("更新学校失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '学校更新失败']);
        }
    }
    
    /**
     * 删除学校
     */
    public function destroy($id)
    {
        try {
            $school = School::find($id);
            
            if (!$school) {
                return json(['code' => 404, 'message' => '学校不存在']);
            }
            
            // 检查是否有学院
            $collegeCount = College::where('school_id', $id)->count();
            if ($collegeCount > 0) {
                return json(['code' => 400, 'message' => '该学校下还有学院，无法删除']);
            }
            
            // 检查是否有管理员
            $adminCount = SchoolAdmin::where('school_id', $id)->count();
            if ($adminCount > 0) {
                return json(['code' => 400, 'message' => '该学校下还有管理员，无法删除']);
            }
            
            // 检查是否有教师
            $teacherCount = Teacher::where('school_id', $id)->count();
            if ($teacherCount > 0) {
                return json(['code' => 400, 'message' => '该学校下还有教师，无法删除']);
            }
            
            // 删除学校
            $school->delete();
            
            return json(['code' => 0, 'message' => '学校删除成功']);
            
        } catch (\Exception $e) {
            Log::error("删除学校失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '学校删除失败']);
        }
    }
    
    /**
     * 更新学校状态
     */
    public function updateStatus($id)
    {
        $data = $this->request->post();
        
        // 验证参数
        $validate = Validate::rule([
            'status' => 'require|in:0,1,2'
        ])->message([
            'status.require' => '状态不能为空',
            'status.in' => '状态值不正确'
        ]);
        
        if (!$validate->check($data)) {
            return json(['code' => 400, 'message' => $validate->getError()]);
        }
        
        try {
            $school = School::find($id);
            
            if (!$school) {
                return json(['code' => 404, 'message' => '学校不存在']);
            }
            
            $school->status = $data['status'];
            $school->save();
            
            return json(['code' => 0, 'message' => '状态更新成功']);
            
        } catch (\Exception $e) {
            Log::error("更新学校状态失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '状态更新失败']);
        }
    }
    
    /**
     * 删除学校（页面方法）
     */
    public function delete()
    {
        $id = $this->request->param('id');
        return $this->destroy($id);
    }
    
    /**
     * 修改学校状态（页面方法）
     */
    public function changeStatus()
    {
        $id = $this->request->param('id');
        $status = $this->request->param('status');
        
        try {
            $school = School::find($id);
            if (!$school) {
                return json(['code' => 404, 'message' => '学校不存在']);
            }
            
            $school->status = $status;
            $school->save();
            return json(['code' => 0, 'message' => '状态修改成功']);
            
        } catch (\Exception $e) {
            Log::error("修改学校状态失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '状态修改失败']);
        }
    }
    
    /**
     * 获取学校列表（API）
     */
    public function getList()
    {
        try {
            $schools = School::where('status', 1)
                ->field('id,name,code,short_name')
                ->order('name', 'asc')
                ->select();
            $list = [];
            foreach ($schools as $item) {
                $list[] = [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'code' => $item['code'],
                    'short_name' => $item['short_name'] ?? ''
                ];
            }
            return json([
                'code' => 200,
                'data' => [
                    'list' => $list
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("获取学校列表失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '获取学校列表失败']);
        }
    }
    
    /**
     * 获取学校详情（API）
     */
    public function getDetail()
    {
        $id = $this->request->param('id');
        
        try {
            $school = School::find($id);
            
            if (!$school) {
                return json(['code' => 404, 'message' => '学校不存在']);
            }
            
            return json([
                'code' => 0,
                'data' => $school
            ]);
            
        } catch (\Exception $e) {
            Log::error("获取学校详情失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '获取学校详情失败']);
        }
    }
    
    /**
     * 简单测试方法
     */
    public function test()
    {
        return json([
            'code' => 1,
            'message' => 'success',
            'data' => 'SchoolController test method works!'
        ]);
    }
    
    /**
     * 获取所有学校列表（API）
     */
    public function getAll()
    {
        try {
            $schools = School::where('status', 1)
                ->field('id,name,code,short_name')
                ->order('name', 'asc')
                ->select();
            
            return json([
                'code' => 1,
                'data' => $schools
            ]);
        } catch (\Exception $e) {
            Log::error("获取所有学校列表失败: " . $e->getMessage());
            return json(['code' => 0, 'message' => '获取学校列表失败']);
        }
    }
    
    /**
     * 获取学校类型列表
     */
    public function types()
    {
        $types = [
            ['value' => 1, 'label' => '小学'],
            ['value' => 2, 'label' => '初中'],
            ['value' => 3, 'label' => '高中'],
            ['value' => 4, 'label' => '大学'],
            ['value' => 5, 'label' => '其他']
        ];
        
        return json(['code' => 0, 'data' => $types]);
    }
    
    /**
     * 获取省份列表
     */
    public function provinces()
    {
        $provinces = School::distinct(true)
            ->where('province', '<>', '')
            ->column('province');
        
        return json(['code' => 0, 'data' => $provinces]);
    }
    
    /**
     * 获取城市列表
     */
    public function cities()
    {
        $province = $this->request->param('province', '');
        
        $query = School::distinct(true)->where('city', '<>', '');
        if ($province) {
            $query->where('province', $province);
        }
        
        $cities = $query->column('city');
        
        return json(['code' => 0, 'data' => $cities]);
    }
    
    /**
     * 获取学校统计
     */
    public function stats()
    {
        try {
            // 总学校数量
            $totalCount = School::count();
            
            // 按状态统计
            $statusStats = School::field('status, COUNT(*) as count')
                ->group('status')
                ->select();
            
            // 按类型统计
            $typeStats = School::field('school_type, COUNT(*) as count')
                ->group('school_type')
                ->select();
            
            // 按省份统计
            $provinceStats = School::field('province, COUNT(*) as count')
                ->group('province')
                ->order('count', 'desc')
                ->limit(10)
                ->select();
            
            // 即将到期的学校
            $expiringCount = School::where('expire_time', '>', date('Y-m-d'))
                ->where('expire_time', '<', date('Y-m-d', strtotime('+30 days')))
                ->count();
            
            // 已过期的学校
            $expiredCount = School::where('expire_time', '<', date('Y-m-d'))
                ->count();
            
            return json([
                'code' => 0,
                'data' => [
                    'total' => $totalCount,
                    'by_status' => $statusStats,
                    'by_type' => $typeStats,
                    'by_province' => $provinceStats,
                    'expiring' => $expiringCount,
                    'expired' => $expiredCount
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error("获取学校统计失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '获取学校统计失败']);
        }
    }
}
