<?php

declare(strict_types=1);

function enqueue_message(int $userId, ?int $apiKeyId, string $to, string $message, array $payload = [], int $priority = 5): int
{
    $effectivePriority = isset($payload['priority']) ? (int) $payload['priority'] : $priority;
    $effectivePriority = max(1, min(100, $effectivePriority));

    $stmt = Database::pdo()->prepare(
        'INSERT INTO message_queue (user_id, api_key_id, recipient, message_type, body, payload, priority)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $userId,
        $apiKeyId,
        trim($to),
        $payload['type'] ?? 'text',
        $message,
        json_encode($payload, JSON_UNESCAPED_SLASHES),
        $effectivePriority,
    ]);

    $insertId = (int) Database::pdo()->lastInsertId();

    try {
        worker_request('POST', 'queue/notify', ['user_id' => $userId], 1);
    } catch (\Throwable $e) {
    }

    return $insertId;
}

function api_key_rate_status(array $apiKey): array
{
    $pdo = Database::pdo();
    $stmt = $pdo->prepare(
        "SELECT
            SUM(created_at >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)) AS per_minute,
            SUM(created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)) AS per_hour,
            SUM(created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)) AS per_day
         FROM message_queue
         WHERE api_key_id = ? AND status <> 'cancelled'"
    );
    $stmt->execute([(int) $apiKey['id']]);
    $row = $stmt->fetch() ?: [];

    $counts = [
        'per_minute' => (int) ($row['per_minute'] ?? 0),
        'per_hour' => (int) ($row['per_hour'] ?? 0),
        'per_day' => (int) ($row['per_day'] ?? 0),
    ];

    $limits = [
        'per_minute' => (int) $apiKey['rate_per_minute'],
        'per_hour' => (int) $apiKey['rate_per_hour'],
        'per_day' => (int) $apiKey['rate_per_day'],
    ];

    return [
        'allowed' => $counts['per_minute'] < $limits['per_minute']
            && $counts['per_hour'] < $limits['per_hour']
            && $counts['per_day'] < $limits['per_day'],
        'counts' => $counts,
        'limits' => $limits,
    ];
}

function log_api_request(int $userId, ?int $apiKeyId, string $endpoint, int $statusCode, ?string $error = null): string
{
    $requestId = bin2hex(random_bytes(12));
    $stmt = Database::pdo()->prepare(
        'INSERT INTO api_request_logs (user_id, api_key_id, endpoint, ip_address, status_code, request_id, error_message)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$userId, $apiKeyId, $endpoint, client_ip(), $statusCode, $requestId, $error]);
    return $requestId;
}

function message_stats(int $userId): array
{
    $stmt = Database::pdo()->prepare(
        "SELECT status, COUNT(*) AS total FROM message_queue WHERE user_id = ? GROUP BY status"
    );
    $stmt->execute([$userId]);
    $stats = ['queued' => 0, 'processing' => 0, 'sent' => 0, 'delivered' => 0, 'read' => 0, 'failed' => 0, 'cancelled' => 0];
    foreach ($stmt->fetchAll() as $row) {
        $stats[$row['status']] = (int) $row['total'];
    }

    return $stats;
}

function recent_messages(int $userId, int $limit = 25): array
{
    $stmt = Database::pdo()->prepare(
        'SELECT message_queue.*, api_keys.key_prefix, contacts.name AS contact_name
         FROM message_queue
         LEFT JOIN api_keys ON api_keys.id = message_queue.api_key_id
         LEFT JOIN contacts ON contacts.user_id = message_queue.user_id 
             AND (contacts.phone = message_queue.recipient OR contacts.phone = message_queue.normalized_recipient)
         WHERE message_queue.user_id = ?
         ORDER BY message_queue.id DESC
         LIMIT ' . max(1, min(100, $limit))
    );
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function retry_message(int $userId, int $messageId): void
{
    $stmt = Database::pdo()->prepare(
        "UPDATE message_queue
         SET status = 'queued', attempts = 0, error_message = NULL, locked_at = NULL, locked_by = NULL, available_at = NOW()
         WHERE id = ? AND user_id = ? AND status IN ('failed','cancelled')"
    );
    $stmt->execute([$messageId, $userId]);
}

function cancel_message(int $userId, int $messageId): void
{
    $stmt = Database::pdo()->prepare(
        "UPDATE message_queue SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status = 'queued'"
    );
    $stmt->execute([$messageId, $userId]);
}

function is_recipient_suppressed(int $userId, string $phone): bool
{
    $clean = preg_replace('/\D/', '', $phone);
    if ($clean === '' || $userId <= 0) {
        return false;
    }
    $pdo = Database::pdo();
    try {
        $stmt = $pdo->prepare(
            'SELECT 1 FROM recipient_suppressions 
             WHERE user_id = ? AND (normalized_phone = ? OR phone = ?)
             LIMIT 1'
        );
        $stmt->execute([$userId, $clean, $phone]);
        return (bool) $stmt->fetchColumn();
    } catch (\Throwable $e) {
        return false;
    }
}

function suppress_recipient(int $userId, string $phone, string $reason = 'opt_out', string $source = 'keyword'): bool
{
    $clean = preg_replace('/\D/', '', $phone);
    if ($clean === '' || $userId <= 0) {
        return false;
    }
    $pdo = Database::pdo();
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO recipient_suppressions (user_id, phone, normalized_phone, reason, source)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE reason = VALUES(reason), source = VALUES(source)'
        );
        $stmt->execute([$userId, $phone, $clean, $reason, $source]);

        // Also update contacts table if present
        $updateContact = $pdo->prepare('UPDATE contacts SET opt_in = 0 WHERE user_id = ? AND (phone = ? OR normalized_phone = ?)');
        $updateContact->execute([$userId, $phone, $clean]);

        // Cancel any pending queued messages to this recipient
        $cancelQueue = $pdo->prepare(
            "UPDATE message_queue
             SET status = 'cancelled', error_message = 'Recipient opted out / suppressed'
             WHERE user_id = ? AND status = 'queued' AND (recipient = ? OR normalized_recipient = ?)"
        );
        $cancelQueue->execute([$userId, $phone, $clean]);

        return true;
    } catch (\Throwable $e) {
        return false;
    }
}

function unsuppress_recipient(int $userId, string $phone): bool
{
    $clean = preg_replace('/\D/', '', $phone);
    if ($clean === '' || $userId <= 0) {
        return false;
    }
    $pdo = Database::pdo();
    try {
        $stmt = $pdo->prepare('DELETE FROM recipient_suppressions WHERE user_id = ? AND (normalized_phone = ? OR phone = ?)');
        $stmt->execute([$userId, $clean, $phone]);

        $updateContact = $pdo->prepare('UPDATE contacts SET opt_in = 1 WHERE user_id = ? AND (phone = ? OR normalized_phone = ?)');
        $updateContact->execute([$userId, $phone, $clean]);

        return true;
    } catch (\Throwable $e) {
        return false;
    }
}

function create_campaign(int $userId, string $name, string $template, ?string $availableAt = null, ?array $attachment = null): int
{
    $contacts = opted_in_contacts($userId);
    return create_campaign_for_contacts($userId, $name, $template, $contacts, $availableAt, $attachment);
}

function create_campaign_for_contacts(
    int $userId,
    string $name,
    string $template,
    array $contacts,
    ?string $availableAt = null,
    ?array $attachment = null
): int {
    if ($userId <= 0) {
        throw new InvalidArgumentException('Invalid user ID.');
    }
    $template = trim($template);
    if ($template === '') {
        throw new InvalidArgumentException('Campaign template cannot be empty.');
    }

    $pdo = Database::pdo();
    $pdo->beginTransaction();
    try {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO campaigns (user_id, name, message_template, target_count, scheduled_at) VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([$userId, trim($name) ?: 'Untitled campaign', $template, count($contacts), $availableAt ?: null]);
        } catch (\Throwable $e) {
            $stmt = $pdo->prepare(
                'INSERT INTO campaigns (user_id, name, message_template, target_count) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$userId, trim($name) ?: 'Untitled campaign', $template, count($contacts)]);
        }
        $campaignId = (int) $pdo->lastInsertId();

        $queued = 0;
        $batchSize = 250;
        $chunks = array_chunk($contacts, $batchSize);
        $messageType = !empty($attachment['type']) ? (string) $attachment['type'] : 'text';

        foreach ($chunks as $chunk) {
            foreach ($chunk as $contact) {
                $recipientPhone = (string) ($contact['phone'] ?? '');
                if ($recipientPhone === '') {
                    continue;
                }

                // Check suppression at campaign creation boundary
                if (is_recipient_suppressed($userId, $recipientPhone)) {
                    continue;
                }

                $body = apply_contact_template($template, $contact);
                $payloadData = [
                    'source' => 'campaign',
                    'campaign_id' => $campaignId,
                    'contact_id' => (int) ($contact['id'] ?? 0),
                    'type' => $messageType,
                ];
                if (!empty($attachment)) {
                    $payloadData['attachment'] = $attachment;
                }

                if ($availableAt) {
                    $queue = $pdo->prepare(
                        'INSERT INTO message_queue (user_id, api_key_id, recipient, message_type, body, payload, priority, available_at)
                         VALUES (?, NULL, ?, ?, ?, ?, 1, ?)'
                    );
                    $queue->execute([
                        $userId,
                        $recipientPhone,
                        $messageType,
                        $body,
                        json_encode($payloadData, JSON_UNESCAPED_SLASHES),
                        $availableAt,
                    ]);
                } else {
                    $queue = $pdo->prepare(
                        'INSERT INTO message_queue (user_id, api_key_id, recipient, message_type, body, payload, priority)
                         VALUES (?, NULL, ?, ?, ?, ?, 1)'
                    );
                    $queue->execute([
                        $userId,
                        $recipientPhone,
                        $messageType,
                        $body,
                        json_encode($payloadData, JSON_UNESCAPED_SLASHES),
                    ]);
                }
                $queued++;
            }
        }

        $pdo->prepare('UPDATE campaigns SET queued_count = ?, status = ? WHERE id = ?')
            ->execute([$queued, $queued > 0 ? 'queued' : 'completed', $campaignId]);
        $pdo->commit();

        try {
            worker_request('POST', 'queue/notify', ['user_id' => $userId], 1);
        } catch (\Throwable $e) {
        }

        return $campaignId;
    } catch (\Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
}

function opted_in_contacts(int $userId): array
{
    $pdo = Database::pdo();
    try {
        $stmt = $pdo->prepare(
            'SELECT c.* FROM contacts c
             WHERE c.user_id = ? AND c.opt_in = 1
               AND NOT EXISTS (
                   SELECT 1 FROM recipient_suppressions s
                   WHERE s.user_id = c.user_id
                     AND (s.normalized_phone = c.normalized_phone OR s.phone = c.phone)
               )
             ORDER BY c.id ASC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    } catch (\Throwable $e) {
        // Fallback if migration 003 is pending
        $fallback = $pdo->prepare('SELECT * FROM contacts WHERE user_id = ? AND opt_in = 1 ORDER BY id ASC');
        $fallback->execute([$userId]);
        return $fallback->fetchAll();
    }
}

function apply_contact_template(string $template, array $contact): string
{
    return strtr($template, [
        '{name}' => (string) ($contact['name'] ?? ''),
        '{phone}' => (string) ($contact['phone'] ?? ''),
    ]);
}

function recent_campaigns(int $userId, int $limit = 8): array
{
    sync_campaign_statuses($userId);
    $stmt = Database::pdo()->prepare(
        'SELECT * FROM campaigns WHERE user_id = ? ORDER BY id DESC LIMIT ' . max(1, min(50, $limit))
    );
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function get_campaign(int $userId, int $campaignId): ?array
{
    sync_campaign_statuses($userId);
    $stmt = Database::pdo()->prepare(
        'SELECT * FROM campaigns WHERE id = ? AND user_id = ? LIMIT 1'
    );
    $stmt->execute([$campaignId, $userId]);
    $campaign = $stmt->fetch();

    return $campaign ?: null;
}

function update_campaign(int $userId, int $campaignId, string $name, string $template): void
{
    $stmt = Database::pdo()->prepare(
        'UPDATE campaigns SET name = ?, message_template = ? WHERE id = ? AND user_id = ?'
    );
    $stmt->execute([trim($name) ?: 'Untitled campaign', $template, $campaignId, $userId]);
}

function cancel_campaign(int $userId, int $campaignId): void
{
    $pdo = Database::pdo();
    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare(
            "UPDATE message_queue
             SET status = 'cancelled', error_message = 'Campaign cancelled by user'
             WHERE user_id = ?
               AND status = 'queued'
               AND JSON_UNQUOTE(JSON_EXTRACT(payload, '$.source')) = 'campaign'
               AND JSON_EXTRACT(payload, '$.campaign_id') = ?"
        );
        $stmt->execute([$userId, $campaignId]);

        $pdo->prepare('UPDATE campaigns SET status = ? WHERE id = ? AND user_id = ?')
            ->execute(['cancelled', $campaignId, $userId]);

        $pdo->commit();
    } catch (\Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
}

function pause_campaign(int $userId, int $campaignId): void
{
    $pdo = Database::pdo();
    $pdo->prepare("UPDATE campaigns SET status = 'paused' WHERE id = ? AND user_id = ? AND status = 'queued'")
        ->execute([$campaignId, $userId]);
}

function resume_campaign(int $userId, int $campaignId): void
{
    $pdo = Database::pdo();
    $pdo->prepare("UPDATE campaigns SET status = 'queued' WHERE id = ? AND user_id = ? AND status = 'paused'")
        ->execute([$campaignId, $userId]);
}

function campaign_contacts(int $userId, int $campaignId): array
{
    $stmt = Database::pdo()->prepare(
        "SELECT DISTINCT contacts.*
         FROM message_queue
         INNER JOIN contacts
             ON contacts.id = JSON_UNQUOTE(JSON_EXTRACT(message_queue.payload, '$.contact_id'))
            AND contacts.user_id = message_queue.user_id
         WHERE message_queue.user_id = ?
           AND JSON_UNQUOTE(JSON_EXTRACT(message_queue.payload, '$.source')) = 'campaign'
           AND JSON_EXTRACT(message_queue.payload, '$.campaign_id') = ?
         ORDER BY contacts.name ASC, contacts.id ASC"
    );
    $stmt->execute([$userId, $campaignId]);
    return $stmt->fetchAll();
}

function resend_campaign(int $userId, int $campaignId): ?int
{
    $campaign = get_campaign($userId, $campaignId);
    if (!$campaign) {
        return null;
    }

    $contacts = campaign_contacts($userId, $campaignId);
    if (!$contacts) {
        $contacts = opted_in_contacts($userId);
    }

    return create_campaign_for_contacts(
        $userId,
        trim((string) $campaign['name']) . ' (Resend)',
        (string) $campaign['message_template'],
        $contacts
    );
}

function sync_campaign_statuses(int $userId): void
{
    $pdo = Database::pdo();
    $stmt = $pdo->prepare(
        "SELECT campaigns.id,
                campaigns.status AS campaign_status,
                COUNT(message_queue.id) AS total_messages,
                SUM(message_queue.status IN ('queued', 'processing')) AS active_messages,
                SUM(message_queue.status = 'cancelled') AS cancelled_messages
         FROM campaigns
         LEFT JOIN message_queue
            ON message_queue.user_id = campaigns.user_id
           AND JSON_UNQUOTE(JSON_EXTRACT(message_queue.payload, '$.source')) = 'campaign'
           AND JSON_EXTRACT(message_queue.payload, '$.campaign_id') = campaigns.id
         WHERE campaigns.user_id = ?
         GROUP BY campaigns.id, campaigns.status"
    );
    $stmt->execute([$userId]);

    $update = $pdo->prepare('UPDATE campaigns SET status = ? WHERE id = ? AND user_id = ?');
    foreach ($stmt->fetchAll() as $row) {
        $totalMessages = (int) ($row['total_messages'] ?? 0);
        $activeMessages = (int) ($row['active_messages'] ?? 0);
        $cancelledMessages = (int) ($row['cancelled_messages'] ?? 0);
        $currentStatus = (string) ($row['campaign_status'] ?? 'queued');

        // Do not alter paused or cancelled states if explicitly set
        if ($currentStatus === 'paused' || $currentStatus === 'cancelled') {
            continue;
        }

        if ($activeMessages > 0) {
            $nextStatus = 'queued';
        } elseif ($totalMessages > 0 && $cancelledMessages === $totalMessages) {
            $nextStatus = 'cancelled';
        } elseif ($totalMessages > 0) {
            $nextStatus = 'completed';
        } else {
            $nextStatus = $currentStatus;
        }

        if ($nextStatus !== $currentStatus) {
            $update->execute([$nextStatus, (int) $row['id'], $userId]);
        }
    }
}

function paginate_messages(int $userId, int $page = 1, int $perPage = 25): array
{
    $offset = max(0, ($page - 1) * $perPage);
    $stmt = Database::pdo()->prepare(
        'SELECT message_queue.*, api_keys.key_prefix
         FROM message_queue
         LEFT JOIN api_keys ON api_keys.id = message_queue.api_key_id
         WHERE message_queue.user_id = ?
         ORDER BY message_queue.id DESC
         LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset
    );
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function count_messages(int $userId): int
{
    $stmt = Database::pdo()->prepare('SELECT COUNT(*) FROM message_queue WHERE user_id = ?');
    $stmt->execute([$userId]);
    return (int) $stmt->fetchColumn();
}


/**
 * Retrieves aggregated message analytics for dashboard charts (Sent vs Received, By Type, Top Chats)
 * Period options: '24h', '7d', '30d'
 */
function get_dashboard_analytics_stats(int $userId, string $period = '24h'): array
{
    $pdo = Database::pdo();
    $validPeriods = ['24h', '7d', '30d'];
    if (!in_array($period, $validPeriods, true)) {
        $period = '24h';
    }

    $dbNow = $pdo->query('SELECT NOW()')->fetchColumn() ?: date('Y-m-d H:i:s');
    $baseTime = strtotime((string) $dbNow);

    if ($period === '7d') {
        $groupBy = '%Y-%m-%d';
        $intervalSql = 'INTERVAL 7 DAY';
    } elseif ($period === '30d') {
        $groupBy = '%Y-%m-%d';
        $intervalSql = 'INTERVAL 30 DAY';
    } else { // 24h
        $groupBy = '%Y-%m-%d %H:00';
        $intervalSql = 'INTERVAL 24 HOUR';
    }

    // 1. Time Series (Sent vs Received from chat_messages)
    $tsStmt = $pdo->prepare("
        SELECT
            DATE_FORMAT(created_at, '$groupBy') AS time_slot,
            SUM(CASE WHEN sender_type IN ('bot', 'agent') THEN 1 ELSE 0 END) AS sent_count,
            SUM(CASE WHEN sender_type = 'contact' THEN 1 ELSE 0 END) AS received_count,
            SUM(CASE WHEN sender_type IN ('bot', 'agent') AND status IN ('delivered', 'read') THEN 1 ELSE 0 END) AS delivered_count,
            SUM(CASE WHEN sender_type IN ('bot', 'agent') AND status = 'failed' THEN 1 ELSE 0 END) AS failed_count
        FROM chat_messages
        WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), $intervalSql)
        GROUP BY time_slot
        ORDER BY time_slot ASC
    ");
    $tsStmt->execute([$userId]);
    $indexed = [];
    foreach ($tsStmt->fetchAll(PDO::FETCH_ASSOC) as $pt) {
        $indexed[$pt['time_slot']] = [
            'sent' => (int) $pt['sent_count'],
            'received' => (int) $pt['received_count'],
            'delivered' => (int) $pt['delivered_count'],
            'failed' => (int) $pt['failed_count'],
        ];
    }

    $timeSeries = [];
    if ($period === '24h') {
        for ($i = 23; $i >= 0; $i--) {
            $t = $baseTime - ($i * 3600);
            $slotKey = date('Y-m-d H:00', $t);
            $label = date('H:i', $t);
            $timeSeries[] = [
                'timestamp' => $slotKey,
                'label' => $label,
                'sent' => $indexed[$slotKey]['sent'] ?? 0,
                'received' => $indexed[$slotKey]['received'] ?? 0,
                'delivered' => $indexed[$slotKey]['delivered'] ?? 0,
                'failed' => $indexed[$slotKey]['failed'] ?? 0,
            ];
        }
    } else {
        $days = ($period === '7d') ? 6 : 29;
        for ($i = $days; $i >= 0; $i--) {
            $t = $baseTime - ($i * 86400);
            $slotKey = date('Y-m-d', $t);
            $label = date('M j', $t);
            $timeSeries[] = [
                'timestamp' => $slotKey,
                'label' => $label,
                'sent' => $indexed[$slotKey]['sent'] ?? 0,
                'received' => $indexed[$slotKey]['received'] ?? 0,
                'delivered' => $indexed[$slotKey]['delivered'] ?? 0,
                'failed' => $indexed[$slotKey]['failed'] ?? 0,
            ];
        }
    }

    // 2. By Message Type
    $typeStmt = $pdo->prepare("
        SELECT
            COALESCE(NULLIF(message_type, ''), 'text') AS mtype,
            COUNT(*) AS cnt
        FROM chat_messages
        WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), $intervalSql)
        GROUP BY mtype
    ");
    $typeStmt->execute([$userId]);
    $byType = ['text' => 0, 'image' => 0, 'document' => 0, 'audio' => 0, 'video' => 0];
    foreach ($typeStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $mtype = strtolower((string) $row['mtype']);
        if (!isset($byType[$mtype])) {
            $byType[$mtype] = 0;
        }
        $byType[$mtype] += (int) $row['cnt'];
    }

    // 3. Top Active Chats
    $topStmt = $pdo->prepare("
        SELECT
            c.id,
            c.phone,
            c.contact_name,
            COUNT(m.id) AS message_count
        FROM chat_conversations c
        JOIN chat_messages m ON m.conversation_id = c.id
        WHERE c.user_id = ? AND m.created_at >= DATE_SUB(NOW(), $intervalSql)
        GROUP BY c.id, c.phone, c.contact_name
        ORDER BY message_count DESC
        LIMIT 8
    ");
    $topStmt->execute([$userId]);
    $topChats = [];
    foreach ($topStmt->fetchAll(PDO::FETCH_ASSOC) as $tc) {
        $name = !empty($tc['contact_name']) ? (string) $tc['contact_name'] : (string) $tc['phone'];
        $topChats[] = [
            'chatId' => (string) $tc['phone'],
            'name' => $name,
            'count' => (int) $tc['message_count'],
        ];
    }

    return [
        'period' => $period,
        'timeSeries' => $timeSeries,
        'byType' => $byType,
        'topChats' => $topChats,
        'totals' => [
            'sent' => array_sum(array_column($timeSeries, 'sent')),
            'received' => array_sum(array_column($timeSeries, 'received')),
            'delivered' => array_sum(array_column($timeSeries, 'delivered')),
            'failed' => array_sum(array_column($timeSeries, 'failed')),
            'total' => array_sum($byType),
        ],
    ];
}