#!/bin/bash

# MacCMS 网站写入锁定/解锁脚本
# 锁定MacCMS常见目录，但不锁定缓存和上传目录

set -e

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 获取脚本所在目录
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DATA_DIR="$(dirname "$SCRIPT_DIR")/data"

# 需要锁定的目录（相对路径）
LOCK_DIRS=(
    "application"
    "thinkphp"
    "template"
    "public"
    "extend"
    "vendor"
)

# 需要锁定的文件
LOCK_FILES=(
    "api.php"
    "install.php"
    "index.php"
    "*.php"
)

# 不锁定的目录（缓存和上传相关）
EXCLUDE_DIRS=(
    "runtime"
    "upload"
    "uploads"
    "static/upload"
    "public/upload"
    "public/uploads"
    "public/static/upload"
)

usage() {
    echo "用法: $0 [lock|unlock]"
    echo "  lock   - 锁定网站写入权限"
    echo "  unlock - 解锁网站写入权限"
    exit 1
}

# 检查参数
if [ $# -ne 1 ]; then
    usage
fi

ACTION="$1"

if [ "$ACTION" != "lock" ] && [ "$ACTION" != "unlock" ]; then
    usage
fi

echo -e "${BLUE}MacCMS 网站权限管理${NC}"
echo ""

# 检查site.txt是否存在
if [ ! -f "$DATA_DIR/site.txt" ]; then
    echo -e "${RED}错误: 未找到站点列表文件${NC}"
    exit 1
fi

# 检查是否有站点
if [ ! -s "$DATA_DIR/site.txt" ]; then
    echo -e "${YELLOW}站点列表为空${NC}"
    exit 0
fi

echo -e "${GREEN}站点列表:${NC}"
cat "$DATA_DIR/site.txt" | nl -w2 -s'. '
echo ""

echo -n "请选择要操作的站点 (输入数字，多个用空格分隔，或输入 'all' 选择全部): "
read -r selection

# 获取选中的站点
selected_sites=()

if [ "$selection" = "all" ]; then
    while IFS= read -r site; do
        if [ -n "$site" ]; then
            selected_sites+=("$site")
        fi
    done < "$DATA_DIR/site.txt"
else
    # 解析数字选择
    for num in $selection; do
        if [[ "$num" =~ ^[0-9]+$ ]]; then
            site=$(sed -n "${num}p" "$DATA_DIR/site.txt")
            if [ -n "$site" ]; then
                selected_sites+=("$site")
            fi
        fi
    done
fi

if [ ${#selected_sites[@]} -eq 0 ]; then
    echo -e "${RED}未选择任何有效站点${NC}"
    exit 1
fi

echo ""
echo -e "${YELLOW}选中的站点:${NC}"
for site in "${selected_sites[@]}"; do
    echo "  $site"
done
echo ""

# 执行锁定或解锁操作
for site in "${selected_sites[@]}"; do
    if [ ! -d "$site" ]; then
        echo -e "${RED}站点目录不存在: $site${NC}"
        continue
    fi
    
    echo -e "${YELLOW}处理站点: $site${NC}"
    
    if [ "$ACTION" = "lock" ]; then
        echo -e "${YELLOW}  正在锁定写入权限...${NC}"
        
        # 锁定指定目录
        for dir in "${LOCK_DIRS[@]}"; do
            target_dir="$site/$dir"
            if [ -d "$target_dir" ]; then
                chmod -R a-w "$target_dir" 2>/dev/null || echo -e "${RED}    无法锁定: $target_dir${NC}"
                echo -e "${GREEN}    已锁定: $dir${NC}"
            fi
        done
        
        # 锁定根目录的重要文件
        for file_pattern in "${LOCK_FILES[@]}"; do
            find "$site" -maxdepth 1 -name "$file_pattern" -type f -exec chmod a-w {} \; 2>/dev/null
        done
        
        # 确保排除目录保持可写
        for exclude_dir in "${EXCLUDE_DIRS[@]}"; do
            target_dir="$site/$exclude_dir"
            if [ -d "$target_dir" ]; then
                chmod -R u+w "$target_dir" 2>/dev/null || true
                echo -e "${BLUE}    保持可写: $exclude_dir${NC}"
            fi
        done
        
    else # unlock
        echo -e "${YELLOW}  正在解锁写入权限...${NC}"
        
        # 解锁所有目录和文件
        chmod -R u+w "$site" 2>/dev/null || echo -e "${RED}    部分文件解锁失败${NC}"
        echo -e "${GREEN}    已解锁所有文件${NC}"
    fi
    
    echo ""
done

if [ "$ACTION" = "lock" ]; then
    echo -e "${GREEN}网站写入权限锁定完成！${NC}"
    echo -e "${YELLOW}注意: 缓存和上传目录保持可写状态${NC}"
else
    echo -e "${GREEN}网站写入权限解锁完成！${NC}"
fi

echo ""