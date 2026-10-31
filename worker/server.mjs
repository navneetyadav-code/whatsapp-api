import express from 'express';
import { attachClientErrorDiagnostics } from './http-diagnostics.mjs';
import QRCode from 'qrcode';
import pino from 'pino';
import mysql from 'mysql2/promise';
import crypto from 'crypto';
import dns from 'dns/promises';
import net from 'net';
import { existsSync, mkdirSync, readFileSync, readdirSync, statSync } from 'fs';
import { rm } from 'fs/promises';
import { dirname, join, resolve, basename } from 'path';
import { fileURLToPath } from 'url';
import makeWASocket, {
  Browsers,
  DisconnectReason,
  fetchLatestBaileysVersion,
  makeCacheableSignalKeyStore,
  useMultiFileAuthState
} from '@whiskeysockets/baileys';
import {
  detectFrustrationAndEscalation,
  retrieveRelevantKnowledge,
  buildPromptWithRag,
  generateAiReply
} from './ai-agent.mjs';
import { globalDebouncer } from './chat-debouncer.mjs';
import { globalGreetingCache } from './chat-greeting-cache.mjs';
import { generateSessionUuid, resolveActiveSession, closeSession, isWakeupTrigger, isTopicResetTrigger } from './chat-session-manager.mjs';
import { ChatMemoryEngine } from './chat-memory.mjs';
import { ChatFactMemory } from './chat-fact-memory.mjs';
import { ChatPresenceManager } from './chat-presence.mjs';
import { ChatResolutionEngine } from './chat-resolution.mjs';
import { detectIntents, composeSkillsAndTools } from './intent-router.mjs';
import { UniversalToolsRegistry } from './tools-engine.mjs';

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
let supportsTimelockColumns = true;

function isMissingSleepingColumnError(error) {
  return error?.code === 'ER_BAD_FIELD_ERROR'
    && typeof error?.sqlMessage === 'string'
    && error.sqlMessage.includes('sleeping');
}

function isMissingTimelockColumnError(error) {
  return error?.code === 'ER_BAD_FIELD_ERROR'
    && typeof error?.sqlMessage === 'string'
    && error.sqlMessage.includes('reachout_timelock');
}
const logger = pino({ level: process.env.LOG_LEVEL || 'warn' });

if (APP_ENV === 'production' && (WORKER_TOKEN.length < 32 || WORKER_TOKEN === 'change-this-worker-token-before-production')) {
  throw new Error('Set a random WORKER_TOKEN of at least 32 characters before running the worker in production.');
}

mkdirSync(SESSION_DIR, { recursive: true });

function createMemoryCache(maxEntries = 2000, ttlMs = 15 * 60 * 1000) {
  const store = new Map();
  return {
    get(key) {
      const item = store.get(key);
      if (!item) return undefined;
      if (Date.now() > item.expiresAt) {
        store.delete(key);
        return undefined;
      }
      return item.value;
    },
    set(key, value) {
      if (store.size >= maxEntries) {
        const oldestKey = store.keys().next().value;
        store.delete(oldestKey);
      }
      store.set(key, { value, expiresAt: Date.now() + ttlMs });
    },
    del(key) {
      store.delete(key);
    },
    flushAll() {
      store.clear();
    }
  };
}

const msgRetryCounterCache = createMemoryCache(2000, 15 * 60 * 1000);
const inboundDedupeCache = createMemoryCache(5000, 10 * 60 * 1000);
const memoryMessageStore = new Map();
const MAX_MEMORY_MESSAGE_STORE = 2000;
const recipientLookupCache = new Map();
const recipientLookupInFlight = new Map();
const MAX_LOOKUP_CACHE_SIZE = 5000;
const chatbotReplyTracker = new Map();

async function storeBaileysMessage(userId, remoteJid, messageId, fromMe, participant, messageProto) {
  if (!messageId || !messageProto) return;
  try {
    if (memoryMessageStore.size >= MAX_MEMORY_MESSAGE_STORE) {
      const oldestKey = memoryMessageStore.keys().next().value;
      memoryMessageStore.delete(oldestKey);
    }
    memoryMessageStore.set(messageId, messageProto);

    const dataStr = JSON.stringify(messageProto);
    await db.execute(
      `INSERT INTO baileys_message_store (user_id, remote_jid, message_id, from_me, participant, message_data)
       VALUES (?, ?, ?, ?, ?, ?)
       ON DUPLICATE KEY UPDATE message_data = VALUES(message_data)`,
      [userId, remoteJid, messageId, fromMe ? 1 : 0, participant || null, dataStr]
    );
  } catch (err) {
    logger.warn({ err: err.message, messageId }, 'Could not persist message to baileys_message_store');
  }
}

async function getBaileysMessage(key) {
  if (!key?.id) return undefined;
  if (memoryMessageStore.has(key.id)) {
    return memoryMessageStore.get(key.id);
  }

  try {
    const [rows] = await db.execute(
      'SELECT message_data FROM baileys_message_store WHERE message_id = ? AND remote_jid = ? LIMIT 1',
      [key.id, key.remoteJid || '']
    );
    if (rows && rows[0]?.message_data) {
      return typeof rows[0].message_data === 'string' ? JSON.parse(rows[0].message_data) : rows[0].message_data;
    }

    const [queueRows] = await db.execute(
      'SELECT body FROM message_queue WHERE provider_message_id = ? LIMIT 1',
      [key.id]
    );
    if (queueRows && queueRows[0]?.body) {
      return { conversation: String(queueRows[0].body) };
    }
  } catch (err) {
    logger.warn({ err: err.message, keyId: key.id }, 'Error retrieving message in getMessage');
  }
  return undefined;
}

async function verifyRecipientLookup(sock, recipientJid, cleanPhone) {
  const cacheKey = cleanPhone || recipientJid;
  const cached = recipientLookupCache.get(cacheKey);
  if (cached && cached.expiresAt > Date.now()) {
    return cached;
  }

  if (recipientLookupInFlight.has(cacheKey)) {
    return await recipientLookupInFlight.get(cacheKey);
  }

  const lookupPromise = (async () => {
    try {
      const matches = await sock.onWhatsApp(recipientJid);
      const match = Array.isArray(matches) ? matches.find((m) => m.exists) : null;
      const result = {
        exists: Boolean(match),
        jid: match?.jid || recipientJid,
        transientError: false,
        expiresAt: Date.now() + (match ? 24 * 3600 * 1000 : 15 * 60 * 1000)
      };

      if (recipientLookupCache.size >= MAX_LOOKUP_CACHE_SIZE) {
        const oldest = recipientLookupCache.keys().next().value;
        recipientLookupCache.delete(oldest);
      }
      recipientLookupCache.set(cacheKey, result);
      return result;
    } catch (err) {
      logger.warn({ err: err.message, recipientJid }, 'onWhatsApp upstream query error; treating as transient');
      return { exists: true, jid: recipientJid, transientError: true, expiresAt: Date.now() + 60000 };
    } finally {
      recipientLookupInFlight.delete(cacheKey);
    }
  })();

  recipientLookupInFlight.set(cacheKey, lookupPromise);
  return await lookupPromise;
}

async function isRecipientSuppressedInDb(userId, normalizedRecipient) {
  if (!normalizedRecipient) return false;
  try {
    const [rows] = await db.execute(
      'SELECT 1 FROM recipient_suppressions WHERE user_id = ? AND (normalized_phone = ? OR phone = ?) LIMIT 1',
      [userId, normalizedRecipient, normalizedRecipient]
    );
    return Boolean(rows && rows.length > 0);
  } catch (err) {
    logger.warn({ err: err.message, userId, normalizedRecipient }, 'Failed to check recipient suppression');
    return false;
  }
}

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
  const isTiDB = options.host.toLowerCase().includes('tidbcloud.com') || options.port === 4000;
  if (isTiDB && (!options.database || options.database === 'whatsapp_api')) {
    options.database = 'test';
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

function emptySession(userId) {
  return {
    userId,
    state: 'idle',
    sleeping: false,
    autoSleepEnabled: true,
    autoSleepTimeoutSeconds: 60,
    activeOperations: 0,
    lastActivityAt: Date.now(),
    qr: null,
    hasQr: false,
    pairingCode: null,
    phone: null,
    pushName: null,
    error: null,
    sock: null,
    starting: null,
    reconnectTimer: null,
    reachoutTimelock: null
  };
}

function publicSession(session) {
  return {
    userId: session.userId,
    state: session.state,
    sleeping: Boolean(session.sleeping),
    auto_sleep_enabled: session.autoSleepEnabled !== false,
    auto_sleep_timeout_seconds: Number(session.autoSleepTimeoutSeconds) || 60,
    last_activity_at: session.lastActivityAt || Date.now(),
    qr: session.qr,
    hasQr: Boolean(session.qr),
    pairingCode: session.pairingCode || null,
    phone: session.phone,
    pushName: session.pushName,
    profilePicture: session.profilePicture,
    error: session.error,
    reachout_timelock: session.reachoutTimelock || null
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
    if (supportsSleepingColumn && supportsTimelockColumns) {
      await db.execute(
        `UPDATE whatsapp_sessions
         SET status = ?, sleeping = ?, phone = ?, push_name = ?,
             connected_at = IF(? = 'connected', NOW(), connected_at),
             disconnected_at = IF(? IN ('disconnected', 'error', 'idle'), NOW(), disconnected_at),
             last_error = ?,
             reachout_timelock_active = ?,
             reachout_timelock_ends_at = ?,
             reachout_timelock_type = ?
         WHERE user_id = ?`,
        [
          session.state,
          Number(Boolean(session.sleeping)),
          session.phone,
          session.pushName,
          session.state,
          session.state,
          session.error,
          session.reachoutTimelock?.isActive ? 1 : 0,
          session.reachoutTimelock?.timeEnforcementEnds || null,
          session.reachoutTimelock?.enforcementType || null,
          userId
        ]
      );
      return;
    }

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
    if (supportsTimelockColumns && isMissingTimelockColumnError(error)) {
      supportsTimelockColumns = false;
      await updateSessionRecord(userId, session);
      return;
    }
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


async function resolveJidToPhone(userId, jid, session = null) {
  if (!jid) return '';
  const rawJid = String(jid).trim();

  // 1. Group chat
  if (rawJid.endsWith('@g.us')) {
    return rawJid;
  }

  // 2. Standard WhatsApp User JID (@s.whatsapp.net)
  if (rawJid.endsWith('@s.whatsapp.net')) {
    let digits = rawJid.split('@')[0].split(':')[0].replace(/\D/g, '');
    if (digits.length === 10 && DEFAULT_COUNTRY_CODE) {
      digits = `${DEFAULT_COUNTRY_CODE}${digits}`;
    }
    return digits;
  }

  // 3. WhatsApp Linked Device Identifier (@lid)
  if (rawJid.endsWith('@lid')) {
    const lidUser = rawJid.split('@')[0].split(':')[0].replace(/\D/g, '');
    let resolvedPn = null;

    // 3a. In-memory / Baileys LID mapping cache lookup
    try {
      if (session?.sock?.signalRepository?.lidMapping?.getPNForLID) {
        resolvedPn = await session.sock.signalRepository.lidMapping.getPNForLID(rawJid);
      }
    } catch (_) {}

    // 3b. Read disk reverse mapping: lid-mapping-${lidUser}_reverse.json
    if (!resolvedPn) {
      try {
        const authDir = join(SESSION_DIR, `user_${userId}`);
        const reverseFile = join(authDir, `lid-mapping-${lidUser}_reverse.json`);
        if (existsSync(reverseFile)) {
          const raw = readFileSync(reverseFile, 'utf8').trim();
          resolvedPn = JSON.parse(raw);
        }
      } catch (_) {}
    }

    // 3c. Scan forward mapping files in session directory
    if (!resolvedPn) {
      try {
        const authDir = join(SESSION_DIR, `user_${userId}`);
        if (existsSync(authDir)) {
          const files = readdirSync(authDir);
          for (const f of files) {
            if (f.startsWith('lid-mapping-') && !f.endsWith('_reverse.json') && f.endsWith('.json')) {
              const content = readFileSync(join(authDir, f), 'utf8');
              if (content.includes(lidUser)) {
                const m = f.match(/^lid-mapping-(\d+)\.json$/);
                if (m && m[1]) {
                  resolvedPn = m[1];
                  break;
                }
              }
            }
          }
        }
      } catch (_) {}
    }

    if (resolvedPn) {
      let digits = String(resolvedPn).split('@')[0].split(':')[0].replace(/\D/g, '');
      if (digits.length === 10 && DEFAULT_COUNTRY_CODE) {
        digits = `${DEFAULT_COUNTRY_CODE}${digits}`;
      }
      return digits;
    }

    // Fallback: digits from the lid
    return lidUser;
  }

  // 4. Raw digits or general phone input (also handles raw LID digits like 28343109968029)
  let digits = rawJid.split('@')[0].replace(/\D/g, '');
  if (digits.length > 12) {
    try {
      const authDir = join(SESSION_DIR, `user_${userId}`);
      const reverseFile = join(authDir, `lid-mapping-${digits}_reverse.json`);
      if (existsSync(reverseFile)) {
        const raw = readFileSync(reverseFile, 'utf8').trim();
        const resolved = JSON.parse(raw);
        if (resolved) {
          let pn = String(resolved).split('@')[0].split(':')[0].replace(/\D/g, '');
          if (pn.length === 10 && DEFAULT_COUNTRY_CODE) {
            pn = `${DEFAULT_COUNTRY_CODE}${pn}`;
          }
          return pn;
        }
      }
    } catch (_) {}
  }
  if (digits.startsWith('00')) {
    digits = digits.slice(2);
  }
  if (digits.length === 11 && digits.startsWith('0') && DEFAULT_COUNTRY_CODE) {
    digits = `${DEFAULT_COUNTRY_CODE}${digits.slice(1)}`;
  }
  if (digits.length === 10 && DEFAULT_COUNTRY_CODE) {
    digits = `${DEFAULT_COUNTRY_CODE}${digits}`;
  }
  return digits;
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

  try {
    const [prefRows] = await db.execute(
      'SELECT auto_sleep_enabled, auto_sleep_timeout_seconds, phone, push_name, reachout_timelock_active, reachout_timelock_ends_at, reachout_timelock_type FROM whatsapp_sessions WHERE user_id = ? LIMIT 1',
      [userId]
    );
    if (prefRows && prefRows[0]) {
      if (prefRows[0].auto_sleep_enabled !== undefined && prefRows[0].auto_sleep_enabled !== null) {
        session.autoSleepEnabled = Number(prefRows[0].auto_sleep_enabled) === 1;
      }
      if (prefRows[0].auto_sleep_timeout_seconds) {
        session.autoSleepTimeoutSeconds = Math.max(15, Math.min(86400, Number(prefRows[0].auto_sleep_timeout_seconds) || 60));
      }
      if (!session.phone && prefRows[0].phone) session.phone = prefRows[0].phone;
      if (!session.pushName && prefRows[0].push_name) session.pushName = prefRows[0].push_name;
      if (prefRows[0].reachout_timelock_active !== undefined) {
        const isActive = Number(prefRows[0].reachout_timelock_active) === 1;
        const endsAt = prefRows[0].reachout_timelock_ends_at ? new Date(prefRows[0].reachout_timelock_ends_at) : null;
        if (isActive && (!endsAt || endsAt.getTime() > Date.now())) {
          session.reachoutTimelock = {
            isActive: true,
            timeEnforcementEnds: endsAt,
            enforcementType: prefRows[0].reachout_timelock_type || 'DEFAULT'
          };
        } else {
          session.reachoutTimelock = {
            isActive: false,
            timeEnforcementEnds: null,
            enforcementType: null
          };
        }
      }
    }
  } catch (err) {
    logger.warn({ err: err.message, userId }, 'Could not read settings from database in startSession');
  }

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

  const authDir = join(SESSION_DIR, `user_${userId}`);
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
      getMessage: async (key) => await getBaileysMessage(key),
      msgRetryCounterCache,
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
        const { connection, lastDisconnect, qr, reachoutTimeLock } = update;

        if (reachoutTimeLock) {
          session.reachoutTimelock = {
            isActive: Boolean(reachoutTimeLock.isActive),
            timeEnforcementEnds: reachoutTimeLock.timeEnforcementEnds ? new Date(reachoutTimeLock.timeEnforcementEnds) : null,
            enforcementType: reachoutTimeLock.enforcementType || 'DEFAULT'
          };
          if (reachoutTimeLock.isActive) {
            logger.warn({
              userId,
              endsAt: session.reachoutTimelock.timeEnforcementEnds,
              type: session.reachoutTimelock.enforcementType
            }, '⚠️ WhatsApp Reachout Timelock is ACTIVE on this account!');
          } else {
            logger.info({ userId }, 'WhatsApp Reachout Timelock lifted / clear.');
          }
          await updateSessionRecord(userId, session);
        }

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
          session.pairingCode = null;
          session.error = null;
          const rawId = sock.user?.id || '';
          session.phone = rawId ? rawId.split(':')[0].split('@')[0] : null;
          session.pushName = sock.user?.name || sock.user?.verifiedName || null;
          try { session.profilePicture = await sock.profilePictureUrl(sock.user.id, 'image'); } catch(e) { session.profilePicture = null; }
          if (typeof sock.fetchAccountReachoutTimelock === 'function') {
            sock.fetchAccountReachoutTimelock().catch(err => {
              logger.warn({ err: err?.message, userId }, 'Could not probe account reachout timelock');
            });
          }
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
            session.pairingCode = null;
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
            session.pairingCode = null;
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

async function handleMessageStatusUpdate(userId, providerMessageId, newStatus, timestamp = null) {
  if (!providerMessageId) return;
  const occurredAt = timestamp ? new Date(Number(timestamp) * 1000).toISOString() : new Date().toISOString();

  // Sync status to native chat_messages table
  try {
    const [res] = await db.execute(
      'UPDATE chat_messages SET status = ? WHERE wa_message_id = ? AND user_id = ?',
      [newStatus, providerMessageId, userId]
    );
    if (res.affectedRows === 0) {
      const [qRows] = await db.execute(
        'SELECT payload FROM message_queue WHERE provider_message_id = ? AND user_id = ? LIMIT 1',
        [providerMessageId, userId]
      );
      if (qRows && qRows[0]?.payload) {
        const p = typeof qRows[0].payload === 'string' ? JSON.parse(qRows[0].payload) : qRows[0].payload;
        if (p?.chat_message_id) {
          await db.execute(
            'UPDATE chat_messages SET status = ?, wa_message_id = ? WHERE id = ? AND user_id = ?',
            [newStatus, providerMessageId, p.chat_message_id, userId]
          );
        }
      }
    }
  } catch (err) {
    // Non-fatal
  }

  // 1. Fetch message belonging to this exact tenant
  const [rows] = await db.execute(
    'SELECT id, user_id, recipient, normalized_recipient, status, delivered_at, read_at FROM message_queue WHERE provider_message_id = ? AND user_id = ? LIMIT 1',
    [providerMessageId, userId]
  );
  const msg = rows[0];
  if (!msg) {
    return;
  }

  // 2. Monotonic state order: queued -> processing -> sent -> delivered -> read
  // Never downgrade a read message to delivered
  if (msg.status === 'read') {
    return;
  }
  if (msg.status === 'delivered' && newStatus === 'delivered') {
    return;
  }

  if (newStatus === 'delivered') {
    const [res] = await db.execute(
      `UPDATE message_queue
       SET status = 'delivered',
           delivered_at = COALESCE(delivered_at, NOW())
       WHERE id = ? AND status IN ('queued', 'processing', 'sent')`,
      [msg.id]
    );

    if (res.affectedRows > 0) {
      await db.execute(
        `UPDATE message_logs
         SET status = 'delivered'
         WHERE user_id = ? AND recipient = ? AND status IN ('queued', 'sent')
         ORDER BY id DESC LIMIT 1`,
        [userId, msg.normalized_recipient || msg.recipient]
      );

      await enqueueWebhookEvent(userId, 'message.delivered', {
        message_id: msg.id,
        provider_message_id: providerMessageId,
        to: msg.normalized_recipient || msg.recipient,
        delivered_at: occurredAt,
        status: 'delivered'
      }, msg.id);
    }
  } else if (newStatus === 'read') {
    const [res] = await db.execute(
      `UPDATE message_queue
       SET status = 'read',
           read_at = COALESCE(read_at, NOW()),
           delivered_at = COALESCE(delivered_at, NOW())
       WHERE id = ? AND status IN ('queued', 'processing', 'sent', 'delivered')`,
      [msg.id]
    );

    if (res.affectedRows > 0) {
      await db.execute(
        `UPDATE message_logs
         SET status = 'read'
         WHERE user_id = ? AND recipient = ? AND status IN ('queued', 'sent', 'delivered')
         ORDER BY id DESC LIMIT 1`,
        [userId, msg.normalized_recipient || msg.recipient]
      );

      await enqueueWebhookEvent(userId, 'message.read', {
        message_id: msg.id,
        provider_message_id: providerMessageId,
        to: msg.normalized_recipient || msg.recipient,
        read_at: occurredAt,
        status: 'read'
      }, msg.id);
    }
  }
}

function normalizeInboundMessage(userId, msg, session = {}, resolvedSenderPhone = null) {
  const remoteJid = String(msg.key?.remoteJid || '');
  const messageId = String(msg.key?.id || '');
  const isGroup = remoteJid.endsWith('@g.us');
  const senderNumber = resolvedSenderPhone || remoteJid.replace(/@.*$/, '');
  const participant = msg.key?.participant || null;
  const senderName = msg.pushName || null;
  const timestamp = Number(msg.messageTimestamp) || Math.floor(Date.now() / 1000);
  const eventId = `evt_${crypto.randomUUID()}`;

  let messageType = 'text';
  let body = '';
  let caption = null;
  let mediaInfo = null;

  const content = msg.message || {};

  if (content.conversation) {
    messageType = 'text';
    body = content.conversation;
  } else if (content.extendedTextMessage) {
    messageType = 'text';
    body = content.extendedTextMessage.text || '';
  } else if (content.imageMessage) {
    messageType = 'image';
    caption = content.imageMessage.caption || null;
    body = caption || '';
    mediaInfo = {
      mimetype: content.imageMessage.mimetype || 'image/jpeg',
      fileLength: Number(content.imageMessage.fileLength) || null
    };
  } else if (content.videoMessage) {
    messageType = 'video';
    caption = content.videoMessage.caption || null;
    body = caption || '';
    mediaInfo = {
      mimetype: content.videoMessage.mimetype || 'video/mp4',
      seconds: content.videoMessage.seconds || null,
      fileLength: Number(content.videoMessage.fileLength) || null
    };
  } else if (content.audioMessage) {
    messageType = 'audio';
    mediaInfo = {
      mimetype: content.audioMessage.mimetype || 'audio/ogg',
      seconds: content.audioMessage.seconds || null,
      ptt: Boolean(content.audioMessage.ptt)
    };
  } else if (content.documentMessage) {
    messageType = 'document';
    caption = content.documentMessage.caption || null;
    body = caption || '';
    mediaInfo = {
      fileName: content.documentMessage.fileName || 'document',
      mimetype: content.documentMessage.mimetype || 'application/octet-stream',
      fileLength: Number(content.documentMessage.fileLength) || null
    };
  } else if (content.stickerMessage) {
    messageType = 'sticker';
    mediaInfo = {
      mimetype: content.stickerMessage.mimetype || 'image/webp'
    };
  } else if (content.contactMessage || content.contactsArrayMessage) {
    messageType = 'contact';
    body = content.contactMessage?.displayName || 'Contact';
  } else if (content.locationMessage || content.liveLocationMessage) {
    messageType = 'location';
    const loc = content.locationMessage || content.liveLocationMessage;
    mediaInfo = {
      latitude: loc.degreesLatitude,
      longitude: loc.degreesLongitude,
      name: loc.name || null,
      address: loc.address || null
    };
  } else if (content.reactionMessage) {
    messageType = 'reaction';
    body = content.reactionMessage.text || '';
    mediaInfo = {
      target_id: content.reactionMessage.key?.id || null
    };
  } else if (content.buttonsResponseMessage) {
    messageType = 'button_reply';
    body = content.buttonsResponseMessage.selectedDisplayText || content.buttonsResponseMessage.selectedButtonId || '';
  } else if (content.listResponseMessage) {
    messageType = 'list_reply';
    body = content.listResponseMessage.title || content.listResponseMessage.singleSelectReply?.selectedRowId || '';
  }

  const payload = {
    id: messageId,
    from: senderNumber,
    from_jid: remoteJid,
    is_group: isGroup,
    participant: participant,
    sender_name: senderName,
    type: messageType,
    text: body,
    caption: caption,
    media: mediaInfo,
    timestamp: timestamp
  };

  return {
    eventId,
    userId: Number(userId),
    remoteJid,
    messageId,
    messageType,
    senderName,
    body,
    timestamp,
    payload
  };
}

    sock.ev.on('messages.upsert', async (m) => {
      // Guard: Ignore messages from superseded sockets
      if (session.sock !== sock) return;

      try {
        if (m.type !== 'notify') return;
        session.lastActivityAt = Date.now();

        for (const msg of m.messages) {
          if (!msg.message || msg.key.remoteJid === 'status@broadcast') continue;

          // Deduplication Guard: Ignore duplicate inbound packets from Baileys / WhatsApp retries
          if (msg.key.id && !msg.key.fromMe) {
            const dedupeKey = `${userId}_${msg.key.remoteJid}_${msg.key.id}`;
            if (inboundDedupeCache.get(dedupeKey)) {
              logger.info({ messageId: msg.key.id }, '⚡ Skipping duplicate inbound message (in-memory dedupe)');
              continue;
            }
            inboundDedupeCache.set(dedupeKey, true);
          }

          // 0. Sync outgoing messages sent from the physical phone app
          if (msg.key.fromMe) {
            const recipientPhone = await resolveJidToPhone(userId, msg.key.remoteJid, session);
            if (recipientPhone && msg.key.id) {
              try {
                const normOut = normalizeInboundMessage(userId, msg, session, recipientPhone);
                const bodyOut = normOut.body || (normOut.messageType !== 'text' ? `[${normOut.messageType}]` : '');

                await db.execute(
                  `INSERT INTO chat_conversations (user_id, phone, last_message, last_message_at)
                   VALUES (?, ?, ?, NOW())
                   ON DUPLICATE KEY UPDATE
                     last_message = VALUES(last_message),
                     last_message_at = NOW()`,
                  [userId, recipientPhone, bodyOut]
                );
                const [cRows] = await db.execute(
                  'SELECT id FROM chat_conversations WHERE user_id = ? AND phone = ? LIMIT 1',
                  [userId, recipientPhone]
                );
                if (cRows && cRows.length > 0) {
                  const convId = cRows[0].id;
                  const [exists] = await db.execute(
                    'SELECT id FROM chat_messages WHERE conversation_id = ? AND wa_message_id = ? LIMIT 1',
                    [convId, msg.key.id]
                  );
                  if (!exists || exists.length === 0) {
                    await db.execute(
                      `INSERT INTO chat_messages (user_id, conversation_id, sender_type, sender_name, message_type, body, wa_message_id, status)
                       VALUES (?, ?, 'agent', ?, ?, ?, ?, 'sent')`,
                      [userId, convId, session.pushName || 'Agent', normOut.messageType || 'text', bodyOut, msg.key.id]
                    );
                  }
                }
              } catch (phoneSyncErr) {
                logger.warn({ phoneSyncErr: phoneSyncErr.message }, 'Failed to record outgoing chat message from phone');
              }
            }
            continue;
          }

          // 1. Resolve canonical phone number (handling WhatsApp @lid reverse mapping)
          const senderPhone = await resolveJidToPhone(userId, msg.key.remoteJid, session);
          const norm = normalizeInboundMessage(userId, msg, session, senderPhone);
          const senderName = norm.senderName || msg.pushName || null;
          const messagePreview = norm.body || (norm.messageType !== 'text' ? `[${norm.messageType}]` : '');
          let isBotPaused = 0;
          let feedbackHandled = false;
          let convStatus = 'open';
          let conversationId = null;
          let currentSessionUuid = null;
          let wokeUp = false;

          if (senderPhone) {
            try {
              // Check if conversation exists to see if awaiting_feedback is active
              const [preConvRows] = await db.execute(
                'SELECT id, status, awaiting_feedback, bot_paused, contact_name FROM chat_conversations WHERE user_id = ? AND phone = ? LIMIT 1',
                [userId, senderPhone]
              );
              const preConv = preConvRows && preConvRows.length > 0 ? preConvRows[0] : null;

              // Check if user has personal assistant mode or feedback surveys disabled
              const [userSettingsRows] = await db.execute(
                'SELECT agent_mode, feedback_enabled FROM ai_agent_settings WHERE user_id = ? LIMIT 1',
                [userId]
              );
              const isPersonalModeSetting = userSettingsRows && userSettingsRows.length > 0 && userSettingsRows[0].agent_mode === 'personal';
              const feedbackAllowedSetting = !isPersonalModeSetting && (!userSettingsRows || userSettingsRows.length === 0 || Number(userSettingsRows[0].feedback_enabled ?? 1) === 1);

              if (preConv && Number(preConv.awaiting_feedback) === 1 && !feedbackAllowedSetting) {
                await db.execute('UPDATE chat_conversations SET awaiting_feedback = 0 WHERE id = ?', [preConv.id]);
              }

              // Check if contact is responding with 5-star rating feedback
              if (feedbackAllowedSetting && preConv && Number(preConv.awaiting_feedback) === 1) {
                const rawBody = (norm.body || '').trim();
                let parsedRating = null;

                // 1. Star emoji count (⭐ or ★)
                const starMatches = rawBody.match(/⭐|★/g);
                if (starMatches && starMatches.length >= 1 && starMatches.length <= 5) {
                  parsedRating = starMatches.length;
                }

                // 2. Numeric rating 1-5 (e.g. "5", "5/5", "4 stars", "rating: 5", "5 - good")
                if (!parsedRating) {
                  const numMatch = rawBody.match(/(?:^|\b|\s)([1-5])(?:\s*(?:\/5|stars?|★|⭐)?)(?:\b|\s|$)/i);
                  if (numMatch) {
                    parsedRating = parseInt(numMatch[1], 10);
                  }
                }

                // 3. Word representations
                if (!parsedRating) {
                  const wordMatch = rawBody.toLowerCase().match(/\b(one|two|three|four|five)\b/);
                  if (wordMatch) {
                    const wordMap = { one: 1, two: 2, three: 3, four: 4, five: 5 };
                    parsedRating = wordMap[wordMatch[1]];
                  }
                }

                if (parsedRating) {
                  feedbackHandled = true;
                  const finalContactName = senderName || preConv.contact_name || null;
                  await db.execute(
                    `INSERT INTO chat_feedback (user_id, conversation_id, phone, contact_name, rating, message, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, NOW())`,
                    [userId, preConv.id, senderPhone, finalContactName, parsedRating, rawBody]
                  );

                                    // Update conversation status: feedback recorded and status kept closed
                  await db.execute(
                    `UPDATE chat_conversations
                     SET awaiting_feedback = 0,
                         status = 'closed',
                         closed_at = COALESCE(closed_at, NOW()),
                         last_message = ?,
                         last_message_at = NOW(),
                         unread_count = unread_count + 1
                     WHERE id = ?`,
                    [`Feedback received: ${parsedRating}/5 ⭐`, preConv.id]
                  );

                  // Send customer thank-you note via WhatsApp socket
                  const starsDisplay = '⭐'.repeat(parsedRating);
                  const thanksText = `Thank you for your rating of ${parsedRating}/5 ${starsDisplay}! 🙏 We appreciate your feedback.\n\n` +
                    `🔒 *This chat session is now closed.*\n` +
                    `If you ever want to chat again or have any questions, simply reply with */start* or *start* to begin a new chat session! 😊`;
                  try {
                    await (session.sock || sock).sendMessage(msg.key.remoteJid, { text: thanksText });
                    await db.execute(
                      `INSERT INTO chat_messages (user_id, conversation_id, session_uuid, sender_type, sender_name, message_type, body, status)
                       VALUES (?, ?, ?, 'bot', 'Support Bot', 'text', ?, 'sent')`,
                      [userId, preConv.id, preConv.session_uuid || null, thanksText]
                    );
                  } catch (replyErr) {
                    logger.warn({ replyErr: replyErr.message }, 'Failed to dispatch feedback thank-you reply');
                  }
                } else if (isWakeupTrigger(rawBody)) {
                  // User bypassed rating and typed /start or start
                  await db.execute('UPDATE chat_conversations SET awaiting_feedback = 0 WHERE id = ?', [preConv.id]);
                }
              }

              // Standard conversation session resolution (unique ticket lifecycles)
              if (!feedbackHandled) {
                const sessionInfo = await resolveActiveSession(db, userId, senderPhone, senderName, messagePreview);
                conversationId = sessionInfo.conversationId;
                currentSessionUuid = sessionInfo.sessionUuid;
                isBotPaused = sessionInfo.botPaused ? 1 : 0;
                convStatus = sessionInfo.status;
                wokeUp = Boolean(sessionInfo.wokeUp);
              } else {
                const [cRows] = await db.execute('SELECT id, session_uuid, bot_paused, status FROM chat_conversations WHERE user_id = ? AND phone = ? LIMIT 1', [userId, senderPhone]);
                if (cRows && cRows.length > 0) {
                  conversationId = cRows[0].id;
                  currentSessionUuid = cRows[0].session_uuid;
                  isBotPaused = Number(cRows[0].bot_paused || 0);
                  convStatus = cRows[0].status;
                }
              }

              if (conversationId) {
                if (norm.messageId) {
                  const [existingInConv] = await db.execute(
                    'SELECT id FROM chat_messages WHERE conversation_id = ? AND wa_message_id = ? LIMIT 1',
                    [conversationId, norm.messageId]
                  );
                  if (existingInConv && existingInConv.length > 0) {
                    logger.info({ conversationId, messageId: norm.messageId }, 'Inbound message already recorded in conversation; skipping duplicate handling');
                    continue;
                  }
                }

                const mediaUrl = norm.payload?.media_url || norm.payload?.file_url || null;
                await db.execute(
                  `INSERT INTO chat_messages
                   (user_id, conversation_id, session_uuid, sender_type, sender_name, message_type, body, media_url, wa_message_id, status)
                   VALUES (?, ?, ?, 'contact', ?, ?, ?, ?, ?, 'delivered')`,
                  [
                    userId,
                    conversationId,
                    currentSessionUuid,
                    senderName,
                    norm.messageType || 'text',
                    norm.body || '',
                    mediaUrl,
                    norm.messageId
                  ]
                );
              }
            } catch (convErr) {
              logger.warn({ convErr: convErr.message, userId }, 'Failed to record native chat message');
            }
          }

          // 2. Persist inbound message to database before acknowledging
          try {
            await db.execute(
              `INSERT INTO inbound_messages
               (user_id, remote_jid, message_id, message_type, sender_name, body, payload, message_timestamp)
               VALUES (?, ?, ?, ?, ?, ?, ?, ?)
               ON DUPLICATE KEY UPDATE
                 sender_name = COALESCE(VALUES(sender_name), sender_name),
                 body = COALESCE(VALUES(body), body),
                 payload = COALESCE(VALUES(payload), payload)`,
              [
                userId,
                norm.remoteJid,
                norm.messageId,
                norm.messageType,
                norm.senderName,
                norm.body,
                JSON.stringify(norm.payload),
                norm.timestamp
              ]
            );
          } catch (dbErr) {
            logger.warn({ dbErr: dbErr.message, userId }, 'Failed to persist inbound message');
          }

          // 3. Enqueue inbound webhook event asynchronously (non-blocking)
          await enqueueWebhookEvent(userId, 'message.received', {
            event_id: norm.eventId,
            session: {
              user_id: Number(userId),
              phone: session.phone || null
            },
            message: norm.payload
          }, norm.messageId);

          // 4. Persist message representation for Signal retry handling
          if (msg.key?.id && msg.message) {
            await storeBaileysMessage(userId, msg.key.remoteJid, msg.key.id, Boolean(msg.key.fromMe), msg.key.participant, msg.message);
          }

          // 5. Inbound Consent / Opt-Out & Opt-In keyword processing
          const rawText = (norm.body || '').trim().toLowerCase();
          const OPT_OUT_KEYWORDS = ['stop', 'unsubscribe', 'opt out', 'optout', 'cancel', 'quit', 'arretez', 'alto'];
          const OPT_IN_KEYWORDS = ['start', 'unstop', 'subscribe', 'commencer'];

          if (OPT_OUT_KEYWORDS.includes(rawText)) {
            const senderDigits = senderPhone;
            if (senderDigits) {
              try {
                await db.execute(
                  `INSERT INTO recipient_suppressions (user_id, phone, normalized_phone, reason, source)
                   VALUES (?, ?, ?, 'opt_out', 'inbound_stop')
                   ON DUPLICATE KEY UPDATE reason = VALUES(reason), source = VALUES(source)`,
                  [userId, senderDigits, senderDigits]
                );
                await db.execute(
                  'UPDATE contacts SET opt_in = 0 WHERE user_id = ? AND (phone = ? OR normalized_phone = ?)',
                  [userId, senderDigits, senderDigits]
                );
                await db.execute(
                  `UPDATE message_queue
                   SET status = 'cancelled', error_message = 'Recipient opted out via inbound keyword'
                   WHERE user_id = ? AND status = 'queued' AND (recipient = ? OR normalized_recipient = ?)`,
                  [userId, senderDigits, senderDigits]
                );
                logger.info({ userId, phone: senderDigits }, 'Processed inbound opt-out command');
              } catch (err) {
                logger.error({ err: err.message, userId }, 'Failed to record inbound opt-out');
              }
            }
            continue; // Do NOT evaluate chatbot rules on opt-out commands
          }

          if (OPT_IN_KEYWORDS.includes(rawText)) {
            const senderDigits = senderPhone;
            if (senderDigits) {
              try {
                await db.execute(
                  'DELETE FROM recipient_suppressions WHERE user_id = ? AND (normalized_phone = ? OR phone = ?)',
                  [userId, senderDigits, senderDigits]
                );
                await db.execute(
                  'UPDATE contacts SET opt_in = 1 WHERE user_id = ? AND (phone = ? OR normalized_phone = ?)',
                  [userId, senderDigits, senderDigits]
                );
                logger.info({ userId, phone: senderDigits }, 'Processed inbound opt-in command');
              } catch (err) {
                logger.error({ err: err.message, userId }, 'Failed to record inbound opt-in');
              }
            }
            if (!isWakeupTrigger(rawText)) {
              continue;
            }
          }

          // Pre-fetch active AI agent settings to resolve persona mode & admin caller role
          const [aiSettingsRows] = await db.execute(
            'SELECT * FROM ai_agent_settings WHERE user_id = ? AND enabled = 1 AND auto_reply_enabled = 1 LIMIT 1',
            [userId]
          );
          const aiSettings = aiSettingsRows && aiSettingsRows.length > 0 ? aiSettingsRows[0] : null;
          const isPersonalMode = aiSettings?.agent_mode === 'personal';
          const ownerName = aiSettings?.owner_name || 'Boss';

          let hasConfiguredAiKeys = Boolean(aiSettings && (aiSettings.gemini_api_key || aiSettings.groq_api_key || aiSettings.openrouter_api_key));
          if (!hasConfiguredAiKeys) {
            try {
              const [keyCheck] = await db.execute(
                'SELECT 1 FROM ai_provider_keys WHERE user_id = ? AND is_active = 1 LIMIT 1',
                [userId]
              );
              if (keyCheck && keyCheck.length > 0) {
                hasConfiguredAiKeys = true;
              }
            } catch (e) {}
          }

          let toolsConfig = null;
          if (aiSettings?.tools_config) {
            try {
              toolsConfig = typeof aiSettings.tools_config === 'string'
                ? JSON.parse(aiSettings.tools_config)
                : aiSettings.tools_config;
            } catch (e) {}
          }
          const adminPhones = Array.isArray(toolsConfig?.admin_phones) && toolsConfig.admin_phones.length > 0
            ? toolsConfig.admin_phones
            : (process.env.ADMIN_WHITELIST_PHONES ? process.env.ADMIN_WHITELIST_PHONES.split(',').map(s => s.trim()) : []);
          const cleanPhone = (senderPhone || '').replace(/[^\d]/g, '');
          const isAdminCaller = isPersonalMode || adminPhones.some(p => cleanPhone.includes(p.replace(/[^\d]/g, '')));

          // 6. Escalation & Frustration check
          // STRICT RESTRICTION: In Personal Assistant mode OR when communicating with the Admin/Boss,
          // customer support desk escalation is 100% strictly disabled!
          let wasEscalated = false;
          if (!feedbackHandled && senderPhone && !isPersonalMode && !isAdminCaller) {
            const escCheck = detectFrustrationAndEscalation(norm.body);
            if (escCheck.shouldEscalate) {
              wasEscalated = true;
              logger.warn({ userId, senderPhone, reason: escCheck.reason, score: escCheck.score }, '🚨 Escalation trigger detected! Pausing bot and alerting human agents.');

              await db.execute(
                `UPDATE chat_conversations
                 SET status = 'escalated',
                     escalated_at = NOW(),
                     escalation_reason = ?,
                     bot_paused = 1,
                     consecutive_bot_replies = 0
                 WHERE user_id = ? AND phone = ?`,
                [escCheck.reason, userId, senderPhone]
              );

              const [cRows] = await db.execute(
                'SELECT id FROM chat_conversations WHERE user_id = ? AND phone = ? LIMIT 1',
                [userId, senderPhone]
              );
              if (cRows && cRows.length > 0) {
                const convId = cRows[0].id;
                await db.execute(
                  `INSERT INTO chat_escalations (user_id, conversation_id, reason_code, trigger_text, status, created_at)
                   VALUES (?, ?, ?, ?, 'active', NOW())`,
                  [userId, convId, escCheck.reason, (norm.body || '').slice(0, 500)]
                );

                const handoverMsg = 'I understand you need assistance from our team. I have escalated this conversation to our support representatives and paused automated replies. A team member will join you shortly. 🔔';
                try {
                  await (session.sock || sock).sendMessage(msg.key.remoteJid, { text: handoverMsg });
                  await db.execute(
                    `INSERT INTO chat_messages (user_id, conversation_id, sender_type, sender_name, message_type, body, status)
                     VALUES (?, ?, 'bot', 'Support System', 'text', ?, 'sent')`,
                    [userId, convId, handoverMsg]
                  );
                } catch (sendErr) {
                  logger.warn({ sendErr: sendErr.message }, 'Failed to dispatch handover confirmation');
                }
              }
            }
          }

          if (wasEscalated) {
            continue;
          }

          // 7. Enterprise AI Agent with Debouncer, Memory, Typing Presence, & Auto-Resolution
          let aiHandled = false;
          const rawInboundText = (norm.body || '').trim();

          // Intercept wake-up trigger (/start or start): ALWAYS resets bot_paused, starts a fresh session ticket, and sends dynamic greeting
          if (rawInboundText && isWakeupTrigger(rawInboundText)) {
            logger.info({ userId, senderPhone, isPersonalMode }, '⚡ Wake-up command received (/start or start) - unpausing bot and starting fresh session');
            currentSessionUuid = generateSessionUuid();
            await db.execute(
              'UPDATE chat_conversations SET session_uuid = ?, bot_paused = 0, status = "open", escalation_reason = NULL, awaiting_feedback = 0 WHERE user_id = ? AND phone = ?',
              [currentSessionUuid, userId, senderPhone]
            );
            isBotPaused = 0;
            convStatus = 'open';

            const welcomeText = isPersonalMode
              ? `Hello ${ownerName}! 👋 I am your 24/7 Personal AI Executive Assistant.\n\n` +
                `I am ready to assist you with tasks, live web searches, weather updates, math calculations, drafting messages, and daily planning.\n\n` +
                `How can I help you right now? ⚡`
              : `Hello! 👋 Welcome to our support desk.\n\n` +
                `I am your virtual assistant, ready to assist you with inquiries, services, and support.\n\n` +
                `How can I help you today? ✨`;
            try {
              const sentWelcome = await (session.sock || sock).sendMessage(msg.key.remoteJid, { text: welcomeText });
              await db.execute(
                `INSERT INTO chat_messages (user_id, conversation_id, session_uuid, sender_type, sender_name, message_type, body, wa_message_id, status)
                 VALUES (?, ?, ?, 'bot', 'AI Assistant', 'text', ?, ?, 'sent')`,
                [userId, conversationId, currentSessionUuid, welcomeText, sentWelcome?.key?.id || null]
              );
              await db.execute(
                `UPDATE chat_conversations SET last_message = ?, last_message_at = NOW(), consecutive_bot_replies = 1 WHERE id = ?`,
                [welcomeText.slice(0, 200), conversationId]
              );
              aiHandled = true;
            } catch (sendErr) {
              logger.warn({ sendErr: sendErr.message }, 'Failed to dispatch start welcome greeting');
            }
            continue;
          }

          // Intercept topic reset triggers (/clear, /new, /reset, etc.): Wipes working context, starts a fresh topic session
          if (rawInboundText && isTopicResetTrigger(rawInboundText)) {
            logger.info({ userId, senderPhone, isPersonalMode }, '🧹 Topic reset command received (/clear, /new, /reset) - generating fresh session UUID');
            currentSessionUuid = generateSessionUuid();
            await db.execute(
              'UPDATE chat_conversations SET session_uuid = ?, bot_paused = 0, status = "open", escalation_reason = NULL, awaiting_feedback = 0, consecutive_bot_replies = 0, last_message_at = NOW() WHERE user_id = ? AND phone = ?',
              [currentSessionUuid, userId, senderPhone]
            );
            isBotPaused = 0;
            convStatus = 'open';

            const resetReplyText = isPersonalMode
              ? `🧹 *Working Context Cleared!*\nStarting a fresh topic session. How can I assist you now, ${ownerName}?`
              : '🧹 *Conversation Context Cleared!*\nHow can I help you with a fresh inquiry today?';
            try {
              const sentReset = await (session.sock || sock).sendMessage(msg.key.remoteJid, { text: resetReplyText });
              await db.execute(
                `INSERT INTO chat_messages (user_id, conversation_id, session_uuid, sender_type, sender_name, message_type, body, wa_message_id, status)
                 VALUES (?, ?, ?, 'bot', 'AI Assistant', 'text', ?, ?, 'sent')`,
                [userId, conversationId, currentSessionUuid, resetReplyText, sentReset?.key?.id || null]
              );
              await db.execute(
                `UPDATE chat_conversations SET last_message = ?, last_message_at = NOW(), consecutive_bot_replies = 1 WHERE id = ?`,
                [resetReplyText.slice(0, 200), conversationId]
              );
              aiHandled = true;
            } catch (sendErr) {
              logger.warn({ sendErr: sendErr.message }, 'Failed to dispatch topic reset confirmation');
            }
            continue;
          }

          if (!feedbackHandled && (isPersonalMode || (isBotPaused !== 1 && convStatus === 'open'))) {
            if (rawInboundText) {
              try {
                if (aiSettings && hasConfiguredAiKeys) {
                  const [convCheck] = await db.execute(
                    'SELECT id, session_uuid, consecutive_bot_replies, status, bot_paused FROM chat_conversations WHERE user_id = ? AND phone = ? LIMIT 1',
                    [userId, senderPhone]
                  );
                  if (convCheck && convCheck.length > 0) {
                    const conv = convCheck[0];
                    const canReply = isPersonalMode || (conv.status === 'open' && conv.bot_paused !== 1 && Number(conv.consecutive_bot_replies || 0) < 8);
                    if (canReply) {
                      
                      // Step A: Dispatch to 2.5s sliding debouncer to consolidate rapid fragmented messages
                      globalDebouncer.enqueue(userId, msg.key.remoteJid, {
                        text: rawInboundText,
                        norm,
                        msg,
                        convId: conv.id,
                        sessionUuid: conv.session_uuid
                      }, async ({ consolidatedText, primaryItem }) => {
                        try {
                          const isPersonalMode = aiSettings?.agent_mode === 'personal';

                          // Double-check conversation status on flush
                          const [freshConv] = await db.execute(
                            'SELECT id, session_uuid, status, bot_paused FROM chat_conversations WHERE id = ? LIMIT 1',
                            [conv.id]
                          );
                          if (!freshConv || freshConv.length === 0) {
                            return;
                          }
                          if (!isPersonalMode && (freshConv[0].status === 'escalated' || freshConv[0].bot_paused === 1)) {
                            return;
                          }
                          if (isPersonalMode && (freshConv[0].status === 'escalated' || freshConv[0].bot_paused === 1)) {
                            await db.execute(
                              'UPDATE chat_conversations SET status = "open", bot_paused = 0, escalation_reason = NULL WHERE id = ?',
                              [conv.id]
                            );
                          }

                          const activeSessionUuid = freshConv[0].session_uuid;

                          // Step B: Retrieve recent multi-turn conversation memory (enforces 45-min rolling TTL decay)
                          const historyTurns = await ChatMemoryEngine.getRecentTurns(db, conv.id, activeSessionUuid, 6, 45);

                          // Step C: Check for query resolution / customer satisfaction
                          // In Personal Assistant mode, customer feedback surveys and auto-closing are completely disabled!
                          const feedbackAllowed = !isPersonalMode && Number(aiSettings?.feedback_enabled ?? 1) === 1;

                          if (!isPersonalMode && feedbackAllowed) {
                            const resolutionCheck = ChatResolutionEngine.detectResolution(consolidatedText, historyTurns);
                            if (resolutionCheck.isResolved) {
                              logger.info({ userId, convId: conv.id, remark: resolutionCheck.remark }, '🎉 Inquiry resolved! Auto-closing session.');
                              await closeSession(db, conv.id, resolutionCheck.remark, true, true);

                              const closureMsg = ChatResolutionEngine.getClosureMessage();
                              await (session.sock || sock).sendMessage(msg.key.remoteJid, { text: closureMsg });
                              await db.execute(
                                `INSERT INTO chat_messages (user_id, conversation_id, session_uuid, sender_type, sender_name, message_type, body, status)
                                 VALUES (?, ?, ?, 'bot', 'Support Bot', 'text', ?, 'sent')`,
                                [userId, conv.id, activeSessionUuid, closureMsg]
                              );
                              await db.execute(
                                `UPDATE chat_conversations SET last_message = ?, last_message_at = NOW() WHERE id = ?`,
                                [closureMsg.slice(0, 200), conv.id]
                              );
                              return;
                            }
                          }

                          // Check for active pending draft in this conversation (for 2-phase confirmation routing)
                          let pendingDraft = null;
                          try {
                            const [draftRows] = await db.execute(
                              `SELECT id, draft_code, action_type, recipient, subject, body_content, status
                               FROM ai_action_drafts
                               WHERE user_id = ? AND conversation_id = ? AND status = 'pending' AND expires_at > NOW()
                               ORDER BY id DESC LIMIT 1`,
                              [userId, conv.id]
                            );
                            if (draftRows && draftRows.length > 0) {
                              pendingDraft = draftRows[0];
                            }
                          } catch (draftErr) {
                            console.warn('[Server] Failed to query pending drafts:', draftErr.message);
                          }

                          // Step D: Token Burn Optimizer - Intercept repeated greetings & low-entropy acknowledgments
                          const greetingCheck = globalGreetingCache.evaluate(userId, senderPhone, consolidatedText, activeSessionUuid, {
                            isPersonalMode,
                            ownerName,
                            hasPendingDraft: Boolean(pendingDraft)
                          });
                          if (greetingCheck.shouldIntercept && greetingCheck.replyText) {
                            logger.info({ userId, senderPhone }, '⚡ Intercepted greeting/acknowledgment locally - saved LLM tokens!');
                            await (session.sock || sock).sendMessage(msg.key.remoteJid, { text: greetingCheck.replyText });
                            await db.execute(
                              `INSERT INTO chat_messages (user_id, conversation_id, session_uuid, sender_type, sender_name, message_type, body, status)
                               VALUES (?, ?, ?, 'bot', 'AI Assistant', 'text', ?, 'sent')`,
                              [userId, conv.id, activeSessionUuid, greetingCheck.replyText]
                            );
                            return;
                          }

                          // Step E: Trigger WhatsApp 'typing...' indicator
                          const stopTyping = await ChatPresenceManager.startTyping(session.sock || sock, msg.key.remoteJid);

                          let replyText = null;
                          try {
                            // Hybrid Intent Routing & Dynamic Tool/Skill Composition
                            const detectedIntents = await detectIntents(consolidatedText, { pendingDraft });
                            const allUserTools = await UniversalToolsRegistry.loadUserTools(userId, db);
                            const { activeIntents, filteredTools, skillDirectives } = composeSkillsAndTools(detectedIntents, allUserTools, isPersonalMode, { pendingDraft, messageText: consolidatedText });

                            // In Personal Assistant mode, business customer support knowledge base retrieval is strictly banned/disabled!
                            const relevantChunks = isPersonalMode ? [] : await retrieveRelevantKnowledge(db, userId, consolidatedText, 3, 0.15);
                            const userFacts = await ChatFactMemory.loadUserFacts(db, userId, {
                              contactPhone: isPersonalMode ? '' : (senderPhone || ''),
                              queryText: consolidatedText,
                              maxFacts: 3
                            });
                            const trainingData = await ChatFactMemory.loadTrainingExamples(db, userId, null, 2);

                            const { systemPrompt, userMessage } = buildPromptWithRag(
                              aiSettings.system_prompt,
                              relevantChunks,
                              consolidatedText,
                              {
                                agentMode: aiSettings.agent_mode || 'support',
                                ownerName: aiSettings.owner_name || 'Boss',
                                skillDirectives,
                                factsText: userFacts.factsText,
                                sessionSummary: conv.session_summary || null,
                                examplesText: trainingData.examplesText,
                                constraintsText: trainingData.constraintsText
                              }
                            );

                            let callerRole = 'guest';
                            if (isAdminCaller) {
                              callerRole = 'admin';
                            } else {
                              const [custRows] = await db.execute(
                                'SELECT id FROM contacts WHERE user_id = ? AND (phone = ? OR phone LIKE ?) LIMIT 1',
                                [userId, senderPhone, `%${cleanPhone.slice(-10)}%`]
                              );
                              if (custRows && custRows.length > 0) {
                                callerRole = 'customer';
                              }
                            }

                            const toolsContext = {
                              db,
                              userId,
                              conversationId: conv.id,
                              callerPhone: senderPhone,
                              callerRole,
                              isPersonalMode,
                              agentMode: aiSettings.agent_mode || 'support',
                              ownerName: aiSettings.owner_name || 'Boss',
                              toolsConfig,
                              scopedTools: filteredTools
                            };

                            replyText = await generateAiReply(aiSettings, systemPrompt, userMessage, historyTurns, toolsContext);
                          } finally {
                            await stopTyping();
                          }

                          if (replyText && replyText.trim()) {
                            const sentAi = await (session.sock || sock).sendMessage(msg.key.remoteJid, { text: replyText.trim() });
                            await db.execute(
                              `INSERT INTO chat_messages (user_id, conversation_id, session_uuid, sender_type, sender_name, message_type, body, wa_message_id, status)
                               VALUES (?, ?, ?, 'bot', 'AI Assistant', 'text', ?, ?, 'sent')`,
                              [userId, conv.id, activeSessionUuid, replyText.trim(), sentAi?.key?.id || null]
                            );
                            await db.execute(
                              `UPDATE chat_conversations
                               SET last_message = ?,
                                   last_message_at = NOW(),
                                   consecutive_bot_replies = consecutive_bot_replies + 1
                               WHERE id = ?`,
                              [replyText.trim().slice(0, 200), conv.id]
                            );
                            logger.info({ userId, convId: conv.id }, 'Dispatched multi-turn AI reply');
                          }
                        } catch (debouncedErr) {
                          logger.error({ debouncedErr: debouncedErr.message, userId }, 'Error executing debounced AI reply');
                          try {
                            const failNotice = "⚠️ *Temporary AI Service Notice*\nI'm encountering high API load with the AI provider right now. Please try again in a few moments.";
                            const sentNotice = await (session.sock || sock).sendMessage(msg.key.remoteJid, { text: failNotice });
                            await db.execute(
                              `INSERT INTO chat_messages (user_id, conversation_id, session_uuid, sender_type, sender_name, message_type, body, wa_message_id, status)
                               VALUES (?, ?, ?, 'bot', 'AI Assistant', 'text', ?, ?, 'sent')`,
                              [userId, conv.id, activeSessionUuid, failNotice, sentNotice?.key?.id || null]
                            );
                          } catch (_) {}
                        }
                      });

                      aiHandled = true;
                    }
                  }
                }
              } catch (aiErr) {
                logger.warn({ aiErr: aiErr.message, userId }, 'AI Agent processing error, falling back to rule-based chatbot');
              }
            }
          }

          if (aiHandled) {
            continue;
          }

          // 8. Chatbot rule processing for text messages with loop protection
          if (feedbackHandled || isBotPaused === 1 || convStatus === 'closed') {
            if (isBotPaused === 1) {
              logger.info({ userId, senderPhone }, 'Skipping chatbot auto-reply: bot is paused by human agent for this conversation');
            }
            continue; // Do not trigger chatbot auto-replies on feedback submissions or closed conversations
          }
          const text = norm.body || '';
          if (!text.trim()) continue;

          // Chatbot loop protection: maximum 3 auto-replies per 5-minute window
          const chatbotCooldownKey = `${userId}:${msg.key.remoteJid}`;
          const nowTs = Date.now();
          const tracker = chatbotReplyTracker.get(chatbotCooldownKey) || { count: 0, resetAt: nowTs + 300000 };
          if (nowTs > tracker.resetAt) {
            tracker.count = 0;
            tracker.resetAt = nowTs + 300000;
          }
          if (tracker.count >= 3) {
            logger.warn({ userId, remoteJid: msg.key.remoteJid }, 'Chatbot reply rate limit exceeded; skipping to prevent loops');
            continue;
          }

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
            tracker.count += 1;
            chatbotReplyTracker.set(chatbotCooldownKey, tracker);

            const payload = JSON.stringify({ source: 'chatbot', rule_id: matchedRule.id, type: 'text' });
            await db.execute(
              `INSERT INTO message_queue (user_id, api_key_id, recipient, message_type, body, payload, priority) VALUES (?, NULL, ?, 'text', ?, ?, 5)`,
              [userId, msg.key.remoteJid, matchedRule.reply_text, payload]
            );
          }
        }
      } catch (err) {
        logger.error({ err: err.message, userId }, 'Inbound message processing failed');
      }
    });

    sock.ev.on('messages.update', async (updates) => {
      if (session.sock !== sock) return;
      session.lastActivityAt = Date.now();
      try {
        for (const item of updates) {
          const providerMessageId = item.key?.id;
          const status = item.update?.status;
          if (!providerMessageId) continue;

          // Baileys WAMessageStatus: 3 = DELIVERY_ACK, 4 = READ, 5 = PLAYED
          if (status === 3 || status === 'DELIVERY_ACK') {
            await handleMessageStatusUpdate(userId, providerMessageId, 'delivered', item.update?.messageTimestamp);
          } else if (status === 4 || status === 5 || status === 'READ' || status === 'PLAYED') {
            await handleMessageStatusUpdate(userId, providerMessageId, 'read', item.update?.messageTimestamp);
          }
        }
      } catch (err) {
        logger.error({ err: err.message, userId }, 'Failed to process messages.update event');
      }
    });

    sock.ev.on('message-receipt.update', async (receipts) => {
      if (session.sock !== sock) return;
      session.lastActivityAt = Date.now();
      try {
        for (const item of receipts) {
          const providerMessageId = item.key?.id;
          if (!providerMessageId) continue;
          if (item.receipt?.readTimestamp) {
            await handleMessageStatusUpdate(userId, providerMessageId, 'read', item.receipt.readTimestamp);
          } else if (item.receipt?.receiptTimestamp) {
            await handleMessageStatusUpdate(userId, providerMessageId, 'delivered', item.receipt.receiptTimestamp);
          }
        }
      } catch (err) {
        logger.error({ err: err.message, userId }, 'Failed to process message-receipt.update event');
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

async function safelyCleanupAttachment(message, payload) {
  try {
    const attachment = payload?.attachment;
    if (!attachment?.is_temp || !attachment.path) {
      return;
    }

    const attachmentPath = resolve(attachment.path);
    if (!existsSync(attachmentPath)) {
      return;
    }

    const filename = basename(attachmentPath);

    // 1. If part of an active campaign, retain media until campaign finishes or cancels
    if (payload?.campaign_id) {
      const [campRows] = await db.execute(
        `SELECT status FROM campaigns WHERE id = ? LIMIT 1`,
        [payload.campaign_id]
      );
      if (campRows[0] && ['queued', 'processing', 'running'].includes(campRows[0].status)) {
        return;
      }
    }

    // 2. Check if any other queued or processing messages still reference this attachment
    const [activeRows] = await db.execute(
      `SELECT id FROM message_queue
       WHERE id != ?
         AND status IN ('queued', 'processing')
         AND (payload LIKE ? OR payload LIKE ?)
       LIMIT 1`,
      [message.id, `%${filename}%`, `%${attachment.path.replace(/\\/g, '\\\\')}%`]
    );
    if (activeRows.length > 0) {
      return;
    }

    // 3. Decrement or check media_assets record reference count
    try {
      const [assetRows] = await db.execute(
        `SELECT id, reference_count FROM media_assets
         WHERE file_path = ? OR storage_key = ? OR file_path LIKE ?
         LIMIT 1`,
        [attachment.path, filename, `%${filename}`]
      );
      if (assetRows.length > 0) {
        const asset = assetRows[0];
        if (asset.reference_count > 1) {
          await db.execute(
            `UPDATE media_assets SET reference_count = reference_count - 1 WHERE id = ?`,
            [asset.id]
          );
          return;
        }
        await db.execute(`DELETE FROM media_assets WHERE id = ?`, [asset.id]);
      }
    } catch (_) {}

    // No active references left: safely delete file
    await rm(attachmentPath, { force: true });
    logger.info({ path: attachmentPath }, 'Ephemeral media cleaned up safely.');
  } catch (err) {
    logger.warn({ err: err.message }, 'Failed during safe media cleanup');
  }
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

  // Atomic transaction claim with FOR UPDATE SKIP LOCKED
  const conn = await db.getConnection();
  try {
    await conn.beginTransaction();

    const [rows] = await conn.execute(
      `SELECT * FROM message_queue
       WHERE status = 'queued'
         AND available_at <= NOW()
       ORDER BY priority DESC, available_at ASC, id ASC
       LIMIT 1
       FOR UPDATE SKIP LOCKED`
    );

    if (!rows || rows.length === 0) {
      await conn.commit();
      return null;
    }

    const message = rows[0];

    await conn.execute(
      `UPDATE message_queue
       SET status = 'processing',
           locked_at = NOW(),
           locked_by = ?,
           attempts = attempts + 1
       WHERE id = ?`,
      [WORKER_ID, message.id]
    );

    await conn.commit();

    message.status = 'processing';
    message.locked_by = WORKER_ID;
    message.locked_at = new Date();
    message.attempts = (Number(message.attempts) || 0) + 1;

    return message;
  } catch (error) {
    try {
      await conn.rollback();
    } catch (_) {}
    throw error;
  } finally {
    conn.release();
  }
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
        'SELECT sleeping, auto_sleep_enabled, auto_sleep_timeout_seconds FROM whatsapp_sessions WHERE user_id = ? LIMIT 1',
        [message.user_id]
      );
      if (sessionRows[0]) {
        if (Number(sessionRows[0].sleeping) === 1) currentSession.sleeping = true;
        if (sessionRows[0].auto_sleep_enabled !== undefined && sessionRows[0].auto_sleep_enabled !== null) {
          currentSession.autoSleepEnabled = Number(sessionRows[0].auto_sleep_enabled) === 1;
        }
        if (sessionRows[0].auto_sleep_timeout_seconds) {
          currentSession.autoSleepTimeoutSeconds = Number(sessionRows[0].auto_sleep_timeout_seconds);
        }
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

  // If session is not connected, schedule a non-blocking retry with exponential backoff & jitter
  if (session.state !== 'connected' || !session.sock) {
    logger.info(`Session for user ${message.user_id} is not connected (state: ${session.state}). Scheduling background retry.`);
    const attempts = Number(message.attempts) || 1;
    const maxAttempts = Number(message.max_attempts) || 5;

    if (attempts >= maxAttempts) {
      await markMessageFailed(message, 'WhatsApp device is not connected or failed to wake up after maximum retry attempts.');
      return;
    }

    // Exponential backoff with jitter (5s, 10s, 20s, 40s... capped at 120s)
    const baseDelay = Math.min(120, 5 * Math.pow(2, Math.max(0, attempts - 1)));
    const jitter = Math.floor(Math.random() * 5);
    const delaySeconds = baseDelay + jitter;

    await db.execute(
      `UPDATE message_queue
       SET status = 'queued',
           locked_at = NULL,
           locked_by = NULL,
           error_message = 'Session waking up or not yet connected; retry scheduled.',
           available_at = DATE_ADD(NOW(), INTERVAL ? SECOND)
       WHERE id = ?`,
      [delaySeconds, message.id]
    );
    return;
  }

  session.lastActivityAt = Date.now();
  if (wasAsleep) {
    session.wasSleeping = true;
  }

  // 1. Guard against cancelled jobs
  const [currentQueueStatus] = await db.execute('SELECT status FROM message_queue WHERE id = ? LIMIT 1', [message.id]);
  if (currentQueueStatus && currentQueueStatus[0]?.status === 'cancelled') {
    logger.info({ messageId: message.id }, 'Message was cancelled while awaiting dispatch; aborting send.');
    return;
  }

  // 2. Guard against cancelled or paused campaigns
  if (payload?.campaign_id) {
    const [campRows] = await db.execute('SELECT status FROM campaigns WHERE id = ? LIMIT 1', [payload.campaign_id]);
    if (campRows && campRows[0]) {
      if (campRows[0].status === 'cancelled') {
        logger.info({ messageId: message.id, campaignId: payload.campaign_id }, 'Campaign was cancelled; aborting message send.');
        await db.execute('UPDATE message_queue SET status = "cancelled", error_message = "Campaign was cancelled" WHERE id = ?', [message.id]);
        return;
      }
      if (campRows[0].status === 'paused') {
        logger.info({ messageId: message.id, campaignId: payload.campaign_id }, 'Campaign is paused; deferring message send.');
        await db.execute('UPDATE message_queue SET status = "queued", locked_at = NULL, locked_by = NULL, available_at = DATE_ADD(NOW(), INTERVAL 30 SECOND) WHERE id = ?', [message.id]);
        return;
      }
    }
  }

  // 3. WhatsApp Reachout-Timelock Anti-Ban Protection
  // If WhatsApp temporarily restricted this account from messaging new contacts (cold outreach),
  // automatically defer campaign messages to protect the phone number from permanent ban!
  if (session.reachoutTimelock?.isActive) {
    const endsAt = session.reachoutTimelock.timeEnforcementEnds;
    const isStillActive = !endsAt || (endsAt instanceof Date && endsAt.getTime() > Date.now());
    if (isStillActive) {
      const isCampaign = Boolean(payload?.campaign_id) || payload?.source === 'campaign';
      if (isCampaign) {
        logger.warn({ userId: message.user_id, messageId: message.id, endsAt }, 'Anti-Ban Safety: Account under WhatsApp Reachout Timelock. Deferring campaign broadcast.');
        await db.execute(
          `UPDATE message_queue
           SET status = 'queued',
               locked_at = NULL,
               locked_by = NULL,
               error_message = 'Anti-Ban Protection: Account is under temporary WhatsApp Reachout Timelock. Broadcast deferred.',
               available_at = DATE_ADD(NOW(), INTERVAL 5 MINUTE)
           WHERE id = ?`,
          [message.id]
        );
        return;
      }
    } else {
      session.reachoutTimelock.isActive = false;
      await updateSessionRecord(message.user_id, session);
    }
  }

  const targetRecipient = await resolveJidToPhone(message.user_id, message.recipient, session);
  const recipient = normalizeRecipient(targetRecipient || message.recipient);
  if (!recipient.jid) {
    await markMessageFailed(message, 'Invalid recipient. Use an international number or a 10-digit Indian mobile number.', recipient.normalizedTo);
    return;
  }

  // 3. Final dispatch boundary consent / suppression check
  const isSuppressed = await isRecipientSuppressedInDb(message.user_id, recipient.normalizedTo);
  if (isSuppressed) {
    logger.warn({ userId: message.user_id, recipient: recipient.normalizedTo }, 'Recipient is suppressed or opted out; cancelling message.');
    await markMessageFailed(message, 'Recipient opted out or suppressed.', recipient.normalizedTo);
    return;
  }

  await new Promise((resolve) => setTimeout(resolve, SEND_DELAY_MS));

  if (!recipient.isGroup && !recipient.isLid && typeof session.sock.onWhatsApp === 'function') {
    const verified = await verifyRecipientLookup(session.sock, recipient.jid, recipient.normalizedTo);
    if (!verified.exists && !verified.transientError) {
      await markMessageFailed(message, `The number ${recipient.normalizedTo} is not registered on WhatsApp.`, recipient.normalizedTo);
      return;
    }
    if (verified.jid) {
      recipient.jid = verified.jid;
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
    if (!existsSync(attachment.path)) {
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

    const fileStats = statSync(attachment.path);
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
    session.activeOperations = (session.activeOperations || 0) + 1;
    session.lastActivityAt = Date.now();
    let result;
    try {
      result = await session.sock.sendMessage(recipient.jid, outgoingMessage);
      if (result?.key?.id && result?.message) {
        await storeBaileysMessage(message.user_id, recipient.jid, result.key.id, true, null, result.message);
      }
    } finally {
      session.activeOperations = Math.max(0, (session.activeOperations || 1) - 1);
      session.lastActivityAt = Date.now();
    }
  await markMessageSent(message, result, recipient.normalizedTo);
}

async function enqueueWebhookEvent(userId, event, payload, messageId = null) {
  try {
    const [rows] = await db.execute(
      'SELECT id, target_url, secret, secret_alt, enabled FROM webhooks WHERE user_id = ? AND enabled = 1 AND target_url IS NOT NULL LIMIT 1',
      [userId]
    );
    const webhook = rows[0];
    if (!webhook) {
      return null;
    }

    const eventId = payload?.event_id || `evt_${crypto.randomUUID()}`;
    const fullPayload = {
      event,
      event_id: eventId,
      user_id: Number(userId),
      ...payload,
      occurred_at: payload?.occurred_at || new Date().toISOString()
    };

    const payloadJson = JSON.stringify(fullPayload);

    await db.execute(
      `INSERT INTO webhook_queue
       (event_id, user_id, webhook_id, event, payload, status)
       VALUES (?, ?, ?, ?, ?, 'queued')
       ON DUPLICATE KEY UPDATE id = id`,
      [eventId, userId, webhook.id, event, payloadJson]
    );

    setImmediate(processWebhookQueueOnce);
    return eventId;
  } catch (error) {
    logger.warn({ error: error.message, userId, event }, 'Failed to enqueue webhook event');
    return null;
  }
}

async function deliverWebhookJob(job) {
  const [rows] = await db.execute(
    'SELECT * FROM webhooks WHERE user_id = ? AND enabled = 1 AND target_url IS NOT NULL LIMIT 1',
    [job.user_id]
  );
  const webhook = rows[0];
  if (!webhook) {
    await db.execute(
      `UPDATE webhook_queue SET status = 'failed', last_error = 'Webhook disabled or removed', locked_at = NULL, locked_by = NULL WHERE id = ?`,
      [job.id]
    );
    return;
  }

  const payload = typeof job.payload === 'string' ? JSON.parse(job.payload) : job.payload;
  const bodyString = typeof job.payload === 'string' ? job.payload : JSON.stringify(job.payload);

  // 1. SSRF Guard
  try {
    await assertSafeWebhookUrl(webhook.target_url);
  } catch (error) {
    await recordWebhookDelivery(webhook, payload, null, error.message, (job.attempts || 0) + 1);
    await db.execute(
      `UPDATE webhook_queue SET status = 'failed', last_error = ?, locked_at = NULL, locked_by = NULL WHERE id = ?`,
      [error.message, job.id]
    );
    logger.warn({ error: error.message, userId: job.user_id }, 'Blocked unsafe webhook target');
    return;
  }

  // 2. Prepare headers & HMAC signatures
  const timestamp = Math.floor(Date.now() / 1000).toString();
  const headers = {
    'Content-Type': 'application/json',
    'X-WA-Hub-Event-Id': job.event_id,
    'X-WA-Hub-Timestamp': timestamp
  };

  if (webhook.secret) {
    const sig = crypto.createHmac('sha256', webhook.secret).update(bodyString).digest('hex');
    headers['X-WA-Hub-Signature'] = sig;
    headers['X-Hub-Signature-256'] = `sha256=${sig}`;
  }

  // Secret rotation support: if secondary secret exists, sign with that too
  if (webhook.secret_alt) {
    const oldSig = crypto.createHmac('sha256', webhook.secret_alt).update(bodyString).digest('hex');
    headers['X-WA-Hub-Signature-Old'] = oldSig;
  }

  const currentAttempt = (Number(job.attempts) || 0) + 1;

  // 3. Dispatch HTTP POST request
  let success = false;
  let statusCode = null;
  let deliveryError = null;

  try {
    const response = await fetch(webhook.target_url, {
      method: 'POST',
      headers,
      body: bodyString,
      redirect: 'error',
      signal: AbortSignal.timeout(8000)
    });
    statusCode = response.status;
    if (response.ok) {
      success = true;
    } else {
      deliveryError = `HTTP ${response.status} ${response.statusText}`;
    }
  } catch (err) {
    deliveryError = err.message || 'Network error';
  }

  // 4. Record delivery attempt in webhook_deliveries
  await recordWebhookDelivery(webhook, payload, statusCode, deliveryError, currentAttempt);

  if (success) {
    await db.execute(
      `UPDATE webhook_queue
       SET status = 'delivered', locked_at = NULL, locked_by = NULL, attempts = ?
       WHERE id = ?`,
      [currentAttempt, job.id]
    );
    logger.info({ eventId: job.event_id, userId: job.user_id, attempt: currentAttempt }, 'Webhook successfully delivered');
  } else {
    const maxAttempts = Number(job.max_attempts) || 5;
    if (currentAttempt >= maxAttempts) {
      await db.execute(
        `UPDATE webhook_queue
         SET status = 'dead_letter', attempts = ?, last_error = ?, locked_at = NULL, locked_by = NULL
         WHERE id = ?`,
        [currentAttempt, deliveryError, job.id]
      );
      logger.warn({ eventId: job.event_id, userId: job.user_id, attempts: currentAttempt, error: deliveryError }, 'Webhook delivery reached terminal failure (dead letter)');
    } else {
      const baseDelay = Math.min(300, 5 * Math.pow(2, currentAttempt - 1));
      const jitter = Math.floor(Math.random() * 5);
      const delaySeconds = baseDelay + jitter;

      await db.execute(
        `UPDATE webhook_queue
         SET status = 'queued', attempts = ?, last_error = ?,
             available_at = DATE_ADD(NOW(), INTERVAL ? SECOND), locked_at = NULL, locked_by = NULL
         WHERE id = ?`,
        [currentAttempt, deliveryError, delaySeconds, job.id]
      );
      logger.info({ eventId: job.event_id, userId: job.user_id, attempt: currentAttempt, nextRetrySeconds: delaySeconds }, 'Webhook delivery failed; scheduled retry');
    }
  }
}

let webhookQueueBusy = false;
async function processWebhookQueueOnce() {
  if (webhookQueueBusy) return;
  webhookQueueBusy = true;

  try {
    await db.execute(
      `UPDATE webhook_queue
       SET status = 'dead_letter', locked_at = NULL, locked_by = NULL,
           last_error = COALESCE(last_error, 'Webhook worker lease expired after final attempt.')
       WHERE status = 'processing'
         AND (locked_at IS NULL OR locked_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE))
         AND attempts >= max_attempts`
    );

    await db.execute(
      `UPDATE webhook_queue
       SET status = 'queued', locked_at = NULL, locked_by = NULL,
           last_error = COALESCE(last_error, 'Webhook worker lease expired; returned to retry queue.')
       WHERE status = 'processing'
         AND (locked_at IS NULL OR locked_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE))
         AND attempts < max_attempts`
    );

    let job = null;
    const conn = await db.getConnection();
    try {
      await conn.beginTransaction();
      const [rows] = await conn.execute(
        `SELECT * FROM webhook_queue
         WHERE status = 'queued' AND available_at <= NOW()
         ORDER BY id ASC
         LIMIT 1
         FOR UPDATE SKIP LOCKED`
      );

      if (rows && rows.length > 0) {
        job = rows[0];
        await conn.execute(
          `UPDATE webhook_queue
           SET status = 'processing', locked_at = NOW(), locked_by = ?
           WHERE id = ?`,
          [WORKER_ID, job.id]
        );
      }
      await conn.commit();
    } catch (err) {
      try { await conn.rollback(); } catch (_) {}
      throw err;
    } finally {
      conn.release();
    }

    if (!job) {
      return;
    }

    await deliverWebhookJob(job);
    setImmediate(processWebhookQueueOnce);
  } catch (err) {
    logger.error({ err: err.message }, 'Webhook queue processor error');
  } finally {
    webhookQueueBusy = false;
  }
}

async function sendWebhook(userId, payload) {
  return enqueueWebhookEvent(userId, payload?.event || 'message.event', payload, payload?.message_id || null);
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

  // Link WhatsApp provider message ID to native chat_messages for live delivery & read receipts
  try {
    const payload = message.payload ? (typeof message.payload === 'string' ? JSON.parse(message.payload) : message.payload) : null;
    if (payload?.chat_message_id && result?.key?.id) {
      await db.execute(
        'UPDATE chat_messages SET wa_message_id = ?, status = "sent" WHERE id = ? AND user_id = ?',
        [result.key.id, payload.chat_message_id, message.user_id]
      );
    }
  } catch (_) {}

  // Ephemeral media cleanup with reference lifecycle management
  try {
    const payload = message.payload ? (typeof message.payload === 'string' ? JSON.parse(message.payload) : message.payload) : null;
    await safelyCleanupAttachment(message, payload);
  } catch (_) {}
}

async function markMessageFailed(message, error, normalizedTo = null, isUncertain = false) {
  const finalFail = isUncertain || Number(message.attempts) >= Number(message.max_attempts);
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

    // Cleanup temporary media with reference lifecycle management if final attempt failed
    try {
      const payload = message.payload ? (typeof message.payload === 'string' ? JSON.parse(message.payload) : message.payload) : null;
      await safelyCleanupAttachment(message, payload);
    } catch (_) {}
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
        const isUncertain = error.code === 'ECONNRESET' || error.message?.includes('socket') || error.message?.includes('timeout');
        await markMessageFailed(message, error.message, null, isUncertain);
      }
      setImmediate(processQueueOnce);
    } else {
      const now = Date.now();
      for (const session of sessions.values()) {
        if (!session.autoSleepEnabled) {
          continue; // Auto-sleep disabled by tenant
        }
        if (session.state !== 'connected' || !session.sock || session.sleeping) {
          continue; // Session is not connected or already sleeping
        }
        if ((session.activeOperations || 0) > 0) {
          continue; // Ongoing send or critical operation in progress; do not disconnect!
        }

        const timeoutSeconds = Math.max(15, Math.min(86400, Number(session.autoSleepTimeoutSeconds) || 60));
        const timeoutMs = timeoutSeconds * 1000;
        const lastActivity = session.lastActivityAt || now;

        if (now - lastActivity >= timeoutMs) {
          // Verify with database that auto-sleep is still enabled before sleeping
          try {
            const [dbRows] = await db.execute(
              'SELECT auto_sleep_enabled, auto_sleep_timeout_seconds FROM whatsapp_sessions WHERE user_id = ? LIMIT 1',
              [session.userId]
            );
            if (dbRows && dbRows[0]) {
              if (dbRows[0].auto_sleep_enabled !== undefined && dbRows[0].auto_sleep_enabled !== null) {
                session.autoSleepEnabled = Number(dbRows[0].auto_sleep_enabled) === 1;
              }
              if (dbRows[0].auto_sleep_timeout_seconds) {
                session.autoSleepTimeoutSeconds = Math.max(15, Math.min(86400, Number(dbRows[0].auto_sleep_timeout_seconds) || 60));
              }
            }
          } catch (_) {}

          if (!session.autoSleepEnabled) {
            continue; // Auto-sleep disabled by tenant in database
          }

          // Verify no active messages queued or processing for this tenant
          const [activeQueue] = await db.execute(
            `SELECT id FROM message_queue WHERE user_id = ? AND status IN ('queued', 'processing') AND available_at <= NOW() LIMIT 1`,
            [session.userId]
          );
          if (activeQueue && activeQueue.length > 0) {
            session.lastActivityAt = now;
            continue;
          }

          logger.info(`Session for user ${session.userId} reached idle timeout of ${timeoutSeconds}s without activity. Entering auto-sleep.`);
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

app.post('/sessions/:userId/pair-code', async (req, res) => {
  const userId = req.params.userId;
  const rawPhone = String(req.body?.phone || req.body?.phoneNumber || '').trim();
  const cleanPhone = rawPhone.replace(/\D/g, '');

  if (!cleanPhone || cleanPhone.length < 8 || cleanPhone.length > 15) {
    return res.status(400).json({
      ok: false,
      error: 'Please provide a valid phone number with country code (8 to 15 digits, e.g. 15551234567).'
    });
  }

  try {
    let session = getSession(userId);

    if (session.state === 'connected' && session.phone) {
      return res.status(400).json({
        ok: false,
        error: 'A device is already connected. Please logout before pairing a new device.'
      });
    }

    const authDir = join(SESSION_DIR, `user_${userId}`);
    const credsFile = join(authDir, 'creds.json');
    let isRegistered = false;
    if (existsSync(credsFile)) {
      try {
        const parsed = JSON.parse(readFileSync(credsFile, 'utf8') || '{}');
        isRegistered = Boolean(parsed.registered);
      } catch (_) {}
    }

    // If existing credentials are for an unlinked/stale registered session, clean them up
    if (isRegistered || !session.sock || !['connecting', 'qr'].includes(session.state)) {
      if (isRegistered) {
        destroySocket(session);
        try { await rm(authDir, { recursive: true, force: true }); } catch (_) {}
      }
      session = await startSession(userId, true, false);
    }

    if (session.starting) {
      try {
        await session.starting;
      } catch (err) {
        logger.warn({ userId, err: err.message }, 'Session starting encountered warning during pair-code request');
      }
    }

    const sock = session.sock;
    if (!sock) {
      throw new Error('WhatsApp gateway socket failed to initialize.');
    }

    const timeoutPromise = new Promise((_, reject) =>
      setTimeout(() => reject(new Error('Connection timed out waiting for WhatsApp gateway.')), 15000)
    );

    await Promise.race([
      sock.waitForSocketOpen(),
      timeoutPromise
    ]);

    // Safety pause to allow WhatsApp server initial handshake
    await new Promise(resolve => setTimeout(resolve, 1000));

    const rawCode = await sock.requestPairingCode(cleanPhone);
    const formattedCode = rawCode ? (rawCode.match(/.{1,4}/g)?.join('-') || rawCode) : rawCode;

    session.pairingCode = formattedCode;
    session.error = null;

    res.json({
      ok: true,
      code: formattedCode,
      rawCode,
      session: publicSession(session)
    });
  } catch (error) {
    logger.error({ userId, error: error.message }, 'Failed to generate WhatsApp pairing code');
    const session = getSession(userId);
    session.error = error.message;
    res.status(500).json({
      ok: false,
      error: error.message || 'Failed to request pairing code from WhatsApp.',
      session: publicSession(session)
    });
  }
});

app.post('/sessions/:userId/logout', async (req, res) => {
  const session = getSession(req.params.userId);
  const authDir = join(SESSION_DIR, `user_${req.params.userId}`);

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

app.post('/sessions/:userId/auto-sleep', async (req, res) => {
  const userId = req.params.userId;
  const session = getSession(userId);
  const enabled = req.body?.auto_sleep_enabled !== undefined ? Boolean(req.body.auto_sleep_enabled) : true;
  const rawTimeout = Number(req.body?.auto_sleep_timeout_seconds) || 60;
  const timeoutSeconds = Math.max(15, Math.min(86400, rawTimeout));

  session.autoSleepEnabled = enabled;
  session.autoSleepTimeoutSeconds = timeoutSeconds;
  session.lastActivityAt = Date.now();

  try {
    await db.execute(
      `UPDATE whatsapp_sessions
       SET auto_sleep_enabled = ?, auto_sleep_timeout_seconds = ?
       WHERE user_id = ?`,
      [Number(enabled), timeoutSeconds, userId]
    );
  } catch (_) {}

  if (!enabled && session.sleeping) {
    session.sleeping = false;
    session.wasSleeping = false;
    session.lastActivityAt = Date.now();
    try {
      await updateSessionRecord(userId, session);
      startSession(userId, false, false).catch((err) => {
        logger.warn({ err: err.message, userId }, 'Could not automatically wake session after disabling auto-sleep');
      });
    } catch (_) {}
  }

  res.json({
    ok: true,
    user_id: Number(userId),
    auto_sleep_enabled: enabled,
    auto_sleep_timeout_seconds: timeoutSeconds
  });
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
  const targetRecipient = await resolveJidToPhone(req.params.userId, req.body.to, session);
  const recipient = normalizeRecipient(targetRecipient || req.body.to);
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

  // 1. Consent and suppression boundary check
  const isSuppressed = await isRecipientSuppressedInDb(req.params.userId, recipient.normalizedTo);
  if (isSuppressed) {
    res.status(403).json({
      ok: false,
      error: `The recipient ${recipient.normalizedTo} is suppressed or opted out.`,
      normalizedTo: recipient.normalizedTo
    });
    return;
  }

  try {
    if (!recipient.isGroup && !recipient.isLid && typeof session.sock.onWhatsApp === 'function') {
      const verified = await verifyRecipientLookup(session.sock, recipient.jid, recipient.normalizedTo);

      if (!verified.exists && !verified.transientError) {
        res.status(404).json({
          ok: false,
          error: `The number ${recipient.normalizedTo} is not registered on WhatsApp.`,
          normalizedTo: recipient.normalizedTo
        });
        return;
      }
      if (verified.jid) {
        recipient.jid = verified.jid;
      }
    }

    // 2. Centralized dispatch pipeline integration: record job in message_queue with priority 10
    const payload = JSON.stringify({ source: 'direct_send', type: 'text' });
    const [insertResult] = await db.execute(
      `INSERT INTO message_queue (user_id, api_key_id, recipient, normalized_recipient, message_type, body, payload, priority, status)
       VALUES (?, NULL, ?, ?, 'text', ?, ?, 10, 'processing')`,
      [req.params.userId, recipient.jid, recipient.normalizedTo, message, payload]
    );
    const queueId = insertResult.insertId;

    session.activeOperations = (session.activeOperations || 0) + 1;
    session.lastActivityAt = Date.now();
    let result;
    try {
      result = await session.sock.sendMessage(recipient.jid, { text: message });
      if (result?.key?.id && result?.message) {
        await storeBaileysMessage(req.params.userId, recipient.jid, result.key.id, true, null, result.message);
      }
    } finally {
      session.activeOperations = Math.max(0, (session.activeOperations || 1) - 1);
      session.lastActivityAt = Date.now();
    }

    const providerMessageId = result?.key?.id || null;
    await db.execute(
      `UPDATE message_queue
       SET status = 'sent', provider_message_id = ?, sent_at = NOW(), locked_at = NULL, locked_by = NULL
       WHERE id = ?`,
      [providerMessageId, queueId]
    );

    await db.execute(
      `INSERT INTO message_logs (user_id, recipient, message_preview, status, provider_response)
       VALUES (?, ?, ?, 'sent', ?)`,
      [req.params.userId, recipient.normalizedTo, String(message).slice(0, 255), JSON.stringify({ ok: true, messageId: providerMessageId })]
    );

    res.json({ ok: true, messageId: providerMessageId, queueId, normalizedTo: recipient.normalizedTo });
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

  try {
    const [rows] = await db.execute(
      'SELECT user_id, status, phone, push_name, sleeping, auto_sleep_enabled, auto_sleep_timeout_seconds, reachout_timelock_active, reachout_timelock_ends_at, reachout_timelock_type FROM whatsapp_sessions'
    );
    for (const row of rows) {
      const uid = String(row.user_id);
      const s = getSession(uid);
      s.phone = row.phone || null;
      s.pushName = row.push_name || null;
      s.state = row.status || 'idle';
      s.sleeping = Number(row.sleeping) === 1;
      if (row.auto_sleep_enabled !== undefined && row.auto_sleep_enabled !== null) {
        s.autoSleepEnabled = Number(row.auto_sleep_enabled) === 1;
      }
      if (row.auto_sleep_timeout_seconds) {
        s.autoSleepTimeoutSeconds = Math.max(15, Math.min(86400, Number(row.auto_sleep_timeout_seconds) || 60));
      }
      if (row.reachout_timelock_active !== undefined) {
        const isActive = Number(row.reachout_timelock_active) === 1;
        const endsAt = row.reachout_timelock_ends_at ? new Date(row.reachout_timelock_ends_at) : null;
        if (isActive && (!endsAt || endsAt.getTime() > Date.now())) {
          s.reachoutTimelock = {
            isActive: true,
            timeEnforcementEnds: endsAt,
            enforcementType: row.reachout_timelock_type || 'DEFAULT'
          };
        }
      }

      // Auto-reconnect active sessions so WhatsApp Baileys sockets are restored on worker startup
      if (row.status === 'connected' && !s.sleeping) {
        logger.info({ userId: uid }, 'Auto-reconnecting active WhatsApp session socket on worker startup');
        startSession(uid, false, false).catch((reconnectErr) => {
          logger.warn({ err: reconnectErr.message, userId: uid }, 'Failed to auto-reconnect session on worker startup');
        });
      }
    }
  } catch (err) {
    logger.warn({ err: err.message }, 'Failed to pre-load sessions from database on worker startup');
  }

  const server = app.listen(PORT, '127.0.0.1', () => {
    console.info(`[worker] listener=http://127.0.0.1:${PORT} health_route=GET /health database=ready`);
    setInterval(processQueueOnce, QUEUE_POLL_MS);
    setInterval(processWebhookQueueOnce, QUEUE_POLL_MS);
  });
  server.on('error', (err) => {
    if (err.code === 'EADDRINUSE') {
      console.error(`[worker] FATAL: Port ${PORT} is already in use by another instance. Exiting immediately.`);
    } else {
      console.error(`[worker] Server error:`, err);
    }
    process.exit(1);
  });
  attachClientErrorDiagnostics(server, logger);
}

startWorker().catch(async (error) => {
  logger.error({ error_class: error?.name || 'Error', error_code: error?.code || null, error_message: safeErrorMessage(error) }, 'Worker startup failed; queue processor was not started');
  await db.end().catch(() => {});
  process.exit(1);
});

