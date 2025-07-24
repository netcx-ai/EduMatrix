<?php
require_once __DIR__ . '/vendor/autoload.php';

// 初始化应用
$app = new think\App();
$app->initialize();

use think\facade\Db;
use app\util\JwtUtil;
use app\middleware\JwtAuth;
use app\middleware\SchoolAuth;

echo "=== 调试课程API完整流程 ===\n";

try {
    // 1. 获取学校管理员用户
    $adminUser = Db::name('user')
        ->where('user_type', 'school_admin')
        ->where('status', 1)
        ->limit(1)
        ->find();
    
    if (!$adminUser) {
        echo "没有找到学校管理员用户\n";
        exit;
    }
    
    echo "管理员用户: {$adminUser['username']}\n";
    echo "学校ID: {$adminUser['primary_school_id']}\n";
    
    // 2. 获取学校信息
    $school = Db::name('school')->where('id', $adminUser['primary_school_id'])->find();
    if (!$school) {
        echo "学校信息不存在\n";
        exit;
    }
    
    echo "学校名称: {$school['name']}\n";
    echo "学校编码: {$school['code']}\n";
    
    // 3. 生成JWT token
    $payload = [
        'user_id' => $adminUser['id'],
        'user_type' => $adminUser['user_type'],
        'username' => $adminUser['username'],
        'primary_school_id' => $adminUser['primary_school_id'],
    ];
    $token = JwtUtil::createToken($payload);
    
    // 4. 创建请求对象
    $request = new \think\Request();
    $request->header('Authorization', 'Bearer ' . $token);
    $request->header('X-School-Code', $school['code']);
    $request->param('page', 1);
    $request->param('pageSize', 10);
    $request->param('name', '');
    $request->param('status', '');
    
    echo "\n=== 模拟中间件执行 ===\n";
    
    // 5. 模拟JwtAuth中间件
    echo "执行JwtAuth中间件...\n";
    $jwtAuth = new JwtAuth();
    $jwtResult = $jwtAuth->handle($request, function($req) {
        echo "JwtAuth中间件通过\n";
        return true;
    });
    
    if ($jwtResult !== true) {
        echo "JwtAuth中间件失败\n";
        exit;
    }
    
    // 6. 检查用户信息
    echo "用户信息检查:\n";
    echo "user_id: " . ($request->userId ?? 'null') . "\n";
    echo "user_type: " . ($request->userType ?? 'null') . "\n";
    echo "user对象: " . (isset($request->user) ? '存在' : '不存在') . "\n";
    
    if (isset($request->user)) {
        echo "user->id: " . $request->user->id . "\n";
        echo "user->primary_school_id: " . $request->user->primary_school_id . "\n";
    }
    
    // 7. 模拟SchoolAuth中间件
    echo "\n执行SchoolAuth中间件...\n";
    $schoolAuth = new SchoolAuth();
    $schoolResult = $schoolAuth->handle($request, function($req) {
        echo "SchoolAuth中间件通过\n";
        return true;
    });
    
    if ($schoolResult !== true) {
        echo "SchoolAuth中间件失败\n";
        exit;
    }
    
    // 8. 模拟课程控制器
    echo "\n=== 模拟课程控制器 ===\n";
    $user = $request->user;
    if (!$user) {
        echo "错误：用户信息不存在\n";
        exit;
    }
    
    echo "控制器中获取的用户信息:\n";
    echo "user->id: " . $user->id . "\n";
    echo "user->primary_school_id: " . $user->primary_school_id . "\n";
    
    // 9. 执行课程查询
    $query = Db::name('course')
        ->alias('c')
        ->join('college co', 'c.college_id = co.id')
        ->join('teacher t', 'c.responsible_teacher_id = t.id')
        ->where('co.school_id', $user->primary_school_id);
    
    $list = $query->field('c.*, co.name as college_name, t.real_name as teacher_name')
        ->order('c.create_time', 'desc')
        ->paginate([
            'list_rows' => 10,
            'page' => 1
        ]);
    
    echo "课程查询成功！\n";
    echo "总记录数: {$list->total()}\n";
    echo "当前页: {$list->currentPage()}\n";
    
    // 10. 模拟API响应
    $items = $list->items();
    foreach ($items as &$item) {
        $item['status'] = $item['status'] == 1 ? 'active' : 'inactive';
        $item['createdAt'] = date('Y-m-d H:i:s', strtotime($item['create_time']));
    }
    
    $response = [
        'code' => 200,
        'data' => [
            'list' => $items,
            'total' => $list->total(),
            'page' => $list->currentPage(),
            'pageSize' => $list->listRows()
        ]
    ];
    
    echo "\n=== API响应 ===\n";
    echo "响应码: " . $response['code'] . "\n";
    echo "课程数量: " . count($response['data']['list']) . "\n";
    echo "总记录数: " . $response['data']['total'] . "\n";
    
} catch (Exception $e) {
    echo "调试失败: " . $e->getMessage() . "\n";
    echo "错误位置: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "错误堆栈: " . $e->getTraceAsString() . "\n";
} 