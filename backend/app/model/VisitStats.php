<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;
use think\facade\Db;

class VisitStats extends Model
{
    // 设置表名
    protected $name = 'visit_stats';
    
    // 设置字段信息
    protected $schema = [
        'id'              => 'int',
        'date'            => 'date',
        'pv'              => 'int',
        'uv'              => 'int',
        'ip_count'        => 'int',
        'new_users'       => 'int',
        'bounce_rate'     => 'float',
        'avg_visit_time'  => 'int',
        'create_time'     => 'datetime',
        'update_time'     => 'datetime',
    ];

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * 生成指定日期的统计数据
     */
    public static function generateStats($date = null)
    {
        if (!$date) {
            $date = date('Y-m-d', strtotime('-1 day')); // 默认统计昨天的数据
        }

        try {
            // 获取基础访问数据
            $pv = VisitLog::where('date', $date)->count();
            $uv = VisitLog::where('date', $date)->distinct(true)->field('session_id')->count();
            $ipCount = VisitLog::where('date', $date)->distinct(true)->field('ip')->count();
            
            // 获取新用户数
            $newUsers = User::whereTime('create_time', $date)->count();
            
            // 计算跳出率（访问一个页面就离开的比例）
            $bounceRate = self::calculateBounceRate($date);
            
            // 计算平均访问时长
            $avgVisitTime = self::calculateAvgVisitTime($date);

            // 检查是否已存在该日期的统计
            $stats = self::where('date', $date)->find();
            
            $data = [
                'date' => $date,
                'pv' => $pv,
                'uv' => $uv,
                'ip_count' => $ipCount,
                'new_users' => $newUsers,
                'bounce_rate' => $bounceRate,
                'avg_visit_time' => $avgVisitTime,
            ];

            if ($stats) {
                // 更新已存在的记录
                $stats->save($data);
                return $stats;
            } else {
                // 创建新记录
                return self::create($data);
            }
        } catch (\Exception $e) {
            trace('生成访问统计失败: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * 计算跳出率
     */
    private static function calculateBounceRate($date)
    {
        try {
            // 获取当天所有会话
            $sessions = VisitLog::where('date', $date)
                ->field('session_id, count(*) as page_views')
                ->group('session_id')
                ->select()
                ->toArray();

            if (empty($sessions)) {
                return 0;
            }

            $totalSessions = count($sessions);
            $bounceSessions = 0;

            // 统计只访问一个页面的会话数
            foreach ($sessions as $session) {
                if ($session['page_views'] == 1) {
                    $bounceSessions++;
                }
            }

            return round(($bounceSessions / $totalSessions) * 100, 2);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * 计算平均访问时长
     */
    private static function calculateAvgVisitTime($date)
    {
        try {
            // 按会话分组，计算每个会话的访问时长
            $sessions = VisitLog::where('date', $date)
                ->field('session_id, min(visit_time) as first_visit, max(visit_time) as last_visit')
                ->group('session_id')
                ->having('count(*) > 1') // 只计算访问多个页面的会话
                ->select()
                ->toArray();

            if (empty($sessions)) {
                return 0;
            }

            $totalTime = 0;
            $validSessions = 0;

            foreach ($sessions as $session) {
                $firstVisit = strtotime($session['first_visit']);
                $lastVisit = strtotime($session['last_visit']);
                $duration = $lastVisit - $firstVisit;

                // 过滤异常数据（访问时长超过2小时的会话）
                if ($duration > 0 && $duration <= 7200) {
                    $totalTime += $duration;
                    $validSessions++;
                }
            }

            return $validSessions > 0 ? round($totalTime / $validSessions) : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * 获取统计趋势数据
     */
    public static function getTrendData($days = 30)
    {
        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime("-{$days} days"));

        return self::where('date', 'between', [$startDate, $endDate])
            ->order('date', 'asc')
            ->select()
            ->toArray();
    }

    /**
     * 获取指定时间段的汇总统计
     */
    public static function getSummaryStats($startDate, $endDate)
    {
        try {
            $stats = self::where('date', 'between', [$startDate, $endDate])
                ->field('
                    sum(pv) as total_pv,
                    sum(uv) as total_uv,
                    sum(ip_count) as total_ip,
                    sum(new_users) as total_new_users,
                    avg(bounce_rate) as avg_bounce_rate,
                    avg(avg_visit_time) as avg_visit_time
                ')
                ->find();

            return [
                'total_pv' => $stats['total_pv'] ?? 0,
                'total_uv' => $stats['total_uv'] ?? 0,
                'total_ip' => $stats['total_ip'] ?? 0,
                'total_new_users' => $stats['total_new_users'] ?? 0,
                'avg_bounce_rate' => round($stats['avg_bounce_rate'] ?? 0, 2),
                'avg_visit_time' => round($stats['avg_visit_time'] ?? 0),
            ];
        } catch (\Exception $e) {
            return [
                'total_pv' => 0,
                'total_uv' => 0,
                'total_ip' => 0,
                'total_new_users' => 0,
                'avg_bounce_rate' => 0,
                'avg_visit_time' => 0,
            ];
        }
    }

    /**
     * 获取实时统计数据
     */
    public static function getRealTimeStats()
    {
        $today = date('Y-m-d');
        $now = date('Y-m-d H:i:s');
        $oneHourAgo = date('Y-m-d H:i:s', strtotime('-1 hour'));

        try {
            return [
                'today_pv' => VisitLog::where('date', $today)->count(),
                'today_uv' => VisitLog::where('date', $today)->distinct(true)->field('session_id')->count(),
                'today_ip' => VisitLog::where('date', $today)->distinct(true)->field('ip')->count(),
                'hour_pv' => VisitLog::where('visit_time', 'between', [$oneHourAgo, $now])->count(),
                'hour_uv' => VisitLog::where('visit_time', 'between', [$oneHourAgo, $now])->distinct(true)->field('session_id')->count(),
                'online_users' => self::getOnlineUsers(),
            ];
        } catch (\Exception $e) {
            return [
                'today_pv' => 0,
                'today_uv' => 0,
                'today_ip' => 0,
                'hour_pv' => 0,
                'hour_uv' => 0,
                'online_users' => 0,
            ];
        }
    }

    /**
     * 估算在线用户数
     */
    private static function getOnlineUsers()
    {
        try {
            $fiveMinutesAgo = date('Y-m-d H:i:s', strtotime('-5 minutes'));
            return VisitLog::where('visit_time', '>=', $fiveMinutesAgo)
                ->distinct(true)
                ->field('session_id')
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
} 