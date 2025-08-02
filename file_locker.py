# -*- coding: utf-8 -*-
#!/usr/bin/env python3
"""
MacCMS File Locker
Manages file locking/unlocking using chattr +i/-i attributes
"""

import os
import subprocess
import sys
from pathlib import Path
from utils import Colors, print_colored, print_header, confirm_action, get_script_dir, read_site_list


class MacCMSFileLocker:
    """Manages file locking and unlocking for MacCMS sites"""
    
    def __init__(self):
        self.script_dir = get_script_dir()
        self.data_dir = os.path.join(self.script_dir, "data")
        
        # Core directories to protect (relative paths)
        self.lock_dirs = [
            "application",
            "thinkphp", 
            "template",
            "public/static/js",
            "public/static/css",
            "extend",
            "static",
            "vendor"
        ]
        
        # Core files to protect (patterns)
        self.lock_files = [
            "api.php",
            "index.php",
            "*.php"
        ]
        
        # Directories to exclude from locking (cache and upload related)
        self.exclude_dirs = [
            "runtime",
            "upload",
            "uploads", 
            "static/upload",
            "public/upload",
            "public/uploads",
            "public/static/upload"
        ]
    
    def check_chattr_support(self):
        """Check if chattr command is available"""
        try:
            subprocess.run(['chattr', '--version'], capture_output=True, check=True)
            return True
        except (subprocess.CalledProcessError, FileNotFoundError):
            return False
    
    def run_chattr(self, operation, path):
        """Run chattr command on a path"""
        try:
            cmd = ['chattr', operation, str(path)]
            subprocess.run(cmd, capture_output=True, check=True)
            return True
        except subprocess.CalledProcessError:
            return False
        except Exception:
            return False
    
    def lock_path(self, path):
        """Lock a file or directory using chattr +i"""
        return self.run_chattr('+i', path)
    
    def unlock_path(self, path):
        """Unlock a file or directory using chattr -i"""
        return self.run_chattr('-i', path)
    
    def lock_directory_recursive(self, directory):
        """Recursively lock all files and directories in a path"""
        dir_path = Path(directory)
        if not dir_path.exists():
            return False
        
        success_count = 0
        total_count = 0
        
        # Lock the directory itself first
        if self.lock_path(dir_path):
            success_count += 1
        total_count += 1
        
        # Recursively lock all files and subdirectories
        try:
            for item in dir_path.rglob('*'):
                total_count += 1
                if self.lock_path(item):
                    success_count += 1
        except Exception as e:
            print_colored(f"    递归锁定时出错: {e}", Colors.RED)
        
        return success_count > 0
    
    def unlock_directory_recursive(self, directory):
        """Recursively unlock all files and directories in a path"""
        dir_path = Path(directory)
        if not dir_path.exists():
            return False
        
        success_count = 0
        total_count = 0
        
        try:
            # Unlock all files and subdirectories first, then the directory
            for item in dir_path.rglob('*'):
                total_count += 1
                if self.unlock_path(item):
                    success_count += 1
            
            # Unlock the directory itself
            if self.unlock_path(dir_path):
                success_count += 1
            total_count += 1
            
        except Exception as e:
            print_colored(f"    递归解锁时出错: {e}", Colors.RED)
        
        return success_count > 0
    
    def get_php_files_in_root(self, site_path):
        """Get PHP files in the root directory of a site"""
        site_dir = Path(site_path)
        php_files = []
        
        try:
            for pattern in self.lock_files:
                if pattern == "*.php":
                    php_files.extend(site_dir.glob("*.php"))
                else:
                    file_path = site_dir / pattern
                    if file_path.exists():
                        php_files.append(file_path)
        except Exception:
            pass
        
        return php_files
    
    def lock_site(self, site_path):
        """Lock core files and directories for a MacCMS site"""
        site_dir = Path(site_path)
        
        if not site_dir.exists():
            print_colored(f"站点目录不存在: {site_path}", Colors.RED)
            return False
        
        print_colored(f"处理站点: {site_path}", Colors.YELLOW)
        print_colored("  正在锁定核心文件和目录（chattr +i）...", Colors.YELLOW)
        
        # Lock specified directories and their contents
        for dir_name in self.lock_dirs:
            target_dir = site_dir / dir_name
            if target_dir.exists():
                if self.lock_directory_recursive(target_dir):
                    print_colored(f"    已锁定: {dir_name} 目录及其所有内容", Colors.GREEN)
                else:
                    print_colored(f"    锁定失败: {dir_name}", Colors.RED)
        
        # Lock root PHP files
        php_files = self.get_php_files_in_root(site_path)
        for php_file in php_files:
            if self.lock_path(php_file):
                print_colored(f"    已锁定: {php_file.name}", Colors.GREEN)
            else:
                print_colored(f"    锁定失败: {php_file.name}", Colors.RED)
        
        # Ensure exclude directories are unlocked
        for exclude_dir in self.exclude_dirs:
            target_dir = site_dir / exclude_dir
            if target_dir.exists():
                if self.unlock_directory_recursive(target_dir):
                    print_colored(f"    保持可写: {exclude_dir}", Colors.BLUE)
        
        return True
    
    def unlock_site(self, site_path):
        """Unlock all files and directories for a MacCMS site"""
        site_dir = Path(site_path)
        
        if not site_dir.exists():
            print_colored(f"站点目录不存在: {site_path}", Colors.RED)
            return False
        
        print_colored(f"处理站点: {site_path}", Colors.YELLOW)
        print_colored("  正在解锁所有文件和目录（chattr -i）...", Colors.YELLOW)
        
        # Recursively unlock all files and directories in the site
        if self.unlock_directory_recursive(site_dir):
            print_colored("    已解锁所有文件和目录", Colors.GREEN)
        else:
            print_colored("    解锁过程中出现一些错误", Colors.YELLOW)
        
        return True
    
    def select_sites(self, sites):
        """Allow user to select which sites to operate on"""
        if not sites:
            print_colored("站点列表为空", Colors.YELLOW)
            return []
        
        print_colored("站点列表:", Colors.GREEN)
        for i, site in enumerate(sites, 1):
            print(f"{i:2d}. {site}")
        print()
        
        try:
            selection = input("请选择要操作的站点 (输入数字，多个用空格分隔，或输入 'all' 选择全部): ").strip()
            
            if selection.lower() == 'all':
                return sites
            
            selected_sites = []
            for num_str in selection.split():
                try:
                    num = int(num_str)
                    if 1 <= num <= len(sites):
                        selected_sites.append(sites[num - 1])
                except ValueError:
                    continue
            
            return selected_sites
            
        except KeyboardInterrupt:
            print_colored("\n操作已取消", Colors.YELLOW)
            return []
    
    def lock_sites(self):
        """Interactive site locking"""
        print_header("MacCMS 网站核心文件保护")
        
        if not self.check_chattr_support():
            print_colored("错误: 当前系统不支持 chattr 命令", Colors.RED)
            return False
        
        sites = read_site_list(self.data_dir)
        if not sites:
            print_colored("错误: 未找到站点列表文件或站点列表为空", Colors.RED)
            return False
        
        selected_sites = self.select_sites(sites)
        if not selected_sites:
            print_colored("未选择任何有效站点", Colors.RED)
            return False
        
        print()
        print_colored("选中的站点:", Colors.YELLOW)
        for site in selected_sites:
            print(f"  {site}")
        print()
        
        # Lock each selected site
        for site in selected_sites:
            self.lock_site(site)
            print()
        
        print_colored("网站核心文件保护完成！", Colors.GREEN)
        print_colored("注意: 即使root用户也无法修改被锁定的文件，需要先解锁才能更新网站", Colors.YELLOW)
        print()
        
        return True
    
    def unlock_sites(self):
        """Interactive site unlocking"""
        print_header("MacCMS 网站文件解锁")
        
        if not self.check_chattr_support():
            print_colored("错误: 当前系统不支持 chattr 命令", Colors.RED)
            return False
        
        sites = read_site_list(self.data_dir)
        if not sites:
            print_colored("错误: 未找到站点列表文件或站点列表为空", Colors.RED)
            return False
        
        selected_sites = self.select_sites(sites)
        if not selected_sites:
            print_colored("未选择任何有效站点", Colors.RED)
            return False
        
        print()
        print_colored("选中的站点:", Colors.YELLOW)
        for site in selected_sites:
            print(f"  {site}")
        print()
        
        # Unlock each selected site
        for site in selected_sites:
            self.unlock_site(site)
            print()
        
        print_colored("网站文件解锁完成！", Colors.GREEN)
        print()
        
        return True


def main():
    """Main function for standalone execution"""
    if len(sys.argv) != 2 or sys.argv[1] not in ['lock', 'unlock']:
        print("用法: python file_locker.py [lock|unlock]")
        print("  lock   - 锁定网站核心文件（使用chattr +i）")
        print("  unlock - 解锁网站核心文件（使用chattr -i）")
        sys.exit(1)
    
    locker = MacCMSFileLocker()
    
    if sys.argv[1] == 'lock':
        locker.lock_sites()
    else:
        locker.unlock_sites()


if __name__ == "__main__":
    main()