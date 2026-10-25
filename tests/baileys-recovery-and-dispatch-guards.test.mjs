import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';

const workerSource = readFileSync(new URL('../worker/server.mjs', import.meta.url), 'utf8');
const migration3Sql = readFileSync(new URL('../migrations/003_consent_and_message_store.sql', import.meta.url), 'utf8');

test('Phase 2.1: Direct send endpoint routes through centralized message_queue with priority 10', () => {
  assert.match(
    workerSource,
    /app\.post\('\/sessions\/:userId\/send'/,
    'Direct send endpoint must exist'
  );
  assert.match(
    workerSource,
    /INSERT INTO message_queue[\s\S]*?VALUES \(\?, NULL, \?, \?, 'text', \?, \?, 10, 'processing'\)/,
    'Direct send endpoint must record job into message_queue with priority 10 and processing status'
  );
  assert.match(
    workerSource,
    /UPDATE message_queue\s+SET status = 'sent', provider_message_id = \?, sent_at = NOW\(\)/,
    'Direct send endpoint must update message_queue upon completion'
  );
  assert.match(
    workerSource,
    /INSERT INTO message_logs/,
    'Direct send endpoint must record audit log into message_logs'
  );
  assert.match(
    workerSource,
    /queueId = insertResult\.insertId/,
    'Direct send endpoint returns queueId for correlation'
  );
});

test('Phase 2.1 & 6: Direct send endpoint enforces suppression / opt-out boundary', () => {
  assert.match(
    workerSource,
    /const isSuppressed = await isRecipientSuppressedInDb\(req\.params\.userId, recipient\.normalizedTo\);/,
    'Direct send route must verify recipient suppression before dispatch'
  );
  assert.match(
    workerSource,
    /res\.status\(403\)\.json\(\{\s*ok:\s*false,\s*error:\s*`The recipient \${recipient\.normalizedTo} is suppressed or opted out.`/,
    'Direct send route must reject suppressed recipients with HTTP 403'
  );
});

test('Phase 3.1 & 3.2: Baileys getMessage and msgRetryCounterCache are properly wired', () => {
  assert.match(
    workerSource,
    /getMessage:\s*async\s*\(key\)\s*=>\s*await\s+getBaileysMessage\(key\)/,
    'makeWASocket must wire getMessage to getBaileysMessage'
  );
  assert.match(
    workerSource,
    /msgRetryCounterCache,/,
    'makeWASocket must wire msgRetryCounterCache'
  );
  assert.match(
    workerSource,
    /function createMemoryCache\(maxEntries = 2000, ttlMs = 15 \* 60 \* 1000\)/,
    'Worker provides bounded memory cache store with TTL'
  );
  assert.match(
    workerSource,
    /function storeBaileysMessage\(userId, remoteJid, messageId, fromMe, participant, messageProto\)/,
    'Worker provides storeBaileysMessage'
  );
  assert.match(
    workerSource,
    /function getBaileysMessage\(key\)/,
    'Worker provides getBaileysMessage'
  );
  assert.match(
    migration3Sql,
    /CREATE TABLE IF NOT EXISTS `baileys_message_store`/,
    'Migration 003 creates baileys_message_store'
  );
});

test('Phase 4: Recipient validation lookup caching and transient error resilience', () => {
  assert.match(
    workerSource,
    /function verifyRecipientLookup\(sock, recipientJid, cleanPhone\)/,
    'Worker provides verifyRecipientLookup abstraction'
  );
  assert.match(
    workerSource,
    /recipientLookupCache = new Map\(\)/,
    'Worker maintains bounded recipientLookupCache'
  );
  assert.match(
    workerSource,
    /recipientLookupInFlight = new Map\(\)/,
    'Worker deduplicates concurrent in-flight lookups'
  );
  assert.match(
    workerSource,
    /transientError:\s*true/,
    'Transient network failures do not falsely mark numbers as unregistered'
  );
});

test('Phase 7: Chatbot auto-reply loop protection prevents infinite recursion', () => {
  assert.match(
    workerSource,
    /chatbotReplyTracker = new Map\(\)/,
    'Worker tracks chatbot replies per recipient'
  );
  assert.match(
    workerSource,
    /Chatbot reply rate limit exceeded; skipping to prevent loops/,
    'Worker logs rate limit warning and skips reply when recursion threshold is reached'
  );
  assert.match(
    workerSource,
    /tracker\.count >= 3/,
    'Worker bounds auto-replies to 3 per recipient within window'
  );
});

test('Phase 2.2: Dispatch boundary guards against cancelled jobs and campaigns', () => {
  assert.match(
    workerSource,
    /Message was cancelled while awaiting dispatch; aborting send/,
    'Worker aborts message if cancelled in queue'
  );
  assert.match(
    workerSource,
    /Campaign was cancelled; aborting message send/,
    'Worker aborts send if parent campaign was cancelled'
  );
  assert.match(
    workerSource,
    /Campaign is paused; deferring message send/,
    'Worker defers send if parent campaign was paused'
  );
});
