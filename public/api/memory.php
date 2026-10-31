<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$user = current_user();
if (!$user) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authentication required.']);
    exit;
}

$userId = (int) $user['id'];
$pdo = Database::pdo();
$action = (string) ($_GET['action'] ?? ($_POST['action'] ?? 'list'));

try {
    if ($action === 'list') {
        $stmt = $pdo->prepare(
            'SELECT id, contact_phone, memory_type, is_durable, expires_at, memory_key, memory_value, access_count, last_accessed_at, updated_at
             FROM ai_user_memory
             WHERE user_id = ?
             ORDER BY id DESC'
        );
        $stmt->execute([$userId]);
        $memories = $stmt->fetchAll();

        $tStmt = $pdo->prepare(
            'SELECT id, category, user_query, ideal_response, negative_constraint, is_active, created_at
             FROM ai_training_examples
             WHERE user_id = ?
             ORDER BY id DESC'
        );
        $tStmt->execute([$userId]);
        $training = $tStmt->fetchAll();

        echo json_encode([
            'success' => true,
            'memories' => $memories,
            'training' => $training
        ]);
        exit;
    }

    if ($action === 'save_memory') {
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $id = !empty($input['id']) ? (int) $input['id'] : null;
        $key = strtolower(trim(preg_replace('/[^a-z0-9_]/', '_', (string) ($input['key'] ?? ''))));
        $value = trim((string) ($input['value'] ?? ''));
        $contactPhone = trim((string) ($input['contact_phone'] ?? ''));
        $type = in_array($input['type'] ?? '', ['profile', 'fact', 'preference', 'entity', 'rule'], true) ? $input['type'] : 'fact';
        $isDurable = !empty($input['is_durable']) ? 1 : 0;
        $ttlDays = !empty($input['ttl_days']) ? (int) $input['ttl_days'] : ($isDurable ? null : 14);

        if ($key === '' || $value === '') {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'Memory key and value are required.']);
            exit;
        }

        $expiresAt = null;
        if (!$isDurable && $ttlDays > 0) {
            $expiresAt = (new DateTime("+{$ttlDays} days"))->format('Y-m-d H:i:s');
        }

        if ($id) {
            $stmt = $pdo->prepare(
                'UPDATE ai_user_memory
                 SET memory_key = ?, memory_value = ?, memory_type = ?, contact_phone = ?, is_durable = ?, expires_at = ?, updated_at = NOW()
                 WHERE id = ? AND user_id = ?'
            );
            $stmt->execute([$key, $value, $type, $contactPhone, $isDurable, $expiresAt, $id, $userId]);
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO ai_user_memory
                 (user_id, contact_phone, memory_type, is_durable, expires_at, memory_key, memory_value, source_context)
                 VALUES (?, ?, ?, ?, ?, ?, ?, "dashboard_manual")
                 ON DUPLICATE KEY UPDATE
                   memory_value = VALUES(memory_value),
                   memory_type = VALUES(memory_type),
                   is_durable = VALUES(is_durable),
                   expires_at = VALUES(expires_at),
                   updated_at = NOW()'
            );
            $stmt->execute([$userId, $contactPhone, $type, $isDurable, $expiresAt, $key, $value]);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Memory fact saved successfully.'
        ]);
        exit;
    }

    if ($action === 'delete_memory') {
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $id = !empty($input['id']) ? (int) $input['id'] : null;
        $key = trim((string) ($input['key'] ?? ''));

        if ($id) {
            $stmt = $pdo->prepare('DELETE FROM ai_user_memory WHERE id = ? AND user_id = ?');
            $stmt->execute([$id, $userId]);
        } elseif ($key !== '') {
            $phone = trim((string) ($input['contact_phone'] ?? ''));
            $stmt = $pdo->prepare('DELETE FROM ai_user_memory WHERE user_id = ? AND contact_phone = ? AND memory_key = ?');
            $stmt->execute([$userId, $phone, $key]);
        } else {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'Memory ID or Key is required for deletion.']);
            exit;
        }

        echo json_encode(['success' => true, 'message' => 'Memory removed successfully.']);
        exit;
    }

    if ($action === 'save_training') {
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $id = !empty($input['id']) ? (int) $input['id'] : null;
        $query = trim((string) ($input['user_query'] ?? ''));
        $response = trim((string) ($input['ideal_response'] ?? ''));
        $constraint = trim((string) ($input['negative_constraint'] ?? ''));
        $category = trim((string) ($input['category'] ?? 'general'));

        if ($query === '' || $response === '') {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'User query and ideal response are required.']);
            exit;
        }

        if ($id) {
            $stmt = $pdo->prepare(
                'UPDATE ai_training_examples
                 SET user_query = ?, ideal_response = ?, negative_constraint = ?, category = ?
                 WHERE id = ? AND user_id = ?'
            );
            $stmt->execute([$query, $response, $constraint !== '' ? $constraint : null, $category, $id, $userId]);
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO ai_training_examples
                 (user_id, category, user_query, ideal_response, negative_constraint, is_active)
                 VALUES (?, ?, ?, ?, ?, 1)'
            );
            $stmt->execute([$userId, $category, $query, $response, $constraint !== '' ? $constraint : null]);
        }

        echo json_encode(['success' => true, 'message' => 'Training example saved successfully.']);
        exit;
    }

    if ($action === 'delete_training') {
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $id = (int) ($input['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'Invalid training example ID.']);
            exit;
        }

        $stmt = $pdo->prepare('DELETE FROM ai_training_examples WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);

        echo json_encode(['success' => true, 'message' => 'Training example deleted.']);
        exit;
    }

    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Unknown action.']);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
