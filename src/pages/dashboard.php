<?php
declare(strict_types=1);

/**
 * WhatsGateway Modular Dashboard Dispatcher
 *
 * Decomposes monolithic dashboard into isolated data providers (src/data/dashboard/)
 * and view partials (src/pages/dashboard/) with lazy execution.
 *
 * Modular Subviews:
 * - overview:  src/pages/dashboard/overview.php
 * - live-chat: src/pages/dashboard/live-chat.php
 * - message:   src/pages/dashboard/message.php
 * - device:    src/pages/dashboard/device.php
 * - queue:     src/pages/dashboard/queue.php
 * - api-keys:  src/pages/dashboard/api-keys.php
 * - contacts:  src/pages/dashboard/contacts.php
 * - campaigns: src/pages/dashboard/campaigns.php
 * - webhooks:  src/pages/dashboard/webhooks.php
 * - audit:     src/pages/dashboard/audit.php
 * - chatbot:   src/pages/dashboard/chatbot.php
 */

$user = $user ?? require_auth();
$pdo = Database::pdo();
$agentMode = 'support';
try {
    $modeStmt = $pdo->prepare('SELECT agent_mode FROM ai_agent_settings WHERE user_id = ? LIMIT 1');
    $modeStmt->execute([(int) $user['id']]);
    $agentMode = (string) ($modeStmt->fetchColumn() ?: 'support');
} catch (\Throwable $e) {}
$isPersonalMode = ($agentMode === 'personal');
$section = (string) ($page ?? ($_GET['page'] ?? 'dashboard'));

// Personal Assistant Mode Guard: Redirect away if user directly visits Support Bot pages
if ($isPersonalMode && in_array($section, ['live-chat', 'knowledge', 'escalations'], true)) {
    flash('info', 'Company Knowledge Base, Live Chat, and Escalation Alarms are disabled while in Personal Assistant Mode.');
    redirect_to('index.php?page=chatbot');
}

// Backward compatibility: gracefully map legacy ?page=chatbot&tab=... to separate dedicated pages
if ($section === 'chatbot' && !empty($_GET['tab'])) {
    $tab = trim((string) $_GET['tab']);
    if ($tab === 'knowledge') {
        $section = 'knowledge';
    } elseif ($tab === 'tools') {
        $section = 'tools';
    } elseif ($tab === 'escalations') {
        $section = 'escalations';
    } elseif ($tab === 'presets' || $tab === 'app-store') {
        $section = 'app-store';
    }
}
$pdo = Database::pdo();

// Section registry mapping route keys to data loaders and isolated view partials
$sections = [
    'dashboard' => ['data' => 'overview.data.php',  'view' => 'overview.php'],
    'live-chat' => ['data' => 'live-chat.data.php', 'view' => 'live-chat.php'],
    'message'   => ['data' => 'message.data.php',   'view' => 'message.php'],
    'device'    => ['data' => 'device.data.php',    'view' => 'device.php'],
    'queue'     => ['data' => 'queue.data.php',     'view' => 'queue.php'],
    'api-keys'  => ['data' => 'api-keys.data.php',  'view' => 'api-keys.php'],
    'contacts'  => ['data' => 'contacts.data.php',  'view' => 'contacts.php'],
    'feedback'  => ['data' => 'feedback.data.php',  'view' => 'feedback.php'],
    'campaigns' => ['data' => 'campaigns.data.php', 'view' => 'campaigns.php'],
    'webhooks'  => ['data' => 'webhooks.data.php',  'view' => 'webhooks.php'],
    'audit'     => ['data' => 'audit.data.php',     'view' => 'audit.php'],
    'chatbot'     => ['data' => 'chatbot.data.php',     'view' => 'chatbot.php'],
    'ai-agent'    => ['data' => 'chatbot.data.php',     'view' => 'chatbot.php'],
    'knowledge'   => ['data' => 'knowledge.data.php',   'view' => 'knowledge.php'],
    'tools'       => ['data' => 'tools.data.php',       'view' => 'tools.php'],
    'app-store'   => ['data' => 'app-store.data.php',   'view' => 'app-store.php'],
    'presets'     => ['data' => 'app-store.data.php',   'view' => 'app-store.php'],
    'escalations' => ['data' => 'escalations.data.php', 'view' => 'escalations.php'],
    'rules'     => ['data' => 'rules.data.php',     'view' => 'rules.php'],
    'keyword-rules' => ['data' => 'rules.data.php', 'view' => 'rules.php'],
    'ai-credentials' => ['data' => 'ai-credentials.data.php', 'view' => 'ai-credentials.php'],
    'ai-keys'        => ['data' => 'ai-credentials.data.php', 'view' => 'ai-credentials.php'],
];

if (!isset($sections[$section])) {
    $section = 'dashboard';
}

// 1. Lazy load ONLY the data needed for the requested section
require_once dirname(__DIR__) . '/data/dashboard/' . $sections[$section]['data'];

if (!function_exists('page_header')) {
    function page_header(string $title, string $subtitle): void
    {
        ?>
        <div class="page-title overview-title">
            <div>
                <h1><?= e($title) ?></h1>
                <p class="muted"><?= e($subtitle) ?></p>
            </div>
        </div>
        <?php
    }
}
?>
<section class="dashboard <?= $section === 'live-chat' ? 'h-full flex flex-col min-h-0' : '' ?>" data-session-page="1">
    <?php require __DIR__ . '/dashboard/' . $sections[$section]['view']; ?>
</section>
<script src="<?= e(base_url('assets/wa-studio.js')) ?>?v=<?= time() ?>"></script>
<script src="<?= e(base_url('assets/dashboard.js')) ?>?v=<?= time() ?>"></script>
