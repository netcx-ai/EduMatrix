<?php
declare (strict_types = 1);

namespace app\controller\api;

use think\App;
use think\facade\Cache;

class BaseController
{
    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * 获取当前登录用户ID
     */
    protected function getUserId()
    {
        $token = request()->header('token');
        if (!$token) {
            return null;
        }
        
        return Cache::get('user_token_' . $token);
    }
    
    /**
     * 返回成功响应
     */
    protected function success($data = [], $msg = 'success', $code = 200)
    {
        return json([
            'code' => $code,
            'message' => $msg,
            'data' => $data
        ]);
    }
    
    /**
     * 返回错误响应
     */
    protected function error($msg = 'error', $code = 400, $data = [])
    {
        return json([
            'code' => $code,
            'message' => $msg,
            'data' => $data
        ]);
    }
} 