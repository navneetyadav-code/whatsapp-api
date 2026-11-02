<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/src/bootstrap.php';

$user = current_user();
if (!$user) {
    json_response(['ok' => false, 'error' => 'Unauthorized. Please log in.'], 401);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$id = !empty($input['id']) ? (int) $input['id'] : null;
$label = trim((string) ($input['label'] ?? 'Primary SMTP'));
$host = trim((string) ($input['host'] ?? ''));
$port = isset($input['port']) ? max(1, (int) $input['port']) : 587;
$encryption = trim((string) ($input['encryption'] ?? 'tls'));
$username = trim((string) ($input['username'] ?? ''));
$password = (string) ($input['password'] ?? '');
$fromEmail = trim((string) ($input['from_email'] ?? ''));
$fromName = trim((string) ($input['from_name'] ?? 'AI Assistant'));
$priority = isset($input['priority']) ? max(1, (int) $input['priority']) : 1;

if (!in_array($encryption, ['tls', 'ssl', 'none'], true)) {
    $encryption = 'tls';
}

if ($host === '') {
    json_response(['ok' => false, 'error' => 'SMTP Host server is required (e.g. smtp.gmail.com).'], 422);
    exit;
}

if ($username === '') {
    json_response(['ok' => false, 'error' => 'SMTP Username/Email is required.'], 422);
    exit;
}

if ($fromEmail === '' || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
    // If from_email is not valid, fallback to username if username is email
    if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
        $fromEmail = $username;
    } else {
        json_response(['ok' => false, 'error' => 'Valid "From Email" address is required.'], 422);
        exit;
    }
}

$pdo = Database::pdo();
$userId = (int) $user['id'];

try {
    if ($id !== null) {
        $checkStmt = $pdo->prepare('SELECT id, password FROM user_smtp_credentials WHERE id = ? AND user_id = ? LIMIT 1');
        $checkStmt->execute([$id, $userId]);
        $existing = $checkStmt->fetch();
        if (!$existing) {
            json_response(['ok' => false, 'error' => 'SMTP record not found.'], 404);
            exit;
        }

        $finalPassword = ($password !== '') ? $password : (string) $existing['password'];

        $upStmt = $pdo->prepare(
            'UPDATE user_smtp_credentials
             SET label = ?, host = ?, port = ?, encryption = ?, username = ?, password = ?, from_email = ?, from_name = ?, priority = ?, status = "active", last_error = NULL
             WHERE id = ? AND user_id = ?'
        );
        $upStmt->execute([$label, $host, $port, $encryption, $username, $finalPassword, $fromEmail, $fromName, $priority, $id, $userId]);
        $smtpId = $id;
    } else {
        if ($password === '') {
            json_response(['ok' => false, 'error' => 'SMTP Password or App Password is required.'], 422);
            exit;
        }

        $insStmt = $pdo->prepare(
            'INSERT INTO user_smtp_credentials 
             (user_id, label, host, port, encryption, username, password, from_email, from_name, priority, is_active, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, "active")'
        );
        $insStmt->execute([$userId, $label, $host, $port, $encryption, $username, $password, $fromEmail, $fromName, $priority]);
        $smtpId = (int) $pdo->lastInsertId();
    }

    json_response([
        'ok' => true,
        'message' => 'SMTP credentials saved successfully.',
        'smtp' => [
            'id' => $smtpId,
            'label' => $label,
            'host' => $host,
            'port' => $port,
            'encryption' => $encryption,
            'username' => $username,
            'from_email' => $fromEmail,
            'from_name' => $fromName,
            'priority' => $priority,
            'status' => 'active'
        ]
    ]);
} catch (\Throwable $e) {
    json_response(['ok' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
}
