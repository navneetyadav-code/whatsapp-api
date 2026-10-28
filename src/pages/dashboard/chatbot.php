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
        <a href="<?= e(base_url('index.php?page=chatbot&tab=tools')) ?>" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all <?= $activeTab === 'tools' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
            🛠️ Tools & Integrations (RBAC)
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
    <!-- TAB 5: AUTONOMOUS TOOLS & RBAC ENGINE      -->
    <!-- ========================================== -->
    <div class="space-y-6 mb-8">
        <!-- Engine Status Banner -->
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-xl p-6 text-white shadow-card">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 mb-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Autonomous Function Calling Engine Active
                    </div>
                    <h2 class="text-xl font-bold tracking-tight">Agent Tools & Role-Based Access Control (RBAC)</h2>
                    <p class="text-xs text-slate-300 mt-1 max-w-2xl">
                        Equip your AI Agent with external capabilities. Tools are dynamically pruned from Gemini's prompt schema based on the caller's role, ensuring guests never see or trigger sensitive administrative actions.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-400">Model:</span>
                    <span class="text-xs font-mono font-bold bg-white/10 px-2.5 py-1 rounded border border-white/10 text-emerald-300">gemini-3.5-flash-lite</span>
                </div>
            </div>
        </div>

        <form method="post" action="<?= e(base_url('index.php?page=ai-tools-save')) ?>" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

            <!-- Master Toggle & Admin Phones Whitelist Card -->
            <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-card">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <span>⚙️</span> Master Tool Engine Switch
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">When disabled, the bot functions as a pure text-only knowledge assistant without calling external APIs.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="tools_enabled" value="1" <?= !empty($toolsConfig['enabled']) ? 'checked' : '' ?> class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        <span class="ml-3 text-xs font-bold text-slate-700">Enable Agent Tools</span>
                    </label>
                </div>

                <div class="pt-4">
                    <label class="block text-xs font-bold text-slate-800 mb-1">
                        🛡️ Admin Phone Numbers Whitelist (Comma-Separated)
                    </label>
                    <p class="text-[11px] text-slate-500 mb-2">WhatsApp numbers permitted to execute privileged administrative actions (e.g. system statistics, team overview):</p>
                    <input type="text" name="admin_phones" value="<?= e(implode(', ', $toolsConfig['admin_phones'] ?? ['+918581065107', '+919507286092'])) ?>" class="w-full text-xs font-mono px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" placeholder="+918581065107, +919507286092">
                </div>
            </div>

            <!-- Grid: Tools Configuration Cards -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- TOOL 1: WEB SEARCH ENGINE -->
                <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-card flex flex-col justify-between">
                    <div>
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-base">🌐</span>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900">Live Web Search</h4>
                                    <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded border border-blue-200 uppercase">DuckDuckGo Resilient Scraper</span>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="web_search_enabled" value="1" <?= !empty($toolsConfig['tools']['web_search']['enabled']) ? 'checked' : '' ?> class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                            </label>
                        </div>

                        <p class="text-xs text-slate-500 mb-4">
                            Enables the agent to search live internet sources for real-time external facts, competitor comparisons, and news with prompt-injection sanitization.
                        </p>

                        <div class="space-y-3 pt-3 border-t border-slate-100">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Max Snippets Per Query</label>
                                <input type="number" name="web_search_max_results" value="<?= (int) ($toolsConfig['tools']['web_search']['max_results'] ?? 3) ?>" min="1" max="5" class="w-24 text-xs px-2.5 py-1.5 border border-slate-200 rounded-lg">
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1.5">Permitted Roles (RBAC)</label>
                                <div class="flex items-center gap-4 text-xs text-slate-600">
                                    <?php $wsRoles = $toolsConfig['tools']['web_search']['roles'] ?? ['guest', 'customer', 'admin']; ?>
                                    <label class="inline-flex items-center gap-1.5">
                                        <input type="checkbox" name="web_search_roles[]" value="guest" <?= in_array('guest', $wsRoles, true) ? 'checked' : '' ?> class="rounded text-emerald-600">
                                        <span>Public Guests</span>
                                    </label>
                                    <label class="inline-flex items-center gap-1.5">
                                        <input type="checkbox" name="web_search_roles[]" value="customer" <?= in_array('customer', $wsRoles, true) ? 'checked' : '' ?> class="rounded text-emerald-600">
                                        <span>Customers</span>
                                    </label>
                                    <label class="inline-flex items-center gap-1.5">
                                        <input type="checkbox" name="web_search_roles[]" value="admin" <?= in_array('admin', $wsRoles, true) ? 'checked' : '' ?> class="rounded text-emerald-600">
                                        <span>Admins</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TOOL 2: UTILITY ARITHMETIC & CLOCK -->
                <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-card flex flex-col justify-between">
                    <div>
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-base">🧮</span>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900">Utility Tool Belt</h4>
                                    <span class="text-[10px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded border border-amber-200 uppercase">Math, Clock & Escalation</span>
                                </div>
                            </div>
                        </div>

                        <p class="text-xs text-slate-500 mb-4">
                            Precision calculations (GST, discounts, quotes), real-time Indian Standard Time (IST) office hours checker, and human support handoff alarm.
                        </p>

                        <div class="space-y-3 pt-3 border-t border-slate-100">
                            <!-- Math Toggle -->
                            <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-50 border border-slate-200/70">
                                <div>
                                    <div class="text-xs font-bold text-slate-900">Math & Pricing Calculator</div>
                                    <div class="text-[10px] text-slate-500">Hallucination-free exact arithmetic for quotations</div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="math_enabled" value="1" <?= !empty($toolsConfig['tools']['evaluate_math']['enabled']) ? 'checked' : '' ?> class="sr-only peer">
                                    <div class="w-8 h-4 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-emerald-600"></div>
                                </label>
                            </div>

                            <!-- Time & Clock Toggle -->
                            <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-50 border border-slate-200/70">
                                <div>
                                    <div class="text-xs font-bold text-slate-900">IST Real-Time & Business Hours</div>
                                    <div class="text-[10px] text-slate-500">Mon-Sat 9:30 AM - 6:30 PM office status check</div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="time_enabled" value="1" <?= !empty($toolsConfig['tools']['get_current_time']['enabled']) ? 'checked' : '' ?> class="sr-only peer">
                                    <div class="w-8 h-4 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-emerald-600"></div>
                                </label>
                            </div>

                            <!-- Human Escalation Toggle -->
                            <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-50 border border-slate-200/70">
                                <div>
                                    <div class="text-xs font-bold text-slate-900">Human Escalation Trigger</div>
                                    <div class="text-[10px] text-slate-500">Flags conversation & sounds buzzer for human takeover</div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="escalate_enabled" value="1" <?= !empty($toolsConfig['tools']['escalate_to_human']['enabled']) ? 'checked' : '' ?> class="sr-only peer">
                                    <div class="w-8 h-4 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-emerald-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TOOL 3: ASSISTGROUP API CONNECTOR (Full Width) -->
                <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200/80 p-5 shadow-card">
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-base">🏢</span>
                            <div>
                                <h4 class="text-sm font-bold text-slate-900">AssistGroup Multi-Environment API Connector</h4>
                                <span class="text-[10px] font-bold text-purple-600 bg-purple-50 px-2 py-0.5 rounded border border-purple-200 uppercase">Localhost & Live Shared Hosting</span>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="assistgroup_enabled" value="1" <?= !empty($toolsConfig['tools']['assistgroup_api']['enabled']) ? 'checked' : '' ?> class="sr-only peer">
                            <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>

                    <?php
                    $agConfig = $toolsConfig['tools']['assistgroup_api'] ?? [];
                    $activeEnv = $agConfig['active_env'] ?? 'local';
                    $localEnv = $agConfig['environments']['local'] ?? [];
                    $liveEnv = $agConfig['environments']['live'] ?? [];
                    ?>

                    <!-- Environment Selector Radios -->
                    <div class="p-3 bg-slate-50 rounded-lg border border-slate-200/80 mb-4">
                        <div class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-2">Active Environment Target:</div>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                            <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-800">
                                <input type="radio" name="assistgroup_active_env" value="local" <?= $activeEnv === 'local' ? 'checked' : '' ?> class="text-purple-600">
                                <span>Localhost Development (<span class="font-mono text-[11px] text-slate-500">http://localhost/assistgroup/...</span>)</span>
                            </label>
                            <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-800">
                                <input type="radio" name="assistgroup_active_env" value="live" <?= $activeEnv === 'live' ? 'checked' : '' ?> class="text-purple-600">
                                <span>Live Shared Hosting (<span class="font-mono text-[11px] text-purple-700">https://assistgroup.gt.tc/...</span>)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Environment Inputs Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                        <div class="p-3.5 rounded-lg border border-slate-200">
                            <div class="text-xs font-bold text-slate-900 mb-2">💻 Localhost Settings</div>
                            <div class="space-y-2">
                                <div>
                                    <label class="block text-[10px] text-slate-500 uppercase font-semibold mb-0.5">Base URL</label>
                                    <input type="text" name="assistgroup_local_url" value="<?= e($localEnv['base_url'] ?? 'http://localhost/assistgroup/api/admin/v1') ?>" class="w-full text-xs font-mono px-2.5 py-1.5 border border-slate-200 rounded-lg">
                                </div>
                                <div>
                                    <label class="block text-[10px] text-slate-500 uppercase font-semibold mb-0.5">Admin Bearer Token</label>
                                    <input type="password" name="assistgroup_local_token" placeholder="<?= !empty($localEnv['bearer_token']) ? '•••••••••••••••••••••••• (Configured)' : 'ast_live_...' ?>" class="w-full text-xs font-mono px-2.5 py-1.5 border border-slate-200 rounded-lg">
                                </div>
                            </div>
                        </div>

                        <div class="p-3.5 rounded-lg border border-purple-200 bg-purple-50/20">
                            <div class="text-xs font-bold text-purple-900 mb-2">🌐 Live Shared Hosting Settings</div>
                            <div class="space-y-2">
                                <div>
                                    <label class="block text-[10px] text-slate-500 uppercase font-semibold mb-0.5">Base URL</label>
                                    <input type="text" name="assistgroup_live_url" value="<?= e($liveEnv['base_url'] ?? 'https://assistgroup.gt.tc/api/admin/v1') ?>" class="w-full text-xs font-mono px-2.5 py-1.5 border border-slate-200 rounded-lg">
                                </div>
                                <div>
                                    <label class="block text-[10px] text-slate-500 uppercase font-semibold mb-0.5">Admin Bearer Token</label>
                                    <input type="password" name="assistgroup_live_token" placeholder="<?= !empty($liveEnv['bearer_token']) ? '•••••••••••••••••••••••• (Configured)' : 'ast_live_...' ?>" class="w-full text-xs font-mono px-2.5 py-1.5 border border-slate-200 rounded-lg">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Role Gated Actions Matrix -->
                    <div>
                        <h5 class="text-xs font-bold text-slate-900 mb-2">AssistGroup Action Access Matrix (RBAC)</h5>
                        <div class="divide-y divide-slate-100 border border-slate-200 rounded-lg overflow-hidden text-xs">
                            <?php
                            $actions = [
                                'pricing' => ['key' => 'ag_pricing', 'name' => 'Get Services & Pricing Plans', 'desc' => 'Allows bot to quote official web, app, and SEO plans', 'roles' => $agConfig['actions']['get_services_pricing']['roles'] ?? ['guest', 'customer', 'admin']],
                                'inquiry' => ['key' => 'ag_inquiry', 'name' => 'Create Inquiries / Leads in CRM', 'desc' => 'Directly saves prospective leads into AssistGroup database', 'roles' => $agConfig['actions']['create_inquiry']['roles'] ?? ['guest', 'customer', 'admin']],
                                'lookup' => ['key' => 'ag_lookup', 'name' => 'Lookup Customer Tickets / Inquiries', 'desc' => 'Retrieves caller past inquiries using their phone number', 'roles' => $agConfig['actions']['lookup_inquiries']['roles'] ?? ['customer', 'admin']],
                                'team' => ['key' => 'ag_team', 'name' => 'Get Team & Staff Overview', 'desc' => 'Answers questions about developers, engineers, and consultants', 'roles' => $agConfig['actions']['get_team_overview']['roles'] ?? ['guest', 'customer', 'admin']],
                                'stats' => ['key' => 'ag_stats', 'name' => 'Admin Dashboard Executive Metrics', 'desc' => 'Strictly restricted metrics: registered users, file counts, growth', 'roles' => $agConfig['actions']['admin_dashboard_stats']['roles'] ?? ['admin']]
                            ];
                            foreach ($actions as $actKey => $act):
                            ?>
                            <div class="p-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/50">
                                <div>
                                    <label class="inline-flex items-center gap-2 font-bold text-slate-800">
                                        <input type="checkbox" name="<?= $act['key'] ?>_enabled" value="1" checked class="rounded text-purple-600">
                                        <span><?= e($act['name']) ?></span>
                                    </label>
                                    <div class="text-[11px] text-slate-400 pl-5"><?= e($act['desc']) ?></div>
                                </div>
                                <div class="flex items-center gap-3 pl-5 sm:pl-0 text-slate-600">
                                    <label class="inline-flex items-center gap-1">
                                        <input type="checkbox" name="<?= $act['key'] ?>_roles[]" value="guest" <?= in_array('guest', $act['roles'], true) ? 'checked' : '' ?> class="rounded text-purple-600">
                                        <span>Guest</span>
                                    </label>
                                    <label class="inline-flex items-center gap-1">
                                        <input type="checkbox" name="<?= $act['key'] ?>_roles[]" value="customer" <?= in_array('customer', $act['roles'], true) ? 'checked' : '' ?> class="rounded text-purple-600">
                                        <span>Customer</span>
                                    </label>
                                    <label class="inline-flex items-center gap-1">
                                        <input type="checkbox" name="<?= $act['key'] ?>_roles[]" value="admin" <?= in_array('admin', $act['roles'], true) ? 'checked' : '' ?> class="rounded text-purple-600">
                                        <span class="font-bold text-slate-900">Admin</span>
                                    </label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Submit Button Bar -->
            <div class="flex justify-end gap-3 pt-4">
                <button type="submit" class="px-5 py-2.5 rounded-lg text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white shadow-xs transition-all active:scale-[0.98]">
                    💾 Save Autonomous Tools & Permissions
                </button>
            </div>
        </form>

        <!-- Audit Log Table: Recent Tool Invocations -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-card overflow-hidden">
            <div class="p-4 border-b border-slate-200/80 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Recent AI Tool Invocations (Audit Trail)</h3>
                    <p class="text-xs text-slate-500">Real-time log of tools executed by the WhatsApp AI Agent, caller roles, and latencies.</p>
                </div>
                <span class="text-xs font-mono text-slate-400"><?= count($recentInvocations) ?> events</span>
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
