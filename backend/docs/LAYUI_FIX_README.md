# Layui 未定义错误修复方案

## 问题描述
在访问 `/admin/content/category` 和 `/admin/content/tag` 页面时出现 `Uncaught ReferenceError: layui is not defined` 错误。

## 问题原因
**脚本块使用方式错误**：分类和标签页面没有使用正确的 ThinkPHP 模板脚本块 `{block name="script"}`，而是直接在页面中写脚本，导致脚本执行时序问题。

## 修复方案

### 1. 参考正确的实现方式
参考 `/admin/article/article` 页面的正确实现方式，该页面使用 `{block name="script"}` 块来包含 JavaScript 代码。

### 2. 修改分类页面 (backend/view/admin/content/category.html)
- 将 JavaScript 代码从页面主体移到 `{block name="script"}` 块中
- 移除不必要的 DOM 加载检查和错误处理代码
- 使用标准的 layui.use 方式初始化
- **修复图标代码**：将 HTML 实体编码 `&#xe654;` 和 `&#xe615;` 改为 layui 图标类名

### 3. 修改标签页面 (backend/view/admin/content/tag.html)
- 应用与分类页面相同的修复方案
- 确保使用正确的脚本块结构
- **修复图标代码**：将 HTML 实体编码改为 layui 图标类名

### 4. 修改测试页面 (backend/view/admin/content/test_layui.html)
- 同样使用 `{block name="script"}` 块
- 保留必要的调试信息

### 5. 修复后端接口问题 (backend/app/controller/admin/Content.php)
- **修复查询条件**：在 category 方法中添加 `where('status', 1)` 条件
- 确保只查询启用的分类，与模型方法保持一致
- 避免数据库查询错误导致的接口异常

## 修复后的特性

### 正确的模板结构
- 使用 ThinkPHP 的模板继承机制
- 脚本代码在基础模板的 `{block name="script"}` 块中执行
- 确保 layui 在正确的时机加载和初始化

### 简化的代码结构
- 移除复杂的错误检查和 DOM 加载监听
- 使用标准的 layui.use 模式
- 代码更简洁、更易维护

### 与系统其他页面保持一致
- 采用与文章管理页面相同的代码结构
- 确保整个系统的代码风格统一

### 正确的图标使用
- 使用 layui 的图标类名而不是 HTML 实体编码
- 避免模板解析错误
- 与系统其他页面保持一致的图标风格

### 正确的后端接口
- 查询条件与模型方法保持一致
- 只返回启用状态的数据
- 避免数据库查询错误

## 修复原理

### 模板继承机制
ThinkPHP 的模板继承机制确保：
1. 基础模板 (`base.html`) 先加载
2. layui 库在基础模板中引入
3. 子页面的 `{block name="script"}` 块在基础模板的对应位置执行
4. 此时 layui 已经可用

### 执行顺序
```
1. 加载基础模板 (包含 layui.js)
2. 渲染页面内容 {block name="content"}
3. 执行页面脚本 {block name="script"}
4. layui.use 正确初始化
```

### 图标修复原理
- HTML 实体编码 `&#xe654;` 和 `&#xe615;` 在模板解析时可能被误认为是模板语法
- 使用 layui 的图标类名 `layui-icon-add-1` 和 `layui-icon-search` 更安全
- 与系统其他页面保持一致的图标使用方式

### 后端接口修复原理
- 原查询条件 `where('parent_id', 0)` 只查询顶级分类
- 修复后使用 `where('status', 1)` 查询所有启用的分类
- 与 `CourseCategory::getCategoryList()` 和 `CourseCategory::getCategoryTree()` 方法保持一致
- 避免查询条件不一致导致的错误

## 测试方法

1. **访问测试页面**：
   ```
   http://your-domain/admin/content/test_layui
   ```

2. **检查控制台**：
   - 打开浏览器开发者工具
   - 查看 Console 标签页
   - 确认没有 layui 相关错误

3. **测试功能页面**：
   ```
   http://your-domain/admin/content/category
   http://your-domain/admin/content/tag
   ```

4. **检查网络请求**：
   - 打开浏览器开发者工具
   - 查看 Network 标签页
   - 确认 `/admin/content/category` 和 `/admin/content/tag` 接口返回正确的 JSON 数据

## 注意事项

1. **模板结构**：确保使用正确的 `{block name="script"}` 块
2. **代码位置**：JavaScript 代码必须在脚本块中，不能在页面主体中
3. **图标使用**：使用 layui 图标类名，避免 HTML 实体编码
4. **查询条件**：确保后端查询条件与模型方法保持一致
5. **缓存清理**：修复后请清除浏览器缓存

## 相关文件

- `backend/view/common/base.html` - 基础模板（包含 `{block name="script"}` 块）
- `backend/view/admin/content/category.html` - 分类管理页面（已修复）
- `backend/view/admin/content/tag.html` - 标签管理页面（已修复）
- `backend/view/admin/content/test_layui.html` - 测试页面（已修复）
- `backend/app/controller/admin/Content.php` - 控制器（已修复查询条件）
- `backend/app/model/CourseCategory.php` - 分类模型
- `backend/app/model/CourseTag.php` - 标签模型
- `backend/view/admin/article/index.html` - 参考实现（文章管理页面）

## 修复前后对比

### 修复前（错误方式）
```html
{extend name="common/base" /}
{block name="content"}
<!-- 页面内容 -->
<button class="layui-btn layui-btn-sm" id="addCategory">
    <i class="layui-icon">&#xe654;</i> 添加分类
</button>
<script>
// 直接在页面中写脚本 - 错误！
layui.use(['table', 'form', 'layer'], function(){
    // ...
});
</script>
{/block}
```

```php
// 后端查询条件错误
$query = CourseCategory::where('parent_id', 0); // 只查询顶级分类
```

### 修复后（正确方式）
```html
{extend name="common/base" /}
{block name="content"}
<!-- 页面内容 -->
<button class="layui-btn layui-btn-sm" id="addCategory">
    <i class="layui-icon layui-icon-add-1"></i> 添加分类
</button>
{/block}

{block name="script"}
<script>
// 在脚本块中写代码 - 正确！
layui.use(['table', 'form', 'layer'], function(){
    // ...
});
</script>
{/block}
```

```php
// 后端查询条件正确
$query = CourseCategory::where('status', 1); // 查询所有启用的分类
```

## 修复的问题列表

1. ✅ **layui 未定义错误** - 通过使用正确的脚本块解决
2. ✅ **模板解析错误** - 通过修复图标代码解决
3. ✅ **代码结构问题** - 通过统一使用 ThinkPHP 模板继承机制解决
4. ✅ **后端接口错误** - 通过修复查询条件解决 