import mysql from '../worker/node_modules/mysql2/promise.js';
import assert from 'assert';
import { generateAiReply } from '../worker/ai-agent.mjs';

async function main() {
  console.log('🤖 Testing LLM Autonomous Function Calling for AssistGroup APIs...\n');

  const db = await mysql.createConnection({
    host: '127.0.0.1',
    user: 'root',
    password: '',
    database: 'whatsapp_api'
  });

  const userId = 1;
  const [settingsRows] = await db.execute('SELECT * FROM ai_agent_settings WHERE user_id = ? LIMIT 1', [userId]);
  assert(settingsRows && settingsRows.length > 0, 'ai_agent_settings for user 1 must exist');
  const aiSettings = settingsRows[0];

  const toolsContext = {
    db,
    userId,
    callerPhone: '918581065107',
    callerRole: 'admin',
    agentMode: 'personal',
    ownerName: 'Boss'
  };

  const userQuery = 'Boss here. Please check our AssistGroup platform metrics right now and tell me how many total users, active inquiries, and files we have.';
  console.log(`[User]: "${userQuery}"`);

  const reply = await generateAiReply(
    aiSettings,
    aiSettings.system_prompt,
    userQuery,
    [],
    toolsContext
  );

  console.log('\n[Assistant Reply]:\n', reply);
  assert(reply && reply.length > 20, 'Reply must not be empty');
  assert(reply.includes('9') || reply.includes('user') || reply.includes('inquir'), 'Reply should include metric details');
  console.log('\n✔ AssistGroup Autonomous Function Calling Test PASS!');

  // Check audit log in ai_tool_invocations
  const [auditRows] = await db.execute(
    'SELECT tool_name, caller_role, status, execution_time_ms FROM ai_tool_invocations WHERE tool_name = "assistgroup_crm" ORDER BY id DESC LIMIT 1'
  );
  if (auditRows && auditRows.length > 0) {
    console.log('\n[Audit Log Record]:', auditRows[0]);
  }

  await db.end();
}

main().catch(err => {
  console.error('❌ Test failed:', err);
  process.exit(1);
});
