<?php

declare(strict_types=1);

function app_config(?string $key = null): mixed
{
    global $config;

    if ($key === null) {
        return $config;
    }

    $value = $config;
    foreach (explode('.', $key) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return null;
        }
        $value = $value[$segment];
    }

    return $value;
}

function base_url(string $path = ''): string
{
    // Automatically convert legacy 'index.php?page=foo' into professional '/foo'
    if (preg_match('#^index\.php\?page=([a-zA-Z0-9_-]+)(?:&(.*))?$#', $path, $matches)) {
        $cleanPath = $matches[1];
        if (!empty($matches[2])) {
            $cleanPath .= '?' . $matches[2];
        }
        $path = $cleanPath;
    }

    $base = rtrim((string) app_config('app.base_path'), '/');
    $clean = ltrim($path, '/');
    return $clean === '' ? ($base !== '' ? $base : '/') : ($base !== '' ? $base . '/' . $clean : '/' . $clean);
}

function public_url(string $path = ''): string
{
    // Automatically convert legacy 'index.php?page=foo' into professional '/foo'
    if (preg_match('#^index\.php\?page=([a-zA-Z0-9_-]+)(?:&(.*))?$#', $path, $matches)) {
        $cleanPath = $matches[1];
        if (!empty($matches[2])) {
            $cleanPath .= '?' . $matches[2];
        }
        $path = $cleanPath;
    }

    $baseUrl = rtrim((string) app_config('app.public_url'), '/');
    if ($baseUrl === '' || !preg_match('#^https?://#i', $baseUrl)) {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
            || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);
        $scheme = $isHttps ? 'https' : 'http';
        $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
        $prefix = $baseUrl !== '' ? '/' . ltrim($baseUrl, '/') : '';
        $baseUrl = $scheme . '://' . $host . $prefix;
    }
    return $path !== '' ? $baseUrl . '/' . ltrim($path, '/') : $baseUrl;
}


function redirect_to(string $path): never
{
    header('Location: ' . base_url($path));
    exit;
}

function normalize_phone(string $phone): string {
    $phone = trim($phone);
    if ($phone === '') {
        return '';
    }
    if (str_ends_with($phone, '@g.us') || str_ends_with($phone, '@lid') || str_ends_with($phone, '@s.whatsapp.net')) {
        return $phone;
    }

    $default = strtolower((string) config_env('DEFAULT_COUNTRY_CODE', 'in'));
    $isoMap = [
        'in' => '91', 'us' => '1', 'ca' => '1', 'gb' => '44', 'uk' => '44',
        'au' => '61', 'ae' => '971', 'pk' => '92', 'bd' => '880', 'ng' => '234'
    ];
    $countryDial = $isoMap[$default] ?? preg_replace('/\D/', '', $default);
    if ($countryDial === '') {
        $countryDial = '91';
    }

    $digits = preg_replace('/\D/', '', $phone);
    if (str_starts_with($phone, '+')) {
        return '+' . $digits;
    }
    if (str_starts_with($digits, '00')) {
        return '+' . substr($digits, 2);
    }
    if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
        return '+' . $countryDial . substr($digits, 1);
    }
    if (strlen($digits) === 10) {
        return '+' . $countryDial . $digits;
    }
    return '+' . $digits;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(419);
        exit('Invalid security token.');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flashes(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

function request_json(): array
{
    $body = file_get_contents('php://input') ?: '';
    $data = json_decode($body, true);
    return is_array($data) ? $data : $_POST;
}

function is_local_request(): bool
{
    $ip = client_ip();
    return in_array($ip, ['127.0.0.1', '::1'], true) || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.');
}

function is_private_ip(string $ip): bool
{
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return true;
    }

    return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
}

function validate_webhook_url(?string $url): ?string
{
    $url = trim((string) $url);
    if ($url === '') {
        return null;
    }

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        throw new InvalidArgumentException('Enter a valid webhook URL.');
    }

    $parts = parse_url($url);
    $scheme = strtolower((string) ($parts['scheme'] ?? ''));
    $host = strtolower((string) ($parts['host'] ?? ''));

    if ($scheme !== 'https') {
        throw new InvalidArgumentException('Webhook URL must use HTTPS.');
    }

    if ($host === '' || in_array($host, ['localhost', '127.0.0.1', '::1'], true) || str_ends_with($host, '.local')) {
        throw new InvalidArgumentException('Webhook URL cannot point to localhost or private hosts.');
    }

    $addresses = [];
    if (filter_var($host, FILTER_VALIDATE_IP)) {
        $addresses[] = $host;
    } else {
        foreach (dns_get_record($host, DNS_A | DNS_AAAA) ?: [] as $record) {
            $address = $record['ip'] ?? $record['ipv6'] ?? null;
            if ($address) {
                $addresses[] = $address;
            }
        }
    }

    if (!$addresses) {
        throw new InvalidArgumentException('Webhook URL host could not be resolved safely.');
    }

    foreach ($addresses as $address) {
        if (is_private_ip($address)) {
            throw new InvalidArgumentException('Webhook URL cannot resolve to a private or reserved IP address.');
        }
    }

    return $url;
}

function seo_meta(?string $page): array {
    $baseUrl = rtrim((string) app_config('app.public_url'), '/');
    $defaults = [
        'title' => 'Free WhatsApp API Gateway & Dashboard | Self-Hosted Open Source',
        'description' => '100% Free WhatsApp API Gateway built with PHP, Node.js, MySQL & Baileys. Self-hosted REST API for sending messages, media, webhooks, and automation without Meta Cloud fees.',
        'keywords' => 'free whatsapp api, open source whatsapp api, whatsapp api php, baileys whatsapp api, self hosted whatsapp api, whatsapp gateway, whatsapp web automation, whatsapp webhook api, free whatsapp api github',
        'canonical' => $baseUrl . '/' . ($page === 'dashboard' ? '' : ltrim($page ?? '', '/')),
        'type' => 'website',
        'robots' => 'index, follow',
        'author' => 'Navneet Kumar Yadav',
    ];

    switch ($page) {
        case 'dashboard':
            $defaults['title'] = 'Dashboard | Free WhatsApp API Gateway';
            $defaults['description'] = 'Manage WhatsApp linked devices, view live queue depth, monitor delivery statistics, and generate secure REST API keys.';
            break;
        case 'login':
            $defaults['title'] = 'Login | Free WhatsApp API Hub';
            $defaults['description'] = 'Sign in to your self-hosted WhatsApp API Dashboard to manage messaging campaigns, webhooks, and API keys.';
            break;
        case 'register':
            $defaults['title'] = 'Create Free Account | WhatsApp API Hub';
            $defaults['description'] = 'Get started with self-hosted WhatsApp Web automation, multi-tenant message queues, and REST API endpoints.';
            break;
        case 'docs':
            $defaults['title'] = 'REST API Documentation & Code Examples | Free WhatsApp API';
            $defaults['description'] = 'Complete REST API documentation for cURL, Python, Node.js, PHP, and Go. Send WhatsApp messages, manage delivery queues, and configure webhooks.';
            break;
        case 'message':
            $defaults['title'] = 'Send WhatsApp Message | Free WhatsApp API Hub';
            break;
        case 'queue':
            $defaults['title'] = 'Live Message Queue & Delivery Logs | WhatsApp API';
            break;
        case 'credentials':
        case 'api-keys':
            $defaults['title'] = 'REST API Keys & Authentication | Free WhatsApp API';
            break;
    }

    return $defaults;
}

function send_smtp_email(string $to, string $subject, string $body): void {
    $host = app_config('smtp.host');
    $port = app_config('smtp.port') ?: 587;
    $user = app_config('smtp.user');
    $pass = app_config('smtp.pass');
    $from = app_config('smtp.from');
    $fromName = app_config('smtp.from_name');

    if (!$host) return;

    $socket = fsockopen($host, $port, $errno, $errstr, 10);
    if (!$socket) return;
    
    stream_set_timeout($socket, 10);
    fread($socket, 1024); // read greeting

    fwrite($socket, "EHLO localhost\r\n");
    fread($socket, 1024);

    if ($port == 587) {
        fwrite($socket, "STARTTLS\r\n");
        fread($socket, 1024);
        stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        fwrite($socket, "EHLO localhost\r\n");
        fread($socket, 1024);
    }

    if ($user && $pass) {
        fwrite($socket, "AUTH LOGIN\r\n");
        fread($socket, 1024);
        fwrite($socket, base64_encode($user) . "\r\n");
        fread($socket, 1024);
        fwrite($socket, base64_encode($pass) . "\r\n");
        fread($socket, 1024);
    }

    fwrite($socket, "MAIL FROM: <$from>\r\n");
    fread($socket, 1024);
    fwrite($socket, "RCPT TO: <$to>\r\n");
    fread($socket, 1024);
    fwrite($socket, "DATA\r\n");
    fread($socket, 1024);

    $headers = "From: $fromName <$from>\r\n";
    $headers .= "To: $to\r\n";
    $headers .= "Subject: $subject\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "\r\n";

    fwrite($socket, $headers . $body . "\r\n.\r\n");
    fread($socket, 1024);
    fwrite($socket, "QUIT\r\n");
    fclose($socket);
}
