    <!-- Page Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Contacts Directory</h1>
            <p class="text-[13px] text-slate-500 mt-0.5 font-medium">Store, import, and manage opted-in recipient address books for campaigns and API messages.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a class="px-3 py-1.5 text-[12px] font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg shadow-subtle hover:bg-slate-50 transition-colors inline-flex items-center gap-1.5 btn-tactile active:scale-[0.98]" href="<?= e(base_url('index.php?page=google-contacts')) ?>">
                <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                <span>Import Google Contacts</span>
            </a>
            <a class="px-3 py-1.5 text-[12px] font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg shadow-subtle hover:bg-slate-50 transition-colors inline-flex items-center gap-1.5 btn-tactile active:scale-[0.98]" href="<?= e(base_url('index.php?page=contacts-export-vcf')) ?>">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span>Export VCF</span>
            </a>
        </div>
    </div>

    <!-- Integrated Telemetry Ribbon -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-subtle mb-6 overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-slate-100">
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500 tracking-tight">Total Contact Entries</div>
                    <div class="text-xl font-bold text-slate-900 font-mono tabular-nums tracking-tight mt-0.5"><?= count($contacts) ?></div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Active directory pool</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500 tracking-tight">Verified Opt-In Consent</div>
                    <div class="text-xl font-bold text-emerald-600 font-mono tabular-nums tracking-tight mt-0.5"><?= $optedInCount ?></div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Eligible for broadcasts</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500 tracking-tight">Compliance Health Score</div>
                    <div class="text-xl font-bold text-slate-900 font-mono tabular-nums tracking-tight mt-0.5"><?= count($contacts) > 0 ? round(($optedInCount / count($contacts)) * 100) : 100 ?>%</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Anti-ban delivery standard</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Unified Recipient Ingestion Suite -->
    <section class="bg-white rounded-xl border border-slate-200/80 shadow-subtle mb-6 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 bg-slate-50/40">
            <div class="flex items-center gap-2">
                <span class="text-[12px] font-bold text-slate-800 tracking-tight">Add Recipients</span>
                <span class="text-slate-300">|</span>
                <div class="inline-flex items-center p-0.5 bg-slate-100 rounded-lg text-[11px] font-medium">
                    <button type="button" id="tabManualBtn" onclick="switchContactTab('manual')" class="px-2.5 py-1 rounded-md text-slate-800 bg-white shadow-xs font-semibold flex items-center gap-1.5 transition-all">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                        <span>Single Entry</span>
                    </button>
                    <button type="button" id="tabCsvBtn" onclick="switchContactTab('csv')" class="px-2.5 py-1 rounded-md text-slate-500 hover:text-slate-800 flex items-center gap-1.5 transition-all">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span>CSV Bulk Upload</span>
                    </button>
                </div>
            </div>
            <button type="button" onclick="toggleIngestionPane()" id="toggleIngestionBtn" class="text-[11px] font-medium text-slate-500 hover:text-slate-800 flex items-center gap-1 transition-colors">
                <span id="toggleIngestionText">Collapse</span>
                <svg id="toggleIngestionIcon" class="w-3.5 h-3.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
            </button>
        </div>

        <div id="ingestionContent" class="p-4 sm:p-5">
            <!-- Manual Form -->
            <form id="paneManual" method="post" action="<?= e(base_url('index.php?page=contact-create')) ?>" class="space-y-3">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                    <div class="sm:col-span-4">
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Full Name</label>
                        <input name="name" placeholder="e.g. John Doe" required class="block w-full px-3 py-2 text-[12px] border border-slate-200 rounded-lg bg-white shadow-subtle focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>
                    <div class="sm:col-span-4">
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Phone Number (E.164)</label>
                        <input name="phone" placeholder="+1234567890" required class="block w-full px-3 py-2 text-[12px] border border-slate-200 rounded-lg bg-white shadow-subtle focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-mono tabular-nums">
                    </div>
                    <div class="sm:col-span-4 flex items-center gap-3">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input name="opt_in" type="checkbox" checked class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                            <span class="text-[12px] font-semibold text-slate-700">Opt-in</span>
                        </label>
                        <button type="submit" class="flex-1 px-4 py-2 text-[12px] font-semibold text-white bg-slate-900 rounded-lg shadow-subtle hover:bg-slate-800 transition-all btn-tactile active:scale-[0.98] flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            <span>Save Contact</span>
                        </button>
                    </div>
                </div>
            </form>

            <!-- CSV Form -->
            <form id="paneCsv" method="post" action="<?= e(base_url('index.php?page=contact-import')) ?>" enctype="multipart/form-data" class="hidden space-y-3">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                    <div class="sm:col-span-8">
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-[11px] font-semibold text-slate-700">CSV File <span class="text-slate-400 font-normal">(Headers: name, phone, opt_in, notes)</span></label>
                            <span class="text-[11px] text-slate-400 font-mono">Max 5MB &bull; UTF-8</span>
                        </div>
                        <input name="contacts_csv" type="file" accept=".csv,text/csv" required class="block w-full px-3 py-1.5 text-[12px] border border-slate-200 rounded-lg bg-white shadow-subtle focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 cursor-pointer file:mr-3 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-[11px] file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                    </div>
                    <div class="sm:col-span-4">
                        <button type="submit" class="w-full px-4 py-2 text-[12px] font-semibold text-slate-800 bg-slate-100 hover:bg-slate-200 border border-slate-200/80 rounded-lg shadow-subtle transition-all btn-tactile active:scale-[0.98] flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            <span>Upload &amp; Import</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Contacts Directory Ledger -->
    <section class="bg-white rounded-xl border border-slate-200/80 shadow-card overflow-hidden mb-8">
        <!-- Table Toolbar -->
        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <h2 class="text-[13px] font-bold text-slate-900 tracking-tight">Saved Recipients</h2>
                <span class="px-2 py-0.5 text-[11px] font-bold bg-white text-slate-600 border border-slate-200/80 rounded-md shadow-subtle font-mono tabular-nums" id="contactCountBadge"><?= count($contacts) ?></span>
            </div>

            <!-- Instant Search & Quick Filter -->
            <div class="flex items-center gap-2.5">
                <div class="relative w-full sm:w-64">
                    <input id="contactSearchInput" type="text" placeholder="Search by name or phone..." class="w-full pl-8 pr-3 py-1.5 text-[12px] border border-slate-200 rounded-lg bg-white shadow-subtle focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 placeholder:text-slate-400">
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>

                <div class="inline-flex items-center p-0.5 bg-slate-100 rounded-lg text-[11px] font-medium shrink-0">
                    <button type="button" data-filter="all" class="contact-filter-btn px-2 py-1 rounded-md text-slate-800 bg-white shadow-xs font-semibold transition-all">All</button>
                    <button type="button" data-filter="opted-in" class="contact-filter-btn px-2 py-1 rounded-md text-slate-500 hover:text-slate-800 transition-all">Opted In</button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="contactsTable">
                <thead>
                    <tr class="bg-slate-50/70 text-[11px] font-semibold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <th class="px-5 py-3 font-semibold">Recipient</th>
                        <th class="px-5 py-3 font-semibold">WhatsApp Destination</th>
                        <th class="px-5 py-3 font-semibold">Consent Status</th>
                        <th class="px-5 py-3 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="contactsTbody">
                    <?php 
                    $colors = [
                        'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'bg-blue-50 text-blue-700 border-blue-200',
                        'bg-indigo-50 text-indigo-700 border-indigo-200',
                        'bg-amber-50 text-amber-700 border-amber-200',
                        'bg-purple-50 text-purple-700 border-purple-200',
                        'bg-teal-50 text-teal-700 border-teal-200'
                    ];
                    foreach ($contacts as $contact): 
                        $name = trim((string) $contact['name']);
                        $parts = explode(' ', $name);
                        $initials = mb_substr($parts[0] ?? '', 0, 1) . (isset($parts[1]) ? mb_substr($parts[1], 0, 1) : '');
                        $initials = strtoupper($initials ?: 'C');
                        $colorClass = $colors[((int) $contact['id']) % count($colors)];
                        $phone = (string) $contact['phone'];
                        $isOptedIn = !empty($contact['opt_in']);
                    ?>
                        <tr class="contact-row hover:bg-slate-50/70 transition-colors" data-name="<?= strtolower(e($name)) ?>" data-phone="<?= e($phone) ?>" data-optin="<?= $isOptedIn ? '1' : '0' ?>">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full border shadow-subtle flex items-center justify-center font-bold text-[11px] shrink-0 <?= $colorClass ?>">
                                        <?= e($initials) ?>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-[13px] font-bold text-slate-900 truncate"><?= e($name) ?></div>
                                        <?php if (!empty($contact['notes'])): ?>
                                            <div class="text-[11px] text-slate-400 truncate"><?= e($contact['notes']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-[12px] font-mono text-slate-700 font-medium tabular-nums"><?= e($phone) ?></span>
                                    <a href="<?= e(base_url('index.php?page=message&contact_id=' . (int) $contact['id'])) ?>" class="text-[10px] text-emerald-600 hover:text-emerald-700 hover:underline flex items-center gap-0.5" title="Direct Message">
                                        <span>Chat</span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                    </a>
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                <span class="px-2.5 py-0.5 text-[11px] font-semibold rounded-md border shadow-subtle inline-flex items-center gap-1.5 <?= $isOptedIn ? 'bg-emerald-50 text-emerald-700 border-emerald-200/80' : 'bg-slate-100 text-slate-600 border-slate-200/80' ?>">
                                    <span class="w-1.5 h-1.5 rounded-full <?= $isOptedIn ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' ?>"></span>
                                    <?= $isOptedIn ? 'Opted In' : 'No Opt-In' ?>
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="<?= e(base_url('index.php?page=message&contact_id=' . (int) $contact['id'])) ?>" class="p-1.5 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" title="Compose message">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                    </a>
                                    <form method="post" action="<?= e(base_url('index.php?page=contact-delete')) ?>" onsubmit="return confirm('Delete contact <?= e(addslashes($name)) ?>?');" class="inline">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $contact['id'] ?>">
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Delete contact">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$contacts): ?>
                        <tr id="emptyContactsRow"><td colspan="4" class="px-5 py-12 text-center text-[13px] text-slate-500">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                </div>
                                <span class="font-semibold text-slate-700">No contacts saved yet</span>
                                <span class="text-[12px] text-slate-400">Add a single recipient or upload a CSV above to get started.</span>
                            </div>
                        </td></tr>
                    <?php endif; ?>
                    <tr id="noSearchResultsRow" class="hidden"><td colspan="4" class="px-5 py-10 text-center text-[13px] text-slate-500">
                        <div class="flex flex-col items-center justify-center gap-1">
                            <span class="font-semibold text-slate-700">No matching contacts</span>
                            <span class="text-[12px] text-slate-400">Try adjusting your search query or filter.</span>
                        </div>
                    </td></tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Client-side Directory Script -->
    <script>
    function switchContactTab(tab) {
        const paneManual = document.getElementById('paneManual');
        const paneCsv = document.getElementById('paneCsv');
        const tabManualBtn = document.getElementById('tabManualBtn');
        const tabCsvBtn = document.getElementById('tabCsvBtn');

        if (tab === 'manual') {
            if (paneManual) paneManual.classList.remove('hidden');
            if (paneCsv) paneCsv.classList.add('hidden');
            if (tabManualBtn) tabManualBtn.className = 'px-2.5 py-1 rounded-md text-slate-800 bg-white shadow-xs font-semibold flex items-center gap-1.5 transition-all';
            if (tabCsvBtn) tabCsvBtn.className = 'px-2.5 py-1 rounded-md text-slate-500 hover:text-slate-800 flex items-center gap-1.5 transition-all';
        } else {
            if (paneManual) paneManual.classList.add('hidden');
            if (paneCsv) paneCsv.classList.remove('hidden');
            if (tabCsvBtn) tabCsvBtn.className = 'px-2.5 py-1 rounded-md text-slate-800 bg-white shadow-xs font-semibold flex items-center gap-1.5 transition-all';
            if (tabManualBtn) tabManualBtn.className = 'px-2.5 py-1 rounded-md text-slate-500 hover:text-slate-800 flex items-center gap-1.5 transition-all';
        }
    }

    function toggleIngestionPane() {
        const content = document.getElementById('ingestionContent');
        const text = document.getElementById('toggleIngestionText');
        const icon = document.getElementById('toggleIngestionIcon');
        if (!content) return;
        const isHidden = content.classList.contains('hidden');

        if (isHidden) {
            content.classList.remove('hidden');
            if (text) text.textContent = 'Collapse';
            if (icon) icon.classList.remove('rotate-180');
        } else {
            content.classList.add('hidden');
            if (text) text.textContent = 'Expand';
            if (icon) icon.classList.add('rotate-180');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('contactSearchInput');
        const filterBtns = document.querySelectorAll('.contact-filter-btn');
        const rows = document.querySelectorAll('.contact-row');
        const noResultsRow = document.getElementById('noSearchResultsRow');
        const countBadge = document.getElementById('contactCountBadge');
        let currentFilter = 'all';

        function applyFilter() {
            const query = (searchInput ? searchInput.value : '').toLowerCase().trim();
            let visibleCount = 0;

            rows.forEach(row => {
                const name = row.getAttribute('data-name') || '';
                const phone = row.getAttribute('data-phone') || '';
                const optin = row.getAttribute('data-optin') === '1';

                const matchesQuery = !query || name.includes(query) || phone.includes(query);
                const matchesStatus = currentFilter === 'all' || (currentFilter === 'opted-in' && optin);

                if (matchesQuery && matchesStatus) {
                    row.classList.remove('hidden');
                    visibleCount++;
                } else {
                    row.classList.add('hidden');
                }
            });

            if (noResultsRow) {
                noResultsRow.classList.toggle('hidden', visibleCount > 0 || rows.length === 0);
            }
            if (countBadge) {
                countBadge.textContent = visibleCount;
            }
        }

        if (searchInput) {
            searchInput.addEventListener('input', applyFilter);
        }

        filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                filterBtns.forEach(b => {
                    b.className = 'contact-filter-btn px-2 py-1 rounded-md text-slate-500 hover:text-slate-800 transition-all';
                });
                this.className = 'contact-filter-btn px-2 py-1 rounded-md text-slate-800 bg-white shadow-xs font-semibold transition-all';
                currentFilter = this.getAttribute('data-filter') || 'all';
                applyFilter();
            });
        });
    });
    </script>
