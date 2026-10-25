<?php

declare(strict_types=1);

/**
 * WhatsGateway — Developer Interactive API Tester
 *
 * Standalone utility to test message delivery, headers, and media payloads
 * against local, staging, or production WhatsGateway endpoints.
 */

require_once dirname(__DIR__) . '/src/bootstrap.php';

// Security: Restrict unauthenticated remote access to developer test console (SEC-03)
$isLocal = is_local_request();
if (!$isLocal) {
    require_auth();
}

$defaultApiUrl = base_url('api/send.php');
$apiUrl = !empty($_POST['api_url']) ? trim((string) $_POST['api_url']) : $defaultApiUrl;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$isLocal) {
        verify_csrf();
    }

    $to = trim((string) ($_POST['phone'] ?? ''));
    $message = trim((string) ($_POST['message'] ?? ''));
    $apiKey = trim((string) ($_POST['api_key'] ?? ''));
    $mediaUrl = trim((string) ($_POST['media_url'] ?? ''));
    $humanize = !empty($_POST['humanize']);

    // SSRF Protection: Validate target API URL and media URL
    $targetHost = strtolower((string) (parse_url($apiUrl, PHP_URL_HOST) ?? ''));
    $cleanTargetHost = trim($targetHost, '[]');
    $targetScheme = strtolower((string) (parse_url($apiUrl, PHP_URL_SCHEME) ?? ''));

    $isDisallowed = !filter_var($apiUrl, FILTER_VALIDATE_URL)
        || !in_array($targetScheme, ['http', 'https'], true)
        || $cleanTargetHost === ''
        || str_starts_with($cleanTargetHost, '169.254.')
        || $cleanTargetHost === 'metadata.google.internal'
        || $cleanTargetHost === '100.100.100.200'
        || (!$isLocal && (in_array($cleanTargetHost, ['localhost', '127.0.0.1', '::1', '0.0.0.0'], true) || is_private_ip(gethostbyname($cleanTargetHost))));

    if ($isDisallowed) {
        $result = [
            'code' => 400,
            'response' => ['ok' => false, 'error' => 'API endpoint is restricted or invalid.'],
        ];
    } else {
        if ($mediaUrl !== '') {
            try {
                assert_safe_media_url($mediaUrl);
            } catch (Throwable $e) {
                $result = [
                    'code' => 400,
                    'response' => ['ok' => false, 'error' => 'Invalid media URL: ' . $e->getMessage()],
                ];
            }
        }

        if (!isset($result)) {
            $bodyData = [
                'to' => $to,
                'message' => $message,
                'humanize' => $humanize,
            ];

            if ($mediaUrl !== '') {
                $bodyData['media_url'] = $mediaUrl;
            }

            $payload = json_encode($bodyData, JSON_UNESCAPED_SLASHES);

            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
                'X-Api-Key: ' . $apiKey,
                'ngrok-skip-browser-warning: true',
            ]);

            $response = curl_exec($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $primaryIp = (string) curl_getinfo($ch, CURLINFO_PRIMARY_IP);
            $curlError = curl_error($ch);
            curl_close($ch);

            if (!$isLocal && $primaryIp !== '' && is_private_ip($primaryIp)) {
                $result = [
                    'code' => 400,
                    'response' => ['ok' => false, 'error' => 'API endpoint resolved to a restricted IP address.'],
                ];
            } else {
                $result = [
                    'code' => $httpCode ?: 500,
                    'response' => json_decode((string) $response, true) ?: ($curlError ?: $response),
                ];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, interactive-widget=resizes-content">
    <title>WhatsApp API Tester — Developer Utility</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: #f8fafc; color: #334155; padding: 1.5rem 1rem; max-width: 640px; margin: 0 auto; }
        .card { background: white; padding: 1.75rem 1.5rem; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1); border: 1px solid #e2e8f0; }
        h2 { margin-top: 0; color: #0f172a; font-size: 1.5rem; }
        .form-group { margin-bottom: 1.25rem; }
        label { display: block; font-weight: 600; font-size: 14px; margin-bottom: 0.5rem; color: #475569; }
        input[type="text"], input[type="tel"], input[type="url"], textarea { width: 100%; box-sizing: border-box; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px; font-family: inherit; font-size: 15px; }
        input:focus, textarea:focus { outline: none; border-color: #059669; box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.15); }
        .checkbox-group { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.25rem; font-size: 14px; color: #475569; font-weight: 500; }
        .checkbox-group input { width: 16px; height: 16px; accent-color: #059669; cursor: pointer; }
        button { background: #059669; color: white; border: none; padding: 0.85rem 1.5rem; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 15px; width: 100%; transition: background 0.2s; }
        button:hover { background: #047857; }
        .result { margin-top: 1.5rem; padding: 1rem; background: #f1f5f9; border-radius: 6px; border: 1px solid #e2e8f0; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 13px; white-space: pre-wrap; word-break: break-all; }
        .success { border-color: #10b981; background: #ecfdf5; color: #065f46; }
        .error { border-color: #ef4444; background: #fef2f2; color: #991b1b; }
        .helper-text { font-size: 12px; color: #64748b; margin-top: 0.35rem; }
    </style>
</head>
<body>
    <div class="card">
        <h2>WhatsApp API Tester</h2>
        <p style="font-size: 14px; color: #64748b; margin-bottom: 1.5rem;">Interactive test console for sending WhatsApp messages and media through WhatsGateway.</p>
        
        <form method="POST" autocomplete="off" data-form-type="other">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <div class="form-group">
                <label>API Endpoint URL</label>
                <input type="text" name="api_url" placeholder="http://localhost/wa/public/api/send.php" required value="<?= htmlspecialchars($apiUrl) ?>">
                <div class="helper-text">Accepts local XAMPP URL or public cloud endpoint.</div>
            </div>
            <div class="form-group">
                <label>API Key (Bearer / X-API-Key)</label>
                <input type="text" name="api_key" placeholder="wa_live_..." required autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" data-form-type="other" data-lpignore="true" value="<?= htmlspecialchars($_POST['api_key'] ?? '') ?>">
                <div class="helper-text">Generate your API Key from the dashboard under <strong>API Keys</strong>.</div>
            </div>
            <div class="form-group">
                <label>Recipient Phone Number (with Country Code)</label>
                <input type="tel" inputmode="tel" name="phone" placeholder="919876543210" required autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" data-form-type="other" data-lpignore="true" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                <div class="helper-text">Full international number (e.g. 919876543210 or 15551234567) or 10-digit Indian number.</div>
            </div>
            <div class="form-group">
                <label>Message / Caption</label>
                <textarea name="message" rows="3" placeholder="Type test message text... (supports *bold*, _italic_, ~strikethrough~)" autocomplete="off" autocorrect="off" autocapitalize="sentences" spellcheck="false" data-form-type="other" data-lpignore="true"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label>Media URL (Optional)</label>
                <input type="url" name="media_url" placeholder="https://example.com/invoice.pdf or image.jpg" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" value="<?= htmlspecialchars($_POST['media_url'] ?? '') ?>">
                <div class="helper-text">Public HTTPS direct download link for PDF, Image, Video, or Audio.</div>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" id="humanize" name="humanize" value="1" <?= (!isset($_POST['phone']) || !empty($_POST['humanize'])) ? 'checked' : '' ?>>
                <label for="humanize" style="margin-bottom:0; cursor:pointer;">Simulate human typing delay (Humanized Anti-Ban Protection)</label>
            </div>
            <button type="submit">Dispatch Test Message</button>
        </form>

        <?php if (isset($result)): ?>
            <?php $isSuccess = $result['code'] >= 200 && $result['code'] < 300; ?>
            <div class="result <?= $isSuccess ? 'success' : 'error' ?>">
<strong>HTTP <?= $result['code'] ?></strong>
<?= htmlspecialchars(print_r($result['response'], true)) ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
