# -*- coding: utf-8 -*-
#!/usr/bin/env python3
"""
DEPRECATED: This file has been moved to safemac.core.file_locker
This is kept for backward compatibility. Please use the new package structure.
"""

import warnings
warnings.warn(
    "file_locker.py is deprecated. Use 'from safemac.core import MacCMSFileLocker' instead.",
    DeprecationWarning,
    stacklevel=2
)

from safemac.core.file_locker import MacCMSFileLocker

if __name__ == "__main__":
    from safemac.core.file_locker import main
    main()