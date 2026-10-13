# 🚀 Free WhatsApp API & Automation Gateway (Self-Hosted, Open Source)

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![CI Quality Check](https://img.shields.io/badge/CI-Passing-brightgreen.svg)](.github/workflows/ci.yml)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4.svg?logo=php&logoColor=white)](https://www.php.net/)
[![Node.js](https://img.shields.io/badge/Node.js-18%2B%20%7C%2020%2B-339933.svg?logo=node.js&logoColor=white)](https://nodejs.org/)
[![MySQL](https://img.shields.io/badge/Database-MySQL%20%7C%20TiDB%20Serverless-4479A1.svg?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Baileys](https://img.shields.io/badge/Powered%20By-WhiskeySockets%2FBaileys-25D366.svg?logo=whatsapp&logoColor=white)](https://github.com/WhiskeySockets/Baileys)
[![Cost](https://img.shields.io/badge/Cost-100%25%20Free%20%26%20Open%20Source-orange.svg)](#why-use-this-free-whatsapp-api)
[![PRs Welcome](https://img.shields.io/badge/PRs-Welcome-brightgreen.svg)](CONTRIBUTING.md)

> **A 100% Free, Self-Hosted WhatsApp API Gateway & Web Management Dashboard built with Baileys, PHP 8+, Node.js, and MySQL.**  
> Send unlimited WhatsApp messages, schedule automated notifications, trigger webhook delivery receipts, and manage API keys — **without credit cards, Meta Cloud API fees, or conversation charges.**

---

## 🔍 Why Use This Free WhatsApp API?

Commercial WhatsApp messaging providers (Twilio, Meta Business Cloud API, Wati, MessageBird) charge **\$0.05 to \$0.15 per conversation**, require strict credit-card verifications, and demand complex template approval workflows.

**WhatsApp API Hub** is a self-hosted, developer-first alternative designed for startups, internal tools, hobbyists, and developers who need a reliable, cost-free messaging integration:

- 🟢 **Zero Recurring Costs:** 100% free and open-source under the MIT license.
- 📱 **Use Any Existing Phone Number:** Link your device in seconds by scanning a QR code via WhatsApp Web.
- 🔑 **Simple REST API Authentication:** Generate audited API keys with customizable rate limits and IP allowlists.
- 🔄 **Meta Cloud API Drop-In Compatible:** Seamlessly accepts both our clean JSON format and official Meta WhatsApp Cloud API payloads (`messaging_product`, `text.body`, `template`).
- 📬 **Reliable Message Queue:** Automatic background retries, jitter delays, and humanized dispatch timing.
- 🔔 **Instant Webhook Callbacks:** Receive real-time HTTP POST notifications on message delivery, read, and failure events.
- 📊 **Modern Web Dashboard:** Monitor queue depth, active sessions, message logs, and contacts with a clean UI.
- ☁️ **Flexible Hosting:** Run locally with XAMPP/Docker or deploy 24/7 on free cloud services (Render, Railway, VPS).

---

## 📊 Feature Comparison: Free WhatsApp API vs Paid Providers

| Feature | This Project (WhatsApp API Hub) | Meta Cloud API | Twilio WhatsApp | WATI / 360dialog |
| :--- | :---: | :---: | :---: | :---: |
| **Pricing** | **100% Free ($0)** | Pay-per-conversation | Pay-per-message + fees | $49 - $299 / month |
| **Credit Card Required?** | ❌ **No** | ✅ Yes | ✅ Yes | ✅ Yes |
| **Template Approval Needed?** | ❌ **No (Send any text)** | ✅ Mandatory | ✅ Mandatory | ✅ Mandatory |
| **Self-Hosted & Private?** | ✅ **Yes (Full control)** | ❌ Cloud only | ❌ Cloud only | ❌ Cloud only |
| **Linked Device (QR Code)?** | ✅ **Yes (Instant setup)** | ❌ Requires Meta verify | ❌ Requires Meta verify | ❌ Requires Meta verify |
| **Webhooks Included?** | ✅ **Yes** | ✅ Yes | ✅ Yes | ✅ Yes |
| **Built-in Web Dashboard?** | ✅ **Yes (PHP + Tailwind)** | ❌ No UI | ⚠️ Basic logs only | ✅ Yes |
| **Open Source?** | ✅ **Yes (MIT)** | ❌ Proprietary | ❌ Proprietary | ❌ Proprietary |

---

## 🏗️ System Architecture & Message Flow

```mermaid
graph LR
    subgraph Client["Your Applications"]
        A[cURL / Python / Node / PHP / Go]
    end

    subgraph Gateway["WhatsApp API Gateway (PHP)"]
        B["POST /api/send.php"]
        C["API Key & Rate Limiter"]
        D[("MySQL / TiDB Database")]
    end

    subgraph Worker["Background Engine (Node.js)"]
        E["Worker Queue Poller"]
        F["Baileys WhatsApp Client"]
    end

    subgraph WhatsApp["WhatsApp Network"]
        G["WhatsApp Web WebSocket"]
        H["Recipient Phone"]
    end

    A -->|1. HTTP Request with API Key| B
    B -->|2. Authenticate & Audit| C
    C -->|3. Store in message_queue| D
    D -.->|4. Poll Pending Messages| E
    E -->|5. Humanized Dispatch| F
    F -->|6. Encrypted Protocol| G
    G -->|7. Deliver Message| H
    F -->|8. Status & Delivery Webhook| A
```

---

## ⚡ Quick Start: Local Setup (XAMPP / Apache / MySQL)

### Prerequisites
- **PHP 8.1+** with `pdo_mysql`, `curl`, `mbstring`, `openssl`
- **Node.js 18+** or **20+**
- **MySQL 8.0+** or **MariaDB** (Included in XAMPP)

### Step 1: Clone Repository
```bash
git clone https://github.com/navneetyadav-code/whatsapp-api.git
cd whatsapp-api
```

### Step 2: Configure Database
1. Open phpMyAdmin or your MySQL client and create a database named `whatsapp_hub`:
   ```sql
   CREATE DATABASE whatsapp_hub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
2. Import the schema file [`database.sql`](database.sql) into `whatsapp_hub`.

### Step 3: Configure Environment Variables
Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```
Open `.env` and configure your database credentials:
```ini
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=whatsapp_hub
DB_USER=root
DB_PASS=

APP_ENV=local
APP_PUBLIC_URL=http://localhost/whatsapp-api/public
APP_BASE_PATH=/whatsapp-api/public
WORKER_URL=http://127.0.0.1:3107
```

### Step 4: Install Worker Dependencies & Start Worker
```bash
cd worker
npm install
npm run start
```
The Baileys background worker will initialize on `http://127.0.0.1:3107`.

### Step 5: Access the Dashboard & Link Device
1. Open your browser and navigate to: `http://localhost/whatsapp-api/public`
2. Create an account or log in.
3. Navigate to **WhatsApp Device** tab.
4. Click **Connect via WhatsApp** to generate a QR code.
5. On your mobile phone, open **WhatsApp &rarr; Linked devices &rarr; Link a device**, and scan the QR code.
6. Once connected, generate your first API Key from the **API Keys** tab!

---

## 📖 REST API Documentation & Code Examples

The API gateway accepts signed JSON requests authenticated with your `X-API-Key` or `Authorization: Bearer` header. It natively supports both our clean REST format and official Meta WhatsApp Cloud API schemas.

### Endpoints:
- **Standard Gateway:** `POST /api/send.php`
- **Meta Cloud API Alias:** `POST /api/messages.php`
- **Meta URL Rewrite:** `POST /v20.0/{PHONE_NUMBER_ID}/messages`

### Authentication Headers:
```http
Content-Type: application/json
X-API-Key: wa_live_your_api_key_here
# OR (Meta Cloud API style):
Authorization: Bearer wa_live_your_api_key_here
```

### Request Body Parameters (Supported Formats):

#### Format A: Standard Flat JSON (Recommended)
```json
{
  "to": "919876543210",
  "message": "Hello from Free WhatsApp API! 🚀",
  "humanize": true
}
```

#### Format B: Meta WhatsApp Cloud API (Drop-In)
```json
{
  "messaging_product": "whatsapp",
  "recipient_type": "individual",
  "to": "919876543210",
  "type": "text",
  "text": {
    "body": "Hello via Meta Cloud API format! 🌐"
  }
}
```

| Field | Type | Required | Description | Example |
| :--- | :---: | :---: | :--- | :--- |
| `to` | string | **Yes** | Recipient phone number (international format or 10-digit Indian number) | `"919876543210"` |
| `message` <br>*(or `text.body`)* | string | **Yes** | Message text (supports WhatsApp markdown: `*bold*`, `_italic_`, `~strike~`) | `"Hello World!"` |
| `humanize` | boolean | No | Adds randomized micro-delays between keystrokes to mimic human typing | `true` |
| `campaign_id` | integer | No | Optional campaign reference ID for analytics | `1` |

---

### Code Examples

> 💡 **Looking for complete, runnable projects?** Check out our dedicated [`quickstart/`](quickstart/) folder with ready-to-run source code in **5 languages**:
> - [🐍 Python Quickstart](quickstart/python/) (`send.py` — zero dependencies)
> - [⚡ Node.js Quickstart](quickstart/nodejs/) (`send.js` — native `fetch`)
> - [🐘 PHP Quickstart](quickstart/php/) (`WhatsAppClient.php`, `send.php`)
> - [🐹 Go Quickstart](quickstart/go/) (`main.go` — native `net/http`)
> - [☕ Java Quickstart](quickstart/java/) (`SendWhatsApp.java` — Java 8 to 21+)

#### 1. cURL (Standard Clean Format)
```bash
curl -X POST "http://localhost/whatsapp-api/public/api/send.php" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: wa_live_YOUR_API_KEY" \
  -d '{
    "to": "919876543210",
    "message": "Hello from Free WhatsApp API! 🚀",
    "humanize": true
  }'
```

#### 2. cURL (Meta WhatsApp Cloud API Format — Drop-In)
```bash
curl -X POST "http://localhost/whatsapp-api/public/api/send.php" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer wa_live_YOUR_API_KEY" \
  -d '{
    "messaging_product": "whatsapp",
    "recipient_type": "individual",
    "to": "919876543210",
    "type": "text",
    "text": {
      "preview_url": false,
      "body": "Hello via Meta Cloud API format! 🌐"
    }
  }'
```

#### 3. Python (`requests`)
```python
import requests

url = "http://localhost/whatsapp-api/public/api/send.php"
headers = {
    "Content-Type": "application/json",
    "X-API-Key": "wa_live_YOUR_API_KEY"
}
payload = {
    "to": "919876543210",
    "message": "Automated alert from Python script! 🐍",
    "humanize": True
}

response = requests.post(url, json=payload, headers=headers)
print(response.json())
```

#### 4. JavaScript / Node.js (`fetch`)
```javascript
const response = await fetch("http://localhost/whatsapp-api/public/api/send.php", {
  method: "POST",
  headers: {
    "Content-Type": "application/json",
    "X-API-Key": "wa_live_YOUR_API_KEY"
  },
  body: JSON.stringify({
    to: "919876543210",
    message: "Order #12345 confirmed! 📦",
    humanize: true
  })
});

const data = await response.json();
console.log(data);
```

#### 5. PHP (`cURL`)
```php
$payload = [
    'to' => '919876543210',
    'message' => 'Hello from PHP WhatsApp Client! 🐘',
    'humanize' => true
];

$ch = curl_init('http://localhost/whatsapp-api/public/api/send.php');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'X-API-Key: wa_live_YOUR_API_KEY'
    ],
    CURLOPT_POSTFIELDS => json_encode($payload)
]);

$response = curl_exec($ch);
curl_close($ch);
echo $response;
```

#### 6. Go (`net/http`)
```go
package main

import (
	"bytes"
	"encoding/json"
	"net/http"
)

func main() {
	payload, _ := json.Marshal(map[string]interface{}{
		"to":       "919876543210",
		"message":  "Fast delivery notification via Go! 🐹",
		"humanize": true,
	})

	req, _ := http.NewRequest("POST", "http://localhost/whatsapp-api/public/api/send.php", bytes.NewBuffer(payload))
	req.Header.Set("Content-Type", "application/json")
	req.Header.Set("X-API-Key", "wa_live_YOUR_API_KEY")

	client := &http.Client{}
	client.Do(req)
}
```

#### 7. Interactive Terminal Program (`send_whatsapp.py`)
Run our interactive CLI script directly in PowerShell or Terminal:
```bash
python send_whatsapp.py
```
It prompts you for recipient phone number and message text, automatically saves your API URL and key, and dispatches in real-time.

---

### Response Format (Universal Dual-Compatible):

#### Success Response (HTTP 200 OK):
```json
{
  "ok": true,
  "status": "queued",
  "message_id": 42,
  "request_id": "req_671e8845f7b67",
  "recipient": "919876543210",
  "messaging_product": "whatsapp",
  "contacts": [
    {
      "input": "919876543210",
      "wa_id": "919876543210"
    }
  ],
  "messages": [
    {
      "id": "wamid.2bad39f34245353e31eeeec923ce4aa5",
      "message_status": "accepted"
    }
  ]
}
```

#### Error Response (HTTP 400 / 401 / 429):
```json
{
  "ok": false,
  "error": "Rate limit exceeded. Try again in 60 seconds.",
  "meta_error": {
    "message": "Rate limit hit.",
    "type": "OAuthException",
    "code": 80007
  }
}
```

---

## 🔔 Webhooks & Real-Time Delivery Notifications

Configure your HTTPS callback endpoint in the dashboard under **Webhooks**. The gateway will immediately post delivery updates:

```json
{
  "event": "message.delivered",
  "message_id": 42,
  "to": "919876543210",
  "status": "sent",
  "timestamp": "2026-10-13T12:45:00Z"
}
```

---

## 🛡️ Anti-Ban & Account Safety Best Practices

Because this project automates WhatsApp Web sessions via Baileys, follow these safety practices to protect your phone number:

1. **Enable Humanized Delays (`"humanize": true`):** Simulates human typing intervals rather than robotic instant blasts.
2. **Obtain Explicit User Consent:** Only send messages to customers, leads, or users who have opted in.
3. **Use Sensible Rate Limits:** Avoid sending hundreds of messages in a single second. Space broadcast campaigns over minutes or hours.
4. **Warm Up New Numbers:** If you are using a new SIM card, exchange messages naturally with friends for a week before running automated notifications.

---

## ❓ Frequently Asked Questions (FAQ)

### Is this WhatsApp API really 100% free?
Yes. The project is completely open source under the MIT License. You host it on your own server or free cloud tiers (like Render + TiDB Cloud), meaning there are zero subscription fees and zero per-message charges.

### Do I need a Facebook Business Manager or Meta verification?
No. Unlike Meta's official WhatsApp Business Cloud API, this project does not require company documents, business approval, or credit cards. You simply scan a QR code with any regular or Business WhatsApp account.

### Can I send messages to international numbers?
Yes. Simply specify the recipient's phone number with their country code (e.g., `+1` for USA, `+91` for India, `+44` for UK, etc.).

### Can I host this 24/7 on free cloud hosting?
Yes! The repository is optimized for cloud deployment. You can deploy the web app and Node.js worker on **Render** (Free Tier) and connect to a free **TiDB Cloud Serverless** MySQL database.

### What happens if my server restarts?
The Baileys authentication session is persisted securely in your database and encrypted credentials directory. Upon server restart, the worker automatically reconnects to WhatsApp Web without requiring you to re-scan the QR code.

---

## 🏷️ Search Keywords & Metadata (SEO)

`free whatsapp api` &bull; `open source whatsapp api` &bull; `whatsapp web automation` &bull; `baileys whatsapp api` &bull; `whatsapp api php` &bull; `whatsapp api nodejs` &bull; `self hosted whatsapp gateway` &bull; `whatsapp bulk message api` &bull; `free alternative to twilio whatsapp` &bull; `free alternative to meta whatsapp cloud api` &bull; `meta whatsapp cloud api drop-in replacement` &bull; `whatsapp qr code api` &bull; `whatsapp rest api gateway` &bull; `whatsapp webhook callbacks` &bull; `whatsapp chatbot starter`

---

## 👨‍💻 Author & Maintainer

Created with ❤️ by **[Navneet Kumar Yadav](https://github.com/navneetyadav-code)**.

- **GitHub Profile:** [@navneetyadav-code](https://github.com/navneetyadav-code)
- **Email:** `info.assistgroupofficial@gmail.com`

---

## ⚖️ Disclaimer & Legal Notice

This project is an independent, community-driven, open-source automation library and is **not affiliated with, associated with, authorized by, endorsed by, or in any way officially connected with Meta Platforms, Inc., WhatsApp LLC, or any of their subsidiaries or affiliates.**

WhatsApp is a registered trademark of WhatsApp LLC / Meta Platforms, Inc. The names WhatsApp as well as related names, marks, emblems, and images are registered trademarks of their respective owners.

### Takedown / Copyright Removal Requests
If you are a copyright or trademark owner or an agent thereof and believe that any content in this repository infringes upon your marks, please submit a formal notice to:

📧 **info.assistgroupofficial@gmail.com**

All verified requests will be reviewed and addressed promptly.

---

## 📜 License

This project is licensed under the **MIT License** — see the [LICENSE](LICENSE) file for details.
