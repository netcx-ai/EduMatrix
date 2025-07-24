<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\BaseController;
use app\model\TeacherTitle;
use think\facade\Validate;
use think\facade\Log;
use think\facade\View;

class TeacherTitleController extends BaseController
{
    /**
     * 职称管理页面
     */
    public function index()
    {
        try {
            if ($this->request->isAjax()) {
                $page = $this->request->param('page', 1);
                $limit = $this->request->param('limit', 20);
                $keyword = $this->request->param('keyword', '');
                $status = $this->request->param('status', '');

                $query = TeacherTitle::order('sort', 'asc')->order('level', 'desc');

                if ($keyword) {
                    $query->where('name|code|description', 'like', "%{$keyword}%");
                }
                if ($status !== '') {
                    $query->where('status', $status);
                }

                $list = $query->paginate([
                    'list_rows' => $limit,
                    'page' => $page
                ]);

                $items = $list->items();
                foreach ($items as &$item) {
                    $item['level_desc'] = TeacherTitle::getLevelDescription($item['level']);
                    $item['status_text'] = $item['status'] ? '启用' : '禁用';
                }

                return json([
                    'code' => 0,
                    'msg' => '',
                    'count' => $list->total(),
                    'data' => $items
                ]);
            }

            return View::fetch('admin/teacher_title/index');
        } catch (\Exception $e) {
            Log::error("获取职称列表失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '获取职称列表失败']);
        }
    }

    /**
     * 添加职称页面
     */
    public function add()
    {
        // 判断是否为弹窗模式
        $popup = $this->request->param('popup', 0);
        if ($popup) {
            return View::fetch('admin/teacher_title/add');
        }
        
        return View::fetch('admin/teacher_title/add_full');
    }

    /**
     * 编辑职称页面
     */
    public function edit()
    {
        $id = $this->request->param('id');
        $title = TeacherTitle::find($id);
        if (!$title) {
            $this->error('职称不存在');
        }

        View::assign('title', $title);
        
        // 判断是否为弹窗模式
        $popup = $this->request->param('popup', 0);
        if ($popup) {
            return View::fetch('admin/teacher_title/edit');
        }
        
        return View::fetch('admin/teacher_title/edit_full');
    }

    /**
     * 创建职称
     */
    public function store()
    {
        $data = $this->request->post();
        
        $validate = Validate::rule([
            'name' => 'require|length:2,50|unique:teacher_titles',
            'code' => 'require|length:2,30|unique:teacher_titles',
            'sort' => 'integer|egt:0',
            'level' => 'require|integer|between:1,5',
            'description' => 'length:0,255',
            'status' => 'in:0,1'
        ])->message([
            'name.require' => '职称名称不能为空',
            'name.length' => '职称名称长度必须在2-50个字符之间',
            'name.unique' => '职称名称已存在',
            'code.require' => '职称代码不能为空',
            'code.length' => '职称代码长度必须在2-30个字符之间',
            'code.unique' => '职称代码已存在',
            'sort.integer' => '排序必须是整数',
            'sort.egt' => '排序不能小于0',
            'level.require' => '职称等级不能为空',
            'level.integer' => '职称等级必须是整数',
            'level.between' => '职称等级必须在1-5之间',
            'description.length' => '描述长度不能超过255个字符',
            'status.in' => '状态值不正确'
        ]);

        if (!$validate->check($data)) {
            return json(['code' => 400, 'message' => $validate->getError()]);
        }

        try {
            $title = new TeacherTitle();
            $title->name = $data['name'];
            $title->code = $data['code'];
            $title->sort = $data['sort'] ?? 0;
            $title->level = $data['level'];
            $title->description = $data['description'] ?? '';
            $title->status = $data['status'] ?? 1;
            $title->save();

            return json(['code' => 0, 'message' => '职称创建成功', 'data' => $title]);
        } catch (\Exception $e) {
            Log::error("创建职称失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '职称创建失败']);
        }
    }

    /**
     * 更新职称
     */
    public function update($id)
    {
        $data = $this->request->post();
        
        try {
            $title = TeacherTitle::find($id);
            if (!$title) {
                return json(['code' => 404, 'message' => '职称不存在']);
            }

            $validate = Validate::rule([
                'name' => 'require|length:2,50',
                'code' => 'require|length:2,30',
                'sort' => 'integer|egt:0',
                'level' => 'require|integer|between:1,5',
                'description' => 'length:0,255',
                'status' => 'in:0,1'
            ])->message([
                'name.require' => '职称名称不能为空',
                'name.length' => '职称名称长度必须在2-50个字符之间',
                'code.require' => '职称代码不能为空',
                'code.length' => '职称代码长度必须在2-30个字符之间',
                'sort.integer' => '排序必须是整数',
                'sort.egt' => '排序不能小于0',
                'level.require' => '职称等级不能为空',
                'level.integer' => '职称等级必须是整数',
                'level.between' => '职称等级必须在1-5之间',
                'description.length' => '描述长度不能超过255个字符',
                'status.in' => '状态值不正确'
            ]);

            if (!$validate->check($data)) {
                return json(['code' => 400, 'message' => $validate->getError()]);
            }

            // 检查名称和代码是否与其他记录重复
            $existName = TeacherTitle::where('name', $data['name'])->where('id', '<>', $id)->find();
            if ($existName) {
                return json(['code' => 400, 'message' => '职称名称已存在']);
            }

            $existCode = TeacherTitle::where('code', $data['code'])->where('id', '<>', $id)->find();
            if ($existCode) {
                return json(['code' => 400, 'message' => '职称代码已存在']);
            }

            $title->name = $data['name'];
            $title->code = $data['code'];
            $title->sort = $data['sort'] ?? 0;
            $title->level = $data['level'];
            $title->description = $data['description'] ?? '';
            $title->status = $data['status'] ?? 1;
            $title->save();

            return json(['code' => 0, 'message' => '职称更新成功', 'data' => $title]);
        } catch (\Exception $e) {
            Log::error("更新职称失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '职称更新失败']);
        }
    }

    /**
     * 删除职称
     */
    public function destroy($id)
    {
        try {
            $title = TeacherTitle::find($id);
            if (!$title) {
                return json(['code' => 404, 'message' => '职称不存在']);
            }

            // 检查是否有教师使用此职称
            $teacherCount = \app\model\Teacher::where('title', $id)->count();
            if ($teacherCount > 0) {
                return json(['code' => 400, 'message' => "该职称正在被 {$teacherCount} 位教师使用，无法删除"]);
            }

            $title->delete();
            return json(['code' => 0, 'message' => '职称删除成功']);
        } catch (\Exception $e) {
            Log::error("删除职称失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '职称删除失败']);
        }
    }

    /**
     * 修改职称状态
     */
    public function changeStatus()
    {
        $id = $this->request->param('id');
        $status = $this->request->param('status');

        try {
            $title = TeacherTitle::find($id);
            if (!$title) {
                return json(['code' => 404, 'message' => '职称不存在']);
            }

            $title->status = $status;
            $title->save();
            
            // 清除缓存，确保数据及时更新
            TeacherTitle::clearCache();
            
            return json(['code' => 0, 'message' => '状态修改成功']);

        } catch (\Exception $e) {
            Log::error("修改职称状态失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '状态修改失败']);
        }
    }

    /**
     * 获取职称选项（API）
     */
    public function getOptions()
    {
        try {
            $options = TeacherTitle::getTitleOptions();
            return json(['code' => 0, 'data' => $options]);
        } catch (\Exception $e) {
            Log::error("获取职称选项失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '获取职称选项失败']);
        }
    }

    /**
     * 批量更新排序
     */
    public function updateSort()
    {
        $data = $this->request->post();
        
        if (!isset($data['sorts']) || !is_array($data['sorts'])) {
            return json(['code' => 400, 'message' => '参数错误']);
        }

        try {
            foreach ($data['sorts'] as $item) {
                if (isset($item['id']) && isset($item['sort'])) {
                    TeacherTitle::where('id', $item['id'])->update(['sort' => $item['sort']]);
                }
            }

            // 清除缓存
            TeacherTitle::clearCache();
            
            return json(['code' => 0, 'message' => '排序更新成功']);
        } catch (\Exception $e) {
            Log::error("更新职称排序失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '排序更新失败']);
        }
    }

    /**
     * 职称详情页面
     */
    public function detail()
    {
        $id = $this->request->param('id');
        $title = TeacherTitle::find($id);
        if (!$title) {
            $this->error('职称不存在');
        }

        // 获取使用该职称的教师数量和列表
        $teacherCount = \app\model\Teacher::where('title', $title->id)->count();
        $teachers = \app\model\Teacher::where('title', $title->id)
            ->with(['school', 'college'])
            ->limit(10)
            ->select();

        // 处理教师数据
        $teacherList = [];
        foreach ($teachers as $teacher) {
            $teacherList[] = [
                'name' => $teacher->real_name,
                'teacher_no' => $teacher->teacher_no,
                'school_name' => $teacher->school ? $teacher->school->name : '未分配',
                'college_name' => $teacher->college ? $teacher->college->name : '未分配',
                'status' => $teacher->status,
                'join_date' => $teacher->join_date
            ];
        }

        // 计算使用比例
        $totalTeacherCount = \app\model\Teacher::count();
        $usagePercent = $totalTeacherCount > 0 ? round(($teacherCount / $totalTeacherCount) * 100, 2) : 0;

        View::assign([
            'title' => $title,
            'teacherCount' => $teacherCount,
            'teachers' => $teacherList,
            'usagePercent' => $usagePercent
        ]);

        // 判断是否为弹窗模式
        $popup = $this->request->param('popup', 0);
        if ($popup) {
            return View::fetch('admin/teacher_title/detail');
        }

        return View::fetch('admin/teacher_title/detail_full');
    }

    /**
     * 批量删除职称
     */
    public function batchDelete()
    {
        $ids = $this->request->param('ids');
        if (empty($ids) || !is_array($ids)) {
            return json(['code' => 400, 'message' => '请选择要删除的职称']);
        }

        try {
            $count = 0;
            foreach ($ids as $id) {
                // 检查是否有教师使用该职称
                $teacherCount = \app\model\Teacher::where('title', $id)->count();
                if ($teacherCount > 0) {
                    $title = TeacherTitle::find($id);
                    return json(['code' => 400, 'message' => "职称 '{$title->name}' 还有 {$teacherCount} 个教师在使用，无法删除"]);
                }

                $title = TeacherTitle::find($id);
                if ($title && $title->delete()) {
                    $count++;
                }
            }

            // 清除缓存
            TeacherTitle::clearCache();

            return json(['code' => 0, 'message' => "成功删除 {$count} 个职称"]);
        } catch (\Exception $e) {
            Log::error("批量删除职称失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '删除失败：' . $e->getMessage()]);
        }
    }

    /**
     * 获取级别配置（包含示例）
     */
    public function getLevelOptions()
    {
        try {
            $levels = TeacherTitle::getLevelOptions();
            
            return json([
                'code' => 0,
                'msg' => '',
                'data' => $levels
            ]);
        } catch (\Exception $e) {
            Log::error("获取级别配置失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '获取级别配置失败']);
        }
    }

    /**
     * 清除缓存
     */
    public function clearCache()
    {
        try {
            TeacherTitle::clearCache();
            return json(['code' => 0, 'message' => '缓存清除成功']);
        } catch (\Exception $e) {
            Log::error("清除职称缓存失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '清除缓存失败']);
        }
    }
} 