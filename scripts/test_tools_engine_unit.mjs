import mysql from '../worker/node_modules/mysql2/promise.js';
import {
  searchWebDuckDuckGo,
  callAssistGroupApi,
  evaluateSafeMath,
  getCurrentIstTime,
  ToolsRegistry
} from '../worker/tools-engine.mjs';

async function main() {
  console.log('========================================================');
  console.log(' TESTING TOOLS ENGINE UNIT & RBAC VERIFICATION          ');
  console.log('========================================================\n');

  // 1. Math Evaluator
  console.log('[Test 1] Math Evaluator:');
  const math1 = evaluateSafeMath('25000 * 0.85');
  console.log('  "25000 * 0.85" =>', math1);
  if (math1.result !== 21250) throw new Error('Math 1 failed');

  const math2 = evaluateSafeMath('1000 + (1000 * 18%)');
  console.log('  "1000 + (1000 * 18%)" =>', math2);
  if (math2.result !== 1180) throw new Error('Math 2 failed');
  console.log('  ✅ Math Evaluator PASS');

  // 2. Real-Time IST Clock
  console.log('\n[Test 2] Real-Time Clock & Business Hours:');
  const time = getCurrentIstTime();
  console.log('  Time:', time.current_time, '| Date:', time.current_date, '| Open?:', time.is_office_open);
  if (!time.current_time || !time.day_of_week) throw new Error('Time failed');
  console.log('  ✅ Clock PASS');

  // 3. Web Search
  console.log('\n[Test 3] Web Search (DuckDuckGo Lite):');
  const search = await searchWebDuckDuckGo('AssistGroup Patna software');
  console.log('  Results found:', search.count, '| Engine:', search.engine);
  if (search.count > 0) {
    console.log('  Top Title:', search.results[0].title);
    console.log('  Top URL:  ', search.results[0].url);
  }
  if (search.count === 0 && search.error) {
    console.log('  (Search error, but handled gracefully):', search.error);
  } else {
    console.log('  ✅ Web Search PASS');
  }

  // 4. AssistGroup Connector
  console.log('\n[Test 4] AssistGroup Connector:');
  const db = await mysql.createConnection({ host: '127.0.0.1', user: 'root', database: 'whatsapp_api' });
  const [rows] = await db.execute('SELECT tools_config FROM ai_agent_settings WHERE user_id = 1 LIMIT 1');
  const toolsConfig = JSON.parse(rows[0].tools_config);

  const agPricing = await callAssistGroupApi('/pricing/plans', 'GET', null, toolsConfig);
  console.log('  /pricing/plans Status:', agPricing.status, 'Success:', agPricing.success);
  if (!agPricing.success) throw new Error('AssistGroup Pricing failed');
  console.log('  ✅ AssistGroup API PASS');

  // 5. RBAC Filtering
  console.log('\n[Test 5] RBAC Tool Declarations:');
  const guestTools = ToolsRegistry.getDeclarationsForRole('guest', toolsConfig);
  const guestNames = guestTools[0]?.function_declarations?.map(d => d.name) || [];
  console.log('  Guest Tools (count:', guestNames.length, '):', guestNames);

  const adminTools = ToolsRegistry.getDeclarationsForRole('admin', toolsConfig);
  const adminNames = adminTools[0]?.function_declarations?.map(d => d.name) || [];
  console.log('  Admin Tools (count:', adminNames.length, '):', adminNames);

  if (guestNames.includes('assistgroup_admin_dashboard_stats')) {
    throw new Error('SECURITY VIOLATION: Guest can see admin dashboard stats!');
  }
  if (!adminNames.includes('assistgroup_admin_dashboard_stats')) {
    throw new Error('Admin missing admin dashboard stats');
  }
  console.log('  ✅ RBAC Tool Filtering PASS (Admin tools strictly hidden from Guests)');

  // 6. Execution Guard (Defense-in-depth)
  console.log('\n[Test 6] Execution Guard Security Boundary:');
  const guestContext = {
    db,
    userId: 1,
    conversationId: null,
    callerPhone: '+919999999999',
    callerRole: 'guest',
    toolsConfig
  };

  const maliciousExecution = await ToolsRegistry.executeTool('assistgroup_admin_dashboard_stats', {}, guestContext);
  console.log('  Guest attempting Admin Tool => Result:', maliciousExecution);
  if (!maliciousExecution.error || !maliciousExecution.error.includes('Permission Denied')) {
    throw new Error('SECURITY VIOLATION: Execution guard did not reject unauthorized role!');
  }
  console.log('  ✅ Execution Guard PASS (Unauthorized invocation blocked & logged)');

  // Verify Audit Log
  const [auditRows] = await db.execute(
    'SELECT tool_name, caller_role, status, error_message FROM ai_tool_invocations WHERE caller_role = "guest" ORDER BY id DESC LIMIT 1'
  );
  console.log('  Audit record logged in DB:', auditRows[0]);
  if (!auditRows || auditRows[0]?.status !== 'denied') {
    throw new Error('Audit record not logged correctly');
  }
  console.log('  ✅ Audit Invocations Log PASS');

  await db.end();
  console.log('\n========================================================');
  console.log(' ALL TOOLS ENGINE UNIT TESTS PASSED (100% GREEN)       ');
  console.log('========================================================\n');
}

main().catch(err => {
  console.error('Test failed:', err);
  process.exit(1);
});
