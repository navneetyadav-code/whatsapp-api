#!/bin/bash
set -e

# 1. Internal loopback port & URL configuration
export WORKER_PORT="${WORKER_PORT:-3107}"
export WORKER_URL="http://127.0.0.1:${WORKER_PORT}"

# 2. Guarantee a secure 32+ character WORKER_TOKEN for internal process IPC
if [ -z "${WORKER_TOKEN:-}" ] || [ ${#WORKER_TOKEN} -lt 32 ] || [ "${WORKER_TOKEN}" = "change-this-worker-token-before-production" ]; then
    export WORKER_TOKEN=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1)
    echo "[docker-entrypoint] Auto-generated secure 32-character WORKER_TOKEN for internal IPC."
fi

# 3. Create required runtime directories and set secure permissions
mkdir -p /var/www/html/whatsapp-api-data/sessions \
         /var/www/html/public/uploads \
         /var/log/supervisor
chown -R www-data:www-data /var/www/html/whatsapp-api-data \
                           /var/www/html/public/uploads
chmod 700 /var/www/html/whatsapp-api-data/sessions
chmod 755 /var/www/html/public/uploads

# 4. Export all runtime environment variables to .env and Apache mod_php PassEnv
printenv | grep -E '^(APP_|DB_|WORKER_|DEFAULT_|LOGIN_|ENABLE_|DISABLE_|ALLOW_|ADMIN_|SMTP_|BREVO_|RESEND_|GOOGLE_|SYSTEM_|INITIAL_|SEND_|QUEUE_|LOG_)' > /var/www/html/.env || true
chmod 600 /var/www/html/.env
chown www-data:www-data /var/www/html/.env

echo "PassEnv $(printenv | awk -F= '{print $1}' | grep -E '^(APP_|DB_|WORKER_|DEFAULT_|LOGIN_|ENABLE_|DISABLE_|ALLOW_|ADMIN_|SMTP_|BREVO_|RESEND_|GOOGLE_|SYSTEM_|INITIAL_|SEND_|QUEUE_|LOG_)' | tr '\n' ' ')" > /etc/apache2/conf-available/render-env.conf
a2enconf render-env 2>/dev/null || true

# 5. Resilient Database Readiness Wait Loop
echo "[docker-entrypoint] Verifying database connectivity at ${DB_HOST:-127.0.0.1}:${DB_PORT:-3306}..."
MAX_RETRIES=30
RETRY_COUNT=0
until php -r "
  require_once '/var/www/html/src/bootstrap.php';
  try {
    Database::pdo();
    exit(0);
  } catch (Throwable \$e) {
    exit(1);
  }
" >/dev/null 2>&1; do
  RETRY_COUNT=$((RETRY_COUNT + 1))
  if [ $RETRY_COUNT -ge $MAX_RETRIES ]; then
    echo "[docker-entrypoint] ERROR: Database connection failed after $MAX_RETRIES attempts."
    # Run once without redirect to output diagnostic error log
    php -r "require_once '/var/www/html/src/bootstrap.php'; Database::pdo();" || true
    exit 1
  fi
  echo "[docker-entrypoint] Waiting for database readiness (${RETRY_COUNT}/${MAX_RETRIES})... retrying in 2s"
  sleep 2
done
echo "[docker-entrypoint] Database connection established."

# 6. Run database migrations to ensure all application tables exist
echo "[docker-entrypoint] Running database migrations..."
php /var/www/html/scripts/migrate.php

# 7. Optional initial administrator account provisioning
if [[ -n "${INITIAL_ADMIN_EMAIL:-}" && -n "${INITIAL_ADMIN_PASSWORD:-}" ]]; then
    echo "[docker-entrypoint] Checking initial administrator account..."
    export INITIAL_ADMIN_EMAIL INITIAL_ADMIN_PASSWORD INITIAL_ADMIN_NAME
    php /var/www/html/scripts/create-initial-admin.php
fi

echo "[docker-entrypoint] Launching Supervisor services (Apache + Node.js Baileys Worker)..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
