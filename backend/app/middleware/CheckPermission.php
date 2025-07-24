<?php
declare (strict_types = 1);

namespace app\middleware;

use app\model\Admin;

class CheckPermission
{
    /**
     * 处理请求
     * @param \think\Request $request
     * @param \Closure $next
     * @param string $permission 权限编码
     * @return mixed
     */
    public function handle($request, \Closure $next, $permission)
    {
        $admin = Admin::find($request->adminId);
        
        if (!$admin) {
            return json(['code' => 404, 'message' => '管理员不存在']);
        }

        if (!$admin->hasPermission($permission)) {
            return json(['code' => 403, 'message' => '没有操作权限']);
        }

        return $next($request);
    }
} 