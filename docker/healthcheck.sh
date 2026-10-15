#!/bin/sh
set -e

PORT_CHECK=${PORT:-8080}
curl -f "http://127.0.0.1:${PORT_CHECK}/health" || curl -f "http://127.0.0.1:80/health" || curl -f "http://127.0.0.1:8080/health" || exit 1


