<?php

declare(strict_types=1);

/**
 * Data provider for Customer Feedback & Ratings section
 */

$user = $user ?? require_auth();
$userId = (int) $user['id'];
$pdo = Database::pdo();

// 1. Telemetry Aggregation
$statsStmt = $pdo->prepare(
    'SELECT 
        COUNT(*) AS total_count,
        COALESCE(AVG(rating), 0) AS avg_rating,
        SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) AS count_5,
        SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) AS count_4,
        SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) AS count_3,
        SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) AS count_2,
        SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) AS count_1,
        SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) AS positive_count
     FROM chat_feedback
     WHERE user_id = ?'
);
$statsStmt->execute([$userId]);
$stats = $statsStmt->fetch() ?: [];

$feedbackTotal = (int) ($stats['total_count'] ?? 0);
$feedbackAvg = round((float) ($stats['avg_rating'] ?? 0), 1);
$count5 = (int) ($stats['count_5'] ?? 0);
$count4 = (int) ($stats['count_4'] ?? 0);
$count3 = (int) ($stats['count_3'] ?? 0);
$count2 = (int) ($stats['count_2'] ?? 0);
$count1 = (int) ($stats['count_1'] ?? 0);
$positiveCount = (int) ($stats['positive_count'] ?? 0);

$pct5 = $feedbackTotal > 0 ? round(($count5 / $feedbackTotal) * 100) : 0;
$satisfactionRate = $feedbackTotal > 0 ? round(($positiveCount / $feedbackTotal) * 100) : 0;

// 2. Query Filters & Pagination
$feedbackQuery = trim((string) ($_GET['q'] ?? ''));
$filterRating = isset($_GET['rating']) && $_GET['rating'] !== '' ? (int) $_GET['rating'] : null;

$whereClauses = ['user_id = ?'];
$params = [$userId];

if ($feedbackQuery !== '') {
    $whereClauses[] = '(contact_name LIKE ? OR phone LIKE ? OR message LIKE ?)';
    $like = '%' . $feedbackQuery . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($filterRating !== null && $filterRating >= 1 && $filterRating <= 5) {
    $whereClauses[] = 'rating = ?';
    $params[] = $filterRating;
}

$whereSql = implode(' AND ', $whereClauses);

// Count matching records
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM chat_feedback WHERE {$whereSql}");
$countStmt->execute($params);
$totalFiltered = (int) $countStmt->fetchColumn();

// Pagination
$perPage = 15;
$currentPageNum = max(1, (int) ($_GET['p'] ?? 1));
$totalPages = max(1, (int) ceil($totalFiltered / $perPage));
if ($currentPageNum > $totalPages) {
    $currentPageNum = $totalPages;
}
$offset = ($currentPageNum - 1) * $perPage;

// Fetch records
$dataStmt = $pdo->prepare(
    "SELECT id, conversation_id, phone, contact_name, rating, message, created_at
     FROM chat_feedback
     WHERE {$whereSql}
     ORDER BY created_at DESC, id DESC
     LIMIT {$perPage} OFFSET {$offset}"
);
$dataStmt->execute($params);
$feedbackRows = $dataStmt->fetchAll();

// Helper for phone format
if (!function_exists('format_feedback_phone')) {
    function format_feedback_phone(string $phone): string
    {
        $clean = preg_replace('/\D/', '', $phone) ?? '';
        if (strlen($clean) === 12 && str_starts_with($clean, '91')) {
            return '+91 ' . substr($clean, 2, 5) . ' ' . substr($clean, 7);
        }
        return $clean !== '' ? '+' . $clean : $phone;
    }
}

// Helper for initials
if (!function_exists('feedback_initials')) {
    function feedback_initials(?string $name, string $phone): string
    {
        $text = trim((string) $name);
        if ($text !== '') {
            $words = preg_split('/\s+/', $text) ?: [];
            if (count($words) >= 2) {
                return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
            }
            return strtoupper(substr($text, 0, 2));
        }
        $digits = preg_replace('/\D/', '', $phone) ?? '';
        return strlen($digits) >= 2 ? substr($digits, -2) : 'WA';
    }
}

// Helper for relative time ago
if (!function_exists('feedback_time_ago')) {
    function feedback_time_ago(string $datetime): string
    {
        $time = strtotime($datetime);
        if (!$time) {
            return '';
        }
        $diff = time() - $time;
        if ($diff < 60) {
            return 'just now';
        }
        if ($diff < 3600) {
            return (int) ($diff / 60) . 'm ago';
        }
        if ($diff < 86400) {
            return (int) ($diff / 3600) . 'h ago';
        }
        if ($diff < 604800) {
            return (int) ($diff / 86400) . 'd ago';
        }
        return date('M j, Y', $time);
    }
}
