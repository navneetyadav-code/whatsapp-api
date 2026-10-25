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

echo "Running SSRF and path traversal security unit tests...\n";

// 1. is_private_ip() validation
$privateIps = [
    '127.0.0.1', '127.0.0.2', '127.255.255.255', '0.0.0.0', '169.254.169.254',
    '10.0.0.1', '192.168.1.1', '172.16.0.1', '172.31.255.255',
    '::1', '::', '::ffff:127.0.0.1', '::ffff:169.254.169.254', '::ffff:10.0.0.1',
    '100.64.0.1', '100.127.255.255', '198.18.0.1', '[::1]', '[127.0.0.1]'
];

foreach ($privateIps as $ip) {
    test_assert(is_private_ip($ip), "IP {$ip} must be identified as private/reserved");
}

$publicIps = ['8.8.8.8', '1.1.1.1', '93.184.216.34'];
foreach ($publicIps as $ip) {
    test_assert(!is_private_ip($ip), "IP {$ip} must be identified as public");
}

// 2. is_local_request() header-spoofing defense
$_SERVER['REMOTE_ADDR'] = '203.0.113.195';
$_SERVER['HTTP_X_FORWARDED_FOR'] = '127.0.0.1';
test_assert(!is_local_request(), 'External public client cannot spoof local request via X-Forwarded-For');

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
unset($_SERVER['HTTP_X_FORWARDED_FOR']);
test_assert(is_local_request(), 'Direct loopback connection is recognized as local');

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.195';
test_assert(!is_local_request(), 'Loopback reverse proxy carrying external client IP is not recognized as local');

// 3. client_ip() header-spoofing defense
$_SERVER['REMOTE_ADDR'] = '203.0.113.195';
$_SERVER['HTTP_X_FORWARDED_FOR'] = '10.0.0.1';
test_assert(client_ip() === '203.0.113.195', 'Direct public connection ignores spoofed X-Forwarded-For');

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.20';
test_assert(client_ip() === '198.51.100.20', 'Trusted reverse proxy forwards real client IP');

// Reset $_SERVER
unset($_SERVER['HTTP_X_FORWARDED_FOR']);
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

echo "All SSRF and path traversal security unit tests passed successfully!\n";
