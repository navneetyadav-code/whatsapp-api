<?php
$userForLayout = current_user();
$isAuthed = (bool) $userForLayout;
$currentPage = $page ?? ($_GET['page'] ?? 'dashboard');
$seo = seo_meta($currentPage);
$isPublicDocs = !$isAuthed && $currentPage === 'docs';
$initials = 'AD';
$agentMode = 'support';
if ($userForLayout) {
    $parts = preg_split('/\s+/', trim((string) $userForLayout['name'])) ?: [];
    $initials = strtoupper(substr($parts[0] ?? 'A', 0, 1) . substr($parts[1] ?? 'D', 0, 1));
    try {
        $modeStmt = Database::pdo()->prepare('SELECT agent_mode FROM ai_agent_settings WHERE user_id = ? LIMIT 1');
        $modeStmt->execute([(int) $userForLayout['id']]);
        $agentMode = (string) ($modeStmt->fetchColumn() ?: 'support');
    } catch (\Throwable $e) {}
}
$isPersonalMode = ($agentMode === 'personal');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, interactive-widget=resizes-content">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($seo['title']) ?></title>
    <meta name="description" content="<?= e($seo['description']) ?>">
    <meta name="keywords" content="<?= e($seo['keywords']) ?>">
    <meta name="author" content="Navneet Kumar Yadav">
    <meta name="robots" content="<?= e($seo['robots']) ?>">
    <link rel="canonical" href="<?= e($seo['canonical']) ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= e(base_url('assets/favicon-32x32.png')) ?>">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= e(base_url('assets/favicon.png')) ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= e(base_url('assets/apple-touch-icon.png')) ?>">
    <link rel="shortcut icon" href="<?= e(base_url('favicon.ico')) ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= e($seo['canonical']) ?>">
    <meta property="og:title" content="<?= e($seo['title']) ?>">
    <meta property="og:description" content="<?= e($seo['description']) ?>">
    <meta property="og:site_name" content="WhatsGateway">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($seo['title']) ?>">
    <meta name="twitter:description" content="<?= e($seo['description']) ?>">

    <!-- Schema.org JSON-LD Structured Data -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "SoftwareApplication",
      "name": "WhatsGateway",
      "applicationCategory": "DeveloperApplication",
      "operatingSystem": "Linux, Windows, macOS, Docker",
      "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "USD"
      },
      "author": {
        "@type": "Person",
        "name": "Navneet Kumar Yadav",
        "url": "https://github.com/navneetyadav-code/whatsapp-api"
      },
      "description": "Free open-source WhatsApp API Gateway and Dashboard powered by Baileys, PHP, Node.js, and MySQL."
    }
    </script>
    
    <!-- Old CSS for backward compatibility of non-dashboard pages -->
    <link rel="stylesheet" href="<?= e(base_url('assets/app.css')) ?>?v=<?= time() ?>">
    
    <?php if ($isAuthed): ?>
    <!-- Premium Fonts: Plus Jakarta Sans (Interface) & JetBrains Mono (Technical Data) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/css/intlTelInput.css">
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/js/intlTelInput.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'ui-monospace', 'SFMono-Regular', 'Menlo', 'Monaco', 'Consolas', 'monospace'],
                    },
                    colors: {
                        brand: {
                            wa_dark: '#075E54', 
                            wa_med: '#128C7E',  
                            wa_light: '#25D366',
                            accent: '#0B1120'
                        }
                    },
                    boxShadow: {
                        'subtle': '0 1px 2px 0 rgba(0, 0, 0, 0.03)',
                        'card': '0 0 0 1px rgba(15, 23, 42, 0.06), 0 1px 2px 0 rgba(15, 23, 42, 0.03)',
                        'card-hover': '0 0 0 1px rgba(15, 23, 42, 0.1), 0 4px 12px 0 rgba(15, 23, 42, 0.04)',
                        'elevated': '0 0 0 1px rgba(15, 23, 42, 0.08), 0 10px 25px -5px rgba(15, 23, 42, 0.08)',
                        'saas': '0 0 0 1px rgba(15, 23, 42, 0.05), 0 1px 2px 0 rgba(15, 23, 42, 0.03)',
                        'saas-hover': '0 0 0 1px rgba(15, 23, 42, 0.09), 0 3px 8px -1px rgba(15, 23, 42, 0.04)',
                    }
                }
            }
        };
        window.AppConfig = {
            defaultCountryCode: "<?= e(app_config('app.default_country_code')) ?>" || "in",
            defaultDialCode: "<?= e(default_country_dial_code()) ?>",
            baseUrl: "<?= e(base_url('')) ?>",
            apiBase: "<?= e(base_url('api/chat')) ?>",
            agentMode: "<?= e($agentMode) ?>"
        };
    </script>
    <style>
        .iti { width: 100%; }
        body {
            background-color: #FBFBFC;
            color: #0F172A;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            font-feature-settings: 'cv02', 'cv03', 'cv04', 'cv11';
        }
        .tabular-nums {
            font-variant-numeric: tabular-nums;
        }
        ::selection {
            background: #d1fae5;
            color: #064e3b;
        }
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94A3B8; }
        @keyframes growUp { from { height: 0; opacity: 0; } to { opacity: 1; } }
        .animate-grow { animation: growUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .glass-header {
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }
        /* Focus ring & micro-tactile states */
        .btn-tactile:active {
            transform: scale(0.98);
        }
        /* Universal Button Reset: Prevents legacy app.css green border pollution across dashboard */
        button {
            font-family: inherit;
        }
        button:not([class*="border"]):not(.button):not(.ghost):not(.icon-square):not(.btn-legacy) {
            border: none;
            box-shadow: none;
            min-height: unset;
        }
        /* Mobile keyboard, auto-zoom, and layout shift fixes */
        @media screen and (max-width: 768px) {
            input, textarea, select, .wa-editor {
                font-size: 16px !important;
            }
            html, body {
                height: 100%;
                height: 100dvh;
                overflow-x: hidden;
            }
        }
        .sidebar-scroll { scrollbar-width: none; -ms-overflow-style: none; }
        .sidebar-scroll::-webkit-scrollbar { display: none; }
        .sidebar-category + .sidebar-category { margin-top: 24px; }
        .sidebar-category-title {
            color: #64748b;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 0.08em;
            line-height: 1;
            margin: 0 10px 8px;
            text-transform: uppercase;
        }
        .sidebar-link {
            align-items: center;
            border: 1px solid transparent;
            border-radius: 9px;
            color: #64748b;
            display: flex;
            gap: 11px;
            min-height: 38px;
            padding: 8px 10px;
            transition: background-color 140ms ease, border-color 140ms ease, color 140ms ease;
        }
        .sidebar-link:hover { background: #f1f5f9; color: #0f172a; }
        .sidebar-link.is-active {
            background: #ffffff;
            border-color: #e2e8f0;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            color: #0f172a;
            font-weight: 700;
        }
        .sidebar-icon { color: #94a3b8; flex: 0 0 auto; height: 17px; width: 17px; }
        .sidebar-link.is-active .sidebar-icon { color: #059669; }
        .sidebar-item-content { align-items: center; display: flex; gap: 11px; min-width: 0; }
        .sidebar-badge {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            color: #64748b;
            font-family: 'JetBrains Mono', monospace;
            font-size: 9px;
            font-weight: 700;
            line-height: 1.4;
            padding: 2px 5px;
            margin-left: auto;
            flex-shrink: 0;
        }
        .sidebar-badge-live { background: #ecfdf5; border-color: #bbf7d0; color: #047857; }
        .sidebar-badge-amber { background: #fffbeb; border-color: #fde68a; color: #b45309; }
        .sidebar-badge-indigo { background: #eef2ff; border-color: #c7d2fe; color: #4338ca; }
        .sidebar-badge-emerald { background: #ecfdf5; border-color: #a7f3d0; color: #047857; }
        .sidebar-badge-fuchsia { background: #fdf4ff; border-color: #f5d0fe; color: #a21caf; }
        .sidebar-badge-muted { background: #f1f5f9; border-color: #e2e8f0; color: #64748b; }
        .shadow-subtle { box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04); }
        .sidebar-link:focus-visible,
        .sidebar-sublink:focus-visible,
        .sidebar-group-btn:focus-visible,
        .sidebar-flyout-link:focus-visible {
            outline: 2px solid #10b981;
            outline-offset: 1px;
        }

        /* Sidebar Dropdown Groups & Sublinks */
        .sidebar-group {
            position: relative;
        }
        .sidebar-group:not(.is-open) .sidebar-group-content {
            display: none;
        }
        .sidebar-group.is-open .sidebar-chevron-down {
            transform: rotate(180deg);
            color: #059669;
        }
        .sidebar-group.is-open > .sidebar-group-btn,
        .sidebar-group-btn.group-active {
            color: #0f172a;
            font-weight: 600;
        }
        .sidebar-group-btn.group-active .sidebar-icon {
            color: #059669;
        }
        .sidebar-sublink {
            display: flex;
            align-items: center;
            gap: 8px;
            min-height: 31px;
            padding: 5px 9px;
            border-radius: 7px;
            color: #64748b;
            font-size: 12px;
            font-weight: 500;
            border: 1px solid transparent;
            transition: all 120ms ease;
        }
        .sidebar-sublink:hover {
            background: #f1f5f9;
            color: #0f172a;
        }
        .sidebar-sublink.is-active {
            background: #ffffff;
            color: #047857;
            font-weight: 700;
            border-color: #e2e8f0;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }
        .sidebar-sublink.is-active .sidebar-subicon {
            color: #059669;
        }
        .sidebar-subicon {
            width: 14px;
            height: 14px;
            color: #94a3b8;
            flex-shrink: 0;
            transition: color 120ms ease;
        }
        .sidebar-sublink:hover .sidebar-subicon {
            color: #334155;
        }
        .sidebar-sublink .sidebar-item-content {
            gap: 8px;
        }

        /* Collapsible Sidebar Rail Engine */
        #sidebar {
            transition: width 200ms cubic-bezier(0.4, 0, 0.2, 1), transform 200ms ease;
            will-change: width, transform;
        }
        #sidebar.sidebar-collapsed,
        html.preload-sidebar-collapsed #sidebar {
            width: 72px !important;
            min-width: 72px !important;
        }
        #sidebar.sidebar-collapsed .sidebar-brand-text,
        html.preload-sidebar-collapsed #sidebar .sidebar-brand-text,
        #sidebar.sidebar-collapsed .sidebar-category-title,
        html.preload-sidebar-collapsed #sidebar .sidebar-category-title,
        #sidebar.sidebar-collapsed .sidebar-label,
        html.preload-sidebar-collapsed #sidebar .sidebar-label,
        #sidebar.sidebar-collapsed .sidebar-badge,
        html.preload-sidebar-collapsed #sidebar .sidebar-badge,
        #sidebar.sidebar-collapsed .sidebar-group-meta,
        html.preload-sidebar-collapsed #sidebar .sidebar-group-meta,
        #sidebar.sidebar-collapsed .sidebar-user-meta,
        html.preload-sidebar-collapsed #sidebar .sidebar-user-meta,
        #sidebar.sidebar-collapsed .sidebar-user-logout,
        html.preload-sidebar-collapsed #sidebar .sidebar-user-logout {
            display: none !important;
        }
        #sidebar.sidebar-collapsed .sidebar-category + .sidebar-category,
        html.preload-sidebar-collapsed #sidebar .sidebar-category + .sidebar-category {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
        }
        #sidebar.sidebar-collapsed .sidebar-header,
        html.preload-sidebar-collapsed #sidebar .sidebar-header {
            padding: 0;
            justify-content: center;
        }
        #sidebar.sidebar-collapsed .sidebar-brand,
        html.preload-sidebar-collapsed #sidebar .sidebar-brand {
            margin: 0 auto;
            justify-content: center;
        }
        #sidebar.sidebar-collapsed #collapseSidebar,
        html.preload-sidebar-collapsed #sidebar #collapseSidebar {
            position: absolute;
            right: -13px;
            top: 21px;
            width: 26px;
            height: 26px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 9999px;
            box-shadow: 0 2px 5px rgba(15, 23, 42, 0.08);
            z-index: 40;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #475569;
            transition: all 150ms ease;
        }
        #sidebar.sidebar-collapsed #collapseSidebar:hover,
        html.preload-sidebar-collapsed #sidebar #collapseSidebar:hover {
            background: #f8fafc;
            color: #0f172a;
            border-color: #94a3b8;
            transform: scale(1.08);
        }
        #sidebar.sidebar-collapsed #collapseSidebar svg,
        html.preload-sidebar-collapsed #sidebar #collapseSidebar svg {
            width: 13px;
            height: 13px;
        }
        #sidebar.sidebar-collapsed .sidebar-link,
        html.preload-sidebar-collapsed #sidebar .sidebar-link {
            justify-content: center;
            padding: 8px 0;
            width: 44px;
            margin: 2px auto;
        }
        #sidebar.sidebar-collapsed .sidebar-item-content,
        html.preload-sidebar-collapsed #sidebar .sidebar-item-content {
            justify-content: center;
            gap: 0;
        }
        #sidebar.sidebar-collapsed .sidebar-chevron {
            transform: rotate(180deg);
        }
        #sidebar.sidebar-collapsed .sidebar-user-card,
        html.preload-sidebar-collapsed #sidebar .sidebar-user-card {
            justify-content: center;
            padding: 6px 0;
            background: transparent;
            border: none;
            box-shadow: none;
        }
        .sidebar-dot {
            display: none;
            position: absolute;
            top: -2px;
            right: -3px;
            width: 7px;
            height: 7px;
            border-radius: 9999px;
            box-shadow: 0 0 0 1.5px #F8FAFC;
        }
        #sidebar.sidebar-collapsed .sidebar-dot,
        html.preload-sidebar-collapsed #sidebar .sidebar-dot {
            display: block !important;
        }

        /* Collapsed Rail Mode: Popover Flyout Menus & Micro-Tooltips */
        aside.sidebar-collapsed nav {
            overflow: visible !important;
        }
        aside.sidebar-collapsed .sidebar-group-content,
        html.preload-sidebar-collapsed #sidebar .sidebar-group-content {
            display: none !important;
        }
        aside.sidebar-collapsed .sidebar-chevron-down,
        html.preload-sidebar-collapsed #sidebar .sidebar-chevron-down {
            display: none !important;
        }
        aside.sidebar-collapsed .sidebar-group:hover .sidebar-flyout-menu,
        html.preload-sidebar-collapsed #sidebar .sidebar-group:hover .sidebar-flyout-menu {
            display: block !important;
            animation: flyoutFadeIn 120ms cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes flyoutFadeIn {
            from { opacity: 0; transform: translateX(-4px); }
            to { opacity: 1; transform: translateX(0); }
        }
        /* Invisible hover bridge to eliminate diagonal mouse travel drop-off */
        .sidebar-flyout-menu::before {
            content: '';
            position: absolute;
            top: -12px;
            bottom: -12px;
            left: -24px;
            width: 28px;
            background: transparent;
        }
        /* Direct item & user card instant micro-tooltips in collapsed rail mode */
        #sidebar.sidebar-collapsed .sidebar-link[data-tooltip],
        #sidebar.sidebar-collapsed .sidebar-user-card[data-tooltip],
        html.preload-sidebar-collapsed #sidebar .sidebar-link[data-tooltip],
        html.preload-sidebar-collapsed #sidebar .sidebar-user-card[data-tooltip] {
            position: relative;
        }
        #sidebar.sidebar-collapsed .sidebar-link[data-tooltip]::after,
        #sidebar.sidebar-collapsed .sidebar-user-card[data-tooltip]::after,
        html.preload-sidebar-collapsed #sidebar .sidebar-link[data-tooltip]::after,
        html.preload-sidebar-collapsed #sidebar .sidebar-user-card[data-tooltip]::after {
            content: attr(data-tooltip);
            position: absolute;
            left: 58px;
            top: 50%;
            transform: translateY(-50%) translateX(-4px);
            background: #0f172a;
            color: #ffffff;
            font-size: 11px;
            font-weight: 600;
            line-height: 1.2;
            padding: 5px 9px;
            border-radius: 6px;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            visibility: hidden;
            transition: opacity 120ms ease, transform 120ms ease, visibility 120ms ease;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.18);
            z-index: 60;
        }
        #sidebar.sidebar-collapsed .sidebar-link[data-tooltip]:hover::after,
        #sidebar.sidebar-collapsed .sidebar-user-card[data-tooltip]:hover::after,
        html.preload-sidebar-collapsed #sidebar .sidebar-link[data-tooltip]:hover::after,
        html.preload-sidebar-collapsed #sidebar .sidebar-user-card[data-tooltip]:hover::after {
            opacity: 1;
            visibility: visible;
            transform: translateY(-50%) translateX(0);
        }
        .sidebar-flyout-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            color: #475569;
            transition: all 100ms ease;
        }
        .sidebar-flyout-link:hover {
            background: #f1f5f9;
            color: #0f172a;
        }
        .sidebar-flyout-link.is-active {
            background: #ecfdf5;
            color: #047857;
            font-weight: 700;
        }
        #sidebar.sidebar-collapsed .sidebar-flyout-menu .sidebar-badge,
        html.preload-sidebar-collapsed #sidebar .sidebar-flyout-menu .sidebar-badge {
            display: inline-block !important;
        }
    </style>
    <?php endif; ?>
</head>
<body class="<?= $isAuthed ? 'flex h-screen h-[100dvh] overflow-hidden text-[13px]' : ($isPublicDocs ? 'docs-body' : 'auth-body') ?>" data-agent-mode="<?= e($agentMode) ?>">

<?php if ($isAuthed): ?>
    <?php require __DIR__ . '/components/sidebar.php'; ?>
    
    <!-- Overlay for mobile sidebar -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/20 backdrop-blur-sm z-20 hidden md:hidden transition-opacity"></div>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col h-full md:h-screen min-w-0 bg-[#FBFBFC] relative overflow-hidden">
        <?php require __DIR__ . '/components/navbar.php'; ?>

        <!-- Global Floating Emergency Escalation Banner (Hidden in Personal Assistant Mode) -->
        <?php if (!$isPersonalMode): ?>
        <div id="globalEscalationBanner" class="hidden mx-4 sm:mx-6 mt-3 bg-gradient-to-r from-rose-600 via-rose-700 to-red-800 text-white rounded-xl p-3 px-4 shadow-xl border border-rose-500/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3 animate-in fade-in slide-in-from-top-2 duration-200 z-30 shrink-0">
            <div class="flex items-center gap-3 min-w-0">
                <span class="w-8 h-8 rounded-lg bg-white/20 backdrop-blur-xs flex items-center justify-center text-base shrink-0 animate-bounce">
                    🚨
                </span>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="px-1.5 py-0.2 bg-white text-rose-700 text-[10px] font-black rounded uppercase tracking-wider">Urgent Escalation</span>
                        <span id="globalEscalationCustomer" class="text-xs font-bold truncate">Customer</span>
                    </div>
                    <p id="globalEscalationReason" class="text-[11px] text-rose-100 truncate mt-0.5">Customer requested human takeover. AI Bot paused.</p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a id="globalEscalationOpenChatBtn" href="<?= e(base_url('index.php?page=live-chat')) ?>" class="px-3 py-1.5 rounded-lg bg-white text-rose-700 hover:bg-rose-50 text-xs font-bold shadow-xs transition-all active:scale-[0.98]">
                    Open Live Chat ➔
                </a>
                <button type="button" id="globalEscalationSilenceBtn" class="px-2.5 py-1.5 rounded-lg bg-rose-900/80 hover:bg-rose-950 text-white text-xs font-medium border border-rose-400/30 transition-all" title="Acknowledge and silence buzzer">
                    🔕 Silence Alarm
                </button>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Scrollable Workspace -->
        <div id="workspaceContent" class="flex-1 <?= $currentPage === 'live-chat' ? 'overflow-hidden p-2 md:p-3 flex flex-col' : 'overflow-y-auto overflow-x-hidden custom-scrollbar p-4 md:p-6 lg:p-7' ?> bg-[#FBFBFC] overscroll-contain">
            <div class="<?= $currentPage === 'live-chat' ? 'h-full flex flex-col min-h-0' : 'max-w-6xl mx-auto space-y-6' ?>">
                <?php foreach (flashes() as $flash): ?>
                    <div class="p-3.5 mb-4 text-[13px] font-medium rounded-lg border flex items-center justify-between <?= $flash['type'] === 'success' ? 'bg-emerald-50/80 border-emerald-200/80 text-emerald-800' : 'bg-red-50/80 border-red-200/80 text-red-800' ?>">
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full <?= $flash['type'] === 'success' ? 'bg-emerald-500' : 'bg-red-500' ?>"></span>
                            <?= e($flash['message']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?= $content ?>
            </div>
        </div>
    </main>

    <script>
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const openSidebarBtn = document.getElementById('openSidebar');
        const closeSidebarBtn = document.getElementById('closeSidebar');
        const collapseSidebarBtn = document.getElementById('collapseSidebar');

        function isDesktop() {
            return window.innerWidth >= 768;
        }

        function setSidebarCollapsed(collapsed) {
            if (!sidebar) return;
            if (collapsed) {
                sidebar.classList.add('sidebar-collapsed');
                try { localStorage.setItem('sidebar-collapsed', 'true'); } catch (e) {}
                if (collapseSidebarBtn) {
                    collapseSidebarBtn.setAttribute('title', 'Expand sidebar (Ctrl+B)');
                }
            } else {
                sidebar.classList.remove('sidebar-collapsed');
                try { localStorage.setItem('sidebar-collapsed', 'false'); } catch (e) {}
                if (collapseSidebarBtn) {
                    collapseSidebarBtn.setAttribute('title', 'Collapse sidebar (Ctrl+B)');
                }
            }
            document.documentElement.classList.remove('preload-sidebar-collapsed');
            window.dispatchEvent(new Event('resize'));
        }

        if (isDesktop() && localStorage.getItem('sidebar-collapsed') === 'true') {
            setSidebarCollapsed(true);
        }

        function toggleSidebar() {
            if (isDesktop()) {
                const isCollapsed = sidebar.classList.contains('sidebar-collapsed');
                setSidebarCollapsed(!isCollapsed);
            } else {
                sidebar.classList.toggle('-translate-x-full');
                if (sidebarOverlay) sidebarOverlay.classList.toggle('hidden');
            }
        }

        if (openSidebarBtn) openSidebarBtn.addEventListener('click', toggleSidebar);
        if (closeSidebarBtn) closeSidebarBtn.addEventListener('click', toggleSidebar);
        if (collapseSidebarBtn) collapseSidebarBtn.addEventListener('click', toggleSidebar);
        if (sidebarOverlay) sidebarOverlay.addEventListener('click', toggleSidebar);

        // Dropdown Groups (Accordion click-to-collapse or expand with localStorage memory)
        document.querySelectorAll('.sidebar-group-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const group = btn.closest('.sidebar-group');
                if (!group) return;
                const groupKey = group.dataset.group;

                if (sidebar && sidebar.classList.contains('sidebar-collapsed')) {
                    setSidebarCollapsed(false);
                    group.classList.add('is-open');
                    btn.setAttribute('aria-expanded', 'true');
                    if (groupKey) {
                        try { localStorage.setItem('sidebar_group_' + groupKey, 'open'); } catch (err) {}
                    }
                    return;
                }

                const isOpen = group.classList.contains('is-open');
                if (isOpen) {
                    group.classList.remove('is-open');
                    btn.setAttribute('aria-expanded', 'false');
                    if (groupKey) {
                        try { localStorage.setItem('sidebar_group_' + groupKey, 'closed'); } catch (err) {}
                    }
                } else {
                    group.classList.add('is-open');
                    btn.setAttribute('aria-expanded', 'true');
                    if (groupKey) {
                        try { localStorage.setItem('sidebar_group_' + groupKey, 'open'); } catch (err) {}
                    }
                }
            });
        });

        // Global Command Palette Shortcut (⌘K / Ctrl+K) and Sidebar Toggle (⌘B / Ctrl+B)
        document.addEventListener('keydown', (e) => {
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                const searchInput = document.getElementById('navbarSearchInput');
                if (searchInput) searchInput.focus();
            }
            if ((e.metaKey || e.ctrlKey) && (e.key === 'b' || e.key === 'B')) {
                e.preventDefault();
                toggleSidebar();
            }
        });
    </script>
    <script src="<?= e(base_url('assets/chart.min.js')) ?>"></script>
    <script src="<?= e(base_url('assets/global-alerts.js')) ?>?v=<?= time() ?>"></script>
<?php elseif ($isPublicDocs): ?>
    <main class="public-doc-shell">
        <div class="public-doc-topbar">
            <a class="auth-brand" href="<?= e(base_url('index.php?page=login')) ?>">
                <span class="auth-logo">WA</span>
                <span>Whats<b>Gateway</b></span>
            </a>
            <a class="ghost small" href="<?= e(base_url('index.php?page=login')) ?>">Log in</a>
        </div>
        <?php foreach (flashes() as $flash): ?>
            <div class="alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endforeach; ?>
        <?= $content ?>
    </main>
<?php endif; ?>
</body>
</html>
