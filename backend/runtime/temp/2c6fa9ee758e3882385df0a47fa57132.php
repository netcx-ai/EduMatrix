<?php /*a:2:{s:48:"F:\EduMatrix\backend\view\admin\index\index.html";i:1753341575;s:42:"F:\EduMatrix\backend\view\common\base.html";i:1753341575;}*/ ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>教育矩阵管理系统</title>
    <link rel="stylesheet" href="/static/layui/css/layui.css">
    <link rel="stylesheet" href="/static/admin/css/admin.css">
    <style>
    /* 菜单状态保持样式 */
    .layui-nav-itemed > .layui-nav-child {
        display: block !important;
    }
    
    /* 确保子菜单平滑展开 */
    .layui-nav-child {
        transition: all 0.2s ease-in-out;
    }
    </style>
    
<style>
.stat-item {
    text-align: center;
    padding: 20px;
}
.stat-number {
    font-size: 24px;
    font-weight: bold;
    color: #1E9FFF;
}
.stat-label {
    margin-top: 5px;
    color: #666;
}
.layui-card-header i {
    margin-right: 5px;
}
</style>

</head>
<body class="layui-layout-body">
    <div class="layui-layout layui-layout-admin">
        <!-- 头部区域 -->
        <div class="layui-header">
            <div class="layui-logo">教育矩阵管理系统</div>
            <ul class="layui-nav layui-layout-right">
                <li class="layui-nav-item">
                    <a href="javascript:;">
                        <img src="/static/admin/images/avatar.jpg" class="layui-nav-img">
                        <?php echo htmlentities((string) ''); ?>
                    </a>
                    <dl class="layui-nav-child">
                        <dd><a href="/admin/change-password">修改密码</a></dd>
                        <dd><a href="/admin/login_log">登录日志</a></dd>
                        <dd><a href="javascript:void(0);" id="logoutBtn">退出登录</a></dd>
                    </dl>
                </li>
            </ul>
        </div>
        
        <!-- 左侧导航区域 -->
        <div class="layui-side layui-bg-black">
            <div class="layui-side-scroll">
                <ul class="layui-nav layui-nav-tree">
                    <li class="layui-nav-item">
                        <a href="/admin/index/index">控制台</a>
                    </li>
                    <li class="layui-nav-item">
                        <a href="javascript:;">系统管理</a>
                        <dl class="layui-nav-child">
                            <dd><a href="/admin/system_setting/index">系统设置</a></dd>
                            <dd><a href="/admin/system_config/index">系统配置</a></dd>
                            <dd><a href="/admin/admin/index">管理员管理</a></dd>
                            <dd><a href="/admin/role/index">角色管理</a></dd>
                            <dd><a href="/admin/permission/index">权限管理</a></dd>
                        </dl>
                    </li>
                    <li class="layui-nav-item">
                        <a href="javascript:;">教育管理</a>
                        <dl class="layui-nav-child">
                            <dd><a href="/admin/school/index">学校管理</a></dd>
                            <dd><a href="/admin/college/index">学院管理</a></dd>
                            <dd><a href="/admin/teacher/index">教师管理</a></dd>
                            <dd><a href="/admin/teacher_title/index">职称管理</a></dd>
                            <dd><a href="/admin/course/index">课程管理</a></dd>
                            <dd><a href="/admin/school_admin/index">学校管理员</a></dd>
                        </dl>
                    </li>
                    <li class="layui-nav-item">
                        <a href="javascript:;">内容管理</a>
                        <dl class="layui-nav-child">
                            <dd><a href="/admin/content_library/index">内容库管理</a></dd>
                            <dd><a href="/admin/file/index">文件管理</a></dd>
                            <dd><a href="/admin/content_library/statistics">内容统计</a></dd>
                            <dd><a href="/admin/course/tag">课程标签</a></dd>
                        </dl>
                    </li>
                    <li class="layui-nav-item">
                        <a href="javascript:;">文章管理</a>
                        <dl class="layui-nav-child">
                            <dd><a href="/admin/article/article">文章列表</a></dd>
                            <dd><a href="/admin/article/category">文章分类</a></dd>
                            <dd><a href="/admin/article/tag">文章标签</a></dd>
                        </dl>
                    </li>
                    <li class="layui-nav-item">
                        <a href="javascript:;">AI工具</a>
                        <dl class="layui-nav-child">
                            <dd><a href="/admin/ai_tool/index">AI工具管理</a></dd>
                            <dd><a href="/admin/ai_tool/usage">使用统计</a></dd>
                        </dl>
                    </li>
                    <li class="layui-nav-item">
                        <a href="javascript:;">会员管理</a>
                        <dl class="layui-nav-child">
                            <dd><a href="/admin/user/index">会员列表</a></dd>
                            <dd><a href="/admin/user/level">会员等级</a></dd>
                            <dd><a href="/admin/user/points">积分管理</a></dd>
                            <dd><a href="/admin/user_log/index">用户日志</a></dd>
                        </dl>
                    </li>
                    <li class="layui-nav-item">
                        <a href="javascript:;">数据统计</a>
                        <dl class="layui-nav-child">
                            <dd><a href="/admin/stats/overview">概览统计</a></dd>
                            <dd><a href="/admin/stats/user">用户统计</a></dd>
                            <dd><a href="/admin/stats/content">内容统计</a></dd>
                            <dd><a href="/admin/stats/education">教育统计</a></dd>
                        </dl>
                    </li>
                    <li class="layui-nav-item">
                        <a href="javascript:;">系统工具</a>
                        <dl class="layui-nav-child">
                            <dd><a href="/admin/tools/cache">缓存管理</a></dd>
                            <dd><a href="/admin/tools/log">日志查看</a></dd>
                            <dd><a href="/admin/tools/backup">数据备份</a></dd>
                        </dl>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- 内容主体区域 -->
        <div class="layui-body">
            
<div class="layui-fluid">
    <!-- 统计卡片 -->
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md3">
            <div class="layui-card">
                <div class="layui-card-header">
                    <i class="layui-icon layui-icon-user" style="color: #1E9FFF;"></i>
                    管理员数量
                </div>
                <div class="layui-card-body">
                    <h2><?php echo htmlentities((string) $stats['admin_count']); ?></h2>
                </div>
            </div>
        </div>
        <div class="layui-col-md3">
            <div class="layui-card">
                <div class="layui-card-header">
                    <i class="layui-icon layui-icon-friends" style="color: #5FB878;"></i>
                    用户数量
                </div>
                <div class="layui-card-body">
                    <h2><?php echo htmlentities((string) $stats['user_count']); ?></h2>
                </div>
            </div>
        </div>
        <div class="layui-col-md3">
            <div class="layui-card">
                <div class="layui-card-header">
                    <i class="layui-icon layui-icon-file" style="color: #FFB800;"></i>
                    文章数量
                </div>
                <div class="layui-card-body">
                    <h2><?php echo htmlentities((string) $stats['article_count']); ?></h2>
                </div>
            </div>
        </div>
        <div class="layui-col-md3">
            <div class="layui-card">
                <div class="layui-card-header">
                    <i class="layui-icon layui-icon-tabs" style="color: #FF5722;"></i>
                    分类数量
                </div>
                <div class="layui-card-body">
                    <h2><?php echo htmlentities((string) $stats['category_count']); ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 系统信息和状态 -->
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md6">
            <div class="layui-card">
                <div class="layui-card-header">系统信息</div>
                <div class="layui-card-body">
                    <table class="layui-table" lay-skin="line">
                        <tbody>
                            <tr>
                                <td>平台名称</td>
                                <td><?php echo htmlentities((string) $systemInfo['site_name']); ?></td>
                            </tr>
                            <tr>
                                <td>系统版本</td>
                                <td><?php echo htmlentities((string) $systemInfo['version']); ?></td>
                            </tr>
                            <tr>
                                <td>时区设置</td>
                                <td><?php echo htmlentities((string) $systemInfo['timezone']); ?></td>
                            </tr>
                            <tr>
                                <td>默认语言</td>
                                <td><?php echo htmlentities((string) $systemInfo['language']); ?></td>
                            </tr>
                            <tr>
                                <td>维护模式</td>
                                <td>
                                    <?php if($systemStatus['maintenance_mode']): ?>
                                    <span class="layui-badge layui-bg-orange">已开启</span>
                                    <?php else: ?>
                                    <span class="layui-badge layui-bg-green">已关闭</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="layui-col-md6">
            <div class="layui-card">
                <div class="layui-card-header">系统状态</div>
                <div class="layui-card-body">
                    <table class="layui-table" lay-skin="line">
                        <tbody>
                            <tr>
                                <td>数据库状态</td>
                                <td>
                                    <?php if($systemStatus['database_status']): ?>
                                    <span class="layui-badge layui-bg-green">正常</span>
                                    <?php else: ?>
                                    <span class="layui-badge">异常</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>缓存状态</td>
                                <td>
                                    <?php if($systemStatus['cache_status']): ?>
                                    <span class="layui-badge layui-bg-green">正常</span>
                                    <?php else: ?>
                                    <span class="layui-badge">异常</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>磁盘使用</td>
                                <td>
                                    <div class="layui-progress layui-progress-big" lay-showpercent="true">
                                        <div class="layui-progress-bar" lay-percent="<?php echo htmlentities((string) $systemStatus['disk_usage']['percent']); ?>%"></div>
                                    </div>
                                    <small><?php echo htmlentities((string) $systemStatus['disk_usage']['used']); ?> / <?php echo htmlentities((string) $systemStatus['disk_usage']['total']); ?></small>
                                </td>
                            </tr>
                            <tr>
                                <td>内存使用</td>
                                <td>
                                    当前: <?php echo htmlentities((string) $systemStatus['memory_usage']['current']); ?> | 峰值: <?php echo htmlentities((string) $systemStatus['memory_usage']['peak']); ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 访问统计和最近登录 -->
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md6">
            <div class="layui-card">
                <div class="layui-card-header">访问统计</div>
                <div class="layui-card-body">
                    <div class="layui-row">
                        <div class="layui-col-md6">
                            <div class="stat-item">
                                <div class="stat-number"><?php echo htmlentities((string) $stats['today_visits']); ?></div>
                                <div class="stat-label">今日访问</div>
                            </div>
                        </div>
                        <div class="layui-col-md6">
                            <div class="stat-item">
                                <div class="stat-number"><?php echo htmlentities((string) $stats['month_visits']); ?></div>
                                <div class="stat-label">本月访问</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="layui-col-md6">
            <div class="layui-card">
                <div class="layui-card-header">最近登录</div>
                <div class="layui-card-body">
                    <?php if($recentLogins): ?>
                    <table class="layui-table" lay-skin="line">
                        <thead>
                            <tr>
                                <th>管理员</th>
                                <th>IP地址</th>
                                <th>登录时间</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recentLogins as $login): ?>
                            <tr>
                                <td><?php echo htmlentities((string) (isset($login['admin_name']) && ($login['admin_name'] !== '')?$login['admin_name']:'未知')); ?></td>
                                <td><?php echo htmlentities((string) $login['ip']); ?></td>
                                <td><?php echo htmlentities((string) date('m-d H:i',!is_numeric($login['create_time'])? strtotime($login['create_time']) : $login['create_time'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="layui-text" style="text-align: center; color: #999; padding: 20px;">
                        暂无登录记录
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 快捷操作 -->
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">快捷操作</div>
                <div class="layui-card-body">
                    <a href="/admin/system_setting/index" class="layui-btn layui-btn-normal">
                        <i class="layui-icon layui-icon-set"></i> 系统设置
                    </a>
                    <a href="/admin/system_config/index" class="layui-btn layui-btn-primary">
                        <i class="layui-icon layui-icon-console"></i> 系统配置
                    </a>
                    <a href="/admin/admin/index" class="layui-btn layui-btn-primary">
                        <i class="layui-icon layui-icon-user"></i> 管理员管理
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

        </div>
        
        <!-- 底部固定区域 -->
        <div class="layui-footer">
            © 2025 教育矩阵管理系统
        </div>
    </div>
    
    <script src="/static/admin/js/jquery-3.7.1.min.js"></script>
    <script src="/static/layui/layui.js"></script>
    <script>
    layui.use(['element', 'layer'], function(){
        var element = layui.element;
        var layer = layui.layer;
            
            // 全局Ajax设置 - 处理登录超时
            $.ajaxSetup({
                complete: function(xhr, status) {
                    // 检查响应状态码
                    if (xhr.status === 401) {
                        // 未授权，跳转到登录页面
                        layer.msg('登录已过期，请重新登录', {icon: 2, time: 2000}, function(){
                            window.location.href = '/admin/login';
                        });
                        return;
                    }
                    
                    // 检查响应内容中的错误信息
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.code === 401 || response.message === '请登录' || response.msg === '请登录') {
                            layer.msg('登录已过期，请重新登录', {icon: 2, time: 2000}, function(){
                                window.location.href = '/admin/login';
                            });
                            return;
                        }
                    } catch (e) {
                        // 解析JSON失败，忽略
                    }
                }
            });
            
            // 菜单状态保持功能
            $(document).ready(function(){
                // 获取当前页面URL路径
                var currentPath = window.location.pathname;
                
                // 定义菜单映射关系
                var menuMap = {
                    '/admin/system_setting': '系统管理',
                    '/admin/system_config': '系统管理',
                    '/admin/admin': '系统管理',
                    '/admin/role': '系统管理',
                    '/admin/permission': '系统管理',
                    '/admin/school': '教育管理',
                    '/admin/college': '教育管理',
                    '/admin/teacher': '教育管理',
                    '/admin/course': '教育管理',
                    '/admin/school_admin': '教育管理',
                    '/admin/user': '会员管理',
                    '/admin/user_log': '会员管理',
                    '/admin/article': '文章管理',
                    '/admin/file': '内容管理',
                    '/admin/content_library': '内容管理',
                    '/admin/tag': '内容管理',
                    '/admin/ai_tool': 'AI工具',
                    '/admin/stats': '数据统计',
                    '/admin/tools': '系统工具'
                };
                
                // 特殊处理课程标签管理
                if (currentPath === '/admin/course/tag') {
                    parentMenu = '内容管理';
                } else {
                    // 查找当前页面对应的父菜单
                    var parentMenu = null;
                    for (var path in menuMap) {
                        if (currentPath.startsWith(path)) {
                            parentMenu = menuMap[path];
                            break;
                        }
                    }
                }
                
                // 如果找到对应的父菜单，则展开该菜单
                if (parentMenu) {
                    $('.layui-nav-item').each(function(){
                        var $item = $(this);
                        var $link = $item.find('> a');
                        var linkText = $link.text().trim();
                        
                        if (linkText === parentMenu) {
                            // 添加展开状态
                            $item.addClass('layui-nav-itemed');
                            $item.find('.layui-nav-child').show();
                            
                            // 高亮当前子菜单项
                            $item.find('.layui-nav-child dd a').each(function(){
                                var href = $(this).attr('href');
                                if (href && currentPath === href) {
                                    $(this).parent().addClass('layui-this');
                                    return false; // 跳出循环
                                }
                            });
                            
                            return false; // 跳出循环
                        }
                    });
                }
                
                // 退出登录
                $('#logoutBtn').click(function(){
                    layer.confirm('确定要退出登录吗？', function(index){
                        $.post('/admin/logout', function(res){
                            if(res.code === 0){
                                layer.msg('退出成功', {icon: 1}, function(){
                                    window.location.href = '/admin/login';
                                });
                            } else {
                                layer.msg(res.msg || '退出失败', {icon: 2});
                            }
                        });
                        layer.close(index);
                    });
                });
            });
        });
    </script>
    
<script>
layui.use(['element', 'layer'], function(){
    var element = layui.element;
    var layer = layui.layer;
});
</script>

</body>
</html> 