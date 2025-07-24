# 媒体管理器 (media-manager.html) 使用说明

本文档旨在说明如何调用和使用 `media-manager.html` 媒体选择器。

---

## 1. 核心功能

- **多媒体支持**: 支持图片和视频文件的管理和选择。
- **文件操作**: 支持文件上传、删除、重命名和预览。
- **调用方式**: 通过 URL 参数控制选择模式（单选/多选）和媒体类型（图片/视频）。
- **通信机制**: 使用 `window.postMessage` 与父窗口通信，返回选择结果。
- **用户体验**: 支持拖拽上传、快捷键操作，界面简洁直观。

---

## 2. 调用方式

通过 `iframe` 或新窗口打开 `media-manager.html`，并通过 URL 查询参数进行配置。

### URL 参数说明

| 参数    | 可选值        | 描述                                                                                             |
| :------ | :------------ | :----------------------------------------------------------------------------------------------- |
| `multi` | `true`, `false` | **(必需)** 控制选择模式。<br>- `true`: 多选模式，允许同时选择多个文件，并显示图片/视频切换按钮。<br>- `false`: 单选模式，只能选择一个文件，并隐藏媒体类型切换按钮。 |
| `type`  | `image`, `video`  | **(可选)** 指定默认打开的媒体类型。<br>- `image`: 图片（默认值）。<br>- `video`: 视频。<br>此参数在单选模式下尤其重要，用于限定选择类型。 |

---

## 3. 使用示例

### 示例 1: 编辑器多选（图片和视频均可）

适用于富文本编辑器等需要插入多种媒体的场景。

- **URL**: `/filemanager/media-manager.html?multi=true`
- **特点**: 显示图片/视频切换按钮，允许用户自由选择。

```javascript
// 使用 layer.open 弹出 iframe
layer.open({
    type: 2,
    title: '媒体管理器',
    area: ['80%', '70%'],
    content: '/filemanager/media-manager.html?multi=true'
});

// 监听返回的消息
window.addEventListener('message', function(event) {
    // 建议增加来源验证
    // if (event.origin !== 'http://your-domain.com') return;
    
    if (event.data && event.data.type === 'mediaSelected') {
        const files = event.data.files; // 返回一个包含文件路径的数组
        console.log('已选择的文件:', files);
        // 在这里处理返回的文件路径...
    }
});
```

### 示例 2: 单选图片

适用于选择文章封面、用户头像等场景。

- **URL**: `/filemanager/media-manager.html?multi=false&type=image` (或省略 `type=image`)
- **特点**: 隐藏媒体切换按钮，只显示图片。

```javascript
layer.open({
    type: 2,
    title: '选择封面图片',
    area: ['80%', '70%'],
    content: '/filemanager/media-manager.html?multi=false'
});

// 监听返回的消息
window.addEventListener('message', function(event) {
    if (event.data && event.data.type === 'mediaSelected') {
        const filePath = event.data.files[0]; // 返回一个只包含单个文件路径的数组
        console.log('已选择的图片:', filePath);
        // document.getElementById('coverImage').src = filePath;
    }
});
```

### 示例 3: 单选视频

适用于选择视频封面或插入单个视频的场景。

- **URL**: `/filemanager/media-manager.html?multi=false&type=video`
- **特点**: 隐藏媒体切换按钮，只显示视频。

```javascript
layer.open({
    type: 2,
    title: '选择视频',
    area: ['80%', '70%'],
    content: '/filemanager/media-manager.html?multi=false&type=video'
});

// 监听返回的消息
window.addEventListener('message', function(event) {
    if (event.data && event.data.type === 'mediaSelected') {
        const videoPath = event.data.files[0];
        console.log('已选择的视频:', videoPath);
        // document.getElementById('videoSource').src = videoPath;
    }
});
```

---

## 4. 返回数据格式

媒体管理器通过 `window.postMessage` 发送一个对象给父窗口。

```javascript
{
    type: 'mediaSelected',      // 消息类型
    mediaType: 'image' or 'video', // 选择的媒体类型
    files: ['/path/to/file1.jpg', '/path/to/file2.png'], // 包含一个或多个文件路径的数组
    multi: true or false        // 调用时传入的 multi 参数
}
```

---

## 5. 键盘快捷键

- `Ctrl + 点击` 或 `Cmd + 点击`: 多选
- `双击`: 预览文件
- `Esc`: 取消所有选择
- `Enter`: 确认选择（需至少选择一个文件）
- `Delete`: 删除选中的文件
- `F2`: 重命名选中的文件（仅限单选时）

---

请遵循以上规则调用，以确保媒体管理器正常工作。 