<?php
declare (strict_types = 1);

namespace app\controller\admin;

use app\BaseController;
use app\model\Course;
use app\model\School;
use app\model\College;
use app\model\Teacher;
use think\facade\Validate;
use think\facade\Log;
use think\facade\View;

class CourseController extends BaseController
{
    /**
     * 课程管理页面
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

                $query = Course::with(['school', 'college', 'responsibleTeacher', 'tags']);
                if ($school_id) {
                    $query->where('school_id', $school_id);
                }
                if ($college_id) {
                    $query->where('college_id', $college_id);
                }
                if ($keyword) {
                    $query->where('name|description', 'like', "%{$keyword}%");
                }
                if ($status !== '') {
                    $query->where('status', $status);
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

            return View::fetch('admin/course/index', [
                'schools' => $schools
            ]);
        } catch (\Exception $e) {
            Log::error("获取课程列表失败: " . $e->getMessage());
            return $this->error('获取课程列表失败', 500);
        }
    }

    /**
     * 课程标签管理
     */
    public function tag()
    {
        try {
            if ($this->request->isAjax()) {
                $page = (int)$this->request->param('page', 1);
                $limit = (int)$this->request->param('limit', 10);
                $keyword = $this->request->param('keyword', '');
                $status = $this->request->param('status', '');

                $query = \app\model\CourseTag::withCount('courses');

                // 关键词搜索
                if (!empty($keyword)) {
                    $query->where('name|description', 'like', "%{$keyword}%");
                }

                // 状态筛选
                if ($status !== '' && $status !== null) {
                    $query->where('status', (int)$status);
                }

                $countQuery = clone $query;
                $total = $countQuery->count();

                $list = $query->order('sort', 'asc')
                    ->order('id', 'asc')
                    ->page($page, $limit)
                    ->select()
                    ->toArray();

                // 处理数据格式
                foreach ($list as &$item) {
                    $item['status_name'] = (int)$item['status'] === 1 ? '启用' : '禁用';
                }

                return json([
                    'code' => 0,
                    'msg' => '',
                    'count' => $total,
                    'data' => $list
                ]);
            }

            return View::fetch('admin/course/tag');
            
        } catch (\Exception $e) {
            if ($this->request->isAjax()) {
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
     * 添加课程标签
     */
    public function addTag()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            
            try {
                validate([
                    'name' => 'require|max:50',
                    'color' => 'require|max:20',
                    'status' => 'require|in:0,1',
                ])->check($data);
            } catch (\Exception $e) {
                return json(['code' => 1, 'msg' => $e->getMessage()]);
            }
            
            // 检查标签名是否已存在
            $exists = \app\model\CourseTag::where('name', $data['name'])->find();
            if ($exists) {
                return json(['code' => 1, 'msg' => '标签名已存在']);
            }
            
            $tag = new \app\model\CourseTag;
            $tag->name = $data['name'];
            $tag->description = $data['description'] ?? '';
            $tag->color = $data['color'];
            $tag->status = $data['status'];
            $tag->sort = $data['sort'] ?? 0;
            $tag->save();
            
            return json(['code' => 0, 'msg' => '添加成功']);
        }
        
        return View::fetch('admin/course/add_tag');
    }

    /**
     * 编辑课程标签
     */
    public function editTag()
    {
        $id = $this->request->param('id/d');
        $tag = \app\model\CourseTag::find($id);
        
        if (!$tag) {
            return $this->error('标签不存在');
        }
        
        if ($this->request->isPost()) {
            $data = $this->request->post();
            
            try {
                validate([
                    'name' => 'require|max:50',
                    'color' => 'require|max:20',
                    'status' => 'require|in:0,1',
                ])->check($data);
            } catch (\Exception $e) {
                return json(['code' => 1, 'msg' => $e->getMessage()]);
            }
            
            // 检查标签名是否已存在（排除自己）
            $exists = \app\model\CourseTag::where('name', $data['name'])->where('id', '<>', $id)->find();
            if ($exists) {
                return json(['code' => 1, 'msg' => '标签名已存在']);
            }
            
            $tag->name = $data['name'];
            $tag->description = $data['description'] ?? '';
            $tag->color = $data['color'];
            $tag->status = $data['status'];
            $tag->sort = $data['sort'] ?? 0;
            $tag->save();
            
            return json(['code' => 0, 'msg' => '更新成功']);
        }
        
        return View::fetch('admin/course/edit_tag', ['tag' => $tag]);
    }

    /**
     * 删除课程标签
     */
    public function deleteTag()
    {
        $id = $this->request->param('id/d');
        $tag = \app\model\CourseTag::find($id);
        
        if (!$tag) {
            return json(['code' => 1, 'msg' => '标签不存在']);
        }
        
        // 检查是否有关联的课程
        $courseCount = $tag->courses()->count();
        if ($courseCount > 0) {
            return json(['code' => 1, 'msg' => "该标签下还有 {$courseCount} 个课程，无法删除"]);
        }
        
        $tag->delete();
        return json(['code' => 0, 'msg' => '删除成功']);
    }

    /**
     * 获取课程标签列表（用于下拉选择）
     */
    public function getTagList()
    {
        try {
            $tags = \app\model\CourseTag::where('status', 1)
                ->order('sort', 'asc')
                ->order('id', 'asc')
                ->select()
                ->toArray();
            
            return json([
                'code' => 0,
                'msg' => '',
                'data' => $tags
            ]);
        } catch (\Exception $e) {
            return json([
                'code' => 1,
                'msg' => '获取标签列表失败：' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    /**
     * 课程标签管理页面
     */
    public function tags()
    {
        $id = $this->request->param('id');
        $course = Course::with(['tags'])->find($id);
        if (!$course) {
            $this->error('课程不存在');
        }
        
        // 获取所有可用标签
        $allTags = \app\model\CourseTag::where('status', 1)
            ->order('sort', 'asc')
            ->order('id', 'asc')
            ->select();
            
        View::assign([
            'course' => $course,
            'allTags' => $allTags
        ]);
        return View::fetch('admin/course/tags');
    }
    
    /**
     * 更新课程标签
     */
    public function updateTags()
    {
        $courseId = $this->request->param('course_id');
        $tagIds = $this->request->param('tag_ids/a', []);
        
        try {
            $course = Course::find($courseId);
            if (!$course) {
                return json(['code' => 1, 'msg' => '课程不存在']);
            }
            
            // 更新标签关联
            $course->tags()->detach(); // 清除现有关联
            if (!empty($tagIds)) {
                $course->tags()->attach($tagIds); // 添加新关联
            }
            
            return json(['code' => 0, 'msg' => '标签更新成功']);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '标签更新失败：' . $e->getMessage()]);
        }
    }

    /**
     * 获取课程详情
     */
    public function show($id)
    {
        try {
            $course = Course::with(['school', 'college', 'responsibleTeacher'])->find($id);
            if (!$course) {
                return $this->error('课程不存在', 404);
            }
            return $this->success($course);
        } catch (\Exception $e) {
            Log::error("获取课程详情失败: " . $e->getMessage());
            return $this->error('获取课程详情失败', 500);
        }
    }
    
    /**
     * 添加课程页面
     */
    public function add()
    {
        return View::fetch('admin/course/add');
    }
    
    /**
     * 编辑课程页面
     */
    public function edit()
    {
        $id = $this->request->param('id');
        $course = Course::with(['school', 'college', 'responsibleTeacher'])->find($id);
        if (!$course) {
            $this->error('课程不存在');
        }
        
        // 获取学校列表
        $schools = School::where('status', 1)
            ->field('id,name,code,short_name')
            ->order('name', 'asc')
            ->select();
            
        // 获取学院列表
        $colleges = [];
        if ($course->school_id) {
            $colleges = College::where('school_id', $course->school_id)
                ->where('status', 1)
                ->field('id,name')
                ->order('name', 'asc')
                ->select();
        }
            
        // 获取教师列表
        $teachers = [];
        if ($course->school_id) {
            $teachers = Teacher::where('school_id', $course->school_id)
                ->where('status', 1)
                ->field('id,real_name,teacher_no')
                ->order('real_name', 'asc')
                ->select();
        }
        
        View::assign([
            'course' => $course,
            'schools' => $schools,
            'colleges' => $colleges,
            'teachers' => $teachers
        ]);
        return View::fetch('admin/course/edit');
    }
    
    /**
     * 课程详情页面
     */
    public function detail()
    {
        $id = $this->request->param('id');
        $course = Course::with(['school', 'college', 'responsibleTeacher'])->find($id);
        if (!$course) {
            $this->error('课程不存在');
        }
        View::assign('course', $course);
        return View::fetch('admin/course/detail');
    }

    /**
     * 创建课程
     */
    public function store()
    {
        $data = $this->request->post();
        $validate = Validate::rule([
            'school_id' => 'require|integer',
            'name' => 'require|length:2,100',
            'description' => 'length:0,500',
            'course_code' => 'length:0,50',
            'college_id' => 'integer',
            'credits' => 'float',
            'hours' => 'integer',
            'semester' => 'length:0,20',
            'academic_year' => 'length:0,20',
            'responsible_teacher_id' => 'integer',
            'status' => 'in:0,1'
        ])->message([
            'school_id.require' => '学校ID不能为空',
            'school_id.integer' => '学校ID格式不正确',
            'name.require' => '课程名称不能为空',
            'name.length' => '课程名称长度必须在2-100个字符之间',
            'description.length' => '课程描述长度不能超过500个字符',
            'course_code.length' => '课程代码长度不能超过50个字符',
            'college_id.integer' => '学院ID格式不正确',
            'credits.float' => '学分格式不正确',
            'hours.integer' => '学时格式不正确',
            'semester.length' => '学期长度不能超过20个字符',
            'academic_year.length' => '学年长度不能超过20个字符',
            'responsible_teacher_id.integer' => '负责教师ID格式不正确',
            'status.in' => '状态值不正确'
        ]);
        if (!$validate->check($data)) {
            return $this->error($validate->getError(), 400);
        }
        try {
            if (Course::isNameExists($data['school_id'], $data['name'])) {
                return $this->error('课程名称已存在', 400);
            }
            
            // 检查课程代码是否重复
            if (!empty($data['course_code']) && Course::isCodeExists($data['school_id'], $data['course_code'])) {
                return $this->error('课程代码已存在', 400);
            }
            
            $course = new Course();
            $course->school_id = $data['school_id'];
            $course->name = $data['name'];
            $course->description = $data['description'] ?? '';
            $course->course_code = $data['course_code'] ?? '';
            $course->college_id = $data['college_id'] ?? null;
            $course->credits = $data['credits'] ?? 0;
            $course->hours = $data['hours'] ?? 0;
            $course->semester = $data['semester'] ?? '';
            $course->academic_year = $data['academic_year'] ?? '';
            $course->responsible_teacher_id = $data['responsible_teacher_id'] ?? null;
            $course->is_public = $data['is_public'] ?? 0;
            $course->sort = $data['sort'] ?? 0;
            $course->status = $data['status'] ?? 1;
            $course->save();
            return $this->success($course, '课程创建成功');
        } catch (\Exception $e) {
            Log::error("创建课程失败: " . $e->getMessage());
            return $this->error('课程创建失败', 500);
        }
    }

    /**
     * 更新课程
     */
    public function update($id)
    {
        $data = $this->request->post();
        try {
            $course = Course::find($id);
            if (!$course) {
                return $this->error('课程不存在', 404);
            }
            $validate = Validate::rule([
                'name' => 'require|length:2,100',
                'description' => 'length:0,500',
                'course_code' => 'length:0,50',
                'college_id' => 'integer',
                'credits' => 'float',
                'hours' => 'integer',
                'semester' => 'length:0,20',
                'academic_year' => 'length:0,20',
                'responsible_teacher_id' => 'integer',
                'status' => 'in:0,1'
            ])->message([
                'name.require' => '课程名称不能为空',
                'name.length' => '课程名称长度必须在2-100个字符之间',
                'description.length' => '课程描述长度不能超过500个字符',
                'course_code.length' => '课程代码长度不能超过50个字符',
                'college_id.integer' => '学院ID格式不正确',
                'credits.float' => '学分格式不正确',
                'hours.integer' => '学时格式不正确',
                'semester.length' => '学期长度不能超过20个字符',
                'academic_year.length' => '学年长度不能超过20个字符',
                'responsible_teacher_id.integer' => '负责教师ID格式不正确',
                'status.in' => '状态值不正确'
            ]);
            if (!$validate->check($data)) {
                return $this->error($validate->getError(), 400);
            }
            if (Course::isNameExists($course->school_id, $data['name'], $id)) {
                return $this->error('课程名称已存在', 400);
            }
            
            // 检查课程代码是否重复
            if (!empty($data['course_code']) && Course::isCodeExists($course->school_id, $data['course_code'], $id)) {
                return $this->error('课程代码已存在', 400);
            }
            
            $course->name = $data['name'];
            $course->description = $data['description'] ?? '';
            $course->course_code = $data['course_code'] ?? '';
            $course->college_id = $data['college_id'] ?? null;
            $course->credits = $data['credits'] ?? 0;
            $course->hours = $data['hours'] ?? 0;
            $course->semester = $data['semester'] ?? '';
            $course->academic_year = $data['academic_year'] ?? '';
            $course->responsible_teacher_id = $data['responsible_teacher_id'] ?? null;
            $course->is_public = $data['is_public'] ?? 0;
            $course->sort = $data['sort'] ?? 0;
            $course->status = $data['status'] ?? 1;
            $course->save();
            
            // 处理标签关联
            if (isset($data['tag_ids'])) {
                $tagIds = !empty($data['tag_ids']) ? explode(',', $data['tag_ids']) : [];
                $course->tags()->detach(); // 清除现有关联
                if (!empty($tagIds)) {
                    $course->tags()->attach($tagIds); // 添加新关联
                }
            }
            
            return $this->success($course, '课程更新成功');
        } catch (\Exception $e) {
            Log::error("更新课程失败: " . $e->getMessage());
            return $this->error('课程更新失败', 500);
        }
    }

    /**
     * 删除课程
     */
    public function destroy($id)
    {
        try {
            $course = Course::find($id);
            if (!$course) {
                return $this->error('课程不存在', 404);
            }
            $course->delete();
            return $this->success(null, '课程删除成功');
        } catch (\Exception $e) {
            Log::error("删除课程失败: " . $e->getMessage());
            return $this->error('课程删除失败', 500);
        }
    }
    
    /**
     * 删除课程（页面方法）
     */
    public function delete()
    {
        $id = $this->request->param('id');
        return $this->destroy($id);
    }
    
    /**
     * 修改课程状态（页面方法）
     */
    public function changeStatus()
    {
        $id = $this->request->param('id');
        $status = $this->request->param('status');
        
        try {
            $course = Course::find($id);
            if (!$course) {
                return $this->error('课程不存在', 404);
            }
            
            $course->status = $status;
            $course->save();
            return $this->success(null, '状态修改成功');
            
        } catch (\Exception $e) {
            Log::error("修改课程状态失败: " . $e->getMessage());
            return $this->error('状态修改失败', 500);
        }
    }
    
    /**
     * 获取课程列表（API）
     */
    public function getList()
    {
        try {
            $school_id = $this->request->param('school_id', '');
            $college_id = $this->request->param('college_id', '');
            
            $query = Course::where('status', 1);
            if ($school_id) {
                $query->where('school_id', $school_id);
            }
            if ($college_id) {
                $query->where('college_id', $college_id);
            }
            
            $courses = $query->field('id,name,course_code')
                ->order('name', 'asc')
                ->select();
            
            return $this->success(['list' => $courses]);
        } catch (\Exception $e) {
            Log::error("获取课程列表失败: " . $e->getMessage());
            return $this->error('获取课程列表失败', 500);
        }
    }
    
    /**
     * 获取课程详情（API）
     */
    public function getDetail()
    {
        $id = $this->request->param('id');
        return $this->show($id);
    }

    /**
     * 课程关联教师（批量分配）
     */
    public function assignTeachers($id)
    {
        $data = $this->request->post();
        $validate = Validate::rule([
            'teacher_ids' => 'require|array'
        ])->message([
            'teacher_ids.require' => '教师ID列表不能为空',
            'teacher_ids.array' => '教师ID列表格式不正确'
        ]);
        if (!$validate->check($data)) {
            return $this->error($validate->getError(), 400);
        }
        try {
            $course = Course::find($id);
            if (!$course) {
                return $this->error('课程不存在', 404);
            }
            $course->teachers()->sync($data['teacher_ids']);
            return $this->success(null, '教师分配成功');
        } catch (\Exception $e) {
            Log::error("分配教师失败: " . $e->getMessage());
            return $this->error('分配教师失败', 500);
        }
    }

    /**
     * 获取课程统计
     */
    public function stats()
    {
        try {
            $totalCount = Course::count();
            $bySchool = Course::field('school_id, COUNT(*) as count')->group('school_id')->select();
            $byStatus = Course::field('status, COUNT(*) as count')->group('status')->select();
            return $this->success([
                'total' => $totalCount,
                'by_school' => $bySchool,
                'by_status' => $byStatus
            ]);
        } catch (\Exception $e) {
            Log::error("获取课程统计失败: " . $e->getMessage());
            return $this->error('获取课程统计失败', 500);
        }
    }
} 