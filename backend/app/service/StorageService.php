<?php
declare(strict_types=1);

namespace app\service;

use app\model\File;
use app\model\SystemConfig;
use think\facade\Filesystem;
use think\file\UploadedFile;

/**
 * 存储服务类
 */
class StorageService
{
    /**
     * 上传文件（自动根据系统配置选择存储驱动）
     * @param UploadedFile $file       上传文件对象
     * @param string       $path       保存路径（相对）
     * @param string|null  $storageType 指定存储类型，可选；为空时按系统配置
     * @return string                  保存后的文件相对路径
     * @throws \Exception
     */
    public static function uploadFile(UploadedFile $file, string $path = '', ?string $storageType = null): string
    {
        // 若未显式指定，则读取系统配置
        if (empty($storageType)) {
            $driver = \app\helper\SystemHelper::getStorageDriver();
            switch (strtolower($driver)) {
                case 'oss':
                    $storageType = File::STORAGE_OSS;
                    break;
                case 'cos':
                    $storageType = File::STORAGE_COS;
                    break;
                case 'local':
                default:
                    $storageType = File::STORAGE_LOCAL;
            }
        }

        // 根据存储类型选择不同的上传方式
        switch ($storageType) {
            case File::STORAGE_LOCAL:
                return self::uploadToLocal($file, $path);
                
            case File::STORAGE_OSS:
                return self::uploadToOSS($file, $path);
                
            case File::STORAGE_COS:
                return self::uploadToCOS($file, $path);
                
            default:
                return self::uploadToLocal($file, $path);
        }
    }
    
    /**
     * 上传到本地存储
     */
    private static function uploadToLocal(UploadedFile $file, string $path): string
    {
        $savename = Filesystem::disk('public')->putFile($path, $file);
        if (!$savename) {
            throw new \Exception('文件上传失败');
        }
        // 统一路径分隔符为正斜杠
        return str_replace('\\', '/', $savename);
    }
    
    /**
     * 上传到阿里云OSS
     */
    private static function uploadToOSS(UploadedFile $file, string $path): string
    {
        // 使用 think-filesystem Aliyun 适配器
        $savename = Filesystem::disk('oss')->putFile($path, $file);
        if (!$savename) {
            throw new \Exception('OSS 上传失败');
        }
        return $savename;
    }
    
    /**
     * 上传到腾讯云COS
     */
    private static function uploadToCOS(UploadedFile $file, string $path): string
    {
        $savename = Filesystem::disk('cos')->putFile($path, $file);
        if (!$savename) {
            throw new \Exception('COS 上传失败');
        }
        return $savename;
    }
    
    /**
     * 获取存储配置
     * @param string $storageType 存储类型
     * @return array 配置数组
     */
    public static function getStorageConfig(string $storageType = null): array
    {
        if (empty($storageType)) {
            $storageType = \app\helper\SystemHelper::getStorageDriver();
        }
        
        // 从 SystemConfig 表获取配置
        $config = SystemConfig::getDriverConfig('storage', $storageType);
        
        if ($config) {
            return $config->config ?: [];
        }
        
        // 默认配置
        switch ($storageType) {
            case File::STORAGE_OSS:
                return [
                    'access_id' => env('oss.access_id', ''),
                    'access_secret' => env('oss.access_secret', ''),
                    'bucket' => env('oss.bucket', ''),
                    'endpoint' => env('oss.endpoint', ''),
                    'url' => env('oss.url', ''),
                ];
            case File::STORAGE_COS:
                return [
                    'secret_id' => env('cos.secret_id', ''),
                    'secret_key' => env('cos.secret_key', ''),
                    'bucket' => env('cos.bucket', ''),
                    'region' => env('cos.region', ''),
                    'url' => env('cos.url', ''),
                ];
            case File::STORAGE_LOCAL:
            default:
                return [
                    'root' => app()->getRootPath() . 'public/uploads',
                    'url' => '/uploads',
                ];
        }
    }
    
    /**
     * 删除文件
     */
    public static function deleteFile(File $file): bool
    {
        switch ($file->storage_type) {
            case File::STORAGE_LOCAL:
                return self::deleteFromLocal($file);
                
            case File::STORAGE_OSS:
                return self::deleteFromOSS($file);
                
            case File::STORAGE_COS:
                return self::deleteFromCOS($file);
                
            default:
                return self::deleteFromLocal($file);
        }
    }
    
    /**
     * 从本地删除文件
     */
    private static function deleteFromLocal(File $file): bool
    {
        $filePath = self::getFilePath($file);
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return true;
    }
    
    /**
     * 从OSS删除文件
     */
    private static function deleteFromOSS(File $file): bool
    {
        // TODO: 实现OSS删除
        return true;
    }
    
    /**
     * 从COS删除文件
     */
    private static function deleteFromCOS(File $file): bool
    {
        // TODO: 实现COS删除
        return true;
    }
    
    /**
     * 获取文件完整URL
     */
    public static function getFileUrl(File $file): string
    {
        $baseUrl = config('app.file_base_url', '');
        
        switch ($file->storage_type) {
            case File::STORAGE_LOCAL:
                return $baseUrl . '/' . $file->file_path;
                
            case File::STORAGE_OSS:
            case File::STORAGE_COS:
                return self::getCloudStorageUrl($file);
                
            default:
                return $baseUrl . '/' . $file->file_path;
        }
    }
    
    /**
     * 获取云存储URL
     */
    private static function getCloudStorageUrl(File $file): string
    {
        $driver = $file->storage_type === File::STORAGE_OSS ? 'aliyun' : 'tencent';
        
        $storageConfig = SystemConfig::where('type', 'storage')
            ->where('driver', $driver)
            ->where('status', 1)
            ->value('config');
            
        if ($storageConfig) {
            $config = json_decode($storageConfig, true);
            if (isset($config['domain'])) {
                return $config['domain'] . '/' . $file->file_path;
            }
        }
        
        // 回退到base_url
        return config('app.file_base_url', '') . '/' . $file->file_path;
    }
    
    /**
     * 获取文件物理路径（兼容历史脏数据，统一斜杠，去除绝对路径前缀）
     */
    public static function getFilePath(File $file): string
    {
        // 1. 统一所有斜杠为正斜杠
        $relativePath = str_replace(['\\', '//'], '/', $file->file_path);

        // 2. 去掉绝对路径前缀，只保留 uploads/ 后面的内容
        if (strpos($relativePath, 'uploads/') !== false) {
            $relativePath = substr($relativePath, strpos($relativePath, 'uploads/'));
            $relativePath = substr($relativePath, strlen('uploads/'));
        }
        $relativePath = ltrim($relativePath, '/');

        // 3. 拼接最终物理路径，并去掉末尾的多余反斜杠
        $fullPath = public_path('uploads/' . $relativePath);
        return rtrim($fullPath, '\\/');
    }
    
    /**
     * 下载云存储文件到本地临时目录
     */
    private static function downloadCloudFile(File $file): string
    {
        $tempDir = runtime_path('temp/downloads/');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        $tempFile = $tempDir . md5($file->file_path) . '_' . basename($file->file_path);
        
        // 如果临时文件已存在且未过期（1小时内），直接返回
        if (file_exists($tempFile) && (time() - filemtime($tempFile)) < 3600) {
            return $tempFile;
        }
        
        switch ($file->storage_type) {
            case File::STORAGE_OSS:
                return self::downloadFromOSS($file, $tempFile);
                
            case File::STORAGE_COS:
                return self::downloadFromCOS($file, $tempFile);
                
            default:
                throw new \Exception('不支持的存储类型');
        }
    }
    
    /**
     * 从OSS下载文件
     */
    private static function downloadFromOSS(File $file, string $tempFile): string
    {
        try {
            $config = self::getStorageConfig(File::STORAGE_OSS);
            
            // 使用阿里云SDK下载文件
            $ossClient = new \OSS\OssClient($config['access_id'], $config['access_secret'], $config['endpoint']);
            $ossClient->getObject($config['bucket'], $file->file_path, [
                \OSS\OssClient::OSS_FILE_DOWNLOAD => $tempFile
            ]);
            
            return $tempFile;
        } catch (\Exception $e) {
            throw new \Exception('OSS文件下载失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 从COS下载文件
     */
    private static function downloadFromCOS(File $file, string $tempFile): string
    {
        try {
            $config = self::getStorageConfig(File::STORAGE_COS);
            
            // 使用腾讯云SDK下载文件
            $cosClient = new \Qcloud\Cos\Client([
                'region' => $config['region'],
                'credentials' => [
                    'secretId' => $config['secret_id'],
                    'secretKey' => $config['secret_key'],
                ]
            ]);
            
            $result = $cosClient->getObject([
                'Bucket' => $config['bucket'],
                'Key' => $file->file_path,
                'SaveAs' => $tempFile
            ]);
            
            return $tempFile;
        } catch (\Exception $e) {
            throw new \Exception('COS文件下载失败: ' . $e->getMessage());
        }
    }
} 