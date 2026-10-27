<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
if ($method !== 'POST' && $method !== 'DELETE') {
    json_response([
        'ok' => false,
        'error' => "Method {$method} not allowed. Secure contact deletion requires POST or DELETE with JSON body.",
    ], 405);
}

// -------------------------------------------------------------------------
// PII Security Enforcement: Strictly reject phone numbers in query strings
// -------------------------------------------------------------------------
if (isset($_GET['phone']) || isset($_GET['recipient'])) {
    json_response([
        'ok' => false,
        'error' => 'Security & Privacy Violation: Raw phone numbers cannot be transmitted in URL query parameters to prevent access log leakage. Transmit the phone number securely inside the encrypted JSON request body.',
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
        $requestId = log_api_request((int) $user['user_id'], (int) $user['id'], 'contacts:delete', 429, 'Rate limit exceeded.');
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

$id = (int) ($data['id'] ?? 0);
$phone = trim((string) ($data['phone'] ?? ''));

if ($id <= 0 && $phone === '') {
    json_response([
        'ok' => false,
        'error' => 'Provide either contact "id" or "phone" in the request body to delete.',
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
    log_api_request($userId, (int) $user['id'], 'contacts:delete', 200, "Deleted contact {$id}/[redacted].");
}

json_response([
    'ok' => true,
    'message' => 'Contact deleted successfully.',
    'deleted_count' => 1,
], 200);
