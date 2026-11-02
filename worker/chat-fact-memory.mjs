/**
 * Dual-Tier Cognitive Memory: Persistent Fact Notebook Engine
 * -----------------------------------------------------------
 * Manages long-term factual entity memory, profile attributes, and preferences
 * for the AI assistant without carrying bloated 24/7 conversational dialogue transcripts.
 *
 * Hybrid Scaled Retrieval Architecture:
 * - Low-scale (<= 20 facts): Linear injection (instant, 0 math overhead, ~30 tokens)
 * - Large-scale (> 20 to 100+ facts): Semantic Vector Cosine Similarity retrieval (top-3 facts)
 */

const STOP_WORDS = new Set([
  'a', 'about', 'above', 'after', 'again', 'against', 'all', 'am', 'an', 'and', 'any', 'are', 'as', 'at',
  'be', 'because', 'been', 'before', 'being', 'below', 'between', 'both', 'but', 'by',
  'can', 'could', 'did', 'do', 'does', 'doing', 'down', 'during',
  'each', 'few', 'for', 'from', 'further',
  'had', 'has', 'have', 'having', 'he', 'her', 'here', 'hers', 'herself', 'him', 'himself', 'his', 'how',
  'i', 'if', 'in', 'into', 'is', 'it', 'its', 'itself',
  'just', 'me', 'more', 'most', 'my', 'myself',
  'no', 'nor', 'not', 'now', 'of', 'off', 'on', 'once', 'only', 'or', 'other', 'our', 'ours', 'ourselves', 'out', 'over', 'own',
  'same', 'she', 'should', 'so', 'some', 'such',
  'than', 'that', 'the', 'their', 'theirs', 'them', 'themselves', 'then', 'there', 'these', 'they', 'this', 'those', 'through', 'to', 'too',
  'under', 'until', 'up', 'very',
  'was', 'we', 'were', 'what', 'when', 'where', 'which', 'while', 'who', 'whom', 'why', 'with', 'would',
  'you', 'your', 'yours', 'yourself', 'yourselves'
]);

/**
 * Fast unicode-aware tokenization
 */
export function tokenize(text) {
  if (!text || typeof text !== 'string') return [];
  return text
    .toLowerCase()
    .replace(/[^\p{L}\p{N}\s]/gu, ' ')
    .split(/\s+/)
    .filter(token => token.length > 1 && !STOP_WORDS.has(token));
}

/**
 * Build Term-Frequency (TF) sparse vector
 */
export function buildTfVector(tokens) {
  const tf = {};
  if (!tokens || tokens.length === 0) return tf;
  for (const t of tokens) {
    tf[t] = (tf[t] || 0) + 1;
  }
  const total = tokens.length;
  for (const t in tf) {
    tf[t] = tf[t] / total;
  }
  return tf;
}

/**
 * Cosine similarity between two sparse TF vectors
 */
export function cosineSimilarity(vecA, vecB) {
  let dotProduct = 0;
  let normA = 0;
  let normB = 0;

  for (const key in vecA) {
    const valA = vecA[key];
    normA += valA * valA;
    if (key in vecB) {
      dotProduct += valA * vecB[key];
    }
  }

  for (const key in vecB) {
    const valB = vecB[key];
    normB += valB * valB;
  }

  if (normA === 0 || normB === 0) return 0;
  return dotProduct / (Math.sqrt(normA) * Math.sqrt(normB));
}

export class ChatFactMemory {
  /**
   * Load and format persistent facts with Hybrid Scaling:
   * - <= 20 facts: Linear injection (fast, zero math overhead, ~30 tokens)
   * - > 20 facts: Semantic Vector Cosine Similarity retrieval (top-3 relevant facts)
   *
   * @param {Object} db - MySQL connection pool
   * @param {number|string} userId
   * @param {string} [contactPhone='']
   * @param {string} [queryText=''] - The current user message to score relevance against
   * @param {Object} [options={}] - { vectorThreshold: 20, topK: 3 }
   * @returns {Promise<{ factsText: string, factsCount: number, totalFactsCount: number, retrievalMode: 'linear'|'vector', factsMap: Record<string, string> }>}
   */
  static async loadUserFacts(db, userId, contactPhone = '', queryText = '', options = {}) {
    if (!db || !userId) {
      return { factsText: '', factsCount: 0, totalFactsCount: 0, retrievalMode: 'linear', factsMap: {} };
    }

    const vectorThreshold = options.vectorThreshold || 20;
    const topK = options.topK || 3;

    try {
      const cleanPhone = String(contactPhone || '').replace(/[^\d]/g, '');
      const params = [userId];
      let query = `
        SELECT id, memory_type, memory_key, memory_value, contact_phone
        FROM ai_user_memory
        WHERE user_id = ?
      `;

      if (cleanPhone) {
        query += ` AND (contact_phone = '' OR contact_phone IS NULL OR contact_phone = ?)`;
        params.push(cleanPhone);
      } else {
        query += ` AND (contact_phone = '' OR contact_phone IS NULL)`;
      }

      query += ` ORDER BY memory_type ASC, id ASC`;

      const [rows] = await db.execute(query, params);

      if (!rows || rows.length === 0) {
        return { factsText: '', factsCount: 0, totalFactsCount: 0, retrievalMode: 'linear', factsMap: {} };
      }

      let selectedRows = rows;
      let retrievalMode = 'linear';

      // HYBRID SCALE LOGIC: If facts exceed threshold and a query message is available, run vector semantic search
      if (rows.length > vectorThreshold && queryText && queryText.trim()) {
        retrievalMode = 'vector';
        const queryTokens = tokenize(queryText);
        const queryTf = buildTfVector(queryTokens);
        const lowerQuery = queryText.toLowerCase();

        const scored = rows.map(r => {
          let sim = 0;
          const keyWords = r.memory_key.replace(/_/g, ' ');
          const factText = `${keyWords} ${r.memory_value}`;
          const factTokens = tokenize(factText);
          const factTf = buildTfVector(factTokens);

          sim = cosineSimilarity(queryTf, factTf);

          // Substring / Exact keyword boost
          const lowerKey = r.memory_key.toLowerCase();
          const cleanKeyNoUnderscore = lowerKey.replace(/_/g, ' ');
          if (lowerQuery.includes(cleanKeyNoUnderscore) || lowerQuery.includes(lowerKey)) {
            sim += 0.45;
          }

          // Exact value snippet boost (e.g. searching for a specific name or number)
          for (const token of queryTokens) {
            if (factText.toLowerCase().includes(token)) {
              sim += 0.15;
            }
          }

          // Core profile anchors (profile/rule) get baseline retention boost
          if (r.memory_type === 'profile' || r.memory_type === 'rule') {
            sim += 0.2;
          }

          return { ...r, score: sim };
        });

        // Sort descending by relevance score
        scored.sort((a, b) => b.score - a.score);

        // Take top-K most relevant facts
        selectedRows = scored.slice(0, topK);

        // Update access count and last accessed timestamp in background for analytics
        const selectedIds = selectedRows.map(r => r.id);
        if (selectedIds.length > 0) {
          db.query(`UPDATE ai_user_memory SET access_count = access_count + 1, last_accessed_at = NOW() WHERE id IN (${selectedIds.join(',')})`).catch(() => {});
        }
      }

      const factsMap = {};
      const bulletLines = [];

      for (const r of selectedRows) {
        factsMap[r.memory_key] = r.memory_value;
        const formattedKey = r.memory_key
          .replace(/_/g, ' ')
          .replace(/\b\w/g, c => c.toUpperCase());
        const scopeTag = r.contact_phone ? ' [Contact Note]' : '';
        bulletLines.push(`• ${formattedKey}${scopeTag}: ${r.memory_value}`);
      }

      const headerNote = retrievalMode === 'vector'
        ? `[PERSISTENT FACT NOTEBOOK (Semantic Match: ${selectedRows.length} of ${rows.length} facts)]`
        : `[PERSISTENT FACT NOTEBOOK]`;

      const factsText = `${headerNote}\n${bulletLines.join('\n')}`;

      return {
        factsText,
        factsCount: selectedRows.length,
        totalFactsCount: rows.length,
        retrievalMode,
        factsMap
      };
    } catch (err) {
      console.warn('[ChatFactMemory] Failed to load user facts:', err.message);
      return { factsText: '', factsCount: 0, totalFactsCount: 0, retrievalMode: 'linear', factsMap: {} };
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
   * @param {string} [contactPhone='']
   * @returns {Promise<{ success: boolean, key: string, value: string, error?: string }>}
   */
  static async upsertUserFact(db, userId, key, value, type = 'fact', source = null, conversationId = null, contactPhone = '') {
    if (!db || !userId) {
      return { success: false, error: 'Database or user context missing.' };
    }

    const cleanKey = String(key || '')
      .toLowerCase()
      .trim()
      .replace(/[^a-z0-9_]/g, '_')
      .slice(0, 64);

    const cleanVal = String(value || '').trim().slice(0, 1000);
    const cleanPhone = String(contactPhone || '').replace(/[^\d]/g, '');

    const validTypes = ['profile', 'fact', 'preference', 'entity', 'rule'];
    const cleanType = validTypes.includes(type) ? type : 'fact';

    if (!cleanKey || !cleanVal) {
      return { success: false, error: 'Memory key and value are required.' };
    }

    try {
      await db.execute(
        `INSERT INTO ai_user_memory
         (user_id, conversation_id, contact_phone, memory_type, memory_key, memory_value, source_context)
         VALUES (?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
           memory_value = VALUES(memory_value),
           memory_type = VALUES(memory_type),
           source_context = VALUES(source_context),
           updated_at = NOW()`,
        [userId, conversationId || null, cleanPhone, cleanType, cleanKey, cleanVal, source ? String(source).slice(0, 255) : null]
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
