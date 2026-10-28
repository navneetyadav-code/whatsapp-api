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
    'gemini_model' => 'gemini-3.5-flash-lite',
    'groq_model' => 'llama-3.3-70b-versatile',
    'openrouter_model' => 'meta-llama/llama-3.3-70b-instruct:free',
    'system_prompt' => "You are a friendly, helpful, and professional customer support assistant for our business on WhatsApp. Provide clear, accurate answers grounded in our official company knowledge base. When asked about something not in the knowledge base, politely explain that you don't have that information and offer to connect them to our support team.",
    'temperature' => '0.40',
    'max_tokens' => 500,
    'auto_reply_enabled' => 1,
    'frustration_threshold' => '0.65',
    'tools_config' => null
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

// 5. Tools Configuration & Recent Tool Invocations
$toolsConfigRaw = $aiSettings['tools_config'] ?? null;
$toolsConfig = [];
if (!empty($toolsConfigRaw)) {
    $toolsConfig = json_decode((string) $toolsConfigRaw, true) ?: [];
}
if (empty($toolsConfig)) {
    $toolsConfig = [
        'enabled' => true,
        'admin_phones' => ['+918581065107', '+919507286092', '8581065107', '9507286092'],
        'tools' => [
            'web_search' => ['enabled' => true, 'provider' => 'duckduckgo', 'max_results' => 3, 'timeout_ms' => 3500, 'roles' => ['guest', 'customer', 'admin']],
            'assistgroup_api' => [
                'enabled' => true,
                'active_env' => 'local',
                'environments' => [
                    'local' => ['base_url' => 'http://localhost/assistgroup/api/admin/v1', 'bearer_token' => ''],
                    'live' => ['base_url' => 'https://assistgroup.gt.tc/api/admin/v1', 'bearer_token' => '']
                ],
                'actions' => [
                    'get_services_pricing' => ['enabled' => true, 'roles' => ['guest', 'customer', 'admin']],
                    'create_inquiry' => ['enabled' => true, 'roles' => ['guest', 'customer', 'admin']],
                    'lookup_inquiries' => ['enabled' => true, 'roles' => ['customer', 'admin']],
                    'get_team_overview' => ['enabled' => true, 'roles' => ['guest', 'customer', 'admin']],
                    'admin_dashboard_stats' => ['enabled' => true, 'roles' => ['admin']]
                ]
            ],
            'evaluate_math' => ['enabled' => true, 'roles' => ['guest', 'customer', 'admin']],
            'get_current_time' => ['enabled' => true, 'timezone' => 'Asia/Kolkata', 'roles' => ['guest', 'customer', 'admin']],
            'escalate_to_human' => ['enabled' => true, 'roles' => ['guest', 'customer', 'admin']]
        ]
    ];
}

$invStmt = $pdo->prepare('SELECT * FROM ai_tool_invocations WHERE user_id = ? ORDER BY id DESC LIMIT 15');
$invStmt->execute([$userId]);
$recentInvocations = $invStmt->fetchAll();

// Active tab selector (rules, ai-agent, knowledge, escalations, tools)
$activeTab = trim((string) ($_GET['tab'] ?? 'rules'));
if (!in_array($activeTab, ['rules', 'ai-agent', 'knowledge', 'escalations', 'tools'], true)) {
    $activeTab = 'rules';
}
