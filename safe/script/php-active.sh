#!/bin/bash

# PHP Active 病毒检测脚本
# 检查 application/extra/active.php 和 application/extra/system.php

# set -e  # 移除此行，防止脚本过早退出

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 获取脚本所在目录
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DATA_DIR="$(dirname "$(dirname "$SCRIPT_DIR")")/data"

echo -e "${BLUE}[病毒检查] PHP Active/System 文件检查${NC}"
echo ""

# 检查site.txt是否存在
if [ ! -f "$DATA_DIR/site.txt" ]; then
    echo -e "${RED}错误: 未找到站点列表文件${NC}"
    exit 1
fi

# 遍历每个站点
while IFS= read -r site; do
    if [ -z "$site" ]; then
        continue
    fi
    
    echo -e "${YELLOW}检查站点: $site${NC}"
    
    # 检查的文件路径
    active_file="$site/application/extra/active.php"
    system_file="$site/application/extra/system.php"
    
    found_virus=false
    
    # 检查active.php
    if [ -f "$active_file" ]; then
        echo -e "${RED}命中病毒规则: system-active${NC}"
        echo -e "${RED}发现可疑文件: $active_file${NC}"
        found_virus=true
        
        echo -n "是否将此文件移动到安全位置？(y/N): "
        read -r confirm < /dev/tty
        if [[ $confirm =~ ^[Yy]$ ]]; then
            backup_file="${active_file%.php}.lock"
            mv "$active_file" "$backup_file"
            echo -e "${GREEN}文件已移动到: $backup_file${NC}"
            echo -e "${YELLOW}如果出现问题，可以将文件 $backup_file 重命名为 $active_file 还原${NC}"
        fi
        echo ""
    fi
    
    # 检查system.php
    if [ -f "$system_file" ]; then
        echo -e "${RED}命中病毒规则: system-active${NC}"
        echo -e "${RED}发现可疑文件: $system_file${NC}"
        found_virus=true
        
        echo -n "是否将此文件移动到安全位置？(y/N): "
        read -r confirm < /dev/tty
        if [[ $confirm =~ ^[Yy]$ ]]; then
            backup_file="${system_file%.php}.lock"
            mv "$system_file" "$backup_file"
            echo -e "${GREEN}文件已移动到: $backup_file${NC}"
            echo -e "${YELLOW}如果出现问题，可以将文件 $backup_file 重命名为 $system_file 还原${NC}"
        fi
        echo ""
    fi
    
    if [ "$found_virus" = false ]; then
        echo -e "${GREEN}未发现 active/system 病毒文件${NC}"
    fi
    
    echo ""
    
done < "$DATA_DIR/site.txt"

echo -e "${GREEN}PHP Active/System 文件检查完成${NC}"