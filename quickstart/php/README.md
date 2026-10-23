# 🐘 PHP Quickstart — WhatsGateway

A production-ready PHP client class with zero composer dependencies (uses PHP's built-in `cURL` extension).

## 🚀 Quick Usage

### 1. Interactive Mode
Run via CLI in your terminal:
```bash
php send.php
```

### 2. Command Line Arguments
```bash
# Standard clean format (Recommended)
php send.php --key=wa_live_YOUR_KEY --to=919876543210 --message="Hello from PHP!"

# Meta WhatsApp Cloud API format (Drop-In)
php send.php --key=wa_live_YOUR_KEY --to=919876543210 --message="Hello!" --format=meta
```

### 3. As an OOP Class in Your PHP Application (Laravel / Symfony / Plain PHP)
```php
require_once __DIR__ . '/WhatsAppClient.php';

use WhatsAppHub\WhatsAppClient;

$client = new WhatsAppClient(
    'wa_live_YOUR_KEY_HERE',
    'https://whatsapp-api-xepo.onrender.com/api/send.php' // Or local endpoint
);

// 1. Send Standard Text Message
[$ok, $data, $code] = $client->sendStandard(
    to: '919876543210',
    message: 'Hello! Your password reset code is *482910*. 🔐',
    humanize: true
);

if ($ok) {
    echo "Message Queued! ID: " . $data['message_id'];
} else {
    echo "Failed: " . ($data['error'] ?? 'Unknown error');
}

// 2. Send via Meta WhatsApp Cloud API Format
[$ok, $data] = $client->sendMetaFormat('919876543210', 'Welcome to our platform!');

// 3. Send Template Message
[$ok, $data] = $client->sendTemplate('919876543210', 'account_alert', ['John', 'Active']);
```

## ⚙️ Environment Variables
```bash
export WA_API_KEY="wa_live_YOUR_KEY"
export WA_API_URL="https://whatsapp-api-xepo.onrender.com/api/send.php"
```
