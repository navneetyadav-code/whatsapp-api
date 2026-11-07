import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';

const ROOT_DIR = resolve(new URL('..', import.meta.url).pathname.replace(/^\/([a-zA-Z]:)/, '$1'));
const composeSource = readFileSync(resolve(ROOT_DIR, 'docker-compose.yml'), 'utf8');
const entrypointSource = readFileSync(resolve(ROOT_DIR, 'docker/entrypoint.sh'), 'utf8');
const maintenanceSource = readFileSync(resolve(ROOT_DIR, 'src/lib/DatabaseMaintenance.php'), 'utf8');

// ============================================================================
// PART 1: Docker Compose Volume Mount & Named Volume Verification
// ============================================================================

test('Docker Audit 1.1: docker-compose.yml mounts persistent volume for media uploads', () => {
  // Must mount whatsgateway_uploads to /var/www/html/public/uploads
  assert.match(
    composeSource,
    /-\s+whatsgateway_uploads:\/var\/www\/html\/public\/uploads/,
    'app service must mount whatsgateway_uploads volume to /var/www/html/public/uploads'
  );
});

test('Docker Audit 1.2: docker-compose.yml mounts persistent volume for SQL backups', () => {
  // Must mount whatsgateway_backups to /var/www/html/storage/backups
  assert.match(
    composeSource,
    /-\s+whatsgateway_backups:\/var\/www\/html\/storage\/backups/,
    'app service must mount whatsgateway_backups volume to /var/www/html/storage/backups'
  );
});

test('Docker Audit 1.3: docker-compose.yml defines persistent top-level volumes', () => {
  assert.match(
    composeSource,
    /volumes:\s*[\s\S]*?whatsgateway_mysql_data:\s*[\s\S]*?whatsgateway_sessions:\s*[\s\S]*?whatsgateway_uploads:\s*[\s\S]*?whatsgateway_backups:/,
    'Top-level volumes section must declare whatsgateway_uploads and whatsgateway_backups'
  );
});

// ============================================================================
// PART 2: Container Entrypoint & Directory Permission Contract
// ============================================================================

test('Docker Audit 2.1: entrypoint.sh ensures upload and backup directories exist with www-data permissions', () => {
  assert.match(
    entrypointSource,
    /mkdir -p[^\n]*\/var\/www\/html\/public\/uploads/,
    'entrypoint.sh must create /var/www/html/public/uploads'
  );

  assert.match(
    entrypointSource,
    /mkdir -p[^\n]*\/var\/www\/html\/storage\/backups/,
    'entrypoint.sh must create /var/www/html/storage/backups'
  );

  assert.match(
    entrypointSource,
    /chown -R www-data:www-data[^\n]*\/var\/www\/html\/public\/uploads/,
    'entrypoint.sh must set www-data ownership on uploads directory'
  );

  assert.match(
    entrypointSource,
    /chown -R www-data:www-data[^\n]*\/var\/www\/html\/storage\/backups/,
    'entrypoint.sh must set www-data ownership on backups directory'
  );
});

// ============================================================================
// PART 3: Codebase Alignment & Target Path Invariant
// ============================================================================

test('Docker Audit 3.1: Application backup target aligns with persistent backup mount path', () => {
  // DatabaseMaintenance default backup directory must match storage/backups
  assert.match(
    maintenanceSource,
    /storage\/backups/,
    'DatabaseMaintenance must generate backups into storage/backups directory'
  );
});
