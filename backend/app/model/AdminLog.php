<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

class AdminLog extends Model
{
    // 设置表名
    protected $name = 'admin_log';
    
    // 设置字段信息
    protected $schema = [
        'id'          => 'int',
        'admin_id'    => 'int',
        'action'      => 'string',
        'module'      => 'string',
        'content'     => 'string',
        'ip'          => 'string',
        'user_agent'  => 'string',
        'create_time' => 'datetime',
    ];

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 关联管理员
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
} 