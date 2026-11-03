/**
 * Tiered Hybrid Intent Router & Skill Composer
 * -------------------------------------------------------------
 * Combines 0ms Lexical/Regex Fast-Path with Multi-Label Capability Composition
 * and State-Aware Confirmation Detection for Two-Phase Execution.
 */

// 1. Core Lexical Pattern Matchers
const INTENT_PATTERNS = {
  weather: /\b(weather|temperature|forecast|barometer|climate|sunny|raining|rainfall)\b/i,
  math: /\b(math|calculate|calculation|\d+[\+\-\*\/]\d+|percentage|square root|sum of)\b/i,
  time: /\b(time|clock|ist|office hours|working hours|time now|current time)\b/i,
  web_search: /\b(search|google|lookup|who is|what is the latest|news|find online|browse)\b/i,
  crm_read: /\b(crm|metrics|inquir(y|ies)|lead(s)?|user(s)?|pricing plan(s)?|god mode|storage files|cloudinary|audit log(s)?)\b/i,
  crm_write: /\b(update inquiry|change status|add inquiry|create inquiry|new lead)\b/i,
  email_draft: /\b(draft|mail|email|compose|write (an? )?(email|mail)|send (an? )?(email|mail))\b/i,
  email_confirm: /^(yes+|yeah|yep|ya|send( it)?|proceed|approve(d)?|bhej do|bhejdo|ha(an)? bhej do|confirm(ed)?|ok send|sure send|do it|okay send|ok|okay)\b/i,
  email_cancel: /\b(cancel|discard|don't send|dont send|abort|stop|mat bhejo|ignore draft|no don't send)\b/i,
  memory: /\b(remember|note down|save this|keep in mind|yad rakhna|yaad rakhna|note karo)\b/i,
  escalation: /\b(human|manager|agent|support representative|escalate|talk to human)\b/i
};

const COMPOUND_CONJUNCTIONS = /\b(and|then|also|as well as|based on|what (i|we) emailed|that inquiry|along with)\b/i;

/**
 * Detects all active intents for an incoming message with State-Aware context
 */
export async function detectIntents(text, context = {}) {
  const clean = (text || '').trim();
  if (!clean) return ['general_assistant'];

  // Check if there is an active pending draft waiting for approval in this conversation
  const hasPendingDraft = Boolean(context.pendingDraft);

  // If Boss replies while a draft is pending, evaluate state-aware confirmation or revision
  if (hasPendingDraft) {
    if (INTENT_PATTERNS.email_cancel.test(clean)) {
      return ['email_cancel'];
    }
    if (/\b(change|add|update|revise|edit|modify|instead of)\b/i.test(clean)) {
      return ['email_draft'];
    }
    if (
      INTENT_PATTERNS.email_confirm.test(clean) ||
      /\b(send|dispatch|bhej|approve|confirm|proceed|go ahead|try again|resend|retry|send again|try to send|kardo|kar do)\b/i.test(clean)
    ) {
      return ['email_confirm'];
    }
  }

  // Tier 1: Fast-Path Evaluation
  const matches = [];
  for (const [intent, regex] of Object.entries(INTENT_PATTERNS)) {
    if (regex.test(clean)) {
      matches.push(intent);
    }
  }

  const isCompound = COMPOUND_CONJUNCTIONS.test(clean);

  // If simple, unambiguous single match without compound conjunctions -> Return immediately (0ms)
  if (!isCompound && matches.length === 1) {
    return matches;
  }

  // If multiple intents matched (e.g. crm_read + email_draft) -> Multi-Intent Composition
  if (matches.length > 0) {
    // Deduplicate and filter out conflict states
    const unique = Array.from(new Set(matches));
    return unique;
  }

  // Default fallback to general conversational assistant
  return ['general_assistant'];
}

/**
 * Dynamic Skill & Tool Composer: Filters tools & injects micro-plugins on-demand
 */
/**
 * Dynamic Skill & Tool Composer: Passes user-configured active tools dynamically.
 * Zero hardcoded tools, zero phantom tools, and zero hardcoded fallbacks.
 */
export function composeSkillsAndTools(intents = [], allTools = [], isPersonalMode = true, context = {}) {
  // Purely dynamic: load all active tools configured by the user from database
  const filteredTools = Array.isArray(allTools)
    ? allTools.filter(t => t && t.is_active !== 0 && t.is_active !== false)
    : [];

  return {
    activeIntents: intents || ['general_assistant'],
    filteredTools,
    skillDirectives: []
  };
}
