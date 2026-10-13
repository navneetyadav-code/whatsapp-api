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

// 2. Message extraction (supports both your standard 'message' and Meta Cloud API formats)
$message = '';
if (isset($data['message']) && is_string($data['message'])) {
    // Standard Hub format
    $message = trim($data['message']);
} elseif (isset($data['text']) && is_array($data['text']) && isset($data['text']['body'])) {
    // Meta Cloud API text format: {"type": "text", "text": {"body": "..."}}
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
    $caption = trim((string) ($data['image']['caption'] ?? ''));
    $link = trim((string) ($data['image']['link'] ?? ''));
    $message = $caption !== '' ? $caption : $link;
} elseif (isset($data['document']) && is_array($data['document'])) {
    $caption = trim((string) ($data['document']['caption'] ?? ''));
    $link = trim((string) ($data['document']['link'] ?? ''));
    $message = $caption !== '' ? $caption : $link;
} elseif (isset($data['video']) && is_array($data['video'])) {
    $caption = trim((string) ($data['video']['caption'] ?? ''));
    $link = trim((string) ($data['video']['link'] ?? ''));
    $message = $caption !== '' ? $caption : $link;
} elseif (isset($data['body']) && is_string($data['body'])) {
    $message = trim($data['body']);
}

if ($to === '' || $message === '') {
    $requestId = log_api_request((int) $user['user_id'], (int) $user['id'], 'send', 422, 'Missing fields.');
    json_response([
        'ok' => false,
        'error' => 'Fields "to" and "message" (or Meta "text.body") are required.',
        'request_id' => $requestId,
        'meta_error' => [
            'message' => 'Invalid parameter: "to" and "text.body" or "message" are required.',
            'type' => 'OAuthException',
            'code' => 100,
        ],
    ], 422);
}

$payload = [
    'source' => !empty($data['messaging_product']) ? 'meta_compat_api' : 'api',
    'type' => 'text',
];
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
