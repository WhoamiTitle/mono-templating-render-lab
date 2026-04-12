#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=tests/api/lib.sh
source "$SCRIPT_DIR/lib.sh"

api_test_bootstrap
trap api_test_cleanup EXIT

register_and_login_user

echo "[02-templates] register template"
REGISTER_JSON="$(api_request_log "templates.register.response" POST "/templates" "201" \
  '{"name":"API Template","engineType":"handlebars","templateBody":"Hello {{name}}","isPublic":true}' \
  -H "$AUTH_HEADER")"
TEMPLATE_ID="$(json_value "$REGISTER_JSON" '.templateId')"
assert_non_empty "$TEMPLATE_ID" "template.templateId"
assert_json_equals "$REGISTER_JSON" '.isPublic|tostring' 'true'

echo "[02-templates] update body"
UPDATE_JSON="$(api_request_log "templates.update.response" PUT "/templates/$TEMPLATE_ID" "200" \
  '{"code":"Hello {{name}} from updated body"}' \
  -H "$AUTH_HEADER")"
assert_non_empty "$(json_value "$UPDATE_JSON" '.updatedAt')" "templates.update.updatedAt"

echo "[02-templates] clone"
CLONE_JSON="$(api_request_log "templates.clone.response" POST "/templates/$TEMPLATE_ID/clone" "201" '{}' -H "$AUTH_HEADER")"
CLONE_ID="$(json_value "$CLONE_JSON" '.templateId')"
assert_non_empty "$CLONE_ID" "templates.clone.templateId"
assert_json_equals "$CLONE_JSON" '.isPublic|tostring' 'false'

echo "[02-templates] get/list/public/stats"
GET_JSON="$(api_request_log "templates.get.response" GET "/templates/$TEMPLATE_ID" "200" "" -H "$AUTH_HEADER")"
assert_json_equals "$GET_JSON" '.templateId' "$TEMPLATE_ID"
assert_json_equals "$GET_JSON" '.templateBody' 'Hello {{name}} from updated body'
assert_json_equals "$GET_JSON" '.isPublic|tostring' 'true'
assert_json_equals "$GET_JSON" '.isActive|tostring' 'true'

LIST_JSON="$(api_request_log "templates.list.response" GET "/templates?isActive=true" "200" "" -H "$AUTH_HEADER")"
assert_json_number_gte "$LIST_JSON" '.items | length' 2

PUBLIC_JSON="$(api_request_log "templates.public.response" GET "/templates/public" "200" "")"
assert_json_number_gte "$PUBLIC_JSON" '.items | length' 1

STATS_JSON="$(api_request_log "templates.stats.response" GET "/templates/$TEMPLATE_ID/stats" "200" "" -H "$AUTH_HEADER")"
assert_json_equals "$STATS_JSON" '.templateId' "$TEMPLATE_ID"

echo "[02-templates] delete(alias deactivate)"
DELETE_JSON="$(api_request_log "templates.delete.response" DELETE "/templates/$TEMPLATE_ID" "200" "" -H "$AUTH_HEADER")"
assert_json_equals "$DELETE_JSON" '.isActive|tostring' 'false'

echo "[02-templates] ok"
