<?php
declare (strict_types = 1);

namespace app\controller\admin;

use app\BaseController;
use app\model\Role as RoleModel;
use app\model\Permission as PermissionModel;
use think\facade\View;
use think\Request;

class Role extends BaseController
{
    /**
     * 角色列表
     */
    public function index(Request $request)
    {
        try {
            // 如果是AJAX请求，返回JSON
            if ($request->isAjax()) {
                $page = (int)$request->param('page', 1);
                $limit = (int)$request->param('limit', 10);
                $keyword = $request->param('keyword', '');

                $query = RoleModel::with(['permissions']);

                // 关键词搜索
                if (!empty($keyword)) {
                    $query->where('name|code|description', 'like', "%{$keyword}%");
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
            return View::fetch('admin/role/index');
            
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
     * 添加角色
     */
    public function add(Request $request)
    {
        if ($request->isPost()) {
            $data = $request->post();
            
            // 验证数据
            try {
                validate([
                    'name' => 'require|max:50|unique:role',
                    'code' => 'require|max:50|unique:role',
                    'description' => 'max:200',
                    'status' => 'require|in:0,1',
                ])->check($data);
            } catch (\Exception $e) {
                return json(['code' => 1, 'msg' => $e->getMessage()]);
            }
            
            // 创建角色
            $role = new RoleModel;
            $role->name = $data['name'];
            $role->code = $data['code'];
            $role->description = $data['description'] ?? '';
            $role->status = $data['status'];
            $role->save();
            
            // 分配权限
            if (!empty($data['permission_ids'])) {
                $role->permissions()->attach($data['permission_ids']);
            }
            
            return json(['code' => 0, 'msg' => '添加成功']);
        }
        
        // 获取权限列表
        $permissions = PermissionModel::select();
        
        return View::fetch('admin/role/add', [
            'permissions' => $permissions
        ]);
    }

    /**
     * 编辑角色
     */
    public function edit(Request $request)
    {
        $id = $request->param('id/d');
        $role = RoleModel::with(['permissions'])->find($id);
        
        if (!$role) {
            return $this->error('角色不存在');
        }
        
        if ($request->isPost()) {
            $data = $request->post();
            
            // 验证数据
            try {
                validate([
                    'name' => 'require|max:50|unique:role,name,' . $id,
                    'code' => 'require|max:50|unique:role,code,' . $id,
                    'description' => 'max:200',
                    'status' => 'require|in:0,1',
                ])->check($data);
            } catch (\Exception $e) {
                return json(['code' => 1, 'msg' => $e->getMessage()]);
            }
            
            // 更新角色信息
            $role->name = $data['name'];
            $role->code = $data['code'];
            $role->description = $data['description'] ?? '';
            $role->status = $data['status'];
            $role->save();
            
            // 更新权限
            $role->permissions()->detach();
            if (!empty($data['permission_ids'])) {
                $role->permissions()->attach($data['permission_ids']);
            }
            
            return json(['code' => 0, 'msg' => '更新成功']);
        }
        
        // 获取权限列表
        $permissions = PermissionModel::select();
        
        return View::fetch('admin/role/edit', [
            'role' => $role,
            'permissions' => $permissions
        ]);
    }

    /**
     * 删除角色
     */
    public function delete(Request $request)
    {
        $id = $request->param('id/d');
        $role = RoleModel::find($id);
        
        if (!$role) {
            return json(['code' => 1, 'msg' => '角色不存在']);
        }
        
        $role->delete();
        
        return json(['code' => 0, 'msg' => '删除成功']);
    }

    /**
     * 角色权限分配页面
     */
    public function permission(Request $request)
    {
        $id = $request->param('id/d');
        $role = RoleModel::with(['permissions'])->find($id);
        
        if (!$role) {
            return $this->error('角色不存在');
        }
        
        return View::fetch('admin/role/permission', [
            'role' => $role
        ]);
    }

    /**
     * 获取权限树数据
     */
    public function getPermissions(Request $request)
    {
        $roleId = $request->param('role_id/d');
        $role = RoleModel::with(['permissions'])->find($roleId);
        
        if (!$role) {
            return json(['code' => 1, 'msg' => '角色不存在']);
        }
        
        // 获取所有权限
        $permissions = PermissionModel::order('module, id')->select();
        
        // 获取角色已有的权限ID列表
        $rolePermissionIds = [];
        foreach ($role->permissions as $permission) {
            $rolePermissionIds[] = $permission->id;
        }
        
        // 按模块分组
        $modules = [];
        foreach ($permissions as $permission) {
            $module = $permission->module;
            if (!isset($modules[$module])) {
                $modules[$module] = [
                    'id' => 'module_' . $module,
                    'title' => $module,
                    'children' => []
                ];
            }
            
            $modules[$module]['children'][] = [
                'id' => $permission->id,
                'title' => $permission->name . ' (' . $permission->code . ')',
                'checked' => in_array($permission->id, $rolePermissionIds)
            ];
        }
        
        $treeData = array_values($modules);
        
        return json(['code' => 0, 'msg' => '', 'data' => $treeData]);
    }

    /**
     * 保存角色权限
     */
    public function savePermissions(Request $request)
    {
        $roleId = $request->param('role_id/d');
        $permissionIds = $request->param('permission_ids/a', []);
        
        $role = RoleModel::find($roleId);
        if (!$role) {
            return json(['code' => 1, 'msg' => '角色不存在']);
        }
        
        // 更新权限
        $role->permissions()->detach();
        if (!empty($permissionIds)) {
            $role->permissions()->attach($permissionIds);
        }
        
        return json(['code' => 0, 'msg' => '权限保存成功']);
    }
} 