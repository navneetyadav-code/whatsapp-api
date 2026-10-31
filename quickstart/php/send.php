<?php

declare(strict_types=1);

require_once __DIR__ . '/WhatsAppClient.php';

use WhatsAppHub\WhatsAppClient;

function prompt(string $message): string
{
    echo $message;
    $handle = fopen('php://stdin', 'r');
    $line = fgets($handle);
    fclose($handle);
    return trim((string) $line);
}

// Parse CLI options
$options = getopt('h', ['to:', 'message:', 'key:', 'url:', 'format::', 'no-humanize', 'help']);

if (isset($options['help']) || isset($options['h'])) {
    echo "WhatsApp API Hub — PHP Quickstart\n"
       . "Usage:\n"
       . "  php send.php [options]\n\n"
       . "Options:\n"
       . "  --to=<phone>       Recipient phone number (e.g. 919876543210)\n"
       . "  --message=<text>   Message content\n"
       . "  --key=<api_key>    API Key (wa_live_...)\n"
       . "  --url=<api_url>    API Gateway endpoint\n"
       . "  --format=<type>    Format: 'standard' (default) or 'meta'\n"
       . "  --no-humanize      Disable humanized typing delays\n"
       . "  --help, -h         Show this help message\n";
    exit(0);
}

$apiKey = $options['key'] ?? (string) getenv('WA_API_KEY');
if ($apiKey === '') {
    $apiKey = prompt('Enter your API Key (wa_live_...): ');
    if ($apiKey === '') {
        fwrite(STDERR, "[!] API Key is required.\n");
        exit(1);
    }
}

$apiUrl = $options['url'] ?? ((string) getenv('WA_API_URL') ?: WhatsAppClient::DEFAULT_URL);
$to = $options['to'] ?? prompt('Enter recipient phone number (with country code): ');
$message = $options['message'] ?? prompt('Enter message text: ');
$format = strtolower($options['format'] ?? 'standard');

echo "\n[*] Sending message via [" . strtoupper($format) . "] format...\n";
echo "[*] Endpoint:  {$apiUrl}\n";
echo "[*] Recipient: {$to}\n";

$client = new WhatsAppClient($apiKey, $apiUrl);

if ($format === 'meta') {
    [$success, $response, $code] = $client->sendMetaFormat($to, $message);
} else {
    $humanize = !isset($options['no-humanize']);
    [$success, $response, $code] = $client->sendStandard($to, $message, $humanize);
}

if ($success) {
    echo "\n[+] SUCCESS! Message queued for delivery (HTTP {$code}):\n";
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    exit(0);
} else {
    fwrite(STDERR, "\n[-] FAILED to send message (HTTP {$code}):\n");
    fwrite(STDERR, json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
    exit(1);
}
