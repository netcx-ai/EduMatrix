# AI工具配置化改进指南

## 📋 **改进概述**

我们已经将AI工具从硬编码配置升级为数据库驱动的配置化系统，让管理员可以灵活配置各种AI工具的参数，而教师端只需要输入简单的参数即可使用。

## 🎯 **主要改进点**

### **1. 数据库配置化**
- **之前**：AI工具的配置（如提示词、API参数）硬编码在代码中
- **现在**：所有配置存储在数据库的 `api_config` 字段中，支持JSON格式的复杂配置

### **2. 动态表单生成**
- **之前**：前端表单是固定的，无法适应不同工具的需求
- **现在**：根据数据库配置动态生成表单，支持多种输入类型

### **3. 参数验证**
- **之前**：参数验证逻辑分散，不够统一
- **现在**：统一的参数验证系统，支持类型检查、范围验证等

### **4. 输出格式标准化**
- **之前**：输出格式不统一，难以处理
- **现在**：支持JSON和Markdown两种标准化输出格式

## 🗄️ **数据库配置结构**

### **AI工具配置示例**

```json
{
  "provider": "deepseek",
  "api_key": "your_api_key_here",
  "api_url": "https://api.deepseek.com/v1/chat/completions",
  "model": "deepseek-chat",
  "max_tokens": 2000,
  "temperature": 0.7,
  "system_prompt": "你是一位经验丰富的教师...",
  "output_format": {
    "type": "json",
    "schema": {
      "title": "作业标题",
      "questions": [...]
    }
  },
  "input_params": [
    {
      "name": "topic",
      "label": "课程主题",
      "type": "text",
      "required": true,
      "placeholder": "请输入课程主题"
    },
    {
      "name": "question_count",
      "label": "题目数量",
      "type": "number",
      "required": true,
      "default": 5,
      "min": 1,
      "max": 20
    }
  ]
}
```

### **支持的输入类型**

1. **text** - 文本输入
2. **textarea** - 多行文本
3. **select** - 下拉选择
4. **number** - 数字输入（支持范围限制）
5. **checkbox** - 多选框

## 🔧 **新增的服务类**

### **AiToolConfigService**
- `getInputParams()` - 获取工具的输入参数配置
- `validateParams()` - 验证用户输入的参数
- `buildSystemPrompt()` - 构建系统提示词
- `buildUserPrompt()` - 构建用户提示词
- `getFormConfig()` - 获取前端表单配置

## 📡 **新增的API接口**

### **获取工具表单配置**
```
GET /api/teacher/ai-tool/getToolFormConfig?tool_code=lecture_generator
```

### **获取所有工具配置**
```
GET /api/teacher/ai-tool/getAllToolsFormConfig
```

## 🚀 **使用流程**

### **管理员端（配置工具）**
1. 在平台管理后台配置AI工具的详细参数
2. 设置输入参数的类型、验证规则、默认值等
3. 配置输出格式（JSON或Markdown）
4. 设置系统提示词和用户提示词模板

### **教师端（使用工具）**
1. 选择要使用的AI工具
2. 系统根据配置动态生成表单
3. 填写必要的参数（如课程主题、题目数量等）
4. 提交生成请求
5. 系统自动验证参数并调用AI服务

## 📊 **配置示例**

### **讲稿生成工具**
```json
{
  "input_params": [
    {
      "name": "topic",
      "label": "课程主题",
      "type": "text",
      "required": true
    },
    {
      "name": "duration",
      "label": "课程时长",
      "type": "select",
      "options": [
        {"value": "30分钟", "label": "30分钟"},
        {"value": "45分钟", "label": "45分钟"}
      ]
    }
  ]
}
```

### **作业生成工具**
```json
{
  "input_params": [
    {
      "name": "content",
      "label": "课程内容",
      "type": "textarea",
      "required": true
    },
    {
      "name": "question_count",
      "label": "题目数量",
      "type": "number",
      "default": 5,
      "min": 1,
      "max": 20
    }
  ],
  "output_format": {
    "type": "json",
    "schema": {
      "title": "作业标题",
      "questions": [...]
    }
  }
}
```

## 🔄 **升级步骤**

1. **执行SQL脚本**：运行 `update_ai_tool_configs.sql` 更新现有工具配置
2. **更新代码**：确保使用新的 `AiToolConfigService`
3. **测试验证**：测试各个工具的配置和生成功能
4. **前端适配**：前端需要适配动态表单生成

## ✅ **优势总结**

1. **灵活性**：管理员可以随时调整工具配置，无需修改代码
2. **易用性**：教师端界面更友好，参数输入更直观
3. **可扩展性**：新增工具只需配置数据库，无需开发
4. **标准化**：统一的参数验证和输出格式
5. **可维护性**：配置集中管理，便于维护和调试

## 🎉 **完成状态**

- ✅ 数据库配置结构设计
- ✅ 配置化服务类实现
- ✅ 参数验证系统
- ✅ 动态表单生成
- ✅ API接口开发
- ✅ 现有工具配置更新
- 🔄 前端适配（待完成）
- 🔄 测试验证（待完成）

---

**下一步**：需要前端开发人员适配动态表单生成功能，并完成全面的测试验证。 