<?php
declare(strict_types=1);

$userId = (int) $user['id'];

// Fetch all keyword automation rules for current user
$stmt = $pdo->prepare('SELECT * FROM chatbot_rules WHERE user_id = ? ORDER BY id DESC');
$stmt->execute([$userId]);
$chatbotRules = $stmt->fetchAll();
