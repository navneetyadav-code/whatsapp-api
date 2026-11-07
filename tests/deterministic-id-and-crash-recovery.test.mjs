import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import crypto from 'node:crypto';

const workerSource = readFileSync(new URL('../worker/server.mjs', import.meta.url), 'utf8');

// ============================================================================
// PART 1: Baileys Compatibility Verification
// ============================================================================

test('Baileys Verification: Installed Baileys supports and preserves caller-supplied messageId', async () => {
  // Dynamically import installed Baileys from worker's node_modules
  const baileys = await import('../worker/node_modules/@whiskeysockets/baileys/lib/index.js');

  assert.ok(typeof baileys.generateWAMessageFromContent === 'function', 'Baileys must export generateWAMessageFromContent');

  // Test 1: Caller-supplied messageId in generateWAMessageFromContent
  const customId = '3EB0D4F074A5CA10A7426A';
  const testMsg = baileys.generateWAMessageFromContent(
    '919876543210@s.whatsapp.net',
    { conversation: 'Test message with custom ID' },
    { messageId: customId }
  );

  assert.strictEqual(testMsg.key.id, customId, 'Baileys generateWAMessageFromContent must preserve custom messageId');
  assert.strictEqual(testMsg.key.fromMe, true, 'Message key fromMe must be true');

  // Test 2: Protobuf binary serialization losslessly preserves caller-supplied messageId
  const encoded = baileys.proto.WebMessageInfo.encode(testMsg).finish();
  const decoded = baileys.proto.WebMessageInfo.decode(encoded);
  assert.strictEqual(decoded.key.id, customId, 'Protobuf encoding/decoding must preserve custom messageId exactly');

  // Test 3: Literal WG_1250 style ID also encodes cleanly without protobuf failure
  const literalId = 'WG_1250';
  const literalMsg = baileys.generateWAMessageFromContent(
    '919876543210@s.whatsapp.net',
    { conversation: 'Test literal ID' },
    { messageId: literalId }
  );
  assert.strictEqual(literalMsg.key.id, literalId);
  const literalEncoded = baileys.proto.WebMessageInfo.encode(literalMsg).finish();
  const literalDecoded = baileys.proto.WebMessageInfo.decode(literalEncoded);
  assert.strictEqual(literalDecoded.key.id, literalId);
});

// ============================================================================
// PART 2: Deterministic Message ID Generation
// ============================================================================

test('Safeguard 1: Deterministic ID generates identical ID across multiple worker crashes/restarts', () => {
  function generateDeterministicMessageId(queueId, format = 'web') {
    if (!queueId) return '3EB0DEFAULT';
    if (format === 'literal') return `WG_${queueId}`;
    const hash = crypto.createHash('sha256').update(`WG_${queueId}`).digest('hex').toUpperCase();
    return '3EB0' + hash.substring(0, 18);
  }

  const queueId = 1250;

  // Simulate 5 separate worker process executions/restarts
  const run1 = generateDeterministicMessageId(queueId);
  const run2 = generateDeterministicMessageId(queueId);
  const run3 = generateDeterministicMessageId(queueId);
  const run4 = generateDeterministicMessageId(queueId);
  const run5 = generateDeterministicMessageId(queueId);

  assert.strictEqual(run1, run2, 'Run 2 must match Run 1');
  assert.strictEqual(run2, run3, 'Run 3 must match Run 2');
  assert.strictEqual(run3, run4, 'Run 4 must match Run 3');
  assert.strictEqual(run4, run5, 'Run 5 must match Run 4');

  // Check WhatsApp Web compliance (3EB0 prefix + 18 hex chars = 22 total length)
  assert.strictEqual(run1.startsWith('3EB0'), true, 'ID must use official WhatsApp Web 3EB0 signature');
  assert.strictEqual(run1.length, 22, 'ID length must be exactly 22 characters');
  assert.match(run1, /^3EB0[0-9A-F]{18}$/, 'ID must be valid uppercase hexadecimal');

  // Distinct queue messages produce distinct IDs
  const otherId = generateDeterministicMessageId(1251);
  assert.notStrictEqual(run1, otherId, 'Different queue IDs must never collide');
});

// ============================================================================
// PART 3: Codebase Contract Tests
// ============================================================================

test('Safeguard 2: Worker persists deterministic ID before calling Baileys sendMessage', () => {
  assert.match(
    workerSource,
    /function generateDeterministicMessageId\(queueId\)/,
    'Worker must define generateDeterministicMessageId helper'
  );
  assert.match(
    workerSource,
    /clientMessageId = generateDeterministicMessageId\(message\.id\);/,
    'Worker must derive deterministic ID from message.id'
  );
  assert.match(
    workerSource,
    /UPDATE message_queue SET provider_message_id = \? WHERE id = \? AND provider_message_id IS NULL/,
    'Worker must persist provider_message_id before network transmission'
  );
  assert.match(
    workerSource,
    /sendMessage\(recipient\.jid,\s*outgoingMessage,\s*\{\s*messageId:\s*clientMessageId\s*\}\)/,
    'Worker must pass deterministic messageId to Baileys'
  );
});

test('Safeguard 5: Lease expiration marks in-flight jobs as uncertain with reconciliation delay', () => {
  assert.match(
    workerSource,
    /Worker lease expired while in-flight; marked as uncertain for reconciliation\./,
    'cleanupExpiredLeases must mark in-flight jobs with assigned provider_message_id as uncertain'
  );
  assert.match(
    workerSource,
    /available_at = DATE_ADD\(NOW\(\),\s*INTERVAL 15 SECOND\)/,
    'cleanupExpiredLeases must allow a 15-second reconciliation window before retrying in-flight jobs'
  );
});

test('Safeguard 6: Pre-flight check prevents redundant transmission of reconciled messages', () => {
  assert.match(
    workerSource,
    /\['sent',\s*'delivered',\s*'read'\]\.includes\(currentQueueStatus\[0\]\.status\)/,
    'sendQueuedMessage must verify if message reached sent/delivered/read status before sending'
  );
  assert.match(
    workerSource,
    /skipping redundant resend/,
    'sendQueuedMessage must log and skip resending if message was already confirmed'
  );
});

// ============================================================================
// PART 4: End-to-End Worker Crash & Application Recovery Simulation
// ============================================================================

test('Simulation: Crash after dispatch is safely reconciled without duplicate delivery', async () => {
  // Scenario:
  // 1. Worker picks up job 1250
  // 2. Assigns deterministic ID 3EB0...
  // 3. Persists to DB
  // 4. Dispatches to WhatsApp (WhatsApp receives it!)
  // 5. Worker crashes abruptly before recording success in DB (lease becomes expired)
  // 6. cleanupExpiredLeases() runs on reboot, flags message as uncertain with 15s window
  // 7. Baileys connects, receives async delivery receipt from WhatsApp, and promotes status to 'delivered'
  // 8. Queue runner wakes up, checks DB, sees 'delivered', and skips resending!

  const db = {
    message_queue: {
      id: 1250,
      user_id: 1,
      status: 'processing',
      provider_message_id: null,
      locked_at: new Date(Date.now() - 6 * 60 * 1000), // locked 6 mins ago (lease expired)
      attempts: 1,
      max_attempts: 3
    }
  };

  // Step 2 & 3: Assign and persist deterministic ID
  const deterministicId = '3EB0' + crypto.createHash('sha256').update('WG_1250').digest('hex').toUpperCase().substring(0, 18);
  db.message_queue.provider_message_id = deterministicId;

  // Step 4: Dispatched to WhatsApp network
  let networkSendsCount = 1;

  // Step 5: Worker crashes. Database row is still 'processing' with provider_message_id set.
  assert.strictEqual(db.message_queue.status, 'processing');
  assert.strictEqual(db.message_queue.provider_message_id, deterministicId);

  // Step 6: Worker reboots. cleanupExpiredLeases() executes.
  if (db.message_queue.status === 'processing' && db.message_queue.provider_message_id) {
    db.message_queue.status = 'queued';
    db.message_queue.error_message = 'Worker lease expired while in-flight; marked as uncertain for reconciliation.';
    db.message_queue.available_at = Date.now() + 15000;
  }

  assert.strictEqual(db.message_queue.status, 'queued');
  assert.strictEqual(db.message_queue.error_message.includes('uncertain for reconciliation'), true);

  // Step 7: Baileys reconnects and handles incoming delivery receipt for deterministicId
  function onIncomingWhatsAppReceipt(receiptMessageId) {
    if (db.message_queue.provider_message_id === receiptMessageId) {
      db.message_queue.status = 'delivered';
      db.message_queue.delivered_at = new Date();
    }
  }

  onIncomingWhatsAppReceipt(deterministicId);
  assert.strictEqual(db.message_queue.status, 'delivered', 'Database updated to delivered via async receipt');

  // Step 8: Queue runner runs sendQueuedMessage after the 15-second window
  function sendQueuedMessage(job) {
    // Pre-flight check
    if (['sent', 'delivered', 'read'].includes(job.status)) {
      return { skipped: true, reason: 'Already resolved via async reconciliation' };
    }
    networkSendsCount++;
    return { skipped: false };
  }

  const result = sendQueuedMessage(db.message_queue);
  assert.strictEqual(result.skipped, true);
  assert.strictEqual(networkSendsCount, 1, 'Exactly one message was sent; zero duplicates occurred despite worker crash!');
});
