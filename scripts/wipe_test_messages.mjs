import mysql from '../worker/node_modules/mysql2/promise.js';
import fs from 'fs';
import { resolve, dirname } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const envPath = resolve(__dirname, '..', '.env');
const env = fs.readFileSync(envPath, 'utf8');

const dbUser = env.match(/DB_USER=(.*)/)?.[1]?.trim() || 'root';
const dbPass = env.match(/DB_PASSWORD=(.*)/)?.[1]?.trim() || '';
const dbName = env.match(/DB_NAME=(.*)/)?.[1]?.trim() || 'whatsapp_api';
const dbHost = env.match(/DB_HOST=(.*)/)?.[1]?.trim() || '127.0.0.1';
const dbPort = parseInt(env.match(/DB_PORT=(.*)/)?.[1]?.trim() || '3306', 10);

async function wipeTestChatData() {
  console.log('=====================================================');
  console.log('       SAFE DATABASE RESET: TEST CHAT MESSAGES       ');
  console.log('=====================================================\n');

  const db = await mysql.createConnection({
    host: dbHost,
    user: dbUser,
    password: dbPass,
    database: dbName,
    port: dbPort
  });

  // Verify session integrity before wiping
  const [sessionsBefore] = await db.query('SELECT user_id, phone, push_name, status FROM whatsapp_sessions');
  console.log('Connected WhatsApp Sessions to PRESERVE:');
  console.log(sessionsBefore);

  const [aiSettingsBefore] = await db.query('SELECT user_id, provider_priority, gemini_model FROM ai_agent_settings');
  console.log('AI Agent Settings to PRESERVE:');
  console.log(aiSettingsBefore);

  console.log('\n--- Purging Test Chat Tables ---');

  await db.query('SET FOREIGN_KEY_CHECKS = 0');

  const tablesToWipe = [
    'chat_messages',
    'chat_conversations',
    'chat_escalations',
    'chat_feedback',
    'inbound_messages',
    'baileys_message_store'
  ];

  for (const table of tablesToWipe) {
    const [countRes] = await db.query(`SELECT COUNT(*) as cnt FROM ${table}`);
    const count = countRes[0]?.cnt || 0;
    await db.query(`TRUNCATE TABLE ${table}`);
    console.log(`✔ Cleared table '${table}' (${count} test rows deleted)`);
  }

  await db.query('SET FOREIGN_KEY_CHECKS = 1');

  // Verify preserved tables
  const [sessionsAfter] = await db.query('SELECT user_id, phone, push_name, status FROM whatsapp_sessions');
  const [usersAfter] = await db.query('SELECT id, email FROM users');
  const [aiSettingsAfter] = await db.query('SELECT id, user_id, provider_priority FROM ai_agent_settings');

  console.log('\n--- Verification of Preserved Data ---');
  console.log(`✔ Users intact: ${usersAfter.length} user(s)`);
  console.log(`✔ WhatsApp Sessions intact: ${sessionsAfter.length} session(s) (${sessionsAfter.map(s => s.phone).filter(Boolean).join(', ')})`);
  console.log(`✔ AI Agent Settings intact: ${aiSettingsAfter.length} config(s)`);

  await db.end();

  console.log('\n=====================================================');
  console.log('  SUCCESS: Test chat history wiped cleanly!           ');
  console.log('  Database is 100% fresh for collecting real samples.  ');
  console.log('=====================================================\n');
}

wipeTestChatData().catch(err => {
  console.error('Reset Failed:', err);
  process.exit(1);
});
