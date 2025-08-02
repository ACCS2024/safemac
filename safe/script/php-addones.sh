#!/bin/bash

# PHP Addons 劫持检测脚本
# 检查 application/extra/addons.php 文件是否被劫持

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

echo -e "${BLUE}[病毒检查] PHP Addons 劫持检查${NC}"
echo ""

# 检查site.txt是否存在
if [ ! -f "$DATA_DIR/site.txt" ]; then
    echo -e "${RED}错误: 未找到站点列表文件${NC}"
    exit 1
fi

# 干净的addons.php内容
clean_addons_content='<?php

return array (
  '\''autoload'\'' => false,
  '\''hooks'\'' => 
  array (
  ),
  '\''route'\'' => 
  array (
  ),
);'

# 遍历每个站点
while IFS= read -r site; do

    if [ -z "$site" ]; then
        continue
    fi
    
    echo -e "${YELLOW}检查站点: $site${NC}"
    
    # 检查的文件路径
    addons_file="$site/application/extra/addons.php"
    addones_file="$site/application/extra/addones.php"  # 病毒样本文件名
    
    # 检查两种可能的文件名
    target_file=""
    if [ -f "$addons_file" ]; then
        target_file="$addons_file"
    elif [ -f "$addones_file" ]; then
        target_file="$addones_file"
    fi
    
    if [ -n "$target_file" ]; then
        # 检查文件内容是否包含ThinkPHP（可能的劫持特征）
        if grep -q "ThinkPHP" "$target_file" 2>/dev/null; then
            echo -e "${RED}命中病毒规则: addons劫持${NC}"
            echo -e "${RED}发现可疑文件: $target_file${NC}"
            echo ""
            
            echo -e "${YELLOW}注意: 如果覆盖，会导致插件被禁用，安装插件的用户勿用。${NC}"
            echo -n "是否用干净文件覆盖？(y/N): "
            read -r confirm

            if [[ $confirm =~ ^[Yy]$ ]]; then
                # 备份原文件
                backup_file="${target_file%.php}.lock"
                cp "$target_file" "$backup_file"
                echo -e "${GREEN}原文件已备份到: $backup_file${NC}"
                
                # 写入干净内容
                echo "$clean_addons_content" > "$target_file"
                echo -e "${GREEN}已用干净文件覆盖${NC}"
                echo -e "${YELLOW}如果出现问题，可以删除文件 $target_file，然后将 $backup_file 重命名为 $target_file 还原${NC}"
            fi
        else
            echo -e "${GREEN}addons.php 文件正常${NC}"
        fi
    else
        echo -e "${YELLOW}未找到 addons.php 或 addones.php 文件${NC}"
    fi
    
    echo ""
    
done < "$DATA_DIR/site.txt"

echo -e "${GREEN}PHP Addons 劫持检查完成${NC}"