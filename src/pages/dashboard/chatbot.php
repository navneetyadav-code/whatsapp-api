    <!-- Page Header & Tab Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">🤖 AI Agent Studio</h1>
            <p class="text-[13px] text-slate-500 mt-0.5 font-medium">Configure intelligent multi-provider LLMs, company knowledge retrieval, autonomous tools, and human escalation alarms.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= e(base_url('index.php?page=rules')) ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 shadow-xs transition-all active:scale-[0.98]">
                <span>⚡ Keyword Rules</span>
            </a>
            <a href="<?= e(base_url('index.php?page=live-chat')) ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-xs transition-all active:scale-[0.98]">
                <span>💬 Open Live Chat</span>
            </a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-1.5 border-b border-slate-200/90 pb-3 mb-6 overflow-x-auto">
        <a href="<?= e(base_url('index.php?page=chatbot&tab=ai-agent')) ?>" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all <?= $activeTab === 'ai-agent' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
            🤖 AI Persona & Providers
        </a>
        <a href="<?= e(base_url('index.php?page=chatbot&tab=knowledge')) ?>" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all <?= $activeTab === 'knowledge' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
            📚 Company Knowledge (RAG) (<?= count($knowledgeChunks) ?>)
        </a>
        <a href="<?= e(base_url('index.php?page=chatbot&tab=escalations')) ?>" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all <?= $activeTab === 'escalations' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
            🚨 Escalations & Buzzer Logs
        </a>
        <a href="<?= e(base_url('index.php?page=chatbot&tab=tools')) ?>" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all <?= $activeTab === 'tools' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
            🛠️ Tools & Integrations (RBAC)
        </a>
    </div>

    <?php if ($activeTab === 'ai-agent'): ?>
    <!-- ========================================== -->
    <!-- TAB 1: AI AGENT & MULTI-PROVIDER ENGINE    -->
    <!-- ========================================== -->
    <div class="max-w-4xl bg-white rounded-xl border border-slate-200/80 p-6 shadow-card mb-8">
        <div class="flex items-center justify-between pb-4 border-b border-slate-200/80 mb-6">
            <div>
                <h2 class="text-base font-bold text-slate-900">AI Support Agent Configuration</h2>
                <p class="text-xs text-slate-500 mt-0.5">Customize your AI assistant persona, API keys, and multi-provider fallback cascade.</p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold <?= (int) ($aiSettings['auto_reply_enabled'] ?? 1) === 1 ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200' ?>">
                <span class="w-2 h-2 rounded-full <?= (int) ($aiSettings['auto_reply_enabled'] ?? 1) === 1 ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' ?>"></span>
                <span><?= (int) ($aiSettings['auto_reply_enabled'] ?? 1) === 1 ? 'Autonomous AI Active' : 'Autonomous AI Paused' ?></span>
            </span>
        </div>

        <form method="post" action="<?= e(base_url('index.php?page=ai-agent-save')) ?>" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

            <!-- ============================================== -->
            <!-- AI OPERATING MODE & 1-CLICK ASSISTANT SWITCHER -->
            <!-- ============================================== -->
            <div class="bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 rounded-xl p-5 text-white shadow-md border border-indigo-900/40 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-indigo-800/40">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🤖</span>
                            <h3 class="text-sm font-bold tracking-tight text-white">AI Operating Mode</h3>
                            <span id="activeModeBadge" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wide uppercase <?= ($aiSettings['agent_mode'] ?? 'support') === 'personal' ? 'bg-violet-500/20 text-violet-300 border border-violet-500/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' ?>">
                                <?= ($aiSettings['agent_mode'] ?? 'support') === 'personal' ? '⚡ Personal Assistant (24/7)' : '🏢 Business Support Bot' ?>
                            </span>
                        </div>
                        <p class="text-xs text-indigo-200/80 mt-1">Convert your WhatsApp AI into your personal 24/7 executive assistant or a customer service representative with 1 click.</p>
                    </div>

                    <!-- Quick 1-Click Action Buttons -->
                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button" id="btnConvertToPersonal" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gradient-to-r from-violet-600 to-fuchsia-600 hover:from-violet-500 hover:to-fuchsia-500 text-white rounded-lg text-xs font-bold shadow-md hover:shadow-violet-500/25 transition-all active:scale-[0.98]">
                            <span>⚡</span>
                            <span>Convert to Personal Assistant</span>
                        </button>
                        <button type="button" id="btnSwitchToSupport" class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-lg text-xs font-semibold transition-all active:scale-[0.98]">
                            <span>🏢</span>
                            <span>Support Bot Mode</span>
                        </button>
                    </div>
                </div>

                <!-- Mode Selection Radio Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                    <!-- Option 1: Business Support -->
                    <label class="relative flex flex-col p-3.5 rounded-xl border transition-all cursor-pointer <?= ($aiSettings['agent_mode'] ?? 'support') === 'support' ? 'bg-indigo-900/40 border-emerald-500/80 ring-1 ring-emerald-500/50' : 'bg-slate-800/40 border-slate-700/60 hover:border-slate-600' ?>">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-white">
                                <span>🏢</span> Business Customer Support Bot
                            </span>
                            <input type="radio" name="agent_mode" value="support" <?= ($aiSettings['agent_mode'] ?? 'support') === 'support' ? 'checked' : '' ?> class="text-emerald-500 focus:ring-emerald-400 bg-slate-900 border-slate-600">
                        </div>
                        <p class="text-[11px] text-slate-300 leading-relaxed">
                            Answers customer inquiries grounded strictly in your verified Knowledge Base (RAG). Adheres to office working hours and automatically requests 5-star feedback ratings when inquiries resolve.
                        </p>
                        <div class="mt-2.5 pt-2 border-t border-slate-700/50 flex items-center gap-3 text-[10px] text-slate-400">
                            <span>⭐ Customer 5-Star Feedback: <strong>Active</strong></span>
                            <span>🕒 Office Hours: <strong>Observed</strong></span>
                        </div>
                    </label>

                    <!-- Option 2: Personal AI Executive Assistant -->
                    <label class="relative flex flex-col p-3.5 rounded-xl border transition-all cursor-pointer <?= ($aiSettings['agent_mode'] ?? 'support') === 'personal' ? 'bg-violet-900/40 border-violet-400/80 ring-1 ring-violet-500/50' : 'bg-slate-800/40 border-slate-700/60 hover:border-slate-600' ?>">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-violet-200">
                                <span>👤</span> Personal AI Assistant (24/7 Always On)
                            </span>
                            <input type="radio" name="agent_mode" value="personal" <?= ($aiSettings['agent_mode'] ?? 'support') === 'personal' ? 'checked' : '' ?> class="text-violet-500 focus:ring-violet-400 bg-slate-900 border-slate-600">
                        </div>
                        <p class="text-[11px] text-slate-300 leading-relaxed">
                            Your 24/7 personal executive secretary. Unrestricted general intelligence (tasks, coding, math, web search, weather forecasts, notes). <strong>Customer reviews & feedback surveys are completely silenced</strong>.
                        </p>
                        <div class="mt-2.5 pt-2 border-t border-slate-700/50 flex items-center gap-3 text-[10px] text-violet-300">
                            <span>🚫 Customer Reviews: <strong>Closed / Silenced</strong></span>
                            <span>⚡ Always On: <strong>24/7 Ready</strong></span>
                        </div>
                    </label>
                </div>

                <!-- Personal Mode Detail Controls -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">Address You As (Owner Name)</label>
                        <input type="text" id="ownerNameInput" name="owner_name" value="<?= e($aiSettings['owner_name'] ?? 'Boss') ?>" placeholder="e.g. Boss, Sir, Ankit" class="w-full text-xs px-3 py-1.5 bg-slate-800 border border-slate-700 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-violet-400">
                    </div>
                    <div class="flex items-center">
                        <label class="flex items-center gap-2 cursor-pointer mt-4">
                            <input type="checkbox" id="feedbackEnabledCheckbox" name="feedback_enabled" value="1" <?= (int) ($aiSettings['feedback_enabled'] ?? 1) === 1 && ($aiSettings['agent_mode'] ?? 'support') !== 'personal' ? 'checked' : '' ?> <?= ($aiSettings['agent_mode'] ?? 'support') === 'personal' ? 'disabled' : '' ?> class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-emerald-500 focus:ring-emerald-400">
                            <span class="text-xs text-slate-300">
                                Request 5-Star Feedback
                                <span id="feedbackNoticeText" class="block text-[10px] <?= ($aiSettings['agent_mode'] ?? 'support') === 'personal' ? 'text-amber-400 font-semibold' : 'text-slate-400' ?>">
                                    <?= ($aiSettings['agent_mode'] ?? 'support') === 'personal' ? '(Closed in Personal Assistant mode)' : '(Sent upon chat closure)' ?>
                                </span>
                            </span>
                        </label>
                    </div>
                    <div class="flex items-center">
                        <label class="flex items-center gap-2 cursor-pointer mt-4">
                            <input type="checkbox" name="is_always_on" value="1" <?= (int) ($aiSettings['is_always_on'] ?? 1) === 1 ? 'checked' : '' ?> class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-emerald-500 focus:ring-emerald-400">
                            <span class="text-xs text-slate-300">
                                24/7 Always Active
                                <span class="block text-[10px] text-slate-400">(Never reports closed hours)</span>
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Toast / Status pill -->
                <div id="modeStatusPill" class="hidden p-2.5 rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-emerald-200 text-xs flex items-center justify-between">
                    <span id="modeStatusText"></span>
                    <button type="button" onclick="document.getElementById('modeStatusPill').classList.add('hidden')" class="text-emerald-400 hover:text-white font-bold ml-2">✕</button>
                </div>
            </div>

            <!-- Master Toggles -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 bg-slate-50 rounded-xl border border-slate-200/80">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="auto_reply_enabled" value="1" <?= (int) ($aiSettings['auto_reply_enabled'] ?? 1) === 1 ? 'checked' : '' ?> class="mt-0.5 w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                    <div>
                        <span class="block text-xs font-bold text-slate-900">Enable Autonomous AI Replies</span>
                        <span class="block text-[11px] text-slate-500">When checked, incoming customer questions are answered automatically by the AI Agent using your Knowledge Base.</span>
                    </div>
                </label>
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="enabled" value="1" <?= (int) ($aiSettings['enabled'] ?? 1) === 1 ? 'checked' : '' ?> class="mt-0.5 w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                    <div>
                        <span class="block text-xs font-bold text-slate-900">Master AI Agent Activation</span>
                        <span class="block text-[11px] text-slate-500">Enables LLM provider initialization and sentiment/frustration analysis.</span>
                    </div>
                </label>
            </div>

            <!-- System Role & Persona Description with AI Generator / Polish -->
            <div class="space-y-2">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-800">
                            System Role & Persona Description
                        </label>
                        <p class="text-[11px] text-slate-500">Write rough notes about your business below (services, hours, delivery, policies). Click <strong>"✨ Rewrite with AI"</strong> to transform it into an enterprise persona.</p>
                    </div>

                    <!-- AI Action Controls -->
                    <div class="flex items-center gap-2 shrink-0">
                        <select id="personaToneSelect" class="text-[11px] font-semibold py-1 px-2.5 bg-slate-100 hover:bg-slate-200/80 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-slate-900 transition-colors cursor-pointer" title="Select persona tone style">
                            <option value="professional_warm">Professional & Warm</option>
                            <option value="friendly_hinglish">Friendly & Hinglish</option>
                            <option value="formal_authoritative">Formal & Corporate</option>
                            <option value="concise_direct">Concise & Direct</option>
                        </select>
                        <button type="button" id="btnEnhancePersona" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white rounded-lg text-xs font-bold shadow-xs transition-all active:scale-[0.98]" title="Transform your rough notes into an enterprise WhatsApp Persona">
                            <span id="enhanceIcon">✨</span>
                            <span id="enhanceLabel">Rewrite with AI</span>
                        </button>
                        <button type="button" id="btnUndoPersona" class="hidden text-[11px] font-semibold text-slate-500 hover:text-slate-800 underline transition-colors">
                            Undo
                        </button>
                    </div>
                </div>

                <div class="relative">
                    <textarea id="systemPromptTextarea" name="system_prompt" rows="7" required class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 shadow-subtle font-sans leading-relaxed custom-scrollbar"><?= e($aiSettings['system_prompt'] ?? '') ?></textarea>
                    
                    <!-- Feedback notification pill -->
                    <div id="personaFeedbackPill" class="hidden mt-1.5 p-2.5 rounded-lg bg-violet-50 border border-violet-200 text-violet-900 text-[11px] flex items-center justify-between shadow-2xs">
                        <div class="flex items-center gap-2">
                            <span class="text-xs">✨</span>
                            <span><strong>Polished by AI (<span id="enhancedProviderName">AI Model</span>):</strong> Persona optimized with closed-domain boundaries, formatting, and Hinglish fluency. Click <strong>"Save AI Agent Settings"</strong> below to apply.</span>
                        </div>
                        <button type="button" onclick="document.getElementById('personaFeedbackPill').classList.add('hidden')" class="text-violet-500 hover:text-violet-800 font-bold ml-2">✕</button>
                    </div>
                </div>
            </div>

            <!-- Provider Priority Cascade -->
            <div>
                <label class="block text-xs font-bold text-slate-800 mb-1">Provider Cascade Priority</label>
                <p class="text-[11px] text-slate-500 mb-2">If your primary provider encounters rate limits or network errors, WhatsGateway automatically cascades to the secondary and tertiary providers in real time.</p>
                <select name="provider_priority" class="w-full sm:w-80 text-xs px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 shadow-subtle font-semibold">
                    <option value="gemini" <?= ($aiSettings['provider_priority'] ?? '') === 'gemini' ? 'selected' : '' ?>>1. Google Gemini ➔ 2. Groq ➔ 3. OpenRouter</option>
                    <option value="groq" <?= ($aiSettings['provider_priority'] ?? '') === 'groq' ? 'selected' : '' ?>>1. Groq (Llama 3.3) ➔ 2. Gemini ➔ 3. OpenRouter</option>
                    <option value="openrouter" <?= ($aiSettings['provider_priority'] ?? '') === 'openrouter' ? 'selected' : '' ?>>1. OpenRouter (Free LLMs) ➔ 2. Gemini ➔ 3. Groq</option>
                </select>
            </div>

            <!-- Provider API Keys Grid -->
            <div class="border border-slate-200/90 rounded-xl p-4 space-y-4 bg-slate-50/50">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Multi-Provider API Keys</h3>

                <!-- Google Gemini -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Google Gemini API Key</label>
                        <input type="password" name="gemini_api_key" placeholder="<?= !empty($aiSettings['gemini_api_key']) ? '•••••••••••••••••••• (Saved)' : 'Enter Google Gemini API Key' ?>" class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-slate-900 focus:outline-none font-mono">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Gemini Model ID</label>
                        <input type="text" name="gemini_model" value="<?= e($aiSettings['gemini_model'] ?? 'gemini-1.5-flash') ?>" class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-slate-900 focus:outline-none font-mono">
                    </div>
                </div>

                <!-- Groq -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Groq API Key</label>
                        <input type="password" name="groq_api_key" placeholder="<?= !empty($aiSettings['groq_api_key']) ? '•••••••••••••••••••• (Saved)' : 'Enter Groq API Key' ?>" class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-slate-900 focus:outline-none font-mono">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Groq Model ID</label>
                        <input type="text" name="groq_model" value="<?= e($aiSettings['groq_model'] ?? 'llama-3.3-70b-versatile') ?>" class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-slate-900 focus:outline-none font-mono">
                    </div>
                </div>

                <!-- OpenRouter -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">OpenRouter API Key (Free Models Supported)</label>
                        <input type="password" name="openrouter_api_key" placeholder="<?= !empty($aiSettings['openrouter_api_key']) ? '•••••••••••••••••••• (Saved)' : 'Enter OpenRouter API Key' ?>" class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-slate-900 focus:outline-none font-mono">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">OpenRouter Model ID</label>
                        <input type="text" name="openrouter_model" value="<?= e($aiSettings['openrouter_model'] ?? 'meta-llama/llama-3.3-70b-instruct:free') ?>" class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-slate-900 focus:outline-none font-mono">
                    </div>
                </div>
            </div>

            <!-- Tuning Parameters -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Temperature (Creativity / Precision)</label>
                    <input type="number" step="0.05" min="0.0" max="1.0" name="temperature" value="<?= e($aiSettings['temperature'] ?? '0.40') ?>" class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-slate-900 focus:outline-none font-mono">
                    <p class="text-[10px] text-slate-400 mt-1">Recommended: 0.2 - 0.4 for factual support grounded strictly in knowledge base.</p>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Max Output Tokens</label>
                    <input type="number" step="50" min="100" max="2000" name="max_tokens" value="<?= e($aiSettings['max_tokens'] ?? 500) ?>" class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-slate-900 focus:outline-none font-mono">
                    <p class="text-[10px] text-slate-400 mt-1">Keeps WhatsApp messages readable and cost-effective.</p>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-200 flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-lg shadow-xs transition-all active:scale-[0.98]">
                    Save AI Agent Settings
                </button>
            </div>
        </form>
    </div>

        <?php elseif ($activeTab === 'knowledge'): ?>
    <!-- ========================================== -->
    <!-- TAB 3: COMPANY KNOWLEDGE BASE (RAG)        -->
    <!-- ========================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Knowledge Chunks List Column -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-blue-50/70 border border-blue-200/80 rounded-xl p-4 text-xs text-blue-900 flex items-start gap-3">
                <span class="text-base">💡</span>
                <div>
                    <strong>Zero-Cloud Vector Retrieval (Option A):</strong>
                    <p class="text-[11px] text-blue-800 mt-0.5 leading-relaxed">Each document chunk below is converted into a high-dimensional vector. When a customer messages your WhatsApp number, our Node.js worker performs in-memory Cosine Similarity ranking in sub-millisecond time and injects only the top-matching factual excerpts into the LLM prompt.</p>
                </div>
            </div>

            <?php if (!empty($knowledgeChunks)): ?>
            <!-- Dynamic Search Bar (Only Search Bar) -->
            <div class="relative w-full">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input 
                    id="knowledgeSearchInput" 
                    type="text" 
                    placeholder="Search knowledge documents by title or keywords..." 
                    autocomplete="off"
                    class="w-full pl-10 pr-9 py-2.5 text-xs border border-slate-200 rounded-xl bg-white shadow-subtle focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 placeholder:text-slate-400 transition-all"
                >
                <button 
                    type="button" 
                    id="knowledgeSearchClear" 
                    class="hidden absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors"
                    title="Clear search"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <?php endif; ?>

            <!-- Knowledge Chunks List Container -->
            <div id="knowledgeChunksList" class="space-y-4">
                <?php foreach ($knowledgeChunks as $chunk): ?>
                    <div class="knowledge-chunk-card bg-white rounded-xl border border-slate-200/80 p-5 shadow-card transition-all"
                         data-id="<?= (int) $chunk['id'] ?>"
                         data-title="<?= e($chunk['title']) ?>">
                        <textarea class="chunk-raw-content hidden"><?= e($chunk['content']) ?></textarea>
                        <div class="flex items-start justify-between gap-3 mb-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                <h3 class="text-sm font-bold text-slate-900 truncate chunk-title"><?= e($chunk['title']) ?></h3>
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                                <button type="button" class="btn-edit-chunk text-slate-400 hover:text-slate-700 hover:bg-slate-100 p-1.5 rounded-lg transition-colors" title="Edit document">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                <form method="post" action="<?= e(base_url('index.php?page=ai-knowledge-delete')) ?>" onsubmit="return confirm('Delete this knowledge chunk?');" class="inline">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $chunk['id'] ?>">
                                    <button type="submit" class="text-rose-500 hover:text-rose-700 hover:bg-rose-50 p-1.5 rounded-lg transition-colors" title="Delete chunk">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="bg-slate-50 border border-slate-200/70 rounded-lg p-3 text-xs text-slate-700 whitespace-pre-wrap font-sans leading-relaxed chunk-content">
                            <?= e($chunk['content']) ?>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-slate-400 font-mono mt-2">
                            <span class="chunk-chars">Characters: <?= strlen($chunk['content']) ?></span>
                            <span>Model: <?= e($chunk['embedding_model'] ?? 'local-tfidf') ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Empty Search Results State -->
            <div id="noKnowledgeSearchResults" class="hidden bg-white border border-slate-200/80 rounded-xl p-8 text-center shadow-card">
                <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center mx-auto mb-2 text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <h3 class="text-sm font-bold text-slate-900">No matching documents</h3>
                <p class="text-xs text-slate-500 mt-0.5">No knowledge chunks match your search query.</p>
                <button type="button" id="btnResetSearch" class="mt-3 text-xs font-semibold text-emerald-600 hover:text-emerald-700 underline">Clear search filter</button>
            </div>

            <!-- Dynamic Pagination Footer -->
            <?php if (!empty($knowledgeChunks)): ?>
            <div id="knowledgePaginationContainer" class="bg-white border border-slate-200/80 rounded-xl p-3 px-4 shadow-card flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                <div id="knowledgePaginationSummary" class="text-slate-500 font-medium">
                    Showing <span id="pagStart" class="font-bold text-slate-900">1</span> to <span id="pagEnd" class="font-bold text-slate-900"><?= min(6, count($knowledgeChunks)) ?></span> of <span id="pagTotal" class="font-bold text-slate-900"><?= count($knowledgeChunks) ?></span> documents
                </div>
                <div class="flex items-center gap-1.5" id="knowledgePaginationControls">
                    <button type="button" id="pagPrevBtn" class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition-all">
                        ← Previous
                    </button>
                    <div id="pagNumberPills" class="flex items-center gap-1">
                        <!-- Dynamically populated page pills -->
                    </div>
                    <button type="button" id="pagNextBtn" class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition-all">
                        Next →
                    </button>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!$knowledgeChunks): ?>
                <div class="bg-white border border-slate-200/80 rounded-xl p-10 text-center shadow-card">
                    <div class="w-12 h-12 bg-slate-50 rounded-xl flex items-center justify-center mx-auto mb-3 border border-slate-100 text-slate-400 text-xl">
                        📖
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 mb-0.5">No Company Knowledge Chunks</h3>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">Add your company FAQs, product catalogs, refund policies, or business hours to ground your AI assistant in truth.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Add Knowledge Chunk Form -->
        <div>
            <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-card sticky top-6">
                <h2 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                    <span class="text-emerald-600">+</span>
                    Add Knowledge Document / FAQ
                </h2>
                <form method="post" action="<?= e(base_url('index.php?page=ai-knowledge-create')) ?>" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Topic / Title</label>
                        <input type="text" name="title" required placeholder="e.g. Return & Refund Policy" class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-slate-900 focus:outline-none font-medium">
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Document Content / Details</label>
                        <textarea name="content" rows="8" required placeholder="Paste full policy text, pricing breakdown, business hours, or support answers..." class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-slate-900 focus:outline-none resize-none font-sans leading-relaxed"></textarea>
                    </div>

                    <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold py-2.5 px-4 rounded-lg shadow-xs transition-all active:scale-[0.98]">
                        Save Knowledge Chunk
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Edit Knowledge Chunk Dialog -->
    <div id="editKnowledgeModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-slate-200 animate-in fade-in zoom-in-95 duration-150 relative">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <h3 class="text-sm font-bold text-slate-900">Edit Knowledge Document</h3>
                </div>
                <button type="button" id="btnCloseEditKnowledgeModal" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <p class="text-xs text-slate-500 mb-4">Update the topic title and content details. The AI Agent will immediately reference this updated knowledge.</p>
            
            <form id="editKnowledgeForm" method="post" action="<?= e(base_url('index.php?page=ai-knowledge-update')) ?>" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="id" id="editKnowledgeId" value="">

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Topic / Title</label>
                    <input type="text" name="title" id="editKnowledgeTitle" required class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-slate-900 focus:outline-none font-medium">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-[11px] font-semibold text-slate-700">Document Content / Details</label>
                        <span id="editKnowledgeCharCount" class="text-[10px] text-slate-400 font-mono">0 characters</span>
                    </div>
                    <textarea name="content" id="editKnowledgeContent" rows="8" required class="w-full text-xs px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-slate-900 focus:outline-none resize-none font-sans leading-relaxed"></textarea>
                </div>

                <div id="editKnowledgeFeedback" class="hidden p-2.5 rounded-lg text-xs font-medium"></div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" id="btnCancelEditKnowledge" class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">Cancel</button>
                    <button type="submit" id="btnSubmitEditKnowledge" class="px-4 py-2 text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white rounded-lg shadow-xs transition-all active:scale-[0.98]">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Floating Toast Notification -->
    <div id="knowledgeToast" class="fixed bottom-6 right-6 z-50 transform translate-y-10 opacity-0 pointer-events-none transition-all duration-200 ease-out flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 text-white text-xs font-medium shadow-xl">
        <span class="text-emerald-400 text-sm">✓</span>
        <span id="knowledgeToastText">Knowledge document updated successfully.</span>
    </div>

    <?php elseif ($activeTab === 'escalations'): ?>
    <!-- ========================================== -->
    <!-- TAB 4: HUMAN ESCALATIONS & BUZZER LOGS     -->
    <!-- ========================================== -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-card overflow-hidden mb-8">
        <div class="p-4 border-b border-slate-200/80 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Recent Escalation Alarms</h2>
                <p class="text-xs text-slate-500">Log of customer frustration triggers, human takeover requests, and buzzer events.</p>
            </div>
            <a href="<?= e(base_url('index.php?page=live-chat')) ?>" class="text-xs font-bold text-emerald-700 hover:text-emerald-800">Go to Live Chat ➔</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200/70 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Timestamp</th>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">Trigger Reason</th>
                        <th class="px-4 py-3">Trigger Excerpt</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($recentEscalations as $esc): ?>
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-4 py-3 text-slate-500 font-mono text-[11px] whitespace-nowrap"><?= e($esc['created_at']) ?></td>
                            <td class="px-4 py-3 font-semibold text-slate-900">
                                <div><?= e($esc['contact_name'] ?: '+' . $esc['phone']) ?></div>
                                <div class="text-[10px] text-slate-400 font-mono">+<?= e($esc['phone']) ?></div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $esc['status'] === 'active' ? 'bg-rose-100 text-rose-800 border border-rose-300' : 'bg-slate-100 text-slate-700' ?>">
                                    <?= e($esc['reason_code']) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600 max-w-xs truncate" title="<?= e($esc['trigger_text'] ?? '') ?>">
                                <?= e($esc['trigger_text'] ?? '—') ?>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $esc['status'] === 'active' ? 'bg-rose-600 text-white' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' ?>">
                                    <?= e(strtoupper($esc['status'])) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$recentEscalations): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                                No escalations recorded yet.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php elseif ($activeTab === 'tools'): ?>
    <!-- ========================================== -->
    <!-- TAB 4: UNIVERSAL CUSTOM TOOLS & RBAC ENGINE-->
    <!-- ========================================== -->
    <div class="space-y-6 mb-8">
        <!-- Engine Status Banner -->
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-xl p-6 text-white shadow-card">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 mb-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Universal Multi-Tenant Tools Engine
                    </div>
                    <h2 class="text-xl font-bold tracking-tight">Autonomous Tools & API Integrations</h2>
                    <p class="text-xs text-slate-300 mt-1 max-w-2xl">
                        Add any external REST API, shared hosting dual-environment backend, or outbound webhook to your WhatsApp AI Agent. The engine compiles parameters dynamically into Gemini function calling with strict role gating.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" onclick="openAiImporterModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white transition-all shadow-md active:scale-[0.98]">
                        <span class="text-sm">✨</span>
                        <span>AI & cURL 1-Click Importer</span>
                    </button>
                    <button type="button" onclick="openPresetsModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold bg-white/10 hover:bg-white/20 text-white border border-white/15 transition-all shadow-xs active:scale-[0.98]">
                        <span>⚡ App Store Presets</span>
                    </button>
                    <button type="button" onclick="openAddToolModal()" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-bold bg-emerald-500 hover:bg-emerald-400 text-slate-950 transition-all shadow-xs active:scale-[0.98]">
                        <span>+ Manual Builder</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Whitelisted Admin Phones Card -->
        <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-card">
            <form method="post" action="<?= e(base_url('index.php?page=ai-tools-config-save')) ?>">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex-1">
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <span>🛡️</span> Whitelisted Admin Phone Numbers
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">WhatsApp numbers permitted to execute privileged administrative actions (e.g. system metrics, CRM lookup):</p>
                        <div class="mt-2 flex gap-2">
                            <input type="text" name="admin_phones" value="<?= e(implode(', ', $toolsConfig['admin_phones'] ?? [])) ?>" class="flex-1 text-xs font-mono px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" placeholder="+14155552671, +919876543210">
                            <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-lg transition-colors shadow-xs active:scale-[0.98]">
                                Save Numbers
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Category Filter Tabs Bar -->
        <div class="flex items-center justify-between gap-3 flex-wrap">
            <div class="flex items-center gap-1.5 overflow-x-auto" id="toolCategoryTabs">
                <button type="button" onclick="filterTools('all', this)" class="tool-cat-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-900 text-white shadow-xs transition-all">
                    All Tools (<?= count($userTools) ?>)
                </button>
                <button type="button" onclick="filterTools('rest_api', this)" class="tool-cat-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-all">
                    Normal REST APIs
                </button>
                <button type="button" onclick="filterTools('shared_env_api', this)" class="tool-cat-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-all">
                    Production APIs
                </button>
                <button type="button" onclick="filterTools('webhook', this)" class="tool-cat-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-all">
                    Webhooks
                </button>
                <button type="button" onclick="filterTools('builtin', this)" class="tool-cat-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-all">
                    Built-in Utilities
                </button>
            </div>
        </div>

        <!-- Grid of Tool Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5" id="toolsCardsGrid">
            <?php foreach ($userTools as $tool): 
                $cat = $tool['category'];
                $paramsCount = count($tool['parameters_schema']['properties'] ?? []);
            ?>
                <div class="tool-card bg-white rounded-xl border border-slate-200/80 p-5 shadow-card flex flex-col justify-between hover:border-slate-300 transition-all" data-category="<?= e($cat) ?>" data-tool="<?= htmlspecialchars(json_encode($tool, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>">
                    <div>
                        <!-- Header: Icon, Category Badge & Active Toggle -->
                        <div class="flex items-start justify-between gap-3 mb-2.5">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider <?= $cat === 'rest_api' ? 'bg-sky-50 text-sky-700 border border-sky-200' : ($cat === 'shared_env_api' ? 'bg-purple-50 text-purple-700 border border-purple-200' : ($cat === 'webhook' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-700 border border-slate-200')) ?>">
                                    <?= $cat === 'rest_api' ? 'Normal REST' : ($cat === 'shared_env_api' ? 'Production API' : ($cat === 'webhook' ? 'Webhook' : 'Built-in')) ?>
                                </span>
                            </div>

                            <form method="post" action="<?= e(base_url('index.php?page=custom-tool-toggle')) ?>" class="inline">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="id" value="<?= (int) $tool['id'] ?>">
                                <button type="submit" class="px-2 py-0.5 rounded text-[11px] font-bold border transition-colors shadow-subtle <?= !empty($tool['is_active']) ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border-slate-200 hover:bg-slate-200' ?>">
                                    <?= !empty($tool['is_active']) ? '● Active' : '○ Disabled' ?>
                                </button>
                            </form>
                        </div>

                        <!-- Tool Title & Slug -->
                        <h4 class="text-sm font-bold text-slate-900 mb-0.5"><?= e($tool['display_name']) ?></h4>
                        <div class="text-[11px] font-mono text-slate-400 mb-2">slug: <span class="text-slate-700 font-semibold"><?= e($tool['tool_slug']) ?></span></div>

                        <!-- Description -->
                        <p class="text-xs text-slate-600 line-clamp-2 mb-3 leading-relaxed"><?= e($tool['description']) ?></p>

                        <!-- Execution Spec Pill -->
                        <div class="bg-slate-50 rounded-lg p-2.5 border border-slate-100 mb-3 space-y-1 text-[11px]">
                            <?php if ($cat === 'rest_api'): ?>
                                <div class="flex items-center gap-1.5 truncate">
                                    <span class="px-1.5 py-0.2 rounded font-bold font-mono text-[9px] bg-sky-100 text-sky-800"><?= e($tool['execution_config']['method'] ?? 'GET') ?></span>
                                    <span class="text-slate-600 font-mono truncate" title="<?= e($tool['execution_config']['url'] ?? '') ?>"><?= e($tool['execution_config']['url'] ?? '') ?></span>
                                </div>
                            <?php elseif ($cat === 'shared_env_api'): ?>
                                <div class="flex items-center gap-1.5 text-slate-600 font-mono text-[11px] truncate">
                                    <span class="px-1.5 py-0.2 rounded font-bold font-mono text-[9px] bg-emerald-100 text-emerald-800">PROD</span>
                                    <span class="truncate" title="<?= e($tool['execution_config']['base_url'] ?? $tool['execution_config']['environments']['live']['base_url'] ?? '') ?>"><?= e($tool['execution_config']['base_url'] ?? $tool['execution_config']['environments']['live']['base_url'] ?? 'Production API') ?></span>
                                </div>
                            <?php elseif ($cat === 'webhook'): ?>
                                <div class="truncate text-slate-600 font-mono">
                                    POST <?= e($tool['execution_config']['target_url'] ?? '') ?>
                                </div>
                            <?php else: ?>
                                <div class="text-slate-600">Handler: <span class="font-mono font-bold text-slate-800"><?= e($tool['execution_config']['handler'] ?? $tool['tool_slug']) ?></span></div>
                            <?php endif; ?>
                            <div class="text-[10px] text-slate-400">Parameters: <span class="font-bold text-slate-700"><?= $paramsCount ?> input fields</span></div>
                        </div>

                        <!-- Role Chips -->
                        <div class="flex items-center gap-1 flex-wrap mb-4">
                            <span class="text-[10px] text-slate-400 font-medium mr-1">Access:</span>
                            <?php foreach (($tool['rbac_roles'] ?? []) as $r): ?>
                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold font-mono <?= $r === 'admin' ? 'bg-purple-100 text-purple-800' : ($r === 'customer' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700') ?>">
                                    <?= strtoupper($r) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                        <button type="button" onclick="handleToolTest(this)" class="px-2.5 py-1 text-xs font-bold text-slate-700 hover:text-slate-900 hover:bg-slate-100 rounded-md transition-colors flex items-center gap-1">
                            <span>🧪</span> Test
                        </button>

                        <div class="flex items-center gap-1.5">
                            <button type="button" onclick="handleToolEdit(this)" class="px-2.5 py-1 text-xs font-bold text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-md transition-colors">
                                Edit
                            </button>
                            <form method="post" action="<?= e(base_url('index.php?page=custom-tool-delete')) ?>" onsubmit="return confirm('Delete this tool from your catalog?');" class="inline">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="id" value="<?= (int) $tool['id'] ?>">
                                <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 rounded hover:bg-rose-50 transition-colors" title="Delete tool">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Audit Trail Table -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-card overflow-hidden mt-8">
            <div class="p-4 border-b border-slate-200/80 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Recent AI Tool Invocations (Live Audit Log)</h3>
                    <p class="text-xs text-slate-500">Autonomous tool calls executed by the WhatsApp AI Agent with caller roles and latencies.</p>
                </div>
                <span class="text-xs font-mono text-slate-400"><?= count($recentInvocations) ?> logged events</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200/70 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="px-4 py-3">Timestamp</th>
                            <th class="px-4 py-3">Caller Phone</th>
                            <th class="px-4 py-3">Role</th>
                            <th class="px-4 py-3">Tool Invocated</th>
                            <th class="px-4 py-3">Latency</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($recentInvocations as $inv): ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-4 py-3 text-slate-500 font-mono text-[11px] whitespace-nowrap"><?= e($inv['created_at']) ?></td>
                                <td class="px-4 py-3 font-mono text-slate-900"><?= e($inv['caller_phone']) ?></td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $inv['caller_role'] === 'admin' ? 'bg-purple-100 text-purple-800 border border-purple-200' : ($inv['caller_role'] === 'customer' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700') ?>">
                                        <?= strtoupper($inv['caller_role']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-mono text-slate-800 font-semibold"><?= e($inv['tool_name']) ?></td>
                                <td class="px-4 py-3 text-slate-500 font-mono text-[11px]"><?= (int) $inv['execution_time_ms'] ?>ms</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $inv['status'] === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($inv['status'] === 'denied' ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-rose-100 text-rose-800') ?>">
                                        <?= strtoupper($inv['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (!$recentInvocations): ?>
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                                    No tool invocations recorded yet. Inbound messages triggering external actions will be logged here.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL 1: ADD / EDIT CUSTOM TOOL -->
    <div id="customToolModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <h3 id="customToolModalTitle" class="text-base font-bold text-slate-900">Add Custom Tool</h3>
                <button type="button" onclick="closeToolModal()" class="text-slate-400 hover:text-slate-700 text-lg">✕</button>
            </div>

            <form method="post" action="<?= e(base_url('index.php?page=custom-tool-save')) ?>" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="tool_id" id="modalToolId" value="0">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tool Slug (identifier for AI) *</label>
                        <input type="text" name="tool_slug" id="modalToolSlug" required placeholder="e.g. get_weather, check_order" class="w-full text-xs font-mono px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Display Title *</label>
                        <input type="text" name="display_name" id="modalDisplayName" required placeholder="e.g. Real-Time Weather Forecaster" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Integration Category *</label>
                        <select name="category" id="modalCategory" onchange="toggleCategoryFields(this.value)" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <option value="rest_api">Normal REST API (Single Endpoint)</option>
                            <option value="shared_env_api">Production API (Action Routes & Bearer Auth)</option>
                            <option value="webhook">Outbound Webhook</option>
                            <option value="builtin">Built-in Utility</option>
                        </select>
                    </div>
                    <div class="flex items-center pt-5">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" id="modalIsActive" value="1" checked class="rounded text-emerald-600 focus:ring-emerald-500">
                            <span class="text-xs font-bold text-slate-800">Activate tool immediately</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">AI Prompt Description *</label>
                    <p class="text-[11px] text-slate-500 mb-1">Instruct the AI exactly when and why it should call this tool:</p>
                    <textarea name="description" id="modalDescription" rows="2" required placeholder="Fetches current weather and conditions for any city or location..." class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 resize-none"></textarea>
                </div>

                <!-- CATEGORY FIELDS: NORMAL REST API -->
                <div id="fieldsRestApi" class="space-y-3 p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                    <div class="text-xs font-bold text-slate-900">🌐 Normal REST API Configuration</div>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                        <div class="sm:col-span-1">
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Method</label>
                            <select name="rest_method" id="modalRestMethod" class="w-full text-xs px-2.5 py-1.5 border border-slate-200 rounded-lg bg-white">
                                <option value="GET">GET</option>
                                <option value="POST">POST</option>
                                <option value="PUT">PUT</option>
                                <option value="DELETE">DELETE</option>
                            </select>
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Target URL (supports {param} placeholders) *</label>
                            <input type="text" name="rest_url" id="modalRestUrl" placeholder="https://api.open-meteo.com/v1/forecast?latitude={latitude}&longitude={longitude}" class="w-full text-xs font-mono px-3 py-1.5 border border-slate-200 rounded-lg">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Authorization Header (optional)</label>
                            <input type="text" name="rest_auth_header" id="modalRestAuth" placeholder="Bearer your_secret_token" class="w-full text-xs font-mono px-3 py-1.5 border border-slate-200 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Response Path Filter (optional)</label>
                            <input type="text" name="rest_response_path" id="modalRestPath" placeholder="e.g. current or data.results" class="w-full text-xs font-mono px-3 py-1.5 border border-slate-200 rounded-lg">
                        </div>
                    </div>
                </div>

                <!-- CATEGORY FIELDS: PRODUCTION API -->
                <div id="fieldsSharedEnv" class="space-y-3 p-3.5 bg-purple-50/50 rounded-xl border border-purple-200 hidden">
                    <div class="flex items-center justify-between">
                        <div class="text-xs font-bold text-purple-950 flex items-center gap-1.5">
                            <span>🏢</span> Production API Configuration
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-emerald-100 text-emerald-800">
                            PRODUCTION ONLY
                        </span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Production Base URL *</label>
                            <input type="text" name="shared_live_url" id="modalSharedLiveUrl" placeholder="https://assistgroup.gt.tc/api/admin/v1" class="w-full text-xs font-mono px-3 py-1.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Production Bearer Token / API Key</label>
                            <input type="password" name="shared_live_token" id="modalSharedLiveToken" placeholder="ast_live_..." class="w-full text-xs font-mono px-3 py-1.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                        </div>
                    </div>
                </div>

                <!-- CATEGORY FIELDS: WEBHOOK -->
                <div id="fieldsWebhook" class="space-y-3 p-3.5 bg-amber-50/50 rounded-xl border border-amber-200 hidden">
                    <div class="text-xs font-bold text-amber-950">🎣 Outbound Webhook Configuration</div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Target Webhook URL (POST)</label>
                        <input type="text" name="webhook_url" id="modalWebhookUrl" placeholder="https://hooks.zapier.com/hooks/catch/..." class="w-full text-xs font-mono px-3 py-1.5 border border-slate-200 rounded-lg">
                    </div>
                </div>

                <!-- DYNAMIC PARAMETER BUILDER -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold text-slate-800">Dynamic Tool Parameters</label>
                        <button type="button" onclick="addParameterRow()" class="text-xs font-bold text-emerald-600 hover:text-emerald-700">
                            + Add Parameter
                        </button>
                    </div>
                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200 text-[10px] uppercase">
                                <tr>
                                    <th class="px-3 py-2">Name</th>
                                    <th class="px-3 py-2">Type</th>
                                    <th class="px-3 py-2">Description for AI</th>
                                    <th class="px-3 py-2 text-center">Req?</th>
                                    <th class="px-2 py-2"></th>
                                </tr>
                            </thead>
                            <tbody id="parameterRowsContainer" class="divide-y divide-slate-100">
                                <!-- Dynamic Rows Injected by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- RBAC ROLES SELECTION -->
                <div class="pt-2">
                    <label class="block text-xs font-bold text-slate-800 mb-1.5">Permitted Caller Roles (RBAC)</label>
                    <div class="flex items-center gap-4 text-xs">
                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" name="roles[]" value="guest" id="modalRoleGuest" checked class="rounded text-emerald-600">
                            <span>Public Guest</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" name="roles[]" value="customer" id="modalRoleCustomer" checked class="rounded text-blue-600">
                            <span>Customer</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" name="roles[]" value="admin" id="modalRoleAdmin" checked class="rounded text-purple-600">
                            <span class="font-bold">Admin Only</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeToolModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white rounded-lg shadow-xs">
                        Save Tool
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: 1-CLICK APP STORE PRESETS GALLERY -->
    <div id="presetsModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl max-w-3xl w-full p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 mb-1">
                        <span>⚡ 1-Click Integrations</span>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">Pre-Built App Store Catalog</h3>
                </div>
                <button type="button" onclick="closePresetsModal()" class="text-slate-400 hover:text-slate-700 text-lg">✕</button>
            </div>
            <p class="text-xs text-slate-500 mb-4">Select any pre-configured integration below to instantly populate parameters, execution URLs, and AI tool descriptions:</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                <!-- Preset 1: AssistGroup Enterprise Suite -->
                <button type="button" onclick="loadPresetAssistGroup()" class="text-left p-4 rounded-xl border border-slate-200 hover:border-purple-500 hover:bg-purple-50/20 transition-all group relative">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xl">🏢</span>
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-purple-100 text-purple-800 border border-purple-200">PRODUCTION CRM</span>
                    </div>
                    <div class="text-xs font-bold text-slate-900 group-hover:text-purple-700">AssistGroup Enterprise API</div>
                    <div class="text-[11px] text-slate-500 mt-1 leading-relaxed">Connects your live AssistGroup backend (inquiries, user lookup, system health).</div>
                </button>

                <!-- Preset 2: Weather API -->
                <button type="button" onclick="loadPresetWeather()" class="text-left p-4 rounded-xl border border-slate-200 hover:border-sky-500 hover:bg-sky-50/20 transition-all group relative">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xl">🌤️</span>
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-sky-100 text-sky-800 border border-sky-200">FREE / ZERO-KEY</span>
                    </div>
                    <div class="text-xs font-bold text-slate-900 group-hover:text-sky-700">Live Weather Forecaster</div>
                    <div class="text-[11px] text-slate-500 mt-1 leading-relaxed">Real-time global weather, temperature, humidity, and forecast using Open-Meteo.</div>
                </button>

                <!-- Preset 3: Crypto Price Tracker -->
                <button type="button" onclick="loadPresetCrypto()" class="text-left p-4 rounded-xl border border-slate-200 hover:border-amber-500 hover:bg-amber-50/20 transition-all group relative">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xl">🪙</span>
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-amber-100 text-amber-800 border border-amber-200">FREE / ZERO-KEY</span>
                    </div>
                    <div class="text-xs font-bold text-slate-900 group-hover:text-amber-700">Crypto Price Tracker</div>
                    <div class="text-[11px] text-slate-500 mt-1 leading-relaxed">Live CoinGecko prices for Bitcoin, Ethereum, Solana, and altcoins in INR/USD.</div>
                </button>

                <!-- Preset 4: E-Commerce & Shipment Tracker -->
                <button type="button" onclick="loadPresetEcommerce()" class="text-left p-4 rounded-xl border border-slate-200 hover:border-emerald-500 hover:bg-emerald-50/20 transition-all group relative">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xl">📦</span>
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">ORDERS & COURIER</span>
                    </div>
                    <div class="text-xs font-bold text-slate-900 group-hover:text-emerald-700">E-Commerce Shipment Tracker</div>
                    <div class="text-[11px] text-slate-500 mt-1 leading-relaxed">Tracks courier shipments, waybills, and order status (Shiprocket / Delhivery compatible).</div>
                </button>

                <!-- Preset 5: Razorpay Payment Status -->
                <button type="button" onclick="loadPresetRazorpay()" class="text-left p-4 rounded-xl border border-slate-200 hover:border-blue-500 hover:bg-blue-50/20 transition-all group relative">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xl">💳</span>
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-blue-100 text-blue-800 border border-blue-200">PAYMENTS</span>
                    </div>
                    <div class="text-xs font-bold text-slate-900 group-hover:text-blue-700">Razorpay Payment Status</div>
                    <div class="text-[11px] text-slate-500 mt-1 leading-relaxed">Verifies live customer payment status, capture states, and invoice IDs.</div>
                </button>

                <!-- Preset 6: Lead Webhook -->
                <button type="button" onclick="loadPresetWebhook()" class="text-left p-4 rounded-xl border border-slate-200 hover:border-orange-500 hover:bg-orange-50/20 transition-all group relative">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xl">🎣</span>
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-orange-100 text-orange-800 border border-orange-200">OUTBOUND WEBHOOK</span>
                    </div>
                    <div class="text-xs font-bold text-slate-900 group-hover:text-orange-700">CRM Lead Webhook (Zapier/Make)</div>
                    <div class="text-[11px] text-slate-500 mt-1 leading-relaxed">Dispatches captured lead name, phone, and requirements directly to your CRM.</div>
                </button>

                <!-- Preset 7: Human Email Dispatch -->
                <button type="button" onclick="loadPresetEmailAlert()" class="text-left p-4 rounded-xl border border-slate-200 hover:border-rose-500 hover:bg-rose-50/20 transition-all group relative">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xl">📧</span>
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-rose-100 text-rose-800 border border-rose-200">HITL ESCALATION</span>
                    </div>
                    <div class="text-xs font-bold text-slate-900 group-hover:text-rose-700">Human Support Email Alert</div>
                    <div class="text-[11px] text-slate-500 mt-1 leading-relaxed">Sends priority email notification to support reps when customer needs escalation.</div>
                </button>

                <!-- Preset 8: Web Knowledge Search -->
                <button type="button" onclick="loadPresetWebSearch()" class="text-left p-4 rounded-xl border border-slate-200 hover:border-indigo-500 hover:bg-indigo-50/20 transition-all group relative">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xl">🌐</span>
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">AUTONOMOUS SEARCH</span>
                    </div>
                    <div class="text-xs font-bold text-slate-900 group-hover:text-indigo-700">DuckDuckGo Knowledge Search</div>
                    <div class="text-[11px] text-slate-500 mt-1 leading-relaxed">Autonomous real-time search engine for answering factual and business inquiries.</div>
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL 4: AI & CURL 1-CLICK SMART IMPORTER -->
    <div id="aiImporterModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl max-w-3xl w-full p-6 shadow-2xl max-h-[92vh] overflow-y-auto">
            <!-- Modal Header -->
            <div class="flex items-start justify-between pb-4 border-b border-slate-100 mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-violet-600 to-indigo-500 flex items-center justify-center text-white text-lg shadow-md">
                        ✨
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-bold text-slate-900">AI & cURL 1-Click Tool Creator</h3>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-violet-50 text-violet-700 border border-violet-200">
                                Copilot v3.0
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Paste any raw cURL command, API docs, or plain English prompt to instantly generate an autonomous tool.</p>
                    </div>
                </div>
                <button type="button" onclick="closeAiImporterModal()" class="text-slate-400 hover:text-slate-700 text-lg">✕</button>
            </div>

            <!-- Tabs: cURL vs. Prompt vs. Docs -->
            <div class="flex items-center gap-2 border-b border-slate-200/80 pb-2 mb-4">
                <button type="button" onclick="switchImporterTab('curl')" id="tabBtnCurl" class="importer-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-900 text-white shadow-xs transition-all">
                    📋 Paste cURL Command
                </button>
                <button type="button" onclick="switchImporterTab('prompt')" id="tabBtnPrompt" class="importer-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-all">
                    🤖 Plain English AI Prompt
                </button>
                <button type="button" onclick="switchImporterTab('docs')" id="tabBtnDocs" class="importer-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-all">
                    📄 API Documentation / JSON
                </button>
            </div>

            <!-- TAB 1: cURL Input -->
            <div id="importerPanelCurl" class="space-y-3">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <label class="block text-xs font-bold text-slate-700">Paste Raw cURL Command:</label>
                    <div class="flex items-center gap-1.5 text-[11px]">
                        <span class="text-slate-400">Quick Samples:</span>
                        <button type="button" onclick="fillCurlExample('shiprocket')" class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 rounded text-slate-700 font-medium">📦 Shiprocket</button>
                        <button type="button" onclick="fillCurlExample('weather')" class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 rounded text-slate-700 font-medium">🌤️ Weather</button>
                        <button type="button" onclick="fillCurlExample('assistgroup')" class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 rounded text-slate-700 font-medium">🏢 AssistGroup</button>
                    </div>
                </div>
                <textarea id="aiImporterCurlInput" rows="5" class="w-full text-xs font-mono px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 resize-none" placeholder="curl -X GET &quot;https://api.example.com/v1/orders/{order_id}&quot; \
  -H &quot;Authorization: Bearer YOUR_TOKEN&quot; \
  -H &quot;Accept: application/json&quot;"></textarea>
            </div>

            <!-- TAB 2: AI Prompt Input -->
            <div id="importerPanelPrompt" class="space-y-3 hidden">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <label class="block text-xs font-bold text-slate-700">Describe What Your Tool Should Do in Plain English:</label>
                    <div class="flex items-center gap-1.5 text-[11px]">
                        <span class="text-slate-400">Quick Prompts:</span>
                        <button type="button" onclick="fillPromptExample('delhivery')" class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 rounded text-slate-700 font-medium">📦 Delhivery Tracking</button>
                        <button type="button" onclick="fillPromptExample('razorpay')" class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 rounded text-slate-700 font-medium">💳 Razorpay Status</button>
                        <button type="button" onclick="fillPromptExample('currency')" class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 rounded text-slate-700 font-medium">💱 Currency Rate</button>
                    </div>
                </div>
                <textarea id="aiImporterPromptInput" rows="4" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 resize-none" placeholder="e.g. Build an API tool to track an order shipment from Delhivery courier using customer waybill tracking number..."></textarea>
            </div>

            <!-- TAB 3: Docs Input -->
            <div id="importerPanelDocs" class="space-y-3 hidden">
                <label class="block text-xs font-bold text-slate-700">Paste API Documentation Snippet or Sample Response JSON:</label>
                <textarea id="aiImporterDocsInput" rows="5" class="w-full text-xs font-mono px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 resize-none" placeholder="GET /v1/payments/{payment_id}
Authorization: Bearer <token>
Response: { &quot;id&quot;: &quot;pay_123&quot;, &quot;status&quot;: &quot;captured&quot;, &quot;amount&quot;: 50000 }"></textarea>
            </div>

            <!-- Generation Trigger Action -->
            <div class="mt-4 flex items-center justify-between pt-3 border-t border-slate-100">
                <div class="text-[11px] text-slate-400 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Multi-Provider Synthesis (Groq / Gemini Function Calling Engine)</span>
                </div>
                <button type="button" id="btnAiGenerate" onclick="generateToolFromAI()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white shadow-md active:scale-[0.98] transition-all">
                    <span>✨ Analyze & Generate Tool</span>
                </button>
            </div>

            <!-- Loading State -->
            <div id="aiImporterLoading" class="py-8 text-center space-y-3 hidden">
                <div class="inline-block animate-spin text-3xl">🌀</div>
                <div class="text-xs font-bold text-slate-800" id="aiImporterLoadingText">Synthesizing Tool Definition with AI...</div>
                <p class="text-[11px] text-slate-500 max-w-sm mx-auto">Extracting HTTP methods, parameters, and generating Gemini & Groq function calling schema.</p>
            </div>

            <!-- GENERATED PREVIEW CARD -->
            <div id="aiImporterResult" class="mt-6 border border-slate-200 rounded-2xl p-5 bg-slate-50/70 hidden space-y-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span id="resToolCategory" class="px-2 py-0.5 rounded text-[10px] font-bold bg-sky-100 text-sky-800 uppercase">REST API</span>
                            <span id="resToolSource" class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">AI Synthesized</span>
                        </div>
                        <h4 id="resDisplayName" class="text-sm font-bold text-slate-900">📦 Delhivery Shipment Tracker</h4>
                        <div class="text-[11px] font-mono text-slate-400">slug: <span id="resToolSlug" class="text-slate-700 font-semibold">delhivery_track</span></div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="testGeneratedTool()" id="btnTestGenerated" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-white hover:bg-slate-100 text-slate-800 border border-slate-200 shadow-xs flex items-center gap-1 transition-colors">
                            <span>🧪</span> Test Call
                        </button>
                    </div>
                </div>

                <!-- Description -->
                <p id="resDescription" class="text-xs text-slate-600 leading-relaxed bg-white p-3 rounded-xl border border-slate-200/70"></p>

                <!-- Execution Specs -->
                <div class="bg-white p-3 rounded-xl border border-slate-200/70 space-y-2 text-xs font-mono">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span id="resMethod" class="px-2 py-0.5 rounded font-bold text-[10px] bg-slate-900 text-white">GET</span>
                        <span id="resUrl" class="text-slate-700 break-all">https://api.example.com/...</span>
                    </div>
                    <div id="resHeadersBox" class="text-[11px] text-slate-500 truncate"></div>
                </div>

                <!-- Dynamic Parameters Table -->
                <div class="bg-white rounded-xl border border-slate-200/70 overflow-hidden">
                    <div class="p-2.5 bg-slate-100/70 border-b border-slate-200/70 text-[11px] font-bold text-slate-700 flex items-center justify-between">
                        <span>Dynamic Function Parameters (<span id="resParamCount">0</span>)</span>
                        <span class="text-[10px] text-slate-400">Compiled to Gemini Function Calling</span>
                    </div>
                    <table class="w-full text-left text-xs">
                        <thead class="text-[10px] font-bold text-slate-400 uppercase bg-slate-50/50">
                            <tr>
                                <th class="px-3 py-2">Parameter</th>
                                <th class="px-3 py-2">Type</th>
                                <th class="px-3 py-2">Description for AI</th>
                                <th class="px-3 py-2 text-center">Required</th>
                            </tr>
                        </thead>
                        <tbody id="resParamsTableBody" class="divide-y divide-slate-100 font-mono text-[11px]"></tbody>
                    </table>
                </div>

                <!-- Test Execution Sandbox Result (Live In-Modal) -->
                <div id="importerTestBox" class="bg-slate-950 rounded-xl p-4 text-xs font-mono text-emerald-400 hidden space-y-2">
                    <div class="flex items-center justify-between text-[11px] text-slate-400 border-b border-slate-800 pb-2">
                        <span class="font-bold text-white flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            Live Test Execution Result:
                        </span>
                        <span id="importerTestMeta" class="text-emerald-400 font-bold">200 OK (142ms)</span>
                    </div>
                    <pre id="importerTestOutput" class="max-h-48 overflow-y-auto whitespace-pre-wrap text-[11px]"></pre>
                </div>

                <!-- Bottom Actions -->
                <div class="flex items-center justify-between pt-3 border-t border-slate-200/80">
                    <button type="button" onclick="closeAiImporterModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-200 rounded-lg">
                        Close
                    </button>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="applyGeneratedToolToBuilder()" class="px-4 py-2 rounded-lg text-xs font-bold bg-white hover:bg-slate-100 text-slate-800 border border-slate-200 shadow-xs">
                            ✏️ Review in Manual Builder
                        </button>
                        <button type="button" onclick="saveGeneratedToolDirectly()" class="px-5 py-2 rounded-lg text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white shadow-xs">
                            💾 Save & Activate Tool Now
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 3: INTERACTIVE SANDBOX TESTER -->
    <div id="sandboxModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span>🧪</span> Tool Test Sandbox Simulator
                </h3>
                <button type="button" onclick="closeSandboxModal()" class="text-slate-400 hover:text-slate-700 text-lg">✕</button>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Testing Tool:</label>
                    <div id="sandboxToolTitle" class="text-xs font-bold font-mono text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200">
                        get_weather
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Sample Arguments (JSON format):</label>
                    <textarea id="sandboxArgs" rows="4" class="w-full text-xs font-mono px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 resize-none font-sans"></textarea>
                </div>

                <button type="button" id="btnRunSandbox" onclick="runSandboxTest()" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-lg transition-colors shadow-xs active:scale-[0.98]">
                    ▶ Execute Live Test
                </button>

                <div id="sandboxResultBox" class="hidden">
                    <div class="flex items-center justify-between text-xs font-bold text-slate-700 mb-1">
                        <span>Execution Output:</span>
                        <span id="sandboxLatency" class="font-mono text-emerald-600">0ms</span>
                    </div>
                    <pre id="sandboxOutput" class="text-[11px] font-mono bg-slate-950 text-emerald-400 p-3 rounded-xl overflow-x-auto max-h-48 whitespace-pre-wrap"></pre>
                </div>
            </div>
        </div>
    </div>

    <!-- Universal Custom Tools Script (Self-Contained & Always Active) -->
    <script>
    (function initUniversalTools() {
        window.filterTools = function(category, btn) {
            document.querySelectorAll('.tool-cat-btn').forEach(b => {
                b.className = 'tool-cat-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-all';
            });
            if (btn) {
                btn.className = 'tool-cat-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-900 text-white shadow-xs transition-all';
            }

            document.querySelectorAll('.tool-card').forEach(card => {
                if (category === 'all' || card.getAttribute('data-category') === category) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        };

        window.toggleCategoryFields = function(cat) {
            const fRest = document.getElementById('fieldsRestApi');
            const fShared = document.getElementById('fieldsSharedEnv');
            const fWeb = document.getElementById('fieldsWebhook');
            if (fRest) fRest.classList.toggle('hidden', cat !== 'rest_api');
            if (fShared) fShared.classList.toggle('hidden', cat !== 'shared_env_api');
            if (fWeb) fWeb.classList.toggle('hidden', cat !== 'webhook');
        };

        window.addParameterRow = function(name = '', type = 'STRING', desc = '', isRequired = false) {
            const container = document.getElementById('parameterRowsContainer');
            if (!container) return;
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50/60';
            tr.innerHTML = `
                <td class="px-3 py-2">
                    <input type="text" name="param_name[]" value="${name}" placeholder="e.g. city" required class="w-full text-xs font-mono px-2 py-1 border border-slate-200 rounded">
                </td>
                <td class="px-3 py-2">
                    <select name="param_type[]" class="text-xs px-2 py-1 border border-slate-200 rounded bg-white font-mono">
                        <option value="STRING" ${type === 'STRING' ? 'selected' : ''}>STRING</option>
                        <option value="NUMBER" ${type === 'NUMBER' ? 'selected' : ''}>NUMBER</option>
                        <option value="INTEGER" ${type === 'INTEGER' ? 'selected' : ''}>INTEGER</option>
                        <option value="BOOLEAN" ${type === 'BOOLEAN' ? 'selected' : ''}>BOOLEAN</option>
                    </select>
                </td>
                <td class="px-3 py-2">
                    <input type="text" name="param_desc[]" value="${desc}" placeholder="Instruction for AI..." class="w-full text-xs px-2 py-1 border border-slate-200 rounded">
                </td>
                <td class="px-3 py-2 text-center">
                    <input type="checkbox" name="param_required[]" value="1" ${isRequired ? 'checked' : ''} class="rounded text-emerald-600">
                </td>
                <td class="px-2 py-2 text-right">
                    <button type="button" onclick="this.closest('tr').remove()" class="text-slate-400 hover:text-rose-600 text-xs font-bold">✕</button>
                </td>
            `;
            container.appendChild(tr);
        };

        window.openAddToolModal = function() {
            document.getElementById('customToolModalTitle').textContent = 'Add Custom Tool';
            document.getElementById('modalToolId').value = '0';
            document.getElementById('modalToolSlug').value = '';
            document.getElementById('modalToolSlug').readOnly = false;
            document.getElementById('modalDisplayName').value = '';
            document.getElementById('modalDescription').value = '';
            document.getElementById('modalCategory').value = 'rest_api';
            document.getElementById('modalRestUrl').value = '';
            document.getElementById('modalRestAuth').value = '';
            document.getElementById('modalRestPath').value = '';
            document.getElementById('parameterRowsContainer').innerHTML = '';
            toggleCategoryFields('rest_api');
            document.getElementById('customToolModal').classList.remove('hidden');
        };

        window.handleToolEdit = function(btn) {
            try {
                const card = btn.closest('.tool-card');
                const raw = card.getAttribute('data-tool');
                const tool = JSON.parse(raw);
                openEditToolModal(tool);
            } catch (e) {
                console.error('Error opening edit modal:', e);
            }
        };

        window.openEditToolModal = function(tool) {
            document.getElementById('customToolModalTitle').textContent = 'Edit Tool: ' + (tool.display_name || tool.tool_slug);
            document.getElementById('modalToolId').value = tool.id || 0;
            document.getElementById('modalToolSlug').value = tool.tool_slug || '';
            document.getElementById('modalDisplayName').value = tool.display_name || '';
            document.getElementById('modalDescription').value = tool.description || '';
            document.getElementById('modalCategory').value = tool.category || 'rest_api';
            toggleCategoryFields(tool.category || 'rest_api');

            if (tool.category === 'rest_api') {
                document.getElementById('modalRestMethod').value = tool.execution_config?.method || 'GET';
                document.getElementById('modalRestUrl').value = tool.execution_config?.url || '';
                document.getElementById('modalRestAuth').value = tool.execution_config?.headers?.Authorization || '';
                document.getElementById('modalRestPath').value = tool.execution_config?.response_path || '';
            } else if (tool.category === 'shared_env_api') {
                document.getElementById('modalSharedLiveUrl').value = tool.execution_config?.base_url || tool.execution_config?.environments?.live?.base_url || tool.execution_config?.environments?.production?.base_url || '';
                document.getElementById('modalSharedLiveToken').value = tool.execution_config?.bearer_token || tool.execution_config?.environments?.live?.bearer_token || tool.execution_config?.environments?.production?.bearer_token || '';
            } else if (tool.category === 'webhook') {
                document.getElementById('modalWebhookUrl').value = tool.execution_config?.target_url || '';
            }

            // Populate parameters
            const container = document.getElementById('parameterRowsContainer');
            container.innerHTML = '';
            const props = tool.parameters_schema?.properties || {};
            const req = tool.parameters_schema?.required || [];
            for (const [pName, pSpec] of Object.entries(props)) {
                addParameterRow(pName, pSpec.type || 'STRING', pSpec.description || '', req.includes(pName));
            }

            document.getElementById('customToolModal').classList.remove('hidden');
        };

        window.closeToolModal = function() {
            document.getElementById('customToolModal').classList.add('hidden');
        };

        window.openPresetsModal = function() {
            document.getElementById('presetsModal').classList.remove('hidden');
        };

        window.closePresetsModal = function() {
            document.getElementById('presetsModal').classList.add('hidden');
        };

        // ==========================================
        // AI & cURL 1-Click Importer Logic
        // ==========================================
        let activeImporterTab = 'curl';
        let currentGeneratedTool = null;

        window.openAiImporterModal = function() {
            document.getElementById('aiImporterModal').classList.remove('hidden');
            switchImporterTab('curl');
            document.getElementById('aiImporterResult').classList.add('hidden');
            document.getElementById('aiImporterLoading').classList.add('hidden');
        };

        window.closeAiImporterModal = function() {
            document.getElementById('aiImporterModal').classList.add('hidden');
        };

        window.switchImporterTab = function(tab) {
            activeImporterTab = tab;
            ['curl', 'prompt', 'docs'].forEach(t => {
                const btn = document.getElementById('tabBtn' + t.charAt(0).toUpperCase() + t.slice(1));
                const panel = document.getElementById('importerPanel' + t.charAt(0).toUpperCase() + t.slice(1));
                if (btn) {
                    if (t === tab) {
                        btn.className = 'importer-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-900 text-white shadow-xs transition-all';
                    } else {
                        btn.className = 'importer-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-all';
                    }
                }
                if (panel) {
                    panel.classList.toggle('hidden', t !== tab);
                }
            });
        };

        window.fillCurlExample = function(type) {
            const input = document.getElementById('aiImporterCurlInput');
            if (type === 'shiprocket') {
                input.value = `curl -X GET "https://apiv2.shiprocket.in/v1/external/courier/track/awb?awb_code=9876543210" \\
  -H "Authorization: Bearer YOUR_SHIPROCKET_TOKEN" \\
  -H "Accept: application/json"`;
            } else if (type === 'weather') {
                input.value = `curl -X GET "https://api.open-meteo.com/v1/forecast?latitude=25.59&longitude=85.14&current=temperature_2m,weather_code" \\
  -H "Accept: application/json"`;
            } else if (type === 'assistgroup') {
                input.value = `curl -X GET "https://assistgroup.gt.tc/api/admin/v1/system" \\
  -H "Authorization: Bearer ast_live_demo_token" \\
  -H "Accept: application/json"`;
            }
        };

        window.fillPromptExample = function(type) {
            const input = document.getElementById('aiImporterPromptInput');
            if (type === 'delhivery') {
                input.value = 'Track courier delivery packages from Delhivery courier using customer waybill tracking number';
            } else if (type === 'razorpay') {
                input.value = 'Check live payment status and amount captured from Razorpay using payment_id';
            } else if (type === 'currency') {
                input.value = 'Query current live currency exchange rate from USD to INR using free exchangerate API';
            }
        };

        window.generateToolFromAI = async function() {
            const btn = document.getElementById('btnAiGenerate');
            const loading = document.getElementById('aiImporterLoading');
            const resultBox = document.getElementById('aiImporterResult');
            const loadingText = document.getElementById('aiImporterLoadingText');

            let rawContent = '';
            let inputType = activeImporterTab;

            if (activeImporterTab === 'curl') {
                rawContent = document.getElementById('aiImporterCurlInput').value.trim();
            } else if (activeImporterTab === 'prompt') {
                rawContent = document.getElementById('aiImporterPromptInput').value.trim();
            } else {
                rawContent = document.getElementById('aiImporterDocsInput').value.trim();
            }

            if (!rawContent) {
                alert('Please enter a cURL command, prompt, or API docs snippet first.');
                return;
            }

            btn.disabled = true;
            btn.classList.add('opacity-50');
            loading.classList.remove('hidden');
            resultBox.classList.add('hidden');
            document.getElementById('importerTestBox').classList.add('hidden');
            loadingText.textContent = activeImporterTab === 'curl' 
                ? 'Parsing cURL parameters & synthesizing schema...' 
                : 'Synthesizing Tool Definition with AI Agent...';

            try {
                const res = await fetch('<?= e(base_url('api/tools/ai-generate-tool.php')) ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ raw_content: rawContent, input_type: inputType }),
                    credentials: 'same-origin'
                });

                const data = await res.json();
                if (!res.ok || !data.ok || !data.tool) {
                    throw new Error(data.error || 'Failed to generate tool definition.');
                }

                currentGeneratedTool = data.tool;
                renderGeneratedPreview(data.tool, data.source || 'AI Copilot');
                resultBox.classList.remove('hidden');
                resultBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } catch (err) {
                alert('Generation Error: ' + err.message);
            } finally {
                btn.disabled = false;
                btn.classList.remove('opacity-50');
                loading.classList.add('hidden');
            }
        };

        function renderGeneratedPreview(tool, source) {
            document.getElementById('resToolCategory').textContent = (tool.category || 'rest_api').replace('_', ' ').toUpperCase();
            document.getElementById('resToolSource').textContent = source;
            document.getElementById('resDisplayName').textContent = tool.display_name || 'Custom Tool';
            document.getElementById('resToolSlug').textContent = tool.tool_slug || 'custom_tool';
            document.getElementById('resDescription').textContent = tool.description || '';

            const exec = tool.execution_config || {};
            document.getElementById('resMethod').textContent = (exec.method || 'GET').toUpperCase();
            document.getElementById('resUrl').textContent = exec.url || exec.base_url || exec.target_url || '';

            const headers = exec.headers || {};
            const headersStr = Object.entries(headers).map(([k, v]) => `${k}: ${v}`).join(' | ');
            const hBox = document.getElementById('resHeadersBox');
            if (headersStr) {
                hBox.textContent = 'Headers: ' + headersStr;
                hBox.classList.remove('hidden');
            } else {
                hBox.classList.add('hidden');
            }

            const params = Array.isArray(tool.parameters) ? tool.parameters : [];
            document.getElementById('resParamCount').textContent = params.length;

            const tbody = document.getElementById('resParamsTableBody');
            tbody.innerHTML = '';
            if (params.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="px-3 py-3 text-center text-slate-400">No dynamic parameters required for this endpoint.</td></tr>';
            } else {
                params.forEach(p => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-slate-50/70';
                    tr.innerHTML = `
                        <td class="px-3 py-2 font-bold text-slate-900">${escapeHtml(p.name || '')}</td>
                        <td class="px-3 py-2"><span class="px-1.5 py-0.5 rounded text-[9px] bg-slate-100 text-slate-700">${escapeHtml(p.type || 'STRING')}</span></td>
                        <td class="px-3 py-2 text-slate-600 font-sans">${escapeHtml(p.description || '')}</td>
                        <td class="px-3 py-2 text-center">${p.required ? '<span class="text-rose-600 font-bold">YES</span>' : '<span class="text-slate-400">NO</span>'}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        }

        window.testGeneratedTool = async function() {
            if (!currentGeneratedTool) return;
            const btn = document.getElementById('btnTestGenerated');
            const testBox = document.getElementById('importerTestBox');
            const testMeta = document.getElementById('importerTestMeta');
            const testOutput = document.getElementById('importerTestOutput');

            btn.disabled = true;
            btn.innerHTML = '<span>⏳</span> Testing...';
            testBox.classList.remove('hidden');
            testMeta.textContent = 'Contacting server...';
            testOutput.textContent = 'Executing server-side cURL proxy test...';

            // Formulate sample args from parameters
            const sampleArgs = {};
            const params = Array.isArray(currentGeneratedTool.parameters) ? currentGeneratedTool.parameters : [];
            params.forEach(p => {
                const k = p.name || '';
                if (k.includes('lat')) sampleArgs[k] = 25.5941;
                else if (k.includes('lon')) sampleArgs[k] = 85.1376;
                else if (k.includes('city') || k === 'q') sampleArgs[k] = 'Patna';
                else if (k.includes('order')) sampleArgs[k] = 'ORD-98765';
                else if (k.includes('waybill') || k.includes('awb')) sampleArgs[k] = '9876543210';
                else if (k.includes('payment') || k.includes('pay_')) sampleArgs[k] = 'pay_test123';
                else if (k.includes('phone')) sampleArgs[k] = '919507286092';
                else if (k.includes('action')) sampleArgs[k] = 'system';
                else sampleArgs[k] = 'test_value';
            });

            const startTime = performance.now();
            try {
                const res = await fetch('<?= e(base_url('api/tools/test-tool.php')) ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        category: currentGeneratedTool.category || 'rest_api',
                        execution_config: currentGeneratedTool.execution_config || {},
                        args: sampleArgs
                    }),
                    credentials: 'same-origin'
                });

                const data = await res.json();
                const latency = Math.round(performance.now() - startTime);

                if (data.ok) {
                    testMeta.className = 'text-emerald-400 font-bold';
                    testMeta.textContent = `HTTP ${data.status || 200} OK (${data.latency_ms || latency}ms)`;
                } else {
                    testMeta.className = 'text-rose-400 font-bold';
                    testMeta.textContent = `HTTP ${data.status || 400} Error (${data.latency_ms || latency}ms)`;
                }

                testOutput.textContent = JSON.stringify(data.data || data, null, 2);
            } catch (err) {
                testMeta.className = 'text-rose-400 font-bold';
                testMeta.textContent = 'Network Error';
                testOutput.textContent = 'Error: ' + err.message;
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<span>🧪</span> Test Call';
            }
        };

        window.applyGeneratedToolToBuilder = function() {
            if (!currentGeneratedTool) return;
            closeAiImporterModal();
            openAddToolModal();

            document.getElementById('modalToolSlug').value = currentGeneratedTool.tool_slug || '';
            document.getElementById('modalDisplayName').value = currentGeneratedTool.display_name || '';
            document.getElementById('modalDescription').value = currentGeneratedTool.description || '';
            document.getElementById('modalCategory').value = currentGeneratedTool.category || 'rest_api';
            toggleCategoryFields(currentGeneratedTool.category || 'rest_api');

            const exec = currentGeneratedTool.execution_config || {};
            if (currentGeneratedTool.category === 'rest_api') {
                document.getElementById('modalRestMethod').value = exec.method || 'GET';
                document.getElementById('modalRestUrl').value = exec.url || '';
                document.getElementById('modalRestAuth').value = exec.headers?.Authorization || '';
                document.getElementById('modalRestPath').value = exec.response_path || '';
            } else if (currentGeneratedTool.category === 'shared_env_api') {
                document.getElementById('modalSharedLiveUrl').value = exec.base_url || '';
                document.getElementById('modalSharedLiveToken').value = exec.bearer_token || '';
            } else if (currentGeneratedTool.category === 'webhook') {
                document.getElementById('modalWebhookUrl').value = exec.target_url || '';
            }

            const container = document.getElementById('parameterRowsContainer');
            container.innerHTML = '';
            const params = Array.isArray(currentGeneratedTool.parameters) ? currentGeneratedTool.parameters : [];
            params.forEach(p => {
                addParameterRow(p.name || '', p.type || 'STRING', p.description || '', !!p.required);
            });
        };

        window.saveGeneratedToolDirectly = function() {
            if (!currentGeneratedTool) return;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= e(base_url('index.php?page=custom-tool-save')) ?>';

            const addField = (name, val) => {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = name;
                inp.value = val;
                form.appendChild(inp);
            };

            addField('csrf_token', '<?= e(csrf_token()) ?>');
            addField('tool_id', '0');
            addField('tool_slug', currentGeneratedTool.tool_slug || '');
            addField('display_name', currentGeneratedTool.display_name || '');
            addField('category', currentGeneratedTool.category || 'rest_api');
            addField('description', currentGeneratedTool.description || '');
            addField('is_active', '1');

            const exec = currentGeneratedTool.execution_config || {};
            if (currentGeneratedTool.category === 'rest_api') {
                addField('rest_method', exec.method || 'GET');
                addField('rest_url', exec.url || '');
                if (exec.headers?.Authorization) {
                    addField('rest_auth_header', exec.headers.Authorization);
                }
                addField('rest_response_path', exec.response_path || '');
            } else if (currentGeneratedTool.category === 'shared_env_api') {
                addField('shared_live_url', exec.base_url || '');
                addField('shared_live_token', exec.bearer_token || '');
            } else if (currentGeneratedTool.category === 'webhook') {
                addField('webhook_url', exec.target_url || '');
            }

            const params = Array.isArray(currentGeneratedTool.parameters) ? currentGeneratedTool.parameters : [];
            params.forEach(p => {
                addField('param_name[]', p.name || '');
                addField('param_type[]', p.type || 'STRING');
                addField('param_desc[]', p.description || '');
                if (p.required) {
                    addField('param_required[]', '1');
                } else {
                    addField('param_required[]', '0');
                }
            });

            addField('roles[]', 'guest');
            addField('roles[]', 'customer');
            addField('roles[]', 'admin');

            document.body.appendChild(form);
            form.submit();
        };

        function escapeHtml(str) {
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        // ==========================================
        // 8 App Store Preset Loaders
        // ==========================================
        window.loadPresetAssistGroup = function() {
            closePresetsModal();
            openAddToolModal();
            document.getElementById('modalToolSlug').value = 'assistgroup_crm';
            document.getElementById('modalDisplayName').value = '🏢 AssistGroup Enterprise Suite';
            document.getElementById('modalCategory').value = 'shared_env_api';
            document.getElementById('modalDescription').value = 'Connects to AssistGroup Live CRM & Admin Suite to inspect system health, recent customer inquiries, and user status.';
            toggleCategoryFields('shared_env_api');
            document.getElementById('modalSharedLiveUrl').value = 'https://assistgroup.gt.tc/api/admin/v1';
            document.getElementById('modalSharedLiveToken').value = '';
            addParameterRow('action', 'STRING', 'CRM endpoint action: "system", "health", "inquiries", or "users"', true);
        };

        window.loadPresetWeather = function() {
            closePresetsModal();
            openAddToolModal();
            document.getElementById('modalToolSlug').value = 'get_weather';
            document.getElementById('modalDisplayName').value = '🌤️ Live Weather Forecaster';
            document.getElementById('modalCategory').value = 'rest_api';
            document.getElementById('modalDescription').value = 'Fetches real-time weather, temperature, humidity, apparent temperature, and wind speed for any location or city using coordinates.';
            document.getElementById('modalRestUrl').value = 'https://api.open-meteo.com/v1/forecast?latitude={latitude}&longitude={longitude}&current=temperature_2m,relative_humidity_2m,apparent_temperature,weather_code,wind_speed_10m';
            document.getElementById('modalRestPath').value = 'current';
            toggleCategoryFields('rest_api');
            addParameterRow('city', 'STRING', 'City name (e.g. "Patna", "Delhi", "London")', true);
            addParameterRow('latitude', 'NUMBER', 'Latitude coordinate (e.g. 25.59 for Patna)', true);
            addParameterRow('longitude', 'NUMBER', 'Longitude coordinate (e.g. 85.14 for Patna)', true);
        };

        window.loadPresetCrypto = function() {
            closePresetsModal();
            openAddToolModal();
            document.getElementById('modalToolSlug').value = 'get_crypto_price';
            document.getElementById('modalDisplayName').value = '🪙 Crypto Price Tracker';
            document.getElementById('modalCategory').value = 'rest_api';
            document.getElementById('modalDescription').value = 'Queries current live market prices for cryptocurrencies like bitcoin, ethereum, solana in INR and USD.';
            document.getElementById('modalRestUrl').value = 'https://api.coingecko.com/api/v3/simple/price?ids={coin_id}&vs_currencies=inr,usd';
            toggleCategoryFields('rest_api');
            addParameterRow('coin_id', 'STRING', 'Cryptocurrency id e.g. "bitcoin", "ethereum", "solana"', true);
        };

        window.loadPresetEcommerce = function() {
            closePresetsModal();
            openAddToolModal();
            document.getElementById('modalToolSlug').value = 'track_shipment';
            document.getElementById('modalDisplayName').value = '📦 Courier Shipment Tracker';
            document.getElementById('modalCategory').value = 'rest_api';
            document.getElementById('modalDescription').value = 'Checks current delivery status, carrier location, and expected delivery date using courier tracking AWB or order ID.';
            document.getElementById('modalRestUrl').value = 'https://apiv2.shiprocket.in/v1/external/courier/track/awb?awb_code={awb_code}';
            document.getElementById('modalRestAuth').value = 'Bearer YOUR_COURIER_TOKEN';
            document.getElementById('modalRestPath').value = 'tracking_data';
            toggleCategoryFields('rest_api');
            addParameterRow('awb_code', 'STRING', 'Courier waybill or tracking code (e.g. 9876543210)', true);
        };

        window.loadPresetRazorpay = function() {
            closePresetsModal();
            openAddToolModal();
            document.getElementById('modalToolSlug').value = 'check_razorpay_payment';
            document.getElementById('modalDisplayName').value = '💳 Razorpay Payment Status';
            document.getElementById('modalCategory').value = 'rest_api';
            document.getElementById('modalDescription').value = 'Verifies live payment status, amount, and capture state from Razorpay using payment_id.';
            document.getElementById('modalRestUrl').value = 'https://api.razorpay.com/v1/payments/{payment_id}';
            document.getElementById('modalRestAuth').value = 'Basic YOUR_BASE64_KEY_SECRET';
            document.getElementById('modalRestPath').value = '';
            toggleCategoryFields('rest_api');
            addParameterRow('payment_id', 'STRING', 'Razorpay payment ID (e.g. pay_29QQoUBi66xm2f)', true);
        };

        window.loadPresetWebhook = function() {
            closePresetsModal();
            openAddToolModal();
            document.getElementById('modalToolSlug').value = 'send_crm_lead';
            document.getElementById('modalDisplayName').value = '🎣 CRM Lead Outbound Webhook';
            document.getElementById('modalCategory').value = 'webhook';
            document.getElementById('modalDescription').value = 'Forwards interested customer leads, name, phone, and requirements directly to external CRM webhook.';
            document.getElementById('modalWebhookUrl').value = 'https://hooks.zapier.com/hooks/catch/12345/abcde';
            toggleCategoryFields('webhook');
            addParameterRow('customer_name', 'STRING', 'Full name of customer', true);
            addParameterRow('customer_phone', 'STRING', 'Contact phone number', true);
            addParameterRow('notes', 'STRING', 'Summary of customer requirements', false);
        };

        window.loadPresetEmailAlert = function() {
            closePresetsModal();
            openAddToolModal();
            document.getElementById('modalToolSlug').value = 'draft_email';
            document.getElementById('modalDisplayName').value = '📧 Human Support Email Alert';
            document.getElementById('modalCategory').value = 'builtin';
            document.getElementById('modalDescription').value = 'Prepares an email alert to the human support team when customer needs escalation or formal follow-up.';
            toggleCategoryFields('builtin');
            addParameterRow('recipient_email', 'STRING', 'Target email address for notification', true);
            addParameterRow('subject', 'STRING', 'Subject line of email alert', true);
            addParameterRow('body_text', 'STRING', 'Detailed customer notes or inquiry body', true);
        };

        window.loadPresetWebSearch = function() {
            closePresetsModal();
            openAddToolModal();
            document.getElementById('modalToolSlug').value = 'web_search';
            document.getElementById('modalDisplayName').value = '🌐 DuckDuckGo Knowledge Search';
            document.getElementById('modalCategory').value = 'builtin';
            document.getElementById('modalDescription').value = 'Performs live web search on DuckDuckGo to answer questions regarding current events, product specs, or external facts.';
            toggleCategoryFields('builtin');
            addParameterRow('query', 'STRING', 'Search query string', true);
        };

        // Upgraded Sandbox Simulator Modal (Server-Side Proxy - Zero CORS block)
        let activeSandboxTool = null;
        window.handleToolTest = function(btn) {
            try {
                const card = btn.closest('.tool-card');
                const raw = card.getAttribute('data-tool');
                const tool = JSON.parse(raw);
                openSandboxModal(tool);
            } catch (e) {
                console.error('Error opening test sandbox:', e);
            }
        };

        window.openSandboxModal = function(tool) {
            activeSandboxTool = tool;
            document.getElementById('sandboxToolTitle').textContent = (tool.tool_slug || 'tool') + ' (' + (tool.category || 'rest_api') + ')';
            const sampleArgs = {};
            const props = tool.parameters_schema?.properties || {};
            for (const [k, v] of Object.entries(props)) {
                if (k.includes('lat')) sampleArgs[k] = 25.5941;
                else if (k.includes('lon')) sampleArgs[k] = 85.1376;
                else if (k.includes('city') || k === 'q') sampleArgs[k] = 'Patna';
                else if (k.includes('query')) sampleArgs[k] = 'Latest tech news';
                else if (k.includes('expression')) sampleArgs[k] = '35000 * 0.82';
                else if (k.includes('coin')) sampleArgs[k] = 'bitcoin';
                else if (k.includes('order')) sampleArgs[k] = 'ORD-12345';
                else if (k.includes('action')) sampleArgs[k] = 'system';
                else if (k.includes('payment') || k.includes('pay_')) sampleArgs[k] = 'pay_29QQoUBi66xm2f';
                else if (k.includes('phone')) sampleArgs[k] = '919507286092';
                else sampleArgs[k] = 'test_value';
            }
            document.getElementById('sandboxArgs').value = JSON.stringify(sampleArgs, null, 2);
            document.getElementById('sandboxResultBox').classList.add('hidden');
            document.getElementById('sandboxModal').classList.remove('hidden');
        };

        window.closeSandboxModal = function() {
            document.getElementById('sandboxModal').classList.add('hidden');
        };

        window.runSandboxTest = async function() {
            if (!activeSandboxTool) return;
            const btn = document.getElementById('btnRunSandbox');
            const output = document.getElementById('sandboxOutput');
            const latencyEl = document.getElementById('sandboxLatency');
            const resultBox = document.getElementById('sandboxResultBox');

            let parsedArgs = {};
            try {
                parsedArgs = JSON.parse(document.getElementById('sandboxArgs').value);
            } catch (e) {
                alert('Invalid JSON in arguments textarea.');
                return;
            }

            btn.disabled = true;
            btn.textContent = '⏳ Executing Live Server Test...';
            resultBox.classList.remove('hidden');
            output.textContent = 'Sending server-side execution request...';

            const startTime = performance.now();
            try {
                const res = await fetch('<?= e(base_url('api/tools/test-tool.php')) ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        category: activeSandboxTool.category || 'rest_api',
                        execution_config: activeSandboxTool.execution_config || {},
                        args: parsedArgs
                    }),
                    credentials: 'same-origin'
                });

                const data = await res.json();
                const latency = Math.round(performance.now() - startTime);

                if (data.ok) {
                    latencyEl.className = 'font-mono text-emerald-600 font-bold';
                    latencyEl.textContent = `${data.latency_ms || latency}ms (HTTP ${data.status || 200})`;
                } else {
                    latencyEl.className = 'font-mono text-rose-600 font-bold';
                    latencyEl.textContent = `${data.latency_ms || latency}ms (HTTP ${data.status || 400} Error)`;
                }

                output.textContent = JSON.stringify(data.data || data, null, 2);
            } catch (err) {
                const latency = Math.round(performance.now() - startTime);
                latencyEl.className = 'font-mono text-rose-600 font-bold';
                latencyEl.textContent = `${latency}ms (Network Error)`;
                output.textContent = 'Test failed: ' + err.message;
            } finally {
                btn.disabled = false;
                btn.textContent = '▶ Execute Live Test';
            }
        };
    })();
    </script>
    <?php endif; ?>

    <!-- Persona AI Rewrite Client Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // ==========================================
        // Knowledge Base: Search, Pagination & Edit
        // ==========================================
        (function initKnowledgeBase() {
            const searchInput = document.getElementById('knowledgeSearchInput');
            const searchClear = document.getElementById('knowledgeSearchClear');
            const btnResetSearch = document.getElementById('btnResetSearch');
            const cardsList = document.getElementById('knowledgeChunksList');
            const noResults = document.getElementById('noKnowledgeSearchResults');
            const pagContainer = document.getElementById('knowledgePaginationContainer');
            const pagStart = document.getElementById('pagStart');
            const pagEnd = document.getElementById('pagEnd');
            const pagTotal = document.getElementById('pagTotal');
            const pagPrevBtn = document.getElementById('pagPrevBtn');
            const pagNextBtn = document.getElementById('pagNextBtn');
            const pagNumberPills = document.getElementById('pagNumberPills');

            const editModal = document.getElementById('editKnowledgeModal');
            const editForm = document.getElementById('editKnowledgeForm');
            const editId = document.getElementById('editKnowledgeId');
            const editTitle = document.getElementById('editKnowledgeTitle');
            const editContent = document.getElementById('editKnowledgeContent');
            const editCharCount = document.getElementById('editKnowledgeCharCount');
            const editFeedback = document.getElementById('editKnowledgeFeedback');
            const btnCloseModal = document.getElementById('btnCloseEditKnowledgeModal');
            const btnCancelModal = document.getElementById('btnCancelEditKnowledge');
            const btnSubmit = document.getElementById('btnSubmitEditKnowledge');

            const toast = document.getElementById('knowledgeToast');
            const toastText = document.getElementById('knowledgeToastText');

            if (!cardsList) return;

            const cards = Array.from(cardsList.querySelectorAll('.knowledge-chunk-card'));
            if (cards.length === 0) return;

            const PAGE_SIZE = 6;
            let currentPage = 1;
            let currentFilterCards = [...cards];
            let currentEditingCard = null;

            function showToast(msg) {
                if (!toast || !toastText) return;
                toastText.textContent = msg;
                toast.classList.remove('translate-y-10', 'opacity-0', 'pointer-events-none');
                setTimeout(() => {
                    toast.classList.add('translate-y-10', 'opacity-0', 'pointer-events-none');
                }, 3000);
            }

            function renderPagination() {
                const total = currentFilterCards.length;
                const totalPages = Math.max(1, Math.ceil(total / PAGE_SIZE));

                if (currentPage > totalPages) {
                    currentPage = totalPages;
                }

                const startIndex = (currentPage - 1) * PAGE_SIZE;
                const endIndex = Math.min(startIndex + PAGE_SIZE, total);

                cards.forEach(card => {
                    const indexInFiltered = currentFilterCards.indexOf(card);
                    if (indexInFiltered >= startIndex && indexInFiltered < endIndex) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });

                if (total === 0) {
                    if (noResults) noResults.classList.remove('hidden');
                    if (pagContainer) pagContainer.classList.add('hidden');
                } else {
                    if (noResults) noResults.classList.add('hidden');
                    if (pagContainer) {
                        pagContainer.classList.remove('hidden');
                        if (pagStart) pagStart.textContent = String(startIndex + 1);
                        if (pagEnd) pagEnd.textContent = String(endIndex);
                        if (pagTotal) pagTotal.textContent = String(total);

                        if (pagPrevBtn) {
                            pagPrevBtn.disabled = (currentPage === 1);
                        }
                        if (pagNextBtn) {
                            pagNextBtn.disabled = (currentPage === totalPages);
                        }

                        if (pagNumberPills) {
                            pagNumberPills.innerHTML = '';
                            for (let p = 1; p <= totalPages; p++) {
                                if (totalPages > 7) {
                                    if (p !== 1 && p !== totalPages && Math.abs(p - currentPage) > 2) {
                                        if (p === 2 || p === totalPages - 1) {
                                            const dot = document.createElement('span');
                                            dot.className = 'px-1 text-slate-400 font-bold select-none text-[11px]';
                                            dot.textContent = '…';
                                            pagNumberPills.appendChild(dot);
                                        }
                                        continue;
                                    }
                                }

                                const btn = document.createElement('button');
                                btn.type = 'button';
                                btn.className = (p === currentPage)
                                    ? 'w-7 h-7 flex items-center justify-center rounded-lg text-xs font-bold bg-slate-900 text-white shadow-xs'
                                    : 'w-7 h-7 flex items-center justify-center rounded-lg text-xs font-medium text-slate-600 hover:bg-slate-100 transition-colors';
                                btn.textContent = String(p);
                                btn.addEventListener('click', () => {
                                    currentPage = p;
                                    renderPagination();
                                    scrollToSearch();
                                });
                                pagNumberPills.appendChild(btn);
                            }
                        }
                    }
                }
            }

            function scrollToSearch() {
                if (searchInput) {
                    const rect = searchInput.getBoundingClientRect();
                    if (rect.top < 0 || rect.bottom > window.innerHeight) {
                        searchInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            }

            if (pagPrevBtn) {
                pagPrevBtn.addEventListener('click', () => {
                    if (currentPage > 1) {
                        currentPage--;
                        renderPagination();
                        scrollToSearch();
                    }
                });
            }

            if (pagNextBtn) {
                pagNextBtn.addEventListener('click', () => {
                    const totalPages = Math.ceil(currentFilterCards.length / PAGE_SIZE);
                    if (currentPage < totalPages) {
                        currentPage++;
                        renderPagination();
                        scrollToSearch();
                    }
                });
            }

            // Dynamic Search Filter
            function applySearch() {
                const query = (searchInput ? searchInput.value : '').toLowerCase().trim();
                if (searchClear) {
                    if (query !== '') {
                        searchClear.classList.remove('hidden');
                    } else {
                        searchClear.classList.add('hidden');
                    }
                }

                if (query === '') {
                    currentFilterCards = [...cards];
                } else {
                    currentFilterCards = cards.filter(card => {
                        const title = (card.getAttribute('data-title') || '').toLowerCase();
                        const rawContent = (card.querySelector('.chunk-raw-content')?.value || '').toLowerCase();
                        return title.includes(query) || rawContent.includes(query);
                    });
                }

                currentPage = 1;
                renderPagination();
            }

            if (searchInput) {
                searchInput.addEventListener('input', applySearch);
            }

            if (searchClear) {
                searchClear.addEventListener('click', () => {
                    if (searchInput) {
                        searchInput.value = '';
                        applySearch();
                        searchInput.focus();
                    }
                });
            }

            if (btnResetSearch) {
                btnResetSearch.addEventListener('click', () => {
                    if (searchInput) {
                        searchInput.value = '';
                        applySearch();
                        searchInput.focus();
                    }
                });
            }

            // Initial render
            renderPagination();

            // Edit modal open/close
            function openEditModal(card) {
                currentEditingCard = card;
                const id = card.getAttribute('data-id') || '';
                const title = card.getAttribute('data-title') || '';
                const content = card.querySelector('.chunk-raw-content')?.value || '';

                editId.value = id;
                editTitle.value = title;
                editContent.value = content;
                if (editCharCount) {
                    editCharCount.textContent = content.length + ' characters';
                }
                if (editFeedback) {
                    editFeedback.className = 'hidden p-2.5 rounded-lg text-xs font-medium';
                    editFeedback.textContent = '';
                }

                editModal.classList.remove('hidden');
                editTitle.focus();
            }

            function closeEditModal() {
                if (editModal) {
                    editModal.classList.add('hidden');
                }
                currentEditingCard = null;
            }

            cards.forEach(card => {
                const editBtn = card.querySelector('.btn-edit-chunk');
                if (editBtn) {
                    editBtn.addEventListener('click', () => openEditModal(card));
                }
            });

            if (editContent && editCharCount) {
                editContent.addEventListener('input', () => {
                    editCharCount.textContent = editContent.value.length + ' characters';
                });
            }

            if (btnCloseModal) btnCloseModal.addEventListener('click', closeEditModal);
            if (btnCancelModal) btnCancelModal.addEventListener('click', closeEditModal);

            if (editModal) {
                editModal.addEventListener('click', (e) => {
                    if (e.target === editModal) closeEditModal();
                });
            }

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && editModal && !editModal.classList.contains('hidden')) {
                    closeEditModal();
                }
            });

            // Handle Edit form submission with AJAX and fallback
            if (editForm) {
                editForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    const submitBtn = btnSubmit;
                    const originalBtnHtml = submitBtn ? submitBtn.innerHTML : 'Save Changes';

                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = `<svg class="w-3.5 h-3.5 animate-spin text-white inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Saving...`;
                    }

                    try {
                        const formData = new FormData(editForm);
                        const res = await fetch(editForm.action, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
                        });

                        const data = await res.json();
                        if (!res.ok || !data.success) {
                            throw new Error(data.error || 'Failed to update knowledge chunk.');
                        }

                        // Update card in DOM
                        if (currentEditingCard && data.chunk) {
                            currentEditingCard.setAttribute('data-title', data.chunk.title);
                            const titleEl = currentEditingCard.querySelector('.chunk-title');
                            if (titleEl) titleEl.textContent = data.chunk.title;

                            const rawEl = currentEditingCard.querySelector('.chunk-raw-content');
                            if (rawEl) rawEl.value = data.chunk.content;

                            const contentEl = currentEditingCard.querySelector('.chunk-content');
                            if (contentEl) contentEl.textContent = data.chunk.content;

                            const charsEl = currentEditingCard.querySelector('.chunk-chars');
                            if (charsEl) charsEl.textContent = 'Characters: ' + data.chunk.char_count;
                        }

                        closeEditModal();
                        showToast('Knowledge document updated successfully.');
                        applySearch();

                    } catch (err) {
                        if (editFeedback) {
                            editFeedback.className = 'p-2.5 rounded-lg text-xs font-medium bg-rose-50 text-rose-800 border border-rose-200 block';
                            editFeedback.textContent = err.message || 'Error updating document.';
                        } else {
                            alert(err.message);
                        }
                    } finally {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalBtnHtml;
                        }
                    }
                });
            }
        })();

    
        const btnEnhance = document.getElementById('btnEnhancePersona');
        (function initPersona() {
        const btnUndo = document.getElementById('btnUndoPersona');
        const textarea = document.getElementById('systemPromptTextarea');
        const toneSelect = document.getElementById('personaToneSelect');
        const feedbackPill = document.getElementById('personaFeedbackPill');
        const providerSpan = document.getElementById('enhancedProviderName');
        const enhanceIcon = document.getElementById('enhanceIcon');
        const enhanceLabel = document.getElementById('enhanceLabel');

        let previousText = '';

        if (!btnEnhance || !textarea) return;

        btnEnhance.addEventListener('click', async function() {
            const rawText = textarea.value.trim();
            if (!rawText) {
                alert('Please write some rough details about your business in the text box first.');
                textarea.focus();
                return;
            }

            previousText = textarea.value;

            // Loading state
            btnEnhance.disabled = true;
            enhanceIcon.innerHTML = `<svg class="w-3.5 h-3.5 animate-spin text-white inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>`;
            enhanceLabel.textContent = 'Polishing...';

            try {
                const basePath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'));
                const res = await fetch(`${basePath}/api/chat/enhance-prompt.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        raw_text: rawText,
                        tone: toneSelect ? toneSelect.value : 'professional_warm'
                    })
                });

                const data = await res.json();
                if (!data.ok) throw new Error(data.error || 'Failed to enhance persona prompt.');

                textarea.value = data.enhanced_prompt;
                if (providerSpan && data.provider) {
                    providerSpan.textContent = data.provider;
                }
                if (feedbackPill) {
                    feedbackPill.classList.remove('hidden');
                }
                if (btnUndo) {
                    btnUndo.classList.remove('hidden');
                }

                // Adjust rows height dynamically
                textarea.rows = Math.min(18, Math.max(8, data.enhanced_prompt.split('\n').length + 2));
            } catch (err) {
                alert('AI Polish Error: ' + err.message);
            } finally {
                btnEnhance.disabled = false;
                enhanceIcon.textContent = '✨';
                enhanceLabel.textContent = 'Rewrite with AI';
            }
        });

        if (btnUndo) {
            btnUndo.addEventListener('click', function() {
                if (previousText) {
                    textarea.value = previousText;
                    btnUndo.classList.add('hidden');
                    if (feedbackPill) feedbackPill.classList.add('hidden');
                }
            });
        }
    })();

    (function initModeSwitcher() {
        const btnToPersonal = document.getElementById('btnConvertToPersonal');
        const btnToSupport = document.getElementById('btnSwitchToSupport');
        const modeRadios = document.querySelectorAll('input[name="agent_mode"]');
        const textarea = document.getElementById('systemPromptTextarea');
        const feedbackCheckbox = document.getElementById('feedbackEnabledCheckbox');
        const feedbackNotice = document.getElementById('feedbackNoticeText');
        const ownerNameInput = document.getElementById('ownerNameInput');
        const modeBadge = document.getElementById('activeModeBadge');
        const statusPill = document.getElementById('modeStatusPill');
        const statusText = document.getElementById('modeStatusText');

        if (!btnToPersonal || !btnToSupport) return;

        function updateModeUI(mode) {
            modeRadios.forEach(r => {
                r.checked = (r.value === mode);
                const card = r.closest('label');
                if (card) {
                    if (r.value === mode) {
                        if (mode === 'personal') {
                            card.className = 'relative flex flex-col p-3.5 rounded-xl border transition-all cursor-pointer bg-violet-900/40 border-violet-400/80 ring-1 ring-violet-500/50';
                        } else {
                            card.className = 'relative flex flex-col p-3.5 rounded-xl border transition-all cursor-pointer bg-indigo-900/40 border-emerald-500/80 ring-1 ring-emerald-500/50';
                        }
                    } else {
                        card.className = 'relative flex flex-col p-3.5 rounded-xl border transition-all cursor-pointer bg-slate-800/40 border-slate-700/60 hover:border-slate-600';
                    }
                }
            });

            if (mode === 'personal') {
                if (feedbackCheckbox) {
                    feedbackCheckbox.checked = false;
                    feedbackCheckbox.disabled = true;
                }
                if (feedbackNotice) {
                    feedbackNotice.textContent = '(Closed in Personal Assistant mode)';
                    feedbackNotice.className = 'block text-[10px] text-amber-400 font-semibold';
                }
                if (modeBadge) {
                    modeBadge.textContent = '⚡ Personal Assistant (24/7)';
                    modeBadge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wide uppercase bg-violet-500/20 text-violet-300 border border-violet-500/30';
                }
            } else {
                if (feedbackCheckbox) {
                    feedbackCheckbox.disabled = false;
                    feedbackCheckbox.checked = true;
                }
                if (feedbackNotice) {
                    feedbackNotice.textContent = '(Sent upon chat closure)';
                    feedbackNotice.className = 'block text-[10px] text-slate-400';
                }
                if (modeBadge) {
                    modeBadge.textContent = '🏢 Business Support Bot';
                    modeBadge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wide uppercase bg-emerald-500/20 text-emerald-300 border border-emerald-500/30';
                }
            }
        }

        modeRadios.forEach(r => {
            r.addEventListener('change', function() {
                updateModeUI(this.value);
            });
        });

        async function switchMode(mode, applyPrompt = true) {
            const ownerName = ownerNameInput ? ownerNameInput.value.trim() : 'Boss';
            const originalBtnText = (mode === 'personal') ? btnToPersonal.innerHTML : btnToSupport.innerHTML;
            const activeBtn = (mode === 'personal') ? btnToPersonal : btnToSupport;

            activeBtn.disabled = true;
            activeBtn.innerHTML = `<span class="inline-block animate-spin mr-1">⌛</span> Switching...`;

            try {
                const basePath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'));
                const res = await fetch(`${basePath}/api/chat/switch-mode.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        mode: mode,
                        owner_name: ownerName,
                        apply_prompt: applyPrompt
                    })
                });

                const data = await res.json();
                if (!data.ok) throw new Error(data.error || 'Failed to switch operating mode.');

                updateModeUI(mode);
                if (textarea && data.system_prompt) {
                    textarea.value = data.system_prompt;
                }

                if (statusPill && statusText) {
                    statusText.innerHTML = `<span>✨ ${data.message}</span>`;
                    statusPill.classList.remove('hidden');
                    setTimeout(() => statusPill.classList.add('hidden'), 8000);
                }
            } catch (err) {
                alert('Error switching mode: ' + err.message);
            } finally {
                activeBtn.disabled = false;
                activeBtn.innerHTML = originalBtnText;
            }
        }

        btnToPersonal.addEventListener('click', function() {
            switchMode('personal', true);
        });

        btnToSupport.addEventListener('click', function() {
            switchMode('support', true);
        });
    })();

            });
    </script>

    <!-- Footer Spacer -->
    <div class="h-8"></div>