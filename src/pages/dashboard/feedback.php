<?php

declare(strict_types=1);

/**
 * View Partial: Customer Feedback & 5-Star Ratings
 */
?>

<!-- Page Header & Actions -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center gap-2.5">
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Customer Feedback</h1>
            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200/80 shadow-subtle flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> 5-Star Ratings
            </span>
        </div>
        <p class="text-[13px] text-slate-500 mt-0.5 font-medium">Automatic post-chat WhatsApp satisfaction ratings and reviews gathered upon closing conversations.</p>
    </div>

    <div class="flex items-center gap-2">
        <a href="<?= e(base_url('index.php?page=live-chat')) ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-900 text-white hover:bg-slate-800 transition-all shadow-xs">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
            <span>Live Chat Studio</span>
        </a>
    </div>
</div>

<!-- Telemetry Ribbon (KPI Scorecards) -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    
    <!-- KPI 1: Overall Average Rating -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-subtle p-4 flex flex-col justify-between">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-semibold text-slate-500 tracking-tight">Overall Rating</span>
            <span class="p-1.5 rounded-lg bg-amber-50 text-amber-600 border border-amber-100">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
            </span>
        </div>
        <div class="flex items-baseline gap-2">
            <span class="text-2xl font-bold font-mono text-slate-900 tracking-tight"><?= $feedbackTotal > 0 ? number_format($feedbackAvg, 1) : '—' ?></span>
            <span class="text-xs text-slate-400 font-mono">/ 5.0</span>
        </div>
        <div class="mt-2 flex items-center gap-1 text-amber-400">
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <?php if ($feedbackAvg >= $i): ?>
                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                <?php else: ?>
                    <svg class="w-3.5 h-3.5 text-slate-200 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                <?php endif; ?>
            <?php endfor; ?>
            <span class="text-[10px] text-slate-400 font-mono ml-1">(<?= number_format($feedbackTotal) ?> reviews)</span>
        </div>
    </div>

    <!-- KPI 2: Total Feedbacks Collected -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-subtle p-4 flex flex-col justify-between">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-semibold text-slate-500 tracking-tight">Total Feedbacks</span>
            <span class="p-1.5 rounded-lg bg-blue-50 text-blue-600 border border-blue-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
            </span>
        </div>
        <div class="text-2xl font-bold font-mono text-slate-900 tracking-tight"><?= number_format($feedbackTotal) ?></div>
        <div class="mt-2 text-[11px] text-slate-400 font-medium">Via Baileys auto-feedback loop</div>
    </div>

    <!-- KPI 3: 5-Star Reviews Ratio -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-subtle p-4 flex flex-col justify-between">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-semibold text-slate-500 tracking-tight">5-Star Excellence</span>
            <span class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100 font-mono text-xs font-bold"><?= $count5 ?></span>
        </div>
        <div class="text-2xl font-bold font-mono text-emerald-600 tracking-tight"><?= $pct5 ?>%</div>
        <div class="mt-2 w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
            <div class="bg-emerald-500 h-1.5 rounded-full" style="width: <?= $pct5 ?>%"></div>
        </div>
    </div>

    <!-- KPI 4: Positive Satisfaction (4-5 Stars) -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-subtle p-4 flex flex-col justify-between">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-semibold text-slate-500 tracking-tight">Satisfaction Rate</span>
            <span class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path></svg>
            </span>
        </div>
        <div class="text-2xl font-bold font-mono text-slate-900 tracking-tight"><?= $satisfactionRate ?>%</div>
        <div class="mt-2 text-[11px] text-slate-400 font-medium"><?= $positiveCount ?> of <?= $feedbackTotal ?> rated 4 or 5 stars</div>
    </div>
</div>

<!-- Filter Toolbar & Search -->
<div class="bg-white rounded-xl border border-slate-200/80 shadow-subtle p-3.5 mb-6">
    <form method="get" action="<?= e(base_url('index.php')) ?>" class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
        <input type="hidden" name="page" value="feedback">

        <div class="flex-1 flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
            <!-- Search Input -->
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" name="q" value="<?= e($feedbackQuery) ?>" placeholder="Search by name, mobile number, or message..." class="w-full pl-9 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:bg-white focus:outline-none focus:ring-1 focus:ring-slate-900 focus:border-slate-900 transition-all font-medium placeholder-slate-400">
            </div>

            <!-- Star Rating Dropdown -->
            <div class="w-full sm:w-44">
                <select name="rating" onchange="this.form.submit()" class="w-full py-1.5 px-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 focus:bg-white focus:outline-none focus:ring-1 focus:ring-slate-900">
                    <option value="">All Ratings (1-5★)</option>
                    <option value="5" <?= $filterRating === 5 ? 'selected' : '' ?>>⭐⭐⭐⭐⭐ 5 Stars (<?= $count5 ?>)</option>
                    <option value="4" <?= $filterRating === 4 ? 'selected' : '' ?>>⭐⭐⭐⭐ 4 Stars (<?= $count4 ?>)</option>
                    <option value="3" <?= $filterRating === 3 ? 'selected' : '' ?>>⭐⭐⭐ 3 Stars (<?= $count3 ?>)</option>
                    <option value="2" <?= $filterRating === 2 ? 'selected' : '' ?>>⭐⭐ 2 Stars (<?= $count2 ?>)</option>
                    <option value="1" <?= $filterRating === 1 ? 'selected' : '' ?>>⭐ 1 Star (<?= $count1 ?>)</option>
                </select>
            </div>
        </div>

        <!-- Submit & Reset Buttons -->
        <div class="flex items-center gap-2 shrink-0">
            <button type="submit" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-semibold shadow-xs transition-all active:scale-[0.98]">
                Filter
            </button>
            <?php if ($feedbackQuery !== '' || $filterRating !== null): ?>
                <a href="<?= e(base_url('index.php?page=feedback')) ?>" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-xs font-semibold transition-all">
                    Reset
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Feedback Table Section -->
<section class="bg-white rounded-xl border border-slate-200/80 shadow-card overflow-hidden mb-8">
    <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Feedback Records</h2>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-white border border-slate-200 text-slate-600"><?= number_format($totalFiltered) ?></span>
        </div>
        <span class="text-[11px] text-slate-400 font-medium">Page <?= $currentPageNum ?> of <?= max(1, $totalPages) ?></span>
    </div>

    <?php if (empty($feedbackRows)): ?>
        <div class="p-12 text-center select-none">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-100 mx-auto mb-3 flex items-center justify-center text-amber-500">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800 mb-1">No feedback entries found</h3>
            <p class="text-xs text-slate-400 max-w-sm mx-auto mb-4">
                <?= ($feedbackQuery !== '' || $filterRating !== null) 
                    ? 'No ratings matched your current search filters. Try adjusting or resetting filters.' 
                    : 'When you close a support conversation in Live Chat, WhatsApp automatically requests a 5-star rating from the customer.' ?>
            </p>
            <a href="<?= e(base_url('index.php?page=live-chat')) ?>" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-semibold shadow-xs transition-all">
                <span>Go to Live Chat Studio</span>
            </a>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 text-[11px] font-semibold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <th class="px-5 py-3 font-semibold">Customer</th>
                        <th class="px-5 py-3 font-semibold">Mobile Number</th>
                        <th class="px-5 py-3 font-semibold">Rating</th>
                        <th class="px-5 py-3 font-semibold">Feedback Message</th>
                        <th class="px-5 py-3 font-semibold">Date & Time</th>
                        <th class="px-5 py-3 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    <?php foreach ($feedbackRows as $row): 
                        $phoneFormatted = format_feedback_phone((string) $row['phone']);
                        $initials = feedback_initials($row['contact_name'], (string) $row['phone']);
                        $ratingVal = (int) $row['rating'];
                        $ratingVal = max(1, min(5, $ratingVal));
                        $timeAgo = feedback_time_ago((string) $row['created_at']);
                        $dateFormatted = date('M j, Y, g:i A', strtotime((string) $row['created_at']));
                    ?>
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            
                            <!-- Customer Name & Avatar -->
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-800 text-white font-bold text-xs flex items-center justify-center font-mono shrink-0 shadow-2xs">
                                        <?= e($initials) ?>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 truncate">
                                            <?= e(!empty($row['contact_name']) ? $row['contact_name'] : 'Customer') ?>
                                        </div>
                                        <div class="text-[11px] text-slate-400 font-mono">
                                            ID #<?= (int) $row['id'] ?>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Mobile Number -->
                            <td class="px-5 py-3.5 whitespace-nowrap font-mono text-slate-700 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <a href="https://wa.me/<?= e(preg_replace('/\D/', '', (string) $row['phone'])) ?>" target="_blank" rel="noopener noreferrer" class="hover:text-emerald-600 hover:underline inline-flex items-center gap-1" title="Open chat on WhatsApp Web">
                                        <span><?= e($phoneFormatted) ?></span>
                                        <svg class="w-3 h-3 text-slate-300 hover:text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                    </a>
                                </div>
                            </td>

                            <!-- 5-Star Rating Visual -->
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-0.5 text-amber-400">
                                        <?php for ($s = 1; $s <= 5; $s++): ?>
                                            <?php if ($s <= $ratingVal): ?>
                                                <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                            <?php else: ?>
                                                <svg class="w-4 h-4 text-slate-200 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                        <span class="ml-1 text-[11px] font-bold font-mono text-slate-700"><?= $ratingVal ?>.0</span>
                                    </div>
                                    <?php if ($ratingVal === 5): ?>
                                        <span class="inline-block px-1.5 py-0.2 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">5.0 • Excellent</span>
                                    <?php elseif ($ratingVal === 4): ?>
                                        <span class="inline-block px-1.5 py-0.2 rounded text-[10px] font-bold bg-blue-50 text-blue-800 border border-blue-200">4.0 • Good</span>
                                    <?php elseif ($ratingVal === 3): ?>
                                        <span class="inline-block px-1.5 py-0.2 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">3.0 • Average</span>
                                    <?php else: ?>
                                        <span class="inline-block px-1.5 py-0.2 rounded text-[10px] font-bold bg-rose-50 text-rose-800 border border-rose-200"><?= $ratingVal ?>.0 • Poor</span>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <!-- Feedback Message / Comments -->
                            <td class="px-5 py-3.5 max-w-md">
                                <?php if (!empty($row['message']) && trim((string) $row['message']) !== ''): ?>
                                    <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200/80 text-slate-800 text-xs leading-relaxed font-medium">
                                        <span class="text-slate-400 font-serif text-sm mr-0.5">“</span><?= e((string) $row['message']) ?><span class="text-slate-400 font-serif text-sm ml-0.5">”</span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-slate-400 italic text-[11px]">No written comment provided.</span>
                                <?php endif; ?>
                            </td>

                            <!-- Timestamp -->
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <div class="text-slate-900 font-medium"><?= e($dateFormatted) ?></div>
                                <div class="text-[11px] text-slate-400 font-mono"><?= e($timeAgo) ?></div>
                            </td>

                            <!-- Action: Jump to Live Chat -->
                            <td class="px-5 py-3.5 whitespace-nowrap text-right">
                                <a href="<?= e(base_url('index.php?page=live-chat&phone=' . urlencode((string) $row['phone']))) ?>" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors shadow-2xs" title="Open thread in Live Chat">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                    <span>Chat</span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <?php if ($totalPages > 1): ?>
            <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <span class="text-xs text-slate-500 font-medium">
                    Showing <?= $offset + 1 ?> to <?= min($totalFiltered, $offset + $perPage) ?> of <?= number_format($totalFiltered) ?> feedbacks
                </span>
                <div class="flex items-center gap-1.5">
                    <?php if ($currentPageNum > 1): ?>
                        <a href="<?= e(base_url('index.php?page=feedback&p=' . ($currentPageNum - 1) . ($feedbackQuery !== '' ? '&q=' . urlencode($feedbackQuery) : '') . ($filterRating !== null ? '&rating=' . $filterRating : ''))) ?>" class="px-2.5 py-1 rounded border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50">Previous</a>
                    <?php endif; ?>

                    <span class="px-2 py-1 text-xs font-mono font-bold text-slate-800">
                        <?= $currentPageNum ?> / <?= $totalPages ?>
                    </span>

                    <?php if ($currentPageNum < $totalPages): ?>
                        <a href="<?= e(base_url('index.php?page=feedback&p=' . ($currentPageNum + 1) . ($feedbackQuery !== '' ? '&q=' . urlencode($feedbackQuery) : '') . ($filterRating !== null ? '&rating=' . $filterRating : ''))) ?>" class="px-2.5 py-1 rounded border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>
