#!/usr/bin/env bash
set -euo pipefail

PORT="${PORT:-8000}"
DOC_ROOT="${DOC_ROOT:-$(pwd)}"

cd "$DOC_ROOT"
echo "Serwowanie plików z: $DOC_ROOT na porcie: $PORT"
python3 -m http.server "$PORT"
