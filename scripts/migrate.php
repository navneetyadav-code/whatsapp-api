<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

function migrationSafeError(Throwable $error): string
{
    $message = $error->getMessage();
    foreach (['DB_PASSWORD', 'WORKER_TOKEN', 'SYSTEM_API_SECRET', 'SMTP_PASS'] as $key) {
        $secret = getenv($key);
        if (is_string($secret) && $secret !== '') {
            $message = str_replace($secret, '[redacted]', $message);
        }
    }
    return preg_replace('/(password|passwd|token|secret)=([^\s;]+)/i', '$1=[redacted]', $message) ?? 'Database migration failed.';
}

function migrationRun(): void
{
    $pdo = Database::pdo();
    $database = (string) $pdo->query('SELECT DATABASE()')->fetchColumn();
    if ($database === '') {
        throw new RuntimeException('Database initialization refused: no database selected.');
    }

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS `schema_migrations` (' .
        '`version` varchar(120) NOT NULL, `applied_at` timestamp NOT NULL DEFAULT current_timestamp(), ' .
        'PRIMARY KEY (`version`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $appliedStmt = $pdo->query('SELECT `version` FROM `schema_migrations`');
    $appliedVersions = $appliedStmt ? $appliedStmt->fetchAll(PDO::FETCH_COLUMN) : [];
    $appliedSet = array_flip($appliedVersions);

    $migrationFiles = glob(dirname(__DIR__) . '/migrations/*.sql');
    sort($migrationFiles);
    $tableDefinitions = [];

    foreach ($migrationFiles as $migrationPath) {
        $version = basename($migrationPath, '.sql');
        $isAlreadyApplied = isset($appliedSet[$version]);
        $sql = file_get_contents($migrationPath);
        if ($sql === false) {
            throw new RuntimeException("Could not read {$migrationPath}.");
        }

        $sql = preg_replace('/^\s*--.*$/m', '', $sql) ?? $sql;
        $sql = preg_replace('/^\s*USE\s+`?[a-z0-9_]+`?\s*;/mi', '', $sql) ?? $sql;
        $statements = preg_split('/;\s*(?:\R|$)/', $sql, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if ($statement === '' || preg_match('/^USE\s+/i', $statement)) {
                continue;
            }
            if (preg_match('/^CREATE TABLE IF NOT EXISTS `([a-z0-9_]+)`/i', $statement, $matches)) {
                $tableDefinitions[$matches[1]] = $statement;
            }
            if (!$isAlreadyApplied) {
                try {
                    $pdo->exec($statement);
                } catch (PDOException $e) {
                    $code = (int) ($e->errorInfo[1] ?? 0);
                    // Ignore duplicate table (1050), duplicate column (1060), duplicate key (1061)
                    if (!in_array($code, [1050, 1060, 1061, 1091], true)) {
                        throw $e;
                    }
                }
            }
        }
        if (!$isAlreadyApplied) {
            $pdo->prepare('INSERT IGNORE INTO schema_migrations (version) VALUES (?)')->execute([$version]);
        }
    }

    foreach ($tableDefinitions as $table => $definition) {
        migrationEnsureColumns($pdo, $table, $definition);
        migrationEnsureIndexes($pdo, $table, $definition);
        migrationEnsureForeignKeys($pdo, $table, $definition);
    }

    $requiredTables = array_keys($tableDefinitions);
    $placeholders = implode(',', array_fill(0, count($requiredTables), '?'));
    $query = $pdo->prepare(
        'SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE() ' .
        "AND table_name IN ({$placeholders})"
    );
    $query->execute($requiredTables);
    $existing = $query->fetchAll(PDO::FETCH_COLUMN);
    $missing = array_values(array_diff($requiredTables, $existing));
    if ($missing !== []) {
        throw new RuntimeException('Required database tables are missing: ' . implode(', ', $missing));
    }

    $record = $pdo->prepare('INSERT IGNORE INTO schema_migrations (version) VALUES (?)');
    foreach ($migrationFiles as $migrationPath) {
        $record->execute([basename($migrationPath, '.sql')]);
    }
    fwrite(STDOUT, 'Database schema is ready: ' . count($requiredTables) . ' application tables in ' . $database . '.' . PHP_EOL);
}

function migrationEnsureIndexes(PDO $pdo, string $table, string $definition): void
{
    $indexes = [];
    if (preg_match('/^\s*PRIMARY\s+KEY\s*\(([^)]+)\)/mi', $definition, $primary)) {
        $indexes[] = ['kind' => 'PRIMARY', 'name' => 'PRIMARY', 'columns' => $primary[1]];
    }
    preg_match_all('/^\s*(UNIQUE\s+)?KEY\s+`([^`]+)`\s*\(([^)]+)\)/mi', $definition, $declared, PREG_SET_ORDER);
    foreach ($declared as $index) {
        $indexes[] = [
            'kind' => trim((string) ($index[1] ?? '')) === '' ? 'INDEX' : 'UNIQUE',
            'name' => $index[2],
            'columns' => $index[3],
        ];
    }
    $statement = $pdo->query('SHOW INDEX FROM `' . $table . '`');
    $present = [];
    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $index) {
        $present[(string) $index['Key_name']] = true;
    }

    foreach ($indexes as $index) {
        $kind = $index['kind'];
        $unique = $kind === 'UNIQUE' || $kind === 'PRIMARY';
        $name = $index['name'];
        if (isset($present[$name])) {
            continue;
        }
        $indexKind = $kind === 'PRIMARY' ? 'PRIMARY KEY' : ($unique ? 'UNIQUE INDEX' : 'INDEX');
        $quotedName = $kind === 'PRIMARY' ? '' : ' `' . $name . '`';
        try {
            $pdo->exec('ALTER TABLE `' . $table . '` ADD ' . $indexKind . $quotedName . ' (' . $index['columns'] . ')');
            $present[$name] = true;
        } catch (PDOException $e) {
            $code = (int) ($e->errorInfo[1] ?? 0);
            if (!in_array($code, [1060, 1061], true)) {
                throw $e;
            }
        }
    }
}

function migrationEnsureForeignKeys(PDO $pdo, string $table, string $definition): void
{
    preg_match_all(
        '/CONSTRAINT `([^`]+)` FOREIGN KEY \(([^)]+)\) REFERENCES `([^`]+)` \(([^)]+)\)(?: ON DELETE ([A-Z ]+))?/i',
        $definition,
        $constraints,
        PREG_SET_ORDER
    );
    foreach ($constraints as $constraint) {
        $localColumn = trim(str_replace('`', '', $constraint[2]));
        $referencedColumn = trim(str_replace('`', '', $constraint[4]));
        $check = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE ' .
            'WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? ' .
            'AND REFERENCED_TABLE_NAME = ? AND REFERENCED_COLUMN_NAME = ?'
        );
        $check->execute([$table, $localColumn, $constraint[3], $referencedColumn]);
        if ((int) $check->fetchColumn() > 0) {
            continue;
        }
        $delete = isset($constraint[5]) && trim($constraint[5]) !== ''
            ? ' ON DELETE ' . trim($constraint[5])
            : '';
        try {
            $pdo->exec(
                'ALTER TABLE `' . $table . '` ADD CONSTRAINT `' . $constraint[1] . '` ' .
                'FOREIGN KEY (' . $constraint[2] . ') REFERENCES `' . $constraint[3] . '` (' . $constraint[4] . ')' . $delete
            );
        } catch (PDOException $e) {
            $code = (int) ($e->errorInfo[1] ?? 0);
            if ($code !== 1061) {
                // Ignore constraint already exists
            }
        }
    }
}

function migrationEnsureColumns(PDO $pdo, string $table, string $definition): void
{
    $lines = preg_split('/\R/', $definition);
    $columnDefs = [];
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if (preg_match('/^`([^`]+)`\s+(.+?),?$/', $trimmed, $colMatch)) {
            $colName = $colMatch[1];
            $colDef = rtrim($colMatch[2], ',');
            if (!in_array(strtoupper($colName), ['PRIMARY', 'KEY', 'UNIQUE', 'CONSTRAINT', 'FOREIGN'], true)) {
                $columnDefs[$colName] = $colDef;
            }
        }
    }

    $actual = $pdo->query('SHOW COLUMNS FROM `' . $table . '`')->fetchAll(PDO::FETCH_COLUMN);
    $actualSet = array_flip($actual);

    foreach ($columnDefs as $colName => $colDef) {
        if (!isset($actualSet[$colName])) {
            try {
                $pdo->exec('ALTER TABLE `' . $table . '` ADD COLUMN `' . $colName . '` ' . $colDef);
                fwrite(STDOUT, "  ✔ Added missing column '{$colName}' to table '{$table}'.\n");
            } catch (Throwable $e) {
                throw new RuntimeException(
                    "Failed to add missing column {$colName} to {$table}: " . $e->getMessage()
                );
            }
        }
    }
}

try {
    migrationRun();
} catch (Throwable $error) {
    fwrite(STDERR, '[database-init] ' . get_class($error) . ': ' . migrationSafeError($error) . PHP_EOL);
    exit(1);
}
