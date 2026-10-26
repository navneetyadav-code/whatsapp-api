    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Chatbot & Auto-Reply Rules</h1>
            <p class="text-[13px] text-slate-500 mt-0.5 font-medium">Configure intelligent keyword triggers and default fallback replies for inbound WhatsApp messages.</p>
        </div>
    </div>

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
    
    <!-- Footer Spacer -->
    <div class="h-8"></div>
