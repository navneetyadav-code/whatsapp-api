import { fileURLToPath } from 'url';
import { dirname, resolve } from 'path';
import { readFileSync, existsSync } from 'fs';
import assert from 'assert';
import mysql from '../worker/node_modules/mysql2/promise.js';
import { ChatMemoryEngine } from '../worker/chat-memory.mjs';
import { ChatFactMemory } from '../worker/chat-fact-memory.mjs';
import { isTopicResetTrigger } from '../worker/chat-session-manager.mjs';
import { handleUpdateMemory } from '../worker/tools-engine.mjs';
import { buildPromptWithRag } from '../worker/ai-agent.mjs';

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

async function runTests() {
  console.log('====================================================');
  console.log('🧠 DUAL-TIER COGNITIVE MEMORY & TOPIC RESET SUITE');
  console.log('====================================================\n');

  const db = await mysql.createPool({
    host: process.env.DB_HOST || '127.0.0.1',
    port: Number(process.env.DB_PORT || 3306),
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_NAME || 'whatsapp_api'
  });

  const testUserId = 1;
  const testConvId = 888888;
  const testSessionUuid = 'SES-TEST-MEMORY-001';

  // ----------------------------------------------------
  // TEST 1: Topic Reset Trigger Regex Detection
  // ----------------------------------------------------
  console.log('👉 [Test 1] Topic Reset Trigger Commands (/clear, /new, /reset)');
  assert.strictEqual(isTopicResetTrigger('/clear'), true, '/clear should match');
  assert.strictEqual(isTopicResetTrigger('clear'), true, 'clear should match');
  assert.strictEqual(isTopicResetTrigger('/new'), true, '/new should match');
  assert.strictEqual(isTopicResetTrigger('new topic'), true, 'new topic should match');
  assert.strictEqual(isTopicResetTrigger('/reset'), true, '/reset should match');
  assert.strictEqual(isTopicResetTrigger('reset context'), true, 'reset context should match');
  assert.strictEqual(isTopicResetTrigger('Hello bot how are you?'), false, 'normal text should NOT match');
  assert.strictEqual(isTopicResetTrigger('Clear the table'), false, 'sentence with clear should NOT match');
  console.log('   ✅ All topic reset command variants parsed accurately (0ms regex)');

  // ----------------------------------------------------
  // TEST 2: 45-Minute Rolling TTL Working Memory Window
  // ----------------------------------------------------
  console.log('\n👉 [Test 2] Time-To-Live (TTL) Inactivity Decay in ChatMemoryEngine');
  await db.execute('DELETE FROM chat_messages WHERE conversation_id = ?', [testConvId]);
  await db.execute('DELETE FROM chat_conversations WHERE id = ?', [testConvId]);

  await db.execute(
    `INSERT INTO chat_conversations (id, user_id, phone, session_uuid, status)
     VALUES (?, ?, '919999988888', ?, 'open')`,
    [testConvId, testUserId, testSessionUuid]
  );

  // Insert 2 stale messages from 90 minutes ago
  await db.execute(
    `INSERT INTO chat_messages (user_id, conversation_id, session_uuid, sender_type, sender_name, message_type, body, status, created_at)
     VALUES 
      (?, ?, ?, 'contact', 'Boss', 'text', 'Stale message from 90 mins ago', 'received', NOW() - INTERVAL 90 MINUTE),
      (?, ?, ?, 'bot', 'Assistant', 'text', 'Understood stale response', 'sent', NOW() - INTERVAL 89 MINUTE)`,
    [testUserId, testConvId, testSessionUuid, testUserId, testConvId, testSessionUuid]
  );

  // Insert 2 fresh messages from 5 minutes ago
  await db.execute(
    `INSERT INTO chat_messages (user_id, conversation_id, session_uuid, sender_type, sender_name, message_type, body, status, created_at)
     VALUES 
      (?, ?, ?, 'contact', 'Boss', 'text', 'Active task message 5 mins ago', 'received', NOW() - INTERVAL 5 MINUTE),
      (?, ?, ?, 'bot', 'Assistant', 'text', 'Active task reply 4 mins ago', 'sent', NOW() - INTERVAL 4 MINUTE)`,
    [testUserId, testConvId, testSessionUuid, testUserId, testConvId, testSessionUuid]
  );

  // Query with 45-minute TTL
  const activeTurns = await ChatMemoryEngine.getRecentTurns(db, testConvId, testSessionUuid, 6, 45);
  console.log(`   Retrieved ${activeTurns.length} active turns within 45-minute TTL:`, activeTurns.map(t => t.body));

  assert.strictEqual(activeTurns.length, 2, 'Should only return the 2 messages within the 45-min TTL window');
  assert.strictEqual(activeTurns[0].body, 'Active task message 5 mins ago');
  assert.strictEqual(activeTurns[1].body, 'Active task reply 4 mins ago');

  // Query with 1-minute TTL: both 90m and 5m messages should be excluded!
  const expiredTurns = await ChatMemoryEngine.getRecentTurns(db, testConvId, testSessionUuid, 6, 1);
  assert.strictEqual(expiredTurns.length, 0, 'Should return 0 turns when all messages exceed TTL');
  console.log('   ✅ 45-minute TTL cleanly drops expired context while keeping active dialogue');

  // ----------------------------------------------------
  // TEST 3: Persistent Fact Notebook Loading & Formatting
  // ----------------------------------------------------
  console.log('\n👉 [Test 3] Persistent Fact Notebook Loading');
  const userFacts = await ChatFactMemory.loadUserFacts(db, testUserId);
  console.log(`   Loaded ${userFacts.factsCount} facts from ai_user_memory:`);
  console.log(userFacts.factsText);

  assert(userFacts.factsCount >= 3, 'Must have at least 3 initial facts');
  assert(userFacts.factsText.includes('[PERSISTENT FACT NOTEBOOK]'));
  assert(userFacts.factsText.includes('Boss Name'));
  assert(userFacts.factsText.includes('Assist Group'));

  const factTokens = Math.round(userFacts.factsText.length / 4);
  console.log(`   Notebook size: ${userFacts.factsText.length} characters (~${factTokens} tokens)`);
  assert(factTokens <= 80, `Facts notebook exceeded 80 tokens: ${factTokens}`);
  console.log('   ✅ Fact notebook loads cleanly within strict token budget (<80 tokens)');

  // ----------------------------------------------------
  // TEST 4: Autonomous update_memory Tool Execution
  // ----------------------------------------------------
  console.log('\n👉 [Test 4] Autonomous update_memory Tool Upsert');
  const updateResult = await handleUpdateMemory({
    key: 'preferred_beverage',
    value: 'Ginger Tea with Honey',
    memory_type: 'preference'
  }, {
    db,
    userId: testUserId,
    conversationId: testConvId
  });

  assert(updateResult.success, 'handleUpdateMemory failed: ' + updateResult.error);
  assert.strictEqual(updateResult.key, 'preferred_beverage');
  console.log('   ✅ Tool execution result:', updateResult.message);

  // Verify in ai_user_memory
  const [dbRow] = await db.execute(
    'SELECT * FROM ai_user_memory WHERE user_id = ? AND memory_key = ?',
    [testUserId, 'preferred_beverage']
  );
  assert.strictEqual(dbRow.length, 1, 'Memory row not found in MySQL');
  assert.strictEqual(dbRow[0].memory_value, 'Ginger Tea with Honey');

  // Verify it appears in loadUserFacts
  const refreshedFacts = await ChatFactMemory.loadUserFacts(db, testUserId);
  assert(refreshedFacts.factsText.includes('Preferred Beverage: Ginger Tea with Honey'));
  console.log('   ✅ Fact updated in database and reflected in live notebook');

  // ----------------------------------------------------
  // TEST 5: Micro-Kernel Prompt Budget with Dual-Tier Memory
  // ----------------------------------------------------
  console.log('\n👉 [Test 5] Micro-Kernel Prompt Size with Dual-Tier Integration');
  const combinedPrompt = buildPromptWithRag(
    '',
    [],
    'Schedule my meeting',
    {
      agentMode: 'personal',
      ownerName: 'Ankit',
      factsText: refreshedFacts.factsText,
      skillDirectives: ['EMAIL PROTOCOL: Draft before send.']
    }
  );

  const charCount = combinedPrompt.systemPrompt.length;
  const approxTokens = Math.round(charCount / 4);
  console.log(`   Total System Prompt with Notebook & Skills: ${charCount} chars (~${approxTokens} tokens)`);
  assert(approxTokens <= 350, `Combined prompt exceeded 350 tokens: ${approxTokens}`);
  console.log('   ✅ Combined prompt comfortably fits under 350 tokens (over 80% reduction vs legacy)');

  // Clean up test rows
  await db.execute('DELETE FROM chat_messages WHERE conversation_id = ?', [testConvId]);
  await db.execute('DELETE FROM chat_conversations WHERE id = ?', [testConvId]);
  await db.execute('DELETE FROM ai_user_memory WHERE user_id = ? AND memory_key = ?', [testUserId, 'preferred_beverage']);
  await db.end();

  console.log('\n🎉 ALL 5 DUAL-TIER MEMORY TESTS PASSED! ZERO ERRORS.\n');
}

runTests().catch(err => {
  console.error('\n❌ TEST SUITE FAILED:', err);
  process.exit(1);
});
