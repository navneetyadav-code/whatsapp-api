<?php

declare(strict_types=1);

require_once __DIR__ . '/_auth.php';

$user = get_chat_authenticated_user();
$userId = (int) $user['id'];
$pdo = Database::pdo();

$rawPhone = trim((string) ($_GET['phone'] ?? ''));
if ($rawPhone === '') {
    json_response(['ok' => false, 'error' => 'Phone number parameter "phone" is required.'], 422);
}

$phone = normalize_chat_phone($rawPhone, $userId);
$afterId = isset($_GET['after_id']) ? max(0, (int) $_GET['after_id']) : 0;
$limit = min(200, max(1, (int) ($_GET['limit'] ?? 50)));

// 1. Fetch conversation
$convStmt = $pdo->prepare(
    'SELECT id, phone, contact_name, last_message, last_message_at, unread_count, bot_paused, is_pinned, status, closed_at, awaiting_feedback, session_uuid, resolution_remark, is_auto_closed
     FROM chat_conversations
     WHERE user_id = ? AND phone = ?
     LIMIT 1'
);
$convStmt->execute([$userId, $phone]);
$conv = $convStmt->fetch();

if (!$conv) {
    json_response([
        'ok' => true,
        'conversation' => null,
        'messages' => [],
    ]);
}

$convId = (int) $conv['id'];

// 2. Mark as read when active conversation is viewed or polled
if ((int) $conv['unread_count'] > 0) {
    $pdo->prepare('UPDATE chat_conversations SET unread_count = 0 WHERE id = ?')->execute([$convId]);
    $conv['unread_count'] = 0;
}

// 3. Fetch chronological messages
$msgSql = 'SELECT id, conversation_id, sender_type, sender_name, message_type, body, media_url, wa_message_id, status, session_uuid, created_at
           FROM chat_messages
           WHERE conversation_id = ?';
$params = [$convId];

if ($afterId > 0) {
    $msgSql .= ' AND id > ?';
    $params[] = $afterId;
}

$msgSql .= ' ORDER BY id ASC LIMIT ' . $limit;

$msgStmt = $pdo->prepare($msgSql);
$msgStmt->execute($params);
$rows = $msgStmt->fetchAll();

$messages = [];
foreach ($rows as $row) {
    $ts = strtotime((string) $row['created_at']);
    $messages[] = [
        'id' => (int) $row['id'],
        'conversation_id' => (int) $row['conversation_id'],
        'sender_type' => (string) $row['sender_type'],
        'sender_name' => (string) ($row['sender_name'] ?? ($row['sender_type'] === 'contact' ? 'Customer' : 'Agent')),
        'is_outbound' => in_array($row['sender_type'], ['agent', 'bot'], true),
        'message_type' => (string) $row['message_type'],
        'body' => (string) ($row['body'] ?? ''),
        'media_url' => $row['media_url'] ? (string) $row['media_url'] : null,
        'status' => (string) $row['status'],
        'session_uuid' => $row['session_uuid'] ? (string) $row['session_uuid'] : null,
        'created_at' => (string) $row['created_at'],
        'time' => $ts ? date('g:i A', $ts) : '',
    ];
}

// Check recent message status updates (sent -> delivered -> read)
$statuses = [];
if ($afterId > 0) {
    $statusStmt = $pdo->prepare(
        'SELECT id, status FROM chat_messages 
         WHERE conversation_id = ? AND sender_type IN ("agent", "bot") AND id <= ?
         ORDER BY id DESC LIMIT 25'
    );
    $statusStmt->execute([$convId, $afterId]);
    $statuses = $statusStmt->fetchAll(PDO::FETCH_KEY_PAIR);
}

$formattedPhone = format_chat_phone((string) $conv['phone']);
$displayName = !empty($conv['contact_name']) ? (string) $conv['contact_name'] : $formattedPhone;

json_response([
    'ok' => true,
    'conversation' => [
        'id' => $convId,
        'phone' => (string) $conv['phone'],
        'formatted_phone' => $formattedPhone,
        'contact_name' => $conv['contact_name'] ? (string) $conv['contact_name'] : null,
        'display_name' => $displayName,
        'bot_paused' => (int) $conv['bot_paused'],
        'unread_count' => (int) $conv['unread_count'],
        'status' => (string) ($conv['status'] ?? 'open'),
        'closed_at' => $conv['closed_at'] ? (string) $conv['closed_at'] : null,
        'awaiting_feedback' => (int) ($conv['awaiting_feedback'] ?? 0),
        'session_uuid' => $conv['session_uuid'] ? (string) $conv['session_uuid'] : null,
        'resolution_remark' => $conv['resolution_remark'] ? (string) $conv['resolution_remark'] : null,
        'is_auto_closed' => (int) ($conv['is_auto_closed'] ?? 0),
    ],
    'messages' => $messages,
    'statuses' => $statuses,
]);
