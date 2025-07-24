<?php
declare (strict_types = 1);

namespace app\controller\admin;

use app\BaseController;
use app\model\AiTool;
use app\model\AiUsage;
use app\model\School;
use think\facade\View;
use think\facade\Log;
use think\Request;
use think\Validate;

class AiToolUsageController extends BaseController
{
    /**
     * 使用统计页面
     */
    public function index()
    {
        try {
            // 获取工具列表
            $tools = AiTool::where('status', 1)
                ->field('id,name')
                ->order('name', 'asc')
                ->select();
            
            // 获取学校列表
            $schools = School::where('status', 1)
                ->field('id,name')
                ->order('name', 'asc')
                ->select();
            
            View::assign([
                'tools' => $tools,
                'schools' => $schools
            ]);
            
            return View::fetch('admin/ai_tool/usage');
        } catch (\Exception $e) {
            Log::error("获取使用统计页面失败: " . $e->getMessage());
            $this->error('获取使用统计页面失败');
        }
    }
    
    /**
     * 获取使用记录列表
     */
    public function getList()
    {
        try {
            $page = $this->request->param('page', 1);
            $limit = $this->request->param('limit', 20);
            $tool_id = $this->request->param('tool_id', '');
            $school_id = $this->request->param('school_id', '');
            $date_range = $this->request->param('date_range', 'month');
            
            $query = AiUsage::with(['tool', 'school', 'teacher'])
                ->order('usage_time', 'desc');
            
            // 工具筛选
            if ($tool_id) {
                $query->where('tool_id', $tool_id);
            }
            
            // 学校筛选
            if ($school_id) {
                $query->where('school_id', $school_id);
            }
            
            // 时间范围筛选
            switch ($date_range) {
                case 'today':
                    $query->whereDay('usage_time');
                    break;
                case 'yesterday':
                    $query->whereDay('usage_time', 'yesterday');
                    break;
                case 'week':
                    $query->whereWeek('usage_time');
                    break;
                case 'month':
                    $query->whereMonth('usage_time');
                    break;
                case 'year':
                    $query->whereYear('usage_time');
                    break;
            }
            
            $list = $query->paginate([
                'list_rows' => $limit,
                'page' => $page
            ]);
            
            // 处理数据
            $items = [];
            foreach ($list->items() as $item) {
                $items[] = [
                    'id' => $item->id,
                    'tool_name' => $item->tool->name,
                    'school_name' => $item->school->name,
                    'teacher_name' => $item->teacher->name,
                    'usage_time' => $item->usage_time,
                    'usage_type' => $item->usage_type,
                    'usage_count' => $item->usage_count,
                    'usage_duration' => $item->usage_duration,
                    'status' => $item->status,
                    'remark' => $item->remark
                ];
            }
            
            return json([
                'code' => 0,
                'msg' => '',
                'count' => $list->total(),
                'data' => $items
            ]);
            
        } catch (\Exception $e) {
            Log::error("获取使用记录列表失败: " . $e->getMessage());
            return json(['code' => 500, 'msg' => '获取使用记录列表失败']);
        }
    }
    
    /**
     * 获取统计数据
     */
    public function getStats()
    {
        try {
            $tool_id = $this->request->param('tool_id', '');
            $school_id = $this->request->param('school_id', '');
            $date_range = $this->request->param('date_range', 'month');
            
            $query = AiUsage::where('1=1');
            
            // 工具筛选
            if ($tool_id) {
                $query->where('tool_id', $tool_id);
            }
            
            // 学校筛选
            if ($school_id) {
                $query->where('school_id', $school_id);
            }
            
            // 时间范围筛选
            switch ($date_range) {
                case 'today':
                    $query->whereDay('usage_time');
                    break;
                case 'yesterday':
                    $query->whereDay('usage_time', 'yesterday');
                    break;
                case 'week':
                    $query->whereWeek('usage_time');
                    break;
                case 'month':
                    $query->whereMonth('usage_time');
                    break;
                case 'year':
                    $query->whereYear('usage_time');
                    break;
            }
            
            // 统计数据
            $stats = [
                'total_usage' => $query->sum('usage_count'),
                'today_usage' => AiUsage::whereDay('usage_time')->sum('usage_count'),
                'school_count' => $query->group('school_id')->count(),
                'teacher_count' => $query->group('teacher_id')->count()
            ];
            
            // 使用趋势
            $trend = $this->getUsageTrend($tool_id, $school_id, $date_range);
            
            // 工具分布
            $distribution = $this->getToolDistribution($school_id, $date_range);
            
            return json([
                'code' => 0,
                'msg' => '',
                'data' => [
                    'total_usage' => $stats['total_usage'] ?: 0,
                    'today_usage' => $stats['today_usage'] ?: 0,
                    'school_count' => $stats['school_count'] ?: 0,
                    'teacher_count' => $stats['teacher_count'] ?: 0,
                    'trend' => $trend,
                    'distribution' => $distribution
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error("获取统计数据失败: " . $e->getMessage());
            return json(['code' => 500, 'msg' => '获取统计数据失败']);
        }
    }
    
    /**
     * 获取使用趋势数据
     */
    private function getUsageTrend($tool_id, $school_id, $date_range)
    {
        $query = AiUsage::where('1=1');
        
        if ($tool_id) {
            $query->where('tool_id', $tool_id);
        }
        if ($school_id) {
            $query->where('school_id', $school_id);
        }
        
        switch ($date_range) {
            case 'today':
                $data = $query->whereDay('usage_time')
                    ->field('HOUR(usage_time) as date, SUM(usage_count) as count')
                    ->group('HOUR(usage_time)')
                    ->select();
                $dates = range(0, 23);
                break;
            case 'week':
                $data = $query->whereWeek('usage_time')
                    ->field('DATE(usage_time) as date, SUM(usage_count) as count')
                    ->group('DATE(usage_time)')
                    ->select();
                $dates = array_map(function($i) {
                    return date('Y-m-d', strtotime("-{$i} days"));
                }, range(6, 0));
                break;
            case 'month':
                $data = $query->whereMonth('usage_time')
                    ->field('DATE(usage_time) as date, SUM(usage_count) as count')
                    ->group('DATE(usage_time)')
                    ->select();
                $dates = array_map(function($i) {
                    return date('Y-m-d', strtotime("-{$i} days"));
                }, range(29, 0));
                break;
            case 'year':
                $data = $query->whereYear('usage_time')
                    ->field('MONTH(usage_time) as date, SUM(usage_count) as count')
                    ->group('MONTH(usage_time)')
                    ->select();
                $dates = range(1, 12);
                break;
            default:
                $data = [];
                $dates = [];
        }
        
        $counts = array_fill(0, count($dates), 0);
        foreach ($data as $item) {
            $index = array_search($item['date'], $dates);
            if ($index !== false) {
                $counts[$index] = intval($item['count']);
            }
        }
        
        return [
            'dates' => $dates,
            'counts' => $counts
        ];
    }
    
    /**
     * 获取工具分布数据
     */
    private function getToolDistribution($school_id, $date_range)
    {
        $query = AiUsage::alias('u')
            ->join('ai_tool t', 'u.tool_id = t.id')
            ->field('t.name, SUM(u.usage_count) as value');
        
        if ($school_id) {
            $query->where('u.school_id', $school_id);
        }
        
        switch ($date_range) {
            case 'today':
                $query->whereDay('u.usage_time');
                break;
            case 'yesterday':
                $query->whereDay('u.usage_time', 'yesterday');
                break;
            case 'week':
                $query->whereWeek('u.usage_time');
                break;
            case 'month':
                $query->whereMonth('u.usage_time');
                break;
            case 'year':
                $query->whereYear('u.usage_time');
                break;
        }
        
        return $query->group('u.tool_id')
            ->order('value', 'desc')
            ->select()
            ->toArray();
    }
} 