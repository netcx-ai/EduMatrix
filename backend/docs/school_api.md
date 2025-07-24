# 学校管理端API文档

## 概述

学校管理端API提供学校管理员对学校、学院、教师等资源的管理功能。

## 认证方式

所有API请求都需要在请求头中包含学校编码：
```
X-School-Code: PKU
```

需要认证的API还需要在请求头中包含Token：
```
Authorization: Bearer {token}
```

## 基础URL

```
http://localhost:8000/school
```

## API列表

### 1. 管理员认证

#### 1.1 管理员登录
- **URL**: `/admin/login`
- **方法**: `POST`
- **参数**:
  ```json
  {
    "username": "pku_admin",
    "password": "123456",
    "school_code": "PKU"
  }
  ```
- **响应**:
  ```json
  {
    "code": 200,
    "message": "登录成功",
    "data": {
      "token": "xxx",
      "admin": {
        "id": 1,
        "username": "pku_admin",
        "real_name": "北大管理员",
        "role": "admin",
        "role_text": "管理员"
      },
      "school": {
        "id": 1,
        "name": "北京大学",
        "code": "PKU"
      }
    }
  }
  ```

#### 1.2 获取管理员信息
- **URL**: `/admin/info`
- **方法**: `GET`
- **认证**: 需要
- **响应**:
  ```json
  {
    "code": 200,
    "data": {
      "admin": {
        "id": 1,
        "username": "pku_admin",
        "real_name": "北大管理员",
        "role": "admin",
        "phone": "13800138001",
        "email": "admin@pku.edu.cn"
      },
      "school": {
        "id": 1,
        "name": "北京大学",
        "code": "PKU",
        "teacher_count": 0,
        "student_count": 0
      }
    }
  }
  ```

#### 1.3 退出登录
- **URL**: `/admin/logout`
- **方法**: `POST`
- **认证**: 需要

#### 1.4 修改密码
- **URL**: `/admin/change-password`
- **方法**: `POST`
- **认证**: 需要
- **参数**:
  ```json
  {
    "old_password": "123456",
    "new_password": "newpass123",
    "confirm_password": "newpass123"
  }
  ```

#### 1.5 获取统计信息
- **URL**: `/admin/stats`
- **方法**: `GET`
- **认证**: 需要
- **响应**:
  ```json
  {
    "code": 200,
    "data": {
      "college_count": 2,
      "teacher_stats": {
        "total": 0,
        "active": 0,
        "pending": 0,
        "verified": 0
      },
      "admin_count": 1
    }
  }
  ```

### 2. 学院管理

#### 2.1 获取学院列表
- **URL**: `/college/`
- **方法**: `GET`
- **认证**: 需要
- **参数**:
  - `page`: 页码（默认1）
  - `limit`: 每页数量（默认20）
  - `keyword`: 搜索关键词
- **响应**:
  ```json
  {
    "code": 200,
    "data": {
      "list": [
        {
          "id": 1,
          "name": "计算机学院",
          "code": "CS",
          "short_name": "计院",
          "teacher_count": 0
        }
      ],
      "total": 1,
      "page": 1,
      "limit": 20
    }
  }
  ```

#### 2.2 获取学院详情
- **URL**: `/college/{id}`
- **方法**: `GET`
- **认证**: 需要

#### 2.3 创建学院
- **URL**: `/college/`
- **方法**: `POST`
- **认证**: 需要
- **参数**:
  ```json
  {
    "name": "数学学院",
    "code": "MATH",
    "short_name": "数院",
    "description": "数学学院",
    "dean": "张教授",
    "phone": "010-12345678",
    "email": "math@pku.edu.cn"
  }
  ```

#### 2.4 更新学院
- **URL**: `/college/{id}`
- **方法**: `PUT`
- **认证**: 需要

#### 2.5 删除学院
- **URL**: `/college/{id}`
- **方法**: `DELETE`
- **认证**: 需要

#### 2.6 获取学院下拉列表
- **URL**: `/college/list`
- **方法**: `GET`
- **认证**: 需要

### 3. 教师管理

#### 3.1 获取教师列表
- **URL**: `/teacher/`
- **方法**: `GET`
- **认证**: 需要
- **参数**:
  - `page`: 页码
  - `limit`: 每页数量
  - `keyword`: 搜索关键词
  - `college_id`: 学院ID
  - `status`: 状态（0禁用，1启用，2待审核）
  - `is_verified`: 认证状态（0未认证，1已认证）

#### 3.2 获取教师详情
- **URL**: `/teacher/{id}`
- **方法**: `GET`
- **认证**: 需要

#### 3.3 审核教师
- **URL**: `/teacher/{id}/verify`
- **方法**: `POST`
- **认证**: 需要
- **参数**:
  ```json
  {
    "action": "approve",  // approve 或 reject
    "reason": "审核通过"  // 拒绝时需要提供原因
  }
  ```

#### 3.4 批量审核教师
- **URL**: `/teacher/batch-verify`
- **方法**: `POST`
- **认证**: 需要
- **参数**:
  ```json
  {
    "teacher_ids": [1, 2, 3],
    "action": "approve",
    "reason": "批量审核通过"
  }
  ```

#### 3.5 更新教师状态
- **URL**: `/teacher/{id}/status`
- **方法**: `PUT`
- **认证**: 需要
- **参数**:
  ```json
  {
    "status": 1
  }
  ```

#### 3.6 删除教师
- **URL**: `/teacher/{id}`
- **方法**: `DELETE`
- **认证**: 需要

#### 3.7 获取待审核教师
- **URL**: `/teacher/pending`
- **方法**: `GET`
- **认证**: 需要

#### 3.8 获取教师统计
- **URL**: `/teacher/stats`
- **方法**: `GET`
- **认证**: 需要
- **响应**:
  ```json
  {
    "code": 200,
    "data": {
      "overall": {
        "total": 10,
        "active": 8,
        "pending": 2,
        "verified": 7,
        "unverified": 3
      },
      "by_college": [
        {
          "college_id": 1,
          "count": 5
        }
      ]
    }
  }
  ```

## 错误码说明

- `200`: 成功
- `400`: 请求参数错误
- `401`: 未认证或认证失败
- `403`: 权限不足或学校被禁用
- `404`: 资源不存在
- `500`: 服务器内部错误

## 测试数据

系统已预置以下测试数据：

### 学校
- 北京大学 (PKU)
- 清华大学 (THU)

### 学院
- 北京大学计算机学院 (CS)
- 北京大学数学学院 (MATH)
- 清华大学计算机科学与技术系 (CS)

### 管理员
- 北大管理员: pku_admin / 123456
- 清华管理员: thu_admin / 123456 