import assert from 'assert';
import mysql from '../worker/node_modules/mysql2/promise.js';
import fs from 'fs';
import { resolveActiveSession, closeSession, isWakeupTrigger } from '../worker/chat-session-manager.mjs';
import { ChatResolutionEngine } from '../worker/chat-resolution.mjs';

async function runTest() {
  console.log('=====================================================');
  console.log(' TESTING CHATBOT WAKE-UP & ANTI-BOMBING LIFECYCLE    ');
  console.log('=====================================================');

  const env = fs.readFileSync('.env', 'utf8');
  const dbUser = env.match(/DB_USER=(.*)/)?.[1]?.trim() || 'root';
  const dbPass = env.match(/DB_PASSWORD=(.*)/)?.[1]?.trim() || '';
  const dbName = env.match(/DB_NAME=(.*)/)?.[1]?.trim() || 'whatsapp_api';
  const dbHost = env.match(/DB_HOST=(.*)/)?.[1]?.trim() || '127.0.0.1';

  const db = await mysql.createConnection({ host: dbHost, user: dbUser, password: dbPass, database: dbName });

  const testPhone = '9999999999';
  const userId = 1;

  // Clean test conversation if present
  await db.execute('DELETE FROM chat_conversations WHERE user_id = ? AND phone = ?', [userId, testPhone]);

  // STEP 1: First inbound message - creates open session
  console.log('\n[STEP 1] User sends initial message "Hii"...');
  const s1 = await resolveActiveSession(db, userId, testPhone, 'Test User', 'Hii');
  assert.strictEqual(s1.status, 'open', 'Status must be open');
  assert.strictEqual(s1.isNewSession, true, 'Must be a new session');
  assert.strictEqual(s1.botPaused, false, 'Bot must not be paused');
  console.log(`  ✔ Session created: ${s1.sessionUuid}, status: ${s1.status}`);

  // STEP 2: Conversation is resolved & closed
  console.log('\n[STEP 2] Auto-resolution triggered (e.g. user says "Close the chat")...');
  const resolution = ChatResolutionEngine.detectResolution('Close the chat', [{ senderType: 'bot', body: 'Here is information.' }]);
  assert.strictEqual(resolution.isResolved, true, 'Should detect resolution');
  await closeSession(db, s1.conversationId, resolution.remark, true);

  const [c2] = await db.execute('SELECT status, awaiting_feedback, session_uuid FROM chat_conversations WHERE id = ?', [s1.conversationId]);
  assert.strictEqual(c2[0].status, 'closed', 'Status must be closed');
  assert.strictEqual(c2[0].awaiting_feedback, 1, 'awaiting_feedback must be 1');
  console.log(`  ✔ Conversation marked closed! awaiting_feedback = 1`);

  // STEP 3: User submits feedback rating "5"
  console.log('\n[STEP 3] User replies with 5-star rating "5"...');
  await db.execute(
    `INSERT INTO chat_feedback (user_id, conversation_id, phone, contact_name, rating, message, created_at)
     VALUES (?, ?, ?, 'Test User', 5, '5', NOW())`,
    [userId, s1.conversationId, testPhone]
  );
  await db.execute(
    `UPDATE chat_conversations
     SET awaiting_feedback = 0,
         status = 'closed',
         last_message = 'Feedback received: 5/5 ⭐'
     WHERE id = ?`,
    [s1.conversationId]
  );
  console.log('  ✔ Feedback recorded: 5/5 ⭐. Conversation confirmed CLOSED.');

  // STEP 4: Anti-Bombing Test - user sends random texts while closed
  console.log('\n[STEP 4] Anti-Bombing Test: User sends random messages while chat is closed...');
  const randomMessages = ['ok', 'thanks', 'bye', 'anyone there?', 'fees kitni hai?'];

  for (const msg of randomMessages) {
    const sBomb = await resolveActiveSession(db, userId, testPhone, 'Test User', msg);
    assert.strictEqual(sBomb.status, 'closed', `Message "${msg}" must NOT reopen closed chat!`);
    assert.strictEqual(sBomb.botPaused, true, `Bot must remain paused for "${msg}"!`);
    assert.strictEqual(sBomb.wokeUp, false, `wokeUp must be false for "${msg}"!`);
    console.log(`  ✔ Inbound "${msg}" -> status: ${sBomb.status}, botPaused: ${sBomb.botPaused}, wokeUp: ${sBomb.wokeUp} (NO BOMBING)`);
  }

  // STEP 5: Wake-up Test - user sends /start or start
  console.log('\n[STEP 5] Wake-up Test: User sends "/start" or "start"...');
  assert.strictEqual(isWakeupTrigger('/start'), true, '/start must be recognized');
  assert.strictEqual(isWakeupTrigger('start'), true, 'start must be recognized');
  assert.strictEqual(isWakeupTrigger('  Start!  '), true, 'Start! with whitespace/punctuation must be recognized');
  assert.strictEqual(isWakeupTrigger('start please'), false, 'longer sentences must not trigger wake-up');

  const sWake = await resolveActiveSession(db, userId, testPhone, 'Test User', '/start');
  assert.strictEqual(sWake.status, 'open', 'Chat must reopen to open status');
  assert.strictEqual(sWake.isNewSession, true, 'Must spawn a fresh session');
  assert.strictEqual(sWake.botPaused, false, 'Bot must unpause');
  assert.strictEqual(sWake.wokeUp, true, 'wokeUp must be true');
  assert.notStrictEqual(sWake.sessionUuid, s1.sessionUuid, 'New session must have new ticket UUID');
  console.log(`  ✔ Inbound "/start" -> WOKE UP! status: ${sWake.status}, new session: ${sWake.sessionUuid}`);

  // Clean test conversation
  await db.execute('DELETE FROM chat_feedback WHERE conversation_id = ?', [s1.conversationId]);
  await db.execute('DELETE FROM chat_conversations WHERE id = ?', [s1.conversationId]);
  await db.end();

  console.log('\n🎉 ALL WAKE-UP & ANTI-BOMBING LIFECYCLE TESTS PASSED (100%)!');
}

runTest().catch(err => {
  console.error('❌ Test failed:', err);
  process.exit(1);
});
