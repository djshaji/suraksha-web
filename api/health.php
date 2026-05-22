<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/response.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    methodNotAllowed('GET');
}

try {
    $pdo = DB::conn();
    $pdo->query('SELECT 1');

    successResponse([
        'service' => 'suraksha-api',
        'status' => 'ok',
        'database' => 'ok',
        'timestamp' => gmdate('c'),
    ]);
} catch (Throwable $e) {
    errorResponse(503, 'HEALTH_DB_UNAVAILABLE', 'Service unavailable');
}
