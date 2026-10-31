<?php
/**
 * Assistgroup API Endpoint for InfinityFree Hosting
 * -------------------------------------------------
 * Upload this file to your InfinityFree website (e.g. htdocs/api.php)
 * It accepts GET and POST requests, validates an API key, and returns JSON.
 */

declare(strict_types=1);

// Set JSON headers
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-KEY');

// Handle preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit(0);
}

// 1. Secret API Key - Change this to any secret password you prefer!
$API_SECRET = 'ag_secret_infinity_key_2026';

// 2. Validate Authorization
$providedKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
if (!$providedKey && isset($_SERVER['HTTP_AUTHORIZATION'])) {
    if (preg_match('/Bearer\s+(\S+)/i', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
        $providedKey = $matches[1];
    }
}
if (!$providedKey && isset($_REQUEST['api_key'])) {
    $providedKey = (string)$_REQUEST['api_key'];
}

if ($providedKey !== $API_SECRET) {
    http_response_code(401);
    echo json_encode([
        'ok' => false,
        'error' => 'Unauthorized: Invalid or missing API key.',
        'hint' => 'Provide header X-API-KEY or Bearer token, or parameter ?api_key='
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit(0);
}

// 3. Process Request
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? 'status';

$rawInput = file_get_contents('php://input');
$jsonBody = json_decode($rawInput, true);
$payload = is_array($jsonBody) ? $jsonBody : $_POST;

switch ($action) {
    case 'status':
    case 'ping':
        echo json_encode([
            'ok' => true,
            'service' => 'Assistgroup Live API',
            'host' => $_SERVER['HTTP_HOST'] ?? 'assistgroup.gt.tc',
            'method' => $method,
            'server_time' => date('Y-m-d H:i:s'),
            'php_version' => PHP_VERSION,
            'message' => '🎉 Hello from Assistgroup on InfinityFree! The PHP bypass worked successfully!'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        break;

    case 'echo':
        echo json_encode([
            'ok' => true,
            'action' => 'echo',
            'method' => $method,
            'received_payload' => $payload,
            'received_get' => $_GET
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        break;

    case 'stats':
        echo json_encode([
            'ok' => true,
            'stats' => [
                'status' => 'operational',
                'active_modules' => ['auth', 'workspace', 'whatsapp_bridge'],
                'memory_usage' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB'
            ]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        break;

    default:
        echo json_encode([
            'ok' => true,
            'action' => $action,
            'method' => $method,
            'payload' => $payload,
            'message' => "Action '$action' processed successfully by Assistgroup."
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        break;
}
