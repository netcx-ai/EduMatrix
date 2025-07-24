<?php
declare (strict_types = 1);

namespace app\controller\api\school;

use app\BaseController;
use app\model\User;
use app\model\SchoolAdmin;
use app\model\School;
use think\facade\Validate;
use think\facade\Log;

class ProfileController extends BaseController
{
    /**
     * 获取个人信息
     */
    public function index()
    {
        $user = $this->request->user;
        
        try {
            // 获取学校名称
            $schoolName = '';
            if ($user->primary_school_id) {
                $school = School::find($user->primary_school_id);
                $schoolName = $school ? $school->name : '';
            }
            // 获取用户基本信息
            $userInfo = [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
                'real_name' => $user->real_name ?? '',
                'avatar' => $user->avatar ?? '',
                'user_type' => $user->user_type,
                'school_id' => $user->school_id,
                'primary_school_id' => $user->primary_school_id,
                'school_name' => $schoolName,
                'last_login_time' => $user->last_login_time,
                'create_time' => $user->create_time
            ];
            
            return json(['code' => 200, 'data' => $userInfo]);
            
        } catch (\Exception $e) {
            Log::error("获取个人信息失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '获取个人信息失败']);
        }
    }
    
    /**
     * 更新个人信息
     */
    public function update()
    {
        $user = $this->request->user;
        $data = $this->request->post();
        
        // 验证参数
        $validate = Validate::rule([
            'real_name' => 'require|length:2,50',
            'email' => 'require|email',
            'phone' => 'require|length:11,20',
            'avatar' => 'url'
        ])->message([
            'real_name.require' => '真实姓名不能为空',
            'real_name.length' => '真实姓名长度必须在2-50个字符之间',
            'email.require' => '邮箱不能为空',
            'email.email' => '邮箱格式不正确',
            'phone.require' => '手机号不能为空',
            'phone.length' => '手机号长度必须在11-20个字符之间',
            'avatar.url' => '头像必须是有效的URL地址'
        ]);
        
        if (!$validate->check($data)) {
            return json(['code' => 400, 'message' => $validate->getError()]);
        }
        
        try {
            // 检查邮箱是否已被其他用户使用
            $existingUser = User::where('email', $data['email'])
                ->where('id', '<>', $user->id)
                ->find();
            
            if ($existingUser) {
                return json(['code' => 400, 'message' => '该邮箱已被其他用户使用']);
            }
            
            // 检查手机号是否已被其他用户使用
            $existingPhone = User::where('phone', $data['phone'])
                ->where('id', '<>', $user->id)
                ->find();
            
            if ($existingPhone) {
                return json(['code' => 400, 'message' => '该手机号已被其他用户使用']);
            }
            
            // 更新用户信息
            $user->real_name = $data['real_name'];
            $user->email = $data['email'];
            $user->phone = $data['phone'];
            if (isset($data['avatar'])) {
                $user->avatar = $data['avatar'];
            }
            
            $user->save();
            
            return json(['code' => 200, 'message' => '个人信息更新成功']);
            
        } catch (\Exception $e) {
            Log::error("更新个人信息失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '更新个人信息失败']);
        }
    }
    
    /**
     * 修改密码
     */
    public function changePassword()
    {
        $user = $this->request->user;
        $data = $this->request->post();
        
        // 验证参数
        $validate = Validate::rule([
            'old_password' => 'require',
            'new_password' => 'require|length:6,20',
            'confirm_password' => 'require|confirm:new_password'
        ])->message([
            'old_password.require' => '原密码不能为空',
            'new_password.require' => '新密码不能为空',
            'new_password.length' => '新密码长度必须在6-20个字符之间',
            'confirm_password.require' => '确认密码不能为空',
            'confirm_password.confirm' => '两次密码输入不一致'
        ]);
        
        if (!$validate->check($data)) {
            return json(['code' => 400, 'message' => $validate->getError()]);
        }
        
        try {
            // 验证原密码
            if (!password_verify($data['old_password'], $user->password)) {
                return json(['code' => 400, 'message' => '原密码错误']);
            }
            
            // 更新密码
            $user->password = password_hash($data['new_password'], PASSWORD_DEFAULT);
            $user->save();
            
            return json(['code' => 200, 'message' => '密码修改成功']);
            
        } catch (\Exception $e) {
            Log::error("修改密码失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '修改密码失败']);
        }
    }
    
    /**
     * 上传头像
     */
    public function uploadAvatar()
    {
        try {
            $file = request()->file('avatar');
            
            if (!$file) {
                return json(['code' => 400, 'message' => '请选择要上传的文件']);
            }
            
            // 验证文件类型和大小
            $validate = validate([
                'avatar' => 'fileSize:2097152|fileExt:jpg,jpeg,png,gif'
            ]);
            
            if (!$validate->check(['avatar' => $file])) {
                return json(['code' => 400, 'message' => $validate->getError()]);
            }
            
            // 保存文件
            $savename = \think\facade\Filesystem::disk('public')->putFile('avatar', $file);
            
            if (!$savename) {
                return json(['code' => 500, 'message' => '文件上传失败']);
            }
            
            $url = '/storage/' . $savename;
            
            return json([
                'code' => 200,
                'message' => '头像上传成功',
                'data' => [
                    'url' => $url,
                    'path' => $savename
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error("上传头像失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '上传头像失败']);
        }
    }
} 