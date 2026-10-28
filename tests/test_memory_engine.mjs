import mysql from '../worker/node_modules/mysql2/promise.js';
import { ChatMemoryEngine } from '../worker/chat-memory.mjs';
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
  console.log('=== Running ChatMemoryEngine Unit Tests ===\n');

  // Test 1: In-memory structure builders
  const mockTurns = [
    { senderType: 'contact', body: 'What plans do you offer?', role: 'user', openAiRole: 'user' },
    { senderType: 'bot', body: 'We have Starter ($99) and Enterprise ($299).', role: 'model', openAiRole: 'assistant' }
  ];

  console.log('[Test 1A] Building Gemini contents array with dialogue history...');
  const geminiContents = ChatMemoryEngine.buildGeminiContents('System instructions', mockTurns, 'Tell me more about the first one.');
  console.log('Gemini Contents count:', geminiContents.length);
  if (geminiContents.length === 3 && geminiContents[1].role === 'model' && geminiContents[2].role === 'user') {
    console.log('✔ Test 1A Passed: Gemini dialogue turns formatted cleanly.\n');
  } else {
    console.error('❌ Test 1A Failed!');
    process.exit(1);
  }

  console.log('[Test 1B] Building OpenAI / Groq messages array with dialogue history...');
  const openAiMessages = ChatMemoryEngine.buildOpenAiMessages('System instructions', mockTurns, 'Tell me more about the first one.');
  console.log('OpenAI Messages count:', openAiMessages.length);
  if (openAiMessages.length === 4 && openAiMessages[0].role === 'system' && openAiMessages[2].role === 'assistant') {
    console.log('✔ Test 1B Passed: OpenAI messages turns formatted cleanly.\n');
  } else {
    console.error('❌ Test 1B Failed!');
    process.exit(1);
  }

  console.log('[Test 1C] Testing formatTranscript...');
  const transcript = ChatMemoryEngine.formatTranscript(mockTurns);
  console.log('Transcript:\n' + transcript);
  if (transcript.includes('Customer: What plans') && transcript.includes('Assistant: We have')) {
    console.log('✔ Test 1C Passed: Transcript generated properly.\n');
  } else {
    console.error('❌ Test 1C Failed!');
    process.exit(1);
  }

  // Test 2: Database integration test
  const db = await mysql.createConnection({
    host: process.env.DB_HOST || '127.0.0.1',
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_NAME || 'whatsapp_api',
    port: parseInt(process.env.DB_PORT || '3306', 10),
  });

  const testPhone = '918888877777';
  const userId = 1;
  const testSessionUuid = 'SES-TEST-MEMORY-123';

  // Cleanup
  await db.execute('DELETE FROM chat_messages WHERE conversation_id IN (SELECT id FROM chat_conversations WHERE user_id = ? AND phone = ?)', [userId, testPhone]);
  await db.execute('DELETE FROM chat_conversations WHERE user_id = ? AND phone = ?', [userId, testPhone]);

  // Insert mock conversation and 2 messages
  const [cRes] = await db.execute(
    'INSERT INTO chat_conversations (user_id, phone, session_uuid, contact_name, status, last_message) VALUES (?, ?, ?, ?, "open", "Test message")',
    [userId, testPhone, testSessionUuid, 'Alice']
  );
  const convId = cRes.insertId;

  await db.execute(
    'INSERT INTO chat_messages (user_id, conversation_id, session_uuid, sender_type, message_type, body) VALUES (?, ?, ?, "contact", "text", "Do you do SEO?")',
    [userId, convId, testSessionUuid]
  );
  await db.execute(
    'INSERT INTO chat_messages (user_id, conversation_id, session_uuid, sender_type, message_type, body) VALUES (?, ?, ?, "bot", "text", "Yes, Assistgroup provides SEO audit and optimization.")',
    [userId, convId, testSessionUuid]
  );

  console.log('[Test 2] Querying recent turns from database...');
  const dbTurns = await ChatMemoryEngine.getRecentTurns(db, convId, testSessionUuid, 5);
  console.log('Retrieved turns from DB:', dbTurns.length);
  if (dbTurns.length === 2 && dbTurns[0].body === 'Do you do SEO?' && dbTurns[1].role === 'model') {
    console.log('✔ Test 2 Passed: Historical turns retrieved from MySQL in correct chronological order!\n');
  } else {
    console.error('❌ Test 2 Failed!');
    process.exit(1);
  }

  // Cleanup
  await db.execute('DELETE FROM chat_messages WHERE conversation_id = ?', [convId]);
  await db.execute('DELETE FROM chat_conversations WHERE id = ?', [convId]);
  await db.end();

  console.log('🎉 All ChatMemoryEngine tests passed successfully!');
}

runTest().catch(err => {
  console.error(err);
  process.exit(1);
});
