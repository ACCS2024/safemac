# -*- coding: utf-8 -*-
#!/usr/bin/env python3
"""
DEPRECATED: This file has been moved to safemac.core.scanner
This is kept for backward compatibility. Please use the new package structure.
"""

import warnings
warnings.warn(
    "site_scanner.py is deprecated. Use 'from safemac.core import MacCMSSiteScanner' instead.",
    DeprecationWarning,
    stacklevel=2
)

from safemac.core.scanner import MacCMSSiteScanner

if __name__ == "__main__":
    from safemac.core.scanner import main
    main()