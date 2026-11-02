    <!-- Page Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Overview</h1>
            <p class="text-[13px] text-slate-500 mt-0.5 font-medium">Monitor message delivery, queue health, and your WhatsApp connection.</p>
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
                    WhatsApp connection
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
                    Outbound delivery
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
                    Queue backlog
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
                <?php if ($activeApiKeyCount === 0): ?>
                    <a href="<?= e(base_url('index.php?page=api-keys')) ?>" class="font-semibold text-emerald-600 hover:text-emerald-700 transition-colors flex items-center gap-0.5">
                        + Create Key &rarr;
                    </a>
                <?php else: ?>
                    <a href="<?= e(base_url('index.php?page=api-keys')) ?>" class="font-semibold text-slate-700 hover:text-slate-900 transition-colors">
                        Manage Keys &rarr;
                    </a>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- OpenWA-Adapted Message Traffic & Analytics Graphs -->
    <section class="dashboard-charts space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center border border-emerald-200/60 shadow-xs shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 20V10M12 20V4M6 20v-6"></path></svg>
                </div>
                <div>
                    <h2 class="text-sm sm:text-base font-bold text-slate-900 tracking-tight flex items-center gap-2">
                        Message Traffic & Analytics
                        <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60 font-mono">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> LIVE
                        </span>
                    </h2>
                    <p class="text-[11px] text-slate-500 font-medium">Message volume, payload distribution, and active chats</p>
                </div>
            </div>

            <!-- Segmented Pill Period Selector (24h / 7d / 30d) -->
            <div class="inline-flex gap-1 p-1 bg-slate-100 rounded-lg border border-slate-200/80 shadow-xs self-start sm:self-auto shrink-0" role="group" aria-label="Message traffic period selector">
                <button type="button" data-period="24h" class="chart-period-btn px-3 py-1 text-[11px] font-semibold rounded-md transition-all text-slate-600 hover:text-slate-900 border border-transparent cursor-pointer focus:outline-hidden focus:ring-1 focus:ring-slate-400">
                    24 Hours
                </button>
                <button type="button" data-period="7d" class="chart-period-btn px-3 py-1 text-[11px] font-bold rounded-md transition-all bg-white text-slate-900 shadow-xs border border-slate-200/60 cursor-pointer focus:outline-hidden focus:ring-1 focus:ring-slate-400">
                    7 Days
                </button>
                <button type="button" data-period="30d" class="chart-period-btn px-3 py-1 text-[11px] font-semibold rounded-md transition-all text-slate-600 hover:text-slate-900 border border-transparent cursor-pointer focus:outline-hidden focus:ring-1 focus:ring-slate-400">
                    30 Days
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- Chart 1: Messages Over Time (Wide - Spans full width) -->
            <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200/80 p-4 sm:p-5 shadow-card flex flex-col justify-between min-w-0">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 mb-3">
                    <div>
                        <h3 class="text-[12px] font-bold text-slate-900 tracking-tight flex items-center gap-2">
                            <span>Messages Over Time (Sent vs. Received)</span>
                            <span id="timeSeriesPeriodLabel" class="text-[10px] font-mono normal-case px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 font-semibold">7 Days Volume (Daily)</span>
                        </h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Dual-stream comparison between outbound automated replies and inbound messages</p>
                    </div>
                    <div class="flex flex-wrap items-center justify-start lg:justify-end gap-2 text-[11px] font-medium shrink-0 pt-1 lg:pt-0">
                        <div class="flex items-center gap-1.5 px-2 py-0.5 rounded bg-slate-50 border border-slate-200/60">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 shadow-xs"></span>
                            <span class="text-slate-600 font-medium">Sent: <b id="chartSentTotal" class="font-mono text-slate-900 tabular-nums font-bold"><?= (int) ($chartStats['totals']['sent'] ?? 0) ?></b></span>
                        </div>
                        <div class="flex items-center gap-1.5 px-2 py-0.5 rounded bg-slate-50 border border-slate-200/60">
                            <span class="w-2 h-2 rounded-full bg-blue-500 shadow-xs"></span>
                            <span class="text-slate-600 font-medium">Received: <b id="chartRecvTotal" class="font-mono text-slate-900 tabular-nums font-bold"><?= (int) ($chartStats['totals']['received'] ?? 0) ?></b></span>
                        </div>
                        <div class="flex items-center gap-1.5 px-2 py-0.5 rounded bg-slate-50 border border-slate-200/60">
                            <span class="w-2 h-2 rounded-full bg-cyan-500 shadow-xs"></span>
                            <span class="text-slate-600 font-medium">Delivered: <b id="chartDeliveredTotal" class="font-mono text-slate-900 tabular-nums font-bold"><?= (int) ($chartStats['totals']['delivered'] ?? 0) ?></b></span>
                        </div>
                        <div class="flex items-center gap-1.5 px-2 py-0.5 rounded bg-slate-50 border border-slate-200/60">
                            <span class="w-2 h-2 rounded-full bg-rose-500 shadow-xs"></span>
                            <span class="text-slate-600 font-medium">Failed: <b id="chartFailedTotal" class="font-mono text-slate-900 tabular-nums font-bold"><?= (int) ($chartStats['totals']['failed'] ?? 0) ?></b></span>
                        </div>
                        <div class="flex items-center gap-1.5 pl-1">
                            <span id="analyticsStatus" class="text-[10px] text-emerald-600 font-semibold" aria-live="polite">Live data</span>
                            <button id="analyticsRetry" type="button" class="hidden text-[10px] font-semibold text-rose-600 hover:text-rose-700 underline underline-offset-2 cursor-pointer">Retry</button>
                        </div>
                    </div>
                </div>

                <div class="relative w-full h-64 sm:h-72">
                    <canvas id="chartTimeSeries" role="img" aria-label="Messages Over Time Chart"></canvas>
                    <div id="timeSeriesEmpty" class="hidden absolute inset-0 bg-white/90 backdrop-blur-xs flex flex-col items-center justify-center text-center p-4">
                        <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <p class="text-[12px] font-semibold text-slate-700">No message traffic in this period</p>
                        <p class="text-[11px] text-slate-400">Incoming and outgoing messages will appear here live.</p>
                    </div>
                </div>
            </div>

            <!-- Chart 2: By Message Type (Donut) -->
            <div class="bg-white rounded-xl border border-slate-200/80 p-4 sm:p-5 shadow-card flex flex-col justify-between min-w-0">
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <h3 class="text-[12px] font-bold text-slate-900 tracking-tight uppercase tracking-wider">By Message Type</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Text, media, and document breakdown</p>
                    </div>
                    <span id="chartTypeTotal" class="text-[10px] font-mono px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-bold tabular-nums">
                        <?= (int) ($chartStats['totals']['total'] ?? 0) ?> msgs
                    </span>
                </div>

                <div class="relative w-full h-56 flex items-center justify-center my-1">
                    <canvas id="chartByType" role="img" aria-label="Payload Type Distribution Chart"></canvas>
                    <div id="byTypeEmpty" class="hidden absolute inset-0 bg-white/90 flex flex-col items-center justify-center text-center p-4">
                        <p class="text-[11px] text-slate-400">No payload types recorded in this period</p>
                    </div>
                </div>

                <!-- Custom Type Badges -->
                <div id="typeBadgesContainer" class="flex flex-wrap items-center justify-center gap-2 pt-3 border-t border-slate-100 text-[11px] font-mono">
                    <!-- Dynamic badges rendered by JS -->
                </div>
            </div>

            <!-- Chart 3: Top Active Chats (Horizontal Bar) -->
            <div class="bg-white rounded-xl border border-slate-200/80 p-4 sm:p-5 shadow-card flex flex-col justify-between min-w-0">
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <h3 class="text-[12px] font-bold text-slate-900 tracking-tight uppercase tracking-wider">Top Active Chats</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Ranked by message volume</p>
                    </div>
                    <span id="topChatsCountBadge" class="text-[10px] font-mono px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-bold tabular-nums">
                        <?= count($chartStats['topChats'] ?? []) ?> chats
                    </span>
                </div>

                <div class="relative w-full h-56 flex items-center justify-center my-1">
                    <canvas id="chartTopChats" role="img" aria-label="Top Active Chats Chart"></canvas>
                    <div id="topChatsEmpty" class="hidden absolute inset-0 bg-white/90 flex flex-col items-center justify-center text-center p-4">
                        <p class="text-[11px] text-slate-400">No active chats in this period</p>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                    <span class="text-slate-400 font-medium"><?= !empty($isPersonalMode) ? 'Personal Assistant Studio:' : 'Interactive live chat:' ?></span>
                    <?php if (!empty($isPersonalMode)): ?>
                        <a href="<?= e(base_url('index.php?page=chatbot')) ?>" class="font-semibold text-emerald-600 hover:text-emerald-700 flex items-center gap-1 transition-colors">
                            Manage Assistant &rarr;
                        </a>
                    <?php else: ?>
                        <a href="<?= e(base_url('index.php?page=live-chat')) ?>" class="font-semibold text-emerald-600 hover:text-emerald-700 flex items-center gap-1 transition-colors">
                            Open Inbox &rarr;
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    <!-- Developer quickstart (Interactive Language Switcher) -->
    <div class="grid grid-cols-1 gap-6">

        <div class="bg-white rounded-xl border border-slate-200/80 shadow-card flex flex-col overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 bg-white">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <h2 class="text-[13px] font-bold text-slate-900 tracking-tight">API Quickstart Integration</h2>
                    <span class="text-[10px] font-mono font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-700">POST /api/send.php</span>
                </div>
                
                <div class="flex items-center gap-2">
                    <!-- Interactive Language Tabs -->
                    <div class="inline-flex items-center gap-1 p-0.5 bg-slate-100 rounded-lg border border-slate-200/80 text-[10px] font-mono" role="tablist" aria-label="API code language selector">
                        <button type="button" class="dash-code-tab active px-2.5 py-1 rounded-md font-bold transition-all bg-white text-slate-900 shadow-2xs border border-slate-200/60 cursor-pointer" data-lang="curl">cURL</button>
                        <button type="button" class="dash-code-tab px-2.5 py-1 rounded-md font-medium text-slate-600 hover:text-slate-900 transition-all cursor-pointer" data-lang="node">Node.js</button>
                        <button type="button" class="dash-code-tab px-2.5 py-1 rounded-md font-medium text-slate-600 hover:text-slate-900 transition-all cursor-pointer" data-lang="python">Python</button>
                        <button type="button" class="dash-code-tab px-2.5 py-1 rounded-md font-medium text-slate-600 hover:text-slate-900 transition-all cursor-pointer" data-lang="php">PHP</button>
                    </div>

                    <a href="<?= e(base_url('index.php?page=docs')) ?>" class="text-[11px] font-semibold text-slate-600 hover:text-slate-900 px-2 py-1 rounded bg-slate-50 hover:bg-slate-100 border border-slate-200/70 transition-colors shrink-0">
                        Docs &rarr;
                    </a>
                </div>
            </div>
            
            <div id="quickstartContent" class="p-4 flex-1 flex flex-col justify-between space-y-3">
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="text-slate-500 font-mono text-[11px]">Request payload example:</span>
                        <button id="copyQuickstartBtn" type="button" class="text-[10px] font-semibold text-emerald-600 hover:text-emerald-700 flex items-center gap-1 cursor-pointer">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            <span id="copyQuickstartLabel">Copy Code</span>
                        </button>
                    </div>
                    
                    <div class="bg-slate-900 rounded-lg p-3 text-[11px] font-mono text-slate-200 overflow-x-auto leading-relaxed border border-slate-800">
                        <pre id="quickstartCodeBox" class="text-slate-200"><code><span class="text-emerald-400">curl</span> -X POST "<?= e(base_url('api/send.php')) ?>" \
  -H <span class="text-amber-300">"Content-Type: application/json"</span> \
  -H <span class="text-amber-300">"X-API-Key: wa_live_YOUR_API_KEY_HERE"</span> \
  -d <span class="text-sky-300">'{
    "to": "919876543210",
    "message": "Hello Test Message.",
    "humanize": true
  }'</span></code></pre>
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
                <span class="text-[11px] text-slate-500 font-medium">Live message dispatch queue</span>
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
                Open full message queue &rarr;
            </a>
        </div>
    </div>

    <!-- Footer Spacer -->
    <div class="h-6"></div>

    <!-- Chart.js Controller for OpenWA Graphs -->
    <script>
    (function () {
        const initialStats = <?= json_encode($chartStats, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
        const statsApiUrl = '<?= e(base_url('api/stats/messages.php')) ?>';

        let chartTimeSeries = null;
        let chartByType = null;
        let chartTopChats = null;
        let currentPeriod = '7d';

        const TYPE_COLORS = {
            text: '#22c55e',      // WhatsApp Green
            image: '#3b82f6',     // Blue
            document: '#f59e0b',  // Amber
            audio: '#06b6d4',     // Cyan
            video: '#14b8a6',     // Teal
            other: '#a855f7'      // Purple
        };

        function shortenLabel(str, maxLen = 16) {
            if (!str) return '';
            return str.length > maxLen ? str.slice(0, maxLen - 1) + '…' : str;
        }

        function createAreaGradient(ctx, colorRgb) {
            const gradient = ctx.createLinearGradient(0, 0, 0, 260);
            gradient.addColorStop(0, `rgba(${colorRgb}, 0.28)`);
            gradient.addColorStop(1, `rgba(${colorRgb}, 0.0)`);
            return gradient;
        }

        function initTimeSeriesChart(data) {
            const canvas = document.getElementById('chartTimeSeries');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            const gSent = createAreaGradient(ctx, '34, 197, 94');
            const gRecv = createAreaGradient(ctx, '59, 130, 246');

            const labels = (data.timeSeries || []).map(p => p.label);
            const sentValues = (data.timeSeries || []).map(p => p.sent);
            const recvValues = (data.timeSeries || []).map(p => p.received);
            const deliveredValues = (data.timeSeries || []).map(p => p.delivered);
            const failedValues = (data.timeSeries || []).map(p => p.failed);

            chartTimeSeries = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Sent (Bot / Agent)',
                            data: sentValues,
                            borderColor: '#22c55e',
                            backgroundColor: gSent,
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2,
                            pointRadius: 2.5,
                            pointHoverRadius: 5,
                            pointBackgroundColor: '#22c55e'
                        },
                        {
                            label: 'Received (Contact)',
                            data: recvValues,
                            borderColor: '#3b82f6',
                            backgroundColor: gRecv,
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2,
                            pointRadius: 2.5,
                            pointHoverRadius: 5,
                            pointBackgroundColor: '#3b82f6'
                        },
                        {
                            label: 'Delivered',
                            data: deliveredValues,
                            borderColor: '#06b6d4',
                            backgroundColor: 'transparent',
                            fill: false,
                            tension: 0.35,
                            borderWidth: 2,
                            pointRadius: 2.5,
                            pointHoverRadius: 5,
                            pointBackgroundColor: '#06b6d4'
                        },
                        {
                            label: 'Failed',
                            data: failedValues,
                            borderColor: '#f43f5e',
                            backgroundColor: 'transparent',
                            fill: false,
                            tension: 0.35,
                            borderWidth: 2,
                            borderDash: [6, 4],
                            pointRadius: 2.5,
                            pointHoverRadius: 5,
                            pointBackgroundColor: '#f43f5e'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleColor: '#f8fafc',
                            bodyColor: '#cbd5e1',
                            borderColor: '#334155',
                            borderWidth: 1,
                            padding: 10,
                            boxPadding: 4,
                            cornerRadius: 8,
                            usePointStyle: true
                        }
                    },
                    scales: {
                        x: {
                            grid: { color: '#f1f5f9', drawBorder: false },
                            ticks: {
                                font: { family: "'JetBrains Mono', monospace", size: 10 },
                                color: '#64748b',
                                maxRotation: 0,
                                autoSkip: true,
                                maxTicksLimit: 12
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9', drawBorder: false },
                            ticks: {
                                precision: 0,
                                font: { family: "'JetBrains Mono', monospace", size: 10 },
                                color: '#64748b'
                            }
                        }
                    }
                }
            });
        }

        function initByTypeChart(data) {
            const canvas = document.getElementById('chartByType');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');

            const byTypeObj = data.byType || {};
            const keys = Object.keys(byTypeObj).filter(k => byTypeObj[k] > 0);
            const labels = keys.map(k => k.charAt(0).toUpperCase() + k.slice(1));
            const values = keys.map(k => byTypeObj[k]);
            const colors = keys.map(k => TYPE_COLORS[k] || TYPE_COLORS.other);

            chartByType = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: colors,
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleColor: '#f8fafc',
                            bodyColor: '#cbd5e1',
                            borderColor: '#334155',
                            borderWidth: 1,
                            padding: 10,
                            cornerRadius: 8
                        }
                    }
                }
            });

            renderTypeBadges(byTypeObj);
        }

        function renderTypeBadges(byTypeObj) {
            const container = document.getElementById('typeBadgesContainer');
            if (!container) return;
            container.innerHTML = '';

            const entries = Object.entries(byTypeObj || {});
            if (entries.length === 0) {
                container.innerHTML = '<span class="text-slate-400">No payload types</span>';
                return;
            }

            entries.forEach(([type, count]) => {
                const color = TYPE_COLORS[type] || TYPE_COLORS.other;
                const pill = document.createElement('div');
                pill.className = 'inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-slate-50 border border-slate-200/70 text-slate-700 shadow-2xs';
                pill.innerHTML = `
                    <span class="w-2 h-2 rounded-full" style="background-color: ${color}"></span>
                    <span class="capitalize">${type}:</span>
                    <b class="text-slate-900 tabular-nums">${count}</b>
                `;
                container.appendChild(pill);
            });
        }

        function initTopChatsChart(data) {
            const canvas = document.getElementById('chartTopChats');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');

            const topChats = (data.topChats || []).slice(0, 6);
            const labels = topChats.map(c => shortenLabel(c.name || c.chatId, 14));
            const fullLabels = topChats.map(c => c.name || c.chatId);
            const values = topChats.map(c => c.count);

            chartTopChats = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Messages',
                        data: values,
                        backgroundColor: '#22c55e',
                        borderRadius: 4,
                        barThickness: 16
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleColor: '#f8fafc',
                            bodyColor: '#cbd5e1',
                            borderColor: '#334155',
                            borderWidth: 1,
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                title: function(context) {
                                    const index = context[0].dataIndex;
                                    return fullLabels[index] || labels[index];
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9', drawBorder: false },
                            ticks: {
                                precision: 0,
                                font: { family: "'JetBrains Mono', monospace", size: 10 },
                                color: '#64748b'
                            }
                        },
                        y: {
                            grid: { display: false, drawBorder: false },
                            ticks: {
                                font: { family: "'Plus Jakarta Sans', sans-serif", size: 11, weight: '600' },
                                color: '#334155'
                            }
                        }
                    }
                }
            });
        }

        function updateCharts(data) {
            // Update Totals & Labels
            const sentTotalEl = document.getElementById('chartSentTotal');
            const recvTotalEl = document.getElementById('chartRecvTotal');
            const deliveredTotalEl = document.getElementById('chartDeliveredTotal');
            const failedTotalEl = document.getElementById('chartFailedTotal');
            const typeTotalEl = document.getElementById('chartTypeTotal');
            const topChatsBadge = document.getElementById('topChatsCountBadge');
            const periodLabel = document.getElementById('timeSeriesPeriodLabel');

            if (sentTotalEl) sentTotalEl.textContent = data.totals ? data.totals.sent : 0;
            if (recvTotalEl) recvTotalEl.textContent = data.totals ? data.totals.received : 0;
            if (deliveredTotalEl) deliveredTotalEl.textContent = data.totals ? data.totals.delivered : 0;
            if (failedTotalEl) failedTotalEl.textContent = data.totals ? data.totals.failed : 0;
            if (typeTotalEl) typeTotalEl.textContent = (data.totals ? data.totals.total : 0) + ' msgs';
            if (topChatsBadge) topChatsBadge.textContent = (data.topChats ? data.topChats.length : 0) + ' chats';

            if (periodLabel) {
                if (currentPeriod === '24h') periodLabel.textContent = '24h Hourly Velocity';
                else if (currentPeriod === '7d') periodLabel.textContent = '7 Days Volume (Daily)';
                else periodLabel.textContent = '30 Days Volume (Daily)';
            }

            // 1. Time Series
            if (chartTimeSeries) {
                const labels = (data.timeSeries || []).map(p => p.label);
                const sentValues = (data.timeSeries || []).map(p => p.sent);
                const recvValues = (data.timeSeries || []).map(p => p.received);
                const deliveredValues = (data.timeSeries || []).map(p => p.delivered);
                const failedValues = (data.timeSeries || []).map(p => p.failed);

                chartTimeSeries.data.labels = labels;
                chartTimeSeries.data.datasets[0].data = sentValues;
                chartTimeSeries.data.datasets[1].data = recvValues;
                chartTimeSeries.data.datasets[2].data = deliveredValues;
                chartTimeSeries.data.datasets[3].data = failedValues;
                chartTimeSeries.update();

                const emptyState = document.getElementById('timeSeriesEmpty');
                const hasTraffic = sentValues.some(v => v > 0) || recvValues.some(v => v > 0);
                if (emptyState) {
                    emptyState.classList.toggle('hidden', hasTraffic);
                }
            }

            // 2. By Type
            if (chartByType) {
                const byTypeObj = data.byType || {};
                const keys = Object.keys(byTypeObj).filter(k => byTypeObj[k] > 0);
                chartByType.data.labels = keys.map(k => k.charAt(0).toUpperCase() + k.slice(1));
                chartByType.data.datasets[0].data = keys.map(k => byTypeObj[k]);
                chartByType.data.datasets[0].backgroundColor = keys.map(k => TYPE_COLORS[k] || TYPE_COLORS.other);
                chartByType.update();

                renderTypeBadges(byTypeObj);

                const byTypeEmpty = document.getElementById('byTypeEmpty');
                if (byTypeEmpty) {
                    byTypeEmpty.classList.toggle('hidden', keys.length > 0);
                }
            }

            // 3. Top Chats
            if (chartTopChats) {
                const topChats = (data.topChats || []).slice(0, 6);
                chartTopChats.data.labels = topChats.map(c => shortenLabel(c.name || c.chatId, 14));
                chartTopChats.data.datasets[0].data = topChats.map(c => c.count);
                chartTopChats.update();

                const topChatsEmpty = document.getElementById('topChatsEmpty');
                if (topChatsEmpty) {
                    topChatsEmpty.classList.toggle('hidden', topChats.length > 0);
                }
            }
        }

        // Initialize on DOM ready
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Chart === 'undefined') {
                console.warn('Chart.js not loaded');
                return;
            }

            initTimeSeriesChart(initialStats);
            initByTypeChart(initialStats);
            initTopChatsChart(initialStats);

            // Bind Period Buttons
            const buttons = document.querySelectorAll('.chart-period-btn');
            buttons.forEach(btn => {
                btn.addEventListener('click', async function () {
                    const period = this.getAttribute('data-period');
                    if (period === currentPeriod) return;
                    currentPeriod = period;

                    // Update active button classes
                    buttons.forEach(b => {
                        b.classList.remove('bg-white', 'text-slate-900', 'shadow-xs', 'border-slate-200/60', 'font-bold');
                        b.classList.add('text-slate-600', 'hover:text-slate-900', 'border-transparent', 'font-semibold');
                    });
                    this.classList.remove('text-slate-600', 'hover:text-slate-900', 'border-transparent', 'font-semibold');
                    this.classList.add('bg-white', 'text-slate-900', 'shadow-xs', 'border-slate-200/60', 'font-bold');

                    try {
                        const response = await fetch(`${statsApiUrl}?period=${period}`, {
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const json = await response.json();
                        if (!response.ok || !json.ok) {
                            throw new Error(json.error || `Analytics request failed (${response.status})`);
                        }
                        updateCharts(json);
                        setAnalyticsStatus('Live data', false);
                    } catch (e) {
                        console.error('Failed to fetch period stats', e);
                        setAnalyticsStatus('Could not refresh data', true);
                    }
                });
            });

            const retryButton = document.getElementById('analyticsRetry');
            if (retryButton) {
                retryButton.addEventListener('click', () => {
                    const activePeriod = document.querySelector('.chart-period-btn.bg-white');
                    if (activePeriod) {
                        currentPeriod = '';
                        activePeriod.click();
                    }
                });
            }

            // Quickstart Interactive Language Switcher
            const codeBox = document.getElementById('quickstartCodeBox');
            const copyBtn = document.getElementById('copyQuickstartBtn');
            const copyLabel = document.getElementById('copyQuickstartLabel');
            const sendEndpoint = '<?= e(base_url('api/send.php')) ?>';

            const snippets = {
                curl: {
                    raw: `curl -X POST "${sendEndpoint}" \\\n  -H "Content-Type: application/json" \\\n  -H "X-API-Key: wa_live_YOUR_API_KEY_HERE" \\\n  -d '{\n    "to": "919876543210",\n    "message": "Hello Test Message.",\n    "humanize": true\n  }'`,
                    html: `<code><span class="text-emerald-400">curl</span> -X POST "${sendEndpoint}" \\\n  -H <span class="text-amber-300">"Content-Type: application/json"</span> \\\n  -H <span class="text-amber-300">"X-API-Key: wa_live_YOUR_API_KEY_HERE"</span> \\\n  -d <span class="text-sky-300">'{\n    "to": "919876543210",\n    "message": "Hello Test Message.",\n    "humanize": true\n  }'</span></code>`
                },
                node: {
                    raw: `const res = await fetch('${sendEndpoint}', {\n  method: 'POST',\n  headers: { 'X-API-Key': 'wa_live_YOUR_API_KEY', 'Content-Type': 'application/json' },\n  body: JSON.stringify({ to: '+919876543210', message: 'Hello from WhatsGateway!' })\n});\nconst data = await res.json();\nconsole.log(data.message_id);`,
                    html: `<code><span class="text-purple-400">const</span> res = <span class="text-purple-400">await</span> fetch(<span class="text-emerald-300">'${sendEndpoint}'</span>, {\n  method: <span class="text-emerald-300">'POST'</span>,\n  headers: { <span class="text-emerald-300">'X-API-Key'</span>: <span class="text-amber-300">'wa_live_YOUR_API_KEY'</span>, <span class="text-emerald-300">'Content-Type'</span>: <span class="text-emerald-300">'application/json'</span> },\n  body: JSON.stringify({ to: <span class="text-emerald-300">'+919876543210'</span>, message: <span class="text-emerald-300">'Hello from WhatsGateway!'</span> })\n});\n<span class="text-purple-400">const</span> data = <span class="text-purple-400">await</span> res.json();\nconsole.log(data.message_id);</code>`
                },
                python: {
                    raw: `import requests\n\nres = requests.post('${sendEndpoint}', json={'to': '+919876543210', 'message': 'Hello from WhatsGateway!'}, headers={'X-API-Key': 'wa_live_YOUR_API_KEY'})\nprint(res.json())`,
                    html: `<code><span class="text-purple-400">import</span> requests\n\nres = requests.post(<span class="text-emerald-300">'${sendEndpoint}'</span>, json={<span class="text-sky-300">'to'</span>: <span class="text-emerald-300">'+919876543210'</span>, <span class="text-sky-300">'message'</span>: <span class="text-emerald-300">'Hello from WhatsGateway!'</span>}, headers={<span class="text-sky-300">'X-API-Key'</span>: <span class="text-amber-300">'wa_live_YOUR_API_KEY'</span>})\nprint(res.json())</code>`
                },
                php: {
                    raw: `$payload = json_encode(['to' => '+919876543210', 'message' => 'Hello from WhatsGateway!']);\n$ch = curl_init('${sendEndpoint}');\ncurl_setopt_array($ch, [\n    CURLOPT_POST => true,\n    CURLOPT_HTTPHEADER => ['X-API-Key: wa_live_YOUR_API_KEY', 'Content-Type: application/json'],\n    CURLOPT_POSTFIELDS => $payload,\n    CURLOPT_RETURNTRANSFER => true\n]);\n$res = json_decode(curl_exec($ch), true);\ncurl_close($ch);`,
                    html: `<code>$payload = json_encode([<span class="text-sky-300">'to'</span> => <span class="text-emerald-300">'+919876543210'</span>, <span class="text-sky-300">'message'</span> => <span class="text-emerald-300">'Hello from WhatsGateway!'</span>]);\n$ch = curl_init(<span class="text-emerald-300">'${sendEndpoint}'</span>);\ncurl_setopt_array($ch, [\n    CURLOPT_POST => <span class="text-purple-400">true</span>,\n    CURLOPT_HTTPHEADER => [<span class="text-emerald-300">'X-API-Key: wa_live_YOUR_API_KEY'</span>, <span class="text-emerald-300">'Content-Type: application/json'</span>],\n    CURLOPT_POSTFIELDS => $payload,\n    CURLOPT_RETURNTRANSFER => <span class="text-purple-400">true</span>\n]);\n$res = json_decode(curl_exec($ch), <span class="text-purple-400">true</span>);\ncurl_close($ch);</code>`
                }
            };

            let currentLang = 'curl';

            const langTabs = document.querySelectorAll('.dash-code-tab');
            langTabs.forEach(tab => {
                tab.addEventListener('click', function () {
                    const lang = this.getAttribute('data-lang');
                    if (lang === currentLang || !snippets[lang]) return;
                    currentLang = lang;

                    langTabs.forEach(t => {
                        t.classList.remove('active', 'bg-white', 'text-slate-900', 'shadow-2xs', 'border', 'border-slate-200/60', 'font-bold');
                        t.classList.add('text-slate-600', 'hover:text-slate-900', 'font-medium');
                    });
                    this.classList.add('active', 'bg-white', 'text-slate-900', 'shadow-2xs', 'border', 'border-slate-200/60', 'font-bold');
                    this.classList.remove('text-slate-600', 'hover:text-slate-900', 'font-medium');

                    if (codeBox) {
                        codeBox.innerHTML = snippets[lang].html;
                    }
                });
            });

            if (copyBtn) {
                copyBtn.addEventListener('click', () => {
                    const snippet = snippets[currentLang] || snippets.curl;
                    navigator.clipboard.writeText(snippet.raw).then(() => {
                        if (copyLabel) copyLabel.textContent = 'Copied!';
                        if (typeof showToast === 'function') {
                            showToast(`${currentLang.toUpperCase()} snippet copied to clipboard!`, 'success');
                        }
                        setTimeout(() => {
                            if (copyLabel) copyLabel.textContent = 'Copy Code';
                        }, 2000);
                    });
                });
            }
        });

        function setAnalyticsStatus(message, isError) {
            const status = document.getElementById('analyticsStatus');
            const retry = document.getElementById('analyticsRetry');
            if (status) {
                status.textContent = message;
                status.className = isError
                    ? 'basis-full sm:basis-auto text-[10px] text-rose-600 font-medium'
                    : 'basis-full sm:basis-auto text-[10px] text-emerald-600 font-medium';
            }
            if (retry) retry.classList.toggle('hidden', !isError);
        }
    })();
    </script>