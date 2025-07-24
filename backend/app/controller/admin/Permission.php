<?php
declare (strict_types = 1);

namespace app\controller\admin;

use app\BaseController;
use app\model\Permission as PermissionModel;
use think\facade\View;
use think\Request;

class Permission extends BaseController
{
    /**
     * 权限列表
     */
    public function index(Request $request)
    {
        try {
            // 如果是AJAX请求，返回JSON
            if ($request->isAjax()) {
                $page = (int)$request->param('page', 1);
                $limit = (int)$request->param('limit', 10);
                $keyword = $request->param('keyword', '');

                $query = PermissionModel::where('1=1');

                // 关键词搜索
                if (!empty($keyword)) {
                    $query->where('name|code|module', 'like', "%{$keyword}%");
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
            return View::fetch('admin/permission/index');
            
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
     * 添加权限
     */
    public function add(Request $request)
    {
        if ($request->isPost()) {
            $data = $request->post();
            
            // 验证数据
            try {
                validate([
                    'name' => 'require|max:50',
                    'code' => 'require|max:50|unique:permission',
                    'module' => 'require|max:50',
                    'description' => 'max:200',
                ])->check($data);
            } catch (\Exception $e) {
                return json(['code' => 1, 'msg' => $e->getMessage()]);
            }
            
            // 创建权限
            $permission = new PermissionModel;
            $permission->name = $data['name'];
            $permission->code = $data['code'];
            $permission->module = $data['module'];
            $permission->description = $data['description'] ?? '';
            $permission->save();
            
            return json(['code' => 0, 'msg' => '添加成功']);
        }
        
        return View::fetch('admin/permission/add');
    }

    /**
     * 编辑权限
     */
    public function edit(Request $request)
    {
        $id = $request->param('id/d');
        $permission = PermissionModel::find($id);
        
        if (!$permission) {
            return $this->error('权限不存在');
        }
        
        if ($request->isPost()) {
            $data = $request->post();
            
            // 验证数据
            try {
                validate([
                    'name' => 'require|max:50',
                    'code' => 'require|max:50|unique:permission,code,' . $id,
                    'module' => 'require|max:50',
                    'description' => 'max:200',
                ])->check($data);
            } catch (\Exception $e) {
                return json(['code' => 1, 'msg' => $e->getMessage()]);
            }
            
            // 更新权限信息
            $permission->name = $data['name'];
            $permission->code = $data['code'];
            $permission->module = $data['module'];
            $permission->description = $data['description'] ?? '';
            $permission->save();
            
            return json(['code' => 0, 'msg' => '更新成功']);
        }
        
        return View::fetch('admin/permission/edit', [
            'permission' => $permission
        ]);
    }

    /**
     * 删除权限
     */
    public function delete(Request $request)
    {
        $id = $request->param('id/d');
        $permission = PermissionModel::find($id);
        
        if (!$permission) {
            return json(['code' => 1, 'msg' => '权限不存在']);
        }
        
        $permission->delete();
        
        return json(['code' => 0, 'msg' => '删除成功']);
    }
} 