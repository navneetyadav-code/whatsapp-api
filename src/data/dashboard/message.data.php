<?php
declare(strict_types=1);

$sessionStmt = $pdo->prepare('SELECT * FROM whatsapp_sessions WHERE user_id = ? LIMIT 1');
$sessionStmt->execute([$user['id']]);
$session = $sessionStmt->fetch() ?: ['status' => 'idle'];

$deviceSleeping = !empty($session['sleeping']);
$deviceHasIdentity = !empty($session['phone']) || !empty($session['push_name']);
$deviceConnected = (($session['status'] ?? 'idle') === 'connected') || ($deviceSleeping && $deviceHasIdentity);

$contactOptionsStmt = $pdo->prepare('SELECT * FROM contacts WHERE user_id = ? ORDER BY name ASC, id DESC');
$contactOptionsStmt->execute([$user['id']]);
$contactOptions = $contactOptionsStmt->fetchAll();

$optedInCount = count(opted_in_contacts((int) $user['id']));
$campaigns = recent_campaigns((int) $user['id']);
