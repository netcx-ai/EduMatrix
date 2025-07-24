<?php
declare(strict_types=1);

namespace app\controller\api\teacher;

use app\controller\api\BaseController;
use app\model\Course;
use app\model\College;
use think\Request;
use think\facade\Validate;

/**
 * 教师侧课程管理控制器
 */
class CourseController extends BaseController
{
    /**
     * 获取课程列表
     */
    public function index(Request $request)
    {
        try {
            $page = (int)$request->param('page', 1);
            $limit = (int)$request->param('limit', 10);
            $keyword = $request->param('keyword', '');
            $status = $request->param('status', null);
            
            $teacher = $request->user;
            
            $query = Course::where('school_id', $teacher->primary_school_id)
                ->where(function($q) use ($request) {
                    $q->where('responsible_teacher_id', $request->userId)
                      ->whereOr('id', 'in', function($subQuery) use ($request) {
                          $subQuery->table('edu_course_teacher')
                                   ->where('teacher_id', $request->userId)
                                   ->field('course_id');
                      });
                });
            
            if ($keyword) {
                $query->where('name', 'like', "%{$keyword}%");
            }
            
            if ($status !== null) {
                $query->where('status', $status);
            }
            
            $total = $query->count();
            $list = $query->page($page, $limit)->select();
            
            return $this->success([
                'list' => $list,
                'total' => $total,
                'page' => $page,
                'limit' => $limit
            ]);
            
        } catch (\Exception $e) {
            return $this->error('获取课程列表失败：' . $e->getMessage());
        }
    }
    
    /**
     * 课程详情
     */
    public function show(Request $request, $id)
    {
        try {
            $teacherId = $request->userId;
            $teacher = $request->user;
            
            $course = Course::with(['school', 'college', 'responsibleTeacher'])
                ->where('school_id', $teacher->primary_school_id)
                ->where(function($q) use ($teacherId) {
                    $q->where('responsible_teacher_id', $teacherId)
                      ->whereOr('id', 'in', function($subQuery) use ($teacherId) {
                          $subQuery->table('edu_course_teacher')
                                   ->where('teacher_id', $teacherId)
                                   ->field('course_id');
                      });
                })
                ->find($id);
            
            if (!$course) {
                return $this->error('课程不存在或无权限访问');
            }
            
            return $this->success($course);
            
        } catch (\Exception $e) {
            return $this->error('获取课程详情失败：' . $e->getMessage());
        }
    }
    
    /**
     * 获取学院列表（用于下拉选择）
     */
    public function colleges(Request $request)
    {
        try {
            $teacher = $request->user;
            
            $colleges = College::where('school_id', $teacher->primary_school_id)
                ->where('status', 1)
                ->field('id,name')
                ->select();
            
            return $this->success($colleges);
            
        } catch (\Exception $e) {
            return $this->error('获取学院列表失败：' . $e->getMessage());
        }
    }

    /**
     * 获取层级化课程数据（学校-学院-课程）
     * GET /api/teacher/courses/hierarchical
     */
    public function hierarchical(Request $request)
    {
        try {
            $teacher = $request->user;
            $teacherId = $request->userId;
            
            // 获取教师信息
            $teacherModel = $teacher->teacher ?? $teacher;
            $schoolId = $teacherModel->school_id ?? $teacher->primary_school_id;
            
            if (!$schoolId) {
                return $this->error('教师未关联学校信息');
            }
            
            // 获取学校信息
            $school = \app\model\School::find($schoolId);
            if (!$school) {
                return $this->error('学校信息不存在');
            }
            
            // 获取教师有权限的课程
            $courses = Course::with(['college'])
                ->where('school_id', $schoolId)
                ->where(function($q) use ($teacherId) {
                    $q->where('responsible_teacher_id', $teacherId)
                      ->whereOr('id', 'in', function($subQuery) use ($teacherId) {
                          $subQuery->table('edu_course_teacher')
                                   ->where('teacher_id', $teacherId)
                                   ->field('course_id');
                      });
                })
                ->where('status', 1)
                ->order('college_id ASC, name ASC')
                ->select();
            
            // 按学院分组课程
            $collegeGroups = [];
            $teacherCourseIds = \think\facade\Db::table('edu_course_teacher')
                ->where('teacher_id', $teacherId)
                ->column('course_id');
            
            foreach ($courses as $course) {
                $collegeId = $course->college_id ?: 0;
                $collegeName = $course->college->name ?? '未分组课程';
                $collegeShortName = $course->college->short_name ?? '';
                
                if (!isset($collegeGroups[$collegeId])) {
                    $collegeGroups[$collegeId] = [
                        'id' => $collegeId,
                        'name' => $collegeName,
                        'short_name' => $collegeShortName,
                        'courses' => []
                    ];
                }
                
                // 判断教师在课程中的角色
                $role = 'assistant'; // 默认为参与教师
                if ($course->responsible_teacher_id == $teacherId) {
                    $role = 'responsible'; // 负责教师
                } elseif (in_array($course->id, $teacherCourseIds)) {
                    $role = 'assistant'; // 参与教师
                }
                
                $collegeGroups[$collegeId]['courses'][] = [
                    'id' => $course->id,
                    'course_name' => $course->name,
                    'course_code' => $course->course_code,
                    'credits' => $course->credits ?? 0,
                    'hours' => $course->hours ?? 0,
                    'role' => $role,
                    'semester' => $course->semester,
                    'academic_year' => $course->academic_year
                ];
            }
            
            // 转换为数组并排序
            $colleges = array_values($collegeGroups);
            
            return $this->success([
                'school' => [
                    'id' => $school->id,
                    'name' => $school->name,
                    'short_name' => $school->short_name,
                    'code' => $school->code
                ],
                'colleges' => $colleges,
                'total_courses' => count($courses),
                'total_colleges' => count($colleges)
            ]);
            
        } catch (\Exception $e) {
            return $this->error('获取层级化课程数据失败：' . $e->getMessage());
        }
    }

    /**
     * 获取简化的课程选择列表（用于AI工具等场景）
     * GET /api/teacher/courses/options
     */
    public function options(Request $request)
    {
        try {
            $teacher = $request->user;
            $teacherId = $request->userId;
            $format = $request->param('format', 'flat'); // flat: 扁平列表, grouped: 分组列表
            
            $teacherModel = $teacher->teacher ?? $teacher;
            $schoolId = $teacherModel->school_id ?? $teacher->primary_school_id;
            
            if (!$schoolId) {
                return $this->error('教师未关联学校信息');
            }
            
            // 获取教师有权限的课程
            $courses = Course::with(['college'])
                ->where('school_id', $schoolId)
                ->where(function($q) use ($teacherId) {
                    $q->where('responsible_teacher_id', $teacherId)
                      ->whereOr('id', 'in', function($subQuery) use ($teacherId) {
                          $subQuery->name('course_teacher')
                                   ->where('teacher_id', $teacherId)
                                   ->field('course_id');
                      });
                })
                ->where('status', 1)
                ->order('college_id ASC, name ASC')
                ->select();
            
            if ($format === 'grouped') {
                // 分组格式
                $groups = [];
                $teacherCourseIds = \think\facade\Db::table('edu_course_teacher')
                    ->where('teacher_id', $teacherId)
                    ->column('course_id');
                foreach ($courses as $course) {
                    $collegeId = $course->college_id ?: 0;
                    $collegeName = $course->college->name ?? '未分组课程';
                    if (!isset($groups[$collegeId])) {
                        $groups[$collegeId] = [
                            'label' => $collegeName,
                            'options' => []
                        ];
                    }
                    // 判断角色
                    $role = '';
                    if ($course->responsible_teacher_id == $teacherId) {
                        $role = '负责';
                    } elseif (in_array($course->id, $teacherCourseIds)) {
                        $role = '参与';
                    }
                    $label = $course->name;
                    if ($role) {
                        $label .= " ({$role})";
                    }
                    $groups[$collegeId]['options'][] = [
                        'value' => $course->id,
                        'label' => $label,
                        'course_name' => $course->name,
                        'course_code' => $course->course_code,
                        'role' => $role
                    ];
                }
                $result = array_values($groups);
            } else {
                // 扁平格式
                $result = [];
                foreach ($courses as $course) {
                    $collegeName = $course->college->name ?? '';
                    $displayName = $collegeName ? "【{$collegeName}】{$course->name}" : $course->name;
                    $result[] = [
                        'value' => $course->id,
                        'label' => $displayName,
                        'course_name' => $course->name,
                        'course_code' => $course->course_code,
                        'college_name' => $collegeName
                    ];
                }
            }
            return $this->success($result);
        } catch (\Exception $e) {
            return $this->error('获取课程选择列表失败：' . $e->getMessage());
        }
    }
    
    /**
     * 格式化课程为扁平列表
     */
    private function formatCoursesFlat($courses)
    {
        $options = [];
        foreach ($courses as $course) {
            $collegeName = $course->college->name ?? '';
            $displayName = $collegeName ? "【{$collegeName}】{$course->name}" : $course->name;
            
            $options[] = [
                'value' => $course->id,
                'label' => $displayName,
                'course_name' => $course->name,
                'course_code' => $course->course_code,
                'college_name' => $collegeName
            ];
        }
        return $options;
    }
    
    /**
     * 格式化课程为分组列表
     */
    private function formatCoursesGrouped($courses, $teacherId)
    {
        $groups = [];
        $teacherCourseIds = \think\facade\Db::table('edu_course_teacher')
            ->where('teacher_id', $teacherId)
            ->column('course_id');
        
        foreach ($courses as $course) {
            $collegeId = $course->college_id ?: 0;
            $collegeName = $course->college->name ?? '未分组课程';
            
            if (!isset($groups[$collegeId])) {
                $groups[$collegeId] = [
                    'label' => $collegeName,
                    'options' => []
                ];
            }
            
            // 判断角色
            $role = '';
            if ($course->responsible_teacher_id == $teacherId) {
                $role = '负责';
            } elseif (in_array($course->id, $teacherCourseIds)) {
                $role = '参与';
            }
            
            $label = $course->name;
            if ($role) {
                $label .= " ({$role})";
            }
            
            $groups[$collegeId]['options'][] = [
                'value' => $course->id,
                'label' => $label,
                'course_name' => $course->name,
                'course_code' => $course->course_code,
                'role' => $role
            ];
        }
        
        return array_values($groups);
    }
} 