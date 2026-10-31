import { fileURLToPath } from 'url';
import { dirname, resolve } from 'path';
import { readFileSync, existsSync } from 'fs';
import assert from 'assert';
import mysql from '../worker/node_modules/mysql2/promise.js';
import { handleDraftEmail } from '../worker/tools-engine.mjs';

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
  console.log('🛡️ RECIPIENT RESOLUTION & ZERO-HALLUCINATION TEST');
  console.log('====================================================\n');

  const db = await mysql.createPool({
    host: process.env.DB_HOST || '127.0.0.1',
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_NAME || 'whatsapp_api'
  });

  const testUserId = 1;
  const testConvId = 666666;
  const context = { db, userId: testUserId, conversationId: testConvId };

  // Case 1: Boss asks "detach to me"
  console.log('👉 Case 1: Recipient is "detach to me"');
  const res1 = await handleDraftEmail({
    to: 'detach to me',
    subject: 'CRM Incident Report',
    body: 'Details of 500 error.'
  }, context);
  assert(res1.success, 'Case 1 failed: ' + res1.error);
  assert.strictEqual(res1.recipient, 'info.assistgroupofficial@gmail.com');
  console.log(`   ✅ Correctly routed to Boss primary email: ${res1.recipient}`);

  // Case 2: Model hallucinations like "ankush@assistgroup.com"
  console.log('\n👉 Case 2: Model hallucinations like "ankush@assistgroup.com"');
  const res2 = await handleDraftEmail({
    to: 'ankush@assistgroup.com',
    subject: 'CRM Incident Report',
    body: 'Details of 500 error.'
  }, context);
  assert(res2.success, 'Case 2 failed: ' + res2.error);
  assert.strictEqual(res2.recipient, 'info.assistgroupofficial@gmail.com');
  console.log(`   ✅ Intercepted bogus domain and corrected to: ${res2.recipient}`);

  // Case 3: Legitimate external contact (Ankush personal email)
  console.log('\n👉 Case 3: Legitimate external contact (ankushkumary113@gmail.com)');
  const res3 = await handleDraftEmail({
    to: 'ankushkumary113@gmail.com',
    subject: 'Tomorrow plans',
    body: 'See you next week.'
  }, context);
  assert(res3.success, 'Case 3 failed: ' + res3.error);
  assert.strictEqual(res3.recipient, 'ankushkumary113@gmail.com');
  console.log(`   ✅ Retained verified external recipient: ${res3.recipient}`);

  // Clean up
  await db.execute('DELETE FROM ai_action_drafts WHERE conversation_id = ?', [testConvId]);
  await db.end();

  console.log('\n🎉 ALL RECIPIENT RESOLUTION TESTS PASSED!\n');
}

runTests().catch(err => {
  console.error('\n❌ TEST FAILED:', err);
  process.exit(1);
});
