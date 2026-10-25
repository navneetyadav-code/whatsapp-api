<?php

declare(strict_types=1);

function test_assert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

echo "Running setup script protection tests (SEC-05)...\n";

// 1. Verify root .htaccess blocks HTTP access to setup.php
$htaccessPath = dirname(__DIR__) . '/.htaccess';
test_assert(file_exists($htaccessPath), 'Root .htaccess must exist');
$htaccess = (string) file_get_contents($htaccessPath);

test_assert(
    (str_contains($htaccess, 'setup.php') || str_contains($htaccess, 'setup\.php')) && str_contains($htaccess, 'Require all denied'),
    'Root .htaccess must block direct HTTP access to setup.php'
);

// 2. Verify setup.php source has PHP_SAPI !== cli guard (or has cleanly self-deleted post-installation)
$setupPath = dirname(__DIR__) . '/setup.php';
if (!file_exists($setupPath)) {
    echo "  ✔ setup.php has been self-deleted post-setup (clean & secure state).\n";
} else {
    $setupSource = (string) file_get_contents($setupPath);

    test_assert(
        str_contains($setupSource, "PHP_SAPI !== 'cli'") && str_contains($setupSource, 'http_response_code(403)'),
        'setup.php must enforce CLI-only execution with HTTP 403 response'
    );

    // 3. Verify setup.php aborts immediately if executed outside CLI
    test_assert(
        preg_match('/if\s*\(\s*PHP_SAPI\s*!==\s*\'cli\'\s*\)\s*\{\s*http_response_code\(403\);/m', $setupSource) === 1,
        'setup.php must immediately abort with 403 if not running under CLI'
    );
}

echo "All setup protection unit tests (SEC-05) passed successfully!\n";
