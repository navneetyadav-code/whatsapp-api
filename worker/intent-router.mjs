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
export function composeSkillsAndTools(intents, allTools = [], isPersonalMode = true, context = {}) {
  const activeToolSlugs = new Set();
  const skillDirectives = [];

  // In Personal Assistant mode, all executive tools are available to Boss at all times
  if (isPersonalMode) {
    activeToolSlugs.add('get_current_time');
    activeToolSlugs.add('update_memory');
    activeToolSlugs.add('draft_email');
    activeToolSlugs.add('confirm_action');
    activeToolSlugs.add('assistgroup_crm');
    activeToolSlugs.add('web_search');
    activeToolSlugs.add('evaluate_math');
  }

  // State-Aware Draft Injection: If there is an active pending draft in this conversation,
  // ALWAYS keep confirm_action and draft_email in scope so the LLM can seamlessly confirm or revise!
  if (context?.pendingDraft) {
    activeToolSlugs.add('confirm_action');
    activeToolSlugs.add('draft_email');
    if (!intents.includes('email_confirm') && !intents.includes('email_cancel') && !intents.includes('email_draft')) {
      skillDirectives.push(
        `PENDING ACTION APPROVAL (${context.pendingDraft.draft_code}):\n` +
        `• There is a pending email draft awaiting approval (Recipient: ${context.pendingDraft.recipient}, Subject: "${context.pendingDraft.subject}").\n` +
        `• Boss can confirm (call confirm_action), modify (call draft_email), or cancel (call confirm_action with action="cancel").\n` +
        `• If Boss indicates to send, retry sending, or approves, immediately call confirm_action with draft_code="${context.pendingDraft.draft_code}".`
      );
    }
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
          `EMAIL DRAFTING PROTOCOL (TWO-PHASE SAFETY):\n• Call draft_email with to, subject, body. Prompt: "Reply *'Send'* or *'Yes'* to dispatch, or tell me what to edit."\n• NEVER claim email is sent until confirm_action succeeds.\n• RECIPIENT RULES:\n  - If Boss says "to me" or asks for a report, recipient MUST be: info.assistgroupofficial@gmail.com.\n  - NEVER invent synthetic domain emails (e.g. <name>@assistgroup.com).\n  - Boss is Ankit (info.assistgroupofficial@gmail.com); Ankush is a team collaborator (ankushkumary113@gmail.com).`
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

      case 'memory':
        activeToolSlugs.add('update_memory');
        skillDirectives.push(
          `PERSISTENT MEMORY PROTOCOL:\n• Boss requested to remember or note down a fact, preference, or contact details.\n• Autonomously call update_memory with key and value to store it permanently.`
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

  // Filter tools: active built-in utilities + ALL user-defined custom tools (rest_api, shared_env_api, webhook)
  // Ensures that whenever a user adds or activates any custom tool, the bot gets immediate real-time access!
  const filteredTools = allTools.filter(t => {
    if (activeToolSlugs.has(t.tool_slug)) return true;
    if (t.category === 'rest_api' || t.category === 'shared_env_api' || t.category === 'webhook') return true;
    const builtinSlugs = ['get_current_time', 'update_memory', 'draft_email', 'confirm_action', 'assistgroup_crm', 'web_search', 'evaluate_math', 'escalate_to_human', 'get_weather'];
    return !builtinSlugs.includes(t.tool_slug);
  });

  return {
    activeIntents: intents,
    filteredTools,
    skillDirectives
  };
}
