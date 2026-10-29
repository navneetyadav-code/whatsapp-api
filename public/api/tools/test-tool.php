<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/src/bootstrap.php';

// Authenticate caller (Dashboard Session or API Key)
$user = current_user();
if (!$user) {
    $apiUser = authenticate_api_request();
    if ($apiUser && !empty($apiUser['user_id'])) {
        $stmt = Database::pdo()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([(int) $apiUser['user_id']]);
        $user = $stmt->fetch();
    }
}

if (!$user) {
    json_response([
        'ok' => false,
        'error' => 'Unauthorized. Please log in to test tools.'
    ], 401);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$category = trim((string) ($input['category'] ?? 'rest_api'));
$executionConfig = is_array($input['execution_config'] ?? null) ? $input['execution_config'] : [];
$args = is_array($input['args'] ?? null) ? $input['args'] : [];

$startTime = microtime(true);

if ($category === 'rest_api') {
    $rawUrl = trim((string) ($executionConfig['url'] ?? ''));
    if ($rawUrl === '') {
        json_response(['ok' => false, 'error' => 'Target URL is missing in tool execution configuration.'], 422);
    }

    // Interpolate {param} placeholders in URL
    $finalUrl = $rawUrl;
    foreach ($args as $k => $v) {
        $replacement = is_scalar($v) ? rawurlencode((string) $v) : '';
        $finalUrl = str_replace('{' . $k . '}', $replacement, $finalUrl);
    }

    if (!filter_var($finalUrl, FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $finalUrl)) {
        json_response(['ok' => false, 'error' => "Invalid URL protocol in: {$finalUrl}. Only HTTP/HTTPS permitted."], 422);
    }

    $method = strtoupper((string) ($executionConfig['method'] ?? 'GET'));
    $headers = is_array($executionConfig['headers'] ?? null) ? $executionConfig['headers'] : [];
    $formattedHeaders = [
        'User-Agent: WhatsGateway-Tester/3.0',
        'Accept: application/json'
    ];
    foreach ($headers as $hk => $hv) {
        $formattedHeaders[] = "{$hk}: {$hv}";
    }

    $ch = curl_init($finalUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 12);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $formattedHeaders);

    if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        $bodyData = $args;
        $rawTemplate = $executionConfig['body_template'] ?? null;
        if (is_string($rawTemplate)) {
            $decodedTpl = json_decode($rawTemplate, true);
            $rawTemplate = (json_last_error() === JSON_ERROR_NONE) ? $decodedTpl : $rawTemplate;
        }

        if (!empty($rawTemplate)) {
            if (is_array($rawTemplate)) {
                $bodyData = $rawTemplate;
                array_walk_recursive($bodyData, function (&$val) use ($args) {
                    if (is_string($val)) {
                        foreach ($args as $ak => $av) {
                            if ($val === '{' . $ak . '}') {
                                $val = $av;
                                return;
                            }
                            if (str_contains($val, '{' . $ak . '}')) {
                                $val = str_replace('{' . $ak . '}', is_scalar($av) ? (string) $av : '', $val);
                            }
                        }
                    }
                });
            } else {
                $bodyData = (string) $rawTemplate;
                foreach ($args as $ak => $av) {
                    $bodyData = str_replace('{' . $ak . '}', is_scalar($av) ? (string) $av : '', (string) $bodyData);
                }
            }
        }

        if (!isset($headers['Content-Type']) && !isset($headers['content-type'])) {
            $formattedHeaders[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $formattedHeaders);
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($bodyData) ? json_encode($bodyData) : (string) $bodyData);
    }

    $responseBody = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    $latencyMs = round((microtime(true) - $startTime) * 1000);

    if ($curlError) {
        json_response([
            'ok' => false,
            'status' => 0,
            'latency_ms' => $latencyMs,
            'error' => "cURL network error: {$curlError}"
        ]);
    }

    $decoded = json_decode($responseBody ?: '', true);
    $filteredData = $decoded ?? $responseBody;

    // Filter by response_path if configured
    $responsePath = trim((string) ($executionConfig['response_path'] ?? ''));
    if ($responsePath !== '' && is_array($decoded)) {
        $segments = explode('.', $responsePath);
        $curr = $decoded;
        foreach ($segments as $seg) {
            if (isset($curr[$seg])) {
                $curr = $curr[$seg];
            } else {
                break;
            }
        }
        $filteredData = $curr;
    }

    json_response([
        'ok' => ($httpCode >= 200 && $httpCode < 400),
        'status' => $httpCode,
        'latency_ms' => $latencyMs,
        'final_url' => $finalUrl,
        'data' => $filteredData
    ]);
} elseif ($category === 'webhook') {
    $targetUrl = trim((string) ($executionConfig['target_url'] ?? ''));
    if ($targetUrl === '') {
        json_response(['ok' => false, 'error' => 'Webhook target URL is missing.'], 422);
    }

    $ch = curl_init($targetUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'User-Agent: WhatsGateway-Webhook-Tester/3.0'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'event' => 'tool_test',
        'timestamp' => date('c'),
        'payload' => $args
    ]));

    $responseBody = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $latencyMs = round((microtime(true) - $startTime) * 1000);

    json_response([
        'ok' => ($httpCode >= 200 && $httpCode < 400),
        'status' => $httpCode,
        'latency_ms' => $latencyMs,
        'final_url' => $targetUrl,
        'data' => json_decode($responseBody ?: '', true) ?? $responseBody
    ]);
} elseif ($category === 'shared_env_api') {
    // Production API check
    $baseUrl = rtrim((string) ($executionConfig['base_url'] ?? ''), '/');
    $token = trim((string) ($executionConfig['bearer_token'] ?? ''));
    $action = trim((string) ($args['action'] ?? 'health'));

    $targetUrl = $baseUrl . '/' . ltrim($action, '/');
    $headers = [
        'Accept: application/json',
        'User-Agent: WhatsGateway-Tester/3.0'
    ];
    if ($token !== '') {
        $headers[] = "Authorization: Bearer {$token}";
    }

    $ch = curl_init($targetUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 12);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $responseBody = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $latencyMs = round((microtime(true) - $startTime) * 1000);

    json_response([
        'ok' => ($httpCode >= 200 && $httpCode < 400),
        'status' => $httpCode,
        'latency_ms' => $latencyMs,
        'final_url' => $targetUrl,
        'data' => json_decode($responseBody ?: '', true) ?? $responseBody
    ]);
} else {
    json_response([
        'ok' => true,
        'status' => 200,
        'latency_ms' => 5,
        'message' => 'Built-in handler simulated successfully.',
        'data' => $args
    ]);
}
