<?php
declare(strict_types=1);

$sessionStmt = $pdo->prepare('SELECT * FROM whatsapp_sessions WHERE user_id = ? LIMIT 1');
$sessionStmt->execute([$user['id']]);
$session = $sessionStmt->fetch() ?: ['status' => 'idle'];

$deviceSleeping = !empty($session['sleeping']);
$deviceHasIdentity = !empty($session['phone']) || !empty($session['push_name']);
$deviceConnected = (($session['status'] ?? 'idle') === 'connected') || ($deviceSleeping && $deviceHasIdentity);

$apiKeys = list_api_keys((int) $user['id']);
$activeApiKeyCount = count(array_filter($apiKeys, static fn (array $key): bool => ($key['status'] ?? '') === 'active'));

$stats = message_stats((int) $user['id']);
$messages = recent_messages((int) $user['id']);
$totalQueueMessages = count_messages((int) $user['id']);

$sentCount = (int) ($stats['sent'] ?? 0);
$failedCount = (int) ($stats['failed'] ?? 0);
$queuedCount = (int) ($stats['queued'] ?? 0);
$processingCount = (int) ($stats['processing'] ?? 0);
$deliveryTotal = max(1, $sentCount + $failedCount + $queuedCount + $processingCount);
$sentRate = (int) round(($sentCount / $deliveryTotal) * 100);
$failureRate = (int) round(($failedCount / $deliveryTotal) * 100);
$queueDepth = $queuedCount + $processingCount;

$apiRequestCountStmt = $pdo->prepare('SELECT COUNT(*) FROM api_request_logs WHERE user_id = ?');
$apiRequestCountStmt->execute([(int) $user['id']]);
$apiRequestCount = (int) $apiRequestCountStmt->fetchColumn();

$webhookStmt = $pdo->prepare('SELECT * FROM webhooks WHERE user_id = ? LIMIT 1');
$webhookStmt->execute([$user['id']]);
$webhook = $webhookStmt->fetch() ?: ['enabled' => 0, 'target_url' => '', 'secret' => ''];
$webhookEnabled = !empty($webhook['enabled']);

$readySignals = 1 + ($deviceConnected ? 1 : 0) + ($webhookEnabled ? 1 : 0) + ($queueDepth === 0 ? 1 : 0);
$readinessScore = (int) round(($readySignals / 4) * 100);
$lastActivity = $messages[0]['created_at'] ?? 'No activity yet';
$nextAction = $deviceConnected ? 'Send test message' : 'Pair WhatsApp device';
$nextActionUrl = $deviceConnected ? base_url('index.php?page=queue') : base_url('index.php?page=device');
