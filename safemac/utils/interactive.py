# -*- coding: utf-8 -*-
#!/usr/bin/env python3
"""
Interactive utilities for the MacCMS security tool
Provides user input handling and confirmation functions
"""

import sys
from .colors import Colors, print_colored


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


def pause_for_user():
    """Pause execution until user presses Enter"""
    try:
        input("按回车键继续...")
    except KeyboardInterrupt:
        print_colored("\n操作已取消", Colors.YELLOW)
        sys.exit(0)
    except EOFError:
        pass