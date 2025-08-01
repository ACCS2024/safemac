#!/bin/bash

# MacCMS File Checking System
# 简单、安全、用户友好的MacCMS文件检查系统

set -e

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 获取脚本所在目录
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DATA_DIR="$SCRIPT_DIR/data"
TOOL_DIR="$SCRIPT_DIR/tool"
SAFE_DIR="$SCRIPT_DIR/safe"

echo -e "${BLUE}=====================================${NC}"
echo -e "${BLUE}    MacCMS 文件检查系统 v1.0${NC}"
echo -e "${BLUE}=====================================${NC}"
echo ""

# 检查是否存在data/site.txt
if [ ! -f "$DATA_DIR/site.txt" ]; then
    echo -e "${YELLOW}未发现站点列表文件，开始扫描MacCMS站点...${NC}"
    echo ""
    
    # 确保工具脚本存在且可执行
    if [ ! -f "$TOOL_DIR/site.sh" ]; then
        echo -e "${RED}错误: $TOOL_DIR/site.sh 不存在！${NC}"
        exit 1
    fi
    
    chmod +x "$TOOL_DIR/site.sh"
    bash "$TOOL_DIR/site.sh"
    
    if [ ! -f "$DATA_DIR/site.txt" ]; then
        echo -e "${RED}站点扫描失败，程序退出。${NC}"
        exit 1
    fi
    echo ""
fi

# 显示菜单
show_menu() {
    echo -e "${GREEN}请选择操作:${NC}"
    echo "1. 更新站点列表"
    echo "2. 运行病毒检查"
    echo "3. 锁定网站写入"
    echo "4. 解锁网站写入"
    echo "0. 退出"
    echo ""
    echo -n "请输入选项 [0-4]: "
}

# 主循环
while true; do
    show_menu
    read -r choice
    echo ""
    
    case $choice in
        1)
            echo -e "${YELLOW}正在更新站点列表...${NC}"
            chmod +x "$TOOL_DIR/site.sh"
            bash "$TOOL_DIR/site.sh"
            echo ""
            ;;
        2)
            echo -e "${RED}警告！危险操作！请备份您的网站文件后再操作。${NC}"
            echo -e "${YELLOW}如果想打断运行，请按下 Ctrl+C${NC}"
            echo ""
            echo -n "确认继续吗？(y/N): "
            read -r confirm
            if [[ $confirm =~ ^[Yy]$ ]]; then
                echo -e "${YELLOW}开始运行病毒检查...${NC}"
                if [ -f "$SAFE_DIR/main.sh" ]; then
                    chmod +x "$SAFE_DIR/main.sh"
                    bash "$SAFE_DIR/main.sh"
                else
                    echo -e "${RED}错误: $SAFE_DIR/main.sh 不存在！${NC}"
                fi
            else
                echo -e "${GREEN}已取消操作。${NC}"
            fi
            echo ""
            ;;
        3)
            echo -e "${YELLOW}正在锁定网站写入权限...${NC}"
            if [ -f "$SAFE_DIR/lock.sh" ]; then
                chmod +x "$SAFE_DIR/lock.sh"
                bash "$SAFE_DIR/lock.sh" lock
            else
                echo -e "${RED}错误: $SAFE_DIR/lock.sh 不存在！${NC}"
            fi
            echo ""
            ;;
        4)
            echo -e "${YELLOW}正在解锁网站写入权限...${NC}"
            if [ -f "$SAFE_DIR/lock.sh" ]; then
                chmod +x "$SAFE_DIR/lock.sh"
                bash "$SAFE_DIR/lock.sh" unlock
            else
                echo -e "${RED}错误: $SAFE_DIR/lock.sh 不存在！${NC}"
            fi
            echo ""
            ;;
        0)
            echo -e "${GREEN}感谢使用 MacCMS 文件检查系统！${NC}"
            exit 0
            ;;
        *)
            echo -e "${RED}无效选项，请重新选择。${NC}"
            echo ""
            ;;
    esac
done