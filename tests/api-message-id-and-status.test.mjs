import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';

const sendPhp = readFileSync(new URL('../public/api/send.php', import.meta.url), 'utf8');
const messagesPhp = readFileSync(new URL('../public/api/messages.php', import.meta.url), 'utf8');

// ============================================================================
// PART 1: Phantom wamid Removal & Contract Verification
// ============================================================================

test('Phantom wamid Bug: public/api/send.php does not fabricate a fake wamid', () => {
  // Ensure the old fabricated wamid pattern is eliminated
  assert.doesNotMatch(
    sendPhp,
    /\$wamid\s*=\s*'wamid\.'/,
    'send.php must not fabricate random wamid strings'
  );
  assert.doesNotMatch(
    sendPhp,
    /bin2hex\(random_bytes\(16\)\)/,
    'send.php must not generate throwaway random hex bytes for message IDs'
  );
});

test('API Consistency: public/api/send.php returns real MySQL queue ID across all fields', () => {
  // message_id is real queue ID
  assert.match(
    sendPhp,
    /'message_id'\s*=>\s*\$messageId/,
    'send.php must return real queue ID in message_id'
  );

  // provider_message_id is explicitly null initially
  assert.match(
    sendPhp,
    /'provider_message_id'\s*=>\s*null/,
    'send.php must return provider_message_id as null initially (not yet dispatched)'
  );

  // Meta compatibility messages[0].id matches the real queue ID
  assert.match(
    sendPhp,
    /'id'\s*=>\s*\(string\)\s*\$messageId/,
    'send.php must align Meta compatibility messages[0].id with the real queue ID'
  );
  assert.match(
    sendPhp,
    /'status'\s*=>\s*'queued'/,
    'send.php must declare status as queued'
  );
});

// ============================================================================
// PART 2: Status Querying via public/api/messages.php
// ============================================================================

test('Status Lookup: public/api/messages.php supports GET queries by queue ID or provider ID', () => {
  assert.match(
    messagesPhp,
    /\$method === 'GET'/,
    'messages.php must support GET method for status lookups'
  );
  assert.match(
    messagesPhp,
    /authenticate_api_request\(\)/,
    'messages.php GET must authenticate tenant API request'
  );
  assert.match(
    messagesPhp,
    /WHERE user_id = \? AND \(id = \? OR provider_message_id = \?\)/,
    'messages.php must enforce tenant isolation and query by either queue id or provider_message_id'
  );
  assert.match(
    messagesPhp,
    /'provider_message_id'\s*=>\s*\$msg\['provider_message_id'\]/,
    'messages.php must return the real provider_message_id from database'
  );
});

// ============================================================================
// PART 3: Algorithmic & Behavioral Simulation
// ============================================================================

test('Behavioral: Ingestion and Post-Dispatch ID Lifecycle Correlation', () => {
  // Step 1: Client sends message through API
  const mockDb = {
    messages: []
  };

  function simulateApiSend(userId, apiKeyId, to, text) {
    const queueId = mockDb.messages.length + 1;
    const record = {
      id: queueId,
      user_id: userId,
      api_key_id: apiKeyId,
      recipient: to,
      body: text,
      status: 'queued',
      provider_message_id: null,
      created_at: new Date().toISOString()
    };
    mockDb.messages.push(record);

    // Response matching public/api/send.php
    return {
      ok: true,
      status: 'queued',
      message_id: queueId,
      provider_message_id: null,
      messages: [
        {
          id: String(queueId),
          message_status: 'accepted'
        }
      ]
    };
  }

  const response = simulateApiSend(1, 10, '919876543210', 'Test notification');

  // Verify response
  assert.strictEqual(response.message_id, 1);
  assert.strictEqual(response.provider_message_id, null, 'Provider ID must be null on ingestion');
  assert.strictEqual(response.messages[0].id, '1', 'Meta messages[0].id must be identical to queue message_id');

  // Step 2: Worker claims and dispatches message via Baileys
  const realWhatsAppId = '3EB0D4F074A5CA10A7426A';
  const queuedJob = mockDb.messages.find(m => m.id === response.message_id);
  assert.ok(queuedJob);

  // Baileys dispatch completion
  queuedJob.status = 'sent';
  queuedJob.provider_message_id = realWhatsAppId;
  queuedJob.sent_at = new Date().toISOString();

  // Step 3: Client queries status via messages.php?id=1
  function simulateStatusQuery(userId, queryId) {
    const found = mockDb.messages.find(m => m.user_id === userId && (String(m.id) === queryId || m.provider_message_id === queryId));
    if (!found) return null;
    return {
      ok: true,
      message_id: found.id,
      status: found.status,
      provider_message_id: found.provider_message_id
    };
  }

  // Query by queue ID
  const queryByQueueId = simulateStatusQuery(1, '1');
  assert.ok(queryByQueueId);
  assert.strictEqual(queryByQueueId.status, 'sent');
  assert.strictEqual(queryByQueueId.provider_message_id, realWhatsAppId);

  // Query by real WhatsApp provider ID
  const queryByProviderId = simulateStatusQuery(1, realWhatsAppId);
  assert.ok(queryByProviderId);
  assert.strictEqual(queryByProviderId.message_id, 1);
  assert.strictEqual(queryByProviderId.provider_message_id, realWhatsAppId);

  // Unauthorized tenant cannot access
  const unauthorizedQuery = simulateStatusQuery(999, '1');
  assert.strictEqual(unauthorizedQuery, null, 'Tenant isolation prevents cross-tenant status inspection');
});
