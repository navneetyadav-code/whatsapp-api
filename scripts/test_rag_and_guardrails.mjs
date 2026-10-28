import assert from 'assert';
import { detectFrustrationAndEscalation, buildPromptWithRag, retrieveRelevantKnowledge, generateAiReply } from '../worker/ai-agent.mjs';
import { ChatResolutionEngine } from '../worker/chat-resolution.mjs';
import mysql from '../worker/node_modules/mysql2/promise.js';
import fs from 'fs';
import path from 'path';

async function runTests() {
  console.log('=====================================================');
  console.log(' RUNNING RAG, ESCALATION & AUTO-RESOLUTION TEST SUITE');
  console.log('=====================================================');

  // TEST 1: Frustration & Escalation Detection
  console.log('\n[TEST 1] Testing Frustration & Escalation Detection...');
  const t1_1 = detectFrustrationAndEscalation('Can you tranfer the call to real human agent');
  assert.strictEqual(t1_1.shouldEscalate, true, 'Typo tranfer + real human agent should escalate');
  console.log('  ✔ "Can you tranfer the call to real human agent" -> Escalate:', t1_1.shouldEscalate, `(score: ${t1_1.score})`);

  const t1_2 = detectFrustrationAndEscalation('Bakwas bot useless faltu service');
  assert.strictEqual(t1_2.shouldEscalate, true, 'Anger keywords should escalate');
  console.log('  ✔ "Bakwas bot useless faltu service" -> Escalate:', t1_2.shouldEscalate, `(score: ${t1_2.score})`);

  const t1_3 = detectFrustrationAndEscalation('What are the class 11th batch timings?');
  assert.strictEqual(t1_3.shouldEscalate, false, 'Normal inquiry should not escalate');
  console.log('  ✔ "What are the class 11th batch timings?" -> Escalate:', t1_3.shouldEscalate);

  // TEST 2: Auto-Resolution & Explicit Close Commands
  console.log('\n[TEST 2] Testing Auto-Resolution & Explicit Close Commands...');
  const fakeHistory = [{ senderType: 'bot', body: 'Here are the details for Class 11th.' }];

  const t2_1 = ChatResolutionEngine.detectResolution('Close the chat', fakeHistory);
  assert.strictEqual(t2_1.isResolved, true, '"Close the chat" should trigger resolution');
  console.log('  ✔ "Close the chat" -> Resolved:', t2_1.isResolved, `(remark: ${t2_1.remark})`);

  const t2_2 = ChatResolutionEngine.detectResolution('Yes good bye', fakeHistory);
  assert.strictEqual(t2_2.isResolved, true, '"Yes good bye" should trigger resolution');
  console.log('  ✔ "Yes good bye" -> Resolved:', t2_2.isResolved);

  const t2_3 = ChatResolutionEngine.detectResolution('Samajh gaya bhai dhanyawad', fakeHistory);
  assert.strictEqual(t2_3.isResolved, true, '"Samajh gaya bhai dhanyawad" should trigger resolution');
  console.log('  ✔ "Samajh gaya bhai dhanyawad" -> Resolved:', t2_3.isResolved);

  // TEST 3: RAG Retrieval from MySQL
  console.log('\n[TEST 3] Testing RAG Knowledge Retrieval from MySQL...');
  const env = fs.readFileSync('.env', 'utf8');
  const dbUser = env.match(/DB_USER=(.*)/)?.[1]?.trim() || 'root';
  const dbPass = env.match(/DB_PASSWORD=(.*)/)?.[1]?.trim() || '';
  const dbName = env.match(/DB_NAME=(.*)/)?.[1]?.trim() || 'whatsapp_api';
  const dbHost = env.match(/DB_HOST=(.*)/)?.[1]?.trim() || '127.0.0.1';

  const db = await mysql.createConnection({ host: dbHost, user: dbUser, password: dbPass, database: dbName });

  const retrievedFee = await retrieveRelevantKnowledge(db, 1, 'What is the fee for Class 11th Science?', 3, 0.12);
  assert.ok(retrievedFee.length > 0, 'Should find fee knowledge chunks');
  console.log(`  ✔ Retrieved ${retrievedFee.length} chunks for fee query:`, retrievedFee.map(c => c.title));

  const retrievedOutOfScope = await retrieveRelevantKnowledge(db, 1, 'how to make maggie noodles recipe', 3, 0.12);
  assert.strictEqual(retrievedOutOfScope.length, 0, 'Should not match out-of-scope cooking query');
  console.log(`  ✔ Out-of-scope query returned 0 chunks as expected.`);

  // TEST 4: Live Model Generation with RAG & Strict Closed-Domain
  console.log('\n[TEST 4] Testing Live Model Generation with RAG Grounding...');
  const [settingsRows] = await db.execute('SELECT * FROM ai_agent_settings WHERE user_id = 1 LIMIT 1');
  const settings = settingsRows[0];

  // Test A: In-domain question with fee chunks
  const promptInDomain = buildPromptWithRag(settings.system_prompt, retrievedFee, 'What is the fee for Class 11th science?');
  console.log('  Generating response for in-domain query: "What is the fee for Class 11th science?"...');
  const inDomainReply = await generateAiReply(settings, promptInDomain.systemPrompt, promptInDomain.userMessage, []);
  console.log('\n--- BOT REPLY (IN-DOMAIN) ---');
  console.log(inDomainReply);
  console.log('-----------------------------\n');
  assert.ok(inDomainReply && inDomainReply.length > 20, 'Reply should be substantial and populated');
  assert.ok(inDomainReply.includes('28,000') || inDomainReply.includes('45,000') || inDomainReply.toLowerCase().includes('fee'), 'Should mention the verified fees');

  // Test B: Out-of-scope question with empty chunks
  const promptOutOfScope = buildPromptWithRag(settings.system_prompt, retrievedOutOfScope, 'How to make maggie noodles?');
  console.log('  Generating response for out-of-scope query: "How to make maggie noodles?"...');
  const outOfScopeReply = await generateAiReply(settings, promptOutOfScope.systemPrompt, promptOutOfScope.userMessage, []);
  console.log('\n--- BOT REPLY (OUT-OF-SCOPE) ---');
  console.log(outOfScopeReply);
  console.log('--------------------------------\n');
  assert.ok(
    outOfScopeReply.toLowerCase().includes('aadi shree') ||
    outOfScopeReply.toLowerCase().includes('course') ||
    outOfScopeReply.toLowerCase().includes('assist') ||
    outOfScopeReply.toLowerCase().includes('sorry') ||
    outOfScopeReply.toLowerCase().includes('coaching'),
    'Should politely decline and focus on institute'
  );
  assert.ok(
    !outOfScopeReply.toLowerCase().includes('boil water') &&
    !outOfScopeReply.toLowerCase().includes('tastemaker'),
    'Must NOT give cooking instructions'
  );

  await db.end();
  console.log('\n🎉 ALL 4 TEST SUITES PASSED FLAWLESSLY (100% SUCCESS)!');
}

runTests().catch(err => {
  console.error('❌ Test failed:', err);
  process.exit(1);
});
