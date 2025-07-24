<?php

namespace app\validate;

use think\Validate;

class AiTemplateValidate extends Validate
{
    protected $rule = [
        'name' => 'require|max:255',
        'description' => 'max:1000',
        'tool_code' => 'require|max:100',
        'params' => 'require',
        'category' => 'in:official,personal,shared',
        'is_public' => 'boolean',
        'tags' => 'max:500',
        'version' => 'max:20',
        'status' => 'in:draft,published,archived'
    ];

    protected $message = [
        'name.require' => '模板名称不能为空',
        'name.max' => '模板名称不能超过255个字符',
        'description.max' => '模板描述不能超过1000个字符',
        'tool_code.require' => '工具代码不能为空',
        'tool_code.max' => '工具代码不能超过100个字符',
        'params.require' => '参数配置不能为空',
        'category.in' => '分类必须是: official, personal, shared',
        'is_public.boolean' => '是否公开必须是布尔值',
        'tags.max' => '标签不能超过500个字符',
        'version.max' => '版本号不能超过20个字符',
        'status.in' => '状态必须是: draft, published, archived'
    ];

    protected $scene = [
        'create' => ['name', 'tool_code', 'params', 'category', 'is_public', 'tags', 'version', 'status'],
        'update' => ['name', 'description', 'params', 'category', 'is_public', 'tags', 'version', 'status']
    ];
} 