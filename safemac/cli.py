# -*- coding: utf-8 -*-
#!/usr/bin/env python3
"""
MacCMS File Checking System CLI
Command Line Interface for the MacCMS security tool
"""

import os
import sys
from .core import MacCMSSiteScanner, MacCMSVirusChecker, MacCMSFileLocker
from .utils import Colors, print_colored, print_header, confirm_action, get_script_dir, read_site_list


class MacCMSSecurityTool:
    """Main MacCMS Security Tool"""
    
    def __init__(self):
        self.script_dir = get_script_dir()
        self.data_dir = os.path.join(self.script_dir, "data")
        
        # Initialize components
        self.site_scanner = MacCMSSiteScanner()
        self.virus_checker = MacCMSVirusChecker()
        self.file_locker = MacCMSFileLocker()
    
    def check_initial_setup(self):
        """Check if initial setup is needed"""
        site_file = os.path.join(self.data_dir, "site.txt")
        
        if not os.path.exists(site_file):
            print_colored("未发现站点列表文件，开始扫描MacCMS站点...", Colors.YELLOW)
            print()
            
            sites = self.site_scanner.scan_all_sites()
            if not sites:
                print_colored("站点扫描失败，程序退出。", Colors.RED)
                sys.exit(1)
            print()
    
    def show_main_menu(self):
        """Display the main menu"""
        print_colored("请选择操作:", Colors.GREEN)
        print("1. 更新站点列表")
        print("2. 运行病毒检查") 
        print("3. 锁定网站写入")
        print("4. 解锁网站写入")
        print("0. 退出")
        print()
        
        try:
            choice = input("请输入选项 [0-4]: ").strip()
            return choice
        except KeyboardInterrupt:
            print_colored("\n感谢使用 MacCMS 文件检查系统！", Colors.GREEN)
            sys.exit(0)
        except EOFError:
            print_colored("\n感谢使用 MacCMS 文件检查系统！", Colors.GREEN)
            sys.exit(0)
    
    def update_site_list(self):
        """Update the site list"""
        print_colored("正在更新站点列表...", Colors.YELLOW)
        self.site_scanner.scan_all_sites()
        print()
    
    def run_virus_check(self):
        """Run virus checking with confirmation"""
        print_colored("警告！危险操作！请备份您的网站文件后再操作。", Colors.RED)
        print_colored("如果想打断运行，请按下 Ctrl+C", Colors.YELLOW)
        print()
        
        if confirm_action("确认继续吗？"):
            print_colored("开始运行病毒检查...", Colors.YELLOW)
            self.virus_checker.run_virus_check()
        else:
            print_colored("已取消操作。", Colors.GREEN)
        print()
    
    def lock_website_files(self):
        """Lock website files"""
        print_colored("正在锁定网站写入权限...", Colors.YELLOW)
        if self.file_locker.lock_sites():
            print_colored("锁定操作完成", Colors.GREEN)
        else:
            print_colored("锁定操作失败", Colors.RED)
        print()
    
    def unlock_website_files(self):
        """Unlock website files"""
        print_colored("正在解锁网站写入权限...", Colors.YELLOW)
        if self.file_locker.unlock_sites():
            print_colored("解锁操作完成", Colors.GREEN)
        else:
            print_colored("解锁操作失败", Colors.RED)
        print()
    
    def run(self):
        """Main program loop"""
        print_header("MacCMS 文件检查系统 v1.0")
        print()
        
        # Check initial setup
        self.check_initial_setup()
        
        # Main menu loop
        while True:
            choice = self.show_main_menu()
            print()
            
            if choice == "1":
                self.update_site_list()
            elif choice == "2":
                self.run_virus_check()
            elif choice == "3":
                self.lock_website_files()
            elif choice == "4":
                self.unlock_website_files()
            elif choice == "0":
                print_colored("感谢使用 MacCMS 文件检查系统！", Colors.GREEN)
                break
            else:
                print_colored("无效选项，请重新选择。", Colors.RED)
                print()


def main():
    """Main entry point"""
    try:
        tool = MacCMSSecurityTool()
        tool.run()
    except KeyboardInterrupt:
        print_colored("\n\n感谢使用 MacCMS 文件检查系统！", Colors.GREEN)
        sys.exit(0)
    except Exception as e:
        print_colored(f"\n程序运行时出现错误: {e}", Colors.RED)
        sys.exit(1)


if __name__ == "__main__":
    main()