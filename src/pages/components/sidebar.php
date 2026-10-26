<?php
$currentPage = $page ?? ($_GET['page'] ?? 'dashboard');
?>
<!-- Sidebar (High-Density Modern Rail) -->
<aside id="sidebar" class="bg-[#F8FAFC] border-r border-slate-200/80 text-slate-600 w-64 flex-shrink-0 flex flex-col transition-transform duration-300 z-30 absolute inset-y-0 left-0 transform -translate-x-full md:relative md:translate-x-0 select-none">
    
    <!-- Sidebar Header (Original Logo & Brand, No Version Badge) -->
    <div class="h-14 flex items-center px-4 shrink-0 border-b border-slate-200/60">
        <a href="<?= e(base_url('index.php?page=dashboard')) ?>" class="flex items-center gap-2.5 text-slate-900 group">
            <img src="<?= e(base_url('assets/logo.jpg')) ?>" alt="WhatsGateway Logo" class="w-6 h-6 rounded-lg object-cover shadow-sm border border-slate-200/50">
            <span class="font-bold tracking-tight text-[15px] text-slate-900">WhatsGateway</span>
        </a>
        
        <button id="closeSidebar" class="md:hidden ml-auto text-slate-400 hover:text-slate-900 p-1" aria-label="Close Sidebar">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>

    <!-- Sidebar Navigation Items -->
    <div class="flex-1 overflow-y-auto py-3 px-2.5 space-y-0.5 custom-scrollbar font-medium text-[13px]">
        
        <!-- Section: Core -->
        <div class="px-2.5 mt-1.5 mb-1 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Core</div>
        
        <a href="<?= e(base_url('index.php?page=dashboard')) ?>" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg transition-all <?= $currentPage === 'dashboard' ? 'bg-white shadow-card text-slate-900 font-semibold border border-slate-200/80' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' ?>">
            <svg class="w-4 h-4 <?= $currentPage === 'dashboard' ? 'text-emerald-600' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <span>Overview</span>
        </a>
        
        <a href="<?= e(base_url('index.php?page=message')) ?>" class="flex items-center justify-between px-2.5 py-1.5 rounded-lg transition-all <?= $currentPage === 'message' ? 'bg-white shadow-card text-slate-900 font-semibold border border-slate-200/80' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' ?>">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 <?= $currentPage === 'message' ? 'text-emerald-600' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                <span>Messaging</span>
            </div>
            <span class="text-[10px] font-mono font-medium text-slate-400 px-1 py-0.2 bg-slate-100 rounded">REST</span>
        </a>

        <a href="<?= e(base_url('index.php?page=contacts')) ?>" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg transition-all <?= $currentPage === 'contacts' ? 'bg-white shadow-card text-slate-900 font-semibold border border-slate-200/80' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' ?>">
            <svg class="w-4 h-4 <?= $currentPage === 'contacts' ? 'text-emerald-600' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            <span>Contacts</span>
        </a>

        <!-- Section: Gateway -->
        <div class="px-2.5 mt-4 mb-1 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Gateway</div>
        
        <a href="<?= e(base_url('index.php?page=device')) ?>" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg transition-all <?= $currentPage === 'device' ? 'bg-white shadow-card text-slate-900 font-semibold border border-slate-200/80' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' ?>">
            <svg class="w-4 h-4 <?= $currentPage === 'device' ? 'text-emerald-600' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
            <span>Device</span>
        </a>

        <a href="<?= e(base_url('index.php?page=queue')) ?>" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg transition-all <?= $currentPage === 'queue' ? 'bg-white shadow-card text-slate-900 font-semibold border border-slate-200/80' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' ?>">
            <svg class="w-4 h-4 <?= $currentPage === 'queue' ? 'text-emerald-600' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <span>Message Queue</span>
        </a>

        <a href="<?= e(base_url('index.php?page=campaigns')) ?>" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg transition-all <?= $currentPage === 'campaigns' ? 'bg-white shadow-card text-slate-900 font-semibold border border-slate-200/80' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' ?>">
            <svg class="w-4 h-4 <?= $currentPage === 'campaigns' ? 'text-emerald-600' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
            <span>Campaigns</span>
        </a>

        <a href="<?= e(base_url('index.php?page=chatbot')) ?>" class="flex items-center justify-between px-2.5 py-1.5 rounded-lg transition-all <?= $currentPage === 'chatbot' ? 'bg-white shadow-card text-slate-900 font-semibold border border-slate-200/80' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' ?>">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 <?= $currentPage === 'chatbot' ? 'text-emerald-600' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                <span>Chatbots</span>
            </div>
            <span class="px-1.5 py-0.2 rounded bg-emerald-50 text-emerald-700 text-[10px] font-semibold border border-emerald-200/60">Auto</span>
        </a>

        <!-- Section: Developer -->
        <div class="px-2.5 mt-4 mb-1 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Developer</div>
        
        <a href="<?= e(base_url('index.php?page=api-keys')) ?>" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg transition-all <?= $currentPage === 'api-keys' ? 'bg-white shadow-card text-slate-900 font-semibold border border-slate-200/80' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' ?>">
            <svg class="w-4 h-4 <?= $currentPage === 'api-keys' ? 'text-emerald-600' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
            <span>API Keys</span>
        </a>

        <a href="<?= e(base_url('index.php?page=webhooks')) ?>" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg transition-all <?= $currentPage === 'webhooks' ? 'bg-white shadow-card text-slate-900 font-semibold border border-slate-200/80' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' ?>">
            <svg class="w-4 h-4 <?= $currentPage === 'webhooks' ? 'text-emerald-600' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            <span>Webhooks</span>
        </a>

        <a href="<?= e(base_url('index.php?page=audit')) ?>" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg transition-all <?= $currentPage === 'audit' ? 'bg-white shadow-card text-slate-900 font-semibold border border-slate-200/80' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' ?>">
            <svg class="w-4 h-4 <?= $currentPage === 'audit' ? 'text-emerald-600' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <span>Audit Logs</span>
        </a>

        <a href="<?= e(base_url('index.php?page=security')) ?>" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg transition-all <?= $currentPage === 'security' ? 'bg-white shadow-card text-slate-900 font-semibold border border-slate-200/80' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' ?>">
            <svg class="w-4 h-4 <?= $currentPage === 'security' ? 'text-emerald-600' : 'text-slate-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            <span>Security</span>
        </a>
    </div>

    <!-- Sidebar Footer -->
    <div class="p-2.5 border-t border-slate-200/70 bg-[#F8FAFC] shrink-0 space-y-2">
        <a href="<?= e(base_url('index.php?page=docs')) ?>" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg hover:bg-slate-100/80 text-slate-600 hover:text-slate-900 transition-colors font-medium text-[12px]">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            <span>API Docs</span>
        </a>

        <!-- User Identity Ribbon -->
        <div class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg bg-white border border-slate-200/80 shadow-subtle relative group">
            <div class="w-6 h-6 rounded-md bg-slate-900 text-white flex items-center justify-center text-[10px] font-bold font-mono"><?= e($initials ?? 'AD') ?></div>
            <div class="flex-1 truncate">
                <p class="text-[12px] font-semibold text-slate-900 truncate leading-tight"><?= e($userForLayout['name'] ?? 'Admin') ?></p>
                <p class="text-[10px] text-slate-400 truncate font-mono">Owner</p>
            </div>
            
            <form method="post" action="<?= e(base_url('index.php?page=logout')) ?>" class="shrink-0" title="Sign out">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <button type="submit" class="text-slate-400 hover:text-rose-600 p-1 rounded hover:bg-slate-100 transition-colors" aria-label="Sign out">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </button>
            </form>
        </div>

        <!-- Legal & Attribution Footer -->
        <div class="px-2 pt-1 text-[10px] text-slate-400 border-t border-slate-200/60 space-y-0.5 leading-tight">
            <div class="flex items-center gap-2 pb-0.5 text-slate-500 font-medium">
                <a href="<?= e(base_url('privacy')) ?>" class="hover:text-slate-800 hover:underline">Privacy</a>
                <span>&bull;</span>
                <a href="<?= e(base_url('terms')) ?>" class="hover:text-slate-800 hover:underline">Terms</a>
                <span>&bull;</span>
                <a href="<?= e(base_url('docs')) ?>" class="hover:text-slate-800 hover:underline">Docs</a>
            </div>
            <div>Made by <a href="https://github.com/navneetyadav-code/whatsapp-api" target="_blank" rel="noopener noreferrer" class="font-semibold text-slate-700 hover:underline">Navneet Kumar Yadav</a></div>
            <div class="text-[9px] text-slate-400">Independent &bull; Not affiliated with Meta</div>
        </div>
    </div>
</aside>
