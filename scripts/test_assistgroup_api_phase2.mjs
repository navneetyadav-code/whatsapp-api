import mysql from '../worker/node_modules/mysql2/promise.js';
import crypto from 'crypto';

const BASE_URL = 'http://localhost/assistgroup/api/admin/v1';

async function main() {
  console.log('====================================================');
  console.log(' TESTING ASSISTGROUP ADMIN API PHASE 2 (CORE)       ');
  console.log('====================================================');

  const db = await mysql.createConnection({
    host: '127.0.0.1',
    user: 'root',
    password: '',
    database: 'assistgroup'
  });

  // Setup a full-access test Bearer token
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
    [admin.id, 'Phase 2 Test Runner', tokenPrefix, tokenHash, JSON.stringify(['*']), expiresAt]
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

  // 1. Dashboard Metrics
  await test('GET /dashboard/metrics', async () => {
    const res = await fetch(`${BASE_URL}/dashboard/metrics`, { headers: authHeaders });
    const json = await res.json();
    if (res.status !== 200 || !json.success) throw new Error(json.error || `HTTP ${res.status}`);
    if (typeof json.data.users.total !== 'number' || typeof json.data.files.total_files !== 'number') {
      throw new Error('Invalid metrics response structure');
    }
  });

  // 2. Dashboard Growth
  await test('GET /dashboard/growth?days=14', async () => {
    const res = await fetch(`${BASE_URL}/dashboard/growth?days=14`, { headers: authHeaders });
    const json = await res.json();
    if (res.status !== 200 || !json.success) throw new Error(json.error || `HTTP ${res.status}`);
    if (!Array.isArray(json.data.user_registrations)) throw new Error('Growth array missing');
  });

  // 3. Dashboard System
  await test('GET /dashboard/system', async () => {
    const res = await fetch(`${BASE_URL}/dashboard/system`, { headers: authHeaders });
    const json = await res.json();
    if (res.status !== 200 || !json.success) throw new Error(json.error || `HTTP ${res.status}`);
    if (!json.data.environment?.php_version || typeof json.data.resources?.disk?.free_bytes !== 'number') {
      throw new Error('Invalid system info structure');
    }
  });

  // 4. Users List
  await test('GET /users (pagination & metadata)', async () => {
    const res = await fetch(`${BASE_URL}/users?limit=5`, { headers: authHeaders });
    const json = await res.json();
    if (res.status !== 200 || !json.success) throw new Error(json.error || `HTTP ${res.status}`);
    if (!Array.isArray(json.data) || !json.meta?.pagination) throw new Error('Invalid user list structure');
  });

  // 5. Create Test User
  let createdUserId = null;
  const testUsername = `testuser_${Date.now()}`;
  const testEmail = `${testUsername}@assisttest.org`;
  const testPhone = '9' + String(Date.now()).slice(-9);

  await test('POST /users (create new user)', async () => {
    const res = await fetch(`${BASE_URL}/users`, {
      method: 'POST',
      headers: authHeaders,
      body: JSON.stringify({
        username: testUsername,
        email: testEmail,
        password: 'SecurePassword123!',
        name: 'Automated Tester',
        fname: 'Automated',
        lname: 'Tester',
        mobnumber: testPhone,
        plan: 'Pro'
      })
    });
    const json = await res.json();
    if (res.status !== 201 || !json.success) throw new Error(json.error || `HTTP ${res.status}: ${JSON.stringify(json)}`);
    createdUserId = json.data.id;
    if (!createdUserId) throw new Error('Missing created user ID');
  });

  // 6. Inspect Created User
  await test(`GET /users/${createdUserId}`, async () => {
    if (!createdUserId) throw new Error('Cannot run: user creation failed');
    const res = await fetch(`${BASE_URL}/users/${createdUserId}`, { headers: authHeaders });
    const json = await res.json();
    if (res.status !== 200 || !json.success) throw new Error(json.error || `HTTP ${res.status}`);
    if (json.data.email !== testEmail || !json.data.usage) throw new Error('User details mismatch');
  });

  // 7. Update User Profile
  await test(`PUT /users/${createdUserId}`, async () => {
    if (!createdUserId) throw new Error('Cannot run: user creation failed');
    const updatePhone = '8' + String(Date.now()).slice(-9);
    const res = await fetch(`${BASE_URL}/users/${createdUserId}`, {
      method: 'PUT',
      headers: authHeaders,
      body: JSON.stringify({
        name: 'Updated Tester Name',
        mobnumber: updatePhone
      })
    });
    const json = await res.json();
    if (res.status !== 200 || !json.success) throw new Error(json.error || `HTTP ${res.status}`);
    if (json.data.name !== 'Updated Tester Name') throw new Error('Profile was not updated');
  });

  // 8. Toggle User Status
  await test(`PATCH /users/${createdUserId}/status (suspend)`, async () => {
    const res = await fetch(`${BASE_URL}/users/${createdUserId}/status`, {
      method: 'PATCH',
      headers: authHeaders,
      body: JSON.stringify({
        status: 'suspended',
        reason: 'Automated policy test violation'
      })
    });
    const json = await res.json();
    if (res.status !== 200 || !json.success) throw new Error(json.error || `HTTP ${res.status}`);
    if (json.data.status !== 'suspended') throw new Error('Status not set to suspended');
  });

  // 9. Change Plan
  await test(`PATCH /users/${createdUserId}/plan`, async () => {
    const res = await fetch(`${BASE_URL}/users/${createdUserId}/plan`, {
      method: 'PATCH',
      headers: authHeaders,
      body: JSON.stringify({
        plan: 'Enterprise',
        duration_days: 60
      })
    });
    const json = await res.json();
    if (res.status !== 200 || !json.success) throw new Error(json.error || `HTTP ${res.status}`);
    if (json.data.current_plan !== 'Enterprise') throw new Error('Plan tier was not updated');
  });

  // 10. Unlock Account
  await test(`POST /users/${createdUserId}/unlock`, async () => {
    const res = await fetch(`${BASE_URL}/users/${createdUserId}/unlock`, {
      method: 'POST',
      headers: authHeaders
    });
    const json = await res.json();
    if (res.status !== 200 || !json.success) throw new Error(json.error || `HTTP ${res.status}`);
    if (json.data.is_locked !== false) throw new Error('Unlock did not clear lockout flag');
  });

  // 11. Reset Password
  await test(`POST /users/${createdUserId}/reset-password`, async () => {
    const res = await fetch(`${BASE_URL}/users/${createdUserId}/reset-password`, {
      method: 'POST',
      headers: authHeaders,
      body: JSON.stringify({
        require_change: true
      })
    });
    const json = await res.json();
    if (res.status !== 200 || !json.success) throw new Error(json.error || `HTTP ${res.status}`);
    if (!json.data.temporary_password || json.data.force_password_change !== true) {
      throw new Error('Temporary password not generated');
    }
  });

  // 12. Generate Rescue Code
  await test(`POST /users/${createdUserId}/rescue-code`, async () => {
    const res = await fetch(`${BASE_URL}/users/${createdUserId}/rescue-code`, {
      method: 'POST',
      headers: authHeaders
    });
    const json = await res.json();
    if (res.status !== 200 || !json.success) throw new Error(json.error || `HTTP ${res.status}`);
    if (!json.data.rescue_code?.startsWith('AG-')) throw new Error('Invalid rescue code pattern');
  });

  // 13. Reset 2FA
  await test(`POST /users/${createdUserId}/reset-2fa`, async () => {
    const res = await fetch(`${BASE_URL}/users/${createdUserId}/reset-2fa`, {
      method: 'POST',
      headers: authHeaders
    });
    const json = await res.json();
    if (res.status !== 200 || !json.success) throw new Error(json.error || `HTTP ${res.status}`);
    if (json.data.two_factor_enabled !== false) throw new Error('2FA was not disabled');
  });

  // 14. Soft Delete (Move to Trash)
  await test(`DELETE /users/${createdUserId} (soft delete)`, async () => {
    const res = await fetch(`${BASE_URL}/users/${createdUserId}`, {
      method: 'DELETE',
      headers: authHeaders
    });
    const json = await res.json();
    if (res.status !== 200 || !json.success) throw new Error(json.error || `HTTP ${res.status}`);
    if (json.data.status !== 'trash') throw new Error('User not marked as trash');
  });

  // 15. Restore User
  await test(`POST /users/${createdUserId}/restore`, async () => {
    const res = await fetch(`${BASE_URL}/users/${createdUserId}/restore`, {
      method: 'POST',
      headers: authHeaders
    });
    const json = await res.json();
    if (res.status !== 200 || !json.success) throw new Error(json.error || `HTTP ${res.status}`);
    if (json.data.status !== 'active') throw new Error('User was not restored to active');
  });

  // 16. Permanent GDPR Purge
  await test(`DELETE /users/${createdUserId}/purge (GDPR hard delete)`, async () => {
    const res = await fetch(`${BASE_URL}/users/${createdUserId}/purge`, {
      method: 'DELETE',
      headers: authHeaders
    });
    const json = await res.json();
    if (res.status !== 200 || !json.success) throw new Error(json.error || `HTTP ${res.status}`);

    // Verify row is physically gone from DB
    const [check] = await db.query('SELECT id FROM loginuser WHERE id = ?', [createdUserId]);
    if (check.length > 0) throw new Error('User row still exists after permanent purge');
  });

  // 17. Verify Audit Log was created
  await test('Verify audit_logs table recorded operations', async () => {
    const [logs] = await db.query(
      'SELECT id, event_type, description FROM audit_logs WHERE target_id = ? ORDER BY id DESC LIMIT 5',
      [createdUserId]
    );
    if (logs.length === 0) throw new Error('No audit logs found for test user operations');
  });

  // Cleanup test runner token
  await db.execute('DELETE FROM admin_api_tokens WHERE name = "Phase 2 Test Runner"');
  await db.end();

  console.log('\n====================================================');
  console.log(` RESULTS: ${passed} Passed, ${failed} Failed`);
  console.log('====================================================');
}

main().catch(err => {
  console.error('Fatal test error:', err);
  process.exit(1);
});
