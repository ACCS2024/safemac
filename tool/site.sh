#!/bin/bash

# MacCMS 站点检测脚本
# 扫描常见宝塔路径，检测MacCMS特征

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

# 确保data目录存在
mkdir -p "$DATA_DIR"

# 常见宝塔路径
COMMON_PATHS=(
    "/home/www/wwwroot"
    "/www/wwwroot"
    "/home/wwwroot"
    "/home/www"
)

# MacCMS特征目录
MACCMS_DIRS=(
    "application"
    "runtime"
    "thinkphp"
    "template"
)

# MacCMS特征文件
MACCMS_FILES=(
    "api.php"
    "install.php"
)

echo -e "${BLUE}开始扫描MacCMS站点...${NC}"
echo ""

# 清空或创建site.txt
> "$DATA_DIR/site.txt"

found_sites=0

# 检查单个目录是否为MacCMS
check_maccms() {
    local dir="$1"
    local score=0
    
    # 检查特征目录
    for feature_dir in "${MACCMS_DIRS[@]}"; do
        if [ -d "$dir/$feature_dir" ]; then
            ((score++))
        fi
    done
    
    # 检查特征文件
    for feature_file in "${MACCMS_FILES[@]}"; do
        if [ -f "$dir/$feature_file" ]; then
            ((score++))
        fi
    done
    
    # 如果至少匹配3个特征，认为是MacCMS
    if [ $score -ge 3 ]; then
        return 0
    else
        return 1
    fi
}

# 遍历常见路径
for base_path in "${COMMON_PATHS[@]}"; do
    if [ -d "$base_path" ]; then
        echo -e "${YELLOW}扫描路径: $base_path${NC}"
        
        # 列出所有子目录
        find "$base_path" -maxdepth 1 -type d ! -path "$base_path" 2>/dev/null | while read -r site_dir; do
            if check_maccms "$site_dir"; then
                echo -e "${GREEN}发现MacCMS站点: $site_dir${NC}"
                echo "$site_dir" >> "$DATA_DIR/site.txt"
                ((found_sites++))
            fi
        done
    else
        echo -e "${YELLOW}路径不存在，跳过: $base_path${NC}"
    fi
done

# 读取找到的站点数量
if [ -f "$DATA_DIR/site.txt" ]; then
    found_sites=$(wc -l < "$DATA_DIR/site.txt")
fi

echo ""
if [ $found_sites -gt 0 ]; then
    echo -e "${GREEN}扫描完成！共发现 $found_sites 个MacCMS站点${NC}"
    echo ""
    echo -e "${YELLOW}发现的站点列表:${NC}"
    cat "$DATA_DIR/site.txt" | nl -w2 -s'. '
    echo ""
    echo -e "${YELLOW}警告: 请检查是否是正确的网站目录，已写入到 $DATA_DIR/site.txt${NC}"
    echo -e "${YELLOW}你可以直接编辑 $DATA_DIR/site.txt 删掉不需要检查的目录${NC}"
else
    echo -e "${RED}未发现任何MacCMS站点${NC}"
    echo -e "${YELLOW}如果确实存在MacCMS站点，请手动添加到 $DATA_DIR/site.txt${NC}"
    # 创建空文件以便主脚本继续运行
    touch "$DATA_DIR/site.txt"
fi

echo ""