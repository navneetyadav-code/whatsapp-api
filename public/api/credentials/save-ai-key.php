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
$label = trim((string) ($input['label'] ?? ''));
$apiKey = trim((string) ($input['api_key'] ?? ''));
$model = trim((string) ($input['model'] ?? ''));
$priority = isset($input['priority']) ? max(1, (int) $input['priority']) : 1;

if (!in_array($provider, ['gemini', 'groq', 'openrouter'], true)) {
    json_response(['ok' => false, 'error' => 'Invalid AI provider. Supported: gemini, groq, openrouter.'], 422);
    exit;
}

if ($label === '') {
    $label = ucfirst($provider) . ' Key';
}

$pdo = Database::pdo();
$userId = (int) $user['id'];

// If model wasn't provided, pull default from backend ai_agent_settings
if ($model === '') {
    $settingsStmt = $pdo->prepare('SELECT gemini_model, groq_model, openrouter_model FROM ai_agent_settings WHERE user_id = ? LIMIT 1');
    $settingsStmt->execute([$userId]);
    $aiSettings = $settingsStmt->fetch(PDO::FETCH_ASSOC) ?: [];
    if ($provider === 'gemini') {
        $model = trim((string) ($aiSettings['gemini_model'] ?? ''));
    } elseif ($provider === 'groq') {
        $model = trim((string) ($aiSettings['groq_model'] ?? ''));
    } elseif ($provider === 'openrouter') {
        $model = trim((string) ($aiSettings['openrouter_model'] ?? ''));
    }
}

try {
    if ($id !== null) {
        // Update existing key
        $checkStmt = $pdo->prepare('SELECT id, api_key, model FROM ai_provider_keys WHERE id = ? AND user_id = ? LIMIT 1');
        $checkStmt->execute([$id, $userId]);
        $existing = $checkStmt->fetch();
        if (!$existing) {
            json_response(['ok' => false, 'error' => 'API key record not found.'], 404);
            exit;
        }

        $finalKey = ($apiKey !== '') ? $apiKey : $existing['api_key'];
        $finalModel = ($model !== '') ? $model : ($existing['model'] ?? null);

        $updateStmt = $pdo->prepare(
            'UPDATE ai_provider_keys 
             SET provider = ?, label = ?, api_key = ?, model = ?, priority = ?, status = "active", last_error = NULL
             WHERE id = ? AND user_id = ?'
        );
        $updateStmt->execute([$provider, $label, $finalKey, $finalModel, $priority, $id, $userId]);
        $keyId = $id;
    } else {
        // Insert new key
        if ($apiKey === '') {
            json_response(['ok' => false, 'error' => 'API key string cannot be empty.'], 422);
            exit;
        }

        $insertStmt = $pdo->prepare(
            'INSERT INTO ai_provider_keys (user_id, provider, label, api_key, model, priority, is_active, status)
             VALUES (?, ?, ?, ?, ?, ?, 1, "active")'
        );
        $insertStmt->execute([$userId, $provider, $label, $apiKey, $model ?: null, $priority]);
        $keyId = (int) $pdo->lastInsertId();
    }

    json_response([
        'ok' => true,
        'message' => 'AI API key saved successfully.',
        'key' => [
            'id' => $keyId,
            'provider' => $provider,
            'label' => $label,
            'model' => $model,
            'priority' => $priority,
            'status' => 'active'
        ]
    ]);
} catch (\Throwable $e) {
    json_response(['ok' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
}
