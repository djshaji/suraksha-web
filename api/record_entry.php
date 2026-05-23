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

function guardIdFromSession(): ?int
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $auth = $_SESSION['auth'] ?? null;
    if (!is_array($auth)) {
        return null;
    }

    $googleSub = trim((string) ($auth['sub'] ?? ''));
    $email = trim((string) ($auth['email'] ?? ''));
    if ($googleSub === '' || $email === '') {
        return null;
    }

    $pdo = DB::conn();
    $stmt = $pdo->prepare(
        'SELECT id FROM guards WHERE status = :status AND (google_sub_id = :google_sub_id OR email = :email) LIMIT 1'
    );
    $stmt->execute([
        ':status' => 'active',
        ':google_sub_id' => $googleSub,
        ':email' => $email,
    ]);

    $guard = $stmt->fetch();
    if (!is_array($guard) || !isset($guard['id'])) {
        return null;
    }

    return (int) $guard['id'];
}

function resolveGuardId(): int
{
    try {
        $jwt = bearerTokenFromRequest();
        $claims = verifyAccessToken($jwt);
        return (int) $claims['guard_id'];
    } catch (Throwable $e) {
        $guardId = guardIdFromSession();
        if ($guardId !== null && $guardId > 0) {
            return $guardId;
        }

        unauthorized('AUTH_UNAUTHORIZED', 'Bearer token or active session login required');
    }
}

try {
    $guardId = resolveGuardId();

    $input = readJsonBody();
    $userId = trim((string) ($input['userId'] ?? ''));
    $date = trim((string) ($input['date'] ?? ''));
    $time = trim((string) ($input['time'] ?? ''));

    if ($userId === '' || $date === '' || $time === '') {
        badRequest('REQUEST_MISSING_FIELDS', 'userId, date and time are required');
    }

    $dateObj = DateTime::createFromFormat('Y-m-d', $date);
    $timeObj = DateTime::createFromFormat('H:i:s', $time);
    if (!$dateObj || $dateObj->format('Y-m-d') !== $date) {
        badRequest('REQUEST_INVALID_DATE', 'date must be in Y-m-d format');
    }
    if (!$timeObj || $timeObj->format('H:i:s') !== $time) {
        badRequest('REQUEST_INVALID_TIME', 'time must be in H:i:s format');
    }

    $pdo = DB::conn();
    $stmt = $pdo->prepare(
        'INSERT INTO visitor_logs (visitor_userid, guard_id, log_date, log_time) '
        . 'VALUES (:visitor_userid, :guard_id, :log_date, :log_time)'
    );
    $stmt->execute([
        ':visitor_userid' => $userId,
        ':guard_id' => $guardId,
        ':log_date' => $date,
        ':log_time' => $time,
    ]);

    successResponse([
        'message' => 'Entry recorded successfully',
        'visitor' => [
            'userId' => $userId,
            'name' => 'John Doe',
        ],
    ]);
} catch (InvalidArgumentException $e) {
    badRequest('REQUEST_INVALID', $e->getMessage());
} catch (Throwable $e) {
    logException($e, requestId(), ['endpoint' => 'record_entry']);
    serverError();
}
