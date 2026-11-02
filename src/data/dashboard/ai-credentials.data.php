<?php

declare(strict_types=1);

$userId = (int) ($user['id'] ?? ($_SESSION['user_id'] ?? 1));
$pdo = Database::pdo();

// 1. Fetch AI Provider Keys grouped by provider, ordered by priority ASC, id ASC
$keysStmt = $pdo->prepare(
    'SELECT id, provider, label, api_key, model, priority, is_active, status, fail_count, last_used_at, last_error, updated_at
     FROM ai_provider_keys
     WHERE user_id = ?
     ORDER BY provider ASC, priority ASC, id ASC'
);
$keysStmt->execute([$userId]);
$allKeys = $keysStmt->fetchAll(PDO::FETCH_ASSOC);

$geminiKeys = [];
$groqKeys = [];
$openrouterKeys = [];

foreach ($allKeys as $k) {
    if ($k['provider'] === 'gemini') {
        $geminiKeys[] = $k;
    } elseif ($k['provider'] === 'groq') {
        $groqKeys[] = $k;
    } elseif ($k['provider'] === 'openrouter') {
        $openrouterKeys[] = $k;
    }
}

// 2. Fetch User Isolated SMTP Credentials
$smtpStmt = $pdo->prepare(
    'SELECT id, label, host, port, encryption, username, password, from_email, from_name, priority, is_active, status, fail_count, last_used_at, last_error, updated_at
     FROM user_smtp_credentials
     WHERE user_id = ?
     ORDER BY priority ASC, id ASC'
);
$smtpStmt->execute([$userId]);
$smtpServers = $smtpStmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Fetch AI Agent Settings (for Provider Priority and Agent Mode context)
$aiStmt = $pdo->prepare(
    'SELECT provider_priority, gemini_model, groq_model, openrouter_model, agent_mode, owner_name, enabled, auto_reply_enabled
     FROM ai_agent_settings
     WHERE user_id = ? LIMIT 1'
);
$aiStmt->execute([$userId]);
$aiSettings = $aiStmt->fetch(PDO::FETCH_ASSOC) ?: [
    'provider_priority' => 'gemini',
    'gemini_model' => '',
    'groq_model' => '',
    'openrouter_model' => '',
    'agent_mode' => 'support',
    'owner_name' => 'Boss',
    'enabled' => 1,
    'auto_reply_enabled' => 1
];

// Quick statistics
$totalAiKeys = count($allKeys);
$activeAiKeys = count(array_filter($allKeys, fn($k) => (int) $k['is_active'] === 1));
$totalSmtp = count($smtpServers);
$activeSmtp = count(array_filter($smtpServers, fn($s) => (int) $s['is_active'] === 1));
