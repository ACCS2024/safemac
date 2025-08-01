#!/bin/bash

# JavaScript 病毒检测脚本
# 检查JS文件中的病毒特征

# 移除set -e，防止脚本过早终止
# set -e

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 获取脚本所在目录
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DATA_DIR="$(dirname "$(dirname "$SCRIPT_DIR")")/data"

# 创建日志目录（使用时间戳）
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
LOG_DIR="$(dirname "$(dirname "$SCRIPT_DIR")")/log/$TIMESTAMP"
mkdir -p "$LOG_DIR"

echo -e "${BLUE}[病毒检查] JavaScript 病毒特征检查${NC}"
echo -e "${BLUE}日志目录: $LOG_DIR${NC}"
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

    # 创建站点日志目录
    site_name=$(basename "$site")
    SITE_LOG_DIR="$LOG_DIR/$site_name"
    mkdir -p "$SITE_LOG_DIR"

    # 为每个规则创建空日志文件
    for pattern_name in "${!virus_patterns[@]}"; do
        : > "$SITE_LOG_DIR/${pattern_name}.txt"
    done

    # 分别查找所有JS文件和template目录中的HTML文件
    js_files=$(find "$site" -name "*.js" -type f 2>/dev/null || echo "")
    html_files=$(find "$site" -path "*/template/*.html" -type f 2>/dev/null || echo "")

    # 合并文件列表
    all_files=$(echo -e "$js_files\n$html_files" | grep -v '^$' || echo "")

    if [ -z "$all_files" ]; then
        echo -e "${GREEN}未找到JS文件或template下的HTML文件${NC}"
        echo ""
        continue
    fi

    suspicious_files=0

    # 使用更可靠的方式遍历文件列表
    echo "$all_files" | while read -r file; do
        if [ -z "$file" ] || [ ! -f "$file" ]; then
            continue
        fi

        declare -A pattern_hits
        total_hits=0
        has_hit=false

        # 检查每个病毒特征
        for pattern_name in "${!virus_patterns[@]}"; do
            pattern="${virus_patterns[$pattern_name]}"

            if [[ "$pattern_name" == "hex_string" ]]; then
                hits=$(grep -o '\\\x[0-9a-fA-F][0-9a-fA-F]' "$file" 2>/dev/null | wc -l || echo "0")
            else
                hits=$(grep -c "$pattern" "$file" 2>/dev/null || echo "0")
            fi
            hits=$(echo "$hits" | tr -d '\n' | tr -d '\r')

            # 保存所有命中结果，无论是否为0
            pattern_hits["$pattern_name"]=$hits

            # 如果命中次数大于0，记录到对应规则的日志文件
            if [ "$hits" -gt 0 ]; then
                has_hit=true
                ((total_hits += hits))

                # 将命中的文件和次数写入对应规则的日志文件（格式：次数 文件名）
                echo "$hits $(basename "$file"): $file" >> "$SITE_LOG_DIR/${pattern_name}.txt"
            fi
        done

        # 任一条件命中就输出
        if [ "$has_hit" = true ]; then
            echo ""
            echo -e "${RED}可疑文件: $file${NC}"
            for pattern_name in "${!virus_patterns[@]}"; do
                hits=${pattern_hits["$pattern_name"]}
                if [ -z "$hits" ]; then
                    hits=0
                fi
                echo -e "${YELLOW}  可疑特征 $pattern_name: $hits 次${NC}"
            done
            ((suspicious_files++))
        fi

    done

    # 对每个规则日志文件进行排序（按数字倒序）
    for pattern_name in "${!virus_patterns[@]}"; do
        if [ -s "$SITE_LOG_DIR/${pattern_name}.txt" ]; then
            # 排序文件内容（按数字倒序）
            sort -nr "$SITE_LOG_DIR/${pattern_name}.txt" -o "$SITE_LOG_DIR/${pattern_name}.txt"
        else
            # 如果文件为空，则删除
            rm "$SITE_LOG_DIR/${pattern_name}.txt"
        fi
    done

    if [ $suspicious_files -eq 0 ]; then
        echo -e "${GREEN}未发现可疑JS/HTML文件${NC}"
    else
        echo ""
        echo -e "${RED}在该站点发现 $suspicious_files 个可疑JS/HTML文件${NC}"
    fi

    echo ""

done < "$DATA_DIR/site.txt"

echo -e "${GREEN}JavaScript 病毒特征检查完成${NC}"
echo -e "${GREEN}日志已保存到: $LOG_DIR${NC}"
