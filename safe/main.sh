#!/bin/bash

# MacCMS 病毒检查主程序
# 协调各种病毒查杀修复脚本

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
SCRIPT_DIR_VIRUS="$SCRIPT_DIR/script"

echo -e "${BLUE}=====================================${NC}"
echo -e "${BLUE}      MacCMS 病毒检查系统${NC}"
echo -e "${BLUE}=====================================${NC}"
echo ""

# 检查site.txt是否存在
if [ ! -f "$DATA_DIR/site.txt" ]; then
    echo -e "${RED}错误: 未找到站点列表文件 $DATA_DIR/site.txt${NC}"
    echo -e "${YELLOW}请先运行主菜单选项1更新站点列表${NC}"
    exit 1
fi

# 检查是否有站点
if [ ! -s "$DATA_DIR/site.txt" ]; then
    echo -e "${YELLOW}站点列表为空，无需检查${NC}"
    exit 0
fi

echo -e "${GREEN}读取到以下站点:${NC}"
cat "$DATA_DIR/site.txt" | nl -w2 -s'. '
echo ""

# 病毒检查脚本列表
VIRUS_SCRIPTS=(
    "php-active.sh"
    "php-addones.sh"
    "js-main.sh"
)

# 运行所有病毒检查脚本
for script in "${VIRUS_SCRIPTS[@]}"; do
    script_path="$SCRIPT_DIR_VIRUS/$script"
    
    if [ -f "$script_path" ]; then
        echo -e "${YELLOW}运行检查脚本: $script${NC}"
        chmod +x "$script_path"
        bash "$script_path"
        echo ""
    else
        echo -e "${RED}警告: 病毒检查脚本不存在: $script_path${NC}"
        echo ""
    fi
done

echo -e "${GREEN}所有病毒检查脚本执行完成！${NC}"
echo ""