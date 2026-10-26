<?php
declare(strict_types=1);

$stmt = $pdo->prepare('SELECT * FROM chatbot_rules WHERE user_id = ? ORDER BY id DESC');
$stmt->execute([$user['id']]);
$chatbotRules = $stmt->fetchAll();
