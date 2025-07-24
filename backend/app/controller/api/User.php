<?php
declare (strict_types = 1);

namespace app\controller\api;

use app\controller\api\BaseController;
use app\model\User as UserModel;
use app\model\UserLog;
use app\service\SmsService;
use app\service\EmailService;
use think\facade\Validate;
use think\facade\Cache;
use think\Request;
use think\App;
use app\util\JwtUtil;
use app\model\School;

class User extends BaseController
{
    protected $smsService;
    protected $emailService;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->smsService = new SmsService();
        $this->emailService = new EmailService();
    }

    /**
     * 用户注册
     */
    public function register(Request $request)
    {
        $data = $request->post();
        
        // 验证数据
        try {
            validate([
                'username' => 'require|min:3',
                'phone' => 'require|mobile',
                'code' => 'require|number|length:6',
                'password' => 'require|min:6',
                'confirmPassword' => 'require|confirm:password',
                'email' => 'require|email'
            ])->check($data);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
        
        // 验证验证码（只用缓存）
        if (!$this->smsService->verifyCode($data['phone'], $data['code'])) {
            return $this->error('验证码错误或已过期');
        }
        
        // 检查手机号是否已注册
        if (UserModel::where('phone', $data['phone'])->find()) {
            return $this->error('该手机号已注册');
        }
        // 检查用户名是否已注册
        if (UserModel::where('username', $data['username'])->find()) {
            return $this->error('该用户名已注册');
        }
        // 检查邮箱是否已注册
        if (UserModel::where('email', $data['email'])->find()) {
            return $this->error('该邮箱已注册');
        }
        
        // 创建用户
        $user = new UserModel;
        $user->username = $data['username'];
        $user->phone = $data['phone'];
        $user->email = $data['email'];
        $user->setAttr('password', $data['password']);  // 使用模型的 setAttr 方法
        $user->save();
        
        // 发送注册成功邮件
        $this->emailService->sendRegisterSuccess($data['email'], $data['username']);
        
        // 生成token
        $token = md5(uniqid((string)time(), true));
        
        // 保存token到缓存
        Cache::set('user_token_' . $token, $user->id, 7 * 24 * 3600); // 7天有效期
        
        return $this->success([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'phone' => $user->phone,
                'username' => $user->username,
                'user_type' => $user->user_type,
                'primary_school_id' => $user->primary_school_id
            ]
        ], '注册成功');
    }

    /**
     * 用户登录
     */
    public function login(Request $request)
    {
        $phone = $request->post('phone');
        $password = $request->post('password');
        
        // 验证数据
        try {
            validate([
                'phone' => 'require|mobile',
                'password' => 'require|min:6'
            ])->check(['phone' => $phone, 'password' => $password]);
        } catch (\Exception $e) {
            UserLog::record(null, UserLog::STATUS_FAILED, '数据验证失败：' . $e->getMessage());
            return $this->error($e->getMessage());
        }
        
        // 查找用户
        $user = UserModel::where('phone', $phone)->find();
        if (!$user) {
            UserLog::record(null, UserLog::STATUS_FAILED, '用户不存在');
            return $this->error('用户不存在');
        }
        
        // 验证密码
        try {
            $user->verifyPassword($password);
        } catch (\Exception $e) {
            UserLog::record($user->id, UserLog::STATUS_FAILED, '密码错误');
            return $this->error($e->getMessage());
        }
        
        // 检查用户状态
        if ($user->status != 1) {
            UserLog::record($user->id, UserLog::STATUS_FAILED, '账号已被禁用');
            return $this->error('账号已被禁用');
        }
        
        // 生成 JWT token（统一认证）
        $payload = [
            'user_id' => $user->id,
            'user_type' => $user->user_type,
            'username' => $user->username,
            'primary_school_id' => $user->primary_school_id,
        ];
        $token = JwtUtil::createToken($payload);
        
        // 更新用户访问信息
        $user->last_visit_time = date('Y-m-d H:i:s');
        $user->visit_count += 1;
        $user->save();
        
        // 记录成功登录
        UserLog::record($user->id, UserLog::STATUS_SUCCESS, null, $user->user_type);
        
        // 查找学校名称
        $schoolName = '';
        if ($user->primary_school_id) {
            $school = School::find($user->primary_school_id);
            $schoolName = $school ? $school->name : '';
        }
        return $this->success([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'phone' => $user->phone,
                'username' => $user->username,
                'user_type' => $user->user_type,
                'primary_school_id' => $user->primary_school_id,
                'school_name' => $schoolName
            ]
        ], '登录成功');
    }

    /**
     * 获取用户信息
     */
    public function info(Request $request)
    {
        $userId = $this->getUserId();
        $user = UserModel::find($userId);
        
        if (!$user) {
            return $this->error('用户不存在');
        }
        
        return $this->success([
            'phone' => $user->phone,
            'nickname' => $user->nickname,
            'avatar' => $user->avatar
        ]);
    }

    /**
     * 退出登录
     */
    public function logout(Request $request)
    {
        try {
            // JWT是无状态的，前端删除token即可
            // 如果需要记录退出日志，可以在这里添加
            return $this->success(null, '退出成功');
        } catch (\Exception $e) {
            return $this->error('退出失败：' . $e->getMessage());
        }
    }

    /**
     * 重置密码
     */
    public function resetPassword(Request $request)
    {
        $data = $request->post();
        
        // 验证数据
        try {
            validate([
                'phone' => 'require|mobile',
                'code' => 'require|number|length:6',
                'password' => 'require|min:6',
                'confirmPassword' => 'require|confirm:password'
            ])->check($data);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
        
        // 验证验证码
        if (!$this->smsService->verifyCode($data['phone'], $data['code'])) {
            return $this->error('验证码错误或已过期');
        }
        
        // 查找用户
        $user = UserModel::where('phone', $data['phone'])->find();
        if (!$user) {
            return $this->error('用户不存在');
        }
        
        // 更新密码
        $user->password = $data['password']; // 使用模型的 setPasswordAttr 方法自动处理密码加密
        $user->save();
        
        return $this->success(null, '密码重置成功');
    }
} 