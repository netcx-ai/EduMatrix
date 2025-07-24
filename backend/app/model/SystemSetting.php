<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;
use think\facade\Cache;

class SystemSetting extends Model
{
    // 设置表名（让ThinkPHP自动加前缀）
    protected $name = 'system_settings';
    
    // 设置字段信息
    protected $schema = [
        'id'          => 'int',
        'group'       => 'string',
        'key'         => 'string',
        'value'       => 'text',
        'type'        => 'string',
        'title'       => 'string',
        'description' => 'string',
        'options'     => 'json',
        'sort'        => 'int',
        'status'      => 'boolean',
        'create_time' => 'int',
        'update_time' => 'int',
    ];

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 获取器：将JSON选项转换为数组
    public function getOptionsAttr($value)
    {
        if ($value === null || $value === '') {
            return [];
        }
        return json_decode($value, true) ?: [];
    }

    // 修改器：将数组选项转换为JSON
    public function setOptionsAttr($value)
    {
        if (empty($value)) {
            return null;
        }
        return json_encode($value);
    }

    /**
     * 获取配置值
     * @param string $key 配置键名
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function getSettingValue($key, $default = null)
    {
        $cacheKey = 'system_setting:' . $key;
        
        return Cache::remember($cacheKey, function() use ($key, $default) {
            $setting = self::where('key', $key)
                ->where('status', 1)
                ->find();
            
            return $setting ? $setting->value : $default;
        }, 3600);
    }

    /**
     * 设置配置值
     * @param string $key 配置键名
     * @param mixed $value 配置值
     * @param string|null $group 配置分组
     * @return bool
     */
    public static function setSettingValue($key, $value, $group = null)
    {
        $query = self::where('key', $key);
        if ($group !== null) {
            $query->where('group', $group);
        }
        $setting = $query->find();
        if (!$setting) {
            return false;
        }
        $setting->value = $value;
        $result = $setting->save();
        if ($result) {
            // 清除缓存
            self::clearCache($key);
            if ($group !== null) {
                self::clearCache(); // 清理分组和全部缓存
            }
        }
        return $result;
    }

    /**
     * 获取分组配置
     * @param string $group 配置分组
     * @return array
     */
    public static function getGroupSettings($group)
    {
        $cacheKey = 'system_settings_group:' . $group;
        
        return Cache::remember($cacheKey, function() use ($group) {
            $settings = self::where('group', $group)
                ->where('status', 1)
                ->order('sort', 'asc')
                ->select();
            
            // 转换为数组并确保每个字段都有值
            $result = [];
            foreach ($settings as $setting) {
                $result[] = $setting->toArray();
            }
            
            return $result;
        }, 3600);
    }

    /**
     * 批量更新配置
     * @param array $settings 配置数组
     * @param string|null $group 配置分组
     * @return bool
     */
    public static function batchUpdate($settings, $group = null)
    {
        try {
            foreach ($settings as $key => $value) {
                self::setSettingValue($key, $value, $group);
            }
            // 批量更新后清理所有缓存
            self::clearCache();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取所有配置（按分组）
     * @return array
     */
    public static function getAllSettings()
    {
        $cacheKey = 'system_settings_all';
        
        return Cache::remember($cacheKey, function() {
            $settings = self::where('status', 1)
                ->order('group', 'asc')
                ->order('sort', 'asc')
                ->select();
            
            $result = [];
            foreach ($settings as $setting) {
                $result[$setting->group][$setting->key] = $setting;
            }
            
            return $result;
        }, 3600);
    }

    /**
     * 清除配置缓存
     * @param string|null $key 指定键名，为空则清除所有
     */
    public static function clearCache($key = null)
    {
        if ($key) {
            Cache::delete('system_setting:' . $key);
        } else {
            Cache::delete('system_settings_all');
            // 清除所有分组缓存
            $groups = self::distinct()->column('group');
            foreach ($groups as $group) {
                Cache::delete('system_settings_group:' . $group);
            }
        }
    }

    /**
     * 获取配置类型选项
     * @return array
     */
    public static function getTypeOptions()
    {
        return [
            'text' => '单行文本',
            'textarea' => '多行文本',
            'number' => '数字',
            'select' => '下拉选择',
            'switch' => '开关',
            'image' => '图片',
            'file' => '文件',
            'color' => '颜色选择器',
            'date' => '日期',
            'datetime' => '日期时间',
            'time' => '时间',
            'radio' => '单选框',
            'checkbox' => '复选框',
            'editor' => '富文本编辑器'
        ];
    }

    /**
     * 获取常用配置分组
     * @return array
     */
    public static function getGroupOptions()
    {
        return [
            'basic' => '基础设置',
            'contact' => '联系方式',
            'seo' => 'SEO设置',
            'social' => '社交设置',
            'upload' => '上传设置',
            'notification' => '通知设置',
            'other' => '其他设置'
        ];
    }
} 