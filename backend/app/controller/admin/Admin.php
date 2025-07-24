<?php
declare (strict_types = 1);

namespace app\controller\admin;

use app\BaseController;
use think\Request;
use app\model\Admin as AdminModel;
use app\model\AdminLog;
use app\model\Role;
use app\util\JwtUtil;
use think\facade\Validate;
use think\facade\Cache;
use app\service\SmsService;
use think\facade\Session;
use think\facade\Cookie;
use think\facade\View;

class Admin extends BaseController
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * 显示登录页面
     */
    public function loginPage()
    {
        return view('admin/login');
    }

    /**
     * 管理员登录
     */
    public function login(Request $request)
    {
        $data = $request->post();
        
        // 验证必填字段
        if (empty($data['username']) || empty($data['password'])) {
            return $this->error('用户名和密码不能为空');
        }
        
        // 查找管理员
        $admin = AdminModel::where('username', $data['username'])->find();
        if (!$admin) {
            return $this->error('用户名或密码错误');
        }
        
        // 验证密码
        if (!password_verify($data['password'], $admin->password)) {
            return $this->error('用户名或密码错误');
        }
        
        // 检查账号状态
        if ($admin->status != 1) {
            return $this->error('账号已被禁用');
        }
        
        // 检查是否需要短信验证
        $needSms = false;
        if (!in_array($request->ip(), config('admin.whitelist_ips', []))) {
            $loginAttempts = Cache::get('admin_login_attempts_' . $request->ip(), 0);
            if ($loginAttempts >= 3) {
                $needSms = true;
                
                // 如果提供了验证码，则验证
                if (!empty($data['code'])) {
                    $smsService = new SmsService();
                    if (!$smsService->verifyCode($admin->phone, $data['code'])) {
                        return $this->error('验证码错误');
                    }
                } else {
                    // 发送验证码
                    $smsService = new SmsService();
                    $code = $smsService->sendCode($admin->phone);
                    return $this->success(['need_sms' => true], '请输入验证码');
                }
            }
        }
        
        // 生成token
        $token = md5(uniqid((string)time(), true));
        
        // 更新登录信息
        $admin->last_login_time = date('Y-m-d H:i:s');
        $admin->last_login_ip = $request->ip();
        $admin->save();
        
        // 保存token
        Cache::set('admin_token_' . $token, $admin->id, 7200);
        
        // 设置session
        Session::set('admin_id', $admin->id);
        Session::set('admin_token', $token);
        
        // 设置cookie
        Cookie::set('admin_id', $admin->id, 7200);
        Cookie::set('admin_token', $token, 7200);
        
        // 清除登录尝试次数
        Cache::delete('admin_login_attempts_' . $request->ip());
        
        // 写入管理员操作日志
        try {
            \app\model\AdminLog::create([
                'admin_id' => $admin->id,
                'action' => 'login',
                'module' => 'admin',
                'content' => '管理员登录成功',
                'ip' => $request->ip(),
                'user_agent' => $request->header('user-agent'),
                'create_time' => date('Y-m-d H:i:s')
            ]);
        } catch (\Throwable $e) {
            \think\facade\Log::error('管理员操作日志写入失败：' . $e->getMessage());
        }
        
        return $this->success([
            'token' => $token,
            'need_sms' => $needSms
        ], '登录成功');
    }

    /**
     * 管理员列表
     */
    public function index(Request $request)
    {
        try {
            // 如果是AJAX请求（layui table默认是GET），返回JSON
            if ($request->isAjax()) {
                $page = (int)$request->param('page', 1);
                $limit = (int)$request->param('limit', 10);
                $keyword = $request->param('keyword', '');
                $status = $request->param('status', '');

                $query = AdminModel::with(['roles']);

                // 关键词搜索
                if (!empty($keyword)) {
                    $query->where('username|real_name|phone|email', 'like', "%{$keyword}%");
                }

                // 状态筛选
                if ($status !== '') {
                    $query->where('status', $status);
                }

                // 克隆一份用于统计总数
                $countQuery = clone $query;
                $total = $countQuery->count();

                // 查分页数据并转为数组
                $list = $query->order('id', 'desc')
                    ->page($page, $limit)
                    ->select()
                    ->toArray();

                return json([
                    'code' => 0,
                    'msg' => '',
                    'count' => $total,
                    'data' => $list
                ]);
            }

            // 非AJAX请求，返回页面
            return View::fetch('admin/admin/index');
            
        } catch (\Exception $e) {
            // 记录错误日志
            \think\facade\Log::error('Admin index error: ' . $e->getMessage());
            \think\facade\Log::error('Stack trace: ' . $e->getTraceAsString());
            
            if ($request->isAjax()) {
                return json([
                    'code' => 1,
                    'msg' => '请求异常：' . $e->getMessage(),
                    'count' => 0,
                    'data' => []
                ]);
            } else {
                return $this->error('页面加载失败：' . $e->getMessage());
            }
        }
    }

    /**
     * 添加管理员
     */
    public function add(Request $request)
    {
        if ($request->isPost()) {
            $data = $request->post();
            
            // 验证数据
            try {
                validate([
                    'username' => 'require|max:50|unique:admin',
                    'password' => 'require|min:6',
                    'real_name' => 'require|max:50',
                    'phone' => 'mobile',
                    'email' => 'email',
                    'role_ids' => 'array',
                    'status' => 'require|in:0,1',
                ])->check($data);
            } catch (\Exception $e) {
                return json(['code' => 1, 'msg' => $e->getMessage()]);
            }
            
            // 创建管理员
            $admin = new AdminModel;
            $admin->username = $data['username'];
            $admin->password = $data['password'];
            $admin->real_name = $data['real_name'];
            $admin->phone = $data['phone'] ?? '';
            $admin->email = $data['email'] ?? '';
            $admin->avatar = $data['avatar'] ?? '';
            $admin->status = $data['status'];
            $admin->save();
            
            // 分配角色
            if (!empty($data['role_ids'])) {
                $admin->roles()->attach($data['role_ids']);
            }
            
            return json(['code' => 0, 'msg' => '添加成功']);
        }
        
        // 获取角色列表
        $roles = Role::where('status', 1)->select();
        
        return View::fetch('admin/admin/add', [
            'roles' => $roles
        ]);
    }

    /**
     * 编辑管理员
     */
    public function edit(Request $request)
    {
        $id = $request->param('id/d');
        $admin = AdminModel::with(['roles'])->find($id);
        
        if (!$admin) {
            return $this->error('管理员不存在');
        }
        
        if ($request->isPost()) {
            $data = $request->post();
            
            // 验证数据
            try {
                validate([
                    'username' => 'require|max:50|unique:admin,username,' . $id,
                    'real_name' => 'require|max:50',
                    'phone' => 'mobile',
                    'email' => 'email',
                    'role_ids' => 'array',
                    'status' => 'require|in:0,1',
                ])->check($data);
            } catch (\Exception $e) {
                return json(['code' => 1, 'msg' => $e->getMessage()]);
            }
            
            // 更新管理员信息
            $admin->username = $data['username'];
            $admin->real_name = $data['real_name'];
            $admin->phone = $data['phone'] ?? '';
            $admin->email = $data['email'] ?? '';
            $admin->avatar = $data['avatar'] ?? '';
            $admin->status = $data['status'];
            $admin->save();
            
            // 更新密码（如果提供）
            if (!empty($data['password'])) {
                if (strlen($data['password']) < 6) {
                    return json(['code' => 1, 'msg' => '密码长度不能小于6位']);
                }
                $admin->password = $data['password'];
                $admin->save();
            }
            
            // 更新角色
            $admin->roles()->detach();
            if (!empty($data['role_ids'])) {
                $admin->roles()->attach($data['role_ids']);
            }
            
            return json(['code' => 0, 'msg' => '更新成功']);
        }
        
        // 获取角色列表
        $roles = Role::where('status', 1)->select();
        
        // 获取当前管理员已分配的角色ID数组
        $admin_role_ids = [];
        if ($admin->roles) {
            foreach ($admin->roles as $role) {
                $admin_role_ids[] = $role->id;
            }
        }
        
        return View::fetch('admin/admin/edit', [
            'admin' => $admin,
            'roles' => $roles,
            'admin_role_ids' => $admin_role_ids
        ]);
    }

    /**
     * 删除管理员
     */
    public function delete(Request $request)
    {
        $id = $request->param('id/d');
        $admin = AdminModel::find($id);
        
        if (!$admin) {
            return json(['code' => 1, 'msg' => '管理员不存在']);
        }
        
        // 不能删除自己
        if ($admin->id == Session::get('admin_id')) {
            return json(['code' => 1, 'msg' => '不能删除自己']);
        }
        
        // 不能删除超级管理员
        if ($admin->username == 'admin') {
            return json(['code' => 1, 'msg' => '不能删除超级管理员']);
        }
        
        $admin->delete();
        
        return json(['code' => 0, 'msg' => '删除成功']);
    }

    /**
     * 修改状态
     */
    public function changeStatus(Request $request)
    {
        $id = $request->param('id/d');
        $status = $request->param('status/d');
        
        $admin = AdminModel::find($id);
        if (!$admin) {
            return json(['code' => 1, 'msg' => '管理员不存在']);
        }
        
        // 不能禁用自己
        if ($admin->id == Session::get('admin_id')) {
            return json(['code' => 1, 'msg' => '不能禁用自己']);
        }
        
        $admin->status = $status;
        $admin->save();
        
        return json(['code' => 0, 'msg' => '状态修改成功']);
    }

    /**
     * 获取管理员信息
     */
    public function info()
    {
        $adminId = request()->adminId ?? Session::get('admin_id') ?? Cookie::get('admin_id');
        
        if (!$adminId) {
            return $this->error('未找到登录信息');
        }
        
        $admin = AdminModel::find($adminId);
        if (!$admin) {
            return $this->error('管理员不存在');
        }
        
        return $this->success([
            'id' => $admin->id,
            'username' => $admin->username,
            'real_name' => $admin->real_name,
            'phone' => $admin->phone,
            'email' => $admin->email,
            'avatar' => $admin->avatar,
            'role' => $admin->role
        ]);
    }

    /**
     * 修改密码页面
     */
    public function changePasswordPage()
    {
        return View::fetch('admin/change_password');
    }

    /**
     * 修改密码
     */
    public function changePassword(Request $request)
    {
        $data = $request->post();
        
        // 验证必填字段
        if (empty($data['old_password']) || empty($data['new_password'])) {
            return $this->error('参数错误');
        }
        
        // 验证新密码长度
        if (strlen($data['new_password']) < 6) {
            return $this->error('新密码长度不能小于6位');
        }
        
        // 从中间件注入的adminId获取，或者从session/cookie获取
        $adminId = $request->adminId ?? Session::get('admin_id') ?? Cookie::get('admin_id');
        
        if (!$adminId) {
            return $this->error('未找到登录信息，请重新登录');
        }
        
        $admin = AdminModel::find($adminId);
        if (!$admin) {
            return $this->error('管理员不存在');
        }
        
        // 验证旧密码
        if (!password_verify($data['old_password'], $admin->password)) {
            return $this->error('旧密码错误');
        }
        
        // 更新密码（模型会自动加密）
        $admin->password = $data['new_password'];
        $admin->save();
        
        return $this->success(null, '密码修改成功');
    }

    /**
     * 获取操作日志
     */
    public function getLogs(Request $request)
    {
        $page = $request->param('page', 1);
        $limit = $request->param('limit', 10);
        
        $logs = \app\model\AdminLog::order('id', 'desc')
            ->page($page, $limit)
            ->select();
            
        $total = \app\model\AdminLog::count();
        
        return $this->success([
            'list' => $logs,
            'total' => $total
        ]);
    }

    /**
     * 登录日志列表（兼容页面和AJAX请求）
     */
    public function loginLog(Request $request)
    {
        try {
            if ($request->isAjax()) {
                $page = (int)$request->param('page', 1);
                $limit = (int)$request->param('limit', 10);
                $keyword = $request->param('keyword', '');
                $admin_id = $request->param('admin_id', '');
                $start_time = $request->param('start_time', '');
                $end_time = $request->param('end_time', '');

                // 简化查询，先确保基本功能正常
                $query = \app\model\AdminLog::with(['admin'])->where('action', 'login');

                // 管理员筛选
                if (!empty($admin_id)) {
                    $query->where('admin_id', $admin_id);
                }

                // 时间范围筛选
                if (!empty($start_time)) {
                    $query->where('create_time', '>=', $start_time . ' 00:00:00');
                }
                if (!empty($end_time)) {
                    $query->where('create_time', '<=', $end_time . ' 23:59:59');
                }

                // 统计总数
                $total = $query->count();

                // 查询分页数据
                $list = $query->order('create_time', 'desc')
                    ->page($page, $limit)
                    ->select()
                    ->each(function ($item) {
                        // 格式化数据
                        $item->admin_name = $item->admin ? $item->admin->real_name : '未知';
                        $item->admin_username = $item->admin ? $item->admin->username : '未知';
                        return $item;
                    })
                    ->toArray();

                return json([
                    'code' => 0,
                    'msg' => '',
                    'count' => $total,
                    'data' => $list
                ]);
            }

            // 获取管理员列表（用于筛选）
            $admins = AdminModel::where('status', 1)->field('id,username,real_name')->select();
            return View::fetch('admin/admin_login_log/index', ['admins' => $admins]);

        } catch (\Exception $e) {
            if ($request->isAjax()) {
                \think\facade\Log::error('LoginLog error: ' . $e->getMessage());
                return json([
                    'code' => 1,
                    'msg' => '请求异常：' . $e->getMessage(),
                    'count' => 0,
                    'data' => []
                ]);
            } else {
                return $this->error('页面加载失败：' . $e->getMessage());
            }
        }
    }

    /**
     * 清理登录日志
     */
    public function cleanLoginLog(Request $request)
    {
        try {
            $days = (int)$request->post('days', 30);
            
            if ($days <= 0) {
                return json(['code' => 1, 'msg' => '天数必须大于0']);
            }

            $deleteTime = date('Y-m-d H:i:s', strtotime("-{$days} days"));
            $count = \app\model\AdminLog::where('action', 'login')
                ->where('create_time', '<', $deleteTime)
                ->delete();

            return json(['code' => 0, 'msg' => "成功清理 {$count} 条登录日志"]);

        } catch (\Exception $e) {
            \think\facade\Log::error('Clean login log error: ' . $e->getMessage());
            return json(['code' => 1, 'msg' => '清理失败：' . $e->getMessage()]);
        }
    }

    /**
     * 导出登录日志
     */
    public function exportLoginLog(Request $request)
    {
        try {
            $keyword = $request->param('keyword', '');
            $admin_id = $request->param('admin_id', '');
            $start_time = $request->param('start_time', '');
            $end_time = $request->param('end_time', '');

            $query = \app\model\AdminLog::with(['admin'])->where('action', 'login');

            // 应用筛选条件
            if (!empty($admin_id)) {
                $query->where('admin_id', $admin_id);
            }

            if (!empty($start_time)) {
                $query->where('create_time', '>=', $start_time . ' 00:00:00');
            }
            if (!empty($end_time)) {
                $query->where('create_time', '<=', $end_time . ' 23:59:59');
            }

            $list = $query->order('create_time', 'desc')->select()->toArray();

            // 设置响应头，直接下载CSV文件
            $filename = 'login_log_' . date('Y-m-d_H-i-s') . '.csv';
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
            
            // 输出BOM，解决中文乱码问题
            echo "\xEF\xBB\xBF";
            
            // 创建输出流
            $output = fopen('php://output', 'w');
            
            // 写入CSV头部
            fputcsv($output, ['ID', '管理员', '用户名', '操作', '模块', '内容', 'IP地址', '用户代理', '创建时间']);
            
            // 写入数据
            foreach ($list as $item) {
                fputcsv($output, [
                    $item['id'],
                    $item['admin']['real_name'] ?? '未知',
                    $item['admin']['username'] ?? '未知',
                    $item['action'],
                    $item['module'],
                    $item['content'],
                    $item['ip'],
                    $item['user_agent'],
                    $item['create_time']
                ]);
            }
            
            fclose($output);
            exit;

        } catch (\Exception $e) {
            \think\facade\Log::error('Export login log error: ' . $e->getMessage());
            return json(['code' => 1, 'msg' => '导出失败：' . $e->getMessage()]);
        }
    }

    /**
     * 发送短信验证码
     */
    public function sendCode()
    {
        try {
            $data = request()->post();
            
            // 验证数据
            $validate = Validate::rule([
                'username' => 'require',
            ]);

            if (!$validate->check($data)) {
                return json(['code' => 400, 'message' => $validate->getError()]);
            }

            // 查找管理员
            $admin = AdminModel::where('username', $data['username'])->find();
            if (!$admin) {
                return json(['code' => 400, 'message' => '管理员不存在']);
            }

            // 生成验证码
            $code = mt_rand(100000, 999999);
            Cache::set('admin_sms_code:' . $admin->id, $code, 300); // 5分钟有效

            // 调用短信发送接口
            $smsResult = $this->smsService->sendCode($admin->phone);
            if ($smsResult['code'] != 200) {
                return json($smsResult); // 如果短信发送失败，直接返回错误信息
            }

            return json([
                'code' => 200,
                'message' => '验证码已发送',
                'data' => [
                    'phone' => $admin->phone
                ]
            ]);
        } catch (\Exception $e) {
            return json(['code' => 500, 'message' => $e->getMessage()]);
        }
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        $token = Session::get('admin_token');
        if ($token) {
            Cache::delete('admin_token_' . $token);
        }
        
        Session::clear();
        Cookie::delete('admin_id');
        Cookie::delete('admin_token');
        
        return $this->success(null, '退出成功');
    }
} 