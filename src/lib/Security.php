<?php

declare(strict_types=1);

/**
 * WhatsGateway — Advanced Authentication & Security Library
 *
 * Implements RFC 6238 Time-Based One-Time Password (TOTP) 2FA,
 * Google OAuth account linking/unlinking guardrails, multi-provider credentials,
 * single-use backup recovery codes, and password lifecycle management.
 */

function ensure_security_schema(?PDO $pdo = null): void
{
    $pdo = $pdo ?? Database::pdo();
    $driver = (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

    if ($driver === 'sqlite') {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS `user_security` (' .
            '`user_id` INTEGER PRIMARY KEY, ' .
            '`totp_secret` TEXT DEFAULT NULL, ' .
            '`totp_enabled` INTEGER NOT NULL DEFAULT 0, ' .
            '`google_linked` INTEGER NOT NULL DEFAULT 0, ' .
            '`google_id` TEXT DEFAULT NULL, ' .
            '`google_email` TEXT DEFAULT NULL, ' .
            '`password_set` INTEGER NOT NULL DEFAULT 1, ' .
            '`backup_codes` TEXT DEFAULT NULL, ' .
            '`updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP' .
            ')'
        );
        return;
    }

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS `user_security` (' .
        '`user_id` bigint(20) unsigned NOT NULL, ' .
        '`totp_secret` varchar(128) DEFAULT NULL, ' .
        '`totp_enabled` tinyint(1) NOT NULL DEFAULT 0, ' .
        '`google_linked` tinyint(1) NOT NULL DEFAULT 0, ' .
        '`google_id` varchar(190) DEFAULT NULL, ' .
        '`google_email` varchar(190) DEFAULT NULL, ' .
        '`password_set` tinyint(1) NOT NULL DEFAULT 1, ' .
        '`backup_codes` text DEFAULT NULL, ' .
        '`updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(), ' .
        'PRIMARY KEY (`user_id`), ' .
        'CONSTRAINT `fk_user_security_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE' .
        ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
}

function get_user_security(int $userId, ?PDO $pdo = null): array
{
    $pdo = $pdo ?? Database::pdo();
    ensure_security_schema($pdo);
    $stmt = $pdo->prepare('SELECT * FROM user_security WHERE user_id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $sec = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($sec) {
        return $sec;
    }

    $userStmt = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $userStmt->execute([$userId]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    $hasPassword = !empty($user['password_hash']);

    try {
        $insert = $pdo->prepare('INSERT INTO user_security (user_id, totp_enabled, google_linked, password_set) VALUES (?, 0, 0, ?)');
        $insert->execute([$userId, $hasPassword ? 1 : 0]);
    } catch (Throwable) {
        // Concurrent row creation handled gracefully
    }

    $stmt->execute([$userId]);
    $sec = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($sec) {
        return $sec;
    }

    return [
        'user_id' => $userId,
        'totp_secret' => null,
        'totp_enabled' => 0,
        'google_linked' => 0,
        'google_id' => null,
        'google_email' => null,
        'password_set' => $hasPassword ? 1 : 0,
        'backup_codes' => null,
    ];
}

function base32_encode_str(string $data): string
{
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $binary = '';
    $len = strlen($data);
    for ($i = 0; $i < $len; $i++) {
        $binary .= str_pad(decbin(ord($data[$i])), 8, '0', STR_PAD_LEFT);
    }
    $res = '';
    $binLen = strlen($binary);
    for ($i = 0; $i < $binLen; $i += 5) {
        $chunk = substr($binary, $i, 5);
        if (strlen($chunk) < 5) {
            $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
        }
        $res .= $chars[bindec($chunk)];
    }
    return $res;
}

function base32_decode_str(string $s): string
{
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $s = strtoupper(rtrim($s, '='));
    $bits = '';
    $len = strlen($s);
    for ($i = 0; $i < $len; $i++) {
        $val = strpos($chars, $s[$i]);
        if ($val === false) {
            return '';
        }
        $bits .= str_pad(decbin($val), 5, '0', STR_PAD_LEFT);
    }
    $bytes = '';
    $bitLen = strlen($bits);
    for ($i = 0; $i + 8 <= $bitLen; $i += 8) {
        $bytes .= chr((int) bindec(substr($bits, $i, 8)));
    }
    return $bytes;
}

function totp_generate_secret(int $bytes = 20): string
{
    return base32_encode_str(random_bytes($bytes));
}

function totp_code(string $secret, ?int $timeSlice = null): string
{
    if ($timeSlice === null) {
        $timeSlice = (int) floor(time() / 30);
    }
    $secretKey = base32_decode_str($secret);
    $time = pack('N*', 0) . pack('N*', $timeSlice);
    $hmac = hash_hmac('sha1', $time, $secretKey, true);
    $offset = ord(substr($hmac, -1)) & 0x0F;
    $hashpart = substr($hmac, $offset, 4);
    $unpacked = unpack('N', $hashpart);
    $value = $unpacked[1] & 0x7FFFFFFF;
    return str_pad((string) ($value % 1000000), 6, '0', STR_PAD_LEFT);
}

function totp_verify(string $secret, string $code, int $discrepancy = 1): bool
{
    $cleanCode = preg_replace('/\s+/', '', trim($code));
    if (!ctype_digit($cleanCode) || strlen($cleanCode) !== 6) {
        return false;
    }
    $currentTimeSlice = (int) floor(time() / 30);
    for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
        if (hash_equals(totp_code($secret, $currentTimeSlice + $i), $cleanCode)) {
            return true;
        }
    }
    return false;
}

function totp_uri(string $userEmail, string $secret, string $issuer = 'WhatsGateway'): string
{
    return 'otpauth://totp/' . rawurlencode($issuer) . ':' . rawurlencode($userEmail)
        . '?secret=' . rawurlencode($secret)
        . '&issuer=' . rawurlencode($issuer)
        . '&algorithm=SHA1&digits=6&period=30';
}

function generate_backup_codes(int $count = 8, int $cost = 10): array
{
    $plain = [];
    $hashed = [];
    $options = ['cost' => max(4, min(12, $cost))];
    for ($i = 0; $i < $count; $i++) {
        $code = strtoupper(bin2hex(random_bytes(4)));
        $plain[] = $code;
        $hashed[] = password_hash($code, PASSWORD_BCRYPT, $options);
    }
    return ['plain' => $plain, 'hashed' => $hashed];
}

function verify_and_consume_backup_code(int $userId, string $code, ?PDO $pdo = null): bool
{
    $pdo = $pdo ?? Database::pdo();
    $sec = get_user_security($userId, $pdo);
    if (empty($sec['backup_codes'])) {
        return false;
    }
    $hashedCodes = json_decode((string) $sec['backup_codes'], true) ?: [];
    $clean = strtoupper(trim($code));

    foreach ($hashedCodes as $idx => $hash) {
        if (password_verify($clean, $hash)) {
            unset($hashedCodes[$idx]);
            $stmt = $pdo->prepare('UPDATE user_security SET backup_codes = ? WHERE user_id = ?');
            $stmt->execute([json_encode(array_values($hashedCodes)), $userId]);
            return true;
        }
    }
    return false;
}

function enable_user_totp(int $userId, string $secret, ?PDO $pdo = null, int $cost = 10): array
{
    $pdo = $pdo ?? Database::pdo();
    ensure_security_schema($pdo);
    $backup = generate_backup_codes(8, $cost);
    $jsonCodes = json_encode($backup['hashed']);

    $check = $pdo->prepare('SELECT user_id FROM user_security WHERE user_id = ? LIMIT 1');
    $check->execute([$userId]);
    if ($check->fetch()) {
        $stmt = $pdo->prepare('UPDATE user_security SET totp_secret = ?, totp_enabled = 1, backup_codes = ? WHERE user_id = ?');
        $stmt->execute([$secret, $jsonCodes, $userId]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO user_security (user_id, totp_secret, totp_enabled, backup_codes) VALUES (?, ?, 1, ?)');
        $stmt->execute([$userId, $secret, $jsonCodes]);
    }
    return $backup['plain'];
}

function disable_user_totp(int $userId, ?PDO $pdo = null): void
{
    $pdo = $pdo ?? Database::pdo();
    ensure_security_schema($pdo);
    $stmt = $pdo->prepare(
        'UPDATE user_security SET totp_enabled = 0, totp_secret = NULL, backup_codes = NULL WHERE user_id = ?'
    );
    $stmt->execute([$userId]);
}

function can_unlink_google(int $userId, ?PDO $pdo = null): bool
{
    $pdo = $pdo ?? Database::pdo();
    $sec = get_user_security($userId, $pdo);
    $userStmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ? LIMIT 1');
    $userStmt->execute([$userId]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    return !empty($user['password_hash']) && !empty($sec['password_set']);
}

function unlink_google_account(int $userId, ?PDO $pdo = null): bool
{
    $pdo = $pdo ?? Database::pdo();
    if (!can_unlink_google($userId, $pdo)) {
        return false;
    }
    $stmt = $pdo->prepare(
        'UPDATE user_security SET google_linked = 0, google_id = NULL, google_email = NULL WHERE user_id = ?'
    );
    return $stmt->execute([$userId]);
}

function link_google_account(int $userId, ?string $googleId, string $googleEmail, ?PDO $pdo = null): void
{
    $pdo = $pdo ?? Database::pdo();
    ensure_security_schema($pdo);
    $check = $pdo->prepare('SELECT user_id FROM user_security WHERE user_id = ? LIMIT 1');
    $check->execute([$userId]);
    if ($check->fetch()) {
        $stmt = $pdo->prepare('UPDATE user_security SET google_linked = 1, google_id = ?, google_email = ? WHERE user_id = ?');
        $stmt->execute([$googleId, $googleEmail, $userId]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO user_security (user_id, google_linked, google_id, google_email) VALUES (?, 1, ?, ?)');
        $stmt->execute([$userId, $googleId, $googleEmail]);
    }
}

function update_user_password(int $userId, string $newPassword, ?PDO $pdo = null): void
{
    if (strlen($newPassword) < 8) {
        throw new InvalidArgumentException('Password must be at least 8 characters long.');
    }
    $pdo = $pdo ?? Database::pdo();
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([$hash, $userId]);
    ensure_security_schema($pdo);

    $check = $pdo->prepare('SELECT user_id FROM user_security WHERE user_id = ? LIMIT 1');
    $check->execute([$userId]);
    if ($check->fetch()) {
        $pdo->prepare('UPDATE user_security SET password_set = 1 WHERE user_id = ?')->execute([$userId]);
    } else {
        $pdo->prepare('INSERT INTO user_security (user_id, password_set) VALUES (?, 1)')->execute([$userId]);
    }
}
