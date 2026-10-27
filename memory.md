# Native Live Chat Implementation Memory (`memory.md`)

## 1. Project Context
- **Application:** WhatsGateway (`whatsapp-api`)
- **Core Architecture:**
  - **Worker Service:** Node.js (ESM), Express 5 (`worker/server.mjs`), `@whiskeysockets/baileys` v7.
  - **Web Dashboard & API:** PHP 8.2+, Tailwind CSS (`src/pages/dashboard/`, `public/api/`).
  - **Database:** MySQL / MariaDB (`whatsapp_api`).
- **Feature Scope:** Native WhatsApp Live Chat inbox built directly inside WhatsGateway with zero third-party dependencies.

---

## 2. Key Architectural Decisions & Solutions

### A. Two-Column WhatsApp Web UI
- **Left Panel:** Unified list of active contacts/conversations (`chat_conversations`) ordered by `last_message_at DESC`.
  - Top header: WhatsApp connection badge with device phone number + "+ New Chat" button + Refresh button.
  - Shows contact avatar initials, formatted phone (`+91 95072 86092`) / name, snippet of last message, relative timestamp (`2m ago`), and unread message counter badge.
  - Live client-side search/filter by phone number or contact name.
  - Filter chips for "All" and "Unread".
- **Right Panel:** Active conversation message thread (`chat_messages`).
  - Fixed full-height card filling 100% of viewport without outer page scrollbars.
  - Classic WhatsApp chat bubbles:
    - Inbound customer messages: Left-aligned white bubbles with contact name and timestamp.
    - Outbound agent / bot messages: Right-aligned light-green (`#D9FDD3`) bubbles with timestamp and status ticks.
  - Real-time delivery & seen receipts:
    - Single grey check: Sent
    - Double grey check: Delivered
    - Double sky-blue check (`#53bdeb`): Read / Seen
  - Media support: Inline display for images, interactive player for audio/voice notes, download links for documents (PDF/DOCX).
  - Fixed-bottom chat input box with `Enter` to send, `Shift+Enter` for newline, attachment button (up to 20MB).

### B. Resolution of WhatsApp Multi-Device LID Identifiers
- **The Problem:** In WhatsApp multi-device, messages frequently arrive with an internal Linked Device Identifier JID (`28343109968029@lid`) instead of the phone number JID (`919507286092@s.whatsapp.net`). If recorded raw, the number displays as `+28343109968029` and breaks two-way conversation threading (outgoing replies go to the real phone number while inbound messages are filed under the LID).
- **The Solution:** Added `resolveJidToPhone(userId, jid, session)` in `worker/server.mjs`:
  1. Checks Baileys in-memory signal store `sock.signalRepository.lidMapping.getPNForLID(jid)`.
  2. Reads disk reverse mapping: `C:\xampp\whatsapp-api-data\sessions\user_${userId}\lid-mapping-${lidUser}_reverse.json`.
  3. Scans forward mapping files in the session directory if reverse file is missing.
  4. Automatically canonicalizes Indian numbers to `91...` format.
  5. Both inbound messages and outbound messages are mapped to the exact same canonical conversation thread!

### C. Default Country Code `91` (India)
- **Modal Input:** Default value is pre-populated as `91`.
- **Frontend Normalizer:** Any 10-digit number entered is automatically converted to `91` + digits.
- **PHP REST APIs:** `normalize_chat_phone` helper in `_auth.php` normalizes all incoming and outgoing numbers to 91-prefixed digits.
- **Node.js Worker:** Normalizes all recipients with `DEFAULT_COUNTRY_CODE = '91'`.

### D. Message Status Receipt Tracking (Delivered & Seen)
- When a message is dispatched via `message_queue`, `markMessageSent` extracts `payload.chat_message_id` and records the WhatsApp provider message ID (`result.key.id`) into `chat_messages.wa_message_id`.
- When WhatsApp sends `messages.update` or `message-receipt.update` events:
  - `handleMessageStatusUpdate` matches `chat_messages.wa_message_id` and promotes status from `sent` $\to$ `delivered` $\to$ `read`.
  - Frontend smart delta polling queries `statuses` dictionary and smoothly switches single/double grey ticks to double sky-blue ticks without re-rendering or jumping scroll.

### E. Chat Lifecycle & Automated 5-Star Feedback System
- **Close Chat Action:**
  - Agent clicks "Close Chat" in the Live Chat studio header.
  - Endpoint `POST /api/chat/close.php` updates conversation status to `closed`, sets `awaiting_feedback = 1`, and automatically queues a friendly 5-star feedback request prompt to the customer on WhatsApp.
  - The conversation displays a closed banner and a "Closed" badge in the list. Agents can click "Reopen Chat" at any time.
- **Automated Feedback Ingestion (`worker/server.mjs`):**
  - When customer replies with a rating (e.g., `5`, `5/5`, `⭐⭐⭐⭐⭐`, `4 - good service`), Baileys worker parses the 1-5 rating score and optional comments.
  - Stores the record into `chat_feedback` table (`user_id`, `conversation_id`, `phone`, `contact_name`, `rating`, `message`, `created_at`).
  - Sets `awaiting_feedback = 0` and dispatches an automated WhatsApp thank-you message (`Thank you for your rating of 5/5 ⭐! 🙏`).
  - Skips chatbot auto-replies so feedback numbers don't trigger menu commands.
  - If a customer messages again later, the conversation automatically reopens to `status = 'open'`.
- **Sidebar & Feedback Dashboard Section:**
  - Added "Feedback" item with a gold star icon in `src/pages/components/sidebar.php`.
  - Registered route `page=feedback` in `public/index.php`, `dashboard.php`, and `navbar.php`.
  - Telemetry scorecards: Overall Score (e.g. 4.8 / 5.0 with 5 stars), Total Feedbacks, 5-Star Ratio %, and Positive Sentiment %.
  - Complete SaaS data table with Customer avatar, Name, Mobile Number (with WhatsApp link), 5-Star Visual representation & badges, Feedback comments quote bubble, Date & Time, and Direct Chat jump link.
  - `handleMessageStatusUpdate` updates `chat_messages.status` (`delivered` or `read`).
- In `public/api/chat/messages.php`:
  - When delta polling with `after_id`, it queries the latest status changes for the last 25 outbound messages and returns `'statuses' => [id => status, ...]`.
  - The frontend client updates checkmarks live without re-rendering the message DOM.

### E. Intelligent Bot Pause / Human Takeover Mode
- A `bot_paused` boolean column on `chat_conversations`.
- In the chat window header, an interactive toggle button: `🤖 Bot Active` ⟷ `⏸️ Bot Paused (Takeover Mode)`.
- In `worker/server.mjs`: When evaluating incoming messages, check `chat_conversations.bot_paused`. If `1`, skip keyword matching.
- Auto-pause feature: When an agent sends a message from the Live Chat UI, `bot_paused` is automatically set to `1`.
- If chatbot auto-replies when active, it inserts into `chat_messages` with `sender_type = 'bot'`, ensuring the agent sees the bot's responses in real-time.

---

## 3. Database Schema

### `chat_conversations`
- `id` (BIGINT UNSIGNED PK AUTO_INCREMENT)
- `user_id` (BIGINT UNSIGNED FK users.id)
- `phone` (VARCHAR(80)) - Canonical international digits (e.g. `919507286092`)
- `contact_name` (VARCHAR(120) NULL)
- `last_message` (TEXT NULL)
- `last_message_at` (TIMESTAMP)
- `unread_count` (INT UNSIGNED DEFAULT 0)
- `bot_paused` (TINYINT(1) DEFAULT 0)
- `is_pinned` (TINYINT(1) DEFAULT 0)
- `created_at` (TIMESTAMP), `updated_at` (TIMESTAMP)

### `chat_messages`
- `id` (BIGINT UNSIGNED PK AUTO_INCREMENT)
- `user_id` (BIGINT UNSIGNED FK users.id)
- `conversation_id` (BIGINT UNSIGNED FK chat_conversations.id)
- `sender_type` (ENUM('contact', 'agent', 'bot'))
- `sender_name` (VARCHAR(120) NULL)
- `message_type` (ENUM('text', 'image', 'document', 'audio', 'video'))
- `body` (TEXT NULL)
- `media_url` (VARCHAR(500) NULL)
- `wa_message_id` (VARCHAR(120) NULL)
- `status` (ENUM('sent', 'delivered', 'read', 'failed'))
- `created_at` (TIMESTAMP)
