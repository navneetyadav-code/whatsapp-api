import test from 'node:test';
import assert from 'node:assert/strict';
import crypto from 'node:crypto';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

function verifyWorkerToken(providedToken, expectedToken) {
  const clientToken = String(providedToken || '');
  const clientHash = crypto.createHash('sha256').update(clientToken).digest();
  const expectedHash = crypto.createHash('sha256').update(expectedToken).digest();
  return crypto.timingSafeEqual(clientHash, expectedHash);
}

test('timing-safe token verification approves matching secret', () => {
  const secret = 'test-secret-token-32-characters-long';
  assert.equal(verifyWorkerToken(secret, secret), true);
});

test('timing-safe token verification rejects mismatched secret of identical length', () => {
  const secret = 'test-secret-token-32-characters-long';
  const mismatched = 'test-secret-token-32-characters-diff';
  assert.equal(verifyWorkerToken(mismatched, secret), false);
});

test('timing-safe token verification rejects mismatched secret of different lengths safely', () => {
  const secret = 'test-secret-token-32-characters-long';
  assert.equal(verifyWorkerToken('short', secret), false);
  assert.equal(verifyWorkerToken('extremely-long-token-intended-to-trigger-length-differences-across-buffers', secret), false);
});

test('timing-safe token verification rejects null, empty, or undefined input safely', () => {
  const secret = 'test-secret-token-32-characters-long';
  assert.equal(verifyWorkerToken('', secret), false);
  assert.equal(verifyWorkerToken(null, secret), false);
  assert.equal(verifyWorkerToken(undefined, secret), false);
});

test('worker/server.mjs source employs crypto.timingSafeEqual for X-Worker-Token', () => {
  const serverPath = path.resolve(__dirname, '..', 'worker', 'server.mjs');
  const serverSource = fs.readFileSync(serverPath, 'utf8');

  assert.equal(
    serverSource.includes('crypto.timingSafeEqual'),
    true,
    'server.mjs must call crypto.timingSafeEqual for token verification'
  );
  assert.equal(
    serverSource.includes("X-Worker-Token"),
    true,
    'server.mjs must verify X-Worker-Token header'
  );
});
