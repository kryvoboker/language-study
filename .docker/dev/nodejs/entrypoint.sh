#!/bin/sh
set -e

# Apply umask for runtime
umask "${UMASK:-0002}"

exec npm run dev