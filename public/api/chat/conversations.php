<?php

declare(strict_types=1);

require_once __DIR__ . '/_auth.php';

$user = get_chat_authenticated_user();
$userId = (int) $user['id'];
$pdo = Database::pdo();

$search = trim((string) ($_GET['q'] ?? ''));
$limit = min(200, max(1, (int) ($_GET['limit'] ?? 50)));

$statusFilter = trim((string) ($_GET['status'] ?? ''));
$sql = 'SELECT id, phone, contact_name, last_message, last_message_at, unread_count, bot_paused, is_pinned, status, closed_at, awaiting_feedback, escalated_at, escalation_reason, created_at, updated_at
        FROM chat_conversations
        WHERE user_id = ?';
$params = [$userId];

if ($statusFilter === 'open' || $statusFilter === 'closed' || $statusFilter === 'escalated') {
    $sql .= ' AND status = ?';
    $params[] = $statusFilter;
}

if ($search !== '') {
    $sql .= ' AND (phone LIKE ? OR contact_name LIKE ? OR last_message LIKE ?)';
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= ' ORDER BY (status = "escalated") DESC, is_pinned DESC, last_message_at DESC LIMIT ' . $limit;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

// Count active unacknowledged escalations across all user conversations
$escCountStmt = $pdo->prepare(
    'SELECT COUNT(*) FROM chat_escalations WHERE user_id = ? AND status = "active"'
);
$escCountStmt->execute([$userId]);
$activeEscalationsCount = (int) $escCountStmt->fetchColumn();

$conversations = [];
foreach ($rows as $row) {
    $formatted = format_chat_phone((string) $row['phone']);
    $conversations[] = [
        'id' => (int) $row['id'],
        'phone' => (string) $row['phone'],
        'formatted_phone' => $formatted,
        'contact_name' => $row['contact_name'] ? (string) $row['contact_name'] : null,
        'display_name' => !empty($row['contact_name']) ? (string) $row['contact_name'] : $formatted,
        'last_message' => (string) ($row['last_message'] ?? ''),
        'last_message_at' => (string) $row['last_message_at'],
        'time_ago' => chat_time_ago((string) $row['last_message_at']),
        'unread_count' => (int) $row['unread_count'],
        'bot_paused' => (int) $row['bot_paused'],
        'is_pinned' => (int) $row['is_pinned'],
        'status' => (string) ($row['status'] ?? 'open'),
        'closed_at' => $row['closed_at'] ? (string) $row['closed_at'] : null,
        'awaiting_feedback' => (int) ($row['awaiting_feedback'] ?? 0),
        'escalated_at' => $row['escalated_at'] ? (string) $row['escalated_at'] : null,
        'escalation_reason' => $row['escalation_reason'] ? (string) $row['escalation_reason'] : null,
    ];
}

json_response([
    'ok' => true,
    'count' => count($conversations),
    'active_escalations_count' => $activeEscalationsCount,
    'conversations' => $conversations,
]);
