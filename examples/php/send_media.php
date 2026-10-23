<?php
/**
 * WhatsGateway - Send Media & Message Example
 * Demonstrates sending Images, PDFs, Videos, or Audio via REST API.
 * 
 * Supports:
 * 1. Sending media via public URL (Recommended - simple & fast)
 * 2. Sending local files directly via cURL (Multipart upload)
 */

declare(strict_types=1);

// ==============================================================================
// CONFIGURATION
// ==============================================================================
// For Localhost (XAMPP):
$apiUrl = 'http://localhost/wa/public/api/send.php';

// For Live Render Deployment (uncomment when running against live server):
// $apiUrl = 'https://whatsapp-api-xepo.onrender.com/api/send.php';

// Your API Key from Dashboard -> API Keys
$apiKey = 'wa_live_671e8845f7b671e32922c05079c8d423298fd0aa79b9dfc6';

// Recipient phone number with country code (e.g., 919876543210 for India)
$recipientPhone = '919876543210';


// ==============================================================================
// METHOD 1: Send Media via Public URL (Images, PDFs, Videos, Audio)
// ==============================================================================
function sendMediaViaUrl(string $apiUrl, string $apiKey, string $to, string $mediaUrl, string $caption = '', string $type = 'image'): array
{
    $payload = [
        'to' => $to,
        'type' => $type, // 'image', 'document', 'video', or 'audio'
        'media_url' => $mediaUrl,
        'caption' => $caption,
        'humanize' => true,
    ];

    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'X-API-Key: ' . $apiKey,
            // Or use Bearer auth: 'Authorization: Bearer ' . $apiKey,
        ],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES),
        CURLOPT_TIMEOUT => 30,
    ]);

    $rawResponse = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    return [
        'http_code' => $httpCode,
        'error' => $error,
        'response' => json_decode((string) $rawResponse, true) ?: $rawResponse,
    ];
}


// ==============================================================================
// METHOD 2: Send Local File from Disk (Multipart/form-data Upload)
// ==============================================================================
function sendLocalMediaFile(string $apiUrl, string $apiKey, string $to, string $localFilePath, string $caption = ''): array
{
    if (!file_exists($localFilePath)) {
        throw new InvalidArgumentException("Local file not found: {$localFilePath}");
    }

    $cFile = new CURLFile($localFilePath);

    $postData = [
        'to' => $to,
        'caption' => $caption,
        'file' => $cFile,
        'humanize' => '1',
    ];

    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'X-API-Key: ' . $apiKey,
        ],
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_TIMEOUT => 60,
    ]);

    $rawResponse = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    return [
        'http_code' => $httpCode,
        'error' => $error,
        'response' => json_decode((string) $rawResponse, true) ?: $rawResponse,
    ];
}


// ==============================================================================
// EXECUTION / DEMO RUN
// ==============================================================================
echo "--- WhatsGateway Media Sender ---\n";

// Example: Sending an Image via URL with a Caption
$sampleImageUrl = 'https://images.unsplash.com/photo-1579202673506-ca3ce28943ef';
$captionText = "Hello! Here is your requested receipt / photo 🧾🚀";

echo "Dispatching media message to {$recipientPhone}...\n";

$result = sendMediaViaUrl(
    apiUrl: $apiUrl,
    apiKey: $apiKey,
    to: $recipientPhone,
    mediaUrl: $sampleImageUrl,
    caption: $captionText,
    type: 'image' // or 'document', 'video', 'audio'
);

echo "HTTP Status Code: " . $result['http_code'] . "\n";
echo "Response:\n";
print_r($result['response']);
echo "\n";
