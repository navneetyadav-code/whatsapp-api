<?php
require_once __DIR__ . '/../src/bootstrap.php';

$pdo = Database::pdo();

$executionConfig = [
    'active_env' => 'production',
    'base_url' => 'https://assistgroup.gt.tc/api/admin/v1',
    'bearer_token' => 'ast_live_99584846bd475b06705f88383b0b523e5a50f18b744729376e1501ca3320645f',
    'timeout_ms' => 12000,
    'aliases' => [
        'metrics' => 'dashboard_metrics',
        'stats' => 'dashboard_metrics',
        'growth' => 'dashboard_growth',
        'system' => 'dashboard_system',
        'system_health' => 'dashboard_system',
        'plans' => 'pricing_plans',
        'get_pricing' => 'pricing_plans',
        'god_mode' => 'god_mode_list',
        'team' => 'team_overview',
        'get_team' => 'team_overview',
        'inquiries' => 'list_inquiries',
        'leads' => 'list_inquiries',
        'add_lead' => 'create_inquiry',
        'users' => 'list_users',
        'files' => 'list_files',
        'storage' => 'list_files',
        'cloudinary' => 'cloudinary_pool',
        'security_summary' => 'audit_logs'
    ],
    'action_routes' => [
        'dashboard_metrics' => [
            'endpoint' => '/dashboard/metrics',
            'method' => 'GET'
        ],
        'dashboard_growth' => [
            'endpoint' => '/dashboard/growth',
            'method' => 'GET'
        ],
        'dashboard_system' => [
            'endpoint' => '/dashboard/system',
            'method' => 'GET'
        ],
        'pricing_plans' => [
            'endpoint' => '/pricing/plans',
            'method' => 'GET'
        ],
        'god_mode_list' => [
            'endpoint' => '/pricing/god-mode',
            'method' => 'GET'
        ],
        'team_overview' => [
            'endpoint' => '/team',
            'method' => 'GET'
        ],
        'list_inquiries' => [
            'endpoint' => '/inquiries',
            'method' => 'GET',
            'query_params' => [
                'search' => '{query}'
            ]
        ],
        'get_inquiry' => [
            'endpoint' => '/inquiries/{inquiry_id}',
            'method' => 'GET'
        ],
        'create_inquiry' => [
            'endpoint' => '/inquiries',
            'method' => 'POST',
            'body_template' => [
                'name' => '{client_name|name}',
                'phone' => '{client_phone|phone}',
                'email' => '{client_email|email}',
                'service' => '{service}',
                'message' => '{requirement|message}'
            ]
        ],
        'update_inquiry_status' => [
            'endpoint' => '/inquiries/{inquiry_id}/status',
            'method' => 'PATCH',
            'body_template' => [
                'status' => '{status}'
            ]
        ],
        'list_users' => [
            'endpoint' => '/users',
            'method' => 'GET',
            'query_params' => [
                'search' => '{query}'
            ]
        ],
        'get_user' => [
            'endpoint' => '/users/{user_id}',
            'method' => 'GET'
        ],
        'list_files' => [
            'endpoint' => '/files',
            'method' => 'GET'
        ],
        'cloudinary_pool' => [
            'endpoint' => '/cloudinary',
            'method' => 'GET'
        ],
        'audit_logs' => [
            'endpoint' => '/audit-logs/summary',
            'method' => 'GET'
        ]
    ]
];

$stmt = $pdo->prepare('UPDATE ai_custom_tools SET execution_config = ? WHERE tool_slug = ?');
$stmt->execute([
    json_encode($executionConfig, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
    'assistgroup_crm'
]);

echo "Successfully updated assistgroup_crm: PRODUCTION ONLY! (Local development support removed)" . PHP_EOL;
