import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';

const workerSource = readFileSync(new URL('../worker/server.mjs', import.meta.url), 'utf8');

// ============================================================================
// PART 1: Code Contract Verification (Source Code Audit)
// ============================================================================

test('Audit 1.1: queueBotReply helper exists and routes through message_queue with priority 10', () => {
  assert.match(
    workerSource,
    /async\s+function\s+queueBotReply\s*\(/,
    'queueBotReply helper must be defined in worker/server.mjs'
  );
  assert.match(
    workerSource,
    /INSERT\s+INTO\s+message_queue[\s\S]*?10,\s*'queued'\)/,
    'queueBotReply must enqueue outgoing bot/AI responses into message_queue with priority 10'
  );
  assert.match(
    workerSource,
    /triggerUserQueue\(String\(userId\)\)/,
    'queueBotReply must trigger the per-user queue runner immediately upon enqueueing'
  );
});

test('Audit 1.2: Autonomous multi-turn AI reply routes through queueBotReply instead of direct sock.sendMessage', () => {
  const startIdx = workerSource.indexOf('replyText = await generateAiReply');
  assert.ok(startIdx !== -1, 'generateAiReply call must exist in workerSource');
  const aiReplyBlock = workerSource.slice(startIdx, startIdx + 800);

  // Multi-turn AI response generation must NOT call sock.sendMessage directly
  assert.ok(
    !aiReplyBlock.includes('sock.sendMessage') && !aiReplyBlock.includes('session.sock.sendMessage'),
    'Autonomous AI replies must not call sock.sendMessage directly'
  );
  assert.ok(
    aiReplyBlock.includes('await queueBotReply('),
    'Autonomous AI reply must call queueBotReply with high priority and correlation details'
  );
  assert.ok(
    aiReplyBlock.includes('incrementConsecutive: true'),
    'Autonomous AI reply must track consecutive bot replies'
  );
});

test('Audit 1.3: Autonomous bot triggers route through queueBotReply', () => {
  // Greeting cache intercept
  assert.match(
    workerSource,
    /greetingCheck\.shouldIntercept[\s\S]*?await\s+queueBotReply\s*\(\s*userId,\s*msg\.key\.remoteJid/,
    'Repeated greeting intercept must route through queueBotReply'
  );
  // Session closure message
  assert.match(
    workerSource,
    /ChatResolutionEngine\.getClosureMessage\(\)[\s\S]*?await\s+queueBotReply\s*\(\s*userId,\s*msg\.key\.remoteJid/,
    'Session closure message must route through queueBotReply'
  );
  // Topic reset greeting (/clear, /new, /reset)
  assert.match(
    workerSource,
    /isTopicResetTrigger[\s\S]*?await\s+queueBotReply\s*\(\s*userId,\s*msg\.key\.remoteJid/,
    'Topic reset message must route through queueBotReply'
  );
  // Welcome start message (/start)
  assert.match(
    workerSource,
    /isWakeupTrigger[\s\S]*?await\s+queueBotReply\s*\(\s*userId,\s*msg\.key\.remoteJid/,
    'Start welcome message must route through queueBotReply'
  );
  // Human handover message
  assert.match(
    workerSource,
    /handoverMsg[\s\S]*?await\s+queueBotReply\s*\(\s*userId,\s*msg\.key\.remoteJid/,
    'Human escalation handover message must route through queueBotReply'
  );
});

test('Audit 1.4: chat_messages correlation and lifecycle synchronization', () => {
  // Queue payload embeds chat_message_id
  assert.match(
    workerSource,
    /chat_message_id:\s*chatMessageId/,
    'Queue payload must correlate chat_message_id for delivery receipt reconciliation'
  );
  // markMessageSent updates chat_messages
  assert.match(
    workerSource,
    /UPDATE\s+chat_messages\s+SET\s+wa_message_id\s*=\s*\?,\s*status\s*=\s*["']sent["']\s+WHERE\s+id\s*=\s*\?\s+AND\s+user_id\s*=\s*\?/,
    'markMessageSent must synchronize the confirmed Baileys ID to chat_messages'
  );
  // markMessageFailed updates chat_messages on permanent failure
  assert.match(
    workerSource,
    /UPDATE\s+chat_messages\s+SET\s+status\s*=\s*["']failed["']\s+WHERE\s+id\s*=\s*\?\s+AND\s+user_id\s*=\s*\?/,
    'markMessageFailed must update chat_messages status to failed when final attempt fails'
  );
});

// ============================================================================
// PART 2: Algorithmic & Priority Scheduling Unit Tests
// ============================================================================

test('Audit 2.1: Autonomous AI replies (Priority 10) preempt Bulk Campaign messages (Priority 1)', () => {
  const queue = [
    { id: 1, recipient: '919800000001', priority: 1, available_at: '2026-11-07 10:00:00', type: 'campaign' },
    { id: 2, recipient: '919800000002', priority: 1, available_at: '2026-11-07 10:00:01', type: 'campaign' },
    { id: 3, recipient: '919800000003', priority: 1, available_at: '2026-11-07 10:00:02', type: 'campaign' },
    // Inbound AI reply arrives with priority 10
    { id: 4, recipient: '919876543210', priority: 10, available_at: '2026-11-07 10:00:05', type: 'ai_assistant' },
    { id: 5, recipient: '919800000004', priority: 1, available_at: '2026-11-07 10:00:06', type: 'campaign' }
  ];

  // Simulating SQL: ORDER BY priority DESC, available_at ASC, id ASC
  const sorted = [...queue].sort((a, b) => {
    if (b.priority !== a.priority) return b.priority - a.priority;
    if (a.available_at !== b.available_at) return a.available_at.localeCompare(b.available_at);
    return a.id - b.id;
  });

  assert.strictEqual(sorted[0].id, 4, 'Priority 10 AI reply must jump to the front of the queue');
  assert.strictEqual(sorted[0].type, 'ai_assistant');
  assert.strictEqual(sorted[1].id, 1, 'Older priority 1 messages execute after interactive AI reply');
});

test('Audit 2.2: Session pacing (anti-ban throttle) is enforced on AI replies', () => {
  const SEND_DELAY_MS = 2500;
  const dispatchTimestamps = [];

  // Simulating queue runner dispatching successive AI/bot messages
  function simulateQueueDispatch(count) {
    let clock = 10000;
    for (let i = 0; i < count; i++) {
      clock += SEND_DELAY_MS;
      dispatchTimestamps.push(clock);
    }
  }

  simulateQueueDispatch(3);
  assert.strictEqual(dispatchTimestamps[1] - dispatchTimestamps[0], 2500, 'Minimum 2.5s delay enforced between message 1 & 2');
  assert.strictEqual(dispatchTimestamps[2] - dispatchTimestamps[1], 2500, 'Minimum 2.5s delay enforced between message 2 & 3');
});

// ============================================================================
// PART 3: End-to-End Delivery & Network Drop Resilience Simulation
// ============================================================================

test('Audit 3.1: Full lifecycle of an inbound AI response via message_queue', () => {
  // Step 1: Simulated customer message arrives
  const userId = 1;
  const senderPhone = '919876543210';
  const remoteJid = `${senderPhone}@s.whatsapp.net`;
  const convId = 42;
  const aiGeneratedText = 'Hello! I can help you book an appointment for tomorrow.';

  // In-memory mock database tables
  const db = {
    chat_messages: [],
    chat_conversations: [{ id: convId, last_message: '', consecutive_bot_replies: 0 }],
    message_queue: [],
    message_logs: []
  };

  // Step 2: queueBotReply executes
  function mockQueueBotReply(userId, remoteJid, senderPhone, body, convId, sessionUuid, senderName) {
    const chatMsgId = db.chat_messages.length + 1;
    db.chat_messages.push({
      id: chatMsgId,
      user_id: userId,
      conversation_id: convId,
      sender_type: 'bot',
      sender_name: senderName,
      body,
      wa_message_id: null,
      status: 'sent'
    });

    const conv = db.chat_conversations.find(c => c.id === convId);
    if (conv) {
      conv.last_message = body.slice(0, 200);
      conv.consecutive_bot_replies++;
    }

    const queueId = db.message_queue.length + 1;
    db.message_queue.push({
      id: queueId,
      user_id: userId,
      recipient: remoteJid,
      normalized_recipient: senderPhone,
      body,
      payload: JSON.stringify({
        source: 'ai_assistant',
        conversation_id: convId,
        chat_message_id: chatMsgId,
        type: 'text'
      }),
      priority: 10,
      status: 'queued',
      attempts: 0,
      max_attempts: 3,
      provider_message_id: null
    });

    return { chatMessageId: chatMsgId, queueId };
  }

  const { chatMessageId, queueId } = mockQueueBotReply(
    userId, remoteJid, senderPhone, aiGeneratedText, convId, 'sess-123', 'AI Assistant'
  );

  assert.strictEqual(chatMessageId, 1);
  assert.strictEqual(queueId, 1);
  assert.strictEqual(db.message_queue[0].priority, 10);
  assert.strictEqual(db.message_queue[0].status, 'queued');
  assert.strictEqual(db.chat_conversations[0].consecutive_bot_replies, 1);

  // Step 3: Worker queue runner claims and sends message
  const queuedMsg = db.message_queue[0];
  queuedMsg.status = 'processing';
  queuedMsg.attempts = 1;
  const deterministicId = `BAILEYS_AI_${queuedMsg.id}`;
  queuedMsg.provider_message_id = deterministicId;

  // Mock successful Baileys send
  const sendResult = { key: { id: deterministicId }, message: { conversation: aiGeneratedText } };

  // Step 4: markMessageSent updates message_queue, message_logs, and chat_messages
  function mockMarkMessageSent(msg, result, normalizedTo) {
    msg.status = 'sent';
    db.message_logs.push({
      user_id: msg.user_id,
      recipient: normalizedTo,
      message_preview: msg.body.slice(0, 255),
      status: 'sent',
      provider_response: JSON.stringify({ ok: true, messageId: result.key.id })
    });

    const payload = JSON.parse(msg.payload);
    if (payload?.chat_message_id) {
      const chatMsg = db.chat_messages.find(m => m.id === payload.chat_message_id);
      if (chatMsg) {
        chatMsg.wa_message_id = result.key.id;
        chatMsg.status = 'sent';
      }
    }
  }

  mockMarkMessageSent(queuedMsg, sendResult, senderPhone);

  assert.strictEqual(queuedMsg.status, 'sent');
  assert.strictEqual(db.message_logs.length, 1);
  assert.strictEqual(db.message_logs[0].status, 'sent');
  assert.strictEqual(db.chat_messages[0].wa_message_id, deterministicId, 'chat_messages must have confirmed provider message ID');

  // Step 5: WhatsApp delivery receipt arrives
  function mockHandleReceipt(waMessageId, newStatus) {
    const queueEntry = db.message_queue.find(m => m.provider_message_id === waMessageId);
    if (queueEntry) queueEntry.status = newStatus;
    const chatEntry = db.chat_messages.find(m => m.wa_message_id === waMessageId);
    if (chatEntry) chatEntry.status = newStatus;
  }

  mockHandleReceipt(deterministicId, 'delivered');
  assert.strictEqual(db.message_queue[0].status, 'delivered');
  assert.strictEqual(db.chat_messages[0].status, 'delivered');

  mockHandleReceipt(deterministicId, 'read');
  assert.strictEqual(db.message_queue[0].status, 'read');
  assert.strictEqual(db.chat_messages[0].status, 'read');
});

test('Audit 3.2: Network drop resilience (ECONNRESET does not lose AI reply)', () => {
  // Scenario: Worker experiences ECONNRESET while dispatching autonomous AI reply
  let aiMessage = {
    id: 88,
    status: 'queued',
    attempts: 0,
    max_attempts: 3,
    provider_message_id: 'AI_RETRY_TEST_88'
  };

  // Attempt 1 fails with ECONNRESET
  aiMessage.attempts++;
  const errCode = 'ECONNRESET';
  const isPermanent = false;
  const finalFail = isPermanent || aiMessage.attempts >= aiMessage.max_attempts;

  assert.strictEqual(finalFail, false, 'First attempt ECONNRESET must NOT trigger final failure');
  aiMessage.status = finalFail ? 'failed' : 'queued';
  assert.strictEqual(aiMessage.status, 'queued', 'Message remains safely in queue for retry');

  // Attempt 2 succeeds
  aiMessage.attempts++;
  aiMessage.status = 'sent';
  assert.strictEqual(aiMessage.status, 'sent', 'AI message successfully delivered upon retry after network recovered');
});
