<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 文件模型
 */
class File extends Model
{
    protected $name = 'file';
    
    // 设置字段信息
    protected $schema = [
        'id' => 'int',
        'school_id' => 'int',
        'uploader_id' => 'int',
        'uploader_type' => 'string',
        'course_id' => 'int',
        'ai_tool_code' => 'string',
        'content_id' => 'int',
        'source_type' => 'string',
        'file_name' => 'string',
        'original_name' => 'string',
        'file_path' => 'string',
        'file_size' => 'int',
        'file_type' => 'string',
        'mime_type' => 'string',
        'file_category' => 'string',
        'storage_type' => 'string',
        'is_public' => 'int',
        'download_count' => 'int',
        'view_count' => 'int',
        'status' => 'int',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
    ];
    
    // 自动时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    // 类型转换
    protected $type = [
        'id' => 'integer',
        'school_id' => 'integer',
        'uploader_id' => 'integer',
        'course_id' => 'integer',
        'content_id' => 'integer',
        'file_size' => 'integer',
        'is_public' => 'integer',
        'download_count' => 'integer',
        'view_count' => 'integer',
        'status' => 'integer',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
    ];

    // 状态常量
    const STATUS_NORMAL = 1;
    const STATUS_DISABLED = 0;
    
    // 文件分类常量
    const CATEGORY_DOCUMENT = 'document';
    const CATEGORY_IMAGE = 'image';
    const CATEGORY_VIDEO = 'video';
    const CATEGORY_AUDIO = 'audio';
    const CATEGORY_OTHER = 'other';
    
    // 存储类型常量
    const STORAGE_LOCAL = 'local';
    const STORAGE_OSS = 'oss';
    const STORAGE_COS = 'cos';
    
    // 来源类型常量
    const SOURCE_TYPE_UPLOAD = 'upload';
    const SOURCE_TYPE_AI_GENERATE = 'ai_generate';
    
    // 关联学校
    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }
    
    // 关联上传者（教师）
    public function uploader()
    {
        return $this->belongsTo(Teacher::class, 'uploader_id');
    }
    
    // 关联课程
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
    
    // 关联内容库
    public function content()
    {
        return $this->belongsTo(ContentLibrary::class, 'content_id');
    }
    
    /**
     * 获取文件分类列表
     */
    public static function getCategoryList()
    {
        return [
            self::CATEGORY_DOCUMENT => '文档',
            self::CATEGORY_IMAGE => '图片',
            self::CATEGORY_VIDEO => '视频',
            self::CATEGORY_AUDIO => '音频',
            self::CATEGORY_OTHER => '其他',
        ];
    }
    
    /**
     * 获取存储类型列表
     */
    public static function getStorageTypeList()
    {
        return [
            self::STORAGE_LOCAL => '本地存储',
            self::STORAGE_OSS => '阿里云OSS',
            self::STORAGE_COS => '腾讯云COS',
        ];
    }
    
    /**
     * 获取来源类型列表
     */
    public static function getSourceTypeList()
    {
        return [
            self::SOURCE_TYPE_UPLOAD => '文件上传',
            self::SOURCE_TYPE_AI_GENERATE => 'AI生成'
        ];
    }
    
    /**
     * 获取来源类型名称
     */
    public function getSourceTypeTextAttr($value, $data)
    {
        $types = self::getSourceTypeList();
        return isset($types[$data['source_type']]) ? $types[$data['source_type']] : '';
    }
    
    /**
     * 获取文件大小格式化
     */
    public function getFileSizeTextAttr($value, $data)
    {
        return $this->formatFileSize($data['file_size'] ?? 0);
    }
    
    /**
     * 格式化文件大小
     */
    public function formatFileSize($bytes)
    {
        if ($bytes == 0) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    /**
     * 增加下载次数
     */
    public function incrementDownload()
    {
        $this->download_count += 1;
        $this->save();
    }
    
    /**
     * 增加查看次数
     */
    public function incrementView()
    {
        $this->view_count += 1;
        $this->save();
    }
    
    /**
     * 获取文件完整访问URL
     */
    public function getFullUrlAttribute()
    {
        $baseUrl = config('app.file_base_url', '');
        
        switch ($this->storage_type) {
            case self::STORAGE_LOCAL:
                // 本地存储：使用配置的base_url + 文件路径
                return $baseUrl . '/' . $this->file_path;
                
            case self::STORAGE_OSS:
            case self::STORAGE_COS:
                // 云存储：从系统配置中获取域名
                $storageConfig = \app\model\SystemConfig::where('type', 'storage')
                    ->where('driver', $this->storage_type === self::STORAGE_OSS ? 'aliyun' : 'tencent')
                    ->where('status', 1)
                    ->value('config');
                    
                if ($storageConfig) {
                    $config = json_decode($storageConfig, true);
                    if (isset($config['domain'])) {
                        return $config['domain'] . '/' . $this->file_path;
                    }
                }
                
                // 如果配置不存在，回退到base_url
                return $baseUrl . '/' . $this->file_path;
                
            default:
                return $baseUrl . '/' . $this->file_path;
        }
    }
    
    /**
     * 获取文件下载URL（用于API返回）
     */
    public function getDownloadUrlAttribute()
    {
        return $this->full_url;
    }
} 