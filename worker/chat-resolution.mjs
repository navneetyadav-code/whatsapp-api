/**
 * WhatsApp Smart Auto-Resolution & AI Remark Generator
 * ----------------------------------------------------
 * Analyzes incoming messages and dialogue context to detect customer satisfaction
 * and query resolution (e.g. "thanks", "got it", "solved", "dhanyawad", "samajh gaya").
 * Automatically closes the inquiry, writes an AI resolution remark, and triggers
 * the 5-star customer feedback survey.
 */

export class ChatResolutionEngine {
  /**
   * Resolution signal keywords across English, Hindi, and Hinglish
   */
  static RESOLUTION_PATTERNS = [
    /\b(thank\s*you|thanks|thx|thnx|ty)\b/i,
    /\b(got\s*it|understood|clear\s*now|all\s*good|sorted|solved|fixed)\b/i,
    /\b(shukriya|dhanyawad|dhanyavaad|bahut\s*shukriya)\b/i,
    /\b(samajh\s*gaya|samajh\s*aagaya|ho\s*gaya|theek\s*hai\s*bhai|kaam\s*ho\s*gaya)\b/i,
    /\b(problem\s*solved|resolved|that\s*helped|perfect\s*thanks)\b/i,
    /\b(no\s*more\s*questions|that\s*is\s*all|that's\s*all|nothing\s*else|bas\s*yahi)\b/i,
    /\b(close\s*(the\s*)?chat|end\s*(the\s*)?chat|chat\s*close|chat\s*band|band\s*kar(o|do))\b/i,
    /\b(good\s*bye|goodbye|bye\s*bye|byy|byee|take\s*care|alvida)\b/i
  ];

  /**
   * Negative indicators (words indicating the issue is NOT resolved)
   * Avoids bare 'problem' or 'issue' so phrases like 'problem solved' or 'issue resolved' are not blocked.
   */
  static UNRESOLVED_PATTERNS = [
    /\b(not\s*(working|solved|fixed|resolved|helping)|still\s*(not|unresolved)|doesn't\s*work|nahi\s*hua)\b/i,
    /\b(facing\s*(an?\s*)?(issue|problem)|have\s*(an?\s*)?(issue|problem)|there\s*is\s*(an?\s*)?(issue|problem)|kuch\s*problem\s*hai)\b/i,
    /\b(lekin|par|but)\b.*\b(not|issue|problem|broken|wrong)\b/i
  ];

  /**
   * Detect if the customer message signals query resolution
   * @param {string} text - Customer's latest message
   * @param {Array<{ senderType: string, body: string }>} [historyTurns=[]] - Prior dialogue context
   * @returns {{ isResolved: boolean, confidence: number, remark: string|null }}
   */
  static detectResolution(text, historyTurns = []) {
    if (!text || typeof text !== 'string') {
      return { isResolved: false, confidence: 0, remark: null };
    }

    const clean = text.trim();

    // Check for negative unresolved blockers first (e.g. "thanks but it's not working")
    for (const blocker of this.UNRESOLVED_PATTERNS) {
      if (blocker.test(clean)) {
        return { isResolved: false, confidence: 0, remark: null };
      }
    }

    // Must have at least 1 prior bot message in history for resolution to make sense
    const hasPriorBotTurn = historyTurns.some(t => t.senderType === 'bot' || t.senderType === 'agent');
    if (!hasPriorBotTurn && clean.length > 25) {
      // First turn cannot be resolved unless it's a very brief standalone closing
      return { isResolved: false, confidence: 0, remark: null };
    }

    let matchCount = 0;
    for (const pattern of this.RESOLUTION_PATTERNS) {
      if (pattern.test(clean)) {
        matchCount++;
      }
    }

    if (matchCount === 0) {
      return { isResolved: false, confidence: 0, remark: null };
    }

    // Confidence scoring
    let confidence = 0.75;
    if (clean.length < 35) confidence += 0.15; // Brief standalone gratitude
    if (matchCount >= 2) confidence += 0.10;

    // Generate concise resolution remark summarizing the conversation
    const userTopics = historyTurns
      .filter(t => t.senderType === 'contact')
      .map(t => t.body.slice(0, 60))
      .filter(Boolean);

    let summaryTopic = userTopics.length > 0 ? userTopics[0] : 'Customer inquiry';
    if (summaryTopic.length > 50) summaryTopic = summaryTopic.slice(0, 50) + '...';

    const remark = `Resolved inquiry regarding "${summaryTopic}". Customer acknowledged resolution with "${clean.slice(0, 40)}".`;

    return {
      isResolved: confidence >= 0.75,
      confidence: Math.min(1.0, confidence),
      remark
    };
  }

  /**
   * Generate closure text and feedback request for WhatsApp
   * @param {string} [customSalutation]
   * @returns {string}
   */
  static getClosureMessage(customSalutation = 'Glad I could help you today!') {
    return `${customSalutation} 🙏 I have marked this conversation as resolved.\n\n` +
      `⭐ *How would you rate your support experience today?*\n` +
      `Please reply with a number from *1 to 5* (⭐ 1 = Poor, ⭐⭐⭐⭐⭐ 5 = Excellent).\n\n` +
      `_(Note: To begin a fresh inquiry at any time, simply send */start* or *start*)_`;
  }
}
