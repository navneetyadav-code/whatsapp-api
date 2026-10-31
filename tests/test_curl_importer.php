<?php

require_once __DIR__ . '/../src/lib/CurlImporter.php';

$curl = 'curl -X POST "https://whatsapp-api-xepo.onrender.com/api/messages.php" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer wa_live_test123" \
  -d \'{
  "messaging_product": "whatsapp",
  "recipient_type": "individual",
  "to": "919876543210",
  "type": "text",
  "text": {
    "preview_url": false,
    "body": "Hello via Meta Cloud API format! 🌐"
  }
}\'';

$result = CurlImporter::parse($curl);
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
