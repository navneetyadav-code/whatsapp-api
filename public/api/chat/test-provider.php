<?php

declare(strict_types=1);

require_once __DIR__ . '/_auth.php';

$user = get_chat_authenticated_user();
$userId = (int) $user['id'];
$pdo = Database::pdo();

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$provider = strtolower(trim((string) ($input['provider'] ?? 'gemini')));
$inputKey = trim((string) ($input['api_key'] ?? ''));
$inputModel = trim((string) ($input['model'] ?? ''));

if (!in_array($provider, ['gemini', 'groq', 'openrouter'], true)) {
    json_response(['ok' => false, 'error' => 'Invalid provider specified.'], 400);
}

// Fetch user's saved settings if key or model is not explicitly provided
$stmt = $pdo->prepare('SELECT * FROM ai_agent_settings WHERE user_id = ? LIMIT 1');
$stmt->execute([$userId]);
$settings = $stmt->fetch() ?: [];

// Resolve API Key
if ($inputKey === '' || preg_match('/^[\*•]+$/', $inputKey)) {
    $apiKey = match ($provider) {
        'gemini' => trim((string) ($settings['gemini_api_key'] ?? '')),
        'groq' => trim((string) ($settings['groq_api_key'] ?? '')),
        'openrouter' => trim((string) ($settings['openrouter_api_key'] ?? '')),
        default => ''
    };
} else {
    $apiKey = $inputKey;
}

if ($apiKey === '') {
    json_response([
        'ok' => false,
        'error' => ucfirst($provider) . ' API Key is not set. Please enter or save your API key first.'
    ], 400);
}

// Resolve Model strictly from user input or backend settings (zero hardcoded strings)
if ($inputModel !== '') {
    $model = $inputModel;
} else {
    $model = match ($provider) {
        'gemini' => trim((string) ($settings['gemini_model'] ?? '')),
        'groq' => trim((string) ($settings['groq_model'] ?? '')),
        'openrouter' => trim((string) ($settings['openrouter_model'] ?? '')),
        default => ''
    };
}

if ($model === '') {
    json_response([
        'ok' => false,
        'error' => "No {$provider} model configured in your backend AI settings. Please select or enter a model name."
    ], 400);
}

$prompt = "Ping test. Reply with 'OK' and nothing else.";
$startTime = microtime(true);

if ($provider === 'gemini') {
    // Strip leading models/ if entered by user, but preserve the exact requested model
    $targetModel = preg_replace('/^models\//', '', trim($model));

    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$targetModel}:generateContent?key={$apiKey}";
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 12,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode([
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'maxOutputTokens' => 60,
            ]
        ])
    ]);
    $resp = curl_exec($ch);
    $curlErr = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

    if ($curlErr) {
        json_response(['ok' => false, 'error' => "Network error: {$curlErr}"], 502);
    }

    $data = json_decode((string) $resp, true);
    if ($httpCode >= 200 && $httpCode < 300) {
        $reply = trim((string) ($data['candidates'][0]['content']['parts'][0]['text'] ?? ''));
        if ($reply === '') {
            $reply = 'OK (Connected)';
        }
        json_response([
            'ok' => true,
            'provider' => 'gemini',
            'model' => $targetModel,
            'latency_ms' => $latencyMs,
            'reply' => $reply,
            'message' => "Gemini is working! ({$latencyMs}ms)"
        ]);
    } else {
        $errMsg = $data['error']['message'] ?? "HTTP {$httpCode} error from Gemini API";
        json_response(['ok' => false, 'error' => $errMsg, 'status' => $httpCode], 400);
    }
} elseif ($provider === 'groq') {
    $url = 'https://api.groq.com/openai/v1/chat/completions';
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 12,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            "Authorization: Bearer {$apiKey}"
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.1,
            'max_tokens' => 10,
        ])
    ]);
    $resp = curl_exec($ch);
    $curlErr = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

    if ($curlErr) {
        json_response(['ok' => false, 'error' => "Network error: {$curlErr}"], 502);
    }

    $data = json_decode((string) $resp, true);
    if ($httpCode >= 200 && $httpCode < 300) {
        $reply = trim((string) ($data['choices'][0]['message']['content'] ?? 'OK'));
        json_response([
            'ok' => true,
            'provider' => 'groq',
            'model' => $model,
            'latency_ms' => $latencyMs,
            'reply' => $reply,
            'message' => "Groq is working! ({$latencyMs}ms)"
        ]);
    } else {
        $errMsg = $data['error']['message'] ?? "HTTP {$httpCode} error from Groq API";
        json_response(['ok' => false, 'error' => $errMsg, 'status' => $httpCode], 400);
    }
} elseif ($provider === 'openrouter') {
    $url = 'https://openrouter.ai/api/v1/chat/completions';
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 12,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            "Authorization: Bearer {$apiKey}"
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.1,
            'max_tokens' => 10,
        ])
    ]);
    $resp = curl_exec($ch);
    $curlErr = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

    if ($curlErr) {
        json_response(['ok' => false, 'error' => "Network error: {$curlErr}"], 502);
    }

    $data = json_decode((string) $resp, true);
    if ($httpCode >= 200 && $httpCode < 300) {
        $reply = trim((string) ($data['choices'][0]['message']['content'] ?? 'OK'));
        json_response([
            'ok' => true,
            'provider' => 'openrouter',
            'model' => $model,
            'latency_ms' => $latencyMs,
            'reply' => $reply,
            'message' => "OpenRouter is working! ({$latencyMs}ms)"
        ]);
    } else {
        $errMsg = $data['error']['message'] ?? "HTTP {$httpCode} error from OpenRouter API";
        json_response(['ok' => false, 'error' => $errMsg, 'status' => $httpCode], 400);
    }
}
