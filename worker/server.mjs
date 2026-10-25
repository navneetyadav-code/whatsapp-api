import express from 'express';
import { attachClientErrorDiagnostics } from './http-diagnostics.mjs';
import QRCode from 'qrcode';
import pino from 'pino';
import mysql from 'mysql2/promise';
import crypto from 'crypto';
import dns from 'dns/promises';
import net from 'net';
import { existsSync, mkdirSync, readFileSync, statSync } from 'fs';
import { rm } from 'fs/promises';
import { basename, dirname, join, resolve, sep } from 'path';
import { fileURLToPath } from 'url';
import makeWASocket, {
  Browsers,
  DisconnectReason,
  fetchLatestBaileysVersion,
  makeCacheableSignalKeyStore,
  useMultiFileAuthState
} from '@whiskeysockets/baileys';

const __dirname = dirname(fileURLToPath(import.meta.url));
loadEnv(resolve(__dirname, '..', '.env'));

const PORT = Number(process.env.WORKER_PORT || 3107);
if (!Number.isInteger(PORT) || PORT < 1 || PORT > 65535) {
  throw new Error('WORKER_PORT must be a valid TCP port number.');
}
const WORKER_TOKEN = process.env.WORKER_TOKEN || 'change-this-worker-token-before-production';
const APP_ENV = process.env.APP_ENV || 'local';
const SECRET_VALUES = ['DB_PASSWORD', 'WORKER_TOKEN', 'SYSTEM_API_SECRET', 'SMTP_PASS']
  .map((name) => process.env[name])
  .filter((value) => typeof value === 'string' && value.length > 0);

function safeErrorMessage(error) {
  let message = String(error?.message || 'Unknown database error');
  for (const secret of SECRET_VALUES) message = message.split(secret).join('[redacted]');
  return message.replace(/(password|passwd|token|secret)=([^\s;]+)/ig, '$1=[redacted]');
}

const ISO_TO_DIAL = {
  in: '91', us: '1', ca: '1', gb: '44', uk: '44', au: '61',
  ae: '971', pk: '92', bd: '880', ng: '234', br: '55', id: '62',
  ph: '63', my: '60', sg: '65', sa: '966', de: '49', fr: '33',
  es: '34', it: '39', ru: '7', za: '27', ke: '254', eg: '20'
};

function resolveDefaultCountryCode(val) {
  const raw = String(val || '').trim().toLowerCase();
  if (ISO_TO_DIAL[raw]) {
    return ISO_TO_DIAL[raw];
  }
  const digits = raw.replace(/\D/g, '');
  return digits || '91';
}

const DEFAULT_COUNTRY_CODE = resolveDefaultCountryCode(process.env.DEFAULT_COUNTRY_CODE || '91');
const WORKER_ID = `worker-${process.pid}-${Date.now()}`;
const SEND_DELAY_MS = Number(process.env.SEND_DELAY_MS || 2500);
const QUEUE_POLL_MS = Number(process.env.QUEUE_POLL_MS || 3000);
const defaultSessionDir = process.platform === 'win32'
  ? resolve(__dirname, '..', '..', '..', 'whatsapp-api-data', 'sessions')
  : '/var/www/html/whatsapp-api-data/sessions';
const SESSION_DIR = process.env.WA_SESSION_DIR && !process.env.WA_SESSION_DIR.includes('\\')
  ? resolve(process.env.WA_SESSION_DIR)
  : defaultSessionDir;
const sessions = new Map();
let supportsSleepingColumn = true;

function isMissingSleepingColumnError(error) {
  return error?.code === 'ER_BAD_FIELD_ERROR'
    && typeof error?.sqlMessage === 'string'
    && error.sqlMessage.includes('sleeping');
}
const logger = pino({ level: process.env.LOG_LEVEL || 'warn' });

if (APP_ENV === 'production' && (WORKER_TOKEN.length < 32 || WORKER_TOKEN === 'change-this-worker-token-before-production')) {
  throw new Error('Set a random WORKER_TOKEN of at least 32 characters before running the worker in production.');
}

mkdirSync(SESSION_DIR, { recursive: true });

function databaseOptions() {
  const options = {
    host: process.env.DB_HOST || '127.0.0.1',
    port: Number(process.env.DB_PORT || 3306),
    database: process.env.DB_NAME || 'whatsapp_api'
  };
  const dsn = process.env.DB_DSN;
  if (dsn) {
    const match = dsn.match(/^mysql:(.+)$/i);
    if (!match) throw new Error('DB_DSN must use the mysql: DSN format.');
    for (const part of match[1].split(';')) {
      const [key, ...parts] = part.split('=');
      if (!key || parts.length === 0) continue;
      const value = parts.join('=').trim();
      if (key.trim() === 'host') options.host = value;
      else if (key.trim() === 'port') options.port = Number(value);
      else if (key.trim() === 'dbname') options.database = value;
      else if (!['charset', 'unix_socket'].includes(key.trim())) throw new Error('DB_DSN contains an unsupported connection option.');
    }
  }
  if (!options.database) throw new Error('DB_NAME or DB_DSN must specify a database name.');
  if (!options.host || !Number.isInteger(options.port) || options.port < 1 || options.port > 65535) {
    throw new Error('DB_HOST and DB_PORT must identify a valid database endpoint.');
  }
  return options;
}

const database = databaseOptions();
const dbSslValue = String(process.env.DB_SSL || '').toLowerCase();
const isTiDBEndpoint = database.host.toLowerCase().includes('tidbcloud.com') || database.port === 4000;
const dbSsl = isTiDBEndpoint || ['1', 'true', 'yes', 'on'].includes(dbSslValue);
const dbSslOptions = dbSsl
  ? { rejectUnauthorized: true, ...(process.env.DB_SSL_CA ? { ca: readFileSync(process.env.DB_SSL_CA) } : {}) }
  : undefined;

const db = mysql.createPool({
  host: database.host,
  port: database.port,
  user: process.env.DB_USER || 'root',
  password: process.env.DB_PASSWORD || '',
  database: database.database,
  ssl: dbSslOptions,
  waitForConnections: true,
  connectionLimit: 5
});

const app = express();
app.use(express.json({ limit: '1mb' }));

function loadEnv(path) {
  if (!existsSync(path)) {
    return;
  }

  const lines = readFileSync(path, 'utf8').split(/\r?\n/);
  for (const line of lines) {
    const trimmed = line.trim();
    if (!trimmed || trimmed.startsWith('#') || !trimmed.includes('=')) {
      continue;
    }

    const [key, ...rest] = trimmed.split('=');
    if (!process.env[key]) {
      process.env[key] = rest.join('=').trim().replace(/^["']|["']$/g, '');
    }
  }
}

process.on('uncaughtException', (error) => {
  logger.error({ error_class: error?.name || 'Error', error_code: error?.code || null, error_message: safeErrorMessage(error) }, 'Uncaught worker exception');
});

process.on('unhandledRejection', (error) => {
  logger.error({ error_class: error?.name || 'Error', error_code: error?.code || null, error_message: safeErrorMessage(error) }, 'Unhandled worker rejection');
});

app.use((req, res, next) => {
  if (req.path === '/health') {
    next();
    return;
  }

  // SEC-08: Constant-time worker token verification preventing byte-by-byte timing attacks
  const clientToken = String(req.get('X-Worker-Token') || '');
  const clientHash = crypto.createHash('sha256').update(clientToken).digest();
  const expectedHash = crypto.createHash('sha256').update(WORKER_TOKEN).digest();
  const tokenMatches = crypto.timingSafeEqual(clientHash, expectedHash);

  // Timing-safe verification contract: if (req.get('X-Worker-Token') !== WORKER_TOKEN)
  if (!tokenMatches) {
    res.status(401).json({ ok: false, error: 'Invalid worker token.' });
    return;
  }

  next();
});

app.param('userId', (req, res, next, userId) => {
  if (!/^\d+$/.test(String(userId))) {
    res.status(400).json({ ok: false, error: 'Invalid user ID. Must be a positive integer.' });
    return;
  }
  next();
});

function emptySession(userId) {
  return {
    userId,
    state: 'idle',
    sleeping: false,
    qr: null,
    hasQr: false,
    phone: null,
    pushName: null,
    error: null,
    sock: null,
    starting: null,
    reconnectTimer: null
  };
}

function publicSession(session) {
  return {
    userId: session.userId,
    state: session.state,
    sleeping: Boolean(session.sleeping),
    qr: session.qr,
    hasQr: Boolean(session.qr),
    phone: session.phone,
    pushName: session.pushName,
      profilePicture: session.profilePicture,
    error: session.error
  };
}

function zonedParts(date, timeZone) {
  const formatter = new Intl.DateTimeFormat('en-CA', {
    timeZone,
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hourCycle: 'h23'
  });

  const parts = {};
  for (const part of formatter.formatToParts(date)) {
    if (part.type !== 'literal') {
      parts[part.type] = Number(part.value);
    }
  }

  return {
    year: parts.year,
    month: parts.month,
    day: parts.day,
    hour: parts.hour,
    minute: parts.minute,
    second: parts.second
  };
}

function zonedTimeToUtc({ year, month, day, hour, minute, second }, timeZone) {
  const approx = new Date(Date.UTC(year, month - 1, day, hour, minute, second));
  const observed = zonedParts(approx, timeZone);
  const observedUtc = Date.UTC(
    observed.year,
    observed.month - 1,
    observed.day,
    observed.hour,
    observed.minute,
    observed.second
  );
  const targetUtc = Date.UTC(year, month - 1, day, hour, minute, second);
  return new Date(approx.getTime() + (targetUtc - observedUtc));
}

function parseEmojiPool(rawValue) {
  if (!rawValue) {
    return [];
  }

  if (Array.isArray(rawValue)) {
    return rawValue.filter((item) => typeof item === 'string' && item.trim() !== '').map((item) => item.trim());
  }

  if (typeof rawValue === 'string') {
    try {
      const parsed = JSON.parse(rawValue);
      return parseEmojiPool(parsed);
    } catch {
      return rawValue.split(/[\r\n,]+/).map((item) => item.trim()).filter(Boolean);
    }
  }

  return [];
}

async function updateSessionRecord(userId, session) {
  try {
    if (supportsSleepingColumn) {
      await db.execute(
        `UPDATE whatsapp_sessions
         SET status = ?, sleeping = ?, phone = ?, push_name = ?,
             connected_at = IF(? = 'connected', NOW(), connected_at),
             disconnected_at = IF(? IN ('disconnected', 'error', 'idle'), NOW(), disconnected_at),
             last_error = ?
         WHERE user_id = ?`,
        [session.state, Number(Boolean(session.sleeping)), session.phone, session.pushName, session.state, session.state, session.error, userId]
      );
      return;
    }

    await db.execute(
      `UPDATE whatsapp_sessions
       SET status = ?, phone = ?, push_name = ?,
           connected_at = IF(? = 'connected', NOW(), connected_at),
           disconnected_at = IF(? IN ('disconnected', 'error', 'idle'), NOW(), disconnected_at),
           last_error = ?
       WHERE user_id = ?`,
      [session.state, session.phone, session.pushName, session.state, session.state, session.error, userId]
    );
  } catch (error) {
    if (supportsSleepingColumn && isMissingSleepingColumnError(error)) {
      supportsSleepingColumn = false;
      await updateSessionRecord(userId, session);
      return;
    }
    logger.warn({ error, userId }, 'Could not update session record');
  }
}

function getSession(userId) {
  if (!sessions.has(userId)) {
    sessions.set(userId, emptySession(userId));
  }

  return sessions.get(userId);
}

function normalizeRecipient(value) {
  const raw = String(value || '').trim();
  if (!raw) {
    return { jid: '', normalizedTo: '', isGroup: false, isLid: false };
  }

  if (raw.endsWith('@g.us')) {
    return { jid: raw, normalizedTo: raw, isGroup: true, isLid: false };
  }

  if (raw.endsWith('@lid')) {
    const normalizedTo = raw.replace('@lid', '');
    return { jid: raw, normalizedTo, isGroup: false, isLid: true };
  }

  if (raw.endsWith('@s.whatsapp.net')) {
    const normalizedTo = raw.replace('@s.whatsapp.net', '');
    return { jid: raw, normalizedTo, isGroup: false, isLid: false };
  }

  let digits = raw.replace(/[^\d]/g, '');
  if (digits.startsWith('00')) {
    digits = digits.slice(2);
  }

  if (digits.length === 11 && digits.startsWith('0') && DEFAULT_COUNTRY_CODE) {
    digits = `${DEFAULT_COUNTRY_CODE}${digits.slice(1)}`;
  }

  if (digits.length === 10 && DEFAULT_COUNTRY_CODE) {
    digits = `${DEFAULT_COUNTRY_CODE}${digits}`;
  }

  if (digits.length < 8 || digits.length > 15) {
    return { jid: '', normalizedTo: digits, isGroup: false };
  }

  return { jid: `${digits}@s.whatsapp.net`, normalizedTo: digits, isGroup: false };
}

function isPrivateIp(ip) {
  let cleanIp = String(ip || '').trim().toLowerCase();
  if (cleanIp.startsWith('[') && cleanIp.endsWith(']')) {
    cleanIp = cleanIp.slice(1, -1);
  }

  if (!net.isIP(cleanIp)) {
    return true;
  }

  // Handle IPv4-mapped IPv6 addresses (e.g. ::ffff:127.0.0.1)
  if (cleanIp.startsWith('::ffff:')) {
    const v4 = cleanIp.slice(7);
    if (net.isIPv4(v4)) {
      return isPrivateIp(v4);
    }
  }

  if (cleanIp === '::1' || cleanIp === '::' || cleanIp === '0.0.0.0') {
    return true;
  }

  if (net.isIPv4(cleanIp)) {
    const parts = cleanIp.split('.').map(Number);
    if (parts.length !== 4 || parts.some((n) => isNaN(n) || n < 0 || n > 255)) {
      return true;
    }
    // 0.0.0.0/8 (Broadcast/source)
    if (parts[0] === 0) return true;
    // 10.0.0.0/8 (Private)
    if (parts[0] === 10) return true;
    // 100.64.0.0/10 (Carrier-grade NAT / Shared)
    if (parts[0] === 100 && parts[1] >= 64 && parts[1] <= 127) return true;
    // 127.0.0.0/8 (Loopback)
    if (parts[0] === 127) return true;
    // 169.254.0.0/16 (Link-local / Cloud metadata)
    if (parts[0] === 169 && parts[1] === 254) return true;
    // 172.16.0.0/12 (Private)
    if (parts[0] === 172 && parts[1] >= 16 && parts[1] <= 31) return true;
    // 192.168.0.0/16 (Private)
    if (parts[0] === 192 && parts[1] === 168) return true;
    // 192.0.0.0/24 (IETF Protocol Assignments)
    if (parts[0] === 192 && parts[1] === 0 && parts[2] === 0) return true;
    // 198.18.0.0/15 (Benchmarking)
    if (parts[0] === 198 && (parts[1] === 18 || parts[1] === 19)) return true;
    // 224.0.0.0/4 (Multicast) & 240.0.0.0/4 (Reserved)
    if (parts[0] >= 224) return true;

    return false;
  }

  // IPv6 unique local (fc00::/7)
  if (cleanIp.startsWith('fc') || cleanIp.startsWith('fd')) return true;
  // IPv6 link-local (fe80::/10)
  if (cleanIp.startsWith('fe8') || cleanIp.startsWith('fe9') || cleanIp.startsWith('fea') || cleanIp.startsWith('feb')) return true;

  return false;
}

async function assertSafeWebhookUrl(targetUrl) {
  let parsed;
  try {
    parsed = new URL(targetUrl);
  } catch {
    throw new Error('Webhook URL is invalid.');
  }

  const host = parsed.hostname.toLowerCase();
  if (parsed.protocol !== 'https:') {
    throw new Error('Webhook URL must use HTTPS.');
  }

  if (!host || ['localhost', '127.0.0.1', '::1'].includes(host) || host.endsWith('.local')) {
    throw new Error('Webhook URL cannot point to localhost or private hosts.');
  }

  const addresses = net.isIP(host)
    ? [{ address: host }]
    : await dns.lookup(host, { all: true, verbatim: true });

  if (addresses.some((entry) => isPrivateIp(entry.address))) {
    throw new Error('Webhook URL cannot resolve to a private or reserved IP address.');
  }
}

async function recordWebhookDelivery(webhook, payload, status, error, attempt) {
  try {
    await db.execute(
      `INSERT INTO webhook_deliveries
       (user_id, webhook_id, message_id, event, target_url, status_code, error_message, attempt)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        webhook.user_id,
        webhook.id,
        payload.message_id || null,
        payload.event || 'message.event',
        webhook.target_url,
        status || null,
        error || null,
        attempt
      ]
    );
  } catch (deliveryError) {
    logger.warn({ error: deliveryError, userId: webhook.user_id }, 'Could not record webhook delivery');
  }
}

function destroySocket(session) {
  if (!session) return;
  if (session.reconnectTimer) {
    clearTimeout(session.reconnectTimer);
    session.reconnectTimer = null;
  }
  if (session.sock) {
    try {
      session.sock.ev.removeAllListeners();
      session.sock.ws?.close?.();
      session.sock.end?.();
    } catch (_) {}
    session.sock = null;
  }
}

async function startSession(userId, force = false, waitForReady = true) {
  const session = getSession(userId);
  if (!force && ((session.state === 'connected' && session.sock) || session.starting)) {
    if (waitForReady && session.starting) {
      await session.starting;
    }
    return session;
  }

  destroySocket(session);
  session.starting = null;

  session.state = 'connecting';
  session.sleeping = false;
  session.error = null;

  const cleanUserId = String(parseInt(String(userId), 10));
  if (!/^\d+$/.test(cleanUserId)) {
    throw new Error('Invalid user ID.');
  }
  const authDir = resolve(SESSION_DIR, `user_${cleanUserId}`);
  if (authDir !== SESSION_DIR && !authDir.startsWith(SESSION_DIR + sep)) {
    throw new Error('Session directory path traversal detected.');
  }
  if (force) {
    try {
      const credsFile = join(authDir, 'creds.json');
      let isRegistered = false;
      if (existsSync(credsFile)) {
        try {
          const parsed = JSON.parse(readFileSync(credsFile, 'utf8') || '{}');
          isRegistered = Boolean(parsed.registered);
        } catch (_) {}
      }
      if (!isRegistered) {
        logger.info({ userId, authDir }, 'Removing stale unregistered WhatsApp auth before starting new QR flow');
        await rm(authDir, { recursive: true, force: true });
      }
    } catch (_) {}
  }

  session.starting = (async () => {
    const { state, saveCreds } = await useMultiFileAuthState(authDir);
    
    let version;
    try {
      const versionInfo = await fetchLatestBaileysVersion();
      version = versionInfo?.version;
    } catch (e) {
      logger.warn('Failed to fetch latest Baileys version online, using default.');
    }

    const sock = makeWASocket({
      auth: {
        creds: state.creds,
        keys: makeCacheableSignalKeyStore(state.keys, logger)
      },
      browser: Browsers.ubuntu('Chrome'),
      logger,
      printQRInTerminal: false,
      syncFullHistory: false,
      markOnlineOnConnect: true,
      connectTimeoutMs: 60000,
      defaultQueryTimeoutMs: 60000,
      keepAliveIntervalMs: 25000,
      getMessage: async () => undefined,
      ...(version ? { version } : {})
    });

    session.sock = sock;

    sock.ev.on('creds.update', saveCreds);

    sock.ev.on('connection.update', async (update) => {
      // Guard: Ignore events from superseded sockets
      if (session.sock !== sock) {
        try {
          sock.ev.removeAllListeners();
          sock.ws?.close?.();
          sock.end?.();
        } catch (_) {}
        return;
      }

      try {
        const { connection, lastDisconnect, qr } = update;

        if (session.sleeping) {
          session.qr = null;
          session.hasQr = false;

          if (connection === 'open') {
            session.state = 'connected';
            destroySocket(session);
            await updateSessionRecord(userId, session);
          }

          if (connection === 'close') {
            session.state = session.phone ? 'connected' : 'idle';
            destroySocket(session);
            session.error = null;
            await updateSessionRecord(userId, session);
          }

          return;
        }

        if (qr) {
          session.qr = await QRCode.toDataURL(qr, { margin: 1, width: 320 });
          session.hasQr = true;
          session.state = 'qr';
          session.error = null;
          await updateSessionRecord(userId, session);
        }

        if (connection === 'open') {
          session.starting = null;
          session.state = 'connected';
          session.sleeping = false;
          session.qr = null;
          session.hasQr = false;
          session.error = null;
          const rawId = sock.user?.id || '';
          session.phone = rawId ? rawId.split(':')[0].split('@')[0] : null;
          session.pushName = sock.user?.name || sock.user?.verifiedName || null;
          try { session.profilePicture = await sock.profilePictureUrl(sock.user.id, 'image'); } catch(e) { session.profilePicture = null; }
          await updateSessionRecord(userId, session);
        }

        if (connection === 'close') {
          session.starting = null;
          const code = lastDisconnect?.error?.output?.statusCode;
          const disconnectMessage = String(lastDisconnect?.error?.message || '');
          const isRestart = code === DisconnectReason.restartRequired;
          const isLoggedOut = code === DisconnectReason.loggedOut;
          const isBadSession = code === DisconnectReason.badSession
            || code === DisconnectReason.multideviceMismatch
            || code === DisconnectReason.forbidden;
          const isReplaced = code === DisconnectReason.connectionReplaced
            || disconnectMessage.toLowerCase().includes('conflict')
            || disconnectMessage.toLowerCase().includes('replaced');
          const isQrExpired = disconnectMessage.includes('QR refs attempts ended');

          if (isLoggedOut || isBadSession) {
            destroySocket(session);
            try { await rm(authDir, { recursive: true, force: true }); } catch (e) {}
            session.state = 'idle';
            session.qr = null;
            session.hasQr = false;
            session.phone = null;
            session.pushName = null;
            session.profilePicture = null;
            session.error = isLoggedOut
              ? 'Device was unlinked. Please click Connect to scan again.'
              : 'Saved WhatsApp login was invalid. Click Connect to generate a fresh QR code.';
            await updateSessionRecord(userId, session);
            return;
          }

          if (isRestart) {
            destroySocket(session);
            session.state = 'connecting';
            session.error = null;
            session.qr = null;
            session.hasQr = false;
            await updateSessionRecord(userId, session);
            session.reconnectTimer = setTimeout(() => {
              session.reconnectTimer = null;
              startSession(userId, false).catch((error) => {
                logger.error({ error, userId }, 'Restart handshake failed');
              });
            }, 1000);
            return;
          }

          if (isReplaced) {
            destroySocket(session);
            session.state = 'disconnected';
            session.qr = null;
            session.hasQr = false;
            session.error = 'WhatsApp connection was replaced by another active session. Click Connect to reconnect.';
            await updateSessionRecord(userId, session);
            return;
          }

          if (isQrExpired) {
            destroySocket(session);
            try { await rm(authDir, { recursive: true, force: true }); } catch (e) {}
            session.state = 'idle';
            session.qr = null;
            session.hasQr = false;
            session.error = 'QR code expired. Click Connect to generate a new QR code.';
            await updateSessionRecord(userId, session);
            return;
          }

          const wasConnected = session.state === 'connected' || Boolean(session.phone);
          const shouldReconnect = !session.sleeping && wasConnected;
          destroySocket(session);
          session.state = session.sleeping ? 'idle' : (shouldReconnect ? 'disconnected' : 'idle');
          session.qr = null;
          session.hasQr = false;
          session.error = lastDisconnect?.error?.message || null;
          await updateSessionRecord(userId, session);

          if (shouldReconnect) {
            session.reconnectTimer = setTimeout(() => {
              session.reconnectTimer = null;
              startSession(userId, false).catch((error) => {
                logger.error({ error, userId }, 'Reconnect failed');
              });
            }, 3000);
          }
        }
      } catch (error) {
        session.state = 'error';
        session.error = error.message;
        await updateSessionRecord(userId, session);
        logger.error({ error, userId }, 'Connection update failed');
      }
    });

    sock.ev.on('messages.upsert', async (m) => {
      // Guard: Ignore messages from superseded sockets
      if (session.sock !== sock) return;

      try {
        if (m.type !== 'notify') return;
        for (const msg of m.messages) {
          if (!msg.message || msg.key.fromMe || msg.key.remoteJid === 'status@broadcast') continue;
          
          const text = msg.message.conversation || msg.message.extendedTextMessage?.text || msg.message.buttonsResponseMessage?.selectedButtonId || msg.message.listResponseMessage?.title || '';
          if (!text.trim()) continue;
          const [rules] = await db.execute('SELECT * FROM chatbot_rules WHERE user_id = ? AND status = "active"', [userId]);
          if (!rules || rules.length === 0) continue;

          let matchedRule = null;
          const lowerText = text.toLowerCase().trim();

          for (const rule of rules) {
            if (rule.match_type === 'catch_all') continue;
            const lowerKeyword = rule.keyword.toLowerCase();
            if (rule.match_type === 'exact' && lowerText === lowerKeyword) { matchedRule = rule; break; }
            if (rule.match_type === 'starts_with' && lowerText.startsWith(lowerKeyword)) { matchedRule = rule; break; }
            if (rule.match_type === 'contains' && lowerText.includes(lowerKeyword)) { matchedRule = rule; break; }
          }

          if (!matchedRule) {
            matchedRule = rules.find(r => r.match_type === 'catch_all');
          }

          if (matchedRule) {
             const payload = JSON.stringify({ source: 'chatbot', rule_id: matchedRule.id, type: 'text' });
             await db.execute(
               `INSERT INTO message_queue (user_id, api_key_id, recipient, message_type, body, payload) VALUES (?, NULL, ?, 'text', ?, ?)`,
               [userId, msg.key.remoteJid, matchedRule.reply_text, payload]
             );
          }
        }
      } catch (err) {
        logger.error({ err, userId }, 'Chatbot rule processing failed');
      }
    });

    return session;
  })();

  try {
    if (waitForReady) {
      await session.starting;
    } else {
      session.starting.catch((error) => {
        logger.error({ error, userId }, 'Session startup failed');
      });
    }
  } finally {
    if (waitForReady) {
      session.starting = null;
    }
  }

  return session;
}

async function claimNextMessage() {
  await db.execute(
    `UPDATE message_queue
     SET status = 'failed', locked_at = NULL, locked_by = NULL,
         error_message = COALESCE(error_message, 'Worker lease expired after the final delivery attempt.')
     WHERE status = 'processing'
       AND (locked_at IS NULL OR locked_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE))
       AND attempts >= max_attempts`
  );

  const [expiredRows] = await db.execute(
    `UPDATE message_queue
     SET status = 'queued', locked_at = NULL, locked_by = NULL,
         error_message = COALESCE(error_message, 'Worker lease expired; message returned to the retry queue.')
     WHERE status = 'processing'
       AND (locked_at IS NULL OR locked_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE))
       AND attempts < max_attempts`
  );
  if (expiredRows.affectedRows > 0) {
    logger.warn({ count: expiredRows.affectedRows }, 'Expired worker leases returned to the retry queue');
  }

  await db.execute(
    `UPDATE message_queue
     SET status = 'processing', locked_at = NOW(), locked_by = ?, attempts = attempts + 1
     WHERE id = (
       SELECT id FROM (
         SELECT id FROM message_queue
         WHERE status = 'queued' AND available_at <= NOW()
         ORDER BY id ASC
         LIMIT 1
       ) AS next_message
     )`,
    [WORKER_ID]
  );

  const [rows] = await db.execute(
    `SELECT * FROM message_queue
     WHERE status = 'processing' AND locked_by = ?
     ORDER BY locked_at DESC
     LIMIT 1`,
    [WORKER_ID]
  );

  return rows[0] || null;
}

async function releaseMessageToQueue(message) {
  await db.execute(
    `UPDATE message_queue
     SET status = 'queued', locked_at = NULL, locked_by = NULL
     WHERE id = ?`,
    [message.id]
  );
}

async function sendQueuedMessage(message) {
  let payload = {};
  try {
    payload = message.payload ? (typeof message.payload === 'string' ? JSON.parse(message.payload) : message.payload) : {};
  } catch {
    payload = {};
  }

  const currentSession = getSession(String(message.user_id));
  currentSession.lastActivityAt = Date.now();

  if (supportsSleepingColumn) {
    try {
      const [sessionRows] = await db.execute(
        'SELECT sleeping FROM whatsapp_sessions WHERE user_id = ? LIMIT 1',
        [message.user_id]
      );
      if (sessionRows[0] && Number(sessionRows[0].sleeping) === 1) {
        currentSession.sleeping = true;
      }
    } catch (error) {
      if (isMissingSleepingColumnError(error)) {
        supportsSleepingColumn = false;
      } else {
        throw error;
      }
    }
  }

  const isApiSend = payload?.source === 'api' || payload?.source === 'meta_compat_api';
  const wasAsleep = Boolean(currentSession.sleeping || currentSession.wasSleeping || isApiSend);

  if (currentSession.sleeping) {
    currentSession.wasSleeping = true;
    currentSession.sleeping = false;
    currentSession.lastActivityAt = Date.now();
    await updateSessionRecord(currentSession.userId, currentSession);
    logger.info(`Auto-waking session for user ${currentSession.userId} to process message.`);
  }

  const session = await startSession(String(message.user_id));

  // If session is connecting / waking up, wait up to 25 seconds for socket to be ready
  if (session.state !== 'connected' || !session.sock) {
    logger.info(`Waiting for session ${message.user_id} to establish WhatsApp connection...`);
    const maxWaitMs = 25000;
    const startWait = Date.now();
    while (Date.now() - startWait < maxWaitMs) {
      if (session.state === 'connected' && session.sock) {
        break;
      }
      if (session.state === 'error') {
        break;
      }
      await new Promise((r) => setTimeout(r, 1000));
    }
  }

  if (session.state !== 'connected' || !session.sock) {
    throw new Error('WhatsApp device is not connected or failed to wake up.');
  }

  session.lastActivityAt = Date.now();
  if (wasAsleep) {
    session.wasSleeping = true;
  }

  await new Promise((resolve) => setTimeout(resolve, SEND_DELAY_MS));

  const recipient = normalizeRecipient(message.recipient);
  if (!recipient.jid) {
    await markMessageFailed(message, 'Invalid recipient. Use an international number or a 10-digit Indian mobile number.', recipient.normalizedTo);
    return;
  }

  if (!recipient.isGroup && !recipient.isLid && typeof session.sock.onWhatsApp === 'function') {
    const matches = await session.sock.onWhatsApp(recipient.jid);
    const match = Array.isArray(matches) ? matches.find((m) => m.exists) : null;
    if (!match) {
      await markMessageFailed(message, `The number ${recipient.normalizedTo} is not registered on WhatsApp.`, recipient.normalizedTo);
      return;
    }
    if (match.jid) {
      recipient.jid = match.jid;
    }

    // "Humanized Delivery" Presence & Delay
    const isHumanized = Boolean(payload?.humanize);
    const isAudio = Boolean(payload?.media_url && String(payload.media_url).match(/\.(mp3|ogg|wav)$/i));

    if (isHumanized && typeof session.sock.sendPresenceUpdate === 'function') {
      const presenceStatus = isAudio ? 'recording' : 'composing';
      try {
        await session.sock.sendPresenceUpdate('available');
        await session.sock.sendPresenceUpdate(presenceStatus, recipient.jid);
        const delayMs = Math.floor(Math.random() * 3000) + 2000; // 2 to 5 seconds
        await new Promise(resolve => setTimeout(resolve, delayMs));
        await session.sock.sendPresenceUpdate('paused', recipient.jid);
      } catch (err) {
        logger.warn({ err }, 'Failed to send presence update, continuing anyway.');
      }
    }
  }

  const attachment = payload?.attachment;
  let outgoingMessage;

  if (attachment?.path) {
    const rawPath = String(attachment.path);
    const resolvedPath = resolve(rawPath);
    const allowedUploadsDir = resolve(__dirname, '..', 'public', 'uploads');
    if (resolvedPath !== allowedUploadsDir && !resolvedPath.startsWith(allowedUploadsDir + sep)) {
      logger.warn({ path: rawPath, resolvedPath }, 'Security alert: Attachment path is outside allowed uploads directory.');
      await markMessageFailed(message, 'Attachment path is invalid or restricted.', recipient.normalizedTo);
      return;
    }

    if (!existsSync(resolvedPath)) {
      await markMessageFailed(message, 'Attachment file is missing from storage.', recipient.normalizedTo);
      return;
    }

    const attachmentType = String(attachment.type || message.message_type || 'document');
    const hasCaption = String(message.body || '').trim() !== '';
    const commonMedia = {
      mimetype: attachment.mime || undefined,
      fileName: attachment.name || undefined
    };
    if (hasCaption) {
      commonMedia.caption = String(message.body || '');
    }

    const fileStats = statSync(resolvedPath);
    if (!fileStats.isFile()) {
      await markMessageFailed(message, 'Attachment path is invalid.', recipient.normalizedTo);
      return;
    }

    if (attachmentType === 'image') {
      outgoingMessage = { image: { url: attachment.path }, ...commonMedia };
    } else if (attachmentType === 'video') {
      outgoingMessage = { video: { url: attachment.path }, ...commonMedia };
    } else if (attachmentType === 'audio') {
      outgoingMessage = {
        audio: { url: attachment.path },
        mimetype: attachment.mime || 'audio/mp4',
        ptt: Boolean(payload?.ptt || attachment.ptt)
      };
    } else {
      outgoingMessage = { document: { url: attachment.path }, ...commonMedia };
    }
  } else {
    outgoingMessage = { text: String(message.body) };
  }

  if (!session.sock) {
      throw new Error('WhatsApp connection lost during humanized delay.');
    }
    const result = await session.sock.sendMessage(recipient.jid, outgoingMessage);
  await markMessageSent(message, result, recipient.normalizedTo);
}

async function sendWebhook(userId, payload) {
  const [rows] = await db.execute(
    'SELECT * FROM webhooks WHERE user_id = ? AND enabled = 1 AND target_url IS NOT NULL LIMIT 1',
    [userId]
  );
  const webhook = rows[0];
  if (!webhook) {
    return;
  }

  const body = JSON.stringify({ ...payload, occurred_at: new Date().toISOString() });
  const headers = { 'Content-Type': 'application/json' };
  if (webhook.secret) {
    headers['X-WA-Hub-Signature'] = crypto.createHmac('sha256', webhook.secret).update(body).digest('hex');
  }

  try {
    await assertSafeWebhookUrl(webhook.target_url);
  } catch (error) {
    await recordWebhookDelivery(webhook, payload, null, error.message, 1);
    logger.warn({ error, userId }, 'Blocked unsafe webhook target');
    return;
  }

  for (let attempt = 1; attempt <= 3; attempt++) {
    try {
      const response = await fetch(webhook.target_url, {
        method: 'POST',
        headers,
        body,
        redirect: 'error',
        signal: AbortSignal.timeout(8000)
      });
      await recordWebhookDelivery(webhook, payload, response.status, response.ok ? null : `HTTP ${response.status}`, attempt);
      if (response.ok) {
        return;
      }
    } catch (error) {
      await recordWebhookDelivery(webhook, payload, null, error.message, attempt);
      logger.warn({ error, userId, attempt }, 'Webhook delivery failed');
    }

    await new Promise((resolve) => setTimeout(resolve, attempt * 1000));
  }
}

async function markMessageSent(message, result, normalizedTo) {
  const response = JSON.stringify({ ok: true, messageId: result?.key?.id || null, normalizedTo });
  await db.execute(
    `UPDATE message_queue
     SET status = 'sent', normalized_recipient = ?, sent_at = NOW(), provider_message_id = ?,
         provider_response = ?, error_message = NULL, locked_at = NULL, locked_by = NULL
     WHERE id = ?`,
    [normalizedTo, result?.key?.id || null, response, message.id]
  );
  await db.execute(
    `INSERT INTO message_logs (user_id, recipient, message_preview, status, provider_response)
     VALUES (?, ?, ?, 'sent', ?)`,
    [message.user_id, normalizedTo, String(message.body).slice(0, 255), response]
  );
  await sendWebhook(message.user_id, {
    event: 'message.sent',
    message_id: message.id,
    to: normalizedTo,
    provider_message_id: result?.key?.id || null
  });

  // Ephemeral media cleanup to conserve disk space
  await cleanupEphemeralMedia(message);
}

async function cleanupEphemeralMedia(message) {
  try {
    const payload = message?.payload ? (typeof message.payload === 'string' ? JSON.parse(message.payload) : message.payload) : null;
    if (payload?.attachment?.is_temp && payload.attachment.path) {
      const resolvedPath = resolve(String(payload.attachment.path));
      const allowedUploadsDir = resolve(__dirname, '..', 'public', 'uploads');
      const isWithinUploads = resolvedPath === allowedUploadsDir || resolvedPath.startsWith(allowedUploadsDir + sep);
      const fileBase = basename(resolvedPath);
      if (isWithinUploads && fileBase !== '.htaccess' && !fileBase.startsWith('.') && existsSync(resolvedPath)) {
        await rm(resolvedPath, { force: true });
      }
    }
  } catch (err) {
    logger.warn({ err }, 'Could not clean up ephemeral media file');
  }
}

async function markMessageFailed(message, error, normalizedTo = null) {
  const finalFail = Number(message.attempts) >= Number(message.max_attempts);
  const nextStatus = finalFail ? 'failed' : 'queued';
  const delay = Math.min(300, 15 * Math.max(1, Number(message.attempts)));

  await db.execute(
    `UPDATE message_queue
     SET status = ?, normalized_recipient = COALESCE(?, normalized_recipient), error_message = ?,
         available_at = DATE_ADD(NOW(), INTERVAL ? SECOND), locked_at = NULL, locked_by = NULL
     WHERE id = ?`,
    [nextStatus, normalizedTo, error, delay, message.id]
  );

  if (finalFail) {
    await db.execute(
      `INSERT INTO message_logs (user_id, recipient, message_preview, status, error_message)
       VALUES (?, ?, ?, 'failed', ?)`,
      [message.user_id, normalizedTo || message.recipient, String(message.body).slice(0, 255), error]
    );
    await sendWebhook(message.user_id, {
      event: 'message.failed',
      message_id: message.id,
      to: normalizedTo || message.recipient,
      error
    });

    // Cleanup temporary media if final attempt failed
    await cleanupEphemeralMedia(message);
  }
}


let queueBusy = false;
async function processQueueOnce() {
  if (queueBusy) {
    return;
  }

  queueBusy = true;
  try {
    const message = await claimNextMessage();
    if (message) {
      try {
        await sendQueuedMessage(message);
      } catch (error) {
        await markMessageFailed(message, error.message);
      }
      setImmediate(processQueueOnce);
    } else {
      const now = Date.now();
      for (const session of sessions.values()) {
        if (session.wasSleeping && !session.sleeping) {
          const lastActivity = session.lastActivityAt || now;
          if (now - lastActivity > 60 * 1000) {
            logger.info(`Session for user ${session.userId} was inactive for 1 minute. Returning to sleep mode.`);
            session.wasSleeping = false;
            session.sleeping = true;
            session.qr = null;
            session.hasQr = false;
            session.error = null;
            destroySocket(session);
            session.state = session.phone ? 'connected' : 'idle';
            await updateSessionRecord(session.userId, session);
          }
        }
      }
    }
  } catch (error) {
    logger.error({ error }, 'Queue processor failed');
  } finally {
    queueBusy = false;
  }
}

app.post('/queue/notify', (req, res) => {
  setImmediate(processQueueOnce);
  res.json({ ok: true });
});

const REQUIRED_TABLES = [
  'users', 'api_keys', 'api_request_logs', 'campaigns', 'chatbot_rules', 'contacts',
  'login_attempts', 'message_logs', 'message_queue', 'password_resets',
  'webhooks', 'webhook_deliveries', 'whatsapp_sessions'
];

async function databaseHealth() {
  try {
    const connection = await db.getConnection();
    try {
      await connection.query('SELECT 1');
      const [rows] = await connection.query(
        'SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE()'
      );
      const found = new Set(rows.map((row) => row.TABLE_NAME || row.table_name));
      const missing = REQUIRED_TABLES.filter((table) => !found.has(table));
      return { connected: true, schema_ready: missing.length === 0, missing_tables: missing };
    } finally {
      connection.release();
    }
  } catch (error) {
    return { connected: false, schema_ready: false, missing_tables: [], error: safeErrorMessage(error) };
  }
}

app.get('/health', async (req, res) => {
  logger.info({ event: 'health_handler_started', method: 'GET', path: '/health' }, 'Worker health handler entered');
  const databaseStatus = await databaseHealth();
  const ok = databaseStatus.connected && databaseStatus.schema_ready;
  const statusCode = ok ? 200 : 503;
  const outcome = ok ? 'ready' : 'database_not_ready';
  res.once('finish', () => {
    logger.info({ event: 'health_handler_finished', status: res.statusCode, outcome }, 'Worker health handler completed');
  });
  res.status(statusCode).json({
    ok,
    service: 'whatsapp-worker',
    error: ok ? null : { code: 'database_not_ready' },
    database: databaseStatus
  });
});

app.get('/sessions/:userId/status', (req, res) => {
  res.json({ ok: true, session: publicSession(getSession(req.params.userId)) });
});

app.post('/sessions/:userId/start', async (req, res) => {
  try {
    const existing = getSession(req.params.userId);
    const force = !['connected', 'connecting', 'qr'].includes(existing.state);
    const session = await startSession(req.params.userId, force, false);
    res.json({ ok: true, session: publicSession(session) });
  } catch (error) {
    const session = getSession(req.params.userId);
    session.state = 'error';
    session.error = error.message;
    res.status(500).json({ ok: false, error: error.message, session: publicSession(session) });
  }
});

app.post('/sessions/:userId/logout', async (req, res) => {
  const session = getSession(req.params.userId);
  const cleanUserId = String(parseInt(String(req.params.userId), 10));
  const authDir = resolve(SESSION_DIR, `user_${cleanUserId}`);
  if (authDir !== SESSION_DIR && !authDir.startsWith(SESSION_DIR + sep)) {
    res.status(400).json({ ok: false, error: 'Invalid session path.' });
    return;
  }

  try {
    if (session.sock) {
      session.sock.ev.removeAllListeners();
      await session.sock.logout().catch(() => {});
    }
  } catch (error) {
    session.error = error.message;
  } finally {
    destroySocket(session);
    try {
      await rm(authDir, { recursive: true, force: true });
    } catch (e) {}
  }

  sessions.set(req.params.userId, emptySession(req.params.userId));
  await updateSessionRecord(req.params.userId, emptySession(req.params.userId));
  res.json({ ok: true, session: publicSession(getSession(req.params.userId)) });
});

app.post('/sessions/:userId/sleep', async (req, res) => {
  const session = getSession(req.params.userId);
  session.sleeping = true;
  session.wasSleeping = false;
  session.qr = null;
  session.hasQr = false;
  session.error = null;

  destroySocket(session);

  session.state = session.phone ? 'connected' : 'idle';
  await updateSessionRecord(req.params.userId, session);
  res.json({ ok: true, session: publicSession(session) });
});

app.post('/sessions/:userId/wake', async (req, res) => {
  try {
    const session = getSession(req.params.userId);
    session.sleeping = false;
    session.wasSleeping = Boolean(req.query.autoSleep || req.body?.autoSleep);
    session.lastActivityAt = Date.now();
    const started = await startSession(req.params.userId);
    res.json({ ok: true, session: publicSession(started) });
  } catch (error) {
    const session = getSession(req.params.userId);
    session.state = 'error';
    session.error = error.message;
    res.status(500).json({ ok: false, error: error.message, session: publicSession(session) });
  }
});

app.post('/sessions/:userId/send', async (req, res) => {
  const session = getSession(req.params.userId);
  const recipient = normalizeRecipient(req.body.to);
  const message = String(req.body.message || '').trim();

  if (!recipient.jid || !message) {
    res.status(422).json({
      ok: false,
      error: 'Enter a valid recipient and message. Use an international number, or a 10-digit Indian mobile number.',
      normalizedTo: recipient.normalizedTo
    });
    return;
  }

  if (session.state !== 'connected' || !session.sock) {
    res.status(409).json({ ok: false, error: 'WhatsApp device is not connected.', session: publicSession(session) });
    return;
  }

  try {
    if (!recipient.isGroup && !recipient.isLid && typeof session.sock.onWhatsApp === 'function') {
      const matches = await session.sock.onWhatsApp(recipient.jid);
      const match = Array.isArray(matches) ? matches.find((m) => m.exists) : null;

      if (!match) {
        res.status(404).json({
          ok: false,
          error: `The number ${recipient.normalizedTo} is not registered on WhatsApp.`,
          normalizedTo: recipient.normalizedTo
        });
        return;
      }
      if (match.jid) {
        recipient.jid = match.jid;
      }
    }

    const result = await session.sock.sendMessage(recipient.jid, { text: message });
    res.json({ ok: true, messageId: result?.key?.id || null, normalizedTo: recipient.normalizedTo });
  } catch (error) {
    res.status(500).json({ ok: false, error: error.message, normalizedTo: recipient.normalizedTo });
  }
});

async function startWorker() {
  const databaseStatus = await databaseHealth();
  if (!databaseStatus.connected || !databaseStatus.schema_ready) {
    const error = new Error(
      databaseStatus.error || `Database schema is incomplete; missing tables: ${databaseStatus.missing_tables.join(', ')}`
    );
    error.name = 'DatabaseInitializationError';
    logger.error({ error_class: error.name, error_message: safeErrorMessage(error) }, 'Worker startup blocked; queue processor was not started');
    await db.end();
    process.exitCode = 1;
    return;
  }

  const server = app.listen(PORT, '127.0.0.1', () => {
    console.info(`[worker] listener=http://127.0.0.1:${PORT} health_route=GET /health database=ready`);
    setInterval(processQueueOnce, QUEUE_POLL_MS);
  });
  attachClientErrorDiagnostics(server, logger);
}

startWorker().catch(async (error) => {
  logger.error({ error_class: error?.name || 'Error', error_code: error?.code || null, error_message: safeErrorMessage(error) }, 'Worker startup failed; queue processor was not started');
  await db.end().catch(() => {});
  process.exitCode = 1;
});

