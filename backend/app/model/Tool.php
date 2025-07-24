<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

class Tool extends Model
{
    // 设置表名
    protected $name = 'tool';
    
    // 设置字段信息
    protected $schema = [
        'id'          => 'int',
        'name'        => 'string',
        'type'        => 'string',
        'value'       => 'string',
        'description' => 'string',
        'status'      => 'int',
        'create_time' => 'datetime',
        'update_time' => 'datetime'
    ];
    
    // 类型列表
    public static function getTypeList()
    {
        return [
            'api' => 'API配置',
            'system' => '系统配置',
            'other' => '其他配置'
        ];
    }
    
    // 状态列表
    public static function getStatusList()
    {
        return [
            0 => '停用',
            1 => '启用'
        ];
    }
    
    // 获取类型文本
    public function getTypeTextAttr()
    {
        $list = self::getTypeList();
        return $list[$this->type] ?? '未知';
    }
    
    // 获取状态文本
    public function getStatusTextAttr()
    {
        $list = self::getStatusList();
        return $list[$this->status] ?? '未知';
    }
} 