<?php
declare(strict_types=1);

$stats = message_stats((int) $user['id']);
$queuedCount = (int) ($stats['queued'] ?? 0);
$processingCount = (int) ($stats['processing'] ?? 0);
$failedCount = (int) ($stats['failed'] ?? 0);

$queuePage = max(1, (int) ($_GET['p'] ?? 1));
$queuePerPage = 20;
$totalQueueMessages = count_messages((int) $user['id']);
$totalPages = max(1, (int) ceil($totalQueueMessages / $queuePerPage));
$queueMessages = paginate_messages((int) $user['id'], $queuePage, $queuePerPage);
