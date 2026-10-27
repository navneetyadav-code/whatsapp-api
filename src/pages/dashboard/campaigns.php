<?php
$tab = $tab ?? (string) ($_GET['tab'] ?? 'ledger');
if (!in_array($tab, ['ledger', 'create', 'test'], true)) {
    $tab = 'ledger';
}
?>

<!-- Segmented View Switcher (Tabs) -->
<div class="flex items-center justify-between gap-4 mb-6 flex-wrap">
    <div class="inline-flex items-center gap-1 p-1 bg-slate-100/90 rounded-xl border border-slate-200/80 shadow-xs">
        <a href="<?= e(base_url('index.php?page=campaigns&tab=ledger')) ?>" class="px-3.5 py-1.5 text-[12px] font-semibold rounded-lg transition-all inline-flex items-center gap-2 <?= $tab === 'ledger' ? 'bg-white text-slate-900 shadow-card font-bold border border-slate-200/60' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/50' ?>">
            <svg class="w-4 h-4 <?= $tab === 'ledger' ? 'text-emerald-600' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            <span>Dispatch Ledger</span>
            <span class="px-1.5 py-0.2 text-[10px] rounded-md font-mono <?= $tab === 'ledger' ? 'bg-slate-100 text-slate-800' : 'bg-slate-200/80 text-slate-500' ?>"><?= count($campaigns) ?></span>
        </a>

        <a href="<?= e(base_url('index.php?page=campaigns&tab=create')) ?>" class="px-3.5 py-1.5 text-[12px] font-semibold rounded-lg transition-all inline-flex items-center gap-2 <?= $tab === 'create' ? 'bg-white text-slate-900 shadow-card font-bold border border-slate-200/60' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/50' ?>">
            <svg class="w-4 h-4 <?= $tab === 'create' ? 'text-emerald-600' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"></path></svg>
            <span>New Broadcast Studio</span>
            <span class="px-1.5 py-0.2 text-[10px] rounded-md font-bold font-mono bg-emerald-50 text-emerald-700 border border-emerald-200/60">Studio</span>
        </a>

        <a href="<?= e(base_url('index.php?page=campaigns&tab=test')) ?>" class="px-3.5 py-1.5 text-[12px] font-semibold rounded-lg transition-all inline-flex items-center gap-2 <?= $tab === 'test' ? 'bg-white text-slate-900 shadow-card font-bold border border-slate-200/60' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/50' ?>">
            <svg class="w-4 h-4 <?= $tab === 'test' ? 'text-emerald-600' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
            <span>Test Dispatch Sandbox</span>
        </a>
    </div>

    <!-- Quick Action Shortcut when not on Create Tab -->
    <?php if ($tab !== 'create'): ?>
        <a href="<?= e(base_url('index.php?page=campaigns&tab=create')) ?>" class="px-3.5 py-1.5 text-[12px] font-semibold text-white bg-slate-900 rounded-lg shadow-subtle hover:bg-slate-800 transition-all inline-flex items-center gap-1.5 btn-tactile active:scale-[0.98]">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Create Campaign</span>
        </a>
    <?php endif; ?>
</div>

<?php if ($tab === 'ledger'): ?>
    <!-- ========================================== -->
    <!-- TAB 1: CAMPAIGN DISPATCH LEDGER & HISTORY  -->
    <!-- ========================================== -->

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Campaign Dispatch Ledger</h1>
            <p class="text-[13px] text-slate-500 mt-0.5 font-medium">Real-time status tracking, lifecycle controls, and queue history across all registered broadcasts.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-2.5 py-1 text-[11px] font-semibold rounded-md bg-white border border-slate-200/80 text-slate-700 shadow-subtle">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block mr-1"></span>
                <span><?= $optedInCount ?> Opted-in Contacts</span>
            </span>
        </div>
    </div>

    <!-- Integrated Telemetry Ribbon -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-subtle mb-6 overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-slate-100">
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500 tracking-tight">Total Broadcast Campaigns</div>
                    <div class="text-xl font-bold text-slate-900 font-mono tabular-nums tracking-tight mt-0.5"><?= count($campaigns) ?></div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Campaigns registered in ledger</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                </div>
            </div>
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500 tracking-tight">Verified Opt-In Audience</div>
                    <div class="text-xl font-bold text-emerald-600 font-mono tabular-nums tracking-tight mt-0.5"><?= $optedInCount ?></div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Contacts eligible for broadcast dispatches</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500 tracking-tight">Active Lifecycle Engine</div>
                    <div class="text-xl font-bold text-slate-900 tracking-tight mt-0.5">State Aware</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Pause, resume, and cancel enabled</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Campaigns Ledger Table -->
    <section class="bg-white rounded-xl border border-slate-200/80 shadow-card overflow-hidden mb-8">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <h2 class="text-[14px] font-bold text-slate-900 tracking-tight">Campaign Dispatch Records</h2>
                <span class="text-[11px] font-mono text-slate-500 bg-white border border-slate-200 px-2 py-0.5 rounded"><?= count($campaigns) ?> entries</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 text-[11px] font-semibold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <th class="px-5 py-3 font-semibold">ID</th>
                        <th class="px-5 py-3 font-semibold">Campaign Details</th>
                        <th class="px-5 py-3 font-semibold">Targets</th>
                        <th class="px-5 py-3 font-semibold">Queued</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 font-semibold">Scheduled / Created</th>
                        <th class="px-5 py-3 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($campaigns as $campaign): 
                        $status = (string) ($campaign['status'] ?? 'queued');
                    ?>
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-5 py-3.5 text-[12px] font-mono text-slate-500 tabular-nums">#<?= (int) $campaign['id'] ?></td>
                            <td class="px-5 py-3.5 text-[13px] font-bold text-slate-900">
                                <div><?= e($campaign['name']) ?></div>
                                <div class="text-[11px] text-slate-400 truncate max-w-xs font-normal"><?= e($campaign['message_template']) ?></div>
                            </td>
                            <td class="px-5 py-3.5 text-[12px] font-mono text-slate-700 tabular-nums"><?= (int) $campaign['target_count'] ?></td>
                            <td class="px-5 py-3.5 text-[12px] font-mono text-slate-700 tabular-nums"><?= (int) $campaign['queued_count'] ?></td>
                            <td class="px-5 py-3.5">
                                <?php if ($status === 'completed'): ?>
                                    <span class="px-2 py-0.5 text-[10px] font-semibold rounded-md border shadow-subtle inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 border-emerald-200/80">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>Completed</span>
                                    </span>
                                <?php elseif ($status === 'paused'): ?>
                                    <span class="px-2 py-0.5 text-[10px] font-semibold rounded-md border shadow-subtle inline-flex items-center gap-1.5 bg-amber-50 text-amber-700 border-amber-200/80">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        <span>Paused</span>
                                    </span>
                                <?php elseif ($status === 'cancelled'): ?>
                                    <span class="px-2 py-0.5 text-[10px] font-semibold rounded-md border shadow-subtle inline-flex items-center gap-1.5 bg-rose-50 text-rose-700 border-rose-200/80">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        <span>Cancelled</span>
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 text-[10px] font-semibold rounded-md border shadow-subtle inline-flex items-center gap-1.5 bg-sky-50 text-sky-700 border-sky-200/80">
                                        <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-pulse"></span>
                                        <span>Queued</span>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-3.5 text-[12px] font-mono text-slate-500 tabular-nums">
                                <?php if (!empty($campaign['scheduled_at'])): ?>
                                    <div class="text-[11px] text-amber-700 font-semibold inline-flex items-center gap-1">
                                        <svg class="w-3 h-3 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span><?= e($campaign['scheduled_at']) ?></span>
                                    </div>
                                <?php else: ?>
                                    <div><?= e($campaign['created_at']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <?php if ($status === 'queued'): ?>
                                        <!-- Pause Action -->
                                        <form method="post" action="<?= e(base_url('index.php?page=campaign-pause')) ?>" class="inline">
                                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="campaign_id" value="<?= (int) $campaign['id'] ?>">
                                            <button type="submit" class="px-2 py-1 text-[11px] font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200/80 rounded-md shadow-subtle transition-all inline-flex items-center gap-1 active:scale-95" title="Pause broadcast queue">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <span>Pause</span>
                                            </button>
                                        </form>

                                        <!-- Cancel Action -->
                                        <form method="post" action="<?= e(base_url('index.php?page=campaign-cancel')) ?>" class="inline" onsubmit="return confirm('Cancel this campaign and abort all pending dispatches?');">
                                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="campaign_id" value="<?= (int) $campaign['id'] ?>">
                                            <button type="submit" class="px-2 py-1 text-[11px] font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200/80 rounded-md shadow-subtle transition-all inline-flex items-center gap-1 active:scale-95" title="Abort pending dispatches">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                <span>Cancel</span>
                                            </button>
                                        </form>

                                    <?php elseif ($status === 'paused'): ?>
                                        <!-- Resume Action -->
                                        <form method="post" action="<?= e(base_url('index.php?page=campaign-resume')) ?>" class="inline">
                                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="campaign_id" value="<?= (int) $campaign['id'] ?>">
                                            <button type="submit" class="px-2 py-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200/80 rounded-md shadow-subtle transition-all inline-flex items-center gap-1 active:scale-95" title="Resume broadcast queue">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <span>Resume</span>
                                            </button>
                                        </form>

                                        <!-- Cancel Action -->
                                        <form method="post" action="<?= e(base_url('index.php?page=campaign-cancel')) ?>" class="inline" onsubmit="return confirm('Cancel this campaign and abort all pending dispatches?');">
                                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="campaign_id" value="<?= (int) $campaign['id'] ?>">
                                            <button type="submit" class="px-2 py-1 text-[11px] font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200/80 rounded-md shadow-subtle transition-all inline-flex items-center gap-1 active:scale-95" title="Abort pending dispatches">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                <span>Cancel</span>
                                            </button>
                                        </form>

                                    <?php else: ?>
                                        <!-- Resend Action -->
                                        <form method="post" action="<?= e(base_url('index.php?page=campaign-resend')) ?>" class="inline" onsubmit="return confirm('Duplicate and re-queue this broadcast for opted-in contacts?');">
                                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="campaign_id" value="<?= (int) $campaign['id'] ?>">
                                            <button type="submit" class="px-2 py-1 text-[11px] font-semibold text-slate-700 bg-slate-50 hover:bg-slate-100 border border-slate-200/80 rounded-md shadow-subtle transition-all inline-flex items-center gap-1 active:scale-95" title="Duplicate & re-dispatch">
                                                <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                                <span>Resend</span>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$campaigns): ?>
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center">
                                <div class="max-w-sm mx-auto text-center space-y-3">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                                    </div>
                                    <div class="text-[13px] font-semibold text-slate-700">No campaigns launched yet</div>
                                    <p class="text-[12px] text-slate-400">Queue a personalized announcement or promotional broadcast to your opted-in contacts directory.</p>
                                    <a href="<?= e(base_url('index.php?page=campaigns&tab=create')) ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[12px] font-semibold text-white bg-slate-900 rounded-lg hover:bg-slate-800 transition-all btn-tactile">
                                        <span>Create First Broadcast</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

<?php elseif ($tab === 'create'): ?>
    <!-- ========================================== -->
    <!-- TAB 2: DEDICATED NEW BROADCAST STUDIO      -->
    <!-- ========================================== -->

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">New Broadcast Studio</h1>
                <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200/80">Composer</span>
            </div>
            <p class="text-[13px] text-slate-500 mt-0.5 font-medium">Design bulk announcements with real-time phone preview, rich media attachments, and scheduled delivery.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= e(base_url('index.php?page=campaigns&tab=ledger')) ?>" class="px-3 py-1.5 text-[12px] font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg shadow-subtle hover:bg-slate-50 transition-colors inline-flex items-center gap-1.5 btn-tactile active:scale-[0.98]">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Back to Ledger</span>
            </a>
        </div>
    </div>

    <!-- Telemetry Strip -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-subtle mb-6 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div>
                <div class="text-[13px] font-bold text-slate-900 tracking-tight">Active Target Pool: <?= $optedInCount ?> Verified Recipients</div>
                <div class="text-[11px] text-slate-500">Every message is paced with humanized randomized inter-packet jitter to protect account health.</div>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <span class="px-2 py-0.5 text-[10px] font-mono font-semibold rounded bg-slate-100 text-slate-600">Anti-Ban Pacing Active</span>
            <span class="px-2 py-0.5 text-[10px] font-mono font-semibold rounded bg-emerald-50 text-emerald-700 border border-emerald-200">Consent Verified</span>
        </div>
    </div>

    <!-- 2-Column Authoring Studio -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start mb-8">
        
        <!-- Left Column: The Composer Form (7 cols) -->
        <div class="lg:col-span-7 space-y-4">
            <section class="bg-white rounded-xl border border-slate-200/80 shadow-card p-5 sm:p-6">
                <h2 class="text-[14px] font-bold text-slate-900 tracking-tight mb-1">Broadcast Details & Content</h2>
                <p class="text-[12px] text-slate-500 mb-4">Formulate your announcement text and configure timing options.</p>

                <form method="post" action="<?= e(base_url('index.php?page=campaign-create')) ?>" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    
                    <div>
                        <label class="block text-[12px] font-semibold text-slate-700 mb-1">Campaign Title</label>
                        <input id="campaign-title-input" name="name" placeholder="e.g. October VIP Announcement" value="Special Announcement" required class="block w-full px-3 py-2 text-[12px] border border-slate-200 rounded-lg bg-white shadow-subtle focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all font-sans">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-[12px] font-semibold text-slate-700">Message Template</label>
                            <div class="flex items-center gap-1.5">
                                <span class="text-[11px] text-slate-400 font-medium">Insert:</span>
                                <button type="button" onclick="insertToken('{name}')" class="px-1.5 py-0.5 text-[10px] font-mono font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded border border-slate-200/80 transition-colors">{name}</button>
                                <button type="button" onclick="insertToken('{phone}')" class="px-1.5 py-0.5 text-[10px] font-mono font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded border border-slate-200/80 transition-colors">{phone}</button>
                            </div>
                        </div>
                        <textarea id="campaign-message-input" name="message_template" rows="5" class="wa-editor block w-full px-3 py-2.5 text-[14px] border border-slate-200 rounded-lg bg-white shadow-subtle focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-sans transition-all leading-relaxed" data-presets='["{name}", "{phone}"]' placeholder="Hi {name}, we are pleased to announce our latest update..." required oninput="updatePreview()"></textarea>
                        <div class="flex items-center justify-between mt-1 text-[11px] text-slate-400">
                            <span>Use tokens to personalize text for each contact automatically.</span>
                            <span id="char-counter" class="font-mono">0 chars</span>
                        </div>
                    </div>

                    <!-- Schedule Picker -->
                    <div>
                        <label class="block text-[12px] font-semibold text-slate-700 mb-1">Schedule Timing (Optional)</label>
                        <input type="datetime-local" name="scheduled_at" min="<?= date('Y-m-d\TH:i') ?>" class="block w-full px-3 py-2 text-[12px] border border-slate-200 rounded-lg bg-white shadow-subtle focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all text-slate-700 font-mono">
                        <p class="text-[10px] text-slate-400 mt-1">Leave empty to queue broadcast immediately. Future dates will be held safely in queue until the selected timestamp.</p>
                    </div>

                    <!-- Media Attachment -->
                    <div class="p-3.5 bg-slate-50/80 rounded-lg border border-slate-200/80">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                <span class="text-[12px] font-semibold text-slate-700">Attach Media (Optional)</span>
                            </div>
                            <span class="text-[10px] font-mono text-slate-400">JPG, PNG, PDF, MP4 &le; 15MB</span>
                        </div>
                        <input id="media-upload-input" type="file" name="attachment" accept="image/*,video/*,audio/*,application/pdf" class="block w-full text-[12px] text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-[11px] file:font-semibold file:bg-white file:text-slate-700 file:border file:border-slate-200 hover:file:bg-slate-100 cursor-pointer" onchange="previewMedia(this)">
                        <p class="text-[10px] text-slate-400 mt-1.5">When attached, your template message is dispatched as the rich media caption.</p>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 text-[13px] font-semibold text-white bg-slate-900 rounded-lg shadow-subtle hover:bg-slate-800 transition-all btn-tactile active:scale-[0.98] inline-flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                            <span>Queue Broadcast Dispatch</span>
                        </button>
                    </div>
                </form>
            </section>
        </div>

        <!-- Right Column: Sticky Live WhatsApp Phone Mockup (5 cols) -->
        <div class="lg:col-span-5 sticky top-6">
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-card overflow-hidden">
                <!-- Mockup Phone Header -->
                <div class="bg-[#075E54] text-white px-4 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-emerald-400/20 border border-white/20 flex items-center justify-center text-[12px] font-bold text-white">
                            WG
                        </div>
                        <div>
                            <div class="text-[13px] font-semibold leading-tight">WhatsGateway</div>
                            <div class="text-[10px] text-emerald-100/80 font-normal">Business Account</div>
                        </div>
                    </div>
                    <div class="text-[10px] font-mono text-emerald-200/70">LIVE PREVIEW</div>
                </div>

                <!-- Chat Wallpaper Background -->
                <div class="p-4 bg-[#EFEAE2] min-h-[300px] flex flex-col justify-end space-y-3">
                    
                    <!-- Date Pill -->
                    <div class="text-center">
                        <span class="px-2.5 py-0.5 text-[10px] font-medium bg-white/80 rounded-md text-slate-600 shadow-xs uppercase tracking-wider font-mono">Today</span>
                    </div>

                    <!-- WhatsApp Outgoing Chat Bubble -->
                    <div class="self-end max-w-[88%] bg-[#E7FFDB] rounded-lg rounded-tr-none p-2.5 shadow-xs border border-emerald-900/5 text-slate-800 text-[13px] space-y-1.5 leading-relaxed relative">
                        
                        <!-- Media Container (Hidden by default, injected dynamically on upload) -->
                        <div id="mockup-media-container" class="hidden rounded overflow-hidden max-h-40 bg-slate-200"></div>

                        <!-- Rendered Text -->
                        <div id="mockup-text" class="whitespace-pre-wrap break-words">Hi <span class="font-semibold text-emerald-800">John Doe</span>, we are pleased to announce our latest update...</div>
                        
                        <!-- Bubble Footer (Timestamp & Checkmarks) -->
                        <div class="flex items-center justify-end gap-1 text-[10px] text-slate-400 font-mono pt-0.5">
                            <span id="mockup-time"><?= date('H:i') ?></span>
                            <!-- Double Checkmarks -->
                            <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 16 15" fill="none"><path d="M15 4.5l-8.5 8.5-4.5-4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M11 4.5l-4.5 4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Preview Footer Note -->
                <div class="p-3 bg-slate-50 border-t border-slate-100 text-[11px] text-slate-500 text-center">
                    Shows real-time token interpolation for sample recipient <strong class="text-slate-800">John Doe</strong>.
                </div>
            </div>
        </div>
    </div>

<?php elseif ($tab === 'test'): ?>
    <!-- ========================================== -->
    <!-- TAB 3: TEST DISPATCH SANDBOX               -->
    <!-- ========================================== -->

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Test Dispatch Sandbox</h1>
                <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-md bg-amber-50 text-amber-700 border border-amber-200/80">Sandbox</span>
            </div>
            <p class="text-[13px] text-slate-500 mt-0.5 font-medium">Send an isolated, high-priority test broadcast to your physical WhatsApp number before publishing to all contacts.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= e(base_url('index.php?page=campaigns&tab=ledger')) ?>" class="px-3 py-1.5 text-[12px] font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg shadow-subtle hover:bg-slate-50 transition-colors inline-flex items-center gap-1.5 btn-tactile active:scale-[0.98]">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Back to Ledger</span>
            </a>
            <a href="<?= e(base_url('index.php?page=campaigns&tab=create')) ?>" class="px-3 py-1.5 text-[12px] font-semibold text-white bg-slate-900 rounded-lg shadow-subtle hover:bg-slate-800 transition-colors inline-flex items-center gap-1.5 btn-tactile active:scale-[0.98]">
                <span>New Broadcast</span>
            </a>
        </div>
    </div>

    <!-- 2-Column Sandbox Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start mb-8">
        
        <!-- Left: Test Dispatch Console (7 cols) -->
        <div class="lg:col-span-7">
            <section class="bg-white rounded-xl border border-slate-200/80 shadow-card p-5 sm:p-6">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                    <h2 class="text-[14px] font-bold text-slate-900 tracking-tight">Instant Single Dispatch Console</h2>
                    <span class="px-2 py-0.5 text-[10px] font-semibold font-mono text-emerald-700 bg-emerald-50 rounded border border-emerald-200">Priority 10 Queue</span>
                </div>

                <form method="post" action="<?= e(base_url('index.php?page=campaign-test-send')) ?>" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[12px] font-semibold text-slate-700 mb-1">Your Test Phone Number</label>
                            <input name="test_phone" placeholder="e.g. +14155552671" required class="block w-full px-3 py-2 text-[12px] border border-slate-200 rounded-lg bg-white shadow-subtle focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all font-mono">
                            <p class="text-[10px] text-slate-400 mt-1">Include country code with leading + (E.164 standard).</p>
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-slate-700 mb-1">Sample Name Token ({name})</label>
                            <input name="test_name" value="Valued Tester" required class="block w-full px-3 py-2 text-[12px] border border-slate-200 rounded-lg bg-white shadow-subtle focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                            <p class="text-[10px] text-slate-400 mt-1">Will be injected wherever {name} is used.</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-slate-700 mb-1">Test Message Template</label>
                        <textarea name="message_template" rows="4" class="wa-editor block w-full px-3 py-2 text-[14px] border border-slate-200 rounded-lg bg-white shadow-subtle focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-sans transition-all leading-relaxed" placeholder="Hi {name}, this is a test dispatch from WhatsGateway." required>Hi {name}, this is a test broadcast dispatch from WhatsGateway. Verified at <?= date('H:i:s') ?>.</textarea>
                    </div>

                    <!-- Media Attachment -->
                    <div class="p-3 bg-slate-50/80 rounded-lg border border-slate-200/80">
                        <label class="block text-[12px] font-semibold text-slate-700 mb-1">Optional Media File</label>
                        <input type="file" name="attachment" accept="image/*,video/*,audio/*,application/pdf" class="block w-full text-[12px] text-slate-500 file:mr-3 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-[11px] file:font-semibold file:bg-white file:text-slate-700 file:border file:border-slate-200 hover:file:bg-slate-100 cursor-pointer">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="px-4 py-2 text-[12px] font-semibold text-white bg-slate-900 rounded-lg shadow-subtle hover:bg-slate-800 transition-all btn-tactile active:scale-[0.98] inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                            <span>Dispatch Priority Test Message</span>
                        </button>
                    </div>
                </form>
            </section>
        </div>

        <!-- Right: Verification Checklist & Quality Guide (5 cols) -->
        <div class="lg:col-span-5 space-y-4">
            <section class="bg-white rounded-xl border border-slate-200/80 shadow-card p-5">
                <h3 class="text-[13px] font-bold text-slate-900 tracking-tight mb-2 flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Pre-Flight Launch Checklist</span>
                </h3>
                <ul class="space-y-2.5 text-[12px] text-slate-600">
                    <li class="flex items-start gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0 mt-1.5"></span>
                        <span><strong>Token Interpolation:</strong> Ensure <code class="font-mono text-emerald-700 bg-emerald-50 px-1 rounded">{name}</code> and <code class="font-mono text-emerald-700 bg-emerald-50 px-1 rounded">{phone}</code> replace smoothly without leftover braces.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0 mt-1.5"></span>
                        <span><strong>Media Aspect Ratio:</strong> Verify images render legibly without aggressive cropping in WhatsApp's chat viewer.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0 mt-1.5"></span>
                        <span><strong>Compliance & Unsubscribe:</strong> For promotional blasts, always remind recipients they can reply <code class="font-mono text-slate-800 bg-slate-100 px-1 rounded">STOP</code> to opt out.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0 mt-1.5"></span>
                        <span><strong>Delivery Confirmation:</strong> Confirm your phone receives the socket push within 3–5 seconds before triggering bulk dispatches.</span>
                    </li>
                </ul>
            </section>
        </div>
    </div>
<?php endif; ?>

<script>
function insertToken(token) {
    const textarea = document.getElementById('campaign-message-input');
    if (!textarea) return;
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    textarea.value = text.substring(0, start) + token + text.substring(end);
    textarea.focus();
    textarea.selectionStart = textarea.selectionEnd = start + token.length;
    updatePreview();
}

function updatePreview() {
    const textarea = document.getElementById('campaign-message-input');
    const previewEl = document.getElementById('mockup-text');
    const counterEl = document.getElementById('char-counter');
    if (!textarea || !previewEl) return;

    let content = textarea.value;
    if (counterEl) {
        counterEl.textContent = content.length + ' chars';
    }

    if (!content.trim()) {
        previewEl.innerHTML = 'Hi <span class="font-semibold text-emerald-800">John Doe</span>, we are pleased to announce our latest update...';
        return;
    }

    // Replace tokens with sample data for live preview
    let interpolated = content
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/{name}/g, '<span class="font-semibold text-emerald-800">John Doe</span>')
        .replace(/{phone}/g, '<span class="font-mono text-emerald-800">+1 415 555-0199</span>');

    previewEl.innerHTML = interpolated;
}

function previewMedia(input) {
    const container = document.getElementById('mockup-media-container');
    if (!container) return;

    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                container.innerHTML = `<img src="${e.target.result}" alt="Media attachment preview" class="w-full h-full object-cover">`;
                container.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        } else {
            container.innerHTML = `<div class="p-2 text-[11px] font-mono text-slate-600 bg-slate-100 flex items-center gap-1.5"><svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg><span class="truncate">${file.name}</span></div>`;
            container.classList.remove('hidden');
        }
    } else {
        container.innerHTML = '';
        container.classList.add('hidden');
    }
}
</script>
