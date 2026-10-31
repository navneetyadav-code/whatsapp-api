<?php
declare(strict_types=1);

/**
 * AI Memory & Smart Training Dashboard Page
 * Designed following ui-ux-pro-max guidelines:
 * - Minimal, high-density, professional SaaS aesthetic
 * - Strict SVG icons (zero emojis as icons)
 * - Fira/JetBrains Mono for keys & tokens, Plus Jakarta Sans for UI
 * - High-contrast accessible palettes (WCAG AAA/AA)
 * - cursor-pointer on all clickables, smooth 150-200ms transitions
 */
?>

<div class="space-y-5 mb-8 max-w-7xl mx-auto">

    <!-- ====================================================================
         1. TOP BAR: TITLE, STATUS & PRIMARY ACTIONS
    ===================================================================== -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200/90">
        <div class="flex items-center gap-3">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
                    <span>AI Memory & Smart Training</span>
                    <?php if ($deviceConnected): ?>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Live Sync</span>
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                            <span>Offline</span>
                        </span>
                    <?php endif; ?>
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Token-conscious long-term fact memory and few-shot in-context training for your WhatsApp AI agent.</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="<?= e(base_url('index.php?page=chatbot')) ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-lg shadow-2xs transition-colors cursor-pointer">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3" stroke-width="2"/></svg>
                <span>Studio Prompt</span>
            </a>

            <button type="button" onclick="openMemoryModal()" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-lg shadow-2xs transition-all active:scale-[0.98] cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>New Fact</span>
            </button>

            <button type="button" onclick="openTrainingModal()" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg shadow-2xs transition-all active:scale-[0.98] cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>New Training Pair</span>
            </button>
        </div>
    </div>

    <!-- ====================================================================
         2. KPI METRICS CARDS: TOKEN GOVERNOR & MEMORY HEALTH
    ===================================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
        
        <!-- Card 1: Total Facts -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Fact Notebook</span>
                <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-fuchsia-50 text-fuchsia-700 border border-fuchsia-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span id="kpiTotalMemories" class="text-2xl font-black text-slate-900 tracking-tight tabular-nums"><?= $totalMemories ?></span>
                <span class="text-xs text-slate-400 font-medium">facts</span>
            </div>
            <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span><?= $bossMemoriesCount ?> Boss</span>
                <span>•</span>
                <span><?= $customerMemoriesCount ?> Customer</span>
                <span>•</span>
                <span><?= $temporaryMemoriesCount ?> Temporary</span>
            </div>
        </div>

        <!-- Card 2: Training Pairs -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Few-Shot Pairs</span>
                <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span id="kpiTotalTraining" class="text-2xl font-black text-slate-900 tracking-tight tabular-nums"><?= $totalTrainingCount ?></span>
                <span class="text-xs text-slate-400 font-medium">golden pairs</span>
            </div>
            <div class="mt-2 pt-2 border-t border-slate-100 text-[11px] text-slate-500 truncate">
                <span>In-context behavior tuning & guardrails</span>
            </div>
        </div>

        <!-- Card 3: Token Governor -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Token Governor</span>
                <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-black text-emerald-700 tracking-tight font-mono">120t</span>
                <span class="text-xs text-emerald-600 font-medium">max/prompt</span>
            </div>
            <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span class="text-emerald-700 font-semibold">Top-3 match</span>
                <span>•</span>
                <span>No full dumps</span>
            </div>
        </div>

        <!-- Card 4: Operating Mode & Latency -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Operating Mode</span>
                <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-sky-50 text-sky-700 border border-sky-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-xl font-bold text-slate-900 tracking-tight"><?= ucfirst($agentMode) ?></span>
                <span class="text-[11px] font-mono text-slate-400"><?= $agentMode === 'personal' ? '24/7 Boss' : 'Multi-Client' ?></span>
            </div>
            <div class="mt-2 pt-2 border-t border-slate-100 text-[11px] text-slate-500 truncate">
                <span>Target Response: <strong class="text-slate-800">1.2s TTFT</strong></span>
            </div>
        </div>

    </div>

    <!-- ====================================================================
         3. DUAL-TAB NAVIGATION & CONTROLS
    ===================================================================== -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs space-y-4">
        
        <!-- Tab Buttons & Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-slate-100">
            <div class="flex items-center gap-1.5 p-1 bg-slate-100/90 rounded-lg border border-slate-200/80">
                <button type="button" id="tabBtnMemory" onclick="switchPageTab('memory')" class="flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-bold transition-all bg-white text-slate-900 shadow-2xs border border-slate-200/80 cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-fuchsia-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    <span>Memory Notebook</span>
                    <span id="badgeMemTab" class="text-[10px] font-mono px-1.5 py-0.2 rounded-full bg-fuchsia-50 text-fuchsia-700 border border-fuchsia-200"><?= $totalMemories ?></span>
                </button>

                <button type="button" id="tabBtnTraining" onclick="switchPageTab('training')" class="flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-semibold text-slate-600 hover:text-slate-900 transition-all cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Smart Training (Few-Shot)</span>
                    <span id="badgeTrainTab" class="text-[10px] font-mono px-1.5 py-0.2 rounded-full bg-slate-200 text-slate-700"><?= $totalTrainingCount ?></span>
                </button>

                <button type="button" id="tabBtnBudget" onclick="switchPageTab('budget')" class="flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-semibold text-slate-600 hover:text-slate-900 transition-all cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span>Token Governor Blueprint</span>
                </button>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-[11px] text-slate-400 font-medium">Automatic In-Chat Control:</span>
                <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">"Remember that..."</span>
                <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-rose-50 text-rose-700 border border-rose-200">"Forget my..."</span>
            </div>
        </div>

        <!-- ====================================================================
             TAB 1 PANEL: MEMORY NOTEBOOK (FACTS & ATTRIBUTES)
        ===================================================================== -->
        <div id="panelMemory" class="space-y-4">
            <!-- Filter Bar & Search Input -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-1.5 text-xs">
                    <button type="button" onclick="filterMemories('all')" class="mem-filter-btn px-2.5 py-1 rounded-lg font-bold bg-slate-900 text-white cursor-pointer shadow-2xs" data-filter="all">All (<?= $totalMemories ?>)</button>
                    <button type="button" onclick="filterMemories('owner')" class="mem-filter-btn px-2.5 py-1 rounded-lg font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer" data-filter="owner">Personal Boss (<?= $bossMemoriesCount ?>)</button>
                    <button type="button" onclick="filterMemories('customers')" class="mem-filter-btn px-2.5 py-1 rounded-lg font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer" data-filter="customers">Customers (<?= $customerMemoriesCount ?>)</button>
                    <button type="button" onclick="filterMemories('expiring')" class="mem-filter-btn px-2.5 py-1 rounded-lg font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer" data-filter="expiring">Temporary TTL (<?= $temporaryMemoriesCount ?>)</button>
                </div>

                <div class="relative w-full sm:w-64">
                    <input type="text" id="memorySearchInput" oninput="searchMemories(this.value)" placeholder="Search mnemonic keys or facts..." class="w-full text-xs pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors">
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <!-- Cards Grid -->
            <div id="memoryListContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
                <?php if (empty($userMemories)): ?>
                    <div id="memoryEmptyState" class="col-span-full py-12 text-center text-slate-400 text-xs bg-slate-50/50 rounded-xl border border-dashed border-slate-200">
                        <svg class="w-10 h-10 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        <p class="font-bold text-slate-700 text-sm">No persistent facts saved yet</p>
                        <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">Facts told to the bot in WhatsApp chat (e.g. "Remember that my GSTIN is...") or added via "New Fact" will appear here.</p>
                        <button type="button" onclick="openMemoryModal()" class="mt-3.5 inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-lg shadow-2xs transition-all active:scale-[0.98] cursor-pointer">
                            + Add Your First Fact
                        </button>
                    </div>
                <?php else: ?>
                    <?php foreach ($userMemories as $m): ?>
                        <?php
                            $isCustomer = !empty($m['contact_phone']);
                            $daysLeft = !empty($m['expires_at']) ? max(0, (int) ceil((strtotime($m['expires_at']) - time()) / 86400)) : null;
                        ?>
                        <div class="memory-card bg-white rounded-xl border border-slate-200 hover:border-slate-300 p-3.5 shadow-2xs hover:shadow-xs transition-all flex flex-col justify-between gap-3 group relative"
                             data-id="<?= (int) $m['id'] ?>"
                             data-key="<?= e($m['memory_key']) ?>"
                             data-val="<?= e($m['memory_value']) ?>"
                             data-phone="<?= e($m['contact_phone'] ?? '') ?>"
                             data-type="<?= e($m['memory_type'] ?? 'fact') ?>"
                             data-durable="<?= !empty($m['is_durable']) ? '1' : '0' ?>"
                             data-scope="<?= $isCustomer ? 'customers' : 'owner' ?>"
                             data-expiring="<?= !empty($m['expires_at']) ? '1' : '0' ?>">
                            
                            <div class="space-y-2">
                                <!-- Top Row: Mnemonic Key & Badges -->
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-mono text-[11px] font-bold text-slate-900 bg-slate-100/90 px-2.5 py-1 rounded-md border border-slate-200/80 truncate">
                                        <?= e($m['memory_key']) ?>
                                    </span>
                                    
                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <?php if ($isCustomer): ?>
                                            <span class="text-[9px] font-mono font-bold px-1.5 py-0.5 rounded bg-sky-50 text-sky-700 border border-sky-200" title="Customer Phone Number">
                                                <?= e($m['contact_phone']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200">
                                                Boss
                                            </span>
                                        <?php endif; ?>

                                        <?php if (!empty($m['is_durable'])): ?>
                                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200" title="Permanent Memory">
                                                Durable
                                            </span>
                                        <?php elseif ($daysLeft !== null): ?>
                                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200" title="Auto-expires in <?= $daysLeft ?> days">
                                                <?= $daysLeft ?>d left
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Fact Value -->
                                <div class="text-xs text-slate-700 font-medium leading-relaxed bg-slate-50/70 p-2.5 rounded-lg border border-slate-100 break-words line-clamp-3">
                                    <?= e($m['memory_value']) ?>
                                </div>
                            </div>

                            <!-- Card Bottom: Usage Stats & Actions -->
                            <div class="flex items-center justify-between pt-2.5 border-t border-slate-100 text-[11px] text-slate-400">
                                <span class="font-mono text-[10px]">
                                    Invocations: <strong class="text-slate-700"><?= (int) ($m['access_count'] ?? 0) ?>x</strong>
                                </span>

                                <div class="flex items-center gap-1.5">
                                    <button type="button" onclick="editMemoryRow(this)" class="px-2.5 py-0.5 text-[11px] font-bold text-slate-700 hover:text-slate-900 bg-white hover:bg-slate-100 border border-slate-200 rounded-md cursor-pointer transition-colors shadow-2xs">
                                        Edit
                                    </button>
                                    <button type="button" onclick="deleteMemoryRow(<?= (int) $m['id'] ?>, '<?= e($m['memory_key']) ?>')" class="px-2.5 py-0.5 text-[11px] font-bold text-rose-600 hover:text-rose-700 bg-white hover:bg-rose-50 border border-rose-200 rounded-md cursor-pointer transition-colors shadow-2xs">
                                        Delete
                                    </button>
                                </div>
                            </div>

                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- ====================================================================
             TAB 2 PANEL: SMART TRAINING (FEW-SHOT GOLDEN PAIRS)
        ===================================================================== -->
        <div id="panelTraining" class="hidden space-y-4">
            <div class="p-3.5 bg-indigo-50/80 border border-indigo-200/80 rounded-xl flex items-start gap-3 text-xs text-indigo-950">
                <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-indigo-600 text-white shrink-0 font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </span>
                <div>
                    <h4 class="font-bold text-indigo-950 text-sm">Dynamic In-Context Few-Shot Training</h4>
                    <p class="text-xs text-indigo-800 mt-0.5">Train your AI on ideal replies and strict negative boundaries (what NOT to do) without fine-tuning weights. Only top relevant pairs matching the user's intent are injected (< 180 tokens total).</p>
                </div>
            </div>

            <div id="trainingListContainer" class="space-y-3">
                <?php if (empty($trainingExamples)): ?>
                    <div id="trainingEmptyState" class="py-12 text-center text-slate-400 text-xs bg-slate-50/50 rounded-xl border border-dashed border-slate-200">
                        <svg class="w-10 h-10 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <p class="font-bold text-slate-700 text-sm">No training pairs added yet</p>
                        <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">Seed difficult Q&As (e.g. discount requests, strict return policies) to train your AI on exact behavior.</p>
                        <button type="button" onclick="openTrainingModal()" class="mt-3.5 inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg shadow-2xs transition-all active:scale-[0.98] cursor-pointer">
                            + Add First Training Pair
                        </button>
                    </div>
                <?php else: ?>
                    <?php foreach ($trainingExamples as $t): ?>
                        <div class="training-card bg-white rounded-xl border border-slate-200 hover:border-slate-300 p-4 shadow-2xs hover:shadow-xs transition-all space-y-3 group"
                             data-id="<?= (int) $t['id'] ?>"
                             data-query="<?= e($t['user_query']) ?>"
                             data-resp="<?= e($t['ideal_response']) ?>"
                             data-const="<?= e($t['negative_constraint'] ?? '') ?>"
                             data-cat="<?= e($t['category'] ?? 'general') ?>">
                            
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    <?= e($t['category'] ?? 'general') ?>
                                </span>
                                
                                <div class="flex items-center gap-1.5">
                                    <button type="button" onclick="editTrainingRow(this)" class="px-2.5 py-0.5 text-[11px] font-bold text-slate-700 hover:text-slate-900 bg-white hover:bg-slate-100 border border-slate-200 rounded-md cursor-pointer transition-colors shadow-2xs">
                                        Edit
                                    </button>
                                    <button type="button" onclick="deleteTrainingRow(<?= (int) $t['id'] ?>)" class="px-2.5 py-0.5 text-[11px] font-bold text-rose-600 hover:text-rose-700 bg-white hover:bg-rose-50 border border-rose-200 rounded-md cursor-pointer transition-colors shadow-2xs">
                                        Delete
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                                <div class="bg-slate-50 p-3 rounded-lg border border-slate-200/80">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">When User Asks:</span>
                                    <p class="font-semibold text-slate-900 font-mono text-[12px]"><?= e($t['user_query']) ?></p>
                                </div>

                                <div class="bg-emerald-50/70 p-3 rounded-lg border border-emerald-200/80">
                                    <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider block mb-1">Ideal Model Output:</span>
                                    <p class="text-emerald-950 font-medium leading-relaxed"><?= e($t['ideal_response']) ?></p>
                                </div>
                            </div>

                            <?php if (!empty($t['negative_constraint'])): ?>
                                <div class="bg-rose-50/80 p-2.5 rounded-lg border border-rose-200/80 flex items-center gap-2 text-xs text-rose-900 font-medium">
                                    <span class="text-[10px] font-black uppercase tracking-wider px-1.5 py-0.2 rounded bg-rose-600 text-white shrink-0">Negative Constraint</span>
                                    <span><?= e($t['negative_constraint']) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- ====================================================================
             TAB 3 PANEL: TOKEN BUDGET GOVERNOR BLUEPRINT
        ===================================================================== -->
        <div id="panelBudget" class="hidden space-y-4 text-xs">
            <div class="p-4 bg-slate-900 text-white rounded-xl space-y-3">
                <div class="flex items-center justify-between border-b border-slate-800 pb-2.5">
                    <h4 class="font-bold text-sm tracking-tight text-emerald-400 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>Prompt Envelope Budget Allocation (Capped at 1,500 Tokens)</span>
                    </h4>
                    <span class="text-[11px] font-mono text-emerald-400 bg-emerald-950/80 px-2.5 py-0.5 rounded border border-emerald-800">
                        Zero Truncation Guarantee
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-2.5 text-[11px]">
                    <div class="bg-slate-800/80 p-2.5 rounded-lg border border-slate-700">
                        <span class="text-slate-400 block mb-0.5 font-bold uppercase text-[9px]">1. Persona / Kernel</span>
                        <span class="font-mono text-base font-bold text-white">~350t</span>
                        <span class="text-[10px] text-slate-400 block mt-1">Directives & safety rules</span>
                    </div>

                    <div class="bg-slate-800/80 p-2.5 rounded-lg border border-slate-700">
                        <span class="text-slate-400 block mb-0.5 font-bold uppercase text-[9px]">2. Smart Training</span>
                        <span class="font-mono text-base font-bold text-indigo-400">~200t</span>
                        <span class="text-[10px] text-slate-400 block mt-1">Top-2 golden Q&A pairs</span>
                    </div>

                    <div class="bg-slate-800/80 p-2.5 rounded-lg border border-slate-700">
                        <span class="text-slate-400 block mb-0.5 font-bold uppercase text-[9px]">3. Fact Notebook</span>
                        <span class="font-mono text-base font-bold text-fuchsia-400">~120t</span>
                        <span class="text-[10px] text-slate-400 block mt-1">Top-3 relevant facts only</span>
                    </div>

                    <div class="bg-slate-800/80 p-2.5 rounded-lg border border-slate-700">
                        <span class="text-slate-400 block mb-0.5 font-bold uppercase text-[9px]">4. Active Sliding Window</span>
                        <span class="font-mono text-base font-bold text-amber-400">~350t</span>
                        <span class="text-[10px] text-slate-400 block mt-1">Max 1,600 chars history</span>
                    </div>

                    <div class="bg-slate-800/80 p-2.5 rounded-lg border border-slate-700">
                        <span class="text-slate-400 block mb-0.5 font-bold uppercase text-[9px]">5. Tools / Functions</span>
                        <span class="font-mono text-base font-bold text-emerald-400">~320t</span>
                        <span class="text-[10px] text-slate-400 block mt-1">App store declarations</span>
                    </div>
                </div>
            </div>

            <!-- Best Practice Guidelines -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 pt-2">
                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 space-y-2">
                    <h5 class="font-bold text-slate-900 flex items-center gap-1.5">
                        <span class="text-emerald-600 font-bold">✓</span>
                        <span>Why This Architecture Beats Fine-Tuning</span>
                    </h5>
                    <p class="text-slate-600 leading-relaxed text-[11px]">
                        Fine-tuning models on specific customer data is static, expensive, and risks private data leakage. Dynamic few-shot training with Top-K semantic matching delivers instant updates, 100% data freshness, zero deployment downtime, and cost efficiency.
                    </p>
                </div>

                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 space-y-2">
                    <h5 class="font-bold text-slate-900 flex items-center gap-1.5">
                        <span class="text-emerald-600 font-bold">✓</span>
                        <span>The 45-Minute Rolling Decay Safeguard</span>
                    </h5>
                    <p class="text-slate-600 leading-relaxed text-[11px]">
                        Conversations idle for > 45 minutes decay out of the active turn window. Before clearing, any essential preferences or notes stated during the chat are committed to the durable fact notebook. Subsequent chats start crisp and token-clean.
                    </p>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- ====================================================================
     MODAL 1: ADD / EDIT FACT MEMORY
===================================================================== -->
<div id="memoryModal" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-md p-5 space-y-4 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
            <h3 id="memoryModalTitle" class="text-sm font-bold text-slate-900">Add Memory Fact</h3>
            <button type="button" onclick="closeMemoryModal()" class="text-slate-400 hover:text-slate-800 text-sm font-bold cursor-pointer">✕</button>
        </div>

        <form id="memoryForm" onsubmit="submitMemoryForm(event)" class="space-y-3">
            <input type="hidden" id="memInputId" name="id" value="">

            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 mb-1">Mnemonic Key</label>
                <input type="text" id="memInputKey" name="key" required placeholder="e.g. favorite_airline, gstin, delivery_address" class="w-full text-xs font-mono px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                <span class="text-[10px] text-slate-400 mt-0.5 block">Alphanumeric & underscore only (e.g. "wife_birthday").</span>
            </div>

            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 mb-1">Fact Value / Details</label>
                <textarea id="memInputVal" name="value" required rows="3" placeholder="e.g. Always book window seats on Emirates flights." class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 resize-none"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 mb-1">Scope</label>
                    <select id="memInputScope" onchange="toggleCustomerPhoneField(this.value)" class="w-full text-xs px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 cursor-pointer">
                        <option value="owner">Personal (Boss)</option>
                        <option value="customer">Specific Customer</option>
                    </select>
                </div>

                <div id="memPhoneWrap" class="hidden">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 mb-1">Customer Phone</label>
                    <input type="text" id="memInputPhone" name="contact_phone" placeholder="919876543210" class="w-full text-xs px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg">
                </div>
            </div>

            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                <label class="flex items-center justify-between cursor-pointer">
                    <span class="text-xs font-semibold text-slate-800">Permanent (Durable)</span>
                    <input type="checkbox" id="memInputDurable" name="is_durable" value="1" checked onchange="document.getElementById('memTtlWrap').classList.toggle('hidden', this.checked)" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                </label>
                <div id="memTtlWrap" class="hidden pt-1 border-t border-slate-200 flex items-center justify-between text-xs">
                    <span class="text-slate-500">Auto-expire in days:</span>
                    <input type="number" id="memInputTtl" name="ttl_days" value="14" min="1" max="90" class="w-16 px-2 py-1 text-xs border border-slate-200 rounded bg-white text-center font-bold">
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeMemoryModal()" class="px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 rounded-lg cursor-pointer">Cancel</button>
                <button type="submit" class="px-4 py-1.5 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-lg shadow-2xs cursor-pointer">Save Fact</button>
            </div>
        </form>
    </div>
</div>

<!-- ====================================================================
     MODAL 2: ADD / EDIT TRAINING PAIR
===================================================================== -->
<div id="trainingModal" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-md p-5 space-y-4 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
            <h3 id="trainingModalTitle" class="text-sm font-bold text-slate-900">Add Few-Shot Training Pair</h3>
            <button type="button" onclick="closeTrainingModal()" class="text-slate-400 hover:text-slate-800 text-sm font-bold cursor-pointer">✕</button>
        </div>

        <form id="trainingForm" onsubmit="submitTrainingForm(event)" class="space-y-3">
            <input type="hidden" id="trainInputId" name="id" value="">

            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 mb-1">When Customer Asks:</label>
                <input type="text" id="trainInputQuery" name="user_query" required placeholder="e.g. Can you give me a 30% discount?" class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
            </div>

            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 mb-1">Ideal Model Answer:</label>
                <textarea id="trainInputResp" name="ideal_response" required rows="3" placeholder="e.g. We cannot offer 30%, but I can immediately apply our 10% annual billing plan." class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 resize-none"></textarea>
            </div>

            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 mb-1">Negative Constraint (What NOT to do):</label>
                <input type="text" id="trainInputConst" name="negative_constraint" placeholder="e.g. NEVER offer discounts above 10%" class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeTrainingModal()" class="px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 rounded-lg cursor-pointer">Cancel</button>
                <button type="submit" class="px-4 py-1.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg shadow-2xs cursor-pointer">Save Training Pair</button>
            </div>
        </form>
    </div>
</div>

<script>
    function switchPageTab(tab) {
        document.getElementById('panelMemory').classList.toggle('hidden', tab !== 'memory');
        document.getElementById('panelTraining').classList.toggle('hidden', tab !== 'training');
        document.getElementById('panelBudget').classList.toggle('hidden', tab !== 'budget');

        const btnMem = document.getElementById('tabBtnMemory');
        const btnTrain = document.getElementById('tabBtnTraining');
        const btnBudget = document.getElementById('tabBtnBudget');

        btnMem.className = (tab === 'memory')
            ? 'flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-bold transition-all bg-white text-slate-900 shadow-2xs border border-slate-200/80 cursor-pointer'
            : 'flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-semibold text-slate-600 hover:text-slate-900 transition-all cursor-pointer';

        btnTrain.className = (tab === 'training')
            ? 'flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-bold transition-all bg-white text-slate-900 shadow-2xs border border-slate-200/80 cursor-pointer'
            : 'flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-semibold text-slate-600 hover:text-slate-900 transition-all cursor-pointer';

        btnBudget.className = (tab === 'budget')
            ? 'flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-bold transition-all bg-white text-slate-900 shadow-2xs border border-slate-200/80 cursor-pointer'
            : 'flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-semibold text-slate-600 hover:text-slate-900 transition-all cursor-pointer';
    }

    function filterMemories(filterType) {
        document.querySelectorAll('.mem-filter-btn').forEach(btn => {
            if (btn.dataset.filter === filterType) {
                btn.className = 'mem-filter-btn px-2.5 py-1 rounded-lg font-bold bg-slate-900 text-white cursor-pointer shadow-2xs';
            } else {
                btn.className = 'mem-filter-btn px-2.5 py-1 rounded-lg font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer';
            }
        });

        document.querySelectorAll('.memory-card').forEach(card => {
            if (filterType === 'all') {
                card.classList.remove('hidden');
            } else if (filterType === 'owner') {
                card.classList.toggle('hidden', card.dataset.scope !== 'owner');
            } else if (filterType === 'customers') {
                card.classList.toggle('hidden', card.dataset.scope !== 'customers');
            } else if (filterType === 'expiring') {
                card.classList.toggle('hidden', card.dataset.expiring !== '1');
            }
        });
    }

    function searchMemories(query) {
        const q = (query || '').toLowerCase().trim();
        document.querySelectorAll('.memory-card').forEach(card => {
            const key = (card.dataset.key || '').toLowerCase();
            const val = (card.dataset.val || '').toLowerCase();
            const phone = (card.dataset.phone || '').toLowerCase();
            const match = (!q || key.includes(q) || val.includes(q) || phone.includes(q));
            card.classList.toggle('hidden', !match);
        });
    }

    function openMemoryModal() {
        document.getElementById('memoryModalTitle').textContent = 'Add Memory Fact';
        document.getElementById('memInputId').value = '';
        document.getElementById('memInputKey').value = '';
        document.getElementById('memInputVal').value = '';
        document.getElementById('memInputPhone').value = '';
        document.getElementById('memInputScope').value = 'owner';
        document.getElementById('memPhoneWrap').classList.add('hidden');
        document.getElementById('memInputDurable').checked = true;
        document.getElementById('memTtlWrap').classList.add('hidden');
        document.getElementById('memoryModal').classList.remove('hidden');
    }

    function closeMemoryModal() {
        document.getElementById('memoryModal').classList.add('hidden');
    }

    function toggleCustomerPhoneField(scope) {
        document.getElementById('memPhoneWrap').classList.toggle('hidden', scope !== 'customer');
    }

    function editMemoryRow(btn) {
        const card = btn.closest('.memory-card');
        if (!card) return;
        document.getElementById('memoryModalTitle').textContent = 'Edit Memory Fact';
        document.getElementById('memInputId').value = card.dataset.id;
        document.getElementById('memInputKey').value = card.dataset.key;
        document.getElementById('memInputVal').value = card.dataset.val;
        const phone = card.dataset.phone || '';
        document.getElementById('memInputPhone').value = phone;
        document.getElementById('memInputScope').value = phone ? 'customer' : 'owner';
        document.getElementById('memPhoneWrap').classList.toggle('hidden', !phone);
        const isDurable = (card.dataset.durable === '1');
        document.getElementById('memInputDurable').checked = isDurable;
        document.getElementById('memTtlWrap').classList.toggle('hidden', isDurable);
        document.getElementById('memoryModal').classList.remove('hidden');
    }

    async function submitMemoryForm(e) {
        e.preventDefault();
        const id = document.getElementById('memInputId').value;
        const key = document.getElementById('memInputKey').value;
        const value = document.getElementById('memInputVal').value;
        const scope = document.getElementById('memInputScope').value;
        const phone = (scope === 'customer') ? document.getElementById('memInputPhone').value : '';
        const isDurable = document.getElementById('memInputDurable').checked ? 1 : 0;
        const ttlDays = document.getElementById('memInputTtl').value;

        try {
            const res = await fetch('<?= e(base_url("api/memory.php?action=save_memory")) ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, key, value, contact_phone: phone, is_durable: isDurable, ttl_days: ttlDays })
            });
            const data = await res.json();
            if (data.success) {
                closeMemoryModal();
                window.location.reload();
            } else {
                alert(data.error || 'Failed to save memory.');
            }
        } catch (err) {
            alert('Request failed: ' + err.message);
        }
    }

    async function deleteMemoryRow(id, key) {
        if (!confirm(`Are you sure you want to forget and delete "${key}"?`)) return;
        try {
            const res = await fetch('<?= e(base_url("api/memory.php?action=delete_memory")) ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, key })
            });
            const data = await res.json();
            if (data.success) {
                const card = document.querySelector(`.memory-card[data-id="${id}"]`);
                if (card) card.remove();
                const badge = document.getElementById('badgeMemTab');
                if (badge) badge.textContent = Math.max(0, parseInt(badge.textContent || '1') - 1);
                const kpi = document.getElementById('kpiTotalMemories');
                if (kpi) kpi.textContent = Math.max(0, parseInt(kpi.textContent || '1') - 1);
            } else {
                alert(data.error || 'Failed to delete memory.');
            }
        } catch (err) {
            alert('Request failed: ' + err.message);
        }
    }

    function openTrainingModal() {
        document.getElementById('trainingModalTitle').textContent = 'Add Few-Shot Training Pair';
        document.getElementById('trainInputId').value = '';
        document.getElementById('trainInputQuery').value = '';
        document.getElementById('trainInputResp').value = '';
        document.getElementById('trainInputConst').value = '';
        document.getElementById('trainingModal').classList.remove('hidden');
    }

    function closeTrainingModal() {
        document.getElementById('trainingModal').classList.add('hidden');
    }

    function editTrainingRow(btn) {
        const card = btn.closest('.training-card');
        if (!card) return;
        document.getElementById('trainingModalTitle').textContent = 'Edit Few-Shot Training Pair';
        document.getElementById('trainInputId').value = card.dataset.id;
        document.getElementById('trainInputQuery').value = card.dataset.query;
        document.getElementById('trainInputResp').value = card.dataset.resp;
        document.getElementById('trainInputConst').value = card.dataset.const || '';
        document.getElementById('trainingModal').classList.remove('hidden');
    }

    async function submitTrainingForm(e) {
        e.preventDefault();
        const id = document.getElementById('trainInputId').value;
        const query = document.getElementById('trainInputQuery').value;
        const resp = document.getElementById('trainInputResp').value;
        const constraint = document.getElementById('trainInputConst').value;

        try {
            const res = await fetch('<?= e(base_url("api/memory.php?action=save_training")) ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, user_query: query, ideal_response: resp, negative_constraint: constraint })
            });
            const data = await res.json();
            if (data.success) {
                closeTrainingModal();
                window.location.reload();
            } else {
                alert(data.error || 'Failed to save training pair.');
            }
        } catch (err) {
            alert('Request failed: ' + err.message);
        }
    }

    async function deleteTrainingRow(id) {
        if (!confirm('Are you sure you want to delete this training pair?')) return;
        try {
            const res = await fetch('<?= e(base_url("api/memory.php?action=delete_training")) ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const data = await res.json();
            if (data.success) {
                const card = document.querySelector(`.training-card[data-id="${id}"]`);
                if (card) card.remove();
                const badge = document.getElementById('badgeTrainTab');
                if (badge) badge.textContent = Math.max(0, parseInt(badge.textContent || '1') - 1);
                const kpi = document.getElementById('kpiTotalTraining');
                if (kpi) kpi.textContent = Math.max(0, parseInt(kpi.textContent || '1') - 1);
            } else {
                alert(data.error || 'Failed to delete training pair.');
            }
        } catch (err) {
            alert('Request failed: ' + err.message);
        }
    }

    // Keyboard shortcut ESC to dismiss modals
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeMemoryModal();
            closeTrainingModal();
        }
    });
</script>
