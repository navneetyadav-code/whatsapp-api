# 🐍 Python Quickstart — WhatsApp API Hub

A production-ready Python client with **zero required external dependencies** (falls back to native `urllib` if `requests` is not installed).

## 🚀 Quick Usage

### 1. Interactive Mode
Run the script directly. It will guide you through entering your API Key, recipient phone number, and message:
```bash
python send.py
```

### 2. Command Line Arguments
```bash
# Standard clean format (Recommended)
python send.py --key wa_live_YOUR_KEY --to 919876543210 --message "Hello from Python!"

# Meta WhatsApp Cloud API format (Drop-In)
python send.py --key wa_live_YOUR_KEY --to 919876543210 --message "Hello!" --format meta
```

### 3. As an Imported Library in Your Project
```python
from send import WhatsAppHubClient

client = WhatsAppHubClient(
    api_key="wa_live_YOUR_KEY_HERE",
    api_url="https://whatsapp-api-xepo.onrender.com/api/send.php"  # Or your local endpoint
)

# Send via Standard Format
success, response = client.send_standard(
    to="919876543210",
    message="Your OTP is *123456* 🔐",
    humanize=True
)

if success:
    print(f"Queued message ID: {response['message_id']}")
else:
    print(f"Error: {response['error']}")
```

## ⚙️ Environment Variables
You can export credentials so you don't need to pass them each time:
```bash
# Windows PowerShell:
$env:WA_API_KEY="wa_live_YOUR_KEY"
$env:WA_API_URL="https://whatsapp-api-xepo.onrender.com/api/send.php"

# Linux / Mac Bash:
export WA_API_KEY="wa_live_YOUR_KEY"
export WA_API_URL="https://whatsapp-api-xepo.onrender.com/api/send.php"
```
