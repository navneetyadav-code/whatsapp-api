<?php

declare(strict_types=1);

/**
 * CLI & Web Maintenance Utility: Database SQL Backup & Clean Messages
 *
 * Usage via CLI:
 *   php scripts/backup_and_clean.php
 *   php scripts/backup_and_clean.php --dry-run
 *   php scripts/backup_and_clean.php --backup-only
 *   php scripts/backup_and_clean.php --clean-only
 *   php scripts/backup_and_clean.php --clean-uploads
 */

require_once dirname(__DIR__) . '/src/bootstrap.php';

$isCli = (PHP_SAPI === 'cli');

// Parse CLI options
$options = [];
if ($isCli) {
    $options = getopt('', ['dry-run', 'backup-only', 'clean-only', 'clean-uploads', 'out-dir:', 'help']);
    if (isset($options['help'])) {
        echo <<<HELP
WhatsGateway Maintenance Utility: Database SQL Backup & Clean

Usage:
  php scripts/backup_and_clean.php [options]

Options:
  --dry-run        Simulate cleanup without deleting data
  --backup-only    Generate SQL dump only, skip purging tables
  --clean-only     Purge messages and queues without creating backup (caution!)
  --clean-uploads  Also delete orphaned media files in public/uploads/
  --out-dir=<path> Specify directory for SQL dump file (default: storage/backups)
  --help           Show this help message

HELP;
        exit(0);
    }
} else {
    // If accessed over HTTP, enforce admin authentication or system secret token
    header('Content-Type: application/json; charset=utf-8');

    $providedToken = (string) ($_GET['token'] ?? $_POST['token'] ?? $_SERVER['HTTP_X_MAINTENANCE_TOKEN'] ?? '');
    $validSecrets = array_filter([
        getenv('SYSTEM_API_SECRET'),
        getenv('WORKER_TOKEN'),
        getenv('DB_PASSWORD')
    ]);

    $isTokenValid = false;
    foreach ($validSecrets as $secret) {
        if (is_string($secret) && $secret !== '' && hash_equals($secret, $providedToken)) {
            $isTokenValid = true;
            break;
        }
    }

    $isAdminSession = false;
    if (class_exists('Auth') && method_exists('Auth', 'user')) {
        $currentUser = Auth::user();
        if ($currentUser && ($currentUser['role'] ?? '') === 'admin') {
            $isAdminSession = true;
        }
    }

    if (!$isTokenValid && !$isAdminSession) {
        http_response_code(403);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Unauthorized: Maintenance utility requires valid admin session or ?token parameter.'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit(1);
    }

    if (isset($_GET['dry-run'])) {
        $options['dry-run'] = true;
    }
    if (isset($_GET['backup-only'])) {
        $options['backup-only'] = true;
    }
    if (isset($_GET['clean-only'])) {
        $options['clean-only'] = true;
    }
    if (isset($_GET['clean-uploads'])) {
        $options['clean-uploads'] = true;
    }
}

$dryRun = isset($options['dry-run']);
$backupOnly = isset($options['backup-only']);
$cleanOnly = isset($options['clean-only']);
$cleanUploads = isset($options['clean-uploads']);
$outDir = isset($options['out-dir']) ? (string) $options['out-dir'] : null;

$response = [
    'timestamp' => date('c'),
    'dry_run'   => $dryRun,
    'backup'    => null,
    'cleanup'   => null,
    'uploads'   => null
];

if ($isCli) {
    echo "====================================================================\n";
    echo "          WHATSGATEWAY DATABASE BACKUP & SAFE CLEANUP               \n";
    echo "====================================================================\n";
    if ($dryRun) {
        echo "[!] DRY RUN MODE ACTIVATED - No database modifications will occur.\n";
    }
    echo "\n";
}

// -----------------------------------------------------------------------------
// STEP 1: CREATE SQL BACKUP (unless --clean-only is requested)
// -----------------------------------------------------------------------------
if (!$cleanOnly) {
    if ($isCli) {
        echo ">>> [1/2] Creating full SQL backup...\n";
    }
    try {
        $backupResult = DatabaseMaintenance::createSqlBackup($outDir);
        $response['backup'] = $backupResult;

        if ($isCli) {
            echo "    [✔] Backup successfully generated:\n";
            echo "        File:      {$backupResult['file']}\n";
            echo "        Size:      {$backupResult['size_formatted']} ({$backupResult['size_bytes']} bytes)\n";
            echo "        Tables:    {$backupResult['tables_dumped']}\n";
            echo "        Rows:      {$backupResult['rows_dumped']}\n";
            echo "        Time:      {$backupResult['duration_sec']}s\n\n";
        }
    } catch (Throwable $e) {
        if ($isCli) {
            echo "    [✖] BACKUP FAILED: " . $e->getMessage() . "\n";
            echo "    Cleanup aborted for safety to prevent data loss.\n";
            exit(1);
        } else {
            http_response_code(500);
            $response['status'] = 'error';
            $response['error'] = 'Backup failed: ' . $e->getMessage();
            echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            exit(1);
        }
    }
}

// -----------------------------------------------------------------------------
// STEP 2: CLEAN MESSAGES, LOGS, QUEUES & HISTORY (unless --backup-only)
// -----------------------------------------------------------------------------
if (!$backupOnly) {
    if ($isCli) {
        echo ">>> [2/2] Cleaning message tables and transient history...\n";
        echo "    (Preserving user accounts, credentials, AI configurations, and migrations)\n\n";
    }
    try {
        $cleanResult = DatabaseMaintenance::cleanDatabase($dryRun);
        $response['cleanup'] = $cleanResult;

        if ($isCli) {
            echo "    --- Cleaned Tables Breakdown ---\n";
            foreach ($cleanResult['cleaned_tables'] as $table => $info) {
                $status = $info['status'];
                $count = $info['rows_cleared'] ?? 0;
                printf("    • %-25s : %-10s (%d rows)\n", $table, $status, $count);
            }

            echo "\n    --- Preserved User & System Tables ---\n";
            foreach ($cleanResult['preserved_tables'] as $table => $info) {
                printf("    • %-25s : %-10s (%d rows intact)\n", $table, $info['status'], $info['rows']);
            }

            echo "\n    [✔] Total rows purged: {$cleanResult['total_rows_cleared']}\n";
            echo "    [✔] Execution time:    {$cleanResult['duration_sec']}s\n\n";
        }
    } catch (Throwable $e) {
        if ($isCli) {
            echo "    [✖] CLEANUP ENCOUNTERED ERROR: " . $e->getMessage() . "\n";
        } else {
            $response['cleanup_error'] = $e->getMessage();
        }
    }

    if ($cleanUploads) {
        if ($isCli) {
            echo ">>> Cleaning orphaned uploads in public/uploads/...\n";
        }
        $uploadsResult = DatabaseMaintenance::cleanUploadFiles($dryRun);
        $response['uploads'] = $uploadsResult;
        if ($isCli) {
            echo "    [✔] Deleted {$uploadsResult['files_deleted']} files ({$uploadsResult['bytes_formatted']} freed)\n\n";
        }
    }
}

if ($isCli) {
    echo "====================================================================\n";
    echo "                      MAINTENANCE COMPLETE                          \n";
    echo "====================================================================\n";
} else {
    // Check if web caller requested instant download of the backup file
    if (isset($_GET['download']) && !empty($response['backup']['file']) && file_exists($response['backup']['file'])) {
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . basename($response['backup']['file']) . '"');
        header('Content-Length: ' . filesize($response['backup']['file']));
        readfile($response['backup']['file']);
        exit(0);
    }

    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}
