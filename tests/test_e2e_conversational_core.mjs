import mysql from '../worker/node_modules/mysql2/promise.js';
import { ChatDebouncer } from '../worker/chat-debouncer.mjs';
import { ChatGreetingCache } from '../worker/chat-greeting-cache.mjs';
import { generateSessionUuid, resolveActiveSession, closeSession } from '../worker/chat-session-manager.mjs';
import { ChatPresenceManager } from '../worker/chat-presence.mjs';
import { ChatMemoryEngine } from '../worker/chat-memory.mjs';
import { ChatResolutionEngine } from '../worker/chat-resolution.mjs';
import { readFileSync, existsSync } from 'fs';
import { resolve, dirname } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const envPath = resolve(__dirname, '..', '.env');
if (existsSync(envPath)) {
  for (const line of readFileSync(envPath, 'utf8').split(/\r?\n/)) {
    const trimmed = line.trim();
    if (!trimmed || trimmed.startsWith('#') || !trimmed.includes('=')) continue;
    const [k, ...v] = trimmed.split('=');
    process.env[k.trim()] = v.join('=').trim().replace(/^['"]|['"]$/g, '');
  }
}

async function runEndToEndVerification() {
  console.log('===============================================================');
  console.log('  CORE CONVERSATIONAL ENGINE: END-TO-END VERIFICATION SUITE    ');
  console.log('===============================================================\n');

  let passed = 0;
  let total = 6;

  // -------------------------------------------------------------------------
  // 1. Debounce Fragment Consolidator Verification
  // -------------------------------------------------------------------------
  console.log('--- [Test 1/6] Debounce Fragment Consolidator ---');
  let consolidatedResult = null;
  const debouncer = new ChatDebouncer({ debounceMs: 250, maxWaitMs: 1000 });

  const userId = 'user1';
  const remoteJid = '919876500000@s.whatsapp.net';

  debouncer.enqueue(userId, remoteJid, { text: 'Hello' }, (payload) => {
    consolidatedResult = payload;
  });
  debouncer.enqueue(userId, remoteJid, { text: 'when will my order come' }, (payload) => {
    consolidatedResult = payload;
  });
  debouncer.enqueue(userId, remoteJid, { text: '?' }, (payload) => {
    consolidatedResult = payload;
  });

  await new Promise(r => setTimeout(r, 450));

  if (
    consolidatedResult &&
    consolidatedResult.consolidatedText.includes('Hello') &&
    consolidatedResult.consolidatedText.includes('when will my order come ?')
  ) {
    console.log(`✔ Consolidated fragmented messages into:\n"${consolidatedResult.consolidatedText}"`);
    console.log(`✔ Messages merged cleanly: ${consolidatedResult.itemCount}`);
    passed++;
  } else {
    console.error('❌ Debounce test failed. Result:', consolidatedResult);
    process.exit(1);
  }

  // -------------------------------------------------------------------------
  // 2. Token Burn Optimizer (Repeat Greeting Filter)
  // -------------------------------------------------------------------------
  console.log('\n--- [Test 2/6] Token Burn Optimizer (Greeting Filter) ---');
  const greetingCache = new ChatGreetingCache({ cooldownMs: 3000 });
  const firstCheck = greetingCache.evaluate('user1', '919876500000', 'hii');
  const secondCheck = greetingCache.evaluate('user1', '919876500000', 'hello');
  const thirdCheck = greetingCache.evaluate('user1', '919876500000', 'hey there');
  const nonGreeting = greetingCache.evaluate('user1', '919876500000', 'what is the price?');

  if (
    firstCheck.shouldIntercept === false &&
    secondCheck.shouldIntercept === true &&
    thirdCheck.shouldIntercept === true &&
    nonGreeting.shouldIntercept === false
  ) {
    console.log('✔ First greeting allowed through for initial engagement');
    console.log(`✔ Second repeat greeting intercepted locally with response: "${secondCheck.replyText}"`);
    console.log('✔ Subsequent substantive query correctly bypasses greeting filter');
    passed++;
  } else {
    console.error('❌ Greeting filter test failed:', { firstCheck, secondCheck, thirdCheck, nonGreeting });
    process.exit(1);
  }

  // -------------------------------------------------------------------------
  // 3. Database Lifecycle & Session Ticket Management
  // -------------------------------------------------------------------------
  console.log('\n--- [Test 3/6] Session Ticket Lifecycle & Fresh Ticket On Re-Open ---');
  const db = await mysql.createConnection({
    host: process.env.DB_HOST || '127.0.0.1',
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_NAME || 'whatsapp_api',
    port: parseInt(process.env.DB_PORT || '3306', 10),
  });

  const testPhone = '919000011111';
  const testUserId = 1;

  // Cleanup old test data
  await db.query('DELETE FROM chat_messages WHERE conversation_id IN (SELECT id FROM chat_conversations WHERE user_id = ? AND phone = ?)', [testUserId, testPhone]);
  await db.query('DELETE FROM chat_conversations WHERE user_id = ? AND phone = ?', [testUserId, testPhone]);

  // Session 1: Creation & Resolution
  const session1 = await resolveActiveSession(db, testUserId, testPhone, 'Test Contact');
  const session1Uuid = session1.sessionUuid;
  console.log(`✔ Session 1 Created: ${session1Uuid}`);

  const [convRows1] = await db.query('SELECT * FROM chat_conversations WHERE user_id = ? AND phone = ?', [testUserId, testPhone]);
  const conv1 = convRows1[0];

  // Close Session 1
  const resolutionRemark = 'Customer verified delivery tracking successfully.';
  await closeSession(db, conv1.id, resolutionRemark, true);

  const [convRowsClosed] = await db.query('SELECT * FROM chat_conversations WHERE id = ?', [conv1.id]);
  const convClosed = convRowsClosed[0];

  // Session 2: Fresh inquiry after closure
  const session2 = await resolveActiveSession(db, testUserId, testPhone, 'Test Contact');
  const session2Uuid = session2.sessionUuid;
  console.log(`✔ Session 2 Created after closure: ${session2Uuid}`);

  if (
    convClosed.status === 'closed' &&
    convClosed.is_auto_closed === 1 &&
    convClosed.resolution_remark === resolutionRemark &&
    session1Uuid !== session2Uuid &&
    session2Uuid.startsWith('SES-')
  ) {
    console.log('✔ Session 1 archived with resolution remark and is_auto_closed=1');
    console.log('✔ Subsequent customer message automatically spun up brand new ticket session ID');
    passed++;
  } else {
    console.error('❌ Session lifecycle test failed:', { convClosed, session1Uuid, session2Uuid });
    process.exit(1);
  }

  // -------------------------------------------------------------------------
  // 4. Smart Auto-Resolution & Closure Trigger Detection
  // -------------------------------------------------------------------------
  console.log('\n--- [Test 4/6] Smart Auto-Resolution & Closure Triggers ---');
  const samplePriorTurns = [{ senderType: 'bot', body: 'Your order will be delivered tomorrow by 2 PM.' }];
  const resolution1 = ChatResolutionEngine.detectResolution('thank you so much for your help', samplePriorTurns);
  const resolution2 = ChatResolutionEngine.detectResolution('dhanyawad mera kaam ho gaya', samplePriorTurns);
  const resolution3 = ChatResolutionEngine.detectResolution('thanks, problem solved', samplePriorTurns);
  const notResolution = ChatResolutionEngine.detectResolution('thanks but this is still not working', samplePriorTurns);
  const survey = ChatResolutionEngine.getClosureMessage();

  if (
    resolution1.isResolved &&
    resolution2.isResolved &&
    resolution3.isResolved &&
    !notResolution.isResolved &&
    survey.includes('How would you rate your support experience')
  ) {
    console.log('✔ Multilingual resolution triggers detected (English, Hindi, Hinglish)');
    console.log(`✔ Generated Resolution Remark: "${resolution1.remark}"`);
    console.log('✔ Negative/pending issue ("thanks but still not working") correctly identified as unresolved');
    console.log('✔ 5-star closure survey format generated cleanly');
    passed++;
  } else {
    console.error('❌ Auto-resolution engine test failed:', { resolution1, resolution2, resolution3, notResolution, survey });
    process.exit(1);
  }

  // -------------------------------------------------------------------------
  // 5. Multi-Turn Conversational Memory Engine
  // -------------------------------------------------------------------------
  console.log('\n--- [Test 5/6] Multi-Turn Conversational Memory Window ---');
  const sampleHistory = [
    { senderType: 'contact', role: 'user', openAiRole: 'user', body: 'Which services do you provide?' },
    { senderType: 'bot', role: 'model', openAiRole: 'assistant', body: 'We offer 1. Website Design, 2. SEO Auditing, and 3. WhatsApp API Integration.' }
  ];

  const systemPrompt = 'You are a helpful assistant.';
  const currentFollowUp = 'Can you tell me more about the second one?';
  const geminiContents = ChatMemoryEngine.buildGeminiContents(systemPrompt, sampleHistory, currentFollowUp);
  const openAiMessages = ChatMemoryEngine.buildOpenAiMessages(systemPrompt, sampleHistory, currentFollowUp);
  const transcript = ChatMemoryEngine.formatTranscript(sampleHistory);

  if (
    geminiContents.length === 3 &&
    geminiContents[0].role === 'user' &&
    geminiContents[1].role === 'model' &&
    geminiContents[2].role === 'user' &&
    openAiMessages.length === 4 &&
    transcript.includes('Customer: Which services do you provide?')
  ) {
    console.log('✔ Gemini multi-turn role alternating contents formatted properly');
    console.log('✔ OpenAI messages array prepared with system prompt + dialog turns');
    console.log('✔ Chronological transcript rendered for prompt injection');
    passed++;
  } else {
    console.error('❌ Memory engine test failed:', { geminiContents, openAiMessages });
    process.exit(1);
  }

  // -------------------------------------------------------------------------
  // 6. Presence Typing Simulation
  // -------------------------------------------------------------------------
  console.log('\n--- [Test 6/6] WhatsApp Presence Typing Simulation ---');
  let presenceCalls = [];
  const mockSock = {
    sendPresenceUpdate: async (type, jid) => presenceCalls.push({ action: 'update', type, jid }),
  };

  const testJid = '919876543210@s.whatsapp.net';
  const stopTyping = await ChatPresenceManager.startTyping(mockSock, testJid);
  await stopTyping();

  const hasComposing = presenceCalls.some(c => c.action === 'update' && c.type === 'composing');
  const hasPaused = presenceCalls.some(c => c.action === 'update' && c.type === 'paused');

  if (hasComposing && hasPaused) {
    console.log('✔ Simulated WhatsApp presence "composing" (typing...) and "paused" cleanly');
    passed++;
  } else {
    console.error('❌ Presence manager test failed:', presenceCalls);
    process.exit(1);
  }

  // Cleanup test DB rows
  await db.query('DELETE FROM chat_messages WHERE conversation_id IN (SELECT id FROM chat_conversations WHERE user_id = ? AND phone = ?)', [testUserId, testPhone]);
  await db.query('DELETE FROM chat_conversations WHERE user_id = ? AND phone = ?', [testUserId, testPhone]);
  await db.end();

  console.log('\n===============================================================');
  console.log(`  SUMMARY: ${passed}/${total} CONVERSATIONAL ENGINE TESTS PASSED (100%)  `);
  console.log('===============================================================\n');
}

runEndToEndVerification().catch(err => {
  console.error('Fatal Test Exception:', err);
  process.exit(1);
});
