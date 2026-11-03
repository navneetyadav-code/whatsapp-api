<?php

declare(strict_types=1);

/**
 * DatabaseMaintenance
 *
 * Provides automated SQL backup generation and safe purging of all messages,
 * queues, delivery logs, and transient history while strictly preserving all
 * user accounts, credentials, configurations, AI settings, and system migrations.
 */
final class DatabaseMaintenance
{
    /**
     * Tables that MUST be preserved (User Data, Credentials, Configurations, System).
     */
    public const PRESERVED_TABLES = [
        'users'                 => 'User accounts and authentication credentials',
        'user_security'         => 'User MFA settings and security policies',
        'user_smtp_credentials' => 'User configured outbound SMTP mailer credentials',
        'api_keys'              => 'User API credentials and rate limits',
        'ai_agent_settings'     => 'User AI assistant configuration and prompt settings',
        'ai_provider_keys'      => 'User API keys for AI models (Gemini, OpenAI, Groq)',
        'ai_custom_tools'       => 'User custom registered tools and functions',
        'ai_knowledge_chunks'   => 'User AI knowledge base and RAG documents',
        'ai_training_examples'  => 'User AI few-shot training dataset examples',
        'chatbot_rules'         => 'User automated chatbot response rules',
        'webhooks'              => 'User webhook dispatch URLs and secret configurations',
        'whatsapp_sessions'     => 'WhatsApp Baileys device pairing credentials and sessions',
        'schema_migrations'     => 'System schema version tracking',
    ];

    /**
     * Tables to be cleaned (Messages, Queues, Delivery Logs, Telemetry, Transients).
     */
    public const CLEANED_TABLES = [
        'chat_messages'          => 'Live chat messages and conversation history',
        'chat_conversations'     => 'Live chat conversation sessions',
        'chat_escalations'       => 'Live chat support escalations',
        'chat_feedback'          => 'Live chat satisfaction ratings and feedback',
        'inbound_messages'       => 'Received WhatsApp webhook messages',
        'baileys_message_store'  => 'Raw Baileys WhatsApp protocol message cache',
        'message_queue'          => 'Pending and processed outbound message queue',
        'message_logs'           => 'Outbound message dispatch history and delivery receipts',
        'campaigns'              => 'Broadcast marketing campaigns',
        'contacts'               => 'Imported address book contacts',
        'recipient_suppressions' => 'Campaign recipient opt-out suppression records',
        'ai_action_drafts'       => 'AI-generated pending action drafts',
        'ai_tool_invocations'    => 'AI tool call telemetry and execution logs',
        'ai_user_memory'         => 'Conversational facts learned about contacts',
        'api_request_logs'       => 'HTTP API request access logs',
        'media_assets'           => 'Uploaded media asset database records',
        'webhook_deliveries'     => 'Webhook dispatch execution history',
        'webhook_queue'          => 'Pending webhook dispatch backlog',
        'login_attempts'         => 'Authentication audit logs and throttling records',
        'password_resets'        => 'Temporary password reset security tokens',
    ];

    /**
     * Generate a complete SQL backup of the database via PDO.
     *
     * @param string|null $targetDir Custom output directory (defaults to storage/backups)
     * @return array Backup metadata
     */
    public static function createSqlBackup(?string $targetDir = null): array
    {
        $startTime = microtime(true);
        $pdo = Database::pdo();

        $dbName = (string) $pdo->query('SELECT DATABASE()')->fetchColumn();
        if ($dbName === '') {
            throw new RuntimeException('Cannot create backup: No active database selected.');
        }

        $baseDir = dirname(__DIR__, 2);
        $backupDir = $targetDir ?? ($baseDir . '/storage/backups');
        if (!is_dir($backupDir) && !mkdir($backupDir, 0755, true) && !is_dir($backupDir)) {
            throw new RuntimeException("Failed to create backup directory: {$backupDir}");
        }

        $timestamp = date('Y-m-d_His');
        $randomSuffix = bin2hex(random_bytes(4));
        $filePath = "{$backupDir}/backup_{$dbName}_{$timestamp}_{$randomSuffix}.sql";

        $handle = fopen($filePath, 'wb');
        if (!$handle) {
            throw new RuntimeException("Failed to open backup file for writing: {$filePath}");
        }

        // Write SQL Header
        $now = date('Y-m-d H:i:s T');
        $header = <<<SQL
-- ============================================================
-- WhatsGateway Automated Database Backup
-- Database: `{$dbName}`
-- Generated at: {$now}
-- Server version: {$pdo->getAttribute(PDO::ATTR_SERVER_VERSION)}
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET NAMES utf8mb4;
SET time_zone = "+00:00";


SQL;
        fwrite($handle, $header);

        // Fetch all base tables
        $tablesStmt = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
        $tables = $tablesStmt ? $tablesStmt->fetchAll(PDO::FETCH_NUM) : [];

        $totalTables = 0;
        $totalRows = 0;
        $tableSummaries = [];

        foreach ($tables as $tableRow) {
            $table = $tableRow[0];
            $totalTables++;

            // Drop and recreate table DDL
            fwrite($handle, "--\n-- Table structure for table `{$table}`\n--\n");
            fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");

            $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
            $createRow = $createStmt ? $createStmt->fetch(PDO::FETCH_ASSOC) : null;
            $createSql = $createRow['Create Table'] ?? null;
            if (!$createSql) {
                continue;
            }
            fwrite($handle, $createSql . ";\n\n");

            // Dump data in chunks
            fwrite($handle, "--\n-- Dumping data for table `{$table}`\n--\n");

            $countStmt = $pdo->query("SELECT COUNT(*) FROM `{$table}`");
            $rowCount = (int) ($countStmt ? $countStmt->fetchColumn() : 0);
            $tableSummaries[$table] = $rowCount;

            if ($rowCount === 0) {
                fwrite($handle, "-- (Table is empty)\n\n");
                continue;
            }

            $totalRows += $rowCount;
            $batchSize = 250;
            $offset = 0;

            // Fetch columns to guarantee deterministic INSERT syntax
            $colStmt = $pdo->query("SHOW COLUMNS FROM `{$table}`");
            $columns = $colStmt ? $colStmt->fetchAll(PDO::FETCH_COLUMN) : [];
            $quotedColumns = implode(', ', array_map(fn($col) => "`{$col}`", $columns));

            while ($offset < $rowCount) {
                $dataStmt = $pdo->query("SELECT * FROM `{$table}` LIMIT {$batchSize} OFFSET {$offset}");
                $rows = $dataStmt ? $dataStmt->fetchAll(PDO::FETCH_ASSOC) : [];
                if (empty($rows)) {
                    break;
                }

                $valuesSqlList = [];
                foreach ($rows as $row) {
                    $escapedVals = [];
                    foreach ($columns as $col) {
                        $val = $row[$col] ?? null;
                        if ($val === null) {
                            $escapedVals[] = 'NULL';
                        } elseif (is_int($val) || is_float($val)) {
                            $escapedVals[] = (string) $val;
                        } elseif (is_string($val)) {
                            $escapedVals[] = $pdo->quote($val);
                        } else {
                            $escapedVals[] = $pdo->quote((string) $val);
                        }
                    }
                    $valuesSqlList[] = '(' . implode(', ', $escapedVals) . ')';
                }

                if (!empty($valuesSqlList)) {
                    $insertSql = "INSERT INTO `{$table}` ({$quotedColumns}) VALUES\n" . implode(",\n", $valuesSqlList) . ";\n";
                    fwrite($handle, $insertSql);
                }

                $offset += $batchSize;
            }

            fwrite($handle, "\n");
        }

        // Footer
        fwrite($handle, "-- Re-enable Foreign Key constraints\nSET FOREIGN_KEY_CHECKS = 1;\n");
        fclose($handle);

        $fileSize = filesize($filePath) ?: 0;
        $duration = round(microtime(true) - $startTime, 3);

        return [
            'status'          => 'success',
            'file'            => $filePath,
            'filename'        => basename($filePath),
            'database'        => $dbName,
            'size_bytes'      => $fileSize,
            'size_formatted'  => self::formatBytes($fileSize),
            'tables_dumped'   => $totalTables,
            'rows_dumped'     => $totalRows,
            'duration_sec'    => $duration,
            'table_summaries' => $tableSummaries,
            'created_at'      => date('c')
        ];
    }

    /**
     * Purge all message tables, queues, logs, and telemetry while preserving user data.
     *
     * @param bool $dryRun When true, calculates rows without modifying database
     * @return array Cleanup statistics
     */
    public static function cleanDatabase(bool $dryRun = false): array
    {
        $startTime = microtime(true);
        $pdo = Database::pdo();

        // 1. Snapshot state of Preserved Tables
        $preservedCounts = [];
        foreach (self::PRESERVED_TABLES as $table => $description) {
            try {
                $stmt = $pdo->query("SELECT COUNT(*) FROM `{$table}`");
                $count = (int) ($stmt ? $stmt->fetchColumn() : 0);
                $preservedCounts[$table] = [
                    'description' => $description,
                    'rows'        => $count,
                    'status'      => 'PRESERVED'
                ];
            } catch (Throwable) {
                // Table might not exist yet if migrations haven't run
                $preservedCounts[$table] = [
                    'description' => $description,
                    'rows'        => 0,
                    'status'      => 'NOT_FOUND'
                ];
            }
        }

        // 2. Count and purge Cleaned Tables
        $cleanedCounts = [];
        $totalRowsCleared = 0;

        if (!$dryRun) {
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        }

        foreach (self::CLEANED_TABLES as $table => $description) {
            try {
                $countStmt = $pdo->query("SELECT COUNT(*) FROM `{$table}`");
                $rowCount = (int) ($countStmt ? $countStmt->fetchColumn() : 0);

                if (!$dryRun) {
                    $pdo->exec("TRUNCATE TABLE `{$table}`");
                }

                $totalRowsCleared += $rowCount;
                $cleanedCounts[$table] = [
                    'description'  => $description,
                    'rows_cleared' => $rowCount,
                    'status'       => $dryRun ? 'WOULD_PURGE' : 'PURGED'
                ];
            } catch (Throwable $e) {
                // Fallback to DELETE FROM if TRUNCATE fails on foreign key locks
                if (!$dryRun) {
                    try {
                        $pdo->exec("DELETE FROM `{$table}`");
                        $cleanedCounts[$table] = [
                            'description'  => $description,
                            'rows_cleared' => $rowCount ?? 0,
                            'status'       => 'DELETED_FALLBACK'
                        ];
                    } catch (Throwable $inner) {
                        $cleanedCounts[$table] = [
                            'description' => $description,
                            'error'       => $inner->getMessage(),
                            'status'      => 'FAILED'
                        ];
                    }
                } else {
                    $cleanedCounts[$table] = [
                        'description' => $description,
                        'error'       => $e->getMessage(),
                        'status'      => 'NOT_FOUND'
                    ];
                }
            }
        }

        if (!$dryRun) {
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
        }

        $duration = round(microtime(true) - $startTime, 3);

        return [
            'status'             => 'success',
            'mode'               => $dryRun ? 'dry_run' : 'executed',
            'total_rows_cleared' => $totalRowsCleared,
            'cleaned_tables'     => $cleanedCounts,
            'preserved_tables'   => $preservedCounts,
            'duration_sec'       => $duration,
            'executed_at'        => date('c')
        ];
    }

    /**
     * Clean temporary media uploads in public/uploads/ while preserving .htaccess and folder structure.
     */
    public static function cleanUploadFiles(bool $dryRun = false): array
    {
        $uploadsDir = dirname(__DIR__, 2) . '/public/uploads';
        if (!is_dir($uploadsDir)) {
            return ['status' => 'skipped', 'files_deleted' => 0, 'bytes_freed' => 0];
        }

        $deletedCount = 0;
        $bytesFreed = 0;

        $files = scandir($uploadsDir) ?: [];
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || $file === '.htaccess' || $file === '.gitkeep') {
                continue;
            }
            $fullPath = "{$uploadsDir}/{$file}";
            if (is_file($fullPath)) {
                $size = filesize($fullPath) ?: 0;
                $bytesFreed += $size;
                $deletedCount++;
                if (!$dryRun) {
                    @unlink($fullPath);
                }
            }
        }

        return [
            'status'          => 'success',
            'files_deleted'   => $deletedCount,
            'bytes_freed'     => $bytesFreed,
            'bytes_formatted' => self::formatBytes($bytesFreed),
            'mode'            => $dryRun ? 'dry_run' : 'executed'
        ];
    }

    /**
     * Human-readable byte formatting.
     */
    public static function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = (int) floor(log($bytes, 1024));
        return round($bytes / (1024 ** $i), 2) . ' ' . ($units[$i] ?? 'B');
    }
}
