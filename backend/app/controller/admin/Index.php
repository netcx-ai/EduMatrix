<?php
declare (strict_types = 1);

namespace app\controller\admin;

use app\BaseController;
use app\model\Admin;
use app\model\SystemSetting;
use app\helper\SystemHelper;
use think\facade\View;
use think\facade\Db;

class Index extends BaseController
{
    /**
     * 后台首页
     */
    public function index()
    {
        // 获取系统基本信息
        $systemInfo = [
            'site_name' => SystemHelper::getSiteName(),
            'version' => SystemHelper::getVersion(),
            'timezone' => SystemHelper::getTimezone(),
            'language' => SystemHelper::getLanguage()
        ];
        
        // 获取统计数据
        $stats = $this->getStats();
        
        // 获取系统状态
        $systemStatus = $this->getSystemStatus();
        
        // 获取最近登录记录
        $recentLogins = $this->getRecentLogins();
        
        return View::fetch('admin/index/index', [
            'systemInfo' => $systemInfo,
            'stats' => $stats,
            'systemStatus' => $systemStatus,
            'recentLogins' => $recentLogins
        ]);
    }
    
    /**
     * 获取统计数据
     */
    private function getStats()
    {
        try {
            return [
                'admin_count' => Admin::count(),
                'user_count' => Db::name('user')->count(),
                'article_count' => Db::name('article')->count(),
                'category_count' => Db::name('category')->count(),
                'today_visits' => $this->getTodayVisits(),
                'month_visits' => $this->getMonthVisits()
            ];
        } catch (\Exception $e) {
            return [
                'admin_count' => 0,
                'user_count' => 0,
                'article_count' => 0,
                'category_count' => 0,
                'today_visits' => 0,
                'month_visits' => 0
            ];
        }
    }
    
    /**
     * 获取系统状态
     */
    private function getSystemStatus()
    {
        return [
            'maintenance_mode' => SystemHelper::isMaintenanceMode(),
            'cache_status' => $this->checkCacheStatus(),
            'database_status' => $this->checkDatabaseStatus(),
            'disk_usage' => $this->getDiskUsage(),
            'memory_usage' => $this->getMemoryUsage()
        ];
    }
    
    /**
     * 获取最近登录记录
     */
    private function getRecentLogins()
    {
        try {
            return Db::name('admin_log')
                ->alias('l')
                ->leftJoin('admin a', 'l.admin_id = a.id')
                ->where('l.action', 'login')
                ->order('l.create_time', 'desc')
                ->limit(10)
                ->field('l.*, a.real_name as admin_name, a.username as admin_username')
                ->select()
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * 获取今日访问量
     */
    private function getTodayVisits()
    {
        try {
            $today = date('Y-m-d');
            return Db::name('visit_log')
                ->where('date', $today)
                ->count();
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
            return Db::name('visit_log')
                ->where('date', 'like', $month . '%')
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * 检查缓存状态
     */
    private function checkCacheStatus()
    {
        try {
            $cache = \think\facade\Cache::get('system_status_test');
            if ($cache === null) {
                \think\facade\Cache::set('system_status_test', 'ok', 60);
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * 检查数据库状态
     */
    private function checkDatabaseStatus()
    {
        try {
            Db::query('SELECT 1');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * 获取磁盘使用情况
     */
    private function getDiskUsage()
    {
        try {
            $total = disk_total_space('.');
            $free = disk_free_space('.');
            $used = $total - $free;
            return [
                'total' => $this->formatBytes($total),
                'used' => $this->formatBytes($used),
                'free' => $this->formatBytes($free),
                'percent' => round(($used / $total) * 100, 2)
            ];
        } catch (\Exception $e) {
            return [
                'total' => '0 B',
                'used' => '0 B',
                'free' => '0 B',
                'percent' => 0
            ];
        }
    }
    
    /**
     * 获取内存使用情况
     */
    private function getMemoryUsage()
    {
        try {
            $memory = memory_get_usage(true);
            $peak = memory_get_peak_usage(true);
            return [
                'current' => $this->formatBytes($memory),
                'peak' => $this->formatBytes($peak)
            ];
        } catch (\Exception $e) {
            return [
                'current' => '0 B',
                'peak' => '0 B'
            ];
        }
    }
    
    /**
     * 格式化字节数
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
} 