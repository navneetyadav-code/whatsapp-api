/**
 * Production Universal Dynamic Intent Router & Tool Composer
 * -------------------------------------------------------------
 * 100% Data-Driven, Zero Hardcoded Slugs, Zero Regex Dictionaries.
 * 
 * Features:
 *  1. Adaptive Fast-Path: When active tools <= 15, passes all native declarations (0ms).
 *  2. Semantic BM25 Ranker: When tools > 15, dynamically ranks and selects Top-K candidates.
 *  3. Generic Two-Phase Action Protocol: Dynamic staging and confirmation of high-impact tools.
 */

const STOP_WORDS = new Set([
  'a', 'an', 'the', 'is', 'are', 'was', 'were', 'am', 'and', 'or', 'to', 'in', 'on', 'at', 'by',
  'for', 'with', 'about', 'of', 'from', 'it', 'this', 'that', 'these', 'those', 'please', 'can',
  'could', 'would', 'should', 'will', 'do', 'does', 'did', 'bhai', 'yrr', 'hai', 'ho', 'bhi', 'se', 'ko', 'ki', 'ka', 'ke'
]);

/**
 * Tokenize text into normalized searchable tokens
 */
export function tokenizeText(text) {
  if (!text || typeof text !== 'string') return [];
  return text
    .toLowerCase()
    .replace(/[^\p{L}\p{N}\s]/gu, ' ')
    .split(/\s+/)
    .filter(t => t.length > 1 && !STOP_WORDS.has(t));
}

/**
 * Compute lexical relevance score between user message tokens and tool metadata
 */
export function scoreToolRelevance(messageTokens, tool) {
  if (!messageTokens || messageTokens.length === 0) return 0;

  const searchableText = [
    tool.display_name || '',
    tool.tool_slug || '',
    tool.description || '',
    tool.semantic_keywords || ''
  ].join(' ').toLowerCase();

  const toolTokens = new Set(tokenizeText(searchableText));
  if (toolTokens.size === 0) return 0;

  let matches = 0;
  for (const token of messageTokens) {
    if (toolTokens.has(token)) {
      matches += 1.5;
    } else {
      // Substring match for partial terms
      for (const tToken of toolTokens) {
        if (tToken.includes(token) || token.includes(tToken)) {
          matches += 0.8;
          break;
        }
      }
    }
  }

  return matches / Math.sqrt(toolTokens.size + 1);
}

const trainingExamplesCache = new Map();

/**
 * Dynamically retrieves relevant few-shot training examples using BM25 token overlap.
 * 100% data-driven: zero hardcoded categories, slugs, or prompts.
 */
export async function getRelevantTrainingExamples(db, userId, userMessage, topK = 2) {
  if (!db || !userId || !userMessage || typeof userMessage !== 'string') return [];

  let cached = trainingExamplesCache.get(userId);
  if (!cached || Date.now() > cached.expiresAt) {
    try {
      const [rows] = await db.execute(
        `SELECT id, example_type, user_prompt, assistant_response, category
         FROM ai_training_examples
         WHERE user_id = ? AND is_active = 1`,
        [userId]
      );
      cached = { expiresAt: Date.now() + 5 * 60 * 1000, examples: rows || [] };
      trainingExamplesCache.set(userId, cached);
    } catch (e) {
      return [];
    }
  }

  const examples = cached?.examples;
  if (!examples || examples.length === 0) return [];

  const tokens = tokenizeText(userMessage);
  if (tokens.length === 0) return [];

  const scored = examples.map(ex => {
    const searchable = `${ex.user_prompt} ${ex.category}`.toLowerCase();
    const exTokens = new Set(tokenizeText(searchable));
    if (exTokens.size === 0) return { ...ex, score: 0 };
    let score = 0;
    for (const t of tokens) {
      if (exTokens.has(t)) {
        score += 1.5;
      } else {
        for (const et of exTokens) {
          if (et.includes(t) || t.includes(et)) {
            score += 0.8;
            break;
          }
        }
      }
    }
    return { ...ex, score: score / Math.sqrt(exTokens.size + 1) };
  });

  scored.sort((a, b) => b.score - a.score);

  return scored.filter(s => s.score > 0.35).slice(0, topK);
}

/**
 * Detect general conversational intent
 */
export async function detectIntents(text, context = {}) {
  const clean = (text || '').trim();
  if (!clean) return ['general_assistant'];

  if (context.pendingAction) {
    return ['action_confirmation'];
  }

  return ['tool_invocation'];
}

/**
 * System tool declaration for confirm_pending_action
 */
export const SYSTEM_CONFIRM_TOOL = {
  tool_slug: 'confirm_pending_action',
  display_name: '✅ Confirm Pending Action',
  category: 'system_action',
  description: 'Confirms and executes a pending action that was staged and is awaiting user approval.',
  parameters_schema: {
    type: 'OBJECT',
    required: ['action_code'],
    properties: {
      action_code: {
        type: 'STRING',
        description: 'The unique action code (e.g., ACT-3F2A81) of the pending action to execute.'
      }
    }
  },
  rbac_roles: ['admin', 'customer', 'guest'],
  is_active: true
};

/**
 * System tool declaration for cancel_pending_action
 */

/**
 * System tool declaration for inspect_recent_actions
 */
export const SYSTEM_INSPECT_TOOL = {
  tool_slug: 'inspect_recent_actions',
  display_name: '🔍 Inspect Recent Actions',
  category: 'system_action',
  description: 'Inspect verified recent executed actions and their exact parameters from this conversation history (e.g. to recall what email was drafted or sent, review recently executed tool arguments, or verify action parameters).',
  parameters_schema: {
    type: 'OBJECT',
    properties: {
      limit: {
        type: 'INTEGER',
        description: 'Number of recent executed actions to retrieve (1 to 5, default 2).'
      }
    }
  },
  rbac_roles: ['admin', 'customer', 'guest'],
  is_active: true
};

export const SYSTEM_CANCEL_TOOL = {
  tool_slug: 'cancel_pending_action',
  display_name: '❌ Cancel Pending Action',
  category: 'system_action',
  description: 'Cancels and discards a pending action that was staged and is awaiting user approval.',
  parameters_schema: {
    type: 'OBJECT',
    required: ['action_code'],
    properties: {
      action_code: {
        type: 'STRING',
        description: 'The unique action code (e.g., ACT-3F2A81) of the pending action to cancel.'
      }
    }
  },
  rbac_roles: ['admin', 'customer', 'guest'],
  is_active: true
};

/**
 * Dynamic Skill & Tool Composer:
 * - Direct Fast-Path for <= 15 tools (0ms).
 * - Semantic Ranking for > 15 tools.
 * - Dynamic Pending Action State Injection.
 */
export function composeSkillsAndTools(intents = [], allTools = [], isPersonalMode = true, context = {}) {
  const skillDirectives = [];
  const activeTools = Array.isArray(allTools)
    ? allTools.filter(t => t && t.is_active !== 0 && t.is_active !== false)
    : [];

  let candidateTools = activeTools;

  // Semantic Ranking Fast-Path threshold
  const MAX_DIRECT_TOOLS = 15;
  if (activeTools.length > MAX_DIRECT_TOOLS && context.userMessage) {
    const tokens = tokenizeText(context.userMessage);
    const scored = activeTools.map(tool => ({
      tool,
      score: scoreToolRelevance(tokens, tool)
    }));

    // Sort descending by relevance score
    scored.sort((a, b) => b.score - a.score);

    // Pick top 6 or any tool with score > 0.5
    candidateTools = scored
      .filter((s, idx) => idx < 6 || s.score > 0.5)
      .map(s => s.tool);
  }

  // Universal Two-Phase State Injection: If there is an active pending action awaiting approval
  if (context?.pendingAction) {
    const pa = context.pendingAction;
    candidateTools = [SYSTEM_CONFIRM_TOOL, SYSTEM_CANCEL_TOOL, ...candidateTools];
    skillDirectives.push(
      `PENDING ACTION APPROVAL (${pa.action_code}):\n` +
      `• A previous action is awaiting approval: "${pa.preview_summary}".\n` +
      `• If approved (e.g. user says "yes", "confirm", "proceed", "send", "bhej do"), call confirm_pending_action with action_code="${pa.action_code}".\n` +
      `• If cancelled (e.g. user says "no", "cancel", "stop", "mat bhejo"), call cancel_pending_action with action_code="${pa.action_code}".`
    );
  }

  
  // Action Audit Inspection Tool: Always expose so model can recall recently executed actions
  if (!candidateTools.some(t => t.tool_slug === 'inspect_recent_actions')) {
    candidateTools.push(SYSTEM_INSPECT_TOOL);
  }

  return {
    activeIntents: intents || ['general_assistant'],
    filteredTools: candidateTools,
    skillDirectives
  };
}
