<?php
declare(strict_types=1);

namespace app\controller\api\teacher;

use app\controller\api\BaseController;
use app\model\User;
use app\model\Teacher;
use app\model\UserLog;
use app\util\JwtUtil;
use think\Request;
use think\facade\Validate;

/**
 * 教师端认证控制器
 */
class AuthController extends BaseController
{
    /**
     * 教师登录
     */
    public function login(Request $request)
    {
        try {
            $data = $request->post();
            
            // 验证数据
            $validate = Validate::rule([
                'username' => 'require',  // 可以是手机号、邮箱或用户名
                'password' => [
                    'require',
                    'min' => 8,
                    'max' => 32,
                    'regex' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'
                ]
            ]);
            
            if (!$validate->check($data)) {
                UserLog::record($user->id ?? null, UserLog::STATUS_FAILED, $validate->getError());
                return $this->error($validate->getError());
            }
            
            $username = $data['username'];
            $password = $data['password'];
            
            // 查找用户（支持手机号、邮箱、用户名登录）
            $user = User::where(function($query) use ($username) {
                $query->where('username', $username)
                      ->whereOr('phone', $username)
                      ->whereOr('email', $username);
            })->find();
            
            if (!$user) {
                UserLog::record(null, UserLog::STATUS_FAILED, '用户不存在');
                return $this->error('用户不存在');
            }
            

            
            // 检查用户类型
            if ($user->user_type !== User::USER_TYPE_TEACHER) {
                UserLog::record($user->id, UserLog::STATUS_FAILED, '该账号不是教师账号');
                return $this->error('该账号不是教师账号');
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
            
            // 获取教师信息
            $teacher = $user->teacher;
            if (!$teacher) {
                UserLog::record($user->id, UserLog::STATUS_FAILED, '教师信息不存在');
                return $this->error('教师信息不存在');
            }
            
            // 检查教师状态
            if ($teacher->status != 1) {
                UserLog::record($user->id, UserLog::STATUS_FAILED, '教师账号未激活或已被禁用');
                return $this->error('教师账号未激活或已被禁用');
            }
            
            // 生成JWT token
            $payload = [
                'user_id' => $user->id,
                'user_type' => 'teacher',
                'teacher_id' => $teacher->id,
                'school_id' => $user->primary_school_id,
                'username' => $user->username
            ];
            
            $token = JwtUtil::createToken($payload);
            
            // 更新最后登录时间
            $user->last_visit_time = date('Y-m-d H:i:s');
            $user->visit_count += 1;
            $user->save();
            
            $teacher->last_login_time = date('Y-m-d H:i:s');
            $teacher->save();
            
            // 记录成功登录
            UserLog::record($user->id, UserLog::STATUS_SUCCESS, null, $user->user_type);
            
            return $this->success([
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'real_name' => $user->real_name,
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'user_type' => $user->user_type,
                    'primary_school_id' => $user->primary_school_id
                ],
                'teacher' => [
                    'id' => $teacher->id,
                    'teacher_no' => $teacher->teacher_no,
                    'school_id' => $teacher->school_id,
                    'college_id' => $teacher->college_id,
                    'status' => $teacher->status,
                    'is_verified' => $teacher->is_verified
                ]
            ], '登录成功');
            
        } catch (\Exception $e) {
            UserLog::record($user->id ?? null, UserLog::STATUS_FAILED, $e->getMessage());
            return $this->error('登录失败：' . $e->getMessage());
        }
    }
    
    /**
     * 获取教师信息
     */
    public function info(Request $request)
    {
        try {
            $user = $request->user;
            $teacher = $user->teacher;
            
            if (!$teacher) {
                return $this->error('教师信息不存在');
            }
            
            return $this->success([
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'real_name' => $user->real_name,
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'user_type' => $user->user_type,
                    'primary_school_id' => $user->primary_school_id,
                    'teacher_no' => $user->teacher_no,
                    'create_time' => $user->create_time,
                    'last_visit_time' => $user->last_visit_time
                ],
                'teacher' => [
                    'id' => $teacher->id,
                    'teacher_no' => $teacher->teacher_no,
                    'school_id' => $teacher->school_id,
                    'college_id' => $teacher->college_id,
                    'title' => $teacher->title,
                    'department' => $teacher->department,
                    'position' => $teacher->position,
                    'status' => $teacher->status,
                    'is_verified' => $teacher->is_verified,
                    'last_login_time' => $teacher->last_login_time
                ]
            ]);
            
        } catch (\Exception $e) {
            return $this->error('获取用户信息失败：' . $e->getMessage());
        }
    }
    
    /**
     * 教师注册
     */
    public function register(Request $request)
    {
        try {
            $data = $request->post();
            
            // 验证数据
            $validate = Validate::rule([
                'username' => 'require|max:50|unique:user',
                'real_name' => 'require|max:50',
                'phone' => 'require|mobile|unique:user',
                'email' => 'require|email|unique:user',
                'password' => 'require|min:6',
                'school_id' => 'require|integer',
                'college_id' => 'integer',
                'teacher_no' => 'max:50'
            ]);
            
            if (!$validate->check($data)) {
                return $this->error($validate->getError());
            }
            
            // 开启事务
            \think\facade\Db::startTrans();
            
            try {
                // 1. 创建用户记录
                $user = new User();
                $user->username = $data['username'];
                $user->real_name = $data['real_name'];
                $user->phone = $data['phone'];
                $user->email = $data['email'];
                $user->password = $data['password'];  // 自动加密
                $user->user_type = User::USER_TYPE_TEACHER;
                $user->primary_school_id = $data['school_id'];
                $user->teacher_no = $data['teacher_no'] ?? '';
                $user->status = 1;  // 用户状态正常
                $user->save();
                
                // 2. 创建教师记录
                $teacher = new Teacher();
                $teacher->user_id = $user->id;
                $teacher->school_id = $data['school_id'];
                $teacher->college_id = $data['college_id'] ?? null;
                $teacher->teacher_no = $data['teacher_no'] ?? 'T' . date('Ymd') . str_pad($user->id, 4, '0', STR_PAD_LEFT);
                $teacher->real_name = $data['real_name'];
                $teacher->phone = $data['phone'];
                $teacher->email = $data['email'];
                $teacher->status = 2;  // 待审核状态
                $teacher->is_verified = 0;
                $teacher->save();
                
                // 更新用户的teacher_no
                if (empty($data['teacher_no'])) {
                    $user->teacher_no = $teacher->teacher_no;
                    $user->save();
                }
                
                \think\facade\Db::commit();
                
                return $this->success([
                    'user_id' => $user->id,
                    'teacher_id' => $teacher->id,
                    'teacher_no' => $teacher->teacher_no
                ], '注册成功，请等待学校审核');
                
            } catch (\Exception $e) {
                \think\facade\Db::rollback();
                throw $e;
            }
            
        } catch (\Exception $e) {
            return $this->error('注册失败：' . $e->getMessage());
        }
    }
    
    /**
     * 教师登出
     */
    public function logout(Request $request)
    {
        try {
            // JWT是无状态的，前端删除token即可
            return $this->success(null, '退出成功');
            
        } catch (\Exception $e) {
            return $this->error('退出失败：' . $e->getMessage());
        }
    }

    /**
     * 修改密码
     */
    public function changePassword(Request $request)
    {
        try {
            $data = $request->post();
            
            // 验证数据
            $validate = Validate::rule([
                'old_password' => 'require|min:6',
                'new_password' => 'require|min:6',
                'confirm_password' => 'require|confirm:new_password'
            ]);
            
            if (!$validate->check($data)) {
                return $this->error($validate->getError());
            }
            
            $user = $request->user;
            
            // 验证旧密码
            try {
                $user->verifyPassword($data['old_password']);
            } catch (\Exception $e) {
                return $this->error('原密码错误');
            }
            
            // 检查新密码是否与旧密码相同
            if ($data['old_password'] === $data['new_password']) {
                return $this->error('新密码不能与原密码相同');
            }
            
            // 更新密码
            $user->password = $data['new_password']; // 自动加密
            $user->last_password_change = date('Y-m-d H:i:s');
            $user->save();
            
            return $this->success(null, '密码修改成功');
            
        } catch (\Exception $e) {
            return $this->error('密码修改失败：' . $e->getMessage());
        }
    }
} 