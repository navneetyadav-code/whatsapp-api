<?php

declare(strict_types=1);

function hygiene_assert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

echo "Running secret hygiene tests (SEC-02)...\n";

$rootDir = dirname(__DIR__);

// 1. Test: No live API keys in tracked repository files (excluding tests & ignored .env)
$scanDirs = ['examples', 'prod', 'public', 'quickstart', 'scripts', 'src', 'config'];
$liveKeyPattern = '/wa_live_[0-9a-f]{24,}/i';

foreach ($scanDirs as $dir) {
    $fullDir = $rootDir . DIRECTORY_SEPARATOR . $dir;
    if (!is_dir($fullDir)) continue;

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($fullDir));
    foreach ($iterator as $file) {
        if ($file->isDir()) continue;
        $filePath = $file->getPathname();
        if (str_contains($filePath, 'node_modules') || str_contains($filePath, '.git')) continue;

        $content = file_get_contents($filePath);
        hygiene_assert(
            !preg_match($liveKeyPattern, $content),
            "No live API keys should be embedded in {$filePath}"
        );
    }
}

// 2. Test: Root SQL schema files must not contain sensitive INSERT INTO data
$sqlFiles = ['clean_tidb_import.sql', 'schema_export.sql', 'database.sql', 'prod/schema_production.sql'];
foreach ($sqlFiles as $sqlRel) {
    $sqlPath = $rootDir . DIRECTORY_SEPARATOR . $sqlRel;
    if (!file_exists($sqlPath)) continue;

    $content = file_get_contents($sqlPath);
    hygiene_assert(
        !preg_match('/INSERT\s+INTO\s+[`"]?(users|api_keys|whatsapp_sessions)[`"]?/i', $content),
        "SQL schema file {$sqlRel} must not contain production INSERT INTO user/key statements"
    );
}

// 3. Test: Documentation instructions use generic placeholders
$instructionsPath = $rootDir . '/prod/INSTRUCTIONS.md';
if (file_exists($instructionsPath)) {
    $instructions = file_get_contents($instructionsPath);
    hygiene_assert(
        !str_contains($instructions, '3QGgvLi97sMDpPA.root'),
        'prod/INSTRUCTIONS.md must not contain live TiDB usernames'
    );
    hygiene_assert(
        !str_contains($instructions, 'a7c8e547f63d410db901e149c71c6ae2'),
        'prod/INSTRUCTIONS.md must not contain hardcoded worker tokens'
    );
}

// 4. Test: Demo CSV templates use mock phone numbers
$csvPath = $rootDir . '/public/assets/birthday-task-template.csv';
if (file_exists($csvPath)) {
    $csv = file_get_contents($csvPath);
    hygiene_assert(
        !str_contains($csv, '9507286092'),
        'CSV templates must not expose personal phone numbers'
    );
}

echo "All secret hygiene unit tests passed successfully!\n";
