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
// PII Security Enforcement: Strictly reject phone numbers in URL query strings
// -------------------------------------------------------------------------
if (isset($_GET['phone']) || isset($_GET['recipient'])) {
    json_response([
        'ok' => false,
        'error' => 'Security & Privacy Violation: Raw phone numbers cannot be transmitted in URL query parameters to prevent access log leakage. Use synthetic contact "id" in URL, or provide "phone" in the encrypted JSON request body.',
        'meta_error' => [
            'message' => 'Personally Identifiable Information (PII) rejected in query string.',
            'type' => 'PrivacyException',
            'code' => 400,
        ],
    ], 400);
}

// -------------------------------------------------------------------------
// 1. Authenticate Request (API Key / Bearer Token or Protected Web Session)
// -------------------------------------------------------------------------
$user = authenticate_api_request();

if (!$user) {
    $sessionUser = current_user();
    if ($sessionUser) {
        if (in_array($method, ['POST', 'DELETE', 'PUT', 'PATCH'], true)) {
            $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
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
// 2. Rate Limiting Guardrail
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

$userId = (int) $user['user_id'];

// -------------------------------------------------------------------------
// GET: Retrieve Directory or Single Contact by ID
// -------------------------------------------------------------------------
if ($method === 'GET') {
    // Single contact retrieval by ID
    if (isset($_GET['id']) && (int) $_GET['id'] > 0) {
        $contact = ContactService::getById($userId, (int) $_GET['id']);
        if (!$contact) {
            json_response([
                'ok' => false,
                'error' => 'Contact not found.',
            ], 404);
        }
        json_response([
            'ok' => true,
            'contact' => $contact,
        ], 200);
    }

    // Paginated directory listing with search & opt-in filtering
    $search = (string) ($_GET['search'] ?? $_GET['q'] ?? '');
    $optIn = isset($_GET['opt_in']) && $_GET['opt_in'] !== '' ? (int) $_GET['opt_in'] : null;
    $limit = (int) ($_GET['limit'] ?? 50);
    $offset = (int) ($_GET['offset'] ?? 0);

    $result = ContactService::list($userId, $search, $optIn, $limit, $offset);

    if (!empty($user['id'])) {
        log_api_request($userId, (int) $user['id'], 'contacts:list', 200, "Listed " . count($result['contacts']) . " contacts.");
    }

    json_response(array_merge(['ok' => true], $result), 200);
}

// -------------------------------------------------------------------------
// POST: Create / Upsert Contact (Single or Batch)
// -------------------------------------------------------------------------
if ($method === 'POST') {
    $data = request_json();
    if (empty($data)) {
        $data = $_POST;
    }

    // Handle batch array
    if (!empty($data['contacts']) && is_array($data['contacts'])) {
        try {
            $batchResult = ContactService::batchUpsert($userId, $data['contacts']);
            if (!empty($user['id'])) {
                log_api_request($userId, (int) $user['id'], 'contacts:batch', 200, "Upserted {$batchResult['processed']} contacts.");
            }
            json_response([
                'ok' => true,
                'message' => "Successfully processed {$batchResult['processed']} contact(s).",
                'processed' => $batchResult['processed'],
                'contacts' => $batchResult['contacts'],
                'errors' => $batchResult['errors'] ?: null,
            ], 200);
        } catch (OverflowException $e) {
            json_response([
                'ok' => false,
                'error' => $e->getMessage(),
                'meta_error' => [
                    'message' => 'Payload too large.',
                    'type' => 'PayloadTooLargeException',
                    'code' => 413,
                ],
            ], 413);
        } catch (Throwable $e) {
            json_response(['ok' => false, 'error' => $e->getMessage()], 422);
        }
    }

    // Handle single contact
    if (empty($data['phone'])) {
        json_response([
            'ok' => false,
            'error' => 'Missing contact payload. Provide phone (or an array of contacts).',
            'meta_error' => [
                'message' => 'Missing required parameter: phone.',
                'type' => 'OAuthException',
                'code' => 100,
            ],
        ], 422);
    }

    try {
        $contact = ContactService::upsert($userId, $data);
        if (!empty($user['id'])) {
            log_api_request($userId, (int) $user['id'], 'contacts:upsert', 200, "Upserted contact.");
        }
        json_response([
            'ok' => true,
            'message' => 'Contact saved successfully.',
            'contact' => $contact,
        ], 200);
    } catch (InvalidArgumentException $e) {
        json_response(['ok' => false, 'error' => $e->getMessage()], 422);
    } catch (Throwable $e) {
        json_response(['ok' => false, 'error' => 'Failed to save contact: ' . $e->getMessage()], 500);
    }
}

// -------------------------------------------------------------------------
// DELETE: Remove Contact by Synthetic ID (or JSON Body)
// -------------------------------------------------------------------------
if ($method === 'DELETE') {
    $data = request_json();
    $id = (int) ($data['id'] ?? $_GET['id'] ?? 0);
    $phone = trim((string) ($data['phone'] ?? ''));

    if ($id <= 0 && $phone === '') {
        json_response([
            'ok' => false,
            'error' => 'Specify contact "id" to delete (or provide "phone" in the request body).',
        ], 422);
    }

    $deleted = false;
    if ($id > 0) {
        $deleted = ContactService::deleteById($userId, $id);
    } else {
        $deleted = ContactService::deleteByPhone($userId, $phone);
    }

    if (!$deleted) {
        json_response([
            'ok' => false,
            'error' => 'Contact not found or already deleted.',
        ], 404);
    }

    if (!empty($user['id'])) {
        log_api_request($userId, (int) $user['id'], 'contacts:delete', 200, "Deleted contact {$id}.");
    }

    json_response([
        'ok' => true,
        'message' => 'Contact deleted successfully.',
        'deleted_count' => 1,
    ], 200);
}

json_response([
    'ok' => false,
    'error' => "Method {$method} not allowed. Supported methods: GET, POST, DELETE.",
], 405);
