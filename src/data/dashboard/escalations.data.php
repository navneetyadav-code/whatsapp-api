<?php
declare(strict_types=1);

$userId = (int) ($user['id'] ?? ($_SESSION['user_id'] ?? 1));

$escStmt = $pdo->prepare(
    'SELECT e.*, c.phone, c.contact_name, c.status AS conv_status
     FROM chat_escalations e
     JOIN chat_conversations c ON e.conversation_id = c.id
     WHERE e.user_id = ?
     ORDER BY e.created_at DESC
     LIMIT 50'
);
$escStmt->execute([$userId]);
$escalationsList = $escStmt->fetchAll();

$activeCount = 0;
$resolvedCount = 0;
foreach ($escalationsList as $e) {
    if (($e['status'] ?? '') === 'active') {
        $activeCount++;
    } else {
        $resolvedCount++;
    }
}
