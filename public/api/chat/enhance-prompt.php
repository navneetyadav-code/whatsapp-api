<?php

declare(strict_types=1);

require_once __DIR__ . '/_auth.php';

$user = get_chat_authenticated_user();
$userId = (int) $user['id'];
$pdo = Database::pdo();

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$rawText = trim((string) ($input['raw_text'] ?? ''));
$tone = trim((string) ($input['tone'] ?? 'professional_warm'));
$agentMode = trim((string) ($input['agent_mode'] ?? ''));
$ownerName = trim((string) ($input['owner_name'] ?? ''));

if ($rawText === '') {
    json_response([
        'ok' => false,
        'error' => 'Please provide some rough notes or details first in the text box.'
    ], 422);
}

// 1. Fetch user's baseline AI settings
$stmt = $pdo->prepare('SELECT * FROM ai_agent_settings WHERE user_id = ? LIMIT 1');
$stmt->execute([$userId]);
$settings = $stmt->fetch() ?: [];

if ($agentMode === '') {
    $agentMode = (string) ($settings['agent_mode'] ?? 'support');
}
if ($ownerName === '') {
    $ownerName = trim((string) ($settings['owner_name'] ?? ''));
}

// 2. Fetch active AI credentials from dedicated table (configured in /ai-credentials)
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
    $prov = strtolower(trim((string) ($k['provider'] ?? '')));
    if (isset($keysByProvider[$prov])) {
        $keysByProvider[$prov][] = $k;
    }
}

// Fallback to legacy single-keys in ai_agent_settings if a provider has no dedicated keys
if (empty($keysByProvider['gemini']) && !empty($settings['gemini_api_key'])) {
    $keysByProvider['gemini'][] = [
        'id' => null,
        'provider' => 'gemini',
        'api_key' => $settings['gemini_api_key'],
        'model' => $settings['gemini_model'] ?? 'gemini-3.5-flash-lite',
        'label' => 'Chatbot Settings Gemini Key'
    ];
}
if (empty($keysByProvider['groq']) && !empty($settings['groq_api_key'])) {
    $keysByProvider['groq'][] = [
        'id' => null,
        'provider' => 'groq',
        'api_key' => $settings['groq_api_key'],
        'model' => $settings['groq_model'] ?? 'openai/gpt-oss-120b',
        'label' => 'Chatbot Settings Groq Key'
    ];
}
if (empty($keysByProvider['openrouter']) && !empty($settings['openrouter_api_key'])) {
    $keysByProvider['openrouter'][] = [
        'id' => null,
        'provider' => 'openrouter',
        'api_key' => $settings['openrouter_api_key'],
        'model' => $settings['openrouter_model'] ?? 'meta-llama/llama-3.3-70b-instruct:free',
        'label' => 'Chatbot Settings OpenRouter Key'
    ];
}

$hasKeys = !empty($keysByProvider['gemini']) || !empty($keysByProvider['groq']) || !empty($keysByProvider['openrouter']);
if (!$hasKeys) {
    json_response([
        'ok' => false,
        'needs_credentials' => true,
        'error' => 'No active AI API keys configured. Please add an API key under AI Provider Keys (/ai-credentials) first.'
    ], 400);
}

// 3. Tone instructions
$toneInstructions = match ($tone) {
    'friendly_hinglish' => 'Warm, conversational, and naturally bilingual in English and friendly everyday Hinglish. Welcoming with appropriate WhatsApp emojis.',
    'formal_authoritative' => 'Polished, courteous, and authoritative business vocabulary without unnecessary colloquialisms.',
    'concise_direct' => 'Crisp, ultra-concise, and straight to the point. No conversational filler; direct actionable points.',
    default => 'Professional yet warm, helpful, empathetic, and approachable with clean WhatsApp formatting.'
};

// 4. Salutation instruction using ONLY user-defined Owner Name / Salute
$targetOwner = ($ownerName !== '') ? $ownerName : 'the owner';
$addressDirective = ($ownerName !== '') ? "Address the owner directly as \"{$ownerName}\"." : "Address the owner respectfully.";

// 5. Build Meta-Prompt based on Operating Mode
if ($agentMode === 'personal') {
    $metaPrompt = <<<PROMPT
You are a professional prompt writer. Convert the user's raw notes into a clean, concise System Prompt for their WhatsApp Personal Assistant.

[USER NOTES]
{$rawText}

[OWNER NAME / SALUTE]
{$targetOwner} ({$addressDirective})

[TONE]
{$toneInstructions}

[TASK]
Write a high quality system prompt for the assistant based strictly on the user notes above.

Structure the prompt cleanly:
1. Persona statement: "You are the dedicated WhatsApp Personal Assistant for {$targetOwner}."
2. Responsibilities: Take ONLY the specific tasks from [USER NOTES] and format them into clear bullet points.
3. Guidelines & Style: Apply the requested tone and standard WhatsApp formatting (*bold* for emphasis, clean single-line bullets).

STRICT CONSTRAINTS:
- DO NOT invent or assume any extra tasks, services, or routines not found in [USER NOTES].
- DO NOT include meta-commentary, prompt engineering rules, or warnings (NEVER write phrases like "No Hard-Coded Names", "Anti-hallucination", "Scope enforcement", or warnings to the user).
- Output ONLY the final system prompt text. No markdown code blocks (no ```).
PROMPT;
} else {
    $metaPrompt = <<<PROMPT
You are a professional prompt writer. Convert the business owner's raw notes into a clean, concise System Prompt for their WhatsApp Customer Support Assistant.

[USER NOTES]
{$rawText}

[TONE]
{$toneInstructions}

[TASK]
Write a high quality system prompt for the support bot based strictly on the user notes above.

Structure the prompt cleanly:
1. Persona statement: State the assistant is the customer support assistant for the business.
2. Responsibilities: Structure ONLY the facts, policies, and instructions from [USER NOTES] into clear bullet points. If information is not covered in the notes, instruct the bot to politely offer human support.
3. Communication & Formatting: Apply the requested tone and standard WhatsApp formatting (*bold* for emphasis, clean single-line bullets).

STRICT CONSTRAINTS:
- DO NOT invent or assume products, prices, warranties, or services not found in [USER NOTES].
- DO NOT include meta-commentary, prompt engineering rules, or warnings (NEVER write phrases like "No Hard-Coded Names", "Zero hallucination", or prompt disclaimers).
- Output ONLY the final system prompt text. No markdown code blocks (no ```).
PROMPT;
}

// 6. Determine Provider Order based on User Priority
$priority = strtolower(trim((string) ($settings['provider_priority'] ?? 'gemini')));
$providerOrder = match ($priority) {
    'groq' => ['groq', 'gemini', 'openrouter'],
    'openrouter' => ['openrouter', 'gemini', 'groq'],
    default => ['gemini', 'groq', 'openrouter']
};

$enhanced = null;
$usedProvider = null;
$lastError = null;

foreach ($providerOrder as $prov) {
    if ($enhanced !== null) break;
    $keys = $keysByProvider[$prov] ?? [];
    if (empty($keys)) continue;

    foreach ($keys as $kRecord) {
        $apiKey = decrypt_credential(trim((string) ($kRecord['api_key'] ?? '')));
        if ($apiKey === '') continue;

        if ($prov === 'gemini') {
            $candModel = preg_replace('/^models\//', '', trim((string) ($kRecord['model'] ?? ($settings['gemini_model'] ?? '')))) ?: (config_env('DEFAULT_GEMINI_MODEL') ?: 'gemini-2.5-flash');
            try {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$candModel}:generateContent?key={$apiKey}";
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 20,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                    CURLOPT_POSTFIELDS => json_encode([
                        'contents' => [
                            ['parts' => [['text' => $metaPrompt]]]
                        ],
                        'generationConfig' => [
                            'temperature' => 0.2,
                            'maxOutputTokens' => 1000,
                        ]
                    ])
                ]);
                $resp = curl_exec($ch);
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlErr = curl_error($ch);
                curl_close($ch);

                if ($code === 200 && $resp) {
                    $data = json_decode((string) $resp, true);
                    $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    if (!empty(trim((string) $text))) {
                        $enhanced = trim((string) $text);
                        $usedProvider = "Google Gemini ({$candModel})";
                        if (!empty($kRecord['id'])) {
                            $pdo->prepare('UPDATE ai_provider_keys SET status = "active", last_error = NULL, last_used_at = NOW(), fail_count = 0 WHERE id = ? AND user_id = ?')->execute([(int) $kRecord['id'], $userId]);
                        }
                        break 2;
                    }
                } else {
                    $lastError = "Gemini [{$code}]: " . ($curlErr ?: substr((string) $resp, 0, 150));
                    if (!empty($kRecord['id'])) {
                        $pdo->prepare('UPDATE ai_provider_keys SET status = "error", last_error = ?, fail_count = fail_count + 1 WHERE id = ? AND user_id = ?')->execute([substr($lastError, 0, 255), (int) $kRecord['id'], $userId]);
                    }
                }
            } catch (\Throwable $e) {
                $lastError = "Gemini exception: " . $e->getMessage();
            }
        } elseif ($prov === 'groq') {
            $candModel = trim((string) ($kRecord['model'] ?? ($settings['groq_model'] ?? ''))) ?: (config_env('DEFAULT_GROQ_MODEL') ?: 'llama-3.3-70b-versatile');
            try {
                $url = 'https://api.groq.com/openai/v1/chat/completions';
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 20,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/json',
                        "Authorization: Bearer {$apiKey}"
                    ],
                    CURLOPT_POSTFIELDS => json_encode([
                        'model' => $candModel,
                        'messages' => [
                            ['role' => 'user', 'content' => $metaPrompt]
                        ],
                        'temperature' => 0.2,
                        'max_tokens' => 1000
                    ])
                ]);
                $resp = curl_exec($ch);
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlErr = curl_error($ch);
                curl_close($ch);

                if ($code === 200 && $resp) {
                    $data = json_decode((string) $resp, true);
                    $text = $data['choices'][0]['message']['content'] ?? '';
                    if (!empty(trim((string) $text))) {
                        $enhanced = trim((string) $text);
                        $usedProvider = "Groq ({$candModel})";
                        if (!empty($kRecord['id'])) {
                            $pdo->prepare('UPDATE ai_provider_keys SET status = "active", last_error = NULL, last_used_at = NOW(), fail_count = 0 WHERE id = ? AND user_id = ?')->execute([(int) $kRecord['id'], $userId]);
                        }
                        break 2;
                    }
                } else {
                    $lastError = "Groq [{$code}]: " . ($curlErr ?: substr((string) $resp, 0, 150));
                    if (!empty($kRecord['id'])) {
                        $pdo->prepare('UPDATE ai_provider_keys SET status = "error", last_error = ?, fail_count = fail_count + 1 WHERE id = ? AND user_id = ?')->execute([substr($lastError, 0, 255), (int) $kRecord['id'], $userId]);
                    }
                }
            } catch (\Throwable $e) {
                $lastError = "Groq exception: " . $e->getMessage();
            }
        } elseif ($prov === 'openrouter') {
            $candModel = trim((string) ($kRecord['model'] ?? ($settings['openrouter_model'] ?? ''))) ?: (config_env('DEFAULT_OPENROUTER_MODEL') ?: 'meta-llama/llama-3.3-70b-instruct');
            try {
                $url = 'https://openrouter.ai/api/v1/chat/completions';
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 20,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/json',
                        "Authorization: Bearer {$apiKey}",
                        'HTTP-Referer: ' . base_url(''),
                        'X-Title: WhatsGateway Prompt Enhancer'
                    ],
                    CURLOPT_POSTFIELDS => json_encode([
                        'model' => $candModel,
                        'messages' => [
                            ['role' => 'user', 'content' => $metaPrompt]
                        ],
                        'temperature' => 0.2,
                        'max_tokens' => 1000
                    ])
                ]);
                $resp = curl_exec($ch);
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlErr = curl_error($ch);
                curl_close($ch);

                if ($code === 200 && $resp) {
                    $data = json_decode((string) $resp, true);
                    $text = $data['choices'][0]['message']['content'] ?? '';
                    if (!empty(trim((string) $text))) {
                        $enhanced = trim((string) $text);
                        $usedProvider = "OpenRouter ({$candModel})";
                        if (!empty($kRecord['id'])) {
                            $pdo->prepare('UPDATE ai_provider_keys SET status = "active", last_error = NULL, last_used_at = NOW(), fail_count = 0 WHERE id = ? AND user_id = ?')->execute([(int) $kRecord['id'], $userId]);
                        }
                        break 2;
                    }
                } else {
                    $lastError = "OpenRouter [{$code}]: " . ($curlErr ?: substr((string) $resp, 0, 150));
                    if (!empty($kRecord['id'])) {
                        $pdo->prepare('UPDATE ai_provider_keys SET status = "error", last_error = ?, fail_count = fail_count + 1 WHERE id = ? AND user_id = ?')->execute([substr($lastError, 0, 255), (int) $kRecord['id'], $userId]);
                    }
                }
            } catch (\Throwable $e) {
                $lastError = "OpenRouter exception: " . $e->getMessage();
            }
        }
    }
}

// 7. Failover or Format Output
if (empty($enhanced)) {
    json_response([
        'ok' => false,
        'error' => 'AI Rewrite request failed: ' . ($lastError ?: 'Rate limit or connection issue. Please verify your keys in /ai-credentials.')
    ], 502);
}

// Clean any accidental markdown fence wrappers
$enhanced = preg_replace('/^```(?:markdown|text)?\s*/i', '', $enhanced);
$enhanced = preg_replace('/\s*```$/', '', $enhanced);

// Filter out any leaked prompt-engineering warnings, meta-rules, or disclaimers
$enhanced = preg_replace('/^.*(?:no\s+hard[- ]?coded\s+names?|zero\s+task\s+hallucination|scope\s+enforcement|critical\s+rules|strict\s+constraints).*$/mi', '', $enhanced);
$enhanced = preg_replace('/^[ \t]*[\r\n]+/m', "\n", $enhanced);
$enhanced = trim($enhanced);

json_response([
    'ok' => true,
    'enhanced_prompt' => $enhanced,
    'provider' => $usedProvider
]);
