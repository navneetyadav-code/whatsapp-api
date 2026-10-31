<?php
declare(strict_types=1);

$userId = (int) ($user['id'] ?? ($_SESSION['user_id'] ?? 1));

// Load installed tool slugs for this user
$installedStmt = $pdo->prepare('SELECT tool_slug, id, is_active FROM ai_custom_tools WHERE user_id = ?');
$installedStmt->execute([$userId]);
$installedRows = $installedStmt->fetchAll();
$installedMap = [];
foreach ($installedRows as $ir) {
    $installedMap[$ir['tool_slug']] = $ir;
}

// Preset Catalog Definition
$appStorePresets = [
    [
        'slug' => 'web_search',
        'title' => 'Web Search (DuckDuckGo)',
        'category' => 'builtin',
        'category_label' => 'Built-in Utility',
        'group' => 'General Intelligence',
        'icon' => '🌐',
        'badge_color' => 'sky',
        'recommended_mode' => 'Personal & Support',
        'short_description' => 'Live internet search for facts, news, and queries.',
        'description' => 'Autonomous live internet lookup. Answers questions regarding current events, product specs, latest news, and live facts without hallucinating.',
        'parameters' => [
            ['name' => 'query', 'type' => 'STRING', 'description' => 'Search keywords or query sentence', 'required' => true]
        ],
        'execution_config' => ['handler' => 'web_search', 'max_results' => 3, 'timeout_ms' => 6000],
        'rbac_roles' => ['guest', 'customer', 'admin']
    ],
    [
        'slug' => 'evaluate_math',
        'title' => 'Precision Math Evaluator',
        'category' => 'builtin',
        'category_label' => 'Built-in Utility',
        'group' => 'Calculations',
        'icon' => '🧮',
        'badge_color' => 'emerald',
        'recommended_mode' => 'Personal & Support',
        'short_description' => 'Exact mathematical formulas, percentages, and calculations.',
        'description' => 'Executes exact mathematical calculations, financial formulas, percentages, discounts, and compound interest safely with zero hallucination.',
        'parameters' => [
            ['name' => 'expression', 'type' => 'STRING', 'description' => 'Mathematical expression (e.g. "125000 * 0.18" or "sqrt(144)")', 'required' => true]
        ],
        'execution_config' => ['handler' => 'evaluate_math'],
        'rbac_roles' => ['guest', 'customer', 'admin']
    ],
    [
        'slug' => 'get_current_time',
        'title' => 'Business Hours & Clock',
        'category' => 'builtin',
        'category_label' => 'Built-in Utility',
        'group' => 'Scheduling',
        'icon' => '🕒',
        'badge_color' => 'slate',
        'recommended_mode' => 'Personal & Support',
        'short_description' => 'Current date/time, business hours, and timezone check.',
        'description' => 'Returns current IST/UTC date, day of week, and active business hours status. Observes operating hours in Support mode; 24/7 in Personal mode.',
        'parameters' => [],
        'execution_config' => ['handler' => 'get_current_time', 'timezone' => 'Asia/Kolkata', 'open_time' => '09:30', 'close_time' => '18:30'],
        'rbac_roles' => ['guest', 'customer', 'admin']
    ],
    [
        'slug' => 'get_weather',
        'title' => 'Real-Time Weather Forecaster',
        'category' => 'rest_api',
        'category_label' => 'Normal REST API',
        'group' => 'Forecasting',
        'icon' => '☁️',
        'badge_color' => 'blue',
        'recommended_mode' => 'Personal & Support',
        'short_description' => 'Real-time temperature and forecast for any city or coordinates.',
        'description' => 'Fetches live weather reports, temperature (°C), relative humidity, and weather codes for any city or GPS coordinates via Open-Meteo.',
        'parameters' => [
            ['name' => 'latitude', 'type' => 'NUMBER', 'description' => 'Latitude coordinate (e.g. 28.6139 for Delhi)', 'required' => true],
            ['name' => 'longitude', 'type' => 'NUMBER', 'description' => 'Longitude coordinate (e.g. 77.2090 for Delhi)', 'required' => true]
        ],
        'execution_config' => [
            'method' => 'GET',
            'url' => 'https://api.open-meteo.com/v1/forecast?latitude={latitude}&longitude={longitude}&current=temperature_2m,relative_humidity_2m,weather_code'
        ],
        'rbac_roles' => ['guest', 'customer', 'admin']
    ],
    [
        'slug' => 'draft_email',
        'title' => 'SMTP Email Drafter (Two-Phase)',
        'category' => 'builtin',
        'category_label' => 'Productivity Safety',
        'group' => 'Executive Productivity',
        'icon' => '📝',
        'badge_color' => 'violet',
        'recommended_mode' => 'Personal Assistant (Boss)',
        'short_description' => 'Drafts formal emails with two-phase boss approval.',
        'description' => 'Two-phase safety workflow: drafts formal emails, previews recipient and subject, and prompts Boss to approve before dispatching.',
        'parameters' => [
            ['name' => 'to', 'type' => 'STRING', 'description' => 'Recipient email address', 'required' => true],
            ['name' => 'subject', 'type' => 'STRING', 'description' => 'Subject line of email', 'required' => true],
            ['name' => 'body', 'type' => 'STRING', 'description' => 'Full email body text', 'required' => true],
            ['name' => 'cc', 'type' => 'STRING', 'description' => 'CC email address (optional)', 'required' => false]
        ],
        'execution_config' => ['handler' => 'draft_email'],
        'rbac_roles' => ['admin']
    ],
    [
        'slug' => 'confirm_action',
        'title' => 'Confirm & Dispatch Action',
        'category' => 'builtin',
        'category_label' => 'Productivity Safety',
        'group' => 'Executive Productivity',
        'icon' => '✅',
        'badge_color' => 'violet',
        'recommended_mode' => 'Personal Assistant (Boss)',
        'short_description' => 'Executes or cancels pending two-phase approval actions.',
        'description' => 'Second phase of Two-Phase Action protocol. Safely dispatches pending approved email drafts or cancels them upon instruction.',
        'parameters' => [
            ['name' => 'draft_code', 'type' => 'STRING', 'description' => 'Draft identifier (e.g. DRAFT-4821)', 'required' => false],
            ['name' => 'action', 'type' => 'STRING', 'description' => '"confirm" to dispatch or "cancel" to discard', 'required' => false]
        ],
        'execution_config' => ['handler' => 'confirm_action'],
        'rbac_roles' => ['admin']
    ],
    [
        'slug' => 'update_memory',
        'title' => 'Persistent Fact Memory Notebook',
        'category' => 'builtin',
        'category_label' => 'Long-Term Memory',
        'group' => 'Executive Productivity',
        'icon' => '🧠',
        'badge_color' => 'fuchsia',
        'recommended_mode' => 'Personal Assistant (Boss)',
        'short_description' => 'Stores permanent preferences, key facts, and user notes.',
        'description' => 'Stores permanent personal preferences, key facts, project notes, or collaborator contacts permanently in the AI notebook.',
        'parameters' => [
            ['name' => 'key', 'type' => 'STRING', 'description' => 'Short mnemonic key (e.g. favorite_airline, project_deadline)', 'required' => true],
            ['name' => 'value', 'type' => 'STRING', 'description' => 'Fact details to remember permanently', 'required' => true]
        ],
        'execution_config' => ['handler' => 'update_memory'],
        'rbac_roles' => ['admin']
    ],
    [
        'slug' => 'delete_memory',
        'title' => 'Forget Fact / Clear Memory',
        'category' => 'builtin',
        'category_label' => 'Long-Term Memory',
        'group' => 'Executive Productivity',
        'icon' => '🗑️',
        'badge_color' => 'rose',
        'recommended_mode' => 'Personal & Support',
        'short_description' => 'Erases outdated preferences, old addresses, or specific memories upon request.',
        'description' => 'Deletes a specific remembered key, customer attribute, or outdated fact permanently from the AI memory notebook upon natural language command.',
        'parameters' => [
            ['name' => 'key', 'type' => 'STRING', 'description' => 'Mnemonic key or topic to forget (e.g. delivery_address, favorite_airline)', 'required' => true]
        ],
        'execution_config' => ['handler' => 'delete_memory'],
        'rbac_roles' => ['guest', 'customer', 'admin']
    ],
    [
        'slug' => 'send_crm_lead',
        'title' => 'CRM Lead Outbound Webhook',
        'category' => 'webhook',
        'category_label' => 'Outbound Webhook',
        'group' => 'Integrations',
        'icon' => '🎣',
        'badge_color' => 'amber',
        'recommended_mode' => 'Support & Personal',
        'short_description' => 'Pushes qualified leads and contacts to CRM webhooks.',
        'description' => 'Automatically pushes interested customer leads, requirements, and contact phone numbers directly to your Zapier/Make/n8n webhook URL.',
        'parameters' => [
            ['name' => 'customer_name', 'type' => 'STRING', 'description' => 'Customer full name', 'required' => true],
            ['name' => 'customer_phone', 'type' => 'STRING', 'description' => 'Customer phone number', 'required' => true],
            ['name' => 'requirement', 'type' => 'STRING', 'description' => 'Summary of inquiry or lead requirement', 'required' => true]
        ],
        'execution_config' => [
            'target_url' => 'https://hooks.zapier.com/hooks/catch/sample',
            'body_template' => [
                'name' => '{customer_name}',
                'phone' => '{customer_phone}',
                'requirement' => '{requirement}',
                'timestamp' => '{timestamp}'
            ]
        ],
        'rbac_roles' => ['guest', 'customer', 'admin']
    ],
    [
        'slug' => 'escalate_to_human',
        'title' => 'Human Support Escalation Alert',
        'category' => 'builtin',
        'category_label' => 'Support Escalation',
        'group' => 'Customer Support Desk',
        'icon' => '🚨',
        'badge_color' => 'rose',
        'recommended_mode' => 'Support Bot Mode Only',
        'short_description' => 'Alerts human support and pauses automated AI replies.',
        'description' => 'Emergency handover tool for customer frustration. Pauses automated replies and triggers the live agent escalation queue. (Disabled in Personal Assistant mode).',
        'parameters' => [
            ['name' => 'reason', 'type' => 'STRING', 'description' => 'Detailed reason for human escalation', 'required' => true]
        ],
        'execution_config' => ['handler' => 'escalate_to_human'],
        'rbac_roles' => ['guest', 'customer', 'admin']
    ]
];

$activeFilter = trim((string) ($_GET['filter'] ?? 'all'));
