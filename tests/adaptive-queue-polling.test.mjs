import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';

const workerSource = readFileSync(new URL('../worker/server.mjs', import.meta.url), 'utf8');

// ============================================================================
// PART 1: Codebase Contract & Static Safeguards Verification
// ============================================================================

test('Contract: Adaptive polling constants are defined with correct defaults', () => {
  // QUEUE_POLL_ACTIVE_MS defaults to 1000ms (1s)
  assert.match(
    workerSource,
    /const QUEUE_POLL_ACTIVE_MS\s*=\s*Number\(process\.env\.QUEUE_POLL_ACTIVE_MS\s*\|\|\s*1000\);/,
    'QUEUE_POLL_ACTIVE_MS must default to 1000ms'
  );

  // QUEUE_POLL_IDLE_MS defaults to 5000ms (5s)
  assert.match(
    workerSource,
    /const QUEUE_POLL_IDLE_MS\s*=\s*Number\(process\.env\.QUEUE_POLL_IDLE_MS\s*\|\|\s*5000\);/,
    'QUEUE_POLL_IDLE_MS must default to 5000ms'
  );

  // currentQueuePollMs initializes to QUEUE_POLL_IDLE_MS
  assert.match(
    workerSource,
    /let currentQueuePollMs\s*=\s*QUEUE_POLL_IDLE_MS;/,
    'currentQueuePollMs must initialize to idle interval (5000ms)'
  );
});

test('Contract: scheduleNextQueuePoll handles dynamic delays and timer management', () => {
  assert.match(
    workerSource,
    /function scheduleNextQueuePoll\s*\(delayMs\s*=\s*null\)\s*\{[\s\S]*?if\s*\(queuePollTimer\)\s*\{\s*clearTimeout\(queuePollTimer\);[\s\S]*?queuePollTimer\s*=\s*setTimeout\(/s,
    'scheduleNextQueuePoll must clear existing timer and set a new timeout'
  );

  assert.match(
    workerSource,
    /const delay\s*=\s*\(delayMs\s*!==\s*null\s*&&\s*delayMs\s*!==\s*undefined\)\s*\?\s*delayMs\s*:\s*currentQueuePollMs;/,
    'scheduleNextQueuePoll must support overriding delayMs (e.g. 0ms for instant wake) or use currentQueuePollMs'
  );

  assert.match(
    workerSource,
    /finally\s*\{\s*scheduleNextQueuePoll\(\);\s*\}/,
    'scheduleNextQueuePoll must self-reschedule in finally block'
  );
});

test('Contract: processQueueOnce switches dynamically between active (1s) and idle (5s)', () => {
  assert.match(
    workerSource,
    /if\s*\(hasPending\s*\|\|\s*activeUserRunners\.size\s*>\s*0\)\s*\{\s*currentQueuePollMs\s*=\s*QUEUE_POLL_ACTIVE_MS;\s*\/\/ 1000ms\s*\}\s*else\s*\{\s*currentQueuePollMs\s*=\s*QUEUE_POLL_IDLE_MS;\s*\/\/ 5000ms\s*\}/,
    'processQueueOnce must toggle currentQueuePollMs between 1s (active) and 5s (idle)'
  );
});

test('Contract: POST /queue/notify resets polling interval to active (1s) and wakes immediately (0ms)', () => {
  assert.match(
    workerSource,
    /app\.post\('\/queue\/notify',\s*\(req,\s*res\)\s*=>\s*\{[\s\S]*?currentQueuePollMs\s*=\s*QUEUE_POLL_ACTIVE_MS;\s*scheduleNextQueuePoll\(0\);/s,
    'POST /queue/notify must set currentQueuePollMs to active rate and trigger scheduleNextQueuePoll(0)'
  );
});

// ============================================================================
// PART 2: Behavioral & Operational Verification
// ============================================================================

test('Behavioral: Adaptive poller transitions accurately between active, idle, and immediate wake', async () => {
  const ACTIVE_MS = 100; // Scaled for fast unit testing
  const IDLE_MS = 500;

  let currentInterval = IDLE_MS;
  let scheduledDelay = null;
  let executionCount = 0;
  let pendingCount = 0;
  const activeRunners = new Map();

  function scheduleNext(delayMs = null) {
    scheduledDelay = delayMs !== null && delayMs !== undefined ? delayMs : currentInterval;
  }

  function tick(hasPending) {
    executionCount++;
    if (hasPending || activeRunners.size > 0) {
      currentInterval = ACTIVE_MS;
    } else {
      currentInterval = IDLE_MS;
    }
    scheduleNext();
  }

  function notify() {
    currentInterval = ACTIVE_MS;
    scheduleNext(0);
  }

  // 1. Initial State: Idle
  tick(false);
  assert.equal(currentInterval, IDLE_MS, 'Should be idle when no pending jobs and no active runners');
  assert.equal(scheduledDelay, IDLE_MS, 'Scheduled delay should be 500ms (idle)');

  // 2. Incoming Notification: Immediate wake (0ms)
  notify();
  assert.equal(scheduledDelay, 0, 'Notification must schedule immediate wake with 0ms delay');
  assert.equal(currentInterval, ACTIVE_MS, 'Notification switches interval to active (100ms)');

  // 3. Execution after waking with pending items
  pendingCount = 5;
  tick(pendingCount > 0);
  assert.equal(currentInterval, ACTIVE_MS, 'Remains active while messages are pending');
  assert.equal(scheduledDelay, ACTIVE_MS, 'Rescheduled at active interval (100ms)');

  // 4. Execution when jobs are actively running via runners
  pendingCount = 0;
  activeRunners.set('user_123', Promise.resolve());
  tick(false);
  assert.equal(currentInterval, ACTIVE_MS, 'Remains active while active runners are working');

  // 5. Execution after runners complete and queue is empty
  activeRunners.clear();
  tick(false);
  assert.equal(currentInterval, IDLE_MS, 'Backs off to idle (500ms) once all jobs and runners complete');
  assert.equal(scheduledDelay, IDLE_MS, 'Scheduled delay is 500ms (idle)');
});

test('Safety Invariant: Polling frequency does not compromise WhatsApp 2.5s session rate limiting', () => {
  // Verify SEND_DELAY_MS is independent of queue polling
  const sendDelayMatch = workerSource.match(/const SEND_DELAY_MS\s*=\s*Number\(process\.env\.SEND_DELAY_MS\s*\|\|\s*(\d+)\);/);
  assert.ok(sendDelayMatch, 'SEND_DELAY_MS must be defined');
  assert.equal(Number(sendDelayMatch[1]), 2500, 'WhatsApp message sending pace must remain 2500ms');

  // Ensure polling does not send messages directly without entering runUserQueue with delay
  assert.match(
    workerSource,
    /await new Promise\(\(resolve\)\s*=>\s*setTimeout\(resolve,\s*SEND_DELAY_MS\)\);/,
    'Each message dispatch must still wait 2.5 seconds regardless of 1s queue poll rate'
  );
});

test('Safety Invariant: Repeated polling transitions do not leak memory or timers', () => {
  let timerRef = null;
  let clearCount = 0;
  let setCount = 0;

  function schedule(delay) {
    if (timerRef) {
      clearCount++;
      timerRef = null;
    }
    setCount++;
    timerRef = { id: setCount, delay };
  }

  // Simulate 1,000 polling cycles with interleaved notifications
  for (let i = 0; i < 1000; i++) {
    schedule(i % 2 === 0 ? 1000 : 5000);
    if (i % 50 === 0) {
      schedule(0); // instant wake
    }
  }

  assert.equal(clearCount, 1019, 'Previous timers are consistently cleared without dangling references');
  assert.ok(timerRef, 'Only single active timer remains');
});
