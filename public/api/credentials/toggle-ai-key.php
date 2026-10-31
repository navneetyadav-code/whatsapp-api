<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/src/bootstrap.php';

$user = current_user();
if (!$user) {
    json_response(['ok' => false, 'error' => 'Unauthorized. Please log in.'], 401);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$id = (int) ($input['id'] ?? 0);
$isActive = !empty($input['is_active']) ? 1 : 0;

if ($id <= 0) {
    json_response(['ok' => false, 'error' => 'Valid key ID required.'], 422);
    exit;
}

$pdo = Database::pdo();
$userId = (int) $user['id'];

try {
    $stmt = $pdo->prepare('UPDATE ai_provider_keys SET is_active = ? WHERE id = ? AND user_id = ?');
    $stmt->execute([$isActive, $id, $userId]);

    json_response([
        'ok' => true,
        'is_active' => $isActive,
        'message' => $isActive ? 'Key activated.' : 'Key paused.'
    ]);
} catch (\Throwable $e) {
    json_response(['ok' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
}
