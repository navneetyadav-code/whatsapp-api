import mysql from '../worker/node_modules/mysql2/promise.js';
import { generateSessionUuid, resolveActiveSession, closeSession } from '../worker/chat-session-manager.mjs';
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

async function runTest() {
  console.log('=== Running ChatSessionManager Unit Tests ===\n');

  // Test 1: UUID format
  const uuid = generateSessionUuid();
  console.log('[Test 1] Generated Session UUID:', uuid);
  if (/^SES-\d{8}-[0-9A-F]{6}$/.test(uuid)) {
    console.log('✔ Test 1 Passed: Format matches SES-YYYYMMDD-XXXX\n');
  } else {
    console.error('❌ Test 1 Failed on format:', uuid);
    process.exit(1);
  }

  // Test 2: Database lifecycle
  const db = await mysql.createConnection({
    host: process.env.DB_HOST || '127.0.0.1',
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_NAME || 'whatsapp_api',
    port: parseInt(process.env.DB_PORT || '3306', 10),
  });

  const testPhone = '919999988888';
  const userId = 1;

  // Cleanup test row
  await db.execute('DELETE FROM chat_messages WHERE conversation_id IN (SELECT id FROM chat_conversations WHERE user_id = ? AND phone = ?)', [userId, testPhone]);
  await db.execute('DELETE FROM chat_conversations WHERE user_id = ? AND phone = ?', [userId, testPhone]);

  // Step A: New inquiry
  console.log('[Test 2A] Initial incoming inquiry from new customer...');
  const s1 = await resolveActiveSession(db, userId, testPhone, 'Test User', 'Hello initial inquiry');
  console.log('Session 1:', s1);
  if (s1.isNewSession && s1.status === 'open') {
    console.log('✔ Test 2A Passed: Created new open session.\n');
  } else {
    console.error('❌ Test 2A Failed!');
    process.exit(1);
  }

  // Step B: Ongoing message in active session
  console.log('[Test 2B] Follow-up message while session is open...');
  const s2 = await resolveActiveSession(db, userId, testPhone, 'Test User', 'Second question');
  if (!s2.isNewSession && s2.sessionUuid === s1.sessionUuid) {
    console.log('✔ Test 2B Passed: Same session maintained during active chat.\n');
  } else {
    console.error('❌ Test 2B Failed!');
    process.exit(1);
  }

  // Step C: Close session with AI remark
  console.log('[Test 2C] Closing session with AI resolution remark...');
  await closeSession(db, s1.conversationId, 'Customer asked about pricing, details provided and satisfied.', true);

  const [checkClosed] = await db.execute('SELECT status, is_auto_closed, resolution_remark, awaiting_feedback FROM chat_conversations WHERE id = ?', [s1.conversationId]);
  console.log('Closed record state:', checkClosed[0]);
  if (checkClosed[0].status === 'closed' && checkClosed[0].is_auto_closed === 1 && checkClosed[0].resolution_remark) {
    console.log('✔ Test 2C Passed: Session closed with resolution remark & auto_closed flag.\n');
  } else {
    console.error('❌ Test 2C Failed!');
    process.exit(1);
  }

  // Step D: Customer returns next week (fresh inquiry)
  console.log('[Test 2D] Customer returns later with a new inquiry...');
  const s3 = await resolveActiveSession(db, userId, testPhone, 'Test User', 'Hi I have another question');
  console.log('Session 3:', s3);
  if (s3.isNewSession && s3.sessionUuid !== s1.sessionUuid && s3.status === 'open') {
    console.log('✔ Test 2D Passed: New distinct session established cleanly!\n');
  } else {
    console.error('❌ Test 2D Failed!');
    process.exit(1);
  }

  // Cleanup
  await db.execute('DELETE FROM chat_messages WHERE conversation_id IN (SELECT id FROM chat_conversations WHERE user_id = ? AND phone = ?)', [userId, testPhone]);
  await db.execute('DELETE FROM chat_conversations WHERE user_id = ? AND phone = ?', [userId, testPhone]);
  await db.end();

  console.log('🎉 All ChatSessionManager tests passed successfully!');
}

runTest().catch(err => {
  console.error(err);
  process.exit(1);
});
