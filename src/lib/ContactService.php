<?php

declare(strict_types=1);

/**
 * Class ContactService
 * 
 * Centralized service layer for managing contacts, opt-in consent records,
 * atomic batch ingestion, and privacy-compliant lookups and deletions.
 */
class ContactService
{
    /**
     * Maximum allowed contacts in a single atomic batch operation.
     */
    public const MAX_BATCH_SIZE = 500;

    /**
     * Retrieve paginated contacts directory with optional search and opt-in filtering.
     *
     * @param int $userId Tenant owner ID
     * @param string $search Substring search across name, phone, notes
     * @param int|null $optIn Filter by consent (1 = opted in, 0 = opted out, null = all)
     * @param int $limit Records per page (1 to 250)
     * @param int $offset Records offset
     * @return array{total: int, opted_in_count: int, limit: int, offset: int, contacts: array<int, array>}
     */
    public static function list(
        int $userId,
        string $search = '',
        ?int $optIn = null,
        int $limit = 50,
        int $offset = 0
    ): array {
        $pdo = Database::pdo();
        $limit = min(max($limit, 1), 250);
        $offset = max($offset, 0);

        $where = ['user_id = ?'];
        $params = [$userId];

        if ($optIn !== null) {
            $where[] = 'opt_in = ?';
            $params[] = $optIn;
        }

        $search = mb_substr(trim($search), 0, 100);
        if ($search !== '') {
            $escaped = addcslashes($search, '%_\\');
            $term = '%' . $escaped . '%';
            $where[] = '(name LIKE ? OR phone LIKE ? OR notes LIKE ?)';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereSql = implode(' AND ', $where);

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM contacts WHERE {$whereSql}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $optedStmt = $pdo->prepare('SELECT COUNT(*) FROM contacts WHERE user_id = ? AND opt_in = 1');
        $optedStmt->execute([$userId]);
        $optedTotal = (int) $optedStmt->fetchColumn();

        $selectSql = "SELECT id, user_id, name, phone, normalized_phone, opt_in, notes, created_at, updated_at 
                      FROM contacts 
                      WHERE {$whereSql} 
                      ORDER BY id DESC 
                      LIMIT ? OFFSET ?";

        $fetchParams = array_merge($params, [$limit, $offset]);
        $stmt = $pdo->prepare($selectSql);
        foreach ($fetchParams as $idx => $val) {
            $stmt->bindValue($idx + 1, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($contacts as &$c) {
            $c['id'] = (int) $c['id'];
            $c['user_id'] = (int) $c['user_id'];
            $c['opt_in'] = (int) $c['opt_in'];
        }
        unset($c);

        return [
            'total' => $total,
            'opted_in_count' => $optedTotal,
            'limit' => $limit,
            'offset' => $offset,
            'contacts' => $contacts,
        ];
    }

    /**
     * Retrieve a single contact record by its synthetic database ID.
     */
    public static function getById(int $userId, int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT id, user_id, name, phone, normalized_phone, opt_in, notes, created_at, updated_at 
             FROM contacts 
             WHERE id = ? AND user_id = ? 
             LIMIT 1'
        );
        $stmt->execute([$id, $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        $row['id'] = (int) $row['id'];
        $row['user_id'] = (int) $row['user_id'];
        $row['opt_in'] = (int) $row['opt_in'];
        return $row;
    }

    /**
     * Retrieve a single contact record by its phone number.
     */
    public static function getByPhone(int $userId, string $phone): ?array
    {
        $rawPhone = substr(preg_replace('/[^\d+]/', '', trim($phone)), 0, 32);
        if ($rawPhone === '') {
            return null;
        }

        $normalized = normalize_phone($rawPhone);
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT id, user_id, name, phone, normalized_phone, opt_in, notes, created_at, updated_at 
             FROM contacts 
             WHERE user_id = ? AND (phone = ? OR normalized_phone = ?) 
             LIMIT 1'
        );
        $stmt->execute([$userId, $rawPhone, $normalized]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        $row['id'] = (int) $row['id'];
        $row['user_id'] = (int) $row['user_id'];
        $row['opt_in'] = (int) $row['opt_in'];
        return $row;
    }

    /**
     * Create or update a single contact record (idempotent upsert).
     *
     * @param int $userId Tenant owner ID
     * @param array $data Input payload with name, phone, opt_in, notes
     * @return array Created/updated contact representation
     * @throws InvalidArgumentException When validation fails
     */
    public static function upsert(int $userId, array $data): array
    {
        $rawPhone = substr(preg_replace('/[^\d+]/', '', trim((string) ($data['phone'] ?? ''))), 0, 32);
        if ($rawPhone === '') {
            throw new InvalidArgumentException('Phone number is required.');
        }

        $normalized = normalize_phone($rawPhone);
        if ($normalized === '' || strlen(preg_replace('/\D/', '', $normalized)) < 7) {
            throw new InvalidArgumentException("Invalid phone number format: {$rawPhone}");
        }

        $rawName = mb_substr(trim((string) ($data['name'] ?? 'Contact')), 0, 100);
        if ($rawName === '') {
            $rawName = 'Contact';
        }

        $notes = isset($data['notes']) ? mb_substr(trim((string) $data['notes']), 0, 500) : null;
        $optIn = isset($data['opt_in'])
            ? (filter_var($data['opt_in'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $data['opt_in'] ? 1 : 0)
            : 1;

        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO contacts (user_id, name, phone, normalized_phone, opt_in, notes)
             VALUES (?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                normalized_phone = VALUES(normalized_phone),
                opt_in = VALUES(opt_in),
                notes = VALUES(notes)'
        );
        $stmt->execute([$userId, $rawName, $rawPhone, $normalized, $optIn, $notes]);

        $contact = self::getByPhone($userId, $rawPhone);
        return $contact ?? [];
    }

    /**
     * Ingest an array of contacts inside an atomic database transaction.
     *
     * @param int $userId Tenant owner ID
     * @param array<int, array> $batch Array of contact items
     * @return array{processed: int, contacts: array, errors: array}
     * @throws OverflowException If batch exceeds maximum threshold
     * @throws Throwable If database transaction fails
     */
    public static function batchUpsert(int $userId, array $batch): array
    {
        if (count($batch) > self::MAX_BATCH_SIZE) {
            throw new OverflowException("Batch size exceeds maximum limit of " . self::MAX_BATCH_SIZE . " contacts.");
        }

        $pdo = Database::pdo();
        $upsertStmt = $pdo->prepare(
            'INSERT INTO contacts (user_id, name, phone, normalized_phone, opt_in, notes)
             VALUES (?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                normalized_phone = VALUES(normalized_phone),
                opt_in = VALUES(opt_in),
                notes = VALUES(notes)'
        );

        $processed = 0;
        $errors = [];
        $savedContacts = [];

        if (count($batch) > 1) {
            $pdo->beginTransaction();
        }

        try {
            foreach ($batch as $index => $item) {
                $rawPhone = substr(preg_replace('/[^\d+]/', '', trim((string) ($item['phone'] ?? ''))), 0, 32);
                if ($rawPhone === '') {
                    $errors[] = "Item {$index}: phone number is required.";
                    continue;
                }

                $normalized = normalize_phone($rawPhone);
                if ($normalized === '' || strlen(preg_replace('/\D/', '', $normalized)) < 7) {
                    $errors[] = "Item {$index}: invalid phone number format: {$rawPhone}";
                    continue;
                }

                $rawName = mb_substr(trim((string) ($item['name'] ?? 'Contact')), 0, 100);
                if ($rawName === '') {
                    $rawName = 'Contact';
                }

                $notes = isset($item['notes']) ? mb_substr(trim((string) $item['notes']), 0, 500) : null;
                $optIn = isset($item['opt_in'])
                    ? (filter_var($item['opt_in'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $item['opt_in'] ? 1 : 0)
                    : 1;

                $upsertStmt->execute([$userId, $rawName, $rawPhone, $normalized, $optIn, $notes]);

                $saved = self::getByPhone($userId, $rawPhone);
                if ($saved) {
                    $savedContacts[] = $saved;
                }
                $processed++;
            }

            if (count($batch) > 1 && $pdo->inTransaction()) {
                $pdo->commit();
            }
        } catch (Throwable $e) {
            if (count($batch) > 1 && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }

        return [
            'processed' => $processed,
            'contacts' => $savedContacts,
            'errors' => $errors,
        ];
    }

    /**
     * Delete a contact by its synthetic integer ID.
     */
    public static function deleteById(int $userId, int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        $pdo = Database::pdo();
        $stmt = $pdo->prepare('DELETE FROM contacts WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Delete a contact by its phone number.
     */
    public static function deleteByPhone(int $userId, string $phone): bool
    {
        $rawPhone = substr(preg_replace('/[^\d+]/', '', trim($phone)), 0, 32);
        if ($rawPhone === '') {
            return false;
        }

        $normalized = normalize_phone($rawPhone);
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('DELETE FROM contacts WHERE (phone = ? OR normalized_phone = ?) AND user_id = ?');
        $stmt->execute([$rawPhone, $normalized, $userId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Count total contacts for a user.
     */
    public static function count(int $userId, ?int $optIn = null): int
    {
        $pdo = Database::pdo();
        if ($optIn !== null) {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM contacts WHERE user_id = ? AND opt_in = ?');
            $stmt->execute([$userId, $optIn]);
        } else {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM contacts WHERE user_id = ?');
            $stmt->execute([$userId]);
        }
        return (int) $stmt->fetchColumn();
    }
}
