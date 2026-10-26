    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">API Request Audit Log</h1>
            <p class="text-[13px] text-slate-500 mt-0.5 font-medium">Forensic inspection of incoming HTTP requests, API key authentication, and rate limiting outcomes.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-white border border-slate-200/80 text-slate-700 shadow-subtle">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                <span>Audit Telemetry Active</span>
            </span>
        </div>
    </div>

    <!-- Integrated Telemetry Ribbon -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-subtle mb-6 overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-slate-100">
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500 tracking-tight">Total Recorded Requests</div>
                    <div class="text-xl font-bold text-slate-900 font-mono tabular-nums tracking-tight mt-0.5"><?= number_format($apiRequestCount) ?></div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Lifetime ingested traffic</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500 tracking-tight">Trailing 24-Hour Ingestion</div>
                    <div class="text-xl font-bold text-blue-600 font-mono tabular-nums tracking-tight mt-0.5">
                        <?php
                        $last24hStmt = $pdo->prepare('SELECT COUNT(*) FROM api_request_logs WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)');
                        $last24hStmt->execute([$user['id']]);
                        echo number_format((int) $last24hStmt->fetchColumn());
                        ?>
                    </div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Recent API calls</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
            </div>
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500 tracking-tight">Rate-Limited & Blocked</div>
                    <div class="text-xl font-bold text-rose-600 font-mono tabular-nums tracking-tight mt-0.5">
                        <?php
                        $blockedStmt = $pdo->prepare('SELECT COUNT(*) FROM api_request_logs WHERE user_id = ? AND status_code IN (401, 403, 429)');
                        $blockedStmt->execute([$user['id']]);
                        echo number_format((int) $blockedStmt->fetchColumn());
                        ?>
                    </div>
                    <div class="text-[11px] text-slate-400 mt-0.5">HTTP 401, 403, 429</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-600 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <section class="bg-white rounded-xl border border-slate-200/80 shadow-card overflow-hidden mb-8">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <h2 class="text-[14px] font-bold text-slate-900 tracking-tight">Request Stream Ledger</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50/70 text-[11px] font-semibold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <th class="px-5 py-3 font-semibold">Response Status</th>
                        <th class="px-5 py-3 font-semibold">API Endpoint</th>
                        <th class="px-5 py-3 font-semibold">Credentials Token</th>
                        <th class="px-5 py-3 font-semibold">Client Remote IP</th>
                        <th class="px-5 py-3 font-semibold text-right">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($auditLogs as $log): ?>
                        <tr class="hover:bg-slate-50/60 transition-colors group">
                            <td class="px-5 py-3">
                                <?php if ($log['status_code'] >= 200 && $log['status_code'] < 300): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60 shadow-subtle font-mono tabular-nums">
                                        <?= (int) $log['status_code'] ?> OK
                                    </span>
                                <?php elseif ($log['status_code'] == 429): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200/60 shadow-subtle font-mono tabular-nums">
                                        <?= (int) $log['status_code'] ?> Rate Limit
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200/60 shadow-subtle font-mono tabular-nums" title="<?= e($log['error_message'] ?? '') ?>">
                                        <?= (int) $log['status_code'] ?> Error
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-3 font-mono text-[12px] text-slate-700 font-semibold">
                                /api/<?= e($log['endpoint']) ?>
                            </td>
                            <td class="px-5 py-3">
                                <?php if ($log['key_name']): ?>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[12px] font-medium text-slate-800"><?= e($log['key_name']) ?></span>
                                        <span class="text-[10px] bg-slate-100 border border-slate-200/80 text-slate-500 px-1.5 rounded font-mono tabular-nums"><?= e($log['key_prefix']) ?>...</span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-slate-400 italic text-[11px]">Unauthenticated / Invalid</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-3">
                                <span class="font-mono text-[11px] text-slate-600 bg-slate-50 border border-slate-200/60 px-1.5 py-0.5 rounded shadow-subtle tabular-nums"><?= e($log['ip_address']) ?></span>
                            </td>
                            <td class="px-5 py-3 text-right text-slate-500 text-[12px] font-mono tabular-nums group-hover:text-slate-800 transition-colors">
                                <?= e(date('M j, Y H:i:s', strtotime($log['created_at']))) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$auditLogs): ?>
                        <tr><td colspan="5" class="px-5 py-12 text-center text-[13px] text-slate-500">No API activity logged yet. Incoming API requests will appear here.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($totalAuditPages > 1): ?>
        <div class="px-5 py-3.5 border-t border-slate-100 flex items-center justify-between bg-slate-50/50">
            <span class="text-[12px] text-slate-500 font-medium font-mono tabular-nums">Page <?= $auditPage ?> / <?= $totalAuditPages ?></span>
            <div class="flex gap-1.5">
                <?php if ($auditPage > 1): ?>
                    <a href="?page=audit&p=<?= $auditPage - 1 ?>" class="px-3 py-1.5 text-[12px] font-semibold text-slate-700 bg-white border border-slate-200 rounded-md hover:bg-slate-50 shadow-subtle transition-colors btn-tactile active:scale-[0.98]">Previous</a>
                <?php endif; ?>
                <?php if ($auditPage < $totalAuditPages): ?>
                    <a href="?page=audit&p=<?= $auditPage + 1 ?>" class="px-3 py-1.5 text-[12px] font-semibold text-slate-700 bg-white border border-slate-200 rounded-md hover:bg-slate-50 shadow-subtle transition-colors btn-tactile active:scale-[0.98]">Next</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </section>
    <div class="h-8"></div>

