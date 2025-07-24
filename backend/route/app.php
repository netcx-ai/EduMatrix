<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

Route::get('think', function () {
    return 'hello,ThinkPHP6!';
});

Route::get('hello/:name', 'index/hello');

Route::get('test', 'Test/index');

// 测试路由（不需要认证）
Route::get('test/school/getAll', '\app\controller\admin\SchoolController@getAll');
Route::get('test/school/test', '\app\controller\admin\SchoolController@test');

// API 路由组
Route::group('api', function () {
    // 文件下载（带附件头），放最前面优先匹配，需 JWT
    Route::get('teacher/files/<id>/raw', '\app\controller\api\teacher\FileController@raw')
        ->pattern(['id' => '\d+'])
        ->middleware([\app\middleware\JwtAuth::class]);

    // 用户相关路由
    Route::group('user', function () {
        Route::post('register', '\app\controller\api\User@register');
        Route::post('login', '\app\controller\api\User@login');
        Route::get('info', '\app\controller\api\User@info');
        Route::post('logout', '\app\controller\api\User@logout');
        Route::post('resetPassword', '\app\controller\api\User@resetPassword');
    });
    
    // 通用认证路由
    Route::group('auth', function () {
        Route::post('logout', '\app\controller\api\User@logout');
    });

    // 短信相关路由
    Route::group('sms', function () {
        Route::post('sendCode', '\app\controller\common\Sms@sendCode');
        Route::post('verifyCode', '\app\controller\common\Sms@verifyCode');
    });
    
    // AI服务相关路由
    Route::group('ai', function () {
        Route::get('tools', '\app\controller\AiService@getTools');
        Route::post('call', '\app\controller\AiService@call');
        Route::get('usage/history', '\app\controller\AiService@getUsageHistory');
        Route::get('usage/statistics', '\app\controller\AiService@getUsageStatistics');
    });
    
    // 教师端认证路由（无需JWT认证）
    Route::group('teacher', function () {
        Route::post('login', '\app\controller\api\teacher\AuthController@login');
        Route::post('register', '\app\controller\api\teacher\AuthController@register');
    });
    
    // 教师端API路由（需要JWT认证）
    Route::group('teacher', function () {
        // 认证相关
        Route::get('info', '\app\controller\api\teacher\AuthController@info');
        // 兼容前端调用 /teacher/profile 获取教师信息
        Route::get('profile', '\app\controller\api\teacher\AuthController@info');
        Route::post('logout', '\app\controller\api\teacher\AuthController@logout');
        Route::put('password', '\app\controller\api\teacher\AuthController@changePassword');
        
        // 课程管理（具体路由要放在resource路由之前）
        Route::get('courses', '\app\controller\api\teacher\CourseController@index');
        Route::get('courses/:id', '\app\controller\api\teacher\CourseController@show');
        Route::get('colleges', '\app\controller\api\teacher\CourseController@colleges');
        // 新增：层级化课程数据
        Route::get('courses/hierarchical', '\app\controller\api\teacher\CourseController@hierarchical');
        Route::get('courses/options', '\app\controller\api\teacher\CourseController@options');
        // 教师端课程资源路由（使用更具体的路径避免冲突）
        Route::resource('teacher-courses', '\app\controller\api\teacher\CourseController');
        
        // 内容管理
        Route::post('content/upload', '\app\controller\api\teacher\ContentController@upload');
        Route::post('content/submit', '\app\controller\api\teacher\ContentController@submit');
        Route::get('content/<id>\d+', '\app\controller\api\teacher\ContentController@show');
        Route::put('content/visibility', '\app\controller\api\teacher\ContentController@visibility');
        Route::get('content/personal', '\app\controller\api\teacher\ContentController@personalList');
        Route::get('content/course', '\app\controller\api\teacher\ContentController@courseList');
        
        // 内容中心新增接口
        Route::post('content/regenerate', '\app\controller\api\teacher\ContentController@regenerate');
        Route::post('content/export-word', '\app\controller\api\teacher\ContentController@exportWord');
        Route::post('content/save-to-file-center', '\app\controller\api\teacher\ContentController@saveToFileCenter');
        
        // 内容库管理
        Route::get('content/list', '\app\controller\api\teacher\ContentController@getList');
        Route::get('content/detail/:id', '\app\controller\api\teacher\ContentController@getDetail');
        Route::post('content/export', '\app\controller\api\teacher\ContentController@exportDocument');
        Route::get('content/download/:filename', '\app\controller\api\teacher\ContentController@downloadDocument');
        Route::post('content/submit-audit', '\app\controller\api\teacher\ContentController@submitAudit');
        Route::delete('content/delete/:id', '\app\controller\api\teacher\ContentController@deleteContent');
        Route::get('content/statistics', '\app\controller\api\teacher\ContentController@getStatistics');
        Route::post('content/create', '\app\controller\api\teacher\ContentController@createContent');
        Route::put('content/update/:id', '\app\controller\api\teacher\ContentController@updateContent');
        Route::get('content/available-files', '\app\controller\api\teacher\ContentController@getAvailableFiles');
        Route::post('content/associate-files', '\app\controller\api\teacher\ContentController@associateFiles');
        
        // 内容预览
        Route::get('preview/content/:id', '\app\controller\api\teacher\ContentPreviewController@show');
        
        // AI 工具相关
        Route::get('ai/tools', '\app\controller\api\teacher\AiController@getTools');
        Route::post('ai/generate', '\app\controller\api\teacher\AiController@generate');
        Route::post('ai/batch-generate', '\app\controller\api\teacher\AiController@batchGenerate');
        Route::post('ai/regenerate', '\app\controller\api\teacher\AiController@regenerate');
        Route::get('ai/history', '\app\controller\api\teacher\AiController@getHistory');
        Route::get('ai/statistics', '\app\controller\api\teacher\AiController@getStatistics');
        
        // 教师端AI工具路由
        Route::get('ai-tool/list', '\app\controller\api\teacher\AiToolController@getList');
        Route::get('ai-tool/detail', '\app\controller\api\teacher\AiToolController@getDetail');
        Route::post('ai-tool/generate', '\app\controller\api\teacher\AiToolController@generate');
        Route::post('ai-tool/save-content', '\app\controller\api\teacher\AiToolController@saveContent');
        Route::get('ai-tool/categories', '\app\controller\api\teacher\AiToolController@getCategories');
        Route::post('ai-tool/export-word', '\app\controller\api\teacher\AiToolController@exportWord');
        Route::post('ai-tool/save-to-file-center', '\app\controller\api\teacher\AiToolController@saveToFileCenter');
        // 新增：获取工具表单配置
        Route::get('ai-tool/getToolFormConfig', '\app\controller\api\teacher\AiToolController@getToolFormConfig');
        Route::get('ai-tool/getAllToolsFormConfig', '\app\controller\api\teacher\AiToolController@getAllToolsFormConfig');
        

        
        // 统计概览
        Route::get('statistics', '\app\controller\api\teacher\StatsController@overview');
        
        // 文件管理（先定义 Raw 下载，再注册 resource 避免路由冲突）
        Route::get('files/<id>\d+/raw', '\app\controller\api\teacher\FileController@raw');
        Route::resource('files', '\app\controller\api\teacher\FileController');
        Route::post('files/upload', '\app\controller\api\teacher\FileController@upload');
        Route::get('files/:id/download', '\app\controller\api\teacher\FileController@download');
        Route::get('files/categories/options', '\app\controller\api\teacher\FileController@categories');
        Route::get('files/courses/options', '\app\controller\api\teacher\FileController@courses');
    })->middleware([\app\middleware\JwtAuth::class]);
    
    // 学校端API路由组
    Route::group('school', function () {
        // 统计接口
        Route::get('stats', '\app\controller\api\school\StatsController@overview');
        Route::get('statistics', '\app\controller\api\school\StatsController@statistics');
        Route::get('statistics/list', '\app\controller\api\school\StatsController@statisticsList');
        Route::get('statistics/trend', '\app\controller\api\school\StatsController@visitTrend');
        Route::get('statistics/popular', '\app\controller\api\school\StatsController@popularPages');
        Route::post('statistics/export', '\app\controller\api\school\StatsController@exportStatistics');
        
        // 学院管理（特殊路由在前）
        Route::get('college/list', '\app\controller\api\school\CollegeController@list');
        Route::get('college/test', '\app\controller\api\school\CollegeController@test');
        
        // 学院管理（RESTful资源路由）
        Route::resource('college', '\app\controller\api\school\CollegeController');
        
        // 教师审核相关接口（必须在resource路由之前定义）
        Route::get('teachers/pending', '\app\controller\api\school\TeacherController@pending');
        Route::get('teachers/audit', '\app\controller\api\school\TeacherController@pending'); // 兼容前端audit接口
        Route::post('teachers/:id/verify', '\app\controller\api\school\TeacherController@verify');
        Route::post('teachers/:id/approve', '\app\controller\api\school\TeacherController@approve'); // 通过审核
        Route::post('teachers/:id/reject', '\app\controller\api\school\TeacherController@reject'); // 拒绝审核
        Route::post('teachers/batch-verify', '\app\controller\api\school\TeacherController@batchVerify');
        Route::post('teachers/batch-audit', '\app\controller\api\school\TeacherController@batchVerify'); // 兼容前端batch-audit接口
        Route::get('teachers/pending-count', '\app\controller\api\school\TeacherController@getPendingCount');
        
        // 教师管理
        Route::resource('teachers', '\app\controller\api\school\TeacherController');
        
        // 职称选项
        Route::get('teacher_title/options', '\app\controller\api\school\TeacherController@getTitleOptions');
        
        // 课程管理
        Route::get('courses/:id', '\app\controller\api\school\CourseController@show');
        Route::get('courses', '\app\controller\api\school\CourseController@index');
        Route::post('courses', '\app\controller\api\school\CourseController@store');
        Route::put('courses/:id', '\app\controller\api\school\CourseController@update');
        Route::delete('courses/:id', '\app\controller\api\school\CourseController@destroy');
        
        // 学校设置
        Route::get('settings', '\app\controller\api\school\SettingsController@index');
        Route::post('settings', '\app\controller\api\school\SettingsController@store');
        
        // 最近活动
        Route::get('activity', '\app\controller\api\school\ActivityController@index');
        
        // 个人资料
        Route::get('profile', '\app\controller\api\school\ProfileController@index');
        Route::put('profile', '\app\controller\api\school\ProfileController@update');
        Route::post('profile/password', '\app\controller\api\school\ProfileController@changePassword');
        Route::post('profile/avatar', '\app\controller\api\school\ProfileController@uploadAvatar');
        

        

        
        // 内容审核
        Route::group('audit', function () {
            // 旧文件审核接口保留
            Route::get('files', '\app\controller\api\school\AuditController@files');
            Route::get('files/:id', '\app\controller\api\school\AuditController@show');
            Route::post('files/:id/review', '\app\controller\api\school\AuditController@review');

            // 新内容审核接口
            Route::get('content', '\app\controller\api\school\ContentAuditController@pending');
            Route::post('content/:id', '\app\controller\api\school\ContentAuditController@review');

            Route::post('files/batch-review', '\app\controller\api\school\AuditController@batchReview');
            Route::get('statistics', '\app\controller\api\school\AuditController@statistics');
            Route::get('status-options', '\app\controller\api\school\AuditController@statusOptions');
            Route::get('type-options', '\app\controller\api\school\AuditController@typeOptions');
        });
    })->middleware([\app\middleware\JwtAuth::class, \app\middleware\SchoolAuth::class]);
})->middleware(\app\middleware\AllowCrossDomain::class);

// 管理后台路由组
Route::group('admin', function () {
    // 登录页面
    Route::get('login', '\app\controller\admin\Admin@loginPage');
    Route::post('login', '\app\controller\admin\Admin@login');
    Route::post('logout', '\app\controller\admin\Admin@logout');
    
    // 需要认证的路由
    Route::group(function () {
        // 后台首页
        Route::get('index', '\app\controller\admin\Index@index');
        
        Route::get('info', '\app\controller\admin\Admin@info');
        Route::get('change-password', '\app\controller\admin\Admin@changePasswordPage');
        Route::post('change-password', '\app\controller\admin\Admin@changePassword');
        Route::get('logs', '\app\controller\admin\Admin@getLogs');
        Route::post('send-code', '\app\controller\admin\Admin@sendCode');
        
        // 登录日志管理
        Route::get('login_log', '\app\controller\admin\Admin@loginLog');
        Route::get('login_log/index', '\app\controller\admin\Admin@loginLog');
        Route::post('clean_login_log', '\app\controller\admin\Admin@cleanLoginLog');
        Route::get('export_login_log', '\app\controller\admin\Admin@exportLoginLog');
        
        // 用户管理
        Route::group('user', function () {
            Route::get('index', '\app\controller\admin\User@index');
            Route::post('index', '\app\controller\admin\User@index');
            Route::get('add', '\app\controller\admin\User@add');
            Route::post('add', '\app\controller\admin\User@add');
            Route::get('edit', '\app\controller\admin\User@edit');
            Route::post('edit', '\app\controller\admin\User@edit');
            Route::post('delete', '\app\controller\admin\User@delete');
            Route::post('changeStatus', '\app\controller\admin\User@changeStatus');
            Route::get('role', '\app\controller\admin\User@role');
            Route::post('role', '\app\controller\admin\User@role');
            Route::get('permission', '\app\controller\admin\User@permission');
            Route::post('permission', '\app\controller\admin\User@permission');
        });
        
        // 角色管理
        Route::group('role', function () {
            Route::get('index', '\app\controller\admin\Role@index');
            Route::post('index', '\app\controller\admin\Role@index');
            Route::get('add', '\app\controller\admin\Role@add');
            Route::post('add', '\app\controller\admin\Role@add');
            Route::get('edit', '\app\controller\admin\Role@edit');
            Route::post('edit', '\app\controller\admin\Role@edit');
            Route::post('delete', '\app\controller\admin\Role@delete');
            Route::get('permission', '\app\controller\admin\Role@permission');
            Route::get('getPermissions', '\app\controller\admin\Role@getPermissions');
            Route::post('savePermissions', '\app\controller\admin\Role@savePermissions');
        });
        
        // 内容库管理
        Route::group('content_library', function () {
            Route::get('index', '\app\controller\admin\ContentLibraryController@index');
            Route::post('index', '\app\controller\admin\ContentLibraryController@index');
            Route::get('audit', '\app\controller\admin\ContentLibraryController@audit');
            Route::post('audit', '\app\controller\admin\ContentLibraryController@audit');
            Route::get('view', '\app\controller\admin\ContentLibraryController@view');
            Route::post('auditAction', '\app\controller\admin\ContentLibraryController@auditAction');
            Route::post('delete', '\app\controller\admin\ContentLibraryController@delete');
            Route::post('batch', '\app\controller\admin\ContentLibraryController@batch');
            Route::get('statistics', '\app\controller\admin\ContentLibraryController@statistics');
            Route::post('statistics', '\app\controller\admin\ContentLibraryController@statistics');
        });
        
        // 权限管理
        Route::group('permission', function () {
            Route::get('index', '\app\controller\admin\Permission@index');
            Route::post('index', '\app\controller\admin\Permission@index');
            Route::get('add', '\app\controller\admin\Permission@add');
            Route::post('add', '\app\controller\admin\Permission@add');
            Route::get('edit', '\app\controller\admin\Permission@edit');
            Route::post('edit', '\app\controller\admin\Permission@edit');
            Route::post('delete', '\app\controller\admin\Permission@delete');
        });
        
        // 管理员管理
        Route::group('admin', function () {
            Route::get('index', '\app\controller\admin\Admin@index');
            Route::post('index', '\app\controller\admin\Admin@index');
            Route::get('add', '\app\controller\admin\Admin@add');
            Route::post('add', '\app\controller\admin\Admin@add');
            Route::get('edit', '\app\controller\admin\Admin@edit');
            Route::post('edit', '\app\controller\admin\Admin@edit');
            Route::post('delete', '\app\controller\admin\Admin@delete');
            Route::post('changeStatus', '\app\controller\admin\Admin@changeStatus');
        });
        
        // 文章管理
        Route::group('article', function () {
            Route::get('article', '\app\controller\admin\ArticleController@article');
            Route::post('article', '\app\controller\admin\ArticleController@article');
            Route::get('addArticle', '\app\controller\admin\ArticleController@addArticle');
            Route::post('addArticle', '\app\controller\admin\ArticleController@addArticle');
            Route::get('editArticle', '\app\controller\admin\ArticleController@editArticle');
            Route::post('editArticle', '\app\controller\admin\ArticleController@editArticle');
            Route::post('deleteArticle', '\app\controller\admin\ArticleController@deleteArticle');
            Route::post('batchArticle', '\app\controller\admin\ArticleController@batchArticle');
            Route::post('unpublishArticle', '\app\controller\admin\ArticleController@unpublishArticle');
            Route::post('publishArticle', '\app\controller\admin\ArticleController@publishArticle');
            Route::get('viewArticle', '\app\controller\admin\ArticleController@viewArticle');
            Route::post('copy', '\app\controller\admin\ArticleController@copy');
            Route::post('move', '\app\controller\admin\ArticleController@move');
            Route::post('destroy', '\app\controller\admin\ArticleController@destroy');
            Route::get('preview', '\app\controller\admin\ArticleController@preview');
            Route::get('audit', '\app\controller\admin\ArticleController@audit');
            Route::post('audit', '\app\controller\admin\ArticleController@audit');
            Route::post('approve', '\app\controller\admin\ArticleController@approve');
            Route::post('reject', '\app\controller\admin\ArticleController@reject');
            Route::get('stats', '\app\controller\admin\ArticleController@stats');
            Route::post('stats', '\app\controller\admin\ArticleController@stats');
            
            // 文章分类管理
            Route::get('category', '\app\controller\admin\ArticleController@category');
            Route::post('category', '\app\controller\admin\ArticleController@category');
            Route::get('addCategory', '\app\controller\admin\ArticleController@addCategory');
            Route::post('addCategory', '\app\controller\admin\ArticleController@addCategory');
            Route::get('editCategory', '\app\controller\admin\ArticleController@editCategory');
            Route::post('editCategory', '\app\controller\admin\ArticleController@editCategory');
            Route::post('deleteCategory', '\app\controller\admin\ArticleController@deleteCategory');
            Route::get('getCategoryTree', '\app\controller\admin\ArticleController@getCategoryTree');
            
            // 文章标签管理
            Route::get('tag', '\app\controller\admin\ArticleController@tag');
            Route::post('tag', '\app\controller\admin\ArticleController@tag');
            Route::get('addTag', '\app\controller\admin\ArticleController@addTag');
            Route::post('addTag', '\app\controller\admin\ArticleController@addTag');
            Route::get('editTag', '\app\controller\admin\ArticleController@editTag');
            Route::post('editTag', '\app\controller\admin\ArticleController@editTag');
            Route::post('deleteTag', '\app\controller\admin\ArticleController@deleteTag');
            Route::get('getTagList', '\app\controller\admin\ArticleController@getTagList');
        });
        

        
        // 文件管理
        Route::group('file', function () {
            Route::get('index', '\app\controller\admin\File@index');
            Route::post('index', '\app\controller\admin\File@index');
            Route::get('detail', '\app\controller\admin\File@detail');
            Route::get('show/:id', '\app\controller\admin\File@show');
            Route::get('download', '\app\controller\admin\File@download');
            Route::get('raw', '\app\controller\admin\File@raw');
            Route::post('store', '\app\controller\admin\File@store');
            Route::put('update/:id', '\app\controller\admin\File@update');
            Route::delete('destroy/:id', '\app\controller\admin\File@destroy');
            Route::post('batch', '\app\controller\admin\File@batch');
            Route::get('categories', '\app\controller\admin\File@categories');
            Route::get('tags', '\app\controller\admin\File@tags');
            Route::post('setPermission', '\app\controller\admin\File@setPermission');
            Route::get('permissions/:fileId', '\app\controller\admin\File@permissions');
            Route::post('deletePermission', '\app\controller\admin\File@deletePermission');
            Route::get('shares/:fileId', '\app\controller\admin\File@shares');
            Route::get('logs', '\app\controller\admin\File@logs');
            Route::get('statistics', '\app\controller\admin\File@statistics');
            Route::get('users', '\app\controller\admin\File@users');
        });
        
        // 课程标签管理
        Route::group('course', function () {
            Route::get('tag', '\app\controller\admin\CourseController@tag');
            Route::post('tag', '\app\controller\admin\CourseController@tag');
            Route::get('addTag', '\app\controller\admin\CourseController@addTag');
            Route::post('addTag', '\app\controller\admin\CourseController@addTag');
            Route::get('editTag', '\app\controller\admin\CourseController@editTag');
            Route::post('editTag', '\app\controller\admin\CourseController@editTag');
            Route::post('deleteTag', '\app\controller\admin\CourseController@deleteTag');
            Route::get('getTagList', '\app\controller\admin\CourseController@getTagList');
        });
        
        // 数据统计
        Route::group('stats', function () {
            Route::get('overview', '\app\controller\admin\Stats@overview');
            Route::get('user', '\app\controller\admin\Stats@user');
            Route::post('user', '\app\controller\admin\Stats@user');
            Route::get('content', '\app\controller\admin\Stats@content');
            Route::post('content', '\app\controller\admin\Stats@content');
            Route::get('visit', '\app\controller\admin\Stats@visit');
            Route::post('visit', '\app\controller\admin\Stats@visit');
            Route::get('education', '\app\controller\admin\Stats@education');
            Route::get('getSchoolStats', '\app\controller\admin\Stats@getSchoolStats');
            Route::get('realtime', '\app\controller\admin\Stats@realtime');
        });
        
        // 系统工具
        Route::group('tools', function () {
            Route::get('cache', '\app\controller\admin\Tools@cache');
            Route::post('cache', '\app\controller\admin\Tools@cache');
            Route::get('log', '\app\controller\admin\Tools@log');
            Route::post('log', '\app\controller\admin\Tools@log');
            Route::get('exportLog', '\app\controller\admin\Tools@exportLog');
            Route::post('clearLog', '\app\controller\admin\Tools@clearLog');
            Route::get('logStats', '\app\controller\admin\Tools@logStats');
            Route::get('backup', '\app\controller\admin\Tools@backup');
            Route::post('backup', '\app\controller\admin\Tools@backup');
            Route::get('monitor', '\app\controller\admin\Tools@monitor');
            Route::post('monitor', '\app\controller\admin\Tools@monitor');
        });
        
        // 系统配置管理
        Route::group('system_config', function () {
            Route::get('index', '\app\controller\admin\SystemConfig@index');
            Route::get('add', '\app\controller\admin\SystemConfig@add');
            Route::post('add', '\app\controller\admin\SystemConfig@add');
            Route::get('edit', '\app\controller\admin\SystemConfig@edit');
            Route::post('edit', '\app\controller\admin\SystemConfig@edit');
            Route::post('delete', '\app\controller\admin\SystemConfig@delete');
            Route::post('setDefault', '\app\controller\admin\SystemConfig@setDefault');
        });
        
        // 教育管理
        Route::group('school', function () {
            Route::get('index', '\app\controller\admin\SchoolController@index');
            Route::post('index', '\app\controller\admin\SchoolController@index');
            Route::get('add', '\app\controller\admin\SchoolController@add');
            Route::post('add', '\app\controller\admin\SchoolController@add');
            Route::get('edit', '\app\controller\admin\SchoolController@edit');
            Route::post('edit', '\app\controller\admin\SchoolController@edit');
            Route::post('delete', '\app\controller\admin\SchoolController@delete');
            Route::get('detail', '\app\controller\admin\SchoolController@detail');
            Route::post('changeStatus', '\app\controller\admin\SchoolController@changeStatus');
            Route::get('getList', '\app\controller\admin\SchoolController@getList');
            Route::get('getDetail', '\app\controller\admin\SchoolController@getDetail');
            Route::get('getAll', '\app\controller\admin\SchoolController@getAll');
            Route::get('provinces', '\app\controller\admin\SchoolController@provinces');
            Route::get('cities', '\app\controller\admin\SchoolController@cities');
            Route::get('types', '\app\controller\admin\SchoolController@types');
            Route::get('stats', '\app\controller\admin\SchoolController@stats');
        });
        
        Route::group('college', function () {
            Route::get('index', '\app\controller\admin\CollegeController@index');
            Route::post('index', '\app\controller\admin\CollegeController@index');
            Route::get('add', '\app\controller\admin\CollegeController@add');
            Route::post('add', '\app\controller\admin\CollegeController@add');
            Route::get('edit', '\app\controller\admin\CollegeController@edit');
            Route::post('edit', '\app\controller\admin\CollegeController@edit');
            Route::post('delete', '\app\controller\admin\CollegeController@delete');
            Route::get('detail', '\app\controller\admin\CollegeController@detail');
            Route::post('changeStatus', '\app\controller\admin\CollegeController@changeStatus');
            Route::get('getList', '\app\controller\admin\CollegeController@getList');
            Route::get('getDetail', '\app\controller\admin\CollegeController@getDetail');
        });
        
        Route::group('teacher', function () {
            Route::get('index', '\app\controller\admin\TeacherController@index');
            Route::post('index', '\app\controller\admin\TeacherController@index');
            Route::get('add', '\app\controller\admin\TeacherController@add');
            Route::post('add', '\app\controller\admin\TeacherController@add');
            Route::get('edit', '\app\controller\admin\TeacherController@edit');
            Route::post('edit', '\app\controller\admin\TeacherController@edit');
            Route::post('update/:id', '\app\controller\admin\TeacherController@update');
            Route::post('delete', '\app\controller\admin\TeacherController@delete');
            Route::get('detail', '\app\controller\admin\TeacherController@detail');
            Route::post('approve', '\app\controller\admin\TeacherController@approve');
            Route::post('changeStatus', '\app\controller\admin\TeacherController@changeStatus');
            Route::get('getList', '\app\controller\admin\TeacherController@getList');
            Route::get('getDetail', '\app\controller\admin\TeacherController@getDetail');
        });

        // 教师职称管理路由
        Route::group('teacher_title', function () {
            Route::get('index', '\app\controller\admin\TeacherTitleController@index');
            Route::post('index', '\app\controller\admin\TeacherTitleController@index');
            Route::get('add', '\app\controller\admin\TeacherTitleController@add');
            Route::post('store', '\app\controller\admin\TeacherTitleController@store');
            Route::get('edit', '\app\controller\admin\TeacherTitleController@edit');
            Route::post('update/:id', '\app\controller\admin\TeacherTitleController@update');
            Route::post('destroy/:id', '\app\controller\admin\TeacherTitleController@destroy');
            Route::get('detail', '\app\controller\admin\TeacherTitleController@detail');
            Route::post('changeStatus', '\app\controller\admin\TeacherTitleController@changeStatus');
            Route::post('updateSort', '\app\controller\admin\TeacherTitleController@updateSort');
            Route::get('getOptions', '\app\controller\admin\TeacherTitleController@getOptions');
            Route::get('getLevelOptions', '\app\controller\admin\TeacherTitleController@getLevelOptions');
            Route::post('batchDelete', '\app\controller\admin\TeacherTitleController@batchDelete');
            Route::post('clearCache', '\app\controller\admin\TeacherTitleController@clearCache');
        });
        
        Route::group('course', function () {
            Route::get('index', '\app\controller\admin\CourseController@index');
            Route::post('index', '\app\controller\admin\CourseController@index');
            Route::get('add', '\app\controller\admin\CourseController@add');
            Route::post('add', '\app\controller\admin\CourseController@add');
            Route::get('edit', '\app\controller\admin\CourseController@edit');
            Route::post('edit', '\app\controller\admin\CourseController@edit');
            Route::post('update/:id', '\app\controller\admin\CourseController@update');
            Route::post('delete', '\app\controller\admin\CourseController@delete');
            Route::get('detail', '\app\controller\admin\CourseController@detail');
            Route::post('changeStatus', '\app\controller\admin\CourseController@changeStatus');
            Route::get('getList', '\app\controller\admin\CourseController@getList');
            Route::get('getDetail', '\app\controller\admin\CourseController@getDetail');
            Route::get('tags', '\app\controller\admin\CourseController@tags');
            Route::post('updateTags', '\app\controller\admin\CourseController@updateTags');
        });
        
        Route::group('school_admin', function () {
            Route::get('index', '\app\controller\admin\SchoolAdminController@index');
            Route::post('index', '\app\controller\admin\SchoolAdminController@index');
            Route::get('add', '\app\controller\admin\SchoolAdminController@add');
            Route::post('add', '\app\controller\admin\SchoolAdminController@add');
            Route::get('edit', '\app\controller\admin\SchoolAdminController@edit');
            Route::post('edit', '\app\controller\admin\SchoolAdminController@edit');
            Route::post('update/:id', '\app\controller\admin\SchoolAdminController@update');
            Route::post('delete', '\app\controller\admin\SchoolAdminController@delete');
            Route::get('detail', '\app\controller\admin\SchoolAdminController@detail');
            Route::post('changeStatus', '\app\controller\admin\SchoolAdminController@changeStatus');
            Route::get('getList', '\app\controller\admin\SchoolAdminController@getList');
            Route::get('getDetail', '\app\controller\admin\SchoolAdminController@getDetail');
        });
        
        // AI工具管理
        Route::group('ai_tool', function () {
            Route::get('index', '\\app\\controller\\admin\\AiToolController@index');
            Route::post('index', '\\app\\controller\\admin\\AiToolController@index');
            Route::get('add', '\\app\\controller\\admin\\AiToolController@add');
            Route::post('add', '\\app\\controller\\admin\\AiToolController@add');
            Route::get('edit', '\\app\\controller\\admin\\AiToolController@edit');
            Route::post('edit', '\\app\\controller\\admin\\AiToolController@edit');
            Route::post('delete', '\\app\\controller\\admin\\AiToolController@delete');
            Route::get('detail', '\\app\\controller\\admin\\AiToolController@detail');
            Route::post('changeStatus', '\\app\\controller\\admin\\AiToolController@changeStatus');
            Route::get('getList', '\\app\\controller\\admin\\AiToolController@getList');
            Route::get('getDetail', '\\app\\controller\\admin\\AiToolController@getDetail');
            Route::get('config', '\\app\\controller\\admin\\AiToolController@config');
            Route::post('config', '\\app\\controller\\admin\\AiToolController@config');
            Route::get('show/:id', '\\app\\controller\\admin\\AiToolController@show');
            Route::post('store', '\\app\\controller\\admin\\AiToolController@store');
            Route::put('update/:id', '\\app\\controller\\admin\\AiToolController@update');
            Route::delete('destroy/:id', '\\app\\controller\\admin\\AiToolController@destroy');
            Route::post('batch', '\\app\\controller\\admin\\AiToolController@batch');
            Route::get('schools', '\\app\\controller\\admin\\AiToolController@getSchools');
            Route::post('authorize', '\\app\\controller\\admin\\AiToolController@authorize');
            Route::post('revoke', '\\app\\controller\\admin\\AiToolController@revoke');
            Route::get('statistics', '\\app\\controller\\admin\\AiToolController@statistics');
            Route::post('enable', '\\app\\controller\\admin\\AiToolController@enable');
            Route::post('disable', '\\app\\controller\\admin\\AiToolController@disable');
            
            // 使用统计
            Route::get('usage', '\\app\\controller\\admin\\AiToolUsageController@index');
            Route::get('usage/list', '\\app\\controller\\admin\\AiToolUsageController@getList');
            Route::get('usage/stats', '\\app\\controller\\admin\\AiToolUsageController@getStats');
        });
        
        // 通用工具管理
        Route::group('tool', function () {
            Route::get('index', '\\app\\controller\\admin\\ToolController@index');
            Route::post('index', '\\app\\controller\\admin\\ToolController@index');
            Route::get('show/:id', '\\app\\controller\\admin\\ToolController@show');
            Route::post('store', '\\app\\controller\\admin\\ToolController@store');
            Route::put('update/:id', '\\app\\controller\\admin\\ToolController@update');
            Route::delete('destroy/:id', '\\app\\controller\\admin\\ToolController@destroy');
            Route::post('assignSchools/:id', '\\app\\controller\\admin\\ToolController@assignSchools');
            Route::get('categories', '\\app\\controller\\admin\\ToolController@categories');
            Route::get('stats', '\\app\\controller\\admin\\ToolController@stats');
        });
        
        // 系统设置管理
        Route::group('system_setting', function () {
            Route::get('index', '\app\controller\admin\SystemSetting@index');
            Route::post('save', '\app\controller\admin\SystemSetting@save');
            Route::get('add', '\app\controller\admin\SystemSetting@add');
            Route::post('add', '\app\controller\admin\SystemSetting@add');
            Route::get('edit', '\app\controller\admin\SystemSetting@edit');
            Route::post('edit', '\app\controller\admin\SystemSetting@edit');
            Route::post('delete', '\app\controller\admin\SystemSetting@delete');
            Route::get('getValue', '\app\controller\admin\SystemSetting@getValue');
            Route::post('clearCache', '\app\controller\admin\SystemSetting@clearCache');
            Route::get('export', '\app\controller\admin\SystemSetting@export');
            Route::get('import', '\app\controller\admin\SystemSetting@import');
            Route::post('import', '\app\controller\admin\SystemSetting@import');
            Route::get('test', '\app\controller\admin\SystemSetting@test');
        });
        
        // 用户日志管理
        Route::group('user_log', function () {
            Route::get('index', '\app\controller\admin\UserLogController@index');
            Route::post('clean', '\app\controller\admin\UserLogController@clean');
            Route::get('export', '\app\controller\admin\UserLogController@export');
            Route::get('getTypeList', '\app\controller\admin\UserLogController@getTypeList');
        });
    })->middleware(\app\middleware\AdminAuth::class);
});
