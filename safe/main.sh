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

# 显示病毒检查菜单
show_virus_menu() {
    echo -e "${GREEN}请选择病毒检查类型:${NC}"
    echo "1. PHP活跃病毒检查 (检查PHP文件中的恶意代码)"
    echo "2. PHP插件病毒检查 (检查PHP插件和模板中的病毒)"
    echo "3. JavaScript病毒检查 (检查JS和HTML文件中的可疑代码)"
    echo "0. 返回上级菜单"
    echo ""
    echo -n "请输入选项 [0-3]: "
}

# 执行指定的病毒检查脚本
run_virus_script() {
    local script_name="$1"
    local script_path="$SCRIPT_DIR_VIRUS/$script_name"

    if [ -f "$script_path" ]; then
        echo -e "${YELLOW}开始执行: $script_name${NC}"
        echo ""
        chmod +x "$script_path"
        bash "$script_path"
        echo ""
        echo -e "${GREEN}$script_name 执行完成！${NC}"
        echo ""
        echo -n "按回车键继续..."
        read
    else
        echo -e "${RED}错误: 病毒检查脚本不存在: $script_path${NC}"
        echo ""
    fi
}

# 病毒检查菜单循环
while true; do
    show_virus_menu
    read -r choice
    echo ""

    case $choice in
        1)
            run_virus_script "php-active.sh"
            ;;
        2)
            run_virus_script "php-addones.sh"
            ;;
        3)
            run_virus_script "js-main.sh"
            ;;
        0)
            echo -e "${GREEN}返回主菜单${NC}"
            exit 0
            ;;
        *)
            echo -e "${RED}无效选项，请重新选择。${NC}"
            echo ""
            ;;
    esac
done
