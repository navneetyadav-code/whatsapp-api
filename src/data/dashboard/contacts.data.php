<?php
declare(strict_types=1);

$contactsStmt = $pdo->prepare('SELECT * FROM contacts WHERE user_id = ? ORDER BY id DESC LIMIT 50');
$contactsStmt->execute([$user['id']]);
$contacts = $contactsStmt->fetchAll();

$optedInCount = count(opted_in_contacts((int) $user['id']));
