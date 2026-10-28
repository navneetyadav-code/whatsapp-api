<?php

declare(strict_types=1);

require_once __DIR__ . '/_auth.php';

$user = get_chat_authenticated_user();
$userId = (int) $user['id'];
$pdo = Database::pdo();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare(
        'SELECT e.id, e.conversation_id, e.reason_code, e.trigger_text, e.status, e.created_at, e.acknowledged_at,
                c.phone, c.contact_name
         FROM chat_escalations e
         JOIN chat_conversations c ON e.conversation_id = c.id
         WHERE e.user_id = ? AND e.status = "active"
         ORDER BY e.created_at DESC'
    );
    $stmt->execute([$userId]);
    $escalations = $stmt->fetchAll();

    json_response([
        'ok' => true,
        'count' => count($escalations),
        'escalations' => $escalations,
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = request_json();
    if (empty($data)) {
        $data = $_POST;
    }

    $action = trim((string) ($data['action'] ?? 'acknowledge'));
    $escalationId = (int) ($data['escalation_id'] ?? 0);
    $conversationId = (int) ($data['conversation_id'] ?? 0);
    $rawPhone = trim((string) ($data['phone'] ?? ''));

    // Automatically resolve conversationId from phone number if passed
    if ($conversationId <= 0 && $rawPhone !== '') {
        $phone = normalize_chat_phone($rawPhone, $userId);
        $cStmt = $pdo->prepare('SELECT id FROM chat_conversations WHERE user_id = ? AND (phone = ? OR phone = ?) LIMIT 1');
        $cStmt->execute([$userId, $phone, $rawPhone]);
        $conversationId = (int) $cStmt->fetchColumn();
    }

    if ($action === 'acknowledge') {
        if ($escalationId > 0) {
            $stmt = $pdo->prepare('UPDATE chat_escalations SET status = "acknowledged", acknowledged_at = NOW() WHERE id = ? AND user_id = ?');
            $stmt->execute([$escalationId, $userId]);
        } elseif ($conversationId > 0) {
            $stmt = $pdo->prepare('UPDATE chat_escalations SET status = "acknowledged", acknowledged_at = NOW() WHERE conversation_id = ? AND user_id = ? AND status = "active"');
            $stmt->execute([$conversationId, $userId]);
        } else {
            // Acknowledge all active for this user
            $stmt = $pdo->prepare('UPDATE chat_escalations SET status = "acknowledged", acknowledged_at = NOW() WHERE user_id = ? AND status = "active"');
            $stmt->execute([$userId]);
        }

        json_response([
            'ok' => true,
            'message' => 'Escalation acknowledged. Alarm silenced.',
        ]);
    }

    if ($action === 'resolve') {
        if ($conversationId <= 0 && $escalationId > 0) {
            $cStmt = $pdo->prepare('SELECT conversation_id FROM chat_escalations WHERE id = ? AND user_id = ? LIMIT 1');
            $cStmt->execute([$escalationId, $userId]);
            $conversationId = (int) $cStmt->fetchColumn();
        }

        if ($conversationId > 0) {
            // Mark conversation as open, clear escalation, keep bot_paused = 1 for human takeover
            $pdo->prepare('UPDATE chat_conversations SET status = "open", escalation_reason = NULL, escalated_at = NULL, bot_paused = 1 WHERE id = ? AND user_id = ?')->execute([$conversationId, $userId]);
            $pdo->prepare('UPDATE chat_escalations SET status = "resolved", resolved_at = NOW(), resolved_by = ? WHERE conversation_id = ? AND user_id = ? AND status != "resolved"')->execute([$user['name'] ?? 'Agent', $conversationId, $userId]);
        } else {
            // Global resolve for active user escalations
            $pdo->prepare('UPDATE chat_conversations SET status = "open", escalation_reason = NULL, escalated_at = NULL, bot_paused = 1 WHERE user_id = ? AND status = "escalated"')->execute([$userId]);
            $pdo->prepare('UPDATE chat_escalations SET status = "resolved", resolved_at = NOW(), resolved_by = ? WHERE user_id = ? AND status != "resolved"')->execute([$user['name'] ?? 'Agent', $userId]);
        }

        json_response([
            'ok' => true,
            'message' => 'Escalation resolved and agent takeover activated.',
        ]);
    }

    if ($action === 'escalate_manually') {
        $rawPhone = trim((string) ($data['phone'] ?? ''));
        if ($rawPhone !== '') {
            $phone = normalize_chat_phone($rawPhone, $userId);
            $cStmt = $pdo->prepare('SELECT id FROM chat_conversations WHERE user_id = ? AND phone = ? LIMIT 1');
            $cStmt->execute([$userId, $phone]);
            $conversationId = (int) $cStmt->fetchColumn();
        }

        if ($conversationId <= 0) {
            json_response(['ok' => false, 'error' => 'Valid conversation_id or phone required'], 422);
        }

        $reason = trim((string) ($data['reason'] ?? 'manual_agent_escalation'));
        $pdo->prepare('UPDATE chat_conversations SET status = "escalated", escalated_at = NOW(), escalation_reason = ?, bot_paused = 1 WHERE id = ? AND user_id = ?')->execute([$reason, $conversationId, $userId]);
        $pdo->prepare('INSERT INTO chat_escalations (user_id, conversation_id, reason_code, trigger_text, status, created_at) VALUES (?, ?, ?, ?, "active", NOW())')->execute([$userId, $conversationId, $reason, 'Manually escalated by agent']);

        json_response([
            'ok' => true,
            'message' => 'Conversation escalated successfully.',
        ]);
    }

    json_response(['ok' => false, 'error' => 'Unknown action.'], 400);
}
