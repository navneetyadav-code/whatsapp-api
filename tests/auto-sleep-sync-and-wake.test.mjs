import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';

const workerSource = readFileSync(new URL('../worker/server.mjs', import.meta.url), 'utf8');
const indexPhp = readFileSync(new URL('../public/index.php', import.meta.url), 'utf8');

test('Auto-Sleep Fix: Worker pre-loads session auto-sleep preferences from database on boot', () => {
  assert.match(
    workerSource,
    /SELECT user_id, status, phone, push_name, sleeping, auto_sleep_enabled, auto_sleep_timeout_seconds FROM whatsapp_sessions/,
    'Worker must query session preferences on startup'
  );
});

test('Auto-Sleep Fix: startSession loads auto-sleep settings from database before establishing socket', () => {
  assert.match(
    workerSource,
    /SELECT auto_sleep_enabled, auto_sleep_timeout_seconds, phone, push_name FROM whatsapp_sessions WHERE user_id = \? LIMIT 1/,
    'startSession must load auto_sleep_enabled from database'
  );
});

test('Auto-Sleep Fix: sendQueuedMessage selects auto_sleep_enabled and auto_sleep_timeout_seconds', () => {
  assert.match(
    workerSource,
    /SELECT sleeping, auto_sleep_enabled, auto_sleep_timeout_seconds FROM whatsapp_sessions WHERE user_id = \? LIMIT 1/,
    'sendQueuedMessage must query auto_sleep_enabled so rows contain it'
  );
});

test('Auto-Sleep Fix: processQueueOnce verifies against database before auto-sleeping idle session', () => {
  assert.match(
    workerSource,
    /SELECT auto_sleep_enabled, auto_sleep_timeout_seconds FROM whatsapp_sessions WHERE user_id = \? LIMIT 1/,
    'processQueueOnce must check database preference before sleeping'
  );
  assert.match(
    workerSource,
    /if \(!session\.autoSleepEnabled\) \{\s*continue; \/\/ Auto-sleep disabled by tenant in database/,
    'processQueueOnce must skip sleep if database confirms disabled'
  );
});

test('Auto-Sleep Fix: Disabling auto-sleep immediately wakes up sleeping session in worker and database', () => {
  assert.match(
    workerSource,
    /if \(!enabled && session\.sleeping\) \{\s*session\.sleeping = false;/,
    'Worker auto-sleep endpoint must wake session if currently sleeping'
  );
  assert.match(
    indexPhp,
    /if \(!\$enabled\) \{\s*\$wakeStmt = Database::pdo\(\)->prepare\(\s*'UPDATE whatsapp_sessions\s+SET sleeping = 0/,
    'index.php must reset sleeping status in database when auto-sleep is disabled'
  );
  assert.match(
    indexPhp,
    /worker_request\('POST', "sessions\/\{\$user\['id'\]\}\/wake", \[\], 5\);/,
    'index.php must call worker wake endpoint when auto-sleep is disabled'
  );
});
