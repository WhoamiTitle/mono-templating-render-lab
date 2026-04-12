#!/usr/bin/env bash
set -euo pipefail

API_TESTS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$API_TESTS_DIR/../.." && pwd)"
DEFAULT_COMPOSE_FILE="$REPO_ROOT/../docker-compose.backend.yml"
if [[ -f "$REPO_ROOT/docker-compose.backend.yml" ]]; then
  DEFAULT_COMPOSE_FILE="$REPO_ROOT/docker-compose.backend.yml"
fi
COMPOSE_FILE="${COMPOSE_FILE:-$DEFAULT_COMPOSE_FILE}"
BASE_URL="${BASE_URL:-http://127.0.0.1:8080}"
API_TEST_NO_STACK_MANAGEMENT="${API_TEST_NO_STACK_MANAGEMENT:-0}"
API_TEST_KEEP_STACK="${API_TEST_KEEP_STACK:-0}"

COOKIE_JAR="${COOKIE_JAR:-}"
STACK_STARTED_BY_LIB=0

ensure_tools() {
  local missing=0

  for cmd in docker curl jq python3; do
    if ! command -v "$cmd" >/dev/null 2>&1; then
      echo "Missing required command: $cmd"
      missing=1
    fi
  done

  if [[ "$missing" -ne 0 ]]; then
    exit 1
  fi
}

init_cookie_jar() {
  if [[ -n "$COOKIE_JAR" ]]; then
    return
  fi

  COOKIE_JAR="$(mktemp)"
  export COOKIE_JAR
}

cleanup_cookie_jar() {
  if [[ -n "${COOKIE_JAR:-}" && -f "${COOKIE_JAR}" ]]; then
    rm -f "${COOKIE_JAR}"
  fi
}

start_stack() {
  if [[ "$API_TEST_NO_STACK_MANAGEMENT" == "1" ]]; then
    return
  fi

  echo "Starting backend stack..."
  docker compose -f "$COMPOSE_FILE" up -d postgres backend >/dev/null
  STACK_STARTED_BY_LIB=1
}

wait_backend() {
  for _ in $(seq 1 60); do
    local code
    code="$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/not-found" || true)"
    if [[ "$code" == "404" || "$code" == "401" || "$code" == "405" ]]; then
      return 0
    fi
    sleep 1
  done

  echo "Backend did not become ready in time: $BASE_URL"
  exit 1
}

stop_stack() {
  if [[ "$API_TEST_NO_STACK_MANAGEMENT" == "1" ]]; then
    return
  fi

  if [[ "$STACK_STARTED_BY_LIB" != "1" ]]; then
    return
  fi

  if [[ "$API_TEST_KEEP_STACK" == "1" ]]; then
    echo "Keeping backend stack running (API_TEST_KEEP_STACK=1)."
    return
  fi

  docker compose -f "$COMPOSE_FILE" down -v >/dev/null
}

api_test_bootstrap() {
  ensure_tools
  init_cookie_jar
  start_stack
  wait_backend
}

api_test_cleanup() {
  cleanup_cookie_jar
  stop_stack
}

api_request() {
  local method="$1"
  local path="$2"
  local expected_status="$3"
  local body="${4-}"
  local extra_args=()

  shift 4 || true
  if [[ $# -gt 0 ]]; then
    extra_args=("$@")
  fi

  local response
  if [[ -n "$body" ]]; then
    if [[ ${#extra_args[@]} -gt 0 ]]; then
      response="$(curl -sS -w $'\n%{http_code}' \
        -X "$method" "$BASE_URL$path" \
        -H "Content-Type: application/json" \
        -b "$COOKIE_JAR" -c "$COOKIE_JAR" \
        "${extra_args[@]}" \
        --data "$body")"
    else
      response="$(curl -sS -w $'\n%{http_code}' \
        -X "$method" "$BASE_URL$path" \
        -H "Content-Type: application/json" \
        -b "$COOKIE_JAR" -c "$COOKIE_JAR" \
        --data "$body")"
    fi
  else
    if [[ ${#extra_args[@]} -gt 0 ]]; then
      response="$(curl -sS -w $'\n%{http_code}' \
        -X "$method" "$BASE_URL$path" \
        -b "$COOKIE_JAR" -c "$COOKIE_JAR" \
        "${extra_args[@]}")"
    else
      response="$(curl -sS -w $'\n%{http_code}' \
        -X "$method" "$BASE_URL$path" \
        -b "$COOKIE_JAR" -c "$COOKIE_JAR")"
    fi
  fi

  local status body_out
  status="$(printf '%s' "$response" | tail -n 1)"
  body_out="$(printf '%s' "$response" | sed '$d')"

  if [[ "$status" != "$expected_status" ]]; then
    echo "Request failed: $method $path (expected $expected_status, got $status)"
    echo "Response body:"
    echo "$body_out"
    exit 1
  fi

  printf '%s' "$body_out"
}

api_request_log() {
  local label="$1"
  shift

  local response_json
  response_json="$(api_request "$@")"
  print_json "$label" "$response_json"

  printf '%s' "$response_json"
}

json_value() {
  local json="$1"
  local expression="$2"
  printf '%s' "$json" | jq -r "$expression"
}

print_json() {
  local label="$1"
  local json="${2-}"

  echo "[$label]" >&2

  if [[ -z "$json" ]]; then
    echo "<empty>" >&2
    return
  fi

  if printf '%s' "$json" | jq -e . >/dev/null 2>&1; then
    printf '%s' "$json" | jq . >&2
    return
  fi

  echo "$json" >&2
}

assert_non_empty() {
  local value="$1"
  local field_name="$2"

  if [[ -z "$value" || "$value" == "null" ]]; then
    echo "Assertion failed: $field_name is empty"
    exit 1
  fi
}

assert_json_equals() {
  local json="$1"
  local expression="$2"
  local expected="$3"
  local actual

  actual="$(json_value "$json" "$expression")"
  if [[ "$actual" != "$expected" ]]; then
    echo "Assertion failed for $expression"
    echo "Expected: $expected"
    echo "Actual:   $actual"
    exit 1
  fi
}

assert_json_number_gte() {
  local json="$1"
  local expression="$2"
  local expected_min="$3"
  local actual

  actual="$(json_value "$json" "$expression")"
  if ! [[ "$actual" =~ ^-?[0-9]+$ ]]; then
    echo "Assertion failed: $expression does not evaluate to integer: $actual"
    exit 1
  fi

  if (( actual < expected_min )); then
    echo "Assertion failed for $expression: expected >= $expected_min, got $actual"
    exit 1
  fi
}

unique_email() {
  printf 'api_%s_%s@example.com' "$(date +%s)" "$RANDOM"
}

register_user() {
  local email="$1"
  local password="$2"

  api_request_log "auth.register.response" POST "/users" "201" "{\"email\":\"$email\",\"password\":\"$password\"}" >/dev/null
}

login_user() {
  local email="$1"
  local password="$2"
  local login_json

  login_json="$(api_request_log "auth.login.response" POST "/sessions" "200" "{\"email\":\"$email\",\"password\":\"$password\"}")"
  ACTOR_ID="$(json_value "$login_json" '.userId')"
  AUTH_HEADER="x-actor-id: $ACTOR_ID"

  assert_non_empty "$ACTOR_ID" "login.userId"

  if ! grep -q 'session_id' "$COOKIE_JAR"; then
    echo "Assertion failed: session_id cookie is not set"
    exit 1
  fi
}

register_and_login_user() {
  local email="${1:-$(unique_email)}"
  local password="${2:-Passw0rd!123}"

  register_user "$email" "$password"
  login_user "$email" "$password"
}

measure_work_duration_ms() {
  local work_units="$1"

  python3 - "$work_units" <<'PY'
import sys
import time

work_units = int(sys.argv[1])
start = time.perf_counter()
acc = 0
for i in range(work_units):
    acc += ((i * 31) % 97)
elapsed_ms = int((time.perf_counter() - start) * 1000)
print(max(1, elapsed_ms))
PY
}

build_benchmark_metrics_json() {
  local iterations_n="$1"
  local work_units="$2"
  local output_text="$3"

  python3 - "$iterations_n" "$work_units" "$output_text" <<'PY'
import json
import math
import sys
import time

iterations_n = int(sys.argv[1])
work_units = int(sys.argv[2])
output_text = sys.argv[3]

samples = []
for _ in range(iterations_n):
    start = time.perf_counter()
    acc = 0
    for i in range(work_units):
        acc += ((i * 17) % 113)
    elapsed_ms = int((time.perf_counter() - start) * 1000)
    samples.append(max(1, elapsed_ms))

avg_ms = sum(samples) / len(samples)
sorted_samples = sorted(samples)
p95_index = max(0, min(len(sorted_samples) - 1, math.ceil(0.95 * len(sorted_samples)) - 1))

payload = {
    "samplesMs": samples,
    "avgMs": round(avg_ms, 3),
    "minMs": min(samples),
    "maxMs": max(samples),
    "p95Ms": sorted_samples[p95_index],
    "outputBytes": len(output_text.encode("utf-8")),
}

print(json.dumps(payload, separators=(",", ":")))
PY
}
