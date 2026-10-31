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
$toolSlug = trim((string) ($input['tool_slug'] ?? ''));
$category = trim((string) ($input['category'] ?? 'rest_api'));
$executionConfig = is_array($input['execution_config'] ?? null) ? $input['execution_config'] : [];
$args = is_array($input['args'] ?? null) ? $input['args'] : [];

if (empty($executionConfig) && $toolSlug !== '') {
    $stmt = Database::pdo()->prepare('SELECT * FROM ai_custom_tools WHERE user_id = ? AND tool_slug = ? LIMIT 1');
    $stmt->execute([(int) $user['id'], $toolSlug]);
    $toolRow = $stmt->fetch();
    if ($toolRow) {
        $category = (string) $toolRow['category'];
        $executionConfig = json_decode((string) $toolRow['execution_config'], true) ?: [];
    }
}

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
        'User-Agent: ' . AntiBotBypass::BROWSER_USER_AGENT,
        'Accept: application/json'
    ];
    $hasExplicitCookie = false;
    foreach ($headers as $hk => $hv) {
        if (strcasecmp($hk, 'Cookie') === 0) {
            $hasExplicitCookie = true;
        }
        $formattedHeaders[] = "{$hk}: {$hv}";
    }

    if (!$hasExplicitCookie) {
        $cachedCookie = AntiBotBypass::getCachedCookie($finalUrl);
        if ($cachedCookie) {
            $formattedHeaders[] = "Cookie: __test={$cachedCookie}";
        }
    }

    $ch = curl_init($finalUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
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

        $isForm = false;
        foreach ($headers as $hk => $hv) {
            if (strcasecmp($hk, 'Content-Type') === 0 && str_contains(strtolower($hv), 'application/x-www-form-urlencoded')) {
                $isForm = true;
                break;
            }
        }

        if ($isForm && is_array($bodyData)) {
            $formattedParams = [];
            foreach ($bodyData as $bk => $bv) {
                if (is_bool($bv)) {
                    $formattedParams[$bk] = $bv ? '1' : '0';
                } else {
                    $formattedParams[$bk] = $bv;
                }
            }
            $postPayload = http_build_query($formattedParams);
        } else {
            if (!isset($headers['Content-Type']) && !isset($headers['content-type'])) {
                $formattedHeaders[] = 'Content-Type: application/json';
                curl_setopt($ch, CURLOPT_HTTPHEADER, $formattedHeaders);
            }
            $postPayload = is_array($bodyData) ? json_encode($bodyData) : (string) $bodyData;
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, $postPayload);
    }

    $responseBody = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // Auto-detect and bypass anti-bot / aes.js challenges (InfinityFree, ByetHost, etc.)
    if (AntiBotBypass::isChallenge((string) $responseBody)) {
        $solvedCookie = AntiBotBypass::solveChallenge((string) $responseBody);
        if ($solvedCookie) {
            AntiBotBypass::setCachedCookie($finalUrl, $solvedCookie);
            AntiBotBypass::activateCookie($finalUrl, $solvedCookie);

            $retryHeaders = [];
            $hasCookie = false;
            foreach ($formattedHeaders as $h) {
                if (stripos($h, 'Cookie:') === 0) {
                    $retryHeaders[] = $h . "; __test={$solvedCookie}";
                    $hasCookie = true;
                } elseif (stripos($h, 'User-Agent:') === 0) {
                    $retryHeaders[] = 'User-Agent: ' . AntiBotBypass::BROWSER_USER_AGENT;
                } else {
                    $retryHeaders[] = $h;
                }
            }
            if (!$hasCookie) {
                $retryHeaders[] = "Cookie: __test={$solvedCookie}";
            }

            $chRetry = curl_init($finalUrl);
            curl_setopt($chRetry, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($chRetry, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($chRetry, CURLOPT_TIMEOUT, 12);
            curl_setopt($chRetry, CURLOPT_HTTPHEADER, $retryHeaders);
            if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
                curl_setopt($chRetry, CURLOPT_CUSTOMREQUEST, $method);
                if (isset($postPayload)) {
                    curl_setopt($chRetry, CURLOPT_POSTFIELDS, $postPayload);
                }
            }

            $retryBody = curl_exec($chRetry);
            $retryCode = curl_getinfo($chRetry, CURLINFO_HTTP_CODE);
            $retryErr = curl_error($chRetry);
            curl_close($chRetry);

            if (!$retryErr && $retryBody) {
                $responseBody = $retryBody;
                $httpCode = $retryCode;
                $curlError = '';
            }
        }
    }

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
        'User-Agent: ' . AntiBotBypass::BROWSER_USER_AGENT
    ];
    if ($token !== '') {
        $headers[] = "Authorization: Bearer {$token}";
    }

    $cachedCookie = AntiBotBypass::getCachedCookie($targetUrl);
    if ($cachedCookie) {
        $headers[] = "Cookie: __test={$cachedCookie}";
    }

    $ch = curl_init($targetUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 12);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $responseBody = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (AntiBotBypass::isChallenge((string) $responseBody)) {
        $solvedCookie = AntiBotBypass::solveChallenge((string) $responseBody);
        if ($solvedCookie) {
            AntiBotBypass::setCachedCookie($targetUrl, $solvedCookie);
            AntiBotBypass::activateCookie($targetUrl, $solvedCookie);

            $headers[] = "Cookie: __test={$solvedCookie}";
            $chRetry = curl_init($targetUrl);
            curl_setopt($chRetry, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($chRetry, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($chRetry, CURLOPT_TIMEOUT, 12);
            curl_setopt($chRetry, CURLOPT_HTTPHEADER, $headers);
            $retryBody = curl_exec($chRetry);
            $retryCode = curl_getinfo($chRetry, CURLINFO_HTTP_CODE);
            curl_close($chRetry);

            if ($retryBody) {
                $responseBody = $retryBody;
                $httpCode = $retryCode;
            }
        }
    }

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
