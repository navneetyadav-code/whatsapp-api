/**
 * WhatsApp Inbound Message Debouncer & Fragment Consolidator
 * ---------------------------------------------------------
 * Solves the rapid-fire texting problem where users send fragmented messages
 * (e.g. "When will my order arrive", followed 1s later by "?").
 * Buffers incoming texts within a sliding window (default 2500ms) and consolidates
 * them into a single coherent prompt for the AI engine, preventing token waste
 * and double-reply anomalies.
 */

export class MessageDebouncer {
  /**
   * @param {Object} options
   * @param {number} [options.debounceMs=2500] - Sliding debounce delay in ms
   * @param {number} [options.maxWaitMs=6000] - Hard cap on delay before forced flush
   */
  constructor({ debounceMs = 2500, maxWaitMs = 6000 } = {}) {
    this.debounceMs = debounceMs;
    this.maxWaitMs = maxWaitMs;
    /** @type {Map<string, { timer: NodeJS.Timeout, hardTimer: NodeJS.Timeout, items: Array<any>, firstAt: number, onFlush: Function }>} */
    this.buffers = new Map();
  }

  /**
   * Build unique buffer key
   * @param {string|number} userId
   * @param {string} remoteJid
   * @returns {string}
   */
  getKey(userId, remoteJid) {
    return `${userId}:${remoteJid}`;
  }

  /**
   * Check if text is just orphan punctuation (e.g. "?", "??", "!", "...")
   * @param {string} text
   * @returns {boolean}
   */
  isPunctuationOnly(text) {
    if (!text || typeof text !== 'string') return false;
    const clean = text.trim();
    return clean.length > 0 && clean.length <= 4 && /^[\?\!\.\,\;\:\-\_\~\s]+$/.test(clean);
  }

  /**
   * Enqueue message into the sliding debounce buffer
   * @param {string|number} userId
   * @param {string} remoteJid
   * @param {Object} item - Message packet ({ text, norm, msg, session, senderPhone })
   * @param {Function} onFlush - Callback invoked with consolidated message batch
   */
  enqueue(userId, remoteJid, item, onFlush) {
    const key = this.getKey(userId, remoteJid);
    const text = (item.text || '').trim();
    const now = Date.now();

    let entry = this.buffers.get(key);

    if (entry) {
      // Clear sliding timer
      clearTimeout(entry.timer);

      // Append text to existing buffer
      if (this.isPunctuationOnly(text)) {
        // Attach punctuation to the previous message
        const last = entry.items[entry.items.length - 1];
        if (last) {
          last.text = `${(last.text || '').trim()} ${text}`.trim();
        } else {
          entry.items.push(item);
        }
      } else {
        entry.items.push(item);
      }

      // Reset sliding timer
      entry.timer = setTimeout(() => this.flush(key), this.debounceMs);
    } else {
      // New buffer entry
      entry = {
        items: [item],
        firstAt: now,
        onFlush,
        timer: null,
        hardTimer: null
      };

      entry.timer = setTimeout(() => this.flush(key), this.debounceMs);
      entry.hardTimer = setTimeout(() => this.flush(key), this.maxWaitMs);
      this.buffers.set(key, entry);
    }
  }

  /**
   * Flush and process consolidated buffer
   * @param {string} key
   */
  flush(key) {
    const entry = this.buffers.get(key);
    if (!entry) return;

    // Clear timers
    clearTimeout(entry.timer);
    clearTimeout(entry.hardTimer);
    this.buffers.delete(key);

    const items = entry.items || [];
    if (items.length === 0) return;

    // Consolidate texts
    const consolidatedText = items
      .map(it => (it.text || '').trim())
      .filter(Boolean)
      .join('\n');

    // Primary representative message (use the latest for timestamp and metadata)
    const primary = items[items.length - 1];

    try {
      entry.onFlush({
        consolidatedText,
        itemCount: items.length,
        items,
        primaryItem: primary
      });
    } catch (err) {
      console.error(`[MessageDebouncer] Error in onFlush for ${key}:`, err);
    }
  }

  /**
   * Cancel and discard any pending buffer for a user
   * @param {string|number} userId
   * @param {string} remoteJid
   */
  cancel(userId, remoteJid) {
    const key = this.getKey(userId, remoteJid);
    const entry = this.buffers.get(key);
    if (entry) {
      clearTimeout(entry.timer);
      clearTimeout(entry.hardTimer);
      this.buffers.delete(key);
    }
  }

  /**
   * Check if a buffer is currently accumulating messages
   * @param {string|number} userId
   * @param {string} remoteJid
   * @returns {boolean}
   */
  hasPending(userId, remoteJid) {
    return this.buffers.has(this.getKey(userId, remoteJid));
  }
}

// Global debouncer instance
export const globalDebouncer = new MessageDebouncer({ debounceMs: 2500, maxWaitMs: 6000 });


// Alias export for backwards/flexible compatibility
export { MessageDebouncer as ChatDebouncer };
