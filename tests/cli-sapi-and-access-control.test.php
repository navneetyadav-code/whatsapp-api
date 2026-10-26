<?php

declare(strict_types=1);

function test_assert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

echo "Running CLI SAPI & Access Control Security Tests (Fixes for CWE-306, CWE-552, CWE-754)...\n";

$rootDir = dirname(__DIR__);

// =========================================================================
// 1. Database Migration Script SAPI Execution Guard (CWE-306 / CWE-425)
// =========================================================================

$migratePath = $rootDir . '/scripts/migrate.php';
test_assert(file_exists($migratePath), 'scripts/migrate.php must exist');
$migrateSource = (string) file_get_contents($migratePath);

test_assert(
    str_contains($migrateSource, "PHP_SAPI !== 'cli'") && str_contains($migrateSource, 'http_response_code(404)'),
    'scripts/migrate.php must enforce CLI-only execution with HTTP 404 response'
);

test_assert(
    preg_match('/if\s*\(\s*PHP_SAPI\s*!==\s*\'cli\'\s*\)\s*\{\s*http_response_code\(404\);\s*exit;\s*\}/m', $migrateSource) === 1,
    'scripts/migrate.php must immediately abort with 404 if not running under CLI'
);

// Verify scripts/create-initial-admin.php also enforces CLI guard
$adminScriptPath = $rootDir . '/scripts/create-initial-admin.php';
test_assert(file_exists($adminScriptPath), 'scripts/create-initial-admin.php must exist');
$adminScriptSource = (string) file_get_contents($adminScriptPath);
test_assert(
    str_contains($adminScriptSource, "PHP_SAPI !== 'cli'"),
    'scripts/create-initial-admin.php must enforce CLI-only execution'
);

// =========================================================================
// 2. Web Server Root Access Control Rule Gap (.htaccess / CWE-552)
// =========================================================================

$htaccessPath = $rootDir . '/.htaccess';
test_assert(file_exists($htaccessPath), 'Root .htaccess must exist');
$htaccess = (string) file_get_contents($htaccessPath);

test_assert(
    str_contains($htaccess, 'RedirectMatch 404') && str_contains($htaccess, 'scripts') && str_contains($htaccess, 'tests'),
    'Root .htaccess must redirect access to internal scripts and tests directories to 404'
);

test_assert(
    preg_match('/RedirectMatch\s+404\s+"\^\/\(whatsapp-api\/\)\?\(config\|src\|worker\|scripts\|tests\)\(\/\|\$\)"/', $htaccess) === 1,
    'Root .htaccess RedirectMatch pattern must block config, src, worker, scripts, and tests'
);

// =========================================================================
// 3. Worker Request Body Safety on /sessions/:userId/send (CWE-754 / CWE-20)
// =========================================================================

$workerPath = $rootDir . '/worker/server.mjs';
test_assert(file_exists($workerPath), 'worker/server.mjs must exist');
$workerSource = (string) file_get_contents($workerPath);

test_assert(
    str_contains($workerSource, 'normalizeRecipient(req.body?.to)'),
    'worker/server.mjs must use optional chaining req.body?.to in direct send handler'
);

echo "All CLI SAPI & Access Control security unit tests passed successfully!\n";
