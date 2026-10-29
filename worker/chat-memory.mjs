/**
 * WhatsApp Multi-Turn Conversation Memory Engine
 * -----------------------------------------------
 * Retrieves, windows, and structures recent conversation dialogue turns
 * from the database to give the AI engine human-like conversational memory.
 * Supports rolling Time-To-Live (TTL) decay to avoid 24/7 memory bloat.
 */

export class ChatMemoryEngine {
  /**
   * Fetch recent dialogue turns for the active working session
   * Enforces rolling Time-To-Live (TTL) inactivity decay: if silence exceeds ttlMinutes,
   * working memory resets to a fresh slate to prevent 24/7 context pollution.
   * @param {Object} db - MySQL connection/pool
   * @param {number|string} conversationId
   * @param {string} [sessionUuid]
   * @param {number} [maxTurns=6] - Max historical messages to include
   * @param {number} [ttlMinutes=45] - Inactivity decay window in minutes (default 45)
   * @returns {Promise<Array<{ senderType: string, body: string, role: string }>>}
   */
  static async getRecentTurns(db, conversationId, sessionUuid = null, maxTurns = 6, ttlMinutes = 45) {
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
      query += ' AND (session_uuid = ? OR session_uuid IS NULL)';
      params.push(sessionUuid);
    }

    if (ttlMinutes && Number(ttlMinutes) > 0) {
      query += ' AND created_at >= NOW() - INTERVAL ? MINUTE';
      params.push(Number(ttlMinutes));
    }

    query += ' ORDER BY id DESC LIMIT ?';
    params.push(Math.max(2, Math.min(maxTurns, 20)));

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
   * Build Gemini-compatible contents array including history
   * @param {string} systemPrompt
   * @param {Array<{ role: string, body: string }>} historyTurns
   * @param {string} currentMessage
   * @returns {Array<{ role: string, parts: Array<{ text: string }> }>}
   */
  static buildGeminiContents(systemPrompt, historyTurns, currentMessage) {
    const contents = [];

    // System instruction is prepended to the first turn or combined
    const systemPrefix = systemPrompt ? `[Instructions]\n${systemPrompt}\n\n` : '';

    if (!historyTurns || historyTurns.length === 0) {
      contents.push({
        role: 'user',
        parts: [{ text: `${systemPrefix}[Customer Message]\n${currentMessage}` }]
      });
      return contents;
    }

    // Append history turns
    for (let i = 0; i < historyTurns.length; i++) {
      const turn = historyTurns[i];
      let turnText = turn.body;
      if (i === 0 && systemPrefix && turn.role === 'user') {
        turnText = `${systemPrefix}[Customer Message]\n${turnText}`;
      }
      contents.push({
        role: turn.role === 'model' ? 'model' : 'user',
        parts: [{ text: turnText }]
      });
    }

    // Ensure the current message is the final user turn
    const lastTurn = contents[contents.length - 1];
    if (lastTurn && lastTurn.role === 'user') {
      lastTurn.parts[0].text += `\n${currentMessage}`;
    } else {
      contents.push({
        role: 'user',
        parts: [{ text: currentMessage }]
      });
    }

    return contents;
  }

  /**
   * Build OpenAI / Groq compatible messages array including history
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

    if (historyTurns && historyTurns.length > 0) {
      for (const turn of historyTurns) {
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
