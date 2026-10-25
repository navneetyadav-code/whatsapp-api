<?php

declare(strict_types=1);

function create_api_key(int $userId, string $name = 'API key'): array
{
    $apiKey = 'wa_live_' . bin2hex(random_bytes(24));
    $apiSecret = 'was_' . bin2hex(random_bytes(32));

    $stmt = Database::pdo()->prepare(
        'INSERT INTO api_keys (user_id, name, key_hash, key_prefix, secret_hash) VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $userId,
        trim($name) ?: 'API key',
        hash('sha256', $apiKey),
        substr($apiKey, 0, 22),
        password_hash($apiSecret, PASSWORD_DEFAULT),
    ]);

    return [
        'id' => (int) Database::pdo()->lastInsertId(),
        'api_key' => $apiKey,
        'api_secret' => $apiSecret,
    ];
}

function regenerate_api_credentials(int $userId): array
{
    return create_api_key($userId, 'Generated key');
}

function list_api_keys(int $userId): array
{
    $stmt = Database::pdo()->prepare(
        'SELECT id, name, key_prefix, status, ip_allowlist, rate_per_minute, rate_per_hour, rate_per_day, last_used_at, created_at, allow_humanize
         FROM api_keys WHERE user_id = ? ORDER BY id DESC'
    );
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function update_api_key_status(int $userId, int $keyId, string $status): void
{
    if (!in_array($status, ['active', 'disabled'], true)) {
        return;
    }

    $stmt = Database::pdo()->prepare('UPDATE api_keys SET status = ? WHERE id = ? AND user_id = ?');
    $stmt->execute([$status, $keyId, $userId]);
}

function delete_api_key(int $userId, int $keyId): void
{
    $stmt = Database::pdo()->prepare('DELETE FROM api_keys WHERE id = ? AND user_id = ?');
    $stmt->execute([$keyId, $userId]);
}

function update_api_key_limits(int $userId, int $keyId, array $data): void
{
    $minute = max(1, min(300, (int) ($data['rate_per_minute'] ?? 20)));
    $hour = max($minute, min(5000, (int) ($data['rate_per_hour'] ?? 300)));
    $day = max($hour, min(50000, (int) ($data['rate_per_day'] ?? 1000)));
    $allowlist = trim((string) ($data['ip_allowlist'] ?? ''));
    $allowHumanize = empty($data['allow_humanize']) ? 0 : 1;

    $stmt = Database::pdo()->prepare(
        'UPDATE api_keys SET rate_per_minute = ?, rate_per_hour = ?, rate_per_day = ?, ip_allowlist = ?, allow_humanize = ? WHERE id = ? AND user_id = ?'
    );
    $stmt->execute([$minute, $hour, $day, $allowlist ?: null, $allowHumanize, $keyId, $userId]);
}

function authenticate_api_request(): ?array
{
    $key = $_SERVER['HTTP_X_API_KEY'] ?? '';
    $secret = !empty($_SERVER['HTTP_X_API_SECRET']) ? $_SERVER['HTTP_X_API_SECRET'] : null;
    $authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';

    if (!$authorization && function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        $authorization = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        if (!$key) {
            $key = $headers['X-Api-Key'] ?? $headers['x-api-key'] ?? '';
        }
        if (!$secret && !empty($headers['X-Api-Secret'])) {
            $secret = $headers['X-Api-Secret'];
        } elseif (!$secret && !empty($headers['x-api-secret'])) {
            $secret = $headers['x-api-secret'];
        }
    }

    // If using Bearer authentication
    if (!$key && str_starts_with($authorization, 'Bearer ')) {
        $token = substr($authorization, 7);
        if (str_contains($token, ':')) {
            // Backwards compatibility for key:secret format
            [$key, $secret] = array_pad(explode(':', $token, 2), 2, null);
        } else {
            // Modern Single-Token format (API Key acts as the sole token)
            $key = $token;
            $secret = null; // Bypass secret validation
        }
    }

    // Fallback: check POST body or guarded query params for single-key authentication
    if (!$key) {
        // Priority 1: Request POST body payload (safe from access logs)
        $key = $_POST['api_key'] ?? $_POST['key'] ?? $_POST['access_token'] ?? '';

        // Priority 2: JSON payload if POST body was json
        if (!$key && !empty($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json')) {
            $jsonData = request_json();
            $key = $jsonData['api_key'] ?? $jsonData['key'] ?? $jsonData['access_token'] ?? '';
        }

        // Priority 3: GET query parameter (SEC-07: Guarded to prevent log leakage; requires ALLOW_QUERY_AUTH=true)
        if (!$key && (bool) app_config('security.allow_query_auth', false)) {
            $key = $_GET['api_key'] ?? $_GET['key'] ?? $_GET['access_token'] ?? '';
        }
    }

    if (!is_string($key) || $key === '') {
        return null;
    }

    $stmt = Database::pdo()->prepare(
        'SELECT api_keys.*, users.email, users.name AS user_name
         FROM api_keys
         JOIN users ON users.id = api_keys.user_id
         WHERE api_keys.key_hash = ? LIMIT 1'
    );
    $stmt->execute([hash('sha256', $key)]);
    $apiKey = $stmt->fetch();

    if (!$apiKey || $apiKey['status'] !== 'active') {
        return null;
    }

    // Only verify secret if it was explicitly provided (legacy dual-auth method)
    if (!empty($secret) && !password_verify($secret, $apiKey['secret_hash'] ?? '')) {
        return null;
    }

    if (!api_key_ip_allowed($apiKey, client_ip())) {
        return null;
    }

    Database::pdo()->prepare('UPDATE api_keys SET last_used_at = NOW() WHERE id = ?')->execute([$apiKey['id']]);

    return $apiKey;
}

function api_key_ip_allowed(array $apiKey, string $ip): bool
{
    $allowlist = trim((string) ($apiKey['ip_allowlist'] ?? ''));
    if ($allowlist === '') {
        return true;
    }

    $allowed = array_filter(array_map('trim', preg_split('/[\s,]+/', $allowlist) ?: []));
    return in_array($ip, $allowed, true);
}

function client_ip(): string
{
    $remote = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');

    // Only inspect reverse-proxy headers if the direct connecting socket is a trusted proxy or loopback
    $isProxy = in_array($remote, ['127.0.0.1', '::1'], true)
        || str_starts_with($remote, '10.')
        || str_starts_with($remote, '172.')
        || str_starts_with($remote, '192.168.');

    if ($isProxy) {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_REAL_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_CLIENT_IP',
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                // X-Forwarded-For can contain a comma-separated list of IPs. The first one is the client.
                $ips = explode(',', (string) $_SERVER[$header]);
                $ip = trim($ips[0]);

                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
    }

    return $remote;
}
