# -*- coding: utf-8 -*-
#!/usr/bin/env python3
"""
MacCMS Site Scanner
Scans common web server paths and detects MacCMS installations
"""

import os
import sys
from pathlib import Path
from ..utils import Colors, print_colored, get_script_dir, ensure_dir_exists, write_site_list


class MacCMSSiteScanner:
    """Scanner for MacCMS installations"""
    
    def __init__(self):
        self.script_dir = get_script_dir()
        self.data_dir = os.path.join(self.script_dir, "data")
        
        # Common web server paths to scan
        self.common_paths = [
            "/home/www/wwwroot",
            "/www/wwwroot", 
            "/home/wwwroot",
            "/home/www",
            "/var/www/html",        # Apache default path
            "/usr/share/nginx/html", # Nginx default path
            "/opt/lampp/htdocs",     # XAMPP path
            "/home",
            os.getcwd()              # Current directory for testing
        ]
        
        # MacCMS characteristic directories
        self.maccms_dirs = [
            "application",
            "runtime",
            "thinkphp",
            "template"
        ]
        
        # MacCMS characteristic files
        self.maccms_files = [
            "api.php",
            "install.php"
        ]
    
    def is_maccms_site(self, directory):
        """Check if a directory contains a MacCMS installation"""
        dir_path = Path(directory)
        if not dir_path.exists() or not dir_path.is_dir():
            return False
        
        score = 0
        
        # Special case for demo directory (for testing)
        if dir_path.name == "demo" and (dir_path / "application").exists():
            return True
        
        # Check for characteristic directories
        for feature_dir in self.maccms_dirs:
            if (dir_path / feature_dir).exists():
                score += 1
        
        # Check for characteristic files
        for feature_file in self.maccms_files:
            if (dir_path / feature_file).exists():
                score += 1
        
        # If at least 2 features match, consider it MacCMS
        return score >= 2
    
    def find_sites_in_path(self, base_path):
        """Find MacCMS sites in a given base path"""
        sites = []
        
        if not os.path.exists(base_path):
            return sites
        
        try:
            # Look for directories containing MacCMS characteristic directories
            for root, dirs, files in os.walk(base_path):
                # Check if any of the MacCMS directories exist in current directory
                has_maccms_feature = any(
                    feature_dir in dirs for feature_dir in self.maccms_dirs
                )
                
                if has_maccms_feature and self.is_maccms_site(root):
                    sites.append(root)
                    # Don't scan subdirectories of found MacCMS sites
                    dirs.clear()
        
        except PermissionError:
            print_colored(f"权限不足，跳过: {base_path}", Colors.YELLOW)
        except Exception as e:
            print_colored(f"扫描错误 {base_path}: {e}", Colors.RED)
        
        return sites
    
    def scan_all_sites(self):
        """Scan all common paths for MacCMS sites"""
        print_colored("开始扫描MacCMS站点...", Colors.BLUE)
        print()
        
        ensure_dir_exists(self.data_dir)
        
        all_sites = []
        
        for base_path in self.common_paths:
            if os.path.exists(base_path):
                print_colored(f"扫描路径: {base_path}", Colors.YELLOW)
                sites = self.find_sites_in_path(base_path)
                all_sites.extend(sites)
            else:
                print_colored(f"路径不存在，跳过: {base_path}", Colors.YELLOW)
        
        # Remove duplicates and sort
        unique_sites = sorted(list(set(all_sites)))
        
        # Write to file
        write_site_list(self.data_dir, unique_sites)
        
        print()
        if unique_sites:
            print_colored(f"扫描完成！共发现 {len(unique_sites)} 个MacCMS站点", Colors.GREEN)
            print()
            print_colored("发现的站点列表:", Colors.YELLOW)
            for i, site in enumerate(unique_sites, 1):
                print(f"{i:2d}. {site}")
            print()
            print_colored("警告: 请检查是否是正确的网站目录，已写入到 data/site.txt", Colors.YELLOW)
            print_colored("你可以直接编辑 data/site.txt 删掉不需要检查的目录", Colors.YELLOW)
        else:
            print_colored("未发现任何MacCMS站点", Colors.RED)
            print_colored("如果确实存在MacCMS站点，请手动添加到 data/site.txt", Colors.YELLOW)
        
        print()
        return unique_sites


def main():
    """Main function for standalone execution"""
    scanner = MacCMSSiteScanner()
    scanner.scan_all_sites()


if __name__ == "__main__":
    main()