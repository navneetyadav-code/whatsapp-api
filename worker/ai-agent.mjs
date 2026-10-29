import { ChatMemoryEngine } from './chat-memory.mjs';
import { UniversalToolsRegistry } from './tools-engine.mjs';
/**
 * Enterprise Multi-Provider AI Agent & Zero-Cloud Vector RAG Engine
 * ----------------------------------------------------------------
 * Supports Google Gemini, Groq, and OpenRouter with automatic fallback cascade,
 * high-performance in-memory cosine similarity knowledge retrieval, and frustration detection.
 */

// Simple English & multilingual tokenization and stop words
const STOP_WORDS = new Set([
  'a', 'about', 'above', 'after', 'again', 'against', 'all', 'am', 'an', 'and', 'any', 'are', 'as', 'at',
  'be', 'because', 'been', 'before', 'being', 'below', 'between', 'both', 'but', 'by',
  'can', 'could', 'did', 'do', 'does', 'doing', 'down', 'during',
  'each', 'few', 'for', 'from', 'further',
  'had', 'has', 'have', 'having', 'he', 'her', 'here', 'hers', 'herself', 'him', 'himself', 'his', 'how',
  'i', 'if', 'in', 'into', 'is', 'it', 'its', 'itself',
  'just', 'me', 'more', 'most', 'my', 'myself',
  'no', 'nor', 'not', 'now', 'of', 'off', 'on', 'once', 'only', 'or', 'other', 'our', 'ours', 'ourselves', 'out', 'over', 'own',
  'same', 'she', 'should', 'so', 'some', 'such',
  'than', 'that', 'the', 'their', 'theirs', 'them', 'themselves', 'then', 'there', 'these', 'they', 'this', 'those', 'through', 'to', 'too',
  'under', 'until', 'up', 'very',
  'was', 'we', 'were', 'what', 'when', 'where', 'which', 'while', 'who', 'whom', 'why', 'with', 'would',
  'you', 'your', 'yours', 'yourself', 'yourselves'
]);

/**
 * Tokenize string into normalized terms
 */
export function tokenize(text) {
  if (!text || typeof text !== 'string') return [];
  return text
    .toLowerCase()
    .replace(/[^\p{L}\p{N}\s]/gu, ' ')
    .split(/\s+/)
    .filter(token => token.length > 1 && !STOP_WORDS.has(token));
}

/**
 * Build Term-Frequency (TF) vector
 */
export function buildTfVector(tokens) {
  const tf = {};
  if (!tokens || tokens.length === 0) return tf;
  for (const t of tokens) {
    tf[t] = (tf[t] || 0) + 1;
  }
  const total = tokens.length;
  for (const t in tf) {
    tf[t] = tf[t] / total;
  }
  return tf;
}

/**
 * Calculate Cosine Similarity between two sparse TF vectors
 */
export function cosineSimilarity(vecA, vecB) {
  let dotProduct = 0;
  let normA = 0;
  let normB = 0;

  for (const key in vecA) {
    const valA = vecA[key];
    normA += valA * valA;
    if (key in vecB) {
      dotProduct += valA * vecB[key];
    }
  }

  for (const key in vecB) {
    const valB = vecB[key];
    normB += valB * valB;
  }

  if (normA === 0 || normB === 0) return 0;
  return dotProduct / (Math.sqrt(normA) * Math.sqrt(normB));
}

/**
 * Calculate Cosine Similarity between two dense numeric arrays
 */
export function denseCosineSimilarity(arrA, arrB) {
  if (!Array.isArray(arrA) || !Array.isArray(arrB) || arrA.length !== arrB.length) return 0;
  let dot = 0;
  let normA = 0;
  let normB = 0;
  for (let i = 0; i < arrA.length; i++) {
    dot += arrA[i] * arrB[i];
    normA += arrA[i] * arrA[i];
    normB += arrB[i] * arrB[i];
  }
  if (normA === 0 || normB === 0) return 0;
  return dot / (Math.sqrt(normA) * Math.sqrt(normB));
}

/**
 * Retrieve top-K relevant knowledge chunks for a customer query
 */
export async function retrieveRelevantKnowledge(db, userId, queryText, topK = 3, threshold = 0.15) {
  try {
    const [rows] = await db.execute(
      'SELECT id, title, content, embedding, embedding_model FROM ai_knowledge_chunks WHERE user_id = ? AND is_active = 1 LIMIT 200',
      [userId]
    );

    if (!rows || rows.length === 0) return [];

    const queryTokens = tokenize(queryText);
    const queryTf = buildTfVector(queryTokens);

    const scored = [];
    for (const chunk of rows) {
      let sim = 0;
      // Check if dense embedding is stored
      if (chunk.embedding) {
        try {
          const parsed = typeof chunk.embedding === 'string' ? JSON.parse(chunk.embedding) : chunk.embedding;
          if (Array.isArray(parsed)) {
            // Dense vector available
            // If query vector not available, fallback to TF-IDF
          }
        } catch (_) {}
      }

      // Compute TF-IDF sparse similarity
      const chunkTokens = tokenize((chunk.title || '') + ' ' + (chunk.content || ''));
      const chunkTf = buildTfVector(chunkTokens);
      sim = cosineSimilarity(queryTf, chunkTf);

      // Exact phrase / title boost
      const lowerQuery = queryText.toLowerCase();
      const lowerTitle = (chunk.title || '').toLowerCase();
      if (lowerTitle && lowerQuery.includes(lowerTitle)) {
        sim += 0.3;
      }

      if (sim >= threshold) {
        scored.push({
          id: chunk.id,
          title: chunk.title,
          content: chunk.content,
          score: sim
        });
      }
    }

    scored.sort((a, b) => b.score - a.score);
    return scored.slice(0, topK);
  } catch (err) {
    console.error('[AI Agent RAG] Retrieval error:', err.message);
    return [];
  }
}

/**
 * Detect customer frustration, urgency, or explicit request for a human
 */
export function detectFrustrationAndEscalation(text) {
  if (!text || typeof text !== 'string') {
    return { shouldEscalate: false, score: 0, reason: null, signals: [] };
  }

  const raw = text.trim();
  const lower = raw.toLowerCase();
  const signals = [];
  let score = 0;

  // 1. Explicit human handoff keywords
  const HUMAN_KEYWORDS = [
    'human', 'agent', 'support team', 'representative', 'customer service',
    'person', 'operator', 'talk to someone', 'talk to agent', 'speak to human',
    'real person', 'executive', 'manager', 'boss', 'advisor', 'insaan', 'kisi se baat', 'transfer', 'tranfer', 'call transfer', 'human agent', 'connect me', 'call lagao', 'baat karao', 'phone pe baat', 'real human'
  ];
  for (const kw of HUMAN_KEYWORDS) {
    const regex = new RegExp(`\\b${kw}\\b`, 'i');
    if (regex.test(lower)) {
      signals.push(`explicit_human_request:${kw}`);
      score += 0.9;
      break;
    }
  }

  // 2. High severity complaint / fraud / legal keywords
  const SEVERE_KEYWORDS = [
    'fraud', 'scam', 'cheater', 'stolen', 'police', 'lawyer', 'consumer court',
    'sue', 'court', 'chargeback', 'loot', 'chor', 'fir', 'fake service', 'worst company'
  ];
  for (const kw of SEVERE_KEYWORDS) {
    const regex = new RegExp(`\\b${kw}\\b`, 'i');
    if (regex.test(lower)) {
      signals.push(`severe_risk:${kw}`);
      score += 0.85;
      break;
    }
  }

  // 3. Profanity & Severe Abuse cues
  const PROFANITY_KEYWORDS = [
    'fuck', 'chutiya', 'harami', 'kutta', 'kamina', 'useless bot', 'bakwas', 'fraud company'
  ];
  for (const kw of PROFANITY_KEYWORDS) {
    if (lower.includes(kw)) {
      signals.push(`profanity_abuse:${kw}`);
      score += 0.75;
      break;
    }
  }

  // 4. Frustration / Anger cues
  const ANGER_KEYWORDS = [
    'terrible', 'pathetic', 'idiot', 'stupid', 'waste of time',
    'annoying', 'angry', 'hate this', 'disgusted', 'pagal', 'faltu', 'rubbish', 'nonsense', 'useless'
  ];
  let angerCount = 0;
  for (const kw of ANGER_KEYWORDS) {
    if (lower.includes(kw)) {
      signals.push(`negative_sentiment:${kw}`);
      score += 0.35;
      angerCount++;
      if (angerCount >= 2) break;
    }
  }

  // 4. Shouting heuristic (ALL CAPS)
  const letters = raw.replace(/[^a-zA-Z]/g, '');
  if (letters.length >= 8) {
    const upperCount = (raw.match(/[A-Z]/g) || []).length;
    const upperRatio = upperCount / letters.length;
    if (upperRatio >= 0.70) {
      signals.push('shouting_all_caps');
      score += 0.35;
    }
  }

  // 5. Punctuation intensity
  if (/[!?]{3,}/.test(raw)) {
    signals.push('excessive_punctuation');
    score += 0.25;
  }

  const shouldEscalate = score >= 0.65;
  let primaryReason = 'frustration_detected';
  if (signals.some(s => s.startsWith('explicit_human_request'))) {
    primaryReason = 'explicit_human_request';
  } else if (signals.some(s => s.startsWith('severe_risk'))) {
    primaryReason = 'severe_risk_flag';
  }

  return {
    shouldEscalate,
    score: Math.min(1.0, score),
    reason: shouldEscalate ? primaryReason : null,
    signals
  };
}

/**
 * Format system prompt with company persona and retrieved RAG context
 */
export function buildPromptWithRag(systemPersona, retrievedChunks, customerMessage, options = {}) {
  const isPersonal = options.agentMode === 'personal';
  const ownerName = options.ownerName || 'Boss';
  const skillDirectives = Array.isArray(options.skillDirectives) ? options.skillDirectives : [];

  if (isPersonal) {
    // Ultra-lean Micro-Kernel Architecture (~120 tokens)
    let microKernel = `You are the 24/7 dedicated Personal Executive Assistant to Boss (${ownerName}).
OPERATING DIRECTIVES:
1. Strict Grounding: State facts ONLY from verified executed tool results. Never invent records, prices, phone numbers, or dates.
2. Two-Phase Action Safety: Consequential operations (sending emails, mutating records) MUST be drafted and approved by Boss before dispatch. Never claim an email is sent until confirm_action succeeds.
3. Formatting: Crisp, proactive, structured. Use *bold* highlights, clean bullet points, and appropriate emojis.
4. Silence Feedback: You are a personal assistant, NOT a customer service line. NEVER ask for customer reviews, 5-star ratings, or feedback surveys.`;

    if (options.factsText && options.factsText.trim()) {
      microKernel += '\n\n' + options.factsText.trim();
    }

    if (skillDirectives.length > 0) {
      microKernel += '\n\n' + skillDirectives.join('\n\n');
    }

    return {
      systemPrompt: microKernel,
      userMessage: customerMessage
    };
  }

  let contextBlock = '';
  if (retrievedChunks && retrievedChunks.length > 0) {
    contextBlock = `\n\n--- OFFICIAL VERIFIED KNOWLEDGE BASE ---\n` +
      retrievedChunks.map((c, i) => `[Source ${i + 1}: ${c.title}]\n${c.content}`).join('\n\n') +
      `\n---------------------------------------\n` +
      `STRICT INSTRUCTIONS FOR THE AI ASSISTANT:
1. Base your factual answers STRICTLY on the knowledge base sources above.
2. DO NOT invent, hallucinate, or assume facts (like prices, phone numbers, or rules) not present in the sources.
3. If the user asks something not covered in the sources, politely inform them that you do not have that specific detail and offer to connect them with our human support team.
4. Language & Tone: Detect the user's language and mirror their style naturally. If they use Hindi or natural Hinglish, reply in warm, helpful Hinglish. If they use English, reply in professional, encouraging English.
5. Format for WhatsApp: Use bold (*text*), clean bullet points, and appropriate emojis. Avoid long unbroken walls of text.`;
  } else {
    contextBlock = `\n\n--- KNOWLEDGE RETRIEVAL NOTE ---\n` +
      `No specific knowledge base records matched the user's query.\n` +
      `STRICT INSTRUCTIONS FOR THE AI ASSISTANT:
1. You are a dedicated customer assistant strictly limited to this organization.
2. If the user asks about out-of-scope topics (e.g. general knowledge, recipes, cooking, coding, competitors, unrelated trivia), DO NOT answer the out-of-scope question. Politely decline and state that you can only assist with our organization's official products, services, and policies.
3. If they are asking a genuine question about our organization or services that isn't in your knowledge base, apologize politely and offer to connect them with our human support team.
4. For general pleasantries/greetings (e.g. 'hi', 'hello', 'good morning', 'namaste'), respond warmly and ask how you can help them with our products or services today.`;
  }

  const defaultPrompt = 'You are a helpful customer support assistant on WhatsApp.';
  const fullSystem = (systemPersona || defaultPrompt) + contextBlock;
  return {
    systemPrompt: fullSystem,
    userMessage: customerMessage
  };
}

/**
 * Call Google Gemini API with Autonomous Multi-Turn Function Calling Support
 */
async function callGemini(apiKey, model, systemPrompt, userMessage, temperature = 0.4, maxTokens = 500, conversationHistory = [], toolsContext = null) {
  let modelName = model || 'gemini-3.5-flash-lite';
  if (modelName === 'gemini-1.5-flash' || modelName === 'gemini-2.5-flash') {
    modelName = 'gemini-3.5-flash-lite';
  }

  const url = `https://generativelanguage.googleapis.com/v1beta/models/${modelName}:generateContent?key=${apiKey}`;
  const contents = ChatMemoryEngine.buildGeminiContents(systemPrompt, conversationHistory, userMessage);

  // Dynamically load active custom & built-in tools for this user from ai_custom_tools
  let toolDeclarations = [];
  let userTools = [];
  if (toolsContext && toolsContext.userId && toolsContext.db) {
    try {
      userTools = toolsContext.scopedTools || await UniversalToolsRegistry.loadUserTools(toolsContext.userId, toolsContext.db);
      const callerRole = toolsContext.callerRole || 'guest';
      toolDeclarations = UniversalToolsRegistry.compileDeclarationsForRole(userTools, callerRole);
    } catch (e) {
      console.error('[AI Agent] Failed to compile tool declarations:', e.message);
    }
  }

  const maxTurns = 4; // Up to 3 autonomous tool turns + final synthesis
  let turn = 0;

  while (turn < maxTurns) {
    turn++;
    const payload = {
      contents,
      generationConfig: {
        temperature: Number(temperature) || 0.4,
        maxOutputTokens: Math.max(Number(maxTokens) || 1200, 1000)
      }
    };

    // Supply tools on turns < maxTurns; omit on final turn to guarantee text synthesis
    if (toolDeclarations.length > 0 && turn < maxTurns) {
      payload.tools = [{ functionDeclarations: toolDeclarations }];
    }

    let response;
    let attempts = 0;
    while (attempts < 2) {
      attempts++;
      try {
        response = await fetch(url, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
          signal: AbortSignal.timeout(35000)
        });
        break;
      } catch (fetchErr) {
        if (attempts >= 2) throw fetchErr;
        const reason = fetchErr.cause?.message || fetchErr.message;
        console.warn(`[AI Agent] Gemini fetch attempt ${attempts} failed: ${reason}. Retrying in 1s...`);
        await new Promise(r => setTimeout(r, 1000));
      }
    }

    if (!response.ok) {
      const errText = await response.text();
      throw new Error(`Gemini API error [${response.status}]: ${errText.slice(0, 200)}`);
    }

    const data = await response.json();
    const candidate = data?.candidates?.[0];
    const parts = candidate?.content?.parts || [];

    const functionCalls = parts.filter(p => p.functionCall);

    if (functionCalls.length > 0 && turn < maxTurns) {
      const functionResponseParts = [];
      for (const call of functionCalls) {
        const fnName = call.functionCall.name;
        const fnArgs = call.functionCall.args || {};
        const result = await UniversalToolsRegistry.executeTool(fnName, fnArgs, userTools, toolsContext);
        functionResponseParts.push({
          functionResponse: {
            name: fnName,
            response: result
          }
        });
      }

      contents.push(candidate.content);
      contents.push({
        role: 'user',
        parts: functionResponseParts
      });
      // Continue to next turn
    } else {
      const textParts = parts.filter(p => p.text).map(p => p.text) || [];
      const text = textParts.join('\n').trim();
      if (!text) {
        throw new Error('Gemini API returned empty text');
      }
      return text;
    }
  }

  throw new Error('Gemini function calling loop exceeded max turns without generating text');
}

/**
 * Call Groq API (OpenAI compatible)
 */
async function callGroq(apiKey, model, systemPrompt, userMessage, temperature = 0.4, maxTokens = 500, conversationHistory = []) {
  const modelName = model || 'openai/gpt-oss-20b';
  const url = 'https://api.groq.com/openai/v1/chat/completions';

  const messages = ChatMemoryEngine.buildOpenAiMessages(systemPrompt, conversationHistory, userMessage);

  const payload = {
    model: modelName,
    messages,
    temperature: Number(temperature) || 0.4,
    max_tokens: Math.max(Number(maxTokens) || 1200, 800)
  };

  const response = await fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${apiKey}`
    },
    body: JSON.stringify(payload),
    signal: AbortSignal.timeout(15000)
  });

  if (!response.ok) {
    const errText = await response.text();
    throw new Error(`Groq API error [${response.status}]: ${errText.slice(0, 200)}`);
  }

  const data = await response.json();
  const text = data?.choices?.[0]?.message?.content;
  if (!text) {
    throw new Error('Groq API returned empty text');
  }
  return text.trim();
}

/**
 * Call OpenRouter API (OpenAI compatible fallback)
 */
async function callOpenRouter(apiKey, model, systemPrompt, userMessage, temperature = 0.4, maxTokens = 500, conversationHistory = []) {
  const modelName = model || 'meta-llama/llama-3.3-70b-instruct:free';
  const url = 'https://openrouter.ai/api/v1/chat/completions';

  const messages = ChatMemoryEngine.buildOpenAiMessages(systemPrompt, conversationHistory, userMessage);

  const payload = {
    model: modelName,
    messages,
    temperature: Number(temperature),
    max_tokens: Math.max(Number(maxTokens) || 1200, 800)
  };

  const response = await fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${apiKey}`,
      'HTTP-Referer': 'https://whatsapp-api-xepo.onrender.com',
      'X-Title': 'WhatsGateway AI'
    },
    body: JSON.stringify(payload),
    signal: AbortSignal.timeout(20000)
  });

  if (!response.ok) {
    const errText = await response.text();
    throw new Error(`OpenRouter API error [${response.status}]: ${errText.slice(0, 200)}`);
  }

  const data = await response.json();
  const text = data?.choices?.[0]?.message?.content;
  if (!text) {
    throw new Error('OpenRouter API returned empty text');
  }
  return text.trim();
}

/**
 * Execute Multi-Provider Generation Cascade
 */
export async function generateAiReply(settings, systemPrompt, customerMessage, conversationHistory = [], toolsContext = null) {
  const priority = settings.provider_priority || 'gemini';
  const providers = [];

  if (priority === 'gemini') {
    providers.push('gemini', 'groq', 'openrouter');
  } else if (priority === 'groq') {
    providers.push('groq', 'gemini', 'openrouter');
  } else {
    providers.push('openrouter', 'gemini', 'groq');
  }

  let lastError = null;
  for (const prov of providers) {
    try {
      if (prov === 'gemini' && settings.gemini_api_key) {
        return await callGemini(
          settings.gemini_api_key,
          settings.gemini_model,
          systemPrompt,
          customerMessage,
          settings.temperature,
          settings.max_tokens,
          conversationHistory,
          toolsContext
        );
      }
      if (prov === 'groq' && settings.groq_api_key) {
        return await callGroq(
          settings.groq_api_key,
          settings.groq_model,
          systemPrompt,
          customerMessage,
          settings.temperature,
          settings.max_tokens,
          conversationHistory
        );
      }
      if (prov === 'openrouter' && settings.openrouter_api_key) {
        return await callOpenRouter(
          settings.openrouter_api_key,
          settings.openrouter_model,
          systemPrompt,
          customerMessage,
          settings.temperature,
          settings.max_tokens,
          conversationHistory
        );
      }
    } catch (err) {
      const errDetail = err.cause?.message ? `${err.message} (${err.cause.message})` : err.message;
      console.warn(`[AI Agent] Provider '${prov}' failed: ${errDetail}. Cascading to next provider...`);
      lastError = err;
    }
  }

  if (lastError) {
    throw lastError;
  }
  return null;
}
