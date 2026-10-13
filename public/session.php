<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

$user = require_auth();
$action = $_GET['action'] ?? 'status';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
}

$userId = (int) $user['id'];

if ($action === 'start') {
    $allowNewDevices = (bool) app_config('app.allow_new_devices');

    if (!$allowNewDevices && !is_owner($user)) {
        json_response([
            'ok' => false,
            'error' => (string) app_config('app.restricted_notice'),
        ], 403);
    }

    sync_session_status($userId, [
        'state' => 'connecting',
        'sleeping' => false,
        'error' => null,
    ]);

    $response = worker_request('POST', "sessions/{$userId}/start");
    if (!empty($response['ok'])) {
        sync_session_status($userId, $response['session'] ?? []);
    }
    json_response($response, !empty($response['ok']) ? 200 : 502);
}

if ($action === 'logout') {
    $response = worker_request('POST', "sessions/{$userId}/logout");
    if (!empty($response['ok'])) {
        sync_session_status($userId, $response['session'] ?? ['state' => 'idle']);
    }
    json_response($response, !empty($response['ok']) ? 200 : 502);
}

if ($action === 'sleep') {
    $response = worker_request('POST', "sessions/{$userId}/sleep");
    if (!empty($response['ok'])) {
        sync_session_status($userId, $response['session'] ?? ['state' => 'idle']);
    }
    json_response($response, !empty($response['ok']) ? 200 : 502);
}

if ($action === 'wake') {
    $response = worker_request('POST', "sessions/{$userId}/wake");
    if (!empty($response['ok'])) {
        sync_session_status($userId, $response['session'] ?? []);
    }
    json_response($response, !empty($response['ok']) ? 200 : 502);
}

if ($action === 'send-test') {
    $data = request_json();
    $to = trim((string) ($data['to'] ?? ''));
    $message = trim((string) ($data['message'] ?? ''));

    if ($to === '' || $message === '') {
        json_response(['ok' => false, 'error' => 'Recipient and message are required.'], 422);
    }

    $messageId = enqueue_message($userId, null, $to, $message, [
        'source' => 'dashboard',
        'type' => 'text',
    ]);

    json_response(['ok' => true, 'status' => 'queued', 'message_id' => $messageId], 202);
}

if ($action === 'queue-status') {
    $page = max(1, (int) ($_GET['p'] ?? 1));
    $perPage = 20;
    $stats = message_stats($userId);
    $messages = paginate_messages($userId, $page, $perPage);
    $totalMessages = count_messages($userId);
    $totalPages = max(1, (int) ceil($totalMessages / $perPage));

    json_response([
        'ok' => true,
        'stats' => [
            'queued' => (int) ($stats['queued'] ?? 0),
            'processing' => (int) ($stats['processing'] ?? 0),
            'failed' => (int) ($stats['failed'] ?? 0),
            'sent' => (int) ($stats['sent'] ?? 0),
            'cancelled' => (int) ($stats['cancelled'] ?? 0),
        ],
        'page' => $page,
        'totalPages' => $totalPages,
        'totalMessages' => $totalMessages,
        'messages' => array_map(function ($m) {
            return [
                'id' => (int) $m['id'],
                'recipient' => (string) ($m['normalized_recipient'] ?: $m['recipient']),
                'status' => (string) $m['status'],
                'attempts' => (int) $m['attempts'],
                'max_attempts' => (int) $m['max_attempts'],
                'body' => (string) $m['body'],
                'error_message' => $m['error_message'] ? (string) $m['error_message'] : null,
                'created_at' => (string) $m['created_at'],
                'sent_at' => $m['sent_at'] ? (string) $m['sent_at'] : null,
            ];
        }, $messages),
        'csrf_token' => csrf_token(),
    ]);
}

$response = worker_request('GET', "sessions/{$userId}/status");
if (!empty($response['ok'])) {
    sync_session_status($userId, $response['session'] ?? []);
}
json_response($response, !empty($response['ok']) ? 200 : 502);
