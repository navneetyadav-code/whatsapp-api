<!-- Knowledge Base (RAG) Studio View -->
<div class="space-y-6 mb-8">
    <!-- Header with Breadcrumb and Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200/80">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-800 border border-emerald-200/80">
                    Zero-Cloud RAG
                </span>
                <span class="text-xs text-slate-400 font-mono">BM25 Hybrid Retrieval</span>
            </div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Company Knowledge Base</h2>
            <p class="text-xs text-slate-500 mt-0.5">Verified company knowledge chunks used by the Business Support Bot to answer customer inquiries with 100% factual accuracy.</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="openAddChunkModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-xs transition-all active:scale-[0.98]">
                <span>+ Add Document Chunk</span>
            </button>
        </div>
    </div>

    <!-- Quick Stats Metric Ribbon -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
        <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-subtle flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Indexed Documents</p>
                <p class="text-2xl font-bold text-slate-900 font-mono mt-0.5"><?= $totalChunks ?></p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-emerald-50 border border-emerald-200/60 flex items-center justify-center text-emerald-700 text-lg">
                📚
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-subtle flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Characters</p>
                <p class="text-2xl font-bold text-slate-900 font-mono mt-0.5"><?= number_format($totalChars) ?></p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-indigo-50 border border-indigo-200/60 flex items-center justify-center text-indigo-700 text-lg">
                🔤
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-subtle flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Avg Chunk Size</p>
                <p class="text-2xl font-bold text-slate-900 font-mono mt-0.5"><?= number_format($avgChars) ?> <span class="text-xs font-sans text-slate-400 font-normal">chars</span></p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-amber-50 border border-amber-200/60 flex items-center justify-center text-amber-700 text-lg">
                📊
            </div>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-subtle flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <form method="get" action="<?= e(base_url('index.php')) ?>" class="flex-1 flex items-center gap-2">
            <input type="hidden" name="page" value="knowledge">
            <div class="relative flex-1">
                <input type="text" name="q" value="<?= e($searchQuery) ?>" placeholder="Search knowledge articles by title or keyword..." class="w-full text-xs pl-8 pr-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <button type="submit" class="px-3.5 py-2 text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white rounded-lg transition-colors shadow-xs">
                Search
            </button>
            <?php if ($searchQuery !== ''): ?>
                <a href="<?= e(base_url('index.php?page=knowledge')) ?>" class="px-3 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                    Clear
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Knowledge Chunks Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($knowledgeChunks as $chunk): 
            $charLen = mb_strlen((string) $chunk['content']);
        ?>
            <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-card hover:border-slate-300 transition-all flex flex-col justify-between" id="chunk-card-<?= (int) $chunk['id'] ?>">
                <div>
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200/80 font-mono">
                            ID: #<?= (int) $chunk['id'] ?>
                        </span>
                        <span class="text-[11px] font-mono text-slate-400"><?= $charLen ?> chars</span>
                    </div>

                    <h4 class="text-sm font-bold text-slate-900 mb-2 line-clamp-1 chunk-title" title="<?= e($chunk['title']) ?>">
                        <?= e($chunk['title']) ?>
                    </h4>

                    <div class="text-xs text-slate-600 line-clamp-4 leading-relaxed font-sans bg-slate-50/60 rounded-lg p-2.5 border border-slate-100 mb-3 chunk-content">
                        <?= nl2br(e($chunk['content'])) ?>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-[11px] text-slate-400 font-mono"><?= date('M j, Y', strtotime($chunk['created_at'] ?? 'now')) ?></span>
                    <div class="flex items-center gap-1.5">
                        <button type="button" onclick="openEditChunkModal(<?= (int) $chunk['id'] ?>, <?= htmlspecialchars(json_encode($chunk['title']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($chunk['content']), ENT_QUOTES) ?>)" class="px-2.5 py-1 text-xs font-semibold text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-md transition-colors">
                            Edit
                        </button>
                        <form method="post" action="<?= e(base_url('index.php?page=ai-knowledge-delete')) ?>" onsubmit="return confirm('Delete this knowledge document?');" class="inline">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="id" value="<?= (int) $chunk['id'] ?>">
                            <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 rounded hover:bg-rose-50 transition-colors" title="Delete chunk">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (!$knowledgeChunks): ?>
        <div class="bg-white rounded-xl border border-slate-200/80 p-12 text-center shadow-card">
            <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-xl mx-auto mb-3">📚</div>
            <h3 class="text-sm font-bold text-slate-900 mb-1">No Knowledge Documents Found</h3>
            <p class="text-xs text-slate-500 max-w-sm mx-auto mb-4">Add your business services, FAQs, working hours, pricing, and policies so your Business Customer Support Bot can answer accurately.</p>
            <button type="button" onclick="openAddChunkModal()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-lg shadow-xs transition-all">
                + Add First Document Chunk
            </button>
        </div>
    <?php endif; ?>
</div>

<!-- MODAL: ADD / EDIT KNOWLEDGE CHUNK -->
<div id="chunkModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <h3 id="chunkModalTitle" class="text-base font-bold text-slate-900">Add Knowledge Chunk</h3>
            <button type="button" onclick="closeChunkModal()" class="text-slate-400 hover:text-slate-700 text-lg">✕</button>
        </div>

        <form id="chunkForm" method="post" action="<?= e(base_url('index.php?page=ai-knowledge-create')) ?>" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="id" id="modalChunkId" value="0">

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Document Title / Topic *</label>
                <input type="text" name="title" id="modalChunkTitle" required placeholder="e.g. Return & Refund Policy, Pricing Plans 2026, Office Working Hours" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Knowledge Content (Text / Q&A / FAQs) *</label>
                <textarea name="content" id="modalChunkContent" rows="7" required placeholder="Provide concise, accurate facts, bullet points, policies, or specific questions and answers..." class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 resize-y leading-relaxed font-sans"></textarea>
                <p class="text-[11px] text-slate-400 mt-1">BM25 hybrid search retrieves the top 3 most relevant chunks automatically when customers inquire.</p>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeChunkModal()" class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-lg shadow-xs transition-all active:scale-[0.98]">
                    Save Document
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddChunkModal() {
        document.getElementById('chunkModalTitle').innerText = 'Add Knowledge Chunk';
        document.getElementById('chunkForm').action = '<?= e(base_url('index.php?page=ai-knowledge-create')) ?>';
        document.getElementById('modalChunkId').value = '0';
        document.getElementById('modalChunkTitle').value = '';
        document.getElementById('modalChunkContent').value = '';
        document.getElementById('chunkModal').classList.remove('hidden');
        document.getElementById('modalChunkTitle').focus();
    }

    function openEditChunkModal(id, title, content) {
        document.getElementById('chunkModalTitle').innerText = 'Edit Knowledge Chunk';
        document.getElementById('chunkForm').action = '<?= e(base_url('index.php?page=ai-knowledge-update')) ?>';
        document.getElementById('modalChunkId').value = id;
        document.getElementById('modalChunkTitle').value = title;
        document.getElementById('modalChunkContent').value = content;
        document.getElementById('chunkModal').classList.remove('hidden');
        document.getElementById('modalChunkTitle').focus();
    }

    function closeChunkModal() {
        document.getElementById('chunkModal').classList.add('hidden');
    }
</script>
