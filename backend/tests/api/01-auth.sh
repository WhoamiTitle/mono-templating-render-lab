#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=tests/api/lib.sh
source "$SCRIPT_DIR/lib.sh"

api_test_bootstrap
trap api_test_cleanup EXIT

EMAIL="$(unique_email)"
PASSWORD="Passw0rd!123"

echo "[01-auth] register"
REGISTER_JSON="$(api_request_log "auth.register.response" POST "/users" "201" "{\"email\":\"$EMAIL\",\"password\":\"$PASSWORD\"}")"
assert_non_empty "$(json_value "$REGISTER_JSON" '.userId')" "register.userId"

echo "[01-auth] header-only access should fail"
api_request_log "auth.header_only_protected.response" GET "/templates" "401" "" -H "x-actor-id: fake-user" >/dev/null

echo "[01-auth] login"
LOGIN_JSON="$(api_request_log "auth.login.response" POST "/sessions" "200" "{\"email\":\"$EMAIL\",\"password\":\"$PASSWORD\"}")"
ACTOR_ID="$(json_value "$LOGIN_JSON" '.userId')"
assert_non_empty "$ACTOR_ID" "login.userId"
AUTH_HEADER="x-actor-id: $ACTOR_ID"

if ! grep -q 'session_id' "$COOKIE_JAR"; then
  echo "Assertion failed: session_id cookie is not set"
  exit 1
fi

echo "[01-auth] wrong password should fail"
api_request_log "auth.login_failed.response" POST "/sessions" "401" "{\"email\":\"$EMAIL\",\"password\":\"wrong-password\"}" >/dev/null

echo "[01-auth] logout"
LOGOUT_JSON="$(api_request_log "auth.logout.response" DELETE "/sessions/current" "204" "" -H "$AUTH_HEADER")"
print_json "auth.logout.response.body" "$LOGOUT_JSON"

echo "[01-auth] ok"
