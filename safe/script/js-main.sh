#!/bin/bash

# JavaScript 病毒检测脚本
# 检查JS文件中的病毒特征

set -e

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 获取脚本所在目录
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DATA_DIR="$(dirname "$(dirname "$SCRIPT_DIR")")/data"

echo -e "${BLUE}[病毒检查] JavaScript 病毒特征检查${NC}"
echo ""

# 检查site.txt是否存在
if [ ! -f "$DATA_DIR/site.txt" ]; then
    echo -e "${RED}错误: 未找到站点列表文件${NC}"
    exit 1
fi

# 病毒特征列表
declare -A virus_patterns=(
    ["navigator.platform"]="navigator.platform"
    ["base64"]="base64"
    ["hex_string"]='\\\x[0-9a-fA-F]{2}'
    ["appendChild"]="appendChild"
    ["Mac|Win"]="Mac\|Win"
)

echo -e "${YELLOW}由于病毒变种很多，只输出可疑特征${NC}"
echo ""

# 遍历每个站点
while IFS= read -r site; do
    if [ -z "$site" ]; then
        continue
    fi
    
    echo -e "${YELLOW}检查站点: $site${NC}"
    
    # 查找所有JS文件
    js_files=$(find "$site" -name "*.js" -type f 2>/dev/null || true)
    
    if [ -z "$js_files" ]; then
        echo -e "${GREEN}未找到JS文件${NC}"
        echo ""
        continue
    fi
    
    suspicious_files=0
    
    # 检查每个JS文件
    while IFS= read -r js_file; do
        if [ -z "$js_file" ]; then
            continue
        fi
        
        declare -A pattern_hits
        total_hits=0
        
        # 检查每个病毒特征
        for pattern_name in "${!virus_patterns[@]}"; do
            pattern="${virus_patterns[$pattern_name]}"
            
            # 使用grep检查模式，忽略错误
            if [[ "$pattern_name" == "hex_string" ]]; then
                # 对于十六进制字符串，使用特殊检查（简化版）
                hits=$(grep -o '\\\x[0-9a-fA-F][0-9a-fA-F]' "$js_file" 2>/dev/null | wc -l || echo "0")
            else
                hits=$(grep -c "$pattern" "$js_file" 2>/dev/null || echo "0")
            fi
            # 修复：去除换行符和回车符，保证hits为纯数字
            hits=$(echo "$hits" | tr -d '\n' | tr -d '\r')

            if [ "$hits" -gt 0 ]; then
                pattern_hits["$pattern_name"]=$hits
                ((total_hits += hits))
            fi
        done
        
        # 如果发现可疑特征，输出报告
        if [ $total_hits -gt 0 ]; then
            echo ""
            echo -e "${RED}可疑文件: $js_file${NC}"
            
            for pattern_name in "${!pattern_hits[@]}"; do
                hits=${pattern_hits[$pattern_name]}
                echo -e "${YELLOW}  可疑特征 $pattern_name: $hits 次${NC}"
            done
            
            ((suspicious_files++))
        fi
        
    done <<< "$js_files"
    
    if [ $suspicious_files -eq 0 ]; then
        echo -e "${GREEN}未发现可疑JS文件${NC}"
    else
        echo ""
        echo -e "${RED}在该站点发现 $suspicious_files 个可疑JS文件${NC}"
    fi
    
    echo ""
    
done < "$DATA_DIR/site.txt"

echo -e "${GREEN}JavaScript 病毒特征检查完成${NC}"