<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
if ($method !== 'POST') {
    json_response([
        'ok' => false,
        'error' => "Method {$method} not allowed. Batch contact ingestion requires POST.",
    ], 405);
}

// -------------------------------------------------------------------------
// 1. Authenticate Request (API Key / Bearer Token or Protected Web Session)
// -------------------------------------------------------------------------
$user = authenticate_api_request();

if (!$user) {
    $sessionUser = current_user();
    if ($sessionUser) {
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
        $requestId = log_api_request((int) $user['user_id'], (int) $user['id'], 'contacts:batch', 429, 'Rate limit exceeded.');
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
$data = request_json();
if (empty($data)) {
    $data = $_POST;
}

$batch = $data['contacts'] ?? [];
if (!is_array($batch) || empty($batch)) {
    json_response([
        'ok' => false,
        'error' => 'Missing batch array. Provide a JSON object with a "contacts" array property.',
        'meta_error' => [
            'message' => 'Missing required parameter: contacts.',
            'type' => 'OAuthException',
            'code' => 100,
        ],
    ], 422);
}

try {
    $result = ContactService::batchUpsert($userId, $batch);
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
    json_response([
        'ok' => false,
        'error' => 'Failed to process batch: ' . $e->getMessage(),
    ], 500);
}

if (!empty($user['id'])) {
    log_api_request($userId, (int) $user['id'], 'contacts:batch', 200, "Batch processed {$result['processed']} contacts.");
}

json_response([
    'ok' => true,
    'message' => "Successfully processed {$result['processed']} contact(s).",
    'processed' => $result['processed'],
    'contacts' => $result['contacts'],
    'errors' => $result['errors'] ?: null,
], 200);
