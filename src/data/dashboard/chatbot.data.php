<?php
declare(strict_types=1);

$userId = (int) $user['id'];

// 1. Keyword Chatbot Rules
$stmt = $pdo->prepare('SELECT * FROM chatbot_rules WHERE user_id = ? ORDER BY id DESC');
$stmt->execute([$userId]);
$chatbotRules = $stmt->fetchAll();

// 2. AI Agent Configuration
$aiStmt = $pdo->prepare('SELECT * FROM ai_agent_settings WHERE user_id = ? LIMIT 1');
$aiStmt->execute([$userId]);
$aiSettings = $aiStmt->fetch() ?: [
    'enabled' => 1,
    'provider_priority' => 'gemini',
    'gemini_api_key' => '',
    'groq_api_key' => '',
    'openrouter_api_key' => '',
    'gemini_model' => 'gemini-1.5-flash',
    'groq_model' => 'llama-3.3-70b-versatile',
    'openrouter_model' => 'meta-llama/llama-3.3-70b-instruct:free',
    'system_prompt' => "You are a friendly, helpful, and professional customer support assistant for our business on WhatsApp. Provide clear, accurate answers grounded in our official company knowledge base. When asked about something not in the knowledge base, politely explain that you don't have that information and offer to connect them to our support team.",
    'temperature' => '0.40',
    'max_tokens' => 500,
    'auto_reply_enabled' => 1,
    'frustration_threshold' => '0.65',
];

// 3. Zero-Cloud RAG Knowledge Base Chunks
$chunkStmt = $pdo->prepare('SELECT * FROM ai_knowledge_chunks WHERE user_id = ? ORDER BY id DESC');
$chunkStmt->execute([$userId]);
$knowledgeChunks = $chunkStmt->fetchAll();

// 4. Recent Human Escalations Log
$escStmt = $pdo->prepare(
    'SELECT e.*, c.phone, c.contact_name
     FROM chat_escalations e
     JOIN chat_conversations c ON e.conversation_id = c.id
     WHERE e.user_id = ?
     ORDER BY e.created_at DESC
     LIMIT 10'
);
$escStmt->execute([$userId]);
$recentEscalations = $escStmt->fetchAll();

// Active tab selector (rules, ai-agent, knowledge, escalations)
$activeTab = trim((string) ($_GET['tab'] ?? 'rules'));
if (!in_array($activeTab, ['rules', 'ai-agent', 'knowledge', 'escalations'], true)) {
    $activeTab = 'rules';
}
