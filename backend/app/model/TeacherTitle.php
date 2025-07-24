<?php
declare(strict_types=1);

namespace app\model;

use think\Model;
use think\facade\Cache;
use think\facade\Log; // Added missing import for Log

/**
 * 教师职称模型
 */
class TeacherTitle extends Model
{
    protected $name = 'teacher_titles';
    
    // 设置字段信息
    protected $schema = [
        'id' => 'int',
        'name' => 'string',
        'code' => 'string', 
        'sort' => 'int',
        'level' => 'int',
        'description' => 'string',
        'status' => 'int',
        'create_time' => 'datetime',
        'update_time' => 'datetime'
    ];

    // 自动时间戳
    protected $autoWriteTimestamp = true;

    /**
     * 职称名称访问器 - 解决$name属性与数据库字段冲突
     * 
     * @param mixed $value 原始值
     * @param array $data 数据数组
     * @return string 职称名称
     */
    public function getNameAttr($value, $data)
    {
        // 优先返回数据库字段的name值
        if (isset($data['name'])) {
            return $data['name'];
        }
        // 兼容对象属性
        if (property_exists($this, 'data') && isset($this->data['name'])) {
            return $this->data['name'];
        }
        // 兜底返回原始值
        return $value ?: $this->getData('name');
    }

    // 缓存键名
    const CACHE_KEY_ALL = 'teacher_titles_all';
    const CACHE_KEY_ACTIVE = 'teacher_titles_active';
    const CACHE_KEY_OPTIONS = 'teacher_titles_options';

    /**
     * 获取所有启用的职称（带缓存）
     * @return array
     */
    public static function getActiveTitles(): array
    {
        return Cache::remember(self::CACHE_KEY_ACTIVE, function() {
            return self::where('status', 1)
                ->order('sort', 'asc')
                ->order('level', 'desc')
                ->select()
                ->toArray();
        }, 3600); // 缓存1小时
    }

    /**
     * 获取职称选项
     * 
     * @return array
     */
    public static function getTitleOptions()
    {
        try {
            $titles = self::where('status', 1)
                ->field('id, name')
                ->order('sort', 'asc')
                ->select();
            
            return $titles->toArray();
        } catch (\Exception $e) {
            Log::error("获取职称选项失败: " . $e->getMessage());
            return [];
        }
    }

    /**
     * 获取职称名称（智能处理）
     * 
     * @param int|string $titleId 职称ID
     * @return string 职称名称
     */
    public static function getTitleNameSmart($titleId)
    {
        if (!$titleId || $titleId === '') {
            return '未设置';
        }
        $titleId = (int)$titleId;
        try {
            $title = self::where('id', $titleId)->where('status', 1)->find();
            return $title ? $title['name'] : '未知职称';
        } catch (\Exception $e) {
            Log::error("获取职称名称失败: " . $e->getMessage(), ['title_id' => $titleId]);
            return '未知职称';
        }
    }

    /**
     * 根据代码获取职称ID
     * @param string $code
     * @return int
     */
    public static function getIdByCode(string $code): int
    {
        $title = self::where('code', $code)->where('status', 1)->find();
        return $title ? $title->id : 5; // 默认返回"其他"
    }

    /**
     * 根据ID获取职称代码
     * @param int $id
     * @return string
     */
    public static function getCodeById(int $id): string
    {
        $title = self::where('id', $id)->where('status', 1)->find();
        return $title ? $title->code : 'other';
    }

    /**
     * 清除缓存
     */
    public static function clearCache(): void
    {
        Cache::delete(self::CACHE_KEY_ALL);
        Cache::delete(self::CACHE_KEY_ACTIVE);
        Cache::delete(self::CACHE_KEY_OPTIONS);
    }

    /**
     * 模型事件 - 保存后清除缓存
     */
    public static function onAfterWrite(): void
    {
        self::clearCache();
    }

    /**
     * 模型事件 - 删除后清除缓存  
     */
    public static function onAfterDelete(): void
    {
        self::clearCache();
    }

    /**
     * 兼容旧的英文代码映射
     * @param string $enCode
     * @return int
     */
    public static function convertEnCodeToId(string $enCode): int
    {
        return self::getIdByCode($enCode);
    }

    /**
     * 职称级别配置
     */
    const LEVEL_CONFIG = [
        5 => [
            'name' => '正高级',
            'examples' => '（如：教授、研究员）'
        ],
        4 => [
            'name' => '副高级',
            'examples' => '（如：副教授、副研究员）'
        ],
        3 => [
            'name' => '中级',
            'examples' => '（如：讲师、工程师）'
        ],
        2 => [
            'name' => '助理级',
            'examples' => '（如：助教、助理工程师）'
        ],
        1 => [
            'name' => '初级',
            'examples' => '（如：实习、见习）'
        ]
    ];

    /**
     * 获取职称级别描述
     * @param int $level
     * @return string
     */
    public static function getLevelDescription(int $level): string
    {
        return self::LEVEL_CONFIG[$level]['name'] ?? '未知';
    }

    /**
     * 获取级别配置（用于API返回）
     * @return array
     */
    public static function getLevelOptions(): array
    {
        return self::LEVEL_CONFIG;
    }

    /**
     * 获取级别信息
     * @param int $level
     * @return array|null
     */
    public static function getLevelInfo(int $level): ?array
    {
        return self::LEVEL_CONFIG[$level] ?? null;
    }
} 