<?php
declare (strict_types = 1);

namespace app\service;

use think\facade\Log;

class LogService
{
    /**
     * 记录用户操作日志
     */
    public static function recordUserAction($userId, $action, $module, $content, $result = 'success', $ip = '', $userAgent = '')
    {
        $logData = [
            'user_id' => $userId,
            'action' => $action,
            'module' => $module,
            'content' => $content,
            'result' => $result,
            'ip' => $ip ?: request()->ip(),
            'user_agent' => $userAgent ?: request()->header('User-Agent'),
            'timestamp' => time()
        ];
        
        Log::info('用户操作', $logData);
    }
    
    /**
     * 记录系统错误日志
     */
    public static function recordError($message, $context = [], $exception = null)
    {
        $logData = [
            'message' => $message,
            'context' => $context,
            'file' => $exception ? $exception->getFile() : '',
            'line' => $exception ? $exception->getLine() : '',
            'trace' => $exception ? $exception->getTraceAsString() : '',
            'timestamp' => time()
        ];
        
        Log::error('系统错误', $logData);
    }
    
    /**
     * 记录性能日志
     */
    public static function recordPerformance($operation, $startTime, $endTime = null, $context = [])
    {
        $endTime = $endTime ?: microtime(true);
        $duration = round(($endTime - $startTime) * 1000, 2); // 毫秒
        
        $logData = [
            'operation' => $operation,
            'duration_ms' => $duration,
            'context' => $context,
            'timestamp' => time()
        ];
        
        if ($duration > 1000) { // 超过1秒记录为警告
            Log::warning('性能警告', $logData);
        } else {
            Log::info('性能记录', $logData);
        }
    }
    
    /**
     * 记录安全日志
     */
    public static function recordSecurity($event, $details = [], $level = 'info')
    {
        $logData = [
            'event' => $event,
            'details' => $details,
            'ip' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'timestamp' => time()
        ];
        
        switch ($level) {
            case 'error':
                Log::error('安全事件', $logData);
                break;
            case 'warning':
                Log::warning('安全警告', $logData);
                break;
            default:
                Log::info('安全日志', $logData);
                break;
        }
    }
    
    /**
     * 记录业务日志
     */
    public static function recordBusiness($business, $action, $data = [], $result = 'success')
    {
        $logData = [
            'business' => $business,
            'action' => $action,
            'data' => $data,
            'result' => $result,
            'user_id' => session('admin_id') ?: 0,
            'timestamp' => time()
        ];
        
        Log::info('业务日志', $logData);
    }
    
    /**
     * 记录API访问日志
     */
    public static function recordApiAccess($method, $url, $params = [], $response = [], $duration = 0)
    {
        $logData = [
            'method' => $method,
            'url' => $url,
            'params' => $params,
            'response_code' => $response['code'] ?? 0,
            'duration_ms' => round($duration * 1000, 2),
            'ip' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'timestamp' => time()
        ];
        
        Log::info('API访问', $logData);
    }
    
    /**
     * 记录数据库操作日志
     */
    public static function recordDatabase($operation, $table, $sql, $duration = 0, $affected = 0)
    {
        $logData = [
            'operation' => $operation,
            'table' => $table,
            'sql' => $sql,
            'duration_ms' => round($duration * 1000, 2),
            'affected_rows' => $affected,
            'timestamp' => time()
        ];
        
        if ($duration > 0.5) { // 超过0.5秒记录为警告
            Log::warning('慢查询', $logData);
        } else {
            Log::info('数据库操作', $logData);
        }
    }
    
    /**
     * 清理过期日志
     */
    public static function cleanOldLogs($days = 30)
    {
        $logPath = runtime_path() . 'log/';
        $count = 0;
        
        if (!is_dir($logPath)) {
            return $count;
        }
        
        $date = new \DateTime();
        $date->modify("-$days days");
        $oldDate = $date->format('Ym');
        
        foreach (new \DirectoryIterator($logPath) as $fileInfo) {
            if (!$fileInfo->isDot() && $fileInfo->isDir()) {
                $dirName = $fileInfo->getFilename();
                if (preg_match('/^\d{6}$/', $dirName) && $dirName < $oldDate) {
                    self::deleteDir($logPath . $dirName);
                    $count++;
                }
            }
        }
        
        return $count;
    }
    
    /**
     * 删除目录
     */
    private static function deleteDir($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                self::deleteDir($path);
            } else {
                unlink($path);
            }
        }
        
        rmdir($dir);
    }
} 