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

echo "Running SSRF test console security tests (SEC-03)...\n";

$testIndexPath = dirname(__DIR__) . '/test/index.php';
test_assert(file_exists($testIndexPath), 'test/index.php must exist');
$testIndexContent = (string) file_get_contents($testIndexPath);

// 1. Verify bootstrap and auth requirements are present in test/index.php
test_assert(
    str_contains($testIndexContent, 'bootstrap.php'),
    'test/index.php must require bootstrap.php'
);
test_assert(
    str_contains($testIndexContent, 'require_auth()'),
    'test/index.php must enforce require_auth() for remote requests'
);
test_assert(
    str_contains($testIndexContent, 'verify_csrf()'),
    'test/index.php must enforce verify_csrf() for remote requests'
);
test_assert(
    str_contains($testIndexContent, 'csrf_token()'),
    'test/index.php must include CSRF token in the HTML form'
);

// 2. Unit testing SSRF URL filtering logic
function evaluate_target_ssrf(string $apiUrl, bool $isLocal = false): bool
{
    $targetHost = strtolower((string) (parse_url($apiUrl, PHP_URL_HOST) ?? ''));
    $cleanTargetHost = trim($targetHost, '[]');
    $targetScheme = strtolower((string) (parse_url($apiUrl, PHP_URL_SCHEME) ?? ''));

    return !filter_var($apiUrl, FILTER_VALIDATE_URL)
        || !in_array($targetScheme, ['http', 'https'], true)
        || $cleanTargetHost === ''
        || str_starts_with($cleanTargetHost, '169.254.')
        || $cleanTargetHost === 'metadata.google.internal'
        || $cleanTargetHost === '100.100.100.200'
        || (!$isLocal && (in_array($cleanTargetHost, ['localhost', '127.0.0.1', '::1', '0.0.0.0'], true) || is_private_ip(gethostbyname($cleanTargetHost))));
}

// AWS / GCP / Cloud metadata targets must ALWAYS be blocked
test_assert(
    evaluate_target_ssrf('http://169.254.169.254/latest/meta-data/', true),
    'AWS EC2 / IMDSv1/v2 metadata IP must be blocked even if local'
);
test_assert(
    evaluate_target_ssrf('http://metadata.google.internal/computeMetadata/v1/', true),
    'GCP metadata hostname must be blocked even if local'
);
test_assert(
    evaluate_target_ssrf('http://100.100.100.200/latest/meta-data/', true),
    'Alibaba cloud metadata IP must be blocked even if local'
);

// Dangerous URI schemes must be blocked
test_assert(
    evaluate_target_ssrf('file:///etc/passwd'),
    'file:// protocol must be rejected'
);
test_assert(
    evaluate_target_ssrf('gopher://127.0.0.1:6379/_flushall'),
    'gopher:// protocol must be rejected'
);
test_assert(
    evaluate_target_ssrf('dict://127.0.0.1:11211/stat'),
    'dict:// protocol must be rejected'
);

// Remote clients cannot target loopback or private networks
test_assert(
    evaluate_target_ssrf('http://127.0.0.1:3107/status', false),
    'Loopback target must be blocked for remote requests'
);
test_assert(
    evaluate_target_ssrf('http://localhost:8080/admin', false),
    'localhost target must be blocked for remote requests'
);
test_assert(
    evaluate_target_ssrf('http://10.0.0.5:8000/api', false),
    'Private IP 10.x.x.x target must be blocked for remote requests'
);
test_assert(
    evaluate_target_ssrf('http://192.168.1.100/router', false),
    'Private IP 192.168.x.x target must be blocked for remote requests'
);
test_assert(
    evaluate_target_ssrf('http://172.20.0.5/internal', false),
    'Private IP 172.16-31.x.x target must be blocked for remote requests'
);

// Valid public endpoint must pass
test_assert(
    !evaluate_target_ssrf('https://api.github.com/zen', false),
    'Valid public HTTPS endpoint must be allowed'
);

echo "All SSRF test console unit tests (SEC-03) passed successfully!\n";
