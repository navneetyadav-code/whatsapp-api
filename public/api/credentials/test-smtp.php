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
$host = trim((string) ($input['host'] ?? ''));
$port = isset($input['port']) ? (int) $input['port'] : 587;
$encryption = trim((string) ($input['encryption'] ?? 'tls'));
$username = trim((string) ($input['username'] ?? ''));
$password = (string) ($input['password'] ?? '');
$fromEmail = trim((string) ($input['from_email'] ?? ''));
$fromName = trim((string) ($input['from_name'] ?? 'AI Assistant'));
$testRecipient = trim((string) ($input['test_recipient'] ?? ''));

$pdo = Database::pdo();
$userId = (int) $user['id'];

// If record ID passed, fill missing fields from DB
if ($id !== null) {
    $stmt = $pdo->prepare('SELECT * FROM user_smtp_credentials WHERE id = ? AND user_id = ? LIMIT 1');
    $stmt->execute([$id, $userId]);
    $smtpRow = $stmt->fetch();
    if ($smtpRow) {
        if ($host === '') $host = (string) $smtpRow['host'];
        if ($port <= 0) $port = (int) $smtpRow['port'];
        if ($encryption === '') $encryption = (string) $smtpRow['encryption'];
        if ($username === '') $username = (string) $smtpRow['username'];
        if ($password === '') $password = (string) $smtpRow['password'];
        if ($fromEmail === '') $fromEmail = (string) $smtpRow['from_email'];
        if ($fromName === '') $fromName = (string) $smtpRow['from_name'];
    }
}

if ($host === '' || $username === '' || $password === '') {
    json_response(['ok' => false, 'error' => 'Host, Username, and Password are required to test SMTP.'], 422);
    exit;
}

if ($fromEmail === '') {
    $fromEmail = filter_var($username, FILTER_VALIDATE_EMAIL) ? $username : 'noreply@localhost';
}

$startTime = microtime(true);

function smtp_cmd($socket, string $cmd, array $expectedCodes = [250]): array {
    if ($cmd !== '') {
        fwrite($socket, $cmd . "\r\n");
    }
    $response = '';
    while (!feof($socket)) {
        $line = fgets($socket, 1024);
        if ($line === false) break;
        $response .= $line;
        if (strlen($line) >= 4 && $line[3] === ' ') break;
    }
    $code = (int) substr($response, 0, 3);
    if (!in_array($code, $expectedCodes, true)) {
        return [false, $code, trim($response)];
    }
    return [true, $code, trim($response)];
}

try {
    $targetHost = ($encryption === 'ssl' || $port === 465) ? "ssl://{$host}" : $host;
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ]);

    $socket = @stream_socket_client(
        "{$targetHost}:{$port}",
        $errno,
        $errstr,
        10,
        STREAM_CLIENT_CONNECT,
        $context
    );

    if (!$socket) {
        throw new \Exception("Connection failed to {$host}:{$port} ({$errno}: {$errstr})");
    }

    stream_set_timeout($socket, 12);

    // 1. Initial Greeting
    [$ok, $code, $resp] = smtp_cmd($socket, '', [220]);
    if (!$ok) throw new \Exception("Server greeting rejected: [{$code}] {$resp}");

    // 2. EHLO
    [$ok, $code, $resp] = smtp_cmd($socket, "EHLO localhost", [250]);
    if (!$ok) throw new \Exception("EHLO failed: [{$code}] {$resp}");

    // 3. STARTTLS if port 587 or tls encryption
    if (($port === 587 || $encryption === 'tls') && $encryption !== 'ssl' && $port !== 465) {
        [$ok, $code, $resp] = smtp_cmd($socket, "STARTTLS", [220]);
        if ($ok) {
            $crypto = @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            if (!$crypto) {
                throw new \Exception("TLS Handshake negotiation failed on {$host}:{$port}.");
            }
            [$ok, $code, $resp] = smtp_cmd($socket, "EHLO localhost", [250]);
            if (!$ok) throw new \Exception("EHLO after STARTTLS failed: [{$code}] {$resp}");
        }
    }

    // 4. AUTH LOGIN
    [$ok, $code, $resp] = smtp_cmd($socket, "AUTH LOGIN", [334]);
    if (!$ok) throw new \Exception("AUTH LOGIN command rejected: [{$code}] {$resp}");

    // Username
    [$ok, $code, $resp] = smtp_cmd($socket, base64_encode($username), [334]);
    if (!$ok) throw new \Exception("Username rejected by SMTP server: [{$code}] {$resp}");

    // Password
    [$ok, $code, $resp] = smtp_cmd($socket, base64_encode($password), [235]);
    if (!$ok) throw new \Exception("Authentication Failed (535): Invalid username or app password. [{$code}] {$resp}");

    // 5. Optional real dispatch test if test_recipient provided
    $sentMail = false;
    if ($testRecipient !== '' && filter_var($testRecipient, FILTER_VALIDATE_EMAIL)) {
        [$ok, $code, $resp] = smtp_cmd($socket, "MAIL FROM: <{$fromEmail}>", [250]);
        if (!$ok) throw new \Exception("MAIL FROM failed: [{$code}] {$resp}");

        [$ok, $code, $resp] = smtp_cmd($socket, "RCPT TO: <{$testRecipient}>", [250, 251]);
        if (!$ok) throw new \Exception("RCPT TO <{$testRecipient}> rejected: [{$code}] {$resp}");

        [$ok, $code, $resp] = smtp_cmd($socket, "DATA", [354]);
        if (!$ok) throw new \Exception("DATA initiation rejected: [{$code}] {$resp}");

        $date = date('r');
        $headers = [
            "Date: {$date}",
            "From: \"{$fromName}\" <{$fromEmail}>",
            "To: <{$testRecipient}>",
            "Subject: WhatsGateway AI Agent: SMTP Test Email",
            "X-Mailer: WhatsGateway-Tenant-SMTP",
            "Content-Type: text/plain; charset=UTF-8"
        ];
        $body = "Congratulations!\n\nYour SMTP email server [{$host}] is successfully verified and isolated for your WhatsGateway workspace.\n\nTested At: " . date('Y-m-d H:i:s T') . "\nSender: {$fromName} <{$fromEmail}>";

        $emailData = implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.";
        [$ok, $code, $resp] = smtp_cmd($socket, $emailData, [250]);
        if (!$ok) throw new \Exception("Message delivery rejected: [{$code}] {$resp}");
        $sentMail = true;
    }

    smtp_cmd($socket, "QUIT", [221, 250]);
    fclose($socket);

    $latencyMs = round((microtime(true) - $startTime) * 1000);

    // Update DB status if ID exists
    if ($id !== null) {
        $pdo->prepare('UPDATE user_smtp_credentials SET status = "active", last_error = NULL, last_used_at = NOW(), fail_count = 0 WHERE id = ? AND user_id = ?')
            ->execute([$id, $userId]);
    }

    $msg = $sentMail
        ? "✓ SMTP Authenticated and test email successfully sent to {$testRecipient} in {$latencyMs}ms!"
        : "✓ SMTP Handshake & Authentication succeeded in {$latencyMs}ms!";

    json_response([
        'ok' => true,
        'latency_ms' => $latencyMs,
        'status' => 'active',
        'message' => $msg
    ]);
} catch (\Throwable $e) {
    $latencyMs = round((microtime(true) - $startTime) * 1000);
    $errorMsg = $e->getMessage();

    if ($id !== null) {
        $status = str_contains(strtolower($errorMsg), 'auth') ? 'auth_failed' : 'connection_failed';
        $pdo->prepare('UPDATE user_smtp_credentials SET status = ?, last_error = ?, fail_count = fail_count + 1 WHERE id = ? AND user_id = ?')
            ->execute([$status, substr($errorMsg, 0, 500), $id, $userId]);
    }

    json_response([
        'ok' => false,
        'latency_ms' => $latencyMs,
        'status' => 'error',
        'error' => $errorMsg
    ], 422);
}
