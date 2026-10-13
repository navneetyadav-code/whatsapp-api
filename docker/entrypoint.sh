#!/bin/bash
set -e

# Export relevant environment variables into /var/www/html/.env for PHP and worker
printenv | grep -E '^(DB_|APP_|WORKER_|DEFAULT_|SMTP_|GOOGLE_|OTP_|SYSTEM_)' > /var/www/html/.env || true
chown www-data:www-data /var/www/html/.env 2>/dev/null || true
chmod 644 /var/www/html/.env 2>/dev/null || true

# Pass all environment variables to Apache mod_php
echo "PassEnv DB_HOST DB_PORT DB_USER DB_PASSWORD DB_NAME DB_SSL APP_ENV WORKER_URL WORKER_TOKEN DEFAULT_COUNTRY_CODE APP_PUBLIC_URL APP_BASE_PATH" > /etc/apache2/conf-available/render-env.conf
a2enconf render-env 2>/dev/null || true

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
