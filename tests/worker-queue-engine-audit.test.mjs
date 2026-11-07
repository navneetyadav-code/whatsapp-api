import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';

const workerSource = readFileSync(new URL('../worker/server.mjs', import.meta.url), 'utf8');

// ============================================================================
// AUDIT ITEM 1: De-serialize Queue Processing (Per-Session Dispatch)
// ============================================================================

test('Audit 1.1.A: Global queueBusy lock is completely eliminated from queue processing', () => {
  // Global queueBusy lock must not exist in worker/server.mjs
  assert.doesNotMatch(
    workerSource,
    /let\s+queueBusy\s*=\s*(false|true)/,
    'Global let queueBusy flag must be removed to prevent global gateway serialization'
  );
  assert.doesNotMatch(
    workerSource,
    /if\s*\(\s*queueBusy\s*\)\s*return/,
    'processQueueOnce must not be guarded by a global queueBusy flag'
  );
});

test('Audit 1.1.B: Per-session runner map maintains independent concurrent queues', () => {
  assert.match(
    workerSource,
    /const\s+activeUserRunners\s*=\s*new\s+Map\(\);/,
    'activeUserRunners map must be instantiated for per-session queue dispatch'
  );
  assert.match(
    workerSource,
    /function\s+triggerUserQueue\s*\(\s*userId\s*\)/,
    'triggerUserQueue function must exist to manage deduplicated per-user runners'
  );
  assert.match(
    workerSource,
    /async\s+function\s+runUserQueue\s*\(\s*userId\s*\)/,
    'runUserQueue function must exist to process queue per tenant session'
  );
  assert.match(
    workerSource,
    /claimNextMessage\s*\(\s*uid\s*\)/,
    'runUserQueue must claim messages scoped strictly to the given tenant user_id'
  );
});

test('Audit 1.1.C: processQueueOnce dispatches tenants in parallel and /queue/notify wakes per user', () => {
  assert.match(
    workerSource,
    /SELECT\s+DISTINCT\s+user_id\s+FROM\s+message_queue[\s\S]*?triggerUserQueue\(String\(row\.user_id\)\)/,
    'processQueueOnce must discover pending tenants and trigger their queue runners concurrently'
  );
  assert.match(
    workerSource,
    /app\.post\('\/queue\/notify'[\s\S]*?triggerUserQueue\(String\(userId\)\)/,
    '/queue/notify must immediately wake the specific tenant runner'
  );
});

// ============================================================================
// AUDIT ITEM 2: Prevent Premature Message Failure on Flaky Networks
// ============================================================================

test('Audit 1.2.A: ECONNRESET, socket drops, and timeouts do not force finalFail on attempt 1', () => {
  // Must NOT do: const finalFail = isUncertain || ...
  assert.doesNotMatch(
    workerSource,
    /finalFail\s*=\s*isUncertain/,
    'isUncertain must NOT directly cause finalFail'
  );
  assert.match(
    workerSource,
    /finalFail\s*=\s*isPermanent\s*\|\|\s*attempts\s*>=\s*maxAttempts/,
    'finalFail must only trigger on permanent errors or when attempts >= maxAttempts'
  );
});

test('Audit 1.2.B: Retries use exponential backoff with jitter up to max_attempts', () => {
  // Math.min(300, 5 * Math.pow(2, Math.max(0, attempts - 1))) + jitter
  assert.match(
    workerSource,
    /Math\.min\(300,\s*5\s*\*\s*Math\.pow\(2,\s*Math\.max\(0,\s*attempts\s*-\s*1\)\)\)/,
    'Worker must calculate exponential backoff using base 5 and factor 2 capped at 300s'
  );
  assert.match(
    workerSource,
    /const\s+jitter\s*=\s*Math\.floor\(Math\.random\(\)\s*\*\s*5\);/,
    'Worker must add a random jitter between 0 and 4 seconds'
  );
  assert.match(
    workerSource,
    /DATE_ADD\(NOW\(\),\s*INTERVAL\s+\?\s+SECOND\)/,
    'Worker must reschedule retry in message_queue using available_at'
  );
});

test('Audit 1.2.C: Transient network blips re-queue while permanent errors fail immediately', () => {
  assert.match(
    workerSource,
    /isPermanent\s*=\s*errString\.includes\('not registered'\)/,
    'Unregistered WhatsApp recipient is correctly categorized as permanent error'
  );
  assert.match(
    workerSource,
    /errString\.includes\('Invalid recipient'\)/,
    'Invalid recipient phone number is categorized as permanent error'
  );
  assert.match(
    workerSource,
    /errString\.includes\('opted out'\)/,
    'Suppressed/opted-out recipient is categorized as permanent error'
  );
});

// ============================================================================
// AUDIT ITEM 3: Implement Deterministic Baileys Idempotency
// ============================================================================

test('Audit 1.3.A: Deterministic messageId is generated and passed to Baileys sendMessage options', () => {
  assert.match(
    workerSource,
    /generateMessageID/,
    'Baileys generateMessageID must be imported'
  );
  assert.match(
    workerSource,
    /sendMessage\(recipient\.jid,\s*outgoingMessage,\s*\{\s*messageId:\s*clientMessageId\s*\}\)/,
    'sendMessage must pass { messageId: clientMessageId } to Baileys for stanza deduplication'
  );
});

test('Audit 1.3.B: Idempotency key is persisted to database before socket transmission', () => {
  assert.match(
    workerSource,
    /UPDATE\s+message_queue\s+SET\s+provider_message_id\s*=\s*\?\s+WHERE\s+id\s*=\s*\?\s+AND\s+provider_message_id\s+IS\s+NULL/,
    'provider_message_id must be written to MySQL before sending to preserve key across crashes'
  );
  assert.match(
    workerSource,
    /let\s+clientMessageId\s*=\s*message\.provider_message_id\s*\|\|\s*currentQueueStatus\[0\]\?\.provider_message_id/,
    'Subsequent retry attempts must reuse the exact same provider_message_id'
  );
});

test('Audit 1.3.C: Pre-flight reconciliation prevents duplicate resends if ACK arrived during backoff', () => {
  assert.match(
    workerSource,
    /\['sent',\s*'delivered',\s*'read'\]\.includes\(currentQueueStatus\[0\]\.status\)/,
    'Pre-flight check must verify if WhatsApp already acknowledged message via async receipts'
  );
  assert.match(
    workerSource,
    /skipping redundant resend/,
    'Worker must log and abort send if pre-flight check detects confirmed status'
  );
});

// ============================================================================
// AUDIT ITEM 4: Queue Autonomous AI Inbound Replies
// ============================================================================

test('Audit 1.4.A: Autonomous multi-turn AI responses route through message_queue with Priority 10', () => {
  assert.match(
    workerSource,
    /async\s+function\s+queueBotReply\s*\(/,
    'queueBotReply helper must be defined in worker/server.mjs'
  );
  assert.match(
    workerSource,
    /INSERT\s+INTO\s+message_queue[\s\S]*?10,\s*'queued'\)/,
    'queueBotReply must insert into message_queue with priority 10'
  );
  const startIdx = workerSource.indexOf('replyText = await generateAiReply');
  assert.ok(startIdx !== -1, 'generateAiReply must exist in worker');
  const aiBlock = workerSource.slice(startIdx, startIdx + 800);
  assert.ok(
    !aiBlock.includes('sock.sendMessage') && !aiBlock.includes('session.sock.sendMessage'),
    'generateAiReply block must NOT call sock.sendMessage directly'
  );
  assert.ok(
    aiBlock.includes('await queueBotReply('),
    'generateAiReply block must call queueBotReply'
  );
});

test('Audit 1.4.B: All automated bot triggers route through message_queue', () => {
  // Repeated greeting intercept
  assert.match(
    workerSource,
    /greetingCheck\.shouldIntercept[\s\S]*?await\s+queueBotReply\s*\(\s*userId,\s*msg\.key\.remoteJid/,
    'Repeated greeting intercept routes through queueBotReply'
  );
  // Session resolution closure
  assert.match(
    workerSource,
    /ChatResolutionEngine\.getClosureMessage\(\)[\s\S]*?await\s+queueBotReply\s*\(\s*userId,\s*msg\.key\.remoteJid/,
    'Session closure routes through queueBotReply'
  );
  // Topic reset (/clear, /new)
  assert.match(
    workerSource,
    /isTopicResetTrigger[\s\S]*?await\s+queueBotReply\s*\(\s*userId,\s*msg\.key\.remoteJid/,
    'Topic reset routes through queueBotReply'
  );
  // Welcome start (/start)
  assert.match(
    workerSource,
    /isWakeupTrigger[\s\S]*?await\s+queueBotReply\s*\(\s*userId,\s*msg\.key\.remoteJid/,
    'Welcome greeting routes through queueBotReply'
  );
  // Human handover message
  assert.match(
    workerSource,
    /handoverMsg[\s\S]*?await\s+queueBotReply\s*\(\s*userId,\s*msg\.key\.remoteJid/,
    'Escalation handover routes through queueBotReply'
  );
});

test('Audit 1.4.C: chat_messages and message_queue statuses remain synchronized', () => {
  assert.match(
    workerSource,
    /chat_message_id:\s*chatMessageId/,
    'Payload must correlate chat_message_id'
  );
  assert.match(
    workerSource,
    /UPDATE\s+chat_messages\s+SET\s+wa_message_id\s*=\s*\?,\s*status\s*=\s*["']sent["']\s+WHERE\s+id\s*=\s*\?\s+AND\s+user_id\s*=\s*\?/,
    'markMessageSent must synchronize the confirmed Baileys ID to chat_messages'
  );
  assert.match(
    workerSource,
    /UPDATE\s+chat_messages\s+SET\s+status\s*=\s*["']failed["']\s+WHERE\s+id\s*=\s*\?\s+AND\s+user_id\s*=\s*\?/,
    'markMessageFailed must update chat_messages status to failed when all retries fail'
  );
});
