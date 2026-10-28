/**
 * WhatsApp Chat Session & Ticket Lifecycle Manager
 * ------------------------------------------------
 * Manages unique inquiry lifecycles per customer. Every fresh inquiry receives
 * a distinct session UUID. Once resolved/closed, previous chat is archived with its
 * resolution remark and CSAT rating. If the user messages again in the future,
 * a clean, fresh session is established.
 */

import crypto from 'crypto';

/**
 * Generate a clean, human-readable session UUID
 * Example: "SES-20261028-A4F9B2"
 * @returns {string}
 */
export function generateSessionUuid() {
  const dateStr = new Date().toISOString().slice(0, 10).replace(/-/g, '');
  const rand = crypto.randomBytes(3).toString('hex').toUpperCase();
  return `SES-${dateStr}-${rand}`;
}

/**
 * Resolve or initialize active session for an inbound customer message
 * @param {Object} db - MySQL promise connection/pool
 * @param {number|string} userId
 * @param {string} senderPhone
 * @param {string} [senderName]
 * @param {string} [messagePreview]
 * @returns {Promise<{ conversationId: number, sessionUuid: string, isNewSession: boolean, status: string, botPaused: boolean }>}
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
      botPaused: false
    };
  }

  // 2. Existing conversation exists: check if closed or active
  if (conv.status === 'closed') {
    // Previous session was closed. Fresh inquiry beginning!
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
      botPaused: false
    };
  }

  // 3. Conversation is active (open or escalated)
  if (!sessionUuid) {
    sessionUuid = generateSessionUuid();
    await db.execute(
      'UPDATE chat_conversations SET session_uuid = ? WHERE id = ?',
      [sessionUuid, conv.id]
    );
  }

  return {
    conversationId: conv.id,
    sessionUuid,
    isNewSession: false,
    status: conv.status,
    botPaused: Boolean(Number(conv.bot_paused))
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
