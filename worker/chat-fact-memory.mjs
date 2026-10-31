/**
 * Dual-Tier Cognitive Memory: Smart Fact Notebook & Training Engine
 * -----------------------------------------------------------------
 * Manages long-term factual entity memory, profile attributes, and preferences
 * for the AI assistant without carrying bloated 24/7 conversational dialogue transcripts.
 *
 * Supports:
 *  - Contact-scoped memory partitioning (Personal Owner vs. Customer Profile)
 *  - Token-conscious Top-K relevance retrieval (< 150 tokens)
 *  - Ephemeral auto-expiration (TTL) vs. Durable facts
 *  - In-chat deletion / updates
 *  - Dynamic few-shot training example extraction
 */

export class ChatFactMemory {
  /**
   * Load and format relevant persistent facts within a strict token budget
   * @param {Object} db - MySQL connection pool
   * @param {number|string} userId
   * @param {Object} [options={}]
   * @param {string} [options.contactPhone=''] - Specific customer phone or '' for owner
   * @param {string} [options.queryText=''] - Incoming customer text for relevance scoring
   * @param {number} [options.maxFacts=3] - Maximum facts to return (strictly capped)
   * @returns {Promise<{ factsText: string, factsCount: number, factsMap: Record<string, string>, retrievedIds: number[] }>}
   */
  static async loadUserFacts(db, userId, options = {}) {
    if (!db || !userId) {
      return { factsText: '', factsCount: 0, factsMap: {}, retrievedIds: [] };
    }

    const contactPhone = options.contactPhone ? String(options.contactPhone).trim().replace(/[^\d+]/g, '') : '';
    const queryText = (options.queryText || '').toLowerCase().trim();
    const maxFacts = Math.min(Math.max(Number(options.maxFacts) || 3, 1), 10);

    try {
      // 1. Fetch active, non-expired facts for this user & scope
      const [rows] = await db.execute(
        `SELECT id, memory_type, memory_key, memory_value, is_durable, expires_at
         FROM ai_user_memory
         WHERE user_id = ?
           AND contact_phone = ?
           AND (expires_at IS NULL OR expires_at > NOW())
         ORDER BY id DESC`,
        [userId, contactPhone]
      );

      if (!rows || rows.length === 0) {
        return { factsText: '', factsCount: 0, factsMap: {}, retrievedIds: [] };
      }

      let selectedRows = rows;

      // 2. Token-Conservation: If queryText is provided, score and select Top-K facts
      if (queryText && rows.length > maxFacts) {
        const queryWords = queryText
          .replace(/[^\w\s]/g, ' ')
          .split(/\s+/)
          .filter(w => w.length > 2);

        const scored = rows.map(r => {
          let score = 0;
          const keyNorm = (r.memory_key || '').replace(/_/g, ' ').toLowerCase();
          const valNorm = (r.memory_value || '').toLowerCase();

          for (const word of queryWords) {
            if (keyNorm.includes(word)) score += 3; // Key match is high value
            if (valNorm.includes(word)) score += 1;
          }

          // Slightly prioritize durable facts
          if (r.is_durable) score += 0.5;

          return { ...r, score };
        });

        // Sort descending by score, then by recency
        scored.sort((a, b) => b.score - a.score || b.id - a.id);

        // Pick top items (only take items that have some relevance if score > 0, otherwise pick latest maxFacts)
        const relevant = scored.filter(s => s.score > 0);
        selectedRows = relevant.length > 0 ? relevant.slice(0, maxFacts) : scored.slice(0, maxFacts);
      } else if (rows.length > maxFacts) {
        selectedRows = rows.slice(0, maxFacts);
      }

      const factsMap = {};
      const bulletLines = [];
      const retrievedIds = [];

      for (const r of selectedRows) {
        factsMap[r.memory_key] = r.memory_value;
        retrievedIds.push(r.id);
        const formattedKey = r.memory_key
          .replace(/_/g, ' ')
          .replace(/\b\w/g, c => c.toUpperCase());
        bulletLines.push(`• ${formattedKey}: ${r.memory_value}`);
      }

      // 3. Increment access tracking asynchronously
      if (retrievedIds.length > 0) {
        const placeholders = retrievedIds.map(() => '?').join(',');
        db.execute(
          `UPDATE ai_user_memory
           SET access_count = access_count + 1, last_accessed_at = NOW()
           WHERE id IN (${placeholders})`,
          retrievedIds
        ).catch(() => {});
      }

      const headerLabel = contactPhone ? '[CUSTOMER PROFILE & NOTES]' : '[PERSISTENT FACT NOTEBOOK]';
      const factsText = bulletLines.length > 0 ? `${headerLabel}\n${bulletLines.join('\n')}` : '';

      return {
        factsText,
        factsCount: selectedRows.length,
        factsMap,
        retrievedIds
      };
    } catch (err) {
      console.warn('[ChatFactMemory] Failed to load user facts:', err.message);
      return { factsText: '', factsCount: 0, factsMap: {}, retrievedIds: [] };
    }
  }

  /**
   * Upsert a factual memory for the user or contact
   * @param {Object} db - MySQL connection pool
   * @param {number|string} userId
   * @param {string} key
   * @param {string} value
   * @param {string} [type='fact']
   * @param {string} [source=null]
   * @param {number|string} [conversationId=null]
   * @param {string} [contactPhone='']
   * @param {boolean} [isDurable=true]
   * @param {number} [ttlDays=null] - Number of days before auto-expiry
   * @returns {Promise<{ success: boolean, key: string, value: string, contactPhone: string, error?: string }>}
   */
  static async upsertUserFact(
    db,
    userId,
    key,
    value,
    type = 'fact',
    source = null,
    conversationId = null,
    contactPhone = '',
    isDurable = true,
    ttlDays = null
  ) {
    if (!db || !userId) {
      return { success: false, error: 'Database or user context missing.' };
    }

    const cleanKey = String(key || '')
      .toLowerCase()
      .trim()
      .replace(/[^a-z0-9_]/g, '_')
      .slice(0, 64);

    const cleanVal = String(value || '').trim().slice(0, 1000);
    const cleanPhone = contactPhone ? String(contactPhone).trim().replace(/[^\d+]/g, '').slice(0, 32) : '';

    const validTypes = ['profile', 'fact', 'preference', 'entity', 'rule'];
    const cleanType = validTypes.includes(type) ? type : 'fact';

    if (!cleanKey || !cleanVal) {
      return { success: false, error: 'Memory key and value are required.' };
    }

    // Determine expiration
    let expiresAt = null;
    const durableFlag = isDurable ? 1 : 0;
    if (!isDurable && ttlDays && Number(ttlDays) > 0) {
      const d = new Date();
      d.setDate(d.getDate() + Number(ttlDays));
      expiresAt = d.toISOString().slice(0, 19).replace('T', ' ');
    }

    try {
      await db.execute(
        `INSERT INTO ai_user_memory
         (user_id, conversation_id, contact_phone, memory_type, is_durable, expires_at, memory_key, memory_value, source_context)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
           memory_value = VALUES(memory_value),
           memory_type = VALUES(memory_type),
           is_durable = VALUES(is_durable),
           expires_at = VALUES(expires_at),
           source_context = VALUES(source_context),
           updated_at = NOW()`,
        [
          userId,
          conversationId || null,
          cleanPhone,
          cleanType,
          durableFlag,
          expiresAt,
          cleanKey,
          cleanVal,
          source ? String(source).slice(0, 255) : null
        ]
      );

      return {
        success: true,
        key: cleanKey,
        value: cleanVal,
        type: cleanType,
        contactPhone: cleanPhone,
        isDurable: Boolean(durableFlag),
        expiresAt
      };
    } catch (err) {
      console.error('[ChatFactMemory] Failed to upsert fact:', err.message);
      return { success: false, error: err.message };
    }
  }

  /**
   * Delete a factual memory from the notebook
   * @param {Object} db - MySQL connection pool
   * @param {number|string} userId
   * @param {string} key
   * @param {string} [contactPhone='']
   * @returns {Promise<{ success: boolean, deleted: boolean, error?: string }>}
   */
  static async deleteUserFact(db, userId, key, contactPhone = '') {
    if (!db || !userId || !key) {
      return { success: false, deleted: false, error: 'Missing required parameters.' };
    }

    const cleanKey = String(key).toLowerCase().trim().replace(/[^a-z0-9_]/g, '_');
    const cleanPhone = contactPhone ? String(contactPhone).trim().replace(/[^\d+]/g, '') : '';

    try {
      const [res] = await db.execute(
        `DELETE FROM ai_user_memory
         WHERE user_id = ?
           AND contact_phone = ?
           AND (memory_key = ? OR memory_key LIKE ?)`,
        [userId, cleanPhone, cleanKey, `%${cleanKey}%`]
      );

      return {
        success: true,
        deleted: (res?.affectedRows || 0) > 0,
        affectedRows: res?.affectedRows || 0
      };
    } catch (err) {
      console.error('[ChatFactMemory] Failed to delete fact:', err.message);
      return { success: false, deleted: false, error: err.message };
    }
  }

  /**
   * Load active few-shot training examples for smarter in-context guidance
   * @param {Object} db - MySQL connection pool
   * @param {number|string} userId
   * @param {string} [category=null]
   * @param {number} [limit=2]
   * @returns {Promise<{ examplesText: string, constraintsText: string }>}
   */
  static async loadTrainingExamples(db, userId, category = null, limit = 2) {
    if (!db || !userId) {
      return { examplesText: '', constraintsText: '' };
    }

    try {
      let query = `
        SELECT user_query, ideal_response, negative_constraint
        FROM ai_training_examples
        WHERE user_id = ? AND is_active = 1
      `;
      const params = [userId];

      if (category) {
        query += ` AND (category = ? OR category = 'general')`;
        params.push(category);
      }

      query += ` ORDER BY id DESC LIMIT ?`;
      params.push(Math.min(limit, 5));

      const [rows] = await db.execute(query, params);
      if (!rows || rows.length === 0) {
        return { examplesText: '', constraintsText: '' };
      }

      const pairs = [];
      const constraints = [];

      for (const r of rows) {
        pairs.push(`User: "${r.user_query}"\nAssistant: "${r.ideal_response}"`);
        if (r.negative_constraint && r.negative_constraint.trim()) {
          constraints.push(`• ${r.negative_constraint.trim()}`);
        }
      }

      const examplesText = pairs.length > 0 ? `[GOLDEN FEW-SHOT EXAMPLES]\n${pairs.join('\n\n')}` : '';
      const constraintsText = constraints.length > 0 ? `[NEGATIVE CONSTRAINTS - WHAT NOT TO DO]\n${constraints.join('\n')}` : '';

      return { examplesText, constraintsText };
    } catch (err) {
      console.warn('[ChatFactMemory] Failed to load training examples:', err.message);
      return { examplesText: '', constraintsText: '' };
    }
  }
}
