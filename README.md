# 🚀 WhatsApp API Hub (Self-Hosted, Open-Source WhatsApp REST API Gateway)

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![CI Quality Check](https://img.shields.io/badge/CI-Passing-brightgreen.svg)](.github/workflows/ci.yml)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4.svg?logo=php&logoColor=white)](https://www.php.net/)
[![Node.js](https://img.shields.io/badge/Node.js-18%2B%20%7C%2020%2B-339933.svg?logo=node.js&logoColor=white)](https://nodejs.org/)
[![Database](https://img.shields.io/badge/Database-MySQL%20%7C%20TiDB%20Serverless-4479A1.svg?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Engine](https://img.shields.io/badge/Engine-WhiskeySockets%2FBaileys-25D366.svg?logo=whatsapp&logoColor=white)](https://github.com/WhiskeySockets/Baileys)
[![Cost](https://img.shields.io/badge/Cost-100%25%20Free%20%26%20Open%20Source-orange.svg)](#1-project-overview--purpose)
[![PRs Welcome](https://img.shields.io/badge/PRs-Welcome-brightgreen.svg)](CONTRIBUTING.md)

> **A 100% free, self-hosted WhatsApp REST API Gateway and Web Management Dashboard built with PHP 8.2+, Node.js (Baileys), and MySQL/TiDB.**  
> Send messages, transmit rich media (PDFs, images, audio, video), automate conversational auto-replies, track delivery queues, and receive real-time webhooks — **with zero Meta Cloud API fees, zero per-conversation costs, and no credit card required.**

---

## Table of Contents

1. [Project Overview & Purpose](#1-project-overview--purpose)
2. [Key Features & Capabilities](#2-key-features--capabilities)
3. [Technology Stack & Runtime Requirements](#3-technology-stack--runtime-requirements)
4. [System Architecture & Directory Structure](#4-system-architecture--directory-structure)
5. [Prerequisites & System Requirements](#5-prerequisites--system-requirements)
6. [Installation & Dependency Setup](#6-installation--dependency-setup)
7. [Environment Variable Configuration](#7-environment-variable-configuration)
8. [Database Setup, Migrations & Initialization](#8-database-setup-migrations--initialization)
9. [Running the Application (Local & Production)](#9-running-the-application-local--production)
10. [Comprehensive API Endpoint Reference](#10-comprehensive-api-endpoint-reference)
11. [Request & Response Examples](#11-request--response-examples)
12. [Authentication, Authorization & Security](#12-authentication-authorization--security)
13. [Error Handling & Status Codes](#13-error-handling--status-codes)
14. [Testing & Quality Verification](#14-testing--quality-verification)
15. [Deployment Guide (Docker, Cloud, VPS)](#15-deployment-guide-docker-cloud-vps)
16. [External Integrations & Background Workers](#16-external-integrations--background-workers)
17. [Logging, Monitoring & Debugging](#17-logging-monitoring--debugging)
18. [Anti-Ban Practices, Known Limitations & Notes](#18-anti-ban-practices-known-limitations--notes)

---

## 1. Project Overview & Purpose

Commercial WhatsApp Business API providers (such as Twilio, MessageBird, or Meta Cloud API directly) enforce pay-per-conversation billing (\$0.05 – \$0.15+ per dialogue), require business registration paperwork, mandate pre-approved message templates, and demand credit card verifications.

**WhatsApp API Hub** is an open-source, developer-centric alternative for individuals, startups, and software engineering teams who require a reliable WhatsApp notification gateway without external per-message costs:

- **100% Free & Open Source:** Licensed under MIT with no hidden subscription fees.
- **Any Regular or Business WhatsApp Number:** Connect your existing phone number in seconds by scanning a QR code via WhatsApp Web multi-device.
- **Meta Cloud API Drop-In Compatibility:** Accepts both simple flat JSON and official Meta WhatsApp Cloud API schemas (`messaging_product`, `recipient_type`, `text.body`, `template`).
- **Complete Self-Hosting:** Your messages, media files, and cryptographic keys stay on your own infrastructure.

### Comparison Table

| Dimension | WhatsApp API Hub | Meta Cloud API | Twilio WhatsApp | Traditional Cloud Gateways |
| :--- | :---: | :---: | :---: | :---: |
| **Pricing** | **100% Free ($0)** | Pay-per-conversation | Pay-per-message + markup | $49 – $299 / month |
| **Credit Card Required** | ❌ **No** | ✅ Yes | ✅ Yes | ✅ Yes |
| **Template Approval** | ❌ **No (Freeform text & media)** | ✅ Strict mandatory | ✅ Strict mandatory | ✅ Strict mandatory |
| **Setup Time** | **2 minutes (Scan QR)** | Days / Weeks | Days / Weeks | Hours / Days |
| **Self-Hosted** | ✅ **Yes (Full privacy)** | ❌ Cloud only | ❌ Cloud only | ❌ Cloud only |
| **Web Dashboard** | ✅ **Included (PHP + Tailwind)** | ❌ None | ⚠️ Basic logs only | ✅ Varies |
| **Media Attachments** | ✅ **URL, Base64 & Upload** | ✅ URL / Upload | ✅ URL only | ✅ Varies |
| **Chatbot Engine** | ✅ **Built-in Rule Matcher** | ❌ Custom webhook required | ❌ Studio build required | ⚠️ Add-on cost |

---

## 2. Key Features & Capabilities

- 🔑 **Single-Key REST Authentication:** Authenticate requests using a single token (`X-API-Key: wa_live_...` or `Authorization: Bearer wa_live_...` or query parameter `access_token`).
- 📁 **Universal Media Engine:** Ingests media via direct HTTP/HTTPS URLs (with SSRF protection), Base64 data strings/Data URIs, or standard `multipart/form-data` uploads up to 20MB. Supports Images (JPG, PNG, GIF, WEBP), Documents (PDF, DOCX, XLSX, TXT, CSV), Videos (MP4, MOV), and Audio (MP3, OGG, WAV, M4A) including push-to-talk voice notes (`ptt: true`).
- 🤖 **Chatbot Automation:** Incoming messages are evaluated in real time against customizable trigger rules (`exact`, `contains`, `starts_with`, or `catch_all`) to automatically dispatch instant replies.
- 📬 **Asynchronous Delivery Queue:** Decouples API client latency from WhatsApp WebSocket delivery. Enqueued items are processed with automatic backoff retries, dead-letter tracking, and queue monitoring.
- 🛡️ **Humanized Anti-Ban Delivery:** Emulates human chat behavior by generating randomized presence indicators (`available`, `composing`, `recording`) and delay intervals before message transmission.
- 🔔 **HMAC-Signed Webhooks:** Dispatches instantaneous HTTP POST notifications to your server on `message.sent` and `message.failed` events, signed with `X-WA-Hub-Signature` (HMAC-SHA256).
- 👥 **Contacts & Broadcast Campaigns:** Built-in address book supporting single contact creation, CSV bulk imports, Google Contacts (People API) synchronization, vCard (`.vcf`) exports, and personalized template broadcasts (`{name}`, `{phone}`).
- 🔐 **Enterprise Security:** API keys are stored exclusively as SHA-256 hashes with configurable per-minute, per-hour, and per-day rate limits and CIDR/IP allowlists. Brute-force login attempts are throttled, and uploaded file directories are locked down against arbitrary script execution.
- 🔁 **Self-Service Password Recovery:** 6-digit OTP password reset workflow delivered via SMTP email and/or WhatsApp message.
- 🌐 **Social Authentication:** Optional Google OAuth2 single sign-on.

---

## 3. Technology Stack & Runtime Requirements

### Backend Components
- **API & Web Tier:** PHP 8.1+ / 8.2+ with extensions: `pdo_mysql`, `curl`, `mbstring`, `openssl`, `fileinfo`, `json`.
- **Worker Daemon:** Node.js 18.x or 20.x (ES Modules) running `@whiskeysockets/baileys` (^6.6.0), `express` (^4.19.2), `mysql2` (^3.9.1), `pino` (^8.19.0), and `qrcode` (^1.5.3).
- **Database:** MySQL 8.0+, MariaDB 10.4+, or TiDB Cloud Serverless (SSL-encrypted on port 4000).
- **Web Server:** Apache 2.4+ with `mod_rewrite` and `mod_headers` (or Nginx reverse proxy).

### Client SDKs & Quickstart Programs
Dedicated, zero-dependency examples in five programming languages are located in the [`quickstart/`](quickstart/) directory:
- **Python:** `python quickstart/python/send.py` (CLI arguments, interactive prompt, zero third-party dependencies).
- **Node.js:** `node quickstart/nodejs/send.js` (native `fetch`, zero npm dependencies).
- **PHP:** `php quickstart/php/send.php` (strict types, native cURL wrapper class).
- **Go:** `go run quickstart/go/main.go` (standard library `net/http`).
- **Java:** `java quickstart/java/SendWhatsApp.java` (Java 11+ `java.net.http.HttpClient`).

---

## 4. System Architecture & Directory Structure

```mermaid
flowchart TD
    subgraph Clients["Client Integrations"]
        C1["cURL / HTTP Client"]
        C2["Python / Node / Go / Java SDK"]
        C3["Web Browser / Dashboard"]
    end

    subgraph Apache["Apache HTTP Server / DocumentRoot: public/"]
        A1[".htaccess URL Rewriter"]
        A2["public/api/send.php (REST API)"]
        A3["public/api/health.php (Health Check)"]
        A4["public/index.php (Front Controller)"]
        A5["public/session.php (AJAX Session API)"]
    end

    subgraph Core["Core PHP Engine (src/)"]
        B1["src/bootstrap.php"]
        B2["src/lib/ApiCredentials.php (Auth & Limits)"]
        B3["src/lib/MessageQueue.php (Enqueue & Stats)"]
        B4["src/lib/WorkerClient.php (Internal RPC)"]
        B5["src/lib/Support.php (Media Downloader & SSRF)"]
        B6["src/pages/ (Dashboard & Views)"]
    end

    subgraph DB[("MySQL / MariaDB / TiDB Cloud")]
        D1["message_queue"]
        D2["api_keys & api_request_logs"]
        D3["whatsapp_sessions"]
        D4["webhooks & webhook_deliveries"]
        D5["contacts & campaigns & chatbot_rules"]
    end

    subgraph Worker["Node.js Background Worker (worker/server.mjs:3107)"]
        W1["Express IPC API (Queue Notify & Session)"]
        W2["Queue Poller & Message Claimer"]
        W3["Humanized Presence Delay"]
        W4["Baileys WhatsApp Client (MultiFileAuthState)"]
        W5["Webhook Dispatcher (HMAC-SHA256)"]
        W6["Chatbot Auto-Responder"]
    end

    subgraph WhatsApp["WhatsApp Network"]
        WA["WhatsApp Web Multi-Device WebSocket"]
    end

    C1 -->|POST /api/send.php| A2
    C2 -->|POST /v20.0/.../messages| A1 --> A2
    C3 -->|Web UI & AJAX| A4 & A5
    A2 & A4 & A5 --> Core
    Core -->|Read / Write| DB
    Core -->|POST /queue/notify| W1
    W2 -->|Poll & Claim Messages| DB
    W2 --> W3 --> W4
    W4 <-->|Encrypted Noise Socket| WA
    W4 -->|Incoming message upsert| W6 -->|Insert reply| D1
    W4 -->|Delivery Event| W5 -->|HTTPS Callback| C1
```

### Directory Structure

```text
whatsap_api/
├── .codex-screenshots/           # UI screenshots and validation artifacts
├── .github/                      # Issue templates, PR templates, and CI workflow (ci.yml)
├── api_key/                      # Generated credentials exports
├── config/
│   └── app.php                   # Central application configuration loader & .env parser
├── docker/
│   ├── entrypoint.sh             # Docker startup script, env exporter & Apache config
│   └── supervisord.conf          # Supervisor dual-process manager (Apache + Node.js)
├── examples/                     # Ready-to-use snippets (curl, go, javascript, php, python)
├── prod/ / produ/                # Production database schemas & SSL root certificates (isrgrootx1.pem)
├── public/                       # Web server public root (DocumentRoot)
│   ├── .htaccess                 # Apache rewrites (clean URLs & Meta API compatibility)
│   ├── index.php                 # Main front controller & router
│   ├── robots.php / robots.txt   # Search crawler instructions
│   ├── session.php               # AJAX session management & real-time queue endpoints
│   ├── sitemap.php               # XML Sitemap
│   ├── test-page.php             # Developer test page wrapper
│   ├── api/
│   │   ├── health.php            # Public health-check endpoint
│   │   ├── messages.php          # Meta Cloud API endpoint alias (requires send.php)
│   │   └── send.php              # Core REST messaging endpoint
│   ├── assets/                   # CSS, JS (dashboard.js), templates, and branding
│   └── uploads/                  # Temporary media downloads (protected by .htaccess)
├── quickstart/                   # Standalone zero-dependency client apps in 5 languages
│   ├── go/                       # Go quickstart program
│   ├── java/                     # Java quickstart program
│   ├── nodejs/                   # Node.js quickstart program
│   ├── php/                      # PHP quickstart program
│   └── python/                   # Python quickstart program
├── src/                          # Application source code
│   ├── bootstrap.php             # Session initializer, error handlers, and lib imports
│   ├── lib/
│   │   ├── ApiCredentials.php    # API key generation, hashing, rate limiting, and auth
│   │   ├── Auth.php              # User authentication, registration, session management
│   │   ├── Database.php          # PDO connection factory with automatic TiDB/SSL support
│   │   ├── MessageQueue.php      # Message enqueue, campaign dispatch, retry, and metrics
│   │   ├── Support.php           # SSRF validator, media downloader, MIME detector, emailer
│   │   └── WorkerClient.php      # HTTP client communicating with Node.js worker
│   └── pages/                    # Web views & components (dashboard, docs, auth, chatbot, etc.)
├── test/
│   └── index.php                 # Interactive developer test console
├── worker/                       # Background Baileys worker daemon
│   ├── package.json              # Node.js dependencies
│   ├── server.mjs                # Baileys client, queue loop, chatbot matcher, webhooks
│   └── worker.pid / *.log        # Worker runtime logs and process identifier
├── .dockerignore                 # Docker build exclusion rules
├── .env.example                  # Environment configuration template
├── .gitignore                    # Git version control exclusions
├── .htaccess                     # Root directory protection & redirect rules
├── app.php                       # Alias returning config/app.php
├── database.sql                  # Canonical MySQL schema dump
├── Dockerfile                    # Container definition (PHP 8.2 + Apache + Node.js 20)
├── index.php                     # Root redirector forwarding to public/
├── send_whatsapp.py              # Interactive Python terminal sender tool
├── setup.php                     # Automated browser-based setup wizard
└── start-worker.bat              # Windows background worker daemon controller
```

---

## 5. Prerequisites & System Requirements

| Requirement | Supported Version | Notes |
| :--- | :--- | :--- |
| **Operating System** | Windows 10/11, Ubuntu 20.04+, Debian 11+, macOS | Cross-platform |
| **PHP Runtime** | PHP 8.1.0 or higher (PHP 8.2 recommended) | Must have `pdo_mysql`, `curl`, `mbstring`, `fileinfo` |
| **Node.js** | Node.js 18.x or 20.x LTS | Required for Baileys WebSocket worker |
| **Database** | MySQL 8.0+, MariaDB 10.4+, or TiDB Cloud Serverless | UTF8MB4 collation required |
| **Web Server** | Apache 2.4+ with `mod_rewrite` or Nginx | Must support rewrite rules and `.htaccess` |

---

## 6. Installation & Dependency Setup

### Option A: Local Development Setup (XAMPP on Windows)

1. **Clone the repository:**
   Clone into your local web root (e.g. `c:\xampp\htdocs\wa` or `c:\xampp\htdocs\whatsapp-api`):
   ```bash
   git clone https://github.com/navneetyadav-code/whatsapp-api.git c:\xampp\htdocs\wa
   ```

2. **Create the MySQL Database:**
   Open phpMyAdmin or MySQL CLI and run:
   ```sql
   CREATE DATABASE whatsapp_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
   For a brand-new local database, run `php scripts/migrate.php`. The legacy `database.sql` is a dump with destructive `DROP TABLE` statements; do not import it into a database that has existing data.

3. **Configure Environment:**
   Copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```
   Set your local parameters in `.env`:
   ```ini
   APP_ENV=local
   APP_NAME="WhatsApp API Hub"
   APP_PUBLIC_URL=http://localhost/wa/public
   APP_BASE_PATH=/wa/public

   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=whatsapp_api
   DB_USER=root
   DB_PASSWORD=
   DB_SSL=false

   WORKER_URL=http://127.0.0.1:3107
   WORKER_TOKEN=my-secure-worker-token-12345
   DEFAULT_COUNTRY_CODE=91
   ```

4. **Install Worker Dependencies:**
   ```bash
   cd worker
   npm install
   ```

5. **Start the Background Worker:**
   - **Interactive Menu:** Run `start-worker.bat` from the root folder.
   - **Command Line:** Run `start-worker.bat start` or `node worker/server.mjs`.

6. **Access the Application:**
   Open your browser to: `http://localhost/wa/public` (or `http://localhost/wa/`).

---

### Option B: Linux Server Setup (Ubuntu / Debian / VPS)

1. **Install System Dependencies:**
   ```bash
   sudo apt-get update
   sudo apt-get install -y php8.2 php8.2-curl php8.2-mysql php8.2-mbstring php8.2-xml \
       apache2 libapache2-mod-php8.2 nodejs npm git
   sudo a2enmod rewrite headers
   ```

2. **Clone & Configure DocumentRoot:**
   ```bash
   cd /var/www
   sudo git clone https://github.com/navneetyadav-code/whatsapp-api.git whatsapp-api
   cd whatsapp-api
   cp .env.example .env
   ```
   Update Apache virtual host configuration to point `DocumentRoot` to `/var/www/whatsapp-api/public` with `AllowOverride All`.

3. **Install Worker Dependencies & Manage Process:**
   ```bash
   cd /var/www/whatsapp-api/worker
   npm install --omit=dev
   npm install -g pm2
   pm2 start server.mjs --name "wa-worker"
   pm2 save
   pm2 startup
   ```

4. **Set Permissions:**
   ```bash
   sudo chown -R www-data:www-data /var/www/whatsapp-api/public/uploads
   sudo chmod -R 755 /var/www/whatsapp-api/public/uploads
   ```

---

### Option C: Browser-Based Setup Wizard

If you are deploying on a fresh environment, navigate to `http://localhost/wa/setup.php`. The wizard will:
1. Test your database server connection.
2. Create the database and import `database.sql` (legacy wizard path for a fresh install only; it is destructive and must not be used with existing data). For deployments with an existing database, run `php scripts/migrate.php` instead.
3. Create an initial administrator account.
4. Generate the `.env` configuration file with a secure random `WORKER_TOKEN`.
5. Execute `npm install` inside the `worker/` directory automatically.

---

## 7. Environment Variable Configuration

All application configurations are managed in `.env` at the root directory and loaded via `config/app.php`:

| Variable | Default Value | Description |
| :--- | :--- | :--- |
| `APP_ENV` | `local` | Environment mode (`local` or `production`). Production enforces non-default tokens. |
| `APP_NAME` | `WhatsApp API Hub` | Application display name. |
| `APP_PUBLIC_URL` | `http://localhost/wa/public` | Canonical public URL used for redirects, webhooks, and sitemaps. |
| `APP_BASE_PATH` | `/wa/public` | Subfolder base path (leave blank when using root domain in production). |
| `APP_SESSION_NAME` | `wa_api_hub` | PHP session cookie name. |
| `APP_SECURE_COOKIES` | `false` (auto in prod) | Force HTTPS-only cookie attributes (`Secure; HttpOnly; SameSite=Lax`). |
| `ENABLE_REGISTRATION` | `true` | Set to `false` in production to prevent unauthorized public registrations. |
| `ENABLE_TEST_PAGE` | `true` (dev only) | Enables developer interactive tester at `/test-page.php`. |
| `DEFAULT_COUNTRY_CODE` | `91` | Default country code prefix applied to numbers without a leading `+`. |
| `DB_HOST` | `127.0.0.1` | MySQL / TiDB server hostname. |
| `DB_PORT` | `3306` | Database port (3306 for MySQL, 4000 for TiDB Cloud). |
| `DB_NAME` | `whatsapp_api` | Database name. |
| `DB_USER` | `root` | Database username. |
| `DB_PASSWORD` | `""` | Database password. |
| `DB_SSL` | `false` | Enable TLS/SSL. It is always enabled for TiDB Cloud hostnames or port 4000, with certificate verification. |
| `DB_SSL_CA` | system CA bundle | Optional filesystem path to a trusted CA bundle; never disables certificate verification. |
| `DB_DSN` | *(computed)* | Optional MySQL PDO DSN override; its database must remain `whatsapp_api`. |
| `WORKER_URL` | `http://127.0.0.1:<WORKER_PORT>` (default port `3107`) | Internal HTTP endpoint used by PHP to call the Node worker. The Render Docker entrypoint forces this to the co-located worker loopback URL. |
| `WORKER_PORT` | `3107` | Port for the background worker microservice. PHP derives its default `WORKER_URL` from this value. |
| `WORKER_TOKEN` | *(required in prod)* | Random shared secret of at least 32 characters passed in `X-Worker-Token`; generate it and set it in Render Environment Variables. |
| `WA_SESSION_DIR` | *(platform default)* | Directory where Baileys multi-file authentication keys are stored. |
| `SEND_DELAY_MS` | `2500` | Base delay between message dispatches in milliseconds. |
| `QUEUE_POLL_MS` | `3000` | Polling frequency for claiming queued messages from the database. |
| `LOG_LEVEL` | `warn` | Pino worker log verbosity (`debug`, `info`, `warn`, `error`). |
| `LOGIN_MAX_ATTEMPTS` | `5` | Maximum failed login attempts allowed before IP lockout. |
| `LOGIN_LOCKOUT_MINUTES`| `15` | Lockout duration in minutes after exceeding max failed login attempts. |
| `OTP_VIA_EMAIL` | `true` | Deliver password reset OTPs via SMTP email. |
| `OTP_VIA_PHONE` | `true` | Deliver password reset OTPs via WhatsApp using system API key. |
| `SYSTEM_API_KEY` | `""` | Dedicated API key used for internal system notifications (e.g. OTP delivery). |
| `SMTP_HOST` | `""` | SMTP mail server hostname (e.g. `smtp.gmail.com`). |
| `SMTP_PORT` | `587` | SMTP port (typically 587 for STARTTLS). |
| `SMTP_USER` | `""` | SMTP username / email address. |
| `SMTP_PASS` | `""` | SMTP password or App Password. |
| `SMTP_FROM` | `noreply@localhost` | Outgoing email sender address. |
| `SMTP_FROM_NAME` | `WhatsApp API Hub` | Outgoing email sender name. |
| `GOOGLE_CLIENT_ID` | `""` | Google OAuth2 Client ID (for login and Google Contacts sync). |
| `GOOGLE_CLIENT_SECRET` | `""` | Google OAuth2 Client Secret. |

---

## 8. Database Setup, Migrations & Initialization

The current application schema is defined by the non-destructive, repeatable [`001_initial_schema.sql`](migrations/001_initial_schema.sql) migration. It is based on the actual table definitions in [`database.sql`](database.sql), but contains no `DROP`, `TRUNCATE`, or seed-data statements. The application uses 13 tables: `users`, `api_keys`, `api_request_logs`, `campaigns`, `chatbot_rules`, `contacts`, `login_attempts`, `message_logs`, `message_queue`, `password_resets`, `webhooks`, `webhook_deliveries`, and `whatsapp_sessions`. The migration repairs missing indexes and foreign keys, validates existing columns, and records `001_initial_schema` in `schema_migrations`. It never deletes or resets existing rows. Existing tables with incompatible columns or conflicting data stop initialization and require a reviewed follow-up migration.

Run the initializer manually with `php scripts/migrate.php`. Docker startup runs the same command before Supervisor starts Apache or the queue worker. If it fails, the container exits with a sanitized error and the worker does not start. Health checks require both a live database connection and all 13 application tables.

### Key Tables Overview

```mermaid
erDiagram
    users ||--o{ api_keys : "owns"
    users ||--o{ api_request_logs : "generates"
    users ||--o{ message_queue : "enqueues"
    users ||--o{ message_logs : "records"
    users ||--|| whatsapp_sessions : "maintains"
    users ||--|| webhooks : "configures"
    users ||--o{ webhook_deliveries : "triggers"
    users ||--o{ contacts : "manages"
    users ||--o{ campaigns : "schedules"
    users ||--o{ chatbot_rules : "defines"
```

1. **`users`:** User accounts, hashed passwords (`password_hash`), contact phone numbers, timestamps.
2. **`api_keys`:** API key records storing `key_hash` (SHA-256), `key_prefix`, `secret_hash`, `status` (`active`/`disabled`), `ip_allowlist`, and rate limits (`rate_per_minute`, `rate_per_hour`, `rate_per_day`, `allow_humanize`).
3. **`api_request_logs`:** Audit log capturing endpoint, IP address, HTTP status code, generated request ID, and error messages.
4. **`message_queue`:** Asynchronous FIFO queue containing `recipient`, `normalized_recipient`, `message_type` (`text`, `image`, `document`, `video`), `body`, JSON `payload`, `status` (`queued`, `processing`, `sent`, `failed`, `cancelled`), `attempts`, `available_at`, `locked_at`, and `locked_by`.
5. **`message_logs`:** Historic log of dispatched messages and provider delivery responses.
6. **`whatsapp_sessions`:** Baileys connection state (`idle`, `connecting`, `qr`, `connected`, `disconnected`, `error`), device phone number, push name, sleep status, and timestamps.
7. **`webhooks`:** Webhook subscription containing HTTPS `target_url`, HMAC `secret`, and `enabled` flag.
8. **`webhook_deliveries`:** Audit history of outbound webhook HTTP dispatches, status codes, and retry attempts.
9. **`contacts`:** Opted-in recipient directory with normalized phone numbers and import notes.
10. **`campaigns`:** Bulk broadcast records with template message and target counts.
11. **`chatbot_rules`:** Keyword-based auto-reply rules (`match_type`: `exact`, `contains`, `starts_with`, `catch_all`).
12. **`login_attempts`:** Tracks failed login attempts per email and IP for security lockouts.
13. **`password_resets`:** Temporary OTP codes with 15-minute expiration windows.

---

## 9. Running the Application (Local & Production)

### Windows Service Management (`start-worker.bat`)

The included Windows script manages the Node.js background worker as a background process:

```cmd
:: Launch interactive console menu
start-worker.bat

:: Direct CLI subcommands
start-worker.bat start      :: Starts worker via PowerShell background process
start-worker.bat stop       :: Kills worker PID gracefully
start-worker.bat restart    :: Stops and restarts worker
start-worker.bat status     :: Displays live running status and PID
start-worker.bat logs       :: Shows output and error log paths
```

### Production Process Management (PM2 on Linux)

```bash
cd /var/www/whatsapp-api/worker
pm2 start server.mjs --name "whatsapp-worker" --watch false
pm2 logs whatsapp-worker
```

### Docker Container Execution

```bash
# Build Docker image
docker build -t whatsapp-api-hub .

# Run container with environment configuration
docker run -d \
  -p 80:80 \
  --name whatsapp-api \
  --env-file .env \
  -v wa_sessions:/var/www/html/whatsapp-api-data/sessions \
  whatsapp-api-hub
```

---

## 10. Comprehensive API Endpoint Reference

### 10.1 Public REST Messaging Endpoints

All inbound messaging endpoints point to the same handler (`public/api/send.php`) and accept signed JSON or multipart form-data.

#### `POST /api/send.php` (Core REST Endpoint)
- **Direct Path:** `/api/send.php`
- **Aliases & Rewrites:**
  - `POST /api/messages.php`
  - `POST /api/messages`
  - `POST /messages`
  - `POST /v20.0/{phone_number_id}/messages` (Meta Cloud API route compatibility)
- **Authentication:**
  - `X-API-Key: wa_live_...` OR
  - `Authorization: Bearer wa_live_...` OR
  - URL Query parameter: `?access_token=wa_live_...` (or `?api_key=wa_live_...`)
- **Headers:** `Content-Type: application/json` or `multipart/form-data`
- **Rate Limiting:** Enforced per API key (`rate_per_minute`, `rate_per_hour`, `rate_per_day`). Exceeding returns `429 Too Many Requests`.
- **IP Filtering:** Enforced if `ip_allowlist` is populated on the API key.

#### Request Parameters Reference:

| Field Name | Type | Mandatory? | Description |
| :--- | :--- | :--- | :--- |
| `to` / `recipient` | `string` | **Yes** | Destination phone number. Accepts international numbers (`+919876543210`, `919876543210`) or 10-digit Indian numbers (auto-prefixed with `+91`). |
| `message` / `caption` / `body` | `string` | **Conditional** | Text content or media caption. Required if no media file/URL is supplied. Supports WhatsApp markdown: `*bold*`, `_italic_`, `~strike~`, ````code````. |
| `media_url` / `url` / `link` | `string` | Optional | Direct public HTTP/HTTPS URL to media file. Validated against SSRF and downloaded securely (20MB limit). |
| `type` | `string` | Optional | Declared payload type: `'text'`, `'image'`, `'document'`, `'video'`, or `'audio'`. Auto-detected if omitted. |
| `base64` / `file_base64` | `string` | Optional | Base64-encoded file payload or Data URI string (`data:image/jpeg;base64,...`). |
| `filename` | `string` | Optional | Explicit file name for documents or attachments (e.g. `invoice_october.pdf`). |
| `ptt` | `boolean` | Optional | When sending audio, set to `true` to deliver as a push-to-talk voice memo. |
| `humanize` | `boolean` | Optional | Simulates human typing presence (`composing`/`recording`) with delay before sending. |
| `campaign_id` | `integer`| Optional | Associates the message with a campaign ID in analytics. |

---

### 10.2 System Health Check Endpoint

#### `GET /api/health.php`
- **Method:** `GET`
- **Authentication:** None (Public)
- **Purpose:** Verifies operational readiness of the PHP gateway and the background Node.js worker microservice.
- **Success Response (`200 OK`):**
  ```json
  {
    "ok": true,
    "status": "healthy",
    "timestamp": "2026-10-15T12:00:00+00:00",
    "service": "whatsapp-api-hub",
    "worker": {
      "ok": true,
      "status_code": 200,
      "service": "whatsapp-worker",
      "error": null
    }
  }
  ```
- **Failure Response (`503 Service Unavailable`):**
  ```json
  {
    "ok": false,
    "status": "degraded",
    "timestamp": "2026-10-15T12:00:00+00:00",
    "service": "whatsapp-api-hub",
    "worker": {
      "ok": false,
      "status_code": 0,
      "service": null,
      "error": "Worker is not reachable."
    }
  }
  ```

---

### 10.3 Session Management Endpoints (`public/session.php`)

Used by the web dashboard and AJAX clients to control the WhatsApp connection lifecycle. Requires an authenticated user session cookie; POST actions require `csrf_token`.

| Action Parameter | Method | Description |
| :--- | :---: | :--- |
| `?action=status` | `GET` | Fetches live session state, phone number, push name, QR state, and error logs. |
| `?action=start` | `POST` | Initiates Baileys socket handshake and generates a fresh QR code Data URI. |
| `?action=logout` | `POST` | Unlinks device, purges on-disk session credentials, and resets status to `idle`. |
| `?action=sleep` | `POST` | Suspends socket connection to conserve host RAM while preserving auth. |
| `?action=wake` | `POST` | Wakes up a suspended device and re-establishes the WhatsApp WebSocket. |
| `?action=send-test` | `POST` | Enqueues a test message from the dashboard (`{"to": "...", "message": "..."}`). |
| `?action=queue-status` | `GET` | Returns paginated message queue status and delivery metrics for the user. |

---

### 10.4 Web Application Routes & Form Actions (`public/index.php`)

All form POST submissions require `csrf_token` validation:

| Route / Parameter | HTTP Method | Access | Function |
| :--- | :---: | :---: | :--- |
| `/login` | `GET` / `POST` | Public | Authenticates user credentials with brute-force lockout protection. |
| `/register` | `GET` / `POST` | Public | Creates new user account (when `ENABLE_REGISTRATION=true`). |
| `/logout` | `POST` | Auth | Terminates user session and invalidates cookies. |
| `/forgot-password` | `GET` / `POST` | Public | Self-service OTP password recovery (`request_otp` & `verify_otp`). |
| `/google-auth` | `GET` | Public | Google OAuth2 single-sign-on redirect & callback handler. |
| `/google-contacts` | `GET` | Auth | Google People API OAuth redirect & bulk contact importer. |
| `/dashboard` | `GET` | Auth | Overview workspace: delivery counts, success rate, readiness score. |
| `/device` | `GET` | Auth | Interactive device pairing console (QR code, sleep, disconnect). |
| `/message` | `GET` | Auth | Dashboard message composer with file attachment support. |
| `/queue` | `GET` | Auth | Real-time queue visualizer with retry and cancellation controls. |
| `/api-keys` | `GET` | Auth | API credentials management, IP allowlists, rate limit editor. |
| `/contacts` | `GET` | Auth | Address book, CSV import, vCard export (`/contacts-export-vcf`). |
| `/campaigns` | `GET` | Auth | Broadcast campaigns targeting opted-in contacts with templating. |
| `/chatbot` | `GET` | Auth | Chatbot automation rule builder (`exact`, `contains`, `catch_all`). |
| `/webhooks` | `GET` | Auth | Webhook target URL configuration and secret rotation. |
| `/audit` | `GET` | Auth | Paginated API request audit logs. |
| `/docs` | `GET` | Public | In-app API documentation and integration snippets. |
| `/privacy`, `/terms` | `GET` | Public | Privacy policy and terms of service pages. |

---

### 10.5 Internal Worker Microservice Endpoints (`worker/server.mjs`)

Listening internally on `http://127.0.0.1:3107`. Requires `X-Worker-Token` header on all non-health routes:

| Route | Method | Purpose |
| :--- | :---: | :--- |
| `/health` | `GET` | Returns `{ "ok": true, "service": "whatsapp-worker" }` (No token required). |
| `/queue/notify` | `POST` | Wakes up worker event loop to immediately process pending queue messages. |
| `/sessions/:userId/status` | `GET` | Returns current Baileys state, QR string, and device details. |
| `/sessions/:userId/start` | `POST` | Launches Baileys socket for specific tenant user. |
| `/sessions/:userId/logout` | `POST` | Unlinks session and deletes auth files in `sessions/user_<userId>`. |
| `/sessions/:userId/sleep` | `POST` | Closes active socket and marks session sleeping in database. |
| `/sessions/:userId/wake` | `POST` | Resumes connection and re-opens WhatsApp WebSocket. |
| `/sessions/:userId/send` | `POST` | Direct synchronous message dispatch. |

---

## 11. Request & Response Examples

### 11.1 Format A: Standard Flat JSON (Recommended)

#### Request:
```bash
curl -X POST "http://localhost/wa/public/api/send.php" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: wa_live_YOUR_API_KEY_HERE" \
  -d '{
    "to": "919876543210",
    "message": "Hello from WhatsApp API Hub! 🚀\nYour order *#84920* has been shipped.",
    "humanize": true
  }'
```

#### Success Response (`200 OK`):
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

### 11.2 Format B: Meta WhatsApp Cloud API Format (Drop-In)

Works transparently with existing codebases targeting Meta Graph API without altering payloads:

#### Request:
```bash
curl -X POST "http://localhost/wa/public/v20.0/100012345/messages" \
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

---

### 11.3 Media Transmission: Document / PDF via Direct URL

#### Request:
```bash
curl -X POST "http://localhost/wa/public/api/send.php" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: wa_live_YOUR_API_KEY_HERE" \
  -d '{
    "to": "919876543210",
    "type": "document",
    "media_url": "https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf",
    "caption": "Please find attached your monthly account statement.",
    "filename": "Account_Statement.pdf"
  }'
```

---

### 11.4 Media Transmission: Image via Multipart Form-Data Upload

#### Request:
```bash
curl -X POST "http://localhost/wa/public/api/send.php" \
  -H "X-API-Key: wa_live_YOUR_API_KEY_HERE" \
  -F "to=919876543210" \
  -F "caption=Product Catalogue 📸" \
  -F "file=@/path/to/product_flyer.jpg"
```

---

### 11.5 Error Responses

#### Missing Required Parameters (`422 Unprocessable Entity`):
```json
{
  "ok": false,
  "error": "Field \"to\" and either a \"message\" or media attachment are required.",
  "request_id": "a1b2c3d4e5f6789012345678",
  "meta_error": {
    "message": "Invalid parameter: \"to\" and either \"text.body\" or media object are required.",
    "type": "OAuthException",
    "code": 100
  }
}
```

#### Rate Limit Exceeded (`429 Too Many Requests`):
```json
{
  "ok": false,
  "error": "Rate limit exceeded.",
  "request_id": "f1e2d3c4b5a6789012345678",
  "rate": {
    "allowed": false,
    "counts": { "per_minute": 20, "per_hour": 140, "per_day": 850 },
    "limits": { "rate_per_minute": 20, "rate_per_hour": 300, "rate_per_day": 1000 }
  },
  "meta_error": {
    "message": "Rate limit hit.",
    "type": "OAuthException",
    "code": 80007
  }
}
```

#### Invalid or Missing API Key (`401 Unauthorized`):
```json
{
  "ok": false,
  "error": "Invalid API key or access token.",
  "meta_error": {
    "message": "Invalid OAuth access token or API key.",
    "type": "OAuthException",
    "code": 190
  }
}
```

---

## 12. Authentication, Authorization & Security

### Cryptographic API Key Management
- When an API key is generated via `/credentials`, the user is shown the plain key (`wa_live_...`) and secret (`was_...`) once.
- The database **never stores plaintext keys**. Only the SHA-256 hash (`hash('sha256', $apiKey)`) and a public key prefix (`substr($apiKey, 0, 22)`) are persisted.
- Secrets are hashed using PHP `password_hash()` with `PASSWORD_DEFAULT` (Bcrypt/Argon2id).

### SSRF Protection (Server-Side Request Forgery)
All remote media URLs and webhook target URLs undergo DNS inspection via `assert_safe_media_url()` and `assertSafeWebhookUrl()`:
- Rejects non-HTTP/HTTPS protocols.
- Resolves DNS A/AAAA records and inspects all destination IP addresses.
- Strictly blocks private ranges (`10.0.0.0/8`, `172.16.0.0/12`, `192.168.0.0/16`, `127.0.0.0/8`, `169.254.0.0/16`, `::1`, and IPv6 unique local/site-local).

### Upload Directory Hardening
The `public/uploads/` directory contains an automated [`.htaccess`](public/uploads/.htaccess) policy that:
- Forbids execution of all server-side script extensions (`.php`, `.phtml`, `.cgi`, `.pl`, `.exe`, `.sh`, `.bat`, `.cmd`, `.py`, etc.).
- Turns off `mod_php` engine (`php_flag engine off`).
- Strips script handlers (`RemoveHandler`).

### Webhook Signature Verification
Every outbound webhook includes an HMAC-SHA256 signature in the `X-WA-Hub-Signature` HTTP header. Verify incoming webhooks in your receiving service:

```php
// PHP verification example
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_WA_HUB_SIGNATURE'] ?? '';
$expected = hash_hmac('sha256', $payload, $yourWebhookSecret);

if (!hash_equals($expected, $signature)) {
    http_response_code(403);
    exit('Invalid signature');
}
```

```javascript
// Node.js verification example
import crypto from 'crypto';

function verifyWebhook(rawBody, signature, secret) {
  const expected = crypto.createHmac('sha256', secret).update(rawBody).digest('hex');
  return crypto.timingSafeEqual(Buffer.from(signature), Buffer.from(expected));
}
```

---

## 13. Error Handling & Status Codes

| HTTP Status | Meaning | Typical Cause & Resolution |
| :---: | :--- | :--- |
| `200 OK` | Message Enqueued | Request validated and placed into queue for worker dispatch. |
| `202 Accepted` | Test Message Enqueued | Dashboard test message accepted for immediate queueing. |
| `400 Bad Request` | Malformed Request | Invalid JSON structure or missing parameters. |
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

## 14. Testing & Quality Verification

### Syntax & Quality Verification Commands

Verify the entire codebase locally using the same automated suite run by GitHub Actions (`.github/workflows/ci.yml`):

```bash
# 1. Lint all PHP files in the project
php -l app.php
php -l index.php
php -l public/index.php
php -l test/index.php
php -l public/api/send.php
php -l public/api/health.php

# 2. Check Node.js worker syntax
node --check worker/server.mjs
```

### Interactive Developer Testing Tools

1. **Web Test Console:** Open `http://localhost/wa/test/index.php` (or `/test-page.php`) in your browser to test endpoints, headers, and media URLs interactively.
2. **Interactive Terminal Sender (`send_whatsapp.py`):**
   ```bash
   python send_whatsapp.py
   ```
   Prompts for phone number and message, caches your credentials locally, and delivers via the API.
3. **Multi-Language Quickstart Suite:**
   Run quickstart examples across different language runtimes:
   ```bash
   python quickstart/python/send.py
   node quickstart/nodejs/send.js
   php quickstart/php/send.php
   go run quickstart/go/main.go
   java quickstart/java/SendWhatsApp.java
   ```

---

## 15. Deployment Guide (Docker, Cloud, VPS)

### Production Checklist
1. Set `APP_ENV=production` in `.env`.
2. Generate a strong, random `WORKER_TOKEN` (e.g. `openssl rand -hex 24`).
3. Set `ENABLE_REGISTRATION=false` to prevent public sign-ups on your private instance.
4. Set `ENABLE_TEST_PAGE=false` to restrict internal test utilities.
5. Set `APP_SECURE_COOKIES=true` and ensure SSL/TLS is active.
6. Configure `APP_PUBLIC_URL=https://your-domain.com` (leave `APP_BASE_PATH=` empty).

### Deploying to Render / Railway / PaaS with TiDB Cloud

The repository includes a production [`Dockerfile`](Dockerfile) configured with Supervisor. Its entrypoint applies `php scripts/migrate.php` before any service starts:
1. Connect your GitHub repository to Render / Railway.
2. Set Environment Variables:
   - `DB_HOST`, `DB_PORT=4000`, `DB_NAME=whatsapp_api`, `DB_USER`, `DB_PASSWORD`, `DB_SSL=true` (TLS certificate verification stays enabled; provide `DB_SSL_CA` only if a trusted CA file is required)
   - `APP_ENV=production`, `APP_PUBLIC_URL=https://your-service.onrender.com`, `APP_BASE_PATH=`
   - `WORKER_TOKEN` generated with `node -e "console.log(require('crypto').randomBytes(32).toString('hex'))"`, `ENABLE_REGISTRATION=false`. Paste the generated value directly into Render Environment Variables; do not commit it or send it in chat.
3. Deploy. Startup applies the migration before the queue worker. For a manual run on a compatible Render service shell, execute `php /var/www/html/scripts/migrate.php`; Render also documents one-off jobs for tasks such as database migrations. Do not use `database.sql`, `clean_tidb_import.sql`, or `schema_export.sql` against the existing database because they contain destructive dump statements.
4. The container forces `WORKER_URL` to `http://127.0.0.1:${WORKER_PORT}` because Apache and the Node worker run in the same container; `WORKER_PORT` defaults to `3107`. Loopback cURL requests explicitly bypass inherited HTTP proxy settings. The worker's `GET /health` route is unauthenticated but reports ready only after it connects to the `whatsapp_api` database and confirms all 13 required tables; a not-ready result is HTTP 503 JSON with `error.code=database_not_ready`. Other worker routes require `X-Worker-Token`; PHP includes it without logging or returning it. Verify `https://<service-host>/api/health.php` returns HTTP 200 with `database.ok`, `database.schema_ready`, and `worker.ok` all `true`. On a failed worker probe, the PHP error log records only the sanitized target, peer IP, HTTP status, content type, a redacted response excerpt, and a short failure category. Then check Render logs for `Database schema is ready: 13 application tables in whatsapp_api.` and confirm queue processing no longer logs `ER_NO_SUCH_TABLE`.

### First private dashboard account

Public registration should remain disabled with `ENABLE_REGISTRATION=false`. To provision the first account without a public administrator endpoint, set `INITIAL_ADMIN_EMAIL` and `INITIAL_ADMIN_PASSWORD` as temporary Render environment variables, then deploy. Use a unique valid email and a password of at least 12 characters; optionally set `INITIAL_ADMIN_NAME` (defaults to `Administrator`). The Docker entrypoint runs `scripts/create-initial-admin.php` after the existing non-destructive schema initializer and before Apache or the worker starts. The password is read from the process environment, hashed with PHP `password_hash`, and never included in logs or command arguments. After logs report that the account was created, remove all `INITIAL_ADMIN_*` variables and deploy again. The command is safe to rerun for the same sole account: it reports that the account already exists without changing its password. It refuses if other user accounts exist or if it cannot acquire TiDB's advisory setup lock; it never upgrades, downgrades, or replaces an existing account.

The current schema has no `is_admin` column or separate administrator role: the first account is the initial dashboard owner, and authenticated accounts use the same user-scoped dashboard permissions. This setup creates only that first login account and its normal WhatsApp session and webhook records; it makes no schema changes. Sign in at `/login`; the authenticated root route (`/`) opens the dashboard. Open `/device`, select **Connect**, and scan the QR displayed by the existing session UI. The connect action is a CSRF-protected request to `public/session.php?action=start`; this source confirms the QR pairing flow, but a successful WhatsApp connection still depends on the deployed worker and WhatsApp session.

For local test runs, include `php tests/initial-admin.test.php`.

---

## 16. External Integrations & Background Workers

### Baileys WebSocket Lifecycle
- The Node.js worker initializes a WebSocket connection to WhatsApp servers using the Noise protocol.
- Cryptographic session state is stored in `whatsapp-api-data/sessions/user_<userId>`.
- Supports automatic reconnects on socket dropouts (`restartRequired`, stream errors).
- Ephemeral downloaded media files (`public/uploads/media_*`) are automatically deleted from server disk after successful delivery to prevent storage exhaustion.

### Chatbot Trigger Engine
Incoming messages trigger Baileys `messages.upsert` events. If active rules exist in `chatbot_rules`:
- Evaluates keywords with `exact`, `starts_with`, or `contains` matching.
- Supports fallback `catch_all` rules for unhandled inquiries.
- Enqueues the automated reply with `source: 'chatbot'`.

### SMTP & Email Integration
- Configured via `SMTP_HOST`, `SMTP_PORT`, `SMTP_USER`, and `SMTP_PASS`.
- Used for self-service password reset OTP dispatch.

---

## 17. Logging, Monitoring & Debugging

- **API Request Audit Logs:** Viewable in the dashboard under **Audit Logs** (`api_request_logs`). Records timestamp, IP address, HTTP status code, request ID, and error messages.
- **Worker Daemon Logs:**
  - Standard output: `worker/worker.out.log`
  - Error output: `worker/worker.err.log`
  - Control logs via `start-worker.bat logs` on Windows.
- **Database Delivery Logs:** Recorded in `message_logs` with provider responses and message IDs.
- **Worker Log Verbosity:** Change `LOG_LEVEL=debug` in `.env` to view full Baileys frame logs.

---

## 18. Anti-Ban Practices, Known Limitations & Notes

### WhatsApp Anti-Ban Best Practices
Because this gateway operates via WhatsApp Web multi-device emulation:
1. **Always Enable `"humanize": true`:** Adds typing presence updates (`composing`/`recording`) and realistic 2–5 second delays before dispatching.
2. **Respect Opt-In Consent:** Only message users who have explicitly agreed to receive communications.
3. **Avoid Sudden High-Volume Blasts:** Warm up new numbers gradually over 7–14 days. Do not send thousands of messages instantly.
4. **Configure Rate Limits:** Set conservative limits in the dashboard under **API Keys** (e.g. 20 msgs/min).

### Known Implementation Notes
- **Multi-Device Dependency:** Your WhatsApp mobile account must remain active. If WhatsApp logs out all linked devices from the mobile app, you must re-scan the QR code in the dashboard.
- **Drop-In Compatibility:** Compatible with Meta Cloud API schemas for text, documents, images, audio, video, and simple templates. Advanced interactive Meta catalog flows or Meta business payment flows are not supported by the WhatsApp Web protocol.

---

## ⚖️ License & Trademark Notice

### License
This project is licensed under the [MIT License](LICENSE).

### Trademark Disclaimer
This project is an independent open-source library and is **not affiliated with, associated with, authorized by, endorsed by, or in any way officially connected with Meta Platforms, Inc. or WhatsApp LLC.** WhatsApp is a registered trademark of WhatsApp LLC.

**Inquiries & Support:** `info.assistgroupofficial@gmail.com`  
**Maintainer:** [Navneet Kumar Yadav](https://github.com/navneetyadav-code)
