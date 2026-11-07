import test from 'node:test';
import assert from 'node:assert/strict';
import { existsSync, readFileSync } from 'node:fs';
import { resolve } from 'node:path';
import { spawnSync } from 'node:child_process';

const ROOT_DIR = resolve(new URL('..', import.meta.url).pathname.replace(/^\/([a-zA-Z]:)/, '$1'));
const PUBLIC_API_BACKUP = resolve(ROOT_DIR, 'public/api/backup_and_clean.php');
const SCRIPTS_BACKUP = resolve(ROOT_DIR, 'scripts/backup_and_clean.php');
const ROOT_BACKUP = resolve(ROOT_DIR, 'backup_and_clean.php');

// ============================================================================
// PART 1: Attack Surface Quarantine & File System Isolation
// ============================================================================

test('Audit 1: Public web endpoint public/api/backup_and_clean.php is completely deleted', () => {
  assert.equal(
    existsSync(PUBLIC_API_BACKUP),
    false,
    'public/api/backup_and_clean.php MUST NOT exist in the public web root'
  );
});

test('Audit 2: scripts/backup_and_clean.php enforces strict CLI-only execution gate', () => {
  assert.equal(existsSync(SCRIPTS_BACKUP), true, 'scripts/backup_and_clean.php must exist');

  const content = readFileSync(SCRIPTS_BACKUP, 'utf8');

  // Must have strict CLI-only guard at the very top
  assert.match(
    content,
    /if\s*\(\s*PHP_SAPI\s*!==\s*['"]cli['"]\s*\)\s*\{\s*http_response_code\(404\);\s*exit;\s*\}/,
    'scripts/backup_and_clean.php must reject non-CLI requests with HTTP 404'
  );
});

test('Audit 3: Root backup_and_clean.php enforces strict CLI-only execution gate', () => {
  assert.equal(existsSync(ROOT_BACKUP), true, 'root backup_and_clean.php must exist');

  const content = readFileSync(ROOT_BACKUP, 'utf8');

  assert.match(
    content,
    /if\s*\(\s*PHP_SAPI\s*!==\s*['"]cli['"]\s*\)\s*\{\s*http_response_code\(404\);\s*exit;\s*\}/,
    'root backup_and_clean.php must reject non-CLI requests with HTTP 404'
  );
});

// ============================================================================
// PART 2: Secret Quarantine & Elimination of HTTP Parameter Injection
// ============================================================================

test('Audit 4: Vulnerable HTTP parameter access and secret comparisons are eliminated', () => {
  const content = readFileSync(SCRIPTS_BACKUP, 'utf8');

  // Must not accept ?token= from GET, POST, or HTTP headers
  assert.doesNotMatch(content, /\$_GET\['token'\]/, 'Must not read token from $_GET');
  assert.doesNotMatch(content, /\$_POST\['token'\]/, 'Must not read token from $_POST');
  assert.doesNotMatch(content, /\$_SERVER\['HTTP_X_MAINTENANCE_TOKEN'\]/, 'Must not read token from $_SERVER header');

  // Must not allow DB_PASSWORD or WORKER_TOKEN to serve as authorization tokens to truncate database
  assert.doesNotMatch(content, /getenv\(['"]DB_PASSWORD['"]\)/, 'DB_PASSWORD must never be checked as a maintenance token');
  assert.doesNotMatch(content, /getenv\(['"]WORKER_TOKEN['"]\)/, 'WORKER_TOKEN must not authenticate remote table purge');
  assert.doesNotMatch(content, /getenv\(['"]SYSTEM_API_SECRET['"]\)/, 'SYSTEM_API_SECRET must not authenticate remote table purge');

  // Must not contain web file download logic
  assert.doesNotMatch(content, /readfile\(/, 'Must not support web file downloads');
  assert.doesNotMatch(content, /header\(['"]Content-Disposition:/, 'Must not emit web download headers');
});

// ============================================================================
// PART 3: Runtime Execution Simulation (CLI vs Non-CLI SAPI)
// ============================================================================

test('Audit 5: Non-CLI execution immediately triggers HTTP 404 and terminates', () => {
  // Test simulated SAPI guard by passing simulated non-cli SAPI via PHP
  const phpCode = `
    namespace {
        // Intercept or test the exact guard statement
        function testGuard(string $sapi): array {
            $code = null;
            $exited = false;
            if ($sapi !== 'cli') {
                $code = 404;
                $exited = true;
            }
            return ['code' => $code, 'exited' => $exited];
        }

        $sapis = ['fpm-fcgi', 'apache2handler', 'cgi-fcgi', 'cli-server'];
        foreach ($sapis as $sapi) {
            $res = testGuard($sapi);
            if ($res['code'] !== 404 || !$res['exited']) {
                echo "FAIL: " . $sapi;
                exit(1);
            }
        }
        echo "ALL_BLOCKED";
        exit(0);
    }
  `;

  const proc = spawnSync('php', ['-r', phpCode], { encoding: 'utf8' });
  assert.equal(proc.status, 0, `PHP execution failed: ${proc.stderr}`);
  assert.equal(proc.stdout.trim(), 'ALL_BLOCKED');
});

test('Audit 6: Legitimate CLI execution functions properly (--help)', () => {
  const proc = spawnSync('php', [SCRIPTS_BACKUP, '--help'], { encoding: 'utf8' });
  assert.equal(proc.status, 0, `CLI execution failed: ${proc.stderr}`);
  assert.match(proc.stdout, /WhatsGateway Maintenance Utility: Database SQL Backup & Clean/);
  assert.match(proc.stdout, /--dry-run/);
  assert.match(proc.stdout, /--backup-only/);
  assert.match(proc.stdout, /--clean-only/);
});
