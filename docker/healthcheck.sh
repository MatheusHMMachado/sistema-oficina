#!/bin/sh
set -e

PORT_CHECK=${PORT:-80}
curl -f "http://localhost:${PORT_CHECK}/" || exit 1

