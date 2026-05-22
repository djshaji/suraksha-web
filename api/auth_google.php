<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/vendor/autoload.php';
require_once __DIR__ . '/../lib/config.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/logger.php';
require_once __DIR__ . '/../lib/response.php';
require_once __DIR__ . '/../lib/auth.php';

use Google\Client as GoogleClient;

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    methodNotAllowed('POST');
}

try {
    $googleIdToken = bearerTokenFromRequest();

    $clientId = requireEnv('GOOGLE_CLIENT_ID');
    $googleClient = new GoogleClient(['client_id' => $clientId]);
    $payload = $googleClient->verifyIdToken($googleIdToken);

    if (!is_array($payload)) {
        unauthorized('AUTH_INVALID_GOOGLE_TOKEN', 'Invalid Google token');
    }

    $googleSub = (string) ($payload['sub'] ?? '');
    $email = (string) ($payload['email'] ?? '');

    if ($googleSub === '' || $email === '') {
        unauthorized('AUTH_INVALID_GOOGLE_TOKEN', 'Invalid Google token claims');
    }

    $pdo = DB::conn();
    $stmt = $pdo->prepare(
        'SELECT id, name, email FROM guards WHERE status = :status AND (google_sub_id = :google_sub_id OR email = :email) LIMIT 1'
    );
    $stmt->execute([
        ':status' => 'active',
        ':google_sub_id' => $googleSub,
        ':email' => $email,
    ]);

    $guard = $stmt->fetch();
    if (!$guard) {
        unauthorized('AUTH_GUARD_NOT_AUTHORIZED', 'Guard is not authorized');
    }

    $guardId = (int) $guard['id'];
    $access = issueAccessToken($guardId);
    $refresh = createRefreshTokenPair();

    $ins = $pdo->prepare(
        'INSERT INTO refresh_tokens (guard_id, token_hash, expires_at) VALUES (:guard_id, :token_hash, :expires_at)'
    );
    $ins->execute([
        ':guard_id' => $guardId,
        ':token_hash' => $refresh['hash'],
        ':expires_at' => $refresh['expires_at'],
    ]);

    successResponse([
        'access_token' => $access['token'],
        'token_type' => 'Bearer',
        'expires_in' => $access['expires_in'],
        'refresh_token' => $refresh['plain'],
        'refresh_expires_in' => $refresh['expires_in'],
        'guard' => [
            'id' => $guardId,
            'name' => $guard['name'],
            'email' => $guard['email'],
        ],
    ]);
} catch (InvalidArgumentException $e) {
    badRequest('REQUEST_INVALID', $e->getMessage());
} catch (Throwable $e) {
    logException($e, requestId(), ['endpoint' => 'auth_google']);
    serverError();
}
