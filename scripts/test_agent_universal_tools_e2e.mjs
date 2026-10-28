import mysql from '../worker/node_modules/mysql2/promise.js';
import { generateAiReply } from '../worker/ai-agent.mjs';

async function main() {
  console.log('========================================================');
  console.log(' E2E VERIFICATION: UNIVERSAL CUSTOM TOOLS & WEATHER API ');
  console.log('========================================================\n');

  const db = await mysql.createConnection({
    host: '127.0.0.1',
    user: 'root',
    password: '',
    database: 'whatsapp_api'
  });

  const userId = 1;

  // Fetch settings
  const [settingsRows] = await db.execute('SELECT * FROM ai_agent_settings WHERE user_id = ? LIMIT 1', [userId]);
  const aiSettings = settingsRows[0];

  const systemPrompt = `You are an AI assistant for our company on WhatsApp. You have access to external tools including live weather forecasts for any city, business hours, and precise math calculation. Always use your available tools when customers ask about them.`;

  const toolsContext = {
    db,
    userId,
    conversationId: null,
    callerPhone: '+919999999999',
    callerRole: 'guest'
  };

  // Test 1: Custom Weather API Tool (Open-Meteo REST API)
  console.log('[E2E Test 1] Customer asks about live weather in Patna:');
  const userMsg1 = 'What is the current weather and temperature in Patna right now? Please check.';
  const reply1 = await generateAiReply(aiSettings, systemPrompt, userMsg1, [], toolsContext);
  console.log('  Customer:', userMsg1);
  console.log('  AI Reply:\n', reply1);

  if (reply1.toLowerCase().includes('patna') && (reply1.includes('°C') || reply1.toLowerCase().includes('degree') || reply1.toLowerCase().includes('temperature'))) {
    console.log('  ✅ Live Custom Weather API E2E test PASS\n');
  } else {
    console.log('  ⚠️ Weather reply received:', reply1.slice(0, 150));
  }

  // Test 2: Math Calculation Tool
  console.log('[E2E Test 2] Precise Math calculation via evaluate_math:');
  const userMsg2 = 'Calculate 50,000 rupees minus a 25% discount.';
  const reply2 = await generateAiReply(aiSettings, systemPrompt, userMsg2, [], toolsContext);
  console.log('  Customer:', userMsg2);
  console.log('  AI Reply:\n', reply2);

  if (reply2.includes('37,500') || reply2.includes('37500')) {
    console.log('  ✅ Math calculation test PASS\n');
  } else {
    console.log('  ⚠️ Math test output:', reply2.slice(0, 150));
  }

  // Test 3: Audit Log Table
  console.log('[E2E Test 3] Checking ai_tool_invocations audit table:');
  const [invs] = await db.execute(
    'SELECT tool_name, caller_role, execution_time_ms, status FROM ai_tool_invocations WHERE user_id = ? ORDER BY id DESC LIMIT 5',
    [userId]
  );
  console.table(invs);

  await db.end();
  console.log('========================================================');
  console.log(' ALL UNIVERSAL CUSTOM TOOLS TESTS COMPLETE              ');
  console.log('========================================================\n');
}

main().catch(console.error);
