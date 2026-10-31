<?php
declare(strict_types=1);

$userId = (int) ($user['id'] ?? ($_SESSION['user_id'] ?? 1));

// 1. WhatsApp Connection Status
$sessionStmt = $pdo->prepare('SELECT * FROM whatsapp_sessions WHERE user_id = ? LIMIT 1');
$sessionStmt->execute([$userId]);
$waSession = $sessionStmt->fetch() ?: ['status' => 'idle'];
$deviceConnected = (($waSession['status'] ?? 'idle') === 'connected') || (!empty($waSession['sleeping']) && (!empty($waSession['phone']) || !empty($waSession['push_name'])));
$devicePhone = $waSession['phone'] ?? null;

// 2. Load Agent Mode
$agentMode = 'support';
try {
    $modeStmt = $pdo->prepare('SELECT agent_mode, owner_name FROM ai_agent_settings WHERE user_id = ? LIMIT 1');
    $modeStmt->execute([$userId]);
    $agentSettings = $modeStmt->fetch() ?: [];
    $agentMode = (string) ($agentSettings['agent_mode'] ?? 'support');
    $ownerName = (string) ($agentSettings['owner_name'] ?? 'Boss');
} catch (\Throwable $e) {
    $ownerName = 'Boss';
}

// 3. Load All Memories with Performance Counts
$userMemories = [];
$totalMemories = 0;
$bossMemoriesCount = 0;
$customerMemoriesCount = 0;
$temporaryMemoriesCount = 0;

try {
    $mStmt = $pdo->prepare(
        'SELECT id, contact_phone, memory_type, is_durable, expires_at, memory_key, memory_value, access_count, last_accessed_at, updated_at
         FROM ai_user_memory
         WHERE user_id = ?
         ORDER BY id DESC'
    );
    $mStmt->execute([$userId]);
    $userMemories = $mStmt->fetchAll();
    $totalMemories = count($userMemories);

    foreach ($userMemories as $m) {
        if (empty($m['contact_phone'])) {
            $bossMemoriesCount++;
        } else {
            $customerMemoriesCount++;
        }
        if (!empty($m['expires_at'])) {
            $temporaryMemoriesCount++;
        }
    }
} catch (\Throwable $e) {
    $userMemories = [];
}

// 4. Load Few-Shot Training Examples
$trainingExamples = [];
$totalTrainingCount = 0;
try {
    $tStmt = $pdo->prepare(
        'SELECT id, category, user_query, ideal_response, negative_constraint, is_active, created_at
         FROM ai_training_examples
         WHERE user_id = ?
         ORDER BY id DESC'
    );
    $tStmt->execute([$userId]);
    $trainingExamples = $tStmt->fetchAll();
    $totalTrainingCount = count($trainingExamples);
} catch (\Throwable $e) {
    $trainingExamples = [];
}
