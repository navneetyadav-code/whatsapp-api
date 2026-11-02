<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/src/bootstrap.php';

$user = current_user();
if (!$user) {
    json_response(['ok' => false, 'error' => 'Unauthorized. Please log in.'], 401);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$order = $input['order'] ?? []; // Array of { id: int, priority: int }

if (!is_array($order) || empty($order)) {
    json_response(['ok' => false, 'error' => 'Valid order array required.'], 422);
    exit;
}

$pdo = Database::pdo();
$userId = (int) $user['id'];

try {
    $stmt = $pdo->prepare('UPDATE ai_provider_keys SET priority = ? WHERE id = ? AND user_id = ?');
    foreach ($order as $item) {
        $id = (int) ($item['id'] ?? 0);
        $priority = max(1, (int) ($item['priority'] ?? 1));
        if ($id > 0) {
            $stmt->execute([$priority, $id, $userId]);
        }
    }

    json_response(['ok' => true, 'message' => 'Key priorities updated successfully.']);
} catch (\Throwable $e) {
    json_response(['ok' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
}
