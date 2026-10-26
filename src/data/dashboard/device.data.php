<?php
declare(strict_types=1);

$allowNewDevices = (bool) app_config('app.allow_new_devices');
$isOwner = is_owner($user);
$restrictedNotice = (string) (app_config('app.restricted_notice') ?: 'This is not allowed in current architecture. Contact info.assistgroupofficial@gmail.com');

$sessionStmt = $pdo->prepare('SELECT * FROM whatsapp_sessions WHERE user_id = ? LIMIT 1');
$sessionStmt->execute([$user['id']]);
$session = $sessionStmt->fetch() ?: ['status' => 'idle'];

$deviceSleeping = !empty($session['sleeping']);
$deviceHasIdentity = !empty($session['phone']) || !empty($session['push_name']);
$deviceConnected = (($session['status'] ?? 'idle') === 'connected') || ($deviceSleeping && $deviceHasIdentity);
