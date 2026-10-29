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
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$rawContent = trim((string) ($input['raw_content'] ?? ''));
$inputType = trim((string) ($input['input_type'] ?? 'auto')); // auto | curl | prompt | docs

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

// -----------------------------------------------------------------------------
// STEP 2: AI COPILOT SYNTHESIS (GEMINI / GROQ / OPENROUTER)
// -----------------------------------------------------------------------------
// Fetch active user AI credentials
$settingsStmt = $pdo->prepare('SELECT * FROM ai_agent_settings WHERE user_id = ? LIMIT 1');
$settingsStmt->execute([$userId]);
$aiSettings = $settingsStmt->fetch() ?: [];

$geminiKey = trim((string) ($aiSettings['gemini_api_key'] ?? ''));
$groqKey = trim((string) ($aiSettings['groq_api_key'] ?? ''));
$openrouterKey = trim((string) ($aiSettings['openrouter_api_key'] ?? ''));

$geminiModel = trim((string) ($aiSettings['gemini_model'] ?? 'gemini-1.5-flash')) ?: 'gemini-1.5-flash';
if (str_contains($geminiModel, 'gemini-3.5') || str_contains($geminiModel, 'gemini-2.5')) {
    $geminiModel = 'gemini-1.5-flash';
}
$groqModel = trim((string) ($aiSettings['groq_model'] ?? 'openai/gpt-oss-120b')) ?: 'openai/gpt-oss-120b';
if (str_contains($groqModel, 'llama-3.3-70b-versatile')) {
    $groqModel = 'openai/gpt-oss-120b';
}
$openrouterModel = trim((string) ($aiSettings['openrouter_model'] ?? 'meta-llama/llama-3.3-70b-instruct:free')) ?: 'meta-llama/llama-3.3-70b-instruct:free';
$priority = $aiSettings['provider_priority'] ?? 'gemini';

$hasAiKeys = !empty($geminiKey) || !empty($groqKey) || !empty($openrouterKey);

$aiResult = null;
$usedProvider = null;

if ($hasAiKeys) {
    $contextHint = $isCurl 
        ? "The user has provided a raw cURL command:\n{$rawContent}\n\nPre-extracted baseline structure:\n" . json_encode($parsedCurl, JSON_PRETTY_PRINT)
        : "The user has provided the following requirement, prompt, or API documentation:\n\"{$rawContent}\"";

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
   - "execution_config.body_template" is MANDATORY whenever the endpoint receives a JSON payload.
   - PRESERVE the exact nested JSON object/array hierarchy of the target API.
   - Only dynamic values should be replaced with {param_name} placeholders matching your "parameters" list.
   - Retain ALL static constants, headers, and protocol flags (e.g. "messaging_product": "whatsapp", "type": "text", "recipient_type": "individual", "preview_url": false) EXACTLY as defined in the cURL or documentation. DO NOT turn static constants into dynamic parameters!
7. "execution_config":
   - "method": "GET" | "POST" | "PUT" | "DELETE"
   - "url": Target URL. ANY dynamic parameter in the URL MUST be wrapped in curly braces like {order_id} or {city}.
   - "headers": Object containing needed headers (e.g. {"Content-Type": "application/json", "Authorization": "Bearer ...", "Accept": "application/json"}).
   - "body_template": Object/Array representing the structured JSON payload with {param_name} placeholders (for POST/PUT/PATCH), or omit/null for GET.
   - "response_path": Optional sub-property in response JSON (e.g. "data" or "results" or leave empty "").
8. "parameters": Array of objects:
   - "name": EXACT parameter identifier matching the {placeholder} in the URL or body_template.
   - "type": "STRING" | "NUMBER" | "INTEGER" | "BOOLEAN"
   - "description": Clear explanation of what this input is for the AI.
   - "required": true | false
9. "rbac_roles": Default to ["guest", "customer", "admin"]. If the tool handles private internal operations (like deleting data or viewing employee records), set to ["admin"].

STRICT OUTPUT FORMAT:
Output ONLY a valid JSON object matching this schema. Do NOT enclose in markdown backticks. Do NOT write conversational filler.

Example 1 (POST with nested body_template):
{
  "tool_slug": "send_whatsapp",
  "display_name": "📱 WhatsApp Cloud Message",
  "category": "rest_api",
  "description": "Sends an outbound WhatsApp text message to a specific phone number using the Meta Cloud API format.",
  "execution_config": {
    "method": "POST",
    "url": "https://api.example.com/v1/messages",
    "headers": {
      "Content-Type": "application/json",
      "Authorization": "Bearer wa_live_token"
    },
    "body_template": {
      "messaging_product": "whatsapp",
      "recipient_type": "individual",
      "to": "{to}",
      "type": "text",
      "text": {
        "preview_url": false,
        "body": "{body}"
      }
    },
    "response_path": ""
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

    $providers = match ($priority) {
        'groq' => ['groq', 'gemini', 'openrouter'],
        'openrouter' => ['openrouter', 'gemini', 'groq'],
        default => ['gemini', 'groq', 'openrouter']
    };

    foreach ($providers as $prov) {
        if ($aiResult !== null) break;

        // Try Groq
        if ($prov === 'groq' && !empty($groqKey)) {
            try {
                $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 20,
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/json',
                        "Authorization: Bearer {$groqKey}"
                    ],
                    CURLOPT_POSTFIELDS => json_encode([
                        'model' => $groqModel,
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
                        $usedProvider = "Groq ({$groqModel})";
                    }
                }
            } catch (\Throwable $e) {}
        }

        // Try Gemini
        if ($prov === 'gemini' && !empty($geminiKey)) {
            try {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$geminiModel}:generateContent?key={$geminiKey}";
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
                        $usedProvider = "Google Gemini ({$geminiModel})";
                    }
                }
            } catch (\Throwable $e) {}
        }

        // Try OpenRouter
        if ($prov === 'openrouter' && !empty($openrouterKey)) {
            try {
                $ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 20,
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/json',
                        "Authorization: Bearer {$openrouterKey}"
                    ],
                    CURLOPT_POSTFIELDS => json_encode([
                        'model' => $openrouterModel,
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
                        $usedProvider = "OpenRouter ({$openrouterModel})";
                    }
                }
            } catch (\Throwable $e) {}
        }
    }
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
    $sourceType = 'cURL Engine Fast Parser';
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
