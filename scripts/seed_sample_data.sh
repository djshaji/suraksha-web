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

mysql \
  --host="$DB_HOST" \
  --port="$DB_PORT" \
  --user="$DB_USER" \
  --password="$DB_PASS" \
  "$DB_NAME" < "$ROOT_DIR/sql/sample_data.sql"

echo "Sample data seeded successfully."