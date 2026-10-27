<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/src/bootstrap.php';

/**
 * Resolves the authenticated user for Live Chat API endpoints.
 * Supports both web dashboard session authentication and API key headers.
 */
function get_chat_authenticated_user(): array
{
    $user = current_user();
    if ($user) {
        return $user;
    }

    $apiUser = authenticate_api_request();
    if ($apiUser && !empty($apiUser['user_id'])) {
        $stmt = Database::pdo()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([(int) $apiUser['user_id']]);
        $row = $stmt->fetch();
        if ($row) {
            return $row;
        }
    }

    json_response([
        'ok' => false,
        'error' => 'Unauthorized. Please log in or provide a valid X-API-Key header.',
    ], 401);
    exit;
}

/**
 * Normalizes any phone number into canonical international digits.
 * Automatically prepends '91' for 10-digit Indian numbers.
 * Automatically resolves WhatsApp Linked Device Identifiers (LIDs) to actual phone numbers.
 */
function normalize_chat_phone(string $rawPhone, int $userId = 1): string
{
    $digits = preg_replace('/\D/', '', $rawPhone);
    if (str_starts_with($digits, '00')) {
        $digits = substr($digits, 2);
    }
    if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
        $digits = '91' . substr($digits, 1);
    }
    if (strlen($digits) === 10) {
        $digits = '91' . $digits;
    }

    // WhatsApp LIDs are typically 14-15 digits (e.g. 28343109968029)
    if (strlen($digits) > 12) {
        $sessionDir = 'C:/xampp/whatsapp-api-data/sessions/user_' . $userId;
        $revFile = $sessionDir . '/lid-mapping-' . $digits . '_reverse.json';
        if (file_exists($revFile)) {
            $content = trim((string) @file_get_contents($revFile));
            $resolved = json_decode($content, true);
            if (!empty($resolved)) {
                $pn = preg_replace('/\D/', '', (string) $resolved);
                if (strlen($pn) === 10) {
                    $pn = '91' . $pn;
                }
                return $pn;
            }
        }
    }

    return $digits;
}

/**
 * Formats a canonical phone number for clean UI display.
 * E.g. '919507286092' -> '+91 95072 86092'
 */
function format_chat_phone(string $phone): string
{
    $clean = preg_replace('/\D/', '', $phone);
    if (strlen($clean) === 12 && str_starts_with($clean, '91')) {
        return '+91 ' . substr($clean, 2, 5) . ' ' . substr($clean, 7, 5);
    }
    if ($clean !== '') {
        return '+' . $clean;
    }
    return $phone;
}

/**
 * Formats a timestamp into a human-friendly relative time string.
 */
function chat_time_ago(string $datetime): string
{
    $time = strtotime($datetime);
    if (!$time) {
        return $datetime;
    }
    $diff = time() - $time;
    if ($diff < 60) {
        return 'just now';
    }
    if ($diff < 3600) {
        return floor($diff / 60) . 'm ago';
    }
    if ($diff < 86400) {
        return floor($diff / 3600) . 'h ago';
    }
    if ($diff < 604800) {
        return floor($diff / 86400) . 'd ago';
    }
    return date('M j', $time);
}
