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
    $remoteAddr = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    $isDirectLoopback = in_array($remoteAddr, ['127.0.0.1', '::1'], true);

    // If forwarded via reverse proxy, verify the originating client was also genuinely local
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $forwardedIps = explode(',', (string) $_SERVER['HTTP_X_FORWARDED_FOR']);
        $clientIp = trim($forwardedIps[0]);
        if (!in_array($clientIp, ['127.0.0.1', '::1'], true) && !str_starts_with($clientIp, '192.168.') && !str_starts_with($clientIp, '10.')) {
            return false;
        }
    }

    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $cfIp = trim((string) $_SERVER['HTTP_CF_CONNECTING_IP']);
        if (!in_array($cfIp, ['127.0.0.1', '::1'], true) && !str_starts_with($cfIp, '192.168.') && !str_starts_with($cfIp, '10.')) {
            return false;
        }
    }

    return $isDirectLoopback || str_starts_with($remoteAddr, '192.168.') || str_starts_with($remoteAddr, '10.');
}

function is_private_ip(string $ip): bool
{
    $ip = trim($ip, '[]');

    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return true;
    }

    // Handle IPv4-mapped IPv6 (e.g. ::ffff:127.0.0.1)
    if (str_starts_with(strtolower($ip), '::ffff:')) {
        $v4 = substr($ip, 7);
        if (filter_var($v4, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return is_private_ip($v4);
        }
    }

    if (in_array($ip, ['127.0.0.1', '::1', '0.0.0.0', '::'], true)) {
        return true;
    }

    // Check standard private / reserved ranges
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
        return true;
    }

    // Additional check for CGNAT (100.64.0.0/10) and benchmarking (198.18.0.0/15)
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        $long = ip2long($ip);
        if ($long !== false) {
            // 100.64.0.0/10 (Carrier-Grade NAT / Shared Address Space)
            if (($long & 0xFFC00000) === 0x64400000) {
                return true;
            }
            // 198.18.0.0/15 (Benchmarking)
            if (($long & 0xFFFE0000) === 0xC6120000) {
                return true;
            }
            // 0.0.0.0/8
            if (($long & 0xFF000000) === 0) {
                return true;
            }
        }
    }

    return false;
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
            $defaults['title'] = 'Login | WhatsGateway';
            $defaults['description'] = 'Sign in to your self-hosted WhatsApp API Dashboard to manage messaging campaigns, webhooks, and API keys.';
            break;
        case 'register':
            $defaults['title'] = 'Create Free Account | WhatsGateway';
            $defaults['description'] = 'Get started with self-hosted WhatsApp Web automation, multi-tenant message queues, and REST API endpoints.';
            break;
        case 'docs':
            $defaults['title'] = 'REST API Documentation & Code Examples | Free WhatsApp API';
            $defaults['description'] = 'Complete REST API documentation for cURL, Python, Node.js, PHP, and Go. Send WhatsApp messages, manage delivery queues, and configure webhooks.';
            break;
        case 'message':
            $defaults['title'] = 'Send WhatsApp Message | WhatsGateway';
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

/**
 * Supported Media Extensions & MIME Map for WhatsApp Messaging
 */
function get_allowed_media_types(): array
{
    return [
        // Images
        'jpg'  => ['type' => 'image', 'mime' => 'image/jpeg'],
        'jpeg' => ['type' => 'image', 'mime' => 'image/jpeg'],
        'png'  => ['type' => 'image', 'mime' => 'image/png'],
        'gif'  => ['type' => 'image', 'mime' => 'image/gif'],
        'webp' => ['type' => 'image', 'mime' => 'image/webp'],

        // Documents
        'pdf'  => ['type' => 'document', 'mime' => 'application/pdf'],
        'doc'  => ['type' => 'document', 'mime' => 'application/msword'],
        'docx' => ['type' => 'document', 'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'xls'  => ['type' => 'document', 'mime' => 'application/vnd.ms-excel'],
        'xlsx' => ['type' => 'document', 'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        'ppt'  => ['type' => 'document', 'mime' => 'application/vnd.ms-powerpoint'],
        'pptx' => ['type' => 'document', 'mime' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation'],
        'txt'  => ['type' => 'document', 'mime' => 'text/plain'],
        'csv'  => ['type' => 'document', 'mime' => 'text/csv'],
        'rtf'  => ['type' => 'document', 'mime' => 'application/rtf'],
        'zip'  => ['type' => 'document', 'mime' => 'application/zip'],

        // Videos
        'mp4'  => ['type' => 'video', 'mime' => 'video/mp4'],
        'mov'  => ['type' => 'video', 'mime' => 'video/quicktime'],
        '3gp'  => ['type' => 'video', 'mime' => 'video/3gpp'],

        // Audio
        'mp3'  => ['type' => 'audio', 'mime' => 'audio/mpeg'],
        'ogg'  => ['type' => 'audio', 'mime' => 'audio/ogg'],
        'wav'  => ['type' => 'audio', 'mime' => 'audio/wav'],
        'm4a'  => ['type' => 'audio', 'mime' => 'audio/mp4'],
        'aac'  => ['type' => 'audio', 'mime' => 'audio/aac'],
    ];
}

/**
 * SSRF Protection: Validate remote media URL and resolve IP safely
 */
function assert_safe_media_url(string $url): string
{
    $url = trim($url);
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        throw new InvalidArgumentException('Invalid media URL provided.');
    }

    $parts = parse_url($url);
    $scheme = strtolower((string) ($parts['scheme'] ?? ''));
    $host = strtolower((string) ($parts['host'] ?? ''));

    if (!in_array($scheme, ['http', 'https'], true)) {
        throw new InvalidArgumentException('Media URL must use HTTP or HTTPS.');
    }

    $cleanHost = trim($host, '[]');
    if ($cleanHost === '' || in_array($cleanHost, ['localhost', '127.0.0.1', '::1', '0.0.0.0'], true) || str_ends_with($cleanHost, '.local')) {
        throw new InvalidArgumentException('Media URL cannot point to localhost or loopback.');
    }

    $addresses = [];
    if (filter_var($cleanHost, FILTER_VALIDATE_IP)) {
        $addresses[] = $cleanHost;
    } else {
        $records = dns_get_record($cleanHost, DNS_A | DNS_AAAA) ?: [];
        foreach ($records as $r) {
            $addr = $r['ip'] ?? $r['ipv6'] ?? null;
            if ($addr) {
                $addresses[] = $addr;
            }
        }
    }

    if (empty($addresses)) {
        throw new InvalidArgumentException('Could not resolve hostname for media URL.');
    }

    foreach ($addresses as $ip) {
        if (is_private_ip($ip)) {
            throw new InvalidArgumentException('Media URL cannot resolve to a private or reserved network IP address.');
        }
    }

    return $url;
}

/**
 * Securely download remote media with timeouts, size bounds, and magic-byte inspection
 */
function download_remote_media(string $url, int $maxBytes = 20971520): array
{
    $uploadDir = dirname(__DIR__, 2) . '/public/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $currentUrl = $url;
    $maxRedirects = 3;
    $redirectsFollowed = 0;
    $tmpPath = $uploadDir . 'tmp_' . bin2hex(random_bytes(12)) . '.bin';
    $contentType = '';

    while ($redirectsFollowed <= $maxRedirects) {
        $safeUrl = assert_safe_media_url($currentUrl);

        $fp = fopen($tmpPath, 'w+b');
        if (!$fp) {
            throw new RuntimeException('Unable to create temporary file for media download.');
        }

        $ch = curl_init($safeUrl);
        curl_setopt_array($ch, [
            CURLOPT_FILE => $fp,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_CONNECTTIMEOUT => 7,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'WhatsApp-API-Hub-MediaDownloader/1.0',
            CURLOPT_NOPROGRESS => false,
            CURLOPT_PROGRESSFUNCTION => static function ($resource, $downloadSize, $downloaded) use ($maxBytes): int {
                if ($downloaded > $maxBytes || $downloadSize > $maxBytes) {
                    return 1; // Abort download immediately if exceeded max bytes
                }
                return 0;
            },
        ]);

        $success = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $redirectUrl = (string) curl_getinfo($ch, CURLINFO_REDIRECT_URL);
        $contentType = (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $curlError = curl_error($ch);
        $primaryIp = (string) curl_getinfo($ch, CURLINFO_PRIMARY_IP);
        curl_close($ch);
        fclose($fp);

        if ($primaryIp !== '' && is_private_ip($primaryIp)) {
            if (file_exists($tmpPath)) {
                unlink($tmpPath);
            }
            throw new RuntimeException('Media URL resolved to a private or reserved network IP address during connection.');
        }

        if (in_array($httpCode, [301, 302, 303, 307, 308], true)) {
            if (file_exists($tmpPath)) {
                unlink($tmpPath);
            }
            if ($redirectUrl === '') {
                throw new RuntimeException('Received redirect without Location header.');
            }
            if (!filter_var($redirectUrl, FILTER_VALIDATE_URL)) {
                $baseParts = parse_url($currentUrl);
                $scheme = $baseParts['scheme'] ?? 'https';
                $host = $baseParts['host'] ?? '';
                $port = !empty($baseParts['port']) ? ':' . $baseParts['port'] : '';
                $redirectUrl = $scheme . '://' . $host . $port . '/' . ltrim($redirectUrl, '/');
            }
            $currentUrl = $redirectUrl;
            $redirectsFollowed++;
            continue;
        }

        if (!$success || $httpCode < 200 || $httpCode >= 300) {
            if (file_exists($tmpPath)) {
                unlink($tmpPath);
            }
            throw new RuntimeException('Failed to download media from URL: ' . ($curlError ?: "HTTP {$httpCode}"));
        }

        break;
    }

    if ($redirectsFollowed > $maxRedirects) {
        if (file_exists($tmpPath)) {
            unlink($tmpPath);
        }
        throw new RuntimeException('Too many redirects encountered while downloading media.');
    }

    $fileSize = (int) (file_exists($tmpPath) ? filesize($tmpPath) : 0);
    if ($fileSize <= 0 || $fileSize > $maxBytes) {
        if (file_exists($tmpPath)) {
            unlink($tmpPath);
        }
        throw new RuntimeException('Media file size invalid or exceeds limit (' . round($maxBytes / 1048576) . 'MB max).');
    }

    // Inspect real MIME type via finfo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $realMime = finfo_file($finfo, $tmpPath) ?: 'application/octet-stream';
    finfo_close($finfo);

    if ($realMime === 'application/octet-stream' && $contentType !== '') {
        $realMime = explode(';', $contentType)[0];
    }

    $allowedMap = get_allowed_media_types();
    $matchedExt = null;
    $mediaType = 'document';

    foreach ($allowedMap as $ext => $info) {
        if (strcasecmp($info['mime'], $realMime) === 0) {
            $matchedExt = $ext;
            $mediaType = $info['type'];
            break;
        }
    }

    if (!$matchedExt) {
        $urlPath = parse_url($safeUrl, PHP_URL_PATH) ?? '';
        $pathExt = strtolower(pathinfo($urlPath, PATHINFO_EXTENSION));
        if (isset($allowedMap[$pathExt])) {
            $matchedExt = $pathExt;
            $mediaType = $allowedMap[$pathExt]['type'];
        }
    }

    if (!$matchedExt) {
        if (file_exists($tmpPath)) {
            unlink($tmpPath);
        }
        throw new RuntimeException("Unsupported media type ({$realMime}). Allowed: JPG, PNG, WEBP, GIF, PDF, MP4, MP3, DOCX, etc.");
    }

    $finalFilename = 'media_' . bin2hex(random_bytes(16)) . '.' . $matchedExt;
    $finalPath = $uploadDir . $finalFilename;
    if (!rename($tmpPath, $finalPath)) {
        if (file_exists($tmpPath)) {
            unlink($tmpPath);
        }
        throw new RuntimeException('Failed to finalize downloaded media storage.');
    }

    $originalName = basename(parse_url($safeUrl, PHP_URL_PATH) ?: ('file.' . $matchedExt));

    return [
        'path' => $finalPath,
        'name' => $originalName,
        'type' => $mediaType,
        'mime' => $allowedMap[$matchedExt]['mime'] ?? $realMime,
        'size' => $fileSize,
        'is_temp' => true,
    ];
}

/**
 * Securely decode and store Base64 or Data URI media payloads
 */
function save_base64_media(string $base64Data, ?string $filename = null, ?string $declaredType = null, int $maxBytes = 20971520): array
{
    $base64Data = trim($base64Data);
    $detectedMime = null;

    if (preg_match('/^data:([^;]+);base64,(.+)$/s', $base64Data, $matches)) {
        $detectedMime = strtolower(trim($matches[1]));
        $base64Data = $matches[2];
    }

    $binary = base64_decode($base64Data, true);
    if ($binary === false || strlen($binary) === 0) {
        throw new InvalidArgumentException('Invalid Base64 encoded media payload.');
    }

    if (strlen($binary) > $maxBytes) {
        throw new InvalidArgumentException('Base64 media payload exceeds maximum allowed size (' . round($maxBytes / 1048576) . 'MB max).');
    }

    $uploadDir = dirname(__DIR__, 2) . '/public/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $tmpPath = $uploadDir . 'tmp_' . bin2hex(random_bytes(12)) . '.bin';
    file_put_contents($tmpPath, $binary);

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $realMime = finfo_file($finfo, $tmpPath) ?: ($detectedMime ?: 'application/octet-stream');
    finfo_close($finfo);

    $allowedMap = get_allowed_media_types();
    $matchedExt = null;
    $mediaType = 'document';

    foreach ($allowedMap as $ext => $info) {
        if (strcasecmp($info['mime'], $realMime) === 0) {
            $matchedExt = $ext;
            $mediaType = $info['type'];
            break;
        }
    }

    if (!$matchedExt && $filename) {
        $pathExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (isset($allowedMap[$pathExt])) {
            $matchedExt = $pathExt;
            $mediaType = $allowedMap[$pathExt]['type'];
        }
    }

    if (!$matchedExt && $declaredType && isset($allowedMap[$declaredType])) {
        $matchedExt = $declaredType;
        $mediaType = $allowedMap[$declaredType]['type'];
    }

    if (!$matchedExt) {
        if (file_exists($tmpPath)) {
            unlink($tmpPath);
        }
        throw new InvalidArgumentException("Unsupported media type ({$realMime}).");
    }

    $finalFilename = 'media_' . bin2hex(random_bytes(16)) . '.' . $matchedExt;
    $finalPath = $uploadDir . $finalFilename;
    if (!rename($tmpPath, $finalPath)) {
        if (file_exists($tmpPath)) {
            unlink($tmpPath);
        }
        throw new RuntimeException('Failed to finalize base64 media storage.');
    }

    $originalName = $filename ? basename($filename) : ('file_' . time() . '.' . $matchedExt);

    return [
        'path' => $finalPath,
        'name' => $originalName,
        'type' => $mediaType,
        'mime' => $allowedMap[$matchedExt]['mime'] ?? $realMime,
        'size' => strlen($binary),
        'is_temp' => true,
    ];
}

/**
 * Securely store uploaded files from multipart/form-data with verification
 */
function save_uploaded_media(array $fileInfo, ?string $declaredType = null, int $maxBytes = 20971520): array
{
    if (!isset($fileInfo['error']) || $fileInfo['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize in php.ini.',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds form MAX_FILE_SIZE.',
            UPLOAD_ERR_PARTIAL => 'File upload was incomplete.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder on server.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'Upload blocked by PHP extension.'
        ];
        throw new RuntimeException($uploadErrors[$fileInfo['error']] ?? 'File upload failed.');
    }

    if ($fileInfo['size'] > $maxBytes) {
        throw new RuntimeException('File exceeds maximum allowed size (' . round($maxBytes / 1048576) . 'MB max).');
    }

    $originalName = basename((string) ($fileInfo['name'] ?? 'file'));
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    $allowedMap = get_allowed_media_types();
    if (!isset($allowedMap[$ext])) {
        throw new RuntimeException("Unsupported file extension (.{$ext}). Allowed: JPG, PNG, WEBP, GIF, PDF, MP4, MP3, DOCX, etc.");
    }

    // Verify real MIME via finfo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $realMime = finfo_file($finfo, $fileInfo['tmp_name']) ?: ($fileInfo['type'] ?? 'application/octet-stream');
    finfo_close($finfo);

    $disallowedMimes = ['application/x-php', 'application/x-httpd-php', 'text/x-php', 'application/x-sh', 'application/x-executable'];
    if (in_array(strtolower($realMime), $disallowedMimes, true)) {
        throw new RuntimeException('Executable file uploads are strictly forbidden.');
    }

    $uploadDir = dirname(__DIR__, 2) . '/public/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $finalFilename = 'media_' . bin2hex(random_bytes(16)) . '.' . $ext;
    $target = $uploadDir . $finalFilename;

    $stored = is_uploaded_file($fileInfo['tmp_name'])
        ? move_uploaded_file($fileInfo['tmp_name'], $target)
        : (file_exists($fileInfo['tmp_name']) && copy($fileInfo['tmp_name'], $target));

    if (!$stored) {
        throw new RuntimeException('Failed to store uploaded file on server.');
    }

    return [
        'path' => $target,
        'name' => $originalName,
        'type' => $allowedMap[$ext]['type'] ?? ($declaredType ?: 'document'),
        'mime' => $allowedMap[$ext]['mime'] ?? $realMime,
        'size' => $fileInfo['size'],
        'is_temp' => true,
    ];
}

/**
 * Universal media detector: parses multipart uploads, Meta Cloud API objects, and Hub flat JSON
 */
function parse_api_media_payload(array $data, array $files = []): ?array
{
    // 1. Multipart file upload in $_FILES
    $fileKeys = ['file', 'attachment', 'image', 'document', 'video', 'audio', 'media'];
    foreach ($fileKeys as $k) {
        if (!empty($files[$k]['name']) && isset($files[$k]['error']) && $files[$k]['error'] === UPLOAD_ERR_OK) {
            $declaredType = in_array($k, ['image', 'document', 'video', 'audio'], true) ? $k : null;
            return save_uploaded_media($files[$k], $declaredType);
        }
    }

    // 2. Meta WhatsApp Cloud API format:
    // e.g. {"type": "image", "image": {"link": "...", "caption": "..."}}
    $metaType = strtolower((string) ($data['type'] ?? ''));
    if (in_array($metaType, ['image', 'document', 'video', 'audio'], true) && !empty($data[$metaType]) && is_array($data[$metaType])) {
        $metaObj = $data[$metaType];
        $link = trim((string) ($metaObj['link'] ?? $metaObj['url'] ?? ''));
        $base64 = trim((string) ($metaObj['base64'] ?? $metaObj['data'] ?? ''));
        $filename = trim((string) ($metaObj['filename'] ?? $metaObj['name'] ?? ''));

        if ($link !== '') {
            $attachment = download_remote_media($link);
            if ($filename !== '') {
                $attachment['name'] = $filename;
            }
            $attachment['type'] = $metaType;
            return $attachment;
        }

        if ($base64 !== '') {
            return save_base64_media($base64, $filename, $metaType);
        }
    }

    // Check if type field was omitted but image/document/video/audio objects exist in root
    foreach (['image', 'document', 'video', 'audio'] as $t) {
        if (!empty($data[$t]) && is_array($data[$t])) {
            $link = trim((string) ($data[$t]['link'] ?? $data[$t]['url'] ?? ''));
            $base64 = trim((string) ($data[$t]['base64'] ?? $data[$t]['data'] ?? ''));
            $filename = trim((string) ($data[$t]['filename'] ?? $data[$t]['name'] ?? ''));
            if ($link !== '') {
                $attachment = download_remote_media($link);
                if ($filename !== '') {
                    $attachment['name'] = $filename;
                }
                $attachment['type'] = $t;
                return $attachment;
            }
            if ($base64 !== '') {
                return save_base64_media($base64, $filename, $t);
            }
        }
    }

    // 3. Standard Hub flat JSON:
    // media_url, url, media, file_url, attachment_url
    $url = trim((string) ($data['media_url'] ?? $data['url'] ?? $data['media'] ?? $data['file_url'] ?? $data['attachment_url'] ?? ''));
    if ($url !== '' && filter_var($url, FILTER_VALIDATE_URL)) {
        $attachment = download_remote_media($url);
        if (!empty($data['filename'])) {
            $attachment['name'] = trim((string) $data['filename']);
        }
        if (!empty($data['type']) && in_array(strtolower((string) $data['type']), ['image', 'document', 'video', 'audio'], true)) {
            $attachment['type'] = strtolower((string) $data['type']);
        }
        return $attachment;
    }

    // 4. Base64 in flat JSON
    $base64 = trim((string) ($data['base64'] ?? $data['file_base64'] ?? ''));
    if ($base64 !== '') {
        $fn = !empty($data['filename']) ? trim((string) $data['filename']) : null;
        $t = !empty($data['type']) ? strtolower(trim((string) $data['type'])) : null;
        return save_base64_media($base64, $fn, $t);
    }

    return null;
}

