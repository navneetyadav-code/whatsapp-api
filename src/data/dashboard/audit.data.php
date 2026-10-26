<?php
declare(strict_types=1);

$apiRequestCountStmt = $pdo->prepare('SELECT COUNT(*) FROM api_request_logs WHERE user_id = ?');
$apiRequestCountStmt->execute([(int) $user['id']]);
$apiRequestCount = (int) $apiRequestCountStmt->fetchColumn();

$auditPage = max(1, (int) ($_GET['p'] ?? 1));
$auditPerPage = 25;
$auditOffset = ($auditPage - 1) * $auditPerPage;
$totalAuditPages = max(1, (int) ceil($apiRequestCount / $auditPerPage));

$auditLogsStmt = $pdo->prepare('
    SELECT api_request_logs.*, api_keys.name AS key_name, api_keys.key_prefix 
    FROM api_request_logs 
    LEFT JOIN api_keys ON api_keys.id = api_request_logs.api_key_id 
    WHERE api_request_logs.user_id = ? 
    ORDER BY api_request_logs.id DESC 
    LIMIT ' . (int)$auditPerPage . ' OFFSET ' . (int)$auditOffset
);
$auditLogsStmt->execute([$user['id']]);
$auditLogs = $auditLogsStmt->fetchAll();
