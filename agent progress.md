# Native Live Chat Implementation Progress (`agent progress.md`)

## Project Information
- **Project:** Native WhatsApp Live Chat for WhatsGateway
- **Status:** Complete, Polished & Verified ✅
- **Started:** 2026-10-27
- **Completed:** 2026-10-27 (Latest Patch: Two-Way & UI Polish)

---

## Current Status & Summary
- **Current Milestone:** All 4 Phases Complete + Bugfix / UI Polish Patch Applied
- **Final Status:** 
  1. **WhatsApp LID Resolution Fixed:** WhatsApp Linked Device Identifiers (e.g. `28343109968029@lid`) are now resolved to real international phone numbers (e.g. `919507286092`) via Baileys reverse LID mapping (`lid-mapping-${lidUser}_reverse.json` / `sock.signalRepository.lidMapping.getPNForLID`).
  2. **Two-Way Threading Fixed:** Incoming messages and outgoing replies are now bound to the exact same canonical conversation thread (`91...`), eliminating split threads.
  3. **UI Cleaned & Fixed Height:** The clunky outer header was completely removed. The live chat studio now fills 100% of the viewport height with zero outer page scrollbars. Connection status and "New Chat" buttons are cleanly placed in the sidebar header.
  4. **WhatsApp Web-Style Chat Bubbles & Real-Time Receipts:** Outbound messages render in classic WhatsApp light-green (`#D9FDD3`), inbound in crisp white. Delivery checkmarks (single grey tick, double grey tick, double sky-blue tick for read/seen) are polled and updated live.
  5. **Default Country Code `91`:** Enforced across the New Chat modal, frontend normalizer, PHP REST API endpoints, and Node.js worker.

---

## Detailed Task Progress

### Phase 1: Database Architecture & Worker Message Ingestion
| Task | Status | Notes |
| :--- | :---: | :--- |
| `migrations/005_native_live_chat.sql` | 🟢 Completed | Created `chat_conversations` and `chat_messages` tables |
| Fix `scripts/migrate.php` idempotency | 🟢 Completed | Handles duplicate column and table definition re-runs cleanly |
| Run migration on database `whatsapp_api` | 🟢 Completed | Verified tables in MySQL via test script |
| Update `schema.sql` | 🟢 Completed | Kept schema file canonically in sync |
| Update `worker/server.mjs` for inbound message ingestion | 🟢 Completed | Ingests into `chat_conversations` & `chat_messages` |
| LID Resolution Helper in `worker/server.mjs` | 🟢 Completed | `resolveJidToPhone` resolves `@lid` to real phone number |
| Update `worker/server.mjs` for bot-pause check | 🟢 Completed | Skips chatbot auto-replies when `bot_paused = 1` |
| Update `worker/server.mjs` for mobile phone sync | 🟢 Completed | Captures `msg.key.fromMe = true` messages from phone |
| Update `worker/server.mjs` for status sync | 🟢 Completed | Syncs `delivered` and `read` statuses to `chat_messages` |

### Phase 2: REST APIs & Real-Time Sync Engine
| Task | Status | Notes |
| :--- | :---: | :--- |
| `public/api/chat/_auth.php` | 🟢 Completed | Added `normalize_chat_phone` and `format_chat_phone` |
| `public/api/chat/conversations.php` | 🟢 Completed | Returns formatted phone display and active conversations |
| `public/api/chat/messages.php` | 🟢 Completed | Returns delta messages and recent delivery/read receipt statuses |
| `public/api/chat/send.php` | 🟢 Completed | Normalizes to `91` prefix, enqueues priority 10 |
| `public/api/chat/toggle-bot.php` | 🟢 Completed | Toggles `bot_paused` (0 ⟷ 1) for the conversation |

### Phase 3: Frontend Dashboard UI (`live-chat.php`)
| Task | Status | Notes |
| :--- | :---: | :--- |
| Fix Outer Page Scroll in `layout.php` & `dashboard.php` | 🟢 Completed | `#workspaceContent` set to `overflow-hidden flex flex-col` |
| Remove Bulky Header in `live-chat.php` | 🟢 Completed | Removed outer title/subtitle and merged controls to sidebar |
| Sidebar Header with Connection Pill & New Chat | 🟢 Completed | Sleek WhatsApp Web style top bar |
| Message Feed Redesign in `live-chat.js` | 🟢 Completed | Light green outbound bubbles, white inbound bubbles |
| Live Delivery & Seen Receipts | 🟢 Completed | Single tick, double grey tick, double blue tick (`#53bdeb`) |
| Pre-fill Default `91` in New Chat Modal | 🟢 Completed | Input pre-filled with `91` and auto-prefixes 10-digit input |

### Phase 4: Chat Lifecycle, Close Action & 5-Star Feedback Collection
| Task | Status | Notes |
| :--- | :---: | :--- |
| `migrations/006_chat_feedback.sql` | 🟢 Completed | Added `status`, `closed_at`, `awaiting_feedback` to `chat_conversations` and created `chat_feedback` table |
| `schema.sql` Synchronization | 🟢 Completed | Updated canonical database schema with feedback table |
| `public/api/chat/close.php` | 🟢 Completed | Closes chat, sets `awaiting_feedback = 1`, and enqueues automated WhatsApp feedback rating prompt |
| Baileys Inbound Rating Ingestion (`worker/server.mjs`) | 🟢 Completed | Parses ratings 1-5 & star emojis, records feedback in `chat_feedback`, sends automated thank-you note |
| Live Chat Close / Reopen UI (`live-chat.php` & `live-chat.js`) | 🟢 Completed | Added "Close Chat" & "Reopen Chat" button in header, closed status banner, and 'Closed' filter tab |
| Sidebar Navigation & Routing | 🟢 Completed | Added "Feedback" item with star icon to sidebar; registered routes in `index.php`, `dashboard.php`, `navbar.php` |
| Feedback Data & View Partial | 🟢 Completed | Built `src/data/dashboard/feedback.data.php` & `src/pages/dashboard/feedback.php` with KPI cards, star filters, and feedback records table |

---

## Technical Decisions
- **Canonical Phone Format:** Standard international digits without leading `+` or zeros (e.g. `919507286092`).
- **LID Resolution:** Three-tier fallback: (1) In-memory Baileys signal store, (2) Disk reverse mapping file `lid-mapping-${lid}_reverse.json`, (3) Forward mapping scans.
- **Receipt Polling:** `messages.php` returns recent status transitions for the last 25 outbound messages, ensuring fast update of blue read ticks without re-rendering the whole stream.
- **Feedback Collection Loop:** When agent marks conversation as closed, customer receives a WhatsApp message requesting a 1-5 star rating. When customer responds with a number or star emoji, rating and feedback message are automatically parsed and saved to `chat_feedback`, followed by an automated thank-you note. Customers sending a message when a chat is closed automatically reopens the conversation for agent attention.
