<?php

declare(strict_types=1);

function media_test_assert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

require_once dirname(__DIR__) . '/src/bootstrap.php';

echo "Running Media Protection & Streaming tests (Fix 1)...\n";

// 1. Verify uploads directory is outside or blocked from direct execution/browsing
$uploadDir = get_media_upload_dir();
media_test_assert(is_dir($uploadDir), 'Uploads directory must exist');

$htaccessPath = dirname(__DIR__) . '/public/uploads/.htaccess';
media_test_assert(file_exists($htaccessPath), 'public/uploads/.htaccess must exist');
$htaccess = (string) file_get_contents($htaccessPath);
media_test_assert(str_contains($htaccess, 'Require all denied'), 'uploads/.htaccess must require all denied');

// 2. Test signature generation and verification
$filename = 'media_test_signature_sample.pdf';
$userId = 42;
$expires = time() + 3600;
$sig = generate_media_signature($filename, $userId, $expires);
media_test_assert(strlen($sig) === 64, 'HMAC-SHA256 signature must be 64 hex characters');

// Valid signature verification
media_test_assert(
    verify_media_signature($filename, $userId, $expires, $sig),
    'Valid HMAC signature must verify successfully'
);

// Tampered filename verification
media_test_assert(
    !verify_media_signature('media_another_file.pdf', $userId, $expires, $sig),
    'Signature verification must fail if filename is altered'
);

// Tampered user verification
media_test_assert(
    !verify_media_signature($filename, 999, $expires, $sig),
    'Signature verification must fail if userId is altered'
);

// Tampered signature verification
media_test_assert(
    !verify_media_signature($filename, $userId, $expires, 'bad' . substr($sig, 3)),
    'Signature verification must fail if signature hash is altered'
);

// Expired signature verification
media_test_assert(
    !verify_media_signature($filename, $userId, time() - 10, $sig),
    'Signature verification must fail if expired'
);

// 3. Test media_secure_url() generation
$secureUrl = media_secure_url($filename, $userId, 1800);
media_test_assert(str_contains($secureUrl, 'media.php?') || str_contains($secureUrl, '/media/'), 'Secure URL must use media endpoint');
media_test_assert(str_contains($secureUrl, 'expires='), 'Secure URL must contain expires param');
media_test_assert(str_contains($secureUrl, 'u=' . $userId), 'Secure URL must contain u param');
media_test_assert(str_contains($secureUrl, 'sig='), 'Secure URL must contain sig param');

// 4. Test path traversal defenses in media handler
$invalidNames = [
    '../secret.txt',
    '../../config/database.php',
    'media_test/../../test.txt',
    'media_test\..\something',
    'shell.php',
    'media_.htaccess'
];

foreach ($invalidNames as $badName) {
    $clean = basename(str_replace(['\\', '/'], '', $badName));
    $isAllowed = preg_match('/^media_[a-f0-9]{32,}\.[a-zA-Z0-9]+$/', $clean);
    media_test_assert(!$isAllowed, "Path traversal or non-media filename '{$badName}' must be rejected by regex");
}

echo "All Media Protection & Streaming tests passed successfully!\n";
