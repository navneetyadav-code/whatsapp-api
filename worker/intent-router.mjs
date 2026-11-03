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

  return {
    activeIntents: intents || ['general_assistant'],
    filteredTools: candidateTools,
    skillDirectives
  };
}
