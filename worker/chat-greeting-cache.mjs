/**
 * WhatsApp Greeting Filter & Token Burn Optimizer
 * ------------------------------------------------
 * Intercepts low-entropy repeated greetings ("hii", "hello", "hey", "namaste")
 * from triggering expensive LLM token consumption in active conversations.
 * Responds with lightweight, context-aware local prompts when appropriate.
 */

export class GreetingCache {
  /**
   * @param {Object} [options]
   * @param {number} [options.cooldownMs=300000] - 5-minute cooldown for repeated greetings
   */
  constructor({ cooldownMs = 300000 } = {}) {
    this.cooldownMs = cooldownMs;
    /** @type {Map<string, { lastGreetingAt: number, greetingCount: number, greetedSessions: Set<string> }>} */
    this.cache = new Map();

    // Standard low-entropy greeting pattern (supports "hi", "hello there", "namaste ji", etc.)
    this.greetingRegex = /^(h+i+|h+e+y+|h+e+l+l+o+|namaste+|hola+|s+u+p+|yo+|kem\s*cho|salaam|pranam|good\s*(morning|afternoon|evening|day))(\s+(there|sir|madam|ji|bhai|dear|friend|buddy|bro|team))?[\s\.\!\?\:\)\-\_]*$/i;
  }

  /**
   * Check if text is solely a greeting without substantive question/instruction
   * @param {string} text
   * @returns {boolean}
   */
  isGreetingOnly(text) {
    if (!text || typeof text !== 'string') return false;
    const clean = text.trim();
    if (clean.length > 30) return false; // Substantive messages exceed short greeting length
    return this.greetingRegex.test(clean);
  }

  /**
   * Evaluate whether to intercept repeated greeting and save tokens
   * @param {string|number} userId
   * @param {string} senderPhone
   * @param {string} text
   * @param {string} [sessionUuid]
   * @returns {{ shouldIntercept: boolean, replyText: string|null }}
   */
  evaluate(userId, senderPhone, text, sessionUuid = null) {
    if (!this.isGreetingOnly(text)) {
      return { shouldIntercept: false, replyText: null };
    }

    const key = `${userId}:${senderPhone}`;
    const now = Date.now();
    let record = this.cache.get(key);

    if (!record) {
      record = {
        lastGreetingAt: now,
        greetingCount: 1,
        greetedSessions: new Set(sessionUuid ? [sessionUuid] : [])
      };
      this.cache.set(key, record);
      // Allow first greeting to pass through to LLM for full persona greeting
      return { shouldIntercept: false, replyText: null };
    }

    const timeSinceLast = now - record.lastGreetingAt;
    record.lastGreetingAt = now;
    record.greetingCount++;

    if (sessionUuid) {
      record.greetedSessions.add(sessionUuid);
    }

    // If within cooldown (e.g. 5 minutes) and user is spamming greetings
    if (timeSinceLast < this.cooldownMs) {
      let canned = "I'm right here! 👋 How can I help you today? Feel free to ask about our products, services, or any questions you have.";
      if (record.greetingCount >= 3) {
        canned = "I'm still here and listening! 😊 Please let me know what you need assistance with.";
      }
      return { shouldIntercept: true, replyText: canned };
    }

    // Cooldown expired: allow a fresh greeting turn
    record.greetingCount = 1;
    return { shouldIntercept: false, replyText: null };
  }

  /**
   * Reset cache for a user
   * @param {string|number} userId
   * @param {string} senderPhone
   */
  reset(userId, senderPhone) {
    this.cache.delete(`${userId}:${senderPhone}`);
  }
}

export const globalGreetingCache = new GreetingCache({ cooldownMs: 300000 });


// Alias export for flexible imports
export { GreetingCache as ChatGreetingCache };
