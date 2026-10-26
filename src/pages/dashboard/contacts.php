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
            <a class="px-3 py-1.5 text-[12px] font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg shadow-subtle hover:bg-slate-50 transition-colors btn-tactile active:scale-[0.98]" href="<?= e(base_url('index.php?page=contacts-export-vcf')) ?>">
                Export VCF
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
                    <div class="text-[11px] text-slate-400 mt-0.5">Database storage volume</div>
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
                    <div class="text-[11px] font-semibold text-slate-500 tracking-tight">Export & Interop</div>
                    <div class="text-xl font-bold text-slate-900 tracking-tight mt-0.5">vCard 3.0 (VCF)</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Universal mobile address book</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Creation & Import 2-Column Suite -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Add Contact Form -->
        <section class="bg-white rounded-xl border border-slate-200/80 shadow-card p-5">
            <h2 class="text-[14px] font-bold text-slate-900 tracking-tight mb-1">Add Contact</h2>
            <p class="text-[12px] text-slate-500 mb-4">Manually insert a recipient with verified consent.</p>
            <form method="post" action="<?= e(base_url('index.php?page=contact-create')) ?>" class="space-y-3.5">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Full Name</label>
                        <input name="name" placeholder="John Doe" required class="block w-full px-3 py-1.5 text-[12px] border border-slate-200 rounded-lg bg-white shadow-subtle focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Phone (E.164)</label>
                        <input name="phone" placeholder="+1234567890" required class="block w-full px-3 py-1.5 text-[12px] border border-slate-200 rounded-lg bg-white shadow-subtle focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-mono tabular-nums">
                    </div>
                </div>
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input name="opt_in" type="checkbox" checked class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                        <span class="text-[12px] font-semibold text-slate-700">Verified Opt-In Consent</span>
                    </label>
                    <button type="submit" class="px-4 py-2 text-[12px] font-semibold text-white bg-slate-900 rounded-lg shadow-subtle hover:bg-slate-800 transition-all btn-tactile active:scale-[0.98]">
                        Save Contact
                    </button>
                </div>
            </form>
        </section>

        <!-- CSV Bulk Import Form -->
        <section class="bg-white rounded-xl border border-slate-200/80 shadow-card p-5">
            <h2 class="text-[14px] font-bold text-slate-900 tracking-tight mb-1">Bulk CSV Ingestion</h2>
            <p class="text-[12px] text-slate-500 mb-4">Upload a spreadsheet formatted with: <code>name, phone, opt_in, notes</code>.</p>
            <form method="post" action="<?= e(base_url('index.php?page=contact-import')) ?>" enctype="multipart/form-data" class="space-y-3.5">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">CSV File</label>
                    <input name="contacts_csv" type="file" accept=".csv,text/csv" required class="block w-full px-3 py-1.5 text-[12px] border border-slate-200 rounded-lg bg-white shadow-subtle focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                </div>
                <div class="flex items-center justify-between pt-1">
                    <span class="text-[11px] text-slate-400">Max 5MB &bull; UTF-8 encoded</span>
                    <button type="submit" class="px-4 py-2 text-[12px] font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg shadow-subtle hover:bg-slate-50 transition-all btn-tactile active:scale-[0.98]">
                        Import CSV
                    </button>
                </div>
            </form>
        </section>
    </div>

    <!-- Contacts Directory Ledger -->
    <section class="bg-white rounded-xl border border-slate-200/80 shadow-card overflow-hidden mb-8">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <h2 class="text-[14px] font-bold text-slate-900 tracking-tight">Saved Recipients</h2>
            <span class="text-[11px] text-slate-400">Displaying recent contacts</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 text-[11px] font-semibold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <th class="px-5 py-3 font-semibold">Contact Name</th>
                        <th class="px-5 py-3 font-semibold">Phone Number</th>
                        <th class="px-5 py-3 font-semibold">Consent Status</th>
                        <th class="px-5 py-3 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($contacts as $contact): ?>
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-5 py-3.5 text-[13px] font-bold text-slate-900"><?= e($contact['name']) ?></td>
                            <td class="px-5 py-3.5 text-[12px] font-mono text-slate-600 tabular-nums"><?= e($contact['phone']) ?></td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-0.5 text-[10px] font-semibold rounded-md border shadow-subtle inline-flex items-center gap-1.5 <?= $contact['opt_in'] ? 'bg-emerald-50 text-emerald-700 border-emerald-200/80' : 'bg-slate-100 text-slate-600 border-slate-200/80' ?>">
                                    <span class="w-1.5 h-1.5 rounded-full <?= $contact['opt_in'] ? 'bg-emerald-500' : 'bg-slate-400' ?>"></span>
                                    <?= $contact['opt_in'] ? 'Opted In' : 'No Opt-In' ?>
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <form method="post" action="<?= e(base_url('index.php?page=contact-delete')) ?>" onsubmit="return confirm('Delete this contact?');" class="inline">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $contact['id'] ?>">
                                    <button type="submit" class="p-1 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded transition-colors" title="Delete contact">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$contacts): ?>
                        <tr><td colspan="4" class="px-5 py-12 text-center text-[13px] text-slate-500">No contacts saved yet. Add your first opted-in recipient above.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

