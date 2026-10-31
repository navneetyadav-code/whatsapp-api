import mysql from '../worker/node_modules/mysql2/promise.js';
import crypto from 'crypto';

const BASE_URL = 'http://localhost/assistgroup/api/admin/v1';

async function main() {
  console.log('----------------------------------------------------');
  console.log(' TESTING ASSISTGROUP ADMIN API PHASE 1 GATEWAY      ');
  console.log('----------------------------------------------------');

  // Test 1: GET /health
  console.log('\n[Test 1] GET /health ...');
  try {
    const res1 = await fetch(`${BASE_URL}/health`);
    const data1 = await res1.json();
    console.log(`Status: ${res1.status}`);
    console.log('Response:', JSON.stringify(data1, null, 2));
    if (res1.status === 200 && data1.success) {
      console.log('✅ PASS: /health is operational');
    } else {
      console.error('❌ FAIL: /health failed');
    }
  } catch (err) {
    console.error('❌ FAIL: /health exception:', err.message);
  }

  // Test 2: GET /auth/me without authorization header
  console.log('\n[Test 2] GET /auth/me without token (Expecting 401) ...');
  try {
    const res2 = await fetch(`${BASE_URL}/auth/me`);
    const data2 = await res2.json();
    console.log(`Status: ${res2.status}`);
    console.log('Response:', JSON.stringify(data2, null, 2));
    if (res2.status === 401 && !data2.success) {
      console.log('✅ PASS: Unauthorized access blocked with 401');
    } else {
      console.error('❌ FAIL: /auth/me did not return 401');
    }
  } catch (err) {
    console.error('❌ FAIL: /auth/me exception:', err.message);
  }

  // Test 3: Create a test Bearer token directly in DB to verify Bearer authentication pipeline
  console.log('\n[Test 3] Inserting temporary API token in DB to test Bearer auth ...');
  const db = await mysql.createConnection({
    host: '127.0.0.1',
    user: 'root',
    password: '',
    database: 'assistgroup'
  });

  const [admins] = await db.query('SELECT id, Admin_Name, email FROM admin_login LIMIT 1');
  if (admins.length === 0) {
    console.log('⚠️ No admin found in admin_login, skipping Bearer authenticated test');
    await db.end();
    return;
  }
  const admin = admins[0];
  console.log(`Found Admin: ID ${admin.id} (${admin.Admin_Name})`);

  const rawSecret = crypto.randomBytes(32).toString('hex');
  const token = `ast_live_${rawSecret}`;
  const tokenPrefix = token.substring(0, 16);
  const tokenHash = crypto.createHash('sha256').update(token).digest('hex');

  const expiresAt = new Date(Date.now() + 30 * 24 * 3600 * 1000).toISOString().slice(0, 19).replace('T', ' ');

  const [insertResult] = await db.execute(
    `INSERT INTO admin_api_tokens (admin_id, name, token_prefix, token_hash, scopes, expires_at)
     VALUES (?, ?, ?, ?, ?, ?)`,
    [admin.id, 'Automated Test Token', tokenPrefix, tokenHash, JSON.stringify(['*']), expiresAt]
  );
  const tokenId = insertResult.insertId;
  console.log(`Generated Bearer Token: ${tokenPrefix}... (ID: ${tokenId})`);

  // Test 4: GET /auth/me with Bearer token
  console.log('\n[Test 4] GET /auth/me with Bearer Token ...');
  try {
    const res4 = await fetch(`${BASE_URL}/auth/me`, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });
    const data4 = await res4.json();
    console.log(`Status: ${res4.status}`);
    console.log('Response:', JSON.stringify(data4, null, 2));
    if (res4.status === 200 && data4.success && data4.data?.admin?.id === admin.id) {
      console.log('✅ PASS: Bearer authentication successfully resolved admin profile');
    } else {
      console.error('❌ FAIL: Bearer auth failed on /auth/me');
    }
  } catch (err) {
    console.error('❌ FAIL: /auth/me Bearer exception:', err.message);
  }

  // Test 5: GET /auth/tokens with Bearer token
  console.log('\n[Test 5] GET /auth/tokens with Bearer Token ...');
  try {
    const res5 = await fetch(`${BASE_URL}/auth/tokens`, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });
    const data5 = await res5.json();
    console.log(`Status: ${res5.status}`);
    console.log('Response:', JSON.stringify(data5, null, 2));
    if (res5.status === 200 && data5.success && Array.isArray(data5.data)) {
      console.log(`✅ PASS: Listed ${data5.data.length} tokens successfully`);
    } else {
      console.error('❌ FAIL: /auth/tokens listing failed');
    }
  } catch (err) {
    console.error('❌ FAIL: /auth/tokens exception:', err.message);
  }

  // Test 6: POST /auth/tokens to create a new token via API
  console.log('\n[Test 6] POST /auth/tokens (create new token via API) ...');
  let createdApiTokenId = null;
  try {
    const res6 = await fetch(`${BASE_URL}/auth/tokens`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        name: 'API Created Key',
        scopes: ['users:read', 'dashboard:read'],
        expires_in_days: 7
      })
    });
    const data6 = await res6.json();
    console.log(`Status: ${res6.status}`);
    console.log('Response:', JSON.stringify(data6, null, 2));
    if (res6.status === 201 && data6.success && data6.data?.token) {
      // Find created token ID from DB or list
      const [tokenRow] = await db.query('SELECT id FROM admin_api_tokens WHERE name = "API Created Key" ORDER BY id DESC LIMIT 1');
      if (tokenRow.length > 0) createdApiTokenId = tokenRow[0].id;
      console.log(`✅ PASS: Successfully created new API token via API endpoint (ID: ${createdApiTokenId})`);
    } else {
      console.error('❌ FAIL: /auth/tokens creation failed');
    }
  } catch (err) {
    console.error('❌ FAIL: /auth/tokens creation exception:', err.message);
  }

  // Test 7: DELETE /auth/tokens/:id to revoke token
  if (createdApiTokenId) {
    console.log(`\n[Test 7] DELETE /auth/tokens/${createdApiTokenId} (revoke token) ...`);
    try {
      const res7 = await fetch(`${BASE_URL}/auth/tokens/${createdApiTokenId}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });
      const data7 = await res7.json();
      console.log(`Status: ${res7.status}`);
      console.log('Response:', JSON.stringify(data7, null, 2));
      if (res7.status === 200 && data7.success) {
        console.log('✅ PASS: Successfully revoked token via API endpoint');
      } else {
        console.error('❌ FAIL: Token revocation failed');
      }
    } catch (err) {
      console.error('❌ FAIL: Token revocation exception:', err.message);
    }
  }

  // Cleanup test tokens
  console.log('\n[Cleanup] Removing test tokens from DB ...');
  await db.execute('DELETE FROM admin_api_tokens WHERE name IN (?, ?)', [
    'Automated Test Token',
    'API Created Key'
  ]);
  console.log('✅ Cleaned up temporary test tokens');

  await db.end();
  console.log('\n====================================================');
  console.log(' PHASE 1 GATEWAY & AUTH VERIFICATION COMPLETE       ');
  console.log('====================================================');
}

main().catch(err => {
  console.error('Fatal error running tests:', err);
  process.exit(1);
});
