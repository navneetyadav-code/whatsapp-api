<?php
$userForLayout = current_user();
$isAuthed = (bool) $userForLayout;
$currentPage = $page ?? ($_GET['page'] ?? 'dashboard');
$seo = seo_meta($currentPage);
$isPublicDocs = !$isAuthed && $currentPage === 'docs';
$initials = 'AD';
if ($userForLayout) {
    $parts = preg_split('/\s+/', trim((string) $userForLayout['name'])) ?: [];
    $initials = strtoupper(substr($parts[0] ?? 'A', 0, 1) . substr($parts[1] ?? 'D', 0, 1));
}
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
            defaultCountryCode: "<?= e(app_config('app.default_country_code')) ?>" || "in"
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
    </style>
    <?php endif; ?>
</head>
<body class="<?= $isAuthed ? 'flex h-screen h-[100dvh] overflow-hidden text-[13px]' : ($isPublicDocs ? 'docs-body' : 'auth-body') ?>">

<?php if ($isAuthed): ?>
    <?php require __DIR__ . '/components/sidebar.php'; ?>
    
    <!-- Overlay for mobile sidebar -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/20 backdrop-blur-sm z-20 hidden md:hidden transition-opacity"></div>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col h-full md:h-screen min-w-0 bg-[#FBFBFC] relative overflow-hidden">
        <?php require __DIR__ . '/components/navbar.php'; ?>
        
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

        if (openSidebarBtn && closeSidebarBtn && sidebar && sidebarOverlay) {
            function toggleSidebar() {
                sidebar.classList.toggle('-translate-x-full');
                sidebarOverlay.classList.toggle('hidden');
            }
            openSidebarBtn.addEventListener('click', toggleSidebar);
            closeSidebarBtn.addEventListener('click', toggleSidebar);
            sidebarOverlay.addEventListener('click', toggleSidebar);
        }

        // Global Command Palette Shortcut (⌘K / Ctrl+K)
        document.addEventListener('keydown', (e) => {
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                const searchInput = document.getElementById('navbarSearchInput');
                if (searchInput) searchInput.focus();
            }
        });
    </script>
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
