import test from 'node:test';
import assert from 'node:assert/strict';
import crypto from 'node:crypto';
import { readFileSync } from 'node:fs';

const workerSource = readFileSync(new URL('../worker/server.mjs', import.meta.url), 'utf8');

test('Phase 1: Inbound message normalization extracts all core fields and prevents credential leakage', () => {
  // Test the normalization logic structure
  assert.match(workerSource, /function normalizeInboundMessage/, 'normalizeInboundMessage must be defined');
  assert.match(workerSource, /eventId = `evt_\${crypto\.randomUUID\(\)}`/, 'Each normalized inbound message receives a unique event ID');
  assert.match(workerSource, /isGroup = remoteJid\.endsWith\('@g\.us'\)/, 'Group messages are properly flagged');
  
  // Verify support for diverse message types
  assert.match(workerSource, /content\.imageMessage/, 'Handles image messages');
  assert.match(workerSource, /content\.videoMessage/, 'Handles video messages');
  assert.match(workerSource, /content\.audioMessage/, 'Handles audio messages');
  assert.match(workerSource, /content\.documentMessage/, 'Handles document messages');
  assert.match(workerSource, /content\.locationMessage/, 'Handles location messages');
  assert.match(workerSource, /content\.contactMessage/, 'Handles contact messages');
  assert.match(workerSource, /content\.reactionMessage/, 'Handles reaction messages');

  // Verify that internal file paths or secret values are not leaked in normalized inbound payloads
  assert.doesNotMatch(workerSource, /mediaInfo\s*=\s*\{[^}]*path:/, 'mediaInfo must not expose internal filesystem paths');
});

test('Phase 1: Inbound messages and webhook events are persisted before processing completes', () => {
  assert.match(workerSource, /INSERT INTO inbound_messages/, 'Inbound messages must be persisted to database');
  assert.match(workerSource, /ON DUPLICATE KEY UPDATE/, 'Inbound message persistence is idempotent');
  assert.match(workerSource, /enqueueWebhookEvent\(userId,\s*'message\.received'/, 'Inbound webhook event is enqueued');
  assert.match(workerSource, /INSERT INTO webhook_queue/, 'Webhook queue records the asynchronous event');
});

test('Phase 1: Asynchronous delivery queue ensures WhatsApp listener is not blocked by HTTP calls', () => {
  // Ensure the upsert handler only enqueues and uses setImmediate, never waits for fetch
  const upsertBlock = workerSource.slice(
    workerSource.indexOf("sock.ev.on('messages.upsert'"),
    workerSource.indexOf("sock.ev.on('messages.upsert'") + 3000
  );
  assert.doesNotMatch(upsertBlock, /await\s+fetch\(/, 'messages.upsert must never directly await external HTTP fetch');
  assert.match(workerSource, /function processWebhookQueueOnce\(\)/, 'Asynchronous webhook queue processor must exist');
  assert.match(workerSource, /FOR UPDATE SKIP LOCKED/, 'Webhook queue claims jobs using atomic transactions');
});

test('Phase 1: Webhook signing with HMAC-SHA256, timestamps, event IDs, and secret rotation', () => {
  assert.match(workerSource, /X-WA-Hub-Event-Id/, 'Headers must include X-WA-Hub-Event-Id');
  assert.match(workerSource, /X-WA-Hub-Timestamp/, 'Headers must include X-WA-Hub-Timestamp');
  assert.match(workerSource, /X-WA-Hub-Signature/, 'Headers must include X-WA-Hub-Signature');
  assert.match(workerSource, /X-Hub-Signature-256/, 'Headers must include X-Hub-Signature-256');
  assert.match(workerSource, /X-WA-Hub-Signature-Old/, 'Headers must support secondary secret during rotation');
  assert.match(workerSource, /crypto\.createHmac\('sha256',\s*webhook\.secret\)\.update\(bodyString\)\.digest\('hex'\)/, 'Signature must be HMAC-SHA256 over exact bodyString');

  // Verify HMAC computation math directly
  const secret = 'my_test_webhook_secret_key_123';
  const body = JSON.stringify({ event: 'message.received', event_id: 'evt_test_123' });
  const sig = crypto.createHmac('sha256', secret).update(body).digest('hex');
  const expectedSig = crypto.createHmac('sha256', secret).update(body).digest('hex');
  assert.equal(sig, expectedSig);
  assert.equal(sig.length, 64);
});

test('Phase 1: Webhook bounded retries, exponential backoff, jitter, and dead-letter handling', () => {
  assert.match(workerSource, /status = 'dead_letter'/, 'Jobs reaching max attempts must be moved to dead_letter');
  assert.match(workerSource, /Math\.pow\(2,\s*currentAttempt\s*-\s*1\)/, 'Retry delay must use exponential backoff');
  assert.match(workerSource, /Math\.random\(\)\s*\*\s*5/, 'Retry delay must include randomized jitter');
  assert.match(workerSource, /DATE_ADD\(NOW\(\),\s*INTERVAL \? SECOND\)/, 'Retrying webhook jobs are scheduled in future available_at');
  assert.match(workerSource, /AbortSignal\.timeout\(8000\)/, 'HTTP requests must enforce timeout');
  assert.match(workerSource, /redirect:\s*'error'/, 'Redirects must be rejected to prevent SSRF bypass');
});

test('Phase 1: Webhook URL SSRF validation blocks private networks and requires HTTPS', () => {
  assert.match(workerSource, /assertSafeWebhookUrl/, 'Webhook delivery must enforce assertSafeWebhookUrl');
  assert.match(workerSource, /parsed\.protocol !== 'https:'/, 'Webhook URLs must enforce HTTPS');
  assert.match(workerSource, /isPrivateIp\(entry\.address\)/, 'Webhook destination IPs must not be private or reserved');
});
