import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync, readdirSync } from 'node:fs';
import { resolve } from 'node:path';
import { spawnSync } from 'node:child_process';
import { isPrivateIp, assertSafeWebhookUrl } from '../worker/ssrf-validator.mjs';
import { encryptCredential, decryptCredential } from '../worker/crypto-helper.mjs';

const ROOT_DIR = resolve(new URL('..', import.meta.url).pathname.replace(/^\/([a-zA-Z]:)/, '$1'));
const toolsEngineSource = readFileSync(resolve(ROOT_DIR, 'worker/tools-engine.mjs'), 'utf8');
const apiCredentialsSource = readFileSync(resolve(ROOT_DIR, 'src/lib/ApiCredentials.php'), 'utf8');
const chatAuthSource = readFileSync(resolve(ROOT_DIR, 'public/api/chat/_auth.php'), 'utf8');
const supportSource = readFileSync(resolve(ROOT_DIR, 'src/lib/Support.php'), 'utf8');
const serverSource = readFileSync(resolve(ROOT_DIR, 'worker/server.mjs'), 'utf8');

// ============================================================================
// PART 1: SSRF Validation in Custom Tools Engine
// ============================================================================

test('SSRF 1.1: isPrivateIp correctly identifies loopback, private, and cloud metadata IPs', () => {
  // Loopback and local
  assert.equal(isPrivateIp('127.0.0.1'), true);
  assert.equal(isPrivateIp('127.0.0.2'), true);
  assert.equal(isPrivateIp('::1'), true);
  assert.equal(isPrivateIp('[::1]'), true);
  assert.equal(isPrivateIp('0.0.0.0'), true);

  // AWS/GCP/Azure link-local cloud metadata
  assert.equal(isPrivateIp('169.254.169.254'), true);
  assert.equal(isPrivateIp('169.254.1.1'), true);

  // RFC 1918 Private Ranges
  assert.equal(isPrivateIp('10.0.0.1'), true);
  assert.equal(isPrivateIp('10.255.255.255'), true);
  assert.equal(isPrivateIp('172.16.0.1'), true);
  assert.equal(isPrivateIp('172.31.255.254'), true);
  assert.equal(isPrivateIp('192.168.1.1'), true);
  assert.equal(isPrivateIp('192.168.254.254'), true);

  // CGNAT and Benchmarking
  assert.equal(isPrivateIp('100.64.0.1'), true);
  assert.equal(isPrivateIp('198.18.0.1'), true);

  // Public Routable IPs must NOT be flagged as private
  assert.equal(isPrivateIp('8.8.8.8'), false);
  assert.equal(isPrivateIp('1.1.1.1'), false);
  assert.equal(isPrivateIp('172.217.16.206'), false); // Public Google IP starting with 172
  assert.equal(isPrivateIp('104.26.10.150'), false);
});

test('SSRF 1.2: assertSafeWebhookUrl blocks loopback, private IPs, and cloud metadata', async () => {
  await assert.rejects(
    async () => assertSafeWebhookUrl('http://127.0.0.1:3000/api/internal'),
    /Target URL cannot point to localhost or private hosts|Target URL must use HTTPS/
  );

  await assert.rejects(
    async () => assertSafeWebhookUrl('https://localhost/admin'),
    /Target URL cannot point to localhost or private hosts/
  );

  await assert.rejects(
    async () => assertSafeWebhookUrl('https://169.254.169.254/latest/meta-data/'),
    /Target URL cannot resolve to a private or reserved IP address/
  );

  await assert.rejects(
    async () => assertSafeWebhookUrl('https://10.0.0.5/api/v1'),
    /Target URL cannot resolve to a private or reserved IP address/
  );

  await assert.rejects(
    async () => assertSafeWebhookUrl('https://192.168.1.100/status'),
    /Target URL cannot resolve to a private or reserved IP address/
  );
});

test('SSRF 1.3: tools-engine.mjs imports assertSafeWebhookUrl and secures REST APIs & webhooks', () => {
  assert.match(
    toolsEngineSource,
    /import\s*\{\s*assertSafeWebhookUrl\s*\}\s*from\s*['"]\.\/ssrf-validator\.mjs['"]/,
    'tools-engine.mjs must import assertSafeWebhookUrl from ssrf-validator.mjs'
  );

  assert.match(
    toolsEngineSource,
    /executeCustomRestApi[\s\S]*?assertSafeWebhookUrl\(finalUrl/s,
    'executeCustomRestApi must validate finalUrl with assertSafeWebhookUrl'
  );

  assert.match(
    toolsEngineSource,
    /executeWebhookTool[\s\S]*?assertSafeWebhookUrl\(targetUrl\)/s,
    'executeWebhookTool must validate targetUrl with assertSafeWebhookUrl'
  );
});

// ============================================================================
// PART 2: Client IP Spoofing Flaw Fix (ApiCredentials.php)
// ============================================================================

test('IP Spoofing 2.1: ApiCredentials.php uses FILTER_FLAG_NO_PRIV_RANGE to guard proxy headers', () => {
  assert.match(
    apiCredentialsSource,
    /FILTER_FLAG_NO_PRIV_RANGE\s*\|\s*FILTER_FLAG_NO_RES_RANGE/,
    'client_ip() must employ FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE'
  );

  // Must not treat broad 172.0.0.0/8 as private proxy
  assert.doesNotMatch(
    apiCredentialsSource,
    /str_starts_with\(\$remote,\s*['"]172\.['"]\)/,
    'client_ip() must NOT treat entire 172.0.0.0/8 block as private proxy'
  );
});

test('IP Spoofing 2.2: PHP runtime validates public 172.x.x.x IPs cannot spoof X-Forwarded-For', () => {
  const phpCode = `
    require_once 'src/lib/ApiCredentials.php';

    // Scenario A: Client connects from public IP 172.217.16.206 with spoofed header
    \$_SERVER['REMOTE_ADDR'] = '172.217.16.206';
    \$_SERVER['HTTP_X_FORWARDED_FOR'] = '10.0.0.1';
    \$_SERVER['HTTP_CF_CONNECTING_IP'] = '10.0.0.1';
    \$_SERVER['HTTP_X_REAL_IP'] = '10.0.0.1';

    \$resolved = client_ip();
    if (\$resolved !== '172.217.16.206') {
        echo "SPOOF_FAILED: resolved to " . \$resolved;
        exit(1);
    }

    // Scenario B: Client connects through legitimate private proxy (172.16.0.1)
    unset(\$_SERVER['HTTP_CF_CONNECTING_IP']);
    unset(\$_SERVER['HTTP_X_REAL_IP']);
    unset(\$_SERVER['HTTP_CLIENT_IP']);
    \$_SERVER['REMOTE_ADDR'] = '172.16.0.1';
    \$_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.45';
    \$resolvedProxy = client_ip();
    if (\$resolvedProxy !== '203.0.113.45') {
        echo "PROXY_FORWARD_FAILED: resolved to " . \$resolvedProxy;
        exit(1);
    }

    echo "OK";
  `;

  const proc = spawnSync('php', ['-r', phpCode], { cwd: ROOT_DIR, encoding: 'utf8' });
  assert.equal(proc.status, 0, `PHP evaluation failed: ${proc.stderr}`);
  assert.equal(proc.stdout.trim(), 'OK');
});

// ============================================================================
// PART 3: CSRF Protection on Standalone Credentials & Chat APIs
// ============================================================================

test('CSRF 3.1: Support.php defines verify_api_csrf() with dual token and header verification', () => {
  assert.match(supportSource, /function verify_api_csrf\(\): void/, 'verify_api_csrf must be defined');
  assert.match(supportSource, /HTTP_X_CSRF_TOKEN/, 'Must check HTTP_X_CSRF_TOKEN');
  assert.match(supportSource, /HTTP_X_REQUESTED_WITH/, 'Must check HTTP_X_REQUESTED_WITH');
  assert.match(supportSource, /HTTP_X_API_KEY/, 'API key authenticated clients exempt from CSRF');
});

test('CSRF 3.2: public/api/chat/_auth.php enforces verify_api_csrf() for state-changing HTTP methods', () => {
  assert.match(
    chatAuthSource,
    /verify_api_csrf\(\)/,
    '_auth.php must invoke verify_api_csrf()'
  );
  assert.match(
    chatAuthSource,
    /in_array\(\$_SERVER\['REQUEST_METHOD'\]\s*\?\?\s*['"]GET['"],\s*\['POST',\s*'PUT',\s*'DELETE',\s*'PATCH'\]/s,
    '_auth.php must guard POST, PUT, DELETE, PATCH'
  );
});

test('CSRF 3.3: All endpoints under public/api/credentials/ enforce verify_api_csrf()', () => {
  const credDir = resolve(ROOT_DIR, 'public/api/credentials');
  const files = readdirSync(credDir).filter((f) => f.endsWith('.php'));

  assert.ok(files.length >= 8, 'Must verify all credential endpoints');
  for (const file of files) {
    const src = readFileSync(resolve(credDir, file), 'utf8');
    assert.match(src, /verify_api_csrf\(\);/, `${file} must enforce verify_api_csrf()`);
  }
});

test('CSRF 3.4: PHP runtime rejects cross-site POST lacking CSRF token or anti-CSRF headers', () => {
  const phpCode = `
    require_once 'src/lib/Support.php';
    \$_SESSION['csrf_token'] = 'session_secret_csrf_12345';
    \$_SERVER['REQUEST_METHOD'] = 'POST';

    // Unsafe cross-origin attempt without headers or tokens
    unset(\$_POST['csrf_token']);
    unset(\$_SERVER['HTTP_X_CSRF_TOKEN']);
    unset(\$_SERVER['HTTP_X_REQUESTED_WITH']);
    unset(\$_SERVER['HTTP_X_API_KEY']);

    // Capture exit / 403
    ob_start();
    verify_api_csrf();
    \$out = ob_get_clean();
    echo "DID_NOT_BLOCK";
  `;

  const proc = spawnSync('php', ['-r', phpCode], { cwd: ROOT_DIR, encoding: 'utf8' });
  // Process should terminate with exit or 403 json response, not output DID_NOT_BLOCK
  assert.notEqual(proc.stdout.trim(), 'DID_NOT_BLOCK');
});

// ============================================================================
// PART 4: AES-256-GCM Encryption of AI Keys and SMTP Passwords at Rest
// ============================================================================

test('Crypto 4.1: Node.js encryptCredential and decryptCredential encrypt and decrypt correctly', () => {
  const secretKey = 'sk-or-v1-abcdef1234567890abcdef1234567890';
  const encrypted = encryptCredential(secretKey);

  assert.match(encrypted, /^enc:v1:[A-Za-z0-9+/=]+$/, 'Ciphertext must be prefixed with enc:v1:');
  assert.notEqual(encrypted, secretKey, 'Encrypted output must not match plaintext');

  const decrypted = decryptCredential(encrypted);
  assert.equal(decrypted, secretKey, 'Decrypted output must match original plaintext');

  // Plaintext backward compatibility
  assert.equal(decryptCredential('legacy-unencrypted-key'), 'legacy-unencrypted-key');
});

test('Crypto 4.2: Bidirectional cross-language encryption (PHP <-> Node.js)', () => {
  const testSecret = 'AIzaSyTestApiKeyForCrossLanguageValidation_987654';

  // 1. Encrypt in Node.js, decrypt in PHP
  const nodeEncrypted = encryptCredential(testSecret);
  const phpDecryptCode = `
    require_once 'src/lib/Crypto.php';
    echo decrypt_credential('${nodeEncrypted}');
  `;
  const procPhp = spawnSync('php', ['-r', phpDecryptCode], { cwd: ROOT_DIR, encoding: 'utf8' });
  assert.equal(procPhp.status, 0, `PHP decrypt failed: ${procPhp.stderr}`);
  assert.equal(procPhp.stdout.trim(), testSecret, 'PHP must decrypt Node.js encrypted credential');

  // 2. Encrypt in PHP, decrypt in Node.js
  const phpEncryptCode = `
    require_once 'src/lib/Crypto.php';
    echo encrypt_credential('${testSecret}');
  `;
  const procPhpEnc = spawnSync('php', ['-r', phpEncryptCode], { cwd: ROOT_DIR, encoding: 'utf8' });
  assert.equal(procPhpEnc.status, 0, `PHP encrypt failed: ${procPhpEnc.stderr}`);
  const phpEncrypted = procPhpEnc.stdout.trim();

  assert.match(phpEncrypted, /^enc:v1:/);
  const nodeDecrypted = decryptCredential(phpEncrypted);
  assert.equal(nodeDecrypted, testSecret, 'Node.js must decrypt PHP encrypted credential');
});

test('Crypto 4.3: Credentials endpoints and worker wire encryption and decryption', () => {
  const saveAiKeySrc = readFileSync(resolve(ROOT_DIR, 'public/api/credentials/save-ai-key.php'), 'utf8');
  const saveSmtpSrc = readFileSync(resolve(ROOT_DIR, 'public/api/credentials/save-smtp.php'), 'utf8');

  assert.match(saveAiKeySrc, /encrypt_credential\(/, 'save-ai-key.php must encrypt API key before insert/update');
  assert.match(saveSmtpSrc, /encrypt_credential\(/, 'save-smtp.php must encrypt password before insert/update');
  assert.match(serverSource, /decryptCredential\(/, 'worker/server.mjs must decrypt ai_provider_keys');
});
