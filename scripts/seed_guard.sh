#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

if [[ -f "$ROOT_DIR/.env" ]]; then
  # shellcheck disable=SC1091
  set -a
  source "$ROOT_DIR/.env"
  set +a
fi

require_var() {
  local key="$1"
  if [[ -z "${!key:-}" ]]; then
    echo "Missing required environment variable: $key" >&2
    exit 1
  fi
}

if ! command -v mysql >/dev/null 2>&1; then
  echo "mysql client not found. Install mysql client first." >&2
  exit 1
fi

require_var DB_HOST
require_var DB_PORT
require_var DB_NAME
require_var DB_USER
require_var DB_PASS

TEST_GUARD_NAME="${TEST_GUARD_NAME:-Test Guard}"
TEST_GUARD_EMAIL="${TEST_GUARD_EMAIL:-guard@example.com}"
TEST_GUARD_GOOGLE_SUB_ID="${TEST_GUARD_GOOGLE_SUB_ID:-google-sub-id-placeholder}"
TEST_GUARD_STATUS="${TEST_GUARD_STATUS:-active}"

if [[ "$TEST_GUARD_STATUS" != "active" && "$TEST_GUARD_STATUS" != "inactive" ]]; then
  echo "TEST_GUARD_STATUS must be active or inactive" >&2
  exit 1
fi

name_sql="$(printf "%s" "$TEST_GUARD_NAME" | sed "s/'/''/g")"
email_sql="$(printf "%s" "$TEST_GUARD_EMAIL" | sed "s/'/''/g")"
sub_sql="$(printf "%s" "$TEST_GUARD_GOOGLE_SUB_ID" | sed "s/'/''/g")"
status_sql="$(printf "%s" "$TEST_GUARD_STATUS" | sed "s/'/''/g")"

mysql \
  --host="$DB_HOST" \
  --port="$DB_PORT" \
  --user="$DB_USER" \
  --password="$DB_PASS" \
  "$DB_NAME" <<SQL
INSERT INTO guards (name, email, google_sub_id, status)
VALUES ('$name_sql', '$email_sql', '$sub_sql', '$status_sql')
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  status = VALUES(status),
  updated_at = CURRENT_TIMESTAMP;
SQL

echo "Seeded guard: $TEST_GUARD_EMAIL"
