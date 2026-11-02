<?php
declare(strict_types=1);

$userId = (int) ($user['id'] ?? ($_SESSION['user_id'] ?? 1));

// 1. Universal Custom Tools Catalog for this User
$toolsStmt = $pdo->prepare('SELECT * FROM ai_custom_tools WHERE user_id = ? ORDER BY id ASC');
$toolsStmt->execute([$userId]);
$userTools = $toolsStmt->fetchAll();

foreach ($userTools as &$ut) {
    $ut['parameters_schema'] = !empty($ut['parameters_schema']) ? json_decode((string) $ut['parameters_schema'], true) : [];
    $ut['execution_config'] = !empty($ut['execution_config']) ? json_decode((string) $ut['execution_config'], true) : [];
    $ut['rbac_roles'] = !empty($ut['rbac_roles']) ? json_decode((string) $ut['rbac_roles'], true) : ['guest', 'customer', 'admin'];
}
unset($ut);

// 2. AI Settings (for Whitelisted Admin Phones)
$aiStmt = $pdo->prepare('SELECT tools_config, agent_mode FROM ai_agent_settings WHERE user_id = ? LIMIT 1');
$aiStmt->execute([$userId]);
$aiRow = $aiStmt->fetch() ?: [];

$toolsConfigRaw = $aiRow['tools_config'] ?? null;
$toolsConfig = [];
if (!empty($toolsConfigRaw)) {
    $toolsConfig = json_decode((string) $toolsConfigRaw, true) ?: [];
}
if (!isset($toolsConfig['admin_phones']) || !is_array($toolsConfig['admin_phones'])) {
    $toolsConfig['admin_phones'] = [];
}
$agentMode = $aiRow['agent_mode'] ?? 'support';

// 3. Recent Tool Invocations
$invStmt = $pdo->prepare('SELECT * FROM ai_tool_invocations WHERE user_id = ? ORDER BY id DESC LIMIT 20');
$invStmt->execute([$userId]);
$recentInvocations = $invStmt->fetchAll();

// 4. App Store Presets & Installation Map (for Quick Add Tools)
require_once __DIR__ . '/app-store.data.php';
