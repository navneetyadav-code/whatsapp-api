import mysql from 'mysql2/promise';
import { UniversalToolsRegistry } from './tools-engine.mjs';

async function testEndpoints() {
  const pool = mysql.createPool({ host: '127.0.0.1', user: 'root', password: '', database: 'whatsapp_api' });
  const tools = await UniversalToolsRegistry.loadUserTools(1, pool);

  const testActions = [
    { action: 'dashboard_system', desc: 'System Diagnostics & Server Info (Previously gave HTTP 500)' },
    { action: 'health', desc: 'Direct /health Ping Route' },
    { action: 'system_health', desc: 'System Health Check' },
    { action: 'stats', desc: 'Dashboard Aggregated Metrics' },
    { action: 'inquiries', desc: 'CRM Inquiries / Leads' },
    { action: 'users', desc: 'CRM Registered Users' }
  ];

  console.log('====================================================');
  console.log('🔍 LIVE PRODUCTION API HEALTH & HTTP 500 VERIFICATION');
  console.log('====================================================\n');

  let allPassed = true;

  for (const item of testActions) {
    const t0 = Date.now();
    try {
      const res = await UniversalToolsRegistry.executeTool('assistgroup_crm', { action: item.action }, tools, { callerRole: 'admin', userId: 1 });
      const elapsed = Date.now() - t0;
      const status = res.status || (res.success ? 200 : 500);

      if (status === 200 && res.success) {
        console.log(`✅ [${item.action}] -> HTTP 200 OK (${elapsed}ms)`);
        console.log(`   Description: ${item.desc}`);
        if (res.data) {
          console.log(`   Response summary:`, Object.keys(res.data));
        }
      } else {
        allPassed = false;
        console.log(`❌ [${item.action}] -> HTTP ${status} FAILED (${elapsed}ms)`);
        console.log(`   Error:`, res.error || JSON.stringify(res));
      }
    } catch (err) {
      allPassed = false;
      console.log(`❌ [${item.action}] -> Exception: ${err.message}`);
    }
    console.log('----------------------------------------------------');
  }

  await pool.end();

  if (allPassed) {
    console.log('\n🎉 ALL LIVE ENDPOINTS RETURNED HTTP 200 OK! ZERO HTTP 500 ERRORS!');
  } else {
    console.log('\n⚠️ Some endpoints did not return HTTP 200.');
  }
}

testEndpoints().catch(console.error);
