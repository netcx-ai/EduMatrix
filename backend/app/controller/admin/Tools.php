<?php
declare (strict_types = 1);

namespace app\controller\admin;

use app\BaseController;
use think\facade\View;
use think\facade\Cache;
use think\facade\Log;
use think\facade\Db;
use think\Request;

class Tools extends BaseController
{
    /**
     * 缓存管理
     */
    public function cache(Request $request)
    {
        if ($request->isPost()) {
            $type = $request->param('type', 'all');
            
            try {
                switch ($type) {
                    case 'all':
                        Cache::clear();
                        $message = '所有缓存已清除';
                        break;
                    case 'data':
                        // 清除数据缓存
                        Cache::clear();
                        $message = '数据缓存已清除';
                        break;
                    case 'template':
                        // 清除模板缓存
                        $this->clearTemplateCache();
                        $message = '模板缓存已清除';
                        break;
                    case 'route':
                        // 清除路由缓存
                        $this->clearRouteCache();
                        $message = '路由缓存已清除';
                        break;
                    default:
                        return json(['code' => 1, 'msg' => '未知的缓存类型']);
                }
                
                return json(['code' => 0, 'msg' => $message]);
                
            } catch (\Exception $e) {
                return json(['code' => 1, 'msg' => '清除缓存失败：' . $e->getMessage()]);
            }
        }
        
        // 获取缓存信息
        $cacheInfo = $this->getCacheInfo();
        
        return View::fetch('admin/tools/cache', [
            'cacheInfo' => $cacheInfo
        ]);
    }
    
    /**
     * 日志查看
     */
    public function log(Request $request)
    {
        try {
            // 如果是AJAX请求，返回JSON
            if ($request->isAjax()) {
                $page = (int)$request->param('page', 1);
                $limit = (int)$request->param('limit', 20);
                $date = $request->param('date', date('Y-m-d'));
                $keyword = $request->param('keyword', '');
                $level = $request->param('level', '');
                $startTime = $request->param('start_time', '');
                $endTime = $request->param('end_time', '');
                
                $logs = $this->getLogs($date, $page, $limit, $keyword, $level, $startTime, $endTime);
                $total = $this->getLogCount($date, $keyword, $level, $startTime, $endTime);
                
                return json([
                    'code' => 0,
                    'msg' => '',
                    'count' => $total,
                    'data' => $logs
                ]);
            }
            
            // 非AJAX请求，返回页面
            return View::fetch('admin/tools/log');
            
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
     * 导出日志
     */
    public function exportLog(Request $request)
    {
        try {
            $date = $request->param('date', date('Y-m-d'));
            $keyword = $request->param('keyword', '');
            $level = $request->param('level', '');
            $startTime = $request->param('start_time', '');
            $endTime = $request->param('end_time', '');
            
            $logs = $this->getLogs($date, 1, 10000, $keyword, $level, $startTime, $endTime);
            
            // 设置响应头
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="logs_' . $date . '.csv"');
            
            // 输出CSV内容
            $output = fopen('php://output', 'w');
            
            // 写入BOM，解决中文乱码
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // 写入表头
            fputcsv($output, ['时间', '级别', '内容', '文件']);
            
            // 写入数据
            foreach ($logs as $log) {
                fputcsv($output, [
                    $log['time'],
                    $log['level'],
                    $log['message'],
                    $log['file']
                ]);
            }
            
            fclose($output);
            exit;
            
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '导出失败：' . $e->getMessage()]);
        }
    }
    
    /**
     * 清理日志
     */
    public function clearLog(Request $request)
    {
        try {
            $days = (int)$request->param('days', 30);
            
            // 检查日志目录是否存在
            $logPath = runtime_path() . 'log/';
            if (!is_dir($logPath)) {
                return json(['code' => 1, 'msg' => '日志目录不存在']);
            }
            
            // 检查目录权限
            if (!is_writable($logPath)) {
                return json(['code' => 1, 'msg' => '日志目录无写入权限']);
            }
            
            $result = $this->clearOldLogs($days);
            
            if ($result > 0) {
                $msg = $days == 0 ? 
                    "成功清理所有日志，共 {$result} 个文件/目录" : 
                    "成功清理 {$days} 天前的日志，共 {$result} 个文件/目录";
                return json([
                    'code' => 0,
                    'msg' => $msg,
                    'data' => $result
                ]);
            } else {
                $msg = $days == 0 ? 
                    "没有找到需要清理的日志文件" : 
                    "没有找到 {$days} 天前的日志文件需要清理";
                return json([
                    'code' => 0,
                    'msg' => $msg,
                    'data' => 0
                ]);
            }
            
        } catch (\Exception $e) {
            // 记录错误日志
            error_log("清理日志失败: " . $e->getMessage());
            return json(['code' => 1, 'msg' => '清理失败：' . $e->getMessage()]);
        }
    }
    
    /**
     * 日志统计
     */
    public function logStats(Request $request)
    {
        try {
            $date = $request->param('date', date('Y-m-d'));
            $days = (int)$request->param('days', 7);
            
            $stats = $this->getLogStats($date, $days);
            
            return json([
                'code' => 0,
                'msg' => '',
                'data' => $stats
            ]);
            
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '获取统计失败：' . $e->getMessage()]);
        }
    }
    
    /**
     * 系统监控
     */
    public function monitor(Request $request)
    {
        try {
            // 如果是AJAX请求，返回JSON
            if ($request->isAjax()) {
                $type = $request->param('type', 'system');
                
                $data = $this->getMonitorData($type);
                
                return json([
                    'code' => 0,
                    'msg' => '',
                    'data' => $data
                ]);
            }
            
            // 非AJAX请求，返回页面
            return View::fetch('admin/tools/monitor');
            
        } catch (\Exception $e) {
            if ($request->isAjax()) {
                return json([
                    'code' => 1,
                    'msg' => '请求异常：' . $e->getMessage(),
                    'data' => []
                ]);
            } else {
                return $this->error('页面加载失败：' . $e->getMessage());
            }
        }
    }
    
    /**
     * 数据备份
     */
    public function backup(Request $request)
    {
        if ($request->isPost()) {
            $action = $request->param('action', '');
            
            try {
                switch ($action) {
                    case 'backup':
                        $result = $this->backupDatabase();
                        return json(['code' => 0, 'msg' => '备份成功', 'data' => $result]);
                    case 'restore':
                        $file = $request->param('file', '');
                        $result = $this->restoreDatabase($file);
                        return json(['code' => 0, 'msg' => '恢复成功']);
                    case 'delete':
                        $file = $request->param('file', '');
                        $result = $this->deleteBackup($file);
                        return json(['code' => 0, 'msg' => '删除成功']);
                    default:
                        return json(['code' => 1, 'msg' => '未知操作']);
                }
                
            } catch (\Exception $e) {
                return json(['code' => 1, 'msg' => '操作失败：' . $e->getMessage()]);
            }
        }
        
        // 如果是AJAX请求，返回备份文件列表
        if ($request->isAjax()) {
            try {
                $backupFiles = $this->getBackupFiles();
                return json([
                    'code' => 0,
                    'msg' => '',
                    'count' => count($backupFiles),
                    'data' => $backupFiles
                ]);
            } catch (\Exception $e) {
                return json([
                    'code' => 1,
                    'msg' => '获取备份文件列表失败：' . $e->getMessage(),
                    'count' => 0,
                    'data' => []
                ]);
            }
        }
        
        // 获取备份文件列表
        $backupFiles = $this->getBackupFiles();
        
        return View::fetch('admin/tools/backup', [
            'backupFiles' => $backupFiles
        ]);
    }
    
    /**
     * 获取缓存信息
     */
    private function getCacheInfo()
    {
        $info = [
            'cache_driver' => config('cache.default'),
            'cache_prefix' => config('cache.stores.' . config('cache.default') . '.prefix'),
            'cache_expire' => config('cache.stores.' . config('cache.default') . '.expire'),
        ];
        
        // 获取缓存大小（如果支持）
        if (function_exists('memory_get_usage')) {
            $info['memory_usage'] = $this->formatBytes(memory_get_usage());
            $info['memory_peak'] = $this->formatBytes(memory_get_peak_usage());
        }
        
        return $info;
    }
    
    /**
     * 清除模板缓存
     */
    private function clearTemplateCache()
    {
        $cachePath = runtime_path() . 'cache/';
        if (is_dir($cachePath)) {
            $this->deleteDir($cachePath);
        }
    }
    
    /**
     * 清除路由缓存
     */
    private function clearRouteCache()
    {
        $routeCachePath = runtime_path() . 'route/';
        if (is_dir($routeCachePath)) {
            $this->deleteDir($routeCachePath);
        }
    }
    
    /**
     * 获取日志
     */
    private function getLogs($date, $page, $limit, $keyword = '', $level = '', $startTime = '', $endTime = '')
    {
        $logPath = runtime_path() . 'log/';
        $logs = [];
        
        // 解析日期，格式为 YYYY-MM-DD
        $dateObj = \DateTime::createFromFormat('Y-m-d', $date);
        if (!$dateObj) {
            return [];
        }
        
        // 转换为 ThinkPHP 日志文件路径格式 YYYYMM/DD.log
        $monthDir = $dateObj->format('Ym');
        $day = $dateObj->format('d');
        $logFile = $logPath . $monthDir . '/' . $day . '.log';
        
        if (file_exists($logFile)) {
            $logs = $this->parseLogFile($logFile, $page, $limit, $keyword, $level, $startTime, $endTime);
        }
        
        // 按时间排序
        usort($logs, function($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });
        
        return array_slice($logs, ($page - 1) * $limit, $limit);
    }
    
    /**
     * 解析日志文件
     */
    private function parseLogFile($file, $page, $limit, $keyword = '', $level = '', $startTime = '', $endTime = '')
    {
        $logs = [];
        $lines = file($file);
        
        foreach ($lines as $line) {
            // ThinkPHP 日志格式: [2025-07-11T09:29:37+08:00][sql] 日志内容
            if (preg_match('/^\[(.*?)\]\[(.*?)\] (.*)$/', trim($line), $matches)) {
                $logLevel = strtolower($matches[2]);
                
                // 根据级别过滤
                if ($this->shouldIncludeLog($logLevel, $level)) {
                    $log = [
                        'time' => $matches[1],
                        'level' => strtoupper($matches[2]),
                        'message' => $matches[3],
                        'file' => basename($file)
                    ];
                    
                    // 应用搜索和时间过滤
                    if (!empty($keyword) && strpos($log['message'], $keyword) === false) {
                        continue;
                    }
                    if (!empty($startTime) && strtotime($log['time']) < strtotime($startTime)) {
                        continue;
                    }
                    if (!empty($endTime) && strtotime($log['time']) > strtotime($endTime)) {
                        continue;
                    }
                    
                    $logs[] = $log;
                }
            }
        }
        
        return $logs;
    }
    
    /**
     * 判断是否应该包含该日志
     */
    private function shouldIncludeLog($level, $filterLevel = '')
    {
        // 如果没有指定过滤级别，则包含所有支持的日志级别
        if (empty($filterLevel)) {
            $allowedLevels = ['error', 'warning', 'info', 'debug', 'sql'];
            return in_array($level, $allowedLevels);
        }
        
        // 如果指定了过滤级别，则只包含匹配的级别
        return strtolower($level) === strtolower($filterLevel);
    }
    
    /**
     * 获取日志总数
     */
    private function getLogCount($date, $keyword = '', $level = '', $startTime = '', $endTime = '')
    {
        $logPath = runtime_path() . 'log/';
        
        // 解析日期，格式为 YYYY-MM-DD
        $dateObj = \DateTime::createFromFormat('Y-m-d', $date);
        if (!$dateObj) {
            return 0;
        }
        
        // 转换为 ThinkPHP 日志文件路径格式 YYYYMM/DD.log
        $monthDir = $dateObj->format('Ym');
        $day = $dateObj->format('d');
        $logFile = $logPath . $monthDir . '/' . $day . '.log';
        
        if (!file_exists($logFile)) {
            return 0;
        }
        
        $count = 0;
        $lines = file($logFile);
        
        foreach ($lines as $line) {
            if (preg_match('/^\[(.*?)\]\[(.*?)\] (.*)$/', trim($line), $matches)) {
                $logLevel = strtolower($matches[2]);
                if ($this->shouldIncludeLog($logLevel, $level)) {
                    $log = [
                        'time' => $matches[1],
                        'level' => strtoupper($matches[2]),
                        'message' => $matches[3],
                        'file' => basename($logFile)
                    ];
                    
                    // 应用搜索和时间过滤
                    if (!empty($keyword) && strpos($log['message'], $keyword) === false) {
                        continue;
                    }
                    if (!empty($startTime) && strtotime($log['time']) < strtotime($startTime)) {
                        continue;
                    }
                    if (!empty($endTime) && strtotime($log['time']) > strtotime($endTime)) {
                        continue;
                    }
                    
                    $count++;
                }
            }
        }
        
        return $count;
    }
    
    /**
     * 备份数据库
     */
    private function backupDatabase()
    {
        $backupPath = runtime_path() . 'backup/';
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }
        
        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filepath = $backupPath . $filename;
        
        try {
            // 获取所有表名
            $tables = Db::query('SHOW TABLES');
            $sqlContent = '';
            
            // 添加文件头
            $sqlContent .= "-- EduMatrix 数据库备份\n";
            $sqlContent .= "-- 备份时间: " . date('Y-m-d H:i:s') . "\n";
            $sqlContent .= "-- 数据库: " . config('database.connections.mysql.database') . "\n\n";
            
            foreach ($tables as $table) {
                $tableName = array_values($table)[0];
                
                // 获取表结构
                $createTable = Db::query("SHOW CREATE TABLE `{$tableName}`");
                $sqlContent .= "-- 表结构: {$tableName}\n";
                $sqlContent .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                $sqlContent .= $createTable[0]['Create Table'] . ";\n\n";
                
                // 获取表数据
                $data = Db::table($tableName)->select();
                if (!empty($data)) {
                    $sqlContent .= "-- 表数据: {$tableName}\n";
                    foreach ($data as $row) {
                        $values = [];
                        foreach ($row as $value) {
                            if ($value === null) {
                                $values[] = 'NULL';
                            } else {
                                // 确保值为字符串类型
                                $value = (string)$value;
                                $values[] = "'" . addslashes($value) . "'";
                            }
                        }
                        $sqlContent .= "INSERT INTO `{$tableName}` VALUES (" . implode(', ', $values) . ");\n";
                    }
                    $sqlContent .= "\n";
                }
            }
            
            // 写入文件
            if (file_put_contents($filepath, $sqlContent) === false) {
                throw new \Exception('无法写入备份文件');
            }
            
            return [
                'filename' => $filename,
                'size' => $this->formatBytes(filesize($filepath)),
                'create_time' => date('Y-m-d H:i:s')
            ];
            
        } catch (\Exception $e) {
            throw new \Exception('数据库备份失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 恢复数据库
     */
    private function restoreDatabase($file)
    {
        $backupPath = runtime_path() . 'backup/';
        $filepath = $backupPath . $file;
        
        if (!file_exists($filepath)) {
            throw new \Exception('备份文件不存在');
        }
        
        try {
            // 读取SQL文件内容
            $sqlContent = file_get_contents($filepath);
            if ($sqlContent === false) {
                throw new \Exception('无法读取备份文件');
            }
            
            // 分割SQL语句
            $sqlStatements = array_filter(
                array_map('trim', explode(';', $sqlContent)),
                function($sql) {
                    return !empty($sql) && !preg_match('/^--/', $sql);
                }
            );
            
            // 开始事务
            Db::startTrans();
            
            try {
                foreach ($sqlStatements as $sql) {
                    if (!empty($sql)) {
                        Db::execute($sql);
                    }
                }
                
                // 提交事务
                Db::commit();
                
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                throw new \Exception('执行SQL语句失败: ' . $e->getMessage());
            }
            
        } catch (\Exception $e) {
            throw new \Exception('数据库恢复失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 删除备份
     */
    private function deleteBackup($file)
    {
        $backupPath = runtime_path() . 'backup/';
        $filepath = $backupPath . $file;
        
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }
    
    /**
     * 获取备份文件列表
     */
    private function getBackupFiles()
    {
        $backupPath = runtime_path() . 'backup/';
        $files = [];
        
        if (is_dir($backupPath)) {
            $fileList = glob($backupPath . '*.sql');
            foreach ($fileList as $file) {
                $files[] = [
                    'filename' => basename($file),
                    'size' => $this->formatBytes(filesize($file)),
                    'create_time' => date('Y-m-d H:i:s', filemtime($file)),
                    'status' => '正常'
                ];
            }
        }
        
        // 按时间倒序排列
        usort($files, function($a, $b) {
            return strtotime($b['create_time']) - strtotime($a['create_time']);
        });
        
        return $files;
    }
    
    /**
     * 格式化字节数
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * 删除目录
     */
    private function deleteDir($dir)
    {
        if (!is_dir($dir)) {
            return false;
        }
        
        try {
            $files = array_diff(scandir($dir), ['.', '..']);
            foreach ($files as $file) {
                $path = $dir . DIRECTORY_SEPARATOR . $file;
                if (is_dir($path)) {
                    $this->deleteDir($path);
                } else {
                    if (file_exists($path) && is_writable($path)) {
                        unlink($path);
                    }
                }
            }
            
            return rmdir($dir);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log("删除目录失败: {$dir}, 错误: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 获取监控数据
     */
    private function getMonitorData($type)
    {
        switch ($type) {
            case 'system':
                return $this->getSystemInfo();
            case 'database':
                return $this->getDatabaseInfo();
            case 'performance':
                return $this->getPerformanceInfo();
            case 'storage':
                return $this->getStorageInfo();
            default:
                return [];
        }
    }
    
    /**
     * 获取系统信息
     */
    private function getSystemInfo()
    {
        try {
            return [
                'php_version' => PHP_VERSION,
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'operating_system' => PHP_OS,
                'server_time' => date('Y-m-d H:i:s'),
                'timezone' => date_default_timezone_get(),
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
                'extensions' => $this->getLoadedExtensions(),
            ];
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * 获取数据库信息
     */
    private function getDatabaseInfo()
    {
        try {
            $db = Db::connect();
            $version = $db->query('SELECT VERSION() as version')[0]['version'] ?? 'Unknown';
            
            // 获取数据库大小
            $dbName = config('database.connections.mysql.database');
            $dbSize = $db->query("SELECT 
                ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb 
                FROM information_schema.tables 
                WHERE table_schema = '{$dbName}'")[0]['size_mb'] ?? 0;
            
            // 获取表信息
            $tables = $db->query("SELECT 
                table_name,
                table_rows,
                ROUND((data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.tables 
                WHERE table_schema = '{$dbName}' 
                ORDER BY size_mb DESC 
                LIMIT 10");
            
            return [
                'version' => $version,
                'database_name' => $dbName,
                'database_size' => $dbSize . ' MB',
                'tables' => $tables,
                'connection_status' => 'Connected',
            ];
        } catch (\Exception $e) {
            return [
                'connection_status' => 'Error: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * 获取性能信息
     */
    private function getPerformanceInfo()
    {
        try {
            return [
                'memory_usage' => [
                    'current' => $this->formatBytes(memory_get_usage()),
                    'peak' => $this->formatBytes(memory_get_peak_usage()),
                    'limit' => ini_get('memory_limit'),
                ],
                'cpu_usage' => $this->getCpuUsage(),
                'load_average' => $this->getLoadAverage(),
                'uptime' => $this->getSystemUptime(),
                'processes' => $this->getProcessCount(),
            ];
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * 获取存储信息
     */
    private function getStorageInfo()
    {
        try {
            $rootPath = root_path();
            $runtimePath = runtime_path();
            $publicPath = public_path();
            
            return [
                'disk_total' => $this->formatBytes(disk_total_space($rootPath)),
                'disk_free' => $this->formatBytes(disk_free_space($rootPath)),
                'disk_used' => $this->formatBytes(disk_total_space($rootPath) - disk_free_space($rootPath)),
                'directories' => [
                    'root' => [
                        'path' => $rootPath,
                        'size' => $this->getDirSize($rootPath),
                    ],
                    'runtime' => [
                        'path' => $runtimePath,
                        'size' => $this->getDirSize($runtimePath),
                    ],
                    'public' => [
                        'path' => $publicPath,
                        'size' => $this->getDirSize($publicPath),
                    ],
                ],
            ];
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * 获取已加载的PHP扩展
     */
    private function getLoadedExtensions()
    {
        $extensions = get_loaded_extensions();
        $important = ['mysql', 'pdo', 'pdo_mysql', 'curl', 'gd', 'mbstring', 'openssl', 'redis'];
        
        $result = [];
        foreach ($important as $ext) {
            $result[$ext] = in_array($ext, $extensions);
        }
        
        return $result;
    }
    
    /**
     * 获取CPU使用率
     */
    private function getCpuUsage()
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return round($load[0] * 100, 2) . '%';
        }
        
        if (PHP_OS === 'Linux') {
            $stat1 = file('/proc/stat');
            sleep(1);
            $stat2 = file('/proc/stat');
            
            $info1 = explode(' ', preg_replace('!cpu +!', '', $stat1[0]));
            $info2 = explode(' ', preg_replace('!cpu +!', '', $stat2[0]));
            
            $dif = [];
            $dif['user'] = $info2[0] - $info1[0];
            $dif['nice'] = $info2[1] - $info1[1];
            $dif['sys'] = $info2[2] - $info1[2];
            $dif['idle'] = $info2[3] - $info1[3];
            
            $total = array_sum($dif);
            $cpu = round((($total - $dif['idle']) / $total) * 100, 2);
            
            return $cpu . '%';
        }
        
        return 'N/A';
    }
    
    /**
     * 获取系统负载
     */
    private function getLoadAverage()
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return implode(', ', array_map(function($val) {
                return round($val, 2);
            }, $load));
        }
        
        return 'N/A';
    }
    
    /**
     * 获取系统运行时间
     */
    private function getSystemUptime()
    {
        if (PHP_OS === 'Linux' && file_exists('/proc/uptime')) {
            $uptime = file_get_contents('/proc/uptime');
            $uptime = floatval($uptime);
            
            $days = floor($uptime / 86400);
            $hours = floor(($uptime % 86400) / 3600);
            $minutes = floor(($uptime % 3600) / 60);
            
            return "{$days}天 {$hours}小时 {$minutes}分钟";
        }
        
        return 'N/A';
    }
    
    /**
     * 获取进程数量
     */
    private function getProcessCount()
    {
        if (PHP_OS === 'Linux') {
            $processes = shell_exec('ps aux | wc -l');
            return intval($processes) - 1; // 减去标题行
        }
        
        return 'N/A';
    }
    
    /**
     * 获取目录大小
     */
    private function getDirSize($directory)
    {
        $size = 0;
        if (is_dir($directory)) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory)) as $file) {
                $size += $file->getSize();
            }
        }
        return $this->formatBytes($size);
    }

    /**
     * 清理旧日志
     */
    private function clearOldLogs($days)
    {
        $logPath = runtime_path() . 'log/';
        $deletedFiles = 0;
        $deletedDirs = 0;
        $debugInfo = [];

        if (!is_dir($logPath)) {
            error_log("日志目录不存在: {$logPath}");
            return 0;
        }

        // 如果 days=0，清理所有日志
        if ($days == 0) {
            $debugInfo[] = "清理所有日志";
            
            // 清理所有按月份分组的日志目录
            foreach (new \DirectoryIterator($logPath) as $fileInfo) {
                if (!$fileInfo->isDot() && $fileInfo->isDir()) {
                    $dirName = $fileInfo->getFilename();
                    if (preg_match('/^\d{6}$/', $dirName)) {
                        $dirPath = $logPath . $dirName;
                        
                        // 统计目录中的文件数量
                        $fileCount = 0;
                        if (is_dir($dirPath)) {
                            $fileCount = count(glob($dirPath . '/*.log'));
                        }
                        
                        $debugInfo[] = "准备删除目录: {$dirPath}, 包含 {$fileCount} 个日志文件";
                        
                        // 删除目录
                        if ($this->deleteDir($dirPath)) {
                            $deletedDirs++;
                            $deletedFiles += $fileCount;
                            $debugInfo[] = "成功删除目录: {$dirPath}";
                        } else {
                            $debugInfo[] = "删除目录失败: {$dirPath}";
                        }
                    }
                }
            }

            // 清理根目录下的所有日志文件
            foreach (new \DirectoryIterator($logPath) as $fileInfo) {
                if (!$fileInfo->isDot() && $fileInfo->isFile()) {
                    $fileName = $fileInfo->getFilename();
                    if (preg_match('/^\d{8}\.log$/', $fileName)) {
                        $filePath = $logPath . $fileName;
                        $debugInfo[] = "准备删除文件: {$filePath}";
                        
                        if (unlink($filePath)) {
                            $deletedFiles++;
                            $debugInfo[] = "成功删除文件: {$filePath}";
                        } else {
                            $debugInfo[] = "删除文件失败: {$filePath}";
                        }
                    }
                }
            }
        } else {
            // 清理指定天数前的日志
            $date = new \DateTime();
            $date->modify("-$days days");
            $oldDate = $date->format('Ym');
            $oldDateFull = $date->format('Ymd');
            
            $debugInfo[] = "清理 {$days} 天前的日志，截止日期: {$oldDate}";

            // 清理按月份分组的日志目录
            foreach (new \DirectoryIterator($logPath) as $fileInfo) {
                if (!$fileInfo->isDot() && $fileInfo->isDir()) {
                    $dirName = $fileInfo->getFilename();
                    if (preg_match('/^\d{6}$/', $dirName)) {
                        $debugInfo[] = "检查目录: {$dirName}, 比较: {$dirName} < {$oldDate} = " . ($dirName < $oldDate ? 'true' : 'false');
                        
                        if ($dirName < $oldDate) {
                            $dirPath = $logPath . $dirName;
                            
                            // 统计目录中的文件数量
                            $fileCount = 0;
                            if (is_dir($dirPath)) {
                                $fileCount = count(glob($dirPath . '/*.log'));
                            }
                            
                            $debugInfo[] = "准备删除目录: {$dirPath}, 包含 {$fileCount} 个日志文件";
                            
                            // 删除目录
                            if ($this->deleteDir($dirPath)) {
                                $deletedDirs++;
                                $deletedFiles += $fileCount;
                                $debugInfo[] = "成功删除目录: {$dirPath}";
                            } else {
                                $debugInfo[] = "删除目录失败: {$dirPath}";
                            }
                        }
                    }
                }
            }

            // 清理根目录下的旧日志文件（格式：YYYYMMDD.log）
            foreach (new \DirectoryIterator($logPath) as $fileInfo) {
                if (!$fileInfo->isDot() && $fileInfo->isFile()) {
                    $fileName = $fileInfo->getFilename();
                    if (preg_match('/^\d{8}\.log$/', $fileName)) {
                        $debugInfo[] = "检查文件: {$fileName}, 比较: {$fileName} < {$oldDateFull}.log = " . ($fileName < $oldDateFull . '.log' ? 'true' : 'false');
                        
                        if ($fileName < $oldDateFull . '.log') {
                            $filePath = $logPath . $fileName;
                            $debugInfo[] = "准备删除文件: {$filePath}";
                            
                            if (unlink($filePath)) {
                                $deletedFiles++;
                                $debugInfo[] = "成功删除文件: {$filePath}";
                            } else {
                                $debugInfo[] = "删除文件失败: {$filePath}";
                            }
                        }
                    }
                }
            }
        }
        
        // 记录调试信息
        error_log("清理日志调试信息: " . implode(" | ", $debugInfo));
        
        return $deletedDirs + $deletedFiles; // 返回删除的目录和文件总数
    }

    /**
     * 获取日志统计
     */
    private function getLogStats($date, $days)
    {
        $logPath = runtime_path() . 'log/';
        $stats = [
            'total_logs' => 0,
            'error_count' => 0,
            'warning_count' => 0,
            'info_count' => 0,
            'debug_count' => 0,
            'sql_count' => 0,
            'other_count' => 0,
        ];

        $dateObj = \DateTime::createFromFormat('Y-m-d', $date);
        if (!$dateObj) {
            return $stats;
        }

        // 获取指定日期的日志文件
        $monthDir = $dateObj->format('Ym');
        $day = $dateObj->format('d');
        $logFile = $logPath . $monthDir . '/' . $day . '.log';

        if (file_exists($logFile)) {
            $lines = file($logFile);
            
            foreach ($lines as $line) {
                if (preg_match('/^\[(.*?)\]\[(.*?)\] (.*)$/', trim($line), $matches)) {
                    $level = strtoupper($matches[2]);
                    $stats['total_logs']++;
                    
                    switch ($level) {
                        case 'ERROR':
                            $stats['error_count']++;
                            break;
                        case 'WARNING':
                            $stats['warning_count']++;
                            break;
                        case 'INFO':
                            $stats['info_count']++;
                            break;
                        case 'DEBUG':
                            $stats['debug_count']++;
                            break;
                        case 'SQL':
                            $stats['sql_count']++;
                            break;
                        default:
                            $stats['other_count']++;
                            break;
                    }
                }
            }
        }

        return $stats;
    }
} 