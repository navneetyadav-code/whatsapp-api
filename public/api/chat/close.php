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
$action = strtolower(trim((string) ($data['action'] ?? 'close')));

// Check conversation exists
$stmt = $pdo->prepare('SELECT id, contact_name, status, awaiting_feedback FROM chat_conversations WHERE user_id = ? AND phone = ? LIMIT 1');
$stmt->execute([$userId, $phone]);
$conv = $stmt->fetch();

if (!$conv) {
    json_response(['ok' => false, 'error' => 'Conversation not found for this phone number.'], 404);
}

$convId = (int) $conv['id'];
$contactName = $conv['contact_name'] ? (string) $conv['contact_name'] : null;

if ($action === 'reopen') {
    $updateStmt = $pdo->prepare(
        'UPDATE chat_conversations
         SET status = \'open\', closed_at = NULL, awaiting_feedback = 0, last_message = \'Chat reopened by agent.\', last_message_at = NOW()
         WHERE id = ?'
    );
    $updateStmt->execute([$convId]);

    // Insert bot notice in message history
    $reopenNotice = 'Chat reopened by support agent.';
    $msgStmt = $pdo->prepare(
        'INSERT INTO chat_messages (user_id, conversation_id, sender_type, sender_name, message_type, body, status)
         VALUES (?, ?, \'bot\', \'Support System\', \'text\', ?, \'sent\')'
    );
    $msgStmt->execute([$userId, $convId, $reopenNotice]);

    json_response([
        'ok' => true,
        'action' => 'reopen',
        'status' => 'open',
        'message' => 'Conversation has been reopened.',
        'conversation' => [
            'id' => $convId,
            'phone' => $phone,
            'status' => 'open',
            'awaiting_feedback' => 0,
        ],
    ]);
}

// Action: Close Chat & Request 5-Star Feedback
$feedbackPrompt = "Thank you for contacting us! 🙏\n"
    . "Your support chat has been marked as closed.\n\n"
    . "⭐ How would you rate your support experience today?\n"
    . "Please reply with a number from 1 to 5 (⭐ 1 = Poor, ⭐⭐⭐⭐⭐ 5 = Excellent) and any comments you'd like to share!";

// Update conversation to closed and awaiting feedback
$updateStmt = $pdo->prepare(
    'UPDATE chat_conversations
     SET status = \'closed\', closed_at = NOW(), awaiting_feedback = 1, bot_paused = 0,
         last_message = \'Support chat closed. 5-star feedback requested.\', last_message_at = NOW()
     WHERE id = ?'
);
$updateStmt->execute([$convId]);

// Insert feedback prompt into chat_messages
$msgStmt = $pdo->prepare(
    'INSERT INTO chat_messages (user_id, conversation_id, sender_type, sender_name, message_type, body, status)
     VALUES (?, ?, \'bot\', \'Support Bot\', \'text\', ?, \'sent\')'
);
$msgStmt->execute([$userId, $convId, $feedbackPrompt]);
$promptMsgId = (int) $pdo->lastInsertId();

// Dispatch feedback prompt message via message_queue to WhatsApp
$queuePayload = [
    'source' => 'chat_feedback_request',
    'chat_message_id' => $promptMsgId,
    'type' => 'text',
    'priority' => 10,
];

try {
    enqueue_message($userId, null, $phone, $feedbackPrompt, $queuePayload, 10);
} catch (Throwable $e) {
    // Log enqueue error if any, but conversation is already marked closed
}

json_response([
    'ok' => true,
    'action' => 'close',
    'status' => 'closed',
    'awaiting_feedback' => 1,
    'message' => 'Chat closed successfully and 5-star feedback request has been sent to the customer.',
    'prompt_message_id' => $promptMsgId,
    'conversation' => [
        'id' => $convId,
        'phone' => $phone,
        'status' => 'closed',
        'awaiting_feedback' => 1,
    ],
]);
