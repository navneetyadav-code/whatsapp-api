<?php

declare(strict_types=1);

/**
 * WhatsGateway — Web Front Controller & Application Entry Point
 *
 * Routes incoming web traffic to public landing pages, authentication flows,
 * or authenticated dashboard views. Handles clean URL rewrites and CSRF-protected
 * state mutations.
 *
 * Architecture & Routing:
 *   - Public routes: home, docs, login, register, forgot-password, google-auth, google-contacts, privacy, terms
 *   - Dashboard sections: dashboard (overview), device (Baileys pairing), message (composer),
 *     queue (delivery monitor), api-keys (credentials), contacts (address book),
 *     campaigns (broadcasts), chatbot (auto-reply rules), webhooks (notifications), audit (logs)
 *   - POST handlers: credentials, api-key-status, api-key-limits, api-key-delete,
 *     message-send, message-retry, message-cancel, contact-create, contact-import,
 *     contact-delete, campaign-create, chatbot-create, chatbot-status, chatbot-delete, webhook, logout
 */

if (function_exists('opcache_reset')) { opcache_reset(); }

require_once dirname(__DIR__) . '/src/bootstrap.php';

// Determine requested route from clean URL path or fallback to query parameter
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$basePath = rtrim((string) app_config('app.base_path'), '/');
if ($basePath !== '' && str_starts_with($requestUri, $basePath)) {
    $requestUri = substr($requestUri, strlen($basePath));
}
$path = trim($requestUri, '/');
if (str_starts_with($path, 'index.php')) {
    $path = trim(substr($path, 9), '/');
}

if ($path !== '') {
    $page = explode('/', $path)[0];
} elseif (!empty($_GET['page'])) {
    $page = (string) $_GET['page'];
} else {
    $page = current_user() !== null ? 'dashboard' : 'home';
}

// 301 Permanent Redirect for legacy /index.php?page=foo to professional /foo
if ($_SERVER['REQUEST_METHOD'] === 'GET'
    && !empty($_GET['page'])
    && str_contains($_SERVER['REQUEST_URI'] ?? '', 'index.php')
    && !isset($_GET['code'])
    && !isset($_GET['state'])
) {
    $targetPage = (string) $_GET['page'];
    $queryParams = $_GET;
    unset($queryParams['page']);
    $queryString = http_build_query($queryParams);
    $cleanTarget = ($targetPage === 'dashboard' ? '' : $targetPage) . ($queryString !== '' ? '?' . $queryString : '');
    header('HTTP/1.1 301 Moved Permanently');
    redirect_to($cleanTarget);
}

$publicPages = ['login', 'register', 'docs', 'forgot-password', 'google-auth', 'google-contacts', 'privacy', 'privacy-policy', 'terms', 'terms-of-service', 'tos', 'home', 'api', 'send', 'messages', 'api-contacts', 'contacts-api', 'api-contacts-batch', 'contacts-batch', 'api-contacts-delete', 'contacts-delete'];
$dashboardPages = ['dashboard', 'live-chat', 'message', 'device', 'api-keys', 'queue', 'contacts', 'feedback', 'webhooks', 'campaigns', 'audit', 'chatbot', 'security'];

if ($page === 'logout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    logout_user();
    redirect_to('index.php?page=login');
}

if ($page === 'credentials' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();
    $name = trim((string) ($_POST['name'] ?? 'API key'));
    $_SESSION['new_api_credentials'] = create_api_key((int) $user['id'], $name);
    flash('success', 'New API key and secret generated. Copy them now; the secret is shown once.');
    redirect_to('index.php?page=api-keys');
}

if ($page === 'api-key-status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();
    update_api_key_status((int) $user['id'], (int) ($_POST['id'] ?? 0), (string) ($_POST['status'] ?? 'disabled'));
    redirect_to('index.php?page=api-keys');
}

if ($page === 'api-key-limits' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();
    update_api_key_limits((int) $user['id'], (int) ($_POST['id'] ?? 0), $_POST);
    flash('success', 'API key limits updated.');
    redirect_to('index.php?page=api-keys');
}

if ($page === 'api-key-delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();
    delete_api_key((int) $user['id'], (int) ($_POST['id'] ?? 0));
    flash('success', 'API key deleted.');
    redirect_to('index.php?page=api-keys');
}

if ($page === 'message-retry' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();
    retry_message((int) $user['id'], (int) ($_POST['id'] ?? 0));
    redirect_to('index.php?page=queue');
}

if ($page === 'message-send' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();

    $type = $_POST['recipient_mode'] ?? 'number';
    $message = trim((string) ($_POST['message'] ?? ''));
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    if ($message === '') {
        if ($isAjax) {
            json_response(['ok' => false, 'error' => 'Message is required.'], 400);
        }
        flash('error', 'Message is required.');
        redirect_to('index.php?page=message');
    }

    // Secure media upload handling (validated against allowed MIME types, extension whitelisting, and temporary cleanup)
    $payload = [];
    if (!empty($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
        try {
            $saved = save_uploaded_media($_FILES['attachment']);
            $payload['attachment'] = [
                'path' => $saved['path'],
                'name' => $saved['name'],
                'type' => $saved['type'],
                'mime' => $saved['mime'],
            ];
        } catch (Throwable $uploadException) {
            $uploadError = $uploadException->getMessage();
            if ($isAjax) {
                json_response(['ok' => false, 'error' => $uploadError], 422);
            }
            flash('error', $uploadError);
            redirect_to('index.php?page=message');
        }
    }

    if (!empty($_POST['humanize'])) {
        $payload['humanize'] = true;
    }

    try {
        $queuedCount = 0;
        if ($type === 'number') {
            $phone = trim((string) ($_POST['manual_number'] ?? ''));
            if (!$phone) throw new Exception('Phone number required');
            $phone = normalize_phone($phone);
            enqueue_message((int) $user['id'], null, $phone, $message, $payload);
            $queuedCount = 1;
        } elseif ($type === 'contact') {
            $contactId = (int) ($_POST['contact_id'] ?? 0);
            $stmt = Database::pdo()->prepare('SELECT * FROM contacts WHERE id = ? AND user_id = ?');
            $stmt->execute([$contactId, $user['id']]);
            $contact = $stmt->fetch();
            if (!$contact) throw new Exception('Contact not found');
            
            $formattedMsg = apply_contact_template($message, $contact);
            enqueue_message((int) $user['id'], null, $contact['phone'], $formattedMsg, $payload);
            $queuedCount = 1;
        } elseif ($type === 'campaign') {
            $campaignId = (int) ($_POST['campaign_id'] ?? 0);
            $contacts = campaign_contacts((int) $user['id'], $campaignId);
            foreach ($contacts as $contact) {
                $formattedMsg = apply_contact_template($message, $contact);
                enqueue_message((int) $user['id'], null, $contact['phone'], $formattedMsg, $payload);
                $queuedCount++;
            }
        }
        
        if ($isAjax) {
            json_response(['ok' => true, 'message' => "Successfully queued $queuedCount message(s)."]);
        }
        flash('success', "Successfully queued $queuedCount message(s).");
        redirect_to('index.php?page=queue');
        
    } catch (Exception $e) {
        if ($isAjax) {
            json_response(['ok' => false, 'error' => $e->getMessage()], 400);
        }
        flash('error', $e->getMessage());
        redirect_to('index.php?page=message');
    }
}

if ($page === 'message-cancel' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();
    cancel_message((int) $user['id'], (int) ($_POST['id'] ?? 0));
    redirect_to('index.php?page=queue');
}

if ($page === 'contact-create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();
    $stmt = Database::pdo()->prepare(
        'INSERT INTO contacts (user_id, name, phone, opt_in, notes) VALUES (?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE name = VALUES(name), opt_in = VALUES(opt_in), notes = VALUES(notes)'
    );
    $stmt->execute([
        (int) $user['id'],
        trim((string) ($_POST['name'] ?? 'Contact')),
        trim((string) ($_POST['phone'] ?? '')),
        isset($_POST['opt_in']) ? 1 : 0,
        trim((string) ($_POST['notes'] ?? '')) ?: null,
    ]);
    flash('success', 'Contact saved.');
    redirect_to('index.php?page=contacts');
}

if ($page === 'contact-import' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();

    if (!isset($_FILES['contacts_csv']) || $_FILES['contacts_csv']['error'] !== UPLOAD_ERR_OK) {
        flash('error', 'Upload a valid CSV file.');
        redirect_to('index.php?page=contacts');
    }

    // SEC-09: Enforce maximum file size bound (5MB)
    $maxBytes = 5 * 1024 * 1024;
    if ((int) ($_FILES['contacts_csv']['size'] ?? 0) > $maxBytes) {
        flash('error', 'CSV file exceeds maximum limit of 5MB.');
        redirect_to('index.php?page=contacts');
    }

    $handle = fopen($_FILES['contacts_csv']['tmp_name'], 'r');
    if (!$handle) {
        flash('error', 'Could not read the CSV file.');
        redirect_to('index.php?page=contacts');
    }

    $imported = 0;
    $maxRows = 5000;
    $rowCount = 0;
    $rowsToInsert = [];

    while (($row = fgetcsv($handle)) !== false) {
        $rowCount++;
        if ($rowCount > $maxRows) {
            fclose($handle);
            flash('error', "CSV file exceeds maximum limit of {$maxRows} rows.");
            redirect_to('index.php?page=contacts');
        }

        if (count($row) < 2) {
            continue;
        }

        $name = mb_substr(trim((string) $row[0]), 0, 100);
        $phone = substr(preg_replace('/[^\d+]/', '', trim((string) $row[1])), 0, 32);
        if ($name === '' || $phone === '' || strcasecmp($name, 'name') === 0 || strcasecmp($phone, 'phone') === 0) {
            continue;
        }

        $rowsToInsert[] = [
            'name' => $name,
            'phone' => $phone,
            'opt_in' => isset($row[2]) ? ((int) $row[2] ? 1 : 0) : 1,
            'notes' => isset($row[3]) ? (mb_substr(trim((string) $row[3]), 0, 255) ?: null) : null,
        ];
    }
    fclose($handle);

    if (empty($rowsToInsert)) {
        flash('error', 'No valid contact records found in CSV file.');
        redirect_to('index.php?page=contacts');
    }

    $pdo = Database::pdo();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO contacts (user_id, name, phone, opt_in, notes) VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE name = VALUES(name), opt_in = VALUES(opt_in), notes = VALUES(notes)'
        );

        foreach ($rowsToInsert as $record) {
            $stmt->execute([
                (int) $user['id'],
                $record['name'],
                $record['phone'],
                $record['opt_in'],
                $record['notes'],
            ]);
            $imported++;
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        flash('error', 'Database error during contact import: ' . $e->getMessage());
        redirect_to('index.php?page=contacts');
    }

    flash('success', "Imported {$imported} contacts.");
    redirect_to('index.php?page=contacts');
}

if ($page === 'contacts-export-vcf') {
    $user = require_auth();
    $stmt = Database::pdo()->prepare('SELECT name, phone FROM contacts WHERE user_id = ? ORDER BY name ASC');
    $stmt->execute([(int) $user['id']]);

    header('Content-Type: text/vcard; charset=utf-8');
    header('Content-Disposition: attachment; filename="whatsapp-api-contacts.vcf"');
    foreach ($stmt->fetchAll() as $contact) {
        echo "BEGIN:VCARD\r\n";
        echo "VERSION:3.0\r\n";
        echo 'FN:' . str_replace(["\r", "\n"], '', (string) $contact['name']) . "\r\n";
        echo 'TEL;TYPE=CELL:' . preg_replace('/[^\d+]/', '', (string) $contact['phone']) . "\r\n";
        echo "END:VCARD\r\n";
    }
    exit;
}

if ($page === 'contact-delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();
    $stmt = Database::pdo()->prepare('DELETE FROM contacts WHERE id = ? AND user_id = ?');
    $stmt->execute([(int) ($_POST['id'] ?? 0), (int) $user['id']]);
    redirect_to('index.php?page=contacts');
}

if ($page === 'campaign-create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();
    $name = trim((string) ($_POST['name'] ?? 'Campaign'));
    $template = trim((string) ($_POST['message_template'] ?? ''));

    if ($template === '') {
        flash('error', 'Campaign message cannot be empty.');
        redirect_to('index.php?page=campaigns');
    }

    $scheduledAt = null;
    $scheduleInput = trim((string) ($_POST['scheduled_at'] ?? ''));
    if ($scheduleInput !== '') {
        $timestamp = strtotime($scheduleInput);
        if ($timestamp !== false && $timestamp > time()) {
            $scheduledAt = date('Y-m-d H:i:s', $timestamp);
        }
    }

    $attachment = null;
    if (!empty($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
        try {
            $saved = save_uploaded_media($_FILES['attachment']);
            $attachment = [
                'path' => $saved['path'],
                'name' => $saved['name'],
                'type' => $saved['type'],
                'mime' => $saved['mime'],
            ];
        } catch (Throwable $uploadException) {
            flash('error', $uploadException->getMessage());
            redirect_to('index.php?page=campaigns');
        }
    }

    $campaignId = create_campaign((int) $user['id'], $name, $template, $scheduledAt, $attachment);
    if ($scheduledAt) {
        flash('success', "Campaign #{$campaignId} scheduled for " . date('M j, Y g:i A', strtotime($scheduledAt)) . ".");
    } else {
        flash('success', "Campaign #{$campaignId} queued for opted-in contacts.");
    }
    redirect_to('index.php?page=campaigns&tab=ledger');
}

if ($page === 'campaign-pause' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();
    $campaignId = (int) ($_POST['campaign_id'] ?? 0);
    if ($campaignId > 0) {
        pause_campaign((int) $user['id'], $campaignId);
        flash('success', "Campaign #{$campaignId} has been paused.");
    }
    redirect_to('index.php?page=campaigns&tab=ledger');
}

if ($page === 'campaign-resume' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();
    $campaignId = (int) ($_POST['campaign_id'] ?? 0);
    if ($campaignId > 0) {
        resume_campaign((int) $user['id'], $campaignId);
        try {
            worker_request('POST', 'queue/notify', ['user_id' => (int) $user['id']], 1);
        } catch (\Throwable $e) {}
        flash('success', "Campaign #{$campaignId} has been resumed.");
    }
    redirect_to('index.php?page=campaigns&tab=ledger');
}

if ($page === 'campaign-cancel' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();
    $campaignId = (int) ($_POST['campaign_id'] ?? 0);
    if ($campaignId > 0) {
        cancel_campaign((int) $user['id'], $campaignId);
        flash('success', "Campaign #{$campaignId} and all pending dispatches were cancelled.");
    }
    redirect_to('index.php?page=campaigns&tab=ledger');
}

if ($page === 'campaign-resend' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();
    $campaignId = (int) ($_POST['campaign_id'] ?? 0);
    if ($campaignId > 0) {
        $newId = resend_campaign((int) $user['id'], $campaignId);
        if ($newId) {
            flash('success', "Campaign duplicated and queued as #{$newId}.");
        } else {
            flash('error', "Could not resend campaign #{$campaignId}.");
        }
    }
    redirect_to('index.php?page=campaigns&tab=ledger');
}

if ($page === 'campaign-test-send' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();
    $phone = trim((string) ($_POST['test_phone'] ?? ''));
    $testName = trim((string) ($_POST['test_name'] ?? 'Preview Contact'));
    $template = trim((string) ($_POST['message_template'] ?? ''));

    if ($phone === '') {
        flash('error', 'Test recipient phone number is required.');
        redirect_to('index.php?page=campaigns&tab=test');
    }
    if ($template === '') {
        flash('error', 'Message template cannot be empty.');
        redirect_to('index.php?page=campaigns&tab=test');
    }

    $phone = normalize_phone($phone);
    $formattedMsg = apply_contact_template($template, ['name' => $testName, 'phone' => $phone]);

    $payload = [
        'source' => 'campaign_test',
        'type' => 'text',
    ];

    if (!empty($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
        try {
            $saved = save_uploaded_media($_FILES['attachment']);
            $payload['attachment'] = [
                'path' => $saved['path'],
                'name' => $saved['name'],
                'type' => $saved['type'],
                'mime' => $saved['mime'],
            ];
            $payload['type'] = $saved['type'];
        } catch (Throwable $uploadException) {
            flash('error', $uploadException->getMessage());
            redirect_to('index.php?page=campaigns&tab=test');
        }
    }

    try {
        enqueue_message((int) $user['id'], null, $phone, $formattedMsg, $payload, 10);
        try {
            worker_request('POST', 'queue/notify', ['user_id' => (int) $user['id']], 1);
        } catch (\Throwable $e) {}
        flash('success', "Test message dispatched to {$phone} with priority queue.");
    } catch (\Throwable $e) {
        flash('error', 'Failed to dispatch test message: ' . $e->getMessage());
    }
    redirect_to('index.php?page=campaigns&tab=test');
}

if ($page === 'chatbot-create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();
    
    $keyword = trim((string) ($_POST['keyword'] ?? ''));
    $matchType = $_POST['match_type'] ?? 'exact';
    $replyText = trim((string) ($_POST['reply_text'] ?? ''));
    
    if (!in_array($matchType, ['exact', 'contains', 'starts_with', 'catch_all'])) {
        $matchType = 'exact';
    }
    
    if ($matchType !== 'catch_all' && $keyword === '') {
        flash('error', 'Keyword is required unless match type is catch_all.');
        redirect_to('index.php?page=chatbot');
    }
    if ($replyText === '') {
        flash('error', 'Reply text cannot be empty.');
        redirect_to('index.php?page=chatbot');
    }

    $stmt = Database::pdo()->prepare(
        'INSERT INTO chatbot_rules (user_id, keyword, match_type, reply_text) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([(int) $user['id'], $keyword, $matchType, $replyText]);
    
    flash('success', 'Chatbot automation rule created successfully.');
    redirect_to('index.php?page=chatbot');
}

if ($page === 'chatbot-status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();
    $id = (int) ($_POST['id'] ?? 0);
    $status = ($_POST['status'] ?? 'disabled') === 'active' ? 'active' : 'disabled';
    
    $stmt = Database::pdo()->prepare('UPDATE chatbot_rules SET status = ? WHERE id = ? AND user_id = ?');
    $stmt->execute([$status, $id, (int) $user['id']]);
    redirect_to('index.php?page=chatbot');
}

if ($page === 'chatbot-delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();
    $id = (int) ($_POST['id'] ?? 0);
    
    $stmt = Database::pdo()->prepare('DELETE FROM chatbot_rules WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, (int) $user['id']]);
    
    flash('success', 'Chatbot rule deleted.');
    redirect_to('index.php?page=chatbot');
}

if ($page === 'ai-agent-save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();
    $userId = (int) $user['id'];
    $pdo = Database::pdo();

    $enabled = isset($_POST['enabled']) ? 1 : 0;
    $autoReplyEnabled = isset($_POST['auto_reply_enabled']) ? 1 : 0;
    $providerPriority = in_array($_POST['provider_priority'] ?? '', ['gemini', 'groq', 'openrouter'], true) ? $_POST['provider_priority'] : 'gemini';
    $systemPrompt = trim((string) ($_POST['system_prompt'] ?? ''));
    $temperature = max(0.0, min(1.0, (float) ($_POST['temperature'] ?? 0.4)));
    $maxTokens = max(50, min(2000, (int) ($_POST['max_tokens'] ?? 500)));

    $geminiKey = trim((string) ($_POST['gemini_api_key'] ?? ''));
    $groqKey = trim((string) ($_POST['groq_api_key'] ?? ''));
    $openrouterKey = trim((string) ($_POST['openrouter_api_key'] ?? ''));

    $geminiModel = trim((string) ($_POST['gemini_model'] ?? 'gemini-1.5-flash')) ?: 'gemini-1.5-flash';
    $groqModel = trim((string) ($_POST['groq_model'] ?? 'llama-3.3-70b-versatile')) ?: 'llama-3.3-70b-versatile';
    $openrouterModel = trim((string) ($_POST['openrouter_model'] ?? 'meta-llama/llama-3.3-70b-instruct:free')) ?: 'meta-llama/llama-3.3-70b-instruct:free';

    $stmt = $pdo->prepare(
        'INSERT INTO ai_agent_settings (user_id, enabled, provider_priority, gemini_api_key, groq_api_key, openrouter_api_key, gemini_model, groq_model, openrouter_model, system_prompt, temperature, max_tokens, auto_reply_enabled)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
           enabled = VALUES(enabled),
           provider_priority = VALUES(provider_priority),
           gemini_api_key = IF(VALUES(gemini_api_key) != "", VALUES(gemini_api_key), gemini_api_key),
           groq_api_key = IF(VALUES(groq_api_key) != "", VALUES(groq_api_key), groq_api_key),
           openrouter_api_key = IF(VALUES(openrouter_api_key) != "", VALUES(openrouter_api_key), openrouter_api_key),
           gemini_model = VALUES(gemini_model),
           groq_model = VALUES(groq_model),
           openrouter_model = VALUES(openrouter_model),
           system_prompt = VALUES(system_prompt),
           temperature = VALUES(temperature),
           max_tokens = VALUES(max_tokens),
           auto_reply_enabled = VALUES(auto_reply_enabled)'
    );
    $stmt->execute([
        $userId, $enabled, $providerPriority, $geminiKey, $groqKey, $openrouterKey,
        $geminiModel, $groqModel, $openrouterModel, $systemPrompt, $temperature, $maxTokens, $autoReplyEnabled
    ]);

    flash('success', 'AI Agent configuration updated successfully.');
    redirect_to('index.php?page=chatbot&tab=ai-agent');
}

if ($page === 'ai-knowledge-create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();
    $userId = (int) $user['id'];
    $pdo = Database::pdo();

    $title = trim((string) ($_POST['title'] ?? ''));
    $content = trim((string) ($_POST['content'] ?? ''));

    if ($title === '' || $content === '') {
        flash('error', 'Title and content are required.');
        redirect_to('index.php?page=chatbot&tab=knowledge');
    }

    $stmt = $pdo->prepare('INSERT INTO ai_knowledge_chunks (user_id, title, content, is_active) VALUES (?, ?, ?, 1)');
    $stmt->execute([$userId, $title, $content]);

    flash('success', 'Knowledge chunk added to RAG database.');
    redirect_to('index.php?page=chatbot&tab=knowledge');
}

if ($page === 'ai-knowledge-delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();
    $userId = (int) $user['id'];
    $id = (int) ($_POST['id'] ?? 0);

    $stmt = Database::pdo()->prepare('DELETE FROM ai_knowledge_chunks WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $userId]);

    flash('success', 'Knowledge chunk deleted.');
    redirect_to('index.php?page=chatbot&tab=knowledge');
}

if ($page === 'session-auto-sleep' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();
    $enabled = isset($_POST['auto_sleep_enabled']) ? 1 : 0;
    $rawTimeout = (int) ($_POST['auto_sleep_timeout_seconds'] ?? 60);
    $timeout = max(15, min(86400, $rawTimeout));

    $stmt = Database::pdo()->prepare(
        'UPDATE whatsapp_sessions
         SET auto_sleep_enabled = ?, auto_sleep_timeout_seconds = ?, updated_at = NOW()
         WHERE user_id = ?'
    );
    $stmt->execute([$enabled, $timeout, (int) $user['id']]);

    try {
        worker_request('POST', "sessions/{$user['id']}/auto-sleep", [
            'auto_sleep_enabled' => (bool) $enabled,
            'auto_sleep_timeout_seconds' => $timeout,
        ], 2);
    } catch (\Throwable $e) {}

    flash('success', 'Auto-Sleep settings updated successfully.');
    redirect_to('index.php?page=device');
}

if ($page === 'webhook' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    verify_csrf();
    try {
        $targetUrl = validate_webhook_url($_POST['target_url'] ?? null);
    } catch (InvalidArgumentException $exception) {
        flash('error', $exception->getMessage());
        redirect_to('index.php?page=webhooks');
    }

    $stmtOld = Database::pdo()->prepare('SELECT secret, secret_alt FROM webhooks WHERE user_id = ? LIMIT 1');
    $stmtOld->execute([(int) $user['id']]);
    $current = $stmtOld->fetch() ?: [];

    $secret = trim((string) ($_POST['secret'] ?? '')) ?: null;
    $secretAlt = $current['secret_alt'] ?? null;
    $secretRotatedAt = null;

    if (isset($_POST['rotate_secret']) || (isset($_POST['enabled']) && $targetUrl && !$secret)) {
        $newSecret = bin2hex(random_bytes(32));
        if (!empty($current['secret']) && $current['secret'] !== $newSecret) {
            $secretAlt = $current['secret'];
            $secretRotatedAt = date('Y-m-d H:i:s');
        }
        $secret = $newSecret;
        $_SESSION['new_webhook_secret'] = $secret;
    }

    $stmt = Database::pdo()->prepare(
        'INSERT INTO webhooks (user_id, target_url, secret, secret_alt, secret_rotated_at, enabled) VALUES (?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE target_url = VALUES(target_url), secret = VALUES(secret), secret_alt = VALUES(secret_alt), secret_rotated_at = COALESCE(VALUES(secret_rotated_at), secret_rotated_at), enabled = VALUES(enabled)'
    );
    $stmt->execute([
        (int) $user['id'],
        $targetUrl,
        $secret,
        $secretAlt,
        $secretRotatedAt,
        isset($_POST['enabled']) && $targetUrl ? 1 : 0,
    ]);
    flash('success', 'Webhook settings saved.');
    redirect_to('index.php?page=webhooks');
}

if ($page === 'google-auth') {
    require dirname(__DIR__) . '/src/pages/google-auth.php';
    exit;
}

if ($page === 'google-contacts') {
    require dirname(__DIR__) . '/src/pages/google-contacts.php';
    exit;
}

if ($page === 'forgot-password') {
    require dirname(__DIR__) . '/src/pages/forgot-password.php';
    exit;
}

if ($page === 'privacy' || $page === 'privacy-policy') {
    require dirname(__DIR__) . '/src/pages/privacy.php';
    exit;
}

if ($page === 'terms' || $page === 'terms-of-service' || $page === 'tos') {
    require dirname(__DIR__) . '/src/pages/terms.php';
    exit;
}

if ($page === 'docs' || $page === 'documentation' || $page === 'api-docs') {
    require dirname(__DIR__) . '/src/pages/docs.php';
    exit;
}

if ($page === 'home') {
    require dirname(__DIR__) . '/src/pages/home.php';
    exit;
}

if ($page === 'api' || $page === 'messages' || $page === 'send') {
    require __DIR__ . '/api/send.php';
    exit;
}

if ($page === 'api-contacts' || $page === 'contacts-api') {
    require __DIR__ . '/api/contacts.php';
    exit;
}

if ($page === 'api-contacts-batch' || $page === 'contacts-batch') {
    require __DIR__ . '/api/contacts-batch.php';
    exit;
}

if ($page === 'api-contacts-delete' || $page === 'contacts-delete') {
    require __DIR__ . '/api/contacts-delete.php';
    exit;
}

if (!in_array($page, $publicPages, true)) {
    $user = require_auth();
}

$view = match (true) {
    $page === 'login' => dirname(__DIR__) . '/src/pages/login.php',
    $page === 'register' => dirname(__DIR__) . '/src/pages/register.php',
    $page === 'docs' => dirname(__DIR__) . '/src/pages/docs.php',
    $page === 'security' => dirname(__DIR__) . '/src/pages/security.php',
    in_array($page, $dashboardPages, true) => dirname(__DIR__) . '/src/pages/dashboard.php',
    default => dirname(__DIR__) . '/src/pages/dashboard.php',
};

ob_start();
require $view;
$content = ob_get_clean();

require dirname(__DIR__) . '/src/pages/layout.php';


