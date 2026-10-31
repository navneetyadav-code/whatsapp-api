<?php
$pageLabels = [
    'dashboard'   => 'Overview',
    'live-chat'   => 'Live Chat',
    'device'      => 'Device Management',
    'api-keys'    => 'API Keys',
    'queue'       => 'Message Queue',
    'contacts'    => 'Contacts',
    'feedback'    => 'Customer Feedback',
    'message'     => 'New Message',
    'chatbot'     => 'AI Agent Studio',
    'ai-agent'    => 'AI Agent Studio',
    'knowledge'   => 'Company Knowledge Base (RAG)',
    'tools'       => 'Installed Tools & RBAC',
    'app-store'   => 'App Store & Presets Catalog',
    'presets'     => 'App Store & Presets Catalog',
    'escalations' => 'Escalations & Alarms',
    'rules'       => 'Keyword Trigger Rules',
    'webhooks'    => 'Webhooks',
    'campaigns'   => 'Campaigns',
    'audit'       => 'Audit Logs',
    'security'    => 'Security',
];
$title = $pageLabels[$page ?? ($_GET['page'] ?? 'dashboard')] ?? 'Overview';
?>
<!-- Top Navbar -->
<header class="h-[68px] bg-white/95 backdrop-blur-md border-b border-slate-200/80 flex items-center justify-between px-4 sm:px-6 lg:px-8 shrink-0 z-20 sticky top-0">
    <div class="flex items-center gap-2.5">
        <!-- Mobile navigation drawer toggle -->
        <button id="openSidebar" type="button" class="md:hidden text-slate-500 hover:text-slate-900 p-1.5 rounded-lg hover:bg-slate-100 transition-colors cursor-pointer" aria-label="Open navigation" title="Open navigation">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>

        <!-- Current View Title / Breadcrumb -->
        <div class="flex items-center gap-2">
            <span class="text-[14px] sm:text-[15px] font-semibold text-slate-800 tracking-[-0.01em]"><?= e($title) ?></span>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <!-- Quick Link: Live Chat -->
        <?php if (empty($isPersonalMode)): ?>
        <a id="navbarLiveChatLink" href="<?= e(base_url('index.php?page=live-chat')) ?>" class="inline-flex items-center gap-1.5 px-2.5 py-2 text-xs font-semibold text-slate-700 bg-slate-50 hover:bg-slate-100 hover:text-slate-900 rounded-lg border border-slate-200/80 transition-all shadow-subtle btn-tactile" title="Open Live Chat">
            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
            <span class="hidden sm:inline">Live Chat</span>
        </a>
        <?php endif; ?>

        <!-- Quick Link: API Docs -->
        <a href="<?= e(base_url('index.php?page=docs')) ?>" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-100 transition-colors" title="API Documentation">
            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
        </a>

        <!-- User Identity Ribbon Mini -->
        <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
            <div class="w-7 h-7 rounded-lg bg-slate-900 text-white flex items-center justify-center text-[10px] font-bold font-mono shadow-xs">
                <?= e($initials ?? 'AD') ?>
            </div>
            <span class="hidden lg:inline text-xs font-semibold text-slate-800"><?= e($userForLayout['name'] ?? 'Admin') ?></span>
        </div>
    </div>
</header>
