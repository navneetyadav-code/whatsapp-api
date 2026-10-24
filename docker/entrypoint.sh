#!/bin/bash
set -e

# Keep the PHP health probe and Node listener on the same internal loopback port.
# Render may omit WORKER_URL; in that case use the actual WORKER_PORT default.
export WORKER_PORT="${WORKER_PORT:-3107}"
# This image runs PHP and Node in one container. Ignore an externally supplied
# URL here so PHP cannot accidentally probe the public site or another handler.
export WORKER_URL="http://127.0.0.1:${WORKER_PORT}"

# Pass all environment variables to Apache mod_php
echo "PassEnv DB_HOST DB_PORT DB_USER DB_PASSWORD DB_NAME DB_DSN DB_SSL DB_SSL_CA APP_ENV WORKER_URL WORKER_PORT WORKER_TOKEN DEFAULT_COUNTRY_CODE APP_PUBLIC_URL APP_BASE_PATH" > /etc/apache2/conf-available/render-env.conf
a2enconf render-env 2>/dev/null || true

# Run the idempotent, non-destructive schema migration before Supervisor can
# start Apache or the queue worker. `set -e` prevents workers starting on failure.
php /var/www/html/scripts/migrate.php

# Optional private first-account bootstrap. Credentials are read only from the
# deployment environment and are never written to logs or passed as arguments.
if [[ -n "${INITIAL_ADMIN_EMAIL:-}" || -n "${INITIAL_ADMIN_PASSWORD:-}" ]]; then
    export INITIAL_ADMIN_EMAIL INITIAL_ADMIN_PASSWORD INITIAL_ADMIN_NAME
    php /var/www/html/scripts/create-initial-admin.php
fi

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
