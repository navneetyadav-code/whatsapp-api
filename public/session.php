<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

$user = require_auth();
$action = $_POST['action'] ?? $_GET['action'] ?? 'status';

$stateChangingActions = ['start', 'logout', 'sleep', 'wake', 'send-test'];
if (in_array($action, $stateChangingActions, true)) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['ok' => false, 'error' => 'Method not allowed. Use POST.'], 405);
    }
    verify_csrf();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
}

$userId = (int) $user['id'];

if ($action === 'start') {
    $allowNewDevices = (bool) app_config('app.allow_new_devices');

    if (!$allowNewDevices && !is_owner($user)) {
        json_response([
            'ok' => false,
            'error' => (string) (app_config('app.restricted_notice') ?: 'New device linking is disabled on this instance.'),
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

if ($action === 'pair-code') {
    $allowNewDevices = (bool) app_config('app.allow_new_devices');

    if (!$allowNewDevices && !is_owner($user)) {
        json_response([
            'ok' => false,
            'error' => (string) (app_config('app.restricted_notice') ?: 'New device linking is disabled on this instance.'),
        ], 403);
    }

    $input = request_json();
    $phone = trim((string) ($input['phone'] ?? $input['phoneNumber'] ?? $_POST['phone'] ?? ''));
    $cleanPhone = preg_replace('/\D+/', '', $phone);

    if (strlen($cleanPhone) < 8 || strlen($cleanPhone) > 15) {
        json_response([
            'ok' => false,
            'error' => 'Please provide a valid phone number with country code (8 to 15 digits).',
        ], 422);
    }

    sync_session_status($userId, [
        'state' => 'connecting',
        'sleeping' => false,
        'error' => null,
    ]);

    $response = worker_request('POST', "sessions/{$userId}/pair-code", ['phone' => $cleanPhone], 30);
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

if ($action === 'status') {
    $response = worker_request('GET', "sessions/{$userId}/status");
    if (!empty($response['ok']) && isset($response['session'])) {
        sync_session_status($userId, $response['session']);
    }

    $pdo = Database::pdo();
    $stmt = $pdo->prepare('SELECT status, phone, push_name, updated_at FROM whatsapp_sessions WHERE user_id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $dbSession = $stmt->fetch() ?: [];

    json_response([
        'ok' => !empty($response['ok']),
        'session' => array_merge($dbSession, $response['session'] ?? []),
    ]);
}

if ($action === 'queue-status') {
    $pdo = Database::pdo();
    $page = max(1, (int) ($_GET['p'] ?? 1));
    $perPage = 20;

    $stats = message_stats($userId);
    $totalMessages = count_messages($userId);
    $totalPages = max(1, (int) ceil($totalMessages / $perPage));
    $queueMessages = paginate_messages($userId, $page, $perPage);

    json_response([
        'ok' => true,
        'stats' => $stats,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $perPage,
            'total_messages' => $totalMessages,
            'total_pages' => $totalPages,
        ],
        'messages' => $queueMessages,
    ]);
}

json_response(['ok' => false, 'error' => 'Invalid action.'], 400);
