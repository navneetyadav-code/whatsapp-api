<!-- Escalations & Buzzer Logs Studio View -->
<div class="space-y-6 mb-8">
    <!-- Header with Breadcrumb and Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200/80">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-rose-50 text-rose-800 border border-rose-200/80">
                    Human Escalation Queue
                </span>
                <span class="text-xs text-slate-400 font-mono">Customer Support Desk</span>
            </div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Escalations & Alarm Logs</h2>
            <p class="text-xs text-slate-500 mt-0.5">Real-time incident log when customers request live human takeover or the bot encounters frustration triggers. Note: Strictly disabled in Personal Assistant mode.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= e(base_url('index.php?page=live-chat')) ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-xs transition-all active:scale-[0.98]">
                <span>💬 Open Live Chat Queue</span>
            </a>
        </div>
    </div>

    <!-- Metric Ribbon -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
        <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-subtle flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Active Alarms</p>
                <p class="text-2xl font-bold <?= $activeCount > 0 ? 'text-rose-600 animate-pulse' : 'text-slate-900' ?> font-mono mt-0.5"><?= $activeCount ?></p>
            </div>
            <div class="w-10 h-10 rounded-lg <?= $activeCount > 0 ? 'bg-rose-50 border border-rose-200 text-rose-600' : 'bg-slate-50 border border-slate-200 text-slate-400' ?> flex items-center justify-center text-lg">
                🚨
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-subtle flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Resolved Incidents</p>
                <p class="text-2xl font-bold text-slate-900 font-mono mt-0.5"><?= $resolvedCount ?></p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-emerald-50 border border-emerald-200/60 flex items-center justify-center text-emerald-700 text-lg">
                ✅
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-subtle flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Recorded</p>
                <p class="text-2xl font-bold text-slate-900 font-mono mt-0.5"><?= count($escalationsList) ?></p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-indigo-50 border border-indigo-200/60 flex items-center justify-center text-indigo-700 text-lg">
                📜
            </div>
        </div>
    </div>

    <!-- Escalations Log Table -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-card overflow-hidden">
        <div class="p-4 border-b border-slate-200/80 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Live Escalation Incident History</h3>
                <p class="text-xs text-slate-500">Chronological history of human handover triggers, timestamps, and resolution states.</p>
            </div>
            <span class="text-xs font-mono text-slate-400"><?= count($escalationsList) ?> events</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200/70 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Timestamp</th>
                        <th class="px-4 py-3">Customer Contact</th>
                        <th class="px-4 py-3">Trigger Reason</th>
                        <th class="px-4 py-3">Customer Trigger Text</th>
                        <th class="px-4 py-3">Incident Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($escalationsList as $esc): ?>
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-4 py-3 text-slate-500 font-mono text-[11px] whitespace-nowrap"><?= e($esc['created_at']) ?></td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-900"><?= e($esc['contact_name'] ?: '+' . $esc['phone']) ?></div>
                                <div class="text-[10px] text-slate-400 font-mono">+<?= e($esc['phone']) ?></div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $esc['status'] === 'active' ? 'bg-rose-100 text-rose-800 border border-rose-300' : 'bg-slate-100 text-slate-700' ?>">
                                    <?= e($esc['reason_code']) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600 max-w-xs truncate" title="<?= e($esc['trigger_text'] ?? '') ?>">
                                <?= e($esc['trigger_text'] ?? '—') ?>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $esc['status'] === 'active' ? 'bg-rose-600 text-white animate-pulse' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' ?>">
                                    <?= strtoupper($esc['status']) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="<?= e(base_url('index.php?page=live-chat')) ?>" class="px-2.5 py-1 text-xs font-semibold text-slate-700 hover:text-slate-900 hover:bg-slate-100 rounded-md transition-colors">
                                        Open Chat
                                    </a>
                                    <?php if ($esc['status'] === 'active'): ?>
                                        <form method="post" action="<?= e(base_url('index.php?page=escalation-resolve')) ?>" class="inline">
                                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="id" value="<?= (int) $esc['id'] ?>">
                                            <button type="submit" class="px-2.5 py-1 text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white rounded-md transition-colors shadow-xs">
                                                Resolve
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$escalationsList): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                                No escalations recorded yet. When a customer in Support Bot mode requests an agent or expresses frustration, incidents will be logged here.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
