/**
 * Autonomous Tools & Function Calling Engine with RBAC
 * ----------------------------------------------------
 * Provides native tool declarations, role-based tool gating, built-from-scratch web search,
 * AssistGroup API connector (localhost & live shared hosting), math engine, IST clock,
 * and human escalation handling.
 */

import crypto from 'crypto';

// ---------------------------------------------------------------------------
// 1. BUILT-FROM-SCRATCH WEB SEARCH (DuckDuckGo Lite HTML Scraper)
// ---------------------------------------------------------------------------
export async function searchWebDuckDuckGo(query, maxResults = 3, timeoutMs = 4000) {
  if (!query || typeof query !== 'string' || !query.trim()) {
    return { query, count: 0, results: [], message: 'Empty search query provided.' };
  }

  const cleanQuery = query.trim().slice(0, 200);
  const targetUrl = `https://html.duckduckgo.com/html/?q=${encodeURIComponent(cleanQuery)}`;

  try {
    const res = await fetch(targetUrl, {
      method: 'GET',
      headers: {
        'User-Agent': 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_4_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4.1 Mobile/15E148 Safari/604.1',
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language': 'en-US,en;q=0.9',
        'Cookie': 'kl=in-en; l=in-en',
        'Referer': 'https://html.duckduckgo.com/'
      },
      signal: AbortSignal.timeout(timeoutMs)
    });

    let results = [];

    if (res.ok) {
      const html = await res.text();
      const linkMatches = [...html.matchAll(/<h2[^>]*class=["']result__title["'][^>]*>[\s\S]*?<a[^>]*href=["']([^"']+)["'][^>]*>([\s\S]*?)<\/a>/gi)];
      const snippetMatches = [...html.matchAll(/class=["']result__snippet["'][^>]*>([\s\S]*?)<\/a>/gi)];

      const limit = Math.min(Number(maxResults) || 3, linkMatches.length);

      for (let i = 0; i < limit; i++) {
        let rawUrl = linkMatches[i][1];
        let cleanUrl = rawUrl;
        try {
          if (rawUrl.includes('uddg=')) {
            const parsed = new URL('https://duckduckgo.com' + rawUrl);
            cleanUrl = parsed.searchParams.get('uddg') || rawUrl;
          }
        } catch (e) {}

        const title = decodeHtmlEntities(linkMatches[i][2].replace(/<[^>]+>/g, '').trim());
        const snippet = snippetMatches[i] ? decodeHtmlEntities(snippetMatches[i][1].replace(/<[^>]+>/g, '').trim()) : '';
        const safeSnippet = sanitizeText(snippet.slice(0, 300));

        results.push({
          title,
          url: cleanUrl,
          snippet: safeSnippet
        });
      }
    }

    // Fallback: If HTML parser returned 0 results (e.g. rate limit), use Instant Answer API
    if (results.length === 0) {
      try {
        const apiUrl = `https://api.duckduckgo.com/?q=${encodeURIComponent(cleanQuery)}&format=json&no_html=1&skip_disambig=1`;
        const apiRes = await fetch(apiUrl, { signal: AbortSignal.timeout(3000) });
        if (apiRes.ok) {
          const apiData = await apiRes.json();
          if (apiData.AbstractText) {
            results.push({
              title: apiData.Heading || cleanQuery,
              url: apiData.AbstractURL || 'https://duckduckgo.com',
              snippet: sanitizeText(apiData.AbstractText.slice(0, 300))
            });
          }
          if (Array.isArray(apiData.RelatedTopics)) {
            for (const topic of apiData.RelatedTopics.slice(0, 2)) {
              if (topic.Text && topic.FirstURL) {
                results.push({
                  title: topic.Text.split(' - ')[0] || cleanQuery,
                  url: topic.FirstURL,
                  snippet: sanitizeText(topic.Text.slice(0, 250))
                });
              }
            }
          }
        }
      } catch (e) {}
    }

    return {
      query: cleanQuery,
      engine: 'DuckDuckGo Resilient Search',
      count: results.length,
      results
    };
  } catch (err) {
    return {
      query: cleanQuery,
      count: 0,
      results: [],
      error: `Search timed out or failed: ${err.message}`
    };
  }
}

function decodeHtmlEntities(str) {
  if (!str) return '';
  return str
    .replace(/&amp;/g, '&')
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>')
    .replace(/&quot;/g, '"')
    .replace(/&#39;/g, "'")
    .replace(/&nbsp;/g, ' ');
}

function sanitizeText(str) {
  if (!str) return '';
  return str
    .replace(/(ignore previous instructions|disregard earlier instructions|system prompt|developer mode)/gi, '[redacted]')
    .trim();
}

// ---------------------------------------------------------------------------
// 2. ASSISTGROUP ADMIN API CONNECTOR (Localhost & Live Shared Hosting)
// ---------------------------------------------------------------------------
export async function callAssistGroupApi(endpoint, method = 'GET', body = null, config = {}) {
  const agConfig = config.tools?.assistgroup_api || {};
  const activeEnv = agConfig.active_env || 'local';
  const envConfig = agConfig.environments?.[activeEnv] || {};

  const baseUrl = envConfig.base_url || (activeEnv === 'live' ? 'https://assistgroup.gt.tc/api/admin/v1' : 'http://localhost/assistgroup/api/admin/v1');
  const token = envConfig.bearer_token || '';

  if (!token) {
    return { error: 'AssistGroup API Bearer token is not configured.' };
  }

  const cleanEndpoint = endpoint.startsWith('/') ? endpoint : `/${endpoint}`;
  const fullUrl = `${baseUrl.replace(/\/+$/, '')}${cleanEndpoint}`;

  const headers = {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json',
    'User-Agent': 'WhatsGateway-AI-Agent/2.0'
  };

  const reqOptions = {
    method,
    headers,
    signal: AbortSignal.timeout(agConfig.timeout_ms || 6000)
  };

  if (body && ['POST', 'PUT', 'PATCH'].includes(method.toUpperCase())) {
    headers['Content-Type'] = 'application/json';
    reqOptions.body = JSON.stringify(body);
  }

  try {
    const res = await fetch(fullUrl, reqOptions);
    const data = await res.json().catch(() => null);

    if (!res.ok || !data) {
      return {
        status: res.status,
        success: false,
        error: data?.error || `API returned status ${res.status}`
      };
    }

    return {
      status: res.status,
      success: true,
      environment: activeEnv,
      data: data.data || data
    };
  } catch (err) {
    return {
      success: false,
      error: `AssistGroup API request failed: ${err.message}`
    };
  }
}

// ---------------------------------------------------------------------------
// 3. SAFE ARITHMETIC / MATH ENGINE (Zero Eval, AST Parsing)
// ---------------------------------------------------------------------------
export function evaluateSafeMath(expression) {
  if (!expression || typeof expression !== 'string') {
    return { error: 'Missing mathematical expression.' };
  }

  // Pre-process common percentage patterns: "1000 + 18%" or "25000 * 15%"
  let clean = expression
    .replace(/,/g, '')
    .replace(/₹|rs\.?/gi, '')
    .replace(/\s+/g, '')
    .toLowerCase();

  // Validate allowed characters: digits, ., +, -, *, /, (, ), %, ^
  if (!/^[0-9.+\-*/()^%]+$/.test(clean)) {
    return { error: 'Invalid characters in mathematical expression. Only standard arithmetic is permitted.' };
  }

  try {
    // Replace percentage notation: e.g. "15%" -> "0.15"
    clean = clean.replace(/(\d+(\.\d+)?)%/g, '($1/100)');

    // Simple recursive descent parser for +, -, *, /, ^, parentheses
    let index = 0;

    function peek() {
      return clean[index];
    }

    function get() {
      return clean[index++];
    }

    function parseExpression() {
      let val = parseTerm();
      while (peek() === '+' || peek() === '-') {
        const op = get();
        const next = parseTerm();
        val = op === '+' ? val + next : val - next;
      }
      return val;
    }

    function parseTerm() {
      let val = parseFactor();
      while (peek() === '*' || peek() === '/') {
        const op = get();
        const next = parseFactor();
        if (op === '/' && next === 0) throw new Error('Division by zero');
        val = op === '*' ? val * next : val / next;
      }
      return val;
    }

    function parseFactor() {
      let val = parsePrimary();
      while (peek() === '^') {
        get();
        const exp = parsePrimary();
        val = Math.pow(val, exp);
      }
      return val;
    }

    function parsePrimary() {
      if (peek() === '(') {
        get(); // consume '('
        const val = parseExpression();
        if (get() !== ')') throw new Error("Mismatched parentheses");
        return val;
      }
      if (peek() === '-') {
        get();
        return -parsePrimary();
      }
      if (peek() === '+') {
        get();
        return parsePrimary();
      }

      let numStr = '';
      while (peek() !== undefined && /[0-9.]/.test(peek())) {
        numStr += get();
      }

      if (numStr === '') throw new Error('Expected number');
      const num = Number(numStr);
      if (Number.isNaN(num)) throw new Error('Invalid number');
      return num;
    }

    const result = parseExpression();
    if (index < clean.length) throw new Error('Unexpected token');

    const rounded = Math.round(result * 100) / 100;
    const formatted = rounded.toLocaleString('en-IN', { maximumFractionDigits: 2 });

    return {
      expression,
      result: rounded,
      formatted_inr: `₹${formatted}`,
      success: true
    };
  } catch (err) {
    return { expression, error: `Math evaluation error: ${err.message}`, success: false };
  }
}

// ---------------------------------------------------------------------------
// 4. REAL-TIME CLOCK & BUSINESS HOURS (IST)
// ---------------------------------------------------------------------------
export function getCurrentIstTime(timeConfig = {}) {
  const now = new Date();
  
  // Format to IST (UTC+5:30)
  const istString = now.toLocaleString('en-US', { timeZone: 'Asia/Kolkata' });
  const istDate = new Date(istString);

  const dayOfWeek = istDate.getDay(); // 0 = Sun, 1 = Mon ... 6 = Sat
  const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
  const dayName = dayNames[dayOfWeek];

  const hours = istDate.getHours();
  const minutes = istDate.getMinutes();
  const currentTimeMinutes = hours * 60 + minutes;

  // Default office hours: Mon-Sat, 9:30 AM (570m) to 6:30 PM (1110m)
  const openMinutes = 9 * 60 + 30; // 09:30
  const closeMinutes = 18 * 60 + 30; // 18:30
  const workDays = [1, 2, 3, 4, 5, 6]; // Mon through Sat

  const isWorkDay = workDays.includes(dayOfWeek);
  const isOpen = isWorkDay && currentTimeMinutes >= openMinutes && currentTimeMinutes < closeMinutes;

  const formattedDate = istDate.toLocaleDateString('en-IN', {
    timeZone: 'Asia/Kolkata',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });

  const formattedTime = istDate.toLocaleTimeString('en-IN', {
    timeZone: 'Asia/Kolkata',
    hour: '2-digit',
    minute: '2-digit',
    hour12: true
  });

  return {
    timezone: 'Asia/Kolkata (IST)',
    current_time: formattedTime,
    current_date: formattedDate,
    day_of_week: dayName,
    is_office_open: isOpen,
    office_schedule: 'Monday to Saturday, 9:30 AM to 6:30 PM IST',
    office_status: isOpen ? 'Currently OPEN for business & live support' : 'Currently CLOSED. Inquiries received now will be prioritized next working morning.'
  };
}

// ---------------------------------------------------------------------------
// 5. HUMAN ESCALATION ENGINE
// ---------------------------------------------------------------------------
export async function triggerHumanEscalation(reason, context) {
  const { db, userId, conversationId, callerPhone } = context;

  try {
    if (conversationId && db) {
      await db.execute(
        `UPDATE chat_conversations
         SET status = 'escalated', escalated_at = NOW(), escalation_reason = ?
         WHERE id = ?`,
        [reason?.slice(0, 180) || 'AI Agent Tool Triggered Escalation', conversationId]
      );

      await db.execute(
        `INSERT INTO chat_escalations (user_id, conversation_id, reason_code, trigger_text, status)
         VALUES (?, ?, 'ai_tool_escalate', ?, 'active')`,
        [userId, conversationId, reason?.slice(0, 500) || 'Customer requested human intervention.']
      );
    }

    return {
      escalated: true,
      reason,
      phone: callerPhone,
      message: 'Human support alert triggered. An expert will join this WhatsApp chat shortly.'
    };
  } catch (err) {
    return {
      escalated: false,
      error: `Failed to record escalation: ${err.message}`
    };
  }
}

// ---------------------------------------------------------------------------
// 6. TOOLS REGISTRY & GEMINI FUNCTION SCHEMAS
// ---------------------------------------------------------------------------
export class ToolsRegistry {
  /**
   * Complete definitions matching Google Gemini FunctionDeclaration specification
   */
  static DEFINITIONS = {
    web_search: {
      name: 'web_search',
      description: 'Search the live internet for up-to-date facts, current news, technology comparisons, and information not contained in the local knowledge base.',
      parameters: {
        type: 'OBJECT',
        properties: {
          query: {
            type: 'STRING',
            description: 'The search query to look up on the web'
          }
        },
        required: ['query']
      }
    },

    assistgroup_get_services_pricing: {
      name: 'assistgroup_get_services_pricing',
      description: 'Fetch official AssistGroup services, active pricing packages, and features for web development, software engineering, cloud services, and mobile applications.',
      parameters: {
        type: 'OBJECT',
        properties: {},
        required: []
      }
    },

    assistgroup_create_inquiry: {
      name: 'assistgroup_create_inquiry',
      description: 'Submit a new prospective customer inquiry, project lead, or quote request directly into the AssistGroup CRM system.',
      parameters: {
        type: 'OBJECT',
        properties: {
          name: { type: 'STRING', description: "Prospective customer's full name" },
          phone: { type: 'STRING', description: "Prospective customer's contact phone number" },
          email: { type: 'STRING', description: "Customer's email address (optional)" },
          service: { type: 'STRING', description: 'The requested service type, e.g. "Web Development", "App Development", "SEO", "Custom CRM"' },
          message: { type: 'STRING', description: 'Brief summary of their requirements or project goals' }
        },
        required: ['name', 'phone', 'message']
      }
    },

    assistgroup_lookup_inquiries: {
      name: 'assistgroup_lookup_inquiries',
      description: 'Look up past inquiries, support tickets, and quotes submitted by a customer using their registered contact phone number.',
      parameters: {
        type: 'OBJECT',
        properties: {
          phone: { type: 'STRING', description: "The customer's phone number to look up" }
        },
        required: ['phone']
      }
    },

    assistgroup_get_team: {
      name: 'assistgroup_get_team',
      description: 'Get information about AssistGroup team members, leadership, and technical specializations.',
      parameters: {
        type: 'OBJECT',
        properties: {},
        required: []
      }
    },

    assistgroup_admin_dashboard_stats: {
      name: 'assistgroup_admin_dashboard_stats',
      description: '[ADMIN ONLY] Fetch executive real-time metrics: registered users, file storage, inquiries count, and system status.',
      parameters: {
        type: 'OBJECT',
        properties: {},
        required: []
      }
    },

    evaluate_math: {
      name: 'evaluate_math',
      description: 'Evaluate arithmetic calculations, pricing plans, percentage discounts, GST (18%), or project cost totals with 100% precision without hallucinations.',
      parameters: {
        type: 'OBJECT',
        properties: {
          expression: {
            type: 'STRING',
            description: 'The math expression to evaluate, e.g. "25000 * 0.85" or "15000 + (15000 * 0.18)"'
          }
        },
        required: ['expression']
      }
    },

    get_current_time: {
      name: 'get_current_time',
      description: 'Get the exact current date, time in Indian Standard Time (IST), day of the week, and check whether AssistGroup offices are open.',
      parameters: {
        type: 'OBJECT',
        properties: {},
        required: []
      }
    },

    escalate_to_human: {
      name: 'escalate_to_human',
      description: 'Escalate the chat to a human agent, engineer, or supervisor when requested or when an inquiry requires custom human assessment.',
      parameters: {
        type: 'OBJECT',
        properties: {
          reason: {
            type: 'STRING',
            description: 'The specific reason why human intervention is required'
          }
        },
        required: ['reason']
      }
    }
  };

  /**
   * Filter tools based on caller's RBAC role and configuration
   */
  static getDeclarationsForRole(role = 'guest', toolsConfig = {}) {
    if (!toolsConfig.enabled) {
      return [];
    }

    const allowed = [];
    const cfg = toolsConfig.tools || {};

    for (const [toolKey, toolDef] of Object.entries(this.DEFINITIONS)) {
      let isPermitted = false;

      if (toolKey.startsWith('assistgroup_')) {
        const agCfg = cfg.assistgroup_api || {};
        if (agCfg.enabled) {
          const actionKey = toolKey.replace('assistgroup_', '');
          const actionCfg = agCfg.actions?.[actionKey];
          if (actionCfg && actionCfg.enabled && actionCfg.roles?.includes(role)) {
            isPermitted = true;
          }
        }
      } else {
        const tCfg = cfg[toolKey];
        if (tCfg && tCfg.enabled && tCfg.roles?.includes(role)) {
          isPermitted = true;
        }
      }

      if (isPermitted) {
        allowed.push(toolDef);
      }
    }

    if (allowed.length === 0) return [];

    return [
      {
        function_declarations: allowed
      }
    ];
  }

  /**
   * Execute tool with security bounds, RBAC validation, and audit logging
   */
  static async executeTool(toolName, args = {}, context = {}) {
    const startTime = Date.now();
    const { db, userId, conversationId, callerPhone, callerRole, toolsConfig } = context;

    // RBAC Execution Guard (Defense-in-depth)
    const declarations = this.getDeclarationsForRole(callerRole, toolsConfig);
    const permittedFunctions = declarations[0]?.function_declarations?.map(d => d.name) || [];

    if (!permittedFunctions.includes(toolName)) {
      const errRes = {
        error: `Permission Denied: Caller role '${callerRole}' is not authorized to execute tool '${toolName}'.`
      };
      await this.logInvocation(context, toolName, args, errRes, Date.now() - startTime, 'denied', errRes.error);
      return errRes;
    }

    let result = null;
    let status = 'success';
    let errorMessage = null;

    try {
      switch (toolName) {
        case 'web_search': {
          const maxResults = toolsConfig.tools?.web_search?.max_results || 3;
          const timeout = toolsConfig.tools?.web_search?.timeout_ms || 4000;
          result = await searchWebDuckDuckGo(args.query, maxResults, timeout);
          break;
        }

        case 'assistgroup_get_services_pricing': {
          const res = await callAssistGroupApi('/pricing/plans', 'GET', null, toolsConfig);
          if (res.success && Array.isArray(res.data)) {
            result = {
              services_and_packages: res.data.map(p => ({
                id: p.id,
                plan_name: p.plan_name || p.name || 'Custom Package',
                price: p.price ? `₹${p.price}` : 'Custom Quote',
                billing_cycle: p.billing_cycle || 'one-time',
                features: p.features || []
              }))
            };
          } else {
            result = res;
          }
          break;
        }

        case 'assistgroup_create_inquiry': {
          const payload = {
            name: args.name,
            phone: args.phone || callerPhone,
            email: args.email || '',
            service_type: args.service || 'General Inquiry',
            message: args.message
          };
          result = await callAssistGroupApi('/inquiries', 'POST', payload, toolsConfig);
          break;
        }

        case 'assistgroup_lookup_inquiries': {
          const phoneToLookup = args.phone || callerPhone;
          result = await callAssistGroupApi(`/inquiries?search=${encodeURIComponent(phoneToLookup)}&limit=5`, 'GET', null, toolsConfig);
          break;
        }

        case 'assistgroup_get_team': {
          result = await callAssistGroupApi('/team', 'GET', null, toolsConfig);
          break;
        }

        case 'assistgroup_admin_dashboard_stats': {
          result = await callAssistGroupApi('/dashboard/metrics', 'GET', null, toolsConfig);
          break;
        }

        case 'evaluate_math': {
          result = evaluateSafeMath(args.expression);
          break;
        }

        case 'get_current_time': {
          result = getCurrentIstTime(toolsConfig.tools?.get_current_time);
          break;
        }

        case 'escalate_to_human': {
          result = await triggerHumanEscalation(args.reason, context);
          break;
        }

        default:
          status = 'error';
          errorMessage = `Unknown tool: ${toolName}`;
          result = { error: errorMessage };
      }
    } catch (err) {
      status = 'error';
      errorMessage = err.message;
      result = { error: `Tool execution failed: ${err.message}` };
    }

    const duration = Date.now() - startTime;
    await this.logInvocation(context, toolName, args, result, duration, status, errorMessage);

    return result;
  }

  /**
   * Log tool invocation to audit table
   */
  static async logInvocation(context, toolName, args, result, duration, status, errorMessage) {
    const { db, userId, conversationId, callerPhone, callerRole } = context;
    if (!db || !userId) return;

    try {
      await db.execute(
        `INSERT INTO ai_tool_invocations
          (user_id, conversation_id, caller_phone, caller_role, tool_name, tool_arguments, tool_result, execution_time_ms, status, error_message)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
        [
          userId,
          conversationId || null,
          callerPhone || 'unknown',
          callerRole || 'guest',
          toolName,
          JSON.stringify(args || {}),
          JSON.stringify(result || {}),
          duration,
          status,
          errorMessage || null
        ]
      );
    } catch (err) {
      console.error('Failed to log tool invocation audit:', err.message);
    }
  }
}
