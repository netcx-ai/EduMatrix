<?php
declare (strict_types = 1);

namespace app\middleware;

use think\facade\Session;
use think\facade\Cache;
use think\facade\Cookie;
use think\Response;

class AdminAuth
{
    /**
     * 处理请求
     * @param \think\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        // 获取session中的管理员ID和token
        $adminId = Session::get('admin_id');
        $token = Session::get('admin_token');
        
        // 如果session中没有，尝试从cookie中获取
        if (!$adminId || !$token) {
            $adminId = Cookie::get('admin_id');
            $token = Cookie::get('admin_token');
        }
        
        if (!$adminId || !$token) {
            return $this->unauthorizedResponse($request);
        }

        // 验证token是否有效
        $cacheToken = Cache::get('admin_token_' . $token);
        if (!$cacheToken || $cacheToken != $adminId) {
            return $this->unauthorizedResponse($request);
        }

        // 将管理员信息注入到请求中
        $request->adminId = $adminId;

        return $next($request);
    }
    
    /**
     * 返回未授权响应
     * @param \think\Request $request
     * @return Response
     */
    protected function unauthorizedResponse($request)
    {
        if ($request->isAjax()) {
            // Ajax请求返回JSON
            return json(['code' => 401, 'message' => '请先登录']);
        } else {
            // 普通请求重定向到登录页面
            return redirect('/admin/login');
        }
    }
} 