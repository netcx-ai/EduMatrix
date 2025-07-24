<?php
declare (strict_types = 1);

namespace app\controller\api\school;

use app\BaseController;
use app\model\College;
use app\model\School;
use app\model\Teacher;
use think\facade\Validate;
use think\facade\Log;

class CollegeController extends BaseController
{
    /**
     * 获取学院列表
     */
    public function index()
    {
        $user = $this->request->user;
        $page = $this->request->param('page', 1);
        $pageSize = $this->request->param('pageSize', 10);
        $name = $this->request->param('name', '');
        $status = $this->request->param('status', '');
        
        // 调试信息
        Log::info("用户信息: " . json_encode([
            'user_id' => $user->id ?? 'null',
            'school_id' => $user->school_id ?? 'null',
            'user_type' => $user->user_type ?? 'null',
            'name' => $user->name ?? 'null'
        ]));
        
        try {
            $query = College::where('school_id', $user->primary_school_id);
            
            // 调试信息：记录查询条件
            Log::info("查询条件: school_id = " . $user->primary_school_id);
            
            // 学院名称搜索
            if ($name) {
                $query->where('name', 'like', "%{$name}%");
                Log::info("添加名称搜索条件: " . $name);
            }
            
            // 状态筛选
            if ($status !== '') {
                $statusValue = $status === 'active' ? 1 : 0;
                $query->where('status', $statusValue);
                Log::info("添加状态筛选条件: " . $status . " -> " . $statusValue);
            }
            
            $list = $query->with(['school'])
                ->order('sort', 'asc')
                ->order('create_time', 'desc')
                ->paginate([
                    'list_rows' => $pageSize,
                    'page' => $page
                ]);
            
            // 调试信息：记录查询结果
            Log::info("查询结果数量: " . $list->total());
            
            // 处理数据格式
            $items = $list->items();
            foreach ($items as &$item) {
                $item['teacherCount'] = Teacher::where('college_id', $item['id'])->where('status', 1)->count();
                $item['courseCount'] = 0; // TODO: 添加课程统计
                $item['createdAt'] = date('Y-m-d H:i:s', strtotime($item['create_time']));
                $item['status'] = $item['status'] == 1 ? 'active' : 'inactive';
            }
            
            return json([
                'code' => 200,
                'data' => [
                    'list' => $items,
                    'total' => $list->total(),
                    'page' => $list->currentPage(),
                    'pageSize' => $list->listRows()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error("获取学院列表失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '获取学院列表失败']);
        }
    }
    
    /**
     * 获取学院详情（RESTful read方法）
     */
    public function read($id)
    {
        return $this->show($id);
    }
    
    /**
     * 获取学院详情
     */
    public function show($id)
    {
        $user = $this->request->user;
        
        // 添加调试信息
        Log::info("CollegeController::show 方法被调用，学院ID: {$id}");
        Log::info("用户信息: " . json_encode([
            'user_id' => $user->id ?? 'null',
            'primary_school_id' => $user->primary_school_id ?? 'null',
            'user_type' => $user->user_type ?? 'null'
        ]));
        
        try {
            $college = College::where('school_id', $user->primary_school_id)
                ->where('id', $id)
                ->with(['school'])
                ->find();
            
            if (!$college) {
                Log::warning("学院不存在，ID: {$id}, 学校ID: {$user->primary_school_id}");
                return json(['code' => 404, 'message' => '学院不存在']);
            }
            
            // 获取学院教师数量
            $teacherCount = Teacher::where('college_id', $college->id)->where('status', 1)->count();
            
            // 处理返回数据格式，便于前端编辑表单使用
            $collegeData = $college->toArray();
            $collegeData['teacherCount'] = $teacherCount;
            $collegeData['courseCount'] = 0; // TODO: 添加课程统计
            
            // 格式化状态字段，与列表页面保持一致
            $collegeData['status'] = $collegeData['status'] == 1 ? 'active' : 'inactive';
            
            // 格式化时间字段
            $collegeData['createdAt'] = date('Y-m-d H:i:s', strtotime($collegeData['create_time']));
            
            // 清理不需要的关联数据
            unset($collegeData['school']);
            
            Log::info("CollegeController::show 返回数据格式: " . json_encode([
                'type' => 'single_college',
                'has_list_field' => isset($collegeData['list']),
                'data_keys' => array_keys($collegeData),
                'status_formatted' => $collegeData['status']
            ]));
            
            return json([
                'code' => 200,
                'data' => $collegeData
            ]);
            
        } catch (\Exception $e) {
            Log::error("获取学院详情失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '获取学院详情失败']);
        }
    }
    
    /**
     * 创建学院
     */
    public function store()
    {
        $user = $this->request->user;
        $data = $this->request->post();
        
        // 验证参数
        $validate = Validate::rule([
            'name' => 'require|length:2,100',
            'code' => 'require|length:2,50',
            'short_name' => 'length:0,50',
            'description' => 'length:0,500',
            'dean' => 'length:0,50',
            'phone' => 'length:0,20',
            'email' => 'email',
            'address' => 'length:0,255',
            'sort' => 'integer|egt:0'
        ])->message([
            'name.require' => '学院名称不能为空',
            'name.length' => '学院名称长度必须在2-100个字符之间',
            'code.require' => '学院编码不能为空',
            'code.length' => '学院编码长度必须在2-50个字符之间',
            'short_name.length' => '学院简称长度不能超过50个字符',
            'description.length' => '学院描述长度不能超过500个字符',
            'dean.length' => '院长姓名长度不能超过50个字符',
            'phone.length' => '联系电话长度不能超过20个字符',
            'email.email' => '邮箱格式不正确',
            'address.length' => '学院地址长度不能超过255个字符',
            'sort.integer' => '排序必须是整数',
            'sort.egt' => '排序不能小于0'
        ]);
        
        if (!$validate->check($data)) {
            return json(['code' => 400, 'message' => $validate->getError()]);
        }
        
        try {
            // 检查学院编码是否已存在
            $existingCollege = College::where('school_id', $user->school_id)
                ->where('code', $data['code'])
                ->find();
            if ($existingCollege) {
                return json(['code' => 400, 'message' => '学院编码已存在']);
            }
            
            // 创建学院
            $college = new College();
            $college->school_id = $user->school_id;
            $college->name = $data['name'];
            $college->code = $data['code'];
            $college->short_name = $data['short_name'] ?? '';
            $college->description = $data['description'] ?? '';
            $college->dean = $data['dean'] ?? '';
            $college->phone = $data['phone'] ?? '';
            $college->email = $data['email'] ?? '';
            $college->address = $data['address'] ?? '';
            $college->sort = $data['sort'] ?? 0;
            $college->status = 1;
            
            $college->save();
            
            return json(['code' => 200, 'message' => '学院创建成功', 'data' => $college]);
            
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
        $user = $this->request->user;
        $data = $this->request->post();
        
        try {
            $college = College::where('school_id', $user->school_id)
                ->where('id', $id)
                ->find();
            
            if (!$college) {
                return json(['code' => 404, 'message' => '学院不存在']);
            }
            
            // 验证参数
            $validate = Validate::rule([
                'name' => 'require|length:2,100',
                'code' => 'require|length:2,50',
                'short_name' => 'length:0,50',
                'description' => 'length:0,500',
                'dean' => 'length:0,50',
                'phone' => 'length:0,20',
                'email' => 'email',
                'address' => 'length:0,255',
                'sort' => 'integer|egt:0',
                'status' => 'in:active,inactive'
            ])->message([
                'name.require' => '学院名称不能为空',
                'name.length' => '学院名称长度必须在2-100个字符之间',
                'code.require' => '学院编码不能为空',
                'code.length' => '学院编码长度必须在2-50个字符之间',
                'short_name.length' => '学院简称长度不能超过50个字符',
                'description.length' => '学院描述长度不能超过500个字符',
                'dean.length' => '院长姓名长度不能超过50个字符',
                'phone.length' => '联系电话长度不能超过20个字符',
                'email.email' => '邮箱格式不正确',
                'address.length' => '学院地址长度不能超过255个字符',
                'sort.integer' => '排序必须是整数',
                'sort.egt' => '排序不能小于0',
                'status.in' => '状态值必须是active或inactive'
            ]);
            
            if (!$validate->check($data)) {
                return json(['code' => 400, 'message' => $validate->getError()]);
            }
            
            // 检查学院编码是否已存在（排除当前学院）
            $existingCollege = College::where('school_id', $user->school_id)
                ->where('code', $data['code'])
                ->where('id', '<>', $id)
                ->find();
            if ($existingCollege) {
                return json(['code' => 400, 'message' => '学院编码已存在']);
            }
            
            // 更新学院信息
            $college->name = $data['name'];
            $college->code = $data['code'];
            $college->short_name = $data['short_name'] ?? '';
            $college->description = $data['description'] ?? '';
            $college->dean = $data['dean'] ?? '';
            $college->phone = $data['phone'] ?? '';
            $college->email = $data['email'] ?? '';
            $college->address = $data['address'] ?? '';
            $college->sort = $data['sort'] ?? 0;
            
            // 状态转换：前端发送'active'/'inactive'，数据库存储1/0
            if (isset($data['status'])) {
                $college->status = $data['status'] === 'active' ? 1 : 0;
            } else {
                $college->status = 1;
            }
            
            $college->save();
            
            return json(['code' => 200, 'message' => '学院更新成功', 'data' => $college]);
            
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
        $user = $this->request->user;
        
        try {
            $college = College::where('school_id', $user->school_id)
                ->where('id', $id)
                ->find();
            
            if (!$college) {
                return json(['code' => 404, 'message' => '学院不存在']);
            }
            
            // 检查是否有教师关联
            $teacherCount = Teacher::where('college_id', $college->id)->count();
            if ($teacherCount > 0) {
                return json(['code' => 400, 'message' => "该学院下还有 {$teacherCount} 名教师，无法删除"]);
            }
            
            // 删除学院
            $college->delete();
            
            return json(['code' => 200, 'message' => '学院删除成功']);
            
        } catch (\Exception $e) {
            Log::error("删除学院失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '学院删除失败']);
        }
    }
    
    /**
     * 获取学院下拉列表
     */
    public function list()
    {
        $user = $this->request->user;
        
        try {
            $list = College::where('school_id', $user->primary_school_id)
                ->where('status', 1)
                ->field('id,name,code,short_name')
                ->order('sort', 'asc')
                ->order('name', 'asc')
                ->select();
            
            return json(['code' => 200, 'data' => $list]);
            
        } catch (\Exception $e) {
            Log::error("获取学院列表失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '获取学院列表失败']);
        }
    }
    
    /**
     * 测试接口 - 检查数据库连接和数据
     */
    public function test()
    {
        try {
            $user = $this->request->user;
            
            // 获取所有学院（不限制学校）
            $allColleges = College::field('id,name,school_id,status')->select();
            
            // 获取当前用户学校的学院
            $userColleges = College::where('school_id', $user->primary_school_id)->select();
            
            return json([
                'code' => 200,
                'data' => [
                    'user_info' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'school_id' => $user->primary_school_id,
                        'user_type' => $user->user_type
                    ],
                    'all_colleges_count' => count($allColleges),
                    'all_colleges' => $allColleges,
                    'user_colleges_count' => count($userColleges),
                    'user_colleges' => $userColleges
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error("测试接口失败: " . $e->getMessage());
            return json([
                'code' => 500, 
                'message' => '测试失败: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
} 