<?php
require_once __DIR__ . '/../src/bootstrap.php';

$pdo = Database::pdo();

$usersStmt = $pdo->query("SELECT id FROM users");
$users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($users)) {
    echo "No users found in database.\n";
    exit(0);
}

$toolSlug = 'update_memory';
$displayName = '🧠 Remember Persistent Fact';
$category = 'builtin';
$description = 'Permanently saves or updates a durable fact, profile attribute, key entity (e.g. contact email, phone, role), or user preference into the Persistent Fact Notebook.';

$paramsSchema = json_encode([
    'type' => 'OBJECT',
    'required' => ['key', 'value'],
    'properties' => [
        'key' => [
            'type' => 'STRING',
            'description' => 'Short identifier for the fact (e.g. "boss_phone", "ankush_email", "office_hours", "preferred_tone")'
        ],
        'value' => [
            'type' => 'STRING',
            'description' => 'The exact factual information to remember permanently.'
        ],
        'memory_type' => [
            'type' => 'STRING',
            'enum' => ['fact', 'preference', 'entity', 'rule', 'profile'],
            'description' => 'Category of memory (default: "fact")'
        ]
    ]
], JSON_UNESCAPED_SLASHES);

$execConfig = json_encode([
    'handler' => 'update_memory',
    'description' => 'Autonomous persistent fact memory store'
], JSON_UNESCAPED_SLASHES);

$rbacRoles = json_encode(['admin'], JSON_UNESCAPED_SLASHES);

foreach ($users as $u) {
    $userId = (int)$u['id'];

    $checkStmt = $pdo->prepare("SELECT id FROM ai_custom_tools WHERE user_id = ? AND tool_slug = ? LIMIT 1");
    $checkStmt->execute([$userId, $toolSlug]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $updateStmt = $pdo->prepare("
            UPDATE ai_custom_tools 
            SET display_name = ?, description = ?, parameters_schema = ?, execution_config = ?, rbac_roles = ?, is_active = 1
            WHERE id = ?
        ");
        $updateStmt->execute([$displayName, $description, $paramsSchema, $execConfig, $rbacRoles, $existing['id']]);
        echo "Updated tool '$toolSlug' for user ID $userId.\n";
    } else {
        $insertStmt = $pdo->prepare("
            INSERT INTO ai_custom_tools 
            (user_id, tool_slug, display_name, category, description, parameters_schema, execution_config, rbac_roles, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        $insertStmt->execute([$userId, $toolSlug, $displayName, $category, $description, $paramsSchema, $execConfig, $rbacRoles]);
        echo "Inserted tool '$toolSlug' for user ID $userId.\n";
    }

    // Seed initial facts for user in ai_user_memory
    $initFacts = [
        ['fact', 'boss_name', 'Ankit', 'Initial Profile'],
        ['fact', 'organization', 'Assist Group', 'Initial Profile'],
        ['fact', 'primary_email', 'info.assistgroupofficial@gmail.com', 'Initial Profile'],
        ['entity', 'ankush_email', 'ankushkumary113@gmail.com (Developer / Collaborator)', 'Initial Profile']
    ];

    foreach ($initFacts as [$type, $key, $val, $src]) {
        $factStmt = $pdo->prepare("
            INSERT INTO ai_user_memory (user_id, memory_type, memory_key, memory_value, source_context)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE memory_value = VALUES(memory_value), source_context = VALUES(source_context), updated_at = NOW()
        ");
        $factStmt->execute([$userId, $type, $key, $val, $src]);
    }
    echo "Seeded initial facts in ai_user_memory for user ID $userId.\n";
}

echo "Done seeding update_memory tool and facts.\n";
