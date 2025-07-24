<?php
declare (strict_types = 1);

namespace app\controller\admin;

use app\BaseController;
use app\model\User;
use app\model\Admin;
use app\model\Article;
use app\model\Category;
use app\model\Tag;
use app\model\VisitLog;
use app\model\VisitStats;
use app\model\School;
use app\model\College;
use app\model\Teacher;
use app\model\Course;
use think\facade\View;
use think\facade\Db;
use think\Request;

class Stats extends BaseController
{
    /**
     * 概览统计
     */
    public function overview(Request $request)
    {
        try {
            // 获取基础统计数据
            $stats = [
                'user_count' => User::count(),
                'admin_count' => Admin::count(),
                'article_count' => Article::count(),
                'category_count' => Category::count(),
                'tag_count' => Tag::count(),
                'today_visits' => $this->getTodayVisits(),
                'month_visits' => $this->getMonthVisits(),
                'today_users' => $this->getTodayUsers(),
                'month_users' => $this->getMonthUsers(),
            ];
            
            // 获取图表数据
            $chartData = [
                'user_trend' => $this->getUserTrend(),
                'article_trend' => $this->getArticleTrend(),
                'visit_trend' => $this->getVisitTrend(),
            ];
            
            return View::fetch('admin/stats/overview', [
                'stats' => $stats,
                'chartData' => $chartData
            ]);
            
        } catch (\Exception $e) {
            return $this->error('页面加载失败：' . $e->getMessage());
        }
    }
    
    /**
     * 用户统计
     */
    public function user(Request $request)
    {
        try {
            // 如果是AJAX请求，返回JSON
            if ($request->isAjax()) {
                $type = $request->param('type', 'daily');
                $start_date = $request->param('start_date', '');
                $end_date = $request->param('end_date', '');
                
                $data = $this->getUserStats($type, $start_date, $end_date);
                
                return json([
                    'code' => 0,
                    'msg' => '',
                    'data' => $data
                ]);
            }
            
            // 非AJAX请求，返回页面
            return View::fetch('admin/stats/user');
            
        } catch (\Exception $e) {
            if ($request->isAjax()) {
                return json([
                    'code' => 1,
                    'msg' => '请求异常：' . $e->getMessage(),
                    'data' => []
                ]);
            } else {
                return $this->error('页面加载失败：' . $e->getMessage());
            }
        }
    }
    
    /**
     * 内容统计
     */
    public function content(Request $request)
    {
        try {
            // 如果是AJAX请求，返回JSON
            if ($request->isAjax()) {
                $type = $request->param('type', 'category');
                
                $data = $this->getContentStats($type);
                
                return json([
                    'code' => 0,
                    'msg' => '',
                    'data' => $data
                ]);
            }
            
            // 非AJAX请求，返回页面
            return View::fetch('admin/stats/content');
            
        } catch (\Exception $e) {
            if ($request->isAjax()) {
                return json([
                    'code' => 1,
                    'msg' => '请求异常：' . $e->getMessage(),
                    'data' => []
                ]);
            } else {
                return $this->error('页面加载失败：' . $e->getMessage());
            }
        }
    }
    
    /**
     * 访问统计
     */
    public function visit(Request $request)
    {
        try {
            // 如果是AJAX请求，返回JSON
            if ($request->isAjax()) {
                $type = $request->param('type', 'trend');
                $days = (int)$request->param('days', 7);
                
                $data = $this->getVisitStats($type, $days);
                
                return json([
                    'code' => 0,
                    'msg' => '',
                    'data' => $data
                ]);
            }
            
            // 非AJAX请求，返回页面
            return View::fetch('admin/stats/visit');
            
        } catch (\Exception $e) {
            if ($request->isAjax()) {
                return json([
                    'code' => 1,
                    'msg' => '请求异常：' . $e->getMessage(),
                    'data' => []
                ]);
            } else {
                return $this->error('页面加载失败：' . $e->getMessage());
            }
        }
    }
    
    /**
     * 教育统计
     */
    public function education(Request $request)
    {
        try {
            // 获取基础统计数据
            $stats = [
                'school_count' => School::count(),
                'active_school_count' => School::where('status', 1)->count(),
                'pending_school_count' => School::where('status', 0)->count(),
                'suspended_school_count' => School::where('status', 2)->count(),
                
                'college_count' => College::count(),
                'active_college_count' => College::where('status', 1)->count(),
                
                'teacher_count' => Teacher::count(),
                'active_teacher_count' => Teacher::where('status', 1)->count(),
                
                'course_count' => Course::count(),
                'active_course_count' => Course::where('status', 1)->count(),
                'ended_course_count' => Course::where('status', 2)->count(),
                'pending_course_count' => Course::where('status', 0)->count(),
                'rejected_course_count' => Course::where('status', 3)->count(),
                
                // 教师职称统计
                'professor_count' => Teacher::where('title', '教授')->count(),
                'associate_professor_count' => Teacher::where('title', '副教授')->count(),
                'lecturer_count' => Teacher::where('title', '讲师')->count(),
                'assistant_count' => Teacher::where('title', '助教')->count(),
            ];
            
            return View::fetch('admin/stats/education', [
                'stats' => $stats
            ]);
            
        } catch (\Exception $e) {
            return $this->error('页面加载失败：' . $e->getMessage());
        }
    }
    
    /**
     * 获取学校统计数据
     */
    public function getSchoolStats(Request $request)
    {
        try {
            $page = (int)$request->param('page', 1);
            $limit = (int)$request->param('limit', 10);
            
            $query = School::with(['colleges', 'teachers', 'courses']);
            
            $total = $query->count();
            $schools = $query->page($page, $limit)->select();
            
            $data = [];
            foreach ($schools as $school) {
                $data[] = [
                    'school_name' => $school->name,
                    'college_count' => $school->colleges ? count($school->colleges) : 0,
                    'teacher_count' => $school->teachers ? count($school->teachers) : 0,
                    'course_count' => $school->courses ? count($school->courses) : 0,
                    'student_count' => 0, // 暂时设为0，后续可以添加学生统计
                    'active_rate' => $school->status == 1 ? '正常' : ($school->status == 0 ? '待审核' : '已停用'),
                    'create_time' => $school->create_time
                ];
            }
            
            return json([
                'code' => 0,
                'msg' => '',
                'count' => $total,
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            return json([
                'code' => 1,
                'msg' => '请求异常：' . $e->getMessage(),
                'count' => 0,
                'data' => []
            ]);
        }
    }
    
    /**
     * 实时统计
     */
    public function realtime(Request $request)
    {
        try {
            $data = VisitStats::getRealTimeStats();
            
            return json([
                'code' => 0,
                'msg' => '',
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            return json([
                'code' => 1,
                'msg' => '请求异常：' . $e->getMessage(),
                'data' => []
            ]);
        }
    }
    
    /**
     * 获取今日访问量
     */
    private function getTodayVisits()
    {
        try {
            $today = date('Y-m-d');
            return VisitLog::where('date', $today)->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * 获取本月访问量
     */
    private function getMonthVisits()
    {
        try {
            $month = date('Y-m');
            return VisitLog::where('date', 'like', $month . '%')->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * 获取今日新增用户
     */
    private function getTodayUsers()
    {
        $today = date('Y-m-d');
        return User::whereTime('create_time', 'today')->count();
    }
    
    /**
     * 获取本月新增用户
     */
    private function getMonthUsers()
    {
        return User::whereTime('create_time', 'month')->count();
    }
    
    /**
     * 获取用户趋势数据
     */
    private function getUserTrend()
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $count = User::whereTime('create_time', $date)->count();
            $data[] = [
                'date' => $date,
                'count' => $count
            ];
        }
        return $data;
    }
    
    /**
     * 获取文章趋势数据
     */
    private function getArticleTrend()
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $count = Article::whereTime('create_time', $date)->count();
            $data[] = [
                'date' => $date,
                'count' => $count
            ];
        }
        return $data;
    }
    
    /**
     * 获取访问趋势数据
     */
    private function getVisitTrend()
    {
        try {
            return VisitLog::getVisitTrend(7);
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * 获取用户统计数据
     */
    private function getUserStats($type, $start_date, $end_date)
    {
        switch ($type) {
            case 'daily':
                return $this->getDailyUserStats($start_date, $end_date);
            case 'monthly':
                return $this->getMonthlyUserStats($start_date, $end_date);
            case 'gender':
                return $this->getUserGenderStats();
            case 'age':
                return $this->getUserAgeStats();
            case 'member_level':
                return $this->getUserMemberLevelStats();
            default:
                return [];
        }
    }
    
    /**
     * 获取内容统计数据
     */
    private function getContentStats($type)
    {
        switch ($type) {
            case 'category':
                return $this->getCategoryStats();
            case 'tag':
                return $this->getTagStats();
            case 'author':
                return $this->getAuthorStats();
            default:
                return [];
        }
    }
    
    /**
     * 获取每日用户统计
     */
    private function getDailyUserStats($start_date, $end_date)
    {
        $data = [];
        $start = $start_date ?: date('Y-m-d', strtotime('-7 days'));
        $end = $end_date ?: date('Y-m-d');
        
        $current = strtotime($start);
        $endTime = strtotime($end);
        
        while ($current <= $endTime) {
            $date = date('Y-m-d', $current);
            $count = User::whereTime('create_time', $date)->count();
            $data[] = [
                'date' => $date,
                'count' => $count
            ];
            $current = strtotime('+1 day', $current);
        }
        
        return $data;
    }
    
    /**
     * 获取每月用户统计
     */
    private function getMonthlyUserStats($start_date, $end_date)
    {
        $data = [];
        $start = $start_date ?: date('Y-m', strtotime('-6 months'));
        $end = $end_date ?: date('Y-m');
        
        $current = strtotime($start . '-01');
        $endTime = strtotime($end . '-01');
        
        while ($current <= $endTime) {
            $month = date('Y-m', $current);
            $count = User::whereTime('create_time', $month)->count();
            $data[] = [
                'month' => $month,
                'count' => $count
            ];
            $current = strtotime('+1 month', $current);
        }
        
        return $data;
    }
    
    /**
     * 获取用户性别统计
     */
    private function getUserGenderStats()
    {
        try {
            $data = [
                ['name' => '男', 'count' => User::where('gender', User::GENDER_MALE)->count()],
                ['name' => '女', 'count' => User::where('gender', User::GENDER_FEMALE)->count()],
                ['name' => '未知', 'count' => User::where('gender', User::GENDER_UNKNOWN)->count()]
            ];
            return $data;
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * 获取用户年龄统计
     */
    private function getUserAgeStats()
    {
        try {
            $data = [
                ['name' => '18岁以下', 'count' => 0],
                ['name' => '18-25岁', 'count' => 0],
                ['name' => '26-35岁', 'count' => 0],
                ['name' => '36-45岁', 'count' => 0],
                ['name' => '45岁以上', 'count' => 0]
            ];
            
            // 计算年龄分布
            $users = User::where('birthday', '<>', '')
                ->where('birthday', 'is not', null)
                ->field('birthday')
                ->select();
                
            foreach ($users as $user) {
                $age = $this->calculateAge($user->birthday);
                if ($age < 18) {
                    $data[0]['count']++;
                } elseif ($age >= 18 && $age <= 25) {
                    $data[1]['count']++;
                } elseif ($age >= 26 && $age <= 35) {
                    $data[2]['count']++;
                } elseif ($age >= 36 && $age <= 45) {
                    $data[3]['count']++;
                } else {
                    $data[4]['count']++;
                }
            }
            
            return $data;
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * 计算年龄
     */
    private function calculateAge($birthday)
    {
        if (empty($birthday)) {
            return 0;
        }
        
        $birthDate = new \DateTime($birthday);
        $today = new \DateTime();
        $age = $today->diff($birthDate);
        
        return $age->y;
    }
    
    /**
     * 获取会员等级统计
     */
    private function getUserMemberLevelStats()
    {
        try {
            $data = [
                ['name' => '普通会员', 'count' => User::where('member_level', User::MEMBER_LEVEL_NORMAL)->count()],
                ['name' => 'VIP会员', 'count' => User::where('member_level', User::MEMBER_LEVEL_VIP)->count()],
                ['name' => 'SVIP会员', 'count' => User::where('member_level', User::MEMBER_LEVEL_SVIP)->count()]
            ];
            return $data;
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * 获取分类统计
     */
    private function getCategoryStats()
    {
        $categories = Category::withCount('articles')->select();
        $data = [];
        foreach ($categories as $category) {
            $data[] = [
                'name' => $category->name,
                'count' => $category->articles_count
            ];
        }
        return $data;
    }
    
    /**
     * 获取标签统计
     */
    private function getTagStats()
    {
        $tags = Tag::withCount('articles')->select();
        $data = [];
        foreach ($tags as $tag) {
            $data[] = [
                'name' => $tag->name,
                'count' => $tag->articles_count
            ];
        }
        return $data;
    }
    
    /**
     * 获取作者统计
     */
    private function getAuthorStats()
    {
        $authors = Admin::withCount('articles')->select();
        $data = [];
        foreach ($authors as $author) {
            $data[] = [
                'name' => $author->real_name ?: $author->username,
                'count' => $author->articles_count
            ];
        }
        return $data;
    }
    
    /**
     * 获取访问统计数据
     */
    private function getVisitStats($type, $days = 7)
    {
        switch ($type) {
            case 'trend':
                return VisitLog::getVisitTrend($days);
            case 'popular_pages':
                return VisitLog::getPopularPages();
            case 'referer':
                return VisitLog::getRefererStats();
            case 'device':
                return VisitLog::getDeviceStats();
            case 'browser':
                return VisitLog::getBrowserStats();
            case 'location':
                return VisitLog::getLocationStats();
            default:
                return [];
        }
    }
} 