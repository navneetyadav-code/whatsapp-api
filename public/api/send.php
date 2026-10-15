<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'Use POST.'], 405);
}

$user = authenticate_api_request();
if (!$user) {
    json_response([
        'ok' => false,
        'error' => 'Invalid API key or access token.',
        'meta_error' => [
            'message' => 'Invalid OAuth access token or API key.',
            'type' => 'OAuthException',
            'code' => 190,
        ],
    ], 401);
}

$rate = api_key_rate_status($user);
if (!$rate['allowed']) {
    $requestId = log_api_request((int) $user['user_id'], (int) $user['id'], 'send', 429, 'Rate limit exceeded.');
    json_response([
        'ok' => false,
        'error' => 'Rate limit exceeded.',
        'request_id' => $requestId,
        'rate' => $rate,
        'meta_error' => [
            'message' => 'Rate limit hit.',
            'type' => 'OAuthException',
            'code' => 80007,
        ],
    ], 429);
}

$data = request_json();

// 1. Recipient extraction (supports standard 'to' / 'recipient' or Meta contacts)
$rawTo = trim((string) ($data['to'] ?? $data['recipient'] ?? ''));
if ($rawTo === '' && !empty($data['contacts'][0]['wa_id'])) {
    $rawTo = trim((string) $data['contacts'][0]['wa_id']);
}
$to = normalize_phone($rawTo);

// 2. Media ingestion & validation (multipart uploads, remote URLs, or Base64)
$attachment = null;
$mediaError = null;
try {
    $attachment = parse_api_media_payload($data, $_FILES ?? []);
} catch (Throwable $e) {
    $mediaError = $e->getMessage();
}

if ($mediaError !== null) {
    $requestId = log_api_request((int) $user['user_id'], (int) $user['id'], 'send', 422, $mediaError);
    json_response([
        'ok' => false,
        'error' => $mediaError,
        'request_id' => $requestId,
        'meta_error' => [
            'message' => $mediaError,
            'type' => 'OAuthException',
            'code' => 100,
        ],
    ], 422);
}

// 3. Message / Caption extraction (supports Hub message/caption and Meta formats)
$message = '';
if (isset($data['caption']) && is_string($data['caption'])) {
    $message = trim($data['caption']);
} elseif (isset($data['message']) && is_string($data['message'])) {
    $message = trim($data['message']);
} elseif (isset($data['text']) && is_array($data['text']) && isset($data['text']['body'])) {
    // Meta Cloud API text format
    $message = trim((string) $data['text']['body']);
} elseif (isset($data['template']) && is_array($data['template'])) {
    // Meta Cloud API template format
    $templateName = $data['template']['name'] ?? 'template';
    $params = [];
    if (!empty($data['template']['components']) && is_array($data['template']['components'])) {
        foreach ($data['template']['components'] as $component) {
            if (!empty($component['parameters']) && is_array($component['parameters'])) {
                foreach ($component['parameters'] as $param) {
                    if (isset($param['text'])) {
                        $params[] = (string) $param['text'];
                    }
                }
            }
        }
    }
    $message = !empty($params) ? implode(' ', $params) : "[$templateName]";
} elseif (isset($data['image']) && is_array($data['image'])) {
    $message = trim((string) ($data['image']['caption'] ?? ''));
} elseif (isset($data['document']) && is_array($data['document'])) {
    $message = trim((string) ($data['document']['caption'] ?? ''));
} elseif (isset($data['video']) && is_array($data['video'])) {
    $message = trim((string) ($data['video']['caption'] ?? ''));
} elseif (isset($data['body']) && is_string($data['body'])) {
    $message = trim($data['body']);
}

// 4. Validate requirements: Recipient is required AND at least one of (message or media) must be provided
if ($to === '' || ($message === '' && $attachment === null)) {
    $requestId = log_api_request((int) $user['user_id'], (int) $user['id'], 'send', 422, 'Missing fields.');
    json_response([
        'ok' => false,
        'error' => 'Field "to" and either a "message" or media attachment are required.',
        'request_id' => $requestId,
        'meta_error' => [
            'message' => 'Invalid parameter: "to" and either "text.body" or media object are required.',
            'type' => 'OAuthException',
            'code' => 100,
        ],
    ], 422);
}

$payloadType = $attachment ? ($attachment['type'] ?? 'document') : 'text';
$payload = [
    'source' => !empty($data['messaging_product']) ? 'meta_compat_api' : 'api',
    'type' => $payloadType,
];

if ($attachment !== null) {
    $payload['attachment'] = $attachment;
    if (!empty($data['ptt']) || !empty($data['audio']['ptt'])) {
        $payload['ptt'] = true;
    }
}
if (!empty($data['humanize']) || !empty($user['allow_humanize'])) {
    $payload['humanize'] = true;
}
if (!empty($data['campaign_id'])) {
    $payload['campaign_id'] = (int) $data['campaign_id'];
}

$messageId = enqueue_message((int) $user['user_id'], (int) $user['id'], $to, $message, $payload);
$requestId = log_api_request((int) $user['user_id'], (int) $user['id'], 'send', 200);

$cleanTo = preg_replace('/\D/', '', $to);
$wamid = 'wamid.' . bin2hex(random_bytes(16));

json_response([
    // Standard Hub API fields
    'ok' => true,
    'status' => 'queued',
    'message_id' => $messageId,
    'request_id' => $requestId,
    'recipient' => $cleanTo,
    'type' => $payloadType,
    'has_media' => $attachment !== null,

    // Meta WhatsApp Cloud API compatibility fields
    'messaging_product' => 'whatsapp',
    'contacts' => [
        [
            'input' => $rawTo,
            'wa_id' => $cleanTo,
        ],
    ],
    'messages' => [
        [
            'id' => $wamid,
            'message_status' => 'accepted',
        ],
    ],
], 200);

