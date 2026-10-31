<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/src/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$user = current_user();
if (!$user) {
    $apiUser = authenticate_api_request();
    if ($apiUser && !empty($apiUser['user_id'])) {
        $stmt = Database::pdo()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([(int) $apiUser['user_id']]);
        $user = $stmt->fetch();
    }
}

if (!$user) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
}

$period = (string) ($_GET['period'] ?? '24h');
$stats = get_dashboard_analytics_stats((int) $user['id'], $period);

echo json_encode([
    'ok' => true,
    'period' => $stats['period'],
    'timeSeries' => $stats['timeSeries'],
    'byType' => $stats['byType'],
    'topChats' => $stats['topChats'],
    'totals' => $stats['totals'],
]);
