<?php

declare(strict_types=1);

$userId = (int) $user['id'];

// 1. Preload initial conversations list for instant first render
$convStmt = $pdo->prepare(
    'SELECT id, phone, contact_name, last_message, last_message_at, unread_count, bot_paused, is_pinned
     FROM chat_conversations
     WHERE user_id = ?
     ORDER BY is_pinned DESC, last_message_at DESC
     LIMIT 50'
);
$convStmt->execute([$userId]);
$chatConversations = $convStmt->fetchAll();

// 2. Total unread message counter
$unreadStmt = $pdo->prepare(
    'SELECT COALESCE(SUM(unread_count), 0) FROM chat_conversations WHERE user_id = ?'
);
$unreadStmt->execute([$userId]);
$totalChatUnread = (int) $unreadStmt->fetchColumn();

// 3. WhatsApp connection status check
$deviceStmt = $pdo->prepare('SELECT status, phone, push_name FROM whatsapp_sessions WHERE user_id = ? LIMIT 1');
$deviceStmt->execute([$userId]);
$chatDeviceSession = $deviceStmt->fetch() ?: null;
$isDeviceConnected = ($chatDeviceSession['status'] ?? '') === 'connected';
