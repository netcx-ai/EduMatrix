<?php /*a:2:{s:49:"F:\EduMatrix\backend\view\admin\tools\backup.html";i:1753341575;s:42:"F:\EduMatrix\backend\view\common\base.html";i:1753341575;}*/ ?>
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
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">
                    <span class="layui-badge layui-bg-blue">数据备份</span>
                </div>
                <div class="layui-card-body">
                    <!-- 备份操作 -->
                    <div class="layui-row">
                        <div class="layui-col-md6">
                            <div class="layui-card">
                                <div class="layui-card-header">备份操作</div>
                                <div class="layui-card-body">
                                    <button class="layui-btn layui-btn-normal" id="createBackup">
                                        <i class="layui-icon layui-icon-download-circle"></i> 创建备份
                                    </button>
                                    <br><br>
                                    <p class="layui-text">
                                        <i class="layui-icon layui-icon-tips"></i>
                                        备份将包含所有数据库表结构和数据
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="layui-col-md6">
                            <div class="layui-card">
                                <div class="layui-card-header">备份信息</div>
                                <div class="layui-card-body">
                                    <p><strong>数据库类型：</strong>MySQL</p>
                                    <p><strong>备份位置：</strong>runtime/backup/</p>
                                    <p><strong>备份格式：</strong>SQL文件</p>
                                    <p><strong>注意事项：</strong>备份前请确保有足够磁盘空间</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 备份文件列表 -->
                    <div class="layui-row" style="margin-top: 20px;">
                        <div class="layui-col-md12">
                            <div class="layui-card">
                                <div class="layui-card-header">备份文件列表</div>
                                <div class="layui-card-body">
                                    <table id="backupTable" lay-filter="backupTable"></table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 表格操作列模板 -->
<script type="text/html" id="tableBar">
    <a class="layui-btn layui-btn-xs layui-btn-normal" lay-event="download">下载</a>
    <a class="layui-btn layui-btn-xs layui-btn-warm" lay-event="restore">恢复</a>
    <a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="delete">删除</a>
</script>

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
layui.use(['table', 'layer'], function(){
    var table = layui.table;
    var layer = layui.layer;
    
    // 初始化表格
    var tableIns = table.render({
        elem: '#backupTable',
        url: '/admin/tools/backup',
        method: 'get',
        page: true,
        limit: 10,
        limits: [10, 20, 50],
        cols: [[
            {field: 'filename', title: '文件名'},
            {field: 'size', title: '文件大小', width: 150},
            {field: 'create_time', title: '创建时间'},
            {field: 'status', title: '状态', width: 100},
            {title: '操作', toolbar: '#tableBar', width: 200}
        ]],
        parseData: function(res){
            return {
                "code": res.code,
                "msg": res.msg,
                "count": res.count,
                "data": res.data
            };
        }
    });
    
    // 创建备份
    $('#createBackup').click(function(){
        layer.confirm('确定要创建数据备份吗？', function(index){
            var loadIndex = layer.load(1);
            
            $.post('/admin/tools/backup', {action: 'backup'}, function(res){
                layer.close(loadIndex);
                if(res.code == 0){
                    layer.msg(res.msg, {icon: 1});
                    tableIns.reload();
                } else {
                    layer.msg(res.msg, {icon: 2});
                }
            });
            layer.close(index);
        });
    });
    
    // 监听工具条
    table.on('tool(backupTable)', function(obj){
        var data = obj.data;
        if(obj.event === 'download'){
            // 下载备份文件
            window.open('/admin/tools/backup?action=download&file=' + data.filename);
        } else if(obj.event === 'restore'){
            // 恢复备份
            layer.confirm('确定要恢复此备份吗？此操作将覆盖当前数据！', function(index){
                var loadIndex = layer.load(1);
                
                $.post('/admin/tools/backup', {
                    action: 'restore',
                    file: data.filename
                }, function(res){
                    layer.close(loadIndex);
                    if(res.code == 0){
                        layer.msg(res.msg, {icon: 1});
                    } else {
                        layer.msg(res.msg, {icon: 2});
                    }
                });
                layer.close(index);
            });
        } else if(obj.event === 'delete'){
            // 删除备份
            layer.confirm('确定要删除此备份文件吗？', function(index){
                var loadIndex = layer.load(1);
                
                $.post('/admin/tools/backup', {
                    action: 'delete',
                    file: data.filename
                }, function(res){
                    layer.close(loadIndex);
                    if(res.code == 0){
                        layer.msg(res.msg, {icon: 1});
                        tableIns.reload();
                    } else {
                        layer.msg(res.msg, {icon: 2});
                    }
                });
                layer.close(index);
            });
        }
    });
});
</script>

</body>
</html> 