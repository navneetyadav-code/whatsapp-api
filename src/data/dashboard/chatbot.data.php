<?php
declare(strict_types=1);

$userId = (int) $user['id'];

// 1. AI Agent Configuration
$aiStmt = $pdo->prepare('SELECT * FROM ai_agent_settings WHERE user_id = ? LIMIT 1');
$aiStmt->execute([$userId]);
$aiSettings = $aiStmt->fetch() ?: [
    'enabled' => 1,
    'provider_priority' => 'gemini',
    'gemini_api_key' => '',
    'groq_api_key' => '',
    'openrouter_api_key' => '',
    'gemini_model' => 'gemini-3.5-flash',
    'groq_model' => 'openai/gpt-oss-120b',
    'openrouter_model' => 'meta-llama/llama-3.3-70b-instruct:free',
    'system_prompt' => "You are a friendly, helpful, and professional customer support assistant for our business on WhatsApp. Provide clear, accurate answers grounded in our official company knowledge base. When asked about something not in the knowledge base, politely explain that you don't have that information and offer to connect them to our support team.",
    'temperature' => '0.40',
    'max_tokens' => 500,
    'auto_reply_enabled' => 1,
    'agent_mode' => 'support',
    'feedback_enabled' => 1,
    'is_always_on' => 1,
    'owner_name' => 'Boss',
    'frustration_threshold' => '0.65',
    'tools_config' => null
];

// Clean up any legacy asterisks from display
if (($aiSettings['gemini_api_key'] ?? '') === '********') {
    $aiSettings['gemini_api_key'] = '';
}
if (($aiSettings['groq_api_key'] ?? '') === '********') {
    $aiSettings['groq_api_key'] = '';
}
if (($aiSettings['openrouter_api_key'] ?? '') === '********') {
    $aiSettings['openrouter_api_key'] = '';
}

// 2. WhatsApp Gateway Connection Status
$sessionStmt = $pdo->prepare('SELECT * FROM whatsapp_sessions WHERE user_id = ? LIMIT 1');
$sessionStmt->execute([$userId]);
$waSession = $sessionStmt->fetch() ?: ['status' => 'idle'];
$deviceConnected = (($waSession['status'] ?? 'idle') === 'connected') || (!empty($waSession['sleeping']) && (!empty($waSession['phone']) || !empty($waSession['push_name'])));
$devicePhone = $waSession['phone'] ?? null;

// 2. Counts for Quick Status Reference
try {
    $kStmt = $pdo->prepare('SELECT COUNT(*) FROM ai_knowledge_chunks WHERE user_id = ?');
    $kStmt->execute([$userId]);
    $knowledgeCount = (int) $kStmt->fetchColumn();

    $tStmt = $pdo->prepare('SELECT COUNT(*) FROM ai_custom_tools WHERE user_id = ? AND is_active = 1');
    $tStmt->execute([$userId]);
    $toolsCount = (int) $tStmt->fetchColumn();

    $mStmt = $pdo->prepare(
        'SELECT id, contact_phone, memory_type, is_durable, expires_at, memory_key, memory_value, access_count, last_accessed_at, updated_at
         FROM ai_user_memory
         WHERE user_id = ?
         ORDER BY id DESC'
    );
    $mStmt->execute([$userId]);
    $userMemories = $mStmt->fetchAll();

    $exStmt = $pdo->prepare(
        'SELECT id, category, user_query, ideal_response, negative_constraint, is_active, created_at
         FROM ai_training_examples
         WHERE user_id = ?
         ORDER BY id DESC'
    );
    $exStmt->execute([$userId]);
    $trainingExamples = $exStmt->fetchAll();
} catch (\Throwable $e) {
    $knowledgeCount = 0;
    $toolsCount = 0;
    $userMemories = [];
    $trainingExamples = [];
}
