import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';

const migration = readFileSync(new URL('../migrations/001_initial_schema.sql', import.meta.url), 'utf8');
const entrypoint = readFileSync(new URL('../docker/entrypoint.sh', import.meta.url), 'utf8');
const worker = readFileSync(new URL('../worker/server.mjs', import.meta.url), 'utf8');
const workerClient = readFileSync(new URL('../src/lib/WorkerClient.php', import.meta.url), 'utf8');
const healthEndpoint = readFileSync(new URL('../public/api/health.php', import.meta.url), 'utf8');

const requiredTables = [
  'users', 'api_keys', 'api_request_logs', 'campaigns', 'chatbot_rules', 'contacts',
  'login_attempts', 'message_logs', 'message_queue', 'password_resets',
  'webhooks', 'webhook_deliveries', 'whatsapp_sessions'
];

test('repeatable initial migration creates every current application table without destructive SQL', () => {
  for (const table of requiredTables) {
    assert.ok(migration.includes('CREATE TABLE IF NOT EXISTS `' + table + '`'), `missing table ${table}`);
  }
  const sqlWithoutComments = migration.replace(/^\s*--.*$/gm, '');
  assert.doesNotMatch(sqlWithoutComments, /\b(DROP|TRUNCATE|DELETE FROM|INSERT INTO)\b/i);
  assert.match(migration, /idx_message_queue_worker/);
  assert.match(migration, /idx_message_queue_user_created/);
  assert.match(migration, /idx_message_queue_api_key_created/);
});

test('container migration runs before Supervisor can start workers', () => {
  assert.ok(entrypoint.indexOf('php /var/www/html/scripts/migrate.php') < entrypoint.indexOf('exec /usr/bin/supervisord'));
  assert.match(entrypoint, /^set -e/m);
});

test('TiDB TLS verification is enabled and failed schema startup prevents queue polling', () => {
  assert.match(worker, /rejectUnauthorized:\s*true/);
  assert.doesNotMatch(worker, /rejectUnauthorized:\s*false/);
  assert.match(worker, /Worker startup blocked; queue processor was not started/);
  assert.match(worker, /setInterval\(processQueueOnce, QUEUE_POLL_MS\)/);
});

test('worker health contract uses the actual listener port and requires database readiness', () => {
  assert.match(entrypoint, /WORKER_URL="http:\/\/127\.0\.0\.1:\$\{WORKER_PORT\}"/);
  assert.match(entrypoint, /PassEnv .*WORKER_URL WORKER_PORT WORKER_TOKEN/);
  assert.match(worker, /const PORT = Number\(process\.env\.WORKER_PORT \|\| 3107\)/);
  assert.match(worker, /app\.listen\(PORT, '127\.0\.0\.1'/);
  assert.match(worker, /app\.get\('\/health', async/);
  assert.match(workerClient, /function worker_health_ready\(array \$response\): bool/);
  assert.match(workerClient, /\$response\['status_code'\].*=== 200/s);
  assert.match(workerClient, /\$database\['connected'\].*=== true/s);
  assert.match(workerClient, /\$database\['schema_ready'\].*=== true/s);
  assert.match(workerClient, /function worker_request_url\(string \$baseUrl, string \$path\): string/);
  assert.match(workerClient, /function worker_decode_response\(string \$raw, int \$status\): array/);
  assert.match(workerClient, /worker_safe_response_excerpt\(\$raw\)/);
  assert.match(workerClient, /worker_loopback_proxy_override\(\$url\)/);
  assert.match(workerClient, /CURLINFO_CONTENT_TYPE/);
  assert.match(workerClient, /CURLINFO_PRIMARY_IP/);
  assert.match(worker, /database_not_ready/);
  assert.match(worker, /health_route=GET \/health/);
  assert.match(healthEndpoint, /worker_health_ready\(\$worker\)/);
  assert.match(worker, /if \(req\.get\('X-Worker-Token'\) !== WORKER_TOKEN\)/);
  assert.match(worker, /attempts >= max_attempts/);
  assert.match(worker, /attempts < max_attempts/);
  assert.match(worker, /Worker lease expired after the final delivery attempt/);
  assert.match(worker, /Expired worker leases returned to the retry queue/);
});
