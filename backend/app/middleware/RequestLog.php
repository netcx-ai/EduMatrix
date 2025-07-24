<?php
declare (strict_types = 1);

namespace app\middleware;

use app\service\LogService;
use think\Response;

class RequestLog
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        // 记录请求开始时间
        $startTime = microtime(true);
        
        // 获取请求信息
        $method = $request->method();
        $url = $request->url(true);
        $ip = $request->ip();
        $userAgent = $request->header('User-Agent');
        
        // 过滤不需要记录的请求
        if ($this->shouldSkipLog($url)) {
            return $next($request);
        }
        
        // 记录请求开始
        LogService::recordApiAccess($method, $url, $request->param(), [], 0);
        
        // 执行请求
        $response = $next($request);
        
        // 计算请求耗时
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        
        // 获取响应信息
        $responseData = [];
        if ($response instanceof Response) {
            $responseData = $response->getData();
        }
        
        // 记录请求完成
        LogService::recordApiAccess($method, $url, $request->param(), $responseData, $duration);
        
        return $response;
    }
    
    /**
     * 判断是否应该跳过日志记录
     */
    private function shouldSkipLog($url)
    {
        // 跳过静态资源
        $skipPatterns = [
            '/static/',
            '/uploads/',
            '/favicon.ico',
            '/robots.txt',
            '/admin/tools/log', // 避免日志查看页面产生过多日志
            '/admin/tools/logStats',
            '/admin/tools/exportLog'
        ];
        
        foreach ($skipPatterns as $pattern) {
            if (strpos($url, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }
} 