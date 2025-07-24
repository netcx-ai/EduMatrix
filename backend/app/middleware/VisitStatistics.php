<?php
declare (strict_types = 1);

namespace app\middleware;

use app\model\VisitLog;
use think\Request;
use think\Response;

class VisitStatistics
{
    /**
     * 处理请求
     *
     * @param Request $request
     * @param \Closure $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        $startTime = microtime(true);
        
        // 执行请求
        $response = $next($request);
        
        // 计算响应时间
        $responseTime = round((microtime(true) - $startTime) * 1000);
        
        // 异步记录访问日志（不影响响应速度）
        $this->recordVisitLog($request, $response, $responseTime);
        
        return $response;
    }
    
    /**
     * 记录访问日志
     */
    private function recordVisitLog($request, $response, $responseTime)
    {
        try {
            // 排除不需要统计的请求
            if ($this->shouldSkipLogging($request)) {
                return;
            }
            
            $data = [
                'ip' => $this->getClientIp($request),
                'user_agent' => $request->header('user-agent', ''),
                'url' => $request->url(true),
                'referer' => $request->header('referer', ''),
                'user_id' => $this->getUserId($request),
                'session_id' => session_id() ?: $this->generateSessionId($request),
                'method' => $request->method(),
                'response_time' => $responseTime,
                'status_code' => $response->getCode(),
                'visit_time' => date('Y-m-d H:i:s'),
                'date' => date('Y-m-d'),
            ];
            
            // 使用队列异步处理（如果配置了队列）
            if (class_exists('\think\Queue')) {
                \think\Queue::push('app\job\VisitLogJob', $data);
            } else {
                // 直接记录
                VisitLog::record($data);
            }
            
            // 更新用户访问信息
            $this->updateUserVisitInfo($data['user_id']);
            
        } catch (\Exception $e) {
            // 记录错误但不影响主流程
            trace('访问统计中间件错误: ' . $e->getMessage(), 'error');
        }
    }
    
    /**
     * 判断是否需要跳过日志记录
     */
    private function shouldSkipLogging($request)
    {
        $url = $request->url();
        $method = $request->method();
        
        // 跳过的URL模式
        $skipPatterns = [
            '/favicon.ico',
            '/robots.txt',
            '/sitemap.xml',
            '/admin/api/', // API接口
            '/static/',    // 静态资源
            '/assets/',    // 资源文件
            '/uploads/',   // 上传文件
        ];
        
        foreach ($skipPatterns as $pattern) {
            if (strpos($url, $pattern) !== false) {
                return true;
            }
        }
        
        // 跳过非GET和POST请求的某些情况
        if (!in_array($method, ['GET', 'POST'])) {
            return true;
        }
        
        // 跳过AJAX心跳检测等
        if ($request->isAjax() && in_array($url, ['/admin/heartbeat', '/api/ping'])) {
            return true;
        }
        
        return false;
    }
    
    /**
     * 获取客户端真实IP
     */
    private function getClientIp($request)
    {
        $ip = $request->ip();
        
        // 处理代理服务器的情况
        $headers = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_CLIENT_IP',
            'HTTP_X_CLUSTER_CLIENT_IP',
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    break;
                }
            }
        }
        
        return $ip;
    }
    
    /**
     * 获取用户ID
     */
    private function getUserId($request)
    {
        // 从session中获取用户ID
        if (session('user_id')) {
            return session('user_id');
        }
        
        // 从JWT token中获取用户ID（如果使用JWT）
        $token = $request->header('authorization');
        if ($token && class_exists('\Firebase\JWT\JWT')) {
            try {
                // 这里需要根据实际的JWT实现来解析
                // $decoded = JWT::decode($token, $key, ['HS256']);
                // return $decoded->user_id;
            } catch (\Exception $e) {
                // JWT解析失败
            }
        }
        
        return null;
    }
    
    /**
     * 生成会话ID
     */
    private function generateSessionId($request)
    {
        // 基于IP和User-Agent生成唯一会话ID
        $ip = $this->getClientIp($request);
        $userAgent = $request->header('user-agent', '');
        $date = date('Y-m-d');
        
        return md5($ip . $userAgent . $date);
    }
    
    /**
     * 更新用户访问信息
     */
    private function updateUserVisitInfo($userId)
    {
        if (!$userId) {
            return;
        }
        
        try {
            $user = \app\model\User::find($userId);
            if ($user) {
                $user->last_visit_time = date('Y-m-d H:i:s');
                $user->visit_count = $user->visit_count + 1;
                $user->save();
            }
        } catch (\Exception $e) {
            trace('更新用户访问信息失败: ' . $e->getMessage(), 'error');
        }
    }
} 