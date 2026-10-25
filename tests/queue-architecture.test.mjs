import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';

const workerSource = readFileSync(new URL('../worker/server.mjs', import.meta.url), 'utf8');
const messageQueuePhp = readFileSync(new URL('../src/lib/MessageQueue.php', import.meta.url), 'utf8');
const forgotPasswordPhp = readFileSync(new URL('../src/pages/forgot-password.php', import.meta.url), 'utf8');
const sendApiPhp = readFileSync(new URL('../public/api/send.php', import.meta.url), 'utf8');
const initialMigrationSql = readFileSync(new URL('../migrations/001_initial_schema.sql', import.meta.url), 'utf8');
const canonicalSchemaSql = readFileSync(new URL('../schema.sql', import.meta.url), 'utf8');

test('Fix 2: schema and migrations contain priority, sleeping, and media_assets', () => {
  assert.ok(canonicalSchemaSql.length > 500, 'schema.sql must not be empty');
  assert.match(canonicalSchemaSql, /`sleeping`\s+tinyint\(1\)/, 'schema.sql must have sleeping column');
  assert.match(canonicalSchemaSql, /`priority`\s+(tinyint|int)/i, 'schema.sql must have priority column');
  assert.match(canonicalSchemaSql, /CREATE TABLE IF NOT EXISTS `media_assets`/, 'schema.sql must have media_assets table');
  assert.match(initialMigrationSql, /`sleeping`\s+tinyint\(1\)/, '001_initial_schema.sql must have sleeping column');
  assert.match(initialMigrationSql, /`priority`\s+(tinyint|int)/i, '001_initial_schema.sql must have priority column');
  assert.match(initialMigrationSql, /CREATE TABLE IF NOT EXISTS `media_assets`/, '001_initial_schema.sql must have media_assets table');
  assert.match(initialMigrationSql, /idx_message_queue_worker_priority/, '001_initial_schema.sql must index worker priority');
});

test('Fix 4: forgot-password removes hardcoded user_id = 1 fallback', () => {
  assert.doesNotMatch(forgotPasswordPhp, /\$userId\s*=\s*1;/, 'forgot-password.php must not assign fallback $userId = 1');
  assert.match(forgotPasswordPhp, /\$userId\s*=\s*null;/, 'forgot-password.php initializes $userId as null');
  assert.match(forgotPasswordPhp, /admin_email/, 'forgot-password.php queries admin session dynamically');
  assert.match(forgotPasswordPhp, /priority'\s*=>\s*10/, 'forgot-password.php marks OTP with high priority 10');
});

test('Phase 2 Problem A & B: queue claiming is atomic with FOR UPDATE SKIP LOCKED and priority order', () => {
  assert.match(workerSource, /FOR UPDATE SKIP LOCKED/, 'claimNextMessage must use FOR UPDATE SKIP LOCKED');
  assert.match(workerSource, /ORDER BY priority DESC, available_at ASC, id ASC/, 'claimNextMessage must order by priority DESC');
  assert.match(workerSource, /beginTransaction\(\)/, 'claimNextMessage must claim inside a database transaction');
  assert.match(workerSource, /commit\(\)/, 'claimNextMessage must commit the claim transaction');
  assert.doesNotMatch(workerSource, /SELECT \* FROM message_queue\s+WHERE status = 'processing' AND locked_by = \?/, 'claimNextMessage must not re-select by locked_by');

  // Verify PHP layers populate priority properly
  assert.match(messageQueuePhp, /int \$priority = 5/, 'enqueue_message has priority parameter with default 5');
  assert.match(messageQueuePhp, /priority\)\s+VALUES \(\?, \?, \?, \?, \?, \?, \?\)/, 'enqueue_message writes priority column');
  assert.match(messageQueuePhp, /priority\)\s+VALUES \(\?, NULL, \?, "text", \?, \?, 1\)/, 'campaign enqueue writes priority 1');
  assert.match(sendApiPhp, /\$priority = 5/, 'send.php default priority is 5');
  assert.match(sendApiPhp, /\$priority = 10/, 'send.php assigns priority 10 to high/urgent');
  assert.match(sendApiPhp, /\$priority = 1;/, 'send.php assigns priority 1 to low/bulk');
});

test('Phase 2 Problem C: disconnected sessions do not block worker (scheduled exponential retries)', () => {
  assert.doesNotMatch(workerSource, /while\s*\(Date\.now\(\)\s*-\s*startWait\s*<\s*maxWaitMs\)/, 'Blocking wait loop must be removed');
  assert.match(workerSource, /Math\.pow\(2,\s*Math\.max\(0,\s*attempts\s*-\s*1\)\)/, 'Worker must use exponential backoff');
  assert.match(workerSource, /Math\.random\(\)\s*\*\s*5/, 'Worker must add jitter to retry delay');
  assert.match(workerSource, /DATE_ADD\(NOW\(\),\s*INTERVAL \? SECOND\)/, 'Worker must schedule future available_at for retrying messages');
  assert.match(workerSource, /status = 'queued'/, 'Worker must release claim back to queued state');
});

test('Phase 2 Problem D: shared media is retained across campaign / multiple references', () => {
  assert.match(workerSource, /async function safelyCleanupAttachment/, 'Worker must define safelyCleanupAttachment');
  assert.match(workerSource, /campaigns WHERE id = \?/, 'Worker must check campaign status before media deletion');
  assert.match(workerSource, /status IN \('queued', 'processing'\)/, 'Worker must check remaining queued/processing messages before unlinking media');
  assert.match(workerSource, /media_assets/, 'Worker checks media_assets table reference count');
  assert.match(workerSource, /reference_count - 1/, 'Worker decrements media_assets reference count');
});
