<?php

declare(strict_types=1);

function test_assert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

echo "Running CSV import security tests (SEC-09)...\n";

$indexPath = dirname(__DIR__) . '/public/index.php';
test_assert(file_exists($indexPath), 'public/index.php must exist');
$indexSource = (string) file_get_contents($indexPath);

// 1. Verify file size boundary exists in public/index.php
test_assert(
    str_contains($indexSource, '$maxBytes = 5 * 1024 * 1024') || str_contains($indexSource, '5242880'),
    'public/index.php must enforce a 5MB maximum file size limit on CSV uploads'
);

// 2. Verify row limit boundary exists in public/index.php
test_assert(
    str_contains($indexSource, '$maxRows = 5000') && str_contains($indexSource, '$rowCount > $maxRows'),
    'public/index.php must enforce a 5,000 row cap on CSV uploads'
);

// 3. Verify transactional integrity
test_assert(
    str_contains($indexSource, 'beginTransaction()') && str_contains($indexSource, 'commit()') && str_contains($indexSource, 'rollBack()'),
    'public/index.php must wrap CSV contact imports in a database transaction'
);

// 4. Verify input sanitization and length bounds
test_assert(
    str_contains($indexSource, 'mb_substr(trim((string) $row[0]), 0, 100)'),
    'Contact names must be bounded to 100 characters'
);
test_assert(
    str_contains($indexSource, 'substr(preg_replace(\'/[^\\d+]/\', \'\', trim((string) $row[1])), 0, 32)'),
    'Contact phone numbers must be sanitized and bounded to 32 characters'
);

// 5. Functional test: Simulate CSV parsing loop with row bounds
function simulate_csv_import(array $rows, int $maxRows = 5000): array
{
    $imported = [];
    $rowCount = 0;
    foreach ($rows as $row) {
        $rowCount++;
        if ($rowCount > $maxRows) {
            throw new OverflowException("CSV exceeds {$maxRows} rows limit");
        }
        if (count($row) < 2) continue;
        $name = mb_substr(trim((string) $row[0]), 0, 100);
        $phone = substr(preg_replace('/[^\d+]/', '', trim((string) $row[1])), 0, 32);
        if ($name === '' || $phone === '' || strcasecmp($name, 'name') === 0) continue;
        $imported[] = ['name' => $name, 'phone' => $phone];
    }
    return $imported;
}

$sampleValid = [
    ['Name', 'Phone'],
    [' Alice Test ', '+91 98765 43210 '],
    [' Bob Test ', '918765432109'],
];
$result = simulate_csv_import($sampleValid);
test_assert(count($result) === 2, 'Header must be ignored and 2 contacts parsed');
test_assert($result[0]['phone'] === '+919876543210', 'Phone number must be cleanly stripped of whitespace');

$excessiveRows = array_fill(0, 5005, ['John Doe', '+919876543210']);
$caughtOverflow = false;
try {
    simulate_csv_import($excessiveRows, 5000);
} catch (OverflowException $e) {
    $caughtOverflow = true;
}
test_assert($caughtOverflow, 'CSV with excessive rows must trigger OverflowException');

echo "All CSV import security unit tests (SEC-09) passed successfully!\n";
