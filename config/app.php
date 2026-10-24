<?php

declare(strict_types=1);

if (!function_exists('config_load_env')) {
    function config_load_env(string $path): void
    {
        if (!is_file($path)) {
            return;
        }

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = array_map('trim', explode('=', $line, 2));
            $value = trim($value, "\"'");
            if ($key !== '' && getenv($key) === false) {
                putenv("{$key}={$value}");
                $_ENV[$key] = $value;
            }
        }
    }
}

if (!function_exists('config_env')) {
    function config_env(string $key, ?string $default = null): ?string
    {
        $value = getenv($key);
        return $value === false ? $default : $value;
    }
}

if (!function_exists('config_bool')) {
    function config_bool(string $key, bool $default = false): bool
    {
        $value = config_env($key);
        if ($value === null) {
            return $default;
        }

        return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
    }
}

config_load_env(dirname(__DIR__) . '/.env');

$appEnv = config_env('APP_ENV', 'local');
$workerToken = config_env('WORKER_TOKEN', 'change-this-worker-token-before-production');
$workerPort = config_env('WORKER_PORT', '3107');

if (!ctype_digit((string) $workerPort) || (int) $workerPort < 1 || (int) $workerPort > 65535) {
    throw new RuntimeException('WORKER_PORT must be a valid TCP port number.');
}

if ($appEnv === 'production' && (strlen($workerToken) < 32 || $workerToken === 'change-this-worker-token-before-production')) {
    throw new RuntimeException('Set a random WORKER_TOKEN of at least 32 characters before running in production.');
}

$dbHost = config_env('DB_HOST', '127.0.0.1');
$dbPort = config_env('DB_PORT', '3306');
$dbName = config_env('DB_NAME', 'whatsapp_api');
if ($dbName !== 'whatsapp_api') {
    throw new RuntimeException('DB_NAME must be whatsapp_api for this deployment.');
}
$defaultDsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
$dbDsn = config_env('DB_DSN', $defaultDsn);
$dsnDatabase = [];
preg_match('/(?:^|;)dbname=([^;]+)/i', (string) preg_replace('/^mysql:/i', '', $dbDsn), $dsnDatabase);
if (empty($dsnDatabase[1])
    || $dsnDatabase[1] !== 'whatsapp_api') {
    throw new RuntimeException('DB_DSN must point to the whatsapp_api database.');
}
if (!ctype_digit((string) $dbPort) || (int) $dbPort < 1 || (int) $dbPort > 65535) {
    throw new RuntimeException('DB_PORT must be a valid TCP port number.');
}
$dsnHost = $dbHost;
$dsnPort = (string) $dbPort;
$dsnHostMatch = [];
$dsnPortMatch = [];
preg_match('/(?:^|;)host=([^;]+)/i', (string) preg_replace('/^mysql:/i', '', $dbDsn), $dsnHostMatch);
preg_match('/(?:^|;)port=([^;]+)/i', (string) preg_replace('/^mysql:/i', '', $dbDsn), $dsnPortMatch);
if (!empty($dsnHostMatch[1])) {
    $dsnHost = $dsnHostMatch[1];
}
if (!empty($dsnPortMatch[1])) {
    $dsnPort = $dsnPortMatch[1];
}

return [
    'db' => [
        'dsn' => $dbDsn,
        'user' => config_env('DB_USER', 'root'),
        'password' => config_env('DB_PASSWORD', ''),
        'ssl' => str_contains(strtolower($dsnHost), 'tidbcloud.com') || $dsnPort === '4000' || config_bool('DB_SSL'),
        'ssl_ca' => config_env('DB_SSL_CA', ''),
    ],
    'app' => [
        'env' => $appEnv,
        'name' => config_env('APP_NAME', 'WhatsApp API Hub'),
        'public_url' => rtrim((string) config_env('APP_PUBLIC_URL', $appEnv === 'production' ? '' : 'http://localhost/whatsapp-api/public'), '/'),
        'base_path' => config_env('APP_BASE_PATH', $appEnv === 'production' ? '' : '/whatsapp-api/public'),
        'session_name' => config_env('APP_SESSION_NAME', 'wa_api_hub'),
        'secure_cookies' => config_bool('APP_SECURE_COOKIES', $appEnv === 'production'),
        'enable_test_page' => config_bool('ENABLE_TEST_PAGE', $appEnv !== 'production'),
        'enable_registration' => config_bool('ENABLE_REGISTRATION', true),
        'default_country_code' => config_env('DEFAULT_COUNTRY_CODE', 'in'),
    ],
    'security' => [
        'worker_token' => $workerToken,
        'login_max_attempts' => (int) config_env('LOGIN_MAX_ATTEMPTS', '5'),
        'login_lockout_minutes' => (int) config_env('LOGIN_LOCKOUT_MINUTES', '15'),
    ],
    'worker' => [
        // PHP and Node run in the same Render container. An omitted/blank URL
        // therefore uses the worker's loopback listener and the same port.
        'url' => rtrim(trim((string) config_env('WORKER_URL', '')) ?: 'http://127.0.0.1:' . $workerPort, '/'),
        'port' => (int) $workerPort,
    ],
    'otp' => [
        'via_email' => config_bool('OTP_VIA_EMAIL', true),
        'via_phone' => config_bool('OTP_VIA_PHONE', true),
    ],
    'system_api' => [
        'key' => config_env('SYSTEM_API_KEY', ''),
        'secret' => config_env('SYSTEM_API_SECRET', ''),
    ],
    'smtp' => [
        'host' => config_env('SMTP_HOST', ''),
        'port' => (int) config_env('SMTP_PORT', '587'),
        'user' => config_env('SMTP_USER', ''),
        'pass' => config_env('SMTP_PASS', ''),
        'from' => config_env('SMTP_FROM', 'noreply@localhost'),
        'from_name' => config_env('SMTP_FROM_NAME', 'WhatsApp API Hub'),
    ],
    'google' => [
        'client_id' => config_env('GOOGLE_CLIENT_ID', ''),
        'client_secret' => config_env('GOOGLE_CLIENT_SECRET', ''),
    ],
];
