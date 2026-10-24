<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

header('Content-Type: application/json');

$worker = worker_request('GET', 'health');
$workerOk = worker_health_ready($worker);
$requiredTables = [
    'users', 'api_keys', 'api_request_logs', 'campaigns', 'chatbot_rules', 'contacts',
    'login_attempts', 'message_logs', 'message_queue', 'password_resets',
    'webhooks', 'webhook_deliveries', 'whatsapp_sessions',
];
$databaseOk = false;
$databaseConnected = false;
$missingTables = $requiredTables;

try {
    $pdo = Database::pdo();
    $databaseConnected = true;
    $databaseName = (string) $pdo->query('SELECT DATABASE()')->fetchColumn();
    if ($databaseName === 'whatsapp_api') {
        $statement = $pdo->query(
            'SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE()'
        );
        $existingTables = $statement->fetchAll(PDO::FETCH_COLUMN);
        $missingTables = array_values(array_diff($requiredTables, $existingTables));
        $databaseOk = $missingTables === [];
    }
} catch (Throwable $error) {
    error_log('[health] database probe failed: ' . get_class($error));
}

$ok = $workerOk && $databaseOk;
$status = $ok ? 200 : 503;

http_response_code($status);

echo json_encode([
    'ok' => $ok,
    'status' => $ok ? 'healthy' : 'degraded',
    'timestamp' => date('c'),
    'service' => 'whatsapp-api-hub',
    'database' => [
        'ok' => $databaseOk,
        'connected' => $databaseConnected,
        'schema_ready' => $databaseOk,
        'missing_tables' => $missingTables,
    ],
    'worker' => [
        'ok' => $workerOk,
        'status_code' => $worker['status_code'] ?? $worker['status'] ?? null,
        'service' => $worker['service'] ?? null,
        'error' => $workerOk ? null : 'Worker is not reachable or its database is not ready.',
    ],
]);
