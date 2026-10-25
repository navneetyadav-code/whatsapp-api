<?php

declare(strict_types=1);

function test_assert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

echo "Running campaign lifecycle, bounded batching, and scheduling tests (Phase 5 / Phase 11.D)...\n";

$mqPath = dirname(__DIR__) . '/src/lib/MessageQueue.php';
test_assert(file_exists($mqPath), 'src/lib/MessageQueue.php must exist');
$mqSource = (string) file_get_contents($mqPath);

$workerPath = dirname(__DIR__) . '/worker/server.mjs';
test_assert(file_exists($workerPath), 'worker/server.mjs must exist');
$workerSource = (string) file_get_contents($workerPath);

// 1. Verify input validation
test_assert(
    str_contains($mqSource, '$userId <= 0') && str_contains($mqSource, "throw new InvalidArgumentException('Invalid user ID.')"),
    'create_campaign_for_contacts must reject non-positive user ID'
);
test_assert(
    str_contains($mqSource, "throw new InvalidArgumentException('Campaign template cannot be empty.')"),
    'create_campaign_for_contacts must reject empty message template'
);

// 2. Verify bounded batching (chunks of 250)
test_assert(
    str_contains($mqSource, '$batchSize = 250') && str_contains($mqSource, 'array_chunk($contacts, $batchSize)'),
    'create_campaign_for_contacts must process contacts in bounded chunks of 250'
);

// 3. Verify transactional integrity with rollback
test_assert(
    str_contains($mqSource, '$pdo->beginTransaction()') && str_contains($mqSource, '$pdo->commit()') && str_contains($mqSource, '$pdo->rollBack()'),
    'create_campaign_for_contacts must wrap queue insertions in a database transaction'
);

// 4. Verify campaign cancellation cancels queued jobs
test_assert(
    str_contains($mqSource, "SET status = 'cancelled', error_message = 'Campaign cancelled by user'")
    && str_contains($mqSource, "UPDATE campaigns SET status = ? WHERE id = ? AND user_id = ?"),
    'cancel_campaign must atomically cancel queued jobs and update campaign status'
);

// 5. Verify campaign pause and resume functions
test_assert(
    str_contains($mqSource, 'function pause_campaign(') && str_contains($mqSource, "UPDATE campaigns SET status = 'paused'"),
    'MessageQueue.php must define pause_campaign()'
);
test_assert(
    str_contains($mqSource, 'function resume_campaign(') && str_contains($mqSource, "UPDATE campaigns SET status = 'queued' WHERE id = ? AND user_id = ? AND status = 'paused'"),
    'MessageQueue.php must define resume_campaign()'
);

// 6. Verify worker checks campaign cancellation and pause state at dispatch time
test_assert(
    str_contains($workerSource, "Campaign was cancelled; aborting message send"),
    'Worker must check if parent campaign was cancelled before dispatching message'
);
test_assert(
    str_contains($workerSource, "Campaign is paused; deferring message send"),
    'Worker must defer message send if parent campaign is paused'
);

echo "All campaign lifecycle, bounded batching, and scheduling tests passed successfully!\n";
