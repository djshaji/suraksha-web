#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

if [[ -f "$ROOT_DIR/.env" ]]; then
  # shellcheck disable=SC1091
  set -a
  source "$ROOT_DIR/.env"
  set +a
fi

missing=()
for key in DB_HOST DB_PORT DB_NAME DB_USER DB_PASS; do
  if [[ -z "${!key:-}" ]]; then
    missing+=("$key")
  fi
done

if ! command -v mysql >/dev/null 2>&1; then
  echo "mysql client not found. Install mysql client first." >&2
  exit 1
fi

if [[ ${#missing[@]} -gt 0 ]]; then
  echo "Missing required environment variables: ${missing[*]}" >&2
  exit 1
fi

mysql \
  --host="$DB_HOST" \
  --port="$DB_PORT" \
  --user="$DB_USER" \
  --password="$DB_PASS" \
  "$DB_NAME" < "$ROOT_DIR/sql/schema.sql"

echo "Schema applied successfully."