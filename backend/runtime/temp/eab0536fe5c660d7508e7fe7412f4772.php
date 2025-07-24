<?php /*a:2:{s:46:"F:\EduMatrix\backend\view\admin\tools\log.html";i:1753341575;s:42:"F:\EduMatrix\backend\view\common\base.html";i:1753341575;}*/ ?>
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
    <!-- 统计卡片 -->
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md2">
            <div class="layui-card">
                <div class="layui-card-header">
                    <i class="layui-icon layui-icon-file" style="color: #1E9FFF;"></i>
                    总日志数
                </div>
                <div class="layui-card-body">
                    <h2 id="totalLogs">0</h2>
                </div>
            </div>
        </div>
        <div class="layui-col-md2">
            <div class="layui-card">
                <div class="layui-card-header">
                    <i class="layui-icon layui-icon-close" style="color: #FF5722;"></i>
                    错误日志
                </div>
                <div class="layui-card-body">
                    <h2 id="errorCount">0</h2>
                </div>
            </div>
        </div>
        <div class="layui-col-md2">
            <div class="layui-card">
                <div class="layui-card-header">
                    <i class="layui-icon layui-icon-help" style="color: #FFB800;"></i>
                    警告日志
                </div>
                <div class="layui-card-body">
                    <h2 id="warningCount">0</h2>
                </div>
            </div>
        </div>
        <div class="layui-col-md2">
            <div class="layui-card">
                <div class="layui-card-header">
                    <i class="layui-icon layui-icon-ok" style="color: #5FB878;"></i>
                    信息日志
                </div>
                <div class="layui-card-body">
                    <h2 id="infoCount">0</h2>
                </div>
            </div>
        </div>
        <div class="layui-col-md2">
            <div class="layui-card">
                <div class="layui-card-header">
                    <i class="layui-icon layui-icon-database" style="color: #409EFF;"></i>
                    SQL日志
                </div>
                <div class="layui-card-body">
                    <h2 id="sqlCount">0</h2>
                </div>
            </div>
        </div>
        <div class="layui-col-md2">
            <div class="layui-card">
                <div class="layui-card-header">
                    <i class="layui-icon layui-icon-debug" style="color: #9F7AEA;"></i>
                    调试日志
                </div>
                <div class="layui-card-body">
                    <h2 id="debugCount">0</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">
                    <span class="layui-badge layui-bg-blue">日志查看</span>
                    <div class="layui-btn-group" style="float: right;">
                        <button class="layui-btn layui-btn-sm" id="exportBtn">
                            <i class="layui-icon layui-icon-export"></i> 导出
                        </button>
                        <button class="layui-btn layui-btn-sm layui-btn-warm" id="clearBtn">
                            <i class="layui-icon layui-icon-delete"></i> 清理
                        </button>
                        <button class="layui-btn layui-btn-sm layui-btn-normal" id="refreshBtn">
                            <i class="layui-icon layui-icon-refresh"></i> 刷新
                        </button>
                    </div>
                </div>
                <div class="layui-card-body">
                    <!-- 筛选条件 -->
                    <form class="layui-form" lay-filter="searchForm">
                        <div class="layui-form-item">
                            <div class="layui-inline">
                                <label class="layui-form-label">日志级别</label>
                                <div class="layui-input-inline">
                                    <select name="level">
                                        <option value="">全部级别</option>
                                        <option value="ERROR">错误</option>
                                        <option value="WARNING">警告</option>
                                        <option value="INFO">信息</option>
                                        <option value="DEBUG">调试</option>
                                        <option value="SQL">SQL</option>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">日期</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="date" id="logDate" placeholder="请选择日期" autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">关键词</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="keyword" placeholder="搜索日志内容" autocomplete="off" class="layui-input">
                                </div>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <div class="layui-inline">
                                <label class="layui-form-label">时间范围</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="start_time" id="startTime" placeholder="开始时间" autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <label class="layui-form-label">至</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="end_time" id="endTime" placeholder="结束时间" autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <button class="layui-btn" lay-submit lay-filter="search">
                                    <i class="layui-icon layui-icon-search"></i> 查询
                                </button>
                                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                            </div>
                        </div>
                    </form>
                    
                    <!-- 日志表格 -->
                    <table id="logTable" lay-filter="logTable"></table>
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
layui.use(['table', 'form', 'layer', 'laydate'], function(){
    var table = layui.table;
    var form = layui.form;
    var layer = layui.layer;
    var laydate = layui.laydate;
    
    // 日期选择器
    laydate.render({
        elem: '#logDate',
        type: 'date',
        value: new Date().toISOString().split('T')[0]
    });
    
    // 时间选择器
    laydate.render({
        elem: '#startTime',
        type: 'datetime',
        format: 'yyyy-MM-dd HH:mm:ss'
    });
    
    laydate.render({
        elem: '#endTime',
        type: 'datetime',
        format: 'yyyy-MM-dd HH:mm:ss'
    });
    
    // 初始化表格
    var tableIns = table.render({
        elem: '#logTable',
        url: '/admin/tools/log',
        method: 'get',
        where: {
            date: new Date().toISOString().split('T')[0]
        },
        page: true,
        limit: 20,
        limits: [10, 20, 50, 100],
        cols: [[
            {field: 'time', title: '时间', width: 180},
            {field: 'level', title: '级别', width: 100, templet: function(d){
                var colors = {
                    'ERROR': 'layui-bg-red',
                    'WARNING': 'layui-bg-orange',
                    'INFO': 'layui-bg-green',
                    'DEBUG': 'layui-bg-gray',
                    'SQL': 'layui-bg-blue'
                };
                return '<span class="layui-badge ' + (colors[d.level] || 'layui-bg-gray') + '">' + d.level + '</span>';
            }},
            {field: 'message', title: '日志内容'},
            {field: 'file', title: '文件', width: 120}
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
    
    // 监听搜索
    form.on('submit(search)', function(data){
        tableIns.reload({
            where: data.field,
            page: {
                curr: 1
            }
        });
        return false;
    });
    
    // 导出日志
    $('#exportBtn').click(function(){
        var searchData = form.val('searchForm');
        var params = new URLSearchParams(searchData);
        window.open('/admin/tools/exportLog?' + params.toString());
    });
    
    // 清理日志
    $('#clearBtn').click(function(){
        // 显示清理选项
        layer.open({
            type: 1,
            title: '清理日志',
            area: ['400px', '300px'],
            content: `
                <div style="padding: 20px;">
                    <p style="margin-bottom: 15px; color: #666;">请选择要清理的日志时间范围：</p>
                    <div style="margin-bottom: 15px;">
                        <input type="radio" name="clearDays" value="7" id="clear7" checked>
                        <label for="clear7">清理7天前的日志</label>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <input type="radio" name="clearDays" value="15" id="clear15">
                        <label for="clear15">清理15天前的日志</label>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <input type="radio" name="clearDays" value="30" id="clear30">
                        <label for="clear30">清理30天前的日志</label>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <input type="radio" name="clearDays" value="all" id="clearAll">
                        <label for="clearAll" style="color: #FF5722;">清理所有日志（危险操作）</label>
                    </div>
                    <div style="margin-top: 20px; padding: 10px; background: #f8f8f8; border-radius: 4px;">
                        <small style="color: #999;">
                            <i class="layui-icon layui-icon-help"></i>
                            注意：清理操作不可恢复，请谨慎选择
                        </small>
                    </div>
                </div>
            `,
            btn: ['确定清理', '取消'],
            yes: function(index, layero){
                var selectedDays = layero.find('input[name="clearDays"]:checked').val();
                var days = selectedDays === 'all' ? 0 : parseInt(selectedDays);
                var confirmMsg = selectedDays === 'all' ? 
                    '确定要清理所有日志吗？此操作不可恢复！' : 
                    `确定要清理${days}天前的日志吗？`;
                
                layer.confirm(confirmMsg, {
                    title: '最终确认',
                    icon: 3,
                    btn: ['确定', '取消']
                }, function(confirmIndex){
                    // 显示加载提示
                    var loadIndex = layer.load(1, {shade: [0.3, '#000']});
                    
                    $.post('/admin/tools/clearLog', {days: days}, function(res){
                        layer.close(loadIndex);
                        if(res.code === 0){
                            layer.msg(res.msg, {icon: 1, time: 2000});
                            // 刷新表格和统计
                            tableIns.reload();
                            loadStats();
                        } else {
                            layer.msg(res.msg, {icon: 2, time: 3000});
                        }
                    }).fail(function(xhr, status, error) {
                        layer.close(loadIndex);
                        layer.msg('网络请求失败：' + error, {icon: 2, time: 3000});
                    });
                    layer.close(confirmIndex);
                });
                layer.close(index);
            }
        });
    });
    
    // 刷新
    $('#refreshBtn').click(function(){
        tableIns.reload();
        loadStats();
    });
    
    // 加载统计
    function loadStats() {
        $.get('/admin/tools/logStats', function(res){
            if(res.code === 0){
                $('#totalLogs').text(res.data.total_logs);
                $('#errorCount').text(res.data.error_count);
                $('#warningCount').text(res.data.warning_count);
                $('#infoCount').text(res.data.info_count);
                $('#sqlCount').text(res.data.sql_count);
                $('#debugCount').text(res.data.debug_count);
            }
        });
    }
    
    // 页面加载时获取统计
    loadStats();
});
</script>

</body>
</html> 