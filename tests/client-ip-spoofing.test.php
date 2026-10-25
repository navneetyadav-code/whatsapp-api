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

echo "Running client IP spoofing protection tests (SEC-06)...\n";

$originalServer = $_SERVER;

// Helper to reset and configure $_SERVER
function simulate_request(string $remoteAddr, array $headers = []): void
{
    $_SERVER = [
        'REMOTE_ADDR' => $remoteAddr,
    ];
    foreach ($headers as $key => $val) {
        $_SERVER[$key] = $val;
    }
}

// 1. Untrusted direct client attempting to spoof loopback via X-Forwarded-For
simulate_request('203.0.113.195', [
    'HTTP_X_FORWARDED_FOR' => '127.0.0.1',
]);
test_assert(
    client_ip() === '203.0.113.195',
    'Direct connection from untrusted IP must NOT trust HTTP_X_FORWARDED_FOR'
);
test_assert(
    !is_local_request(),
    'Direct connection from untrusted IP with spoofed X-Forwarded-For must NOT be considered local'
);

// 2. Untrusted direct client attempting to spoof allowlisted IP via X-Real-IP
simulate_request('198.51.100.4', [
    'HTTP_X_REAL_IP' => '185.27.134.37',
]);
test_assert(
    client_ip() === '198.51.100.4',
    'Direct connection from untrusted IP must NOT trust HTTP_X_REAL_IP'
);

$apiKey = ['ip_allowlist' => '185.27.134.37'];
test_assert(
    !api_key_ip_allowed($apiKey, client_ip()),
    'Spoofed IP allowlist bypass attempt must fail'
);

// 3. Untrusted direct client attempting to spoof Cloudflare connecting IP
simulate_request('203.0.113.55', [
    'HTTP_CF_CONNECTING_IP' => '127.0.0.1',
]);
test_assert(
    client_ip() === '203.0.113.55',
    'Direct connection from untrusted IP must NOT trust HTTP_CF_CONNECTING_IP'
);

// 4. Trusted reverse proxy (e.g. local container proxy or Cloudflare proxy 127.0.0.1 / 10.x.x.x)
simulate_request('127.0.0.1', [
    'HTTP_X_FORWARDED_FOR' => '198.51.100.99, 10.0.0.1',
]);
test_assert(
    client_ip() === '198.51.100.99',
    'Trusted reverse proxy (127.0.0.1) forwarding must extract the genuine client IP'
);

simulate_request('10.0.0.2', [
    'HTTP_CF_CONNECTING_IP' => '203.0.113.77',
]);
test_assert(
    client_ip() === '203.0.113.77',
    'Trusted private proxy (10.0.0.2) must extract HTTP_CF_CONNECTING_IP'
);

// 5. Genuine local request
simulate_request('127.0.0.1');
test_assert(
    client_ip() === '127.0.0.1',
    'Direct loopback connection must return 127.0.0.1'
);
test_assert(
    is_local_request(),
    'Direct loopback connection must be identified as local'
);

$_SERVER = $originalServer;

echo "All client IP spoofing protection unit tests (SEC-06) passed successfully!\n";
