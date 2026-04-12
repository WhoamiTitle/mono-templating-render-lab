#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=tests/api/lib.sh
source "$SCRIPT_DIR/lib.sh"

api_test_bootstrap
trap api_test_cleanup EXIT

register_and_login_user

TEMPLATE_JSON="$(api_request_log "benchmark.template.register.response" POST "/templates" "201" \
  '{"name":"Benchmark Template","engineType":"handlebars","templateBody":"Hello {{name}}"}' \
  -H "$AUTH_HEADER")"
TEMPLATE_ID="$(json_value "$TEMPLATE_JSON" '.templateId')"
assert_non_empty "$TEMPLATE_ID" "benchmark.templateId"

ITERATIONS_N=7

echo "[04-benchmark] success run"
BENCH_SUCCESS_JSON="$(api_request_log "benchmark.start_success.response" POST "/benchmark-runs" "201" \
  "{\"templateId\":\"$TEMPLATE_ID\",\"context\":{\"name\":\"Bench\"},\"iterationsN\":$ITERATIONS_N}" \
  -H "$AUTH_HEADER")"
BENCH_SUCCESS_ID="$(json_value "$BENCH_SUCCESS_JSON" '.benchmarkRunId')"
assert_non_empty "$BENCH_SUCCESS_ID" "benchmark.successRunId"

BENCH_SUCCESS_METRICS_JSON="$(build_benchmark_metrics_json "$ITERATIONS_N" 260000 'Hello Bench')"
print_json "benchmark.metrics.generated" "$BENCH_SUCCESS_METRICS_JSON"

COMPLETE_BENCH_SUCCESS_JSON="$(api_request_log "benchmark.complete_success.response" POST "/benchmark-runs/$BENCH_SUCCESS_ID/success" "200" \
  "$BENCH_SUCCESS_METRICS_JSON" \
  -H "$AUTH_HEADER")"
assert_json_equals "$COMPLETE_BENCH_SUCCESS_JSON" '.status' 'success'

echo "[04-benchmark] failure run"
BENCH_FAILURE_JSON="$(api_request_log "benchmark.start_failure.response" POST "/benchmark-runs" "201" \
  "{\"templateId\":\"$TEMPLATE_ID\",\"context\":{\"name\":\"BenchFail\"},\"iterationsN\":3}" \
  -H "$AUTH_HEADER")"
BENCH_FAILURE_ID="$(json_value "$BENCH_FAILURE_JSON" '.benchmarkRunId')"
assert_non_empty "$BENCH_FAILURE_ID" "benchmark.failureRunId"

COMPLETE_BENCH_FAILURE_JSON="$(api_request_log "benchmark.complete_failure.response" POST "/benchmark-runs/$BENCH_FAILURE_ID/failure" "200" \
  '{"errorCode":"BENCH_ERR","errorMessage":"failed bench"}' \
  -H "$AUTH_HEADER")"
assert_json_equals "$COMPLETE_BENCH_FAILURE_JSON" '.status' 'failed'

echo "[04-benchmark] query checks"
GET_SUCCESS_JSON="$(api_request_log "benchmark.get_success.response" GET "/benchmark-runs/$BENCH_SUCCESS_ID" "200" "" -H "$AUTH_HEADER")"
assert_json_equals "$GET_SUCCESS_JSON" '.status' 'success'
assert_json_equals "$GET_SUCCESS_JSON" '.iterationsN|tostring' "$ITERATIONS_N"

GET_FAILURE_JSON="$(api_request_log "benchmark.get_failure.response" GET "/benchmark-runs/$BENCH_FAILURE_ID" "200" "" -H "$AUTH_HEADER")"
assert_json_equals "$GET_FAILURE_JSON" '.status' 'failed'

LIST_JSON="$(api_request_log "benchmark.list.response" GET "/benchmark-runs?templateId=$TEMPLATE_ID" "200" "" -H "$AUTH_HEADER")"
assert_json_number_gte "$LIST_JSON" '.items | length' 2

echo "[04-benchmark] ok"
