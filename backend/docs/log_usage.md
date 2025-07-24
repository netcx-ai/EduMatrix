# 日志系统使用说明

## 概述

增强后的日志系统提供了更实用、更强大的日志管理功能，包括日志查看、搜索、导出、统计和清理等功能。

## 主要功能

### 1. 日志查看
- **实时查看**：实时显示系统日志
- **分页显示**：支持分页浏览大量日志
- **级别过滤**：按日志级别（ERROR、WARNING、INFO、DEBUG、SQL）过滤
- **时间范围**：按时间范围筛选日志
- **关键词搜索**：在日志内容中搜索关键词

### 2. 日志统计
- **总日志数**：显示当日总日志数量
- **错误日志**：显示错误日志数量
- **警告日志**：显示警告日志数量
- **信息日志**：显示信息日志数量
- **SQL日志**：显示SQL日志数量
- **调试日志**：显示调试日志数量

### 3. 日志导出
- **CSV格式**：导出为CSV文件，支持Excel打开
- **条件导出**：根据搜索条件导出特定日志
- **中文支持**：正确处理中文字符

### 4. 日志清理
- **自动清理**：清理指定天数前的日志
- **安全确认**：清理前需要确认
- **批量清理**：一次性清理多个日志文件

## 使用方法

### 1. 访问日志页面
```
/admin/tools/log
```

### 2. 搜索日志
1. 选择日志类型（全部、错误、警告、信息、调试、SQL）
2. 选择日志级别
3. 选择日期
4. 输入关键词
5. 设置时间范围
6. 点击"查询"按钮

### 3. 导出日志
1. 设置搜索条件
2. 点击"导出"按钮
3. 选择保存位置

### 4. 清理日志
1. 点击"清理"按钮
2. 确认清理操作
3. 系统自动清理30天前的日志

## 日志服务使用

### 1. 记录用户操作
```php
use app\service\LogService;

// 记录用户登录
LogService::recordUserAction(
    $userId, 
    'login', 
    'auth', 
    '用户登录成功', 
    'success'
);

// 记录用户操作
LogService::recordUserAction(
    $userId, 
    'create', 
    'article', 
    '创建文章：' . $title, 
    'success'
);
```

### 2. 记录系统错误
```php
try {
    // 业务代码
} catch (\Exception $e) {
    LogService::recordError(
        '数据库连接失败', 
        ['database' => 'mysql'], 
        $e
    );
}
```

### 3. 记录性能日志
```php
$startTime = microtime(true);

// 执行耗时操作
$result = $this->heavyOperation();

LogService::recordPerformance(
    'heavy_operation', 
    $startTime, 
    microtime(true), 
    ['result' => $result]
);
```

### 4. 记录安全日志
```php
// 记录登录失败
LogService::recordSecurity(
    'login_failed', 
    ['username' => $username, 'reason' => '密码错误'], 
    'warning'
);

// 记录可疑操作
LogService::recordSecurity(
    'suspicious_activity', 
    ['ip' => $ip, 'action' => '频繁访问'], 
    'error'
);
```

### 5. 记录业务日志
```php
LogService::recordBusiness(
    'order', 
    'create', 
    ['order_id' => $orderId, 'amount' => $amount], 
    'success'
);
```

### 6. 记录API访问
```php
LogService::recordApiAccess(
    'POST', 
    '/api/user/login', 
    ['username' => 'admin'], 
    ['code' => 0, 'msg' => 'success'], 
    0.15
);
```

### 7. 记录数据库操作
```php
LogService::recordDatabase(
    'SELECT', 
    'users', 
    'SELECT * FROM users WHERE id = 1', 
    0.05, 
    1
);
```

## 日志配置

### 1. 日志级别
- **ERROR**：系统错误，需要立即关注
- **WARNING**：警告信息，需要关注
- **INFO**：一般信息，用于追踪
- **DEBUG**：调试信息，开发时使用
- **SQL**：数据库查询日志

### 2. 日志通道
- **file**：默认文件日志
- **error**：错误日志专用通道
- **warning**：警告日志专用通道
- **info**：信息日志专用通道
- **debug**：调试日志专用通道
- **sql**：SQL日志专用通道
- **database**：数据库操作日志
- **security**：安全日志
- **business**：业务日志

### 3. 日志保留策略
- 默认保留30天
- 最大文件数量：30个
- 自动清理过期日志

## 最佳实践

### 1. 日志记录原则
- **有意义**：记录有价值的信息
- **结构化**：使用结构化的数据格式
- **可搜索**：包含便于搜索的关键词
- **可追踪**：包含足够的上下文信息

### 2. 性能考虑
- 避免记录敏感信息
- 控制日志文件大小
- 定期清理过期日志
- 使用异步日志记录（如需要）

### 3. 安全考虑
- 不记录密码等敏感信息
- 记录安全相关事件
- 监控异常访问模式
- 定期审查安全日志

## 故障排查

### 1. 日志文件不存在
- 检查日志目录权限
- 确认日志配置正确
- 检查磁盘空间

### 2. 日志内容为空
- 检查日志级别配置
- 确认日志记录代码正确
- 检查文件权限

### 3. 性能问题
- 减少日志记录频率
- 使用日志级别过滤
- 定期清理日志文件

## 扩展功能

### 1. 日志告警
可以基于日志内容设置告警规则，如：
- 错误日志数量超过阈值
- 特定错误模式出现
- 性能指标异常

### 2. 日志分析
可以基于日志数据进行：
- 用户行为分析
- 系统性能分析
- 错误趋势分析

### 3. 日志可视化
可以开发图表展示：
- 日志数量趋势
- 错误率统计
- 性能指标监控 