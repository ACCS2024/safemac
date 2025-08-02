# -*- coding: utf-8 -*-
#!/usr/bin/env python3
"""
Color and output utilities for the MacCMS security tool
Provides colored terminal output and formatted headers
"""

import sys


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