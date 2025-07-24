# 学校管理系统 - 学校端

这是学校管理系统的学校端前端应用，基于 Vue 3 + Element Plus 开发。

## 项目结构

```
frontend-school/
├── src/
│   ├── components/          # 公共组件
│   ├── layouts/            # 布局组件
│   │   └── MainLayout.vue  # 主布局
│   ├── router/             # 路由配置
│   │   └── index.js        # 路由定义
│   ├── stores/             # 状态管理
│   │   └── user.js         # 用户状态
│   ├── utils/              # 工具函数
│   │   └── api.js          # API配置
│   ├── views/              # 页面组件
│   │   ├── Login.vue       # 登录页
│   │   ├── Dashboard.vue   # 控制台
│   │   ├── NotFound.vue    # 404页面
│   │   ├── colleges/       # 学院管理
│   │   ├── teachers/       # 教师管理
│   │   ├── courses/        # 课程管理
│   │   ├── statistics/     # 使用统计
│   │   └── settings/       # 学校设置
│   ├── App.vue             # 根组件
│   └── main.js             # 入口文件
├── package.json
└── README.md
```

## 功能模块

### 1. 用户认证
- 登录/登出
- 用户信息管理
- 权限控制

### 2. 学院管理
- 学院列表
- 学院详情
- 学院信息编辑

### 3. 教师管理
- 教师列表
- 教师审核
- 教师权限配置

### 4. 课程管理
- 课程列表
- 课程详情
- 课程状态管理

### 5. 使用统计
- 教师统计
- 学院统计
- 活跃度统计
- AI使用统计

### 6. 学校设置
- 基本信息设置
- 公告管理
- 通知设置

## 技术栈

- **Vue 3** - 前端框架
- **Element Plus** - UI组件库
- **Vue Router** - 路由管理
- **Pinia** - 状态管理
- **Axios** - HTTP客户端
- **Vite** - 构建工具

## 开发环境

### 安装依赖
```bash
npm install
```

### 启动开发服务器
```bash
npm run dev
```

### 构建生产版本
```bash
npm run build
```

### 代码格式化
```bash
npm run format
```

## API配置

项目使用统一的API配置，基础URL为：`http://localhost:8000/api`

主要接口：
- `/auth/login` - 用户登录
- `/auth/profile` - 获取用户信息
- `/colleges` - 学院管理
- `/teachers` - 教师管理
- `/courses` - 课程管理
- `/statistics` - 统计数据

## 部署说明

1. 构建项目：`npm run build`
2. 将 `dist` 目录部署到Web服务器
3. 配置API地址为生产环境地址

## 注意事项

- 确保后端API服务正常运行
- 检查API地址配置是否正确
- 确保用户权限配置正确
