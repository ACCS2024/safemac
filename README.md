# MacCMS 文件检查系统 使用文档

## 系统简介

MacCMS 文件检查系统是一个简单、安全、用户友好的工具，专门用于检查和保护 MacCMS 网站文件。系统提供自动站点发现、病毒检测、文件保护等功能。**现已升级为Python版本，开箱即用，无需安装第三方库！**

## 功能特性

- ✅ **自动站点发现**: 扫描常见web服务器路径，自动发现MacCMS安装
- ✅ **多种病毒检测**: 检测PHP和JavaScript中的常见病毒特征
- ✅ **安全隔离**: 发现病毒文件时安全移动到隔离区域
- ✅ **文件保护**: 锁定重要文件防止恶意修改
- ✅ **交互式界面**: 友好的命令行菜单系统
- ✅ **备份恢复**: 所有操作都有备份，支持安全恢复
- 🆕 **Python版本**: 纯Python实现，无需shell环境，跨平台兼容
- 🆕 **开箱即用**: 仅使用Python标准库，无需安装第三方依赖

## 系统结构

```
safemac/
├── main.py              # 主程序入口（Python版本）
├── utils.py             # 通用工具模块
├── site_scanner.py      # 站点发现模块
├── virus_checker.py     # 病毒检测模块
├── file_locker.py       # 文件锁定模块
├── data/                # 数据目录
│   └── site.txt         # 站点列表文件
├── log/                 # 日志目录（自动创建）
├── demo/                # 测试数据目录
├── README.md            # 本文档
└── .gitignore           # Git忽略文件
```

## 安装和使用

### 1. 从GitHub获取代码

```bash
# 克隆仓库
rm -rf  safemac  # 清理旧版本
git clone https://github.com/ACCS2024/safemac.git
cd safemac
```

### 2. 系统要求

- **Python 3.6+**: 系统需要Python 3.6或更高版本
- **Linux系统**: 文件锁定功能需要支持`chattr`命令的Linux系统
- **管理员权限**: 某些操作（如文件锁定）需要root权限

### 3. 运行系统

```bash
# 启动主程序
python3 main.py

# 或者直接执行（如果有执行权限）
./main.py
```

### 4. 首次运行

首次运行时，系统会自动扫描以下路径寻找MacCMS安装：
- `/home/www/wwwroot/`
- `/www/wwwroot/`
- `/home/wwwroot/`
- `/home/www/`
- `/var/www/html/` (Apache默认路径)
- `/usr/share/nginx/html/` (Nginx默认路径)
- `/opt/lampp/htdocs/` (XAMPP路径)

系统通过以下特征识别MacCMS：
- **目录特征**: `application/`, `runtime/`, `thinkphp/`, `template/`
- **文件特征**: `api.php`, `install.php`

### 4. 主菜单功能

#### 选项1: 更新站点列表
- 重新扫描系统中的MacCMS站点
- 更新 `data/site.txt` 文件
- 可手动编辑此文件删除不需要检查的站点

#### 选项2: 运行病毒检查
执行完整的病毒扫描，包括：

**PHP Active/System病毒检测**
- 检查 `application/extra/active.php`
- 检查 `application/extra/system.php`
- 发现时可选择安全隔离

**PHP Addons劫持检测**
- 检查 `application/extra/addons.php`
- 检测是否包含恶意ThinkPHP代码
- 可选择用干净文件覆盖

**JavaScript病毒检测**
- 扫描所有 `.js` 文件和template目录下的 `.html` 文件
- 检测多种病毒特征：
  - `navigator.platform`
  - `base64` 编码
  - 十六进制编码字符串 (`\x68\x74...`)
  - `appendChild` DOM操作
  - `Mac|Win` 平台检测
- 生成详细的分析日志

#### 选项3: 锁定网站写入
保护重要文件不被恶意修改：
- 锁定 `application/`、`thinkphp/`、`template/` 等核心目录
- 锁定 `api.php`、`install.php`、`index.php` 等关键文件
- **保持可写**: `runtime/`、`upload/`、`uploads/` 等缓存和上传目录

#### 选项4: 解锁网站写入
恢复所有文件的写入权限

## 安全特性

### 1. 文件备份机制
- 所有危险操作都会先备份原文件
- 备份文件使用 `.lock` 扩展名
- 提供详细的恢复说明

### 2. 用户确认机制
- 所有危险操作都需要用户确认
- 提供清晰的警告信息
- 支持 Ctrl+C 中断操作

### 3. 最小权限原则
- 只修改必要的文件
- 保护缓存和上传功能正常运行
- 提供精确的权限控制

## 日志和监控

### 查看系统状态
```bash
# 查看当前站点列表
cat data/site.txt

# 查看备份文件
find . -name "*.lock" -type f

# 查看病毒检测日志
ls -la log/
```

### JavaScript病毒检测日志
系统会为每次JavaScript病毒检测创建时间戳目录：
```
log/
└── 20241201_143022/          # 检测时间戳
    └── demo/                 # 站点名称
        ├── base64.txt        # base64特征检测结果
        ├── appendChild.txt   # appendChild特征检测结果
        └── hex_string.txt    # 十六进制字符串检测结果
```

### 恢复备份文件
如果需要恢复被隔离的文件：
```bash
# 恢复active.php
mv /path/to/active.lock /path/to/active.php

# 恢复addons.php
rm /path/to/addons.php
mv /path/to/addons.lock /path/to/addons.php
```

## 更新和维护

### 同步最新版本
```bash
# 在safemac目录中执行
git pull origin main
```

### 自定义配置

#### 添加检测路径
编辑 `site_scanner.py`，在 `common_paths` 列表中添加新路径：
```python
self.common_paths = [
    "/home/www/wwwroot",
    "/www/wwwroot",
    "/home/wwwroot",
    "/home/www",
    "/var/www/html",
    "/usr/share/nginx/html",
    "/opt/lampp/htdocs",
    "/home",
    "/your/custom/path"  # 添加自定义路径
]
```

#### 修改病毒特征
编辑 `virus_checker.py`，在 `js_virus_patterns` 字典中添加新的病毒特征模式：
```python
self.js_virus_patterns = {
    'navigator.platform': r'navigator\.platform',
    'base64': r'base64',
    'hex_string': r'\\\\x[0-9a-fA-F]{2}',
    'appendChild': r'appendChild', 
    'Mac|Win': r'Mac\|Win',
    'custom_pattern': r'your_regex_pattern'  # 添加自定义特征
}
```

## 故障排除

### 常见问题

**1. Python版本问题**
```bash
# 检查Python版本
python3 --version

# 如果系统只有python命令
python --version
```

**2. 权限不足**
```bash
# 确保脚本有执行权限
chmod +x main.py

# 文件锁定功能需要root权限
sudo python3 main.py
```

**3. 未发现站点**
- 检查MacCMS是否安装在标准路径
- 手动编辑 `data/site.txt` 添加站点路径
- 确保站点目录包含MacCMS特征文件

**4. 病毒检测误报**
- 仔细检查报告的可疑文件
- 根据实际情况选择是否处理
- 保留备份文件以便恢复

**5. 文件锁定后网站异常**
```bash
# 立即解锁所有文件
python3 main.py
# 选择选项4解锁
```

**6. chattr命令不支持**
- 文件锁定功能需要支持ext2/ext3/ext4文件系统
- 某些云服务器或容器环境可能不支持
- 可以跳过文件锁定功能，仅使用病毒检测

### 技术支持

如遇到问题，请提供以下信息：
- 操作系统版本和Python版本
- Web服务器类型（Apache/Nginx等）
- MacCMS版本
- 错误信息截图或完整错误信息
- 相关日志内容

## 版本更新日志

### v2.0 (Python版本)
- 🆕 完全重写为Python版本
- 🆕 使用纯Python标准库，无第三方依赖
- 🆕 跨平台兼容性改进
- 🆕 增强的日志记录功能
- 🆕 更好的错误处理和用户交互
- 🆕 代码模块化，易于维护和扩展

### v1.0 (Shell版本)
- ✅ 基础的MacCMS站点发现
- ✅ PHP和JavaScript病毒检测
- ✅ 文件锁定保护功能
- ✅ 交互式菜单系统

## 安全建议

1. **定期备份**: 运行检查前务必备份整个网站
2. **测试环境**: 先在测试环境验证脚本功能
3. **权限管理**: 仅在必要时运行，避免滥用管理权限
4. **监控日志**: 定期检查系统和访问日志
5. **及时更新**: 保持脚本和MacCMS系统最新版本

---

**版本**: v2.0 (Python版本)  
**更新日期**: 2024年  
**维护**: ACCS2024/safemac 项目组

## 从Shell版本迁移指南

如果您之前使用的是Shell版本(v1.0)，迁移到Python版本非常简单：

1. **备份现有配置**：
   ```bash
   # 备份站点列表（如果存在）
   cp data/site.txt data/site.txt.backup
   ```

2. **更新代码**：
   ```bash
   git pull origin main
   ```

3. **直接运行新版本**：
   ```bash
   python3 main.py
   ```

4. **功能对比**：
   - ✅ 所有原有功能完全保留
   - ✅ 用户界面和操作流程完全一致
   - ✅ 配置文件格式兼容
   - 🆕 增强的错误处理和日志记录
   - 🆕 更好的跨平台兼容性

**注意**：Python版本完全兼容Shell版本的所有配置和数据文件，可以无缝迁移。