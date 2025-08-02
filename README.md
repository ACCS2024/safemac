# MacCMS 文件检查系统 使用文档

## 系统简介

MacCMS 文件检查系统是一个简单、安全、用户友好的工具，专门用于检查和保护 MacCMS 网站文件。系统提供自动站点发现、病毒检测、文件保护等功能。

## 功能特性

- ✅ **自动站点发现**: 扫描常见web服务器路径，自动发现MacCMS安装
- ✅ **多种病毒检测**: 检测PHP和JavaScript中的常见病毒特征
- ✅ **安全隔离**: 发现病毒文件时安全移动到隔离区域
- ✅ **文件保护**: 锁定重要文件防止恶意修改
- ✅ **交互式界面**: 友好的命令行菜单系统
- ✅ **备份恢复**: 所有操作都有备份，支持安全恢复

## 系统结构

```
safemac/
├── main.sh              # 主程序入口
├── data/                # 数据目录
│   └── site.txt         # 站点列表文件
├── tool/                # 工具脚本
│   └── site.sh          # 站点发现脚本
├── safe/                # 安全检查模块
│   ├── main.sh          # 病毒检查协调器
│   ├── lock.sh          # 文件锁定/解锁脚本
│   └── script/          # 病毒检测脚本
│       ├── php-active.sh    # PHP active/system病毒检测
│       ├── php-addones.sh   # PHP addons劫持检测
│       └── js-main.sh       # JavaScript病毒检测
├── README.md            # 本文档
└── 任务列表.md          # 任务需求文档
```

## 安装和使用

### 1. 从GitHub获取代码

```bash
# 克隆仓库
git clone https://github.com/ACCS2024/safemac.git
cd safemac

# 给脚本添加执行权限
chmod +x main.sh
chmod +x tool/site.sh
chmod +x safe/*.sh
chmod +x safe/script/*.sh
```

### 2. 运行系统

```bash
# 启动主程序
./main.sh
```

### 3. 首次运行

首次运行时，系统会自动扫描以下路径寻找MacCMS安装：
- `/home/www/wwwroot/`
- `/www/wwwroot/`
- `/home/wwwroot/`
- `/home/www/`

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
- 扫描所有 `.js` 文件
- 检测多种病毒特征：
  - `navigator.platform`
  - `base64` 编码
  - 十六进制编码字符串 (`\x68\x74...`)
  - `appendChild` DOM操作
  - `Mac|Win` 平台检测

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

# 重新添加执行权限
chmod +x main.sh tool/site.sh safe/*.sh safe/script/*.sh
```

### 自定义配置

#### 添加检测路径
编辑 `tool/site.sh`，在 `COMMON_PATHS` 数组中添加新路径：
```bash
COMMON_PATHS=(
    "/home/www/wwwroot"
    "/www/wwwroot"
    "/home/wwwroot"
    "/home/www"
    "/home/"  # 添加自定义路径
)
```

#### 修改病毒特征
编辑对应的检测脚本，添加新的病毒特征模式。

## 故障排除

### 常见问题

**1. 权限不足**
```bash
# 确保脚本有执行权限
chmod +x main.sh
chmod +x tool/site.sh
chmod +x safe/*.sh
chmod +x safe/script/*.sh
```

**2. 未发现站点**
- 检查MacCMS是否安装在标准路径
- 手动编辑 `data/site.txt` 添加站点路径
- 确保站点目录包含MacCMS特征文件

**3. 病毒检测误报**
- 仔细检查报告的可疑文件
- 根据实际情况选择是否处理
- 保留备份文件以便恢复

**4. 文件锁定后网站异常**
```bash
# 立即解锁所有文件
./main.sh
# 选择选项4解锁
```

### 技术支持

如遇到问题，请提供以下信息：
- 操作系统版本
- Web服务器类型（Apache/Nginx等）
- MacCMS版本
- 错误信息截图
- 相关日志内容

## 安全建议

1. **定期备份**: 运行检查前务必备份整个网站
2. **测试环境**: 先在测试环境验证脚本功能
3. **权限管理**: 仅在必要时运行，避免滥用管理权限
4. **监控日志**: 定期检查系统和访问日志
5. **及时更新**: 保持脚本和MacCMS系统最新版本

---

**版本**: v1.0  
**更新日期**: 2024年  
**维护**: ACCS2024/safemac 项目组