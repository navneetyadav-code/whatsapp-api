    <!-- Page Header & Tab Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">AI Agent, RAG & Automation Studio</h1>
            <p class="text-[13px] text-slate-500 mt-0.5 font-medium">Configure intelligent multi-provider LLMs, company knowledge retrieval, keyword rules, and human escalation alarms.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= e(base_url('index.php?page=live-chat')) ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-xs transition-all active:scale-[0.98]">
                <span>💬 Open Live Chat</span>
            </a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-1.5 border-b border-slate-200/90 pb-3 mb-6 overflow-x-auto">
        <a href="<?= e(base_url('index.php?page=chatbot&tab=rules')) ?>" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all <?= $activeTab === 'rules' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
            ⚡ Keyword Triggers (<?= count($chatbotRules) ?>)
        </a>
        <a href="<?= e(base_url('index.php?page=chatbot&tab=ai-agent')) ?>" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all <?= $activeTab === 'ai-agent' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
            🤖 AI Persona & Providers
        </a>
        <a href="<?= e(base_url('index.php?page=chatbot&tab=knowledge')) ?>" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all <?= $activeTab === 'knowledge' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
            📚 Company Knowledge (RAG) (<?= count($knowledgeChunks) ?>)
        </a>
        <a href="<?= e(base_url('index.php?page=chatbot&tab=escalations')) ?>" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all <?= $activeTab === 'escalations' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
            🚨 Escalations & Buzzer Logs
        </a>
    </div>

    <?php if ($activeTab === 'rules'): ?>
    <!-- ========================================== -->
    <!-- TAB 1: KEYWORD AUTOMATION RULES            -->
    <!-- ========================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Active Rules Column -->
        <div class="lg:col-span-2 space-y-4">
            <?php foreach ($chatbotRules as $rule): ?>
                <div class="bg-white rounded-xl border <?= $rule['status'] === 'active' ? 'border-slate-200/80 shadow-card' : 'border-slate-200/50 opacity-60' ?> p-5 transition-all">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex items-center gap-2">
                            <?php if ($rule['match_type'] === 'catch_all'): ?>
                                <span class="bg-purple-50 text-purple-700 text-[10px] font-bold px-2 py-0.5 rounded border border-purple-200 uppercase tracking-wider">Catch All</span>
                                <h3 class="font-bold text-slate-900 text-[14px]">Default Fallback Reply</h3>
                            <?php else: ?>
                                <span class="bg-blue-50 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded border border-blue-200 uppercase tracking-wider"><?= e(str_replace('_', ' ', $rule['match_type'])) ?></span>
                                <h3 class="font-bold text-slate-900 text-[14px]">"<?= e($rule['keyword']) ?>"</h3>
                            <?php endif; ?>
                        </div>
                        
                        <div class="flex items-center gap-1.5">
                            <form method="post" action="<?= e(base_url('index.php?page=chatbot-status')) ?>">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="id" value="<?= (int) $rule['id'] ?>">
                                <input type="hidden" name="status" value="<?= $rule['status'] === 'active' ? 'disabled' : 'active' ?>">
                                <button type="submit" class="text-[11px] font-semibold px-2.5 py-1 rounded-md border <?= $rule['status'] === 'active' ? 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' ?> transition-colors shadow-subtle btn-tactile active:scale-[0.98]">
                                    <?= $rule['status'] === 'active' ? 'Pause' : 'Resume' ?>
                                </button>
                            </form>
                            <form method="post" action="<?= e(base_url('index.php?page=chatbot-delete')) ?>" onsubmit="return confirm('Delete this chatbot rule?');">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="id" value="<?= (int) $rule['id'] ?>">
                                <button type="submit" class="text-rose-500 hover:text-rose-700 hover:bg-rose-50 p-1 rounded transition-colors" title="Delete rule">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="bg-slate-50/80 border border-slate-200/70 rounded-lg p-3">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Bot Response</div>
                        <p class="text-[12px] text-slate-800 whitespace-pre-wrap font-sans leading-relaxed"><?= e($rule['reply_text']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (!$chatbotRules): ?>
                <div class="bg-white border border-slate-200/80 rounded-xl p-10 text-center shadow-card">
                    <div class="w-12 h-12 bg-slate-50 rounded-xl flex items-center justify-center mx-auto mb-3 border border-slate-100 text-slate-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                    </div>
                    <h3 class="text-[14px] font-bold text-slate-900 mb-0.5">No Chatbot Automation Rules</h3>
                    <p class="text-[12px] text-slate-500">Create your first keyword trigger to automate WhatsApp replies.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sticky Create Rule Panel -->
        <div>
            <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-card sticky top-6">
                <h2 class="text-[14px] font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Create Automation Rule
                </h2>
                <form method="post" action="<?= e(base_url('index.php?page=chatbot-create')) ?>" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Match Type</label>
                        <select name="match_type" onchange="document.getElementById('keyword_container').style.display = this.value === 'catch_all' ? 'none' : 'block'" class="w-full text-[12px] px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 shadow-subtle transition-shadow">
                            <option value="exact">Exact Match (e.g. "pricing")</option>
                            <option value="contains">Contains (e.g. "what is your pricing")</option>
                            <option value="starts_with">Starts With (e.g. "price ...")</option>
                            <option value="catch_all">Catch All (Default Reply)</option>
                        </select>
                    </div>

                    <div id="keyword_container">
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Keyword Trigger</label>
                        <input type="text" name="keyword" placeholder="e.g. pricing" class="w-full text-[12px] px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 shadow-subtle transition-shadow">
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Bot Automated Response</label>
                        <textarea name="reply_text" rows="4" required placeholder="Type the automated response message..." class="w-full text-[12px] px-3 py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 shadow-subtle transition-shadow resize-none font-sans"></textarea>
                    </div>

                    <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white text-[12px] font-bold py-2.5 px-4 rounded-lg transition-colors shadow-subtle btn-tactile active:scale-[0.98]">
                        Create Automation
                    </button>
                </form>
            </div>
        </div>
    </div>

    <?php elseif ($activeTab === 'ai-agent'): ?>
    <!-- ========================================== -->
    <!-- TAB 2: AI AGENT & MULTI-PROVIDER ENGINE    -->
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
        <!-- Knowledge Chunks List -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-blue-50/70 border border-blue-200/80 rounded-xl p-4 text-xs text-blue-900 flex items-start gap-3">
                <span class="text-base">💡</span>
                <div>
                    <strong>Zero-Cloud Vector Retrieval (Option A):</strong>
                    <p class="text-[11px] text-blue-800 mt-0.5 leading-relaxed">Each document chunk below is converted into a high-dimensional vector. When a customer messages your WhatsApp number, our Node.js worker performs in-memory Cosine Similarity ranking in sub-millisecond time and injects only the top-matching factual excerpts into the LLM prompt.</p>
                </div>
            </div>

            <?php foreach ($knowledgeChunks as $chunk): ?>
                <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-card transition-all">
                    <div class="flex items-start justify-between gap-3 mb-2">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <h3 class="text-sm font-bold text-slate-900"><?= e($chunk['title']) ?></h3>
                        </div>
                        <form method="post" action="<?= e(base_url('index.php?page=ai-knowledge-delete')) ?>" onsubmit="return confirm('Delete this knowledge chunk?');">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="id" value="<?= (int) $chunk['id'] ?>">
                            <button type="submit" class="text-rose-500 hover:text-rose-700 hover:bg-rose-50 p-1 rounded transition-colors" title="Delete chunk">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    </div>
                    <div class="bg-slate-50 border border-slate-200/70 rounded-lg p-3 text-xs text-slate-700 whitespace-pre-wrap font-sans leading-relaxed">
                        <?= e($chunk['content']) ?>
                    </div>
                    <div class="flex items-center justify-between text-[10px] text-slate-400 font-mono mt-2">
                        <span>Characters: <?= strlen($chunk['content']) ?></span>
                        <span>Model: <?= e($chunk['embedding_model'] ?? 'local-tfidf') ?></span>
                    </div>
                </div>
            <?php endforeach; ?>

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
    <?php endif; ?>

    <!-- Persona AI Rewrite Client Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnEnhance = document.getElementById('btnEnhancePersona');
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
    });
    </script>

    <!-- Footer Spacer -->
    <div class="h-8"></div>
