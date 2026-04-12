#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=tests/api/lib.sh
source "$SCRIPT_DIR/lib.sh"

api_test_bootstrap
trap api_test_cleanup EXIT

register_and_login_user

TEMPLATE_JSON="$(api_request_log "render.template.register.response" POST "/templates" "201" \
  '{"name":"Render Template","engineType":"handlebars","templateBody":"Hello {{name}}"}' \
  -H "$AUTH_HEADER")"
TEMPLATE_ID="$(json_value "$TEMPLATE_JSON" '.templateId')"
assert_non_empty "$TEMPLATE_ID" "render.templateId"

echo "[03-render-runs] success run"
RUN_SUCCESS_JSON="$(api_request_log "render.start_success.response" POST "/render-runs" "201" \
  "{\"templateId\":\"$TEMPLATE_ID\",\"context\":{\"name\":\"Alice\"}}" \
  -H "$AUTH_HEADER")"
RUN_SUCCESS_ID="$(json_value "$RUN_SUCCESS_JSON" '.runId')"
assert_non_empty "$RUN_SUCCESS_ID" "render.successRunId"

SUCCESS_DURATION_MS="$(measure_work_duration_ms 450000)"
COMPLETE_SUCCESS_JSON="$(api_request_log "render.complete_success.response" POST "/render-runs/$RUN_SUCCESS_ID/success" "200" \
  "{\"durationMs\":$SUCCESS_DURATION_MS,\"outputText\":\"Hello Alice\"}" \
  -H "$AUTH_HEADER")"
assert_json_equals "$COMPLETE_SUCCESS_JSON" '.status' 'success'

echo "[03-render-runs] failure run"
RUN_FAILURE_JSON="$(api_request_log "render.start_failure.response" POST "/render-runs" "201" \
  "{\"templateId\":\"$TEMPLATE_ID\",\"context\":{\"name\":\"Broken\"}}" \
  -H "$AUTH_HEADER")"
RUN_FAILURE_ID="$(json_value "$RUN_FAILURE_JSON" '.runId')"
assert_non_empty "$RUN_FAILURE_ID" "render.failureRunId"

FAIL_DURATION_MS="$(measure_work_duration_ms 280000)"
COMPLETE_FAILURE_JSON="$(api_request_log "render.complete_failure.response" POST "/render-runs/$RUN_FAILURE_ID/failure" "200" \
  "{\"durationMs\":$FAIL_DURATION_MS,\"errorCode\":\"RENDER_ERR\",\"errorMessage\":\"boom\"}" \
  -H "$AUTH_HEADER")"
assert_json_equals "$COMPLETE_FAILURE_JSON" '.status' 'failed'

echo "[03-render-runs] query checks"
GET_SUCCESS_JSON="$(api_request_log "render.get_success.response" GET "/render-runs/$RUN_SUCCESS_ID" "200" "" -H "$AUTH_HEADER")"
assert_json_equals "$GET_SUCCESS_JSON" '.status' 'success'
assert_json_equals "$GET_SUCCESS_JSON" '.outputText' 'Hello Alice'
assert_json_equals "$GET_SUCCESS_JSON" '.durationMs|tostring' "$SUCCESS_DURATION_MS"

GET_FAILURE_JSON="$(api_request_log "render.get_failure.response" GET "/render-runs/$RUN_FAILURE_ID" "200" "" -H "$AUTH_HEADER")"
assert_json_equals "$GET_FAILURE_JSON" '.status' 'failed'
assert_json_equals "$GET_FAILURE_JSON" '.durationMs|tostring' "$FAIL_DURATION_MS"

LIST_JSON="$(api_request_log "render.list.response" GET "/render-runs?templateId=$TEMPLATE_ID" "200" "" -H "$AUTH_HEADER")"
assert_json_number_gte "$LIST_JSON" '.items | length' 2

FAILURES_JSON="$(api_request_log "render.failures_recent.response" GET "/render-runs/failures/recent?limit=5" "200" "" -H "$AUTH_HEADER")"
assert_json_number_gte "$FAILURES_JSON" '.items | length' 1

STATS_JSON="$(api_request_log "render.template_stats.response" GET "/templates/$TEMPLATE_ID/stats" "200" "" -H "$AUTH_HEADER")"
assert_json_equals "$STATS_JSON" '.totalRuns|tostring' '2'
assert_json_equals "$STATS_JSON" '.successRuns|tostring' '1'
assert_json_equals "$STATS_JSON" '.failedRuns|tostring' '1'

echo "[03-render-runs] ok"
