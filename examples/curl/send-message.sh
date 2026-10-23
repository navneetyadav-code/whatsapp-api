#!/usr/bin/env bash
# Free WhatsApp API - cURL Example
# Send a WhatsApp text message via your self-hosted WhatsGateway

API_URL="http://localhost/whatsapp-api/public/api/send.php"
API_KEY="wa_live_YOUR_API_KEY_HERE"
RECIPIENT_PHONE="919876543210" # Full international number or 10-digit Indian number
MESSAGE_TEXT="Hello from WhatsGateway! 🚀"

curl -X POST "${API_URL}" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: ${API_KEY}" \
  -d '{
    "to": "'"${RECIPIENT_PHONE}"'",
    "message": "'"${MESSAGE_TEXT}"'"
  }'
