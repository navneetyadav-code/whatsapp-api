<?php

declare(strict_types=1);

require_once __DIR__ . '/_auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'Method not allowed. Use POST.'], 405);
}

$user = get_chat_authenticated_user();
$userId = (int) $user['id'];
$pdo = Database::pdo();

$data = request_json();
if (empty($data)) {
    $data = $_POST;
}

$rawPhone = trim((string) ($data['phone'] ?? ''));
if ($rawPhone === '') {
    json_response(['ok' => false, 'error' => 'Parameter "phone" is required.'], 422);
}

$phone = normalize_chat_phone($rawPhone, $userId);

// Check current status
$stmt = $pdo->prepare('SELECT id, bot_paused, status FROM chat_conversations WHERE user_id = ? AND phone = ? LIMIT 1');
$stmt->execute([$userId, $phone]);
$conv = $stmt->fetch();

$newStatus = 1;
if ($conv) {
    if (isset($data['bot_paused'])) {
        $newStatus = (int) $data['bot_paused'] === 1 ? 1 : 0;
    } else {
        $newStatus = (int) $conv['bot_paused'] === 1 ? 0 : 1;
    }
    
    // If resuming bot (bot_paused = 0), reset escalated status back to open and acknowledge escalations
    if ($newStatus === 0) {
        $updateStmt = $pdo->prepare('UPDATE chat_conversations SET bot_paused = 0, status = "open", escalation_reason = NULL, escalated_at = NULL WHERE id = ?');
        $updateStmt->execute([$conv['id']]);
        $pdo->prepare('UPDATE chat_escalations SET status = "resolved", resolved_at = NOW(), resolved_by = ? WHERE conversation_id = ? AND user_id = ? AND status != "resolved"')->execute([$user['name'] ?? 'Agent', $conv['id'], $userId]);
    } else {
        $updateStmt = $pdo->prepare('UPDATE chat_conversations SET bot_paused = 1 WHERE id = ?');
        $updateStmt->execute([$conv['id']]);
    }
} else {
    if (isset($data['bot_paused'])) {
        $newStatus = (int) $data['bot_paused'] === 1 ? 1 : 0;
    }
    $insertStmt = $pdo->prepare('INSERT INTO chat_conversations (user_id, phone, bot_paused) VALUES (?, ?, ?)');
    $insertStmt->execute([$userId, $phone, $newStatus]);
}

json_response([
    'ok' => true,
    'phone' => $phone,
    'bot_paused' => $newStatus,
    'status_text' => $newStatus === 1 ? 'Bot Paused (Human Takeover)' : 'Bot Active',
]);
