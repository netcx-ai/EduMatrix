# AI工具API文档

## 概述

AI工具模块提供教育管理系统的AI功能支持，包括工具配置管理、权限控制、服务调用和使用统计等功能。

## 认证方式

### 平台端管理API
- 使用Session认证
- 需要在请求头中包含有效的管理员登录状态

### AI服务调用API
- 无需认证，但需要提供有效的school_id和user_id
- 系统会根据学校权限和使用限制进行控制

## 平台端管理API

### 1. 获取AI工具列表

**接口地址：** `GET /admin/ai_tool/index`

**请求参数：**
```json
{
    "page": 1,
    "limit": 10,
    "category": "content",
    "status": 1,
    "keyword": "讲稿"
}
```

**响应示例：**
```json
{
    "code": 200,
    "message": "success",
    "data": {
        "list": [
            {
                "id": 1,
                "name": "讲稿生成",
                "code": "lecture_generator",
                "description": "根据课程主题和内容自动生成教学讲稿",
                "category": "content",
                "category_text": "内容生成",
                "prompt_template": "请根据以下课程信息生成一份详细的教学讲稿...",
                "api_config": {
                    "model": "gpt-3.5-turbo",
                    "temperature": 0.7,
                    "max_tokens": 2000
                },
                "icon": "/static/icons/lecture.png",
                "sort": 1,
                "status": 1,
                "status_text": "启用",
                "usage_count": 150,
                "school_count": 5,
                "statistics": {
                    "total_usage": 150,
                    "today_usage": 12,
                    "month_usage": 89
                },
                "create_time": "2024-01-03 10:00:00",
                "update_time": "2024-01-03 10:00:00"
            }
        ],
        "total": 6,
        "page": 1,
        "limit": 10,
        "categories": {
            "content": "内容生成",
            "analysis": "分析",
            "assessment": "评估"
        },
        "statuses": {
            "0": "禁用",
            "1": "启用"
        }
    }
}
```

### 2. 获取AI工具详情

**接口地址：** `GET /admin/ai_tool/show/{id}`

**响应示例：**
```json
{
    "code": 200,
    "message": "success",
    "data": {
        "tool": {
            "id": 1,
            "name": "讲稿生成",
            "code": "lecture_generator",
            "description": "根据课程主题和内容自动生成教学讲稿",
            "category": "content",
            "prompt_template": "请根据以下课程信息生成一份详细的教学讲稿...",
            "api_config": {
                "model": "gpt-3.5-turbo",
                "temperature": 0.7,
                "max_tokens": 2000
            },
            "icon": "/static/icons/lecture.png",
            "sort": 1,
            "status": 1,
            "create_time": "2024-01-03 10:00:00",
            "update_time": "2024-01-03 10:00:00"
        },
        "authorized_schools": [
            {
                "id": 1,
                "tool_id": 1,
                "school_id": 1,
                "daily_limit": 100,
                "monthly_limit": 3000,
                "status": 1,
                "school": {
                    "id": 1,
                    "name": "示例学校",
                    "code": "EXAMPLE001"
                }
            }
        ],
        "statistics": {
            "total_usage": 150,
            "today_usage": 12,
            "month_usage": 89
        }
    }
}
```

### 3. 创建AI工具

**接口地址：** `POST /admin/ai_tool/store`

**请求参数：**
```json
{
    "name": "测试工具",
    "code": "test_tool",
    "description": "这是一个测试AI工具",
    "category": "content",
    "prompt_template": "请根据以下信息生成内容：{content}",
    "api_config": {
        "model": "gpt-3.5-turbo",
        "temperature": 0.7,
        "max_tokens": 1000
    },
    "icon": "/static/icons/test.png",
    "sort": 10,
    "status": 1
}
```

**响应示例：**
```json
{
    "code": 200,
    "message": "AI工具创建成功",
    "data": {
        "id": 7
    }
}
```

### 4. 更新AI工具

**接口地址：** `PUT /admin/ai_tool/update/{id}`

**请求参数：** 同创建接口

**响应示例：**
```json
{
    "code": 200,
    "message": "AI工具更新成功"
}
```

### 5. 删除AI工具

**接口地址：** `DELETE /admin/ai_tool/destroy/{id}`

**响应示例：**
```json
{
    "code": 200,
    "message": "AI工具删除成功"
}
```

### 6. 批量操作

**接口地址：** `POST /admin/ai_tool/batch`

**请求参数：**
```json
{
    "action": "enable",
    "ids": [1, 2, 3]
}
```

**支持的操作：**
- `enable`: 批量启用
- `disable`: 批量禁用
- `delete`: 批量删除

### 7. 获取学校列表

**接口地址：** `GET /admin/ai_tool/schools`

**响应示例：**
```json
{
    "code": 200,
    "message": "success",
    "data": {
        "schools": [
            {
                "id": 1,
                "name": "示例学校",
                "code": "EXAMPLE001"
            }
        ]
    }
}
```

### 8. 授权工具给学校

**接口地址：** `POST /admin/ai_tool/authorize`

**请求参数：**
```json
{
    "tool_id": 1,
    "school_ids": [1, 2],
    "daily_limit": 50,
    "monthly_limit": 1500
}
```

**响应示例：**
```json
{
    "code": 200,
    "message": "授权成功"
}
```

### 9. 取消授权

**接口地址：** `POST /admin/ai_tool/revoke`

**请求参数：**
```json
{
    "tool_id": 1,
    "school_ids": [1, 2]
}
```

**响应示例：**
```json
{
    "code": 200,
    "message": "取消授权成功"
}
```

### 10. 获取统计信息

**接口地址：** `GET /admin/ai_tool/statistics`

**响应示例：**
```json
{
    "code": 200,
    "message": "success",
    "data": {
        "total_tools": 6,
        "enabled_tools": 5,
        "total_usage": 1250,
        "today_usage": 89,
        "month_usage": 567
    }
}
```

## AI服务调用API

### 1. 获取可用工具列表

**接口地址：** `GET /api/ai/tools`

**请求参数：**
```
school_id=1&category=content
```

**响应示例：**
```json
{
    "code": 200,
    "message": "success",
    "data": {
        "tools": [
            {
                "id": 1,
                "name": "讲稿生成",
                "code": "lecture_generator",
                "description": "根据课程主题和内容自动生成教学讲稿",
                "category": "content",
                "category_text": "内容生成",
                "icon": "/static/icons/lecture.png",
                "prompt_template": "请根据以下课程信息生成一份详细的教学讲稿...",
                "usage_limits": {
                    "daily_limit": 100,
                    "monthly_limit": 3000,
                    "today_usage": 12,
                    "month_usage": 89,
                    "daily_remaining": 88,
                    "monthly_remaining": 2911
                }
            }
        ]
    }
}
```

### 2. 调用AI工具

**接口地址：** `POST /api/ai/call`

**请求参数：**
```json
{
    "tool_code": "lecture_generator",
    "school_id": 1,
    "user_id": 1,
    "params": {
        "topic": "数学基础",
        "duration": "45分钟",
        "objectives": "掌握基本运算",
        "key_points": "加减乘除运算"
    }
}
```

**响应示例：**
```json
{
    "code": 200,
    "message": "调用成功",
    "data": {
        "result": "根据您提供的课程信息，我为您生成了一份详细的教学讲稿...",
        "tokens_used": 245,
        "cost": 0.0049
    }
}
```

### 3. 获取使用记录

**接口地址：** `GET /api/ai/usage/history`

**请求参数：**
```
user_id=1&school_id=1&tool_id=1&page=1&limit=10
```

**响应示例：**
```json
{
    "code": 200,
    "message": "success",
    "data": {
        "list": [
            {
                "id": 1,
                "tool_id": 1,
                "user_id": 1,
                "school_id": 1,
                "request_data": {
                    "tool_code": "lecture_generator",
                    "params": {
                        "topic": "数学基础"
                    }
                },
                "response_data": {
                    "success": true,
                    "data": "根据您提供的课程信息..."
                },
                "tokens_used": 245,
                "cost": 0.0049,
                "status": "success",
                "create_time": "2024-01-03 15:30:00",
                "tool": {
                    "id": 1,
                    "name": "讲稿生成",
                    "code": "lecture_generator"
                }
            }
        ],
        "total": 25,
        "page": 1,
        "limit": 10
    }
}
```

### 4. 获取使用统计

**接口地址：** `GET /api/ai/usage/statistics`

**请求参数：**
```
user_id=1&school_id=1
```

**响应示例：**
```json
{
    "code": 200,
    "message": "success",
    "data": {
        "user_statistics": {
            "today_usage": 5,
            "month_usage": 45,
            "total_usage": 125
        },
        "school_statistics": {
            "today_usage": 25,
            "month_usage": 567,
            "total_usage": 1250
        }
    }
}
```

## 错误码说明

| 错误码 | 说明 |
|--------|------|
| 200 | 成功 |
| 400 | 请求参数错误 |
| 401 | 未授权 |
| 403 | 权限不足 |
| 404 | 资源不存在 |
| 429 | 使用限制已满 |
| 500 | 服务器内部错误 |

## 使用限制

1. **每日限制**：每个学校每个工具每日使用次数限制
2. **每月限制**：每个学校每个工具每月使用次数限制
3. **权限控制**：只有被授权的学校才能使用指定工具
4. **参数验证**：调用时必须提供有效的school_id和user_id

## 注意事项

1. AI工具调用会产生费用，系统会记录token使用量和费用
2. 使用记录会保存请求和响应数据，便于后续分析
3. 工具状态为禁用时无法调用
4. 学校权限被禁用时无法使用对应工具
5. 超出使用限制时会返回429错误 