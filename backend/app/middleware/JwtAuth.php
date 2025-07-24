<?php
declare(strict_types=1);

namespace app\middleware;

use app\util\JwtUtil;
use app\model\User;
use app\model\Teacher;
use Closure;
use think\Request;
use think\Response;

/**
 * JWT认证中间件
 */
class JwtAuth
{
    /**
     * 处理请求
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle($request, Closure $next)
    {
        // 获取Authorization头
        $authorization = $request->header('Authorization');
        
        if (!$authorization) {
            return $this->unauthorizedResponse('缺少认证token');
        }
        
        // 移除Bearer前缀
        if (strpos($authorization, 'Bearer ') === 0) {
            $token = substr($authorization, 7);
        } else {
            $token = $authorization;
        }
        
        if (empty($token)) {
            return $this->unauthorizedResponse('Token格式错误');
        }
        
        // 验证JWT token
        $payload = JwtUtil::verifyToken($token);
        
        if ($payload === false) {
            return $this->unauthorizedResponse('Token无效或已过期');
        }
        
        // 获取用户信息
        $userId = $payload['user_id'] ?? null;
        $userType = $payload['user_type'] ?? null;
        
        if (!$userId || !$userType) {
            return $this->unauthorizedResponse('Token数据不完整');
        }
        
        // 获取用户信息（统一从User表获取）
        $user = User::find($userId);
        
        if (!$user) {
            return $this->unauthorizedResponse('用户不存在');
        }
        
        // 检查用户状态
        if ($user->status != 1) {
            return $this->unauthorizedResponse('用户已被禁用');
        }
        
        // 验证用户类型
        if ($user->user_type !== $userType) {
            return $this->unauthorizedResponse('用户类型不匹配');
        }
        
        // 将用户信息注入到请求中
        $request->user = $user;
        $request->userId = $userId;
        $request->userType = $userType;
        
        return $next($request);
    }
    
    /**
     * 返回未授权响应
     * @param string $message
     * @return Response
     */
    protected function unauthorizedResponse($message = '未授权访问')
    {
        return json([
            'code' => 401,
            'message' => $message,
            'data' => null
        ], 401);
    }
} 