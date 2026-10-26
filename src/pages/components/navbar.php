<?php
$pageLabels = [
    'dashboard' => 'Overview',
    'device' => 'Device Management',
    'api-keys' => 'API Keys',
    'queue' => 'Message Queue',
    'contacts' => 'Contacts',
    'message' => 'New Message',
    'chatbot' => 'Chatbots',
    'webhooks' => 'Webhooks',
    'campaigns' => 'Campaigns',
    'audit' => 'Audit Logs',
    'security' => 'Security',
];
$title = $pageLabels[$page ?? ($_GET['page'] ?? 'dashboard')] ?? 'Overview';
?>
<!-- Top Navbar (High-Density Linear/Stripe Style) -->
<header class="h-14 bg-white/95 backdrop-blur-md border-b border-slate-200/80 flex items-center justify-between px-5 shrink-0 z-20 sticky top-0">
    <div class="flex items-center gap-3">
        <button id="openSidebar" class="md:hidden text-slate-500 hover:text-slate-900 p-1.5 rounded-md hover:bg-slate-100 transition-colors" aria-label="Open Navigation">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
        
        <!-- Breadcrumbs / Operational Status -->
        <div class="hidden sm:flex items-center text-[13px]">
            <span class="text-slate-500 hover:text-slate-800 font-medium transition-colors cursor-default">Admin Console</span>
            <svg class="w-3.5 h-3.5 text-slate-300 mx-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            <span class="font-semibold text-slate-900 tracking-tight"><?= e($title) ?></span>
            
            <!-- Real Operational State -->
            <span class="ml-3 px-2 py-0.5 text-[10px] font-bold bg-emerald-50/90 text-emerald-800 rounded-md border border-emerald-200/70 tracking-wide flex items-center gap-1.5 shadow-subtle">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 ring-2 ring-emerald-500/20"></span>
                <span>Active</span>
            </span>
        </div>
    </div>

    <div class="flex items-center gap-2.5 sm:gap-3">
        <!-- Command Search Box with Keyboard Shortcut -->
        <div class="hidden md:flex relative group">
            <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                <svg class="h-3.5 w-3.5 text-slate-400 group-focus-within:text-brand-wa_med transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input id="navbarSearchInput" type="text" class="block w-60 pl-8 pr-12 py-1.5 border border-slate-200/90 rounded-lg text-slate-800 placeholder:text-slate-400 bg-slate-50/70 hover:bg-slate-100/60 focus:bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500/30 focus:border-emerald-600 sm:text-[12px] font-medium transition-all shadow-subtle" placeholder="Search commands...">
            <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none">
                <kbd class="inline-flex items-center bg-white border border-slate-200 rounded px-1.5 text-[10px] font-mono font-semibold text-slate-400 shadow-sm">⌘K</kbd>
            </div>
        </div>

        <!-- Star on GitHub CTA -->
        <a href="https://github.com/navneetyadav-code/whatsapp-api" target="_blank" rel="noopener noreferrer" class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 text-[12px] font-semibold text-slate-700 bg-white hover:bg-slate-50 hover:text-slate-900 border border-slate-200/90 rounded-lg transition-all shadow-subtle btn-tactile" title="Star WhatsGateway on GitHub">
            <svg class="w-3.5 h-3.5 text-amber-500 fill-amber-400" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            <span class="tabular-nums">Star</span>
        </a>

        <!-- Divider -->
        <div class="h-4 w-px bg-slate-200 hidden sm:block mx-0.5"></div>
        
        <a href="<?= e(base_url('index.php?page=docs')) ?>" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-100 hidden sm:block transition-colors" title="Documentation">
            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
        </a>
        <button class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-100 relative transition-colors" title="Notifications">
            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
            <span class="absolute top-1.5 right-1.5 block h-1.5 w-1.5 rounded-full bg-emerald-500 ring-2 ring-white"></span>
        </button>
    </div>
</header>
