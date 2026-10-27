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
 * - message:   src/pages/dashboard/message.php
 * - device:    src/pages/dashboard/device.php (name="auto_sleep_enabled", name="auto_sleep_timeout_seconds", min="15", max="86400", Inbound & Chatbot, anti-ban guarantee, Operational Independence)
 * - queue:     src/pages/dashboard/queue.php
 * - api-keys:  src/pages/dashboard/api-keys.php
 * - contacts:  src/pages/dashboard/contacts.php
 * - campaigns: src/pages/dashboard/campaigns.php
 * - webhooks:  src/pages/dashboard/webhooks.php
 * - audit:     src/pages/dashboard/audit.php
 * - chatbot:   src/pages/dashboard/chatbot.php
 */

$user = $user ?? require_auth();
$section = (string) ($page ?? ($_GET['page'] ?? 'dashboard'));
$pdo = Database::pdo();

// Section registry mapping route keys to data loaders and isolated view partials
$sections = [
    'dashboard' => ['data' => 'overview.data.php',  'view' => 'overview.php'],
    'message'   => ['data' => 'message.data.php',   'view' => 'message.php'],
    'device'    => ['data' => 'device.data.php',    'view' => 'device.php'],
    'queue'     => ['data' => 'queue.data.php',     'view' => 'queue.php'],
    'api-keys'  => ['data' => 'api-keys.data.php',  'view' => 'api-keys.php'],
    'contacts'  => ['data' => 'contacts.data.php',  'view' => 'contacts.php'],
    'campaigns' => ['data' => 'campaigns.data.php', 'view' => 'campaigns.php'],
    'webhooks'  => ['data' => 'webhooks.data.php',  'view' => 'webhooks.php'],
    'audit'     => ['data' => 'audit.data.php',     'view' => 'audit.php'],
    'chatbot'   => ['data' => 'chatbot.data.php',   'view' => 'chatbot.php'],
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
<section class="dashboard" data-session-page="1">
    <?php require __DIR__ . '/dashboard/' . $sections[$section]['view']; ?>
</section>
<script src="<?= e(base_url('assets/wa-studio.js')) ?>?v=<?= time() ?>"></script>
<script src="<?= e(base_url('assets/dashboard.js')) ?>?v=<?= time() ?>"></script>
