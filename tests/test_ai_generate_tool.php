<?php

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/lib/CurlImporter.php';

$rawCurl = 'curl -X POST "https://whatsapp-api-xepo.onrender.com/api/messages.php" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer wa_live_dummy_token_123" \
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

$parsed = CurlImporter::parse($rawCurl);
echo "=== PARSED CURL ===\n";
echo json_encode($parsed, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

// Verify fields
assert($parsed['execution_config']['body_template']['to'] === '{to}');
assert($parsed['execution_config']['body_template']['text']['body'] === '{body}');
assert($parsed['execution_config']['body_template']['messaging_product'] === 'whatsapp');
assert($parsed['execution_config']['body_template']['text']['preview_url'] === false);

$paramNames = array_column($parsed['parameters'], 'name');
assert(in_array('to', $paramNames, true));
assert(in_array('body', $paramNames, true));
assert(!in_array('phone_number', $paramNames, true));
assert(!in_array('message_content', $paramNames, true));

echo "\nALL ASSERTIONS PASSED!\n";
