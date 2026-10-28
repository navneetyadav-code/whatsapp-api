/**
 * Verification Test: Personal AI Assistant Mode, 24/7 Engine, & Feedback Closure
 */

import mysql from '../worker/node_modules/mysql2/promise.js';
import assert from 'assert';
import { buildPromptWithRag } from '../worker/ai-agent.mjs';
import { getCurrentCustomTime } from '../worker/tools-engine.mjs';
import { closeSession } from '../worker/chat-session-manager.mjs';

const dbConfig = {
  host: process.env.DB_HOST || '127.0.0.1',
  user: process.env.DB_USER || 'root',
  password: process.env.DB_PASSWORD || '',
  database: process.env.DB_NAME || 'whatsapp_api'
};

async function runTests() {
  console.log('🧪 Starting Personal AI Assistant Mode Verification Suite...\n');
  const db = await mysql.createConnection(dbConfig);

  try {
    const testUserId = 1;

    // --- TEST 1: Database Schema Verification ---
    console.log('Test 1: Database Schema Verification for ai_agent_settings...');
    const [cols] = await db.execute(
      'SELECT COLUMN_NAME, DATA_TYPE, COLUMN_DEFAULT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = "ai_agent_settings"',
      [dbConfig.database]
    );
    const colNames = cols.map(c => c.COLUMN_NAME);
    assert(colNames.includes('agent_mode'), 'Missing agent_mode column');
    assert(colNames.includes('feedback_enabled'), 'Missing feedback_enabled column');
    assert(colNames.includes('is_always_on'), 'Missing is_always_on column');
    assert(colNames.includes('owner_name'), 'Missing owner_name column');
    console.log('  ✔ All 4 columns (agent_mode, feedback_enabled, is_always_on, owner_name) present.\n');

    // --- TEST 2: Convert to Personal Assistant Mode via Database Update ---
    console.log('Test 2: Switching user 1 to Personal Assistant mode...');
    await db.execute(
      `UPDATE ai_agent_settings 
       SET agent_mode = 'personal', feedback_enabled = 0, is_always_on = 1, owner_name = 'Boss Ankit'
       WHERE user_id = ?`,
      [testUserId]
    );

    const [settingsRows] = await db.execute(
      'SELECT agent_mode, feedback_enabled, is_always_on, owner_name FROM ai_agent_settings WHERE user_id = ? LIMIT 1',
      [testUserId]
    );
    const settings = settingsRows[0];
    assert.strictEqual(settings.agent_mode, 'personal');
    assert.strictEqual(settings.feedback_enabled, 0);
    assert.strictEqual(settings.is_always_on, 1);
    assert.strictEqual(settings.owner_name, 'Boss Ankit');
    console.log('  ✔ User 1 successfully configured in Personal Assistant mode (feedback_enabled = 0, is_always_on = 1).\n');

    // --- TEST 3: Prompt Generation for Personal Assistant vs Business Support ---
    console.log('Test 3: Prompt Generation Verification...');
    const personalPromptResult = buildPromptWithRag(
      'Custom persona instructions',
      [],
      'Can you help me solve a math problem and check the weather?',
      { agentMode: 'personal', ownerName: 'Boss Ankit' }
    );
    assert(personalPromptResult.systemPrompt.includes('PERSONAL ASSISTANT OPERATING DIRECTIVES'), 'Prompt must include Personal Assistant directives');
    assert(personalPromptResult.systemPrompt.includes('Boss Ankit'), 'Prompt must address ownerName');
    assert(personalPromptResult.systemPrompt.includes('NO Feedback/Reviews'), 'Prompt must explicitly silence feedback requests');
    assert(!personalPromptResult.systemPrompt.includes('DO NOT answer the out-of-scope question'), 'Personal mode must not restrict out-of-scope');
    console.log('  ✔ Personal Assistant system prompt correctly built with unrestricted general intelligence & feedback silenced.\n');

    const supportPromptResult = buildPromptWithRag(
      'Support persona',
      [],
      'Random question',
      { agentMode: 'support' }
    );
    assert(supportPromptResult.systemPrompt.includes('STRICT INSTRUCTIONS FOR THE AI ASSISTANT'), 'Support mode must have strict instructions');
    assert(supportPromptResult.systemPrompt.includes('DO NOT answer the out-of-scope question'), 'Support mode must restrict out-of-scope');
    console.log('  ✔ Business Support Bot system prompt maintains strict domain grounding.\n');

    // --- TEST 4: Operating Hours / 24/7 Clock Verification ---
    console.log('Test 4: 24/7 Always On Clock Verification...');
    const personalTime = getCurrentCustomTime(
      { timezone: 'Asia/Kolkata', open_time: '09:00', close_time: '18:00', workdays: [1, 2, 3, 4, 5] },
      { agentMode: 'personal' }
    );
    assert.strictEqual(personalTime.is_office_open, true, 'is_office_open must be true in personal mode regardless of time');
    assert(personalTime.office_schedule.includes('24/7 Always Active'), 'office_schedule must show 24/7');
    console.log('  ✔ Personal Assistant mode is 24/7 always on (office_status: ' + personalTime.office_status + ').\n');

    // --- TEST 5: Chat Session Close Feedback Suppression ---
    console.log('Test 5: Chat Session Close Feedback Suppression...');
    // Create a temporary conversation
    const testPhone = '919999900001';
    await db.execute('DELETE FROM chat_conversations WHERE user_id = ? AND phone = ?', [testUserId, testPhone]);
    const [createRes] = await db.execute(
      `INSERT INTO chat_conversations (user_id, phone, session_uuid, contact_name, last_message, status, awaiting_feedback)
       VALUES (?, ?, 'SES-TEST-001', 'Test Contact', 'Hello', 'open', 0)`,
      [testUserId, testPhone]
    );
    const convId = createRes.insertId;

    // Call closeSession with requestFeedback = false (as Personal Assistant does)
    await closeSession(db, convId, 'Personal session ended', true, false);

    const [closedConvRows] = await db.execute(
      'SELECT status, awaiting_feedback, resolution_remark FROM chat_conversations WHERE id = ?',
      [convId]
    );
    const closedConv = closedConvRows[0];
    assert.strictEqual(closedConv.status, 'closed');
    assert.strictEqual(closedConv.awaiting_feedback, 0, 'awaiting_feedback MUST be 0 when feedback is suppressed!');
    console.log('  ✔ Conversation closed with awaiting_feedback = 0 (feedback prompt completely suppressed).\n');

    // Cleanup test conversation
    await db.execute('DELETE FROM chat_conversations WHERE id = ?', [convId]);

    console.log('🎉 ALL TESTS PASSED! Personal AI Assistant Mode, 24/7 Always On, and Feedback Review closure verified.');
  } finally {
    await db.end();
  }
}

runTests().catch(err => {
  console.error('❌ Test failed:', err);
  process.exit(1);
});
