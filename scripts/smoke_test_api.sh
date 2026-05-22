#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

if [[ -f "$ROOT_DIR/.env" ]]; then
  # shellcheck disable=SC1091
  set -a
  source "$ROOT_DIR/.env"
  set +a
fi

BASE_URL="${BASE_URL:-http://127.0.0.1:8000}"
AUTH_ENDPOINT="${BASE_URL}/api/auth_google.php"
REFRESH_ENDPOINT="${BASE_URL}/api/refresh.php"
RECORD_ENDPOINT="${BASE_URL}/api/record_entry.php"

SCAN_USER_ID="${SCAN_USER_ID:-visitor-123}"
SCAN_DATE="${SCAN_DATE:-$(date +%F)}"
SCAN_TIME="${SCAN_TIME:-$(date +%T)}"

ACCESS_TOKEN="${ACCESS_TOKEN:-}"
REFRESH_TOKEN="${REFRESH_TOKEN:-}"
GOOGLE_ID_TOKEN="${GOOGLE_ID_TOKEN:-}"

require_cmd() {
  if ! command -v "$1" >/dev/null 2>&1; then
    echo "Missing required command: $1" >&2
    exit 1
  fi
}

require_cmd curl
require_cmd jq

print_header() {
  echo
  echo "========== $1 =========="
}

call_auth() {
  if [[ -z "$GOOGLE_ID_TOKEN" ]]; then
    echo "Skipping auth step: GOOGLE_ID_TOKEN is not set."
    return
  fi

  print_header "Auth Google"
  local response
  response="$(curl -sS -X POST "$AUTH_ENDPOINT" -H "Authorization: Bearer $GOOGLE_ID_TOKEN")"
  echo "$response" | jq

  local ok
  ok="$(echo "$response" | jq -r '.ok // false')"
  if [[ "$ok" == "true" ]]; then
    ACCESS_TOKEN="$(echo "$response" | jq -r '.data.access_token // ""')"
    REFRESH_TOKEN="$(echo "$response" | jq -r '.data.refresh_token // ""')"
    echo "Auth step passed. Access and refresh tokens captured."
  else
    echo "Auth step failed."
  fi
}

call_refresh() {
  if [[ -z "$REFRESH_TOKEN" ]]; then
    echo "Skipping refresh step: REFRESH_TOKEN is not set."
    return
  fi

  print_header "Refresh"
  local response
  response="$(curl -sS -X POST "$REFRESH_ENDPOINT" -H "Content-Type: application/json" -d "{\"refresh_token\":\"$REFRESH_TOKEN\"}")"
  echo "$response" | jq

  local ok
  ok="$(echo "$response" | jq -r '.ok // false')"
  if [[ "$ok" == "true" ]]; then
    ACCESS_TOKEN="$(echo "$response" | jq -r '.data.access_token // ""')"
    REFRESH_TOKEN="$(echo "$response" | jq -r '.data.refresh_token // ""')"
    echo "Refresh step passed. Tokens rotated and updated."
  else
    echo "Refresh step failed."
  fi
}

call_record_entry() {
  if [[ -z "$ACCESS_TOKEN" ]]; then
    echo "Skipping record entry step: ACCESS_TOKEN is not set."
    return
  fi

  print_header "Record Entry"
  local payload
  payload="$(jq -nc --arg userId "$SCAN_USER_ID" --arg date "$SCAN_DATE" --arg time "$SCAN_TIME" '{userId:$userId, date:$date, time:$time}')"

  local response
  response="$(curl -sS -X POST "$RECORD_ENDPOINT" -H "Authorization: Bearer $ACCESS_TOKEN" -H "Content-Type: application/json" -d "$payload")"
  echo "$response" | jq

  local ok
  ok="$(echo "$response" | jq -r '.ok // false')"
  if [[ "$ok" == "true" ]]; then
    echo "Record entry step passed."
  else
    echo "Record entry step failed."
  fi
}

print_header "Configuration"
echo "BASE_URL=$BASE_URL"
echo "GOOGLE_ID_TOKEN set? $( [[ -n "$GOOGLE_ID_TOKEN" ]] && echo yes || echo no )"
echo "REFRESH_TOKEN set? $( [[ -n "$REFRESH_TOKEN" ]] && echo yes || echo no )"
echo "ACCESS_TOKEN set? $( [[ -n "$ACCESS_TOKEN" ]] && echo yes || echo no )"

call_auth
call_refresh
call_record_entry

print_header "Done"
echo "Smoke test flow complete."
