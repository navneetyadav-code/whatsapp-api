#!/usr/bin/env bash
# ==============================================================================
# WhatsGateway - Automated CloudPanel & Worker Deployment Script
# ==============================================================================
# Usage:
#   sudo bash scripts/setup-cloudpanel.sh --domain api.yourdomain.com [OPTIONS]
#
# Options:
#   --domain <domain>         (Required) Domain name for WhatsGateway
#   --site-user <username>    (Optional) CloudPanel site user (default: whatsgateway)
#   --site-pass <password>    (Optional) SSH password for site user (auto-generated if omitted)
#   --db-name <database>      (Optional) MySQL Database name (default: whatsgateway)
#   --db-user <username>      (Optional) MySQL User name (default: wa_user)
#   --db-pass <password>      (Optional) MySQL Password (auto-generated if omitted)
#   --php-version <version>   (Optional) PHP Version: 8.2 or 8.3 (default: 8.2)
#   --worker-port <port>      (Optional) Background worker port (default: 3107)
#   --country-code <digits>   (Optional) Default phone country code (from env DEFAULT_COUNTRY_CODE or default: 1)
# ==============================================================================

set -euo pipefail

# Text colors
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}====================================================${NC}"
echo -e "${BLUE}   WhatsGateway Automated CloudPanel Installer      ${NC}"
echo -e "${BLUE}====================================================${NC}"

# Check for root
if [ "$EUID" -ne 0 ]; then
  echo -e "${RED}[ERROR] This script must be run as root (use sudo).${NC}"
  exit 1
fi

# Check for CloudPanel CLI
if ! command -v clpctl &> /dev/null; then
  echo -e "${RED}[ERROR] 'clpctl' command not found.${NC}"
  echo -e "This script is designed to run on a server with CloudPanel installed."
  exit 1
fi

# Default parameters
DOMAIN=""
SITE_USER="whatsgateway"
SITE_PASS=""
DB_NAME="whatsgateway"
DB_USER="wa_user"
DB_PASS=""
PHP_VER="8.2"
WORKER_PORT="3107"
COUNTRY_CODE="${DEFAULT_COUNTRY_CODE:-1}"

# Parse CLI arguments
while [[ $# -gt 0 ]]; do
  case "$1" in
    --domain)
      DOMAIN="$2"
      shift 2
      ;;
    --site-user)
      SITE_USER="$2"
      shift 2
      ;;
    --site-pass)
      SITE_PASS="$2"
      shift 2
      ;;
    --db-name)
      DB_NAME="$2"
      shift 2
      ;;
    --db-user)
      DB_USER="$2"
      shift 2
      ;;
    --db-pass)
      DB_PASS="$2"
      shift 2
      ;;
    --php-version)
      PHP_VER="$2"
      shift 2
      ;;
    --worker-port)
      WORKER_PORT="$2"
      shift 2
      ;;
    --country-code)
      COUNTRY_CODE="$2"
      shift 2
      ;;
    *)
      echo -e "${RED}[ERROR] Unknown option: $1${NC}"
      exit 1
      ;;
  esac
done

if [ -z "$DOMAIN" ]; then
  echo -e "${RED}[ERROR] Domain is required. Example: --domain api.yourdomain.com${NC}"
  exit 1
fi

# Generate passwords if not provided
if [ -z "$SITE_PASS" ]; then
  SITE_PASS=$(openssl rand -base64 16 | tr -dc 'a-zA-Z0-9' | head -c 16)
fi

if [ -z "$DB_PASS" ]; then
  DB_PASS=$(openssl rand -base64 18 | tr -dc 'a-zA-Z0-9' | head -c 18)
fi

WORKER_TOKEN=$(openssl rand -hex 24)
SYSTEM_API_SECRET=$(openssl rand -hex 32)

SITE_DIR="/home/${SITE_USER}/htdocs/${DOMAIN}"

echo -e "\n${YELLOW}Deployment Plan:${NC}"
echo -e "  Domain:         ${GREEN}${DOMAIN}${NC}"
echo -e "  Site User:      ${GREEN}${SITE_USER}${NC}"
echo -e "  PHP Version:    ${GREEN}${PHP_VER}${NC}"
echo -e "  Database:       ${GREEN}${DB_NAME}${NC}"
echo -e "  Database User:  ${GREEN}${DB_USER}${NC}"
echo -e "  Worker Port:    ${GREEN}${WORKER_PORT}${NC}"
echo -e "  Country Code:   ${GREEN}${COUNTRY_CODE}${NC}"
echo -e "  Site Directory: ${GREEN}${SITE_DIR}${NC}\n"

# Step 1: Install prerequisites (Node.js & PM2)
echo -e "${BLUE}[1/7] Checking Node.js and PM2...${NC}"
if ! command -v node &> /dev/null; then
  echo -e "Installing Node.js..."
  curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
  apt-get install -y nodejs
fi

if ! command -v pm2 &> /dev/null; then
  echo -e "Installing PM2 globally..."
  npm install -g pm2
fi

# Step 2: Create Site in CloudPanel
echo -e "${BLUE}[2/7] Creating PHP site via CloudPanel CLI...${NC}"
if [ ! -d "$SITE_DIR" ]; then
  clpctl site:add:php \
    --domainName="$DOMAIN" \
    --phpVersion="$PHP_VER" \
    --vhostTemplate="Generic" \
    --siteUser="$SITE_USER" \
    --siteUserPassword="$SITE_PASS"
else
  echo -e "Site directory already exists, continuing..."
fi

# Step 3: Copy / Deploy Repo files to Site Directory
echo -e "${BLUE}[3/7] Syncing project files to ${SITE_DIR}...${NC}"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

# Copy project files if running from within cloned repo
if [ -f "${SCRIPT_DIR}/schema.sql" ] && [ "$SCRIPT_DIR" != "$SITE_DIR" ]; then
  rsync -av --exclude 'node_modules' --exclude '.git' --exclude 'sessions' "${SCRIPT_DIR}/" "${SITE_DIR}/"
fi

# Step 4: Configure Database
echo -e "${BLUE}[4/7] Creating Database and importing schema...${NC}"
clpctl db:add \
  --site="$DOMAIN" \
  --databaseName="$DB_NAME" \
  --databaseUserName="$DB_USER" \
  --databaseUserPassword="$DB_PASS" || true

# Import schema
if [ -f "${SITE_DIR}/schema.sql" ]; then
  mysql -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" < "${SITE_DIR}/schema.sql" || {
    echo -e "${YELLOW}Warning: Direct user import failed, attempting root MySQL import...${NC}"
    mysql "${DB_NAME}" < "${SITE_DIR}/schema.sql" || true
  }
fi

# Step 5: Generate .env Configuration
echo -e "${BLUE}[5/7] Creating production .env file...${NC}"
cat > "${SITE_DIR}/.env" <<EOF
# Generated automatically by setup-cloudpanel.sh
APP_ENV=production
APP_NAME="WhatsGateway"
APP_PUBLIC_URL=https://${DOMAIN}
APP_BASE_PATH=
APP_SESSION_NAME=wa_api_hub
APP_SECURE_COOKIES=true
ENABLE_REGISTRATION=false
DISABLE_PASSWORD_LOGIN=false
ALLOW_NEW_DEVICES=true

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=${DB_NAME}
DB_USER=${DB_USER}
DB_PASSWORD=${DB_PASS}
DB_SSL=false

WORKER_URL=http://127.0.0.1:${WORKER_PORT}
WORKER_PORT=${WORKER_PORT}
WORKER_TOKEN=${WORKER_TOKEN}
WA_SESSION_DIR=${SITE_DIR}/worker/sessions

DEFAULT_COUNTRY_CODE=${COUNTRY_CODE}
SEND_DELAY_MS=2500
QUEUE_POLL_MS=3000
LOG_LEVEL=warn

SYSTEM_API_SECRET=${SYSTEM_API_SECRET}
EOF

# Step 6: Configure Nginx Vhost for Clean URLs and Meta Cloud API
echo -e "${BLUE}[6/7] Configuring Nginx Vhost and routing...${NC}"
NGINX_CONF="/etc/nginx/sites-available/${DOMAIN}.conf"

if [ -f "$NGINX_CONF" ]; then
  # Point root to /public
  sed -i "s|root ${SITE_DIR};|root ${SITE_DIR}/public;|g" "$NGINX_CONF"

  # Inject custom rewrite rules if not already present
  if ! grep -q "api/send.php" "$NGINX_CONF"; then
    python3 -c "
with open('$NGINX_CONF', 'r') as f:
    content = f.read()

rules = '''
  # WhatsGateway Clean URLs & Meta Cloud API Routing
  location / {
    try_files \$uri \$uri/ /index.php?\$query_string;
  }

  location ~* ^/(v[0-9.]+/([0-9]+)/messages|api/messages|api/send|messages|send)/?$ {
    rewrite ^ /api/send.php last;
  }

  location ~* ^/media/([a-zA-Z0-9_.-]+)/?$ {
    rewrite ^/media/([a-zA-Z0-9_.-]+)/?\$ /media.php?file=\$1 last;
  }
'''

if 'location ~ \.php$' in content:
    content = content.replace('location ~ \.php$', rules + '\n  location ~ \.php$', 1)
    with open('$NGINX_CONF', 'w') as f:
        f.write(content)
" 2>/dev/null || true
  fi

  nginx -t && systemctl reload nginx
fi

# Step 7: Worker Dependencies & PM2 Process
echo -e "${BLUE}[7/7] Installing Node.js worker and launching PM2...${NC}"
mkdir -p "${SITE_DIR}/worker/sessions" "${SITE_DIR}/public/uploads"
chown -R "${SITE_USER}:${SITE_USER}" "${SITE_DIR}"
chmod -R 775 "${SITE_DIR}/worker/sessions" "${SITE_DIR}/public/uploads"

cd "${SITE_DIR}/worker"
sudo -u "${SITE_USER}" npm install --omit=dev

# Start worker under site user via PM2
sudo -u "${SITE_USER}" pm2 delete whatsgateway-worker 2>/dev/null || true
sudo -u "${SITE_USER}" pm2 start server.mjs --name "whatsgateway-worker"
sudo -u "${SITE_USER}" pm2 save

# Setup PM2 autostart on reboot
env PATH=$PATH:/usr/bin /usr/lib/node_modules/pm2/bin/pm2 startup systemd -u "${SITE_USER}" --hp "/home/${SITE_USER}" 2>/dev/null || true

echo -e "\n${GREEN}====================================================${NC}"
echo -e "${GREEN}   WhatsGateway Successfully Installed!            ${NC}"
echo -e "${GREEN}====================================================${NC}"
echo -e "Dashboard URL:  https://${DOMAIN}"
echo -e "API Base:       https://${DOMAIN}/api/messages"
echo -e "Database:       ${DB_NAME} (User: ${DB_USER}, Pass: ${DB_PASS})"
echo -e "Worker Status:  Port ${WORKER_PORT} (Managed by PM2 as '${SITE_USER}')"
echo -e "\n${YELLOW}Cloudflare / SSL Next Steps:${NC}"
echo -e "1. Point your domain DNS (A Record) to this server IP."
echo -e "2. In Cloudflare, set SSL/TLS mode to: Full (Strict)."
echo -e "3. Install SSL in CloudPanel: clpctl site:install:certificate:lets-encrypt --domainName=\"${DOMAIN}\""
echo -e "4. Check worker logs anytime: sudo -u ${SITE_USER} pm2 logs whatsgateway-worker\n"
