<?php
declare (strict_types = 1);

namespace app\middleware;

use app\model\SchoolAdmin;
use app\model\School;
use app\model\User;
use app\util\JwtUtil;
use think\facade\Session;
use think\facade\Cache;
use think\facade\Log;
use think\facade\Config;

/**
 * 学校认证中间件
 * 
 * @package app\middleware
 * @author EduMatrix System
 */
class SchoolAuth
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
        try {
            // 获取学校编码
            $schoolCode = $this->getSchoolCode($request);
            
            if (!$schoolCode) {
                Log::warning("缺少学校编码参数", [
                    'ip' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'url' => $request->url()
                ]);
                
                return json([
                    'code' => 400,
                    'message' => '缺少学校编码参数'
                ]);
            }

            // 获取学校信息（使用缓存）
            $school = $this->getSchoolByCode($schoolCode);
            if (!$school) {
                Log::warning("学校不存在", [
                    'school_code' => $schoolCode,
                    'ip' => $request->ip(),
                    'url' => $request->url()
                ]);
                
                return json([
                    'code' => 404,
                    'message' => '学校不存在'
                ]);
            }

            // 检查学校状态
            if ($school->status != 1) {
                Log::warning("学校状态异常", [
                    'school_id' => $school->id,
                    'school_code' => $school->code,
                    'status' => $school->status,
                    'ip' => $request->ip()
                ]);
                
                return json([
                    'code' => 403,
                    'message' => '学校已被禁用或待审核'
                ]);
            }

            // 检查学校是否过期
            if ($school->isExpired()) {
                Log::warning("学校服务已过期", [
                    'school_id' => $school->id,
                    'school_code' => $school->code,
                    'expire_time' => $school->expire_time,
                    'ip' => $request->ip()
                ]);
                
                return json([
                    'code' => 403,
                    'message' => '学校服务已过期'
                ]);
            }

            // 将学校信息存储到请求中
            $request->school = $school;
            $request->schoolId = $school->id;

            // 处理用户认证
            $this->handleUserAuth($request, $school);

            // 记录访问日志
            $this->logAccess($request, $school);

            return $next($request);
            
        } catch (\Exception $e) {
            Log::error("SchoolAuth中间件异常: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip(),
                'url' => $request->url()
            ]);
            
            return json([
                'code' => 500,
                'message' => '服务器内部错误'
            ]);
        }
    }
    
    /**
     * 获取学校编码
     * 
     * @param \think\Request $request
     * @return string|null
     */
    private function getSchoolCode($request)
    {
        // 优先级：请求参数 > 请求头 > JWT token
        $schoolCode = $request->param('school_code') ?: $request->header('X-School-Code');
        
        // 如果没有从请求中获取到学校编码，尝试从JWT token中获取
        if (!$schoolCode) {
            $token = $request->header('Authorization') ?: $request->param('token');
            if ($token) {
                // 移除Bearer前缀
                $token = str_replace('Bearer ', '', $token);
                
                try {
                    // 验证JWT token
                    $payload = JwtUtil::verifyToken($token);
                    if ($payload && isset($payload['primary_school_id'])) {
                        // 根据学校ID获取学校编码
                        $school = School::find($payload['primary_school_id']);
                        if ($school) {
                            $schoolCode = $school->code;
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("JWT token验证失败", [
                        'error' => $e->getMessage(),
                        'ip' => $request->ip()
                    ]);
                }
            }
        }
        
        return $schoolCode;
    }
    
    /**
     * 根据编码获取学校信息（带缓存）
     * 
     * @param string $schoolCode
     * @return School|null
     */
    private function getSchoolByCode($schoolCode)
    {
        $cacheKey = 'school_info_' . md5($schoolCode);
        
        // 尝试从缓存获取
        $school = Cache::get($cacheKey);
        if ($school) {
            return $school;
        }
        
        // 从数据库获取
        $school = School::getByCode($schoolCode);
        if ($school) {
            // 缓存学校信息（5分钟）
            Cache::set($cacheKey, $school, 300);
        }
        
        return $school;
    }
    
    /**
     * 处理用户认证
     * 
     * @param \think\Request $request
     * @param School $school
     */
    private function handleUserAuth($request, $school)
    {
        $token = $request->header('Authorization') ?: $request->param('token');
        
        if ($token) {
            // 移除Bearer前缀
            $token = str_replace('Bearer ', '', $token);
            
            try {
                // 首先尝试从缓存中获取管理员信息（传统session方式）
                $adminData = Cache::get("school_admin_token:{$token}");
                
                if ($adminData && $adminData['school_id'] == $school->id) {
                    $admin = SchoolAdmin::find($adminData['admin_id']);
                    
                    if ($admin && $admin->status == 1) {
                        // 验证成功，将管理员信息存储到请求中
                        $request->schoolAdmin = $admin;
                        $request->adminId = $admin->id;
                        
                        // 更新最后访问时间（异步处理，避免影响响应速度）
                        $this->updateAdminLoginInfo($admin);
                        return; // 找到管理员，直接返回
                    }
                }
                
                // 如果缓存中没有找到，尝试JWT token验证
                $payload = JwtUtil::verifyToken($token);
                if ($payload && isset($payload['user_id'])) {
                    $user = User::find($payload['user_id']);
                    
                    if ($user && $user->status == 1) {
                        // 验证用户是否属于该学校
                        if ($user->primary_school_id == $school->id) {
                            $request->user = $user;
                            $request->userId = $user->id;
                            
                            // 检查用户是否是学校管理员
                            if ($user->user_type === \app\model\User::USER_TYPE_SCHOOL_ADMIN) {
                                // 查找对应的学校管理员记录
                                $admin = SchoolAdmin::where('school_id', $school->id)
                                    ->where(function($query) use ($user) {
                                        $query->where('username', $user->username)
                                              ->whereOr('phone', $user->phone)
                                              ->whereOr('email', $user->email);
                                    })
                                    ->where('status', 1)
                                    ->find();
                                
                                if ($admin) {
                                    $request->schoolAdmin = $admin;
                                    $request->adminId = $admin->id;
                                }
                            }
                        }
                    }
                }
                
            } catch (\Exception $e) {
                Log::warning("用户认证处理失败", [
                    'error' => $e->getMessage(),
                    'school_id' => $school->id,
                    'ip' => $request->ip()
                ]);
            }
        }
    }
    

    
    /**
     * 异步更新管理员登录信息
     * 
     * @param SchoolAdmin $admin
     */
    private function updateAdminLoginInfo($admin)
    {
        // 使用异步任务或延迟更新，避免影响响应速度
        try {
            $admin->updateLoginInfo();
        } catch (\Exception $e) {
            Log::warning("更新管理员登录信息失败", [
                'admin_id' => $admin->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * 记录访问日志
     * 
     * @param \think\Request $request
     * @param School $school
     */
    private function logAccess($request, $school)
    {
        // 只在生产环境记录详细日志
        if (Config::get('app.debug') === false) {
            $logData = [
                'school_id' => $school->id,
                'school_code' => $school->code,
                'ip' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'url' => $request->url(),
                'method' => $request->method(),
                'user_id' => $request->userId ?? 0,
                'admin_id' => $request->adminId ?? 0
            ];
            
            Log::info("学校访问日志", $logData);
        }
    }
} 