import { fileURLToPath } from 'url';
import { dirname, resolve } from 'path';
import { readFileSync, existsSync } from 'fs';
import assert from 'assert';
import mysql from '../worker/node_modules/mysql2/promise.js';
import { detectIntents, composeSkillsAndTools } from '../worker/intent-router.mjs';
import { buildPromptWithRag } from '../worker/ai-agent.mjs';
import { UniversalToolsRegistry, handleDraftEmail, handleConfirmAction } from '../worker/tools-engine.mjs';

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
  console.log('🧪 HYBRID INTENT ROUTER & TWO-PHASE HITL EMAIL SUITE');
  console.log('====================================================\n');

  // ----------------------------------------------------
  // TEST 1: Tier 1 Lexical Fast-Path Router & Latency (<5ms)
  // ----------------------------------------------------
  console.log('👉 [Test 1] Fast-Path Lexical Intent Router Latency & Accuracy');
  const t0 = performance.now();
  const weatherIntents = await detectIntents('What is the weather in Delhi today?');
  const mathIntents = await detectIntents('Calculate 450 * 12 + 100');
  const crmIntents = await detectIntents('Show me total CRM leads and metrics');
  const timeIntents = await detectIntents('What is the current time?');
  const elapsedFast = performance.now() - t0;

  assert.deepStrictEqual(weatherIntents, ['weather'], 'Weather intent mismatch');
  assert.deepStrictEqual(mathIntents, ['math'], 'Math intent mismatch');
  assert.deepStrictEqual(crmIntents, ['crm_read'], 'CRM read intent mismatch');
  assert.deepStrictEqual(timeIntents, ['time'], 'Time intent mismatch');
  console.log(`   ✅ 4 queries resolved in ${elapsedFast.toFixed(2)}ms (Average ${(elapsedFast / 4).toFixed(3)}ms/query)`);

  // ----------------------------------------------------
  // TEST 2: Compound Multi-Intent Resolution
  // ----------------------------------------------------
  console.log('\n👉 [Test 2] Compound Conjunction Multi-Intent Tagging');
  const compoundQuery1 = 'Check the CRM inquiries and draft an email to the client';
  const compoundIntents1 = await detectIntents(compoundQuery1);
  console.log(`   Query: "${compoundQuery1}" -> Intents:`, compoundIntents1);
  assert(compoundIntents1.includes('crm_read'), 'Should detect crm_read in compound query');
  assert(compoundIntents1.includes('email_draft'), 'Should detect email_draft in compound query');

  const compoundQuery2 = 'Calculate 500 * 20 then tell me what is the time';
  const compoundIntents2 = await detectIntents(compoundQuery2);
  console.log(`   Query: "${compoundQuery2}" -> Intents:`, compoundIntents2);
  assert(compoundIntents2.includes('math'), 'Should detect math');
  assert(compoundIntents2.includes('time'), 'Should detect time');
  console.log('   ✅ Multi-intent non-mutually exclusive routing verified');

  // ----------------------------------------------------
  // TEST 3: State-Aware Confirmation Detection
  // ----------------------------------------------------
  console.log('\n👉 [Test 3] State-Aware Draft Confirmation & Rejection Routing');
  const confirm1 = await detectIntents('Send it', { pendingDraft: { id: 99, draft_code: 'DRAFT-1234' } });
  const confirm2 = await detectIntents('bhej do', { pendingDraft: { id: 99, draft_code: 'DRAFT-1234' } });
  const confirm3 = await detectIntents('yes', { pendingDraft: { id: 99, draft_code: 'DRAFT-1234' } });
  const cancel1 = await detectIntents('cancel', { pendingDraft: { id: 99, draft_code: 'DRAFT-1234' } });
  const cancel2 = await detectIntents('mat bhejo', { pendingDraft: { id: 99, draft_code: 'DRAFT-1234' } });
  const edit1 = await detectIntents('change subject to Updated Proposal', { pendingDraft: { id: 99, draft_code: 'DRAFT-1234' } });

  assert.deepStrictEqual(confirm1, ['email_confirm'], 'Confirm 1 failed');
  assert.deepStrictEqual(confirm2, ['email_confirm'], 'Confirm 2 failed');
  assert.deepStrictEqual(confirm3, ['email_confirm'], 'Confirm 3 failed');
  assert.deepStrictEqual(cancel1, ['email_cancel'], 'Cancel 1 failed');
  assert.deepStrictEqual(cancel2, ['email_cancel'], 'Cancel 2 failed');
  assert.deepStrictEqual(edit1, ['email_draft'], 'Edit failed');
  console.log('   ✅ State-aware contextual confirmation routing verified');

  // ----------------------------------------------------
  // TEST 4: Micro-Kernel Prompt Size & Efficiency Assertion
  // ----------------------------------------------------
  console.log('\n👉 [Test 4] Micro-Kernel Prompt Size & Token Budget');
  const baseMicro = buildPromptWithRag('', [], 'Hello', { agentMode: 'personal', ownerName: 'Ankit' });
  const charCountBase = baseMicro.systemPrompt.length;
  const approxTokensBase = Math.round(charCountBase / 4);
  console.log(`   Base Micro-Kernel characters: ${charCountBase} (~${approxTokensBase} tokens)`);
  assert(approxTokensBase <= 200, `Base Micro-Kernel exceeded 200 tokens: ${approxTokensBase}`);

  const dynamicCompose = composeSkillsAndTools(['email_draft'], [
    { tool_slug: 'draft_email', tool_name: 'draft_email' },
    { tool_slug: 'assistgroup_crm', tool_name: 'assistgroup_crm' },
    { tool_slug: 'evaluate_math', tool_name: 'evaluate_math' }
  ], true);

  const dynamicMicro = buildPromptWithRag('', [], 'Draft mail', {
    agentMode: 'personal',
    ownerName: 'Ankit',
    skillDirectives: dynamicCompose.skillDirectives
  });
  const charCountWithSkill = dynamicMicro.systemPrompt.length;
  const approxTokensWithSkill = Math.round(charCountWithSkill / 4);
  console.log(`   Micro-Kernel + Email Skill: ${charCountWithSkill} characters (~${approxTokensWithSkill} tokens)`);
  assert(approxTokensWithSkill <= 300, `Prompt with dynamic skill exceeded 300 tokens: ${approxTokensWithSkill}`);
  console.log('   ✅ Micro-kernel meets strict token budget (<300 tokens)');

  // ----------------------------------------------------
  // TEST 5: Dynamic Tool Filtering & Scoping
  // ----------------------------------------------------
  console.log('\n👉 [Test 5] Dynamic Tool Scoping via composeSkillsAndTools');
  const mockAllTools = [
    { tool_slug: 'draft_email', tool_name: 'draft_email' },
    { tool_slug: 'confirm_action', tool_name: 'confirm_action' },
    { tool_slug: 'assistgroup_crm', tool_name: 'assistgroup_crm' },
    { tool_slug: 'evaluate_math', tool_name: 'evaluate_math' },
    { tool_slug: 'get_current_time', tool_name: 'get_current_time' },
    { tool_slug: 'web_search', tool_name: 'web_search' }
  ];

  // Support mode: strictly scoped to detected intent
  const supportEmailScope = composeSkillsAndTools(['email_draft'], mockAllTools, false);
  const supportToolSlugs = supportEmailScope.filteredTools.map(t => t.tool_slug);
  assert(supportToolSlugs.includes('draft_email'), 'draft_email must be in scope');
  assert(!supportToolSlugs.includes('assistgroup_crm'), 'assistgroup_crm must NOT be in support email_draft scope');

  // Personal mode: Boss has all tools available + dynamic skill directives
  const personalScope = composeSkillsAndTools(['email_draft'], mockAllTools, true);
  const personalToolSlugs = personalScope.filteredTools.map(t => t.tool_slug);
  assert(personalToolSlugs.includes('draft_email'), 'draft_email must be in personal scope');
  assert(personalToolSlugs.includes('confirm_action'), 'confirm_action must be in personal scope');
  assert(personalScope.skillDirectives.some(d => d.includes('EMAIL DRAFTING PROTOCOL')), 'Must include email drafting directive');
  console.log(`   Support Scope: [${supportToolSlugs.join(', ')}]`);
  console.log(`   Personal Executive Scope: [${personalToolSlugs.join(', ')}]`);
  console.log('   ✅ Tool scoping & personal executive coverage verified');

  // ----------------------------------------------------
  // TEST 6: Two-Phase Database HITL Operations (Draft & Cancel)
  // ----------------------------------------------------
  console.log('\n👉 [Test 6] Database HITL Draft Creation & Cancellation');
  const db = await mysql.createPool({
    host: process.env.DB_HOST || '127.0.0.1',
    port: Number(process.env.DB_PORT || 3306),
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_NAME || 'whatsapp_api'
  });

  const testUserId = 1;
  const testConvId = 999999;

  // Clean up any stale test rows first
  await db.execute('DELETE FROM ai_action_drafts WHERE conversation_id = ?', [testConvId]);

  // Phase 1: Draft Email
  const draftRes = await handleDraftEmail({
    to: 'test.recipient@example.com',
    subject: 'Project Kickoff Meeting',
    body: 'Hello, please confirm your availability for tomorrow 3 PM IST.',
    cc: 'team@example.com'
  }, { db, userId: testUserId, conversationId: testConvId });

  assert(draftRes.success, 'Drafting email failed: ' + draftRes.error);
  assert(draftRes.draft_code && draftRes.draft_code.startsWith('DRAFT-'), 'Invalid draft code format');
  console.log(`   ✅ Phase 1 Draft created: Code=${draftRes.draft_code}, Status=${draftRes.status}`);

  // Verify in MySQL
  const [draftDbRows] = await db.execute(
    'SELECT * FROM ai_action_drafts WHERE draft_code = ?',
    [draftRes.draft_code]
  );
  assert.strictEqual(draftDbRows.length, 1, 'Draft was not saved in ai_action_drafts table');
  assert.strictEqual(draftDbRows[0].status, 'pending', 'Draft initial status must be pending');

  // Phase 2: Cancel Draft safely
  const cancelRes = await handleConfirmAction({
    draft_code: draftRes.draft_code,
    action: 'cancel'
  }, { db, userId: testUserId, conversationId: testConvId, callerRole: 'admin' });

  assert(cancelRes.success, 'Cancelling draft failed: ' + cancelRes.error);
  assert.strictEqual(cancelRes.status, 'cancelled');

  const [cancelledDbRows] = await db.execute(
    'SELECT status FROM ai_action_drafts WHERE draft_code = ?',
    [draftRes.draft_code]
  );
  assert.strictEqual(cancelledDbRows[0].status, 'cancelled', 'Draft status in DB must be cancelled');
  console.log(`   ✅ Phase 2 Cancelled: Status=${cancelledDbRows[0].status}`);

  // ----------------------------------------------------
  // TEST 7: RBAC Permission Enforcement
  // ----------------------------------------------------
  console.log('\n👉 [Test 7] Role-Based Access Control on HITL Email Tools');
  const userToolsFromDb = await UniversalToolsRegistry.loadUserTools(testUserId, db);
  const draftToolDef = userToolsFromDb.find(t => t.tool_slug === 'draft_email');
  const confirmToolDef = userToolsFromDb.find(t => t.tool_slug === 'confirm_action');

  assert(draftToolDef, 'draft_email must be present in ai_custom_tools');
  assert(confirmToolDef, 'confirm_action must be present in ai_custom_tools');

  // RBAC compilation test: Guest must NOT receive draft_email or confirm_action
  const guestDeclarations = UniversalToolsRegistry.compileDeclarationsForRole(userToolsFromDb, 'guest');
  const guestToolNames = guestDeclarations.map(d => d.name);
  assert(!guestToolNames.includes('draft_email'), 'Guest must NOT have access to draft_email');
  assert(!guestToolNames.includes('confirm_action'), 'Guest must NOT have access to confirm_action');

  // Admin MUST receive both
  const adminDeclarations = UniversalToolsRegistry.compileDeclarationsForRole(userToolsFromDb, 'admin');
  const adminToolNames = adminDeclarations.map(d => d.name);
  assert(adminToolNames.includes('draft_email'), 'Admin must have access to draft_email');
  assert(adminToolNames.includes('confirm_action'), 'Admin must have access to confirm_action');

  // Clean up test draft
  await db.execute('DELETE FROM ai_action_drafts WHERE conversation_id = ?', [testConvId]);
  await db.end();

  console.log('   ✅ RBAC strictly protects email tools from unauthorized execution');
  console.log('\n🎉 ALL 7 TESTS PASSED SUCCESSFULLY! ZERO ERRORS.\n');
}

runTests().catch(err => {
  console.error('\n❌ TEST SUITE FAILED:', err);
  process.exit(1);
});
