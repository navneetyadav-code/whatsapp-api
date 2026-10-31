<?php
require_once __DIR__ . '/../src/bootstrap.php';

$pdo = Database::pdo();

$tools = [
    [
        'tool_slug' => 'draft_email',
        'display_name' => '📝 Email Drafting & Preview',
        'category' => 'builtin',
        'description' => 'Drafts an outbound email with recipient, subject line, and body, creating a pending action draft that awaits Boss confirmation before dispatch.',
        'parameters_schema' => [
            'type' => 'OBJECT',
            'required' => ['to', 'subject', 'body'],
            'properties' => [
                'to' => ['type' => 'STRING', 'description' => 'Recipient email address (e.g. client@example.com)'],
                'subject' => ['type' => 'STRING', 'description' => 'Clear, professional email subject line'],
                'body' => ['type' => 'STRING', 'description' => 'Full drafted email body content'],
                'cc' => ['type' => 'STRING', 'description' => 'Optional CC recipient email address']
            ]
        ],
        'execution_config' => [
            'handler' => 'draft_email'
        ],
        'rbac_roles' => ['admin']
    ],
    [
        'tool_slug' => 'confirm_action',
        'display_name' => '✅ Confirm & Dispatch Action',
        'category' => 'builtin',
        'description' => 'Executes or cancels an approved pending action draft (such as sending a drafted email via SMTP) after explicit confirmation.',
        'parameters_schema' => [
            'type' => 'OBJECT',
            'properties' => [
                'draft_code' => ['type' => 'STRING', 'description' => "The draft code to confirm or cancel (e.g. 'DRAFT-4821'). If omitted, targets latest pending draft."],
                'action' => ['type' => 'STRING', 'description' => "Action to take: 'confirm' (send/execute) or 'cancel' (discard draft). Defaults to 'confirm'."],
                'notes' => ['type' => 'STRING', 'description' => 'Optional notes or reason']
            ]
        ],
        'execution_config' => [
            'handler' => 'confirm_action'
        ],
        'rbac_roles' => ['admin']
    ]
];

$userIds = $pdo->query('SELECT id FROM users')->fetchAll(PDO::FETCH_COLUMN);

foreach ($userIds as $uid) {
    foreach ($tools as $t) {
        $stmt = $pdo->prepare('SELECT id FROM ai_custom_tools WHERE user_id = ? AND tool_slug = ? LIMIT 1');
        $stmt->execute([$uid, $t['tool_slug']]);
        if (!$stmt->fetch()) {
            $ins = $pdo->prepare('
                INSERT INTO ai_custom_tools 
                (user_id, tool_slug, display_name, category, description, parameters_schema, execution_config, rbac_roles, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
            ');
            $ins->execute([
                $uid,
                $t['tool_slug'],
                $t['display_name'],
                $t['category'],
                $t['description'],
                json_encode($t['parameters_schema'], JSON_UNESCAPED_SLASHES),
                json_encode($t['execution_config'], JSON_UNESCAPED_SLASHES),
                json_encode($t['rbac_roles'])
            ]);
            echo "Inserted {$t['tool_slug']} for user {$uid}" . PHP_EOL;
        } else {
            echo "Tool {$t['tool_slug']} already exists for user {$uid}" . PHP_EOL;
        }
    }
}

echo "Email Drafting Tools Seed Completed!" . PHP_EOL;
