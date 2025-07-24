# PowerShell 兼容性指南

## 常见语法问题及解决方案

### 1. 命令连接符问题

**问题描述：**
PowerShell 不支持 Unix/Linux 风格的 `&&` 和 `||` 语法

**错误示例：**
```bash
# 这在 PowerShell 中会报错
cd backend && php run_migration_and_seed.php
```

**解决方案：**

#### 方案1：使用分号分隔
```powershell
cd backend; php run_migration_and_seed.php
```

#### 方案2：使用管道和条件执行
```powershell
cd backend; if ($?) { php run_migration_and_seed.php }
```

#### 方案3：使用 PowerShell 的 `-and` 操作符
```powershell
(cd backend) -and (php run_migration_and_seed.php)
```

#### 方案4：创建批处理脚本
创建 `.bat` 或 `.cmd` 文件：
```batch
@echo off
cd backend
php run_migration_and_seed.php
```

### 2. 环境变量语法

**Unix/Linux 风格：**
```bash
export PATH=$PATH:/usr/local/bin
```

**PowerShell 风格：**
```powershell
$env:PATH += ";C:\usr\local\bin"
```

### 3. 路径分隔符

**Unix/Linux 风格：**
```bash
path/to/file
```

**PowerShell 风格：**
```powershell
path\to\file
# 或者使用正斜杠（PowerShell 也支持）
path/to/file
```

### 4. 条件判断语法

**Unix/Linux 风格：**
```bash
if [ -f "file.txt" ]; then
    echo "文件存在"
fi
```

**PowerShell 风格：**
```powershell
if (Test-Path "file.txt") {
    Write-Host "文件存在"
}
```

## 项目开发建议

### 1. 创建跨平台脚本

为项目创建多个版本的脚本：

#### Windows 批处理文件 (.bat)
```batch
@echo off
cd backend
php run_migration_and_seed.php
```

#### Unix/Linux Shell 脚本 (.sh)
```bash
#!/bin/bash
cd backend && php run_migration_and_seed.php
```

#### PowerShell 脚本 (.ps1)
```powershell
Set-Location backend
php run_migration_and_seed.php
```

### 2. 使用 Node.js 脚本

创建 `package.json` 脚本，使用 Node.js 的跨平台能力：

```json
{
  "scripts": {
    "migrate": "node scripts/migrate.js",
    "seed": "node scripts/seed.js"
  }
}
```

### 3. 使用 Python 脚本

创建 Python 脚本处理跨平台命令：

```python
import os
import subprocess
import sys

def run_command(command):
    """跨平台执行命令"""
    try:
        result = subprocess.run(command, shell=True, check=True, capture_output=True, text=True)
        print(result.stdout)
        return True
    except subprocess.CalledProcessError as e:
        print(f"错误: {e}")
        print(f"错误输出: {e.stderr}")
        return False

def main():
    # 切换到 backend 目录
    os.chdir("backend")
    
    # 执行迁移和种子数据
    commands = [
        "php think migrate:run",
        "php think seed:run"
    ]
    
    for cmd in commands:
        if not run_command(cmd):
            sys.exit(1)

if __name__ == "__main__":
    main()
```

## 当前项目解决方案

### 1. 修改现有脚本

将 `run_migration_and_seed.php` 改为独立执行，或创建 PowerShell 版本。

### 2. 创建 PowerShell 脚本

创建 `run_migration_and_seed.ps1`：

```powershell
# 设置错误处理
$ErrorActionPreference = "Stop"

Write-Host "开始执行数据库迁移和种子数据插入..." -ForegroundColor Green

try {
    # 切换到 backend 目录
    Set-Location backend
    
    # 检查 PHP 是否可用
    if (-not (Get-Command php -ErrorAction SilentlyContinue)) {
        throw "PHP 未找到，请确保 PHP 已安装并添加到 PATH"
    }
    
    # 执行迁移
    Write-Host "执行数据库迁移..." -ForegroundColor Yellow
    php think migrate:run
    
    if ($LASTEXITCODE -ne 0) {
        throw "数据库迁移失败"
    }
    
    # 执行种子数据
    Write-Host "插入种子数据..." -ForegroundColor Yellow
    php think seed:run
    
    if ($LASTEXITCODE -ne 0) {
        throw "种子数据插入失败"
    }
    
    Write-Host "数据库迁移和种子数据插入完成！" -ForegroundColor Green
    
} catch {
    Write-Host "错误: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
} finally {
    # 返回原目录
    Set-Location ..
}
```

## 最佳实践

1. **始终考虑跨平台兼容性**
2. **为不同平台创建对应的脚本**
3. **使用高级语言（Python/Node.js）编写跨平台脚本**
4. **在文档中明确说明支持的平台和运行方式**
5. **测试所有平台上的脚本功能**

## 相关文件

- `backend/run_migration_and_seed.php` - PHP 脚本
- `backend/run_migration_and_seed.ps1` - PowerShell 脚本（建议创建）
- `backend/run_migration_and_seed.bat` - Windows 批处理（建议创建）
- `backend/run_migration_and_seed.sh` - Unix/Linux Shell 脚本（建议创建） 