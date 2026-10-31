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

    // Low-entropy acknowledgment and closing pattern (supports "thanks", "ok", "got it", "bye", etc.)
    this.ackRegex = /^(t+h+a+n+k+s*|t+h+x+|thank\s+you(\s+so\s+much|\s+a\s+lot)?|shukriya|dhanyawad|o+k+a*y*|k+|alright|got\s+it|understood|noted|perfect|awesome|great|cool|superb|b+y+e+|goodbye|cya|take\s+care)(\s+(there|sir|madam|ji|bhai|dear|friend|buddy|bro|team))?[\s\.\!\?\:\)\-\_]*$/i;
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
   * Check if text is solely a low-entropy acknowledgment or closing
   * @param {string} text
   * @returns {boolean}
   */
  isAcknowledgmentOnly(text) {
    if (!text || typeof text !== 'string') return false;
    const clean = text.trim();
    if (clean.length > 35) return false;
    return this.ackRegex.test(clean);
  }

  /**
   * Evaluate whether to intercept repeated greeting or acknowledgment and save tokens
   * @param {string|number} userId
   * @param {string} senderPhone
   * @param {string} text
   * @param {string} [sessionUuid]
   * @param {Object} [options={}]
   * @returns {{ shouldIntercept: boolean, replyText: string|null }}
   */
  evaluate(userId, senderPhone, text, sessionUuid = null, options = {}) {
    const isPersonal = options.isPersonalMode === true;
    const owner = options.ownerName || 'Boss';

    // 1. Intercept low-entropy acknowledgments & closings (saves 100% LLM tokens)
    if (!options.hasPendingDraft && this.isAcknowledgmentOnly(text)) {
      const clean = text.trim();
      let ackReply;
      if (/^(b+y+e+|goodbye|cya|take\s+care)/i.test(clean)) {
        ackReply = isPersonal
          ? `Take care, ${owner}! Have a great day ahead! 👋`
          : 'Goodbye! Have a wonderful day ahead! 👋';
      } else if (/^(t+h+a+n+k|t+h+x|shukriya|dhanyawad)/i.test(clean)) {
        ackReply = isPersonal
          ? `You're very welcome, ${owner}! Always here whenever you need me. 👍`
          : "You're very welcome! Feel free to reach out anytime if you need more help. 😊";
      } else {
        ackReply = isPersonal
          ? `Noted, ${owner}! 👍 Let me know whenever you need anything.`
          : 'Understood! Let us know if you need anything else. 👍';
      }
      return { shouldIntercept: true, replyText: ackReply };
    }

    // 2. Intercept repeated greetings
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
      let canned = isPersonal
        ? `I'm right here, ${owner}! 👋 How can I assist you right now? Let me know what you need.`
        : "I'm right here! 👋 How can I help you today? Feel free to ask any questions you have.";
      if (record.greetingCount >= 3) {
        canned = isPersonal
          ? `I'm right here on standby, ${owner}! 😊 Just let me know what you need.`
          : "I'm still here and listening! 😊 Please let me know what you need assistance with.";
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
