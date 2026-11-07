<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if ($method === 'POST') {
    require __DIR__ . '/send.php';
    exit;
}

if ($method === 'GET') {
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

    $id = trim((string) ($_GET['id'] ?? ''));
    if ($id === '') {
        json_response(['ok' => false, 'error' => 'Parameter "id" is required.'], 400);
    }

    $pdo = db();
    $stmt = $pdo->prepare(
        'SELECT id, status, recipient, message_type, provider_message_id, attempts, sent_at, delivered_at, read_at, error_message, created_at
         FROM message_queue
         WHERE user_id = ? AND (id = ? OR provider_message_id = ?)
         LIMIT 1'
    );
    $stmt->execute([(int) $user['user_id'], $id, $id]);
    $msg = $stmt->fetch();

    if (!$msg) {
        json_response(['ok' => false, 'error' => 'Message not found.'], 404);
    }

    json_response([
        'ok' => true,
        'message_id' => (int) $msg['id'],
        'status' => $msg['status'],
        'recipient' => $msg['recipient'],
        'provider_message_id' => $msg['provider_message_id'],
        'attempts' => (int) $msg['attempts'],
        'sent_at' => $msg['sent_at'],
        'delivered_at' => $msg['delivered_at'],
        'read_at' => $msg['read_at'],
        'error_message' => $msg['error_message'],
        'created_at' => $msg['created_at'],
    ], 200);
}

json_response(['ok' => false, 'error' => 'Method not allowed.'], 405);
