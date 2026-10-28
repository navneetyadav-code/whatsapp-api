import mysql from '../worker/node_modules/mysql2/promise.js';
import { UniversalToolsRegistry } from '../worker/tools-engine.mjs';

async function runTest() {
  console.log('========================================================');
  console.log(' TESTING UNIVERSAL DYNAMIC TOOLS ENGINE                 ');
  console.log('========================================================\n');

  const db = await mysql.createConnection({
    host: '127.0.0.1',
    user: 'root',
    password: '',
    database: 'whatsapp_api'
  });

  const userId = 1;

  // 1. Load tools
  const tools = await UniversalToolsRegistry.loadUserTools(userId, db);
  console.log(`[Test 1] Loaded ${tools.length} user tools from DB:`);
  for (const t of tools) {
    console.log(`   - ${t.tool_slug.padEnd(20)} [${t.category.padEnd(14)}] roles: ${t.rbac_roles.join(', ')}`);
  }

  // 2. Compile declarations
  const guestDecls = UniversalToolsRegistry.compileDeclarationsForRole(tools, 'guest');
  console.log(`\n[Test 2] Compiled ${guestDecls.length} tool declarations for 'guest' role.`);

  // 3. Execute Custom REST API: Live Weather (Open-Meteo)
  console.log('\n[Test 3] Executing Custom REST API: get_weather for Patna...');
  const weatherContext = { db, userId, callerRole: 'guest', callerPhone: '+919999999999' };
  const weatherRes = await UniversalToolsRegistry.executeTool(
    'get_weather',
    { city: 'Patna', latitude: 25.5941, longitude: 85.1376 },
    tools,
    weatherContext
  );
  console.log('Weather API Result:', JSON.stringify(weatherRes, null, 2));

  if (weatherRes.success && weatherRes.data?.temperature_2m !== undefined) {
    console.log(`✅ Live Weather API PASS: Temperature in Patna is ${weatherRes.data.temperature_2m}°C, wind: ${weatherRes.data.wind_speed_10m} km/h`);
  } else {
    console.error('❌ Weather API failed:', weatherRes);
  }

  // 4. Execute Built-in Math
  console.log('\n[Test 4] Executing evaluate_math...');
  const mathRes = await UniversalToolsRegistry.executeTool(
    'evaluate_math',
    { expression: '45000 - (45000 * 20%)' },
    tools,
    weatherContext
  );
  console.log('Math Result:', mathRes);
  if (mathRes.success && mathRes.result === 36000) {
    console.log('✅ evaluate_math PASS: 45000 - 20% = ₹36,000');
  }

  // 5. Verify Audit Log
  const [invLogs] = await db.execute(
    'SELECT tool_name, caller_role, execution_time_ms, status FROM ai_tool_invocations WHERE user_id = ? ORDER BY id DESC LIMIT 2',
    [userId]
  );
  console.log('\n[Test 5] Recent Audit Logs:');
  console.table(invLogs);

  await db.end();
  console.log('\n========================================================');
  console.log(' UNIVERSAL DYNAMIC TOOLS UNIT TESTS COMPLETE            ');
  console.log('========================================================');
}

runTest().catch(console.error);
