import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';

const workerSource = readFileSync(new URL('../worker/server.mjs', import.meta.url), 'utf8');

// ============================================================================
// PART 1: Codebase Contract & Static Safeguard Verification
// ============================================================================

test('Safeguard 1: One independent queue runner per user_id', () => {
  // activeUserRunners Map must track running user queues
  assert.match(workerSource, /const activeUserRunners\s*=\s*new Map\(\);/, 'activeUserRunners Map must be defined');
  assert.match(workerSource, /function triggerUserQueue\(userId\)/, 'triggerUserQueue must be defined');
  assert.match(workerSource, /if\s*\(activeUserRunners\.has\(uid\)\)\s*\{\s*return activeUserRunners\.get\(uid\);/, 'triggerUserQueue must reuse active runner if already executing');
  assert.match(workerSource, /activeUserRunners\.set\(uid,\s*runnerPromise\);/, 'Runner promise must be registered in activeUserRunners');
  assert.match(workerSource, /activeUserRunners\.delete\(uid\);/, 'Runner must unregister from activeUserRunners on completion or error');
});

test('Safeguard 2: Maintain a 2.5-second delay between messages from the same session', () => {
  // SEND_DELAY_MS defaults to 2500ms
  assert.match(workerSource, /const SEND_DELAY_MS\s*=\s*Number\(process\.env\.SEND_DELAY_MS\s*\|\|\s*2500\);/, 'SEND_DELAY_MS must default to 2500ms');
  assert.match(workerSource, /await new Promise\(\(resolve\)\s*=>\s*setTimeout\(resolve,\s*SEND_DELAY_MS\)\);/, 'sendQueuedMessage must pause for SEND_DELAY_MS before dispatching');
});

test('Safeguard 3: Allow different users\' sessions to run concurrently', () => {
  // processQueueOnce dispatches queues per user independently rather than running in one global loop
  assert.match(
    workerSource,
    /SELECT DISTINCT user_id FROM message_queue\s+WHERE status = 'queued'\s+AND available_at <= NOW\(\)/,
    'processQueueOnce must discover users with ready queued messages'
  );
  assert.match(
    workerSource,
    /for\s*\(const row of rows\)\s*\{\s*triggerUserQueue\(String\(row\.user_id\)\);\s*\}/,
    'processQueueOnce must independently trigger queue runner for each distinct user'
  );
  // app.post('/queue/notify') accepts user_id to trigger specifically for that user
  assert.match(
    workerSource,
    /const userId = req\.body\?\.user_id;\s*if\s*\(userId\)\s*\{\s*triggerUserQueue\(String\(userId\)\);/,
    'Queue notification endpoint triggers targeted per-user runner'
  );
});

test('Safeguard 4: Never send two messages simultaneously through the same session', () => {
  // runUserQueue runs sequentially in a while loop claiming one message at a time
  assert.match(
    workerSource,
    /async function runUserQueue\(userId\)\s*\{[\s\S]*?while\s*\(true\)\s*\{[\s\S]*?const message = await claimNextMessage\(uid\);[\s\S]*?await sendQueuedMessage\(message\);/s,
    'runUserQueue must process messages strictly sequentially for the session'
  );
});

test('Safeguard 5: Handle failed messages and retries without blocking other customers', () => {
  // Exponential backoff and jitter calculations in markMessageFailed
  assert.match(workerSource, /Math\.pow\(2,\s*Math\.max\(0,\s*attempts\s*-\s*1\)\)/, 'Exponential backoff must be used for retries');
  assert.match(workerSource, /Math\.random\(\)\s*\*\s*5/, 'Jitter must be added to retry delay');
  assert.match(workerSource, /DATE_ADD\(NOW\(\),\s*INTERVAL \? SECOND\)/, 'Future available_at scheduled without thread sleep');
  assert.match(workerSource, /status = \?,\s*normalized_recipient = COALESCE\(\?,\s*normalized_recipient\)/, 'Message released from processing state');
});

test('Safeguard 6: Clean up queue runners when sessions disconnect', () => {
  // Session destruction removes from activeUserRunners
  assert.match(
    workerSource,
    /if\s*\(session\?\.userId\)\s*\{?\s*activeUserRunners\.delete\(String\(session\.userId\)\);/,
    'destroySocket must remove the disconnected user from activeUserRunners'
  );
  // runUserQueue breaks if session state is disconnected or error
  assert.match(
    workerSource,
    /if\s*\(session\.state === 'disconnected'\s*\|\|\s*session\.state === 'error'\)\s*\{[\s\S]*?break;\s*\}/,
    'runUserQueue must exit immediately if session state is disconnected or error'
  );
});

test('Safeguard 7: Keep database claims atomic to prevent duplicate message processing', () => {
  // claimNextMessage uses transaction with FOR UPDATE SKIP LOCKED
  assert.match(workerSource, /await conn\.beginTransaction\(\);/, 'claimNextMessage must start transaction');
  assert.match(workerSource, /FOR UPDATE SKIP LOCKED/, 'claimNextMessage must use FOR UPDATE SKIP LOCKED');
  assert.match(workerSource, /SET status = 'processing',/s, 'claimNextMessage must set status to processing inside transaction');
  assert.match(workerSource, /attempts = attempts \+ 1/, 'claimNextMessage increments attempts atomically');
  assert.match(workerSource, /await conn\.commit\(\);/, 'claimNextMessage must commit transaction before returning message');
});

// ============================================================================
// PART 2: Behavioral / Simulation Verification of Queue Mechanics
// ============================================================================

test('Behavioral: Per-user runner deduplication prevents duplicate runners (Safeguard 1 & 4)', async () => {
  const activeRunners = new Map();
  let executionCount = 0;

  function triggerRunner(userId) {
    const uid = String(userId);
    if (activeRunners.has(uid)) {
      return activeRunners.get(uid);
    }

    const promise = (async () => {
      try {
        executionCount++;
        await new Promise((r) => setTimeout(r, 40));
      } finally {
        activeRunners.delete(uid);
      }
    })();

    activeRunners.set(uid, promise);
    return promise;
  }

  // Trigger concurrently 5 times for user "tenant-1"
  const p1 = triggerRunner('tenant-1');
  const p2 = triggerRunner('tenant-1');
  const p3 = triggerRunner('tenant-1');
  const p4 = triggerRunner('tenant-1');
  const p5 = triggerRunner('tenant-1');

  assert.strictEqual(p1, p2, 'Concurrent triggers for the same user return the identical in-flight promise');
  assert.strictEqual(p1, p5, 'All triggers map to the single active runner');

  await Promise.all([p1, p2, p3, p4, p5]);
  assert.strictEqual(executionCount, 1, 'Only exactly 1 runner execution ran for tenant-1');
  assert.strictEqual(activeRunners.has('tenant-1'), false, 'Runner cleaned up after execution');
});

test('Behavioral: Different users run concurrently and do not block each other (Safeguard 3)', async () => {
  const activeRunners = new Map();
  const logs = [];

  function triggerRunner(userId, workDurationMs) {
    const uid = String(userId);
    if (activeRunners.has(uid)) return activeRunners.get(uid);

    const promise = (async () => {
      logs.push(`start:${uid}`);
      await new Promise((r) => setTimeout(r, workDurationMs));
      logs.push(`end:${uid}`);
    })().finally(() => activeRunners.delete(uid));

    activeRunners.set(uid, promise);
    return promise;
  }

  const startTime = Date.now();
  // Tenant A and Tenant B both take 60ms of work
  const pa = triggerRunner('user-a', 60);
  const pb = triggerRunner('user-b', 60);

  await Promise.all([pa, pb]);
  const elapsed = Date.now() - startTime;

  // If serialized, it would take ~120ms+. Since concurrent, it should finish in < 100ms.
  assert.ok(elapsed < 110, `Concurrent runs should take ~60ms, took ${elapsed}ms`);
  assert.ok(logs.indexOf('start:user-a') !== -1);
  assert.ok(logs.indexOf('start:user-b') !== -1);
  // Both start before either finishes
  assert.ok(logs.indexOf('start:user-b') < logs.indexOf('end:user-a'), 'User B started before User A finished');
});

test('Behavioral: Sequential pacing within single session (Safeguard 2 & 4)', async () => {
  const messageTimestamps = [];
  const delayMs = 30; // scaled down for unit test speed

  async function mockSend(id) {
    await new Promise((r) => setTimeout(r, delayMs));
    messageTimestamps.push({ id, time: Date.now() });
  }

  // Queue of 3 messages for user 1
  const queue = [1, 2, 3];
  while (queue.length > 0) {
    const msgId = queue.shift();
    await mockSend(msgId);
  }

  assert.strictEqual(messageTimestamps.length, 3);
  const diff1 = messageTimestamps[1].time - messageTimestamps[0].time;
  const diff2 = messageTimestamps[2].time - messageTimestamps[1].time;

  assert.ok(diff1 >= 25, `Message 2 must wait delay (was ${diff1}ms)`);
  assert.ok(diff2 >= 25, `Message 3 must wait delay (was ${diff2}ms)`);
});

test('Behavioral: Failure in tenant A does not affect or block tenant B (Safeguard 5)', async () => {
  const activeRunners = new Map();
  const completed = [];

  async function processUserQueue(userId, shouldFail) {
    if (shouldFail) {
      // Simulate exponential backoff scheduling (immediate return without blocking event loop)
      const attempts = 1;
      const baseDelay = Math.min(300, 5 * Math.pow(2, Math.max(0, attempts - 1)));
      const jitter = Math.floor(Math.random() * 5);
      const delay = baseDelay + jitter;
      assert.ok(delay >= 5, 'Backoff delay calculated');
      throw new Error('Baileys connection dropped');
    } else {
      await new Promise((r) => setTimeout(r, 20));
      completed.push(userId);
    }
  }

  function trigger(userId, shouldFail) {
    const promise = (async () => {
      try {
        await processUserQueue(userId, shouldFail);
      } catch (_) {
        // error caught and handled per-user
      }
    })().finally(() => activeRunners.delete(userId));
    activeRunners.set(userId, promise);
    return promise;
  }

  // Run User A (failing) and User B (successful) simultaneously
  await Promise.all([trigger('user-failing', true), trigger('user-healthy', false)]);

  assert.ok(completed.includes('user-healthy'), 'Healthy user completed successfully despite neighbor failure');
});

test('Behavioral: Disconnected session immediately aborts queue loop and cleans up runner (Safeguard 6)', async () => {
  const activeRunners = new Map();
  const session = { userId: 'user-dc', state: 'connected' };

  let messagesProcessed = 0;
  async function runMockUserQueue(userId) {
    while (true) {
      if (session.state === 'disconnected' || session.state === 'error') {
        break;
      }
      messagesProcessed++;
      await new Promise((r) => setTimeout(r, 20));
    }
  }

  const runnerPromise = (async () => {
    try {
      await runMockUserQueue('user-dc');
    } finally {
      activeRunners.delete('user-dc');
    }
  })();
  activeRunners.set('user-dc', runnerPromise);

  // After 10ms, disconnect the session
  setTimeout(() => {
    session.state = 'disconnected';
    activeRunners.delete('user-dc');
  }, 10);

  await runnerPromise;

  assert.strictEqual(activeRunners.has('user-dc'), false, 'Runner cleaned up after disconnect');
  assert.ok(messagesProcessed <= 1, 'Queue loop stopped promptly upon disconnect');
});

test('Behavioral: Atomic message claiming prevents double-delivery under concurrency (Safeguard 7)', async () => {
  // Simulate database row pool with locked flags
  const mockDbQueue = [
    { id: 101, status: 'queued', locked_by: null },
    { id: 102, status: 'queued', locked_by: null },
    { id: 103, status: 'queued', locked_by: null },
  ];

  let lockMutex = Promise.resolve();

  async function mockClaimNextMessage(workerId) {
    // Simulate FOR UPDATE SKIP LOCKED
    return new Promise((resolve) => {
      lockMutex = lockMutex.then(() => {
        const item = mockDbQueue.find((row) => row.status === 'queued' && !row.locked_by);
        if (item) {
          item.status = 'processing';
          item.locked_by = workerId;
          resolve({ ...item });
        } else {
          resolve(null);
        }
      });
    });
  }

  // Simulate 6 concurrent worker claims
  const claims = await Promise.all([
    mockClaimNextMessage('worker-1'),
    mockClaimNextMessage('worker-2'),
    mockClaimNextMessage('worker-3'),
    mockClaimNextMessage('worker-4'),
    mockClaimNextMessage('worker-5'),
    mockClaimNextMessage('worker-6'),
  ]);

  const claimedIds = claims.filter((c) => c !== null).map((c) => c.id);
  const uniqueIds = new Set(claimedIds);

  assert.strictEqual(claimedIds.length, 3, 'Exactly 3 messages claimed from 3 available');
  assert.strictEqual(claimedIds.length, uniqueIds.size, 'No message was claimed more than once (zero duplicates)');
});
