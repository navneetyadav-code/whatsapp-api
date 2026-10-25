<?php

declare(strict_types=1);

/**
 * WhatsGateway — Protected Media Delivery Service
 *
 * SEC-MEDIA: Authenticated & Signed Media Streaming.
 * Direct public HTTP downloads to the uploads directory are forbidden.
 * This script ensures only authorized recipients, API key owners,
 * dashboard users, or cryptographically signed URLs can access stored media.
 */

require_once dirname(__DIR__) . '/src/bootstrap.php';

$filename = basename((string) ($_GET['file'] ?? ''));
if ($filename === '' || !preg_match('/^[a-zA-Z0-9_\.-]+$/', $filename)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'error' => 'Invalid media filename.']);
    exit;
}

$uploadDir = get_media_upload_dir();
$filePath = realpath($uploadDir . $filename);

if ($filePath === false || !str_starts_with($filePath, realpath($uploadDir)) || !is_file($filePath)) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'error' => 'Media file not found.']);
    exit;
}

// 1. Check Signed URL Signature
$isAuthorized = false;
$authorizedUserId = null;

if (!empty($_GET['sig']) && !empty($_GET['expires']) && isset($_GET['u'])) {
    $expires = (int) $_GET['expires'];
    $uid = (int) $_GET['u'];
    $sig = (string) $_GET['sig'];

    if (verify_media_signature($filename, $uid, $expires, $sig)) {
        $isAuthorized = true;
        $authorizedUserId = $uid;
    }
}

// 2. Check Active Dashboard Session
if (!$isAuthorized) {
    $sessionUser = current_user();
    if ($sessionUser) {
        $authorizedUserId = (int) $sessionUser['id'];
        // Verify user ownership or admin privilege
        $pdo = Database::pdo();
        $ownerCheck = $pdo->prepare(
            "SELECT 1 FROM message_queue 
             WHERE user_id = ? 
               AND (
                 JSON_UNQUOTE(JSON_EXTRACT(payload, '$.attachment.path')) LIKE ?
                 OR JSON_UNQUOTE(JSON_EXTRACT(payload, '$.attachment.name')) = ?
               )
             LIMIT 1"
        );
        $ownerCheck->execute([$authorizedUserId, '%' . $filename, $filename]);
        if ($ownerCheck->fetchColumn()) {
            $isAuthorized = true;
        } else {
            // Check media_assets
            $assetCheck = $pdo->prepare("SELECT 1 FROM media_assets WHERE user_id = ? AND file_path LIKE ? LIMIT 1");
            $assetCheck->execute([$authorizedUserId, '%' . $filename]);
            if ($assetCheck->fetchColumn()) {
                $isAuthorized = true;
            }
        }
    }
}

// 3. Check REST API Key Credentials
if (!$isAuthorized) {
    $apiUser = authenticate_api_request();
    if ($apiUser) {
        $authorizedUserId = (int) $apiUser['user_id'];
        $pdo = Database::pdo();
        $ownerCheck = $pdo->prepare(
            "SELECT 1 FROM message_queue 
             WHERE user_id = ? 
               AND (
                 JSON_UNQUOTE(JSON_EXTRACT(payload, '$.attachment.path')) LIKE ?
                 OR JSON_UNQUOTE(JSON_EXTRACT(payload, '$.attachment.name')) = ?
               )
             LIMIT 1"
        );
        $ownerCheck->execute([$authorizedUserId, '%' . $filename, $filename]);
        if ($ownerCheck->fetchColumn()) {
            $isAuthorized = true;
        }
    }
}

if (!$isAuthorized) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'error' => 'Access denied: You do not have permission to view this media file.']);
    exit;
}

// Inspect real MIME type via finfo
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $filePath) ?: 'application/octet-stream';
finfo_close($finfo);

$fileSize = filesize($filePath);
$ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

// Hardening headers against MIME confusion and XSS
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . (string) $fileSize);
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-transform, max-age=7200');

// Use inline for images/audio/video/pdf, attachment for raw documents
$inlineExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'mp3', 'ogg', 'wav', 'mp4'];
$disposition = in_array($ext, $inlineExtensions, true) ? 'inline' : 'attachment';
header('Content-Disposition: ' . $disposition . '; filename="' . rawurlencode($filename) . '"');

readfile($filePath);
exit;
