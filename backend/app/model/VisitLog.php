<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;
use think\facade\Db;

class VisitLog extends Model
{
    // 设置表名
    protected $name = 'visit_log';
    
    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'ip'            => 'string',
        'user_agent'    => 'string',
        'url'           => 'string',
        'referer'       => 'string',
        'user_id'       => 'int',
        'session_id'    => 'string',
        'method'        => 'string',
        'response_time' => 'int',
        'status_code'   => 'int',
        'country'       => 'string',
        'province'      => 'string',
        'city'          => 'string',
        'device_type'   => 'string',
        'browser'       => 'string',
        'os'            => 'string',
        'visit_time'    => 'datetime',
        'date'          => 'date',
    ];

    // 自动写入时间戳
    protected $autoWriteTimestamp = false;

    /**
     * 记录访问日志
     */
    public static function record($data)
    {
        try {
            // 解析用户代理
            $userAgentInfo = self::parseUserAgent($data['user_agent'] ?? '');
            
            // 获取IP地理位置信息
            $locationInfo = self::getLocationByIp($data['ip'] ?? '');
            
            $logData = [
                'ip'            => $data['ip'] ?? '',
                'user_agent'    => $data['user_agent'] ?? '',
                'url'           => $data['url'] ?? '',
                'referer'       => $data['referer'] ?? '',
                'user_id'       => $data['user_id'] ?? null,
                'session_id'    => $data['session_id'] ?? '',
                'method'        => $data['method'] ?? 'GET',
                'response_time' => $data['response_time'] ?? null,
                'status_code'   => $data['status_code'] ?? 200,
                'country'       => $locationInfo['country'] ?? '',
                'province'      => $locationInfo['province'] ?? '',
                'city'          => $locationInfo['city'] ?? '',
                'device_type'   => $userAgentInfo['device_type'] ?? '',
                'browser'       => $userAgentInfo['browser'] ?? '',
                'os'            => $userAgentInfo['os'] ?? '',
                'visit_time'    => $data['visit_time'] ?? date('Y-m-d H:i:s'),
                'date'          => $data['date'] ?? date('Y-m-d'),
            ];
            
            return self::create($logData);
        } catch (\Exception $e) {
            // 记录错误但不影响主流程
            trace('访问日志记录失败: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * 解析用户代理信息
     */
    private static function parseUserAgent($userAgent)
    {
        $info = [
            'device_type' => 'unknown',
            'browser' => 'unknown',
            'os' => 'unknown'
        ];

        if (empty($userAgent)) {
            return $info;
        }

        // 检测设备类型
        if (preg_match('/Mobile|Android|iPhone|iPad/i', $userAgent)) {
            $info['device_type'] = 'mobile';
        } elseif (preg_match('/Tablet|iPad/i', $userAgent)) {
            $info['device_type'] = 'tablet';
        } else {
            $info['device_type'] = 'desktop';
        }

        // 检测浏览器
        if (preg_match('/Chrome\/([0-9.]+)/i', $userAgent, $matches)) {
            $info['browser'] = 'Chrome ' . $matches[1];
        } elseif (preg_match('/Firefox\/([0-9.]+)/i', $userAgent, $matches)) {
            $info['browser'] = 'Firefox ' . $matches[1];
        } elseif (preg_match('/Safari\/([0-9.]+)/i', $userAgent, $matches)) {
            $info['browser'] = 'Safari ' . $matches[1];
        } elseif (preg_match('/Edge\/([0-9.]+)/i', $userAgent, $matches)) {
            $info['browser'] = 'Edge ' . $matches[1];
        }

        // 检测操作系统
        if (preg_match('/Windows NT ([0-9.]+)/i', $userAgent, $matches)) {
            $info['os'] = 'Windows ' . $matches[1];
        } elseif (preg_match('/Mac OS X ([0-9._]+)/i', $userAgent, $matches)) {
            $info['os'] = 'macOS ' . str_replace('_', '.', $matches[1]);
        } elseif (preg_match('/Linux/i', $userAgent)) {
            $info['os'] = 'Linux';
        } elseif (preg_match('/Android ([0-9.]+)/i', $userAgent, $matches)) {
            $info['os'] = 'Android ' . $matches[1];
        } elseif (preg_match('/iPhone OS ([0-9._]+)/i', $userAgent, $matches)) {
            $info['os'] = 'iOS ' . str_replace('_', '.', $matches[1]);
        }

        return $info;
    }

    /**
     * 根据IP获取地理位置信息
     */
    private static function getLocationByIp($ip)
    {
        // 这里可以接入第三方IP地理位置服务
        // 如：高德地图、百度地图、腾讯位置服务等
        // 暂时返回默认值
        return [
            'country' => '中国',
            'province' => '',
            'city' => ''
        ];
    }

    /**
     * 获取今日访问统计
     */
    public static function getTodayStats()
    {
        $today = date('Y-m-d');
        
        return [
            'pv' => self::where('date', $today)->count(),
            'uv' => self::where('date', $today)->distinct(true)->field('session_id')->count(),
            'ip_count' => self::where('date', $today)->distinct(true)->field('ip')->count(),
        ];
    }

    /**
     * 获取访问趋势数据
     */
    public static function getVisitTrend($days = 7)
    {
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $pv = self::where('date', $date)->count();
            $uv = self::where('date', $date)->distinct(true)->field('session_id')->count();
            
            $data[] = [
                'date' => $date,
                'pv' => $pv,
                'uv' => $uv
            ];
        }
        return $data;
    }

    /**
     * 获取热门页面
     */
    public static function getPopularPages($limit = 10)
    {
        return self::field('url, count(*) as visit_count')
            ->where('date', '>=', date('Y-m-d', strtotime('-7 days')))
            ->group('url')
            ->order('visit_count', 'desc')
            ->limit($limit)
            ->select()
            ->toArray();
    }

    /**
     * 获取访问来源统计
     */
    public static function getRefererStats($limit = 10)
    {
        return self::field('referer, count(*) as visit_count')
            ->where('referer', '<>', '')
            ->where('date', '>=', date('Y-m-d', strtotime('-7 days')))
            ->group('referer')
            ->order('visit_count', 'desc')
            ->limit($limit)
            ->select()
            ->toArray();
    }

    /**
     * 获取设备类型统计
     */
    public static function getDeviceStats()
    {
        return self::field('device_type, count(*) as count')
            ->where('date', '>=', date('Y-m-d', strtotime('-7 days')))
            ->group('device_type')
            ->select()
            ->toArray();
    }

    /**
     * 获取浏览器统计
     */
    public static function getBrowserStats()
    {
        return self::field('browser, count(*) as count')
            ->where('date', '>=', date('Y-m-d', strtotime('-7 days')))
            ->group('browser')
            ->order('count', 'desc')
            ->limit(10)
            ->select()
            ->toArray();
    }

    /**
     * 获取地域分布统计
     */
    public static function getLocationStats()
    {
        return self::field('province, city, count(*) as count')
            ->where('date', '>=', date('Y-m-d', strtotime('-7 days')))
            ->where('province', '<>', '')
            ->group('province, city')
            ->order('count', 'desc')
            ->limit(20)
            ->select()
            ->toArray();
    }
} 