    <!-- Page Header & Live Pip -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Message Queue</h1>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/80 shadow-subtle flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Live Auto-Refresh
                </span>
            </div>
            <p class="text-[13px] text-slate-500 mt-0.5 font-medium">Real-time pipeline monitoring for queued, processing, delivered, and failed transmissions.</p>
        </div>
    </div>
    
    <!-- Integrated Telemetry Ribbon -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-subtle mb-6 overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-slate-100">
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500 tracking-tight">Queued In Buffer</div>
                    <div class="text-xl font-bold text-slate-900 font-mono tabular-nums tracking-tight mt-0.5" id="liveQueuedCount"><?= $queuedCount ?></div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Pending worker ingestion</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500 tracking-tight">In-Flight Processing</div>
                    <div class="text-xl font-bold text-blue-600 font-mono tabular-nums tracking-tight mt-0.5" id="liveProcessingCount"><?= $processingCount ?></div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Active socket transmission</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </div>
            </div>
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500 tracking-tight">Terminal Failures</div>
                    <div class="text-xl font-bold text-rose-600 font-mono tabular-nums tracking-tight mt-0.5" id="liveQueueFailedCount"><?= $failedCount ?></div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Requires retry or review</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-600 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Ledger Table -->
    <section class="bg-white rounded-xl border border-slate-200/80 shadow-card overflow-hidden mb-8">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 text-[11px] font-semibold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <th class="px-5 py-3 font-semibold">ID</th>
                        <th class="px-5 py-3 font-semibold">Recipient</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 font-semibold">Attempts</th>
                        <th class="px-5 py-3 font-semibold">Message Payload</th>
                        <th class="px-5 py-3 font-semibold">Diagnostics / Error</th>
                        <th class="px-5 py-3 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="liveQueueTable" class="divide-y divide-slate-100">
                    <?php foreach ($queueMessages as $message): ?>
                        <?php 
                            $statusColors = [
                                'queued' => 'bg-slate-100 text-slate-700 border-slate-200',
                                'processing' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'sent' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'delivered' => 'bg-teal-50 text-teal-700 border-teal-200',
                                'read' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                'failed' => 'bg-rose-50 text-rose-700 border-rose-200',
                                'cancelled' => 'bg-orange-50 text-orange-700 border-orange-200'
                            ];
                            $dotColors = [
                                'queued' => 'bg-slate-500',
                                'processing' => 'bg-blue-500 animate-pulse',
                                'sent' => 'bg-emerald-500',
                                'delivered' => 'bg-teal-500',
                                'read' => 'bg-indigo-500',
                                'failed' => 'bg-rose-500',
                                'cancelled' => 'bg-orange-500'
                            ];
                            $sc = $statusColors[$message['status']] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                            $dc = $dotColors[$message['status']] ?? 'bg-slate-500';
                        ?>
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-5 py-3.5 text-[12px] font-mono text-slate-500 tabular-nums">#<?= (int) $message['id'] ?></td>
                            <td class="px-5 py-3.5 text-[13px] font-mono font-medium text-slate-900 tabular-nums"><?= e($message['normalized_recipient'] ?: $message['recipient']) ?></td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-0.5 text-[10px] font-semibold rounded-md border shadow-subtle inline-flex items-center gap-1.5 <?= $sc ?>">
                                    <span class="w-1.5 h-1.5 rounded-full <?= $dc ?>"></span>
                                    <?= e(ucfirst($message['status'])) ?>
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-[12px] text-slate-600 font-mono tabular-nums"><?= (int) $message['attempts'] ?>/<?= (int) $message['max_attempts'] ?></td>
                            <td class="px-5 py-3.5 text-[12px] text-slate-600 truncate max-w-xs" title="<?= e($message['body']) ?>"><?= e(mb_substr($message['body'], 0, 60)) ?><?= mb_strlen($message['body']) > 60 ? '...' : '' ?></td>
                            <td class="px-5 py-3.5 text-[11px] text-rose-600 font-mono max-w-xs truncate" title="<?= e($message['error_message'] ?? '') ?>"><?= e($message['error_message'] ?? '—') ?></td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <?php if ($message['status'] === 'queued'): ?>
                                        <form method="post" action="<?= e(base_url('index.php?page=message-cancel')) ?>">
                                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="id" value="<?= (int) $message['id'] ?>">
                                            <button type="submit" class="px-2.5 py-1 text-[11px] font-semibold text-rose-700 bg-white border border-rose-200 rounded-md hover:bg-rose-50 transition-colors shadow-subtle btn-tactile active:scale-[0.98]">Cancel</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if (in_array($message['status'], ['failed', 'cancelled'], true)): ?>
                                        <form method="post" action="<?= e(base_url('index.php?page=message-retry')) ?>">
                                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="id" value="<?= (int) $message['id'] ?>">
                                            <button type="submit" class="px-2.5 py-1 text-[11px] font-semibold text-slate-700 bg-white border border-slate-200 rounded-md hover:bg-slate-50 transition-colors shadow-subtle btn-tactile active:scale-[0.98]">Retry</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$queueMessages): ?>
                        <tr><td colspan="7" class="px-5 py-12 text-center text-[13px] text-slate-500">The message queue is currently empty. Incoming API dispatches will stream here.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination Controls -->
        <?php if ($totalPages > 1): ?>
        <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <div class="text-[12px] text-slate-500 font-medium">
                Showing <strong class="text-slate-900 font-mono tabular-nums"><?= (($queuePage - 1) * $queuePerPage) + 1 ?></strong> to <strong class="text-slate-900 font-mono tabular-nums"><?= min($totalQueueMessages, $queuePage * $queuePerPage) ?></strong> of <strong class="text-slate-900 font-mono tabular-nums"><?= $totalQueueMessages ?></strong> messages
            </div>
            <div class="flex items-center gap-1.5">
                <?php if ($queuePage > 1): ?>
                    <a href="<?= e(base_url('index.php?page=queue&p=' . ($queuePage - 1))) ?>" class="px-3 py-1.5 text-[12px] font-semibold text-slate-700 bg-white border border-slate-200 rounded-md hover:bg-slate-50 transition-colors shadow-subtle btn-tactile active:scale-[0.98]">Previous</a>
                <?php else: ?>
                    <button disabled class="px-3 py-1.5 text-[12px] font-semibold text-slate-400 bg-slate-50 border border-slate-200/60 rounded-md cursor-not-allowed">Previous</button>
                <?php endif; ?>
                
                <div class="px-2.5 text-[12px] font-semibold text-slate-700 font-mono tabular-nums">
                    Page <?= $queuePage ?> / <?= $totalPages ?>
                </div>

                <?php if ($queuePage < $totalPages): ?>
                    <a href="<?= e(base_url('index.php?page=queue&p=' . ($queuePage + 1))) ?>" class="px-3 py-1.5 text-[12px] font-semibold text-slate-700 bg-white border border-slate-200 rounded-md hover:bg-slate-50 transition-colors shadow-subtle btn-tactile active:scale-[0.98]">Next</a>
                <?php else: ?>
                    <button disabled class="px-3 py-1.5 text-[12px] font-semibold text-slate-400 bg-slate-50 border border-slate-200/60 rounded-md cursor-not-allowed">Next</button>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </section>

