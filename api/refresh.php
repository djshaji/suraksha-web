<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/vendor/autoload.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/logger.php';
require_once __DIR__ . '/../lib/response.php';
require_once __DIR__ . '/../lib/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    methodNotAllowed('POST');
}

try {
    $input = readJsonBody();
    $refreshToken = isset($input['refresh_token']) ? trim((string) $input['refresh_token']) : '';

    if ($refreshToken === '') {
        badRequest('REQUEST_MISSING_REFRESH_TOKEN', 'refresh_token is required');
    }

    $tokenHash = hash('sha256', $refreshToken);
    $pdo = DB::conn();

    $stmt = $pdo->prepare(
        'SELECT rt.id, rt.guard_id, rt.expires_at '
        . 'FROM refresh_tokens rt '
        . 'JOIN guards g ON g.id = rt.guard_id '
        . 'WHERE rt.token_hash = :token_hash AND g.status = :status '
        . 'LIMIT 1'
    );
    $stmt->execute([
        ':token_hash' => $tokenHash,
        ':status' => 'active',
    ]);

    $row = $stmt->fetch();
    if (!$row) {
        forbidden('AUTH_REFRESH_FORBIDDEN', 'Invalid or expired refresh token');
    }

    $expiresAt = strtotime((string) $row['expires_at']);
    if ($expiresAt === false || $expiresAt < time()) {
        $del = $pdo->prepare('DELETE FROM refresh_tokens WHERE id = :id');
        $del->execute([':id' => (int) $row['id']]);
        forbidden('AUTH_REFRESH_FORBIDDEN', 'Invalid or expired refresh token');
    }

    $guardId = (int) $row['guard_id'];
    $newAccess = issueAccessToken($guardId);
    $newRefresh = createRefreshTokenPair();

    $pdo->beginTransaction();
    $del = $pdo->prepare('DELETE FROM refresh_tokens WHERE id = :id');
    $del->execute([':id' => (int) $row['id']]);

    $ins = $pdo->prepare(
        'INSERT INTO refresh_tokens (guard_id, token_hash, expires_at) VALUES (:guard_id, :token_hash, :expires_at)'
    );
    $ins->execute([
        ':guard_id' => $guardId,
        ':token_hash' => $newRefresh['hash'],
        ':expires_at' => $newRefresh['expires_at'],
    ]);
    $pdo->commit();

    successResponse([
        'access_token' => $newAccess['token'],
        'token_type' => 'Bearer',
        'expires_in' => $newAccess['expires_in'],
        'refresh_token' => $newRefresh['plain'],
        'refresh_expires_in' => $newRefresh['expires_in'],
    ]);
} catch (InvalidArgumentException $e) {
    badRequest('REQUEST_INVALID', $e->getMessage());
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    logException($e, requestId(), ['endpoint' => 'refresh']);
    serverError();
}
