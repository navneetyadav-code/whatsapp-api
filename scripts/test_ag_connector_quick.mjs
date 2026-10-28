import mysql from '../worker/node_modules/mysql2/promise.js';

async function main() {
  const db = await mysql.createConnection({
    host: '127.0.0.1',
    user: 'root',
    password: '',
    database: 'whatsapp_api'
  });

  const [rows] = await db.execute('SELECT tools_config FROM ai_agent_settings WHERE user_id = 1 LIMIT 1');
  const cfg = JSON.parse(rows[0].tools_config);
  const token = cfg.tools.assistgroup_api.environments.local.bearer_token;
  const baseUrl = cfg.tools.assistgroup_api.environments.local.base_url;

  console.log('Testing AssistGroup Connector:');
  console.log('Base URL:', baseUrl);
  console.log('Token Prefix:', token.slice(0, 16));

  const authHeaders = {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  };

  // Test 1: Pricing Plans
  console.log('\nFetching /pricing/plans ...');
  const pRes = await fetch(`${baseUrl}/pricing/plans`, { headers: authHeaders });
  const pData = await pRes.json();
  console.log('Status:', pRes.status, 'Success:', pData.success, 'Count:', pData.data?.length || 0);
  if (pData.data?.length > 0) {
    console.log('Sample Plan:', pData.data[0].plan_name, 'Price:', pData.data[0].price);
  }

  // Test 2: Inquiries
  console.log('\nFetching /inquiries?limit=3 ...');
  const iRes = await fetch(`${baseUrl}/inquiries?limit=3`, { headers: authHeaders });
  const iData = await iRes.json();
  console.log('Status:', iRes.status, 'Success:', iData.success, 'Count:', iData.data?.length || 0);

  // Test 3: Dashboard Metrics
  console.log('\nFetching /dashboard/metrics ...');
  const dRes = await fetch(`${baseUrl}/dashboard/metrics`, { headers: authHeaders });
  const dData = await dRes.json();
  console.log('Status:', dRes.status, 'Success:', dData.success, 'Metrics:', dData.data?.users);

  await db.end();
}

main().catch(console.error);
