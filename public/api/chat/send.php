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

$rawPhone = trim((string) ($data['phone'] ?? $data['to'] ?? ''));
if ($rawPhone === '') {
    json_response(['ok' => false, 'error' => 'Recipient "phone" is required.'], 422);
}

$phone = normalize_chat_phone($rawPhone, $userId);
if (strlen($phone) < 10) {
    json_response(['ok' => false, 'error' => 'Recipient "phone" must have at least 10 digits.'], 422);
}
$message = trim((string) ($data['message'] ?? $data['body'] ?? $data['text'] ?? ''));
$autoPauseBot = !isset($data['auto_pause_bot']) || (bool) $data['auto_pause_bot'];

// 1. Optional media attachment handling
$attachment = null;
try {
    $attachment = parse_api_media_payload($data, $_FILES ?? []);
} catch (Throwable $e) {
    json_response(['ok' => false, 'error' => 'Media attachment error: ' . $e->getMessage()], 422);
}

if ($message === '' && $attachment === null) {
    json_response(['ok' => false, 'error' => 'Either "message" text or a media attachment is required.'], 422);
}

$messageType = $attachment ? ($attachment['type'] ?? 'document') : 'text';
$mediaUrl = $attachment['url'] ?? $attachment['path'] ?? null;
$messagePreview = $message !== '' ? $message : "[{$messageType}]";

// 2. Upsert conversation
$botPausedVal = $autoPauseBot ? 1 : 0;
$upsertConvStmt = $pdo->prepare(
    'INSERT INTO chat_conversations (user_id, phone, last_message, last_message_at, unread_count, bot_paused, status, closed_at, awaiting_feedback)
     VALUES (?, ?, ?, NOW(), 0, ?, \'open\', NULL, 0)
     ON DUPLICATE KEY UPDATE
       last_message = VALUES(last_message),
       last_message_at = NOW(),
       unread_count = 0,
       status = \'open\',
       closed_at = NULL,
       awaiting_feedback = 0,
       bot_paused = CASE WHEN ? = 1 THEN 1 ELSE bot_paused END'
);
$upsertConvStmt->execute([$userId, $phone, $messagePreview, $botPausedVal, $botPausedVal]);

$convStmt = $pdo->prepare('SELECT id, phone, contact_name, bot_paused, status, awaiting_feedback FROM chat_conversations WHERE user_id = ? AND phone = ? LIMIT 1');
$convStmt->execute([$userId, $phone]);
$conv = $convStmt->fetch();
$convId = (int) $conv['id'];

// 3. Insert into chat_messages
$agentName = !empty($user['name']) ? (string) $user['name'] : 'Agent';
$insertMsgStmt = $pdo->prepare(
    'INSERT INTO chat_messages (user_id, conversation_id, sender_type, sender_name, message_type, body, media_url, status)
     VALUES (?, ?, \'agent\', ?, ?, ?, ?, \'sent\')'
);
$insertMsgStmt->execute([$userId, $convId, $agentName, $messageType, $message, $mediaUrl]);
$messageId = (int) $pdo->lastInsertId();

// 4. Dispatch immediately via message_queue (priority 10 for interactive live chat)
$payload = [
    'source' => 'live_chat',
    'chat_message_id' => $messageId,
    'type' => $messageType,
    'priority' => 10,
];
if ($attachment !== null) {
    $payload['attachment'] = $attachment;
}

enqueue_message($userId, null, $phone, $message, $payload, 10);

$now = date('Y-m-d H:i:s');
json_response([
    'ok' => true,
    'message' => [
        'id' => $messageId,
        'conversation_id' => $convId,
        'sender_type' => 'agent',
        'sender_name' => $agentName,
        'is_outbound' => true,
        'message_type' => $messageType,
        'body' => $message,
        'media_url' => $mediaUrl,
        'status' => 'sent',
        'created_at' => $now,
        'time' => date('g:i A', strtotime($now)),
    ],
    'conversation' => [
        'id' => $convId,
        'phone' => $phone,
        'bot_paused' => (int) $conv['bot_paused'],
        'status' => (string) ($conv['status'] ?? 'open'),
        'awaiting_feedback' => (int) ($conv['awaiting_feedback'] ?? 0),
    ],
]);
