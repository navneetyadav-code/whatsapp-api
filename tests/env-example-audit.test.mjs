import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';

const ROOT_DIR = resolve(new URL('..', import.meta.url).pathname.replace(/^\/([a-zA-Z]:)/, '$1'));
const envExampleSource = readFileSync(resolve(ROOT_DIR, '.env.example'), 'utf8');
const entrypointSource = readFileSync(resolve(ROOT_DIR, 'docker/entrypoint.sh'), 'utf8');

function parseEnvExample(source) {
  const vars = new Map();
  for (const line of source.split(/\r?\n/)) {
    const trimmed = line.trim();
    if (!trimmed || trimmed.startsWith('#')) continue;
    const match = trimmed.match(/^([A-Z0-9_]+)=(.*)$/);
    if (match) {
      vars.set(match[1], match[2]);
    }
  }
  return vars;
}

const envVars = parseEnvExample(envExampleSource);

// ============================================================================
// PART 1: Core Security & Encryption Environment Variables
// ============================================================================

test('Env Audit 1.1: Core security variables are declared', () => {
  assert.ok(envVars.has('APP_KEY'), 'APP_KEY must be declared in .env.example for AES-256-GCM encryption');
  assert.ok(envVars.has('WORKER_TOKEN'), 'WORKER_TOKEN must be declared');
  assert.ok(envVars.has('SYSTEM_API_SECRET'), 'SYSTEM_API_SECRET must be declared');
  assert.ok(envVars.has('LOGIN_MAX_ATTEMPTS'), 'LOGIN_MAX_ATTEMPTS must be declared');
  assert.ok(envVars.has('LOGIN_LOCKOUT_MINUTES'), 'LOGIN_LOCKOUT_MINUTES must be declared');
});

// ============================================================================
// PART 2: Database & Queue Adaptive Engine Variables
// ============================================================================

test('Env Audit 2.1: Database configuration variables are declared', () => {
  const dbKeys = ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_SSL', 'DB_SSL_CA', 'DB_DSN', 'DB_POOL_LIMIT'];
  for (const k of dbKeys) {
    assert.ok(envVars.has(k), `${k} must be declared in .env.example`);
  }
});

test('Env Audit 2.2: Queue adaptive engine and anti-ban timing variables are declared', () => {
  const timingKeys = ['DEFAULT_COUNTRY_CODE', 'SEND_DELAY_MS', 'QUEUE_POLL_MS', 'QUEUE_POLL_ACTIVE_MS', 'QUEUE_POLL_IDLE_MS', 'WA_MESSAGE_ID_FORMAT'];
  for (const k of timingKeys) {
    assert.ok(envVars.has(k), `${k} must be declared in .env.example`);
  }
});

// ============================================================================
// PART 3: AI Multi-Key Provider Fallbacks & Model Selection
// ============================================================================

test('Env Audit 3.1: AI provider API keys and model defaults are declared', () => {
  const aiKeys = [
    'GEMINI_API_KEY',
    'GROQ_API_KEY',
    'OPENROUTER_API_KEY',
    'DEFAULT_GEMINI_MODEL',
    'DEFAULT_GROQ_MODEL',
    'DEFAULT_OPENROUTER_MODEL',
    'GEMINI_FALLBACK_MODELS'
  ];
  for (const k of aiKeys) {
    assert.ok(envVars.has(k), `${k} must be declared in .env.example`);
  }
});

// ============================================================================
// PART 4: Clean Open Source Guardrails (rules/rules.md compliance)
// ============================================================================

test('Env Audit 4.1: No personal credentials or private keys in .env.example', () => {
  // Ensure no real personal API keys or tokens are checked into .env.example
  assert.doesNotMatch(envExampleSource, /AIzaSy[A-Za-z0-9_-]{33}/, 'No real Gemini API keys in .env.example');
  assert.doesNotMatch(envExampleSource, /gsk_[A-Za-z0-9]{20,}/, 'No real Groq API keys in .env.example');
  assert.doesNotMatch(envExampleSource, /sk-or-v1-[A-Za-z0-9]{30,}/, 'No real OpenRouter keys in .env.example');
  assert.doesNotMatch(envExampleSource, /EAAG[A-Za-z0-9]{30,}/, 'No Meta access tokens in .env.example');
});

// ============================================================================
// PART 5: Container PassEnv Alignment
// ============================================================================

test('Env Audit 5.1: entrypoint.sh PassEnv forwards APP_KEY to Apache', () => {
  assert.match(
    entrypointSource,
    /PassEnv[^\n]*\bAPP_KEY\b/,
    'entrypoint.sh PassEnv must forward APP_KEY to Apache mod_php'
  );
});
