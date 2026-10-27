    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Broadcast Campaigns</h1>
            <p class="text-[13px] text-slate-500 mt-0.5 font-medium">Formulate personalized bulk announcements to opted-in audiences with dynamic token replacement.</p>
        </div>
    </div>

    <!-- Integrated Telemetry Ribbon -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-subtle mb-6 overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-slate-100">
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500 tracking-tight">Recent Broadcast Runs</div>
                    <div class="text-xl font-bold text-slate-900 font-mono tabular-nums tracking-tight mt-0.5"><?= count($campaigns) ?></div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Campaigns registered</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                </div>
            </div>
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500 tracking-tight">Eligible Audience Pool</div>
                    <div class="text-xl font-bold text-emerald-600 font-mono tabular-nums tracking-tight mt-0.5"><?= $optedInCount ?></div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Verified opted-in contacts</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500 tracking-tight">Template Interpolation</div>
                    <div class="text-xl font-bold text-slate-900 font-mono tracking-tight mt-0.5">{name}, {phone}</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Dynamic personalization engine</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Campaign Form Card -->
    <section class="bg-white rounded-xl border border-slate-200/80 shadow-card p-5 sm:p-6 mb-6">
        <h2 class="text-[14px] font-bold text-slate-900 tracking-tight mb-1">Queue New Campaign</h2>
        <p class="text-[12px] text-slate-500 mb-4">Craft a template message to dispatch to all opted-in contacts in queue.</p>
        <form method="post" action="<?= e(base_url('index.php?page=campaign-create')) ?>" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            
            <div>
                <label class="block text-[12px] font-semibold text-slate-700 mb-1">Campaign Title</label>
                <input name="name" placeholder="e.g. November Product Update" value="Opt-in announcement" required class="block w-full px-3 py-2 text-[12px] border border-slate-200 rounded-lg bg-white shadow-subtle focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="text-[12px] font-semibold text-slate-700">Message Template</label>
                    <span class="text-[11px] font-mono text-slate-400">Tokens: {name}, {phone}</span>
                </div>
                <textarea name="message_template" rows="4" class="wa-editor block w-full px-3 py-2 text-[15px] sm:text-[16px] border border-slate-200 rounded-lg bg-white shadow-subtle focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-sans" data-presets='["{name}", "{phone}"]' placeholder="Hi {name}, this is an update from WhatsGateway." required></textarea>
            </div>

            <div>
                <button type="submit" class="px-4 py-2 text-[12px] font-semibold text-white bg-slate-900 rounded-lg shadow-subtle hover:bg-slate-800 transition-all btn-tactile active:scale-[0.98]">
                    Queue Campaign Broadcast
                </button>
            </div>
        </form>
    </section>

    <!-- Campaigns Ledger -->
    <section class="bg-white rounded-xl border border-slate-200/80 shadow-card overflow-hidden mb-8">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <h2 class="text-[14px] font-bold text-slate-900 tracking-tight">Campaign Dispatch History</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 text-[11px] font-semibold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <th class="px-5 py-3 font-semibold">ID</th>
                        <th class="px-5 py-3 font-semibold">Campaign Name</th>
                        <th class="px-5 py-3 font-semibold">Targets</th>
                        <th class="px-5 py-3 font-semibold">Queued</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 font-semibold text-right">Created At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($campaigns as $campaign): ?>
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-5 py-3.5 text-[12px] font-mono text-slate-500 tabular-nums">#<?= (int) $campaign['id'] ?></td>
                            <td class="px-5 py-3.5 text-[13px] font-bold text-slate-900"><?= e($campaign['name']) ?></td>
                            <td class="px-5 py-3.5 text-[12px] font-mono text-slate-700 tabular-nums"><?= (int) $campaign['target_count'] ?></td>
                            <td class="px-5 py-3.5 text-[12px] font-mono text-slate-700 tabular-nums"><?= (int) $campaign['queued_count'] ?></td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-0.5 text-[10px] font-semibold rounded-md border shadow-subtle inline-flex items-center gap-1.5 <?= e($campaign['status'] === 'completed' ? 'bg-emerald-50 text-emerald-700 border-emerald-200/80' : 'bg-slate-100 text-slate-600 border-slate-200/80') ?>">
                                    <span class="w-1.5 h-1.5 rounded-full <?= e($campaign['status'] === 'completed' ? 'bg-emerald-500' : 'bg-slate-400') ?>"></span>
                                    <?= e(ucfirst($campaign['status'])) ?>
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right text-[12px] font-mono text-slate-500 tabular-nums"><?= e($campaign['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$campaigns): ?>
                        <tr><td colspan="6" class="px-5 py-12 text-center text-[13px] text-slate-500">No campaigns launched yet. Queue one above when contacts are opted in.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

