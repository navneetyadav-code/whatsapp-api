import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';

const workerSource = readFileSync(new URL('../worker/server.mjs', import.meta.url), 'utf8');
const dashboardPhp = readFileSync(new URL('../src/pages/dashboard.php', import.meta.url), 'utf8');
const indexPhp = readFileSync(new URL('../public/index.php', import.meta.url), 'utf8');
const canonicalSchemaSql = readFileSync(new URL('../schema.sql', import.meta.url), 'utf8');
const initialMigrationSql = readFileSync(new URL('../migrations/001_initial_schema.sql', import.meta.url), 'utf8');

test('Phase 3: Database schema and migrations contain auto_sleep_enabled and auto_sleep_timeout_seconds', () => {
  assert.match(canonicalSchemaSql, /`auto_sleep_enabled`\s+tinyint\(1\)/, 'schema.sql must have auto_sleep_enabled');
  assert.match(canonicalSchemaSql, /`auto_sleep_timeout_seconds`\s+int\(10\)/, 'schema.sql must have auto_sleep_timeout_seconds');
  assert.match(canonicalSchemaSql, /`last_activity_at`\s+timestamp/, 'schema.sql must have last_activity_at');

  assert.match(initialMigrationSql, /`auto_sleep_enabled`\s+tinyint\(1\)/, '001_initial_schema.sql must have auto_sleep_enabled');
  assert.match(initialMigrationSql, /`auto_sleep_timeout_seconds`\s+int\(10\)/, '001_initial_schema.sql must have auto_sleep_timeout_seconds');
});

test('Phase 3: UI provides enable toggle, customizable timeout, and required operational disclaimers', () => {
  assert.match(dashboardPhp, /name="auto_sleep_enabled"/, 'Dashboard contains enable toggle');
  assert.match(dashboardPhp, /name="auto_sleep_timeout_seconds"/, 'Dashboard contains customizable idle timeout');
  assert.match(dashboardPhp, /min="15"/, 'Dashboard enforces minimum bound in HTML');
  assert.match(dashboardPhp, /max="86400"/, 'Dashboard enforces maximum bound in HTML');
  assert.match(dashboardPhp, /Inbound & Chatbot/, 'Dashboard warns that inbound processing and chatbot responses are paused during sleep');
  assert.match(dashboardPhp, /anti-ban guarantee/, 'Dashboard clarifies auto-sleep is not an anti-ban guarantee');
  assert.match(dashboardPhp, /Operational Independence/, 'Dashboard clarifies auto-sleep is separate from business hours and campaign scheduling');
});

test('Phase 3: Backend handler validates bounds and updates database and worker', () => {
  assert.match(indexPhp, /max\(15,\s*min\(86400,\s*\$rawTimeout\)\)/, 'Backend validates timeout within 15s to 86400s');
  assert.match(indexPhp, /UPDATE whatsapp_sessions\s+SET auto_sleep_enabled = \?,\s*auto_sleep_timeout_seconds = \?/, 'Backend updates database');
  assert.match(indexPhp, /worker_request\('POST',\s*"sessions\/\{\$user\['id'\]\}\/auto-sleep"/, 'Backend notifies worker of configuration changes');
});

test('Phase 3: Worker enforces tenant-isolated auto-sleep, checks activeOperations, and respects enabled toggle', () => {
  assert.match(workerSource, /if \(!session\.autoSleepEnabled\) \{\s*continue;/, 'Disabled auto-sleep does not disconnect inactive session');
  assert.match(workerSource, /if \(\(session\.activeOperations \|\| 0\) > 0\) \{\s*continue;/, 'Active operations (sends) prevent auto-sleep disconnection');
  assert.match(workerSource, /Math\.max\(15,\s*Math\.min\(86400,\s*Number\(session\.autoSleepTimeoutSeconds\)/, 'Worker enforces timeout bounds');
  assert.match(workerSource, /app\.post\('\/sessions\/:userId\/auto-sleep'/, 'Worker exposes auto-sleep configuration route');
  assert.match(workerSource, /session\.activeOperations = \(session\.activeOperations \|\| 0\) \+ 1;/, 'Outgoing message increments activeOperations counter');
  assert.match(workerSource, /session\.activeOperations = Math\.max\(0, \(session\.activeOperations \|\| 1\) - 1\);/, 'Outgoing message completion decrements activeOperations counter');
});

test('Phase 3: Activity resets idle timer and state updates are accurate', () => {
  assert.match(workerSource, /session\.lastActivityAt = Date\.now\(\);/, 'Qualifying activity resets lastActivityAt');
  assert.match(workerSource, /session\.state = session\.phone \? 'connected' : 'idle';/, 'Accurate state on sleep transition');
  assert.match(workerSource, /session\.sleeping = true;/, 'Session state explicitly marked sleeping');
});
