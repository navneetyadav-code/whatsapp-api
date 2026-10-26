<?php
declare(strict_types=1);

$webhookStmt = $pdo->prepare('SELECT * FROM webhooks WHERE user_id = ? LIMIT 1');
$webhookStmt->execute([$user['id']]);
$webhook = $webhookStmt->fetch() ?: ['enabled' => 0, 'target_url' => '', 'secret' => ''];
$webhookEnabled = !empty($webhook['enabled']);

$newWebhookSecret = $_SESSION['new_webhook_secret'] ?? null;
unset($_SESSION['new_webhook_secret']);
