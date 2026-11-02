<!-- Tools & Integrations View (Minimal, High-Density, Non-Bloated) -->
<div class="space-y-4 mb-8">
    <!-- Header: Title, Stats, and Essential Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200/80">
        <div class="flex items-center gap-3">
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Tools & Integrations</h1>
            <div class="flex items-center gap-1.5">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-mono font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span id="activeToolsBadge"><?= count(array_filter($userTools, fn($t) => !empty($t['is_active']))) ?>/<?= count($userTools) ?> Active</span>
                </span>
                <span class="text-xs text-slate-400 font-mono hidden sm:inline">| <?= count($recentInvocations) ?> logged calls</span>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" id="toolsExportToggleBtn" onclick="toggleExportMode()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 shadow-2xs transition-all active:scale-[0.98] cursor-pointer" title="Select and export tools into a ZIP package">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span id="toolsExportToggleBtnText">Export ZIP</span>
            </button>
            <button type="button" onclick="openToolsImportModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 shadow-2xs transition-all active:scale-[0.98] cursor-pointer" title="Import tools from a ZIP package">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                <span>Import ZIP</span>
            </button>
            <button type="button" onclick="openPresetsModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 shadow-2xs transition-all active:scale-[0.98] cursor-pointer">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                <span>Presets</span>
            </button>
            <button type="button" onclick="openAiImporterModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200/90 shadow-2xs transition-all active:scale-[0.98] cursor-pointer">
                <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                <span>Import cURL / AI</span>
            </button>
            <button type="button" onclick="openCustomToolDrawer()" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-900 hover:bg-slate-800 text-white shadow-2xs transition-all active:scale-[0.98] cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>New Tool</span>
            </button>
        </div>
    </div>

    <!-- Compact Whitelisted Admin Phones Bar (Single-Row) -->
    <div class="bg-white rounded-xl border border-slate-200/90 px-3.5 py-2.5 shadow-2xs">
        <form method="post" action="<?= e(base_url('index.php?page=ai-tools-config-save')) ?>" class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <div class="flex items-center gap-2 shrink-0">
                <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <label for="adminPhonesInput" class="text-xs font-bold text-slate-800 whitespace-nowrap">Admin Phone Numbers</label>
                <span class="text-[10px] font-mono px-1.5 py-0.2 rounded bg-amber-50 text-amber-700 border border-amber-200/80">RBAC</span>
            </div>
            <div class="flex items-center gap-2 flex-1 sm:max-w-xl sm:ml-auto">
                <input type="text" id="adminPhonesInput" name="admin_phones" value="<?= e(implode(', ', $toolsConfig['admin_phones'] ?? [])) ?>" class="w-full text-xs font-mono px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50/60 focus:bg-white focus:ring-2 focus:ring-slate-900/10 focus:border-slate-800 transition-colors" placeholder="+14155552671, +919876543210">
                <button type="submit" class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg transition-colors shadow-2xs active:scale-[0.98] shrink-0 cursor-pointer">
                    Save
                </button>
            </div>
        </form>
    </div>

    <!-- Segmented Main View Switcher (Reduces Page Scrolling) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
        <div class="inline-flex p-1 bg-slate-100 rounded-lg border border-slate-200/80 self-start">
            <button type="button" onclick="switchMainTab('tools')" id="mainTabToolsBtn" class="main-view-tab px-3 py-1 text-xs font-bold rounded-md bg-white text-slate-900 shadow-2xs transition-all cursor-pointer">
                Installed Tools (<?= count($userTools) ?>)
            </button>
            <button type="button" onclick="switchMainTab('invocations')" id="mainTabInvocationsBtn" class="main-view-tab px-3 py-1 text-xs font-bold rounded-md text-slate-600 hover:text-slate-900 transition-all cursor-pointer">
                Execution Log (<?= count($recentInvocations) ?>)
            </button>
        </div>

        <!-- Filter & Search (For Tools View) -->
        <div id="toolsFilterBar" class="flex flex-col sm:flex-row sm:items-center gap-2">
            <div class="flex items-center gap-1 overflow-x-auto pb-1 sm:pb-0" id="toolCategoryTabs">
                <button type="button" onclick="filterTools('all', this)" class="tool-cat-btn px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-900 text-white shadow-2xs transition-all shrink-0 cursor-pointer">
                    All
                </button>
                <button type="button" onclick="filterTools('rest_api', this)" class="tool-cat-btn px-2.5 py-1 rounded-md text-xs font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-all shrink-0 cursor-pointer">
                    REST
                </button>
                <button type="button" onclick="filterTools('shared_env_api', this)" class="tool-cat-btn px-2.5 py-1 rounded-md text-xs font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-all shrink-0 cursor-pointer">
                    Production
                </button>
                <button type="button" onclick="filterTools('webhook', this)" class="tool-cat-btn px-2.5 py-1 rounded-md text-xs font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-all shrink-0 cursor-pointer">
                    Webhook
                </button>
                <button type="button" onclick="filterTools('builtin', this)" class="tool-cat-btn px-2.5 py-1 rounded-md text-xs font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-all shrink-0 cursor-pointer">
                    Built-in
                </button>
            </div>

            <div class="relative w-full sm:w-56">
                <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" id="toolSearchInput" oninput="handleSearchTools(this.value)" placeholder="Search tools..." class="w-full text-xs pl-8 pr-2.5 py-1 border border-slate-200 rounded-lg bg-white focus:ring-2 focus:ring-slate-900/10 focus:border-slate-800 transition-all">
            </div>
        </div>
    </div>

    <!-- VIEW 1: TOOLS GRID -->
    <div id="viewToolsSection">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5" id="toolsCardsGrid">
            <?php foreach ($userTools as $tool): 
                $cat = $tool['category'];
                $paramsCount = count($tool['parameters_schema']['properties'] ?? []);
                $method = $cat === 'rest_api' ? ($tool['execution_config']['method'] ?? 'GET') : ($cat === 'webhook' ? 'POST' : ($cat === 'shared_env_api' ? 'PROD' : 'BUILTIN'));
                $targetUrl = $cat === 'rest_api' ? ($tool['execution_config']['url'] ?? '') : ($cat === 'webhook' ? ($tool['execution_config']['target_url'] ?? '') : ($cat === 'shared_env_api' ? ($tool['execution_config']['base_url'] ?? '') : ($tool['execution_config']['handler'] ?? $tool['tool_slug'])));
            ?>
                <div class="tool-card bg-white rounded-xl border border-slate-200/90 hover:border-slate-300 p-3.5 shadow-2xs hover:shadow-card transition-all flex flex-col justify-between group relative" data-tool-id="<?= (int) $tool['id'] ?>" data-tool-slug="<?= e($tool['tool_slug']) ?>" data-category="<?= e($cat) ?>" data-tool="<?= htmlspecialchars(json_encode($tool, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>" onclick="handleCardExportSelect(this, event)">
                    <div>
                        <!-- Header: Method Badge, Title, Toggle, Export Selection Indicator -->
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <div class="flex items-center gap-1.5 min-w-0 flex-1">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold uppercase shrink-0 <?= $cat === 'rest_api' ? 'bg-sky-50 text-sky-700 border border-sky-200/80' : ($cat === 'shared_env_api' ? 'bg-purple-50 text-purple-700 border border-purple-200/80' : ($cat === 'webhook' ? 'bg-amber-50 text-amber-700 border border-amber-200/80' : 'bg-slate-100 text-slate-700 border border-slate-200')) ?>">
                                    <?= e($method) ?>
                                </span>
                                <h3 class="text-xs font-bold text-slate-900 group-hover:text-indigo-600 transition-colors truncate" title="<?= e($tool['display_name']) ?>">
                                    <?= e($tool['display_name']) ?>
                                </h3>
                            </div>

                            <div class="flex items-center gap-1 shrink-0">
                                <form method="post" action="<?= e(base_url('index.php?page=custom-tool-toggle')) ?>" class="inline tool-toggle-form">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $tool['id'] ?>">
                                    <button type="submit" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border transition-colors cursor-pointer <?= !empty($tool['is_active']) ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border-slate-200 hover:bg-slate-200' ?>" title="<?= !empty($tool['is_active']) ? 'Disable tool' : 'Enable tool' ?>">
                                        <span class="w-1.5 h-1.5 rounded-full <?= !empty($tool['is_active']) ? 'bg-emerald-500' : 'bg-slate-400' ?>"></span>
                                        <span><?= !empty($tool['is_active']) ? 'Active' : 'Off' ?></span>
                                    </button>
                                </form>

                                <!-- Export Selection Indicator (Radio / Checkbox style) -->
                                <div class="export-indicator-wrap hidden" title="Select tool for export">
                                    <div class="w-5 h-5 rounded-full border-2 border-slate-300 bg-white flex items-center justify-center transition-all duration-150 export-radio-circle shadow-2xs group-hover:border-indigo-400">
                                        <svg class="w-3 h-3 text-white hidden export-check-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Slug & Target URL Line -->
                        <div class="flex items-center gap-1.5 mb-2 font-mono text-[10px] text-slate-500 bg-slate-50/80 px-2 py-1 rounded border border-slate-200/60 truncate">
                            <span class="text-slate-800 font-semibold shrink-0"><?= e($tool['tool_slug']) ?></span>
                            <span class="text-slate-300">|</span>
                            <span class="truncate text-slate-600" title="<?= e($targetUrl) ?>"><?= e($targetUrl) ?></span>
                        </div>

                        <!-- Description (Compact 2 Lines Max) -->
                        <p class="text-[11px] text-slate-500 line-clamp-2 leading-relaxed mb-3 min-h-[2rem]">
                            <?= e($tool['description']) ?>
                        </p>

                        <!-- Meta Chips: Parameters & RBAC Roles -->
                        <div class="flex items-center justify-between text-[10px] pb-2 text-slate-400">
                            <span class="font-mono text-slate-600 bg-slate-100/80 px-1.5 py-0.5 rounded">
                                <?= $paramsCount ?> param<?= $paramsCount === 1 ? '' : 's' ?>
                            </span>
                            <div class="flex items-center gap-1">
                                <?php foreach (($tool['rbac_roles'] ?? []) as $r): ?>
                                    <span class="px-1 py-0.2 rounded text-[9px] font-mono uppercase <?= $r === 'admin' ? 'bg-purple-50 text-purple-700' : ($r === 'customer' ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-600') ?>">
                                        <?= e($r) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions: Test, Edit, Delete -->
                    <div class="tool-card-footer pt-2 border-t border-slate-100 flex items-center justify-between gap-1.5 transition-opacity">
                        <button type="button" onclick="handleToolTest(this)" class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold text-slate-700 hover:text-slate-900 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-md transition-colors cursor-pointer active:scale-95">
                            <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Test</span>
                        </button>

                        <div class="flex items-center gap-1">
                            <button type="button" onclick="handleToolEdit(this)" class="inline-flex items-center gap-1 px-2 py-1 text-[11px] font-semibold text-slate-600 hover:text-indigo-600 hover:bg-slate-50 rounded-md transition-colors cursor-pointer">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span>Edit</span>
                            </button>
                            <form method="post" action="<?= e(base_url('index.php?page=custom-tool-delete')) ?>" onsubmit="return confirm('Delete this tool?');" class="inline">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="id" value="<?= (int) $tool['id'] ?>">
                                <button type="submit" class="w-6 h-6 inline-flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-md transition-colors cursor-pointer" title="Delete tool">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (!$userTools): ?>
            <div class="bg-white rounded-xl border border-dashed border-slate-200 p-8 text-center shadow-2xs">
                <div class="w-10 h-10 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center mx-auto mb-2.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h3 class="text-xs font-bold text-slate-900 mb-1">No Tools Configured</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto mb-4">Add a custom REST endpoint, import a ZIP package, or install from presets.</p>
                <div class="flex items-center justify-center gap-2">
                    <button type="button" onclick="openCustomToolDrawer()" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg shadow-2xs transition-all cursor-pointer">
                        + Add Tool
                    </button>
                    <button type="button" onclick="openToolsImportModal()" class="px-3.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200/90 text-xs font-semibold rounded-lg shadow-2xs transition-all cursor-pointer">
                        Import ZIP Package
                    </button>
                    <button type="button" onclick="openPresetsModal()" class="px-3.5 py-1.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-semibold rounded-lg shadow-2xs transition-all cursor-pointer">
                        Browse Presets
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Floating Export Selection Action Bar (Docked at bottom of viewport) -->
    <div id="exportSelectionBar" class="fixed bottom-6 inset-x-0 mx-auto max-w-2xl px-4 z-40 hidden animate-in fade-in slide-in-from-bottom-4 duration-200">
        <div class="bg-slate-900/95 backdrop-blur-md text-white border border-slate-700/80 rounded-2xl shadow-2xl p-3 sm:px-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-indigo-500/20 border border-indigo-400/30 flex items-center justify-center text-indigo-400 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                </div>
                <div>
                    <div class="text-xs font-bold leading-tight flex items-center gap-1.5">
                        <span>Export Mode</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] bg-indigo-500/30 text-indigo-300 font-mono font-bold" id="exportSelectionCountBadge">0 selected</span>
                    </div>
                    <p class="text-[11px] text-slate-400">Click tool cards to select which ones to bundle into the ZIP package.</p>
                </div>
            </div>
            
            <div class="flex items-center gap-2 shrink-0 self-end sm:self-auto">
                <button type="button" onclick="selectAllExportTools()" class="px-2.5 py-1.5 rounded-lg text-xs font-medium text-slate-300 hover:text-white hover:bg-slate-800 transition-colors cursor-pointer">
                    Select All
                </button>
                <button type="button" onclick="deselectAllExportTools()" class="px-2.5 py-1.5 rounded-lg text-xs font-medium text-slate-300 hover:text-white hover:bg-slate-800 transition-colors cursor-pointer">
                    Clear
                </button>
                <button type="button" onclick="exitExportMode()" class="px-2.5 py-1.5 rounded-lg text-xs font-medium text-slate-400 hover:text-slate-200 transition-colors cursor-pointer">
                    Cancel
                </button>
                <button type="button" id="exportSubmitBtn" onclick="submitExportPackage()" disabled class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-bold bg-indigo-600 hover:bg-indigo-500 disabled:bg-slate-800 disabled:text-slate-500 disabled:cursor-not-allowed text-white shadow-md transition-all active:scale-[0.98] cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Download ZIP (<span id="exportSubmitCount">0</span>)</span>
                </button>
            </div>
        </div>
    </div>

    <!-- VIEW 2: EXECUTION LOG TABLE -->
    <div id="viewInvocationsSection" class="hidden">
        <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <h3 class="text-xs font-bold text-slate-900">Recent Executions</h3>
                </div>
                <span class="text-[11px] font-mono text-slate-500">
                    <?= count($recentInvocations) ?> event<?= count($recentInvocations) === 1 ? '' : 's' ?>
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50/80 border-b border-slate-200/70 text-slate-500 font-semibold uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="px-3.5 py-2.5 whitespace-nowrap">Timestamp</th>
                            <th class="px-3.5 py-2.5 whitespace-nowrap">Caller</th>
                            <th class="px-3.5 py-2.5 whitespace-nowrap">Role</th>
                            <th class="px-3.5 py-2.5 whitespace-nowrap">Tool</th>
                            <th class="px-3.5 py-2.5 whitespace-nowrap">Latency</th>
                            <th class="px-3.5 py-2.5 whitespace-nowrap">Status</th>
                            <th class="px-3.5 py-2.5 text-right whitespace-nowrap">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono text-[11px]">
                        <?php foreach ($recentInvocations as $inv): ?>
                            <tr class="hover:bg-slate-50/70 transition-colors cursor-pointer group" onclick="openInvocationDetails(<?= e(json_encode($inv, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)) ?>)">
                                <td class="px-3.5 py-2 text-slate-500 whitespace-nowrap"><?= e($inv['created_at']) ?></td>
                                <td class="px-3.5 py-2 text-slate-900 whitespace-nowrap"><?= e($inv['caller_phone']) ?></td>
                                <td class="px-3.5 py-2 whitespace-nowrap">
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase <?= $inv['caller_role'] === 'admin' ? 'bg-purple-50 text-purple-700 border border-purple-200/80' : ($inv['caller_role'] === 'customer' ? 'bg-blue-50 text-blue-700 border border-blue-200/80' : 'bg-slate-100 text-slate-700 border border-slate-200') ?>">
                                        <?= strtoupper($inv['caller_role']) ?>
                                    </span>
                                </td>
                                <td class="px-3.5 py-2 text-slate-900 font-semibold whitespace-nowrap"><?= e($inv['tool_name']) ?></td>
                                <td class="px-3.5 py-2 whitespace-nowrap">
                                    <span class="<?= (int)$inv['execution_time_ms'] < 500 ? 'text-emerald-700 font-semibold' : ((int)$inv['execution_time_ms'] < 1500 ? 'text-amber-700 font-semibold' : 'text-slate-600') ?>">
                                        <?= (int) $inv['execution_time_ms'] ?>ms
                                    </span>
                                </td>
                                <td class="px-3.5 py-2 whitespace-nowrap">
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase <?= $inv['status'] === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($inv['status'] === 'denied' ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-rose-100 text-rose-800 border border-rose-200') ?>">
                                        <?= strtoupper($inv['status']) ?>
                                    </span>
                                </td>
                                <td class="px-3.5 py-2 text-right whitespace-nowrap">
                                    <span class="text-xs text-indigo-600 group-hover:text-indigo-800 font-medium">Inspect →</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (!$recentInvocations): ?>
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-slate-400 text-xs font-sans">
                                    No executions recorded yet.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================= -->
<!-- CUSTOM TOOL BUILDER DRAWER                               -->
<!-- ========================================================= -->
<div id="customToolDrawer" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-3 sm:p-5 hidden">
    <div class="bg-white rounded-xl max-w-2xl w-full p-5 sm:p-6 shadow-2xl max-h-[92vh] overflow-y-auto custom-scrollbar animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <h3 id="toolDrawerTitle" class="text-sm font-bold text-slate-900">Add Tool</h3>
            <button type="button" onclick="closeCustomToolDrawer()" class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer" aria-label="Close">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form method="post" action="<?= e(base_url('index.php?page=custom-tool-save')) ?>" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="tool_id" id="modalToolId" value="0">

            <!-- Optional Preset Pre-Fill -->
            <div class="flex items-center justify-between gap-2 p-2 bg-slate-50 border border-slate-200/80 rounded-lg">
                <span class="text-xs font-semibold text-slate-700">Load Template:</span>
                <select id="builderPresetSelect" onchange="if(this.value){ loadPresetToDrawer(this.value); this.value=''; }" class="text-xs font-medium px-2 py-1 rounded border border-slate-200 bg-white text-slate-800 cursor-pointer">
                    <option value="">-- Choose Preset... --</option>
                    <?php foreach (($appStorePresets ?? []) as $ap): ?>
                        <option value="<?= e($ap['slug']) ?>"><?= e($ap['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Type Selector -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Type</label>
                <div class="grid grid-cols-3 gap-2">
                    <label class="flex items-center justify-center gap-2 p-2 rounded-lg border border-slate-200 bg-slate-50/50 hover:bg-white cursor-pointer text-xs font-semibold text-slate-800">
                        <input type="radio" name="category" value="rest_api" checked onchange="handleCategorySelection(this.value)" class="w-4 h-4 text-slate-900 border-slate-300 focus:ring-slate-900 shrink-0">
                        <span>REST API</span>
                    </label>
                    <label class="flex items-center justify-center gap-2 p-2 rounded-lg border border-slate-200 bg-slate-50/50 hover:bg-white cursor-pointer text-xs font-semibold text-slate-800">
                        <input type="radio" name="category" value="webhook" onchange="handleCategorySelection(this.value)" class="w-4 h-4 text-slate-900 border-slate-300 focus:ring-slate-900 shrink-0">
                        <span>Webhook</span>
                    </label>
                    <label class="flex items-center justify-center gap-2 p-2 rounded-lg border border-slate-200 bg-slate-50/50 hover:bg-white cursor-pointer text-xs font-semibold text-slate-800">
                        <input type="radio" name="category" value="shared_env_api" onchange="handleCategorySelection(this.value)" class="w-4 h-4 text-slate-900 border-slate-300 focus:ring-slate-900 shrink-0">
                        <span>Production</span>
                    </label>
                </div>
            </div>

            <!-- Identity -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Title *</label>
                    <input type="text" name="display_name" id="modalDisplayName" required oninput="autoGenerateSlug(this.value)" placeholder="e.g. Order Status" class="w-full text-xs px-2.5 py-1.5 border border-slate-200 rounded-lg bg-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Slug (Identifier) *</label>
                    <input type="text" name="tool_slug" id="modalToolSlug" required placeholder="order_status" class="w-full text-xs font-mono px-2.5 py-1.5 border border-slate-200 rounded-lg bg-white">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Description (When AI Should Call) *</label>
                <textarea name="description" id="modalDescription" rows="2" required placeholder="e.g. Fetches shipping status and ETA for order number" class="w-full text-xs px-2.5 py-1.5 border border-slate-200 rounded-lg resize-none bg-white"></textarea>
            </div>

            <!-- Roles & Active -->
            <div class="flex items-center justify-between gap-4 pt-1 text-xs">
                <div class="flex items-center gap-3">
                    <span class="font-bold text-slate-700">Roles:</span>
                    <label class="inline-flex items-center gap-1.5 cursor-pointer">
                        <input type="checkbox" name="rbac_roles[]" value="admin" checked class="w-4 h-4 rounded text-purple-600 border-slate-300 focus:ring-purple-500 shrink-0">
                        <span>Admin</span>
                    </label>
                    <label class="inline-flex items-center gap-1.5 cursor-pointer">
                        <input type="checkbox" name="rbac_roles[]" value="customer" checked class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500 shrink-0">
                        <span>Customer</span>
                    </label>
                    <label class="inline-flex items-center gap-1.5 cursor-pointer">
                        <input type="checkbox" name="rbac_roles[]" value="guest" checked class="w-4 h-4 rounded text-slate-600 border-slate-300 focus:ring-slate-500 shrink-0">
                        <span>Guest</span>
                    </label>
                </div>
                <label class="inline-flex items-center gap-1.5 cursor-pointer">
                    <input type="checkbox" name="is_active" id="modalIsActive" value="1" checked class="w-4 h-4 rounded text-emerald-600 border-slate-300 focus:ring-emerald-500 shrink-0">
                    <span class="font-semibold text-slate-800">Active</span>
                </label>
            </div>

            <!-- Endpoint Configuration -->
            <div class="pt-2 border-t border-slate-100">
                <label class="block text-xs font-bold text-slate-700 mb-2">Endpoint</label>

                <!-- REST API Fields -->
                <div id="fieldsRestApi" class="space-y-2.5 p-3 bg-slate-50/80 rounded-lg border border-slate-200/90 text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-2">
                        <div class="sm:col-span-1">
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Method</label>
                            <select name="rest_method" id="modalRestMethod" class="w-full text-xs px-2 py-1.5 border border-slate-200 rounded-lg bg-white font-mono font-bold">
                                <option value="GET">GET</option>
                                <option value="POST">POST</option>
                                <option value="PUT">PUT</option>
                                <option value="DELETE">DELETE</option>
                            </select>
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Endpoint URL (use {param} placeholders)</label>
                            <input type="text" name="rest_url" id="modalRestUrl" placeholder="https://api.example.com/orders/{order_id}" class="w-full text-xs font-mono px-2.5 py-1.5 border border-slate-200 rounded-lg bg-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Auth Header (optional)</label>
                            <input type="text" name="rest_auth_header" id="modalRestAuth" placeholder="Bearer token..." class="w-full text-xs font-mono px-2.5 py-1.5 border border-slate-200 rounded-lg bg-white">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Response Path Filter (optional)</label>
                            <input type="text" name="rest_response_path" id="modalRestPath" placeholder="data or result.status" class="w-full text-xs font-mono px-2.5 py-1.5 border border-slate-200 rounded-lg bg-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Body Template (for POST/PUT)</label>
                        <textarea name="rest_body_template" id="modalRestBodyTemplate" rows="2" placeholder='{ "order_id": "{order_id}" }' class="w-full text-xs font-mono px-2.5 py-1.5 border border-slate-200 rounded-lg resize-y bg-white"></textarea>
                    </div>
                </div>

                <!-- Webhook Fields -->
                <div id="fieldsWebhook" class="p-3 bg-amber-50/50 rounded-lg border border-amber-200 hidden text-xs">
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Webhook URL (POST)</label>
                    <input type="text" name="webhook_url" id="modalWebhookUrl" placeholder="https://hooks.zapier.com/..." class="w-full text-xs font-mono px-2.5 py-1.5 border border-slate-200 rounded-lg bg-white">
                </div>

                <!-- Production Fields -->
                <div id="fieldsSharedEnv" class="p-3 bg-purple-50/50 rounded-lg border border-purple-200 hidden text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Base URL</label>
                            <input type="text" name="shared_live_url" id="modalSharedLiveUrl" placeholder="https://api.example.com/v1" class="w-full text-xs font-mono px-2.5 py-1.5 border border-slate-200 rounded-lg bg-white">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Bearer Token</label>
                            <input type="password" name="shared_live_token" id="modalSharedLiveToken" placeholder="token..." class="w-full text-xs font-mono px-2.5 py-1.5 border border-slate-200 rounded-lg bg-white">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Parameters -->
            <div class="pt-2 border-t border-slate-100">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-bold text-slate-700">Parameters</label>
                    <button type="button" onclick="addParameterCard()" class="text-xs font-semibold text-slate-900 hover:text-indigo-600 cursor-pointer">
                        + Add Parameter
                    </button>
                </div>
                <div id="parameterCardsContainer" class="space-y-2"></div>
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeCustomToolDrawer()" class="px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-1.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg shadow-2xs transition-all active:scale-[0.98] cursor-pointer">
                    Save Tool
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================= -->
<!-- LIVE TOOL TESTING MODAL                                   -->
<!-- ========================================================= -->
<div id="toolTestModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-3 hidden">
    <div class="bg-white rounded-xl max-w-lg w-full p-5 shadow-2xl animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-2.5 border-b border-slate-100 mb-3">
            <h3 class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span id="testModalToolName">Test Tool</span>
            </h3>
            <button type="button" onclick="closeToolTestModal()" class="w-6 h-6 rounded-md flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer" aria-label="Close">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="space-y-3">
            <div id="testParamsForm" class="space-y-2"></div>

            <button type="button" id="btnExecuteTest" onclick="executeMockToolTest()" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg transition-all shadow-2xs active:scale-[0.99] cursor-pointer">
                Run Test
            </button>

            <div id="testResultContainer" class="hidden mt-2 p-3 bg-slate-950 text-emerald-400 rounded-lg font-mono text-[11px] overflow-x-auto max-h-56 border border-slate-800">
                <pre id="testResultJson">Running...</pre>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================= -->
<!-- EXECUTION INSPECTOR MODAL                                 -->
<!-- ========================================================= -->
<div id="invocationDetailsModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-3 hidden">
    <div class="bg-white rounded-xl max-w-xl w-full p-5 shadow-2xl max-h-[90vh] overflow-y-auto custom-scrollbar animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-start justify-between pb-3 border-b border-slate-100 mb-3">
            <div>
                <div class="flex items-center gap-2">
                    <span id="invModalStatusBadge" class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase font-mono"></span>
                    <span id="invModalToolSlug" class="text-xs font-mono font-bold text-slate-900"></span>
                </div>
            </div>
            <button type="button" onclick="closeInvocationDetails()" class="w-6 h-6 rounded-md flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer" aria-label="Close">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Error Message -->
        <div id="invModalErrorContainer" class="mb-3 p-2.5 bg-rose-50 border border-rose-200 rounded-lg hidden">
            <p id="invModalErrorMessage" class="text-xs font-mono text-rose-700 break-words"></p>
        </div>

        <!-- Chips -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 p-2.5 bg-slate-50 border border-slate-100 rounded-lg mb-3 text-xs">
            <div>
                <span class="text-[10px] text-slate-400 block">Time</span>
                <span id="invModalTime" class="font-mono text-slate-700 text-[11px]"></span>
            </div>
            <div>
                <span class="text-[10px] text-slate-400 block">Latency</span>
                <span id="invModalLatency" class="font-mono text-slate-700 text-[11px]"></span>
            </div>
            <div>
                <span class="text-[10px] text-slate-400 block">Role</span>
                <span id="invModalRole" class="font-bold text-[11px]"></span>
            </div>
            <div>
                <span class="text-[10px] text-slate-400 block">Caller</span>
                <span id="invModalPhone" class="font-mono text-slate-700 text-[11px]"></span>
            </div>
        </div>

        <!-- Inputs -->
        <div class="mb-3">
            <div class="flex items-center justify-between mb-1">
                <label class="text-[11px] font-bold text-slate-700">Inputs</label>
                <button type="button" onclick="copyPreContent('invModalArgsJson', this)" class="text-[10px] text-indigo-600 hover:text-indigo-800 font-medium cursor-pointer">Copy</button>
            </div>
            <pre id="invModalArgsJson" class="text-[11px] font-mono bg-slate-900 text-slate-200 p-2.5 rounded-lg overflow-x-auto max-h-40 custom-scrollbar border border-slate-800"></pre>
        </div>

        <!-- Output -->
        <div class="mb-4">
            <div class="flex items-center justify-between mb-1">
                <label class="text-[11px] font-bold text-slate-700">Output Result</label>
                <button type="button" onclick="copyPreContent('invModalResultJson', this)" class="text-[10px] text-indigo-600 hover:text-indigo-800 font-medium cursor-pointer">Copy</button>
            </div>
            <pre id="invModalResultJson" class="text-[11px] font-mono bg-slate-900 text-slate-200 p-2.5 rounded-lg overflow-x-auto max-h-48 custom-scrollbar border border-slate-800"></pre>
        </div>

        <div class="pt-2 border-t border-slate-100 flex justify-end">
            <button type="button" onclick="closeInvocationDetails()" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors cursor-pointer">
                Close
            </button>
        </div>
    </div>
</div>

<!-- ========================================================= -->
<!-- cURL & AI IMPORTER MODAL                                  -->
<!-- ========================================================= -->
<div id="aiImporterModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-3 hidden">
    <div class="bg-white rounded-xl max-w-2xl w-full p-5 shadow-2xl max-h-[92vh] overflow-y-auto custom-scrollbar animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
            <h3 class="text-sm font-bold text-slate-900">Import Tool from cURL or Schema</h3>
            <button type="button" onclick="closeAiImporterModal()" class="w-6 h-6 rounded-md flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer" aria-label="Close">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Tabs -->
        <div class="flex items-center gap-1.5 border-b border-slate-200/80 pb-2 mb-3">
            <button type="button" onclick="switchImporterTab('curl')" id="tabBtnCurl" class="importer-tab-btn px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-900 text-white shadow-2xs transition-all cursor-pointer">
                cURL
            </button>
            <button type="button" onclick="switchImporterTab('prompt')" id="tabBtnPrompt" class="importer-tab-btn px-2.5 py-1 rounded-md text-xs font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-all cursor-pointer">
                Plain Prompt
            </button>
            <button type="button" onclick="switchImporterTab('docs')" id="tabBtnDocs" class="importer-tab-btn px-2.5 py-1 rounded-md text-xs font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-all cursor-pointer">
                API Docs / JSON
            </button>
            <button type="button" onclick="switchImporterTab('shared')" id="tabBtnShared" class="importer-tab-btn px-2.5 py-1 rounded-md text-xs font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-all cursor-pointer">
                Shared Hosting
            </button>
        </div>

        <!-- TAB 1: cURL -->
        <div id="importerPanelCurl" class="space-y-2">
            <div class="flex items-center justify-between text-[11px]">
                <span class="text-slate-500">Paste cURL:</span>
                <div class="flex items-center gap-1">
                    <span class="text-slate-400">Samples:</span>
                    <button type="button" onclick="fillCurlExample('shiprocket')" class="px-1.5 py-0.5 bg-slate-100 hover:bg-slate-200 rounded text-slate-700 cursor-pointer">Shiprocket</button>
                    <button type="button" onclick="fillCurlExample('weather')" class="px-1.5 py-0.5 bg-slate-100 hover:bg-slate-200 rounded text-slate-700 cursor-pointer">Weather</button>
                </div>
            </div>
            <textarea id="aiImporterCurlInput" rows="4" class="w-full text-xs font-mono px-3 py-2 border border-slate-200 rounded-lg resize-none bg-white" placeholder="curl -X GET &quot;https://api.example.com/orders/{order_id}&quot; -H &quot;Authorization: Bearer token&quot;"></textarea>
        </div>

        <!-- TAB 2: Prompt -->
        <div id="importerPanelPrompt" class="space-y-2 hidden">
            <div class="flex items-center justify-between text-[11px]">
                <span class="text-slate-500">Describe requirement:</span>
                <div class="flex items-center gap-1">
                    <span class="text-slate-400">Samples:</span>
                    <button type="button" onclick="fillPromptExample('delhivery')" class="px-1.5 py-0.5 bg-slate-100 hover:bg-slate-200 rounded text-slate-700 cursor-pointer">Courier</button>
                    <button type="button" onclick="fillPromptExample('razorpay')" class="px-1.5 py-0.5 bg-slate-100 hover:bg-slate-200 rounded text-slate-700 cursor-pointer">Payment</button>
                </div>
            </div>
            <textarea id="aiImporterPromptInput" rows="3" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg resize-none bg-white" placeholder="e.g. Check order status from courier API using tracking number..."></textarea>
        </div>

        <!-- TAB 3: Docs -->
        <div id="importerPanelDocs" class="space-y-2 hidden">
            <span class="text-[11px] text-slate-500">Paste endpoint schema or response sample:</span>
            <textarea id="aiImporterDocsInput" rows="4" class="w-full text-xs font-mono px-3 py-2 border border-slate-200 rounded-lg resize-none bg-white" placeholder="GET /v1/orders/{id}&#10;Authorization: Bearer token&#10;Response: { &quot;id&quot;: &quot;123&quot;, &quot;status&quot;: &quot;shipped&quot; }"></textarea>
        </div>

        <!-- TAB 4: Shared Hosting -->
        <div id="importerPanelShared" class="space-y-2 hidden">
            <div class="flex items-center justify-between text-[11px]">
                <span class="text-slate-500 font-medium">Paste Shared Hosting cURL:</span>
                <div class="flex items-center gap-1">
                    <span class="text-slate-400">Samples:</span>
                    <button type="button" onclick="fillSharedExample('local')" class="px-1.5 py-0.5 bg-slate-100 hover:bg-slate-200 rounded text-slate-700 cursor-pointer">Local API</button>
                    <button type="button" onclick="fillSharedExample('cpanel')" class="px-1.5 py-0.5 bg-slate-100 hover:bg-slate-200 rounded text-slate-700 cursor-pointer">cPanel / Shared Host</button>
                </div>
            </div>
            <textarea id="aiImporterSharedInput" rows="4" class="w-full text-xs font-mono px-3 py-2 border border-slate-200 rounded-lg resize-none bg-white" placeholder="curl -X GET &quot;http://localhost/api/v1/health&quot; -H &quot;Accept: application/json&quot;"></textarea>
            <div class="flex items-center gap-1.5 text-[11px] text-slate-500">
                <svg class="w-3.5 h-3.5 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Full AI synthesis for local &amp; shared hosting APIs. Anti-bot (aes.js) challenges bypassed automatically.</span>
            </div>
        </div>

        <!-- Action Row -->
        <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-end">
            <button type="button" id="btnAiGenerate" onclick="generateToolFromAI()" class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-lg text-xs font-semibold bg-slate-900 hover:bg-slate-800 text-white shadow-2xs active:scale-[0.98] transition-all cursor-pointer">
                <span>Generate Tool Definition</span>
            </button>
        </div>

        <!-- Loading State -->
        <div id="aiImporterLoading" class="py-6 text-center space-y-2 hidden">
            <div class="inline-block animate-spin text-xl text-slate-500">⏳</div>
            <div class="text-xs font-bold text-slate-800" id="aiImporterLoadingText">Parsing definition...</div>
        </div>

        <!-- Result Preview -->
        <div id="aiImporterResult" class="mt-4 border border-slate-200 rounded-xl p-3.5 bg-slate-50/70 hidden space-y-3">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <h4 id="resDisplayName" class="text-xs font-bold text-slate-900"></h4>
                    <div class="text-[10px] font-mono text-slate-500 mt-0.5">slug: <span id="resToolSlug" class="font-semibold text-slate-700"></span></div>
                </div>
                <button type="button" onclick="testGeneratedTool()" id="btnTestGenerated" class="px-2.5 py-1 rounded text-[11px] font-semibold bg-white hover:bg-slate-100 text-slate-800 border border-slate-200 cursor-pointer">
                    Test Call
                </button>
            </div>

            <p id="resDescription" class="text-xs text-slate-600 bg-white p-2.5 rounded-lg border border-slate-200/70"></p>

            <div class="bg-white p-2.5 rounded-lg border border-slate-200/70 space-y-1 text-xs font-mono">
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span id="resMethod" class="px-1.5 py-0.2 rounded font-bold text-[9px] bg-slate-900 text-white">GET</span>
                    <span id="resUrl" class="text-slate-700 break-all text-[11px]"></span>
                </div>
                <div id="resHeadersBox" class="text-[10px] text-slate-500 truncate hidden"></div>
                <div id="resBodyTemplateBox" class="text-[10px] text-slate-700 bg-slate-50 p-2 rounded border border-slate-200 font-mono hidden">
                    <pre id="resBodyTemplatePre" class="whitespace-pre-wrap max-h-28 overflow-y-auto text-[10px] text-slate-800"></pre>
                </div>
            </div>

            <!-- Parameters Table -->
            <div class="bg-white rounded-lg border border-slate-200/70 overflow-hidden">
                <div class="p-2 bg-slate-100/70 border-b border-slate-200/70 text-[10px] font-bold text-slate-700">
                    Parameters (<span id="resParamCount">0</span>)
                </div>
                <table class="w-full text-left text-xs">
                    <tbody id="resParamsTableBody" class="divide-y divide-slate-100 font-mono text-[10px]"></tbody>
                </table>
            </div>

            <!-- Test Output -->
            <div id="importerTestBox" class="bg-slate-950 rounded-lg p-3 text-xs font-mono text-emerald-400 hidden space-y-1">
                <div class="flex items-center justify-between text-[10px] text-slate-400 border-b border-slate-800 pb-1">
                    <span class="font-bold text-white">Test Output:</span>
                    <span id="importerTestMeta" class="text-emerald-400 font-bold"></span>
                </div>
                <pre id="importerTestOutput" class="max-h-36 overflow-y-auto whitespace-pre-wrap text-[10px]"></pre>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200/80">
                <button type="button" onclick="applyGeneratedToolToBuilder()" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-white hover:bg-slate-100 text-slate-800 border border-slate-200 cursor-pointer">
                    Edit in Builder
                </button>
                <button type="button" onclick="saveGeneratedToolDirectly()" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-900 hover:bg-slate-800 text-white shadow-2xs cursor-pointer">
                    Save Tool
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================= -->
<!-- PRESETS MODAL                                             -->
<!-- ========================================================= -->
<div id="presetsModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-3 hidden">
    <div class="bg-white rounded-xl max-w-2xl w-full p-5 shadow-2xl max-h-[90vh] overflow-y-auto custom-scrollbar animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
            <h3 class="text-sm font-bold text-slate-900">Integration Presets</h3>
            <button type="button" onclick="closePresetsModal()" class="w-6 h-6 rounded-md flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer" aria-label="Close">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 mb-3">
            <?php foreach (($appStorePresets ?? []) as $preset): 
                $isInst = isset($installedMap[$preset['slug']]);
            ?>
                <div class="p-3 rounded-lg border border-slate-200 bg-white hover:border-slate-300 transition-all flex flex-col justify-between shadow-2xs">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-bold text-slate-900"><?= e($preset['title']) ?></span>
                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-slate-100 text-slate-700 border border-slate-200 uppercase">
                                <?= e($preset['category_label'] ?? 'API') ?>
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-normal line-clamp-2"><?= e($preset['description']) ?></p>
                    </div>

                    <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between gap-2">
                        <?php if ($isInst): ?>
                            <span class="text-[10px] font-semibold text-emerald-700">✓ Installed</span>
                            <button type="button" onclick="loadPresetToDrawer('<?= e($preset['slug']) ?>')" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium cursor-pointer">
                                Customize →
                            </button>
                        <?php else: ?>
                            <form method="post" action="<?= e(base_url('index.php?page=app-store-install')) ?>" class="inline">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="preset_slug" value="<?= e($preset['slug']) ?>">
                                <input type="hidden" name="return_to" value="index.php?page=tools">
                                <button type="submit" class="px-2.5 py-1 bg-slate-900 hover:bg-slate-800 text-white text-[11px] font-semibold rounded-md shadow-2xs transition-all cursor-pointer">
                                    + Install
                                </button>
                            </form>
                            <button type="button" onclick="loadPresetToDrawer('<?= e($preset['slug']) ?>')" class="text-xs text-slate-600 hover:text-slate-900 font-medium cursor-pointer">
                                Customize →
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="flex justify-end pt-2 border-t border-slate-100">
            <button type="button" onclick="closePresetsModal()" class="px-3 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg cursor-pointer">
                Close
            </button>
        </div>
    </div>
</div>

<!-- ========================================================= -->
<!-- TOOLS IMPORT ZIP PACKAGE MODAL                            -->
<!-- ========================================================= -->
<div id="toolsImportModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 hidden animate-in fade-in duration-150">
    <div class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl border border-slate-200/90 max-h-[92vh] flex flex-col overflow-hidden animate-in zoom-in-95 duration-150">
        <!-- Modal Header -->
        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between gap-3 bg-slate-50/50">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-200/80 text-indigo-600 flex items-center justify-center shadow-2xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 leading-tight">Import Tools Package</h3>
                    <p class="text-[11px] text-slate-500">Restore or import custom tools from a verified ZIP archive.</p>
                </div>
            </div>
            <button type="button" onclick="closeToolsImportModal()" class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer" aria-label="Close modal">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div class="p-5 overflow-y-auto flex-1 custom-scrollbar space-y-4">
            <!-- Security Guardrail Notice -->
            <div class="bg-slate-50 rounded-xl p-3 border border-slate-200/80 flex items-start gap-2.5 text-[11px] text-slate-600">
                <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <div class="leading-relaxed">
                    <span class="font-bold text-slate-800">Sandboxed & Security Verified:</span>
                    <span>Archives are inspected in memory with Zip-Slip defense, anti-Zip-Bomb quotas, SSRF protocol whitelisting (http/https only), and RBAC role normalization.</span>
                </div>
            </div>

            <!-- STAGE 1: Drag-and-drop Upload -->
            <div id="importStageUpload" class="space-y-3">
                <div id="importDropzone" ondragover="handleImportDragOver(event)" ondragleave="handleImportDragLeave(event)" ondrop="handleImportDrop(event)" onclick="document.getElementById('importZipInput').click()" class="border-2 border-dashed border-slate-200 hover:border-indigo-400 bg-slate-50/50 hover:bg-indigo-50/20 rounded-2xl p-8 text-center transition-all cursor-pointer group">
                    <input type="file" id="importZipInput" accept=".zip,application/zip,application/x-zip-compressed" onchange="handleImportFileSelect(this)" class="hidden">
                    <div class="w-12 h-12 rounded-2xl bg-white border border-slate-200 shadow-2xs text-slate-500 group-hover:text-indigo-600 group-hover:scale-105 group-hover:border-indigo-200 flex items-center justify-center mx-auto mb-3 transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    </div>
                    <h4 class="text-xs font-bold text-slate-800 mb-1">Click to browse or drop ZIP package here</h4>
                    <p class="text-[11px] text-slate-400">Accepts standard <span class="font-mono text-slate-600">.zip</span> packages containing exported tools (Max 10MB)</p>
                </div>

                <!-- Inspection Loading State -->
                <div id="importLoadingState" class="hidden py-8 text-center space-y-2">
                    <div class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 animate-spin">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </div>
                    <div class="text-xs font-semibold text-slate-800">Inspecting package & validating schemas...</div>
                    <div class="text-[11px] text-slate-400">Verifying signatures, JSON structures, and collision states</div>
                </div>

                <!-- Error Alert -->
                <div id="importErrorAlert" class="hidden p-3 rounded-xl bg-rose-50 border border-rose-200/90 text-rose-800 text-xs flex items-start gap-2">
                    <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span id="importErrorMessage" class="leading-relaxed"></span>
                </div>
            </div>

            <!-- STAGE 2: Tool Selection Grid (Select which ones to import) -->
            <div id="importStagePreview" class="hidden space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-2 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-800" id="importSummaryBadge"></span>
                        <span class="text-slate-300">|</span>
                        <div class="flex items-center gap-1.5">
                            <button type="button" onclick="selectAllImportTools()" class="text-[11px] text-indigo-600 hover:text-indigo-800 font-semibold cursor-pointer">Select All</button>
                            <span class="text-slate-300">•</span>
                            <button type="button" onclick="deselectAllImportTools()" class="text-[11px] text-slate-500 hover:text-slate-700 font-medium cursor-pointer">Deselect All</button>
                        </div>
                    </div>

                    <label class="inline-flex items-center gap-2 cursor-pointer select-none text-[11px] text-slate-600 font-medium">
                        <input type="checkbox" id="importOverwriteCheckbox" checked class="w-3.5 h-3.5 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500">
                        <span>Overwrite existing tools on slug conflict</span>
                    </label>
                </div>

                <!-- Discovered Tools List -->
                <div id="importToolsList" class="space-y-2 max-h-72 overflow-y-auto custom-scrollbar pr-1"></div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between gap-3">
            <button type="button" id="importResetBtn" onclick="resetImportModalState()" class="hidden px-3 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-200/60 rounded-lg transition-colors cursor-pointer">
                ← Choose Another File
            </button>
            <div class="flex items-center gap-2 ml-auto">
                <button type="button" onclick="closeToolsImportModal()" class="px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-200/60 rounded-lg transition-colors cursor-pointer">
                    Cancel
                </button>
                <button type="button" id="importCommitBtn" onclick="commitSelectedImportTools()" disabled class="hidden inline-flex items-center gap-1.5 px-4 py-1.5 rounded-lg text-xs font-bold bg-indigo-600 hover:bg-indigo-500 disabled:bg-slate-300 disabled:cursor-not-allowed text-white shadow-2xs transition-all active:scale-[0.98] cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Import Selected (<span id="importCommitCount">0</span>)</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const appStorePresetsCatalog = <?= json_encode($appStorePresets ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
    let activeImporterTab = 'curl';
    let currentGeneratedTool = null;
    let currentFilterCategory = 'all';
    let currentSearchQuery = '';

    // Main Tab Switcher: Tools vs. Invocations
    function switchMainTab(tab, updateHistory = true) {
        const toolsSection = document.getElementById('viewToolsSection');
        const invocationsSection = document.getElementById('viewInvocationsSection');
        const filterBar = document.getElementById('toolsFilterBar');
        const tabToolsBtn = document.getElementById('mainTabToolsBtn');
        const tabInvocationsBtn = document.getElementById('mainTabInvocationsBtn');

        if (tab === 'tools') {
            toolsSection.classList.remove('hidden');
            invocationsSection.classList.add('hidden');
            filterBar.classList.remove('hidden');

            tabToolsBtn.className = 'main-view-tab px-3 py-1 text-xs font-bold rounded-md bg-white text-slate-900 shadow-2xs transition-all cursor-pointer';
            tabInvocationsBtn.className = 'main-view-tab px-3 py-1 text-xs font-bold rounded-md text-slate-600 hover:text-slate-900 transition-all cursor-pointer';
        } else {
            toolsSection.classList.add('hidden');
            invocationsSection.classList.remove('hidden');
            filterBar.classList.add('hidden');

            tabInvocationsBtn.className = 'main-view-tab px-3 py-1 text-xs font-bold rounded-md bg-white text-slate-900 shadow-2xs transition-all cursor-pointer';
            tabToolsBtn.className = 'main-view-tab px-3 py-1 text-xs font-bold rounded-md text-slate-600 hover:text-slate-900 transition-all cursor-pointer';
        }

        try {
            localStorage.setItem('tools_active_tab', tab);
            if (updateHistory) {
                const url = new URL(window.location);
                url.searchParams.set('tab', tab);
                window.history.replaceState(null, '', url);
            }
        } catch (_) {}
    }

    // Auto-restore tab on load (checks URL parameter, hash, or localStorage)
    document.addEventListener('DOMContentLoaded', function () {
        try {
            const urlParams = new URLSearchParams(window.location.search);
            const hashTab = window.location.hash ? window.location.hash.replace('#', '').replace('tab=', '') : null;
            const tabParam = urlParams.get('tab') || hashTab || localStorage.getItem('tools_active_tab');
            if (tabParam === 'invocations') {
                switchMainTab('invocations', false);
            } else if (tabParam === 'tools') {
                switchMainTab('tools', false);
            }
        } catch (_) {}
    });

    // Category Filter
    function filterTools(category, buttonEl) {
        currentFilterCategory = category;
        document.querySelectorAll('.tool-cat-btn').forEach(b => {
            b.classList.remove('bg-slate-900', 'text-white', 'shadow-2xs');
            b.classList.add('text-slate-600', 'hover:bg-slate-100');
        });
        if (buttonEl) {
            buttonEl.classList.add('bg-slate-900', 'text-white', 'shadow-2xs');
            buttonEl.classList.remove('text-slate-600', 'hover:bg-slate-100');
        }
        applyToolFilters();
    }

    // Search Tools
    function handleSearchTools(query) {
        currentSearchQuery = (query || '').toLowerCase().trim();
        applyToolFilters();
    }

    function applyToolFilters() {
        const cards = document.querySelectorAll('.tool-card');
        let visibleCount = 0;
        let activeVisibleCount = 0;

        cards.forEach(card => {
            const cardCategory = card.getAttribute('data-category');
            const categoryMatch = currentFilterCategory === 'all' || cardCategory === currentFilterCategory;
            
            let searchMatch = true;
            if (currentSearchQuery) {
                const text = card.textContent.toLowerCase();
                searchMatch = text.includes(currentSearchQuery);
            }

            if (categoryMatch && searchMatch) {
                card.classList.remove('hidden');
                visibleCount++;
                if (card.querySelector('.bg-emerald-50')) {
                    activeVisibleCount++;
                }
            } else {
                card.classList.add('hidden');
            }
        });

        let noResultsEl = document.getElementById('toolsNoSearchResults');
        if (!noResultsEl && cards.length > 0) {
            noResultsEl = document.createElement('div');
            noResultsEl.id = 'toolsNoSearchResults';
            noResultsEl.className = 'col-span-full py-8 text-center text-slate-400 text-xs hidden';
            noResultsEl.innerHTML = '<p class="font-medium text-slate-700">No matching tools</p>';
            const grid = document.getElementById('toolsCardsGrid');
            if (grid) grid.appendChild(noResultsEl);
        }
        if (noResultsEl) {
            noResultsEl.classList.toggle('hidden', visibleCount > 0 || cards.length === 0);
        }

        const badge = document.getElementById('activeToolsBadge');
        if (badge) {
            badge.innerText = `${activeVisibleCount}/${visibleCount} Active`;
        }
    }

    function autoGenerateSlug(title) {
        const slugInput = document.getElementById('modalToolSlug');
        if (slugInput && !slugInput.dataset.manualEdited) {
            slugInput.value = title.toLowerCase().trim()
                .replace(/[^a-z0-9]+/g, '_')
                .replace(/^_+|_+$/g, '');
        }
    }

    document.getElementById('modalToolSlug')?.addEventListener('input', function() {
        this.dataset.manualEdited = 'true';
    });

    function handleCategorySelection(cat) {
        document.getElementById('fieldsRestApi').classList.add('hidden');
        document.getElementById('fieldsWebhook').classList.add('hidden');
        document.getElementById('fieldsSharedEnv').classList.add('hidden');

        if (cat === 'rest_api') {
            document.getElementById('fieldsRestApi').classList.remove('hidden');
        } else if (cat === 'webhook') {
            document.getElementById('fieldsWebhook').classList.remove('hidden');
        } else if (cat === 'shared_env_api') {
            document.getElementById('fieldsSharedEnv').classList.remove('hidden');
        }
    }

    function openCustomToolDrawer() {
        document.getElementById('toolDrawerTitle').innerText = 'Add Tool';
        document.getElementById('modalToolId').value = '0';
        document.getElementById('modalDisplayName').value = '';
        document.getElementById('modalToolSlug').value = '';
        delete document.getElementById('modalToolSlug').dataset.manualEdited;
        document.getElementById('modalDescription').value = '';
        document.getElementById('modalRestUrl').value = '';
        document.getElementById('modalRestAuth').value = '';
        document.getElementById('modalRestPath').value = '';
        document.getElementById('modalRestBodyTemplate').value = '';
        document.getElementById('modalWebhookUrl').value = '';
        document.getElementById('modalSharedLiveUrl').value = '';
        document.getElementById('modalSharedLiveToken').value = '';

        document.querySelector('input[name="category"][value="rest_api"]').checked = true;
        handleCategorySelection('rest_api');

        const container = document.getElementById('parameterCardsContainer');
        container.innerHTML = '';
        addParameterCard();

        document.getElementById('customToolDrawer').classList.remove('hidden');
    }

    function closeCustomToolDrawer() {
        document.getElementById('customToolDrawer').classList.add('hidden');
    }

    function addParameterCard(name = '', type = 'STRING', desc = '', required = true) {
        const container = document.getElementById('parameterCardsContainer');
        const card = document.createElement('div');
        card.className = 'param-row bg-slate-50/80 rounded-lg p-2 border border-slate-200/90 flex flex-col sm:flex-row sm:items-center gap-2 text-xs';
        card.innerHTML = `
            <div class="flex-1">
                <input type="text" name="param_names[]" value="${escapeHtml(name)}" required placeholder="Param Name" class="w-full text-xs font-mono px-2 py-1 border border-slate-200 rounded-md bg-white">
            </div>
            <div class="w-full sm:w-28">
                <select name="param_types[]" class="w-full text-xs font-mono px-2 py-1 border border-slate-200 rounded-md bg-white">
                    <option value="STRING" ${type === 'STRING' ? 'selected' : ''}>STRING</option>
                    <option value="NUMBER" ${type === 'NUMBER' ? 'selected' : ''}>NUMBER</option>
                    <option value="BOOLEAN" ${type === 'BOOLEAN' ? 'selected' : ''}>BOOLEAN</option>
                    <option value="INTEGER" ${type === 'INTEGER' ? 'selected' : ''}>INTEGER</option>
                </select>
            </div>
            <div class="flex-[2]">
                <input type="text" name="param_descriptions[]" value="${escapeHtml(desc)}" required placeholder="Description for AI" class="w-full text-xs px-2 py-1 border border-slate-200 rounded-md bg-white">
            </div>
            <div class="flex items-center justify-between sm:justify-start gap-2">
                <label class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-700 cursor-pointer">
                    <input type="checkbox" name="param_requireds[]" value="1" ${required ? 'checked' : ''} class="rounded text-slate-900">
                    <span>Req</span>
                </label>
                <button type="button" onclick="this.closest('.param-row').remove()" class="w-6 h-6 inline-flex items-center justify-center text-slate-400 hover:text-rose-600 rounded cursor-pointer" title="Delete">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
        `;
        container.appendChild(card);
    }

    function escapeHtml(str) {
        return (str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function handleToolEdit(btn) {
        const card = btn.closest('.tool-card');
        if (!card) return;
        const toolData = JSON.parse(card.getAttribute('data-tool'));
        if (!toolData) return;

        openCustomToolDrawer();
        document.getElementById('toolDrawerTitle').innerText = 'Edit: ' + toolData.display_name;
        document.getElementById('modalToolId').value = toolData.id;
        document.getElementById('modalDisplayName').value = toolData.display_name;
        document.getElementById('modalToolSlug').value = toolData.tool_slug;
        document.getElementById('modalToolSlug').dataset.manualEdited = 'true';
        document.getElementById('modalDescription').value = toolData.description;

        const cat = toolData.category || 'rest_api';
        const radio = document.querySelector(`input[name="category"][value="${cat}"]`);
        if (radio) {
            radio.checked = true;
            handleCategorySelection(cat);
        }

        const exec = toolData.execution_config || {};
        if (cat === 'rest_api') {
            document.getElementById('modalRestMethod').value = exec.method || 'GET';
            document.getElementById('modalRestUrl').value = exec.url || '';
            document.getElementById('modalRestAuth').value = exec.headers?.Authorization || '';
            document.getElementById('modalRestPath').value = exec.response_path || '';
            document.getElementById('modalRestBodyTemplate').value = exec.body_template ? (typeof exec.body_template === 'string' ? exec.body_template : JSON.stringify(exec.body_template, null, 2)) : '';
        } else if (cat === 'webhook') {
            document.getElementById('modalWebhookUrl').value = exec.target_url || '';
        } else if (cat === 'shared_env_api') {
            document.getElementById('modalSharedLiveUrl').value = exec.base_url || '';
        }

        const container = document.getElementById('parameterCardsContainer');
        container.innerHTML = '';
        const props = toolData.parameters_schema?.properties || {};
        const reqs = toolData.parameters_schema?.required || [];
        for (const [pName, pObj] of Object.entries(props)) {
            addParameterCard(pName, (pObj.type || 'STRING').toUpperCase(), pObj.description || '', reqs.includes(pName));
        }
    }

    let activeTestingTool = null;
    function handleToolTest(btn) {
        const card = btn.closest('.tool-card');
        if (!card) return;
        activeTestingTool = JSON.parse(card.getAttribute('data-tool'));
        if (!activeTestingTool) return;

        document.getElementById('testModalToolName').innerText = 'Test: ' + activeTestingTool.display_name;
        const formContainer = document.getElementById('testParamsForm');
        formContainer.innerHTML = '';

        const props = activeTestingTool.parameters_schema?.properties || {};
        for (const [pName, pObj] of Object.entries(props)) {
            const div = document.createElement('div');
            div.innerHTML = `
                <label class="block text-[11px] font-bold text-slate-700 mb-0.5">${escapeHtml(pName)} (${pObj.type || 'string'}):</label>
                <input type="text" data-param="${escapeHtml(pName)}" placeholder="${escapeHtml(pObj.description || '')}" class="test-param-input w-full text-xs px-2.5 py-1.5 border border-slate-200 rounded-lg">
            `;
            formContainer.appendChild(div);
        }

        if (Object.keys(props).length === 0) {
            formContainer.innerHTML = '<p class="text-xs text-slate-400 italic">No parameters required. Click Run Test below.</p>';
        }

        document.getElementById('testResultContainer').classList.add('hidden');
        document.getElementById('toolTestModal').classList.remove('hidden');
    }

    function closeToolTestModal() {
        document.getElementById('toolTestModal').classList.add('hidden');
    }

    async function executeMockToolTest() {
        if (!activeTestingTool) return;
        const resBox = document.getElementById('testResultContainer');
        const resPre = document.getElementById('testResultJson');
        resBox.classList.remove('hidden');
        resPre.innerText = 'Executing call...';

        const args = {};
        document.querySelectorAll('.test-param-input').forEach(inp => {
            args[inp.dataset.param] = inp.value;
        });

        try {
            const res = await fetch('<?= e(base_url('api/tools/test-tool.php')) ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    tool_slug: activeTestingTool.tool_slug,
                    category: activeTestingTool.category || 'rest_api',
                    execution_config: activeTestingTool.execution_config || {},
                    args: args
                }),
                credentials: 'same-origin'
            });
            const data = await res.json();
            resPre.innerText = JSON.stringify(data.data || data, null, 2);
        } catch (e) {
            resPre.innerText = 'Test execution finished: ' + e.message;
        }
    }

    function openInvocationDetails(inv) {
        if (!inv) return;
        document.getElementById('invModalToolSlug').innerText = inv.tool_name || 'unknown_tool';
        document.getElementById('invModalTime').innerText = inv.created_at || 'N/A';
        document.getElementById('invModalLatency').innerText = (inv.execution_time_ms || 0) + 'ms';
        document.getElementById('invModalPhone').innerText = inv.caller_phone || 'unknown';
        
        const roleEl = document.getElementById('invModalRole');
        roleEl.innerText = (inv.caller_role || 'guest').toUpperCase();
        roleEl.className = 'font-bold text-[11px] ' + (inv.caller_role === 'admin' ? 'text-purple-700' : (inv.caller_role === 'customer' ? 'text-blue-700' : 'text-slate-600'));

        const statusEl = document.getElementById('invModalStatusBadge');
        const isSuccess = inv.status === 'success';
        const isDenied = inv.status === 'denied';
        statusEl.innerText = (inv.status || 'UNKNOWN').toUpperCase();
        statusEl.className = 'px-1.5 py-0.2 rounded text-[9px] font-bold uppercase font-mono ' + 
            (isSuccess ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 
            (isDenied ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-rose-100 text-rose-800 border border-rose-200'));

        const errContainer = document.getElementById('invModalErrorContainer');
        const errMsgEl = document.getElementById('invModalErrorMessage');
        if (inv.error_message || !isSuccess) {
            let msg = inv.error_message;
            if (!msg && inv.tool_result) {
                try {
                    const parsed = JSON.parse(inv.tool_result);
                    msg = parsed.error || parsed.details?.message || parsed.message;
                } catch (_) {}
            }
            errMsgEl.innerText = msg || 'Execution failure recorded.';
            errContainer.classList.remove('hidden');
        } else {
            errContainer.classList.add('hidden');
        }

        let argsFormatted = '';
        try {
            const parsedArgs = typeof inv.tool_arguments === 'string' ? JSON.parse(inv.tool_arguments) : (inv.tool_arguments || {});
            argsFormatted = JSON.stringify(parsedArgs, null, 2);
        } catch (_) {
            argsFormatted = String(inv.tool_arguments || '{}');
        }
        document.getElementById('invModalArgsJson').innerText = argsFormatted;

        let resultFormatted = '';
        try {
            const parsedRes = typeof inv.tool_result === 'string' ? JSON.parse(inv.tool_result) : (inv.tool_result || {});
            resultFormatted = JSON.stringify(parsedRes, null, 2);
        } catch (_) {
            resultFormatted = String(inv.tool_result || '{}');
        }
        document.getElementById('invModalResultJson').innerText = resultFormatted;

        document.getElementById('invocationDetailsModal').classList.remove('hidden');
    }

    function closeInvocationDetails() {
        document.getElementById('invocationDetailsModal').classList.add('hidden');
    }

    function copyPreContent(preId, btn) {
        const text = document.getElementById(preId)?.innerText || '';
        if (!text) return;
        navigator.clipboard.writeText(text).then(() => {
            const orig = btn.innerText;
            btn.innerText = 'Copied';
            setTimeout(() => { btn.innerText = orig; }, 1500);
        });
    }

    function loadPresetToDrawer(slug) {
        const preset = (appStorePresetsCatalog || []).find(p => p.slug === slug);
        if (!preset) return;

        closePresetsModal();
        openCustomToolDrawer();

        document.getElementById('toolDrawerTitle').innerText = 'Preset: ' + (preset.title || preset.slug);
        document.getElementById('modalToolId').value = '0';
        document.getElementById('modalDisplayName').value = preset.title || '';
        document.getElementById('modalToolSlug').value = preset.slug || '';
        document.getElementById('modalToolSlug').dataset.manualEdited = 'true';
        document.getElementById('modalDescription').value = preset.description || '';

        const cat = preset.category || 'rest_api';
        const radio = document.querySelector(`input[name="category"][value="${cat}"]`);
        if (radio) radio.checked = true;
        handleCategorySelection(cat);

        const exec = preset.execution_config || {};
        if (cat === 'rest_api') {
            document.getElementById('modalRestMethod').value = exec.method || 'GET';
            document.getElementById('modalRestUrl').value = exec.url || '';
            document.getElementById('modalRestAuth').value = exec.headers?.Authorization || '';
            document.getElementById('modalRestPath').value = exec.response_path || '';
            document.getElementById('modalRestBodyTemplate').value = exec.body_template ? (typeof exec.body_template === 'string' ? exec.body_template : JSON.stringify(exec.body_template, null, 2)) : '';
        } else if (cat === 'webhook') {
            document.getElementById('modalWebhookUrl').value = exec.target_url || '';
        } else if (cat === 'shared_env_api') {
            document.getElementById('modalSharedLiveUrl').value = exec.base_url || '';
            document.getElementById('modalSharedLiveToken').value = exec.bearer_token || '';
        }

        const roles = Array.isArray(preset.rbac_roles) ? preset.rbac_roles : ['guest', 'customer', 'admin'];
        ['guest', 'customer', 'admin'].forEach(r => {
            const cb = document.querySelector(`input[name="rbac_roles[]"][value="${r}"]`);
            if (cb) cb.checked = roles.includes(r);
        });

        const container = document.getElementById('parameterCardsContainer');
        container.innerHTML = '';
        const params = Array.isArray(preset.parameters) ? preset.parameters : [];
        if (params.length > 0) {
            params.forEach(p => {
                addParameterCard(p.name || '', p.type || 'STRING', p.description || '', !!p.required);
            });
        } else {
            addParameterCard();
        }
    }

    function openPresetsModal() {
        document.getElementById('presetsModal').classList.remove('hidden');
    }

    function closePresetsModal() {
        document.getElementById('presetsModal').classList.add('hidden');
    }

    function openAiImporterModal() {
        document.getElementById('aiImporterModal').classList.remove('hidden');
        switchImporterTab('curl');
        document.getElementById('aiImporterResult').classList.add('hidden');
        document.getElementById('aiImporterLoading').classList.add('hidden');
    }

    function closeAiImporterModal() {
        document.getElementById('aiImporterModal').classList.add('hidden');
    }

    function switchImporterTab(tab) {
        activeImporterTab = tab;
        ['curl', 'prompt', 'docs', 'shared'].forEach(t => {
            const btn = document.getElementById('tabBtn' + t.charAt(0).toUpperCase() + t.slice(1));
            const panel = document.getElementById('importerPanel' + t.charAt(0).toUpperCase() + t.slice(1));
            if (btn) {
                if (t === tab) {
                    btn.className = 'importer-tab-btn px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-900 text-white shadow-2xs transition-all cursor-pointer';
                } else {
                    btn.className = 'importer-tab-btn px-2.5 py-1 rounded-md text-xs font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-all cursor-pointer';
                }
            }
            if (panel) {
                panel.classList.toggle('hidden', t !== tab);
            }
        });
    }

    function fillSharedExample(type) {
        const input = document.getElementById('aiImporterSharedInput');
        if (type === 'local') {
            input.value = `curl -X GET "http://localhost/api/v1/health" \\\n  -H "Accept: application/json"`;
        } else if (type === 'cpanel') {
            input.value = `curl -X GET "https://api.yourdomain.com/api/v1/health" \\\n  -H "Accept: application/json"`;
        }
    }

    function fillCurlExample(type) {
        const input = document.getElementById('aiImporterCurlInput');
        if (type === 'shiprocket') {
            input.value = `curl -X GET "https://apiv2.shiprocket.in/v1/external/courier/track/awb?awb_code=9876543210" \\\n  -H "Authorization: Bearer YOUR_TOKEN" \\\n  -H "Accept: application/json"`;
        } else if (type === 'weather') {
            input.value = `curl -X GET "https://api.open-meteo.com/v1/forecast?latitude=25.59&longitude=85.14&current=temperature_2m" \\\n  -H "Accept: application/json"`;
        }
    }

    function fillPromptExample(type) {
        const input = document.getElementById('aiImporterPromptInput');
        if (type === 'delhivery') {
            input.value = 'Track courier package with Delhivery API using waybill number';
        } else if (type === 'razorpay') {
            input.value = 'Check Razorpay payment status using payment_id';
        }
    }

    async function generateToolFromAI() {
        const btn = document.getElementById('btnAiGenerate');
        const loading = document.getElementById('aiImporterLoading');
        const resultBox = document.getElementById('aiImporterResult');
        const loadingText = document.getElementById('aiImporterLoadingText');

        let rawContent = '';
        let inputType = activeImporterTab;

        if (activeImporterTab === 'curl') {
            rawContent = document.getElementById('aiImporterCurlInput').value.trim();
        } else if (activeImporterTab === 'shared') {
            rawContent = document.getElementById('aiImporterSharedInput').value.trim();
        } else if (activeImporterTab === 'prompt') {
            rawContent = document.getElementById('aiImporterPromptInput').value.trim();
        } else {
            rawContent = document.getElementById('aiImporterDocsInput').value.trim();
        }

        if (!rawContent) {
            alert('Please enter input content first.');
            return;
        }

        btn.disabled = true;
        btn.classList.add('opacity-50');
        loading.classList.remove('hidden');
        resultBox.classList.add('hidden');
        document.getElementById('importerTestBox').classList.add('hidden');
        loadingText.textContent = 'Parsing & generating...';

        try {
            const res = await fetch('<?= e(base_url('api/tools/ai-generate-tool.php')) ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ raw_content: rawContent, input_type: inputType }),
                credentials: 'same-origin'
            });

            const data = await res.json();
            if (!res.ok || !data.ok || !data.tool) {
                throw new Error(data.error || 'Failed to generate tool definition.');
            }

            currentGeneratedTool = data.tool;
            renderGeneratedPreview(data.tool, data.source || 'AI Copilot');
            resultBox.classList.remove('hidden');
            resultBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } catch (err) {
            alert('Error: ' + err.message);
        } finally {
            btn.disabled = false;
            btn.classList.remove('opacity-50');
            loading.classList.add('hidden');
        }
    }

    function renderGeneratedPreview(tool, source) {
        document.getElementById('resDisplayName').textContent = tool.display_name || 'Custom Tool';
        document.getElementById('resToolSlug').textContent = tool.tool_slug || 'custom_tool';
        document.getElementById('resDescription').textContent = tool.description || '';

        const exec = tool.execution_config || {};
        document.getElementById('resMethod').textContent = (exec.method || 'GET').toUpperCase();
        document.getElementById('resUrl').textContent = exec.url || exec.base_url || exec.target_url || '';

        const headers = exec.headers || {};
        const headersStr = Object.entries(headers).map(([k, v]) => `${k}: ${v}`).join(' | ');
        const hBox = document.getElementById('resHeadersBox');
        if (headersStr) {
            hBox.textContent = 'Headers: ' + headersStr;
            hBox.classList.remove('hidden');
        } else {
            hBox.classList.add('hidden');
        }

        const bBox = document.getElementById('resBodyTemplateBox');
        if (exec.body_template) {
            bBox.classList.remove('hidden');
            document.getElementById('resBodyTemplatePre').textContent = typeof exec.body_template === 'string'
                ? exec.body_template
                : JSON.stringify(exec.body_template, null, 2);
        } else {
            bBox.classList.add('hidden');
        }

        const params = Array.isArray(tool.parameters) ? tool.parameters : [];
        document.getElementById('resParamCount').textContent = params.length;
        const tbody = document.getElementById('resParamsTableBody');
        tbody.innerHTML = '';

        if (params.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" class="px-2 py-2 text-center text-slate-400 font-sans">No required parameters</td></tr>`;
        } else {
            params.forEach(p => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="px-2 py-1.5 font-bold text-slate-800">${escapeHtml(p.name || '')}</td>
                    <td class="px-2 py-1.5 text-indigo-700">${escapeHtml(p.type || 'STRING')}</td>
                    <td class="px-2 py-1.5 font-sans text-slate-600">${escapeHtml(p.description || '')}</td>
                    <td class="px-2 py-1.5 text-center">${p.required ? '<span class="text-emerald-600 font-bold">YES</span>' : '<span class="text-slate-400">NO</span>'}</td>
                `;
                tbody.appendChild(tr);
            });
        }
    }

    async function testGeneratedTool() {
        if (!currentGeneratedTool) return;
        const btn = document.getElementById('btnTestGenerated');
        const testBox = document.getElementById('importerTestBox');
        const testMeta = document.getElementById('importerTestMeta');
        const testOutput = document.getElementById('importerTestOutput');

        btn.disabled = true;
        btn.innerText = 'Testing...';
        testBox.classList.remove('hidden');
        testMeta.textContent = 'Calling server...';
        testOutput.textContent = 'Running test...';

        const sampleArgs = {};
        const params = Array.isArray(currentGeneratedTool.parameters) ? currentGeneratedTool.parameters : [];
        params.forEach(p => {
            const k = (p.name || '').toLowerCase();
            if (k.includes('lat')) sampleArgs[p.name] = 25.5941;
            else if (k.includes('lon')) sampleArgs[p.name] = 85.1376;
            else if (k.includes('city') || k === 'q') sampleArgs[p.name] = 'Patna';
            else if (k.includes('order')) sampleArgs[p.name] = 'ORD-98765';
            else if (k.includes('waybill') || k.includes('awb')) sampleArgs[p.name] = '9876543210';
            else sampleArgs[p.name] = 'test_value';
        });

        const startTime = performance.now();
        try {
            const res = await fetch('<?= e(base_url('api/tools/test-tool.php')) ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    category: currentGeneratedTool.category || 'rest_api',
                    execution_config: currentGeneratedTool.execution_config || {},
                    args: sampleArgs
                }),
                credentials: 'same-origin'
            });

            const data = await res.json();
            const latency = Math.round(performance.now() - startTime);

            if (data.ok) {
                testMeta.className = 'text-emerald-400 font-bold';
                testMeta.textContent = `HTTP ${data.status || 200} OK (${data.latency_ms || latency}ms)`;
            } else {
                testMeta.className = 'text-rose-400 font-bold';
                testMeta.textContent = `HTTP ${data.status || 400} Error (${data.latency_ms || latency}ms)`;
            }

            testOutput.textContent = JSON.stringify(data.data || data, null, 2);
        } catch (err) {
            testMeta.className = 'text-rose-400 font-bold';
            testMeta.textContent = 'Network Error';
            testOutput.textContent = 'Error: ' + err.message;
        } finally {
            btn.disabled = false;
            btn.innerText = 'Test Call';
        }
    }

    function applyGeneratedToolToBuilder() {
        if (!currentGeneratedTool) return;
        closeAiImporterModal();
        openCustomToolDrawer();

        document.getElementById('toolDrawerTitle').innerText = 'Edit: ' + (currentGeneratedTool.display_name || 'Custom Tool');
        document.getElementById('modalToolId').value = '0';
        document.getElementById('modalDisplayName').value = currentGeneratedTool.display_name || '';
        document.getElementById('modalToolSlug').value = currentGeneratedTool.tool_slug || '';
        document.getElementById('modalToolSlug').dataset.manualEdited = 'true';
        document.getElementById('modalDescription').value = currentGeneratedTool.description || '';

        const cat = currentGeneratedTool.category || 'rest_api';
        const radio = document.querySelector(`input[name="category"][value="${cat}"]`);
        if (radio) radio.checked = true;
        handleCategorySelection(cat);

        const exec = currentGeneratedTool.execution_config || {};
        if (cat === 'rest_api') {
            document.getElementById('modalRestMethod').value = exec.method || 'GET';
            document.getElementById('modalRestUrl').value = exec.url || '';
            document.getElementById('modalRestAuth').value = exec.headers?.Authorization || '';
            document.getElementById('modalRestPath').value = exec.response_path || '';
            if (exec.body_template) {
                document.getElementById('modalRestBodyTemplate').value = typeof exec.body_template === 'string'
                    ? exec.body_template
                    : JSON.stringify(exec.body_template, null, 2);
            } else {
                document.getElementById('modalRestBodyTemplate').value = '';
            }
        } else if (cat === 'shared_env_api') {
            document.getElementById('modalSharedLiveUrl').value = exec.base_url || '';
            document.getElementById('modalSharedLiveToken').value = exec.bearer_token || '';
        } else if (cat === 'webhook') {
            document.getElementById('modalWebhookUrl').value = exec.target_url || '';
        }

        const container = document.getElementById('parameterCardsContainer');
        container.innerHTML = '';
        const params = Array.isArray(currentGeneratedTool.parameters) ? currentGeneratedTool.parameters : [];
        if (params.length > 0) {
            params.forEach(p => {
                addParameterCard(p.name || '', p.type || 'STRING', p.description || '', !!p.required);
            });
        } else {
            addParameterCard();
        }
    }

    function saveGeneratedToolDirectly() {
        if (!currentGeneratedTool) return;
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= e(base_url('index.php?page=custom-tool-save')) ?>';

        const addField = (name, val) => {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = name;
            inp.value = val;
            form.appendChild(inp);
        };

        addField('csrf_token', '<?= e(csrf_token()) ?>');
        addField('tool_id', '0');
        addField('tool_slug', currentGeneratedTool.tool_slug || '');
        addField('display_name', currentGeneratedTool.display_name || '');
        addField('category', currentGeneratedTool.category || 'rest_api');
        addField('description', currentGeneratedTool.description || '');
        addField('is_active', '1');

        const exec = currentGeneratedTool.execution_config || {};
        if (currentGeneratedTool.category === 'rest_api') {
            addField('rest_method', exec.method || 'GET');
            addField('rest_url', exec.url || '');
            if (exec.headers?.Authorization) {
                addField('rest_auth_header', exec.headers.Authorization);
            }
            if (exec.headers) {
                addField('headers_json', typeof exec.headers === 'string' ? exec.headers : JSON.stringify(exec.headers));
            }
            if (exec.body_template) {
                addField('body_template', typeof exec.body_template === 'string' ? exec.body_template : JSON.stringify(exec.body_template));
            }
            addField('rest_response_path', exec.response_path || '');
        } else if (currentGeneratedTool.category === 'shared_env_api') {
            addField('shared_live_url', exec.base_url || '');
            addField('shared_live_token', exec.bearer_token || '');
        } else if (currentGeneratedTool.category === 'webhook') {
            addField('webhook_url', exec.target_url || '');
        }

        (currentGeneratedTool.rbac_roles || ['guest', 'customer', 'admin']).forEach(r => {
            addField('rbac_roles[]', r);
        });

        const params = Array.isArray(currentGeneratedTool.parameters) ? currentGeneratedTool.parameters : [];
        params.forEach(p => {
            addField('param_names[]', p.name || '');
            addField('param_types[]', p.type || 'STRING');
            addField('param_descriptions[]', p.description || '');
            if (p.required) {
                addField('param_requireds[]', '1');
            }
        });

        document.body.appendChild(form);
        form.submit();
    }

    // =========================================================
    // TOOLS ZIP EXPORT LOGIC (RADIO / CHECKBOX SELECTION MODE)
    // =========================================================
    const toolsCsrfToken = '<?= e(csrf_token()) ?>';
    let isExportMode = false;
    const selectedExportToolIds = new Set();

    function toggleExportMode() {
        if (!isExportMode) {
            enterExportMode();
        } else {
            exitExportMode();
        }
    }

    function enterExportMode() {
        isExportMode = true;
        const toggleBtn = document.getElementById('toolsExportToggleBtn');
        const toggleBtnText = document.getElementById('toolsExportToggleBtnText');
        const selectionBar = document.getElementById('exportSelectionBar');
        const cardsGrid = document.getElementById('toolsCardsGrid');

        if (toggleBtn) {
            toggleBtn.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-50 border border-indigo-300 text-indigo-700 shadow-2xs transition-all cursor-pointer';
        }
        if (toggleBtnText) toggleBtnText.textContent = 'Cancel Export';
        if (selectionBar) selectionBar.classList.remove('hidden');

        // Reveal selection indicators on all tool cards
        document.querySelectorAll('.export-indicator-wrap').forEach(el => el.classList.remove('hidden'));
        document.querySelectorAll('.tool-toggle-form').forEach(el => el.classList.add('hidden'));

        // Highlight cards grid
        if (cardsGrid) {
            cardsGrid.classList.add('export-mode-active');
        }
        document.querySelectorAll('.tool-card').forEach(card => {
            card.classList.add('cursor-pointer', 'select-none');
            const footer = card.querySelector('.tool-card-footer');
            if (footer) footer.style.pointerEvents = 'none';
        });

        updateExportUI();
    }

    function exitExportMode() {
        isExportMode = false;
        const toggleBtn = document.getElementById('toolsExportToggleBtn');
        const toggleBtnText = document.getElementById('toolsExportToggleBtnText');
        const selectionBar = document.getElementById('exportSelectionBar');
        const cardsGrid = document.getElementById('toolsCardsGrid');

        if (toggleBtn) {
            toggleBtn.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 shadow-2xs transition-all active:scale-[0.98] cursor-pointer';
        }
        if (toggleBtnText) toggleBtnText.textContent = 'Export ZIP';
        if (selectionBar) selectionBar.classList.add('hidden');

        // Hide indicators and restore cards
        document.querySelectorAll('.export-indicator-wrap').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.tool-toggle-form').forEach(el => el.classList.remove('hidden'));

        if (cardsGrid) {
            cardsGrid.classList.remove('export-mode-active');
        }
        document.querySelectorAll('.tool-card').forEach(card => {
            card.classList.remove('cursor-pointer', 'select-none', 'ring-2', 'ring-indigo-500', 'border-indigo-400', 'bg-indigo-50/20');
            const circle = card.querySelector('.export-radio-circle');
            if (circle) circle.className = 'w-5 h-5 rounded-full border-2 border-slate-300 bg-white flex items-center justify-center transition-all duration-150 export-radio-circle shadow-2xs group-hover:border-indigo-400';
            const check = card.querySelector('.export-check-svg');
            if (check) check.classList.add('hidden');
            const footer = card.querySelector('.tool-card-footer');
            if (footer) footer.style.pointerEvents = '';
        });

        selectedExportToolIds.clear();
        updateExportUI();
    }

    function handleCardExportSelect(cardEl, event) {
        if (!isExportMode) return;

        event.preventDefault();
        event.stopPropagation();

        const toolId = parseInt(cardEl.dataset.toolId, 10);
        if (!toolId) return;

        if (selectedExportToolIds.has(toolId)) {
            selectedExportToolIds.delete(toolId);
            cardEl.classList.remove('ring-2', 'ring-indigo-500', 'border-indigo-400', 'bg-indigo-50/20');
            const circle = cardEl.querySelector('.export-radio-circle');
            if (circle) circle.className = 'w-5 h-5 rounded-full border-2 border-slate-300 bg-white flex items-center justify-center transition-all duration-150 export-radio-circle shadow-2xs group-hover:border-indigo-400';
            const check = cardEl.querySelector('.export-check-svg');
            if (check) check.classList.add('hidden');
        } else {
            selectedExportToolIds.add(toolId);
            cardEl.classList.add('ring-2', 'ring-indigo-500', 'border-indigo-400', 'bg-indigo-50/20');
            const circle = cardEl.querySelector('.export-radio-circle');
            if (circle) circle.className = 'w-5 h-5 rounded-full border-2 border-indigo-600 bg-indigo-600 flex items-center justify-center transition-all duration-150 export-radio-circle shadow-2xs';
            const check = cardEl.querySelector('.export-check-svg');
            if (check) check.classList.remove('hidden');
        }

        updateExportUI();
    }

    function selectAllExportTools() {
        document.querySelectorAll('.tool-card').forEach(card => {
            const toolId = parseInt(card.dataset.toolId, 10);
            if (toolId) {
                selectedExportToolIds.add(toolId);
                card.classList.add('ring-2', 'ring-indigo-500', 'border-indigo-400', 'bg-indigo-50/20');
                const circle = card.querySelector('.export-radio-circle');
                if (circle) circle.className = 'w-5 h-5 rounded-full border-2 border-indigo-600 bg-indigo-600 flex items-center justify-center transition-all duration-150 export-radio-circle shadow-2xs';
                const check = card.querySelector('.export-check-svg');
                if (check) check.classList.remove('hidden');
            }
        });
        updateExportUI();
    }

    function deselectAllExportTools() {
        selectedExportToolIds.clear();
        document.querySelectorAll('.tool-card').forEach(card => {
            card.classList.remove('ring-2', 'ring-indigo-500', 'border-indigo-400', 'bg-indigo-50/20');
            const circle = card.querySelector('.export-radio-circle');
            if (circle) circle.className = 'w-5 h-5 rounded-full border-2 border-slate-300 bg-white flex items-center justify-center transition-all duration-150 export-radio-circle shadow-2xs group-hover:border-indigo-400';
            const check = card.querySelector('.export-check-svg');
            if (check) check.classList.add('hidden');
        });
        updateExportUI();
    }

    function updateExportUI() {
        const count = selectedExportToolIds.size;
        const countBadge = document.getElementById('exportSelectionCountBadge');
        const submitCount = document.getElementById('exportSubmitCount');
        const submitBtn = document.getElementById('exportSubmitBtn');

        if (countBadge) countBadge.textContent = `${count} selected`;
        if (submitCount) submitCount.textContent = count;
        if (submitBtn) submitBtn.disabled = (count === 0);
    }

    function submitExportPackage() {
        if (selectedExportToolIds.size === 0) return;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= e(base_url('index.php?page=custom-tools-export')) ?>';

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = toolsCsrfToken;
        form.appendChild(csrfInput);

        selectedExportToolIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'tool_ids[]';
            input.value = id;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
        setTimeout(() => {
            document.body.removeChild(form);
            exitExportMode();
        }, 300);
    }

    // =========================================================
    // TOOLS ZIP IMPORT MODAL & STAGING LOGIC
    // =========================================================
    let importStagingToken = null;
    let importDiscoveredTools = [];
    let selectedImportSlugs = new Set();

    function openToolsImportModal() {
        const modal = document.getElementById('toolsImportModal');
        if (!modal) return;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        resetImportModalState();
    }

    function closeToolsImportModal() {
        const modal = document.getElementById('toolsImportModal');
        if (!modal) return;
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        resetImportModalState();
    }

    function resetImportModalState() {
        importStagingToken = null;
        importDiscoveredTools = [];
        selectedImportSlugs.clear();

        const stageUpload = document.getElementById('importStageUpload');
        const dropzone = document.getElementById('importDropzone');
        const loadingState = document.getElementById('importLoadingState');
        const errorAlert = document.getElementById('importErrorAlert');
        const stagePreview = document.getElementById('importStagePreview');
        const resetBtn = document.getElementById('importResetBtn');
        const commitBtn = document.getElementById('importCommitBtn');
        const fileInput = document.getElementById('importZipInput');

        if (stageUpload) stageUpload.classList.remove('hidden');
        if (dropzone) dropzone.classList.remove('hidden');
        if (loadingState) loadingState.classList.add('hidden');
        if (errorAlert) errorAlert.classList.add('hidden');
        if (stagePreview) stagePreview.classList.add('hidden');
        if (resetBtn) resetBtn.classList.add('hidden');
        if (commitBtn) {
            commitBtn.classList.add('hidden');
            commitBtn.disabled = true;
        }
        if (fileInput) fileInput.value = '';
    }

    function handleImportDragOver(e) {
        e.preventDefault();
        e.stopPropagation();
        const dropzone = document.getElementById('importDropzone');
        if (dropzone) {
            dropzone.classList.add('border-indigo-500', 'bg-indigo-50/40');
        }
    }

    function handleImportDragLeave(e) {
        e.preventDefault();
        e.stopPropagation();
        const dropzone = document.getElementById('importDropzone');
        if (dropzone) {
            dropzone.classList.remove('border-indigo-500', 'bg-indigo-50/40');
        }
    }

    function handleImportDrop(e) {
        e.preventDefault();
        e.stopPropagation();
        const dropzone = document.getElementById('importDropzone');
        if (dropzone) {
            dropzone.classList.remove('border-indigo-500', 'bg-indigo-50/40');
        }

        const files = e.dataTransfer ? e.dataTransfer.files : null;
        if (files && files.length > 0) {
            inspectToolsZipFile(files[0]);
        }
    }

    function handleImportFileSelect(input) {
        if (input.files && input.files.length > 0) {
            inspectToolsZipFile(input.files[0]);
        }
    }

    async function inspectToolsZipFile(file) {
        if (!file.name.toLowerCase().endsWith('.zip')) {
            showImportError('Please select a valid .zip archive file.');
            return;
        }

        if (file.size > 10 * 1024 * 1024) {
            showImportError('Package file exceeds the 10MB upload limit.');
            return;
        }

        const dropzone = document.getElementById('importDropzone');
        const loadingState = document.getElementById('importLoadingState');
        const errorAlert = document.getElementById('importErrorAlert');

        if (dropzone) dropzone.classList.add('hidden');
        if (errorAlert) errorAlert.classList.add('hidden');
        if (loadingState) loadingState.classList.remove('hidden');

        const formData = new FormData();
        formData.append('tools_zip', file);
        formData.append('csrf_token', toolsCsrfToken);

        try {
            const response = await fetch('<?= e(base_url('index.php?page=custom-tools-import-inspect')) ?>', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            const data = await response.json();
            if (!response.ok || !data.ok) {
                throw new Error(data.error || 'Failed to inspect package file.');
            }

            importStagingToken = data.import_token;
            importDiscoveredTools = data.tools || [];
            selectedImportSlugs = new Set(importDiscoveredTools.map(t => t.tool_slug));

            renderImportPreview(importDiscoveredTools, data.summary || {});
        } catch (err) {
            if (loadingState) loadingState.classList.add('hidden');
            if (dropzone) dropzone.classList.remove('hidden');
            showImportError(err.message || 'Inspection failed.');
        }
    }

    function showImportError(msg) {
        const errorAlert = document.getElementById('importErrorAlert');
        const errorMsg = document.getElementById('importErrorMessage');
        if (errorMsg) errorMsg.textContent = msg;
        if (errorAlert) errorAlert.classList.remove('hidden');
    }

    function renderImportPreview(tools, summary) {
        const stageUpload = document.getElementById('importStageUpload');
        const stagePreview = document.getElementById('importStagePreview');
        const resetBtn = document.getElementById('importResetBtn');
        const commitBtn = document.getElementById('importCommitBtn');
        const summaryBadge = document.getElementById('importSummaryBadge');
        const toolsList = document.getElementById('importToolsList');

        if (stageUpload) stageUpload.classList.add('hidden');
        if (stagePreview) stagePreview.classList.remove('hidden');
        if (resetBtn) resetBtn.classList.remove('hidden');
        if (commitBtn) commitBtn.classList.remove('hidden');

        const total = summary.total || tools.length;
        const newCount = summary.new || 0;
        const updates = summary.updates || 0;

        if (summaryBadge) {
            summaryBadge.innerHTML = `<span class="font-mono">${total}</span> tool(s) found (<span class="text-emerald-600 font-bold">${newCount} new</span>, <span class="text-amber-600 font-bold">${updates} update${updates === 1 ? '' : 's'}</span>)`;
        }

        if (toolsList) {
            toolsList.innerHTML = tools.map(t => {
                const isSelected = selectedImportSlugs.has(t.tool_slug);
                const isExisting = !!t.exists;
                const statusBadge = isExisting
                    ? `<span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200 shrink-0">⚠️ Overwrites Existing</span>`
                    : `<span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0">✨ New Tool</span>`;

                const cat = t.category || 'rest_api';
                const method = cat === 'rest_api' ? (t.execution_config?.method || 'GET') : (cat === 'webhook' ? 'POST' : (cat === 'shared_env_api' ? 'PROD' : 'BUILTIN'));
                const badgeClass = cat === 'rest_api' ? 'bg-sky-50 text-sky-700 border border-sky-200' : (cat === 'shared_env_api' ? 'bg-purple-50 text-purple-700 border border-purple-200' : (cat === 'webhook' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-700 border border-slate-200'));
                const paramsCount = Object.keys(t.parameters_schema?.properties || {}).length;

                return `
                    <div class="import-tool-row p-3 rounded-xl border transition-all cursor-pointer flex items-start gap-3 select-none ${isSelected ? 'border-indigo-400 bg-indigo-50/30' : 'border-slate-200 bg-white hover:border-slate-300'}" data-slug="${escapeHtml(t.tool_slug)}" onclick="toggleImportItem('${escapeHtml(t.tool_slug)}')">
                        <div class="pt-0.5 shrink-0">
                            <div class="import-radio-circle w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all ${isSelected ? 'border-indigo-600 bg-indigo-600 shadow-2xs' : 'border-slate-300 bg-white'}">
                                <svg class="w-3 h-3 text-white import-check-svg ${isSelected ? '' : 'hidden'}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-mono font-bold uppercase shrink-0 ${badgeClass}">${escapeHtml(method)}</span>
                                    <h4 class="text-xs font-bold text-slate-900 truncate">${escapeHtml(t.display_name)}</h4>
                                    <span class="text-[10px] font-mono text-slate-400 truncate">(${escapeHtml(t.tool_slug)})</span>
                                </div>
                                ${statusBadge}
                            </div>
                            <p class="text-[11px] text-slate-500 line-clamp-1 mb-1.5">${escapeHtml(t.description || 'No description provided.')}</p>
                            <div class="flex items-center gap-2 text-[10px] text-slate-400 font-mono">
                                <span>${paramsCount} parameter${paramsCount === 1 ? '' : 's'}</span>
                                <span>•</span>
                                <span>RBAC: ${(t.rbac_roles || ['guest', 'customer', 'admin']).join(', ')}</span>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        updateImportCommitUI();
    }

    function toggleImportItem(slug) {
        if (selectedImportSlugs.has(slug)) {
            selectedImportSlugs.delete(slug);
        } else {
            selectedImportSlugs.add(slug);
        }

        // Update row styling
        const row = document.querySelector(`.import-tool-row[data-slug="${slug}"]`);
        if (row) {
            const isSelected = selectedImportSlugs.has(slug);
            row.className = `import-tool-row p-3 rounded-xl border transition-all cursor-pointer flex items-start gap-3 select-none ${isSelected ? 'border-indigo-400 bg-indigo-50/30' : 'border-slate-200 bg-white hover:border-slate-300'}`;
            const circle = row.querySelector('.import-radio-circle');
            if (circle) circle.className = `import-radio-circle w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all ${isSelected ? 'border-indigo-600 bg-indigo-600 shadow-2xs' : 'border-slate-300 bg-white'}`;
            const check = row.querySelector('.import-check-svg');
            if (check) check.classList.toggle('hidden', !isSelected);
        }

        updateImportCommitUI();
    }

    function selectAllImportTools() {
        importDiscoveredTools.forEach(t => selectedImportSlugs.add(t.tool_slug));
        document.querySelectorAll('.import-tool-row').forEach(row => {
            row.classList.add('border-indigo-400', 'bg-indigo-50/30');
            row.classList.remove('border-slate-200', 'bg-white');
            const circle = row.querySelector('.import-radio-circle');
            if (circle) circle.className = 'import-radio-circle w-5 h-5 rounded-full border-2 border-indigo-600 bg-indigo-600 flex items-center justify-center transition-all shadow-2xs';
            const check = row.querySelector('.import-check-svg');
            if (check) check.classList.remove('hidden');
        });
        updateImportCommitUI();
    }

    function deselectAllImportTools() {
        selectedImportSlugs.clear();
        document.querySelectorAll('.import-tool-row').forEach(row => {
            row.classList.remove('border-indigo-400', 'bg-indigo-50/30');
            row.classList.add('border-slate-200', 'bg-white');
            const circle = row.querySelector('.import-radio-circle');
            if (circle) circle.className = 'import-radio-circle w-5 h-5 rounded-full border-2 border-slate-300 bg-white flex items-center justify-center transition-all';
            const check = row.querySelector('.import-check-svg');
            if (check) check.classList.add('hidden');
        });
        updateImportCommitUI();
    }

    function updateImportCommitUI() {
        const count = selectedImportSlugs.size;
        const commitCount = document.getElementById('importCommitCount');
        const commitBtn = document.getElementById('importCommitBtn');

        if (commitCount) commitCount.textContent = count;
        if (commitBtn) commitBtn.disabled = (count === 0);
    }

    async function commitSelectedImportTools() {
        if (selectedImportSlugs.size === 0 || !importStagingToken) return;

        const commitBtn = document.getElementById('importCommitBtn');
        const originalContent = commitBtn.innerHTML;
        commitBtn.disabled = true;
        commitBtn.innerHTML = `
            <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <span>Importing...</span>
        `;

        const overwrite = document.getElementById('importOverwriteCheckbox')?.checked ? 1 : 0;

        try {
            const res = await fetch('<?= e(base_url('index.php?page=custom-tools-import-commit')) ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': toolsCsrfToken
                },
                body: JSON.stringify({
                    import_token: importStagingToken,
                    selected_slugs: Array.from(selectedImportSlugs),
                    overwrite_existing: overwrite
                }),
                credentials: 'same-origin'
            });

            const data = await res.json();
            if (!res.ok || !data.ok) {
                throw new Error(data.error || 'Failed to commit imported tools.');
            }

            closeToolsImportModal();
            window.location.reload();
        } catch (err) {
            commitBtn.innerHTML = originalContent;
            commitBtn.disabled = false;
            alert(err.message || 'Import error.');
        }
    }

    // Close modals on ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (isExportMode) exitExportMode();
            closeToolsImportModal();
            closeInvocationDetails();
            closeToolTestModal();
            closeCustomToolDrawer();
            closeAiImporterModal();
            closePresetsModal();
        }
    });
</script>
