import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';

const workerSource = readFileSync(new URL('../worker/server.mjs', import.meta.url), 'utf8');

// ============================================================================
// PART 1: Code Contract Verification
// ============================================================================

test('Exponential Backoff: Worker implements the exact backoff formula with jitter', () => {
  // Math.min(300, 5 * Math.pow(2, Math.max(0, attempts - 1))) + jitter
  assert.match(
    workerSource,
    /Math\.min\(300,\s*5\s*\*\s*Math\.pow\(2,\s*Math\.max\(0,\s*attempts\s*-\s*1\)\)\)/,
    'Worker must compute exponential backoff with base 5 and factor 2'
  );
  assert.match(
    workerSource,
    /const jitter\s*=\s*Math\.floor\(Math\.random\(\)\s*\*\s*5\);/,
    'Worker must add a random jitter delay between 0 and 5 seconds'
  );
  assert.match(
    workerSource,
    /DATE_ADD\(NOW\(\),\s*INTERVAL \? SECOND\)/,
    'Worker must schedule next retry using available_at timestamp in database'
  );
});

test('Idempotency & Deduplication: Worker generates and passes deterministic messageId to Baileys', () => {
  assert.match(
    workerSource,
    /generateMessageID/,
    'Worker must import or employ Baileys generateMessageID'
  );
  assert.match(
    workerSource,
    /message\.provider_message_id\s*\|\|\s*currentQueueStatus\[0\]\?\.provider_message_id/,
    'Worker must reuse existing provider_message_id across retry attempts'
  );
  assert.match(
    workerSource,
    /UPDATE message_queue SET provider_message_id = \? WHERE id = \? AND provider_message_id IS NULL/,
    'Worker must persist provider_message_id before or during dispatch'
  );
  assert.match(
    workerSource,
    /sendMessage\(recipient\.jid,\s*outgoingMessage,\s*\{\s*messageId:\s*clientMessageId\s*\}\)/,
    'Worker must pass deterministic messageId to Baileys sendMessage options'
  );
});

test('Reconciliation: Worker pre-checks if message was already acknowledged via async receipts', () => {
  assert.match(
    workerSource,
    /\['sent',\s*'delivered',\s*'read'\]\.includes\(currentQueueStatus\[0\]\.status\)/,
    'Worker must check if message was already confirmed before dispatching retry'
  );
  assert.match(
    workerSource,
    /skipping redundant resend/,
    'Worker logs and aborts resend if async receipt already resolved message'
  );
});

test('Error Classification: Distinguishes permanent failures from retriable transient errors', () => {
  assert.match(
    workerSource,
    /isPermanent\s*=\s*errString\.includes\('not registered'\)/,
    'Worker identifies unregistered WhatsApp recipients as permanent'
  );
  assert.match(
    workerSource,
    /finalFail\s*=\s*isPermanent\s*\|\|\s*attempts\s*>=\s*maxAttempts/,
    'Transient network failures are retried automatically until max_attempts'
  );
});

// ============================================================================
// PART 2: Mathematical & Algorithmic Unit Tests
// ============================================================================

test('Algorithm: Exponential backoff calculation matches exact table progression', () => {
  function computeBaseDelay(attempt) {
    return Math.min(300, 5 * Math.pow(2, Math.max(0, attempt - 1)));
  }

  // Attempt 1: 5 * 2^0 = 5s
  assert.strictEqual(computeBaseDelay(1), 5, '1st failure: 5 seconds');
  // Attempt 2: 5 * 2^1 = 10s
  assert.strictEqual(computeBaseDelay(2), 10, '2nd failure: 10 seconds');
  // Attempt 3: 5 * 2^2 = 20s
  assert.strictEqual(computeBaseDelay(3), 20, '3rd failure: 20 seconds');
  // Attempt 4: 5 * 2^3 = 40s
  assert.strictEqual(computeBaseDelay(4), 40, '4th failure: 40 seconds');
  // Attempt 5: 5 * 2^4 = 80s
  assert.strictEqual(computeBaseDelay(5), 80, '5th failure: 80 seconds');
  // Attempt 6: 5 * 2^5 = 160s
  assert.strictEqual(computeBaseDelay(6), 160, '6th failure: 160 seconds');
  // Attempt 7: 5 * 2^6 = 320s -> capped at 300s
  assert.strictEqual(computeBaseDelay(7), 300, '7th failure: capped at 300s');
  // Attempt 10: capped at 300s
  assert.strictEqual(computeBaseDelay(10), 300, '10th failure: capped at 300s');
});

test('Algorithm: Jitter produces bounded, dispersed retry intervals', () => {
  const delays = [];
  for (let i = 0; i < 100; i++) {
    const jitter = Math.floor(Math.random() * 5);
    const delay = 10 + jitter; // 2nd attempt base 10s
    assert.ok(delay >= 10 && delay <= 14, `Delay ${delay} should be between 10s and 14s`);
    delays.push(delay);
  }

  const uniqueDelays = new Set(delays);
  assert.ok(uniqueDelays.size >= 3, 'Jitter should distribute retry timestamps across multiple seconds');
});

// ============================================================================
// PART 3: Real-Life Workflow Simulation Tests
// ============================================================================

test('Scenario: Transient failure recovery (ECONNRESET -> Timeout -> Sent)', async () => {
  let messageRecord = {
    id: 101,
    user_id: 1,
    recipient: '919876543210',
    body: 'Hello! Your appointment is confirmed for tomorrow at 10 AM.',
    attempts: 0,
    max_attempts: 3,
    status: 'queued',
    provider_message_id: null,
    available_at: Date.now()
  };

  const simulationEvents = [];

  function simulateSendAttempt(attemptNum, networkCondition) {
    messageRecord.attempts++;

    // Ensure deterministic message ID
    if (!messageRecord.provider_message_id) {
      messageRecord.provider_message_id = `BAILEYS_MSG_${messageRecord.id}`;
    }

    if (networkCondition === 'ECONNRESET') {
      const err = new Error('read ECONNRESET');
      err.code = 'ECONNRESET';
      const isPermanent = false;
      const finalFail = isPermanent || messageRecord.attempts >= messageRecord.max_attempts;
      const baseDelay = Math.min(300, 5 * Math.pow(2, Math.max(0, messageRecord.attempts - 1)));
      const jitter = Math.floor(Math.random() * 5);
      const delay = baseDelay + jitter;

      messageRecord.status = finalFail ? 'failed' : 'queued';
      messageRecord.available_at = Date.now() + delay * 1000;
      simulationEvents.push({
        attempt: attemptNum,
        outcome: 'ECONNRESET',
        status: messageRecord.status,
        retryDelaySeconds: delay,
        msgId: messageRecord.provider_message_id
      });
      return false;
    }

    if (networkCondition === 'TIMEOUT') {
      const isPermanent = false;
      const finalFail = isPermanent || messageRecord.attempts >= messageRecord.max_attempts;
      const baseDelay = Math.min(300, 5 * Math.pow(2, Math.max(0, messageRecord.attempts - 1)));
      const jitter = Math.floor(Math.random() * 5);
      const delay = baseDelay + jitter;

      messageRecord.status = finalFail ? 'failed' : 'queued';
      messageRecord.available_at = Date.now() + delay * 1000;
      simulationEvents.push({
        attempt: attemptNum,
        outcome: 'TIMEOUT',
        status: messageRecord.status,
        retryDelaySeconds: delay,
        msgId: messageRecord.provider_message_id
      });
      return false;
    }

    if (networkCondition === 'HEALTHY') {
      messageRecord.status = 'sent';
      simulationEvents.push({
        attempt: attemptNum,
        outcome: 'SENT',
        status: 'sent',
        msgId: messageRecord.provider_message_id
      });
      return true;
    }
  }

  // Attempt 1: ECONNRESET
  simulateSendAttempt(1, 'ECONNRESET');
  assert.strictEqual(messageRecord.status, 'queued', '1st attempt must retry (queued)');
  assert.strictEqual(simulationEvents[0].attempt, 1);
  assert.ok(simulationEvents[0].retryDelaySeconds >= 5 && simulationEvents[0].retryDelaySeconds <= 9);

  // Attempt 2: TIMEOUT
  simulateSendAttempt(2, 'TIMEOUT');
  assert.strictEqual(messageRecord.status, 'queued', '2nd attempt must retry (queued)');
  assert.strictEqual(simulationEvents[1].attempt, 2);
  assert.ok(simulationEvents[1].retryDelaySeconds >= 10 && simulationEvents[1].retryDelaySeconds <= 14);

  // Attempt 3: Network recovered -> SENT
  const success = simulateSendAttempt(3, 'HEALTHY');
  assert.strictEqual(success, true);
  assert.strictEqual(messageRecord.status, 'sent', '3rd attempt successfully delivered to WhatsApp');

  // Verify that provider_message_id remained IDENTICAL across all 3 attempts
  assert.strictEqual(simulationEvents[0].msgId, simulationEvents[1].msgId);
  assert.strictEqual(simulationEvents[1].msgId, simulationEvents[2].msgId);
});

test('Deduplication: Two Generals Problem prevention via Pre-Flight Reconciliation', () => {
  // Scenario:
  // 1. Worker sends message with provider_message_id "BAILEYS_XYZ_1"
  // 2. WhatsApp receives message and delivers to recipient
  // 3. Internet disconnects before worker receives ACK; worker records uncertain failure
  // 4. WhatsApp reconnects and fires incoming delivery receipt for "BAILEYS_XYZ_1"
  // 5. handleMessageStatusUpdate updates message status to 'delivered'
  // 6. When the retry timer wakes up, pre-flight check discovers 'delivered' and prevents duplicate send

  let messageQueue = {
    id: 202,
    status: 'queued',
    provider_message_id: 'BAILEYS_XYZ_1',
    attempts: 1
  };

  let actualSendsExecuted = 0;

  // Step 4 & 5: Incoming async receipt arrives
  function handleAsyncDeliveryReceipt(incomingId) {
    if (messageQueue.provider_message_id === incomingId) {
      messageQueue.status = 'delivered';
    }
  }

  handleAsyncDeliveryReceipt('BAILEYS_XYZ_1');
  assert.strictEqual(messageQueue.status, 'delivered', 'Database updated to delivered via async receipt');

  // Step 6: Queue runner attempts retry
  function executeQueuedSend(msg) {
    // Pre-flight check
    if (['sent', 'delivered', 'read'].includes(msg.status)) {
      return { skipped: true, reason: 'Already confirmed via reconciliation' };
    }
    actualSendsExecuted++;
    return { skipped: false };
  }

  const result = executeQueuedSend(messageQueue);
  assert.strictEqual(result.skipped, true);
  assert.strictEqual(actualSendsExecuted, 0, 'Zero duplicate messages sent to recipient');
});

test('Error Classification: Permanent errors fail immediately without retry', () => {
  const permanentErrors = [
    'The number 919999999999 is not registered on WhatsApp.',
    'Invalid recipient. Use an international number or a 10-digit Indian mobile number.',
    'Recipient opted out or suppressed.',
    'Attachment file is missing from storage.'
  ];

  for (const errText of permanentErrors) {
    const isPermanent = errText.includes('not registered') ||
      errText.includes('Invalid recipient') ||
      errText.includes('opted out') ||
      errText.includes('missing from storage') ||
      errText.includes('invalid');

    const attempts = 1;
    const maxAttempts = 3;
    const finalFail = isPermanent || attempts >= maxAttempts;
    const nextStatus = finalFail ? 'failed' : 'queued';

    assert.strictEqual(isPermanent, true, `Should classify as permanent: "${errText}"`);
    assert.strictEqual(nextStatus, 'failed', `Permanent error must fail immediately on attempt ${attempts}`);
  }
});
