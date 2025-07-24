<?php
declare (strict_types = 1);

namespace app\controller\api\school;

use app\BaseController;
use app\model\Teacher;
use app\model\School;
use app\model\College;
use app\model\User;
use app\model\SchoolAdmin;
use app\model\TeacherTitle;
use think\facade\Validate;
use think\facade\Log;
use think\facade\Db;
use think\facade\Cache;
use think\exception\ValidateException;
use think\exception\DatabaseException;

/**
 * 教师管理控制器
 * 
 * @package app\controller\api\school
 * @author EduMatrix System
 */
class TeacherController extends BaseController
{
    /**
     * 获取教师列表
     * 
     * @return \think\Response
     */
    public function index()
    {
        try {
            $user = $this->request->user;
            if (!$user) {
                return json(['code' => 401, 'message' => '用户未登录']);
            }

            // 参数验证
            $validate = Validate::rule([
                'page' => 'integer|min:1',
                'pageSize' => 'integer|min:1|max:100',
                'name' => 'length:0,50',
                'collegeId' => 'integer|min:0',
                'title' => 'integer|min:0',
                'status' => 'in:active,inactive,0,1,2'
            ])->message([
                'page.integer' => '页码必须是整数',
                'page.min' => '页码不能小于1',
                'pageSize.integer' => '每页数量必须是整数',
                'pageSize.min' => '每页数量不能小于1',
                'pageSize.max' => '每页数量不能超过100',
                'name.length' => '姓名长度不能超过50个字符',
                'collegeId.integer' => '学院ID必须是整数',
                'collegeId.min' => '学院ID不能小于0',
                'title.integer' => '职称ID必须是整数',
                'title.min' => '职称ID不能小于0',
                'status.in' => '状态值不正确'
            ]);

            $params = $this->request->only(['page', 'pageSize', 'name', 'collegeId', 'title', 'status']);
            $params = array_merge([
                'page' => 1,
                'pageSize' => 10,
                'name' => '',
                'collegeId' => '',
                'title' => '',
                'status' => ''
            ], $params);

            if (!$validate->check($params)) {
                return json(['code' => 400, 'message' => $validate->getError()]);
            }

            // 构建查询条件
            $query = Teacher::where('school_id', $user->primary_school_id);
            
            // 教师姓名搜索
            if (!empty($params['name'])) {
                $query->where('real_name', 'like', '%' . trim($params['name']) . '%');
            }
            
            // 学院筛选
            if (!empty($params['collegeId'])) {
                $query->where('college_id', (int)$params['collegeId']);
            }
            
            // 职称筛选
            if (!empty($params['title'])) {
                $query->where('title', (int)$params['title']);
            }
            
            // 状态筛选
            if ($params['status'] !== '') {
                $statusValue = $this->normalizeStatus($params['status']);
                $query->where('status', $statusValue);
            }
            
            // 执行查询
            $list = $query->with(['school', 'college', 'user'])
                ->order('create_time', 'desc')
                ->paginate([
                    'list_rows' => (int)$params['pageSize'],
                    'page' => (int)$params['page']
                ]);
            
            // 处理数据格式
            $items = $list->items();
            $processedItems = [];
            
            foreach ($items as $item) {
                $processedItems[] = $this->formatTeacherData($item, false, false);
            }
            
            // 记录访问日志
            Log::info("获取教师列表成功", [
                'user_id' => $user->id,
                'school_id' => $user->primary_school_id,
                'total' => $list->total(),
                'page' => $list->currentPage(),
                'pageSize' => $list->listRows()
            ]);
            
            return json([
                'code' => 200,
                'message' => '获取成功',
                'data' => [
                    'list' => $processedItems,
                    'total' => $list->total(),
                    'page' => $list->currentPage(),
                    'pageSize' => $list->listRows(),
                    'hasMore' => method_exists($list, 'hasMore') ? $list->hasMore() : false
                ]
            ]);
            
        } catch (ValidateException $e) {
            Log::warning("教师列表参数验证失败: " . $e->getMessage());
            return json(['code' => 400, 'message' => $e->getMessage()]);
        } catch (DatabaseException $e) {
            Log::error("教师列表数据库查询失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '数据库查询失败']);
        } catch (\Exception $e) {
            Log::error("获取教师列表失败: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return json(['code' => 500, 'message' => '获取教师列表失败']);
        }
    }
    
    /**
     * 获取教师详情 (read方法 - 兼容前端调用)
     * 
     * @param int $id 教师ID
     * @return \think\Response
     */
    public function read($id)
    {
        return $this->show($id);
    }

    /**
     * 获取教师详情
     * 
     * @param int $id 教师ID
     * @return \think\Response
     */
    public function show($id)
    {
        try {
            $user = $this->request->user;
            if (!$user) {
                return json(['code' => 401, 'message' => '用户未登录']);
            }

            // 参数验证
            if (!is_numeric($id) || $id <= 0) {
                return json(['code' => 400, 'message' => '教师ID格式不正确']);
            }

            $teacher = Teacher::where('school_id', $user->primary_school_id)
                ->where('id', (int)$id)
                ->with(['school', 'college', 'user', 'verifier'])
                ->find();
            
            if (!$teacher) {
                return json(['code' => 404, 'message' => '教师不存在']);
            }
            
            // 记录访问日志
            Log::info("获取教师详情成功", [
                'user_id' => $user->id,
                'teacher_id' => $id,
                'school_id' => $user->primary_school_id
            ]);
            
                            return json([
                    'code' => 200,
                    'message' => '获取成功',
                    'data' => $this->formatTeacherData($teacher, true, false)
                ]);
            
        } catch (DatabaseException $e) {
            Log::error("获取教师详情数据库查询失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '数据库查询失败']);
        } catch (\Exception $e) {
            Log::error("获取教师详情失败: " . $e->getMessage(), [
                'teacher_id' => $id,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return json(['code' => 500, 'message' => '获取教师详情失败']);
        }
    }
    
    /**
     * 创建教师
     * 
     * @return \think\Response
     */
    public function store()
    {
        try {
            $school = $this->request->school;
            if (!$school) {
                return json(['code' => 400, 'message' => '学校信息获取失败']);
            }

            $data = $this->request->post();
            
            // 验证参数
            $validate = Validate::rule([
                'real_name' => 'require|length:2,50|chsDash',
                'teacher_no' => 'require|length:3,20|alphaDash',
                'email' => 'require|email|max:100',
                'phone' => 'require|mobile',
                'title' => 'require|integer|min:1',
                'college_id' => 'require|integer|gt:0',
                'bio' => 'length:0,500',
                'hire_date' => 'date'
            ])->message([
                'real_name.require' => '教师姓名不能为空',
                'real_name.length' => '教师姓名长度必须在2-50个字符之间',
                'real_name.chsDash' => '教师姓名只能包含汉字、字母、数字、下划线和破折号',
                'teacher_no.require' => '工号不能为空',
                'teacher_no.length' => '工号长度必须在3-20个字符之间',
                'teacher_no.alphaDash' => '工号只能包含字母、数字、下划线和破折号',
                'email.require' => '邮箱不能为空',
                'email.email' => '邮箱格式不正确',
                'email.max' => '邮箱长度不能超过100个字符',
                'phone.require' => '电话不能为空',
                'phone.mobile' => '电话格式不正确',
                'title.require' => '职称不能为空',
                'title.integer' => '职称ID必须是整数',
                'title.min' => '职称ID必须大于0',
                'college_id.require' => '所属学院不能为空',
                'college_id.integer' => '学院ID必须是整数',
                'college_id.gt' => '学院ID必须大于0',
                'bio.length' => '简介长度不能超过500个字符',
                'hire_date.date' => '入职日期格式不正确'
            ]);
            
            if (!$validate->check($data)) {
                return json(['code' => 400, 'message' => $validate->getError()]);
            }

            // 检查邮箱是否已被使用
            $existingTeacher = Teacher::where('email', $data['email'])
                ->where('school_id', $school->id)
                ->find();
            
            if ($existingTeacher) {
                return json(['code' => 400, 'message' => '该邮箱已被其他教师使用']);
            }
            
            // 检查工号是否已被使用
            $existingTeacherNo = Teacher::where('teacher_no', $data['teacher_no'])
                ->where('school_id', $school->id)
                ->find();
            
            if ($existingTeacherNo) {
                return json(['code' => 400, 'message' => '该工号已被其他教师使用']);
            }
            
            // 检查学院是否存在
            $college = College::where('id', $data['college_id'])
                ->where('school_id', $school->id)
                ->find();
            
            if (!$college) {
                return json(['code' => 400, 'message' => '学院不存在']);
            }

            // 检查职称是否存在
            $title = TeacherTitle::find($data['title']);
            if (!$title) {
                return json(['code' => 400, 'message' => '职称不存在']);
            }

            // 开启事务
            Db::startTrans();
            try {
                $teacher = new Teacher();
                $teacher->school_id = $school->id;
                $teacher->real_name = trim($data['real_name']);
                $teacher->teacher_no = trim($data['teacher_no']);
                $teacher->email = trim($data['email']);
                $teacher->phone = trim($data['phone']);
                $teacher->title = (int)$data['title'];
                $teacher->college_id = (int)$data['college_id'];
                $teacher->bio = isset($data['bio']) ? trim($data['bio']) : '';
                $teacher->hire_date = $data['hire_date'] ?? null;
                $teacher->status = 1; // 默认启用
                $teacher->save();
                
                // 更新学校教师数量缓存
                $this->updateSchoolTeacherCount($school->id);
                
                Db::commit();
                
                // 记录操作日志
                Log::info("创建教师成功", [
                    'teacher_id' => $teacher->id,
                    'school_id' => $school->id,
                    'admin_id' => $this->request->adminId ?? 0
                ]);
                
                return json([
                    'code' => 200, 
                    'message' => '教师创建成功', 
                    'data' => $this->formatTeacherData($teacher, false, false)
                ]);
                
            } catch (\Exception $e) {
                Db::rollback();
                throw $e;
            }
            
        } catch (ValidateException $e) {
            Log::warning("创建教师参数验证失败: " . $e->getMessage());
            return json(['code' => 400, 'message' => $e->getMessage()]);
        } catch (DatabaseException $e) {
            Log::error("创建教师数据库操作失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '数据库操作失败']);
        } catch (\Exception $e) {
            Log::error("创建教师失败: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return json(['code' => 500, 'message' => '教师创建失败']);
        }
    }

    /**
     * 更新教师
     * 
     * @param int $id 教师ID
     * @return \think\Response
     */
    public function update($id)
    {
        try {
            $school = $this->request->school;
            if (!$school) {
                return json(['code' => 400, 'message' => '学校信息获取失败']);
            }

            if (!is_numeric($id) || $id <= 0) {
                return json(['code' => 400, 'message' => '教师ID格式不正确']);
            }

            $data = $this->request->post();
            if (empty($data)) {
                return json(['code' => 400, 'message' => '更新数据不能为空']);
            }
            
            $teacher = Teacher::where('school_id', $school->id)
                ->where('id', (int)$id)
                ->find();
            
            if (!$teacher) {
                return json(['code' => 404, 'message' => '教师不存在']);
            }
            
            // 验证参数 - 只验证提供的字段
            $rules = [];
            $messages = [];
            
            if (isset($data['real_name'])) {
                $rules['real_name'] = 'require|length:2,50|chsDash';
                $messages['real_name.require'] = '教师姓名不能为空';
                $messages['real_name.length'] = '教师姓名长度必须在2-50个字符之间';
                $messages['real_name.chsDash'] = '教师姓名只能包含汉字、字母、数字、下划线和破折号';
            }
            if (isset($data['teacher_no'])) {
                $rules['teacher_no'] = 'require|length:3,20|alphaDash';
                $messages['teacher_no.require'] = '工号不能为空';
                $messages['teacher_no.length'] = '工号长度必须在3-20个字符之间';
                $messages['teacher_no.alphaDash'] = '工号只能包含字母、数字、下划线和破折号';
            }
            if (isset($data['email'])) {
                $rules['email'] = 'require|email|max:100';
                $messages['email.require'] = '邮箱不能为空';
                $messages['email.email'] = '邮箱格式不正确';
                $messages['email.max'] = '邮箱长度不能超过100个字符';
            }
            if (isset($data['phone'])) {
                $rules['phone'] = 'require|mobile';
                $messages['phone.require'] = '电话不能为空';
                $messages['phone.mobile'] = '电话格式不正确';
            }
            if (isset($data['title'])) {
                $rules['title'] = 'require|integer|min:1';
                $messages['title.require'] = '职称不能为空';
                $messages['title.integer'] = '职称ID必须是整数';
                $messages['title.min'] = '职称ID必须大于0';
            }
            if (isset($data['college_id'])) {
                $rules['college_id'] = 'require|integer|gt:0';
                $messages['college_id.require'] = '所属学院不能为空';
                $messages['college_id.integer'] = '学院ID必须是整数';
                $messages['college_id.gt'] = '学院ID必须大于0';
            }
            if (isset($data['hire_date'])) {
                $rules['hire_date'] = 'date';
                $messages['hire_date.date'] = '入职日期格式不正确';
            }
            if (isset($data['bio'])) {
                $rules['bio'] = 'length:0,500';
                $messages['bio.length'] = '简介长度不能超过500个字符';
            }
            if (isset($data['status'])) {
                $rules['status'] = 'in:0,1,2,active,inactive';
                $messages['status.in'] = '状态值不正确';
            }
            
            if (!empty($rules)) {
                $validate = Validate::rule($rules)->message($messages);
                if (!$validate->check($data)) {
                    return json(['code' => 400, 'message' => $validate->getError()]);
                }
            }
            
            // 检查邮箱是否已被其他教师使用
            if (isset($data['email'])) {
                $existingTeacher = Teacher::where('email', $data['email'])
                    ->where('school_id', $school->id)
                    ->where('id', '<>', (int)$id)
                    ->find();
                
                if ($existingTeacher) {
                    return json(['code' => 400, 'message' => '该邮箱已被其他教师使用']);
                }
            }
            
            // 检查工号是否已被其他教师使用
            if (isset($data['teacher_no'])) {
                $existingTeacherNo = Teacher::where('teacher_no', $data['teacher_no'])
                    ->where('school_id', $school->id)
                    ->where('id', '<>', (int)$id)
                    ->find();
                
                if ($existingTeacherNo) {
                    return json(['code' => 400, 'message' => '该工号已被其他教师使用']);
                }
            }
            
            // 检查学院是否存在
            if (isset($data['college_id'])) {
                $college = College::where('id', $data['college_id'])
                    ->where('school_id', $school->id)
                    ->find();
                
                if (!$college) {
                    return json(['code' => 400, 'message' => '学院不存在']);
                }
            }

            // 检查职称是否存在
            if (isset($data['title'])) {
                $title = TeacherTitle::find($data['title']);
                if (!$title) {
                    return json(['code' => 400, 'message' => '职称不存在']);
                }
            }
            
            // 开启事务
            Db::startTrans();
            try {
                // 更新教师信息 - 支持部分更新
                if (isset($data['real_name'])) {
                    $teacher->real_name = trim($data['real_name']);
                }
                if (isset($data['teacher_no'])) {
                    $teacher->teacher_no = trim($data['teacher_no']);
                }
                if (isset($data['email'])) {
                    $teacher->email = trim($data['email']);
                }
                if (isset($data['phone'])) {
                    $teacher->phone = trim($data['phone']);
                }
                if (isset($data['title'])) {
                    $teacher->title = (int)$data['title'];
                }
                if (isset($data['college_id'])) {
                    $teacher->college_id = (int)$data['college_id'];
                }
                if (isset($data['hire_date'])) {
                    $teacher->hire_date = $data['hire_date'];
                }
                if (isset($data['bio'])) {
                    $teacher->bio = trim($data['bio']);
                }
                if (isset($data['status'])) {
                    $teacher->status = $this->normalizeStatus($data['status']);
                }
                
                $teacher->save();
                
                Db::commit();
                
                // 记录操作日志
                Log::info("更新教师成功", [
                    'teacher_id' => $teacher->id,
                    'school_id' => $school->id,
                    'admin_id' => $this->request->adminId ?? 0,
                    'updated_fields' => array_keys($data)
                ]);
                
                return json([
                    'code' => 200, 
                    'message' => '教师更新成功', 
                    'data' => $this->formatTeacherData($teacher, false, false)
                ]);
                
            } catch (\Exception $e) {
                Db::rollback();
                throw $e;
            }
            
        } catch (ValidateException $e) {
            Log::warning("更新教师参数验证失败: " . $e->getMessage());
            return json(['code' => 400, 'message' => $e->getMessage()]);
        } catch (DatabaseException $e) {
            Log::error("更新教师数据库操作失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '数据库操作失败']);
        } catch (\Exception $e) {
            Log::error("更新教师失败: " . $e->getMessage(), [
                'teacher_id' => $id,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return json(['code' => 500, 'message' => '教师更新失败']);
        }
    }
    
    /**
     * 审核教师
     * 
     * @param int $id 教师ID
     * @return \think\Response
     */
    public function verify($id)
    {
        try {
            $user = $this->request->user;
            if (!$user) {
                return json(['code' => 401, 'message' => '用户未登录']);
            }

            if (!is_numeric($id) || $id <= 0) {
                return json(['code' => 400, 'message' => '教师ID格式不正确']);
            }

            $data = $this->request->post();
            
            // 验证参数
            $validate = Validate::rule([
                'action' => 'require|in:approve,reject',
                'remark' => 'length:0,200',
                'reason' => 'length:0,200'
            ])->message([
                'action.require' => '审核操作不能为空',
                'action.in' => '审核操作只能是approve或reject',
                'remark.length' => '备注长度不能超过200个字符',
                'reason.length' => '拒绝原因长度不能超过200个字符'
            ]);
            
            if (!$validate->check($data)) {
                return json(['code' => 400, 'message' => $validate->getError()]);
            }
            
            $teacher = Teacher::where('school_id', $user->primary_school_id)
                ->where('id', (int)$id)
                ->find();
            
            if (!$teacher) {
                return json(['code' => 404, 'message' => '教师不存在']);
            }
            
            if ($teacher->status != 2) {
                return json(['code' => 400, 'message' => '该教师不在待审核状态']);
            }
            
            $action = $data['action'];
            $remark = $data['remark'] ?? $data['reason'] ?? '';
            
            // 开启事务
            Db::startTrans();
            try {
                if ($action === 'approve') {
                    // 通过审核
                    $teacher->status = 1;
                    $teacher->verify_time = date('Y-m-d H:i:s');
                    $teacher->verify_remark = $remark ?: '审核通过';
                    $teacher->verifier_id = $user->id;
                    $teacher->save();
                    
                    // 更新学校教师数量缓存
                    $this->updateSchoolTeacherCount($user->primary_school_id);
                    
                    // 记录操作日志
                    Log::info("教师审核通过", [
                        'teacher_id' => $teacher->id,
                        'school_id' => $user->primary_school_id,
                        'admin_id' => $user->id,
                        'remark' => $remark
                    ]);
                    
                    return json(['code' => 200, 'message' => '教师审核通过']);
                    
                } else {
                    // 拒绝审核
                    $teacher->status = 0;
                    $teacher->verify_time = date('Y-m-d H:i:s');
                    $teacher->verify_remark = $remark ?: '审核拒绝';
                    $teacher->verifier_id = $user->id;
                    $teacher->save();
                    
                    // 记录操作日志
                    Log::info("教师审核拒绝", [
                        'teacher_id' => $teacher->id,
                        'school_id' => $user->primary_school_id,
                        'admin_id' => $user->id,
                        'reason' => $remark
                    ]);
                    
                    return json(['code' => 200, 'message' => '教师审核拒绝']);
                }
                
            } catch (\Exception $e) {
                Db::rollback();
                throw $e;
            }
            
        } catch (ValidateException $e) {
            Log::warning("审核教师参数验证失败: " . $e->getMessage());
            return json(['code' => 400, 'message' => $e->getMessage()]);
        } catch (DatabaseException $e) {
            Log::error("审核教师数据库操作失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '数据库操作失败']);
        } catch (\Exception $e) {
            Log::error("审核教师失败: " . $e->getMessage(), [
                'teacher_id' => $id,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return json(['code' => 500, 'message' => '审核教师失败']);
        }
    }
    
    /**
     * 通过教师审核
     * 
     * @param int $id 教师ID
     * @return \think\Response
     */
    public function approve($id)
    {
        $data = $this->request->post();
        $data['action'] = 'approve';
        return $this->verify($id);
    }
    
    /**
     * 拒绝教师审核
     * 
     * @param int $id 教师ID
     * @return \think\Response
     */
    public function reject($id)
    {
        $data = $this->request->post();
        $data['action'] = 'reject';
        return $this->verify($id);
    }
    
    /**
     * 批量审核教师
     */
    public function batchVerify()
    {
        $school = $this->request->school;
        $admin = $this->request->schoolAdmin;
        $data = $this->request->post();
        
        if (!$admin) {
            return json(['code' => 401, 'message' => '请先登录']);
        }
        
        // 验证参数
        $validate = Validate::rule([
            'teacher_ids' => 'require|array',
            'action' => 'require|in:approve,reject'
        ])->message([
            'teacher_ids.require' => '请选择要审核的教师',
            'teacher_ids.array' => '教师ID必须是数组',
            'action.require' => '请选择审核操作',
            'action.in' => '审核操作不正确'
        ]);
        
        if (!$validate->check($data)) {
            return json(['code' => 400, 'message' => $validate->getError()]);
        }
        
        try {
            Db::startTrans();
            
            $teacherIds = $data['teacher_ids'];
            $action = $data['action'];
            $reason = $data['reason'] ?? '';
            
            $teachers = Teacher::where('school_id', $school->id)
                ->where('id', 'in', $teacherIds)
                ->where('status', 2)
                ->select();
            
            if ($teachers->isEmpty()) {
                return json(['code' => 400, 'message' => '没有找到待审核的教师']);
            }
            
            foreach ($teachers as $teacher) {
                if ($action === 'approve') {
                    $teacher->verify($admin->id);
                    
                    // 更新用户类型
                    $user = User::find($teacher->user_id);
                    if ($user) {
                        $user->convertToTeacher($school->id, $teacher->teacher_no);
                    }
                } else {
                    $teacher->reject($admin->id, $reason);
                }
            }
            
            // 更新学校教师数量
            $school->updateTeacherCount();
            
            Db::commit();
            
            return json(['code' => 200, 'message' => '批量审核完成']);
            
        } catch (\Exception $e) {
            Db::rollback();
            Log::error("批量审核教师失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '批量审核失败']);
        }
    }
    
    /**
     * 更新教师状态
     */
    public function updateStatus($id)
    {
        $school = $this->request->school;
        $data = $this->request->post();
        
        // 验证参数
        $validate = Validate::rule([
            'status' => 'require|in:0,1,2'
        ])->message([
            'status.require' => '状态不能为空',
            'status.in' => '状态值不正确'
        ]);
        
        if (!$validate->check($data)) {
            return json(['code' => 400, 'message' => $validate->getError()]);
        }
        
        try {
            $teacher = Teacher::where('school_id', $school->id)
                ->where('id', $id)
                ->find();
            
            if (!$teacher) {
                return json(['code' => 404, 'message' => '教师不存在']);
            }
            
            $teacher->status = $data['status'];
            $teacher->save();
            
            // 更新学校教师数量
            $school->updateTeacherCount();
            
            return json(['code' => 200, 'message' => '状态更新成功']);
            
        } catch (\Exception $e) {
            Log::error("更新教师状态失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '状态更新失败']);
        }
    }
    
    /**
     * 删除教师
     */
    public function destroy($id)
    {
        $school = $this->request->school;
        
        try {
            $teacher = Teacher::where('school_id', $school->id)
                ->where('id', $id)
                ->find();
            
            if (!$teacher) {
                return json(['code' => 404, 'message' => '教师不存在']);
            }
            
            // 删除教师记录
            $teacher->delete();
            
            // 更新用户类型为普通会员
            $user = User::find($teacher->user_id);
            if ($user) {
                $user->convertToMember();
            }
            
            // 更新学校教师数量
            $school->updateTeacherCount();
            
            return json(['code' => 200, 'message' => '教师删除成功']);
            
        } catch (\Exception $e) {
            Log::error("删除教师失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '教师删除失败']);
        }
    }
    
    /**
     * 获取待审核教师列表
     */
    public function pending()
    {
        $user = $this->request->user;
        $page = $this->request->param('page', 1);
        $limit = $this->request->param('limit', 20);
        $name = $this->request->param('name', '');
        $status = $this->request->param('status', '');
        
        try {
            $query = Teacher::where('school_id', $user->primary_school_id)
                ->with(['school', 'college', 'user']);
            
            // 状态筛选 - 支持前端的字符串状态
            if ($status) {
                $statusMap = [
                    'pending' => 2,    // 待审核
                    'approved' => 1,   // 已通过
                    'rejected' => 0    // 已拒绝
                ];
                if (isset($statusMap[$status])) {
                    $query->where('status', $statusMap[$status]);
                }
            } else {
                // 默认只显示待审核的
                $query->where('status', 2);
            }
            
            // 姓名搜索
            if ($name) {
                $query->where('real_name', 'like', "%{$name}%");
            }
            
            $list = $query->order('create_time', 'asc')
                ->paginate([
                    'list_rows' => $limit,
                    'page' => $page
                ]);
            
            // 处理返回数据格式
            $items = $list->items();
            $processedItems = [];
            
            foreach ($items as $item) {
                $itemData = $item->toArray();
                $itemData['id'] = (int)$itemData['id'];
                
                // 姓名字段映射
                $itemData['name'] = $itemData['real_name'];
                
                // 状态字段映射
                $itemData['status'] = Teacher::getAuditStatusName($itemData['status']);
                
                // 职称名称转换
                $itemData['titleName'] = '';
                if (!empty($itemData['title'])) {
                    $titleObj = \app\model\TeacherTitle::where('id', (int)$itemData['title'])->where('status', 1)->find();
                    $itemData['titleName'] = $titleObj ? $titleObj['name'] : '未知职称';
                }
                
                // 学院信息处理
                $college = $itemData['college'] ?? null;
                $itemData['college'] = $college ? $college['name'] : '未分配学院';
                
                // 清理关联数据
                unset($itemData['school'], $itemData['user']);
                
                $processedItems[] = $itemData;
            }
            
            return json([
                'code' => 200,
                'data' => [
                    'list' => $processedItems,
                    'total' => $list->total(),
                    'page' => $list->currentPage(),
                    'limit' => $list->listRows()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error("获取待审核教师列表失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '获取待审核教师列表失败']);
        }
    }
    
    /**
     * 获取待审核教师数量
     */
    public function getPendingCount()
    {
        $user = $this->request->user;
        
        try {
            $count = Teacher::where('school_id', $user->primary_school_id)
                ->where('status', 2)  // 待审核状态
                ->count();
            
            return json([
                'code' => 200,
                'data' => $count
            ]);
            
        } catch (\Exception $e) {
            Log::error("获取待审核教师数量失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '获取待审核教师数量失败']);
        }
    }
    
    /**
     * 获取职称选项
     */
    public function getTitleOptions()
    {
        try {
            $options = TeacherTitle::getTitleOptions();
            if (!is_array($options)) {
                $options = [];
            }
            return json([
                'code' => 200,
                'data' => $options
            ]);
        } catch (\Exception $e) {
            Log::error("获取职称选项失败: " . $e->getMessage());
            return json(['code' => 200, 'data' => []]);
        }
    }
    
    /**
     * 获取教师统计信息
     */
    public function stats()
    {
        $school = $this->request->school;
        
        try {
            $stats = Teacher::getTeacherStats($school->id);
            
            // 按学院统计
            $collegeStats = Teacher::where('school_id', $school->id)
                ->where('status', 1)
                ->field('college_id, COUNT(*) as count')
                ->group('college_id')
                ->select();
            
            return json([
                'code' => 200,
                'data' => [
                    'overall' => $stats,
                    'by_college' => $collegeStats
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error("获取教师统计失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '获取教师统计失败']);
        }
    }

    /**
     * 格式化教师数据
     * 
     * @param Teacher $teacher 教师模型实例
     * @param bool $withRelations 是否包含关联数据
     * @param bool $isAuditContext 是否是审核上下文
     * @return array
     */
    private function formatTeacherData($teacher, $withRelations = false, $isAuditContext = false)
    {
        $itemData = $teacher->toArray();
        
        // 姓名字段映射（前端期望name字段）
        $itemData['name'] = $itemData['real_name'];
        
        // 学院信息处理
        $college = $itemData['college'] ?? null;
        $itemData['collegeName'] = $college ? $college['name'] : '未分配学院';
        $itemData['collegeId'] = $college ? $college['id'] : null;
        
        // 用户登录信息
        $user = $itemData['user'] ?? null;
        $itemData['lastLoginTime'] = $user ? ($user['last_login_time'] ?? '') : '';
        
        // 职称名称转换
        $itemData['titleName'] = '';
        if (!empty($itemData['title'])) {
            $titleObj = \app\model\TeacherTitle::where('id', (int)$itemData['title'])->where('status', 1)->find();
            $itemData['titleName'] = $titleObj ? $titleObj['name'] : '未知职称';
        }
        
        // 入职时间处理
        $itemData['joinDate'] = $itemData['hire_date'] ?? '';
        
        // 格式化时间
        $itemData['createdAt'] = date('Y-m-d H:i:s', strtotime($itemData['create_time']));
        
        // 状态转换 - 根据上下文判断是审核状态还是启用状态
        if ($isAuditContext) {
            // 审核上下文：使用审核状态映射
            $itemData['status'] = Teacher::getAuditStatusName($itemData['status']);
        } else {
            // 列表上下文：使用启用状态映射
            $itemData['status'] = Teacher::getActiveStatusName($itemData['status']);
        }
        
        // 清理关联数据，避免前端显示 [object Object]
        unset($itemData['college'], $itemData['user'], $itemData['school']);
        
        return $itemData;
    }

    /**
     * 规范化状态值
     * 
     * @param string|int $status 前端传入的状态值
     * @return int 数据库状态值
     */
    private function normalizeStatus($status)
    {
        return Teacher::normalizeStatus($status);
    }

    /**
     * 更新学校教师数量缓存
     * 
     * @param int $schoolId 学校ID
     */
    private function updateSchoolTeacherCount($schoolId)
    {
        $cacheKey = 'school_teacher_count_' . $schoolId;
        $count = Teacher::where('school_id', $schoolId)->count();
        Cache::set($cacheKey, $count, 3600); // 缓存1小时
    }
} 