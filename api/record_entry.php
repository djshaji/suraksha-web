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
    $jwt = bearerTokenFromRequest();
    $claims = verifyAccessToken($jwt);
    $guardId = (int) $claims['guard_id'];

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
} catch (RuntimeException $e) {
    unauthorized('AUTH_INVALID_ACCESS_TOKEN', 'Invalid or expired token');
} catch (Throwable $e) {
    logException($e, requestId(), ['endpoint' => 'record_entry']);
    serverError();
}
