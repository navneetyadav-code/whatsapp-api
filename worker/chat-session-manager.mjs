/**
 * WhatsApp Chat Session & Ticket Lifecycle Manager
 * ------------------------------------------------
 * Manages unique inquiry tickets (SES-YYYYMMDD-XXXX).
 * When customer inquiry is resolved, the session archives.
 * When the customer sends a fresh inquiry, a brand new ticket session is created.
 */

import crypto from 'crypto';

/**
 * Generate human-friendly unique Session Ticket ID
 * Format: SES-YYYYMMDD-XXXXXX (e.g. SES-20261028-A4F91B)
 * @returns {string}
 */
export function generateSessionUuid() {
  const d = new Date();
  const yyyy = d.getFullYear();
  const mm = String(d.getMonth() + 1).padStart(2, '0');
  const dd = String(d.getDate()).padStart(2, '0');
  const rand = crypto.randomBytes(3).toString('hex').toUpperCase();
  return `SES-${yyyy}${mm}${dd}-${rand}`;
}

/**
 * Check if incoming text is an explicit wake-up / start command (/start or start)
 * @param {string} text
 * @returns {boolean}
 */
export function isWakeupTrigger(text) {
  if (!text || typeof text !== 'string') return false;
  const clean = text.trim().toLowerCase().replace(/[.!?]/g, '');
  return /^(\/start|start)$/.test(clean);
}

/**
 * Resolve active session for incoming message or initialize fresh session
 * @param {Object} db - MySQL connection/pool
 * @param {number|string} userId
 * @param {string} senderPhone
 * @param {string} [senderName]
 * @param {string} [messagePreview]
 * @returns {Promise<{ conversationId: number, sessionUuid: string, isNewSession: boolean, status: string, botPaused: boolean, wokeUp: boolean }>}
 */
export async function resolveActiveSession(db, userId, senderPhone, senderName = null, messagePreview = '') {
  const [rows] = await db.execute(
    'SELECT id, session_uuid, status, bot_paused, awaiting_feedback, consecutive_bot_replies FROM chat_conversations WHERE user_id = ? AND phone = ? LIMIT 1',
    [userId, senderPhone]
  );

  let conv = rows && rows.length > 0 ? rows[0] : null;
  let sessionUuid = conv?.session_uuid;
  let isNewSession = false;

  if (!conv) {
    // 1. First time customer ever messages: create new conversation & session
    sessionUuid = generateSessionUuid();
    isNewSession = true;

    const [res] = await db.execute(
      `INSERT INTO chat_conversations 
       (user_id, phone, session_uuid, contact_name, last_message, last_message_at, unread_count, status, closed_at, awaiting_feedback, consecutive_bot_replies, is_auto_closed)
       VALUES (?, ?, ?, ?, ?, NOW(), 1, 'open', NULL, 0, 0, 0)`,
      [userId, senderPhone, sessionUuid, senderName, messagePreview.slice(0, 200)]
    );

    return {
      conversationId: res.insertId,
      sessionUuid,
      isNewSession: true,
      status: 'open',
      botPaused: false,
      wokeUp: isWakeupTrigger(messagePreview)
    };
  }

  // 2. Existing conversation exists: check if closed or active
  if (conv.status === 'closed') {
    const isWakeup = isWakeupTrigger(messagePreview);
    if (!isWakeup) {
      // Conversation is closed and user did NOT send /start or start.
      // Keep conversation closed, DO NOT spawn a new session, DO NOT wake the chatbot!
      await db.execute(
        `UPDATE chat_conversations
         SET contact_name = COALESCE(?, contact_name),
             last_message = ?,
             last_message_at = NOW(),
             unread_count = unread_count + 1
         WHERE id = ?`,
        [senderName, messagePreview.slice(0, 200), conv.id]
      );
      return {
        conversationId: conv.id,
        sessionUuid: conv.session_uuid || generateSessionUuid(),
        isNewSession: false,
        status: 'closed',
        botPaused: true,
        wokeUp: false
      };
    }

    // User explicitly sent /start or start to wake up the bot! Fresh session begins!
    sessionUuid = generateSessionUuid();
    isNewSession = true;

    await db.execute(
      `UPDATE chat_conversations
       SET session_uuid = ?,
           contact_name = COALESCE(?, contact_name),
           last_message = ?,
           last_message_at = NOW(),
           unread_count = unread_count + 1,
           status = 'open',
           closed_at = NULL,
           awaiting_feedback = 0,
           consecutive_bot_replies = 0,
           is_auto_closed = 0,
           resolution_remark = NULL,
           bot_paused = 0
       WHERE id = ?`,
      [sessionUuid, senderName, messagePreview.slice(0, 200), conv.id]
    );

    return {
      conversationId: conv.id,
      sessionUuid,
      isNewSession: true,
      status: 'open',
      botPaused: false,
      wokeUp: true
    };
  }

  // 3. Conversation is active (open or escalated)
  if (!sessionUuid) {
    sessionUuid = generateSessionUuid();
  }

  const isWakeup = isWakeupTrigger(messagePreview);

  await db.execute(
    `UPDATE chat_conversations
     SET session_uuid = COALESCE(session_uuid, ?),
         contact_name = COALESCE(?, contact_name),
         last_message = ?,
         last_message_at = NOW(),
         unread_count = unread_count + 1,
         consecutive_bot_replies = 0
     WHERE id = ?`,
    [sessionUuid, senderName, messagePreview.slice(0, 200), conv.id]
  );

  return {
    conversationId: conv.id,
    sessionUuid,
    isNewSession: false,
    status: conv.status,
    botPaused: Boolean(Number(conv.bot_paused)),
    wokeUp: isWakeup
  };
}

/**
 * Close conversation session with resolution remark and feedback prompt
 * @param {Object} db
 * @param {number|string} conversationId
 * @param {string} remark
 * @param {boolean} [isAuto=true]
 */
export async function closeSession(db, conversationId, remark, isAuto = true) {
  await db.execute(
    `UPDATE chat_conversations
     SET status = 'closed',
         closed_at = NOW(),
         resolution_remark = ?,
         is_auto_closed = ?,
         awaiting_feedback = 1,
         consecutive_bot_replies = 0
     WHERE id = ?`,
    [remark.slice(0, 500), isAuto ? 1 : 0, conversationId]
  );
}
