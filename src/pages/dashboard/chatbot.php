<?php
/**
 * AI Assistant Studio — Compact Dashboard View
 * Redesigned per ui-ux-pro-max guidelines:
 * - Minimal, high-aesthetic split-pane layout (knobs on left, prompt on right)
 * - Zero explanatory clutter, concise labels
 * - Strict SVG icons (zero emojis as icons)
 * - High density & low scroll overhead
 */

$activePriority = $aiSettings['provider_priority'] ?? 'gemini';
$pOrder = match ($activePriority) {
    'groq' => ['groq', 'gemini', 'openrouter'],
    'openrouter' => ['openrouter', 'gemini', 'groq'],
    default => ['gemini', 'groq', 'openrouter'],
};
$pNames = [
    'gemini' => 'Google Gemini',
    'groq' => 'Groq LPU',
    'openrouter' => 'OpenRouter',
];
$activeMode = $aiSettings['agent_mode'] ?? 'support';
?>

<div class="space-y-4 mb-6 max-w-7xl mx-auto">
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200">
        <div class="flex items-center gap-3">
            <div>
                <h1 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>AI Assistant Studio</span>
                    <?php if ($deviceConnected): ?>
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Online <?= $devicePhone ? '(' . e($devicePhone) . ')' : '' ?>
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                            Offline
                        </span>
                    <?php endif; ?>
                </h1>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="<?= e(base_url('index.php?page=ai-credentials')) ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-lg shadow-2xs transition-colors cursor-pointer">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                <span>AI Keys & SMTP</span>
            </a>
            <button type="button" onclick="document.getElementById('aiAgentForm').requestSubmit()" class="inline-flex items-center gap-1.5 px-4 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-500 rounded-lg shadow-xs transition-all active:scale-[0.98] cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Save Changes</span>
            </button>
        </div>
    </div>

    <!-- Main Settings Form -->
    <form id="aiAgentForm" method="post" action="<?= e(base_url('index.php?page=ai-agent-save')) ?>">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <!-- Compact 2-Column Responsive Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">
            
            <!-- Left Column: Mode, Cascade & Parameters (5 cols) -->
            <div class="lg:col-span-5 space-y-4">
                
                <!-- Card 1: Engine Status & Operating Mode -->
                <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs space-y-3.5">
                    <!-- Engine Master Toggle -->
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                            </span>
                            <div>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Engine Status</h3>
                                <span id="masterStatusBadge" class="text-[11px] font-semibold <?= !empty($aiSettings['enabled']) ? 'text-emerald-700' : 'text-slate-400' ?>">
                                    <?= !empty($aiSettings['enabled']) ? 'Active & Ready' : 'Paused' ?>
                                </span>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="enabled" value="1" <?= !empty($aiSettings['enabled']) ? 'checked' : '' ?> onchange="document.getElementById('masterStatusBadge').textContent = this.checked ? 'Active & Ready' : 'Paused'; document.getElementById('masterStatusBadge').className = this.checked ? 'text-[11px] font-semibold text-emerald-700' : 'text-[11px] font-semibold text-slate-400';" class="sr-only peer">
                            <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>

                    <!-- Mode Segmented Control -->
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600">Operating Mode</label>
                        <div class="grid grid-cols-2 p-1 bg-slate-100/90 rounded-lg gap-1 border border-slate-200/80">
                            <!-- Support Option -->
                            <label id="btnModeSupport" class="flex items-center justify-center gap-1.5 py-2 px-2.5 rounded-md text-xs font-semibold transition-all cursor-pointer <?= $activeMode === 'support' ? 'bg-white text-slate-900 shadow-2xs font-bold border border-slate-200/80' : 'text-slate-600 hover:text-slate-900' ?>">
                                <input type="radio" name="agent_mode" value="support" <?= $activeMode === 'support' ? 'checked' : '' ?> class="sr-only">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <span>Support Bot</span>
                            </label>

                            <!-- Personal Option -->
                            <label id="btnModePersonal" class="flex items-center justify-center gap-1.5 py-2 px-2.5 rounded-md text-xs font-semibold transition-all cursor-pointer <?= $activeMode === 'personal' ? 'bg-white text-emerald-800 shadow-2xs font-bold border border-emerald-200' : 'text-slate-600 hover:text-slate-900' ?>">
                                <input type="radio" name="agent_mode" value="personal" <?= $activeMode === 'personal' ? 'checked' : '' ?> class="sr-only">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span>Personal Assistant (24/7)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Mode Modifiers: Salute, Feedback & Always-on -->
                    <div class="space-y-2 pt-1">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Owner Name / Salute</label>
                            <input type="text" id="ownerNameInput" name="owner_name" value="<?= e($aiSettings['owner_name'] ?? 'Boss') ?>" placeholder="Boss" class="w-full text-xs px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
                            <label class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-200/80 cursor-pointer">
                                <span class="text-[11px] font-semibold text-slate-700">24/7 Always Active</span>
                                <input type="checkbox" name="is_always_on" value="1" <?= (int) ($aiSettings['is_always_on'] ?? 1) === 1 ? 'checked' : '' ?> class="w-3.5 h-3.5 rounded text-emerald-600 focus:ring-emerald-500">
                            </label>

                            <label id="feedbackRow" class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-200/80 cursor-pointer transition-opacity <?= $activeMode === 'personal' ? 'opacity-40 cursor-not-allowed' : '' ?>">
                                <span class="text-[11px] font-semibold text-slate-700">Feedback Survey</span>
                                <input type="checkbox" id="feedbackEnabledCheckbox" name="feedback_enabled" value="1" <?= (int) ($aiSettings['feedback_enabled'] ?? 1) === 1 && $activeMode !== 'personal' ? 'checked' : '' ?> <?= $activeMode === 'personal' ? 'disabled' : '' ?> class="w-3.5 h-3.5 rounded text-emerald-600 focus:ring-emerald-500">
                            </label>
                        </div>
                    </div>

                    <!-- Mode Switch Toast -->
                    <div id="modeStatusPill" class="hidden p-2 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-900 text-[11px] flex items-center justify-between">
                        <span id="modeStatusText"></span>
                        <button type="button" onclick="document.getElementById('modeStatusPill').classList.add('hidden')" class="text-emerald-700 font-bold ml-2">✕</button>
                    </div>
                </div>

                <!-- Card 2: Priority Failover Cascade -->
                <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs space-y-2.5">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Priority Failover</h3>
                        <span id="cascadeSummaryPill" class="text-[10px] font-medium text-slate-500 bg-slate-50 px-2 py-0.5 rounded border border-slate-200">
                            <?= $pNames[$pOrder[0]] ?> → <?= $pNames[$pOrder[1]] ?> → <?= $pNames[$pOrder[2]] ?>
                        </span>
                    </div>

                    <div class="space-y-1.5">
                        <!-- Gemini -->
                        <?php $gRank = array_search('gemini', $pOrder, true) + 1; ?>
                        <label id="cardProvider_gemini" class="flex items-center justify-between p-2.5 rounded-lg border transition-all cursor-pointer <?= $activePriority === 'gemini' ? 'bg-emerald-50/50 border-emerald-500 text-slate-900' : 'bg-slate-50/60 border-slate-200 text-slate-700 hover:bg-slate-100/60' ?>">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.286L13 21l-2.286-6.857L5 12l5.714-2.286L13 3z"/></svg>
                                <span class="text-xs font-bold">Google Gemini</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span id="badgePriority_gemini" class="px-2 py-0.5 rounded text-[10px] <?= $gRank === 1 ? 'font-bold bg-emerald-100 text-emerald-800' : 'font-semibold bg-slate-200/80 text-slate-600' ?>">
                                    <?= $gRank === 1 ? '#1 Primary' : '#' . $gRank . ' Fallback' ?>
                                </span>
                                <input type="radio" name="provider_priority" value="gemini" <?= $activePriority === 'gemini' ? 'checked' : '' ?> class="sr-only">
                            </div>
                        </label>

                        <!-- Groq -->
                        <?php $qRank = array_search('groq', $pOrder, true) + 1; ?>
                        <label id="cardProvider_groq" class="flex items-center justify-between p-2.5 rounded-lg border transition-all cursor-pointer <?= $activePriority === 'groq' ? 'bg-emerald-50/50 border-emerald-500 text-slate-900' : 'bg-slate-50/60 border-slate-200 text-slate-700 hover:bg-slate-100/60' ?>">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <span class="text-xs font-bold">Groq LPU</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span id="badgePriority_groq" class="px-2 py-0.5 rounded text-[10px] <?= $qRank === 1 ? 'font-bold bg-emerald-100 text-emerald-800' : 'font-semibold bg-slate-200/80 text-slate-600' ?>">
                                    <?= $qRank === 1 ? '#1 Primary' : '#' . $qRank . ' Fallback' ?>
                                </span>
                                <input type="radio" name="provider_priority" value="groq" <?= $activePriority === 'groq' ? 'checked' : '' ?> class="sr-only">
                            </div>
                        </label>

                        <!-- OpenRouter -->
                        <?php $oRank = array_search('openrouter', $pOrder, true) + 1; ?>
                        <label id="cardProvider_openrouter" class="flex items-center justify-between p-2.5 rounded-lg border transition-all cursor-pointer <?= $activePriority === 'openrouter' ? 'bg-emerald-50/50 border-emerald-500 text-slate-900' : 'bg-slate-50/60 border-slate-200 text-slate-700 hover:bg-slate-100/60' ?>">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                                <span class="text-xs font-bold">OpenRouter</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span id="badgePriority_openrouter" class="px-2 py-0.5 rounded text-[10px] <?= $oRank === 1 ? 'font-bold bg-emerald-100 text-emerald-800' : 'font-semibold bg-slate-200/80 text-slate-600' ?>">
                                    <?= $oRank === 1 ? '#1 Primary' : '#' . $oRank . ' Fallback' ?>
                                </span>
                                <input type="radio" name="provider_priority" value="openrouter" <?= $activePriority === 'openrouter' ? 'checked' : '' ?> class="sr-only">
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Card 3: Parameters (Temperature, Max Output, Autonomous) -->
                <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Generation Controls</h3>
                    
                    <div class="grid grid-cols-2 gap-3 items-center">
                        <!-- Temperature -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-[11px] font-semibold text-slate-700">Temperature</label>
                                <span id="tempVal" class="font-mono text-xs font-bold text-emerald-700"><?= e($aiSettings['temperature'] ?? '0.40') ?></span>
                            </div>
                            <input type="range" name="temperature" min="0.0" max="1.0" step="0.05" value="<?= e($aiSettings['temperature'] ?? '0.40') ?>" oninput="document.getElementById('tempVal').innerText = parseFloat(this.value).toFixed(2)" class="w-full accent-emerald-600 cursor-pointer">
                        </div>

                        <!-- Max Output Tokens -->
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Max Tokens</label>
                            <input type="number" name="max_tokens" min="100" max="2000" step="50" value="<?= (int) ($aiSettings['max_tokens'] ?? 500) ?>" class="w-full text-xs font-mono px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors">
                        </div>
                    </div>

                    <!-- Autonomous Toggle -->
                    <label class="flex items-center justify-between p-2.5 rounded-lg bg-emerald-50/50 border border-emerald-200/80 cursor-pointer">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span class="text-xs font-bold text-slate-900">Enable Autonomous Replies</span>
                        </div>
                        <input type="checkbox" name="auto_reply_enabled" value="1" <?= (int) ($aiSettings['auto_reply_enabled'] ?? 1) === 1 ? 'checked' : '' ?> class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500">
                    </label>
                </div>

            </div>

            <!-- Right Column: System Prompt & AI Rewrite (7 cols) -->
            <div class="lg:col-span-7 bg-white rounded-xl border border-slate-200 p-4 shadow-2xs flex flex-col space-y-3 min-h-[580px]">
                
                <!-- Prompt Toolbar -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">System Prompt</h3>
                        <span id="promptStats" class="text-[10px] font-mono text-slate-400 bg-slate-100 px-2 py-0.5 rounded">0 chars</span>
                    </div>

                    <!-- Presets & AI Rewrite Tools -->
                    <div class="flex flex-wrap items-center gap-1.5">
                        <button type="button" onclick="loadPromptTemplate('personal')" class="px-2 py-1 text-[11px] font-semibold text-slate-600 hover:text-emerald-700 bg-slate-50 hover:bg-emerald-50 border border-slate-200 rounded-md transition-colors cursor-pointer">
                            Personal
                        </button>
                        <button type="button" onclick="loadPromptTemplate('support')" class="px-2 py-1 text-[11px] font-semibold text-slate-600 hover:text-emerald-700 bg-slate-50 hover:bg-emerald-50 border border-slate-200 rounded-md transition-colors cursor-pointer">
                            Support
                        </button>
                        
                        <div class="h-3 w-px bg-slate-200 mx-0.5"></div>

                        <select id="aiRewriteTone" class="text-[11px] font-medium px-2 py-1 bg-white border border-slate-200 rounded-md text-slate-700 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 cursor-pointer">
                            <option value="professional_warm">Professional & Warm</option>
                            <option value="friendly_hinglish">Friendly Hinglish</option>
                            <option value="concise_direct">Concise & Direct</option>
                            <option value="formal_authoritative">Formal Corporate</option>
                        </select>

                        <button type="button" id="btnAiRewrite" onclick="rewritePromptWithAI()" class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 rounded-md shadow-2xs transition-all active:scale-[0.98] cursor-pointer">
                            <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                            <span>AI Rewrite</span>
                        </button>
                    </div>
                </div>

                <!-- Status Toast for AI Rewrite -->
                <div id="aiRewriteStatus" class="hidden p-2 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs flex items-center justify-between">
                    <span id="aiRewriteStatusText"></span>
                    <button type="button" onclick="document.getElementById('aiRewriteStatus').classList.add('hidden')" class="text-emerald-700 font-bold ml-2">✕</button>
                </div>

                <!-- Prompt Textarea -->
                <div class="flex-1 flex flex-col">
                    <textarea id="systemPromptTextarea" name="system_prompt" required class="w-full flex-1 min-h-[380px] text-xs font-mono px-3.5 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 resize-y leading-relaxed bg-slate-50/50 hover:bg-white focus:bg-white transition-colors text-slate-800"><?= e($aiSettings['system_prompt'] ?? '') ?></textarea>
                </div>

                <!-- Submit Action in Panel Footer -->
                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                    <span class="text-[11px] text-slate-400">Settings take effect immediately upon save.</span>
                    <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-lg shadow-xs transition-all active:scale-[0.98] cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Save AI Configuration</span>
                    </button>
                </div>

            </div>

        </div>
    </form>
</div>

<script>
    const personalPromptTemplate = `You are the dedicated, highly capable Personal AI Executive Assistant & Chief of Staff to your Boss.

DIRECTIVES:
1. Executive Role: You work directly and exclusively for your Boss. Address them respectfully as "Boss" or "Sir". Be proactive, loyal, dependable, and efficient.
2. 24/7 Availability: You are active 24/7 around the clock. No closed periods.
3. Capabilities: Assist with daily tasks, schedules, reminders, math, coding, search, research, and WhatsApp communication.
4. Function Calling: Proactively trigger tools when real-time facts or calculations are needed.
5. NO Surveys: NEVER request customer ratings or feedback surveys.
6. NO Escalation: You handle all tasks directly. Never offer to transfer to another agent or human.
7. Style: Structured, bold key points (*bold*), clean bullets, and concise messages.`;

    const supportPromptTemplate = `You are the friendly, professional, and knowledgeable AI Support Assistant for our business on WhatsApp.

DIRECTIVES:
1. Customer Service: Warmly answer customer questions about our services, products, pricing, working hours, and policies.
2. Verified Information: Ground all factual answers in our official knowledge base.
3. Escalation: If specific information is unavailable, politely offer to connect them with our human support team.
4. Style: Courteous, helpful, structured bullets, bold highlights (*bold*), and concise formatting.`;

    function updatePromptStats() {
        const textarea = document.getElementById('systemPromptTextarea');
        const stats = document.getElementById('promptStats');
        if (!textarea || !stats) return;
        const len = textarea.value.length;
        const words = textarea.value.trim() ? textarea.value.trim().split(/\s+/).length : 0;
        stats.innerText = `${len} chars · ${words} words`;
    }

    const promptArea = document.getElementById('systemPromptTextarea');
    if (promptArea) {
        promptArea.addEventListener('input', updatePromptStats);
        updatePromptStats();
    }

    function loadPromptTemplate(mode) {
        const textarea = document.getElementById('systemPromptTextarea');
        if (mode === 'personal') {
            textarea.value = personalPromptTemplate;
        } else {
            textarea.value = supportPromptTemplate;
        }
        updatePromptStats();
    }

    // AI Rewrite
    async function rewritePromptWithAI() {
        const textarea = document.getElementById('systemPromptTextarea');
        const toneSelect = document.getElementById('aiRewriteTone');
        const btn = document.getElementById('btnAiRewrite');
        const statusBox = document.getElementById('aiRewriteStatus');
        const statusText = document.getElementById('aiRewriteStatusText');

        const rawText = (textarea.value || '').trim();
        if (!rawText) {
            alert('Please enter some notes or your current prompt text first.');
            return;
        }

        const originalBtnHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<svg class="w-3 h-3 animate-spin text-emerald-600 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg> <span>Rewriting...</span>`;

        statusBox.classList.remove('hidden');
        statusText.innerText = 'AI is optimizing your system prompt...';

        try {
            const res = await fetch('<?= e(base_url('api/chat/enhance-prompt.php')) ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    raw_text: rawText,
                    tone: toneSelect ? toneSelect.value : 'professional_warm'
                })
            });
            const data = await res.json();
            if (data.ok && data.enhanced_prompt) {
                textarea.value = data.enhanced_prompt;
                updatePromptStats();
                statusText.innerText = 'Prompt rewritten successfully via ' + (data.provider || 'AI Engine') + '.';
                setTimeout(() => { statusBox.classList.add('hidden'); }, 4000);
            } else {
                statusText.innerText = 'Rewrite note: ' + (data.error || 'Failed to rewrite prompt.');
            }
        } catch (e) {
            statusText.innerText = 'Network error: ' + e.message;
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalBtnHtml;
        }
    }

    // Priority Cascade Switcher
    function updatePriorityCascade(selectedPriority) {
        const providers = ['gemini', 'groq', 'openrouter'];
        let priorityOrder = [];
        if (selectedPriority === 'gemini') {
            priorityOrder = ['gemini', 'groq', 'openrouter'];
        } else if (selectedPriority === 'groq') {
            priorityOrder = ['groq', 'gemini', 'openrouter'];
        } else {
            priorityOrder = ['openrouter', 'gemini', 'groq'];
        }

        const providerNames = {
            gemini: 'Google Gemini',
            groq: 'Groq LPU',
            openrouter: 'OpenRouter'
        };

        providers.forEach(p => {
            const card = document.getElementById('cardProvider_' + p);
            const badge = document.getElementById('badgePriority_' + p);
            const rank = priorityOrder.indexOf(p) + 1;

            if (badge) {
                if (rank === 1) {
                    badge.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800';
                    badge.innerText = '#1 Primary';
                } else if (rank === 2) {
                    badge.className = 'px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-200/80 text-slate-600';
                    badge.innerText = '#2 Fallback';
                } else {
                    badge.className = 'px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-200/80 text-slate-600';
                    badge.innerText = '#3 Fallback';
                }
            }

            if (card) {
                if (p === selectedPriority) {
                    card.className = 'flex items-center justify-between p-2.5 rounded-lg border transition-all cursor-pointer bg-emerald-50/50 border-emerald-500 text-slate-900';
                } else {
                    card.className = 'flex items-center justify-between p-2.5 rounded-lg border transition-all cursor-pointer bg-slate-50/60 border-slate-200 text-slate-700 hover:bg-slate-100/60';
                }
            }
        });

        const cascadePill = document.getElementById('cascadeSummaryPill');
        if (cascadePill) {
            cascadePill.innerText = `${providerNames[priorityOrder[0]]} → ${providerNames[priorityOrder[1]]} → ${providerNames[priorityOrder[2]]}`;
        }
    }

    document.querySelectorAll('input[name="provider_priority"]').forEach(radio => {
        radio.addEventListener('change', function() {
            updatePriorityCascade(this.value);
        });
    });

    // Operating Mode Switcher
    function updateModeButtonStyles(activeMode) {
        const btnSupport = document.getElementById('btnModeSupport');
        const btnPersonal = document.getElementById('btnModePersonal');
        const feedbackRow = document.getElementById('feedbackRow');
        const feedbackCheckbox = document.getElementById('feedbackEnabledCheckbox');

        if (activeMode === 'personal') {
            if (btnPersonal) btnPersonal.className = 'flex items-center justify-center gap-1.5 py-2 px-2.5 rounded-md text-xs font-bold transition-all cursor-pointer bg-white text-emerald-800 shadow-2xs border border-emerald-200';
            if (btnSupport) btnSupport.className = 'flex items-center justify-center gap-1.5 py-2 px-2.5 rounded-md text-xs font-semibold transition-all cursor-pointer text-slate-600 hover:text-slate-900';
            if (feedbackCheckbox) {
                feedbackCheckbox.checked = false;
                feedbackCheckbox.disabled = true;
            }
            if (feedbackRow) {
                feedbackRow.classList.add('opacity-40', 'cursor-not-allowed');
            }
        } else {
            if (btnSupport) btnSupport.className = 'flex items-center justify-center gap-1.5 py-2 px-2.5 rounded-md text-xs font-bold transition-all cursor-pointer bg-white text-slate-900 shadow-2xs border border-slate-200/80';
            if (btnPersonal) btnPersonal.className = 'flex items-center justify-center gap-1.5 py-2 px-2.5 rounded-md text-xs font-semibold transition-all cursor-pointer text-slate-600 hover:text-slate-900';
            if (feedbackCheckbox) {
                feedbackCheckbox.disabled = false;
                feedbackCheckbox.checked = true;
            }
            if (feedbackRow) {
                feedbackRow.classList.remove('opacity-40', 'cursor-not-allowed');
            }
        }

        // Instantly toggle Support Bot category & navbar Live Chat in the DOM
        const supportBotCategory = document.getElementById('sidebarCategorySupportBot');
        const navbarLiveChat = document.getElementById('navbarLiveChatLink');
        const escalationBanner = document.getElementById('globalEscalationBanner');
        if (activeMode === 'personal') {
            if (supportBotCategory) supportBotCategory.style.display = 'none';
            if (navbarLiveChat) navbarLiveChat.style.display = 'none';
            if (escalationBanner) escalationBanner.classList.add('hidden');
            if (window.AppConfig) window.AppConfig.agentMode = 'personal';
            document.body.setAttribute('data-agent-mode', 'personal');
        } else {
            if (supportBotCategory) supportBotCategory.style.display = '';
            if (navbarLiveChat) navbarLiveChat.style.display = '';
            if (window.AppConfig) window.AppConfig.agentMode = 'support';
            document.body.setAttribute('data-agent-mode', 'support');
        }
        window.dispatchEvent(new CustomEvent('whatsAgentModeChange', { detail: { mode: activeMode } }));
    }

    async function triggerModeSwitch(targetMode) {
        const ownerName = document.getElementById('ownerNameInput').value || 'Boss';
        const pill = document.getElementById('modeStatusPill');
        const text = document.getElementById('modeStatusText');

        pill.classList.remove('hidden');
        text.innerText = 'Switching operating mode...';

        try {
            const res = await fetch('<?= e(base_url('api/chat/switch-mode.php')) ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    mode: targetMode,
                    owner_name: ownerName,
                    apply_prompt: true
                })
            });
            const data = await res.json();
            if (data.ok) {
                text.innerText = data.message;
                const radio = document.querySelector(`input[name="agent_mode"][value="${targetMode}"]`);
                if (radio) radio.checked = true;
                updateModeButtonStyles(targetMode);

                if (data.system_prompt) {
                    document.getElementById('systemPromptTextarea').value = data.system_prompt;
                    updatePromptStats();
                }
            } else {
                text.innerText = 'Failed: ' + (data.error || 'Unknown error');
            }
        } catch (e) {
            text.innerText = 'Error: ' + e.message;
        }
    }

    document.querySelectorAll('input[name="agent_mode"]').forEach(radio => {
        radio.addEventListener('change', function() {
            triggerModeSwitch(this.value);
        });
    });
</script>
