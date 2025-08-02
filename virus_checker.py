# -*- coding: utf-8 -*-
#!/usr/bin/env python3
"""
MacCMS Virus Checker
Comprehensive virus detection for MacCMS installations
Combines PHP and JavaScript virus detection functionality
"""

import os
import re
import sys
from datetime import datetime
from pathlib import Path
from utils import (Colors, print_colored, print_header, confirm_action, 
                   get_script_dir, read_site_list, ensure_dir_exists, pause_for_user)


class MacCMSVirusChecker:
    """Comprehensive virus checker for MacCMS sites"""
    
    def __init__(self):
        self.script_dir = get_script_dir()
        self.data_dir = os.path.join(self.script_dir, "data")
        self.log_dir = os.path.join(self.script_dir, "log")
        
        # Clean content for addons.php
        self.clean_addons_content = '''<?php

return array (
  'autoload' => false,
  'hooks' => 
  array (
  ),
  'route' => 
  array (
  ),
);'''
        
        # JavaScript virus patterns
        self.js_virus_patterns = {
            'navigator.platform': r'navigator\.platform',
            'base64': r'base64',
            'hex_string': r'\\\\x[0-9a-fA-F]{2}',
            'appendChild': r'appendChild',
            'Mac|Win': r'Mac\|Win'
        }
    
    def check_php_active_system(self, sites):
        """Check for PHP active.php and system.php virus files"""
        print_header("PHP Active/System 文件检查")
        
        if not sites:
            print_colored("站点列表为空", Colors.YELLOW)
            return
        
        for site in sites:
            if not site.strip():
                continue
            
            print_colored(f"检查站点: {site}", Colors.YELLOW)
            
            # Files to check
            active_file = os.path.join(site, "application", "extra", "active.php")
            system_file = os.path.join(site, "application", "extra", "system.php")
            
            found_virus = False
            
            # Check active.php
            if os.path.exists(active_file):
                print_colored("命中病毒规则: system-active", Colors.RED)
                print_colored(f"发现可疑文件: {active_file}", Colors.RED)
                found_virus = True
                
                if confirm_action("是否将此文件移动到安全位置？"):
                    backup_file = active_file.replace('.php', '.lock')
                    try:
                        os.rename(active_file, backup_file)
                        print_colored(f"文件已移动到: {backup_file}", Colors.GREEN)
                        print_colored(f"如果出现问题，可以将文件 {backup_file} 重命名为 {active_file} 还原", Colors.YELLOW)
                    except Exception as e:
                        print_colored(f"移动文件失败: {e}", Colors.RED)
                print()
            
            # Check system.php
            if os.path.exists(system_file):
                print_colored("命中病毒规则: system-active", Colors.RED)
                print_colored(f"发现可疑文件: {system_file}", Colors.RED)
                found_virus = True
                
                if confirm_action("是否将此文件移动到安全位置？"):
                    backup_file = system_file.replace('.php', '.lock')
                    try:
                        os.rename(system_file, backup_file)
                        print_colored(f"文件已移动到: {backup_file}", Colors.GREEN)
                        print_colored(f"如果出现问题，可以将文件 {backup_file} 重命名为 {system_file} 还原", Colors.YELLOW)
                    except Exception as e:
                        print_colored(f"移动文件失败: {e}", Colors.RED)
                print()
            
            if not found_virus:
                print_colored("未发现 active/system 病毒文件", Colors.GREEN)
            
            print()
        
        print_colored("PHP Active/System 文件检查完成", Colors.GREEN)
    
    def check_php_addons_hijack(self, sites):
        """Check for PHP addons.php hijacking"""
        print_header("PHP Addons 劫持检查")
        
        if not sites:
            print_colored("站点列表为空", Colors.YELLOW)
            return
        
        for site in sites:
            if not site.strip():
                continue
            
            print_colored(f"检查站点: {site}", Colors.YELLOW)
            
            # Check both possible file names
            addons_file = os.path.join(site, "application", "extra", "addons.php")
            addones_file = os.path.join(site, "application", "extra", "addones.php")  # virus sample filename
            
            target_file = None
            if os.path.exists(addons_file):
                target_file = addons_file
            elif os.path.exists(addones_file):
                target_file = addones_file
            
            if target_file:
                try:
                    with open(target_file, 'r', encoding='utf-8', errors='ignore') as f:
                        content = f.read()
                    
                    # Check if file contains ThinkPHP (possible hijack signature)
                    if 'ThinkPHP' in content:
                        print_colored("命中病毒规则: addons劫持", Colors.RED)
                        print_colored(f"发现可疑文件: {target_file}", Colors.RED)
                        print()
                        
                        print_colored("注意: 如果覆盖，会导致插件被禁用，安装插件的用户勿用。", Colors.YELLOW)
                        if confirm_action("是否用干净文件覆盖？"):
                            # Backup original file
                            backup_file = target_file.replace('.php', '.lock')
                            try:
                                # Create backup
                                with open(backup_file, 'w', encoding='utf-8') as f:
                                    f.write(content)
                                print_colored(f"原文件已备份到: {backup_file}", Colors.GREEN)
                                
                                # Write clean content
                                with open(target_file, 'w', encoding='utf-8') as f:
                                    f.write(self.clean_addons_content)
                                print_colored("已用干净文件覆盖", Colors.GREEN)
                                print_colored(f"如果出现问题，可以删除文件 {target_file}，然后将 {backup_file} 重命名为 {target_file} 还原", Colors.YELLOW)
                            except Exception as e:
                                print_colored(f"处理文件失败: {e}", Colors.RED)
                    else:
                        print_colored("addons.php 文件正常", Colors.GREEN)
                
                except Exception as e:
                    print_colored(f"读取文件失败 {target_file}: {e}", Colors.RED)
            else:
                print_colored("未找到 addons.php 或 addones.php 文件", Colors.YELLOW)
            
            print()
        
        print_colored("PHP Addons 劫持检查完成", Colors.GREEN)
    
    def find_js_and_html_files(self, site_path):
        """Find JavaScript and HTML files in a site"""
        js_files = []
        html_files = []
        
        try:
            site_dir = Path(site_path)
            
            # Find all .js files
            js_files = list(site_dir.rglob('*.js'))
            
            # Find HTML files specifically in template directories
            for template_dir in site_dir.rglob('template'):
                if template_dir.is_dir():
                    html_files.extend(template_dir.rglob('*.html'))
        
        except Exception as e:
            print_colored(f"搜索文件时出错: {e}", Colors.RED)
        
        return js_files + html_files
    
    def analyze_js_file(self, file_path):
        """Analyze a JavaScript or HTML file for virus patterns"""
        pattern_hits = {}
        
        try:
            with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                content = f.read()
            
            # Check each virus pattern
            for pattern_name, pattern_regex in self.js_virus_patterns.items():
                if pattern_name == 'hex_string':
                    # Special handling for hex strings
                    matches = re.findall(pattern_regex, content)
                    pattern_hits[pattern_name] = len(matches)
                else:
                    matches = re.findall(pattern_regex, content, re.IGNORECASE)
                    pattern_hits[pattern_name] = len(matches)
        
        except Exception as e:
            print_colored(f"分析文件失败 {file_path}: {e}", Colors.RED)
            return {}
        
        return pattern_hits
    
    def check_javascript_virus(self, sites):
        """Check for JavaScript virus patterns"""
        print_header("JavaScript 病毒特征检查")
        
        if not sites:
            print_colored("站点列表为空", Colors.YELLOW)
            return
        
        # Create timestamped log directory
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        log_dir = os.path.join(self.log_dir, timestamp)
        ensure_dir_exists(log_dir)
        
        print_colored(f"日志目录: {log_dir}", Colors.BLUE)
        print_colored("由于病毒变种很多，只输出可疑特征", Colors.YELLOW)
        print()
        
        for site in sites:
            if not site.strip():
                continue
            
            print_colored(f"检查站点: {site}", Colors.YELLOW)
            
            # Create site log directory
            site_name = os.path.basename(site.rstrip('/'))
            site_log_dir = os.path.join(log_dir, site_name)
            ensure_dir_exists(site_log_dir)
            
            # Initialize log files for each pattern
            pattern_logs = {}
            for pattern_name in self.js_virus_patterns.keys():
                log_file = os.path.join(site_log_dir, f"{pattern_name}.txt")
                pattern_logs[pattern_name] = log_file
                # Create empty log file
                with open(log_file, 'w', encoding='utf-8') as f:
                    pass
            
            # Find all JS and HTML files
            files_to_check = self.find_js_and_html_files(site)
            
            if not files_to_check:
                print_colored("未找到JS文件或template下的HTML文件", Colors.GREEN)
                print()
                continue
            
            suspicious_files = 0
            
            for file_path in files_to_check:
                if not file_path.exists():
                    continue
                
                pattern_hits = self.analyze_js_file(file_path)
                
                # Check if any pattern was hit
                total_hits = sum(pattern_hits.values())
                has_hit = total_hits > 0
                
                if has_hit:
                    print()
                    print_colored(f"可疑文件: {file_path}", Colors.RED)
                    
                    for pattern_name, hits in pattern_hits.items():
                        print_colored(f"  可疑特征 {pattern_name}: {hits} 次", Colors.YELLOW)
                        
                        # Log to pattern-specific file if there are hits
                        if hits > 0:
                            log_file = pattern_logs[pattern_name]
                            with open(log_file, 'a', encoding='utf-8') as f:
                                f.write(f"{hits} {file_path.name}: {file_path}\n")
                    
                    print()
                    suspicious_files += 1
            
            # Sort and clean up log files
            for pattern_name, log_file in pattern_logs.items():
                if os.path.exists(log_file) and os.path.getsize(log_file) > 0:
                    # Sort file contents by hit count (descending)
                    with open(log_file, 'r', encoding='utf-8') as f:
                        lines = f.readlines()
                    
                    # Sort by the number at the beginning of each line
                    lines.sort(key=lambda x: int(x.split()[0]) if x.split() else 0, reverse=True)
                    
                    with open(log_file, 'w', encoding='utf-8') as f:
                        f.writelines(lines)
                else:
                    # Remove empty log files
                    if os.path.exists(log_file):
                        os.remove(log_file)
            
            if suspicious_files == 0:
                print_colored("未发现可疑JS/HTML文件", Colors.GREEN)
            else:
                print_colored(f"在该站点发现 {suspicious_files} 个可疑JS/HTML文件", Colors.RED)
            
            print()
        
        print_colored("JavaScript病毒检查完成！", Colors.GREEN)
        print_colored(f"详细日志已保存到: {log_dir}", Colors.BLUE)
        print()
    
    def show_virus_menu(self):
        """Show virus checking menu"""
        print_colored("请选择病毒检查类型:", Colors.GREEN)
        print("1. PHP活跃病毒检查 (检查PHP文件中的恶意代码)")
        print("2. PHP插件病毒检查 (检查PHP插件和模板中的病毒)")
        print("3. JavaScript病毒检查 (检查JS和HTML文件中的可疑代码)")
        print("0. 返回上级菜单")
        print()
        
        try:
            choice = input("请输入选项 [0-3]: ").strip()
            return choice
        except KeyboardInterrupt:
            print_colored("\n操作已取消", Colors.YELLOW)
            return "0"
        except EOFError:
            print_colored("\n操作已取消", Colors.YELLOW)
            return "0"
    
    def run_virus_check(self):
        """Main virus checking interface"""
        print_header("MacCMS 病毒检查系统")
        
        # Check if site.txt exists
        sites = read_site_list(self.data_dir)
        if not sites:
            print_colored("错误: 未找到站点列表文件或站点列表为空", Colors.RED)
            print_colored("请先运行主菜单选项1更新站点列表", Colors.YELLOW)
            return False
        
        print_colored("读取到以下站点:", Colors.GREEN)
        for i, site in enumerate(sites, 1):
            print(f"{i:2d}. {site}")
        print()
        
        # Virus checking menu loop
        while True:
            choice = self.show_virus_menu()
            print()
            
            if choice == "1":
                self.check_php_active_system(sites)
                pause_for_user()
                print()
            elif choice == "2":
                self.check_php_addons_hijack(sites)
                pause_for_user()
                print()
            elif choice == "3":
                self.check_javascript_virus(sites)
                pause_for_user()
                print()
            elif choice == "0":
                print_colored("返回主菜单", Colors.GREEN)
                break
            else:
                print_colored("无效选项，请重新选择。", Colors.RED)
                print()
        
        return True


def main():
    """Main function for standalone execution"""
    checker = MacCMSVirusChecker()
    checker.run_virus_check()


if __name__ == "__main__":
    main()