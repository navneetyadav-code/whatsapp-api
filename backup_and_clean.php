<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

/**
 * Root convenience entry point for Database SQL Backup & Clean.
 * Delegates directly to scripts/backup_and_clean.php.
 */
require_once __DIR__ . '/scripts/backup_and_clean.php';
