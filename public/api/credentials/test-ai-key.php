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
$provider = trim((string) ($input['provider'] ?? ''));
$apiKey = trim((string) ($input['api_key'] ?? ''));

$pdo = Database::pdo();
$userId = (int) $user['id'];

// If ID is provided, look up saved key and its customized model
$keyModel = null;
if ($id !== null) {
    $stmt = $pdo->prepare('SELECT id, provider, api_key, label, model FROM ai_provider_keys WHERE id = ? AND user_id = ? LIMIT 1');
    $stmt->execute([$id, $userId]);
    $keyRow = $stmt->fetch();
    if ($keyRow) {
        $provider = (string) $keyRow['provider'];
        $keyModel = !empty($keyRow['model']) ? trim((string) $keyRow['model']) : null;
        if ($apiKey === '') {
            $apiKey = (string) $keyRow['api_key'];
        }
    }
}

if ($apiKey === '') {
    json_response(['ok' => false, 'error' => 'No API key provided to test.'], 422);
    exit;
}

if (!in_array($provider, ['gemini', 'groq', 'openrouter'], true)) {
    json_response(['ok' => false, 'error' => 'Invalid AI provider.'], 422);
    exit;
}

// Fetch user's configured models dynamically from backend database
$settingsStmt = $pdo->prepare('SELECT gemini_model, groq_model, openrouter_model FROM ai_agent_settings WHERE user_id = ? LIMIT 1');
$settingsStmt->execute([$userId]);
$aiSettings = $settingsStmt->fetch(PDO::FETCH_ASSOC) ?: [];

$model = trim((string) ($input['model'] ?? ''));
if ($model === '' && $keyModel !== null) {
    $model = $keyModel;
}
if ($model === '') {
    if ($provider === 'gemini') {
        $model = trim((string) ($aiSettings['gemini_model'] ?? ''));
    } elseif ($provider === 'groq') {
        $model = trim((string) ($aiSettings['groq_model'] ?? ''));
    } elseif ($provider === 'openrouter') {
        $model = trim((string) ($aiSettings['openrouter_model'] ?? ''));
    }
}

if ($model === '') {
    json_response([
        'ok' => false,
        'error' => "No {$provider} model configured in your backend AI settings. Please configure your model in AI Agent Studio first."
    ], 422);
    exit;
}

$startTime = microtime(true);
$httpCode = 0;
$responseBody = '';
$curlError = '';

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_CONNECTTIMEOUT => 7,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
]);

if ($provider === 'gemini') {
    $cleanKey = trim($apiKey);
    $cleanModel = preg_replace('/^models\//', '', $model);
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . urlencode($cleanModel) . ':generateContent?key=' . urlencode($cleanKey);
    $payload = json_encode([
        'contents' => [
            ['parts' => [['text' => 'ping']]]
        ],
        'generationConfig' => [
            'maxOutputTokens' => 3
        ]
    ]);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
} elseif ($provider === 'groq') {
    $url = 'https://api.groq.com/openai/v1/chat/completions';
    $payload = json_encode([
        'model' => $model,
        'messages' => [['role' => 'user', 'content' => 'ping']],
        'max_tokens' => 3
    ]);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . trim($apiKey)
    ]);
} elseif ($provider === 'openrouter') {
    $url = 'https://openrouter.ai/api/v1/chat/completions';
    $payload = json_encode([
        'model' => $model,
        'messages' => [['role' => 'user', 'content' => 'ping']],
        'max_tokens' => 3
    ]);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . trim($apiKey),
        'HTTP-Referer: https://whatsapp-api-xepo.onrender.com',
        'X-Title: WhatsGateway AI Key Test'
    ]);
}

$responseBody = (string) curl_exec($ch);
$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

$latencyMs = round((microtime(true) - $startTime) * 1000);

$isSuccess = ($httpCode >= 200 && $httpCode < 300);
$errorMessage = null;

if (!$isSuccess) {
    if ($curlError !== '') {
        $errorMessage = "Network/cURL Error: {$curlError}";
    } else {
        $decoded = json_decode($responseBody, true);
        if ($provider === 'gemini') {
            $errorMessage = $decoded['error']['message'] ?? "Gemini API error [HTTP {$httpCode}]";
        } elseif ($provider === 'groq' || $provider === 'openrouter') {
            $errorMessage = $decoded['error']['message'] ?? "API error [HTTP {$httpCode}]";
        } else {
            $errorMessage = "HTTP {$httpCode}: " . substr(strip_tags($responseBody), 0, 150);
        }
    }
}

// Update DB record status if key exists
if ($id !== null) {
    try {
        if ($isSuccess) {
            $upStmt = $pdo->prepare('UPDATE ai_provider_keys SET status = "active", last_error = NULL, last_used_at = NOW(), fail_count = 0 WHERE id = ? AND user_id = ?');
            $upStmt->execute([$id, $userId]);
        } else {
            $newStatus = ($httpCode === 429) ? 'rate_limited' : 'error';
            $upStmt = $pdo->prepare('UPDATE ai_provider_keys SET status = ?, last_error = ?, fail_count = fail_count + 1 WHERE id = ? AND user_id = ?');
            $upStmt->execute([$newStatus, substr((string) $errorMessage, 0, 500), $id, $userId]);
        }
    } catch (\Throwable $e) {}
}

if ($isSuccess) {
    json_response([
        'ok' => true,
        'latency_ms' => $latencyMs,
        'http_code' => $httpCode,
        'status' => 'active',
        'model' => $model,
        'message' => "✓ {$provider} key verified successfully with model '{$model}' in {$latencyMs}ms!"
    ]);
} else {
    json_response([
        'ok' => false,
        'latency_ms' => $latencyMs,
        'http_code' => $httpCode,
        'status' => ($httpCode === 429 ? 'rate_limited' : 'error'),
        'error' => $errorMessage
    ], 422);
}
