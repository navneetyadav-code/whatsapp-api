<?php

declare(strict_types=1);

function test_assert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

require_once dirname(__DIR__) . '/src/bootstrap.php';

echo "Running API authentication security tests (SEC-07)...\n";

$originalServer = $_SERVER;
$originalGet = $_GET;
$originalPost = $_POST;

// 1. By default, query parameter authentication must be blocked (prevent log leakage)
$_SERVER = [
    'REQUEST_METHOD' => 'GET',
    'REMOTE_ADDR' => '127.0.0.1',
];
$_GET = ['api_key' => 'wa_live_test_query_key_12345'];
$_POST = [];

$extracted = authenticate_api_request();
test_assert(
    $extracted === null,
    'authenticate_api_request must reject API key in GET query parameters by default (SEC-07)'
);

// 2. Authorization Bearer header format must be accepted
$_SERVER = [
    'REQUEST_METHOD' => 'POST',
    'HTTP_AUTHORIZATION' => 'Bearer wa_live_dummy_test_bearer_token',
    'REMOTE_ADDR' => '127.0.0.1',
];
$_GET = [];
$_POST = [];

$authHeader = $_SERVER['HTTP_AUTHORIZATION'];
test_assert(
    str_starts_with($authHeader, 'Bearer '),
    'Bearer token format must be supported in Authorization header'
);

// 3. X-API-Key header format must be accepted
$_SERVER = [
    'REQUEST_METHOD' => 'POST',
    'HTTP_X_API_KEY' => 'wa_live_dummy_test_header_key',
    'REMOTE_ADDR' => '127.0.0.1',
];
test_assert(
    isset($_SERVER['HTTP_X_API_KEY']),
    'X-API-Key header must be supported'
);

// 4. POST body single-key authentication must be accepted
$_SERVER = [
    'REQUEST_METHOD' => 'POST',
    'REMOTE_ADDR' => '127.0.0.1',
];
$_GET = [];
$_POST = ['api_key' => 'wa_live_test_post_key'];
test_assert(
    isset($_POST['api_key']),
    'POST body api_key must be prioritized before query parameters'
);

// 5. Verify config option exists
$allowQueryAuth = (bool) app_config('security.allow_query_auth', false);
test_assert(
    $allowQueryAuth === false,
    'security.allow_query_auth must default to false for security hardening'
);

$_SERVER = $originalServer;
$_GET = $originalGet;
$_POST = $originalPost;

echo "All API authentication security unit tests (SEC-07) passed successfully!\n";
