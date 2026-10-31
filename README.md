<p align="center">
  <a href="https://whatsapp-api-xepo.onrender.com" target="_blank" rel="noopener noreferrer">
    <img src="public/assets/logo_white.png" alt="WhatsGateway Logo" width="128" style="border-radius: 50%;">
  </a>
</p>

<h1 align="center">WhatsGateway</h1>

<p align="center">
  <strong>Your Self-Hosted WhatsApp API. Your Infrastructure. Your Control.</strong><br>
  <em>The modern, open-source WhatsApp REST API gateway and web management dashboard built for developers and teams.<br>
  Zero per-conversation fees, zero template approvals, and no credit card required.</em>
</p>

<p align="center">
  <a href="https://github.com/navneetyadav-code/whatsapp-api/stargazers"><img src="https://img.shields.io/github/stars/navneetyadav-code/whatsapp-api?style=for-the-badge&logo=github&color=gold" alt="GitHub Stars"></a>
  <a href="https://github.com/navneetyadav-code/whatsapp-api/network/members"><img src="https://img.shields.io/github/forks/navneetyadav-code/whatsapp-api?style=for-the-badge&logo=github&color=blue" alt="GitHub Forks"></a>
  <a href="https://github.com/navneetyadav-code/whatsapp-api/issues"><img src="https://img.shields.io/github/issues/navneetyadav-code/whatsapp-api?style=for-the-badge&color=brightgreen" alt="GitHub Issues"></a>
  <a href="https://github.com/navneetyadav-code/whatsapp-api/discussions"><img src="https://img.shields.io/badge/Discussions-Join-purple?style=for-the-badge&logo=github" alt="GitHub Discussions"></a>
  <a href="LICENSE"><img src="https://img.shields.io/badge/License-MIT-blue.svg?style=for-the-badge" alt="License: MIT"></a>
</p>

<p align="center">
  <a href="https://www.php.net/"><img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4.svg?style=flat-square&logo=php&logoColor=white" alt="PHP 8.2+"></a>
  <a href="https://nodejs.org/"><img src="https://img.shields.io/badge/Node.js-18%2B%20%7C%2020%2B-339933.svg?style=flat-square&logo=node.js&logoColor=white" alt="Node.js"></a>
  <a href="https://github.com/WhiskeySockets/Baileys"><img src="https://img.shields.io/badge/Engine-Baileys%20v7-25D366.svg?style=flat-square&logo=whatsapp&logoColor=white" alt="Baileys Engine"></a>
  <a href="https://www.mysql.com/"><img src="https://img.shields.io/badge/Database-MySQL%20%7C%20MariaDB%20%7C%20TiDB-4479A1.svg?style=flat-square&logo=mysql&logoColor=white" alt="Database"></a>
  <a href="Dockerfile"><img src="https://img.shields.io/badge/Docker-Ready-2496ED.svg?style=flat-square&logo=docker&logoColor=white" alt="Docker Ready"></a>
  <a href="https://whatsapp-api-xepo.onrender.com"><img src="https://img.shields.io/badge/Live%20Demo-Online-success?style=flat-square&logo=render" alt="Live Demo"></a>
</p>

<p align="center">
  <a href="#quick-start"><b>🚀 Quick Start</b></a> •
  <a href="#api-quick-reference"><b>📖 API Reference</b></a> •
  <a href="https://whatsapp-api-xepo.onrender.com" target="_blank"><b>⚡ Live Demo</b></a> •
  <a href="#system-architecture"><b>🏗️ Architecture</b></a> •
  <a href="#why-whatsgateway"><b>⚖️ Why WhatsGateway</b></a> •
  <a href="#client-sdks"><b>💻 SDKs</b></a> •
  <a href="#faq"><b>❓ FAQ</b></a> •
  <a href="https://github.com/navneetyadav-code/whatsapp-api/stargazers"><b>⭐ Star on GitHub</b></a>
</p>

---

## 📌 Executive Summary

**WhatsGateway** is a production-ready, self-hosted WhatsApp REST API Gateway and Web Management Dashboard built with **PHP 8.2+**, **Node.js (@whiskeysockets/baileys)**, and **MySQL / MariaDB / TiDB**.

Connect any existing WhatsApp number (personal or WhatsApp Business) in seconds by scanning a QR code. Send transactional alerts, OTPs, customer notifications, and rich media without paying \$0.05–\$0.15+ per conversation to third-party providers.

> 💡 **Drop-In Compatibility:** WhatsGateway natively accepts both clean flat JSON and official **Meta WhatsApp Cloud API payloads** (`messaging_product`, `recipient_type`, `text.body`, `template`), allowing you to switch existing integrations with zero code rewrites.

---

<a id="why-whatsgateway"></a>
## ⚖️ Why WhatsGateway? (vs. Meta Cloud & Twilio)

Commercial messaging providers enforce high conversation fees, strict template pre-approval delays, and cloud vendor lock-in. WhatsGateway gives you total privacy, speed, and freedom on your own infrastructure.

| Capability / Metric | WhatsGateway | Meta WhatsApp Cloud API | Twilio WhatsApp API | Traditional Cloud SaaS |
| :--- | :---: | :---: | :---: | :---: |
| **Software Cost** | **Free & Open Source (MIT)** | Pay-per-conversation | Pay-per-message + markup | \$49 – \$299 / month |
| **Credit Card Mandate** | ❌ **None** | ✅ Required | ✅ Required | ✅ Required |
| **Message Approval** | ❌ **Instant (Freeform text & media)** | ⚠️ Strict template review | ⚠️ Strict template review | ⚠️ Strict template review |
| **Onboarding Time** | **2 minutes (Scan QR)** | Days to weeks (Meta verification) | Days to weeks | Hours to days |
| **Data Privacy & Ownership** | ✅ **100% On your own servers** | ❌ Hosted by Meta | ❌ Hosted by Twilio | ❌ Third-party cloud |
| **Web Dashboard** | ✅ **Included (PHP + Tailwind)** | ❌ No built-in dashboard | ⚠️ Basic log view only | ✅ Varies |
| **Meta API Drop-In Format** | ✅ **Yes (`/v20.0/.../messages`)** | ✅ Native | ❌ Proprietary Twilio API | ❌ Proprietary API |
| **Autonomous AI Agent & RAG**| ✅ **Built-in (Gemini, Groq, Llama)** | ❌ Custom webhook required | ❌ Studio build required | ⚠️ High add-on cost |
| **Live Chat & Human Takeover**| ✅ **Included with sound alerts** | ❌ None | ❌ Extra service | ⚠️ Varies |
| **Multi-Model Cognitive Memory**| ✅ **Dual-tier memory + Top-K** | ❌ None | ❌ None | ❌ None |

> ℹ️ *Note on Infrastructure:* WhatsGateway software is 100% free under the MIT License. Underlying hosting and server infrastructure (e.g. VPS, local hardware, or cloud database) are managed directly by you.

---

## 🖼️ Visual Product Showcase

### System Architecture Overview
The application decouples high-throughput API ingestion from persistent queueing and real-time WebSocket protocol handling:

<p align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="public/assets/architecture-dark.png">
    <source media="(prefers-color-scheme: light)" srcset="public/assets/architecture.png">
    <img src="public/assets/architecture.png" alt="WhatsGateway System Architecture Diagram" width="100%">
  </picture>
</p>

---

## 🌟 Core Features & Highlights

```
┌────────────────────────────────────────────────────────────────────────┐
│                              WhatsGateway                              │
├─────────────────────┬─────────────────────┬────────────────────────────┤
│  📨 Messaging       │  🤖 Autonomous AI   │  🛡️ Enterprise Security    │
│  • Text & Markdown  │  • Gemini & Groq    │  • SHA-256 API Key Hash    │
│  • PDF & Documents  │  • Cognitive Memory │  • Per-Key Rate Limiter    │
│  • Images & Audio   │  • Top-K Token Gov  │  • SSRF DNS Filtering      │
│  • PTT Voice Memos  │  • Function Calling │  • Active PII Rejecter     │
│  • Video & Media    │  • Live Takeover    │  • HMAC Signed Webhooks    │
├─────────────────────┼─────────────────────┼────────────────────────────┤
│  ⚡ Developer UX    │  👥 Audiences       │  🚀 Deployment             │
│  • Universal REST   │  • Contact Directory│  • 1-Command Docker Compose│
│  • Meta Cloud DropIn│  • CSV Bulk Import  │  • MySQL / MariaDB / TiDB  │
│  • 5-Language SDKs  │  • Google Contacts  │  • Render & VPS Guide      │
│  • Zero-Dep Snippets│  • vCard (.vcf)     │  • Supervisor Daemon       │
│  • Health Probes    │  • Templating {name}│  • Auto Migrations         │
└─────────────────────┴─────────────────────┴────────────────────────────┘
```

### 1. 📨 Universal Messaging Engine
- **Text & Rich Markdown:** Send formatting (`*bold*`, `_italic_`, `~strike~`, ````monospace````) with full emoji support.
- **Universal Media Handler:** Transmit documents (PDF, DOCX, XLSX, TXT, CSV), images (JPEG, PNG, WEBP, GIF), videos (MP4, MOV), and audio files (MP3, OGG, WAV, M4A).
- **Push-to-Talk (PTT) Voice Notes:** Deliver audio files as native WhatsApp green-waveform voice memos (`"ptt": true`).
- **Flexible Ingestion:** Deliver media via direct public HTTP/HTTPS URLs (with SSRF protection), Base64 data strings / Data URIs, or standard `multipart/form-data` uploads up to 20MB.

### 2. ⚡ Developer Experience & API Compatibility
- **Universal REST API:** Authenticate with a single Bearer token (`wa_live_...`) or `X-API-Key`.
- **Meta Cloud API Compatibility:** Seamless drop-in route (`POST /v20.0/{phone_number_id}/messages`) accepts Meta Graph API schemas without code rewrites.
- **Multi-Language Quickstarts:** Zero-dependency, copy-paste snippets in Python, Node.js, PHP, Go, and Java.
- **Observability:** Dedicated health probe (`GET /api/health.php`) verifying PHP runtime, database schemas, and worker status.

### 3. 🤖 Autonomous AI Agent Studio & Memory Engine
- **Multi-Model LLM Orchestration:** Auto-cascade across Google Gemini (Flash 2.5 / 3.5), Groq LPU (Llama 3.3 70B, GPT-OSS 120B), and OpenRouter.
- **Dual-Tier Cognitive Memory:** 
  - *Short-Term Working Memory:* Character-budgeted sliding window with 45-minute rolling TTL inactivity decay.
  - *Persistent Fact Notebook:* Contact-scoped durable memory (`ai_user_memory`) with conversational self-update (*"Remember that..."*, *"Forget my..."*).
- **Token Governor (Top-K Relevance Matching):** Scores incoming queries against stored facts and injects only the Top-3 relevant facts (< 120 tokens) to prevent context bloat and minimize latency.
- **Smart Few-Shot In-Context Training:** Define Golden Q&A pairs and strict negative guardrails (*"What NOT to do"*, such as discount caps) right from the web dashboard.
- **Modular App Store & Function Calling:** 1-click tools for DuckDuckGo web search, exact math calculation, business hours clock, Open-Meteo weather, outbound CRM leads, and a **Two-Phase Action Protocol** (drafting emails with Boss WhatsApp confirmation).

### 4. 💬 Live Chat & Human Escalation Desk
- **Real-Time Webchat Desk:** Intercept conversations directly from the browser (`/live-chat`).
- **Frustration Detection & Audio Alerts:** Trigger immediate sound buzzer alerts when customers express high urgency or request human support.
- **1-Click Human Takeover:** Seamlessly pause automated AI replies to let human agents respond.
- **Automated CSAT Surveys:** Collect customer satisfaction ratings automatically upon chat resolution.

### 5. 📬 Queueing & Humanized Anti-Ban Delivery
- **Asynchronous FIFO Queue:** Decouples API client responses from WhatsApp delivery latency.
- **Humanized Presence Emulation:** Simulates authentic user behavior with typing presence indicators (`composing` / `recording`) and configurable randomized jitter intervals.
- **Retry Backoff & Dead-Letter Tracking:** Automatic backoff retries with queue visualizer and cancellation controls in the dashboard.

### 6. 🛡️ Enterprise Security & Privacy Guardrails
- **SHA-256 Single API Key Hashing:** Plaintext keys are never stored; only cryptographic hashes are persisted.
- **Active PII Query String Rejection:** Rejects plaintext phone numbers in URL query strings with `HTTP 400` to prevent PII leakage into web server logs.
- **SSRF DNS Inspection:** Validates destination IPs for all media URLs and webhooks, blocking private ranges (`10.0.0.0/8`, `127.0.0.0/8`, `192.168.0.0/16`, `169.254.0.0/16`, `::1`).
- **HMAC-SHA256 Webhook Signatures:** Signs every webhook dispatch (`X-WA-Hub-Signature`) for tamper-proof delivery verification.
- **Hardened Uploads:** Upload directories prevent script execution via strict `.htaccess` directives.

---

<a id="quick-start"></a>
## 🚀 Quick Start

Get WhatsGateway running in under 2 minutes:

### Option 1: Docker Compose (Recommended)

The fastest way to deploy WhatsGateway with PHP 8.2 Apache, Node.js 20, and MySQL 8.0:

```bash
# 1. Clone the repository
git clone https://github.com/navneetyadav-code/whatsapp-api.git
cd whatsapp-api

# 2. Launch the full stack
docker compose up -d

# 3. Open your browser
# Dashboard: http://localhost:8080
```

> 🔑 **Initial Account Setup:** On your first container launch, configure `INITIAL_ADMIN_EMAIL` and `INITIAL_ADMIN_PASSWORD` in your environment or `docker-compose.yml` to automatically provision your dashboard administrator account.

---

### Option 2: Local / Server Setup (PHP + Node.js + MySQL)

#### Prerequisites
- **PHP 8.2+** (with extensions: `pdo_mysql`, `curl`, `mbstring`, `openssl`, `fileinfo`, `json`)
- **Node.js 18.x or 20.x LTS** & `npm`
- **MySQL 8.0+**, **MariaDB 10.4+**, or **TiDB Cloud Serverless**
- **Apache 2.4+** (with `mod_rewrite` and `mod_headers`) or **Nginx**

#### Step-by-Step Installation:

```bash
# 1. Clone repository into your web root
git clone https://github.com/navneetyadav-code/whatsapp-api.git
cd whatsapp-api

# 2. Configure environment
cp .env.example .env
# Edit .env with your database credentials and APP_PUBLIC_URL

# 3. Run database migrations (17 application tables)
php scripts/migrate.php

# 4. Install worker dependencies
cd worker
npm install
cd ..

# 5. Start the background worker daemon
# On Windows:
start-worker.bat start
# On Linux / macOS:
cd worker && npm start
```

---

### Pairing Your WhatsApp Account

1. Open your WhatsGateway dashboard: `http://localhost:8080` (Docker) or `http://localhost/wa/public` (Local).
2. Navigate to **Device** (`/device`) and click **Connect**.
3. Open **WhatsApp** on your phone > **Linked Devices** > **Link a Device**.
4. Scan the QR code displayed on your screen.
5. Your session will transition to `connected`. You are ready to dispatch messages!

---

<a id="send-first-message"></a>
## ⚡ Send Your First API Request in 10 Seconds

Once your device is connected, generate an API key in **API Keys** (`/api-keys`), then execute:

```bash
curl -X POST "http://localhost:8080/api/send.php" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: wa_live_YOUR_API_KEY_HERE" \
  -d '{
    "to": "919876543210",
    "message": "Hello from WhatsGateway! 🚀\nYour self-hosted WhatsApp API is up and running.",
    "humanize": true
  }'
```

### Success Response (`200 OK`):
```json
{
  "ok": true,
  "status": "queued",
  "message_id": 142,
  "request_id": "9d8e7a6b5c4f1e2d3c4b5a6f",
  "recipient": "919876543210",
  "type": "text",
  "has_media": false,
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

---

<a id="system-architecture"></a>
## 🏗️ System Architecture

### Request & Delivery Lifecycle

```mermaid
flowchart LR
    subgraph Clients["1. Clients & Ingestion"]
        direction TB
        C1["REST API Consumers<br/>(cURL, Python, Node, Go, Java)"]
        C2["Admin Dashboard<br/>(PHP 8.2 + Tailwind)"]
    end

    subgraph Gateway["2. Web Gateway & Security"]
        direction TB
        G1["Apache / Nginx Router<br/>(URL Rewriting & Headers)"]
        G2["Security & Guardrails<br/>(SHA-256 Auth, Rate Limiter, SSRF Check)"]
        G3["REST Ingestion<br/>(public/api/send.php)"]
    end

    subgraph Storage["3. Persistent Storage"]
        direction TB
        DB[("MySQL / MariaDB / TiDB<br/>ACID Message Queue & Sessions")]
    end

    subgraph Worker["4. Node.js Worker Microservice"]
        direction TB
        W1["Express Controller (:3107)"]
        W2["FIFO Queue Poller & Humanized Delay"]
        W3["Baileys Multi-Device Client"]
        W4["Chatbot & AI Tools Engine"]
    end

    subgraph WhatsApp["5. WhatsApp Network"]
        WA["Meta WhatsApp Servers<br/>(Noise Protocol WebSocket)"]
    end

    Clients -->|HTTP POST / Auth| Gateway
    Gateway -->|Validate & Enqueue| Storage
    Gateway -.->|Fast Wakeup Notify| Worker
    Worker -->|Claim Pending Batch| Storage
    Worker <==|Encrypted WebSocket|=> WhatsApp
    Worker -.->|HMAC-SHA256 Webhooks| Clients
```

### Why This Architecture Works:
1. **Zero Client Blocking:** The PHP REST API immediately validates your payload and returns `200 OK` with a queued message ID in milliseconds. Your application never waits for WhatsApp network latency.
2. **Resilience to Network Drops:** Messages are safely held in MySQL/TiDB. If the WhatsApp socket temporarily disconnects, messages remain safely queued and resume dispatch upon reconnection.
3. **Internal Worker Security:** The Node.js Baileys worker listens strictly on internal localhost (`127.0.0.1:3107`) with shared secret validation (`X-Worker-Token`).

---

<a id="api-quick-reference"></a>
## 📖 API Quick Reference

### Inbound Messaging Endpoints
- **Primary Endpoint:** `POST /api/send.php`
- **Meta Cloud API Drop-In:** `POST /v20.0/{phone_number_id}/messages`
- **Aliases:** `POST /api/messages.php`, `POST /api/messages`, `POST /messages`

### Authentication Methods
- `X-API-Key: wa_live_...` (Recommended)
- `Authorization: Bearer wa_live_...`
- JSON Body: `{"api_key": "wa_live_...", ...}`

---

### Request Parameters Reference

| Field Name | Type | Mandatory? | Description |
| :--- | :---: | :---: | :--- |
| `to` / `recipient` | `string` | **Yes** | Destination phone number with country code (e.g. `919876543210` or `+15551234567`). |
| `message` / `caption` / `body` | `string` | **Conditional** | Message text or media caption. Required if no media attachment is provided. |
| `media_url` / `url` | `string` | Optional | Direct HTTP/HTTPS URL to media file (validated against SSRF; 20MB limit). |
| `type` | `string` | Optional | Declared payload type: `'text'`, `'image'`, `'document'`, `'video'`, or `'audio'`. |
| `base64` / `file_base64` | `string` | Optional | Base64-encoded file payload or Data URI string. |
| `filename` | `string` | Optional | Explicit file name for document attachments (e.g. `Invoice_8492.pdf`). |
| `ptt` | `boolean` | Optional | Set to `true` to deliver audio files as push-to-talk voice memos. |
| `humanize` | `boolean` | Optional | Simulates human typing presence (`composing`/`recording`) before sending. |
| `priority` | `string`/`int`| Optional | Queue priority: `'urgent'` / `'high'` (10), `'bulk'` / `'low'` (1), or integer 1-100. |

---

### API Examples

<details open>
<summary><b>1. Standard Flat JSON (Recommended)</b></summary>

```bash
curl -X POST "http://localhost:8080/api/send.php" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: wa_live_YOUR_API_KEY_HERE" \
  -d '{
    "to": "919876543210",
    "message": "Your booking for *Conference 2026* is confirmed! 🎫",
    "humanize": true
  }'
```
</details>

<details>
<summary><b>2. Meta WhatsApp Cloud API Drop-In Format</b></summary>

WhatsGateway parses official Meta Cloud API payloads natively:

```bash
curl -X POST "http://localhost:8080/v20.0/100012345/messages" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer wa_live_YOUR_API_KEY_HERE" \
  -d '{
    "messaging_product": "whatsapp",
    "recipient_type": "individual",
    "to": "919876543210",
    "type": "text",
    "text": {
      "preview_url": false,
      "body": "Hello via Meta Cloud API drop-in format! 🌐"
    }
  }'
```
</details>

<details>
<summary><b>3. Send PDF / Document via Remote URL</b></summary>

```bash
curl -X POST "http://localhost:8080/api/send.php" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: wa_live_YOUR_API_KEY_HERE" \
  -d '{
    "to": "919876543210",
    "type": "document",
    "media_url": "https://example.com/files/Monthly_Statement.pdf",
    "caption": "Your monthly statement is ready for review.",
    "filename": "Monthly_Statement.pdf"
  }'
```
</details>

<details>
<summary><b>4. Send Image via Multipart Form-Data Upload</b></summary>

```bash
curl -X POST "http://localhost:8080/api/send.php" \
  -H "X-API-Key: wa_live_YOUR_API_KEY_HERE" \
  -F "to=919876543210" \
  -F "caption=Product Brochure 📸" \
  -F "file=@/local/path/to/brochure.jpg"
```
</details>

---

### Contacts & Audience REST API Suite

Manage customer directories, opt-in compliance, and bulk imports via `ContactService`.

> 🛡️ **OWASP Privacy Protection:** To guarantee phone numbers are never leaked into web server access logs or edge CDN traces, the API actively rejects phone numbers passed in URL query strings (`?phone=...`) with an immediate `HTTP 400 Bad Request`. Pass phone numbers in the encrypted JSON request body or use synthetic integer IDs (`?id=142`).

- **List Contacts:** `GET /api/contacts.php?limit=50&search=Acme`
- **Create / Upsert Contact:** `POST /api/contacts.php` with `{"phone": "+919876543210", "name": "Acme Corp", "opt_in": true}`
- **Batch Contact Ingestion:** `POST /api/contacts-batch.php` (Atomic transaction up to 500 records)
- **Delete Contact by ID:** `DELETE /api/contacts.php?id=142`
- **Delete Contact by Phone:** `POST /api/contacts-delete.php` with `{"phone": "+919876543210"}`

---

### System Health Probe

Monitor system readiness via `GET /api/health.php` (no authentication required):

```json
{
  "ok": true,
  "status": "healthy",
  "timestamp": "2026-10-31T12:00:00+00:00",
  "service": "whatsapp-api-hub",
  "database": {
    "ok": true,
    "connected": true,
    "schema_ready": true,
    "missing_tables": []
  },
  "worker": {
    "ok": true,
    "status_code": 200,
    "service": "whatsapp-worker",
    "error": null
  }
}
```

---

### HTTP Status Codes & Error Handling

| HTTP Status | Meaning | Typical Cause & Resolution |
| :---: | :--- | :--- |
| `200 OK` | Message Enqueued | Request validated and placed into queue for worker dispatch. |
| `202 Accepted` | Test Message Enqueued | Dashboard test message accepted for immediate queueing. |
| `400 Bad Request` | Malformed Request | Invalid JSON structure or raw phone number in query string. |
| `401 Unauthorized` | Authentication Failure | Missing or invalid `X-API-Key` or `Authorization: Bearer` header. |
| `404 Not Found` | Unregistered Number | The recipient phone number is not registered on WhatsApp. |
| `405 Method Not Allowed`| Wrong HTTP Method | The endpoint expects `POST`. Check your HTTP client method. |
| `409 Conflict` | Device Not Connected | WhatsApp device is currently in `idle` or `disconnected` state. |
| `419 Security Timeout` | CSRF Token Mismatch | Web session expired. Refresh page and re-submit form. |
| `422 Unprocessable` | Validation Error | Missing recipient or message body; unsupported media extension. |
| `429 Too Many Requests`| Rate Limit Exceeded | API key rate limits exceeded. Adjust limits in dashboard. |
| `502 Bad Gateway` | Worker Unreachable | Node.js worker microservice is offline. Run `start-worker.bat start`. |
| `503 Service Degraded` | Health Check Degraded | Worker is offline or database connection is failing. |

---

<a id="client-sdks"></a>
## 💻 Client SDKs & Quickstarts

Ready-to-use, zero-dependency client implementations are located in [`quickstart/`](quickstart/):

| Language | Path | Features |
| :--- | :--- | :--- |
| **Python** | [`quickstart/python/send.py`](quickstart/python/send.py) | CLI arguments, interactive prompts, zero third-party packages |
| **Node.js** | [`quickstart/nodejs/send.js`](quickstart/nodejs/send.js) | Native `fetch`, zero npm dependencies |
| **PHP** | [`quickstart/php/send.php`](quickstart/php/send.php) | Strict types, native cURL client wrapper class |
| **Go** | [`quickstart/go/main.go`](quickstart/go/main.go) | Standard library `net/http` |
| **Java** | [`quickstart/java/SendWhatsApp.java`](quickstart/java/SendWhatsApp.java) | Compatible with Java 8, 11, 17, and 21+ using `HttpURLConnection` |

<details>
<summary><b>View Python Example</b></summary>

```python
import urllib.request
import json

url = "http://localhost:8080/api/send.php"
api_key = "wa_live_YOUR_API_KEY_HERE"

payload = {
    "to": "919876543210",
    "message": "Hello from Python! 🐍",
    "humanize": True
}

req = urllib.request.Request(
    url,
    data=json.dumps(payload).encode("utf-8"),
    headers={
        "Content-Type": "application/json",
        "X-API-Key": api_key
    }
)

with urllib.request.urlopen(req) as response:
    print(response.read().decode())
```
</details>

<details>
<summary><b>View Node.js Example</b></summary>

```javascript
const url = "http://localhost:8080/api/send.php";
const apiKey = "wa_live_YOUR_API_KEY_HERE";

async function sendMessage() {
  const response = await fetch(url, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-API-Key": apiKey
    },
    body: JSON.stringify({
      to: "919876543210",
      message: "Hello from Node.js! 🚀",
      humanize: true
    })
  });
  console.log(await response.json());
}

sendMessage();
```
</details>

---

## 🔔 Outbound Webhooks & HMAC Verification

Configure an HTTPS destination in **Webhooks** (`/webhooks`) to receive instant delivery and inbound message updates. Every dispatch is signed using HMAC-SHA256 in the `X-WA-Hub-Signature` header.

### Verifying Webhook Signatures:

#### In PHP:
```php
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_WA_HUB_SIGNATURE'] ?? '';
$expected = hash_hmac('sha256', $payload, $yourWebhookSecret);

if (!hash_equals($expected, $signature)) {
    http_response_code(403);
    exit('Invalid signature');
}
// Signature verified safely
```

#### In Node.js:
```javascript
import crypto from 'crypto';

function verifyWebhook(rawBody, signature, secret) {
  const expected = crypto.createHmac('sha256', secret).update(rawBody).digest('hex');
  return crypto.timingSafeEqual(Buffer.from(signature), Buffer.from(expected));
}
```

---

## 🤖 AI Assistant Studio, Memory Engine & App Store

WhatsGateway features a built-in Autonomous AI Agent architecture running directly on WhatsApp:

### 1. Dual-Tier Cognitive Memory Engine
- **Short-Term Dialogue Context:** Rolling sliding window (1,600 chars) with 45-minute inactivity decay. Silent chats decay to a clean slate, preventing stale context bleed.
- **Persistent Fact Notebook (`ai_user_memory`):** Durable attributes partitioned by scope:
  - *Personal Boss Mode:* Executive facts for the account owner (GSTIN, Home Address, Travel Preferences).
  - *Customer Support Mode:* Scoped per customer phone (VIP Status, Delivery Address, Preferences).
- **Top-K Token Governor:** Matches incoming questions against stored memories and injects only the **Top-3 relevant facts (< 120 tokens)** into the model prompt to keep latency low.
- **Conversational Memory Control:** WhatsApp users can self-manage facts (*"Remember my address is Flat 2B"* or *"Forget my old address"*).

### 2. Smart Few-Shot In-Context Training
- Define 3–5 **Golden Q&A Pairs** in `/memory` to guide model style and tone without fine-tuning.
- Define strict **Negative Constraints** (*"What NOT to do"*, such as *"NEVER quote discounts above 15%"*) to eliminate hallucinations.

### 3. Modular App Store & Function Calling
Pre-built tools enabled with 1 click:
- `web_search`: Live DuckDuckGo internet searches.
- `evaluate_math`: Exact financial math and formula calculation.
- `get_current_time`: Live IST/UTC time and business operating hours verification.
- `get_weather`: Open-Meteo real-time temperature and weather forecasts.
- `send_crm_lead`: Outbound webhooks to Zapier, Make, n8n, or CRM.
- `draft_email` & `confirm_action`: **Two-Phase Action Safety Protocol** — drafts formal emails and waits for explicit WhatsApp confirmation before sending.

---

## 🚀 Production Deployment & Operations

### Production Checklist
1. Set `APP_ENV=production` in `.env`.
2. Generate a strong, random `WORKER_TOKEN` (at least 32 characters, e.g. `openssl rand -hex 24`).
3. Set `ENABLE_REGISTRATION=false` to prevent unauthorized dashboard registrations.
4. Set `ENABLE_TEST_PAGE=false` to restrict internal developer test utilities.
5. Set `APP_SECURE_COOKIES=true` and ensure TLS/HTTPS is active.
6. Configure `APP_PUBLIC_URL=https://your-domain.com` (leave `APP_BASE_PATH=` empty).

### Deploying to Render / Railway with TiDB Cloud Serverless

The repository includes a production [`Dockerfile`](Dockerfile) with Supervisor process orchestration:

1. Connect your GitHub repository to Render or Railway.
2. Configure Environment Variables:
   - `DB_HOST`, `DB_PORT=4000`, `DB_NAME=whatsapp_api`, `DB_USER`, `DB_PASSWORD`, `DB_SSL=true`
   - `APP_ENV=production`, `APP_PUBLIC_URL=https://your-service.onrender.com`, `APP_BASE_PATH=`
   - `WORKER_TOKEN=your-random-32-char-token`, `ENABLE_REGISTRATION=false`
3. **Provision the First Admin Account:** Set temporary environment variables `INITIAL_ADMIN_EMAIL` and `INITIAL_ADMIN_PASSWORD` (minimum 12 characters). The Docker entrypoint executes [`scripts/create-initial-admin.php`](scripts/create-initial-admin.php) before launching services. Once created, delete these variables from your dashboard.
4. Startup automatically executes `php scripts/migrate.php` to verify all 17 tables are ready.
5. Verify health: `https://your-service.onrender.com/api/health.php` returns `status: "healthy"` and `database.ok: true`.

### Linux VPS Service Management (PM2)
```bash
cd /var/www/whatsapp-api/worker
npm install --omit=dev
npm install -g pm2
pm2 start server.mjs --name "whatsapp-worker"
pm2 save
pm2 startup
```

### Windows Background Worker (`start-worker.bat`)
```cmd
start-worker.bat start      :: Starts worker background process
start-worker.bat stop       :: Stops worker gracefully
start-worker.bat restart    :: Restarts worker process
start-worker.bat status     :: Shows current PID and status
start-worker.bat logs       :: Shows output and error log paths
```

---

## 🧪 Interactive Testing & Developer Tools

1. **Web Test Console:** Navigate to `http://localhost:8080/test/index.php` (when `ENABLE_TEST_PAGE=true`) to test API keys, payloads, and media URLs interactively.
2. **Interactive Terminal Sender (`send_whatsapp.py`):**
   ```bash
   python send_whatsapp.py
   ```
   Prompts for recipient and message, caches credentials, and sends via the REST API.
3. **Automated Syntax Checks:**
   ```bash
   # PHP syntax validation
   php -l public/api/send.php
   php -l public/api/health.php

   # Node.js worker syntax
   node --check worker/server.mjs
   ```

---

## ⚙️ Environment Configuration Reference

All settings are configured in `.env` and loaded by `config/app.php`:

| Variable | Default Value | Description |
| :--- | :--- | :--- |
| `APP_ENV` | `local` | Environment mode (`local` or `production`). Production enforces strict tokens. |
| `APP_NAME` | `WhatsGateway` | Application display name. |
| `APP_PUBLIC_URL` | `http://localhost/wa/public` | Canonical URL used for redirects, webhooks, and sitemaps. |
| `APP_BASE_PATH` | `/wa/public` | Subfolder base path (leave blank when using root domain in production). |
| `APP_SESSION_NAME` | `whatsgateway_session` | Name of PHP session cookie. |
| `APP_SECURE_COOKIES`| `false` (auto in prod) | Force HTTPS-only cookie attributes (`Secure; HttpOnly; SameSite=Lax`). |
| `ENABLE_REGISTRATION`| `false` | Enable/disable public user registration in the web UI. |
| `ENABLE_TEST_PAGE` | `true` (dev only) | Enables developer interactive tester at `/test-page.php`. |
| `DB_HOST` | `127.0.0.1` | MySQL, MariaDB, or TiDB server hostname. |
| `DB_PORT` | `3306` | Database port (3306 for MySQL, 4000 for TiDB Cloud). |
| `DB_NAME` | `whatsapp_api` | Database name. |
| `DB_USER` | `root` | Database username. |
| `DB_PASSWORD` | `""` | Database password. |
| `DB_SSL` | `false` | Enable TLS/SSL (auto-enabled for TiDB Cloud hostnames or port 4000). |
| `WORKER_URL` | `http://127.0.0.1:3107` | Internal HTTP address used by PHP to communicate with Node worker. |
| `WORKER_PORT` | `3107` | Port for the background worker daemon (defaults to `3107`). |
| `WORKER_TOKEN` | *(required in prod)* | Shared secret of at least 32 characters passed in `X-Worker-Token`. |
| `SEND_DELAY_MS` | `2500` | Base delay between message dispatches in milliseconds. |
| `QUEUE_POLL_MS` | `3000` | Polling frequency for claiming queued messages from database. |
| `LOG_LEVEL` | `warn` | Pino worker log verbosity (`debug`, `info`, `warn`, `error`). |
| `LOGIN_MAX_ATTEMPTS`| `5` | Maximum failed logins before IP lockout. |
| `LOGIN_LOCKOUT_MINUTES`| `15` | Lockout duration in minutes after exceeding failed attempts. |
| `DEFAULT_COUNTRY_CODE`| `91` | Default country code prefix applied to numbers without leading `+`. |

---

## 🗄️ Database Architecture (17 Application Tables)

The database schema is managed via the non-destructive migration runner [`php scripts/migrate.php`](scripts/migrate.php):

1. **`users`:** User accounts, hashed passwords, contact numbers, and timestamps.
2. **`api_keys`:** Single API keys storing `key_hash` (SHA-256), `key_prefix`, `status`, `ip_allowlist`, and rate limits.
3. **`api_request_logs`:** Audit trail capturing endpoints, IP addresses, HTTP status codes, and error logs.
4. **`campaigns`:** Bulk broadcast records with template message, scheduling, and target counts.
5. **`chatbot_rules`:** Keyword auto-reply rules (`exact`, `contains`, `starts_with`, `catch_all`).
6. **`contacts`:** Opted-in recipient directory with normalized phone numbers and consent flags.
7. **`login_attempts`:** Tracks failed login attempts per email/IP for security lockouts.
8. **`message_logs`:** Historic log of dispatched messages and provider delivery responses.
9. **`message_queue`:** Asynchronous FIFO queue with status (`queued`, `processing`, `sent`, `failed`), retry attempts, and priority.
10. **`password_resets`:** Temporary OTP codes with 15-minute expiration windows.
11. **`webhooks`:** Webhook targets containing HTTPS URL, HMAC secret, and enabled flag.
12. **`webhook_deliveries`:** Audit history of outbound webhook HTTP dispatches and response codes.
13. **`whatsapp_sessions`:** Baileys connection state (`idle`, `connecting`, `qr`, `connected`, `disconnected`), push name, and timestamps.
14. **`user_security`:** Two-Factor Authentication (RFC 6238 TOTP), hashed recovery codes, and security preferences.
15. **`media_assets`:** Uploaded media file metadata, SHA-256 checksums, and reference counting.
16. **`inbound_messages`:** Normalized record of incoming customer messages with processing status.
17. **`webhook_queue`:** Asynchronous delivery and retry queue for outbound HTTP webhooks.

Run migrations anytime without dropping data:
```bash
php scripts/migrate.php
```

---

## 🛡️ Anti-Ban Best Practices & Limitations

Because WhatsGateway connects via WhatsApp Web multi-device emulation:

### Anti-Ban Recommendations
1. **Always Enable `"humanize": true`:** Dispatches typing indicators (`composing`/`recording`) and realistic delays before transmission.
2. **Respect Opt-In Consent:** Only message users who have explicitly requested communications from you.
3. **Warm Up New Numbers Gradually:** On brand new WhatsApp numbers, start with 20–50 messages per day and scale gradually over 14 days. Avoid sudden bursts of hundreds of messages.
4. **Configure Rate Limits:** Set conservative limits in **API Keys** (e.g. 15–25 messages per minute).

### Known Limitations
- **Phone Active Requirement:** Your paired mobile phone must keep WhatsApp installed. If WhatsApp logs out all linked devices, re-scan the QR in the dashboard.
- **Protocol Boundary:** WhatsApp Web emulation supports text, media, documents, and simple templates. Interactive Meta Catalog flows or native WhatsApp Pay buttons are only available on Meta's official Cloud API.

---

<a id="faq"></a>
## ❓ Frequently Asked Questions (FAQ)

<details>
<summary><strong>Q1: Is WhatsGateway really 100% free? Are there any hidden message costs?</strong></summary>

**Yes, 100% free software.** WhatsGateway is licensed under MIT. There are no per-conversation fees, no message volume surcharges, and no monthly subscriptions. You only manage your own host or server.
</details>

<details>
<summary><strong>Q2: How does WhatsGateway work without Meta Business verification?</strong></summary>

WhatsGateway connects directly to WhatsApp Web's multi-device protocol via [WhiskeySockets/Baileys](https://github.com/WhiskeySockets/Baileys). You simply scan a QR code from your existing WhatsApp mobile app (just like WhatsApp Web). No Facebook Developer account, credit card, or business paperwork is required.
</details>

<details>
<summary><strong>Q3: Can I drop WhatsGateway into existing codebases built for Meta Cloud API?</strong></summary>

Yes! The endpoint `POST /v20.0/{phone_number_id}/messages` accepts official Meta Graph API JSON payloads (`messaging_product: "whatsapp"`, `recipient_type: "individual"`, `text.body`, etc.) and returns compliant `wamid` response structures.
</details>

<details>
<summary><strong>Q4: Can I use my personal WhatsApp number or WhatsApp Business number?</strong></summary>

Yes. Any active WhatsApp phone number can be connected in seconds via the dashboard device pairing screen.
</details>

<details>
<summary><strong>Q5: Can I deploy WhatsGateway with Docker?</strong></summary>

Yes. A multi-container [`docker-compose.yml`](docker-compose.yml) and production [`Dockerfile`](Dockerfile) are included. Run `docker compose up -d` to launch PHP 8.2 Apache, Node.js 20, and MySQL 8.0 in seconds.
</details>

---

## ⭐ Support Open Source — Star on GitHub!

If WhatsGateway saves you development time or helps your project avoid expensive SaaS messaging fees, please consider giving it a **Star (⭐)**!

Your support helps more developers discover free open-source tools and motivates continued development.

<p align="center">
  <a href="https://github.com/navneetyadav-code/whatsapp-api/stargazers">
    <img src="https://img.shields.io/badge/⭐%20Star%20WhatsGateway-on%20GitHub-gold?style=for-the-badge&logo=github" alt="Star on GitHub">
  </a>
</p>

---

## 🤝 Contributing & Community

Contributions are welcomed! Whether reporting bugs, improving documentation, or submitting pull requests:

1. **Discussions:** Have a question or idea? Join [GitHub Discussions](https://github.com/navneetyadav-code/whatsapp-api/discussions).
2. **Issue Tracker:** Found a bug? Open a [GitHub Issue](https://github.com/navneetyadav-code/whatsapp-api/issues).
3. **Pull Requests:** Check our [Contributing Guide](CONTRIBUTING.md) and run local quality checks:

```powershell
# Check PHP syntax
foreach ($file in Get-ChildItem -Recurse -Filter *.php -File) { php -l $file.FullName }

# Check Node.js worker syntax
node --check worker/server.mjs
```

---

## ⚖️ License & Trademark Disclaimer

### License
This project is open-source software licensed under the [MIT License](LICENSE).

### Trademark Disclaimer
WhatsGateway is an independent open-source project and is **not affiliated with, associated with, authorized by, endorsed by, or in any way officially connected with Meta Platforms, Inc. or WhatsApp LLC.** WhatsApp is a registered trademark of WhatsApp LLC.

---

<p align="center">
  <strong>WhatsGateway</strong> — Crafted with ❤️ by <a href="https://github.com/navneetyadav-code">Navneet Kumar Yadav</a> & the Open-Source Community.
</p>
