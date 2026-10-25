<?php

declare(strict_types=1);

function test_assert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

echo "Running consent, suppression, and opt-out tests (Phase 6 / Phase 11.E)...\n";

$mqPath = dirname(__DIR__) . '/src/lib/MessageQueue.php';
test_assert(file_exists($mqPath), 'src/lib/MessageQueue.php must exist');
$mqSource = (string) file_get_contents($mqPath);

$migrationPath = dirname(__DIR__) . '/migrations/003_consent_and_message_store.sql';
test_assert(file_exists($migrationPath), 'migrations/003_consent_and_message_store.sql must exist');
$migrationSource = (string) file_get_contents($migrationPath);

$workerPath = dirname(__DIR__) . '/worker/server.mjs';
test_assert(file_exists($workerPath), 'worker/server.mjs must exist');
$workerSource = (string) file_get_contents($workerPath);

// 1. Verify recipient_suppressions schema exists and enforces tenant isolation
test_assert(
    str_contains($migrationSource, 'CREATE TABLE IF NOT EXISTS `recipient_suppressions`'),
    'Migration 003 must create recipient_suppressions table'
);
test_assert(
    str_contains($migrationSource, '`user_id` bigint(20) unsigned NOT NULL'),
    'recipient_suppressions must enforce tenant user_id column'
);
test_assert(
    str_contains($migrationSource, 'UNIQUE KEY `uniq_suppression_user_phone` (`user_id`, `normalized_phone`)'),
    'recipient_suppressions must enforce uniqueness per tenant and normalized phone'
);

// 2. Verify PHP helpers exist in MessageQueue.php
test_assert(
    str_contains($mqSource, 'function is_recipient_suppressed('),
    'MessageQueue.php must define is_recipient_suppressed()'
);
test_assert(
    str_contains($mqSource, 'function suppress_recipient('),
    'MessageQueue.php must define suppress_recipient()'
);
test_assert(
    str_contains($mqSource, 'function unsuppress_recipient('),
    'MessageQueue.php must define unsuppress_recipient()'
);

// 3. Verify opt-out cancels pending queued messages
test_assert(
    str_contains($mqSource, "SET status = 'cancelled', error_message = 'Recipient opted out / suppressed'")
    && str_contains($mqSource, "WHERE user_id = ? AND status = 'queued'"),
    'suppress_recipient() must cancel pending queued messages for that tenant and recipient'
);

// 4. Verify opted_in_contacts filters out suppressed recipients
test_assert(
    str_contains($mqSource, 'NOT EXISTS (') && str_contains($mqSource, 'recipient_suppressions'),
    'opted_in_contacts() must exclude contacts present in recipient_suppressions'
);

// 5. Verify worker enforces suppression at the final dispatch boundary
test_assert(
    str_contains($workerSource, 'isRecipientSuppressedInDb'),
    'worker/server.mjs must define isRecipientSuppressedInDb helper'
);
test_assert(
    str_contains($workerSource, 'Recipient is suppressed or opted out; cancelling message'),
    'sendQueuedMessage must abort send when recipient is suppressed'
);

// 6. Verify worker processes inbound STOP keywords without chatbot recursion
test_assert(
    str_contains($workerSource, 'OPT_OUT_KEYWORDS'),
    'worker/server.mjs must recognize standard opt-out keywords (STOP, UNSUBSCRIBE, OPT OUT)'
);
test_assert(
    str_contains($workerSource, 'Processed inbound opt-out command'),
    'worker/server.mjs must log inbound opt-out events'
);

// 7. Verify resubscription workflow
test_assert(
    str_contains($workerSource, 'OPT_IN_KEYWORDS'),
    'worker/server.mjs must recognize opt-in resubscription keywords (START, UNSTOP)'
);

echo "All consent, suppression, and opt-out tests passed successfully!\n";
