<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

// Support method overriding via _method query/body or X-HTTP-Method-Override header
if ($method === 'POST') {
    $override = strtoupper((string) ($_REQUEST['_method'] ?? $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? ''));
    if (in_array($override, ['DELETE', 'PUT', 'PATCH'], true)) {
        $method = $override;
    }
}

// -------------------------------------------------------------------------
// 1. Authenticate Request (API Key / Bearer Token or Protected Web Session)
// -------------------------------------------------------------------------
$user = authenticate_api_request();

// If not using API Key credentials, check active web dashboard session
if (!$user) {
    $sessionUser = current_user();
    if ($sessionUser) {
        // Enforce anti-CSRF validation for state-changing session requests
        if (in_array($method, ['POST', 'DELETE', 'PUT', 'PATCH'], true)) {
            $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
            $expectedToken = (string) ($_SESSION['csrf_token'] ?? '');
            if ($expectedToken === '' || !hash_equals($expectedToken, (string) $csrfToken)) {
                json_response([
                    'ok' => false,
                    'error' => 'CSRF validation failed for session-based request. Provide valid X-CSRF-Token or use API Key authentication.',
                    'meta_error' => [
                        'message' => 'Cross-Site Request Forgery validation failed.',
                        'type' => 'SecurityException',
                        'code' => 403,
                    ],
                ], 403);
            }
        }

        $user = [
            'user_id' => (int) $sessionUser['id'],
            'id' => 0,
            'name' => 'Session User',
            'status' => 'active',
            'rate_limit_per_minute' => 600,
        ];
    }
}

if (!$user) {
    json_response([
        'ok' => false,
        'error' => 'Invalid or missing API key. Provide Authorization: Bearer <key> or X-API-Key header.',
        'meta_error' => [
            'message' => 'Invalid OAuth access token or API key.',
            'type' => 'OAuthException',
            'code' => 190,
        ],
    ], 401);
}

// -------------------------------------------------------------------------
// 2. Rate Limiting Guardrail (for API Keys)
// -------------------------------------------------------------------------
if (!empty($user['id'])) {
    $rate = api_key_rate_status($user);
    if (!$rate['allowed']) {
        $requestId = log_api_request((int) $user['user_id'], (int) $user['id'], 'contacts', 429, 'Rate limit exceeded.');
        json_response([
            'ok' => false,
            'error' => 'Rate limit exceeded.',
            'request_id' => $requestId,
            'rate' => $rate,
            'meta_error' => [
                'message' => 'Rate limit hit.',
                'type' => 'OAuthException',
                'code' => 80007,
            ],
        ], 429);
    }
}

$pdo = Database::pdo();
$userId = (int) $user['user_id'];

// -------------------------------------------------------------------------
// GET: Retrieve Contacts Directory with Search & Filtering
// -------------------------------------------------------------------------
if ($method === 'GET') {
    $rawSearch = mb_substr(trim((string) ($_GET['search'] ?? $_GET['q'] ?? '')), 0, 100);
    $optInFilter = isset($_GET['opt_in']) && $_GET['opt_in'] !== '' ? (int) $_GET['opt_in'] : null;
    $limit = min(max((int) ($_GET['limit'] ?? 50), 1), 250);
    $offset = max((int) ($_GET['offset'] ?? 0), 0);

    $where = ['user_id = ?'];
    $params = [$userId];

    if ($optInFilter !== null) {
        $where[] = 'opt_in = ?';
        $params[] = $optInFilter;
    }

    if ($rawSearch !== '') {
        // Escape LIKE wildcards to prevent ReDoS / unintended regex-like pattern matching
        $escaped = addcslashes($rawSearch, '%_\\');
        $searchTerm = '%' . $escaped . '%';
        $where[] = '(name LIKE ? OR phone LIKE ? OR notes LIKE ?)';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    $whereSql = implode(' AND ', $where);

    // Get total count matching query
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM contacts WHERE {$whereSql}");
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    // Get opted-in total for this tenant
    $optedCountStmt = $pdo->prepare('SELECT COUNT(*) FROM contacts WHERE user_id = ? AND opt_in = 1');
    $optedCountStmt->execute([$userId]);
    $optedInTotal = (int) $optedCountStmt->fetchColumn();

    // Fetch paginated contacts
    $selectSql = "SELECT id, user_id, name, phone, normalized_phone, opt_in, notes, created_at, updated_at 
                  FROM contacts 
                  WHERE {$whereSql} 
                  ORDER BY id DESC 
                  LIMIT ? OFFSET ?";
    $fetchParams = array_merge($params, [$limit, $offset]);
    $stmt = $pdo->prepare($selectSql);
    
    foreach ($fetchParams as $idx => $val) {
        $type = is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR;
        $stmt->bindValue($idx + 1, $val, $type);
    }
    $stmt->execute();
    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Cast types for JSON consistency
    foreach ($contacts as &$c) {
        $c['id'] = (int) $c['id'];
        $c['user_id'] = (int) $c['user_id'];
        $c['opt_in'] = (int) $c['opt_in'];
    }
    unset($c);

    if (!empty($user['id'])) {
        log_api_request($userId, (int) $user['id'], 'contacts:list', 200, "Listed " . count($contacts) . " contacts.");
    }

    json_response([
        'ok' => true,
        'total' => $total,
        'opted_in_count' => $optedInTotal,
        'limit' => $limit,
        'offset' => $offset,
        'contacts' => $contacts,
    ], 200);
}

// -------------------------------------------------------------------------
// POST: Create or Upsert Contacts (Single or Batch with Bounds & Transaction)
// -------------------------------------------------------------------------
if ($method === 'POST') {
    $data = request_json();
    if (empty($data)) {
        $data = $_POST;
    }

    // Determine payload structure
    $batch = [];
    if (!empty($data['contacts']) && is_array($data['contacts'])) {
        $batch = $data['contacts'];
    } elseif (!empty($data['name']) || !empty($data['phone'])) {
        $batch = [$data];
    } else {
        json_response([
            'ok' => false,
            'error' => 'Missing contact payload. Provide name and phone (or an array of contacts).',
            'meta_error' => [
                'message' => 'Missing required parameter: phone.',
                'type' => 'OAuthException',
                'code' => 100,
            ],
        ], 422);
    }

    // Enforce upper bound on batch size to prevent DoS
    $maxBatch = 500;
    if (count($batch) > $maxBatch) {
        json_response([
            'ok' => false,
            'error' => "Batch size exceeds maximum limit of {$maxBatch} contacts.",
            'meta_error' => [
                'message' => 'Payload too large.',
                'type' => 'PayloadTooLargeException',
                'code' => 413,
            ],
        ], 413);
    }

    $upsertStmt = $pdo->prepare(
        'INSERT INTO contacts (user_id, name, phone, normalized_phone, opt_in, notes) 
         VALUES (?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE 
            name = VALUES(name), 
            normalized_phone = VALUES(normalized_phone), 
            opt_in = VALUES(opt_in), 
            notes = VALUES(notes)'
    );

    $processed = 0;
    $errors = [];
    $savedContacts = [];

    // Wrap multiple inserts in transaction for atomic integrity
    if (count($batch) > 1) {
        $pdo->beginTransaction();
    }

    try {
        foreach ($batch as $index => $item) {
            $rawPhone = substr(preg_replace('/[^\d+]/', '', trim((string) ($item['phone'] ?? ''))), 0, 32);
            $rawName = mb_substr(trim((string) ($item['name'] ?? 'Contact')), 0, 100);
            if ($rawName === '') {
                $rawName = 'Contact';
            }
            $notes = isset($item['notes']) ? mb_substr(trim((string) $item['notes']), 0, 500) : null;
            $optIn = isset($item['opt_in']) ? (filter_var($item['opt_in'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $item['opt_in'] ? 1 : 0) : 1;

            if ($rawPhone === '') {
                $errors[] = "Item {$index}: phone number is required.";
                continue;
            }

            $normalized = normalize_phone($rawPhone);
            if ($normalized === '' || strlen(preg_replace('/\D/', '', $normalized)) < 7) {
                $errors[] = "Item {$index}: invalid phone number format: {$rawPhone}";
                continue;
            }

            $upsertStmt->execute([
                $userId,
                $rawName,
                $rawPhone,
                $normalized,
                $optIn,
                $notes,
            ]);

            // Retrieve updated/created record
            $fetchStmt = $pdo->prepare('SELECT id, user_id, name, phone, normalized_phone, opt_in, notes, created_at, updated_at FROM contacts WHERE user_id = ? AND phone = ? LIMIT 1');
            $fetchStmt->execute([$userId, $rawPhone]);
            $saved = $fetchStmt->fetch(PDO::FETCH_ASSOC);
            if ($saved) {
                $saved['id'] = (int) $saved['id'];
                $saved['user_id'] = (int) $saved['user_id'];
                $saved['opt_in'] = (int) $saved['opt_in'];
                $savedContacts[] = $saved;
            }
            $processed++;
        }

        if (count($batch) > 1 && $pdo->inTransaction()) {
            $pdo->commit();
        }
    } catch (Throwable $e) {
        if (count($batch) > 1 && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $errors[] = "Database transaction error: " . $e->getMessage();
    }

    if ($processed === 0 && !empty($errors)) {
        json_response([
            'ok' => false,
            'error' => 'Failed to save contacts.',
            'errors' => $errors,
        ], 422);
    }

    if (!empty($user['id'])) {
        log_api_request($userId, (int) $user['id'], 'contacts:upsert', 200, "Upserted {$processed} contacts.");
    }

    // Return single contact if only 1 was submitted
    if (count($batch) === 1 && !empty($savedContacts)) {
        json_response([
            'ok' => true,
            'message' => 'Contact saved successfully.',
            'contact' => $savedContacts[0],
            'errors' => $errors ?: null,
        ], 200);
    }

    json_response([
        'ok' => true,
        'message' => "Successfully processed {$processed} contact(s).",
        'processed' => $processed,
        'contacts' => $savedContacts,
        'errors' => $errors ?: null,
    ], 200);
}

// -------------------------------------------------------------------------
// DELETE: Remove Contact by ID or Phone with Strict Tenant Isolation
// -------------------------------------------------------------------------
if ($method === 'DELETE') {
    $data = request_json();
    $id = (int) ($data['id'] ?? $_GET['id'] ?? $_POST['id'] ?? 0);
    $phone = substr(preg_replace('/[^\d+]/', '', trim((string) ($data['phone'] ?? $_GET['phone'] ?? $_POST['phone'] ?? ''))), 0, 32);

    if ($id <= 0 && $phone === '') {
        json_response([
            'ok' => false,
            'error' => 'Specify either contact id or phone to delete.',
        ], 422);
    }

    if ($id > 0) {
        $delStmt = $pdo->prepare('DELETE FROM contacts WHERE id = ? AND user_id = ?');
        $delStmt->execute([$id, $userId]);
    } else {
        $delStmt = $pdo->prepare('DELETE FROM contacts WHERE (phone = ? OR normalized_phone = ?) AND user_id = ?');
        $norm = normalize_phone($phone);
        $delStmt->execute([$phone, $norm, $userId]);
    }

    $deleted = $delStmt->rowCount();
    if ($deleted === 0) {
        json_response([
            'ok' => false,
            'error' => 'Contact not found or already deleted.',
        ], 404);
    }

    if (!empty($user['id'])) {
        log_api_request($userId, (int) $user['id'], 'contacts:delete', 200, "Deleted contact {$id}/{$phone}.");
    }

    json_response([
        'ok' => true,
        'message' => 'Contact deleted successfully.',
        'deleted_count' => $deleted,
    ], 200);
}

json_response([
    'ok' => false,
    'error' => "Method {$method} not allowed. Supported methods: GET, POST, DELETE.",
], 405);
