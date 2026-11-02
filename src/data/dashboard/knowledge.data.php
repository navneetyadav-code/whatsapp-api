<?php
declare(strict_types=1);

$userId = (int) ($user['id'] ?? ($_SESSION['user_id'] ?? 1));

$searchQuery = trim((string) ($_GET['q'] ?? ''));

if ($searchQuery !== '') {
    $chunkStmt = $pdo->prepare(
        'SELECT * FROM ai_knowledge_chunks 
         WHERE user_id = ? AND (title LIKE ? OR content LIKE ?) 
         ORDER BY id DESC'
    );
    $like = '%' . $searchQuery . '%';
    $chunkStmt->execute([$userId, $like, $like]);
} else {
    $chunkStmt = $pdo->prepare('SELECT * FROM ai_knowledge_chunks WHERE user_id = ? ORDER BY id DESC');
    $chunkStmt->execute([$userId]);
}

$knowledgeChunks = $chunkStmt->fetchAll();

// Statistics calculation
$totalChunks = count($knowledgeChunks);
$totalChars = 0;
foreach ($knowledgeChunks as $c) {
    $totalChars += mb_strlen((string) ($c['content'] ?? ''));
}
$avgChars = $totalChunks > 0 ? (int) round($totalChars / $totalChunks) : 0;
