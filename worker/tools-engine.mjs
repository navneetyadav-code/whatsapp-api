/**
 * Universal Dynamic Tools & Multi-Tenant Function Calling Engine
 * ----------------------------------------------------------------
 * Completely decoupled, open-source integration framework.
 * Supports:
 *  - Account-scoped custom REST APIs (Normal API)
 *  - Dual-environment APIs (Localhost vs. Live Shared Hosting)
 *  - Outbound Webhooks
 *  - Native utility tools (DuckDuckGo search, Safe Math, Business Hours, Escalation)
 *  - Dynamic Gemini FunctionDeclaration synthesis & RBAC enforcement
 *  - Audit logging into ai_tool_invocations
 */

import crypto from 'crypto';

// ---------------------------------------------------------------------------
// 1. BUILT-IN CORE UTILITIES
// ---------------------------------------------------------------------------

/**
 * Resilient DuckDuckGo web search (mobile headers + Instant Answer API fallback)
 */
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

    // Fallback: DuckDuckGo Instant Answer JSON API
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

/**
 * Safe Recursive-Descent Math Parser (Zero eval)
 */
export function evaluateSafeMath(expression) {
  if (!expression || typeof expression !== 'string') {
    return { error: 'Missing mathematical expression.' };
  }

  let clean = expression
    .replace(/,/g, '')
    .replace(/₹|rs\.?/gi, '')
    .replace(/\s+/g, '')
    .toLowerCase();

  if (!/^[0-9.+\-*/()^%]+$/.test(clean)) {
    return { error: 'Invalid characters in mathematical expression. Only standard arithmetic is permitted.' };
  }

  try {
    clean = clean.replace(/(\d+(\.\d+)?)%/g, '($1/100)');
    let index = 0;

    function peek() { return clean[index]; }
    function get() { return clean[index++]; }

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
        get();
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

/**
 * Dynamic Configurable Time Clock & Operating Hours
 */
export function getCurrentCustomTime(config = {}, context = {}) {
  const isPersonalMode = context.agentMode === 'personal' || config.agent_mode === 'personal';
  const tz = config.timezone || 'Asia/Kolkata';
  const now = new Date();
  
  let localString;
  try {
    localString = now.toLocaleString('en-US', { timeZone: tz });
  } catch (e) {
    localString = now.toLocaleString('en-US', { timeZone: 'UTC' });
  }
  const localDate = new Date(localString);

  const dayOfWeek = localDate.getDay(); // 0 = Sun, 1 = Mon ... 6 = Sat
  const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
  const dayName = dayNames[dayOfWeek];

  const hours = localDate.getHours();
  const minutes = localDate.getMinutes();
  const currentTimeMinutes = hours * 60 + minutes;

  const [openH, openM] = (config.open_time || '09:30').split(':').map(Number);
  const [closeH, closeM] = (config.close_time || '18:30').split(':').map(Number);
  const openMinutes = (openH || 9) * 60 + (openM || 30);
  const closeMinutes = (closeH || 18) * 60 + (closeM || 30);

  const workDays = Array.isArray(config.workdays) ? config.workdays : [1, 2, 3, 4, 5, 6];
  const isWorkDay = workDays.includes(dayOfWeek);
  const isOpen = isWorkDay && currentTimeMinutes >= openMinutes && currentTimeMinutes < closeMinutes;

  const formattedDate = localDate.toLocaleDateString('en-US', {
    timeZone: tz,
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });

  const formattedTime = localDate.toLocaleTimeString('en-US', {
    timeZone: tz,
    hour: '2-digit',
    minute: '2-digit',
    hour12: true
  });

  return {
    timezone: tz,
    current_time: formattedTime,
    current_date: formattedDate,
    day_of_week: dayName,
    is_office_open: isPersonalMode ? true : isOpen,
    office_schedule: isPersonalMode ? '24/7 Always Active (Personal Assistant Mode)' : `${config.open_time || '09:30'} to ${config.close_time || '18:30'} (${tz})`,
    office_status: isPersonalMode
      ? 'Active 24/7 - Personal Executive Assistant ready on standby'
      : (isOpen ? 'Currently OPEN for business & live support' : 'Currently CLOSED. Inquiries received now will be prioritized next working morning.')
  };
}

/**
 * Human Escalation Recorder
 */
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
// 2. DYNAMIC PARAMETER INTERPOLATION & HTTP EXECUTOR
// ---------------------------------------------------------------------------

/**
 * Replaces {variable} or {var1|var2} placeholders in strings or nested objects
 */
function interpolateVariables(template, variables = {}, isUrl = false) {
  if (typeof template === 'string') {
    return template.replace(/\{([a-zA-Z0-9_|]+)\}/g, (match, expression) => {
      const keys = expression.split('|');
      for (const key of keys) {
        if (variables[key] !== undefined && variables[key] !== null && String(variables[key]).trim() !== '') {
          return isUrl ? encodeURIComponent(String(variables[key])) : String(variables[key]);
        }
      }
      return '';
    });
  }

  if (Array.isArray(template)) {
    return template.map(item => interpolateVariables(item, variables, isUrl));
  }

  if (template && typeof template === 'object') {
    const output = {};
    for (const [k, v] of Object.entries(template)) {
      output[k] = interpolateVariables(v, variables, isUrl);
    }
    return output;
  }

  return template;
}

/**
 * Executes a custom REST API (Normal API)
 */
async function executeCustomRestApi(executionConfig, args = {}) {
  const rawUrl = executionConfig.url || '';
  if (!rawUrl) {
    return { error: 'Target URL is missing in tool execution configuration.' };
  }

  // 1. Interpolate URL parameters
  const finalUrl = interpolateVariables(rawUrl, args, true);

  // Security guardrail: Validate protocol
  if (!/^https?:\/\//i.test(finalUrl)) {
    return { error: 'Invalid URL protocol. Only HTTP and HTTPS are permitted.' };
  }

  const method = (executionConfig.method || 'GET').toUpperCase();
  const headers = {
    'Accept': 'application/json',
    'User-Agent': 'WhatsGateway-Autonomous-Engine/3.0',
    ...(executionConfig.headers || {})
  };

  const reqOptions = {
    method,
    headers,
    signal: AbortSignal.timeout(executionConfig.timeout_ms || 5000)
  };

  // 2. Attach JSON body for write methods
  if (['POST', 'PUT', 'PATCH'].includes(method)) {
    headers['Content-Type'] = 'application/json';
    if (executionConfig.body_template) {
      reqOptions.body = JSON.stringify(interpolateVariables(executionConfig.body_template, args, false));
    } else {
      reqOptions.body = JSON.stringify(args);
    }
  }

  try {
    const res = await fetch(finalUrl, reqOptions);
    const contentType = res.headers.get('content-type') || '';
    
    let responseData;
    if (contentType.includes('application/json')) {
      responseData = await res.json();
    } else {
      const text = await res.text();
      responseData = { raw_text: text.slice(0, 1000) };
    }

    if (!res.ok) {
      return {
        status: res.status,
        success: false,
        error: responseData?.error || `API returned HTTP ${res.status}`,
        details: responseData
      };
    }

    // Optional response path extraction (e.g. "current" or "data.items")
    if (executionConfig.response_path && responseData && typeof responseData === 'object') {
      const parts = executionConfig.response_path.split('.');
      let target = responseData;
      for (const p of parts) {
        if (target && target[p] !== undefined) {
          target = target[p];
        }
      }
      return { success: true, status: res.status, data: target };
    }

    return { success: true, status: res.status, data: responseData };
  } catch (err) {
    return {
      success: false,
      error: `API request failed: ${err.message}`
    };
  }
}

// Anti-bot / reverse-proxy AES challenge solver cache for production endpoints
const sharedCookieJar = new Map();

async function resolveProxyCookie(targetUrl) {
  try {
    const domain = new URL(targetUrl).hostname;
    const cached = sharedCookieJar.get(domain);
    if (cached && Date.now() < cached.expires) {
      return cached.cookie;
    }

    const testRes = await fetch(targetUrl, {
      headers: {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36'
      }
    });
    const html = await testRes.text();
    const regex = /var\s+a=toNumbers\("([0-9a-fA-F]+)"\),\s*b=toNumbers\("([0-9a-fA-F]+)"\),\s*c=toNumbers\("([0-9a-fA-F]+)"\)/;
    const match = html.match(regex);
    if (!match) return null;

    const key = Buffer.from(match[1], 'hex');
    const iv = Buffer.from(match[2], 'hex');
    const ciphertext = Buffer.from(match[3], 'hex');
    const decipher = crypto.createDecipheriv('aes-128-cbc', key, iv);
    decipher.setAutoPadding(false);
    const decrypted = Buffer.concat([decipher.update(ciphertext), decipher.final()]);
    const cookieValue = decrypted.toString('hex');

    const activateUrl = targetUrl.includes('?') ? `${targetUrl}&i=1` : `${targetUrl}?i=1`;
    await fetch(activateUrl, {
      headers: {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36',
        'Cookie': `__test=${cookieValue}`
      }
    }).catch(() => {});

    sharedCookieJar.set(domain, {
      cookie: cookieValue,
      expires: Date.now() + 5 * 3600 * 1000
    });
    return cookieValue;
  } catch (err) {
    return null;
  }
}

/**
 * Executes a Production Shared API (Config-driven Action Routes)
 */
async function executeSharedEnvApi(executionConfig, args = {}) {
  // Production-only resolution: direct base_url or production/live environment
  const baseUrl = executionConfig.base_url
    || executionConfig.environments?.production?.base_url
    || executionConfig.environments?.live?.base_url
    || '';
  const token = executionConfig.bearer_token
    || executionConfig.environments?.production?.bearer_token
    || executionConfig.environments?.live?.bearer_token
    || '';

  if (!baseUrl) {
    return { error: 'Production Base URL is not configured in tool execution settings.' };
  }

  // Determine endpoint, method, and payload from dynamic action_routes or fallback executionConfig
  const actionRoutes = executionConfig.action_routes || {};
  let route = null;

  if (args.action) {
    const act = String(args.action).toLowerCase().trim().replace(/[\s\-]/g, '_');
    if (actionRoutes[act]) {
      route = actionRoutes[act];
    } else {
      const aliasesMap = executionConfig.aliases || {};
      const targetAct = aliasesMap[act];
      if (targetAct && actionRoutes[targetAct]) {
        route = actionRoutes[targetAct];
      } else {
        for (const [rKey, rCfg] of Object.entries(actionRoutes)) {
          if (Array.isArray(rCfg.aliases) && rCfg.aliases.includes(act)) {
            route = rCfg;
            break;
          }
        }
      }
    }
  }

  let endpoint = '';
  let method = 'GET';
  let bodyPayload = null;

  if (route) {
    endpoint = interpolateVariables(route.endpoint || '', args, true);
    method = (route.method || 'GET').toUpperCase();

    // Dynamically interpolate and append query parameters if configured
    if (route.query_params && typeof route.query_params === 'object') {
      const qp = new URLSearchParams();
      for (const [paramKey, paramTpl] of Object.entries(route.query_params)) {
        const val = interpolateVariables(paramTpl, args, false);
        if (val && !val.startsWith('{')) {
          qp.append(paramKey, val);
        } else if (args[paramKey] !== undefined && args[paramKey] !== null && String(args[paramKey]).trim() !== '') {
          qp.append(paramKey, String(args[paramKey]));
        }
      }
      const queryString = qp.toString();
      if (queryString) {
        endpoint += (endpoint.includes('?') ? '&' : '?') + queryString;
      }
    }

    // Dynamic body payload for write methods
    if (['POST', 'PUT', 'PATCH'].includes(method)) {
      if (route.body_template && typeof route.body_template === 'object') {
        bodyPayload = interpolateVariables(route.body_template, args, false);
      } else {
        const { action: _act, ...restArgs } = args;
        bodyPayload = restArgs;
      }
    }
  } else {
    // Universal fallback: use configured endpoint or requested action as path
    endpoint = executionConfig.endpoint
      ? interpolateVariables(executionConfig.endpoint, args, true)
      : (args.action ? `/${args.action.replace(/^\/+/, '')}` : '');
    method = (executionConfig.method || 'GET').toUpperCase();
    if (['POST', 'PUT', 'PATCH'].includes(method)) {
      const { action: _act, ...restArgs } = args;
      bodyPayload = restArgs;
    }
  }

  const finalUrl = `${baseUrl.replace(/\/+$/, '')}/${endpoint.replace(/^\/+/, '')}`;

  const headers = {
    'Accept': 'application/json',
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36',
    ...(executionConfig.headers || {})
  };

  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
    headers['X-API-KEY'] = token;
  }

  const reqOptions = {
    method,
    headers,
    signal: AbortSignal.timeout(executionConfig.timeout_ms || 12000)
  };

  if (['POST', 'PUT', 'PATCH'].includes(method)) {
    headers['Content-Type'] = 'application/json';
    reqOptions.body = JSON.stringify(bodyPayload || {});
  }

  try {
    const testCookie = await resolveProxyCookie(finalUrl);
    if (testCookie) {
      headers['Cookie'] = `__test=${testCookie}`;
    }

    let res = await fetch(finalUrl, reqOptions);
    let rawText = await res.text();
    let data = null;

    if (rawText.includes('toNumbers') && rawText.includes('slowAES')) {
      const solved = await resolveProxyCookie(finalUrl);
      if (solved) {
        headers['Cookie'] = `__test=${solved}`;
        res = await fetch(finalUrl, reqOptions);
        rawText = await res.text();
      }
    }

    try {
      data = JSON.parse(rawText);
    } catch (e) {
      data = null;
    }

    if (!res.ok || !data) {
      return {
        status: res.status,
        success: false,
        error: data?.error || `Production API returned status ${res.status}: ${rawText.slice(0, 150)}`
      };
    }

    return {
      status: res.status,
      success: true,
      environment: 'production',
      data: data.data || data
    };
  } catch (err) {
    return {
      success: false,
      error: `Production API request failed: ${err.message}`
    };
  }
}

/**
 * Executes an Outbound Webhook tool
 */
async function executeWebhookTool(executionConfig, args = {}) {
  const targetUrl = executionConfig.target_url || '';
  if (!targetUrl) {
    return { error: 'Webhook target URL is not configured.' };
  }

  const headers = {
    'Content-Type': 'application/json',
    'User-Agent': 'WhatsGateway-Webhook-Dispatcher/3.0',
    ...(executionConfig.headers || {})
  };

  const payload = executionConfig.body_template 
    ? interpolateVariables(executionConfig.body_template, args)
    : { event: 'ai_tool_invocation', data: args, timestamp: new Date().toISOString() };

  try {
    const res = await fetch(targetUrl, {
      method: 'POST',
      headers,
      body: JSON.stringify(payload),
      signal: AbortSignal.timeout(executionConfig.timeout_ms || 5000)
    });

    return {
      dispatched: true,
      status: res.status,
      message: `Webhook sent with status ${res.status}`
    };
  } catch (err) {
    return {
      dispatched: false,
      error: `Webhook delivery failed: ${err.message}`
    };
  }
}

// ---------------------------------------------------------------------------
// 3. UNIVERSAL TOOLS REGISTRY & GEMINI SCHEMA GENERATOR
// ---------------------------------------------------------------------------

export class UniversalToolsRegistry {
  /**
   * Loads all active tools for a given user from ai_custom_tools table
   */
  static async loadUserTools(userId, db) {
    if (!userId || !db) return [];

    try {
      const [rows] = await db.execute(
        `SELECT tool_slug, display_name, category, description, parameters_schema, execution_config, rbac_roles, is_active
         FROM ai_custom_tools
         WHERE user_id = ? AND is_active = 1
         ORDER BY id ASC`,
        [userId]
      );

      return rows.map(r => ({
        tool_slug: r.tool_slug,
        display_name: r.display_name,
        category: r.category,
        description: r.description,
        parameters_schema: typeof r.parameters_schema === 'string' ? JSON.parse(r.parameters_schema) : (r.parameters_schema || {}),
        execution_config: typeof r.execution_config === 'string' ? JSON.parse(r.execution_config) : (r.execution_config || {}),
        rbac_roles: typeof r.rbac_roles === 'string' ? JSON.parse(r.rbac_roles) : (r.rbac_roles || ['admin']),
        is_active: Boolean(r.is_active)
      }));
    } catch (err) {
      console.error('[UniversalToolsRegistry] Failed to load tools from database:', err.message);
      return [];
    }
  }

  /**
   * Compiles Gemini FunctionDeclaration schemas for tools allowed for caller's role
   */
  static compileDeclarationsForRole(tools = [], callerRole = 'guest') {
    const declarations = [];

    for (const tool of tools) {
      // Check RBAC permission
      const allowedRoles = Array.isArray(tool.rbac_roles) ? tool.rbac_roles : ['admin'];
      if (!allowedRoles.includes(callerRole) && callerRole !== 'admin') {
        continue;
      }

      // Ensure valid parameters object conforming to Gemini API spec
      let params = tool.parameters_schema;
      if (!params || typeof params !== 'object' || !params.type) {
        params = {
          type: 'OBJECT',
          properties: {},
          required: []
        };
      }

      declarations.push({
        name: tool.tool_slug,
        description: tool.description,
        parameters: params
      });
    }

    return declarations;
  }

  /**
   * Dispatches and executes tool dynamically by slug
   */
  static async executeTool(toolName, args, tools = [], context = {}) {
    const startTime = Date.now();
    const callerRole = context.callerRole || 'guest';
    const userId = context.userId || 1;
    const db = context.db;

    // 1. Locate tool in catalog
    const tool = tools.find(t => t.tool_slug === toolName);

    if (!tool) {
      return { error: `Tool "${toolName}" is not registered or is disabled.` };
    }

    // 2. Enforce Role-Based Access Control
    const allowedRoles = Array.isArray(tool.rbac_roles) ? tool.rbac_roles : ['admin'];
    if (!allowedRoles.includes(callerRole) && callerRole !== 'admin') {
      const deniedResponse = {
        denied: true,
        error: `Permission Denied: Caller role "${callerRole}" is not permitted to execute tool "${toolName}".`
      };
      await UniversalToolsRegistry.logInvocation(db, userId, context.callerPhone, callerRole, toolName, args, deniedResponse, 0, 'denied');
      return deniedResponse;
    }

    // 3. Execute according to category
    let result;
    try {
      if (tool.category === 'builtin') {
        const handler = tool.execution_config.handler || tool.tool_slug;
        if (handler === 'web_search') {
          result = await searchWebDuckDuckGo(args.query, tool.execution_config.max_results, tool.execution_config.timeout_ms);
        } else if (handler === 'evaluate_math') {
          result = evaluateSafeMath(args.expression);
        } else if (handler === 'get_current_time') {
          result = getCurrentCustomTime(tool.execution_config, context);
        } else if (handler === 'escalate_to_human') {
          result = await triggerHumanEscalation(args.reason, context);
        } else {
          result = { error: `Unknown built-in handler: ${handler}` };
        }
      } else if (tool.category === 'rest_api') {
        result = await executeCustomRestApi(tool.execution_config, args);
      } else if (tool.category === 'shared_env_api') {
        result = await executeSharedEnvApi(tool.execution_config, args);
      } else if (tool.category === 'webhook') {
        result = await executeWebhookTool(tool.execution_config, args);
      } else {
        result = { error: `Unsupported tool category: ${tool.category}` };
      }
    } catch (execErr) {
      result = { error: `Execution exception: ${execErr.message}` };
    }

    const latency = Date.now() - startTime;
    const status = result?.error ? 'error' : 'success';

    // 4. Record audit log
    await UniversalToolsRegistry.logInvocation(db, userId, context.callerPhone, callerRole, toolName, args, result, latency, status);

    return result;
  }

  /**
   * Internal audit logger into ai_tool_invocations
   */
  static async logInvocation(db, userId, callerPhone, callerRole, toolName, args, result, latencyMs, status) {
    if (!db || !userId) return;

    try {
      await db.execute(
        `INSERT INTO ai_tool_invocations 
         (user_id, caller_phone, caller_role, tool_name, tool_arguments, tool_result, execution_time_ms, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
        [
          userId,
          callerPhone || 'unknown',
          callerRole || 'guest',
          toolName,
          JSON.stringify(args || {}),
          JSON.stringify(result || {}).slice(0, 4000),
          latencyMs || 0,
          status || 'success'
        ]
      );
    } catch (e) {
      console.error('[UniversalToolsRegistry] Audit log insert failed:', e.message);
    }
  }
}

// ---------------------------------------------------------------------------
// 4. SANITIZATION HELPERS
// ---------------------------------------------------------------------------
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
