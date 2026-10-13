<?php
/**
 * Free WhatsApp API - PHP Client Example
 * Send WhatsApp messages from PHP using cURL.
 */

$apiUrl = 'http://localhost/whatsapp-api/public/api/send.php';
$apiKey = 'wa_live_YOUR_API_KEY_HERE';
$recipientPhone = '919876543210';

$payload = [
    'to' => $recipientPhone,
    'message' => 'Hello from PHP! 🐘 Free WhatsApp Web API Gateway.',
    'humanize' => true,
];

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'X-API-Key: ' . $apiKey,
    ],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 15,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "cURL Error: " . $error . PHP_EOL;
} else {
    echo "HTTP Status: " . $httpCode . PHP_EOL;
    echo "Response: " . $response . PHP_EOL;
}
