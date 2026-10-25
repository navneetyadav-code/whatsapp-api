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

echo "Running upload security tests (SEC-01)...\n";

// 1. Verify public/uploads/.htaccess exists and contains security hardening
$htaccessPath = dirname(__DIR__) . '/public/uploads/.htaccess';
test_assert(file_exists($htaccessPath), 'public/uploads/.htaccess must exist');
$htaccessContent = (string) file_get_contents($htaccessPath);
test_assert(
    str_contains($htaccessContent, 'Require all denied') && str_contains($htaccessContent, 'php|phtml'),
    '.htaccess must deny script execution for php/phtml files'
);
test_assert(
    str_contains($htaccessContent, 'php_flag engine off'),
    '.htaccess must disable the PHP engine'
);

// Helper to create a temporary test file
function create_test_file(string $content, string $name): array
{
    $tmpDir = sys_get_temp_dir();
    $tmpPath = $tmpDir . DIRECTORY_SEPARATOR . 'test_' . uniqid() . '_' . $name;
    file_put_contents($tmpPath, $content);

    return [
        'name' => $name,
        'type' => 'application/octet-stream',
        'tmp_name' => $tmpPath,
        'error' => UPLOAD_ERR_OK,
        'size' => strlen($content),
    ];
}

// 2. Test: Reject direct PHP script upload (.php)
$phpFile = create_test_file("<?php phpinfo(); ?>", "shell.php");
$phpRejected = false;
try {
    save_uploaded_media($phpFile);
} catch (RuntimeException $e) {
    $phpRejected = true;
    test_assert(str_contains($e->getMessage(), 'Unsupported file extension'), 'Rejection message explains disallowed extension');
} finally {
    if (file_exists($phpFile['tmp_name'])) unlink($phpFile['tmp_name']);
}
test_assert($phpRejected, 'Direct .php file upload must be rejected');

// 3. Test: Reject alternative executable extensions (.phtml, .phar, .sh, .exe)
foreach (['backdoor.phtml', 'archive.phar', 'script.sh', 'malware.exe'] as $badExtFile) {
    $f = create_test_file("echo test", $badExtFile);
    $rejected = false;
    try {
        save_uploaded_media($f);
    } catch (RuntimeException $e) {
        $rejected = true;
    } finally {
        if (file_exists($f['tmp_name'])) unlink($f['tmp_name']);
    }
    test_assert($rejected, "Dangerous extension {$badExtFile} must be rejected");
}

// 4. Test: Reject PHP code disguised as an image (.png extension with text/x-php content)
$disguisedFile = create_test_file("<?php echo 'hacked'; ?>", "avatar.png");
$disguisedRejected = false;
try {
    save_uploaded_media($disguisedFile);
} catch (RuntimeException $e) {
    $disguisedRejected = true;
    test_assert(str_contains($e->getMessage(), 'Executable file uploads are strictly forbidden'), 'Executable MIME rejection triggered');
} finally {
    if (file_exists($disguisedFile['tmp_name'])) unlink($disguisedFile['tmp_name']);
}
test_assert($disguisedRejected, 'PHP script disguised with image extension must be rejected via MIME inspection');

// 5. Test: Reject oversized file
$oversizedFile = create_test_file("dummy data", "document.pdf");
$oversizedRejected = false;
try {
    save_uploaded_media($oversizedFile, null, 5); // 5 bytes max limit
} catch (RuntimeException $e) {
    $oversizedRejected = true;
    test_assert(str_contains($e->getMessage(), 'exceeds maximum allowed size'), 'Size bounds enforced');
} finally {
    if (file_exists($oversizedFile['tmp_name'])) unlink($oversizedFile['tmp_name']);
}
test_assert($oversizedRejected, 'Oversized file upload must be rejected');

// 6. Test: Reject upload error states
$errFile = [
    'name' => 'photo.jpg',
    'type' => 'image/jpeg',
    'tmp_name' => '',
    'error' => UPLOAD_ERR_PARTIAL,
    'size' => 0,
];
$errRejected = false;
try {
    save_uploaded_media($errFile);
} catch (RuntimeException $e) {
    $errRejected = true;
    test_assert(str_contains($e->getMessage(), 'incomplete'), 'Upload error status mapped to clear message');
}
test_assert($errRejected, 'Upload error code must be rejected');

// 7. Test: Valid media upload succeeds with random name and correct metadata
// 1x1 transparent PNG bytes
$pngBytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');
$validPng = create_test_file($pngBytes, "my_photo.png");
$savedResult = null;
try {
    $savedResult = save_uploaded_media($validPng);
} finally {
    if (file_exists($validPng['tmp_name'])) unlink($validPng['tmp_name']);
}

test_assert($savedResult !== null, 'Valid PNG upload must succeed');
test_assert(file_exists($savedResult['path']), 'Saved file must exist on disk in uploads directory');
test_assert(str_starts_with(basename($savedResult['path']), 'media_'), 'Saved file must have randomized media_ prefix');
test_assert(str_ends_with($savedResult['path'], '.png'), 'Saved file must have sanitized extension');
test_assert($savedResult['type'] === 'image', 'Media type must be mapped to image');
test_assert($savedResult['name'] === 'my_photo.png', 'Original filename preserved in metadata');

// Clean up saved test file
if (file_exists($savedResult['path'])) {
    unlink($savedResult['path']);
}

echo "All upload security unit tests passed successfully!\n";
