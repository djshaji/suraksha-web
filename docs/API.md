# SPMR Suraksha API Documentation

This document describes the current backend API implemented in this repository, with Android integration guidance and prompt templates for generating an Android client using an AI coding agent.

## 1. API Overview

- Service style: JSON over HTTP
- Auth model: Google ID token exchange -> JWT access token + refresh token
- Access token TTL: configurable (default 900 seconds)
- Refresh token TTL: configurable (default 604800 seconds)
- Primary client: Android app

## 2. Base URL

Use your deployment URL as base.

Examples:

```text
Local: http://127.0.0.1:8000
Production: https://your-domain.example
```

## 3. Common Response Envelope

Success envelope:

```json
{
	"ok": true,
	"data": {},
	"request_id": "string"
}
```

Error envelope:

```json
{
	"ok": false,
	"error": {
		"code": "ERROR_CODE",
		"message": "Human-readable message"
	},
	"request_id": "string"
}
```

Notes:

- request_id is generated per request and can be supplied via X-Request-Id header.
- Method mismatch currently returns status 400 with code REQUEST_METHOD_INVALID.

## 4. Authentication Model

Flow:

1. Android app gets Google ID token from Google Sign-In.
2. App calls POST /api/auth_google.php with Authorization Bearer <google_id_token>.
3. Server verifies token, checks active guard, returns access_token + refresh_token.
4. App calls protected APIs with Authorization Bearer <access_token>.
5. On access token expiry, app calls POST /api/refresh.php with refresh_token.
6. Server rotates refresh token and returns new access + refresh pair.

## 5. Endpoints

## 5.1 Health Check

- Path: /api/health.php
- Method: GET
- Auth: none

Success response example:

```json
{
	"ok": true,
	"data": {
		"service": "suraksha-api",
		"status": "ok",
		"database": "ok",
		"timestamp": "2026-05-22T12:00:00+00:00"
	},
	"request_id": "..."
}
```

Failure response:

- Status: 503
- Code: HEALTH_DB_UNAVAILABLE

## 5.2 Google Token Exchange

- Path: /api/auth_google.php
- Method: POST
- Auth: Authorization Bearer <Google ID token>
- Body: none

Headers:

```text
Authorization: Bearer <google_id_token>
```

Success response example:

```json
{
	"ok": true,
	"data": {
		"access_token": "<jwt>",
		"token_type": "Bearer",
		"expires_in": 900,
		"refresh_token": "<opaque_refresh_token>",
		"refresh_expires_in": 604800,
		"guard": {
			"id": 1,
			"name": "Aarav Singh",
			"email": "aarav.singh@spmr.edu"
		}
	},
	"request_id": "..."
}
```

Known errors:

- 400 REQUEST_INVALID
- 401 AUTH_INVALID_GOOGLE_TOKEN
- 401 AUTH_GUARD_NOT_AUTHORIZED
- 500 SERVER_ERROR

## 5.3 Refresh Access Token

- Path: /api/refresh.php
- Method: POST
- Auth: none (token in JSON body)

Request body:

```json
{
	"refresh_token": "<opaque_refresh_token>"
}
```

Success response example:

```json
{
	"ok": true,
	"data": {
		"access_token": "<new_jwt>",
		"token_type": "Bearer",
		"expires_in": 900,
		"refresh_token": "<new_refresh_token>",
		"refresh_expires_in": 604800
	},
	"request_id": "..."
}
```

Known errors:

- 400 REQUEST_INVALID
- 400 REQUEST_MISSING_REFRESH_TOKEN
- 403 AUTH_REFRESH_FORBIDDEN
- 500 SERVER_ERROR

## 5.4 Record Visitor Entry

- Path: /api/record_entry.php
- Method: POST
- Auth: Authorization Bearer <access_jwt>

Request body:

```json
{
	"userId": "VIS-1001",
	"date": "2026-05-22",
	"time": "10:30:00"
}
```

Validation rules:

- userId required, non-empty string
- date format must be YYYY-MM-DD
- time format must be HH:MM:SS

Success response example:

```json
{
	"ok": true,
	"data": {
		"message": "Entry recorded successfully",
		"visitor": {
			"userId": "VIS-1001",
			"name": "John Doe"
		}
	},
	"request_id": "..."
}
```

Known errors:

- 400 REQUEST_INVALID
- 400 REQUEST_MISSING_FIELDS
- 400 REQUEST_INVALID_DATE
- 400 REQUEST_INVALID_TIME
- 401 AUTH_INVALID_ACCESS_TOKEN
- 500 SERVER_ERROR

## 6. Error Code Reference

| HTTP | Code | Meaning |
|---|---|---|
| 400 | REQUEST_METHOD_INVALID | Wrong HTTP method used |
| 400 | REQUEST_INVALID | Generic request validation error |
| 400 | REQUEST_MISSING_REFRESH_TOKEN | refresh_token missing in refresh call |
| 400 | REQUEST_MISSING_FIELDS | One or more record entry fields missing |
| 400 | REQUEST_INVALID_DATE | date has wrong format |
| 400 | REQUEST_INVALID_TIME | time has wrong format |
| 401 | AUTH_INVALID_GOOGLE_TOKEN | Google token invalid or missing claims |
| 401 | AUTH_GUARD_NOT_AUTHORIZED | Guard does not exist or is not active |
| 401 | AUTH_INVALID_ACCESS_TOKEN | Access token invalid or expired |
| 403 | AUTH_REFRESH_FORBIDDEN | Refresh token invalid or expired |
| 503 | HEALTH_DB_UNAVAILABLE | DB not reachable in health check |
| 500 | SERVER_ERROR | Unexpected server-side failure |

## 7. Android Integration Guidance

Recommended stack:

- Kotlin
- Retrofit + OkHttp
- Kotlinx Serialization or Moshi
- Coroutines
- EncryptedSharedPreferences or Encrypted DataStore for token storage

Token handling strategy:

1. Save access_token, refresh_token, expires_in after auth.
2. Add access token to Authorization header for protected APIs.
3. On 401 from protected endpoints, call refresh endpoint once.
4. If refresh succeeds, retry original request once.
5. If refresh fails with 403, clear session and force sign-in.

Minimal Retrofit interfaces:

```kotlin
interface AuthApi {
		@POST("api/auth_google.php")
		suspend fun authGoogle(
				@Header("Authorization") authorization: String
		): ApiEnvelope<AuthData>

		@POST("api/refresh.php")
		suspend fun refresh(
				@Body body: RefreshRequest
		): ApiEnvelope<RefreshData>
}

interface VisitorApi {
		@POST("api/record_entry.php")
		suspend fun recordEntry(
				@Header("Authorization") authorization: String,
				@Body body: RecordEntryRequest
		): ApiEnvelope<RecordEntryData>
}

@Serializable
data class RefreshRequest(val refresh_token: String)

@Serializable
data class RecordEntryRequest(val userId: String, val date: String, val time: String)
```

## 8. Prompt Templates For Android App Agent

Use these prompts with your coding agent.

## Prompt A: Generate Core Networking Layer

```text
Build a Kotlin Android networking module for SPMR Suraksha API.

Base URL: <YOUR_BASE_URL>

Endpoints:
- POST /api/auth_google.php with Authorization: Bearer <google_id_token>
- POST /api/refresh.php with JSON body {"refresh_token":"..."}
- POST /api/record_entry.php with Authorization: Bearer <access_token> and JSON body {"userId":"...","date":"YYYY-MM-DD","time":"HH:MM:SS"}
- GET /api/health.php

Requirements:
- Retrofit + OkHttp + Coroutines
- Kotlinx Serialization
- Unified response envelope {ok,data,request_id} and error envelope {ok:false,error:{code,message},request_id}
- Add interceptor that injects access token and retries once after refresh on 401
- If refresh returns 403, clear session and expose auth-required state
- Include strongly typed models for all request/response payloads
- No UI, only data/network layer and repository interfaces
```

## Prompt B: Generate Full Android App Skeleton

```text
Create an Android app skeleton for guard workflow with these screens:
1) Google sign-in screen
2) Dashboard screen
3) QR scan + record entry screen

Backend API contract:
- auth_google.php exchanges Google ID token for access+refresh tokens
- refresh.php rotates refresh token and returns new tokens
- record_entry.php requires Bearer access token and logs visitor entry
- health.php checks backend status

Technical constraints:
- Kotlin, MVVM, Hilt DI, Retrofit, Room optional, Coroutines, StateFlow
- Secure token storage using EncryptedSharedPreferences
- Centralized error mapping by API error code
- Automatic token refresh and request retry
- Include unit tests for token refresh logic
- Include README with setup and env/base-url configuration
```

## Prompt C: Generate Postman Test Collection

```text
Generate a Postman collection and environment for SPMR Suraksha API.

Include requests:
- GET /api/health.php
- POST /api/auth_google.php (Bearer Google ID token)
- POST /api/refresh.php
- POST /api/record_entry.php

Collection requirements:
- Store and update access_token and refresh_token in environment variables
- Script record_entry request to use current date and current time in required formats
- Add tests for HTTP status and response envelope shape
```

## 9. Security Notes For Android Agent

- Never hardcode tokens or credentials in code.
- Never log access_token or refresh_token.
- Use HTTPS in production only.
- Treat refresh token as highly sensitive secret.
- Clear tokens immediately on logout or refresh failure.

## 10. Kotlin Models (Ready To Paste)

Use these models as the canonical API contract in Android.

```kotlin
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class ApiEnvelope<T>(
	val ok: Boolean,
	val data: T? = null,
	val error: ApiError? = null,
	@SerialName("request_id") val requestId: String
)

@Serializable
data class ApiError(
	val code: String,
	val message: String
)

@Serializable
data class Guard(
	val id: Int,
	val name: String,
	val email: String
)

@Serializable
data class AuthData(
	@SerialName("access_token") val accessToken: String,
	@SerialName("token_type") val tokenType: String,
	@SerialName("expires_in") val expiresIn: Int,
	@SerialName("refresh_token") val refreshToken: String,
	@SerialName("refresh_expires_in") val refreshExpiresIn: Int,
	val guard: Guard
)

@Serializable
data class RefreshData(
	@SerialName("access_token") val accessToken: String,
	@SerialName("token_type") val tokenType: String,
	@SerialName("expires_in") val expiresIn: Int,
	@SerialName("refresh_token") val refreshToken: String,
	@SerialName("refresh_expires_in") val refreshExpiresIn: Int
)

@Serializable
data class RefreshRequest(
	@SerialName("refresh_token") val refreshToken: String
)

@Serializable
data class RecordEntryRequest(
	val userId: String,
	val date: String,
	val time: String
)

@Serializable
data class VisitorInfo(
	val userId: String,
	val name: String
)

@Serializable
data class RecordEntryData(
	val message: String,
	val visitor: VisitorInfo
)

@Serializable
data class HealthData(
	val service: String,
	val status: String,
	val database: String,
	val timestamp: String
)
```

## 11. OkHttp Authenticator Blueprint (Token Refresh)

Use an interceptor for adding Authorization and an Authenticator for refresh-on-401.

```kotlin
import kotlinx.coroutines.runBlocking
import okhttp3.Authenticator
import okhttp3.Interceptor
import okhttp3.Request
import okhttp3.Response
import okhttp3.Route

interface TokenStore {
	fun accessToken(): String?
	fun refreshToken(): String?
	fun saveTokens(accessToken: String, refreshToken: String, expiresIn: Int)
	fun clear()
}

class AccessTokenInterceptor(
	private val tokenStore: TokenStore
) : Interceptor {
	override fun intercept(chain: Interceptor.Chain): Response {
		val original = chain.request()
		val token = tokenStore.accessToken()

		if (token.isNullOrBlank()) {
			return chain.proceed(original)
		}

		val updated = original.newBuilder()
			.header("Authorization", "Bearer $token")
			.build()

		return chain.proceed(updated)
	}
}

interface RefreshApi {
	suspend fun refresh(refreshToken: String): ApiEnvelope<RefreshData>
}

class TokenAuthenticator(
	private val tokenStore: TokenStore,
	private val refreshApi: RefreshApi
) : Authenticator {

	override fun authenticate(route: Route?, response: Response): Request? {
		// Avoid infinite retry loops
		if (responseCount(response) >= 2) return null

		val currentRefreshToken = tokenStore.refreshToken() ?: return null

		val refreshEnvelope = runBlocking {
			refreshApi.refresh(currentRefreshToken)
		}

		if (!refreshEnvelope.ok || refreshEnvelope.data == null) {
			// Refresh failed (usually 403). Force sign-in.
			tokenStore.clear()
			return null
		}

		val refreshed = refreshEnvelope.data
		tokenStore.saveTokens(
			accessToken = refreshed.accessToken,
			refreshToken = refreshed.refreshToken,
			expiresIn = refreshed.expiresIn
		)

		return response.request.newBuilder()
			.header("Authorization", "Bearer ${refreshed.accessToken}")
			.build()
	}

	private fun responseCount(response: Response): Int {
		var result = 1
		var prior = response.priorResponse
		while (prior != null) {
			result++
			prior = prior.priorResponse
		}
		return result
	}
}
```

## 12. Agent Prompt Add-On (Implementation Grade)

Append this to your Android agent prompt for better output quality:

```text
Use the exact Kotlin models from docs/API.md section "Kotlin Models (Ready To Paste)".
Implement OkHttp with:
- AccessTokenInterceptor to add Bearer access token
- TokenAuthenticator to refresh once on 401

Behavior rules:
- On refresh failure (ok=false or HTTP 403), clear token store and emit signed-out state.
- Retry the failed request exactly once after successful refresh.
- Never log token values.
- Treat API envelope as authoritative: parse ok/data/error/request_id on every response.
```
