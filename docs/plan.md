# SPMR Suraksha Backend Plan

This document defines an implementation-ready backend plan for Android client authentication and visitor entry logging.

## 1. Scope

Build secure PHP 8.1+ JSON APIs that:
1. Verify Google ID token from Android.
2. Authorize guard from database.
3. Issue short-lived JWT access token plus DB-backed refresh token.
4. Refresh access tokens.
5. Accept protected visitor QR scan logs.

## 2. Tech Stack

- Language: PHP 8.1+
- Dependencies: `google/apiclient`, `firebase/php-jwt`
- Database: MySQL or MariaDB with PDO + prepared statements
- Response format: JSON for all outcomes

## 3. Environment Variables

Required runtime configuration:

```env
APP_ENV=production
APP_DEBUG=0

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=suraksha
DB_USER=suraksha_user
DB_PASS=change_me

GOOGLE_CLIENT_ID=your-google-web-client-id.apps.googleusercontent.com

JWT_SECRET=replace_with_long_random_secret
JWT_ISSUER=spmr-suraksha-api
JWT_AUDIENCE=spmr-suraksha-android
ACCESS_TOKEN_TTL_SECONDS=900
REFRESH_TOKEN_TTL_SECONDS=604800
```

Notes:
- Keep secrets out of version control.
- In production, set secure file permissions for config and logs.

## 4. Target File Structure

```text
api/
    auth_google.php
    refresh.php
    record_entry.php
lib/
    db.php
    auth.php
    response.php
    logger.php
    config.php
sql/
    schema.sql
```

## 5. Database Schema (sql/schema.sql)

```sql
CREATE TABLE IF NOT EXISTS guards (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(191) NOT NULL UNIQUE,
    google_sub_id VARCHAR(64) NOT NULL UNIQUE,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_guards_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS refresh_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    guard_id BIGINT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_refresh_guard FOREIGN KEY (guard_id) REFERENCES guards(id) ON DELETE CASCADE,
    INDEX idx_refresh_guard (guard_id),
    INDEX idx_refresh_expires (expires_at),
    UNIQUE KEY uq_refresh_hash (token_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS visitor_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    visitor_userid VARCHAR(120) NOT NULL,
    guard_id BIGINT UNSIGNED NOT NULL,
    log_date DATE NOT NULL,
    log_time TIME NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_visitor_guard FOREIGN KEY (guard_id) REFERENCES guards(id) ON DELETE RESTRICT,
    INDEX idx_visitor_guard (guard_id),
    INDEX idx_visitor_date (log_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 6. Common Response Contract

Success payload shape:

```json
{
    "ok": true,
    "data": {},
    "request_id": "uuid-or-unique-id"
}
```

Error payload shape:

```json
{
    "ok": false,
    "error": {
        "code": "AUTH_INVALID_TOKEN",
        "message": "Invalid or expired token"
    },
    "request_id": "uuid-or-unique-id"
}
```

Status code policy:
- `200`: success
- `400`: malformed request body, missing fields, invalid formats
- `401`: invalid or expired access token, unauthorized guard
- `403`: invalid or expired refresh token
- `500`: server or DB errors

## 7. Shared Helpers

### 7.1 lib/config.php

Responsibilities:
- Read environment values.
- Provide defaults only for non-secret values.
- Throw for missing required secrets in production.

### 7.2 lib/db.php

Responsibilities:
- Create PDO connection via env values.
- Set `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`.
- Set `PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC`.
- Set `PDO::ATTR_EMULATE_PREPARES => false`.

Suggested implementation:

```php
<?php
declare(strict_types=1);

final class DB
{
        private static ?PDO $pdo = null;

        public static function conn(): PDO
        {
                if (self::$pdo instanceof PDO) {
                        return self::$pdo;
                }

                $host = getenv('DB_HOST') ?: '127.0.0.1';
                $port = getenv('DB_PORT') ?: '3306';
                $name = getenv('DB_NAME') ?: '';
                $user = getenv('DB_USER') ?: '';
                $pass = getenv('DB_PASS') ?: '';

                $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $name);

                self::$pdo = new PDO($dsn, $user, $pass, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                ]);

                return self::$pdo;
        }
}
```

### 7.3 lib/response.php

Responsibilities:
- Set `Content-Type: application/json`.
- Emit consistent success and error payloads.
- Attach `request_id` for traceability.

### 7.4 lib/logger.php

Responsibilities:
- Log server-side exceptions and context.
- Never leak internal errors to client response body.

### 7.5 lib/auth.php

Responsibilities:
- Extract bearer token from `Authorization` header.
- Issue JWT (`HS256`) with claims: `iss`, `aud`, `iat`, `exp`, `sub`, `guard_id`.
- Verify JWT and return decoded claims.
- Create refresh token (`bin2hex(random_bytes(32))`) and compute hash (`hash('sha256', $token)`).

## 8. Endpoint Specifications

## 8.1 POST /api/auth_google.php

Input:
- Header: `Authorization: Bearer <google_id_token>`

Flow:
1. Parse bearer token from header.
2. Verify token via Google client using `GOOGLE_CLIENT_ID`.
3. Extract `sub` and `email`.
4. Query `guards` by `google_sub_id` or `email` where `status='active'`.
5. If no active guard, return 401.
6. Generate access token (TTL: 15 minutes).
7. Generate refresh token, hash it, store with 7-day expiry.
8. Return both tokens and expiry fields.

Success response example:

```json
{
    "ok": true,
    "data": {
        "access_token": "...",
        "token_type": "Bearer",
        "expires_in": 900,
        "refresh_token": "..."
    },
    "request_id": "..."
}
```

## 8.2 POST /api/refresh.php

Input:
- JSON body: `{ "refresh_token": "<token>" }`

Flow:
1. Parse JSON body and validate field presence.
2. Hash received refresh token.
3. Find matching row in `refresh_tokens` where `expires_at > NOW()`.
4. If missing or expired, return 403.
5. Rotate refresh token:
     - delete old row,
     - create new refresh token and insert new hash with new expiry.
6. Issue new JWT access token for same `guard_id`.
7. Return new access token and new refresh token.

Error behavior:
- Invalid token or expired token must return 403 and not disclose details.

## 8.3 POST /api/record_entry.php

Input:
- Header: `Authorization: Bearer <access_jwt>`
- JSON body:

```json
{
    "userId": "visitor-123",
    "date": "2026-05-22",
    "time": "10:30:00"
}
```

Flow:
1. Verify access JWT.
2. Extract `guard_id` from claims.
3. Validate JSON body fields and date/time format.
4. Insert row into `visitor_logs` with prepared statement.
5. Return success payload, include mocked visitor name.

Success response example:

```json
{
    "ok": true,
    "data": {
        "message": "Entry recorded successfully",
        "visitor": {
            "userId": "visitor-123",
            "name": "John Doe"
        }
    },
    "request_id": "..."
}
```

## 9. Security Requirements

Mandatory controls:
1. Use prepared statements for every SQL call.
2. Never store plaintext refresh tokens.
3. Verify JWT `iss` and `aud` claims.
4. Keep access token TTL short (900s).
5. Revoke/rotate refresh token on each refresh.
6. Return generic auth failures to clients.
7. Log server-side errors with request id.

Recommended controls:
1. Add rate limiting for auth and refresh endpoints.
2. Restrict CORS origins to Android app backend domain only.
3. Add periodic cleanup job for expired refresh tokens.

## 10. Implementation Checklist

1. Install dependencies:

```bash
cd lib
composer require google/apiclient firebase/php-jwt
```

2. Create `sql/schema.sql` and apply it to database.
3. Implement shared helpers: `config.php`, `db.php`, `logger.php`, `response.php`, `auth.php`.
4. Implement endpoints in this order: `auth_google.php`, `refresh.php`, `record_entry.php`.
5. Test error paths:
     - missing auth header,
     - malformed JSON,
     - expired JWT,
     - expired refresh token,
     - inactive guard.
6. Verify all responses follow the common contract.

## 11. Minimum Test Matrix

1. Auth success with valid Google token and active guard.
2. Auth failure when guard not in table.
3. Refresh success with valid refresh token.
4. Refresh failure on replayed token after rotation.
5. Record entry success with valid JWT.
6. Record entry failure with expired JWT.
7. Validation failure for invalid date/time format.