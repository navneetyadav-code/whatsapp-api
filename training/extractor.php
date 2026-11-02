<?php

declare(strict_types=1);

/**
 * Universal WhatsApp AI Training Dataset Extractor
 * 
 * Extracts real user inbound messages and corresponding AI assistant replies
 * from the database and formats them into industry-standard fine-tuning datasets
 * (OpenAI/Gemini JSONL, Alpaca, ShareGPT, CSV, Text) without creating multiple temp files.
 * 
 * CLI Usage:
 *   php training/extractor.php
 *   php training/extractor.php --user="Sanjeet Yadav"
 *   php training/extractor.php --user="Sanjeet Yadav" --format=chat --mode=pairs
 *   php training/extractor.php --user="Sanjeet Yadav" --format=sharegpt --mode=multi_turn
 *   php training/extractor.php --all --format=chat
 */

require_once dirname(__DIR__) . '/src/bootstrap.php';

final class AiTrainingExtractor
{
    private PDO $pdo;
    private string $outputDir;

    public function __construct()
    {
        $this->pdo = Database::pdo();
        $this->outputDir = dirname(__DIR__) . '/training/output';
        if (!is_dir($this->outputDir)) {
            mkdir($this->outputDir, 0755, true);
        }
    }

    /**
     * Resolve target user IDs from name, email, or query string.
     */
    public function resolveUser(string $query = 'Sanjeet Yadav'): array
    {
        $query = trim($query);
        if ($query === '' || strtolower($query) === 'all') {
            return ['id' => null, 'name' => 'All Users', 'ids' => []];
        }

        // 1. Search users table
        $stmt = $this->pdo->prepare('SELECT id, name, email FROM users WHERE name LIKE ? OR email LIKE ? LIMIT 10');
        $like = "%{$query}%";
        $stmt->execute([$like, $like]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($users)) {
            $ids = array_map(fn($u) => (int) $u['id'], $users);
            return [
                'id' => $ids[0],
                'name' => $users[0]['name'] ?: $users[0]['email'],
                'ids' => $ids,
                'matched' => $users
            ];
        }

        // 2. Search contacts or conversations
        $cStmt = $this->pdo->prepare('SELECT DISTINCT user_id, contact_name, phone FROM chat_conversations WHERE contact_name LIKE ? OR phone LIKE ? LIMIT 10');
        $cStmt->execute([$like, $like]);
        $convs = $cStmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($convs)) {
            $ids = array_values(array_unique(array_filter(array_map(fn($c) => (int) $c['user_id'], $convs))));
            return [
                'id' => $ids[0] ?? null,
                'name' => $convs[0]['contact_name'] ?: $query,
                'ids' => $ids,
                'matched' => $convs
            ];
        }

        return ['id' => null, 'name' => $query, 'ids' => []];
    }

    /**
     * Retrieve the system prompt configured for this user.
     */
    public function getSystemPrompt(?int $userId): string
    {
        if (!$userId) {
            return "You are a professional, helpful, and reliable AI Assistant on WhatsApp.";
        }
        $stmt = $this->pdo->prepare('SELECT system_prompt FROM ai_agent_settings WHERE user_id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $prompt = trim((string) ($row['system_prompt'] ?? ''));
        return $prompt ?: "You are a professional, helpful, and reliable AI Assistant on WhatsApp.";
    }

    /**
     * Extract raw message sequences from conversations.
     */
    public function fetchConversations(?array $userIds = null, ?string $phone = null): array
    {
        $params = [];
        $where = ["cm.body IS NOT NULL AND TRIM(cm.body) != ''"];

        if (!empty($userIds)) {
            $inClause = implode(',', array_fill(0, count($userIds), '?'));
            $where[] = "cm.user_id IN ({$inClause})";
            $params = array_merge($params, $userIds);
        }

        if ($phone) {
            $where[] = "cc.phone LIKE ?";
            $params[] = "%{$phone}%";
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT cm.id, cm.user_id, cm.conversation_id, cm.sender_type, cm.sender_name, cm.body, cm.created_at,
                       cc.contact_name, cc.phone AS contact_phone, u.name AS account_name
                FROM chat_messages cm
                LEFT JOIN chat_conversations cc ON cc.id = cm.conversation_id
                LEFT JOIN users u ON u.id = cm.user_id
                WHERE {$whereSql}
                ORDER BY cm.conversation_id ASC, cm.id ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Group by conversation
        $grouped = [];
        foreach ($messages as $m) {
            $cid = (int) $m['conversation_id'];
            if (!isset($grouped[$cid])) {
                $grouped[$cid] = [
                    'conversation_id' => $cid,
                    'user_id' => (int) $m['user_id'],
                    'account_name' => $m['account_name'] ?? 'User',
                    'contact_name' => $m['contact_name'] ?? '',
                    'contact_phone' => $m['contact_phone'] ?? '',
                    'messages' => []
                ];
            }
            $grouped[$cid]['messages'][] = $m;
        }

        return array_values($grouped);
    }

    /**
     * Parse message stream into Q&A Pairs (User -> AI).
     */
    public function extractPairs(array $conversations): array
    {
        $pairs = [];

        foreach ($conversations as $conv) {
            $messages = $conv['messages'];
            $currentUserBatch = [];

            foreach ($messages as $msg) {
                $type = strtolower(trim((string) $msg['sender_type']));
                $body = trim((string) $msg['body']);
                if ($body === '') continue;

                // Incoming user message (contact or user)
                if (in_array($type, ['contact', 'user', 'customer', 'client'], true)) {
                    $currentUserBatch[] = [
                        'id' => (int) $msg['id'],
                        'body' => $body,
                        'name' => $msg['sender_name'] ?: ($conv['contact_name'] ?: 'User'),
                        'time' => $msg['created_at']
                    ];
                }
                // Outgoing AI assistant reply
                elseif (in_array($type, ['bot', 'assistant'], true)) {
                    if (!empty($currentUserBatch)) {
                        // Consolidate batch if user sent consecutive quick lines
                        $userText = implode("\n", array_map(fn($u) => $u['body'], $currentUserBatch));
                        $firstUser = $currentUserBatch[0];

                        $pairs[] = [
                            'conversation_id' => $conv['conversation_id'],
                            'user_id' => $conv['user_id'],
                            'account_name' => $conv['account_name'],
                            'contact_name' => $conv['contact_name'],
                            'contact_phone' => $conv['contact_phone'],
                            'user_message' => $userText,
                            'ai_reply' => $body,
                            'user_time' => $firstUser['time'],
                            'ai_time' => $msg['created_at']
                        ];

                        $currentUserBatch = [];
                    }
                }
            }
        }

        return $pairs;
    }

    /**
     * Export extracted data to specified format.
     */
    public function export(array $conversations, array $pairs, string $format = 'chat', string $mode = 'pairs', ?string $customOutput = null, ?string $systemPrompt = null): array
    {
        $format = strtolower($format);
        $mode = strtolower($mode);

        $slug = !empty($conversations[0]['account_name']) ? preg_replace('/[^a-z0-9]+/i', '_', strtolower($conversations[0]['account_name'])) : 'whatsapp_ai';
        $slug = trim($slug, '_') ?: 'whatsapp_ai';

        $ext = match ($format) {
            'csv' => 'csv',
            'text', 'txt' => 'txt',
            'alpaca' => 'json',
            default => 'jsonl'
        };

        $filename = $customOutput ?: "{$this->outputDir}/training_{$slug}_{$mode}_{$format}.{$ext}";
        $resolvedSystemPrompt = $systemPrompt ?: $this->getSystemPrompt($conversations[0]['user_id'] ?? null);

        $linesCount = 0;

        if ($format === 'csv') {
            $fp = fopen($filename, 'w');
            fputcsv($fp, ['conversation_id', 'account_name', 'contact_name', 'contact_phone', 'user_message', 'ai_reply', 'user_time', 'ai_time']);
            foreach ($pairs as $p) {
                fputcsv($fp, [
                    $p['conversation_id'],
                    $p['account_name'],
                    $p['contact_name'],
                    $p['contact_phone'],
                    $p['user_message'],
                    $p['ai_reply'],
                    $p['user_time'],
                    $p['ai_time']
                ]);
                $linesCount++;
            }
            fclose($fp);
        } elseif ($format === 'text' || $format === 'txt') {
            $content = "# WhatsApp AI Training Dialogue Export\n";
            $content .= "# Target: {$slug} | Date: " . date('Y-m-d H:i:s') . "\n";
            $content .= "# System Prompt: {$resolvedSystemPrompt}\n";
            $content .= str_repeat('=', 80) . "\n\n";

            foreach ($pairs as $idx => $p) {
                $num = $idx + 1;
                $content .= "=== Turn #{$num} (Conv ID: {$p['conversation_id']} | {$p['user_time']}) ===\n";
                $content .= "USER [{$p['contact_name']}]:\n{$p['user_message']}\n\n";
                $content .= "AI ASSISTANT:\n{$p['ai_reply']}\n\n";
                $content .= str_repeat('-', 80) . "\n\n";
                $linesCount++;
            }
            file_put_contents($filename, $content);
        } elseif ($format === 'alpaca') {
            $alpacaRecords = [];
            foreach ($pairs as $p) {
                $alpacaRecords[] = [
                    'instruction' => $p['user_message'],
                    'input' => '',
                    'output' => $p['ai_reply'],
                    'system' => $resolvedSystemPrompt
                ];
                $linesCount++;
            }
            file_put_contents($filename, json_encode($alpacaRecords, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        } elseif ($format === 'sharegpt') {
            $shareGptRecords = [];
            if ($mode === 'multi_turn') {
                foreach ($conversations as $conv) {
                    $convTurns = [
                        ['from' => 'system', 'value' => $resolvedSystemPrompt]
                    ];
                    $hasTurns = false;
                    foreach ($conv['messages'] as $m) {
                        $type = strtolower(trim((string)$m['sender_type']));
                        $body = trim((string)$m['body']);
                        if ($body === '') continue;

                        if (in_array($type, ['contact', 'user', 'customer', 'client'], true)) {
                            $convTurns[] = ['from' => 'human', 'value' => $body];
                            $hasTurns = true;
                        } elseif (in_array($type, ['bot', 'assistant'], true)) {
                            $convTurns[] = ['from' => 'gpt', 'value' => $body];
                            $hasTurns = true;
                        }
                    }
                    if ($hasTurns) {
                        $shareGptRecords[] = ['conversations' => $convTurns];
                        $linesCount++;
                    }
                }
            } else {
                foreach ($pairs as $p) {
                    $shareGptRecords[] = [
                        'conversations' => [
                            ['from' => 'system', 'value' => $resolvedSystemPrompt],
                            ['from' => 'human', 'value' => $p['user_message']],
                            ['from' => 'gpt', 'value' => $p['ai_reply']]
                        ]
                    ];
                    $linesCount++;
                }
            }
            file_put_contents($filename, json_encode($shareGptRecords, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        } else {
            // Default: Standard OpenAI / Gemini / LLaMA Chat JSONL format
            $fileHandle = fopen($filename, 'w');
            if ($mode === 'multi_turn') {
                foreach ($conversations as $conv) {
                    $chatMessages = [
                        ['role' => 'system', 'content' => $resolvedSystemPrompt]
                    ];
                    $hasValidExchange = false;
                    foreach ($conv['messages'] as $m) {
                        $type = strtolower(trim((string)$m['sender_type']));
                        $body = trim((string)$m['body']);
                        if ($body === '') continue;

                        if (in_array($type, ['contact', 'user', 'customer', 'client'], true)) {
                            $chatMessages[] = ['role' => 'user', 'content' => $body];
                        } elseif (in_array($type, ['bot', 'assistant'], true)) {
                            $chatMessages[] = ['role' => 'assistant', 'content' => $body];
                            $hasValidExchange = true;
                        }
                    }
                    if ($hasValidExchange) {
                        fwrite($fileHandle, json_encode(['messages' => $chatMessages], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n");
                        $linesCount++;
                    }
                }
            } else {
                foreach ($pairs as $p) {
                    $chatLine = [
                        'messages' => [
                            ['role' => 'system', 'content' => $resolvedSystemPrompt],
                            ['role' => 'user', 'content' => $p['user_message']],
                            ['role' => 'assistant', 'content' => $p['ai_reply']]
                        ]
                    ];
                    fwrite($fileHandle, json_encode($chatLine, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n");
                    $linesCount++;
                }
            }
            fclose($fileHandle);
        }

        return [
            'file' => $filename,
            'format' => $format,
            'mode' => $mode,
            'records_count' => $linesCount,
            'file_size' => is_file($filename) ? filesize($filename) : 0
        ];
    }
}

// -----------------------------------------------------------------------------
// CLI EXECUTION CONTROLLER
// -----------------------------------------------------------------------------
if (php_sapi_name() === 'cli' || !isset($_SERVER['HTTP_HOST'])) {
    $options = getopt('', ['user::', 'format::', 'mode::', 'output::', 'phone::', 'all', 'help']);

    if (isset($options['help'])) {
        echo <<<HELP
Universal WhatsApp AI Training Dataset Extractor

Usage:
  php training/extractor.php [options]

Options:
  --user="Name"      Target user/contact name to extract (default: "Sanjeet Yadav")
  --format="type"    Dataset format: chat (jsonl), alpaca (json), sharegpt (json), csv, text (default: chat)
  --mode="type"      Extraction mode: pairs (default) or multi_turn
  --phone="digits"   Filter by contact phone digits
  --output="path"    Custom output filepath (default saves cleanly into training/output/)
  --all              Extract all users and conversations across the entire database
  --help             Show this help message

HELP;
        exit(0);
    }

    $extractor = new AiTrainingExtractor();

    $userQuery = isset($options['all']) ? 'all' : (string) ($options['user'] ?? 'Sanjeet Yadav');
    $format = (string) ($options['format'] ?? 'chat');
    $mode = (string) ($options['mode'] ?? 'pairs');
    $phone = isset($options['phone']) ? (string) $options['phone'] : null;
    $customOutput = isset($options['output']) ? (string) $options['output'] : null;

    echo "\n" . str_repeat('=', 65) . "\n";
    echo "  🚀 WHATSAPP AI TRAINING DATASET EXTRACTOR\n";
    echo str_repeat('=', 65) . "\n";

    echo "🔍 Resolving target: '{$userQuery}'...\n";
    $resolved = $extractor->resolveUser($userQuery);
    $targetName = $resolved['name'];
    $userIds = $resolved['ids'];

    if (!empty($userIds)) {
        echo "✅ Found target: {$targetName} (User ID(s): " . implode(', ', $userIds) . ")\n";
    } elseif (strtolower($userQuery) === 'all') {
        echo "✅ Mode: Extracting ALL users and conversations.\n";
    } else {
        echo "⚠️ Target '{$userQuery}' not found in database. Extracting all available chats...\n";
        $userIds = [];
    }

    echo "📦 Querying conversations & messages...\n";
    $convs = $extractor->fetchConversations($userIds, $phone);
    echo "   Scanned " . count($convs) . " conversation threads.\n";

    $pairs = $extractor->extractPairs($convs);
    echo "   Extracted " . count($pairs) . " clean (User -> AI) dialogue turns.\n";

    if (empty($pairs)) {
        echo "⚠️ No dialogue pairs found to export.\n\n";
        exit(0);
    }

    echo "💾 Generating training dataset [format: {$format}, mode: {$mode}]...\n";
    $res = $extractor->export($convs, $pairs, $format, $mode, $customOutput);

    $kb = round($res['file_size'] / 1024, 2);
    echo "🎉 SUCCESS: Export complete!\n";
    echo "   • Output file: {$res['file']} ({$kb} KB)\n";
    echo "   • Total training examples: {$res['records_count']}\n";
    echo "   • Git status: Safe (auto-ignored in .gitignore)\n";

    echo "\n--- PREVIEW OF EXTRACTED TRAINING PAIRS ---\n";
    $previewLimit = min(2, count($pairs));
    for ($i = 0; $i < $previewLimit; $i++) {
        $p = $pairs[$i];
        echo "Turn #" . ($i + 1) . ":\n";
        echo "  [User]: " . str_replace("\n", " ", substr($p['user_message'], 0, 80)) . "...\n";
        echo "  [AI]  : " . str_replace("\n", " ", substr($p['ai_reply'], 0, 80)) . "...\n\n";
    }

    echo "💡 You can now upload this file to OpenAI, Google Vertex AI / AI Studio, or Unsloth for fine-tuning!\n";
    echo str_repeat('=', 65) . "\n\n";
    exit(0);
}

// -----------------------------------------------------------------------------
// WEB / API HANDLER (When requested via browser/fetch)
// -----------------------------------------------------------------------------
header('Content-Type: application/json; charset=utf-8');

try {
    $extractor = new AiTrainingExtractor();
    $userQuery = trim((string) ($_GET['user'] ?? 'Sanjeet Yadav'));
    $format = trim((string) ($_GET['format'] ?? 'chat'));
    $mode = trim((string) ($_GET['mode'] ?? 'pairs'));
    $phone = isset($_GET['phone']) ? trim((string) $_GET['phone']) : null;

    $resolved = $extractor->resolveUser($userQuery);
    $convs = $extractor->fetchConversations($resolved['ids'], $phone);
    $pairs = $extractor->extractPairs($convs);
    $exportRes = $extractor->export($convs, $pairs, $format, $mode);

    echo json_encode([
        'ok' => true,
        'target' => $resolved['name'],
        'conversations_count' => count($convs),
        'pairs_count' => count($pairs),
        'export' => $exportRes,
        'preview' => array_slice($pairs, 0, 5)
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
