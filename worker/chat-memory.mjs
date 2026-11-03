/**
 * WhatsApp Multi-Turn Conversation Memory Engine
 * -----------------------------------------------
 * Retrieves, windows, and structures recent conversation dialogue turns
 * from the database to give the AI engine human-like conversational memory.
 * Preserves multi-hour context continuity while avoiding prompt bloat.
 */

export class ChatMemoryEngine {
  /**
   * Fetch recent dialogue turns for the active working session
   * @param {Object} db - MySQL connection/pool
   * @param {number|string} conversationId
   * @param {string} [sessionUuid]
   * @param {number} [maxTurns=16] - Max historical messages to include
   * @param {number|null} [ttlMinutes=null] - Inactivity decay window in minutes (null = disabled)
   * @returns {Promise<Array<{ senderType: string, body: string, role: string, openAiRole: string }>>}
   */
  static async getRecentTurns(db, conversationId, sessionUuid = null, maxTurns = 16, ttlMinutes = null) {
    if (!conversationId) return [];

    let query = `
      SELECT sender_type, body, created_at
      FROM chat_messages
      WHERE conversation_id = ?
        AND message_type = 'text'
        AND body IS NOT NULL
        AND TRIM(body) != ''
    `;
    const params = [conversationId];

    if (sessionUuid) {
      // Prioritize active session but retain recent historical continuity (up to 24h)
      query += ' AND (session_uuid = ? OR session_uuid IS NULL OR created_at >= NOW() - INTERVAL 24 HOUR)';
      params.push(sessionUuid);
    }

    if (ttlMinutes && Number(ttlMinutes) > 0) {
      query += ' AND created_at >= NOW() - INTERVAL ? MINUTE';
      params.push(Number(ttlMinutes));
    }

    query += ' ORDER BY id DESC LIMIT ?';
    params.push(Math.max(2, Math.min(Number(maxTurns) || 16, 30)));

    try {
      const [rows] = await db.execute(query, params);
      if (!rows || rows.length === 0) return [];

      // Reverse to chronological order (oldest to newest)
      const chronological = rows.reverse();

      return chronological.map(r => {
        const isUser = r.sender_type === 'contact';
        return {
          senderType: r.sender_type,
          body: (r.body || '').trim(),
          role: isUser ? 'user' : 'model',
          openAiRole: isUser ? 'user' : 'assistant'
        };
      });
    } catch (err) {
      console.warn('[ChatMemoryEngine] Failed to retrieve turns:', err.message);
      return [];
    }
  }

  /**
   * Build Gemini-compatible contents array including history with strict deduplication
   * @param {string} systemPrompt
   * @param {Array<{ role: string, body: string }>} historyTurns
   * @param {string} currentMessage
   * @returns {Array<{ role: string, parts: Array<{ text: string }> }>}
   */
  static buildGeminiContents(systemPrompt, historyTurns, currentMessage) {
    const contents = [];
    const systemPrefix = systemPrompt ? `[Instructions]\n${systemPrompt}\n\n` : '';

    // Filter out currentMessage if it is already present as the last user turn in history
    let cleanTurns = Array.isArray(historyTurns) ? [...historyTurns] : [];
    if (cleanTurns.length > 0 && currentMessage) {
      const last = cleanTurns[cleanTurns.length - 1];
      if ((last.role === 'user' || last.senderType === 'contact') &&
          last.body.trim().toLowerCase() === currentMessage.trim().toLowerCase()) {
        cleanTurns.pop();
      }
    }

    if (cleanTurns.length === 0) {
      contents.push({
        role: 'user',
        parts: [{ text: `${systemPrefix}[Customer Message]\n${currentMessage}` }]
      });
      return contents;
    }

    // Append history turns
    for (let i = 0; i < cleanTurns.length; i++) {
      const turn = cleanTurns[i];
      let turnText = turn.body;
      if (i === 0 && systemPrefix && turn.role === 'user') {
        turnText = `${systemPrefix}[Customer Message]\n${turnText}`;
      }
      contents.push({
        role: turn.role === 'model' ? 'model' : 'user',
        parts: [{ text: turnText }]
      });
    }

    // Append current message as the final user turn
    contents.push({
      role: 'user',
      parts: [{ text: currentMessage }]
    });

    return contents;
  }

  /**
   * Build OpenAI / Groq compatible messages array including history with strict deduplication
   * @param {string} systemPrompt
   * @param {Array<{ openAiRole: string, body: string }>} historyTurns
   * @param {string} currentMessage
   * @returns {Array<{ role: string, content: string }>}
   */
  static buildOpenAiMessages(systemPrompt, historyTurns, currentMessage) {
    const messages = [];

    if (systemPrompt) {
      messages.push({ role: 'system', content: systemPrompt });
    }

    // Filter out currentMessage if it is already present as the last user turn in history
    let cleanTurns = Array.isArray(historyTurns) ? [...historyTurns] : [];
    if (cleanTurns.length > 0 && currentMessage) {
      const last = cleanTurns[cleanTurns.length - 1];
      if ((last.role === 'user' || last.openAiRole === 'user' || last.senderType === 'contact') &&
          last.body.trim().toLowerCase() === currentMessage.trim().toLowerCase()) {
        cleanTurns.pop();
      }
    }

    if (cleanTurns.length > 0) {
      for (const turn of cleanTurns) {
        messages.push({
          role: turn.openAiRole || (turn.senderType === 'contact' ? 'user' : 'assistant'),
          content: turn.body
        });
      }
    }

    messages.push({ role: 'user', content: currentMessage });
    return messages;
  }

  /**
   * Format history as a plain-text transcript (useful for prompt injection & RAG)
   * @param {Array<{ senderType: string, body: string }>} historyTurns
   * @returns {string}
   */
  static formatTranscript(historyTurns) {
    if (!historyTurns || historyTurns.length === 0) return '';
    return historyTurns
      .map(t => `${t.senderType === 'contact' ? 'Customer' : 'Assistant'}: ${t.body}`)
      .join('\n');
  }
}
