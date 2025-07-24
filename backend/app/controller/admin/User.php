<?php
declare (strict_types = 1);

namespace app\controller\admin;

use app\BaseController;
use app\model\User as UserModel;
use think\facade\View;
use think\facade\Session;
use think\facade\Cache;
use think\facade\Cookie;
use think\facade\Db;
use think\Request;

class User extends BaseController
{
    /**
     * 用户列表
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

                $query = UserModel::where('1=1');

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
                
                // 调试信息
                \think\facade\Log::info('用户列表数据: ' . json_encode($list, JSON_UNESCAPED_UNICODE));

                return json([
                    'code' => 0,
                    'msg' => '',
                    'count' => $total,
                    'data' => $list
                ]);
            }

            // 非AJAX请求，返回页面
            return View::fetch('admin/user/index');
            
        } catch (\Exception $e) {
            // 记录错误日志
            \think\facade\Log::error('User index error: ' . $e->getMessage());
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
     * 添加用户
     */
    public function add(Request $request)
    {
        if ($request->isPost()) {
            $data = $request->post();
            
            // 验证数据
            try {
                validate([
                    'username' => 'require|max:50|unique:user',
                    'password' => 'require|min:6',
                    'real_name' => 'require|max:50',
                    'phone' => 'mobile',
                    'email' => 'email',
                    'status' => 'require|in:0,1',
                ])->check($data);
            } catch (\Exception $e) {
                return json(['code' => 1, 'msg' => $e->getMessage()]);
            }
            
            // 创建用户
            $user = new UserModel;
            $user->username = $data['username'];
            $user->password = $data['password'];
            $user->real_name = $data['real_name'];
            $user->phone = $data['phone'] ?? '';
            $user->email = $data['email'] ?? '';
            $user->avatar = $data['avatar'] ?? '';
            $user->status = $data['status'];
            $user->save();
            
            return json(['code' => 0, 'msg' => '添加成功']);
        }
        
        return View::fetch('admin/user/add');
    }

    /**
     * 编辑用户
     */
    public function edit(Request $request)
    {
        $id = $request->param('id/d');
        
        // 调试信息
        \think\facade\Log::info('编辑用户ID: ' . $id);
        
        $user = UserModel::find($id);
        
        if (!$user) {
            return $this->error('用户不存在');
        }
        
        if ($request->isPost()) {
            $data = $request->post();
            
            // 调试信息
            \think\facade\Log::info('编辑用户数据: ' . json_encode($data));
            
            // 验证数据
            try {
                validate([
                    'username' => 'require|max:50',
                    'real_name' => 'require|max:50',
                    'phone' => 'mobile',
                    'email' => 'email',
                    'status' => 'require|in:0,1',
                ])->check($data);
                
                // 手动检查用户名唯一性
                $existingUser = UserModel::where('username', $data['username'])->where('id', '<>', $id)->find();
                if ($existingUser) {
                    return json(['code' => 1, 'msg' => '用户名已存在']);
                }
            } catch (\Exception $e) {
                \think\facade\Log::error('用户编辑验证失败: ' . $e->getMessage());
                return json(['code' => 1, 'msg' => $e->getMessage()]);
            }
            
            try {
                // 更新用户信息
                $user->username = $data['username'];
                $user->real_name = $data['real_name'];
                $user->phone = $data['phone'] ?? '';
                $user->email = $data['email'] ?? '';
                $user->avatar = $data['avatar'] ?? '';
                $user->status = (int)$data['status'];
                $user->save();
                
                // 更新密码（如果提供）
                if (!empty($data['password'])) {
                    $user->password = $data['password'];
                    $user->save();
                }
                
                return json(['code' => 0, 'msg' => '更新成功']);
            } catch (\Exception $e) {
                \think\facade\Log::error('用户更新失败: ' . $e->getMessage());
                return json(['code' => 1, 'msg' => '更新失败：' . $e->getMessage()]);
            }
        }
        
        return View::fetch('admin/user/edit', [
            'user' => $user
        ]);
    }

    /**
     * 删除用户
     */
    public function delete(Request $request)
    {
        $id = $request->param('id/d');
        $user = UserModel::find($id);
        
        if (!$user) {
            return json(['code' => 1, 'msg' => '用户不存在']);
        }
        
        $user->delete();
        
        return json(['code' => 0, 'msg' => '删除成功']);
    }

    /**
     * 修改状态
     */
    public function changeStatus(Request $request)
    {
        $id = $request->param('id/d');
        $status = $request->param('status/d');
        
        $user = UserModel::find($id);
        if (!$user) {
            return json(['code' => 1, 'msg' => '用户不存在']);
        }
        
        $user->status = $status;
        $user->save();
        
        return json(['code' => 0, 'msg' => '状态修改成功']);
    }

    /**
     * 会员等级管理
     */
    public function level(Request $request)
    {
        try {
            // 如果是AJAX请求，返回JSON
            if ($request->isAjax()) {
                $page = (int)$request->param('page', 1);
                $limit = (int)$request->param('limit', 10);
                $level = $request->param('level', '');

                $query = UserModel::where('1=1');

                // 等级筛选
                if ($level !== '') {
                    $query->where('member_level', $level);
                }

                // 克隆一份用于统计总数
                $countQuery = clone $query;
                $total = $countQuery->count();

                // 查分页数据并转为数组
                $list = $query->order('id', 'desc')
                    ->page($page, $limit)
                    ->select()
                    ->each(function($item) {
                        $item->member_level_text = $item->getMemberLevelText();
                        $item->is_expired = $item->isMemberExpired();
                        $item->remaining_days = $item->getRemainingMemberDays();
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

            // 非AJAX请求，返回页面
            return View::fetch('admin/user/level');
            
        } catch (\Exception $e) {
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
     * 积分管理
     */
    public function points(Request $request)
    {
        try {
            // 如果是AJAX请求，返回JSON
            if ($request->isAjax()) {
                $page = (int)$request->param('page', 1);
                $limit = (int)$request->param('limit', 10);
                $keyword = $request->param('keyword', '');

                $query = UserModel::where('1=1');

                // 关键词搜索
                if (!empty($keyword)) {
                    $query->where('username|real_name|phone|email', 'like', "%{$keyword}%");
                }

                // 克隆一份用于统计总数
                $countQuery = clone $query;
                $total = $countQuery->count();

                // 查分页数据并转为数组
                $list = $query->order('points', 'desc')
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
            return View::fetch('admin/user/points');
            
        } catch (\Exception $e) {
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
     * 调整会员等级
     */
    public function adjustLevel(Request $request)
    {
        $id = $request->param('id/d');
        $level = $request->param('level/d');
        $days = $request->param('days/d', 0);
        
        $user = UserModel::find($id);
        if (!$user) {
            return json(['code' => 1, 'msg' => '用户不存在']);
        }
        
        try {
            $user->upgradeMember($level, $days > 0 ? $days : null);
            return json(['code' => 0, 'msg' => '会员等级调整成功']);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '操作失败：' . $e->getMessage()]);
        }
    }

    /**
     * 调整积分
     */
    public function adjustPoints(Request $request)
    {
        $id = $request->param('id/d');
        $points = $request->param('points/d');
        $type = $request->param('type', 'add'); // add 或 deduct
        $reason = $request->param('reason', '管理员调整');
        
        $user = UserModel::find($id);
        if (!$user) {
            return json(['code' => 1, 'msg' => '用户不存在']);
        }
        
        try {
            if ($type === 'add') {
                $user->addPoints($points, $reason);
            } else {
                $user->deductPoints($points, $reason);
            }
            return json(['code' => 0, 'msg' => '积分调整成功']);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '操作失败：' . $e->getMessage()]);
        }
    }
} 