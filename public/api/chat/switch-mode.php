<?php

declare(strict_types=1);

require_once __DIR__ . '/_auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'Method not allowed. Use POST.'], 405);
}

$user = get_chat_authenticated_user();
$userId = (int) $user['id'];
$pdo = Database::pdo();

$data = request_json();
if (empty($data)) {
    $data = $_POST;
}

$targetMode = strtolower(trim((string) ($data['mode'] ?? 'personal')));
if (!in_array($targetMode, ['personal', 'support'], true)) {
    $targetMode = 'personal';
}

$applyPrompt = isset($data['apply_prompt']) ? (bool) $data['apply_prompt'] : true;
$ownerName = trim((string) ($data['owner_name'] ?? 'Boss')) ?: 'Boss';

$personalPromptPreset = "You are the dedicated, highly capable Personal AI Executive Assistant & Chief of Staff to your Boss.

🎯 CORE RESPONSIBILITIES & DIRECTIVES:
1. Executive Role: You work directly and exclusively for your Boss. Address them respectfully as \"" . $ownerName . "\" or \"Sir\". Be proactive, loyal, dependable, and efficient.
2. 24/7 Availability: You are active 24/7 around the clock. You have no office hour restrictions or closed periods.
3. Unrestricted General Intelligence: Assist with ANY request across:
   • Daily task management, to-do lists, reminders, notes, and schedules.
   • Live web search and fact verification (using the \"web_search\" tool).
   • Real-time weather checks and forecasts (using the \"get_weather\" tool).
   • Precise math, conversion, financial, and formula calculations (using the \"evaluate_math\" tool).
   • Current time, date, and calendar verification (using the \"get_current_time\" tool).
   • Writing, copywriting, proofreading, emails, and WhatsApp message drafting.
   • Software engineering, coding, debugging, architecture, and technology troubleshooting.
   • Creative brainstorming, strategy, analysis, and executive summaries.
4. Autonomous Function Calling: Proactively trigger the relevant tools whenever real-time facts, calculations, weather, or web lookups are needed. Never guess or hallucinate when tools can confirm.
5. NO Feedback Surveys: You are a personal assistant, NOT a customer service line. NEVER request customer ratings, 1-5 star reviews, or feedback surveys.
6. ZERO HUMAN ESCALATION: You are Boss's 24/7 dedicated Personal Executive Assistant. There is NO customer support desk, external agent, or ticket queue. NEVER offer to transfer, connect, or escalate the conversation to a human agent, manager, or representative. NEVER call any escalation tool. If Boss asks for an agent or asks to escalate, inform Boss directly and politely that you are their private personal AI assistant and handle all tasks directly for them without human escalation.
7. WhatsApp Style: Keep messages structured, bold key phrases (*bold*), use clean bullet points, and add natural emojis. Match your Boss's preferred language (English, Hindi, or Hinglish).";

$supportPromptPreset = "You are the friendly, professional, and knowledgeable AI Support Assistant for our business on WhatsApp.

🎯 CORE RESPONSIBILITIES:
1. Customer Service: Warmly answer customer questions about our services, products, pricing, working hours, and policies.
2. Verified Information: Ground all factual answers in our official knowledge base. If specific information is unavailable, politely offer to connect the customer with our human support team.
3. Tone & Fluency: Polite, courteous, and helpful. Mirror the customer's language naturally (English, Hindi, or Hinglish).
4. WhatsApp Format: Use clean bullet points, bold key highlights (*bold*), and appropriate emojis. Avoid long walls of text.
5. Operating Hours: Respect official working hours and business policies.";

$chosenPrompt = ($targetMode === 'personal') ? $personalPromptPreset : $supportPromptPreset;
$feedbackEnabled = ($targetMode === 'personal') ? 0 : 1;
$isAlwaysOn = 1;

// Upsert into ai_agent_settings
$checkStmt = $pdo->prepare('SELECT id, system_prompt FROM ai_agent_settings WHERE user_id = ? LIMIT 1');
$checkStmt->execute([$userId]);
$existing = $checkStmt->fetch();

if ($existing) {
    if ($applyPrompt) {
        $updateStmt = $pdo->prepare(
            'UPDATE ai_agent_settings
             SET enabled = 1, auto_reply_enabled = 1, agent_mode = ?, feedback_enabled = ?, is_always_on = ?, owner_name = ?, system_prompt = ?
             WHERE user_id = ?'
        );
        $updateStmt->execute([$targetMode, $feedbackEnabled, $isAlwaysOn, $ownerName, $chosenPrompt, $userId]);
    } else {
        $updateStmt = $pdo->prepare(
            'UPDATE ai_agent_settings
             SET enabled = 1, auto_reply_enabled = 1, agent_mode = ?, feedback_enabled = ?, is_always_on = ?, owner_name = ?
             WHERE user_id = ?'
        );
        $updateStmt->execute([$targetMode, $feedbackEnabled, $isAlwaysOn, $ownerName, $userId]);
        $chosenPrompt = $existing['system_prompt'] ?? $chosenPrompt;
    }
} else {
    $insertStmt = $pdo->prepare(
        'INSERT INTO ai_agent_settings (user_id, enabled, provider_priority, gemini_model, system_prompt, auto_reply_enabled, agent_mode, feedback_enabled, is_always_on, owner_name)
         VALUES (?, 1, \'gemini\', \'gemini-3.5-flash\', ?, 1, ?, ?, ?, ?)'
    );
    $insertStmt->execute([$userId, $chosenPrompt, $targetMode, $feedbackEnabled, $isAlwaysOn, $ownerName]);
}

// Start fresh session tickets for all conversations of this user to flush out previous mode history
$newSessionPrefix = 'SES-' . date('Ymd') . '-';
$convStmt = $pdo->prepare('SELECT id FROM chat_conversations WHERE user_id = ?');
$convStmt->execute([$userId]);
$userConvs = $convStmt->fetchAll();

foreach ($userConvs as $c) {
    $freshUuid = $newSessionPrefix . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
    if ($targetMode === 'personal') {
        $pdo->prepare('UPDATE chat_conversations SET session_uuid = ?, consecutive_bot_replies = 0, awaiting_feedback = 0, status = "open", bot_paused = 0, escalation_reason = NULL WHERE id = ?')
            ->execute([$freshUuid, (int)$c['id']]);
    } else {
        $pdo->prepare('UPDATE chat_conversations SET session_uuid = ?, consecutive_bot_replies = 0, awaiting_feedback = 0 WHERE id = ?')
            ->execute([$freshUuid, (int)$c['id']]);
    }
}

if ($targetMode === 'personal') {
    // Auto-resolve any active escalations in chat_escalations
    $pdo->prepare("UPDATE chat_escalations SET status = 'resolved', resolved_at = NOW(), resolved_by = 'System (Mode Switch)' WHERE user_id = ? AND status = 'active'")
        ->execute([$userId]);
}

json_response([
    'ok' => true,
    'mode' => $targetMode,
    'feedback_enabled' => $feedbackEnabled,
    'is_always_on' => $isAlwaysOn,
    'owner_name' => $ownerName,
    'system_prompt' => $chosenPrompt,
    'message' => ($targetMode === 'personal')
        ? '⚡ Successfully switched to Personal AI Assistant! 24/7 always on & feedback reviews closed.'
        : '🏢 Successfully switched to Business Customer Support Bot mode.'
]);
