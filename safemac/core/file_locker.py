# -*- coding: utf-8 -*-
#!/usr/bin/env python3
"""
MacCMS File Locker
Manages file locking/unlocking using chattr +i/-i attributes
"""

import os
import subprocess
import sys
import threading
from pathlib import Path
from ..utils import Colors, print_colored, print_header, confirm_action, get_script_dir, read_site_list


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
            # Test chattr with no arguments - shows usage in stderr, exit code 1 is expected
            result = subprocess.run(['chattr'], stdout=subprocess.PIPE, stderr=subprocess.PIPE)
            # chattr shows usage when run without arguments
            return b'Usage:' in result.stderr or b'usage:' in result.stderr
        except FileNotFoundError:
            return False
        except Exception:
            return False
    
    def execute_shell_command(self, command):
        """Execute a shell command and return True if successful"""
        try:
            # 使用 universal_newlines 而不是 text 参数（Python 3.6.8 兼容）
            subprocess.run(command, stdout=subprocess.PIPE, stderr=subprocess.PIPE,
                          universal_newlines=True, check=True, shell=True)
            return True
        except subprocess.CalledProcessError:
            return False
        except Exception as e:
            print_colored(f"    执行命令出错: {str(e)}", Colors.RED)
            return False
    
    def lock_site(self, site_path):
        """Lock core files and directories for a MacCMS site"""
        site_dir = Path(site_path)
        
        if not site_dir.exists():
            print_colored(f"站点目录不存在: {site_path}", Colors.RED)
            return False
        
        print_colored(f"处理站点: {site_path}", Colors.YELLOW)
        print_colored("  正在锁定核心文件和目录（chattr +i）...", Colors.YELLOW)
        
        # Lock core directories
        for dir_name in self.lock_dirs:
            target_dir = site_dir / dir_name
            if target_dir.exists():
                cmd = f"find {target_dir} -type f -o -type d | xargs chattr +i 2>/dev/null"
                if self.execute_shell_command(cmd):
                    print_colored(f"    已锁定: {dir_name} 目录及其所有内容", Colors.GREEN)
                else:
                    print_colored(f"    锁定失败: {dir_name}", Colors.RED)
        
        # Lock PHP files in root directory
        for pattern in self.lock_files:
            if pattern == "*.php":
                cmd = f"find {site_dir} -maxdepth 1 -name '*.php' | xargs chattr +i 2>/dev/null"
                self.execute_shell_command(cmd)
                print_colored(f"    已锁定: 根目录PHP文件", Colors.GREEN)
            else:
                file_path = site_dir / pattern
                if file_path.exists():
                    cmd = f"chattr +i {file_path} 2>/dev/null"
                    if self.execute_shell_command(cmd):
                        print_colored(f"    已锁定: {pattern}", Colors.GREEN)

        # Ensure exclude directories are unlocked
        for exclude_dir in self.exclude_dirs:
            target_dir = site_dir / exclude_dir
            if target_dir.exists():
                cmd = f"find {target_dir} -type f -o -type d | xargs chattr -i 2>/dev/null"
                if self.execute_shell_command(cmd):
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
        
        # Simple one-line command to recursively unlock all files and directories
        cmd = f"find {site_dir} -type f -o -type d | xargs chattr -i 2>/dev/null"
        if self.execute_shell_command(cmd):
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
    
    def process_sites_in_parallel(self, sites, operation):
        """Process multiple sites in parallel using threads"""
        threads = []

        for site in sites:
            if operation == 'lock':
                thread = threading.Thread(target=self.lock_site, args=(site,))
            else:  # unlock
                thread = threading.Thread(target=self.unlock_site, args=(site,))
            threads.append(thread)
            thread.start()

        # Wait for all threads to complete
        for thread in threads:
            thread.join()

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
        
        # Process selected sites in parallel
        self.process_sites_in_parallel(selected_sites, 'lock')

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
        
        # Process selected sites in parallel
        self.process_sites_in_parallel(selected_sites, 'unlock')

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