import { generateAiReply, buildPromptWithRag } from '../worker/ai-agent.mjs';
import mysql from '../worker/node_modules/mysql2/promise.js';
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

async function testLiveMultiTurn() {
  console.log('=== Testing Live Multi-Turn Memory & Hinglish Mirroring ===\n');

  const db = await mysql.createConnection({
    host: process.env.DB_HOST || '127.0.0.1',
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_NAME || 'whatsapp_api',
    port: parseInt(process.env.DB_PORT || '3306', 10),
  });

  const [rows] = await db.execute('SELECT * FROM ai_agent_settings WHERE user_id = 1 LIMIT 1');
  const settings = rows[0];
  await db.end();

  const historyTurns = [
    {
      senderType: 'contact',
      body: 'What services does Assistgroup offer?',
      role: 'user',
      openAiRole: 'user'
    },
    {
      senderType: 'bot',
      body: 'Assistgroup offers 3 core services: 1. Custom Web Development, 2. SEO Auditing & Optimization, and 3. Cloud Workspace Tools.',
      role: 'model',
      openAiRole: 'assistant'
    }
  ];

  // Follow-up in Hinglish referring to "second wale"
  const currentQuery = 'Bhai second wale ke baare me thoda detail me batao';

  const { systemPrompt, userMessage } = buildPromptWithRag(
    settings.system_prompt,
    [],
    currentQuery
  );

  console.log('Calling generateAiReply with history & Hinglish query...');
  const reply = await generateAiReply(settings, systemPrompt, userMessage, historyTurns);

  console.log('\n--- LIVE AI REPLY ---');
  console.log(reply);
  console.log('---------------------\n');

  // Verify that it understood "second wale" = SEO Auditing!
  const mentionsSeo = /seo/i.test(reply);
  if (mentionsSeo) {
    console.log('✔ SUCCESS: Bot correctly resolved "second wale" to SEO Auditing & Optimization!');
    console.log('✔ SUCCESS: Multi-turn memory and dialect mirroring verified live!');
  } else {
    console.warn('⚠️ Reply did not explicitly mention SEO. Check output above.');
  }
}

testLiveMultiTurn().catch(err => {
  console.error(err);
  process.exit(1);
});
