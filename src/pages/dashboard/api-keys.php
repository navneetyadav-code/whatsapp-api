    <!-- Page Header & Key Generator -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">API Keys</h1>
            <p class="text-[13px] text-slate-500 mt-0.5 font-medium">Provision Bearer tokens, configure IP allowlists, and enforce rate limits for external integrations.</p>
        </div>
        <form method="post" action="<?= e(base_url('index.php?page=credentials')) ?>" class="flex items-center gap-2">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input name="name" type="text" placeholder="Key name (e.g. Zapier)" required class="px-3 py-1.5 text-[12px] border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-white shadow-subtle w-48 transition-all">
            <button type="submit" class="px-3.5 py-1.5 text-[12px] font-semibold text-white bg-slate-900 rounded-lg shadow-subtle hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition-all flex items-center gap-1.5 btn-tactile active:scale-[0.98]">
                <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                <span>Generate Key</span>
            </button>
        </form>
    </div>

    <!-- Integrated Telemetry Ribbon -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-subtle mb-6 overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-slate-100">
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500 tracking-tight">Active Provisioned Keys</div>
                    <div class="text-xl font-bold text-slate-900 font-mono tabular-nums tracking-tight mt-0.5"><?= $activeApiKeyCount ?> <span class="text-[13px] font-normal text-slate-400">/ <?= count($apiKeys) ?></span></div>
                    <div class="text-[11px] text-slate-400 mt-0.5">SHA-256 hashed storage</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                </div>
            </div>
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500 tracking-tight">Baseline Rate Governance</div>
                    <div class="text-xl font-bold text-slate-900 font-mono tabular-nums tracking-tight mt-0.5">60 <span class="text-[13px] font-normal text-slate-400">req / min</span></div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Burst bucket enforcement</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
            </div>
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500 tracking-tight">Access Firewall Guard</div>
                    <div class="text-xl font-bold text-slate-900 tracking-tight mt-0.5">IP Restriction</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Per-key CIDR allowlists</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- New Credentials Reveal Box -->
    <?php if ($newCredentials): ?>
        <div class="mb-6 p-5 bg-emerald-50/80 border border-emerald-200 rounded-xl shadow-subtle relative overflow-hidden">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-7 h-7 rounded-md bg-emerald-100 flex items-center justify-center text-emerald-700 shadow-xs border border-emerald-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h3 class="text-[15px] font-bold text-emerald-950 tracking-tight">API Key Generated Successfully</h3>
            </div>
            <p class="text-[12px] text-emerald-800 font-medium mb-4">Please download or copy your API Token now. <strong class="text-emerald-950">It will never be displayed again.</strong></p>
            
            <div class="mb-4">
                <label class="block text-[11px] font-bold text-emerald-900 uppercase tracking-wider mb-1.5">Authorization Bearer Token</label>
                <input readonly value="<?= e($newCredentials['api_key']) ?>" onclick="this.select()" class="block w-full px-3.5 py-2.5 text-[12px] border border-emerald-300 rounded-lg bg-white text-slate-800 font-mono shadow-subtle focus:outline-none focus:ring-2 focus:ring-emerald-500/20 cursor-copy" title="Click to copy">
            </div>

            <button type="button" onclick="downloadApiKey('<?= e($newCredentials['api_key']) ?>')" class="px-4 py-2 text-[12px] font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-subtle transition-all flex items-center gap-2 btn-tactile active:scale-[0.98]">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span>Download JSON Keyfile</span>
            </button>
        </div>
        <script>
            function downloadApiKey(token) {
                const data = JSON.stringify({ 
                    access_token: token, 
                    created_at: new Date().toISOString(),
                    note: "Use this token in the Authorization header: Bearer <token>"
                }, null, 2);
                const blob = new Blob([data], { type: 'application/json' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'whatsgateway_credentials.json';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            }
        </script>
    <?php endif; ?>

    <!-- Table Section -->
    <section class="bg-white rounded-xl border border-slate-200/80 shadow-card overflow-hidden mb-8">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <div>
                <h2 class="text-[14px] font-bold text-slate-900 tracking-tight">Active API Credentials</h2>
                <p class="text-[12px] text-slate-500 mt-0.5">Manage permissions, IP restrictions, and burst rate limits.</p>
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
                        <th class="px-5 py-3 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($apiKeys as $key): ?>
                        <tr class="hover:bg-slate-50/60 transition-colors group">
                            <td class="px-5 py-3.5">
                                <div class="text-[13px] font-bold text-slate-900"><?= e($key['name']) ?></div>
                                <div class="text-[11px] font-mono text-slate-500 mt-0.5"><code><?= e($key['key_prefix']) ?>...</code></div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-0.5 text-[10px] font-semibold rounded-md border shadow-subtle inline-flex items-center gap-1.5 <?= e($key['status'] === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200/80' : 'bg-rose-50 text-rose-700 border-rose-200/80') ?>">
                                    <span class="w-1.5 h-1.5 rounded-full <?= e($key['status'] === 'active' ? 'bg-emerald-500' : 'bg-rose-500') ?>"></span>
                                    <?= e(ucfirst($key['status'])) ?>
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-1.5 text-[11px] text-slate-600 font-mono tabular-nums">
                                    <span class="bg-slate-100/80 px-2 py-0.5 rounded border border-slate-200/60"><?= (int) $key['rate_per_minute'] ?>/m</span>
                                    <span class="bg-slate-100/80 px-2 py-0.5 rounded border border-slate-200/60"><?= (int) $key['rate_per_hour'] ?>/h</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <?php if ($key['ip_allowlist']): ?>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-50 text-blue-700 text-[11px] font-semibold rounded-md border border-blue-100">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                        Restricted
                                    </span>
                                <?php else: ?>
                                    <span class="text-[12px] text-slate-400 font-mono">Any IP</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" onclick="document.getElementById('settings-<?= (int) $key['id'] ?>').classList.toggle('hidden')" class="px-2.5 py-1 text-[11px] font-semibold text-slate-600 bg-white border border-slate-200 rounded-md hover:bg-slate-50 transition-colors shadow-subtle btn-tactile active:scale-[0.98]">
                                        Configure
                                    </button>
                                    
                                    <form method="post" action="<?= e(base_url('index.php?page=api-key-status')) ?>">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $key['id'] ?>">
                                        <input type="hidden" name="status" value="<?= e($key['status'] === 'active' ? 'disabled' : 'active') ?>">
                                        <button type="submit" class="px-2.5 py-1 text-[11px] font-semibold text-slate-600 bg-white border border-slate-200 rounded-md hover:bg-slate-50 transition-colors shadow-subtle btn-tactile active:scale-[0.98]">
                                            <?= e($key['status'] === 'active' ? 'Disable' : 'Enable') ?>
                                        </button>
                                    </form>
                                    
                                    <form method="post" action="<?= e(base_url('index.php?page=api-key-delete')) ?>" onsubmit="return confirm('Are you sure you want to permanently delete this API key? Apps using it will immediately lose access.');">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $key['id'] ?>">
                                        <button type="submit" class="p-1 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded transition-colors" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Expandable Configuration Drawer (Clean hairline styling, no border-l-2) -->
                        <tr id="settings-<?= (int) $key['id'] ?>" class="hidden bg-slate-50/80 border-b border-slate-200/80">
                            <td colspan="5" class="p-0">
                                <div class="p-5 sm:p-6 bg-slate-50/70 border-t border-b border-slate-200/70">
                                    <form method="post" action="<?= e(base_url('index.php?page=api-key-limits')) ?>" class="space-y-4">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $key['id'] ?>">
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                            <div class="md:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-3">
                                                <label class="block">
                                                    <span class="block text-[11px] font-semibold text-slate-700 mb-1">Rate Limit / Minute</span>
                                                    <input name="rate_per_minute" type="number" min="1" max="300" value="<?= (int) $key['rate_per_minute'] ?>" class="block w-full px-3 py-1.5 text-[12px] border border-slate-200 rounded-lg bg-white shadow-subtle focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-mono tabular-nums">
                                                </label>
                                                <label class="block">
                                                    <span class="block text-[11px] font-semibold text-slate-700 mb-1">Rate Limit / Hour</span>
                                                    <input name="rate_per_hour" type="number" min="1" max="5000" value="<?= (int) $key['rate_per_hour'] ?>" class="block w-full px-3 py-1.5 text-[12px] border border-slate-200 rounded-lg bg-white shadow-subtle focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-mono tabular-nums">
                                                </label>
                                                <label class="block">
                                                    <span class="block text-[11px] font-semibold text-slate-700 mb-1">Rate Limit / Day</span>
                                                    <input name="rate_per_day" type="number" min="1" max="50000" value="<?= (int) $key['rate_per_day'] ?>" class="block w-full px-3 py-1.5 text-[12px] border border-slate-200 rounded-lg bg-white shadow-subtle focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-mono tabular-nums">
                                                </label>
                                                
                                                <label class="block md:col-span-3">
                                                    <span class="block text-[11px] font-semibold text-slate-700 mb-1 flex items-center gap-1.5">
                                                        IP Allowlist 
                                                        <span class="text-[10px] font-normal text-slate-400 font-mono">(Comma separated, e.g. 192.168.1.1, 10.0.0.5)</span>
                                                    </span>
                                                    <input name="ip_allowlist" value="<?= e($key['ip_allowlist'] ?? '') ?>" placeholder="Leave blank to allow any IP" class="block w-full px-3 py-1.5 text-[12px] border border-slate-200 rounded-lg bg-white shadow-subtle focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-mono">
                                                </label>
                                                <label class="flex items-center gap-2 mt-2 cursor-pointer md:col-span-3">
                                                    <input type="checkbox" name="allow_humanize" value="1" <?= empty($key['allow_humanize']) ? '' : 'checked' ?> class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 focus:ring-offset-0 transition-colors cursor-pointer">
                                                    <span class="text-[12px] font-semibold text-slate-700">Force Humanized Anti-Ban Delivery for this API Key</span>
                                                </label>
                                            </div>
                                            
                                            <div class="flex items-end justify-end">
                                                <button type="submit" class="w-full md:w-auto px-4 py-2 text-[12px] font-semibold text-white bg-slate-900 rounded-lg shadow-subtle hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition-all btn-tactile active:scale-[0.98]">
                                                    Save Configuration
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$apiKeys): ?>
                        <tr><td colspan="5" class="px-5 py-12 text-center text-[13px] text-slate-500">No API keys created yet. Generate one above to integrate with your apps.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

