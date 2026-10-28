import mysql from '../worker/node_modules/mysql2/promise.js';
import crypto from 'crypto';

const BASE_URL = 'http://localhost/assistgroup/api/admin/v1';

async function main() {
  console.log('====================================================');
  console.log(' TESTING ASSISTGROUP ADMIN API PHASE 4 (INTEGRATION)');
  console.log('====================================================');

  const db = await mysql.createConnection({
    host: '127.0.0.1',
    user: 'root',
    password: '',
    database: 'assistgroup'
  });

  const [admins] = await db.query('SELECT id, Admin_Name, email FROM admin_login LIMIT 1');
  const admin = admins[0];

  const rawSecret = crypto.randomBytes(32).toString('hex');
  const token = `ast_live_${rawSecret}`;
  const tokenPrefix = token.substring(0, 16);
  const tokenHash = crypto.createHash('sha256').update(token).digest('hex');
  const expiresAt = new Date(Date.now() + 24 * 3600 * 1000).toISOString().slice(0, 19).replace('T', ' ');

  await db.execute(
    `INSERT INTO admin_api_tokens (admin_id, name, token_prefix, token_hash, scopes, expires_at)
     VALUES (?, ?, ?, ?, ?, ?)`,
    [admin.id, 'Phase 4 Test Runner', tokenPrefix, tokenHash, JSON.stringify(['*']), expiresAt]
  );

  const authHeaders = {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  };

  let passed = 0;
  let failed = 0;

  async function test(name, fn) {
    try {
      process.stdout.write(`[Test] ${name} ... `);
      await fn();
      console.log('✅ PASS');
      passed++;
    } catch (err) {
      console.log(`❌ FAIL\n  Error: ${err.message}`);
      failed++;
    }
  }

  // --------------------------------------------------------------------------
  // 1. TEAM TESTS
  // --------------------------------------------------------------------------
  await test('GET /team (roster listing)', async () => {
    const res = await fetch(`${BASE_URL}/team`, { headers: authHeaders });
    const json = await res.json();
    if (res.status !== 200 || !json.success) throw new Error(json.error || `HTTP ${res.status}`);
    if (!Array.isArray(json.data)) throw new Error('Expected array of team members');
  });

  let createdTeamId = null;
  await test('POST /team (create member)', async () => {
    const res = await fetch(`${BASE_URL}/team`, {
      method: 'POST',
      headers: authHeaders,
      body: JSON.stringify({
        name: 'Dr. Jane Cloud',
        role_title: 'Chief Security Officer',
        badge: 'Executive',
        bio: 'Distinguished cybersecurity architect.',
        email: 'jane.cloud@assistgroup.org',
        tech_stack: 'PHP, Go, MySQL, Docker',
        sort_order: 10
      })
    });
    const json = await res.json();
    if (res.status !== 201 || !json.success) throw new Error(json.error || `HTTP ${res.status}`);
    createdTeamId = json.data.id;
    if (!createdTeamId) throw new Error('Missing created team ID');
  });

  await test(`GET /team/${createdTeamId}`, async () => {
    const res = await fetch(`${BASE_URL}/team/${createdTeamId}`, { headers: authHeaders });
    const json = await res.json();
    if (res.status !== 200 || !json.success) throw new Error(json.error || `HTTP ${res.status}`);
    if (json.data.name !== 'Dr. Jane Cloud') throw new Error('Team member mismatch');
  });

  await test(`PUT /team/${createdTeamId} (update role)`, async () => {
    const res = await fetch(`${BASE_URL}/team/${createdTeamId}`, {
      method: 'PUT',
      headers: authHeaders,
      body: JSON.stringify({
        role_title: 'Head of Infrastructure & Security'
      })
    });
    const json = await res.json();
    if (res.status !== 200 || !json.success) throw new Error(json.error || `HTTP ${res.status}`);
    if (!json.data.role_title.includes('Head of Infrastructure')) throw new Error('Update failed');
  });

  await test(`PATCH /team/${createdTeamId}/status (toggle status)`, async () => {
    const res = await fetch(`${BASE_URL}/team/${createdTeamId}/status`, {
      method: 'PATCH',
      headers: authHeaders
    });
    const json = await res.json();
    if (res.status !== 200 || !json.success) throw new Error(json.error || `HTTP ${res.status}`);
    if (json.data.status !== 'hidden') throw new Error('Status not toggled to hidden');
  });

  await test(`DELETE /team/${createdTeamId}`, async () => {
    const res = await fetch(`${BASE_URL}/team/${createdTeamId}`, {
      method: 'DELETE',
      headers: authHeaders
    });
    const json = await res.json();
    if (res.status !== 200 || !json.success) throw new Error(json.error || `HTTP ${res.status}`);
  });

  // --------------------------------------------------------------------------
  // 2. SETTINGS TESTS
  // --------------------------------------------------------------------------
  await test('GET /settings (grouped settings)', async () => {
    const res = await fetch(`${BASE_URL}/settings`, { headers: authHeaders });
    const json = await res.json();
    if (res.status !== 200 || !json.success) throw new Error(json.error || `HTTP ${res.status}`);
    if (!json.data.groups || !json.data.settings) throw new Error('Invalid settings structure');
  });

  await test('POST /settings (batch update settings)', async () => {
    const res = await fetch(`${BASE_URL}/settings`, {
      method: 'POST',
      headers: authHeaders,
      body: JSON.stringify({
        group: 'system',
        settings: {
          test_api_flag: 'enabled',
          test_api_timestamp: String(Date.now())
        }
      })
    });
    const json = await res.json();
    if (res.status !== 200 || !json.success) throw new Error(json.error || `HTTP ${res.status}`);
    if (json.data.updated_count !== 2) throw new Error('Failed to update 2 keys');
  });

  await test('GET /settings/system/test_api_flag', async () => {
    const res = await fetch(`${BASE_URL}/settings/system/test_api_flag`, { headers: authHeaders });
    const json = await res.json();
    if (res.status !== 200 || !json.success) throw new Error(json.error || `HTTP ${res.status}`);
    if (json.data.setting_value !== 'enabled') throw new Error('Setting value mismatch');
  });

  // Clean test settings
  await db.execute('DELETE FROM site_settings WHERE setting_key IN ("test_api_flag", "test_api_timestamp")');

  // --------------------------------------------------------------------------
  // 3. CLOUDINARY STORAGE POOL TESTS
  // --------------------------------------------------------------------------
  await test('GET /cloudinary (pool status)', async () => {
    const res = await fetch(`${BASE_URL}/cloudinary`, { headers: authHeaders });
    const json = await res.json();
    if (res.status !== 200 || !json.success) throw new Error(json.error || `HTTP ${res.status}`);
    if (!json.data.pool_summary || !Array.isArray(json.data.accounts)) throw new Error('Invalid pool structure');
  });

  let createdCloudId = null;
  const testCloudName = `cld_test_${Date.now()}`;
  await test('POST /cloudinary (add storage account)', async () => {
    const res = await fetch(`${BASE_URL}/cloudinary`, {
      method: 'POST',
      headers: authHeaders,
      body: JSON.stringify({
        label: 'Automated Test Cloud',
        cloud_name: testCloudName,
        api_key: '123456789012345',
        api_secret: 'TopSecretCredentialsKey987',
        upload_preset: 'preset_test',
        is_active: 1,
        credits_total: 25.0
      })
    });
    const json = await res.json();
    if (res.status !== 201 || !json.success) throw new Error(json.error || `HTTP ${res.status}`);
    createdCloudId = json.data.id;
    if (!createdCloudId) throw new Error('Missing Cloudinary account ID');
  });

  await test(`GET /cloudinary/${createdCloudId}`, async () => {
    const res = await fetch(`${BASE_URL}/cloudinary/${createdCloudId}`, { headers: authHeaders });
    const json = await res.json();
    if (res.status !== 200 || !json.success) throw new Error(json.error || `HTTP ${res.status}`);
    if (json.data.cloud_name !== testCloudName) throw new Error('Cloud name mismatch');
    if (json.data.api_secret) throw new Error('Security flaw: Raw api_secret leaked!');
    if (!json.data.api_secret_masked) throw new Error('Missing masked secret');
  });

  await test(`PATCH /cloudinary/${createdCloudId}/status (toggle status)`, async () => {
    const res = await fetch(`${BASE_URL}/cloudinary/${createdCloudId}/status`, {
      method: 'PATCH',
      headers: authHeaders
    });
    const json = await res.json();
    if (res.status !== 200 || !json.success) throw new Error(json.error || `HTTP ${res.status}`);
    if (json.data.is_active !== false) throw new Error('Account was not deactivated');
  });

  await test(`DELETE /cloudinary/${createdCloudId}`, async () => {
    const res = await fetch(`${BASE_URL}/cloudinary/${createdCloudId}`, {
      method: 'DELETE',
      headers: authHeaders
    });
    const json = await res.json();
    if (res.status !== 200 || !json.success) throw new Error(json.error || `HTTP ${res.status}`);
  });

  // --------------------------------------------------------------------------
  // 4. AUDIT LOGS & GOVERNANCE TESTS
  // --------------------------------------------------------------------------
  await test('GET /audit-logs/summary (governance distribution)', async () => {
    const res = await fetch(`${BASE_URL}/audit-logs/summary`, { headers: authHeaders });
    const json = await res.json();
    if (res.status !== 200 || !json.success) throw new Error(json.error || `HTTP ${res.status}`);
    if (!Array.isArray(json.data.severities)) throw new Error('Severities array missing');
  });

  let sampleAuditId = null;
  await test('GET /audit-logs (paginated trail with actor & diff)', async () => {
    const res = await fetch(`${BASE_URL}/audit-logs?limit=5`, { headers: authHeaders });
    const json = await res.json();
    if (res.status !== 200 || !json.success) throw new Error(json.error || `HTTP ${res.status}`);
    if (!Array.isArray(json.data) || !json.meta?.pagination) throw new Error('Invalid audit list structure');
    if (json.data.length > 0) sampleAuditId = json.data[0].id;
  });

  if (sampleAuditId) {
    await test(`GET /audit-logs/${sampleAuditId} (inspect event)`, async () => {
      const res = await fetch(`${BASE_URL}/audit-logs/${sampleAuditId}`, { headers: authHeaders });
      const json = await res.json();
      if (res.status !== 200 || !json.success) throw new Error(json.error || `HTTP ${res.status}`);
      if (typeof json.data.event_type !== 'string') throw new Error('Invalid audit event structure');
    });
  }

  // Cleanup test runner token
  await db.execute('DELETE FROM admin_api_tokens WHERE name = "Phase 4 Test Runner"');
  await db.end();

  console.log('\n====================================================');
  console.log(` RESULTS: ${passed} Passed, ${failed} Failed`);
  console.log('====================================================');
}

main().catch(err => {
  console.error('Fatal test error:', err);
  process.exit(1);
});
