<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

use think\facade\Log;
class SystemConfig extends Model
{
    // 设置表名（让ThinkPHP自动加前缀）
    protected $name = 'system_config';

    // 设置字段信息
    protected $schema = [
        'id'          => 'int',
        'type'        => 'string',
        'name'        => 'string',
        'driver'      => 'string',
        'config'      => 'json',
        'status'      => 'boolean',
        'is_default'  => 'boolean',
        'remark'      => 'string',
        'create_time' => 'int',
        'update_time' => 'int',
    ];

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 获取器：将JSON配置转换为数组
    public function getConfigAttr($value)
    {
        return json_decode($value, true);
    }

    // 修改器：将数组配置转换为JSON
    public function setConfigAttr($value)
    {
        return json_encode($value);
    }

    /**
     * 获取指定类型的默认配置
     * @param string $type 配置类型
     * @return array|null
     */
    public static function getDefaultConfig($type)
    {
        \think\facade\Log::info('开始获取默认配置，类型：' . $type);
        
        $config = self::where('type', $type)
            ->where('is_default', '=', 1)
            ->where('status', '=', 1)
            ->find();

        \think\facade\Log::info('查询到的配置：' . json_encode($config, JSON_UNESCAPED_UNICODE));

        if (!$config) {
            \think\facade\Log::error('未找到默认配置');
            return null;
        }

        return $config;
    }

    // 获取指定类型和驱动的配置
    public static function getDriverConfig($type, $driver)
    {
        return self::where('type', $type)
            ->where('driver', $driver)
            ->where('status', '=', 1)
            ->find();
    }

    // 获取指定类型的所有配置
    public static function getTypeConfigs($type)
    {
        return self::where('type', $type)
            ->where('status', '=', 1)
            ->select();
    }

    // 设置默认配置
    public static function setDefault($id)
    {
        try {
            // 使用数据库事务确保数据一致性
            return self::transaction(function() use ($id) {
                $config = self::find($id);
                if (!$config) {
                    return false;
                }
                
                // 使用原子操作：先清除同类型的所有默认配置，再设置新的默认配置
                $type = $config->getData('type');
                
                // 清除同类型的所有默认配置
                self::where('type', '=', $type)
                    ->where('is_default', '=', 1)
                    ->update(['is_default' => 0]);

                // 设置新的默认配置
                $setResult = self::where('id', '=', $id)->update(['is_default' => 1]);

                // 验证结果：确保只有一个默认配置
                $defaultCount = self::where('type', '=', $type)
                    ->where('is_default', '=', 1)
                    ->count();
                
                if ($defaultCount !== 1) {
                    throw new \Exception('设置默认配置失败：默认配置数量异常');
                }

                return $setResult !== false;
            });
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取指定类型的配置
     * @param string $type 配置类型
     * @return array|null
     */
    public static function getConfig(string $type)
    {
        $configs = self::where('type', $type)->select();
        if ($configs->isEmpty()) {
            return null;
        }

        $result = [];
        foreach ($configs as $config) {
            $result[$config->name] = $config->value;
        }
        return $result;
    }

    /**
     * 保存配置
     * @param string $type 配置类型
     * @param array $configs 配置数组
     * @return bool
     */
    public static function saveConfig(string $type, array $configs)
    {
        try {
            $config = self::where('type', $type)
                ->where('is_default', '=', 1)
                ->find();

            if (!$config) {
                // 如果没有默认配置，创建新的
                $config = new self([
                    'type' => $type,
                    'is_default' => 1,
                    'status' => 1
                ]);
            }

            $config->config = $configs;
            return $config->save();
        } catch (\Exception $e) {
            return false;
        }
    }
} 