#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
AUTOLOAD_FILE="$ROOT_DIR/vendor/autoload.php"

if [[ ! -f "$AUTOLOAD_FILE" ]]; then
  echo "Missing $AUTOLOAD_FILE"
  echo "Run: cd backend && composer install"
  exit 1
fi

php "$ROOT_DIR/tests/usecase/run.php"
