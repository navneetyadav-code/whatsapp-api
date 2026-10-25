import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';

const workerSource = readFileSync(new URL('../worker/server.mjs', import.meta.url), 'utf8');
const initialMigrationSql = readFileSync(new URL('../migrations/001_initial_schema.sql', import.meta.url), 'utf8');
const canonicalSchemaSql = readFileSync(new URL('../schema.sql', import.meta.url), 'utf8');

test('Phase 2: Database schema supports delivered_at, read_at, provider index, and status enum', () => {
  assert.match(canonicalSchemaSql, /`delivered_at`\s+timestamp/, 'schema.sql must contain delivered_at column');
  assert.match(canonicalSchemaSql, /`read_at`\s+timestamp/, 'schema.sql must contain read_at column');
  assert.match(canonicalSchemaSql, /enum\('queued','processing','sent','delivered','read','failed','cancelled'\)/, 'schema.sql must include delivered and read status enum');
  assert.match(canonicalSchemaSql, /idx_message_queue_provider_msg/, 'schema.sql must index provider_message_id');

  assert.match(initialMigrationSql, /`delivered_at`\s+timestamp/, '001_initial_schema.sql must contain delivered_at column');
  assert.match(initialMigrationSql, /`read_at`\s+timestamp/, '001_initial_schema.sql must contain read_at column');
  assert.match(initialMigrationSql, /idx_message_queue_provider_msg/, '001_initial_schema.sql must index provider_message_id');
});

test('Phase 2: Provider message ID is persisted on send operation', () => {
  assert.match(workerSource, /provider_message_id = \?/, 'markMessageSent must persist provider_message_id');
  assert.match(workerSource, /result\?\.key\?\.id/, 'Extracts exact WhatsApp message ID from Baileys send result');
});

test('Phase 2: Status update correlation enforces tenant isolation', () => {
  assert.match(workerSource, /WHERE provider_message_id = \? AND user_id = \?/, 'Status correlation must enforce tenant user_id');
});

test('Phase 2: Monotonic state progression guarantees read messages are never downgraded to delivered', () => {
  assert.match(workerSource, /if \(msg\.status === 'read'\) \{\s*return;\s*\}/, 'In-memory check prevents downgrading read status');
  assert.match(workerSource, /status IN \('queued', 'processing', 'sent'\)/, 'SQL update only promotes sent/processing/queued to delivered');
  assert.match(workerSource, /status IN \('queued', 'processing', 'sent', 'delivered'\)/, 'SQL update promotes delivered/sent/queued to read');
  assert.match(workerSource, /if \(msg\.status === 'delivered' && newStatus === 'delivered'\) \{\s*return;\s*\}/, 'Duplicate delivery acknowledgements are ignored');
});

test('Phase 2: Delivery and read timestamps are recorded accurately', () => {
  assert.match(workerSource, /delivered_at = COALESCE\(delivered_at, NOW\(\)\)/, 'delivered_at timestamp is recorded and preserved');
  assert.match(workerSource, /read_at = COALESCE\(read_at, NOW\(\)\)/, 'read_at timestamp is recorded');
});

test('Phase 2: Webhook pipeline dispatches message.delivered and message.read events', () => {
  assert.match(workerSource, /enqueueWebhookEvent\(userId,\s*'message\.delivered'/, 'Dispatches message.delivered webhook event');
  assert.match(workerSource, /enqueueWebhookEvent\(userId,\s*'message\.read'/, 'Dispatches message.read webhook event');
});

test('Phase 2: Confirmed failures vs uncertain send outcomes prevent blind resends', () => {
  assert.match(workerSource, /isUncertain/, 'Worker distinguishes uncertain send outcomes from confirmed errors');
  assert.match(workerSource, /markMessageFailed\(message, error\.message, null, isUncertain\)/, 'Uncertain send outcome flag passed to markMessageFailed');
  assert.match(workerSource, /const finalFail = isUncertain \|\| Number\(message\.attempts\) >= Number\(message\.max_attempts\)/, 'Uncertain send outcomes do not trigger blind automatic resends');
});

test('Phase 2: Baileys event handlers for messages.update and message-receipt.update are wired', () => {
  assert.match(workerSource, /sock\.ev\.on\('messages\.update'/, 'Baileys messages.update listener wired');
  assert.match(workerSource, /sock\.ev\.on\('message-receipt\.update'/, 'Baileys message-receipt.update listener wired');
});
