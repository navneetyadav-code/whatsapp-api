import mysql from '../worker/node_modules/mysql2/promise.js';
import assert from 'assert';
import { UniversalToolsRegistry } from '../worker/tools-engine.mjs';

async function main() {
  console.log('🧪 Testing AssistGroup Tool Execution via Universal Engine...\n');

  const db = await mysql.createConnection({
    host: '127.0.0.1',
    user: 'root',
    password: '',
    database: 'whatsapp_api'
  });

  const userId = 1;
  const tools = await UniversalToolsRegistry.loadUserTools(userId, db);
  const agTool = tools.find(t => t.tool_slug === 'assistgroup_crm');

  assert(agTool, 'assistgroup_crm tool must be loaded');
  console.log(`[Loaded Tool] ${agTool.display_name} (${agTool.tool_slug})`);

  const context = {
    userId,
    db,
    callerRole: 'admin',
    agentMode: 'personal',
    ownerName: 'Boss'
  };

  // Test 1: Dashboard Metrics
  console.log('\n[Test 1] Executing action: dashboard_metrics ...');
  const res1 = await UniversalToolsRegistry.executeTool(
    'assistgroup_crm',
    { action: 'dashboard_metrics' },
    tools,
    context
  );
  assert(res1.success, `dashboard_metrics failed: ${JSON.stringify(res1)}`);
  console.log('  Metrics Data Preview:', {
    users: res1.data.users,
    inquiries: res1.data.inquiries,
    files: res1.data.files
  });
  console.log('  ✔ dashboard_metrics PASS');

  // Test 2: Pricing Plans
  console.log('\n[Test 2] Executing action: pricing_plans ...');
  const res2 = await UniversalToolsRegistry.executeTool(
    'assistgroup_crm',
    { action: 'pricing_plans' },
    tools,
    context
  );
  assert(res2.success, `pricing_plans failed: ${JSON.stringify(res2)}`);
  console.log(`  Found ${res2.data.plans?.length || 0} pricing plans.`);
  console.log('  ✔ pricing_plans PASS');

  // Test 3: Team Overview
  console.log('\n[Test 3] Executing action: team_overview ...');
  const res3 = await UniversalToolsRegistry.executeTool(
    'assistgroup_crm',
    { action: 'team_overview' },
    tools,
    context
  );
  assert(res3.success, `team_overview failed: ${JSON.stringify(res3)}`);
  console.log(`  Found ${res3.data.team?.length || 0} team members.`);
  console.log('  ✔ team_overview PASS');

  // Test 4: Inquiries List
  console.log('\n[Test 4] Executing action: list_inquiries ...');
  const res4 = await UniversalToolsRegistry.executeTool(
    'assistgroup_crm',
    { action: 'list_inquiries' },
    tools,
    context
  );
  assert(res4.success, `list_inquiries failed: ${JSON.stringify(res4)}`);
  console.log(`  Found ${res4.data.inquiries?.length || 0} inquiries.`);
  console.log('  ✔ list_inquiries PASS');

  // Test 5: System Health
  console.log('\n[Test 5] Executing action: dashboard_system ...');
  const res5 = await UniversalToolsRegistry.executeTool(
    'assistgroup_crm',
    { action: 'dashboard_system' },
    tools,
    context
  );
  assert(res5.success, `dashboard_system failed: ${JSON.stringify(res5)}`);
  console.log('  System Status:', res5.data.system?.status || 'OK');
  console.log('  ✔ dashboard_system PASS');

  await db.end();
  console.log('\n🎉 ALL 5 ASSISTGROUP API TOOL TESTS PASSED!');
}

main().catch(err => {
  console.error('❌ Test failed:', err);
  process.exit(1);
});
