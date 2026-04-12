#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=tests/api/lib.sh
source "$SCRIPT_DIR/lib.sh"

api_test_bootstrap
trap api_test_cleanup EXIT

echo "[05-state] save"
SAVE_JSON="$(api_request_log "state.save.response" POST "/state" "201" \
  '{"slotA":{"engineId":"handlebars","code":"Hello {{name}}"},"slotB":{"engineId":"pug","code":"p Hello #{name}"},"json":"{\"name\":\"State\"}"}')"
STATE_ID="$(json_value "$SAVE_JSON" '.id')"
assert_non_empty "$STATE_ID" "state.id"

echo "[05-state] load"
LOAD_JSON="$(api_request_log "state.get.response" GET "/state/$STATE_ID" "200" "")"
assert_json_equals "$LOAD_JSON" '.slotA.engineId' 'handlebars'
assert_json_equals "$LOAD_JSON" '.slotB.engineId' 'pug'
assert_json_equals "$LOAD_JSON" '.json' '{"name":"State"}'

echo "[05-state] not found"
api_request_log "state.get.not_found.response" GET "/state/not-exists" "404" "" >/dev/null

echo "[05-state] ok"
