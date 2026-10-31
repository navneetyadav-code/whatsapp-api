<?php

declare(strict_types=1);

/**
 * WhatsGateway — Local Setup & Deployment Utility
 *
 * SEC-05: Remote Execution Protection
 * This script is strictly restricted to CLI execution to prevent remote database
 * truncate attacks, arbitrary admin registration, and shell injection.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Forbidden: setup.php can only be executed via the command line interface (CLI).\n";
    exit(1);
}

// Banner
echo "\n";
echo "==============================================================\n";
echo "  🚀 WhatsGateway — Local Setup & Deployment Wizard\n";
echo "==============================================================\n";
echo "This utility will configure your database, create the initial\n";
echo "administrator account, generate your .env file, install npm\n";
echo "dependencies, and automatically delete itself once complete.\n";
echo "==============================================================\n\n";

// Helper for CLI arguments
$options = getopt('', [
    'host::',
    'port::',
    'db::',
    'user::',
    'pass::',
    'admin-name::',
    'admin-email::',
    'admin-pass::',
    'force',
    'no-delete',
    'help',
]);

if (isset($options['help'])) {
    echo "Usage: php setup.php [options]\n\n";
    echo "Options:\n";
    echo "  --host=<host>          MySQL Host (default: 127.0.0.1)\n";
    echo "  --port=<port>          MySQL Port (default: 3306)\n";
    echo "  --db=<name>            Database Name (default: whatsapp_api)\n";
    echo "  --user=<user>          MySQL User (default: root)\n";
    echo "  --pass=<pass>          MySQL Password (default: empty)\n";
    echo "  --admin-name=<name>    Admin Full Name (default: Administrator)\n";
    echo "  --admin-email=<email>  Admin Email Login (default: admin@example.com)\n";
    echo "  --admin-pass=<pass>    Admin Password (min 8 characters)\n";
    echo "  --force                Overwrite existing .env without prompt\n";
    echo "  --no-delete            Do not automatically delete setup.php on completion\n";
    echo "  --help                 Show this help screen\n\n";
    exit(0);
}

$isInteractive = function_exists('stream_isatty') ? stream_isatty(STDIN) : true;

function cli_prompt(string $prompt, string $default = '', bool $isInteractive = true): string
{
    if (!$isInteractive) {
        return $default;
    }
    $suffix = $default !== '' ? " [{$default}]" : '';
    echo "{$prompt}{$suffix}: ";
    $input = trim((string) fgets(STDIN));
    return $input !== '' ? $input : $default;
}

$envPath = __DIR__ . '/.env';
$isInstalled = file_exists($envPath);
$force = isset($options['force']);
$noDelete = isset($options['no-delete']);

if ($isInstalled && !$force) {
    echo "[!] An existing .env configuration was detected.\n";
    $confirm = cli_prompt("Do you want to re-run setup and overwrite existing settings? (y/N)", "N", $isInteractive);
    if (strtolower($confirm) !== 'y') {
        echo "Setup aborted by user. Your existing configuration remains untouched.\n";
        exit(0);
    }
}

// 1. Gather Configuration
echo "\n--- [Step 1/5] Database Credentials ---\n";
$dbHost = (string) ($options['host'] ?? cli_prompt("Database Host", "127.0.0.1", $isInteractive));
$dbPort = (int) ($options['port'] ?? cli_prompt("Database Port", "3306", $isInteractive));
$dbName = (string) ($options['db'] ?? cli_prompt("Database Name", "whatsapp_api", $isInteractive));
$dbUser = (string) ($options['user'] ?? cli_prompt("Database User", "root", $isInteractive));
$dbPass = (string) ($options['pass'] ?? cli_prompt("Database Password (leave blank if none)", "", $isInteractive));

echo "\n--- [Step 2/5] Admin Account Details ---\n";
$adminName = (string) ($options['admin-name'] ?? cli_prompt("Administrator Name", "Administrator", $isInteractive));
$adminEmail = strtolower(trim((string) ($options['admin-email'] ?? cli_prompt("Administrator Email (Login ID)", "admin@example.com", $isInteractive))));

while (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
    echo "[!] Invalid email address. Please enter a valid email.\n";
    $adminEmail = strtolower(trim(cli_prompt("Administrator Email", "admin@example.com", $isInteractive)));
}

$adminPass = (string) ($options['admin-pass'] ?? '');
if ($adminPass === '') {
    while (strlen($adminPass) < 8) {
        $adminPass = cli_prompt("Administrator Password (minimum 8 characters)", "", $isInteractive);
        if (strlen($adminPass) < 8) {
            echo "[!] Password must be at least 8 characters long.\n";
        }
    }
}

// 2. Connect & Initialize Database
echo "\n--- [Step 3/5] Initializing Database & Schema ---\n";
try {
    echo "Connecting to MySQL server at {$dbHost}:{$dbPort}...\n";
    $pdo = new PDO("mysql:host={$dbHost};port={$dbPort};charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "  ✔ Connected successfully to MySQL server.\n";

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$dbName}`");
    echo "  ✔ Database '{$dbName}' selected.\n";

    // Read and run initial schema
    $schemaPath = __DIR__ . '/migrations/001_initial_schema.sql';
    if (!file_exists($schemaPath)) {
        throw new RuntimeException("Schema file not found at: {$schemaPath}");
    }

    $schemaSql = (string) file_get_contents($schemaPath);
    $schemaSql = preg_replace('/^\s*--.*$/m', '', $schemaSql) ?? $schemaSql;
    $statements = preg_split('/;\s*(?:\R|$)/', $schemaSql, -1, PREG_SPLIT_NO_EMPTY) ?: [];

    $createdTables = 0;
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if ($statement === '') {
            continue;
        }
        $pdo->exec($statement);
        if (preg_match('/^CREATE TABLE IF NOT EXISTS `([a-z0-9_]+)`/i', $statement)) {
            $createdTables++;
        }
    }

    // Ensure sleeping column in whatsapp_sessions
    try {
        $colCheck = $pdo->query("SHOW COLUMNS FROM `whatsapp_sessions` LIKE 'sleeping'")->fetch();
        if (!$colCheck) {
            $pdo->exec("ALTER TABLE `whatsapp_sessions` ADD COLUMN `sleeping` tinyint(1) NOT NULL DEFAULT 0 AFTER `status`");
        }
    } catch (Throwable) {}

    // Mark migration version
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS `schema_migrations` (" .
        "`version` varchar(120) NOT NULL, `applied_at` timestamp NOT NULL DEFAULT current_timestamp(), " .
        "PRIMARY KEY (`version`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
    $pdo->prepare("INSERT IGNORE INTO schema_migrations (version) VALUES (?)")->execute(['001_initial_schema']);
    echo "  ✔ Applied initial schema ({$createdTables} tables ready).\n";

    // Setup Admin account
    $passwordHash = password_hash($adminPass, PASSWORD_DEFAULT);
    $checkUser = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $checkUser->execute([$adminEmail]);
    $userRow = $checkUser->fetch();

    if ($userRow) {
        $userId = (int) $userRow['id'];
        $pdo->prepare("UPDATE users SET name = ?, password_hash = ? WHERE id = ?")->execute([$adminName, $passwordHash, $userId]);
        echo "  ✔ Existing administrator account updated ({$adminEmail}).\n";
    } else {
        $pdo->prepare("INSERT INTO users (name, email, phone, password_hash) VALUES (?, ?, '', ?)")->execute([$adminName, $adminEmail, $passwordHash]);
        $userId = (int) $pdo->lastInsertId();
        echo "  ✔ Administrator account created ({$adminEmail}).\n";
    }

    $pdo->prepare("INSERT IGNORE INTO whatsapp_sessions (user_id) VALUES (?)")->execute([$userId]);
    $pdo->prepare("INSERT IGNORE INTO webhooks (user_id) VALUES (?)")->execute([$userId]);

    // Ensure user_security row
    try {
        $pdo->prepare("INSERT IGNORE INTO user_security (user_id, totp_enabled, google_linked, password_set) VALUES (?, 0, 0, 1)")->execute([$userId]);
    } catch (Throwable) {}

} catch (Throwable $e) {
    fwrite(STDERR, "\n[ERROR] Database initialization failed: " . $e->getMessage() . "\n");
    exit(1);
}

// 3. Generate .env file
echo "\n--- [Step 4/5] Generating Environment File (.env) ---\n";
try {
    $workerToken = bin2hex(random_bytes(32));
    $envContent = <<<ENV
APP_ENV=local
APP_NAME="WhatsGateway"
APP_PUBLIC_URL=http://localhost/whatsapp-api/public
APP_BASE_PATH=/whatsapp-api/public
APP_SESSION_NAME=wa_api_hub
APP_SECURE_COOKIES=false
ENABLE_TEST_PAGE=true

# Security & Private Instance Lockdown
ENABLE_REGISTRATION=true
DISABLE_PASSWORD_LOGIN=false
ALLOW_NEW_DEVICES=false
ADMIN_EMAIL={$adminEmail}

# Authentication & Brute Force Protection
LOGIN_MAX_ATTEMPTS=5
LOGIN_LOCKOUT_MINUTES=15
ALLOW_QUERY_AUTH=false

# Database Configuration
DB_DSN="mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4"
DB_HOST={$dbHost}
DB_PORT={$dbPort}
DB_NAME={$dbName}
DB_USER={$dbUser}
DB_PASSWORD={$dbPass}
DB_SSL=false
DB_SSL_CA=

# Background Worker Settings
WORKER_URL=http://127.0.0.1:3107
WORKER_PORT=3107
WORKER_TOKEN={$workerToken}

# Anti-Ban & Dispatch Timing
DEFAULT_COUNTRY_CODE=91
SEND_DELAY_MS=2500
QUEUE_POLL_MS=3000
LOG_LEVEL=warn

# Password Reset & OTP Delivery
OTP_VIA_EMAIL=true
OTP_VIA_PHONE=true
ENV;

    if (file_put_contents($envPath, $envContent . "\n") === false) {
        throw new RuntimeException("Could not write to {$envPath}. Check filesystem permissions.");
    }
    echo "  ✔ Environment file (.env) generated successfully.\n";
} catch (Throwable $e) {
    fwrite(STDERR, "\n[ERROR] Failed to create .env file: " . $e->getMessage() . "\n");
    exit(1);
}

// 4. Install Worker Dependencies
echo "\n--- [Step 5/5] Installing Worker Node Dependencies (NPM) ---\n";
$workerDir = __DIR__ . DIRECTORY_SEPARATOR . 'worker';
if (is_dir($workerDir)) {
    echo "Checking npm installation in worker/...\n";
    $npmCmd = PHP_OS_FAMILY === 'Windows' ? 'npm.cmd' : 'npm';
    $npmCommand = "cd " . escapeshellarg($workerDir) . " && {$npmCmd} install --no-audit --no-fund 2>&1";
    $npmOutput = shell_exec($npmCommand);
    if ($npmOutput !== null) {
        echo "  ✔ Worker NPM packages installed successfully.\n";
    } else {
        echo "  [!] Note: npm command execution finished. Ensure Node.js & npm are in PATH.\n";
    }
} else {
    echo "  [!] Worker directory not found; skipping npm install.\n";
}

// Complete Summary
echo "\n==============================================================\n";
echo "🎉 Setup Complete! WhatsGateway is ready.\n";
echo "==============================================================\n";
echo "Account Email : {$adminEmail}\n";
echo "Dashboard URL : http://localhost/whatsapp-api/public\n";
echo "\nNext Steps:\n";
echo "  1. Start the WhatsApp worker:\n";
echo "     - Windows : Double-click 'start-worker.bat' or run '.\\start-worker.bat start'\n";
echo "     - Linux   : cd worker && node server.mjs\n";
echo "  2. Open http://localhost/whatsapp-api/public in your browser.\n";
echo "  3. Log in with your email and password, then scan the WhatsApp QR code.\n";
echo "==============================================================\n\n";

// Automatic Self-Deletion
if (!$noDelete) {
    echo "[*] Cleaning up installation files...\n";
    $currentFile = __FILE__;
    if (@unlink($currentFile)) {
        echo "[✓] setup.php has automatically deleted itself for your security.\n\n";
    } else {
        echo "[!] Notice: Setup completed, but please manually delete setup.php.\n\n";
    }
} else {
    echo "[i] Flag --no-delete detected: setup.php was preserved.\n\n";
}

exit(0);
