"""
Free WhatsApp API - Python Example
Send WhatsApp messages using Python and requests.
"""

import requests
import json

API_URL = "http://localhost/whatsapp-api/public/api/send.php"
API_KEY = "wa_live_YOUR_API_KEY_HERE"
RECIPIENT_PHONE = "919876543210"

payload = {
    "to": RECIPIENT_PHONE,
    "message": "Hello from Python! 🐍 Free WhatsApp API Gateway is working perfectly.",
    "humanize": True
}

headers = {
    "Content-Type": "application/json",
    "X-API-Key": API_KEY
}

try:
    response = requests.post(API_URL, json=payload, headers=headers, timeout=10)
    response_data = response.json()
    print("Response Status:", response.status_code)
    print("Response Data:", json.dumps(response_data, indent=2))
except requests.exceptions.RequestException as e:
    print("Error sending message:", e)
