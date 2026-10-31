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
  crm_read: /\b(crm|metrics|stats|kpi|inquir(y|ies)|lead(s)?|user(s)?|account(s)?|customer(s)?|pricing plan(s)?|god mode|storage|file(s)?|cloudinary|audit log(s)?|team|member(s)?|settings|system info)\b/i,
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
 * Evaluates whether a custom user tool matches the semantic context or keyword intent of the message
 */
function isCustomToolRelevant(tool, text) {
  if (!text || typeof text !== 'string') return false;
  const lower = text.toLowerCase().trim();

  // 1. Direct Slug or Display Name matching (e.g. "delhivery", "order", "weather", "maintenance")
  const slugTerms = (tool.tool_slug || '').toLowerCase().split(/[_\-\s]+/);
  for (const term of slugTerms) {
    if (term.length >= 3) {
      if (lower.includes(term)) return true;
      const stem = term.length > 5 ? term.slice(0, 5) : term;
      if (lower.includes(stem)) return true;
    }
  }
  const nameTerms = (tool.display_name || '').toLowerCase().split(/[_\-\s]+/);
  for (const term of nameTerms) {
    if (term.length >= 3) {
      if (lower.includes(term)) return true;
      const stem = term.length > 5 ? term.slice(0, 5) : term;
      if (lower.includes(stem)) return true;
    }
  }

  // 2. Action verbs for toggle / state tools (e.g. "turn on", "turn off", "disable", "enable", "off that")
  if (/\b(turn\s+on|turn\s+off|off\b|on\b|enable|disable|activate|deactivate|status|toggle|properly)\b/i.test(lower)) {
    const desc = (tool.description || '').toLowerCase();
    const slug = (tool.tool_slug || '').toLowerCase();
    if (desc.includes('enable') || desc.includes('disable') || desc.includes('maintenance') || desc.includes('toggle') || slug.includes('maintenance')) {
      return true;
    }
  }

  // 3. High-entropy description keyword matching
  const desc = (tool.description || '').toLowerCase();
  const descWords = desc.replace(/[^a-z0-9\s]/g, ' ').split(/\s+/).filter(w => w.length >= 4 && !/^(this|that|with|from|have|will|your|when|where|what|then|also)$/.test(w));
  let matchHits = 0;
  for (const w of descWords) {
    const stem = w.length > 5 ? w.slice(0, 5) : w;
    if (lower.includes(stem)) {
      matchHits++;
      if (matchHits >= 1) return true;
    }
  }

  // 4. Dynamic parameter name extraction (e.g. { order_id }, { waybill }, { email }, { city })
  const props = tool.parameters_schema?.properties || {};
  for (const pName of Object.keys(props)) {
    const pClean = pName.toLowerCase().replace(/[^a-z0-9]/g, '');
    if (pClean.length >= 4 && lower.includes(pClean)) return true;
  }

  return false;
}

/**
 * Dynamic Skill & Tool Composer: Filters tools & injects micro-plugins on-demand with Token-Efficient Pruning
 */
export function composeSkillsAndTools(intents, allTools = [], isPersonalMode = true, context = {}) {
  const activeToolSlugs = new Set();
  const skillDirectives = [];
  const messageText = (context.messageText || '').toLowerCase();

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
        for (const t of allTools) {
          if (t.category === 'rest_api' || t.category === 'shared_env_api') {
            const s = (t.tool_slug || '').toLowerCase();
            if (s.includes('metric') || s.includes('user') || s.includes('inquir') || s.includes('lead') || s.includes('crm') || s.includes('system') || s.includes('file') || s.includes('team') || s.includes('plan') || s.includes('audit') || s.includes('cloud') || s.includes('health') || s.includes('growth') || s.includes('maintenance')) {
              activeToolSlugs.add(t.tool_slug);
            }
          }
        }
        skillDirectives.push(
          `ENTERPRISE API & CRM PROTOCOL:\n• Autonomously call the relevant external tool to look up requested information (metrics, users, leads, system status, records).\n• Provide clean, professional summaries with bold labels and numbers.`
        );
        break;

      case 'email_draft':
        activeToolSlugs.add('draft_email');
        skillDirectives.push(
          `EMAIL DRAFTING PROTOCOL (TWO-PHASE SAFETY):\n• Call draft_email with to, subject, body. Prompt: "Reply *'Send'* or *'Yes'* to dispatch, or tell me what to edit."\n• NEVER claim email is sent until confirm_action succeeds.`
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
        if (!isPersonalMode) {
          activeToolSlugs.add('escalate_to_human');
        }
        break;

      case 'general_assistant':
      default:
        // Lean Core Set: Only inject search/time if the message actually warrants outside knowledge
        if (/\b(who|what|where|when|why|how|search|latest|news|price|weather|time|date|calculate|math)\b/i.test(messageText)) {
          activeToolSlugs.add('web_search');
          activeToolSlugs.add('get_current_time');
        }
        break;
    }
  }

  // In Personal mode, if message explicitly hints at core capabilities, ensure they are enabled
  if (isPersonalMode) {
    if (/\b(remember|note down|save this|keep in mind)\b/i.test(messageText)) activeToolSlugs.add('update_memory');
    if (/\b(time|clock|today|date)\b/i.test(messageText)) activeToolSlugs.add('get_current_time');
    if (/\b(mail|email|draft)\b/i.test(messageText)) activeToolSlugs.add('draft_email');

    // Dynamically match user's installed tools based on keyword cues
    for (const t of allTools) {
      if (t.category === 'rest_api' || t.category === 'shared_env_api' || t.category === 'webhook') {
        if (isCustomToolRelevant(t, messageText)) {
          activeToolSlugs.add(t.tool_slug);
        }
      }
    }
  }

  // Dynamic Precision Tool Pruning:
  // Instead of dumping hundreds of tools, keep active custom tools (always keep when count <= 30)
  const filteredTools = allTools.filter(t => {
    // STRICT ISOLATION: In Personal Assistant mode, escalation tools are NEVER provided to the LLM
    if (isPersonalMode && t.tool_slug === 'escalate_to_human') return false;

    // 1. Built-in tool matched by intent router
    if (activeToolSlugs.has(t.tool_slug)) return true;

    // 2. Custom tools (rest_api, shared_env_api, webhook):
    // When catalog is concise (<= 30 tools), provide all active custom tools so natural follow-up commands work!
    if (t.category === 'rest_api' || t.category === 'shared_env_api' || t.category === 'webhook') {
      if (allTools.length <= 30) return true;
      return isCustomToolRelevant(t, messageText);
    }

    return false;
  });

  return {
    activeIntents: intents,
    filteredTools,
    skillDirectives
  };
}
