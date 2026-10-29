/**
 * Dual-Tier Cognitive Memory: Persistent Fact Notebook Engine
 * -----------------------------------------------------------
 * Manages long-term factual entity memory, profile attributes, and preferences
 * for the AI assistant without carrying bloated 24/7 conversational dialogue transcripts.
 */

export class ChatFactMemory {
  /**
   * Load and format all active persistent facts for a user
   * @param {Object} db - MySQL connection pool
   * @param {number|string} userId
   * @returns {Promise<{ factsText: string, factsCount: number, factsMap: Record<string, string> }>}
   */
  static async loadUserFacts(db, userId) {
    if (!db || !userId) {
      return { factsText: '', factsCount: 0, factsMap: {} };
    }

    try {
      const [rows] = await db.execute(
        `SELECT memory_type, memory_key, memory_value
         FROM ai_user_memory
         WHERE user_id = ?
         ORDER BY memory_type ASC, id ASC`,
        [userId]
      );

      if (!rows || rows.length === 0) {
        return { factsText: '', factsCount: 0, factsMap: {} };
      }

      const factsMap = {};
      const bulletLines = [];

      for (const r of rows) {
        factsMap[r.memory_key] = r.memory_value;
        const formattedKey = r.memory_key
          .replace(/_/g, ' ')
          .replace(/\b\w/g, c => c.toUpperCase());
        bulletLines.push(`• ${formattedKey}: ${r.memory_value}`);
      }

      const factsText = `[PERSISTENT FACT NOTEBOOK]\n${bulletLines.join('\n')}`;

      return {
        factsText,
        factsCount: rows.length,
        factsMap
      };
    } catch (err) {
      console.warn('[ChatFactMemory] Failed to load user facts:', err.message);
      return { factsText: '', factsCount: 0, factsMap: {} };
    }
  }

  /**
   * Upsert a durable factual memory for the user
   * @param {Object} db - MySQL connection pool
   * @param {number|string} userId
   * @param {string} key
   * @param {string} value
   * @param {string} [type='fact']
   * @param {string} [source=null]
   * @param {number|string} [conversationId=null]
   * @returns {Promise<{ success: boolean, key: string, value: string, error?: string }>}
   */
  static async upsertUserFact(db, userId, key, value, type = 'fact', source = null, conversationId = null) {
    if (!db || !userId) {
      return { success: false, error: 'Database or user context missing.' };
    }

    const cleanKey = String(key || '')
      .toLowerCase()
      .trim()
      .replace(/[^a-z0-9_]/g, '_')
      .slice(0, 64);

    const cleanVal = String(value || '').trim().slice(0, 1000);

    const validTypes = ['profile', 'fact', 'preference', 'entity', 'rule'];
    const cleanType = validTypes.includes(type) ? type : 'fact';

    if (!cleanKey || !cleanVal) {
      return { success: false, error: 'Memory key and value are required.' };
    }

    try {
      await db.execute(
        `INSERT INTO ai_user_memory
         (user_id, conversation_id, memory_type, memory_key, memory_value, source_context)
         VALUES (?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
           memory_value = VALUES(memory_value),
           memory_type = VALUES(memory_type),
           source_context = VALUES(source_context),
           updated_at = NOW()`,
        [userId, conversationId || null, cleanType, cleanKey, cleanVal, source ? String(source).slice(0, 255) : null]
      );

      return {
        success: true,
        key: cleanKey,
        value: cleanVal,
        type: cleanType
      };
    } catch (err) {
      console.error('[ChatFactMemory] Failed to upsert fact:', err.message);
      return { success: false, error: err.message };
    }
  }
}
