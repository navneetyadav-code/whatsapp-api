<?php

declare(strict_types=1);

require_once __DIR__ . '/_auth.php';

$user = get_chat_authenticated_user();
$userId = (int) $user['id'];
$pdo = Database::pdo();

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$rawText = trim((string) ($input['raw_text'] ?? ''));
$tone = trim((string) ($input['tone'] ?? 'professional_warm'));

if ($rawText === '') {
    json_response([
        'ok' => false,
        'error' => 'Please provide some rough notes or details about your business first in the text box.'
    ], 422);
}

// Fetch user's active AI settings
$stmt = $pdo->prepare('SELECT * FROM ai_agent_settings WHERE user_id = ? LIMIT 1');
$stmt->execute([$userId]);
$settings = $stmt->fetch() ?: [];

$geminiKey = trim((string) ($settings['gemini_api_key'] ?? ''));
$groqKey = trim((string) ($settings['groq_api_key'] ?? ''));
$openrouterKey = trim((string) ($settings['openrouter_api_key'] ?? ''));

$geminiModel = trim((string) ($settings['gemini_model'] ?? 'gemini-1.5-flash')) ?: 'gemini-1.5-flash';
$groqModel = trim((string) ($settings['groq_model'] ?? 'openai/gpt-oss-120b')) ?: 'openai/gpt-oss-120b';
if (str_contains($groqModel, 'llama-3.3-70b-versatile')) {
    $groqModel = 'openai/gpt-oss-120b';
}
$openrouterModel = trim((string) ($settings['openrouter_model'] ?? 'meta-llama/llama-3.3-70b-instruct:free')) ?: 'meta-llama/llama-3.3-70b-instruct:free';

$priority = $settings['provider_priority'] ?? 'gemini';

// Tone guidance
$toneInstructions = match ($tone) {
    'friendly_hinglish' => 'Warm, highly conversational, and naturally bilingual in English and friendly everyday Hinglish (e.g. "Haanji bilkul!", "Aapka order..."). Extremely welcoming with appropriate WhatsApp emojis.',
    'formal_authoritative' => 'Polished, corporate, courteous, and authoritative. Precise, professional business vocabulary without excessive colloquialisms.',
    'concise_direct' => 'Crisp, ultra-concise, and straight to the point. No fluff or conversational filler; direct actionable answers formatted with short bullet points.',
    default => 'Professional yet warm, helpful, empathetic, and approachable. Seamlessly adapts to customer language (English, Hindi, or natural Hinglish) with clean WhatsApp formatting.'
};

$metaPrompt = <<<PROMPT
You are an expert Enterprise AI Persona & Prompt Engineer specializing in high-performing WhatsApp Customer Support bots.

A business owner has provided rough, informal notes about their business:
"{$rawText}"

Tone requirement: {$toneInstructions}

Your task: Transform these rough notes into a production-grade, authoritative System Role & Persona Description prompt for their WhatsApp AI assistant.

The generated persona prompt MUST strictly include and enforce these enterprise rules:
1. IDENTITY & MISSION: State clearly who the assistant is and the exact brand it represents.
2. STRICT CLOSED-DOMAIN BOUNDARY: Explicitly command the bot to answer ONLY questions related to this business, its services, products, pricing, orders, and policies. It MUST politely refuse unrelated general knowledge, trivia, cooking recipes, or homework, refocusing the customer back to the business.
3. LANGUAGE & REGIONAL FLUENCY: Instruct the bot to detect customer language and mirror their style naturally (fluent in English, Hindi, and natural Hinglish).
4. WHATSAPP FORMATTING: Mandate WhatsApp formatting (*bold* for key terms, clean single-line bullet points, emojis).
5. ESCALATION PROTOCOL: When a question cannot be resolved or if a customer requests human help, provide a clear, courteous offer to connect with a human specialist.

OUTPUT INSTRUCTIONS:
- Output ONLY the finished System Role & Persona prompt text directly.
- Do NOT wrap in markdown code blocks (no ```).
- Do NOT include intro or meta chatter (like "Here is your prompt:").
PROMPT;

$enhanced = null;
$usedProvider = null;

// Determine provider order based on priority
$providers = match ($priority) {
    'groq' => ['groq', 'gemini', 'openrouter'],
    'openrouter' => ['openrouter', 'gemini', 'groq'],
    default => ['gemini', 'groq', 'openrouter']
};

foreach ($providers as $prov) {
    if ($enhanced !== null) break;

    if ($prov === 'groq' && !empty($groqKey)) {
        try {
            $url = 'https://api.groq.com/openai/v1/chat/completions';
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    "Authorization: Bearer {$groqKey}"
                ],
                CURLOPT_POSTFIELDS => json_encode([
                    'model' => $groqModel,
                    'messages' => [
                        ['role' => 'user', 'content' => $metaPrompt]
                    ],
                    'temperature' => 0.4,
                    'max_tokens' => 1200,
                ])
            ]);
            $resp = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($code === 200 && $resp) {
                $data = json_decode($resp, true);
                $text = $data['choices'][0]['message']['content'] ?? '';
                if (!empty(trim($text))) {
                    $enhanced = trim($text);
                    $usedProvider = 'Groq (' . $groqModel . ')';
                }
            }
        } catch (\Throwable $e) {}
    }

    if ($prov === 'gemini' && !empty($geminiKey)) {
        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$geminiModel}:generateContent?key={$geminiKey}";
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_POSTFIELDS => json_encode([
                    'contents' => [
                        ['parts' => [['text' => $metaPrompt]]]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.4,
                        'maxOutputTokens' => 1200,
                    ]
                ])
            ]);
            $resp = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($code === 200 && $resp) {
                $data = json_decode($resp, true);
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                if (!empty(trim($text))) {
                    $enhanced = trim($text);
                    $usedProvider = 'Google Gemini (' . $geminiModel . ')';
                }
            }
        } catch (\Throwable $e) {}
    }

    if ($prov === 'openrouter' && !empty($openrouterKey)) {
        try {
            $url = 'https://openrouter.ai/api/v1/chat/completions';
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    "Authorization: Bearer {$openrouterKey}"
                ],
                CURLOPT_POSTFIELDS => json_encode([
                    'model' => $openrouterModel,
                    'messages' => [
                        ['role' => 'user', 'content' => $metaPrompt]
                    ],
                    'temperature' => 0.4,
                    'max_tokens' => 1200,
                ])
            ]);
            $resp = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($code === 200 && $resp) {
                $data = json_decode($resp, true);
                $text = $data['choices'][0]['message']['content'] ?? '';
                if (!empty(trim($text))) {
                    $enhanced = trim($text);
                    $usedProvider = 'OpenRouter (' . $openrouterModel . ')';
                }
            }
        } catch (\Throwable $e) {}
    }
}

// Fallback: If all LLMs hit rate limit or fail, generate deterministic enterprise persona template
if (empty($enhanced)) {
    $cleanedNotes = rtrim($rawText, " .;\n");
    $enhanced = <<<DEFAULT_PROMPT
IDENTITY & MISSION:
You are the official, friendly, and professional WhatsApp Customer Support Assistant for our business. Your goal is to guide customers, answer inquiries accurately, and deliver a smooth support experience.

BUSINESS OVERVIEW & CORE POLICIES:
{$cleanedNotes}

OPERATING GUIDELINES & BOUNDARIES:
1. STRICT CLOSED-DOMAIN BOUNDARY: Answer ONLY questions regarding our products, services, pricing, business hours, and policies as described above or in our official Knowledge Base. Politely decline all unrelated questions (such as general trivia, cooking recipes, or homework) and guide the customer back to our business.
2. LANGUAGE & TONE: Maintain a warm, helpful, and courteous tone. Seamlessly detect and mirror the customer's language, communicating fluently in English, Hindi, or natural conversational Hinglish.
3. WHATSAPP FORMATTING: Use clear, readable WhatsApp styling (*bold* for highlights, clean single-line bullet points, emojis where appropriate) and keep responses concise and structured.
4. SUPPORT ESCALATION: If a customer requests human assistance or asks a question not covered by our documentation, politely offer to connect them to our human support team.
DEFAULT_PROMPT;
    $usedProvider = 'Template Engine';
}

// Clean any accidental markdown fence wrappers
$enhanced = preg_replace('/^```(?:markdown|text)?\s*/i', '', $enhanced);
$enhanced = preg_replace('/\s*```$/', '', $enhanced);
$enhanced = trim($enhanced);

json_response([
    'ok' => true,
    'enhanced_prompt' => $enhanced,
    'provider' => $usedProvider
]);
