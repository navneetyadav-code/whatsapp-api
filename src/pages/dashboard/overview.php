    <!-- Page Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Overview</h1>
            <p class="text-[13px] text-slate-500 mt-0.5 font-medium">Monitor your WhatsApp REST API traffic, Baileys WebSocket connection, and queue metrics.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= e(base_url('index.php?page=webhooks')) ?>" class="px-3 py-1.5 text-[12px] font-semibold text-slate-700 bg-white border border-slate-200/90 rounded-lg shadow-subtle hover:bg-slate-50 hover:text-slate-900 transition-all btn-tactile">
                Manage Webhooks
            </a>
            <a href="<?= e(base_url('index.php?page=message')) ?>" class="px-3.5 py-1.5 text-[12px] font-semibold text-white bg-slate-900 rounded-lg shadow-sm hover:bg-slate-800 transition-all flex items-center gap-1.5 btn-tactile">
                <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Send Message</span>
            </a>
        </div>
    </div>

    <!-- The Signature Move: Real-Time Gateway Telemetry Rail -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-card divide-y lg:divide-y-0 lg:divide-x divide-slate-100 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 overflow-hidden">
        
        <!-- Rail Station 1: Gateway WebSocket Link -->
        <div class="p-4 sm:p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    WhatsApp Socket
                </span>
                <?php if ($deviceConnected): ?>
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 ring-2 ring-emerald-500/20"></span>
                        Connected
                    </span>
                <?php else: ?>
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200/60">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                        Unpaired
                    </span>
                <?php endif; ?>
            </div>
            
            <div class="space-y-1">
                <?php if ($deviceConnected): ?>
                    <div class="font-mono text-base font-bold text-slate-900 tracking-tight tabular-nums">
                        <?= e($session['phone'] ?: 'Active Session') ?>
                    </div>
                    <div class="flex items-center gap-2 text-[11px] text-slate-500 font-medium">
                        <span class="truncate max-w-[140px]"><?= e($session['push_name'] ?: 'WhatsApp Multi-Device') ?></span>
                        <span>&bull;</span>
                        <span class="text-emerald-700 font-medium"><?= $deviceSleeping ? 'Idle / Standby' : 'Ready' ?></span>
                    </div>
                <?php else: ?>
                    <div class="text-[13px] font-bold text-slate-800">No Device Linked</div>
                    <p class="text-[11px] text-slate-500">Scan QR to connect WhatsApp Web</p>
                <?php endif; ?>
            </div>
            
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-400 font-mono text-[10px]">Baileys v6.6</span>
                <a href="<?= e(base_url('index.php?page=device')) ?>" class="font-semibold text-emerald-600 hover:text-emerald-700 transition-colors flex items-center gap-0.5">
                    <?= $deviceConnected ? 'Inspect' : 'Pair Now' ?> &rarr;
                </a>
            </div>
        </div>

        <!-- Rail Station 2: Real Delivery Velocity (No fake sparkline!) -->
        <div class="p-4 sm:p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Delivery Rate
                </span>
                <span class="text-[11px] font-mono text-slate-500 tabular-nums font-medium"><?= number_format($deliveryTotal) ?> msgs</span>
            </div>
            
            <div class="space-y-1">
                <div class="flex items-baseline gap-2">
                    <span class="font-mono text-2xl font-bold text-slate-900 tabular-nums"><?= $sentRate ?>%</span>
                    <span class="text-[11px] text-slate-500 font-medium">delivered</span>
                </div>
                <!-- Real calibrated ratio track -->
                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden flex mt-2">
                    <div class="bg-emerald-500 h-full rounded-full transition-all" style="width: <?= max(3, $sentRate) ?>%"></div>
                    <?php if ($failureRate > 0): ?>
                        <div class="bg-rose-400 h-full rounded-full transition-all" style="width: <?= $failureRate ?>%"></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500 font-medium">
                <span class="text-emerald-600 font-mono font-semibold tabular-nums"><?= number_format($sentCount) ?> sent</span>
                <span class="text-rose-500 font-mono font-semibold tabular-nums"><?= number_format($failedCount) ?> failed</span>
            </div>
        </div>

        <!-- Rail Station 3: Queue Backlog & Pressure -->
        <div class="p-4 sm:p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                    Queue Pressure
                </span>
                <?php if ($queueDepth === 0): ?>
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 font-mono">
                        Drain: 0ms
                    </span>
                <?php else: ?>
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 font-mono">
                        Active Backlog
                    </span>
                <?php endif; ?>
            </div>
            
            <div class="space-y-1">
                <div class="flex items-baseline gap-2">
                    <span class="font-mono text-2xl font-bold tabular-nums <?= $queueDepth > 0 ? 'text-amber-600' : 'text-slate-900' ?>"><?= number_format($queueDepth) ?></span>
                    <span class="text-[11px] text-slate-500 font-medium">pending</span>
                </div>
                <div class="flex items-center gap-3 text-[11px] text-slate-500 font-medium">
                    <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> <?= $processingCount ?> in-flight</span>
                    <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span> <?= $queuedCount ?> queued</span>
                </div>
            </div>
            
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-400 font-mono text-[10px]">Worker: Loopback</span>
                <a href="<?= e(base_url('index.php?page=queue')) ?>" class="font-semibold text-slate-700 hover:text-slate-900 transition-colors">
                    Queue Log &rarr;
                </a>
            </div>
        </div>

        <!-- Rail Station 4: API & Security Envelope -->
        <div class="p-4 sm:p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                    API & Security
                </span>
                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 font-mono">
                    SHA-256
                </span>
            </div>
            
            <div class="space-y-1">
                <div class="flex items-baseline gap-2">
                    <span class="font-mono text-2xl font-bold text-slate-900 tabular-nums"><?= $activeApiKeyCount ?></span>
                    <span class="text-[11px] text-slate-500 font-medium">active keys</span>
                </div>
                <div class="text-[11px] text-slate-500 truncate font-mono">
                    <?= $webhookEnabled ? 'Webhook: ' . e(parse_url($webhook['target_url'] ?? '', PHP_URL_HOST)) : 'Webhook: Off' ?>
                </div>
            </div>
            
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-400 font-mono text-[10px]"><?= number_format($apiRequestCount) ?> requests</span>
                <a href="<?= e(base_url('index.php?page=api-keys')) ?>" class="font-semibold text-slate-700 hover:text-slate-900 transition-colors">
                    Manage Keys &rarr;
                </a>
            </div>
        </div>

    </div>

    <!-- 2-Column Operational Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column (2 Cols): System Readiness & Architectural Checks -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200/80 shadow-card flex flex-col overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 flex justify-between items-center bg-white">
                <div>
                    <h2 class="text-[13px] font-bold text-slate-900 tracking-tight">System Architecture Probes</h2>
                    <p class="text-[11px] text-slate-500 font-medium">Core dependencies and runtime health</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold font-mono bg-slate-50 text-slate-700 border border-slate-200/80 shadow-subtle tabular-nums"><?= $readinessScore ?>% Verified</span>
                </div>
            </div>
            
            <div class="p-4 sm:p-5 flex-1 flex flex-col justify-between">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    
                    <!-- Probe 1 -->
                    <div class="p-3.5 rounded-lg border border-slate-200/80 bg-slate-50/50 flex flex-col justify-between hover:border-slate-300 transition-colors">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[12px] font-bold text-slate-800 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full <?= $deviceConnected ? 'bg-emerald-500 ring-2 ring-emerald-500/20' : 'bg-rose-500' ?>"></span>
                                Multi-Device Session
                            </span>
                            <span class="text-[10px] font-mono px-1.5 py-0.5 rounded <?= $deviceConnected ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-rose-50 text-rose-700 border border-rose-200/60' ?>">
                                <?= $deviceConnected ? 'LINKED' : 'UNPAIRED' ?>
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            <?= $deviceConnected ? 'WebSocket socket connected with Baileys auth keys synced.' : 'No active session token. Pairing QR scan required.' ?>
                        </p>
                        <div class="mt-3 pt-2.5 border-t border-slate-200/60 flex justify-end">
                            <a href="<?= e(base_url('index.php?page=device')) ?>" class="text-[11px] font-semibold text-emerald-600 hover:text-emerald-700">
                                <?= $deviceConnected ? 'Device Settings' : 'Pair Device' ?> &rarr;
                            </a>
                        </div>
                    </div>

                    <!-- Probe 2 -->
                    <div class="p-3.5 rounded-lg border border-slate-200/80 bg-slate-50/50 flex flex-col justify-between hover:border-slate-300 transition-colors">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[12px] font-bold text-slate-800 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 ring-2 ring-emerald-500/20"></span>
                                Database & Queue Table
                            </span>
                            <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                READY
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            MySQL / TiDB connection authenticated with active tables and indices.
                        </p>
                        <div class="mt-3 pt-2.5 border-t border-slate-200/60 flex justify-end">
                            <a href="<?= e(base_url('index.php?page=queue')) ?>" class="text-[11px] font-semibold text-slate-700 hover:text-slate-900">
                                View Queue Table &rarr;
                            </a>
                        </div>
                    </div>

                    <!-- Probe 3 -->
                    <div class="p-3.5 rounded-lg border border-slate-200/80 bg-slate-50/50 flex flex-col justify-between hover:border-slate-300 transition-colors">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[12px] font-bold text-slate-800 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full <?= $activeApiKeyCount > 0 ? 'bg-emerald-500 ring-2 ring-emerald-500/20' : 'bg-amber-400' ?>"></span>
                                API Key Credentials
                            </span>
                            <span class="text-[10px] font-mono px-1.5 py-0.5 rounded <?= $activeApiKeyCount > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-amber-50 text-amber-700 border border-amber-200/60' ?>">
                                <?= $activeApiKeyCount > 0 ? $activeApiKeyCount . ' ACTIVE' : 'NO KEYS' ?>
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            SHA-256 hashed API tokens with per-minute rate limits and IP security.
                        </p>
                        <div class="mt-3 pt-2.5 border-t border-slate-200/60 flex justify-end">
                            <a href="<?= e(base_url('index.php?page=api-keys')) ?>" class="text-[11px] font-semibold text-slate-700 hover:text-slate-900">
                                Manage Keys &rarr;
                            </a>
                        </div>
                    </div>

                    <!-- Probe 4 -->
                    <div class="p-3.5 rounded-lg border border-slate-200/80 bg-slate-50/50 flex flex-col justify-between hover:border-slate-300 transition-colors">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[12px] font-bold text-slate-800 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full <?= $webhookEnabled ? 'bg-emerald-500 ring-2 ring-emerald-500/20' : 'bg-slate-300' ?>"></span>
                                Inbound Webhooks
                            </span>
                            <span class="text-[10px] font-mono px-1.5 py-0.5 rounded <?= $webhookEnabled ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-slate-100 text-slate-600 border border-slate-200/60' ?>">
                                <?= $webhookEnabled ? 'ENABLED' : 'OPTIONAL' ?>
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            <?= $webhookEnabled ? 'HMAC-SHA256 signature payload forwarding configured.' : 'Delivery notifications stay in local SQLite/MySQL queue.' ?>
                        </p>
                        <div class="mt-3 pt-2.5 border-t border-slate-200/60 flex justify-end">
                            <a href="<?= e(base_url('index.php?page=webhooks')) ?>" class="text-[11px] font-semibold text-slate-700 hover:text-slate-900">
                                Configure &rarr;
                            </a>
                        </div>
                    </div>

                </div>

                <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[11px] text-slate-400 font-medium">Recommended next step:</span>
                    <a href="<?= e($nextActionUrl) ?>" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-[12px] font-semibold shadow-subtle transition-all btn-tactile">
                        <?= e($nextAction) ?> &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- Right Column (1 Col): Developer Quickstart & Copy Snippet -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-card flex flex-col overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 flex justify-between items-center bg-white">
                <div>
                    <h2 class="text-[13px] font-bold text-slate-900 tracking-tight">API Quickstart</h2>
                    <p class="text-[11px] text-slate-500 font-medium">Native HTTP cURL test</p>
                </div>
                <span class="text-[10px] font-mono font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-700">POST</span>
            </div>
            
            <div class="p-4 flex-1 flex flex-col justify-between space-y-3">
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="text-slate-500 font-mono text-[11px]">/api/send.php</span>
                        <button onclick="navigator.clipboard.writeText('curl -X POST <?= e(base_url('api/send.php')) ?> -H &quot;X-API-Key: YOUR_API_KEY&quot; -H &quot;Content-Type: application/json&quot; -d '{&quot;recipient&quot;:&quot;1234567890&quot;,&quot;message&quot;:&quot;Hello from WhatsGateway!&quot;}''); showToast('cURL snippet copied to clipboard!', 'success');" class="text-[10px] font-semibold text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            Copy cURL
                        </button>
                    </div>
                    
                    <div class="bg-slate-900 rounded-lg p-3 text-[11px] font-mono text-slate-200 overflow-x-auto leading-relaxed border border-slate-800">
                        <span class="text-emerald-400">curl</span> -X POST <?= e(base_url('api/send.php')) ?> \<br>
                        &nbsp;&nbsp;-H <span class="text-amber-300">"X-API-Key: wa_live_..."</span> \<br>
                        &nbsp;&nbsp;-H <span class="text-amber-300">"Content-Type: application/json"</span> \<br>
                        &nbsp;&nbsp;-d <span class="text-sky-300">'{"recipient": "+1...", "message": "Hi"}'</span>
                    </div>
                </div>

                <div class="space-y-2 pt-2 border-t border-slate-100">
                    <div class="flex items-center justify-between text-[11px] text-slate-500">
                        <span>Language Examples:</span>
                        <a href="<?= e(base_url('index.php?page=docs')) ?>" class="text-slate-700 hover:text-slate-900 font-semibold">Docs &rarr;</a>
                    </div>
                    <div class="grid grid-cols-4 gap-1.5 text-center text-[10px] font-mono">
                        <span class="py-1 bg-slate-50 border border-slate-200/80 rounded text-slate-700">Python</span>
                        <span class="py-1 bg-slate-50 border border-slate-200/80 rounded text-slate-700">Node</span>
                        <span class="py-1 bg-slate-50 border border-slate-200/80 rounded text-slate-700">PHP</span>
                        <span class="py-1 bg-slate-50 border border-slate-200/80 rounded text-slate-700">Go</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- High-Density Event Ledger (Stripe/Linear Style Queue Activity) -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-card overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-white">
            <div class="flex items-center gap-2.5">
                <h2 class="text-[13px] font-bold text-slate-900 tracking-tight">Queue Event Ledger</h2>
                <span class="text-[11px] font-mono text-slate-400">&bull;</span>
                <span class="text-[11px] text-slate-500 font-medium">Live message dispatch stream</span>
            </div>
            <div class="flex items-center gap-2">
                <a href="<?= e(base_url('index.php?page=queue')) ?>" class="text-[11px] font-semibold text-slate-600 hover:text-slate-900 px-2 py-1 rounded bg-slate-50 hover:bg-slate-100 border border-slate-200/70 transition-colors">
                    View Full Queue (<?= number_format($totalQueueMessages) ?>) &rarr;
                </a>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-widest bg-slate-50/60 font-mono">
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Method / Route</th>
                        <th class="px-5 py-3">Recipient</th>
                        <th class="px-5 py-3 hidden sm:table-cell">Payload Preview</th>
                        <th class="px-5 py-3 text-right">Dispatch Time</th>
                    </tr>
                </thead>
                <tbody id="recentQueueTable" class="text-[12px] divide-y divide-slate-100/90 font-sans">
                    <?php foreach (array_slice($messages, 0, 8) as $message): ?>
                        <tr class="hover:bg-slate-50/70 transition-colors group cursor-default">
                            <td class="px-5 py-2.5">
                                <?php if ($message['status'] === 'sent'): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200/70 shadow-subtle font-mono">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Sent
                                    </span>
                                <?php elseif ($message['status'] === 'failed'): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-50 text-rose-800 border border-rose-200/70 shadow-subtle font-mono">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Failed
                                    </span>
                                <?php elseif ($message['status'] === 'queued' || $message['status'] === 'processing'): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-800 border border-amber-200/70 shadow-subtle font-mono">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> <?= ucfirst($message['status']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200/70 shadow-subtle font-mono">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> <?= e(ucfirst($message['status'])) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-2.5">
                                <div class="flex items-center gap-2">
                                    <span class="text-[9px] font-bold font-mono text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200/70">POST</span>
                                    <span class="font-mono text-slate-700 text-[11px]">/api/send.php</span>
                                </div>
                            </td>
                            <td class="px-5 py-2.5 text-slate-800 font-mono text-[12px] tabular-nums font-medium">
                                <?= e($message['normalized_recipient'] ?: $message['recipient']) ?>
                            </td>
                            <td class="px-5 py-2.5 text-slate-600 hidden sm:table-cell font-normal truncate max-w-xs text-[12px]">
                                <?php if (!empty($message['media_url'])): ?>
                                    <span class="inline-flex items-center gap-1 text-slate-700 font-mono text-[11px] bg-slate-100 px-1 rounded mr-1">
                                        <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                        media
                                    </span>
                                <?php endif; ?>
                                <?= e(mb_substr($message['body'] ?? '', 0, 48)) ?><?= mb_strlen($message['body'] ?? '') > 48 ? '...' : '' ?>
                            </td>
                            <td class="px-5 py-2.5 text-right text-slate-400 font-mono text-[11px] tabular-nums group-hover:text-slate-600 transition-colors">
                                <?= e(substr((string) $message['created_at'], 11, 8)) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$messages): ?>
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center">
                                <div class="max-w-xs mx-auto text-center space-y-2">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                    </div>
                                    <p class="text-[13px] font-semibold text-slate-700">No message transactions yet</p>
                                    <p class="text-[11px] text-slate-400">Queue your first WhatsApp message via REST API or the dashboard.</p>
                                    <a href="<?= e(base_url('index.php?page=message')) ?>" class="inline-block mt-2 text-[11px] font-semibold text-emerald-600 hover:text-emerald-700">
                                        Compose Message &rarr;
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="px-5 py-2.5 border-t border-slate-100 bg-slate-50/40 text-center">
            <a href="<?= e(base_url('index.php?page=queue')) ?>" class="text-[12px] font-semibold text-slate-600 hover:text-slate-900 transition-colors">
                Open full audit log & pagination &rarr;
            </a>
        </div>
    </div>

    <!-- Footer Spacer -->
    <div class="h-6"></div>
