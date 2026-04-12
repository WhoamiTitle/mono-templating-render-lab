#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=tests/api/lib.sh
source "$SCRIPT_DIR/lib.sh"

api_test_bootstrap
trap api_test_cleanup EXIT

for test_script in "$SCRIPT_DIR"/[0-9][0-9]-*.sh; do
  test_name="$(basename "$test_script")"
  echo "Running $test_name"
  API_TEST_NO_STACK_MANAGEMENT=1 \
  API_TEST_KEEP_STACK="$API_TEST_KEEP_STACK" \
  BASE_URL="$BASE_URL" \
  COMPOSE_FILE="$COMPOSE_FILE" \
  "$test_script"
done

echo "API test suite passed"
