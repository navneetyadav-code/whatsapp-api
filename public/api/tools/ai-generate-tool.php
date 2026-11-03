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
        'error' => 'Unauthorized. Please log in to your dashboard to use the AI Tool Generator.'
    ], 401);
    exit;
}

$userId = (int) $user['id'];
$pdo = Database::pdo();

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true) ?: ($_POST ?? []);
$rawContent = trim((string) ($input['raw_content'] ?? ''));
$inputType = trim((string) ($input['input_type'] ?? 'auto')); // auto | curl | prompt | docs | shared | shared_hosting
$selectedKeyId = trim((string) ($input['selected_key_id'] ?? 'auto'));
$selectedModel = trim((string) ($input['selected_model'] ?? ''));
$isSharedHosting = in_array($inputType, ['shared', 'shared_hosting'], true);

if ($rawContent === '') {
    json_response([
        'ok' => false,
        'error' => 'Please provide a cURL command, plain English instructions, or API documentation.'
    ], 422);
}

// -----------------------------------------------------------------------------
// STEP 1: PARSE DETERMINISTICALLY VIA CURL IMPORTER
// -----------------------------------------------------------------------------
$parsedCurl = CurlImporter::parse($rawContent);
$isCurl = ($parsedCurl !== null);

// If user explicitly chose Offline cURL Importer (Fast / Zero AI Tokens)
if (in_array($selectedKeyId, ['offline', 'curl', 'offline_curl'], true)) {
    if ($parsedCurl !== null) {
        $finalTool = $parsedCurl;
        if ($isSharedHosting) {
            if (!isset($finalTool['execution_config']['headers']['Accept']) && !isset($finalTool['execution_config']['headers']['accept'])) {
                $finalTool['execution_config']['headers']['Accept'] = 'application/json';
            }
        }
        json_response([
            'ok' => true,
            'source' => $isSharedHosting ? 'Shared Hosting Engine Copilot (Offline)' : 'Production Offline cURL Engine (Deterministic)',
            'tool' => $finalTool
        ]);
        exit;
    } else {
        json_response([
            'ok' => false,
            'error' => 'The Offline cURL Importer requires a valid cURL command (e.g. curl -X GET "https://api..."). If you are importing via Plain Prompt or API Docs, please select an AI Key from AI Credentials or Auto Cascade.'
        ], 422);
        exit;
    }
}

// -----------------------------------------------------------------------------
// STEP 2: AI COPILOT SYNTHESIS (GEMINI -> GROQ -> OPENROUTER CASCADE)
// -----------------------------------------------------------------------------
// 1. Fetch active user AI credentials from dedicated table (from /ai-credentials)
$keysStmt = $pdo->prepare(
    'SELECT id, provider, label, api_key, model, priority, is_active
     FROM ai_provider_keys
     WHERE user_id = ? AND is_active = 1
     ORDER BY priority ASC, id ASC'
);
$keysStmt->execute([$userId]);
$dbKeys = $keysStmt->fetchAll(PDO::FETCH_ASSOC);

$keysByProvider = [
    'gemini' => [],
    'groq' => [],
    'openrouter' => []
];
foreach ($dbKeys as $k) {
    $prov = strtolower(trim((string) $k['provider']));
    if (isset($keysByProvider[$prov])) {
        $keysByProvider[$prov][] = $k;
    }
}

// 2. Fetch AI agent settings (for legacy single-key fallback & priority)
$settingsStmt = $pdo->prepare('SELECT * FROM ai_agent_settings WHERE user_id = ? LIMIT 1');
$settingsStmt->execute([$userId]);
$aiSettings = $settingsStmt->fetch(PDO::FETCH_ASSOC) ?: [];

if (empty($keysByProvider['gemini']) && !empty($aiSettings['gemini_api_key'])) {
    $keysByProvider['gemini'][] = [
        'api_key' => $aiSettings['gemini_api_key'],
        'model' => $aiSettings['gemini_model'] ?? 'gemini-3.5-flash',
        'label' => 'Legacy Gemini Key'
    ];
}
if (empty($keysByProvider['groq']) && !empty($aiSettings['groq_api_key'])) {
    $keysByProvider['groq'][] = [
        'api_key' => $aiSettings['groq_api_key'],
        'model' => $aiSettings['groq_model'] ?? 'openai/gpt-oss-120b',
        'label' => 'Legacy Groq Key'
    ];
}
if (empty($keysByProvider['openrouter']) && !empty($aiSettings['openrouter_api_key'])) {
    $keysByProvider['openrouter'][] = [
        'api_key' => $aiSettings['openrouter_api_key'],
        'model' => $aiSettings['openrouter_model'] ?? 'meta-llama/llama-3.3-70b-instruct:free',
        'label' => 'Legacy OpenRouter Key'
    ];
}

$priority = strtolower(trim((string) ($aiSettings['provider_priority'] ?? 'gemini')));
$providerOrder = match ($priority) {
    'groq' => ['groq', 'gemini', 'openrouter'],
    'openrouter' => ['openrouter', 'gemini', 'groq'],
    default => ['gemini', 'groq', 'openrouter']
};

// Check if user requested a specific AI key & model from /ai-credentials
$targetKey = null;
if ($selectedKeyId !== '' && $selectedKeyId !== 'auto') {
    if (is_numeric($selectedKeyId)) {
        $kid = (int) $selectedKeyId;
        foreach ($dbKeys as $k) {
            if ((int) $k['id'] === $kid) {
                $targetKey = $k;
                break;
            }
        }
        if (!$targetKey) {
            $findStmt = $pdo->prepare('SELECT id, provider, label, api_key, model, priority, is_active FROM ai_provider_keys WHERE id = ? AND user_id = ? LIMIT 1');
            $findStmt->execute([$kid, $userId]);
            $targetKey = $findStmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }
    } elseif (str_starts_with($selectedKeyId, 'legacy_')) {
        $legProv = str_replace('legacy_', '', $selectedKeyId);
        if (!empty($keysByProvider[$legProv])) {
            $targetKey = $keysByProvider[$legProv][0];
            $targetKey['provider'] = $legProv;
        }
    }

    if ($targetKey !== null) {
        $prov = strtolower(trim((string) ($targetKey['provider'] ?? 'gemini')));
        $keysByProvider = [$prov => [$targetKey]];
        $providerOrder = [$prov];
    }
}

$hasAiKeys = !empty($keysByProvider['gemini']) || !empty($keysByProvider['groq']) || !empty($keysByProvider['openrouter']);

$aiResult = null;
$usedProvider = null;
$lastAiError = null;

if ($hasAiKeys) {
    if ($isSharedHosting) {
        $contextHint = $isCurl 
            ? "The user has provided a Shared Hosting API cURL (e.g. cPanel / ByetHost / InfinityFree / Apache virtual host / AssistGroup):\n{$rawContent}\n\nPre-extracted baseline structure:\n" . json_encode($parsedCurl, JSON_PRETTY_PRINT) . "\nEnsure the tool is fully configured for shared hosting execution without aes errors, preserving Accept: application/json and all query/route parameters."
            : "The user has provided the following Shared Hosting requirement or API documentation:\n\"{$rawContent}\"";
    } else {
        $contextHint = $isCurl 
            ? "The user has provided a raw cURL command:\n{$rawContent}\n\nPre-extracted baseline structure:\n" . json_encode($parsedCurl, JSON_PRETTY_PRINT)
            : "The user has provided the following requirement, prompt, or API documentation:\n\"{$rawContent}\"";
    }

    $systemInstruction = <<<PROMPT
You are a Principal Integration Architect & API Engineer.
Your task is to convert the provided cURL command, API documentation, or English description into a PRODUCTION-GRADE autonomous Tool Definition for an enterprise WhatsApp AI Agent.

CRITICAL REQUIREMENTS:
1. "tool_slug": MUST be lowercase alphanumeric with underscores only (e.g. "send_whatsapp", "track_order", "check_weather", "verify_payment"). Maximum 32 characters.
2. "display_name": Engaging, professional title with a relevant leading emoji (e.g. "📱 WhatsApp Message Dispatcher", "📦 Order Tracker", "💳 Payment Status").
3. "category": Must be one of: "rest_api", "shared_env_api", "webhook". Use "rest_api" for standard endpoints, "webhook" for one-way notifications.
4. "description": A crystal-clear instruction explaining WHEN and WHY the WhatsApp AI bot should invoke this tool, what inputs it needs from the customer, and what it returns. This text is injected directly into LLM function calling declarations.
5. STRICT ZERO-RENAMING OF API CONTRACT FIELDS:
   - You MUST NOT invent, abstract, or rename parameter fields.
   - If the API or cURL expects "to" and "body", the tool parameters MUST be named "to" and "body" - NEVER rename them to "phone_number", "message_content", "recipient", etc.!
   - If the API expects "order_id", use "order_id", not "tracking_no".
6. REQUEST BODY & "body_template" FOR WRITE OPERATIONS (POST, PUT, PATCH):
   - You MUST formulate "body_template" as an object matching the exact JSON payload expected by the target API.
   - Use dynamic string templates for customer arguments: e.g. "to": "{to}", "body": "{body}", "order_id": "{order_id}".
   - Keep static platform/API flags as raw values: e.g. "type": "text", "messaging_product": "whatsapp", "preview_url": false.
7. PARAMETERS SCHEMA ("parameters"):
   - For every placeholder `{param}` in the URL or body_template, provide a parameter entry:
   - "name": Exact parameter name without braces (must match placeholder).
   - "type": "STRING", "NUMBER", "INTEGER", or "BOOLEAN".
   - "description": High-context explanation of what value this parameter takes (e.g. "Customer 10-digit mobile number with country code", "Customer AWB tracking number").
   - "required": true for mandatory fields; false for optional fields.
8. EXECUTION CONFIG:
   - "method": HTTP verb (GET, POST, PUT, DELETE, PATCH).
   - "url": Full target endpoint URL with `{param}` placeholders if query/path arguments exist.
   - "headers": Include all required headers from cURL or specs (e.g. "Content-Type": "application/json", "Authorization": "Bearer ...", "Accept": "application/json").
   - "response_path": (Optional) dot-separated path to extract from response JSON (e.g. "data" or "result"). Leave empty "" if returning full response.
9. RBAC ROLES ("rbac_roles"):
   - Default to ["guest", "customer", "admin"]. If tool performs destructive actions or refunds, restrict to ["admin"].

RETURN ONLY A VALID, PARSABLE JSON OBJECT. DO NOT WRAP IN MARKDOWN BACKTICKS OR ADD EXPLANATORY TEXT.

Example 1 (POST with JSON body):
{
  "tool_slug": "send_whatsapp",
  "display_name": "📱 WhatsApp Dispatcher",
  "category": "rest_api",
  "description": "Sends an instant WhatsApp text message to a recipient phone number.",
  "execution_config": {
    "method": "POST",
    "url": "https://api.example.com/v1/messages",
    "headers": {
      "Content-Type": "application/json",
      "Authorization": "Bearer sample_token"
    },
    "body_template": {
      "messaging_product": "whatsapp",
      "to": "{to}",
      "type": "text",
      "text": {
        "body": "{body}"
      }
    },
    "response_path": "messages.0"
  },
  "parameters": [
    {
      "name": "to",
      "type": "STRING",
      "description": "Recipient phone number with country code (e.g. 919876543210)",
      "required": true
    },
    {
      "name": "body",
      "type": "STRING",
      "description": "Message text to be sent",
      "required": true
    }
  ],
  "rbac_roles": ["guest", "customer", "admin"]
}

Example 2 (GET with query parameters):
{
  "tool_slug": "track_shipment",
  "display_name": "📦 Shipment Tracker",
  "category": "rest_api",
  "description": "Fetches current courier location and delivery status when a customer provides their tracking or order ID.",
  "execution_config": {
    "method": "GET",
    "url": "https://api.example.com/v1/tracking?order_id={order_id}",
    "headers": {
      "Accept": "application/json"
    },
    "response_path": "data"
  },
  "parameters": [
    {
      "name": "order_id",
      "type": "STRING",
      "description": "Customer order or AWB number",
      "required": true
    }
  ],
  "rbac_roles": ["guest", "customer", "admin"]
}
PROMPT;

    $promptPayload = $systemInstruction . "\n\nINPUT DATA:\n" . $contextHint;

    // Execute Multi-Provider & Multi-Key Auto-Failover Cascade
    $isSpecificKey = ($targetKey !== null);
    foreach ($providerOrder as $prov) {
        if ($aiResult !== null) break;
        $keys = $keysByProvider[$prov] ?? [];
        if (empty($keys)) continue;

        foreach ($keys as $kRecord) {
            $apiKey = trim((string) ($kRecord['api_key'] ?? ''));
            if ($apiKey === '') continue;

            if ($prov === 'gemini') {
                $baseModel = trim((string) ($kRecord['model'] ?? ($aiSettings['gemini_model'] ?? 'gemini-3.5-flash'))) ?: 'gemini-3.5-flash';
                $candidates = $isSpecificKey ? [$baseModel] : array_unique([$baseModel, 'gemini-2.5-flash', 'gemini-flash-latest']);
                foreach ($candidates as $candModel) {
                    try {
                        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$candModel}:generateContent?key={$apiKey}";
                        $ch = curl_init($url);
                        curl_setopt_array($ch, [
                            CURLOPT_POST => true,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_TIMEOUT => 20,
                            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                            CURLOPT_POSTFIELDS => json_encode([
                                'contents' => [
                                    ['parts' => [['text' => $promptPayload]]]
                                ],
                                'generationConfig' => [
                                    'temperature' => 0.2,
                                    'maxOutputTokens' => 1500,
                                    'responseMimeType' => 'application/json'
                                ]
                            ])
                        ]);
                        $resp = curl_exec($ch);
                        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        curl_close($ch);

                        if ($code === 200 && $resp) {
                            $json = json_decode($resp, true);
                            $content = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
                            $parsed = json_decode(trim($content), true);
                            if (is_array($parsed) && !empty($parsed['tool_slug']) && !empty($parsed['execution_config'])) {
                                $aiResult = $parsed;
                                $usedProvider = "Google Gemini ({$candModel})";
                                break 2;
                            }
                        } else {
                            $errJson = json_decode((string) $resp, true);
                            $lastAiError = $errJson['error']['message'] ?? "Gemini HTTP $code";
                        }
                    } catch (\Throwable $e) {
                        $lastAiError = $e->getMessage();
                    }
                }
            } elseif ($prov === 'groq') {
                $baseModel = trim((string) ($kRecord['model'] ?? ($aiSettings['groq_model'] ?? 'openai/gpt-oss-120b'))) ?: 'openai/gpt-oss-120b';
                $candidates = $isSpecificKey ? [$baseModel] : array_unique([$baseModel, 'openai/gpt-oss-120b', 'llama-3.3-70b-versatile']);
                foreach ($candidates as $candModel) {
                    try {
                        $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
                        curl_setopt_array($ch, [
                            CURLOPT_POST => true,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_TIMEOUT => 20,
                            CURLOPT_HTTPHEADER => [
                                'Content-Type: application/json',
                                "Authorization: Bearer {$apiKey}"
                            ],
                            CURLOPT_POSTFIELDS => json_encode([
                                'model' => $candModel,
                                'messages' => [
                                    ['role' => 'user', 'content' => $promptPayload]
                                ],
                                'temperature' => 0.2,
                                'max_tokens' => 1500,
                                'response_format' => ['type' => 'json_object']
                            ])
                        ]);
                        $resp = curl_exec($ch);
                        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        curl_close($ch);

                        if ($code === 200 && $resp) {
                            $json = json_decode($resp, true);
                            $content = $json['choices'][0]['message']['content'] ?? '';
                            $parsed = json_decode(trim($content), true);
                            if (is_array($parsed) && !empty($parsed['tool_slug']) && !empty($parsed['execution_config'])) {
                                $aiResult = $parsed;
                                $usedProvider = "Groq ({$candModel})";
                                break 2;
                            }
                        } else {
                            $errJson = json_decode((string) $resp, true);
                            $lastAiError = $errJson['error']['message'] ?? "Groq HTTP $code";
                        }
                    } catch (\Throwable $e) {
                        $lastAiError = $e->getMessage();
                    }
                }
            } elseif ($prov === 'openrouter') {
                $baseModel = trim((string) ($kRecord['model'] ?? ($aiSettings['openrouter_model'] ?? 'meta-llama/llama-3.3-70b-instruct:free'))) ?: 'meta-llama/llama-3.3-70b-instruct:free';
                $candidates = $isSpecificKey ? [$baseModel] : array_unique([$baseModel, 'meta-llama/llama-3.3-70b-instruct:free', 'meta-llama/llama-3.3-70b-instruct']);
                foreach ($candidates as $candModel) {
                    try {
                        $ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
                        curl_setopt_array($ch, [
                            CURLOPT_POST => true,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_TIMEOUT => 20,
                            CURLOPT_HTTPHEADER => [
                                'Content-Type: application/json',
                                "Authorization: Bearer {$apiKey}"
                            ],
                            CURLOPT_POSTFIELDS => json_encode([
                                'model' => $candModel,
                                'messages' => [
                                    ['role' => 'user', 'content' => $promptPayload]
                                ],
                                'temperature' => 0.2,
                                'max_tokens' => 1500
                            ])
                        ]);
                        $resp = curl_exec($ch);
                        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        curl_close($ch);

                        if ($code === 200 && $resp) {
                            $json = json_decode($resp, true);
                            $content = $json['choices'][0]['message']['content'] ?? '';
                            $content = preg_replace('/^```(?:json)?\s*/i', '', trim($content));
                            $content = preg_replace('/\s*```$/', '', $content);
                            $parsed = json_decode(trim($content), true);
                            if (is_array($parsed) && !empty($parsed['tool_slug']) && !empty($parsed['execution_config'])) {
                                $aiResult = $parsed;
                                $usedProvider = "OpenRouter ({$candModel})";
                                break 2;
                            }
                        } else {
                            $errJson = json_decode((string) $resp, true);
                            $lastAiError = $errJson['error']['message'] ?? "OpenRouter HTTP $code";
                        }
                    } catch (\Throwable $e) {
                        $lastAiError = $e->getMessage();
                    }
                }
            }
        }
    }
}

// If user specifically requested an AI key and it failed:
if ($targetKey !== null && $aiResult === null) {
    // If input is a valid cURL command, gracefully fall back to the production offline cURL parser
    if ($parsedCurl !== null) {
        $finalTool = $parsedCurl;
        if ($isSharedHosting) {
            if (!isset($finalTool['execution_config']['headers']['Accept']) && !isset($finalTool['execution_config']['headers']['accept'])) {
                $finalTool['execution_config']['headers']['Accept'] = 'application/json';
            }
        }
        json_response([
            'ok' => true,
            'source' => 'Production Offline cURL Engine (Fallback)',
            'tool' => $finalTool,
            'warning' => "Selected AI key '{$targetKey['label']}' failed (" . ($lastAiError ?: 'Error') . "), so the tool was safely generated using the Production Offline cURL Importer."
        ]);
        exit;
    }

    json_response([
        'ok' => false,
        'error' => "Selected AI key '{$targetKey['label']}' (" . ($targetKey['model'] ?? 'default') . ") failed to generate tool: " . ($lastAiError ?: 'Model returned empty or invalid schema. Check your API key in /ai-credentials.')
    ], 422);
    exit;
}

// -----------------------------------------------------------------------------
// STEP 3: ASSEMBLE FINAL TOOL SPECIFICATION
// -----------------------------------------------------------------------------
$finalTool = null;
$sourceType = 'unknown';

if ($aiResult !== null) {
    // Sanitize and validate AI result
    $finalTool = [
        'tool_slug' => strtolower((string) preg_replace('/[^a-z0-9_]/', '_', (string) ($aiResult['tool_slug'] ?? 'custom_tool'))),
        'display_name' => trim((string) ($aiResult['display_name'] ?? 'Custom Tool')),
        'category' => in_array($aiResult['category'] ?? '', ['rest_api', 'shared_env_api', 'webhook'], true) ? $aiResult['category'] : 'rest_api',
        'description' => trim((string) ($aiResult['description'] ?? 'Custom automated tool.')),
        'execution_config' => is_array($aiResult['execution_config'] ?? null) ? $aiResult['execution_config'] : [],
        'parameters' => is_array($aiResult['parameters'] ?? null) ? $aiResult['parameters'] : [],
        'rbac_roles' => is_array($aiResult['rbac_roles'] ?? null) ? $aiResult['rbac_roles'] : ['guest', 'customer', 'admin']
    ];

    // Guarantee baseline cURL body_template and headers if omitted by AI
    if ($parsedCurl !== null) {
        if (!empty($parsedCurl['execution_config']['body_template']) && empty($finalTool['execution_config']['body_template'])) {
            $finalTool['execution_config']['body_template'] = $parsedCurl['execution_config']['body_template'];
        }
        if (!empty($parsedCurl['execution_config']['headers'])) {
            $finalTool['execution_config']['headers'] = array_merge($parsedCurl['execution_config']['headers'], $finalTool['execution_config']['headers'] ?? []);
        }
    }

    $sourceType = "AI Synthesized via {$usedProvider}";
} elseif ($parsedCurl !== null) {
    // Use high-precision deterministic cURL parser
    $finalTool = $parsedCurl;
    if ($isSharedHosting) {
        if (!isset($finalTool['execution_config']['headers']['Accept']) && !isset($finalTool['execution_config']['headers']['accept'])) {
            $finalTool['execution_config']['headers']['Accept'] = 'application/json';
        }
    }
    $sourceType = $isSharedHosting ? 'Shared Hosting Engine Copilot' : 'cURL Engine Fast Parser';
} else {
    // If not a cURL and no AI response was available, formulate a clean generic template
    $slug = strtolower((string) preg_replace('/[^a-z0-9_]/', '_', substr($rawContent, 0, 24)));
    $finalTool = [
        'tool_slug' => $slug ?: 'custom_api_tool',
        'display_name' => '⚡ ' . ucwords(str_replace('_', ' ', $slug ?: 'Custom API Tool')),
        'category' => 'rest_api',
        'description' => 'Automated API integration: ' . substr($rawContent, 0, 160),
        'execution_config' => [
            'method' => 'GET',
            'url' => 'https://api.example.com/v1/resource?param={param}',
            'headers' => ['Accept' => 'application/json'],
            'response_path' => ''
        ],
        'parameters' => [
            [
                'name' => 'param',
                'type' => 'STRING',
                'description' => 'Target identifier or query argument',
                'required' => true
            ]
        ],
        'rbac_roles' => ['guest', 'customer', 'admin']
    ];
    $sourceType = 'Template Generator';
}

// Return successful structured payload
json_response([
    'ok' => true,
    'source' => $sourceType,
    'tool' => $finalTool
]);
