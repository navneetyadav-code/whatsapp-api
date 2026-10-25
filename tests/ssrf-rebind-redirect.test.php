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

echo "Running SSRF rebinding & redirect unit tests (SEC-04)...\n";

// 1. Validate comprehensive is_private_ip detection across RFC ranges
$blockedIps = [
    '127.0.0.1',
    '127.0.0.2',
    '127.255.255.254',
    '::1',
    '0.0.0.0',
    '0.0.0.1',
    '10.0.0.1',
    '10.254.0.5',
    '172.16.0.1',
    '172.31.255.255',
    '192.168.0.1',
    '192.168.100.50',
    '169.254.169.254',
    '169.254.1.1',
    '100.64.0.1',
    '100.100.100.200',
    '100.127.255.254',
    '198.18.0.1',
    '198.19.255.254',
    '224.0.0.1',
    '240.0.0.1',
    '::ffff:127.0.0.1',
    '::ffff:10.0.0.1',
    '::ffff:169.254.169.254',
    '[::1]',
    '[::ffff:127.0.0.1]',
    'fe80::1',
    'fc00::1',
    'fd12:3456:789a:1::1',
];

foreach ($blockedIps as $ip) {
    test_assert(is_private_ip($ip), "IP {$ip} must be identified as private/restricted");
}

// 2. Validate legitimate public IPs are allowed
$allowedIps = [
    '8.8.8.8',
    '1.1.1.1',
    '142.250.190.46',
    '2607:f8b0:4005:805::200e',
];

foreach ($allowedIps as $ip) {
    test_assert(!is_private_ip($ip), "Public IP {$ip} must NOT be flagged as private");
}

// 3. Validate assert_safe_media_url blocks dangerous hosts
$dangerousUrls = [
    'http://127.0.0.1/test.png',
    'http://localhost/test.png',
    'http://[::1]/test.png',
    'http://169.254.169.254/latest/meta-data/',
    'http://metadata.google.internal/computeMetadata/v1/',
    'http://100.100.100.200/latest/meta-data/',
    'http://10.0.0.1/image.jpg',
    'http://192.168.1.1/image.jpg',
    'http://[::ffff:127.0.0.1]/image.jpg',
];

foreach ($dangerousUrls as $url) {
    $blocked = false;
    try {
        assert_safe_media_url($url);
    } catch (InvalidArgumentException $e) {
        $blocked = true;
    }
    test_assert($blocked, "Dangerous URL {$url} must be blocked by assert_safe_media_url()");
}

// 4. Validate download_remote_media implementation enforces secure redirects and IP checks
$supportSource = (string) file_get_contents(dirname(__DIR__) . '/src/lib/Support.php');
test_assert(
    str_contains($supportSource, 'CURLINFO_PRIMARY_IP') && str_contains($supportSource, 'is_private_ip($primaryIp)'),
    'download_remote_media must inspect CURLINFO_PRIMARY_IP to prevent DNS rebinding'
);
test_assert(
    str_contains($supportSource, 'assert_safe_media_url($redirectUrl)'),
    'download_remote_media must validate redirect URLs against assert_safe_media_url()'
);
test_assert(
    str_contains($supportSource, 'CURLOPT_FOLLOWLOCATION => false'),
    'download_remote_media must disable automatic cURL followlocation to prevent uninspected redirects'
);

echo "All SSRF rebinding & redirect unit tests (SEC-04) passed successfully!\n";
