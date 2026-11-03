<?php

declare(strict_types=1);

/**
 * Web API Maintenance Endpoint: Database SQL Backup & Clean
 *
 * Secure endpoint callable from dashboard or remote maintenance runners.
 * Requires admin session or ?token parameter matching SYSTEM_API_SECRET / WORKER_TOKEN.
 */
require_once dirname(__DIR__, 2) . '/scripts/backup_and_clean.php';
