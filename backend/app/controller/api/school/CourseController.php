<?php
declare (strict_types = 1);

namespace app\controller\api\school;

use app\BaseController;
use app\model\Course;
use app\model\College;
use app\model\Teacher;
use think\facade\Validate;
use think\facade\Log;

class CourseController extends BaseController
{
    /**
     * 获取课程列表
     */
    public function index()
    {
        $user = $this->request->user;
        $page = $this->request->param('page', 1);
        $pageSize = $this->request->param('pageSize', 10);
        $name = $this->request->param('name', '');
        $status = $this->request->param('status', '');
        $collegeId = $this->request->param('college_id', '');
        
        
        try {
            $query = Course::alias('c')
                ->join('college co', 'c.college_id = co.id')
                ->join('teacher t', 'c.responsible_teacher_id = t.id')
                ->where('co.school_id', $user->primary_school_id);
            
            // 课程名称搜索
            if ($name) {
                $query->where('c.course_name', 'like', "%{$name}%");
            }
            
            // 状态筛选
            if ($status !== '') {
                $statusValue = $status === 'active' ? 1 : 0;
                $query->where('c.status', $statusValue);
            }

            // 学院筛选
            if ($collegeId) {
                $query->where('c.college_id', $collegeId);
            }
            
            $list = $query->field('c.*, co.name as college_name, t.real_name as teacher_name')
                ->order('c.create_time', 'desc')
                ->paginate([
                    'list_rows' => $pageSize,
                    'page' => $page
                ]);
            
            // 处理数据格式
            $items = $list->items();
            foreach ($items as &$item) {
                // 创建新的数据结构，只包含前端需要的字段
                $newItem = [
                    'id' => $item['id'],
                    'name' => $item['name'], // 数据库字段就是 name
                    'code' => $item['course_code'],
                    'description' => $item['description'],
                    'teacherName' => $item['teacher_name'],
                    'status' => $item['status'] == 1 ? 'active' : 'inactive',
                    'createdAt' => date('Y-m-d H:i:s', strtotime($item['create_time'])),
                    'college_id' => $item['college_id'],
                    'college_name' => $item['college_name'],
                    'credits' => $item['credits'],
                    'hours' => $item['hours'],
                    'semester' => $item['semester'],
                    'academic_year' => $item['academic_year'],
                    'is_public' => $item['is_public'],
                    'sort' => $item['sort'],
                    'view_count' => $item['view_count'],
                    'create_count' => $item['create_count']
                ];
                
                // 替换原数组
                $item = $newItem;
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
            Log::error("获取课程列表失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '获取课程列表失败']);
        }
    }
    
    /**
     * 获取课程详情
     */
    public function show($id)
    {
        $user = $this->request->user;
        
        try {
            $course = Course::alias('c')
                ->join('college co', 'c.college_id = co.id')
                ->join('teacher t', 'c.responsible_teacher_id = t.id')
                ->where('co.school_id', $user->primary_school_id)
                ->where('c.id', $id)
                ->field('c.*, co.name as college_name, t.real_name as teacher_name')
                ->find();
            
            if (!$course) {
                return json(['code' => 404, 'message' => '课程不存在']);
            }
            
            // 处理数据格式，与列表接口保持一致
            $processedCourse = [
                'id' => $course['id'],
                'name' => $course['name'],
                'code' => $course['course_code'],
                'description' => $course['description'],
                'teacherName' => $course['teacher_name'],
                'status' => $course['status'] == 1 ? 'active' : 'inactive',
                'createdAt' => date('Y-m-d H:i:s', strtotime($course['create_time'])),
                'college_id' => $course['college_id'],
                'college_name' => $course['college_name'],
                'credits' => $course['credits'],
                'hours' => $course['hours'],
                'semester' => $course['semester'],
                'academic_year' => $course['academic_year'],
                'is_public' => $course['is_public'],
                'sort' => $course['sort'],
                'view_count' => $course['view_count'],
                'create_count' => $course['create_count']
            ];
            
            return json(['code' => 200, 'data' => $processedCourse]);
            
        } catch (\Exception $e) {
            Log::error("获取课程详情失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '获取课程详情失败']);
        }
    }
    
    /**
     * 创建课程
     */
    public function store()
    {
        $user = $this->request->user;
        $data = $this->request->post();
        
        // 验证参数
        $validate = Validate::rule([
            'name' => 'require|length:2,100',
            'code' => 'require|length:2,50',
            'college_id' => 'require|integer',
            'teacher_id' => 'require|integer',
            'description' => 'length:0,500',
            'credit' => 'float|egt:0',
            'hours' => 'integer|egt:0'
        ])->message([
            'name.require' => '课程名称不能为空',
            'name.length' => '课程名称长度必须在2-100个字符之间',
            'code.require' => '课程编码不能为空',
            'code.length' => '课程编码长度必须在2-50个字符之间',
            'college_id.require' => '请选择学院',
            'college_id.integer' => '学院ID必须是整数',
            'teacher_id.require' => '请选择教师',
            'teacher_id.integer' => '教师ID必须是整数',
            'description.length' => '课程描述长度不能超过500个字符',
            'credit.float' => '学分必须是数字',
            'credit.egt' => '学分不能小于0',
            'hours.integer' => '学时必须是整数',
            'hours.egt' => '学时不能小于0'
        ]);
        
        if (!$validate->check($data)) {
            return json(['code' => 400, 'message' => $validate->getError()]);
        }
        
        try {
            // 验证学院是否属于当前学校
            $college = College::where('id', $data['college_id'])
                ->where('school_id', $user->primary_school_id)
                ->find();
            if (!$college) {
                return json(['code' => 400, 'message' => '学院不存在']);
            }
            
            // 验证教师是否属于当前学校
            $teacher = Teacher::alias('t')
                ->join('college c', 't.college_id = c.id')
                ->where('t.id', $data['teacher_id'])
                ->where('c.school_id', $user->primary_school_id)
                ->find();
            if (!$teacher) {
                return json(['code' => 400, 'message' => '教师不存在']);
            }
            
            // 创建课程
            $course = new Course();
            $course->course_name = $data['name'];
            $course->course_code = $data['code'];
            $course->college_id = $data['college_id'];
            $course->responsible_teacher_id = $data['teacher_id'];
            $course->description = $data['description'] ?? '';
            $course->credits = $data['credit'] ?? 0;
            $course->hours = $data['hours'] ?? 0;
            $course->status = 1;
            
            $course->save();
            
            return json(['code' => 200, 'message' => '课程创建成功', 'data' => $course]);
            
        } catch (\Exception $e) {
            Log::error("创建课程失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '课程创建失败']);
        }
    }
    
    /**
     * 更新课程
     */
    public function update($id)
    {
        $user = $this->request->user;
        $data = $this->request->post();
        try {
            // 验证课程是否属于当前学校
            $course = Course::alias('c')
                ->join('college co', 'c.college_id = co.id')
                ->where('co.school_id', $user->primary_school_id)
                ->where('c.id', $id)
                ->find();
            if (!$course) {
                return json(['code' => 404, 'message' => '课程不存在']);
            }
            $updateData = [];
            // 只切换状态
            if (isset($data['status']) && count($data) === 1) {
                $statusValue = $data['status'] === 'active' ? 1 : 0;
                $updateData['status'] = $statusValue;
            } else {
                // 部分字段更新
                $fields = [
                    'name' => ['require|length:2,100', '课程名称不能为空|课程名称长度必须在2-100个字符之间'],
                    'code' => ['require|length:2,50', '课程编码不能为空|课程编码长度必须在2-50个字符之间'],
                    'college_id' => ['require|integer', '请选择学院|学院ID必须是整数'],
                    'teacher_id' => ['require|integer', '请选择教师|教师ID必须是整数'],
                    'description' => ['length:0,500', '课程描述长度不能超过500个字符'],
                    'credit' => ['float|egt:0', '学分必须是数字|学分不能小于0'],
                    'hours' => ['integer|egt:0', '学时必须是整数|学时不能小于0'],
                    'status' => ['in:0,1', '状态值不合法']
                ];
                foreach ($fields as $key => [$rule, $msg]) {
                    if (array_key_exists($key, $data)) {
                        $validate = Validate::rule([$key => $rule])->message(explode('|', $msg));
                        if (!$validate->check([$key => $data[$key]])) {
                            return json(['code' => 400, 'message' => $validate->getError()]);
                        }
                        $updateData[$key === 'code' ? 'course_code' : ($key === 'credit' ? 'credits' : ($key === 'teacher_id' ? 'responsible_teacher_id' : $key))] = $data[$key];
                    }
                }
                // 额外校验学院和教师是否属于当前学校
                if (isset($data['college_id'])) {
                    $college = College::where('id', $data['college_id'])
                        ->where('school_id', $user->primary_school_id)
                        ->find();
                    if (!$college) {
                        return json(['code' => 400, 'message' => '学院不存在']);
                    }
                }
                if (isset($data['teacher_id'])) {
                    $teacher = Teacher::alias('t')
                        ->join('college c', 't.college_id = c.id')
                        ->where('t.id', $data['teacher_id'])
                        ->where('c.school_id', $user->primary_school_id)
                        ->find();
                    if (!$teacher) {
                        return json(['code' => 400, 'message' => '教师不存在']);
                    }
                }
            }
            $updateData['update_time'] = time();
            Course::where('id', $id)->update($updateData);
            return json(['code' => 200, 'message' => '课程更新成功']);
        } catch (\Exception $e) {
            Log::error("更新课程失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '课程更新失败']);
        }
    }
    
    /**
     * 删除课程
     */
    public function destroy($id)
    {
        $user = $this->request->user;
        
        try {
            // 验证课程是否属于当前学校
            $course = Course::alias('c')
                ->join('college co', 'c.college_id = co.id')
                ->where('co.school_id', $user->primary_school_id)
                ->where('c.id', $id)
                ->find();
            
            if (!$course) {
                return json(['code' => 404, 'message' => '课程不存在']);
            }
            
            // 删除课程
            Course::where('id', $id)->delete();
            
            return json(['code' => 200, 'message' => '课程删除成功']);
            
        } catch (\Exception $e) {
            Log::error("删除课程失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '课程删除失败']);
        }
    }
} 