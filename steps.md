# Native Live Chat Implementation Plan (`steps.md`)

This roadmap breaks down the complete development of the **Native WhatsApp Live Chat** dashboard into actionable, verifiable phases.

---

## 📋 Phase 1: Database Architecture & Worker Message Ingestion (Completed ✅)
- [x] **Task 1.1: Migration File & Execution**
  - [x] Create `migrations/005_native_live_chat.sql` with `chat_conversations` and `chat_messages` tables.
  - [x] Fix `scripts/migrate.php` to handle duplicate column errors idempotently.
  - [x] Execute `php scripts/migrate.php` and verify tables exist in database `whatsapp_api`.
  - [x] Synchronize canonical `schema.sql` for clean deployments.
- [x] **Task 1.2: Baileys Worker Inbound Hook & LID Resolution (`worker/server.mjs`)**
  - [x] Implement `resolveJidToPhone(userId, jid, session)` to translate WhatsApp multi-device `@lid` identifiers into canonical phone numbers.
  - [x] In `messages.upsert`, extract incoming messages from contacts.
  - [x] Upsert conversation record in `chat_conversations` (update `last_message`, `last_message_at`, increment `unread_count`).
  - [x] Insert message row into `chat_messages` (`sender_type = 'contact'`).
  - [x] Add `bot_paused` check before evaluating `chatbot_rules`.
- [x] **Task 1.3: Baileys Worker Outbound & Mobile App Sync Hook (`worker/server.mjs`)**
  - [x] Capture messages sent from physical phone (`msg.key.fromMe = true`) and record in `chat_messages` (`sender_type = 'agent'`).
  - [x] Link WhatsApp provider message ID to `chat_messages` in `markMessageSent`.
  - [x] Sync message delivery/read status updates in `handleMessageStatusUpdate`.

---

## 📋 Phase 2: REST APIs & Real-Time Sync Engine (Completed ✅)
- [x] **Task 2.0: Shared Authentication & Phone Normalizer (`public/api/chat/_auth.php`)**
  - [x] Multi-mode authentication supporting session cookies and API keys.
  - [x] `normalize_chat_phone` helper automatically prepending `91` for 10-digit Indian numbers.
  - [x] `format_chat_phone` helper for clean international display (`+91 95072 86092`).
  - [x] Relative time formatter (`chat_time_ago`).
- [x] **Task 2.1: Conversation List Endpoint (`public/api/chat/conversations.php`)**
  - [x] Return JSON array of active conversations with formatted phone numbers.
  - [x] Search query parameter (`?q=...`) to filter by name/phone/content.
- [x] **Task 2.2: Message History & Status Endpoint (`public/api/chat/messages.php`)**
  - [x] `GET /api/chat/messages.php?phone=...&after_id=...`
  - [x] Incremental delta updates (`after_id`) for lightweight real-time polling.
  - [x] Returns recent status transitions for outbound messages to update delivery and blue seen checkmarks.
  - [x] Mark conversation as read (`unread_count = 0`) on initial view.
- [x] **Task 2.3: Agent Send Message Endpoint (`public/api/chat/send.php`)**
  - [x] `POST /api/chat/send.php` (accepts JSON or FormData with `phone`, `message`, optional `file` upload).
  - [x] Enqueue into `message_queue` with priority 10 (urgent) for instant delivery.
  - [x] Auto-pause bot on human agent response for smooth takeover.
- [x] **Task 2.4: Bot Status Toggle Endpoint (`public/api/chat/toggle-bot.php`)**
  - [x] `POST /api/chat/toggle-bot.php`
  - [x] Toggles `bot_paused` (0 ⟷ 1) for the conversation.

---

## 📋 Phase 3: Frontend Dashboard UI (`live-chat.php` & `live-chat.js`) (Completed ✅)
- [x] **Task 3.1: Viewport & Layout Scrolling Fix**
  - [x] Fix `src/pages/layout.php` and `src/pages/dashboard.php` to set `overflow-hidden` for live chat.
  - [x] Remove outer page scrollbars; container fills 100% of the viewport height.
- [x] **Task 3.2: Studio Header Cleanup**
  - [x] Remove bulky header banner.
  - [x] Relocate connection status and "+ New Chat" button into the left sidebar header.
- [x] **Task 3.3: Two-Way Message Feed & WhatsApp Aesthetics**
  - [x] Outbound agent bubbles: WhatsApp light-green (`#D9FDD3`), right-aligned with status checkmarks.
  - [x] Inbound contact bubbles: White, left-aligned with sender name.
  - [x] Status ticks: single check (sent), double grey check (delivered), double blue check (read/seen `#53bdeb`).
- [x] **Task 3.4: Default `91` Country Code**
  - [x] "New Chat" modal input pre-filled with `91`.
  - [x] Auto-prefixing 10-digit Indian mobile numbers.

---

## 📋 Phase 4: Chat Lifecycle & 5-Star Feedback Automation (Completed ✅)
- [x] **Task 4.1: Database Schema & Migration**
  - [x] Create `migrations/006_chat_feedback.sql` with `status`, `closed_at`, `awaiting_feedback` in `chat_conversations` and `chat_feedback` table.
  - [x] Run `php scripts/migrate.php` to apply migration (22 application tables).
  - [x] Update canonical `schema.sql`.
- [x] **Task 4.2: Close Chat API & Automated Prompt (`public/api/chat/close.php`)**
  - [x] `POST /api/chat/close.php` with `phone` and `action` ('close' or 'reopen').
  - [x] Marks conversation as `status = 'closed'` and `awaiting_feedback = 1`.
  - [x] Records feedback prompt in `chat_messages` and enqueues to WhatsApp with priority 10.
- [x] **Task 4.3: Inbound Rating Parser & Thank-You Loop (`worker/server.mjs`)**
  - [x] Detects `awaiting_feedback = 1` on incoming WhatsApp messages.
  - [x] Parses 1-5 numeric ratings, star emojis (`⭐`), and number words.
  - [x] Saves customer name, phone, rating (1-5), and feedback comments into `chat_feedback`.
  - [x] Dispatches automated WhatsApp thank-you response.
  - [x] Reopens closed conversation automatically if a customer messages later.
- [x] **Task 4.4: Live Chat Studio UI Enhancements (`live-chat.php` & `live-chat.js`)**
  - [x] Add "Close Chat" and "Reopen Chat" button in header.
  - [x] Closed chat notice banner with quick reopen button.
  - [x] Added "Closed" filter tab and closed badges in conversation list.
- [x] **Task 4.5: Feedback Dashboard Section & Sidebar Menu**
  - [x] Added "Feedback" item with star icon in `src/pages/components/sidebar.php`.
  - [x] Registered `'feedback'` in `public/index.php`, `dashboard.php`, and `navbar.php`.
  - [x] Built data provider `src/data/dashboard/feedback.data.php` with aggregate KPI calculations.
  - [x] Built view partial `src/pages/dashboard/feedback.php` with telemetry scorecards, star ratings filter, search, and responsive table.
