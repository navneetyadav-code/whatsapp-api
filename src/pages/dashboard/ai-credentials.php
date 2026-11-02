<?php
/**
 * AI Provider Keys & User SMTP Credentials Studio
 * Redesigned per ui-ux-pro-max guidelines:
 * - Proper vertical multi-column card grid (DevEx best practices)
 * - Accordions collapsed by default
 * - Zero emojis as icons (100% SVG Heroicons/Lucide)
 * - One-click key copying with visual feedback
 * - Interactive provider tiles that expand respective sections
 */
?>

<div class="space-y-4 mb-8 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200">
        <div>
            <h1 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight flex items-center gap-2">
                <span>AI Keys & SMTP</span>
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Isolated Storage
                </span>
                <span class="text-[10px] font-mono text-slate-500 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">
                    Priority: <?= e(strtoupper($aiSettings['provider_priority'] ?? 'GEMINI')) ?>
                </span>
            </h1>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" onclick="openAddAiKeyModal('gemini')" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-lg shadow-xs transition-all active:scale-[0.98] cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add Key</span>
            </button>
            <button type="button" onclick="openAddSmtpModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-purple-700 bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded-lg shadow-2xs transition-all active:scale-[0.98] cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span>Add SMTP</span>
            </button>
            <a href="<?= e(base_url('index.php?page=chatbot')) ?>" class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50 border border-slate-200 rounded-lg transition-colors">
                <span>AI Studio →</span>
            </a>
        </div>
    </div>

    <!-- Quick Overview Provider Tiles (Click to Toggle Section) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <!-- Gemini Tile -->
        <div onclick="toggleAccordion('acc-gemini')" class="bg-white rounded-xl border border-slate-200 p-3 shadow-2xs hover:border-indigo-300 hover:shadow-xs transition-all cursor-pointer flex items-center justify-between group">
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider group-hover:text-indigo-600 transition-colors">Google Gemini</p>
                <p class="text-base font-bold text-slate-900 mt-0.5"><?= count($geminiKeys) ?> <span class="text-[11px] font-normal text-slate-400 font-mono">keys</span></p>
            </div>
            <div class="flex items-center gap-1.5">
                <button type="button" onclick="event.stopPropagation(); openAddAiKeyModal('gemini')" class="p-1 rounded text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors cursor-pointer" title="Add Gemini Key">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </button>
                <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.286L13 21l-2.286-6.857L5 12l5.714-2.286L13 3z"/></svg>
                </div>
            </div>
        </div>

        <!-- Groq Tile -->
        <div onclick="toggleAccordion('acc-groq')" class="bg-white rounded-xl border border-slate-200 p-3 shadow-2xs hover:border-amber-300 hover:shadow-xs transition-all cursor-pointer flex items-center justify-between group">
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider group-hover:text-amber-600 transition-colors">Groq LPU</p>
                <p class="text-base font-bold text-slate-900 mt-0.5"><?= count($groqKeys) ?> <span class="text-[11px] font-normal text-slate-400 font-mono">keys</span></p>
            </div>
            <div class="flex items-center gap-1.5">
                <button type="button" onclick="event.stopPropagation(); openAddAiKeyModal('groq')" class="p-1 rounded text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition-colors cursor-pointer" title="Add Groq Key">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </button>
                <div class="w-8 h-8 rounded-lg bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
            </div>
        </div>

        <!-- OpenRouter Tile -->
        <div onclick="toggleAccordion('acc-openrouter')" class="bg-white rounded-xl border border-slate-200 p-3 shadow-2xs hover:border-blue-300 hover:shadow-xs transition-all cursor-pointer flex items-center justify-between group">
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider group-hover:text-blue-600 transition-colors">OpenRouter</p>
                <p class="text-base font-bold text-slate-900 mt-0.5"><?= count($openrouterKeys) ?> <span class="text-[11px] font-normal text-slate-400 font-mono">keys</span></p>
            </div>
            <div class="flex items-center gap-1.5">
                <button type="button" onclick="event.stopPropagation(); openAddAiKeyModal('openrouter')" class="p-1 rounded text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors cursor-pointer" title="Add OpenRouter Key">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </button>
                <div class="w-8 h-8 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                </div>
            </div>
        </div>

        <!-- SMTP Tile -->
        <div onclick="toggleAccordion('acc-smtp')" class="bg-white rounded-xl border border-slate-200 p-3 shadow-2xs hover:border-purple-300 hover:shadow-xs transition-all cursor-pointer flex items-center justify-between group">
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider group-hover:text-purple-600 transition-colors">SMTP Servers</p>
                <p class="text-base font-bold text-slate-900 mt-0.5"><?= count($smtpServers) ?> <span class="text-[11px] font-normal text-slate-400 font-mono">servers</span></p>
            </div>
            <div class="flex items-center gap-1.5">
                <button type="button" onclick="event.stopPropagation(); openAddSmtpModal()" class="p-1 rounded text-slate-400 hover:text-purple-600 hover:bg-purple-50 transition-colors cursor-pointer" title="Add SMTP Server">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </button>
                <div class="w-8 h-8 rounded-lg bg-purple-50 border border-purple-100 flex items-center justify-center text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Provider Sections (Collapsible, Closed by Default) -->
    <div class="space-y-3">

        <!-- 1. Google Gemini Keys -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 bg-slate-50/70 border-b border-slate-200 cursor-pointer select-none" onclick="toggleAccordion('acc-gemini')">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.286L13 21l-2.286-6.857L5 12l5.714-2.286L13 3z"/></svg>
                    <span class="text-xs font-bold text-slate-900">Google Gemini</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        <?= count($geminiKeys) ?> <?= count($geminiKeys) === 1 ? 'Key' : 'Keys' ?>
                    </span>
                    <?php if (!empty($aiSettings['gemini_model'])): ?>
                        <span class="text-[10px] font-mono text-slate-500 bg-white px-2 py-0.5 rounded border border-slate-200">
                            Backend Default: <?= e($aiSettings['gemini_model']) ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="event.stopPropagation(); openAddAiKeyModal('gemini')" class="px-2 py-1 text-[11px] font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded shadow-2xs transition-colors cursor-pointer">
                        + Add Key
                    </button>
                    <svg id="chevron-acc-gemini" class="w-4 h-4 text-slate-400 transition-transform duration-200 -rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </div>

            <!-- Content Grid (Hidden by Default) -->
            <div id="acc-gemini" class="hidden p-3.5 sm:p-4">
                <?php if (empty($geminiKeys)): ?>
                    <div class="py-6 text-center text-xs text-slate-400">
                        No Gemini keys configured. <button type="button" onclick="openAddAiKeyModal('gemini')" class="text-indigo-600 font-semibold hover:underline cursor-pointer">+ Add First Key</button>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <?php foreach ($geminiKeys as $idx => $k): ?>
                            <div class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-2xs hover:border-slate-300 hover:shadow-xs transition-all flex flex-col justify-between space-y-3" id="key-row-<?= $k['id'] ?>">
                                <div>
                                    <!-- Card Header: Priority, Status, Actions -->
                                    <div class="flex items-center justify-between gap-1 mb-2">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="font-mono text-[10px] font-bold text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200">
                                                #<?= e($k['priority']) ?> <?= $idx === 0 ? 'Primary' : 'Fallback' ?>
                                            </span>
                                            <?php if ((int)$k['is_active'] === 0): ?>
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">Paused</span>
                                            <?php elseif ($k['status'] === 'rate_limited'): ?>
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-200">Rate Limited (429)</span>
                                            <?php elseif ($k['status'] === 'error'): ?>
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-rose-50 text-rose-700 border border-rose-200">Error</span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="flex items-center gap-1">
                                            <button type="button" onclick="editAiKey(<?= htmlspecialchars(json_encode($k), ENT_QUOTES, 'UTF-8') ?>)" class="p-1 rounded text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer" title="Edit">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            </button>
                                            <button type="button" onclick="deleteAiKey(<?= $k['id'] ?>, '<?= e($k['label']) ?>')" class="p-1 rounded text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer" title="Delete">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Key Label -->
                                    <h4 class="text-xs font-bold text-slate-900 truncate" title="<?= e($k['label']) ?>">
                                        <?= e($k['label']) ?>
                                    </h4>

                                    <!-- Masked Key with Copy Action -->
                                    <div class="mt-2 flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-200/80">
                                        <span class="font-mono text-xs text-slate-700 truncate">
                                            <?= e(substr($k['api_key'], 0, 7)) ?>••••••••<?= e(substr($k['api_key'], -4)) ?>
                                        </span>
                                        <button type="button" onclick="copyKeyText('<?= e($k['api_key']) ?>', this)" class="text-slate-400 hover:text-slate-700 p-0.5 rounded transition-colors cursor-pointer" title="Copy Key">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        </button>
                                    </div>

                                    <!-- Model & Telemetry -->
                                    <div class="mt-2.5 flex items-center justify-between text-[11px] text-slate-500 flex-wrap gap-1">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-slate-100 text-slate-700 border border-slate-200 truncate max-w-[170px]" title="Model used by this key">
                                            <?= e($k['model'] ?: ($aiSettings['gemini_model'] ?? 'backend default')) ?>
                                        </span>
                                        <?php if (!empty($k['last_used_at'])): ?>
                                            <span class="text-[10px] text-slate-400">Used <?= e(date('M j, H:i', strtotime($k['last_used_at']))) ?></span>
                                        <?php else: ?>
                                            <span class="text-[10px] text-slate-400">Never used</span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if (!empty($k['last_error'])): ?>
                                        <p class="text-[10px] text-rose-600 font-mono truncate mt-1.5" title="<?= e($k['last_error']) ?>">
                                            ⚠ <?= e($k['last_error']) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <!-- Card Footer: Test & Toggle -->
                                <div class="pt-2 border-t border-slate-100 flex items-center gap-2">
                                    <button type="button" onclick="testAiKey(<?= $k['id'] ?>, 'gemini')" id="btn-test-<?= $k['id'] ?>" class="flex-1 inline-flex items-center justify-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors cursor-pointer">
                                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        <span>Test</span>
                                    </button>
                                    <button type="button" onclick="toggleKeyActive(<?= $k['id'] ?>, <?= (int)$k['is_active'] === 1 ? 0 : 1 ?>)" class="px-2.5 py-1.5 rounded-lg text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 border border-slate-200 transition-colors cursor-pointer">
                                        <?= (int)$k['is_active'] === 1 ? 'Pause' : 'Activate' ?>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 2. Groq LPU Keys -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 bg-slate-50/70 border-b border-slate-200 cursor-pointer select-none" onclick="toggleAccordion('acc-groq')">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span class="text-xs font-bold text-slate-900">Groq LPU</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                        <?= count($groqKeys) ?> <?= count($groqKeys) === 1 ? 'Key' : 'Keys' ?>
                    </span>
                    <?php if (!empty($aiSettings['groq_model'])): ?>
                        <span class="text-[10px] font-mono text-slate-500 bg-white px-2 py-0.5 rounded border border-slate-200">
                            Backend Default: <?= e($aiSettings['groq_model']) ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="event.stopPropagation(); openAddAiKeyModal('groq')" class="px-2 py-1 text-[11px] font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded shadow-2xs transition-colors cursor-pointer">
                        + Add Key
                    </button>
                    <svg id="chevron-acc-groq" class="w-4 h-4 text-slate-400 transition-transform duration-200 -rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </div>

            <!-- Content Grid (Hidden by Default) -->
            <div id="acc-groq" class="hidden p-3.5 sm:p-4">
                <?php if (empty($groqKeys)): ?>
                    <div class="py-6 text-center text-xs text-slate-400">
                        No Groq keys configured. <button type="button" onclick="openAddAiKeyModal('groq')" class="text-amber-600 font-semibold hover:underline cursor-pointer">+ Add First Key</button>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <?php foreach ($groqKeys as $idx => $k): ?>
                            <div class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-2xs hover:border-slate-300 hover:shadow-xs transition-all flex flex-col justify-between space-y-3" id="key-row-<?= $k['id'] ?>">
                                <div>
                                    <!-- Card Header: Priority, Status, Actions -->
                                    <div class="flex items-center justify-between gap-1 mb-2">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="font-mono text-[10px] font-bold text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200">
                                                #<?= e($k['priority']) ?> <?= $idx === 0 ? 'Primary' : 'Fallback' ?>
                                            </span>
                                            <?php if ((int)$k['is_active'] === 0): ?>
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">Paused</span>
                                            <?php elseif ($k['status'] === 'rate_limited'): ?>
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-200">Rate Limited</span>
                                            <?php elseif ($k['status'] === 'error'): ?>
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-rose-50 text-rose-700 border border-rose-200">Error</span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="flex items-center gap-1">
                                            <button type="button" onclick="editAiKey(<?= htmlspecialchars(json_encode($k), ENT_QUOTES, 'UTF-8') ?>)" class="p-1 rounded text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer" title="Edit">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            </button>
                                            <button type="button" onclick="deleteAiKey(<?= $k['id'] ?>, '<?= e($k['label']) ?>')" class="p-1 rounded text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer" title="Delete">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Key Label -->
                                    <h4 class="text-xs font-bold text-slate-900 truncate" title="<?= e($k['label']) ?>">
                                        <?= e($k['label']) ?>
                                    </h4>

                                    <!-- Masked Key with Copy Action -->
                                    <div class="mt-2 flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-200/80">
                                        <span class="font-mono text-xs text-slate-700 truncate">
                                            <?= e(substr($k['api_key'], 0, 7)) ?>••••••••<?= e(substr($k['api_key'], -4)) ?>
                                        </span>
                                        <button type="button" onclick="copyKeyText('<?= e($k['api_key']) ?>', this)" class="text-slate-400 hover:text-slate-700 p-0.5 rounded transition-colors cursor-pointer" title="Copy Key">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        </button>
                                    </div>

                                    <!-- Model & Telemetry -->
                                    <div class="mt-2.5 flex items-center justify-between text-[11px] text-slate-500 flex-wrap gap-1">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-slate-100 text-slate-700 border border-slate-200 truncate max-w-[170px]" title="Model used by this key">
                                            <?= e($k['model'] ?: ($aiSettings['groq_model'] ?? 'backend default')) ?>
                                        </span>
                                        <?php if (!empty($k['last_used_at'])): ?>
                                            <span class="text-[10px] text-slate-400">Used <?= e(date('M j, H:i', strtotime($k['last_used_at']))) ?></span>
                                        <?php else: ?>
                                            <span class="text-[10px] text-slate-400">Never used</span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if (!empty($k['last_error'])): ?>
                                        <p class="text-[10px] text-rose-600 font-mono truncate mt-1.5" title="<?= e($k['last_error']) ?>">
                                            ⚠ <?= e($k['last_error']) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <!-- Card Footer: Test & Toggle -->
                                <div class="pt-2 border-t border-slate-100 flex items-center gap-2">
                                    <button type="button" onclick="testAiKey(<?= $k['id'] ?>, 'groq')" id="btn-test-<?= $k['id'] ?>" class="flex-1 inline-flex items-center justify-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors cursor-pointer">
                                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        <span>Test</span>
                                    </button>
                                    <button type="button" onclick="toggleKeyActive(<?= $k['id'] ?>, <?= (int)$k['is_active'] === 1 ? 0 : 1 ?>)" class="px-2.5 py-1.5 rounded-lg text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 border border-slate-200 transition-colors cursor-pointer">
                                        <?= (int)$k['is_active'] === 1 ? 'Pause' : 'Activate' ?>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 3. OpenRouter Keys -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 bg-slate-50/70 border-b border-slate-200 cursor-pointer select-none" onclick="toggleAccordion('acc-openrouter')">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                    <span class="text-xs font-bold text-slate-900">OpenRouter</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                        <?= count($openrouterKeys) ?> <?= count($openrouterKeys) === 1 ? 'Key' : 'Keys' ?>
                    </span>
                    <?php if (!empty($aiSettings['openrouter_model'])): ?>
                        <span class="text-[10px] font-mono text-slate-500 bg-white px-2 py-0.5 rounded border border-slate-200">
                            Backend Default: <?= e($aiSettings['openrouter_model']) ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="event.stopPropagation(); openAddAiKeyModal('openrouter')" class="px-2 py-1 text-[11px] font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded shadow-2xs transition-colors cursor-pointer">
                        + Add Key
                    </button>
                    <svg id="chevron-acc-openrouter" class="w-4 h-4 text-slate-400 transition-transform duration-200 -rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </div>

            <!-- Content Grid (Hidden by Default) -->
            <div id="acc-openrouter" class="hidden p-3.5 sm:p-4">
                <?php if (empty($openrouterKeys)): ?>
                    <div class="py-6 text-center text-xs text-slate-400">
                        No OpenRouter keys configured. <button type="button" onclick="openAddAiKeyModal('openrouter')" class="text-blue-600 font-semibold hover:underline cursor-pointer">+ Add Key</button>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <?php foreach ($openrouterKeys as $idx => $k): ?>
                            <div class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-2xs hover:border-slate-300 hover:shadow-xs transition-all flex flex-col justify-between space-y-3" id="key-row-<?= $k['id'] ?>">
                                <div>
                                    <!-- Card Header: Priority, Status, Actions -->
                                    <div class="flex items-center justify-between gap-1 mb-2">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="font-mono text-[10px] font-bold text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200">
                                                #<?= e($k['priority']) ?>
                                            </span>
                                            <?php if ((int)$k['is_active'] === 0): ?>
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">Paused</span>
                                            <?php elseif ($k['status'] === 'rate_limited'): ?>
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-200">Rate Limited</span>
                                            <?php elseif ($k['status'] === 'error'): ?>
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-rose-50 text-rose-700 border border-rose-200">Error</span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="flex items-center gap-1">
                                            <button type="button" onclick="editAiKey(<?= htmlspecialchars(json_encode($k), ENT_QUOTES, 'UTF-8') ?>)" class="p-1 rounded text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer" title="Edit">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            </button>
                                            <button type="button" onclick="deleteAiKey(<?= $k['id'] ?>, '<?= e($k['label']) ?>')" class="p-1 rounded text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer" title="Delete">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Key Label -->
                                    <h4 class="text-xs font-bold text-slate-900 truncate" title="<?= e($k['label']) ?>">
                                        <?= e($k['label']) ?>
                                    </h4>

                                    <!-- Masked Key with Copy Action -->
                                    <div class="mt-2 flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-200/80">
                                        <span class="font-mono text-xs text-slate-700 truncate">
                                            <?= e(substr($k['api_key'], 0, 7)) ?>••••••••<?= e(substr($k['api_key'], -4)) ?>
                                        </span>
                                        <button type="button" onclick="copyKeyText('<?= e($k['api_key']) ?>', this)" class="text-slate-400 hover:text-slate-700 p-0.5 rounded transition-colors cursor-pointer" title="Copy Key">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        </button>
                                    </div>

                                    <!-- Model & Telemetry -->
                                    <div class="mt-2.5 flex items-center justify-between text-[11px] text-slate-500 flex-wrap gap-1">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-slate-100 text-slate-700 border border-slate-200 truncate max-w-[170px]" title="Model used by this key">
                                            <?= e($k['model'] ?: ($aiSettings['openrouter_model'] ?? 'backend default')) ?>
                                        </span>
                                        <?php if (!empty($k['last_used_at'])): ?>
                                            <span class="text-[10px] text-slate-400">Used <?= e(date('M j, H:i', strtotime($k['last_used_at']))) ?></span>
                                        <?php else: ?>
                                            <span class="text-[10px] text-slate-400">Never used</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Card Footer: Test & Toggle -->
                                <div class="pt-2 border-t border-slate-100 flex items-center gap-2">
                                    <button type="button" onclick="testAiKey(<?= $k['id'] ?>, 'openrouter')" id="btn-test-<?= $k['id'] ?>" class="flex-1 inline-flex items-center justify-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors cursor-pointer">
                                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        <span>Test</span>
                                    </button>
                                    <button type="button" onclick="toggleKeyActive(<?= $k['id'] ?>, <?= (int)$k['is_active'] === 1 ? 0 : 1 ?>)" class="px-2.5 py-1.5 rounded-lg text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 border border-slate-200 transition-colors cursor-pointer">
                                        <?= (int)$k['is_active'] === 1 ? 'Pause' : 'Activate' ?>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 4. SMTP Email Servers -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 bg-slate-50/70 border-b border-slate-200 cursor-pointer select-none" onclick="toggleAccordion('acc-smtp')">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span class="text-xs font-bold text-slate-900">SMTP Email Dispatchers</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                        <?= count($smtpServers) ?> <?= count($smtpServers) === 1 ? 'Server' : 'Servers' ?>
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="event.stopPropagation(); openAddSmtpModal()" class="px-2 py-1 text-[11px] font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded shadow-2xs transition-colors cursor-pointer">
                        + Add SMTP
                    </button>
                    <svg id="chevron-acc-smtp" class="w-4 h-4 text-slate-400 transition-transform duration-200 -rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </div>

            <!-- Content Grid (Hidden by Default) -->
            <div id="acc-smtp" class="hidden p-3.5 sm:p-4">
                <?php if (empty($smtpServers)): ?>
                    <div class="py-6 text-center text-xs text-slate-400">
                        No SMTP servers configured. <button type="button" onclick="openAddSmtpModal()" class="text-purple-600 font-semibold hover:underline cursor-pointer">+ Add SMTP Server</button>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                        <?php foreach ($smtpServers as $idx => $s): ?>
                            <div class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-2xs hover:border-slate-300 hover:shadow-xs transition-all flex flex-col justify-between space-y-3" id="smtp-row-<?= $s['id'] ?>">
                                <div>
                                    <!-- Header: Priority, Status, Actions -->
                                    <div class="flex items-center justify-between gap-1 mb-2">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="font-mono text-[10px] font-bold text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded border border-purple-200">
                                                #<?= e($s['priority']) ?> <?= $idx === 0 ? 'Default Outbound' : 'Backup' ?>
                                            </span>
                                            <?php if ((int)$s['is_active'] === 0): ?>
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">Paused</span>
                                            <?php elseif ($s['status'] === 'auth_failed'): ?>
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-rose-50 text-rose-700 border border-rose-200">Auth Failed</span>
                                            <?php elseif ($s['status'] === 'connection_failed'): ?>
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-200">Conn Failed</span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Connected
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="flex items-center gap-1">
                                            <button type="button" onclick="editSmtp(<?= htmlspecialchars(json_encode($s), ENT_QUOTES, 'UTF-8') ?>)" class="p-1 rounded text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer" title="Edit">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            </button>
                                            <button type="button" onclick="deleteSmtp(<?= $s['id'] ?>, '<?= e($s['label']) ?>')" class="p-1 rounded text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer" title="Delete">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Label Title -->
                                    <h4 class="text-xs font-bold text-slate-900 truncate" title="<?= e($s['label']) ?>">
                                        <?= e($s['label']) ?>
                                    </h4>

                                    <!-- Host & Port Box -->
                                    <div class="mt-2 p-2.5 rounded-lg bg-slate-50 border border-slate-200/80 space-y-1 text-xs">
                                        <div class="flex items-center justify-between font-mono text-slate-700">
                                            <span class="truncate"><?= e($s['host']) ?>:<?= e($s['port']) ?></span>
                                            <span class="text-[10px] font-semibold text-purple-700 uppercase bg-purple-50 px-1.5 py-0.2 rounded border border-purple-200"><?= e(strtoupper($s['encryption'])) ?></span>
                                        </div>
                                        <div class="flex items-center justify-between text-[11px] text-slate-500 truncate pt-0.5">
                                            <span class="truncate">From: <strong class="text-slate-700 font-normal"><?= e($s['from_email']) ?></strong></span>
                                            <span class="text-[10px] text-slate-400 truncate max-w-[120px]"><?= e($s['from_name']) ?></span>
                                        </div>
                                    </div>

                                    <?php if (!empty($s['last_error'])): ?>
                                        <p class="text-[10px] text-rose-600 font-mono truncate mt-1.5" title="<?= e($s['last_error']) ?>">
                                            ⚠ <?= e($s['last_error']) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <!-- Card Footer: Test & Actions -->
                                <div class="pt-2 border-t border-slate-100 flex items-center gap-2">
                                    <button type="button" onclick="testSmtp(<?= $s['id'] ?>)" id="btn-test-smtp-<?= $s['id'] ?>" class="flex-1 inline-flex items-center justify-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors cursor-pointer">
                                        <span>Test Conn</span>
                                    </button>
                                    <button type="button" onclick="promptSendTestEmail(<?= $s['id'] ?>, '<?= e($s['from_email']) ?>')" class="inline-flex items-center justify-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 transition-colors cursor-pointer">
                                        <span>Send Test</span>
                                    </button>
                                    <button type="button" onclick="toggleSmtpActive(<?= $s['id'] ?>, <?= (int)$s['is_active'] === 1 ? 0 : 1 ?>)" class="px-2.5 py-1.5 rounded-lg text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 border border-slate-200 transition-colors cursor-pointer">
                                        <?= (int)$s['is_active'] === 1 ? 'Pause' : 'Activate' ?>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<!-- ================= MODALS ================= -->

<!-- 1. Add / Edit AI Key Modal -->
<div id="aiKeyModal" class="fixed inset-0 z-50 hidden bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-xl max-w-md w-full overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 bg-slate-50/70">
            <h3 id="modalKeyTitle" class="text-xs font-bold uppercase tracking-wider text-slate-900">Add AI API Key</h3>
            <button type="button" onclick="closeAiKeyModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">✕</button>
        </div>
        <form id="aiKeyForm" onsubmit="handleSaveAiKey(event)" class="p-4 space-y-3">
            <input type="hidden" id="keyRecordId" name="id" value="">
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Provider</label>
                    <select id="keyProvider" name="provider" class="w-full text-xs rounded-lg border border-slate-200 px-2.5 py-1.5 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        <option value="gemini">Google Gemini</option>
                        <option value="groq">Groq LPU</option>
                        <option value="openrouter">OpenRouter</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Priority Order</label>
                    <input type="number" id="keyPriority" name="priority" value="1" min="1" max="99" class="w-full text-xs rounded-lg border border-slate-200 px-2.5 py-1.5 bg-white font-mono text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Label</label>
                <input type="text" id="keyLabel" name="label" required placeholder="e.g. Primary Key, Fallback 2" class="w-full text-xs rounded-lg border border-slate-200 px-2.5 py-1.5 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-[11px] font-semibold text-slate-700">API Key</label>
                    <span id="keyOptionalNotice" class="text-[10px] text-slate-400 hidden">(Unchanged if blank)</span>
                </div>
                <div class="relative">
                    <input type="password" id="keyString" name="api_key" placeholder="Key string..." class="w-full text-xs rounded-lg border border-slate-200 px-2.5 py-1.5 pr-8 bg-white font-mono text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    <button type="button" onclick="togglePasswordVisibility('keyString')" class="absolute right-2 top-2 text-slate-400 hover:text-slate-700">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Model (Optional Customization)</label>
                <input type="text" id="keyModel" name="model" list="modelSuggestions" placeholder="Leave empty for backend default" class="w-full text-xs rounded-lg border border-slate-200 px-2.5 py-1.5 bg-white font-mono text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                <datalist id="modelSuggestions">
                    <option value="gemini-2.5-flash">
                    <option value="gemini-2.5-pro">
                    <option value="gemini-3.5-flash">
                    <option value="openai/gpt-oss-120b">
                    <option value="llama-3.3-70b-versatile">
                    <option value="meta-llama/llama-3.3-70b-instruct:free">
                </datalist>
            </div>

            <div class="pt-1">
                <button type="button" onclick="testModalAiKey()" id="btnModalTestAi" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 transition-colors cursor-pointer">
                    <span>Test Key</span>
                </button>
            </div>

            <div id="modalAiTestResult" class="hidden p-2 rounded-lg text-[11px] font-mono leading-relaxed"></div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeAiKeyModal()" class="px-3 py-1.5 rounded-lg text-xs font-medium text-slate-600 hover:bg-slate-100 cursor-pointer">Cancel</button>
                <button type="submit" id="btnSaveAiKey" class="px-4 py-1.5 rounded-lg text-xs font-semibold bg-emerald-600 hover:bg-emerald-500 text-white shadow-xs cursor-pointer">Save Key</button>
            </div>
        </form>
    </div>
</div>

<!-- 2. Add / Edit SMTP Modal -->
<div id="smtpModal" class="fixed inset-0 z-50 hidden bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-xl max-w-lg w-full overflow-hidden animate-in fade-in zoom-in-95 duration-150 max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 bg-slate-50/70 shrink-0">
            <h3 id="modalSmtpTitle" class="text-xs font-bold uppercase tracking-wider text-slate-900">Configure SMTP Server</h3>
            <button type="button" onclick="closeSmtpModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">✕</button>
        </div>
        <form id="smtpForm" onsubmit="handleSaveSmtp(event)" class="p-4 space-y-3 overflow-y-auto flex-1">
            <input type="hidden" id="smtpRecordId" name="id" value="">

            <div>
                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Configuration Label</label>
                <input type="text" id="smtpLabel" name="label" required placeholder="e.g. Work Google Workspace, Outbound SMTP" class="w-full text-xs rounded-lg border border-slate-200 px-2.5 py-1.5 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Host</label>
                    <input type="text" id="smtpHost" name="host" required placeholder="smtp.gmail.com" class="w-full text-xs rounded-lg border border-slate-200 px-2.5 py-1.5 bg-white font-mono text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Port</label>
                    <input type="number" id="smtpPort" name="port" value="587" required class="w-full text-xs rounded-lg border border-slate-200 px-2.5 py-1.5 bg-white font-mono text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Encryption</label>
                    <select id="smtpEncryption" name="encryption" class="w-full text-xs rounded-lg border border-slate-200 px-2.5 py-1.5 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                        <option value="tls">TLS / STARTTLS (587)</option>
                        <option value="ssl">SSL (465)</option>
                        <option value="none">None (25)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Priority Order</label>
                    <input type="number" id="smtpPriority" name="priority" value="1" min="1" max="99" class="w-full text-xs rounded-lg border border-slate-200 px-2.5 py-1.5 bg-white font-mono text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Username / Login</label>
                    <input type="text" id="smtpUsername" name="username" required placeholder="user@domain.com" class="w-full text-xs rounded-lg border border-slate-200 px-2.5 py-1.5 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-[11px] font-semibold text-slate-700">Password</label>
                        <span id="smtpOptionalNotice" class="text-[10px] text-slate-400 hidden">(Unchanged if blank)</span>
                    </div>
                    <div class="relative">
                        <input type="password" id="smtpPassword" name="password" placeholder="••••••••" class="w-full text-xs rounded-lg border border-slate-200 px-2.5 py-1.5 pr-8 bg-white font-mono text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                        <button type="button" onclick="togglePasswordVisibility('smtpPassword')" class="absolute right-2 top-2 text-slate-400 hover:text-slate-700">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">From Email Address</label>
                    <input type="email" id="smtpFromEmail" name="from_email" required placeholder="assistant@domain.com" class="w-full text-xs rounded-lg border border-slate-200 px-2.5 py-1.5 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">From Display Name</label>
                    <input type="text" id="smtpFromName" name="from_name" value="AI Assistant" class="w-full text-xs rounded-lg border border-slate-200 px-2.5 py-1.5 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                </div>
            </div>

            <div class="pt-1">
                <button type="button" onclick="testModalSmtpConn()" id="btnModalTestSmtp" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 transition-colors cursor-pointer">
                    <span>Test SMTP Connection</span>
                </button>
            </div>

            <div id="modalSmtpTestResult" class="hidden p-2 rounded-lg text-[11px] font-mono leading-relaxed"></div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeSmtpModal()" class="px-3 py-1.5 rounded-lg text-xs font-medium text-slate-600 hover:bg-slate-100 cursor-pointer">Cancel</button>
                <button type="submit" id="btnSaveSmtp" class="px-4 py-1.5 rounded-lg text-xs font-semibold bg-purple-600 hover:bg-purple-700 text-white shadow-xs cursor-pointer">Save SMTP</button>
            </div>
        </form>
    </div>
</div>

<!-- 3. Send Live Test Email Modal -->
<div id="sendTestEmailModal" class="fixed inset-0 z-50 hidden bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-xl max-w-md w-full overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 bg-slate-50/70">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">Send Live Test Email</h3>
            <button type="button" onclick="closeSendTestEmailModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">✕</button>
        </div>
        <form id="sendTestEmailForm" onsubmit="handleSendRealTestEmail(event)" class="p-4 space-y-3">
            <input type="hidden" id="sendTestSmtpId" value="">
            <div>
                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Recipient Email</label>
                <input type="email" id="testEmailRecipient" required placeholder="recipient@domain.com" class="w-full text-xs rounded-lg border border-slate-200 px-2.5 py-1.5 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
            </div>
            <div id="sendTestEmailResult" class="hidden p-2 rounded-lg text-[11px] font-mono leading-relaxed"></div>
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeSendTestEmailModal()" class="px-3 py-1.5 rounded-lg text-xs font-medium text-slate-600 hover:bg-slate-100 cursor-pointer">Cancel</button>
                <button type="submit" id="btnSubmitSendTestEmail" class="px-4 py-1.5 rounded-lg text-xs font-semibold bg-purple-600 hover:bg-purple-700 text-white shadow-xs cursor-pointer">Send Email</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= JAVASCRIPT CONTROLLERS ================= -->
<script>
function toggleAccordion(id) {
    const el = document.getElementById(id);
    const chevron = document.getElementById('chevron-' + id);
    if (!el) return;
    if (el.classList.contains('hidden')) {
        el.classList.remove('hidden');
        if (chevron) {
            chevron.classList.remove('-rotate-90');
            chevron.classList.add('rotate-0');
        }
    } else {
        el.classList.add('hidden');
        if (chevron) {
            chevron.classList.remove('rotate-0');
            chevron.classList.add('-rotate-90');
        }
    }
}

function copyKeyText(text, btn) {
    if (!navigator.clipboard) return;
    navigator.clipboard.writeText(text).then(() => {
        const orig = btn.innerHTML;
        btn.innerHTML = `<svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>`;
        setTimeout(() => { btn.innerHTML = orig; }, 1500);
    });
}

function togglePasswordVisibility(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    field.type = field.type === 'password' ? 'text' : 'password';
}

const defaultBackendModels = {
    gemini: <?= json_encode($aiSettings['gemini_model'] ?? '') ?>,
    groq: <?= json_encode($aiSettings['groq_model'] ?? '') ?>,
    openrouter: <?= json_encode($aiSettings['openrouter_model'] ?? '') ?>
};

function onKeyProviderChange() {
    const prov = document.getElementById('keyProvider').value;
    const modelInput = document.getElementById('keyModel');
    if (modelInput) {
        modelInput.value = defaultBackendModels[prov] || '';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const providerSelect = document.getElementById('keyProvider');
    if (providerSelect) {
        providerSelect.addEventListener('change', onKeyProviderChange);
    }
});

function openAddAiKeyModal(provider = 'gemini') {
    document.getElementById('keyRecordId').value = '';
    document.getElementById('keyProvider').value = provider;
    document.getElementById('keyLabel').value = '';
    document.getElementById('keyString').value = '';
    document.getElementById('keyModel').value = defaultBackendModels[provider] || '';
    document.getElementById('keyPriority').value = '1';
    document.getElementById('modalKeyTitle').innerText = 'Add ' + provider.toUpperCase() + ' Key';
    document.getElementById('keyOptionalNotice').classList.add('hidden');
    document.getElementById('keyString').required = true;
    document.getElementById('modalAiTestResult').classList.add('hidden');
    document.getElementById('aiKeyModal').classList.remove('hidden');
}

function editAiKey(keyData) {
    document.getElementById('keyRecordId').value = keyData.id || '';
    document.getElementById('keyProvider').value = keyData.provider || 'gemini';
    document.getElementById('keyLabel').value = keyData.label || '';
    document.getElementById('keyString').value = '';
    document.getElementById('keyModel').value = keyData.model || defaultBackendModels[keyData.provider] || '';
    document.getElementById('keyPriority').value = keyData.priority || '1';
    document.getElementById('modalKeyTitle').innerText = 'Edit ' + (keyData.provider || '').toUpperCase() + ' Key';
    document.getElementById('keyOptionalNotice').classList.remove('hidden');
    document.getElementById('keyString').required = false;
    document.getElementById('modalAiTestResult').classList.add('hidden');
    document.getElementById('aiKeyModal').classList.remove('hidden');
}

function closeAiKeyModal() {
    document.getElementById('aiKeyModal').classList.add('hidden');
}

async function handleSaveAiKey(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSaveAiKey');
    btn.disabled = true;
    btn.innerText = 'Saving...';

    const payload = {
        id: document.getElementById('keyRecordId').value || null,
        provider: document.getElementById('keyProvider').value,
        label: document.getElementById('keyLabel').value.trim(),
        api_key: document.getElementById('keyString').value.trim(),
        model: document.getElementById('keyModel').value.trim(),
        priority: parseInt(document.getElementById('keyPriority').value, 10) || 1
    };

    try {
        const res = await fetch('<?= e(base_url('api/credentials/save-ai-key.php')) ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.ok) {
            window.location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to save key'));
            btn.disabled = false;
            btn.innerText = 'Save Key';
        }
    } catch (err) {
        alert('Network error: ' + err.message);
        btn.disabled = false;
        btn.innerText = 'Save Key';
    }
}

async function testModalAiKey() {
    const btn = document.getElementById('btnModalTestAi');
    const resultBox = document.getElementById('modalAiTestResult');
    const apiKey = document.getElementById('keyString').value.trim();
    const provider = document.getElementById('keyProvider').value;
    const model = document.getElementById('keyModel').value.trim();
    const keyId = document.getElementById('keyRecordId').value || null;

    if (!apiKey && !keyId) {
        alert('Please enter an API key to test.');
        return;
    }

    btn.disabled = true;
    btn.innerText = 'Testing...';
    resultBox.classList.remove('hidden');
    resultBox.className = 'p-2 rounded-lg text-[11px] font-mono leading-relaxed bg-slate-100 text-slate-700';
    resultBox.innerText = `Connecting to ${provider.toUpperCase()}...`;

    try {
        const res = await fetch('<?= e(base_url('api/credentials/test-ai-key.php')) ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: keyId, provider, api_key: apiKey, model })
        });
        const data = await res.json();
        if (data.ok) {
            resultBox.className = 'p-2 rounded-lg text-[11px] font-mono leading-relaxed bg-emerald-50 border border-emerald-200 text-emerald-800';
            resultBox.innerText = data.message || `✓ Success (${data.latency_ms}ms)`;
        } else {
            resultBox.className = 'p-2 rounded-lg text-[11px] font-mono leading-relaxed bg-rose-50 border border-rose-200 text-rose-800';
            resultBox.innerText = `✗ Failed: ${data.error || 'Unknown error'}`;
        }
    } catch (err) {
        resultBox.className = 'p-2 rounded-lg text-[11px] font-mono leading-relaxed bg-rose-50 border border-rose-200 text-rose-800';
        resultBox.innerText = `✗ Connection error: ${err.message}`;
    } finally {
        btn.disabled = false;
        btn.innerText = 'Test Key';
    }
}

async function testAiKey(id, provider) {
    const btn = document.getElementById('btn-test-' + id);
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = 'Testing...';
    }

    try {
        const res = await fetch('<?= e(base_url('api/credentials/test-ai-key.php')) ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, provider })
        });
        const data = await res.json();
        if (data.ok) {
            alert(data.message || `✓ ${provider} key is active and healthy (${data.latency_ms}ms)`);
            window.location.reload();
        } else {
            alert(`✗ Test Failed: ${data.error || 'Unable to authenticate'}`);
            window.location.reload();
        }
    } catch (err) {
        alert('Network test error: ' + err.message);
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = 'Test';
        }
    }
}

async function toggleKeyActive(id, newActive) {
    try {
        const res = await fetch('<?= e(base_url('api/credentials/toggle-ai-key.php')) ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, is_active: newActive })
        });
        const data = await res.json();
        if (data.ok) {
            window.location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to toggle status'));
        }
    } catch (err) {
        alert('Network error: ' + err.message);
    }
}

async function deleteAiKey(id, label) {
    if (!confirm(`Delete key "${label}"?`)) return;

    try {
        const res = await fetch('<?= e(base_url('api/credentials/delete-ai-key.php')) ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const data = await res.json();
        if (data.ok) {
            window.location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to delete key'));
        }
    } catch (err) {
        alert('Network error: ' + err.message);
    }
}

function openAddSmtpModal() {
    document.getElementById('smtpRecordId').value = '';
    document.getElementById('smtpLabel').value = '';
    document.getElementById('smtpHost').value = 'smtp.gmail.com';
    document.getElementById('smtpPort').value = '587';
    document.getElementById('smtpEncryption').value = 'tls';
    document.getElementById('smtpUsername').value = '';
    document.getElementById('smtpPassword').value = '';
    document.getElementById('smtpFromEmail').value = '';
    document.getElementById('smtpFromName').value = 'AI Assistant';
    document.getElementById('smtpPriority').value = '1';
    document.getElementById('modalSmtpTitle').innerText = 'Configure SMTP Server';
    document.getElementById('smtpOptionalNotice').classList.add('hidden');
    document.getElementById('smtpPassword').required = true;
    document.getElementById('modalSmtpTestResult').classList.add('hidden');
    document.getElementById('smtpModal').classList.remove('hidden');
}

function editSmtp(smtpData) {
    document.getElementById('smtpRecordId').value = smtpData.id || '';
    document.getElementById('smtpLabel').value = smtpData.label || '';
    document.getElementById('smtpHost').value = smtpData.host || '';
    document.getElementById('smtpPort').value = smtpData.port || 587;
    document.getElementById('smtpEncryption').value = smtpData.encryption || 'tls';
    document.getElementById('smtpUsername').value = smtpData.username || '';
    document.getElementById('smtpPassword').value = '';
    document.getElementById('smtpFromEmail').value = smtpData.from_email || '';
    document.getElementById('smtpFromName').value = smtpData.from_name || 'AI Assistant';
    document.getElementById('smtpPriority').value = smtpData.priority || 1;
    document.getElementById('modalSmtpTitle').innerText = 'Edit SMTP Configuration';
    document.getElementById('smtpOptionalNotice').classList.remove('hidden');
    document.getElementById('smtpPassword').required = false;
    document.getElementById('modalSmtpTestResult').classList.add('hidden');
    document.getElementById('smtpModal').classList.remove('hidden');
}

function closeSmtpModal() {
    document.getElementById('smtpModal').classList.add('hidden');
}

async function handleSaveSmtp(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSaveSmtp');
    btn.disabled = true;
    btn.innerText = 'Saving...';

    const payload = {
        id: document.getElementById('smtpRecordId').value || null,
        label: document.getElementById('smtpLabel').value.trim(),
        host: document.getElementById('smtpHost').value.trim(),
        port: parseInt(document.getElementById('smtpPort').value, 10) || 587,
        encryption: document.getElementById('smtpEncryption').value,
        username: document.getElementById('smtpUsername').value.trim(),
        password: document.getElementById('smtpPassword').value,
        from_email: document.getElementById('smtpFromEmail').value.trim(),
        from_name: document.getElementById('smtpFromName').value.trim(),
        priority: parseInt(document.getElementById('smtpPriority').value, 10) || 1
    };

    try {
        const res = await fetch('<?= e(base_url('api/credentials/save-smtp.php')) ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.ok) {
            window.location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to save SMTP'));
            btn.disabled = false;
            btn.innerText = 'Save SMTP';
        }
    } catch (err) {
        alert('Network error: ' + err.message);
        btn.disabled = false;
        btn.innerText = 'Save SMTP';
    }
}

async function testModalSmtpConn() {
    const btn = document.getElementById('btnModalTestSmtp');
    const resultBox = document.getElementById('modalSmtpTestResult');
    const host = document.getElementById('smtpHost').value.trim();
    const port = parseInt(document.getElementById('smtpPort').value, 10) || 587;
    const encryption = document.getElementById('smtpEncryption').value;
    const username = document.getElementById('smtpUsername').value.trim();
    const password = document.getElementById('smtpPassword').value;
    const id = document.getElementById('smtpRecordId').value || null;

    if (!host || !username) {
        alert('Please fill Host and Username first.');
        return;
    }

    btn.disabled = true;
    btn.innerText = 'Connecting...';
    resultBox.classList.remove('hidden');
    resultBox.className = 'p-2 rounded-lg text-[11px] font-mono leading-relaxed bg-slate-100 text-slate-700';
    resultBox.innerText = `Connecting to ${host}:${port}...`;

    try {
        const res = await fetch('<?= e(base_url('api/credentials/test-smtp.php')) ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, host, port, encryption, username, password })
        });
        const data = await res.json();
        if (data.ok) {
            resultBox.className = 'p-2 rounded-lg text-[11px] font-mono leading-relaxed bg-emerald-50 border border-emerald-200 text-emerald-800';
            resultBox.innerText = data.message || `✓ SMTP Handshake succeeded in ${data.latency_ms}ms`;
        } else {
            resultBox.className = 'p-2 rounded-lg text-[11px] font-mono leading-relaxed bg-rose-50 border border-rose-200 text-rose-800';
            resultBox.innerText = `✗ Failed: ${data.error || 'Connection rejected'}`;
        }
    } catch (err) {
        resultBox.className = 'p-2 rounded-lg text-[11px] font-mono leading-relaxed bg-rose-50 border border-rose-200 text-rose-800';
        resultBox.innerText = `✗ Socket error: ${err.message}`;
    } finally {
        btn.disabled = false;
        btn.innerText = 'Test SMTP Connection';
    }
}

async function testSmtp(id) {
    const btn = document.getElementById('btn-test-smtp-' + id);
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = 'Testing...';
    }

    try {
        const res = await fetch('<?= e(base_url('api/credentials/test-smtp.php')) ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const data = await res.json();
        if (data.ok) {
            alert(data.message || `✓ Connection verified (${data.latency_ms}ms)`);
            window.location.reload();
        } else {
            alert(`✗ Test Failed: ${data.error || 'Authentication rejected'}`);
            window.location.reload();
        }
    } catch (err) {
        alert('Network test error: ' + err.message);
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = 'Test Conn';
        }
    }
}

function promptSendTestEmail(id, fromEmail) {
    document.getElementById('sendTestSmtpId').value = id;
    document.getElementById('testEmailRecipient').value = fromEmail || '';
    document.getElementById('sendTestEmailResult').classList.add('hidden');
    document.getElementById('sendTestEmailModal').classList.remove('hidden');
}

function closeSendTestEmailModal() {
    document.getElementById('sendTestEmailModal').classList.add('hidden');
}

async function handleSendRealTestEmail(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitSendTestEmail');
    const resultBox = document.getElementById('sendTestEmailResult');
    const id = document.getElementById('sendTestSmtpId').value;
    const recipient = document.getElementById('testEmailRecipient').value.trim();

    if (!recipient) {
        alert('Please enter a recipient email.');
        return;
    }

    btn.disabled = true;
    btn.innerText = 'Sending...';
    resultBox.classList.remove('hidden');
    resultBox.className = 'p-2 rounded-lg text-[11px] font-mono leading-relaxed bg-slate-100 text-slate-700';
    resultBox.innerText = `Dispatching test email to ${recipient}...`;

    try {
        const res = await fetch('<?= e(base_url('api/credentials/test-smtp.php')) ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, test_recipient: recipient })
        });
        const data = await res.json();
        if (data.ok) {
            resultBox.className = 'p-2 rounded-lg text-[11px] font-mono leading-relaxed bg-emerald-50 border border-emerald-200 text-emerald-800';
            resultBox.innerText = data.message || `✓ Test email delivered to ${recipient}`;
            setTimeout(() => { closeSendTestEmailModal(); }, 2000);
        } else {
            resultBox.className = 'p-2 rounded-lg text-[11px] font-mono leading-relaxed bg-rose-50 border border-rose-200 text-rose-800';
            resultBox.innerText = `✗ Send Failed: ${data.error || 'Delivery rejected'}`;
        }
    } catch (err) {
        resultBox.className = 'p-2 rounded-lg text-[11px] font-mono leading-relaxed bg-rose-50 border border-rose-200 text-rose-800';
        resultBox.innerText = `✗ Error: ${err.message}`;
    } finally {
        btn.disabled = false;
        btn.innerText = 'Send Email';
    }
}

async function toggleSmtpActive(id, newActive) {
    try {
        const res = await fetch('<?= e(base_url('api/credentials/toggle-smtp.php')) ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, is_active: newActive })
        });
        const data = await res.json();
        if (data.ok) {
            window.location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to toggle status'));
        }
    } catch (err) {
        alert('Network error: ' + err.message);
    }
}

async function deleteSmtp(id, label) {
    if (!confirm(`Delete SMTP server "${label}"?`)) return;

    try {
        const res = await fetch('<?= e(base_url('api/credentials/delete-smtp.php')) ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const data = await res.json();
        if (data.ok) {
            window.location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to delete SMTP server'));
        }
    } catch (err) {
        alert('Network error: ' + err.message);
    }
}
</script>
