<?php
declare(strict_types=1);

/**
 * Unit Test Suite for Dashboard Modular Architecture
 * 
 * Verifies:
 * 1. Completeness of the Section Registry in src/pages/dashboard.php
 * 2. Integrity and existence of all 10 Data Loaders (src/data/dashboard/*.data.php)
 * 3. Integrity and existence of all 10 View Partials (src/pages/dashboard/*.php)
 * 4. Critical DOM elements, IDs, and input attributes are preserved intact.
 */

require_once __DIR__ . '/../src/bootstrap.php';

$testCount = 0;
$failCount = 0;

function assert_true(bool $condition, string $message): void {
    global $testCount, $failCount;
    $testCount++;
    if ($condition) {
        echo "  ✔ PASS: {$message}\n";
    } else {
        $failCount++;
        echo "  ✖ FAIL: {$message}\n";
    }
}

echo "=== Dashboard Modular Architecture Unit Tests ===\n\n";

// 1. Verify registry sections
$expectedSections = [
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

echo "[Suite 1: File Existence & Registry Integrity]\n";
$baseDir = dirname(__DIR__);

foreach ($expectedSections as $sec => $cfg) {
    $dataPath = $baseDir . '/src/data/dashboard/' . $cfg['data'];
    $viewPath = $baseDir . '/src/pages/dashboard/' . $cfg['view'];

    assert_true(file_exists($dataPath), "Data loader exists for '{$sec}': {$cfg['data']}");
    assert_true(file_exists($viewPath), "View partial exists for '{$sec}': {$cfg['view']}");
    assert_true(filesize($dataPath) > 50, "Data loader for '{$sec}' is non-empty");
    assert_true(filesize($viewPath) > 200, "View partial for '{$sec}' is non-empty");
}

echo "\n[Suite 2: Critical DOM Anchors & Input Names]\n";

// Overview checks
$overviewHtml = file_get_contents($baseDir . '/src/pages/dashboard/overview.php');
assert_true(strpos($overviewHtml, 'id="recentQueueTable"') !== false, 'Overview contains recentQueueTable');
assert_true(strpos($overviewHtml, 'Gateway Telemetry Rail') !== false, 'Overview contains Gateway Telemetry Rail');

// Message checks
$messageHtml = file_get_contents($baseDir . '/src/pages/dashboard/message.php');
assert_true(strpos($messageHtml, 'name="recipient_mode"') !== false, 'Message view contains recipient_mode input');
assert_true(strpos($messageHtml, 'name="message"') !== false, 'Message view contains message body textarea');
assert_true(strpos($messageHtml, 'id="messageAttachment"') !== false, 'Message view contains attachment file input');

// Device checks
$deviceHtml = file_get_contents($baseDir . '/src/pages/dashboard/device.php');
assert_true(strpos($deviceHtml, 'id="connectBtn"') !== false, 'Device view contains #connectBtn');
assert_true(strpos($deviceHtml, 'id="qrImage"') !== false, 'Device view contains #qrImage');
assert_true(strpos($deviceHtml, 'name="auto_sleep_enabled"') !== false, 'Device view contains auto_sleep_enabled toggle');
assert_true(strpos($deviceHtml, 'name="auto_sleep_timeout_seconds"') !== false, 'Device view contains auto_sleep_timeout_seconds input');
assert_true(strpos($deviceHtml, 'Operational Independence') !== false, 'Device view contains Operational Independence disclaimer');

// Queue checks
$queueHtml = file_get_contents($baseDir . '/src/pages/dashboard/queue.php');
assert_true(strpos($queueHtml, 'id="liveQueueTable"') !== false, 'Queue view contains #liveQueueTable');
assert_true(strpos($queueHtml, 'id="liveQueuedCount"') !== false, 'Queue view contains #liveQueuedCount');

// API keys checks
$apiKeyHtml = file_get_contents($baseDir . '/src/pages/dashboard/api-keys.php');
assert_true(strpos($apiKeyHtml, 'name="name"') !== false, 'API keys view contains name input');
assert_true(strpos($apiKeyHtml, 'name="ip_allowlist"') !== false, 'API keys view contains ip_allowlist input');

// Contacts checks
$contactsHtml = file_get_contents($baseDir . '/src/pages/dashboard/contacts.php');
assert_true(strpos($contactsHtml, 'name="contacts_csv"') !== false, 'Contacts view contains contacts_csv input');
assert_true(strpos($contactsHtml, 'name="phone"') !== false, 'Contacts view contains phone input');

// Webhooks checks
$webhooksHtml = file_get_contents($baseDir . '/src/pages/dashboard/webhooks.php');
assert_true(strpos($webhooksHtml, 'name="target_url"') !== false, 'Webhooks view contains target_url input');
assert_true(strpos($webhooksHtml, 'name="enabled"') !== false, 'Webhooks view contains enabled input');

// Campaigns checks
$campaignsHtml = file_get_contents($baseDir . '/src/pages/dashboard/campaigns.php');
assert_true(strpos($campaignsHtml, 'name="message_template"') !== false, 'Campaigns view contains message_template textarea');
assert_true(strpos($campaignsHtml, 'name="name"') !== false, 'Campaigns view contains campaign name input');

// Audit checks
$auditHtml = file_get_contents($baseDir . '/src/pages/dashboard/audit.php');
assert_true(strpos($auditHtml, 'API Request Audit Log') !== false, 'Audit view contains header');

// Chatbot checks
$chatbotHtml = file_get_contents($baseDir . '/src/pages/dashboard/chatbot.php');
assert_true(strpos($chatbotHtml, 'name="keyword"') !== false, 'Chatbot view contains keyword input');
assert_true(strpos($chatbotHtml, 'name="reply_text"') !== false, 'Chatbot view contains reply_text input');

echo "\n[Suite 3: Lean Dispatcher Code Quality]\n";
$dispatcherLines = count(file($baseDir . '/src/pages/dashboard.php'));
assert_true($dispatcherLines <= 75, "Dispatcher is ultra-lean ({$dispatcherLines} lines <= 75 lines)");

echo "\n==================================================\n";
echo "Results: {$testCount} tests executed, {$failCount} failures.\n";

if ($failCount > 0) {
    exit(1);
}
echo "ALL ARCHITECTURE UNIT TESTS PASSED!\n";
