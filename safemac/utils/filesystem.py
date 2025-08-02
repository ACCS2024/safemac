# -*- coding: utf-8 -*-
#!/usr/bin/env python3
"""
File system utilities for the MacCMS security tool
Provides directory and file management functions
"""

import os


def get_script_dir():
    """Get the directory where the script is located"""
    # Get the directory of the current module, then go up to get the project root
    current_dir = os.path.dirname(os.path.abspath(__file__))
    # Go up from safemac/utils to safemac root
    return os.path.dirname(os.path.dirname(current_dir))


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