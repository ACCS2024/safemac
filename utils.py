#!/usr/bin/env python3
"""
Utility functions for the MacCMS security tool
Provides color output, user input handling, and common functions
"""

import sys
import os


class Colors:
    """ANSI color codes for terminal output"""
    RED = '\033[0;31m'
    GREEN = '\033[0;32m'
    YELLOW = '\033[1;33m'
    BLUE = '\033[0;34m'
    NC = '\033[0m'  # No Color


def print_colored(text, color=Colors.NC):
    """Print colored text to stdout"""
    print(f"{color}{text}{Colors.NC}")


def print_header(title):
    """Print a formatted header"""
    print_colored("=" * 37, Colors.BLUE)
    print_colored(f"    {title}", Colors.BLUE)
    print_colored("=" * 37, Colors.BLUE)
    print()


def get_user_input(prompt, default=None):
    """Get user input with optional default value"""
    try:
        if default is not None:
            response = input(f"{prompt} [{default}]: ").strip()
            return response if response else default
        else:
            return input(f"{prompt}: ").strip()
    except KeyboardInterrupt:
        print_colored("\n操作已取消", Colors.YELLOW)
        sys.exit(0)
    except EOFError:
        print_colored("\n操作已取消", Colors.YELLOW)
        sys.exit(0)


def confirm_action(prompt, default_no=True):
    """Ask for user confirmation, returns True/False"""
    suffix = "(y/N)" if default_no else "(Y/n)"
    try:
        response = input(f"{prompt} {suffix}: ").strip().lower()
        if default_no:
            return response in ['y', 'yes', '是']
        else:
            return response not in ['n', 'no', '否']
    except KeyboardInterrupt:
        print_colored("\n操作已取消", Colors.YELLOW)
        return False
    except EOFError:
        print_colored("\n操作已取消", Colors.YELLOW)
        return False


def get_script_dir():
    """Get the directory where the script is located"""
    return os.path.dirname(os.path.abspath(__file__))


def ensure_dir_exists(directory):
    """Ensure a directory exists, create if it doesn't"""
    os.makedirs(directory, exist_ok=True)


def read_site_list(data_dir):
    """Read the site list from data/site.txt"""
    site_file = os.path.join(data_dir, "site.txt")
    sites = []
    
    if os.path.exists(site_file):
        with open(site_file, 'r', encoding='utf-8') as f:
            sites = [line.strip() for line in f if line.strip()]
    
    return sites


def write_site_list(data_dir, sites):
    """Write the site list to data/site.txt"""
    ensure_dir_exists(data_dir)
    site_file = os.path.join(data_dir, "site.txt")
    
    with open(site_file, 'w', encoding='utf-8') as f:
        for site in sites:
            f.write(f"{site}\n")


def pause_for_user():
    """Pause execution until user presses Enter"""
    try:
        input("按回车键继续...")
    except KeyboardInterrupt:
        print_colored("\n操作已取消", Colors.YELLOW)
        sys.exit(0)
    except EOFError:
        pass