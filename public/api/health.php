<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

header('Content-Type: application/json');

$worker = worker_request('GET', 'health');
$workerOk = !empty($worker['ok']);
$status = $workerOk ? 200 : 503;

http_response_code($status);

echo json_encode([
    'ok' => $workerOk,
    'status' => $workerOk ? 'healthy' : 'degraded',
    'timestamp' => date('c'),
    'service' => 'whatsgateway',
    'worker' => [
        'ok' => $workerOk,
        'status_code' => $worker['status_code'] ?? $worker['status'] ?? null,
        'service' => $worker['service'] ?? null,
        'error' => $workerOk ? null : ($worker['error'] ?? 'Worker is not reachable.'),
    ],
]);
