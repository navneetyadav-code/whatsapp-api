import { fileURLToPath } from 'url';
import { dirname, resolve } from 'path';
import { readFileSync, existsSync } from 'fs';
import assert from 'assert';
import mysql from '../worker/node_modules/mysql2/promise.js';
import { detectIntents, composeSkillsAndTools } from '../worker/intent-router.mjs';
import { UniversalToolsRegistry } from '../worker/tools-engine.mjs';
import { buildPromptWithRag, generateAiReply } from '../worker/ai-agent.mjs';
import { ChatFactMemory } from '../worker/chat-fact-memory.mjs';

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

async function simulateLiveEmailDialogue() {
  console.log('====================================================');
  console.log('📬 E2E SIMULATION: EXACT USER EMAIL DRAFT & DISPATCH');
  console.log('====================================================\n');

  const db = await mysql.createPool({
    host: process.env.DB_HOST || '127.0.0.1',
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_NAME || 'whatsapp_api'
  });

  const testUserId = 1;
  const testConvId = 777777;
  const senderPhone = '918581065107';

  // Ensure clean state
  await db.execute('DELETE FROM ai_action_drafts WHERE conversation_id = ?', [testConvId]);

  const [aiSettingsRows] = await db.execute(
    'SELECT * FROM ai_agent_settings WHERE user_id = ? LIMIT 1',
    [testUserId]
  );
  const aiSettings = aiSettingsRows[0];
  const isPersonalMode = true;

  // ----------------------------------------------------
  // Turn 1: Boss asks to send mail
  // ----------------------------------------------------
  const msg1 = "Send a mail to ankushkumary113@gmail.com about me that friend I am not committed Tommorow 😭 sorry";
  console.log('👉 Turn 1 User Message:', msg1);

  const intents1 = await detectIntents(msg1, { pendingDraft: null });
  console.log('   Intents:', intents1);
  const allTools = await UniversalToolsRegistry.loadUserTools(testUserId, db);
  const comp1 = composeSkillsAndTools(intents1, allTools, isPersonalMode, { pendingDraft: null });
  const userFacts = await ChatFactMemory.loadUserFacts(db, testUserId);

  const prompt1 = buildPromptWithRag(
    aiSettings.system_prompt,
    [],
    msg1,
    {
      agentMode: 'personal',
      ownerName: 'Boss Ankit',
      skillDirectives: comp1.skillDirectives,
      factsText: userFacts.factsText
    }
  );

  const toolsContext1 = {
    db,
    userId: testUserId,
    conversationId: testConvId,
    callerPhone: senderPhone,
    callerRole: 'admin',
    agentMode: 'personal',
    ownerName: 'Boss Ankit',
    scopedTools: comp1.filteredTools
  };

  const reply1 = await generateAiReply(aiSettings, prompt1.systemPrompt, prompt1.userMessage, [], toolsContext1);
  console.log('\n🤖 Bot Reply Turn 1:\n' + reply1 + '\n');

  // Verify draft was created in MySQL
  const [draftsTurn1] = await db.execute(
    'SELECT * FROM ai_action_drafts WHERE conversation_id = ? AND status = "pending"',
    [testConvId]
  );
  assert.strictEqual(draftsTurn1.length, 1, 'Draft MUST be recorded in MySQL with status=pending');
  const pendingDraft = draftsTurn1[0];
  console.log(`   ✅ Draft verified in DB: Code=${pendingDraft.draft_code}, Recipient=${pendingDraft.recipient}`);

  // ----------------------------------------------------
  // Turn 2: Boss confirms with "Yess"
  // ----------------------------------------------------
  const msg2 = "Yess";
  console.log('\n👉 Turn 2 User Message:', msg2);

  const intents2 = await detectIntents(msg2, { pendingDraft });
  console.log('   Intents:', intents2);
  assert(intents2.includes('email_confirm'), '"Yess" must detect email_confirm');

  const comp2 = composeSkillsAndTools(intents2, allTools, isPersonalMode, { pendingDraft });
  const prompt2 = buildPromptWithRag(
    aiSettings.system_prompt,
    [],
    msg2,
    {
      agentMode: 'personal',
      ownerName: 'Boss Ankit',
      skillDirectives: comp2.skillDirectives,
      factsText: userFacts.factsText
    }
  );

  const toolsContext2 = {
    db,
    userId: testUserId,
    conversationId: testConvId,
    callerPhone: senderPhone,
    callerRole: 'admin',
    agentMode: 'personal',
    ownerName: 'Boss Ankit',
    scopedTools: comp2.filteredTools
  };

  const historyTurns = [
    { senderType: 'contact', body: msg1, role: 'user', openAiRole: 'user' },
    { senderType: 'bot', body: reply1, role: 'model', openAiRole: 'assistant' }
  ];

  const reply2 = await generateAiReply(aiSettings, prompt2.systemPrompt, prompt2.userMessage, historyTurns, toolsContext2);
  console.log('\n🤖 Bot Reply Turn 2:\n' + reply2 + '\n');

  // Verify draft was updated to sent
  const [draftsTurn2] = await db.execute(
    'SELECT * FROM ai_action_drafts WHERE id = ?',
    [pendingDraft.id]
  );
  assert.strictEqual(draftsTurn2[0].status, 'sent', 'Draft status MUST update to sent');
  console.log(`   ✅ Draft ${pendingDraft.draft_code} sent status confirmed in MySQL!`);

  // Clean up
  await db.execute('DELETE FROM ai_action_drafts WHERE conversation_id = ?', [testConvId]);
  await db.end();

  console.log('\n🎉 FULL E2E SIMULATION PASSED FLAWLESSLY!\n');
}

simulateLiveEmailDialogue().catch(err => {
  console.error('\n❌ E2E SIMULATION FAILED:', err);
  process.exit(1);
});
