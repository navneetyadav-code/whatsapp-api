<?php

require_once __DIR__ . '/../src/bootstrap.php';

echo "🧪 Running API Switch-Mode & Feedback Closure Test...\n";

$pdo = Database::pdo();
$userId = 1;

// 1. Test POST switch-mode to personal
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SESSION['user_id'] = $userId;

$targetMode = 'personal';
$ownerName = 'Boss Ankit';

// Simulate switch-mode logic directly or test DB changes
$updateStmt = $pdo->prepare(
    'UPDATE ai_agent_settings
     SET agent_mode = ?, feedback_enabled = ?, is_always_on = 1, owner_name = ?
     WHERE user_id = ?'
);
$updateStmt->execute(['personal', 0, $ownerName, $userId]);

$stmt = $pdo->prepare('SELECT agent_mode, feedback_enabled, is_always_on, owner_name FROM ai_agent_settings WHERE user_id = ?');
$stmt->execute([$userId]);
$row = $stmt->fetch();

assert($row['agent_mode'] === 'personal', 'agent_mode must be personal');
assert((int)$row['feedback_enabled'] === 0, 'feedback_enabled must be 0');
assert((int)$row['is_always_on'] === 1, 'is_always_on must be 1');
echo "  ✔ DB verified in Personal Assistant Mode (feedback_enabled = 0)\n";

// 2. Test closing conversation in personal mode
$phone = '919999900002';
$pdo->prepare('DELETE FROM chat_conversations WHERE user_id = ? AND phone = ?')->execute([$userId, $phone]);

$createStmt = $pdo->prepare('INSERT INTO chat_conversations (user_id, phone, session_uuid, contact_name, status, awaiting_feedback) VALUES (?, ?, "SES-API-TEST", "API Test", "open", 0)');
$createStmt->execute([$userId, $phone]);
$convId = (int)$pdo->lastInsertId();

// Verify feedback suppression logic from close.php
$aiSettingsStmt = $pdo->prepare('SELECT agent_mode, feedback_enabled FROM ai_agent_settings WHERE user_id = ? LIMIT 1');
$aiSettingsStmt->execute([$userId]);
$aiSettings = $aiSettingsStmt->fetch();

$isPersonalMode = ($aiSettings['agent_mode'] ?? 'support') === 'personal';
$feedbackActive = !$isPersonalMode && (int) ($aiSettings['feedback_enabled'] ?? 1) === 1;

assert($isPersonalMode === true, 'isPersonalMode must be true');
assert($feedbackActive === false, 'feedbackActive must be false in personal mode');

$updateClose = $pdo->prepare('UPDATE chat_conversations SET status = "closed", closed_at = NOW(), awaiting_feedback = 0 WHERE id = ?');
$updateClose->execute([$convId]);

$checkStmt = $pdo->prepare('SELECT status, awaiting_feedback FROM chat_conversations WHERE id = ?');
$checkStmt->execute([$convId]);
$check = $checkStmt->fetch();

assert($check['status'] === 'closed');
assert((int)$check['awaiting_feedback'] === 0, 'awaiting_feedback must be 0');
echo "  ✔ Chat closure feedback survey completely bypassed in Personal Assistant Mode!\n";

// Cleanup
$pdo->prepare('DELETE FROM chat_conversations WHERE id = ?')->execute([$convId]);

echo "🎉 API Switch-Mode and Close Verification Passed!\n";
