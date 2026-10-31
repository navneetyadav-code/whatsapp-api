    <!-- Page Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">API Keys</h1>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                    Bearer Auth v1
                </span>
            </div>
            <p class="text-[13px] text-slate-500 mt-1 font-medium">Provision Bearer tokens, configure IP allowlists, and enforce burst rate limits for external integrations.</p>
        </div>
        
        <div class="flex items-center gap-2.5">
            <a href="<?= e(base_url('index.php?page=docs')) ?>" class="px-3.5 py-2 text-[12px] font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg shadow-subtle hover:bg-slate-50 hover:text-slate-900 transition-all flex items-center gap-1.5 btn-tactile">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                <span>Documentation</span>
            </a>
            
            <button type="button" onclick="openCreateModal()" class="px-4 py-2 text-[12px] font-semibold text-white bg-slate-900 rounded-lg shadow-subtle hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition-all flex items-center gap-2 btn-tactile active:scale-[0.98]">
                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                <span>Create API Key</span>
            </button>
        </div>
    </div>

    <!-- Telemetry & Health Ribbon -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-subtle mb-6 overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-slate-100">
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500 tracking-tight flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Active Provisioned Keys
                    </div>
                    <div class="text-xl font-bold text-slate-900 font-mono tabular-nums tracking-tight mt-1"><?= $activeApiKeyCount ?> <span class="text-[13px] font-normal text-slate-400">/ <?= count($apiKeys) ?></span></div>
                    <div class="text-[11px] text-slate-400 mt-0.5">SHA-256 hashed storage</div>
                </div>
                <div class="w-9 h-9 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                </div>
            </div>
            
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500 tracking-tight">Baseline Rate Governance</div>
                    <div class="text-xl font-bold text-slate-900 font-mono tabular-nums tracking-tight mt-1">20 - 300 <span class="text-[13px] font-normal text-slate-400">req / min</span></div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Leaky bucket burst protection</div>
                </div>
                <div class="w-9 h-9 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-600 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
            </div>
            
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500 tracking-tight">Access Firewall Guard</div>
                    <div class="text-xl font-bold text-slate-900 tracking-tight mt-1">IP & Anti-Ban</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Per-key CIDR allowlists & humanizer</div>
                </div>
                <div class="w-9 h-9 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-600 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- New Credentials Reveal Banner -->
    <?php if ($newCredentials): ?>
        <div class="mb-6 p-5 sm:p-6 bg-gradient-to-br from-emerald-50/90 to-teal-50/70 border border-emerald-200/90 rounded-2xl shadow-card relative overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-4">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-[15px] font-bold text-slate-900 tracking-tight">API Key Generated Successfully</h3>
                        <p class="text-[12px] text-slate-600 mt-0.5">
                            Please copy or download your API Token now. <strong class="text-rose-700 font-semibold">It will never be displayed again after you leave this page.</strong>
                        </p>
                    </div>
                </div>
                <button type="button" onclick="this.closest('.mb-6').remove()" class="text-slate-400 hover:text-slate-600 p-1 rounded-md" title="Dismiss">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="space-y-3">
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider">Authorization Bearer Token</label>
                        <span class="text-[11px] text-emerald-700 font-medium flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            Use header: Authorization: Bearer &lt;token&gt;
                        </span>
                    </div>
                    <div class="relative flex items-center">
                        <input id="newBearerToken" readonly value="<?= e($newCredentials['api_key']) ?>" class="block w-full pr-24 pl-3.5 py-2.5 text-[12px] border border-emerald-300/80 rounded-xl bg-white text-slate-900 font-mono shadow-subtle focus:outline-none focus:ring-2 focus:ring-emerald-500/20 select-all">
                        <button type="button" onclick="copyTokenToClipboard('newBearerToken', this)" class="absolute right-1.5 px-3 py-1.5 text-[11px] font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 rounded-lg transition-all flex items-center gap-1.5 btn-tactile">
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                            <span>Copy</span>
                        </button>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2.5 pt-1">
                    <button type="button" onclick="downloadApiKey('<?= e($newCredentials['api_key']) ?>')" class="px-3.5 py-2 text-[12px] font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-subtle transition-all flex items-center gap-1.5 btn-tactile active:scale-[0.98]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        <span>Download Keyfile (.json)</span>
                    </button>
                    <button type="button" onclick="document.getElementById('curlQuickSnippet').classList.toggle('hidden')" class="px-3.5 py-2 text-[12px] font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg shadow-subtle transition-all flex items-center gap-1.5 btn-tactile">
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                        <span>View cURL Example</span>
                    </button>
                </div>

                <div id="curlQuickSnippet" class="hidden mt-3 p-3.5 bg-slate-900 rounded-xl text-slate-200 font-mono text-[11px] overflow-x-auto relative group">
                    <div class="text-[10px] text-slate-400 mb-1 flex items-center justify-between">
                        <span>Terminal Test Request:</span>
                        <button type="button" onclick="copySnippetText(this)" class="text-emerald-400 hover:text-emerald-300 font-sans text-[11px]">Copy cURL</button>
                    </div>
                    <pre class="leading-relaxed select-all">curl -X POST <?= e(base_url('api/send')) ?> \
  -H "Authorization: Bearer <?= e($newCredentials['api_key']) ?>" \
  -H "Content-Type: application/json" \
  -d '{"to": "1234567890", "message": "Hello from API"}'</pre>
                </div>
            </div>
        </div>
        <script>
            function downloadApiKey(token) {
                const data = JSON.stringify({ 
                    access_token: token, 
                    token_type: "Bearer",
                    created_at: new Date().toISOString(),
                    note: "Use this token in the HTTP Authorization header: Bearer <token>"
                }, null, 2);
                const blob = new Blob([data], { type: 'application/json' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'whatsapp_api_credentials.json';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            }
        </script>
    <?php endif; ?>

    <!-- Table & Search Filter Section -->
    <section class="bg-white rounded-xl border border-slate-200/80 shadow-card overflow-hidden mb-8">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-[14px] font-bold text-slate-900 tracking-tight">Active API Credentials</h2>
                <p class="text-[12px] text-slate-500 mt-0.5">Manage permissions, IP restrictions, burst rate limits, and revocation.</p>
            </div>
            
            <div class="flex items-center gap-2">
                <div class="relative w-full sm:w-64">
                    <input id="keySearchInput" type="text" placeholder="Search keys by name or prefix..." oninput="filterKeyRows()" class="w-full pl-8 pr-3 py-1.5 text-[12px] border border-slate-200 rounded-lg bg-white shadow-subtle focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <div class="flex items-center bg-slate-100 p-0.5 rounded-lg border border-slate-200/80 text-[11px] font-semibold text-slate-600">
                    <button type="button" onclick="setFilterStatus('all', this)" class="filter-status-btn px-2.5 py-1 rounded-md bg-white shadow-xs text-slate-900">All</button>
                    <button type="button" onclick="setFilterStatus('active', this)" class="filter-status-btn px-2.5 py-1 rounded-md text-slate-600 hover:text-slate-900">Active</button>
                    <button type="button" onclick="setFilterStatus('disabled', this)" class="filter-status-btn px-2.5 py-1 rounded-md text-slate-600 hover:text-slate-900">Disabled</button>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 text-[11px] font-semibold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <th class="px-5 py-3 font-semibold">Key Identifier</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 font-semibold">Rate Limits</th>
                        <th class="px-5 py-3 font-semibold">IP Allowlist</th>
                        <th class="px-5 py-3 font-semibold">Security Rules</th>
                        <th class="px-5 py-3 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="apiKeysTableBody" class="divide-y divide-slate-100">
                    <?php foreach ($apiKeys as $key): ?>
                        <tr class="key-row hover:bg-slate-50/70 transition-colors group" data-name="<?= e(strtolower($key['name'])) ?>" data-prefix="<?= e(strtolower($key['key_prefix'])) ?>" data-status="<?= e($key['status']) ?>">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-slate-100 border border-slate-200/80 flex items-center justify-center text-slate-600 shrink-0 group-hover:bg-emerald-50 group-hover:border-emerald-200 group-hover:text-emerald-700 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                    </div>
                                    <div>
                                        <div class="text-[13px] font-bold text-slate-900 flex items-center gap-1.5">
                                            <span><?= e($key['name']) ?></span>
                                            <?php if (!empty($key['created_at'])): ?>
                                                <span class="text-[10px] text-slate-400 font-normal font-sans">(Created <?= date('M j, Y', strtotime($key['created_at'])) ?>)</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-[11px] font-mono text-slate-500 mt-0.5 flex items-center gap-1.5">
                                            <span class="bg-slate-100/80 px-1.5 py-0.5 rounded border border-slate-200/60"><?= e($key['key_prefix']) ?>••••••••</span>
                                            <button type="button" onclick="copyPrefix('<?= e($key['key_prefix']) ?>', this)" class="text-slate-400 hover:text-slate-600 transition-colors" title="Copy prefix">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-0.5 text-[10px] font-semibold rounded-md border shadow-subtle inline-flex items-center gap-1.5 <?= e($key['status'] === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200/80' : 'bg-slate-100 text-slate-600 border-slate-200') ?>">
                                    <span class="w-1.5 h-1.5 rounded-full <?= e($key['status'] === 'active' ? 'bg-emerald-500' : 'bg-slate-400') ?>"></span>
                                    <?= e(ucfirst($key['status'])) ?>
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-1.5 text-[11px] text-slate-600 font-mono tabular-nums">
                                    <span class="bg-slate-100/90 px-2 py-0.5 rounded-md border border-slate-200/70" title="Rate limit per minute"><?= (int) $key['rate_per_minute'] ?>/m</span>
                                    <span class="bg-slate-100/90 px-2 py-0.5 rounded-md border border-slate-200/70" title="Rate limit per hour"><?= (int) $key['rate_per_hour'] ?>/h</span>
                                    <span class="bg-slate-100/90 px-2 py-0.5 rounded-md border border-slate-200/70 text-slate-400" title="Rate limit per day"><?= (int) $key['rate_per_day'] ?>/d</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <?php if (!empty($key['ip_allowlist'])): ?>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-50 text-blue-700 text-[11px] font-semibold rounded-md border border-blue-100">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                        Restricted
                                    </span>
                                <?php else: ?>
                                    <span class="text-[12px] text-slate-400 font-mono">Any IP</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-3.5">
                                <?php if (!empty($key['allow_humanize'])): ?>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-teal-50 text-teal-700 text-[10px] font-semibold rounded-md border border-teal-200/70">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                        Anti-Ban Active
                                    </span>
                                <?php else: ?>
                                    <span class="text-[11px] text-slate-400">Direct Delivery</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" onclick="toggleConfigDrawer(<?= (int) $key['id'] ?>)" class="px-2.5 py-1 text-[11px] font-semibold text-slate-700 bg-white border border-slate-200 rounded-md hover:bg-slate-50 transition-colors shadow-subtle btn-tactile active:scale-[0.98]">
                                        Configure
                                    </button>
                                    
                                    <form method="post" action="<?= e(base_url('index.php?page=api-key-status')) ?>" class="inline-block">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $key['id'] ?>">
                                        <input type="hidden" name="status" value="<?= e($key['status'] === 'active' ? 'disabled' : 'active') ?>">
                                        <button type="submit" class="px-2.5 py-1 text-[11px] font-semibold <?= $key['status'] === 'active' ? 'text-amber-700 hover:bg-amber-50 border-amber-200' : 'text-emerald-700 hover:bg-emerald-50 border-emerald-200' ?> bg-white border rounded-md transition-colors shadow-subtle btn-tactile active:scale-[0.98]">
                                            <?= e($key['status'] === 'active' ? 'Disable' : 'Enable') ?>
                                        </button>
                                    </form>
                                    
                                    <form method="post" action="<?= e(base_url('index.php?page=api-key-delete')) ?>" class="inline-block" onsubmit="return confirm('Are you sure you want to permanently delete API key \'<?= e(addslashes($key['name'])) ?>\'? External applications relying on it will immediately fail.');">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $key['id'] ?>">
                                        <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded transition-colors" title="Delete API Key">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Smooth Expandable Configuration Drawer for Existing Keys -->
                        <tr id="settings-<?= (int) $key['id'] ?>" class="hidden bg-slate-50/70 border-b border-slate-200/70">
                            <td colspan="6" class="p-0">
                                <div class="p-5 sm:p-6 bg-slate-50/60 border-t border-slate-100">
                                    <form method="post" action="<?= e(base_url('index.php?page=api-key-limits')) ?>" class="space-y-4 max-w-4xl">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $key['id'] ?>">
                                        
                                        <div class="flex items-center justify-between pb-3 border-b border-slate-200/60">
                                            <div class="flex items-center gap-2">
                                                <span class="text-[13px] font-bold text-slate-900">Governance Settings for <?= e($key['name']) ?></span>
                                                <span class="text-[11px] font-mono text-slate-400">(<?= e($key['key_prefix']) ?>)</span>
                                            </div>
                                            <button type="button" onclick="toggleConfigDrawer(<?= (int) $key['id'] ?>)" class="text-[11px] text-slate-400 hover:text-slate-600">Close</button>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                            <label class="block">
                                                <span class="block text-[11px] font-semibold text-slate-700 mb-1">Rate Limit / Minute</span>
                                                <input name="rate_per_minute" type="number" min="1" max="300" value="<?= (int) $key['rate_per_minute'] ?>" class="block w-full px-3 py-1.5 text-[12px] border border-slate-200 rounded-lg bg-white shadow-subtle focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-mono tabular-nums">
                                                <span class="block text-[10px] text-slate-400 mt-1">Min: 1, Max: 300</span>
                                            </label>
                                            <label class="block">
                                                <span class="block text-[11px] font-semibold text-slate-700 mb-1">Rate Limit / Hour</span>
                                                <input name="rate_per_hour" type="number" min="1" max="5000" value="<?= (int) $key['rate_per_hour'] ?>" class="block w-full px-3 py-1.5 text-[12px] border border-slate-200 rounded-lg bg-white shadow-subtle focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-mono tabular-nums">
                                                <span class="block text-[10px] text-slate-400 mt-1">Min: Minute limit, Max: 5,000</span>
                                            </label>
                                            <label class="block">
                                                <span class="block text-[11px] font-semibold text-slate-700 mb-1">Rate Limit / Day</span>
                                                <input name="rate_per_day" type="number" min="1" max="50000" value="<?= (int) $key['rate_per_day'] ?>" class="block w-full px-3 py-1.5 text-[12px] border border-slate-200 rounded-lg bg-white shadow-subtle focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-mono tabular-nums">
                                                <span class="block text-[10px] text-slate-400 mt-1">Min: Hour limit, Max: 50,000</span>
                                            </label>
                                        </div>

                                        <div class="pt-1">
                                            <label class="block">
                                                <span class="block text-[11px] font-semibold text-slate-700 mb-1 flex items-center justify-between">
                                                    <span>IP Allowlist Restriction</span>
                                                    <span class="text-[10px] font-normal text-slate-400">Comma separated (e.g. 192.168.1.1, 10.0.0.0/24)</span>
                                                </span>
                                                <input name="ip_allowlist" value="<?= e($key['ip_allowlist'] ?? '') ?>" placeholder="Leave blank to allow requests from any IP address" class="block w-full px-3 py-1.5 text-[12px] border border-slate-200 rounded-lg bg-white shadow-subtle focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-mono">
                                            </label>
                                        </div>

                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-2">
                                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                                <input type="checkbox" name="allow_humanize" value="1" <?= empty($key['allow_humanize']) ? '' : 'checked' ?> class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 focus:ring-offset-0 cursor-pointer">
                                                <span class="text-[12px] font-semibold text-slate-700">Enforce Humanized Anti-Ban Delivery Engine</span>
                                            </label>
                                            
                                            <div class="flex items-center gap-2">
                                                <button type="button" onclick="toggleConfigDrawer(<?= (int) $key['id'] ?>)" class="px-3 py-1.5 text-[12px] font-semibold text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-all btn-tactile">
                                                    Cancel
                                                </button>
                                                <button type="submit" class="px-4 py-1.5 text-[12px] font-semibold text-white bg-slate-900 rounded-lg shadow-subtle hover:bg-slate-800 transition-all btn-tactile active:scale-[0.98]">
                                                    Update Configuration
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <?php if (!$apiKeys): ?>
                        <tr id="emptyTableRow">
                            <td colspan="6" class="px-5 py-14 text-center">
                                <div class="max-w-xs mx-auto flex flex-col items-center">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 mb-3 border border-slate-200/80">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                    </div>
                                    <h3 class="text-[14px] font-bold text-slate-900">No API keys created yet</h3>
                                    <p class="text-[12px] text-slate-500 mt-1 mb-4 text-center">Provision a secure token to integrate WhatsApp dispatch with your applications, Zapier, or CRMs.</p>
                                    <button type="button" onclick="openCreateModal()" class="px-4 py-2 text-[12px] font-semibold text-white bg-slate-900 rounded-lg shadow-subtle hover:bg-slate-800 transition-all flex items-center gap-1.5 btn-tactile">
                                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                                        <span>Create API Key</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <tr id="noSearchResultsRow" class="hidden">
                        <td colspan="6" class="px-5 py-8 text-center text-[12px] text-slate-500">
                            No API keys match your search criteria.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Developer Quickstart Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">
        <div class="p-5 bg-white rounded-xl border border-slate-200/80 shadow-card">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-6 h-6 rounded-md bg-slate-100 flex items-center justify-center text-slate-600 font-mono text-[11px] font-bold">1</span>
                <h4 class="text-[13px] font-bold text-slate-900">Single Bearer Token Format</h4>
            </div>
            <p class="text-[12px] text-slate-500 leading-relaxed">
                Pass your API key as a standard Bearer token in the <code class="text-slate-800 bg-slate-100 px-1 py-0.5 rounded text-[11px]">Authorization</code> header:
            </p>
            <div class="mt-2.5 p-3 bg-slate-900 rounded-lg text-slate-200 font-mono text-[11px] overflow-x-auto">
                <code>Authorization: Bearer wa_live_xxxxxx...</code>
            </div>
        </div>

        <div class="p-5 bg-white rounded-xl border border-slate-200/80 shadow-card">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-6 h-6 rounded-md bg-slate-100 flex items-center justify-center text-slate-600 font-mono text-[11px] font-bold">2</span>
                <h4 class="text-[13px] font-bold text-slate-900">Dispatch Endpoint</h4>
            </div>
            <p class="text-[12px] text-slate-500 leading-relaxed">
                Post JSON payload directly to the message queuing gateway:
            </p>
            <div class="mt-2.5 p-3 bg-slate-900 rounded-lg text-emerald-400 font-mono text-[11px] overflow-x-auto">
                <code>POST <?= e(base_url('api/send')) ?></code>
            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- HIGH-CRAFT CREATE API KEY MULTI-STAGE MODAL POPUP                -->
    <!-- ================================================================= -->
    <div id="createKeyModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop with Blur -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity opacity-0" id="createKeyBackdrop" onclick="closeCreateModal()"></div>

        <!-- Modal Dialog Positioning -->
        <div class="min-h-full flex items-center justify-center p-4 sm:p-6 text-center">
            <div id="createKeyCard" class="relative bg-white rounded-2xl border border-slate-200/80 shadow-2xl text-left overflow-hidden transform transition-all sm:max-w-lg w-full scale-95 opacity-0 my-8">
                
                <!-- Modal Top Header & Stepper -->
                <div class="px-6 pt-5 pb-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-[15px] font-bold text-slate-900 tracking-tight" id="modal-title">Create API Key</h3>
                            <p class="text-[11px] text-slate-500 font-medium">Provision credentials for automated WhatsApp messaging</p>
                        </div>
                    </div>
                    
                    <button type="button" onclick="closeCreateModal()" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Stepper Progress Pill -->
                <div class="px-6 py-2.5 bg-slate-50/90 border-b border-slate-100 flex items-center justify-between text-[11px]">
                    <div class="flex items-center gap-2">
                        <span id="stepBadge1" class="px-2 py-0.5 rounded-md font-semibold bg-emerald-100 text-emerald-800 transition-colors">1. Name & Purpose</span>
                        <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        <span id="stepBadge2" class="px-2 py-0.5 rounded-md font-medium text-slate-400 transition-colors">2. Rate Limits & Security</span>
                    </div>
                    <span id="stepIndicatorText" class="text-slate-400 font-medium">Step 1 of 2</span>
                </div>

                <!-- Form Wrapping All Stages -->
                <form id="createApiKeyForm" method="post" action="<?= e(base_url('index.php?page=credentials')) ?>" onsubmit="return handleFormSubmit(event)">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                    <!-- ============================================== -->
                    <!-- STAGE 1: KEY NAME & PRESETS                   -->
                    <!-- ============================================== -->
                    <div id="modalStage1" class="p-6 space-y-4">
                        <div>
                            <label for="modalKeyNameInput" class="block text-[12px] font-bold text-slate-800 mb-1.5">
                                API Key Name <span class="text-rose-500">*</span>
                            </label>
                            <input id="modalKeyNameInput" name="name" type="text" placeholder="e.g. Zapier Webhook, Production App" required autofocus class="block w-full px-3.5 py-2.5 text-[13px] border border-slate-200 rounded-xl bg-white text-slate-900 shadow-subtle focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                            <span id="nameErrorMsg" class="hidden text-[11px] text-rose-600 mt-1 font-medium">Please enter a valid key name to proceed.</span>
                        </div>

                        <!-- Quick suggestion chips -->
                        <div>
                            <span class="block text-[11px] font-semibold text-slate-500 mb-2">Popular presets:</span>
                            <div class="flex flex-wrap gap-1.5">
                                <button type="button" onclick="selectNamePreset('Zapier Automation')" class="px-2.5 py-1 text-[11px] font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 hover:text-slate-900 rounded-md transition-colors">⚡ Zapier Automation</button>
                                <button type="button" onclick="selectNamePreset('Production Node Backend')" class="px-2.5 py-1 text-[11px] font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 hover:text-slate-900 rounded-md transition-colors">🚀 Production Backend</button>
                                <button type="button" onclick="selectNamePreset('CRM Leads Sync')" class="px-2.5 py-1 text-[11px] font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 hover:text-slate-900 rounded-md transition-colors">📊 CRM Leads Sync</button>
                                <button type="button" onclick="selectNamePreset('Make.com Scenario')" class="px-2.5 py-1 text-[11px] font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 hover:text-slate-900 rounded-md transition-colors">🔄 Make.com</button>
                                <button type="button" onclick="selectNamePreset('Staging / Dev Test')" class="px-2.5 py-1 text-[11px] font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 hover:text-slate-900 rounded-md transition-colors">🧪 Staging / Dev</button>
                            </div>
                        </div>

                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-[11px] text-slate-500 flex items-start gap-2">
                            <svg class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>After giving your key a descriptive name, you can customize rate limits and IP security rules in the next step.</span>
                        </div>

                        <!-- Stage 1 Footer -->
                        <div class="pt-2 flex items-center justify-end gap-2.5">
                            <button type="button" onclick="closeCreateModal()" class="px-4 py-2 text-[12px] font-semibold text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all btn-tactile">
                                Cancel
                            </button>
                            <button type="button" onclick="triggerLoadingTransition()" class="px-4 py-2 text-[12px] font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-xl shadow-subtle transition-all flex items-center gap-1.5 btn-tactile active:scale-[0.98]">
                                <span>Continue to Configuration</span>
                                <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </button>
                        </div>
                    </div>

                    <!-- ============================================== -->
                    <!-- STAGE 2: LOADING TRANSITION                    -->
                    <!-- ============================================== -->
                    <div id="modalStageLoading" class="p-10 hidden flex-col items-center justify-center text-center space-y-4">
                        <div class="relative w-12 h-12 flex items-center justify-center">
                            <!-- Spinner Circle -->
                            <div class="w-12 h-12 rounded-full border-2 border-slate-100 border-t-emerald-600 animate-spin"></div>
                            <!-- Center Pulse Dot -->
                            <div class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></div>
                        </div>

                        <div>
                            <h4 class="text-[14px] font-bold text-slate-900 tracking-tight">Initializing Governance Parameters...</h4>
                            <p class="text-[12px] text-slate-500 mt-1">Preparing rate limits, quota enforcement & security rules</p>
                        </div>

                        <!-- Subtle Progress Track -->
                        <div class="w-48 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                            <div id="loadingProgressBar" class="h-full bg-gradient-to-r from-emerald-500 to-teal-500 w-0 transition-all duration-700 ease-out"></div>
                        </div>
                    </div>

                    <!-- ============================================== -->
                    <!-- STAGE 3: CONFIGURATION (RATE LIMITS & SECURITY)-->
                    <!-- ============================================== -->
                    <div id="modalStage2" class="p-6 space-y-4 hidden">
                        <!-- Key Name Reminder Badge -->
                        <div class="flex items-center justify-between p-2.5 bg-slate-50 rounded-xl border border-slate-200/60">
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Key Name:</span>
                                <span id="stage2KeyNameDisplay" class="text-[12px] font-bold text-slate-900"></span>
                            </div>
                            <button type="button" onclick="backToStage1()" class="text-[11px] font-semibold text-emerald-700 hover:text-emerald-800 transition-colors">
                                Edit name
                            </button>
                        </div>

                        <!-- Presets Selector -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1.5">Rate Limit Template</label>
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" onclick="applyLimitTemplate(20, 300, 1000, this)" class="limit-template-btn p-2 rounded-xl border border-emerald-500 bg-emerald-50/50 text-left transition-all">
                                    <div class="text-[11px] font-bold text-emerald-950">Standard</div>
                                    <div class="text-[10px] text-emerald-700 font-mono">20/m • 300/h</div>
                                </button>
                                <button type="button" onclick="applyLimitTemplate(60, 1200, 10000, this)" class="limit-template-btn p-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-left transition-all">
                                    <div class="text-[11px] font-bold text-slate-900">High Volume</div>
                                    <div class="text-[10px] text-slate-500 font-mono">60/m • 1.2k/h</div>
                                </button>
                                <button type="button" onclick="applyLimitTemplate(5, 60, 500, this)" class="limit-template-btn p-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-left transition-all">
                                    <div class="text-[11px] font-bold text-slate-900">Strict</div>
                                    <div class="text-[10px] text-slate-500 font-mono">5/m • 60/h</div>
                                </button>
                            </div>
                        </div>

                        <!-- Granular Limit Inputs -->
                        <div class="grid grid-cols-3 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Per Minute</label>
                                <input id="rateMinuteInput" name="rate_per_minute" type="number" min="1" max="300" value="20" required class="block w-full px-3 py-1.5 text-[12px] border border-slate-200 rounded-lg bg-white shadow-subtle focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-mono tabular-nums">
                                <span class="text-[10px] text-slate-400">Max: 300/m</span>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Per Hour</label>
                                <input id="rateHourInput" name="rate_per_hour" type="number" min="1" max="5000" value="300" required class="block w-full px-3 py-1.5 text-[12px] border border-slate-200 rounded-lg bg-white shadow-subtle focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-mono tabular-nums">
                                <span class="text-[10px] text-slate-400">Max: 5k/h</span>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Per Day</label>
                                <input id="rateDayInput" name="rate_per_day" type="number" min="1" max="50000" value="1000" required class="block w-full px-3 py-1.5 text-[12px] border border-slate-200 rounded-lg bg-white shadow-subtle focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-mono tabular-nums">
                                <span class="text-[10px] text-slate-400">Max: 50k/d</span>
                            </div>
                        </div>

                        <!-- IP Allowlist -->
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1 flex items-center justify-between">
                                <span>IP Allowlist (Optional)</span>
                                <span class="text-[10px] font-normal text-slate-400 font-mono">e.g. 192.168.1.1, 10.0.0.5</span>
                            </label>
                            <input name="ip_allowlist" type="text" placeholder="Leave empty to permit requests from any IP address" class="block w-full px-3.5 py-2 text-[12px] border border-slate-200 rounded-xl bg-white shadow-subtle focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-mono">
                        </div>

                        <!-- Anti-Ban Humanizer Delivery Toggle -->
                        <div class="p-3 bg-teal-50/70 border border-teal-100 rounded-xl">
                            <label class="flex items-start gap-2.5 cursor-pointer select-none">
                                <input type="checkbox" name="allow_humanize" value="1" checked class="w-4 h-4 mt-0.5 rounded border-teal-300 text-teal-600 focus:ring-teal-500 focus:ring-offset-0 cursor-pointer">
                                <div>
                                    <span class="block text-[12px] font-bold text-slate-900">Enable Humanized Anti-Ban Delivery</span>
                                    <span class="block text-[11px] text-slate-600 mt-0.5">Injects human typing presence and randomized delays to shield your WhatsApp number from spam algorithms.</span>
                                </div>
                            </label>
                        </div>

                        <!-- Stage 2 Footer Actions -->
                        <div class="pt-2 flex items-center justify-between">
                            <button type="button" onclick="backToStage1()" class="px-3.5 py-2 text-[12px] font-semibold text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all btn-tactile flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                                <span>Back</span>
                            </button>

                            <button id="submitCreateKeyBtn" type="submit" class="px-5 py-2 text-[12px] font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-xl shadow-subtle transition-all flex items-center gap-1.5 btn-tactile active:scale-[0.98]">
                                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>Create Key</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Client Script for Interactive Transitions & Modal Management -->
    <script>
        // Modal State Handlers
        function openCreateModal() {
            const modal = document.getElementById('createKeyModal');
            const backdrop = document.getElementById('createKeyBackdrop');
            const card = document.getElementById('createKeyCard');
            
            // Reset to Stage 1
            resetCreateFlow();

            modal.classList.remove('hidden');
            setTimeout(() => {
                backdrop.classList.remove('opacity-0');
                card.classList.remove('scale-95', 'opacity-0');
                card.classList.add('scale-100', 'opacity-100');
                document.getElementById('modalKeyNameInput').focus();
            }, 10);
        }

        function closeCreateModal() {
            const modal = document.getElementById('createKeyModal');
            const backdrop = document.getElementById('createKeyBackdrop');
            const card = document.getElementById('createKeyCard');

            backdrop.classList.add('opacity-0');
            card.classList.remove('scale-100', 'opacity-100');
            card.classList.add('scale-95', 'opacity-0');

            setTimeout(() => {
                modal.classList.add('hidden');
            }, 200);
        }

        function resetCreateFlow() {
            document.getElementById('modalStage1').classList.remove('hidden');
            document.getElementById('modalStageLoading').classList.add('hidden');
            document.getElementById('modalStageLoading').classList.remove('flex');
            document.getElementById('modalStage2').classList.add('hidden');
            
            document.getElementById('stepBadge1').className = "px-2 py-0.5 rounded-md font-semibold bg-emerald-100 text-emerald-800 transition-colors";
            document.getElementById('stepBadge2').className = "px-2 py-0.5 rounded-md font-medium text-slate-400 transition-colors";
            document.getElementById('stepIndicatorText').textContent = "Step 1 of 2";

            const error = document.getElementById('nameErrorMsg');
            if (error) error.classList.add('hidden');
        }

        function selectNamePreset(presetName) {
            const input = document.getElementById('modalKeyNameInput');
            input.value = presetName;
            document.getElementById('nameErrorMsg').classList.add('hidden');
            input.focus();
        }

        function triggerLoadingTransition() {
            const nameInput = document.getElementById('modalKeyNameInput');
            const name = nameInput.value.trim();

            if (!name) {
                document.getElementById('nameErrorMsg').classList.remove('hidden');
                nameInput.focus();
                return;
            }

            document.getElementById('nameErrorMsg').classList.add('hidden');
            document.getElementById('stage2KeyNameDisplay').textContent = name;

            // Transition: Hide Stage 1, Show Loading with animated bar
            document.getElementById('modalStage1').classList.add('hidden');
            const loadingPane = document.getElementById('modalStageLoading');
            loadingPane.classList.remove('hidden');
            loadingPane.classList.add('flex');

            const progBar = document.getElementById('loadingProgressBar');
            progBar.style.width = '0%';
            setTimeout(() => {
                progBar.style.width = '100%';
            }, 50);

            // Update Stepper to configuration
            document.getElementById('stepBadge1').className = "px-2 py-0.5 rounded-md font-medium text-slate-400 transition-colors";
            document.getElementById('stepBadge2').className = "px-2 py-0.5 rounded-md font-semibold bg-emerald-100 text-emerald-800 transition-colors";
            document.getElementById('stepIndicatorText').textContent = "Step 2 of 2";

            // Delay simulated loading for delightful micro-interaction
            setTimeout(() => {
                loadingPane.classList.add('hidden');
                loadingPane.classList.remove('flex');
                document.getElementById('modalStage2').classList.remove('hidden');
            }, 650);
        }

        function backToStage1() {
            document.getElementById('modalStage2').classList.add('hidden');
            document.getElementById('modalStageLoading').classList.add('hidden');
            document.getElementById('modalStageLoading').classList.remove('flex');
            document.getElementById('modalStage1').classList.remove('hidden');

            document.getElementById('stepBadge1').className = "px-2 py-0.5 rounded-md font-semibold bg-emerald-100 text-emerald-800 transition-colors";
            document.getElementById('stepBadge2').className = "px-2 py-0.5 rounded-md font-medium text-slate-400 transition-colors";
            document.getElementById('stepIndicatorText').textContent = "Step 1 of 2";
            document.getElementById('modalKeyNameInput').focus();
        }

        function applyLimitTemplate(minute, hour, day, btnElement) {
            document.getElementById('rateMinuteInput').value = minute;
            document.getElementById('rateHourInput').value = hour;
            document.getElementById('rateDayInput').value = day;

            document.querySelectorAll('.limit-template-btn').forEach(btn => {
                btn.className = "limit-template-btn p-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-left transition-all";
                const title = btn.querySelector('div:first-child');
                const sub = btn.querySelector('div:last-child');
                if (title) title.className = "text-[11px] font-bold text-slate-900";
                if (sub) sub.className = "text-[10px] text-slate-500 font-mono";
            });

            btnElement.className = "limit-template-btn p-2 rounded-xl border border-emerald-500 bg-emerald-50/50 text-left transition-all";
            const title = btnElement.querySelector('div:first-child');
            const sub = btnElement.querySelector('div:last-child');
            if (title) title.className = "text-[11px] font-bold text-emerald-950";
            if (sub) sub.className = "text-[10px] text-emerald-700 font-mono";
        }

        function handleFormSubmit(e) {
            const submitBtn = document.getElementById('submitCreateKeyBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg class="w-3.5 h-3.5 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                <span>Creating Key...</span>
            `;
            return true;
        }

        // Toggle Expandable Configuration Drawer for existing keys
        function toggleConfigDrawer(id) {
            const row = document.getElementById('settings-' + id);
            if (row) {
                row.classList.toggle('hidden');
            }
        }

        // Quick Copy Helpers
        function copyTokenToClipboard(inputId, button) {
            const input = document.getElementById(inputId);
            input.select();
            navigator.clipboard.writeText(input.value).then(() => {
                const originalHtml = button.innerHTML;
                button.innerHTML = `
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    <span class="text-emerald-700 font-bold">Copied!</span>
                `;
                setTimeout(() => {
                    button.innerHTML = originalHtml;
                }, 2000);
            });
        }

        function copyPrefix(prefix, btn) {
            navigator.clipboard.writeText(prefix).then(() => {
                const orig = btn.innerHTML;
                btn.innerHTML = `<svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>`;
                setTimeout(() => { btn.innerHTML = orig; }, 1500);
            });
        }

        function copySnippetText(btn) {
            const pre = btn.closest('.group').querySelector('pre');
            navigator.clipboard.writeText(pre.innerText).then(() => {
                const orig = btn.innerText;
                btn.innerText = 'Copied!';
                setTimeout(() => { btn.innerText = orig; }, 2000);
            });
        }

        // Table Search & Status Filtering
        let activeStatusFilter = 'all';

        function setFilterStatus(status, btn) {
            activeStatusFilter = status;
            document.querySelectorAll('.filter-status-btn').forEach(b => {
                b.className = "filter-status-btn px-2.5 py-1 rounded-md text-slate-600 hover:text-slate-900";
            });
            btn.className = "filter-status-btn px-2.5 py-1 rounded-md bg-white shadow-xs text-slate-900";
            filterKeyRows();
        }

        function filterKeyRows() {
            const search = (document.getElementById('keySearchInput').value || '').toLowerCase().trim();
            const rows = document.querySelectorAll('.key-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const name = row.dataset.name || '';
                const prefix = row.dataset.prefix || '';
                const status = row.dataset.status || '';

                const matchesSearch = !search || name.includes(search) || prefix.includes(search);
                const matchesStatus = activeStatusFilter === 'all' || status === activeStatusFilter;

                if (matchesSearch && matchesStatus) {
                    row.classList.remove('hidden');
                    visibleCount++;
                } else {
                    row.classList.add('hidden');
                }
            });

            const noSearchRow = document.getElementById('noSearchResultsRow');
            if (noSearchRow) {
                if (visibleCount === 0 && rows.length > 0) {
                    noSearchRow.classList.remove('hidden');
                } else {
                    noSearchRow.classList.add('hidden');
                }
            }
        }

        // Keyboard Shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('createKeyModal');
                if (modal && !modal.classList.contains('hidden')) {
                    closeCreateModal();
                }
            }
            if (e.key === 'Enter') {
                const modal = document.getElementById('createKeyModal');
                const stage1 = document.getElementById('modalStage1');
                if (modal && !modal.classList.contains('hidden') && !stage1.classList.contains('hidden')) {
                    const activeEl = document.activeElement;
                    if (activeEl && activeEl.id === 'modalKeyNameInput') {
                        e.preventDefault();
                        triggerLoadingTransition();
                    }
                }
            }
        });
    </script>
