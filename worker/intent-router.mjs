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
  email_confirm: /^(yes|send( it)?|proceed|approve(d)?|bhej do|bhejdo|ha(an)? bhej do|confirm(ed)?|ok send|yep|sure send|do it|okay send|ok|okay)\b/i,
  email_cancel: /\b(cancel|discard|don't send|dont send|abort|stop|mat bhejo|ignore draft|no don't send)\b/i,
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

  // If Boss replies with a confirmation word while a draft is pending, immediately route to email_confirm!
  if (hasPendingDraft) {
    if (INTENT_PATTERNS.email_confirm.test(clean)) {
      return ['email_confirm'];
    }
    if (INTENT_PATTERNS.email_cancel.test(clean)) {
      return ['email_cancel'];
    }
    // If Boss says "change subject to X" or "add Y", route to email_draft modification
    if (/\b(change|add|update|revise|edit|modify|instead of)\b/i.test(clean)) {
      return ['email_draft'];
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
export function composeSkillsAndTools(intents, allTools = [], isPersonalMode = true) {
  const activeToolSlugs = new Set();
  const skillDirectives = [];

  // Always make basic utility tools available in Personal Assistant mode if needed
  if (isPersonalMode) {
    activeToolSlugs.add('get_current_time');
  }

  for (const intent of intents) {
    switch (intent) {
      case 'weather':
        activeToolSlugs.add('get_weather');
        break;

      case 'math':
        activeToolSlugs.add('evaluate_math');
        break;

      case 'time':
        activeToolSlugs.add('get_current_time');
        break;

      case 'web_search':
        activeToolSlugs.add('web_search');
        break;

      case 'crm_read':
      case 'crm_write':
        activeToolSlugs.add('assistgroup_crm');
        skillDirectives.push(
          `CRM PROTOCOL:\n• Autonomously call assistgroup_crm for platform metrics, user lookups, or inquiry records.\n• Summarize numbers clearly with bold labels.`
        );
        break;

      case 'email_draft':
        activeToolSlugs.add('draft_email');
        skillDirectives.push(
          `EMAIL DRAFTING PROTOCOL (TWO-PHASE SAFETY):\n• To email anyone, you MUST call draft_email.\n• Present the recipient, subject, and body clearly to Boss.\n• ALWAYS prompt Boss: "Reply *'Send'* or *'Yes'* to dispatch, or tell me what to edit."\n• NEVER claim the email is sent until confirmation succeeds.`
        );
        break;

      case 'email_confirm':
        activeToolSlugs.add('confirm_action');
        skillDirectives.push(
          `EMAIL DISPATCH PROTOCOL:\n• Boss has confirmed the pending email draft.\n• Immediately call confirm_action with the draft_code to dispatch the email via SMTP.\n• Report the delivery confirmation upon success.`
        );
        break;

      case 'email_cancel':
        activeToolSlugs.add('confirm_action');
        skillDirectives.push(
          `ACTION CANCELLATION PROTOCOL:\n• Boss requested to cancel/discard the pending draft.\n• Call confirm_action with action="cancel" to discard it safely.`
        );
        break;

      case 'escalation':
        activeToolSlugs.add('escalate_to_human');
        break;

      case 'general_assistant':
      default:
        activeToolSlugs.add('web_search');
        activeToolSlugs.add('evaluate_math');
        activeToolSlugs.add('get_current_time');
        break;
    }
  }

  // Filter tools strictly to the active subset
  const filteredTools = allTools.filter(t => activeToolSlugs.has(t.tool_slug));

  return {
    activeIntents: intents,
    filteredTools,
    skillDirectives
  };
}
