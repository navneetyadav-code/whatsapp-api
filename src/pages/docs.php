<?php

declare(strict_types=1);

$user = current_user();
$seoTitle = 'Developer API Documentation & Reference | WhatsGateway';
$seoDesc = 'Comprehensive developer integration guide for WhatsGateway. REST API endpoints, Meta Cloud API drop-in compatibility, webhook signatures, code examples, and queue architecture.';

$baseEndpoint = public_url('api/send.php');
$metaEndpoint = public_url('api/messages.php');
$healthEndpoint = public_url('api/health.php');
?>
<!DOCTYPE html>
<html lang="en" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($seoTitle) ?></title>
    <meta name="description" content="<?= e($seoDesc) ?>">
    <link rel="canonical" href="<?= e(public_url('docs')) ?>">
    
    <!-- Favicon Suite -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?= e(base_url('assets/favicon-32x32.png')) ?>">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= e(base_url('assets/favicon.png')) ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= e(base_url('assets/apple-touch-icon.png')) ?>">
    <link rel="shortcut icon" href="<?= e(base_url('favicon.ico')) ?>">
    <meta name="theme-color" content="#ffffff">

    <!-- Open Graph & Twitter Cards -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="<?= e(public_url('docs')) ?>">
    <meta property="og:title" content="<?= e($seoTitle) ?>">
    <meta property="og:description" content="<?= e($seoDesc) ?>">
    <meta property="og:image" content="<?= e(public_url('assets/favicon.png')) ?>">
    <meta property="og:site_name" content="WhatsGateway">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($seoTitle) ?>">
    <meta name="twitter:description" content="<?= e($seoDesc) ?>">
    <meta name="twitter:image" content="<?= e(public_url('assets/favicon.png')) ?>">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
                        mono: ['JetBrains Mono', 'Menlo', 'Monaco', 'Consolas', 'monospace']
                    },
                    colors: {
                        brand: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            200: '#a7f3d0',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            900: '#064e3b',
                            wa_green: '#25D366',
                            wa_dark: '#075E54'
                        }
                    },
                    boxShadow: {
                        'soft-xs': '0 1px 2px 0 rgba(16, 24, 40, 0.05)',
                        'soft-sm': '0 2px 4px -1px rgba(16, 24, 40, 0.06), 0 1px 2px -1px rgba(16, 24, 40, 0.04)',
                        'soft-md': '0 4px 8px -2px rgba(16, 24, 40, 0.08), 0 2px 4px -2px rgba(16, 24, 40, 0.04)',
                        'soft-lg': '0 12px 24px -4px rgba(16, 24, 40, 0.08), 0 4px 8px -4px rgba(16, 24, 40, 0.03)'
                    }
                }
            }
        };
    </script>
    <style>
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f8fafc; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .doc-section { scroll-margin-top: 6rem; }
    </style>
</head>
<body class="min-h-full flex flex-col font-sans bg-white text-slate-800 antialiased selection:bg-brand-500/20 selection:text-brand-900">

    <!-- Top Sticky Header -->
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-200 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between">
            
            <!-- Brand & Breadcrumb -->
            <div class="flex items-center gap-3 sm:gap-4">
                <a href="<?= e(base_url('')) ?>" class="flex items-center gap-3 group focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 rounded-xl p-1 transition-all">
                    <div class="relative flex-shrink-0">
                        <img src="<?= e(base_url('assets/favicon.png')) ?>" alt="WhatsGateway Logo" class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl object-cover shadow-soft-xs border border-slate-200 group-hover:scale-105 transition-transform duration-200">
                        <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 border-2 border-white rounded-full animate-pulse" title="Gateway Online"></span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-slate-900 text-base sm:text-lg tracking-tight group-hover:text-brand-600 transition-colors">WhatsGateway</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 font-mono">Docs</span>
                        </div>
                        <span class="text-[11px] text-slate-500 font-medium hidden sm:block">Developer REST API & SDK Reference</span>
                    </div>
                </a>
            </div>

            <!-- Header Quick Links & CTAs -->
            <div class="flex items-center gap-3">
                <a href="https://github.com/navneetyadav-code/Free_WhatsApp_Business_API" target="_blank" rel="noopener noreferrer" class="hidden md:inline-flex items-center gap-2 text-xs font-semibold text-slate-700 hover:text-slate-900 bg-slate-50 hover:bg-slate-100 px-3.5 py-2 rounded-xl border border-slate-200 transition-all shadow-soft-xs">
                    <svg class="w-4 h-4 text-slate-700" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/></svg>
                    <span>GitHub Repo</span>
                </a>

                <a href="<?= e(base_url('')) ?>" class="text-xs font-semibold text-slate-600 hover:text-slate-900 px-3 py-2 transition-colors hidden sm:block">
                    &larr; Back to Home
                </a>

                <?php if ($user): ?>
                    <a href="<?= e(base_url('dashboard')) ?>" class="inline-flex items-center gap-2 text-xs sm:text-sm font-semibold text-white bg-slate-900 hover:bg-slate-800 px-4 py-2 sm:py-2.5 rounded-xl transition-all shadow-soft-sm">
                        <span>Dashboard</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                <?php else: ?>
                    <a href="<?= e(base_url('register')) ?>" class="inline-flex items-center gap-2 text-xs sm:text-sm font-semibold text-white bg-slate-900 hover:bg-slate-800 px-4 py-2 sm:py-2.5 rounded-xl transition-all shadow-soft-sm hover:shadow-soft-md">
                        <span>Get Started Free</span>
                        <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                <?php endif; ?>

                <!-- Mobile Menu Button for Docs Sidebar -->
                <button id="doc-menu-btn" type="button" class="lg:hidden p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 focus:outline-none" aria-label="Toggle Documentation Navigation">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Docs Shell (Sidebar + Main Content + Right TOC) -->
    <div class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 flex">
        
        <!-- Mobile Sidebar Backdrop Overlay -->
        <div id="doc-sidebar-overlay" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-40 hidden lg:hidden"></div>

        <!-- Left Navigation Sidebar -->
        <aside id="doc-sidebar" class="fixed inset-y-0 left-0 z-50 w-72 bg-white border-r border-slate-200 transform -translate-x-full lg:translate-x-0 lg:static lg:w-64 xl:w-72 lg:shrink-0 lg:border-r lg:border-slate-200 transition-transform duration-200 ease-in-out overflow-y-auto pt-6 pb-12 px-4 sm:px-6 text-sm">
            
            <div class="flex items-center justify-between pb-4 border-b border-slate-200 lg:hidden mb-4">
                <span class="font-bold text-slate-900">Documentation Menu</span>
                <button id="doc-sidebar-close" class="p-1 rounded-lg text-slate-500 hover:text-slate-900">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Quick Filter Input -->
            <div class="relative mb-5">
                <input id="doc-search" type="text" placeholder="Quick filter docs..." class="w-full text-xs px-3 py-2 pl-8 rounded-lg bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 placeholder-slate-400">
                <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            <!-- Navigation Categories Tree -->
            <nav class="space-y-6 doc-nav-tree">
                
                <!-- Category 1 -->
                <div>
                    <h5 class="text-[11px] font-bold uppercase tracking-wider text-slate-400 font-mono mb-2">Getting Started</h5>
                    <ul class="space-y-1">
                        <li><a href="#overview" class="doc-link block px-2.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium">Overview & Purpose</a></li>
                        <li><a href="#architecture" class="doc-link block px-2.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium">Architecture Flow</a></li>
                        <li><a href="#environments" class="doc-link block px-2.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium">Base URLs & Endpoints</a></li>
                        <li><a href="#authentication" class="doc-link block px-2.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium">Authentication & API Keys</a></li>
                        <li><a href="#rate-limiting" class="doc-link block px-2.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium">Rate Limits & Bursting</a></li>
                    </ul>
                </div>

                <!-- Category 2 -->
                <div>
                    <h5 class="text-[11px] font-bold uppercase tracking-wider text-slate-400 font-mono mb-2">Messaging API</h5>
                    <ul class="space-y-1">
                        <li><a href="#send-message" class="doc-link block px-2.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium flex items-center justify-between"><span>Send Message</span><span class="text-[10px] bg-slate-100 text-slate-600 font-mono px-1.5 py-0.5 rounded font-bold">POST</span></a></li>
                        <li><a href="#meta-cloud-format" class="doc-link block px-2.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium flex items-center justify-between"><span>Meta Cloud Format</span><span class="text-[10px] bg-emerald-50 text-emerald-700 font-mono px-1.5 py-0.5 rounded font-bold">Drop-In</span></a></li>
                        <li><a href="#media-uploads" class="doc-link block px-2.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium">Media & File Uploads</a></li>
                        <li><a href="#ptt-audio" class="doc-link block px-2.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium">Voice Notes (PTT)</a></li>
                        <li><a href="#health-check" class="doc-link block px-2.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium flex items-center justify-between"><span>Gateway Health</span><span class="text-[10px] bg-slate-100 text-slate-600 font-mono px-1.5 py-0.5 rounded font-bold">GET</span></a></li>
                    </ul>
                </div>

                <!-- Category 3 -->
                <div>
                    <h5 class="text-[11px] font-bold uppercase tracking-wider text-slate-400 font-mono mb-2">Advanced Delivery</h5>
                    <ul class="space-y-1">
                        <li><a href="#anti-ban" class="doc-link block px-2.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium">Anti-Ban & Presence</a></li>
                        <li><a href="#campaigns" class="doc-link block px-2.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium">Campaigns & Grouping</a></li>
                        <li><a href="#google-contacts" class="doc-link block px-2.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium">Google Contacts Sync</a></li>
                    </ul>
                </div>

                <!-- Category 4 -->
                <div>
                    <h5 class="text-[11px] font-bold uppercase tracking-wider text-slate-400 font-mono mb-2">Webhooks</h5>
                    <ul class="space-y-1">
                        <li><a href="#webhooks" class="doc-link block px-2.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium">Webhook Delivery</a></li>
                        <li><a href="#hmac-verification" class="doc-link block px-2.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium">HMAC-SHA256 Security</a></li>
                        <li><a href="#event-schemas" class="doc-link block px-2.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium">Event Payloads</a></li>
                    </ul>
                </div>

                <!-- Category 5 -->
                <div>
                    <h5 class="text-[11px] font-bold uppercase tracking-wider text-slate-400 font-mono mb-2">Code Quickstarts</h5>
                    <ul class="space-y-1">
                        <li><a href="#quickstart-curl" class="doc-link block px-2.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium">cURL Snippet</a></li>
                        <li><a href="#quickstart-nodejs" class="doc-link block px-2.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium">Node.js (Fetch)</a></li>
                        <li><a href="#quickstart-python" class="doc-link block px-2.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium">Python (Requests)</a></li>
                        <li><a href="#quickstart-php" class="doc-link block px-2.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium">PHP (cURL)</a></li>
                        <li><a href="#quickstart-go" class="doc-link block px-2.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium">Go (net/http)</a></li>
                        <li><a href="#quickstart-cli" class="doc-link block px-2.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium">Interactive CLI Tool</a></li>
                    </ul>
                </div>

                <!-- Category 6 -->
                <div>
                    <h5 class="text-[11px] font-bold uppercase tracking-wider text-slate-400 font-mono mb-2">Reference</h5>
                    <ul class="space-y-1">
                        <li><a href="#status-codes" class="doc-link block px-2.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium">HTTP Error Dictionary</a></li>
                        <li><a href="#disclaimers" class="doc-link block px-2.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium">Compliance & License</a></li>
                    </ul>
                </div>

            </nav>
        </aside>

        <!-- Main Content Column -->
        <main class="flex-1 min-w-0 py-8 lg:py-12 lg:px-8 xl:px-12">
            
            <!-- Breadcrumbs -->
            <nav class="flex items-center gap-2 text-xs text-slate-400 mb-6 font-mono">
                <a href="<?= e(base_url('')) ?>" class="hover:text-slate-700">WhatsGateway</a>
                <span>/</span>
                <span class="text-slate-700">Developer Documentation</span>
            </nav>

            <!-- Doc Header Intro -->
            <div class="border-b border-slate-200 pb-8 mb-10">
                <div class="flex flex-wrap items-center gap-2 mb-3">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">REST API v1.0</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-800 border border-blue-200">Single API Key</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-50 text-purple-800 border border-purple-200">Meta Cloud Drop-In Compatible</span>
                </div>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">API Documentation & Integration Guide</h1>
                <p class="text-base text-slate-600 mt-3 leading-relaxed max-w-3xl">
                    Integrate your applications with <strong>WhatsGateway</strong> to programmatically send WhatsApp messages, broadcast rich media, receive verified delivery receipts via HMAC webhooks, and automate conversational responses &mdash; with zero per-message markup.
                </p>
            </div>

            <!-- SECTION 1: OVERVIEW & ARCHITECTURE -->
            <section id="overview" class="doc-section mb-14">
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight mb-3">1. Overview & Key Capabilities</h2>
                <p class="text-sm text-slate-600 leading-relaxed mb-4">
                    WhatsGateway provides a developer-friendly HTTP interface layered on top of the open-source Baileys library and PHP 8.2 backend. Unlike commercial cloud brokers that charge $0.05 to $0.15+ per message conversation, WhatsGateway is completely self-hosted with zero licensing or subscription fees.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 my-6">
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/70">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-sm mb-3">1</div>
                        <h4 class="font-bold text-slate-900 text-sm">Dual Format Compatibility</h4>
                        <p class="text-xs text-slate-600 mt-1">Accepts clean flat JSON or official Meta WhatsApp Cloud API payloads without changing existing application code.</p>
                    </div>
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/70">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-sm mb-3">2</div>
                        <h4 class="font-bold text-slate-900 text-sm">Persistent Queue Buffer</h4>
                        <p class="text-xs text-slate-600 mt-1">Incoming API requests are immediately acknowledged with an HTTP 200 response and buffered into MySQL/TiDB for reliable asynchronous dispatch.</p>
                    </div>
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/70">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-sm mb-3">3</div>
                        <h4 class="font-bold text-slate-900 text-sm">HMAC-Signed Webhooks</h4>
                        <p class="text-xs text-slate-600 mt-1">Outbound webhooks sign every status event using HMAC-SHA256 so your receivers can verify cryptographic authenticity.</p>
                    </div>
                </div>
            </section>

            <!-- SECTION 2: ARCHITECTURE FLOW -->
            <section id="architecture" class="doc-section mb-14">
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight mb-3">2. System Architecture Flow</h2>
                <p class="text-sm text-slate-600 leading-relaxed mb-4">
                    The platform decouples HTTP message ingestion from the WebSocket connection to guarantee sub-millisecond response times and eliminate network timeouts:
                </p>

                <div class="p-5 rounded-2xl bg-slate-900 text-slate-200 font-mono text-xs overflow-x-auto shadow-inner leading-relaxed my-4">
                    <div class="text-emerald-400 font-bold mb-2"># Ingestion to Delivery Pipeline:</div>
                    <div>1. Your App (cURL / Python / SDK) &rarr; POST /api/send.php (Authenticated with X-API-Key)</div>
                    <div>2. PHP Gateway &rarr; Validates schema, checks rate limits, enqueues record &rarr; Returns 200 OK & {message_id, status: "queued"}</div>
                    <div>3. Node.js Worker &rarr; Claims pending messages from queue &rarr; Transmits 'composing' presence to WhatsApp</div>
                    <div>4. Baileys Engine &rarr; Dispatches payload across WhatsApp Web Noise-encrypted socket</div>
                    <div>5. Handset &rarr; Renders message to recipient &rarr; Acknowledges delivery receipt</div>
                    <div>6. Webhook Worker &rarr; Dispatches HMAC-SHA256 signed event (message.delivered) to your URL</div>
                </div>
            </section>

            <!-- SECTION 3: ENVIRONMENTS & BASE URLS -->
            <section id="environments" class="doc-section mb-14">
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight mb-3">3. Base URLs & Environments</h2>
                <p class="text-sm text-slate-600 leading-relaxed mb-4">
                    All API endpoints are relative to your deployment's base URL. Replace the host below with your own private domain or local server address:
                </p>

                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200 font-mono text-slate-700">
                            <tr>
                                <th class="p-3.5 font-semibold">Environment</th>
                                <th class="p-3.5 font-semibold">Base URL</th>
                                <th class="p-3.5 font-semibold">Primary Endpoints</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-slate-600">
                            <tr>
                                <td class="p-3.5 font-bold text-slate-900">Local (XAMPP / Dev)</td>
                                <td class="p-3.5 font-mono text-xs text-slate-800">http://localhost/whatsapp-api/public/</td>
                                <td class="p-3.5 font-mono text-xs"><code class="text-emerald-700">api/send.php</code></td>
                            </tr>
                            <tr>
                                <td class="p-3.5 font-bold text-slate-900">Production / VPS</td>
                                <td class="p-3.5 font-mono text-xs text-slate-800">https://your-domain.com/</td>
                                <td class="p-3.5 font-mono text-xs"><code class="text-emerald-700">api/send.php</code>, <code class="text-emerald-700">api/messages.php</code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- SECTION 4: AUTHENTICATION & SINGLE API KEY -->
            <section id="authentication" class="doc-section mb-14">
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight mb-3">4. Authentication & Single API Key</h2>
                <p class="text-sm text-slate-600 leading-relaxed mb-4">
                    Every API request requires an active API Key generated from your dashboard under <strong>API Keys</strong>. We support two standard header formats interchangeably:
                </p>

                <div class="space-y-3">
                    <div class="p-4 rounded-xl bg-slate-900 text-slate-200 font-mono text-xs">
                        <span class="text-slate-400"># Option 1: Dedicated Custom Header (Standard)</span><br>
                        <span class="text-emerald-400">X-API-Key:</span> wa_live_3f98c21a48e7...<br><br>
                        <span class="text-slate-400"># Option 2: Bearer Authorization Header (Meta Cloud API style)</span><br>
                        <span class="text-emerald-400">Authorization:</span> Bearer wa_live_3f98c21a48e7...
                    </div>
                </div>

                <div class="mt-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900">
                    <strong>Zero Plaintext Storage:</strong> API keys are hashed with modern cryptographic SHA-256 before storage in the database. The secret token is shown only once at generation time.
                </div>
            </section>

            <!-- SECTION 5: RATE LIMITS & BURSTING -->
            <section id="rate-limiting" class="doc-section mb-14">
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight mb-3">5. Rate Limits & Burst Policies</h2>
                <p class="text-sm text-slate-600 leading-relaxed mb-4">
                    Rate limits can be customized per API key directly in your dashboard (default: 60 requests per minute). If a client exceeds the threshold, the gateway responds with HTTP 429:
                </p>

                <div class="p-4 rounded-xl bg-slate-900 text-rose-300 font-mono text-xs leading-relaxed overflow-x-auto">
<pre>{
  "ok": false,
  "error": "Rate limit exceeded.",
  "request_id": "req_6718a209b...",
  "rate": {
    "allowed": false,
    "limit": 60,
    "current": 61,
    "remaining": 0,
    "reset_in_seconds": 45
  },
  "meta_error": {
    "message": "Rate limit hit.",
    "type": "OAuthException",
    "code": 80007
  }
}</pre>
                </div>
            </section>

            <!-- SECTION 6: SEND MESSAGE ENDPOINT -->
            <section id="send-message" class="doc-section mb-14">
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2.5 py-1 text-xs font-bold text-white bg-slate-900 rounded-md font-mono">POST</span>
                    <span class="font-mono text-sm font-bold text-slate-900">/api/send.php</span>
                </div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight mb-2">Send Message (Clean REST Format)</h2>
                <p class="text-sm text-slate-600 leading-relaxed mb-5">
                    Dispatches a text message, document, image, audio, or video file to any international WhatsApp recipient.
                </p>

                <!-- Parameter Specification Table -->
                <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white mb-6">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 font-mono">
                            <tr>
                                <th class="p-3.5 font-bold">Field</th>
                                <th class="p-3.5 font-bold">Type</th>
                                <th class="p-3.5 font-bold">Requirement</th>
                                <th class="p-3.5 font-bold">Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-600">
                            <tr>
                                <td class="p-3.5 font-mono font-bold text-slate-900">to</td>
                                <td class="p-3.5 font-mono text-slate-500">string</td>
                                <td class="p-3.5"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">Required</span></td>
                                <td class="p-3.5">Recipient's full phone number with country code (e.g., <code class="bg-slate-100 px-1 py-0.5 rounded">919876543210</code>, <code class="bg-slate-100 px-1 py-0.5 rounded">+15551234567</code>). Spaces and special characters are stripped automatically.</td>
                            </tr>
                            <tr>
                                <td class="p-3.5 font-mono font-bold text-slate-900">message</td>
                                <td class="p-3.5 font-mono text-slate-500">string</td>
                                <td class="p-3.5"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Conditional</span></td>
                                <td class="p-3.5">The message copy or media caption. Optional if a media URL/file is provided. Supports native WhatsApp markdown (<code class="bg-slate-100 px-1 py-0.5 rounded">*bold*</code>, <code class="bg-slate-100 px-1 py-0.5 rounded">_italic_</code>, <code class="bg-slate-100 px-1 py-0.5 rounded">~strike~</code>, <code class="bg-slate-100 px-1 py-0.5 rounded">```mono```</code>).</td>
                            </tr>
                            <tr>
                                <td class="p-3.5 font-mono font-bold text-slate-900">media_url</td>
                                <td class="p-3.5 font-mono text-slate-500">string (URL)</td>
                                <td class="p-3.5"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">Optional</span></td>
                                <td class="p-3.5">Direct public HTTPS link to an image (JPG, PNG, WEBP), document (PDF, DOCX), video (MP4), or audio (MP3, OGG). Downloaded with SSRF protections. Max 20MB.</td>
                            </tr>
                            <tr>
                                <td class="p-3.5 font-mono font-bold text-slate-900">media_base64</td>
                                <td class="p-3.5 font-mono text-slate-500">string (Base64)</td>
                                <td class="p-3.5"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">Optional</span></td>
                                <td class="p-3.5">Direct Base64 data string (e.g., <code class="bg-slate-100 px-1 py-0.5 rounded">data:image/png;base64,...</code>) or raw base64 data.</td>
                            </tr>
                            <tr>
                                <td class="p-3.5 font-mono font-bold text-slate-900">type</td>
                                <td class="p-3.5 font-mono text-slate-500">string</td>
                                <td class="p-3.5"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">Optional</span></td>
                                <td class="p-3.5"><code class="bg-slate-100 px-1 py-0.5 rounded">text</code>, <code class="bg-slate-100 px-1 py-0.5 rounded">image</code>, <code class="bg-slate-100 px-1 py-0.5 rounded">document</code>, <code class="bg-slate-100 px-1 py-0.5 rounded">video</code>, or <code class="bg-slate-100 px-1 py-0.5 rounded">audio</code>. Auto-detected from attachment MIME type if omitted.</td>
                            </tr>
                            <tr>
                                <td class="p-3.5 font-mono font-bold text-slate-900">filename</td>
                                <td class="p-3.5 font-mono text-slate-500">string</td>
                                <td class="p-3.5"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">Optional</span></td>
                                <td class="p-3.5">Custom filename presented to the recipient in WhatsApp for document attachments (e.g., <code class="bg-slate-100 px-1 py-0.5 rounded">Monthly-Invoice-Oct2026.pdf</code>).</td>
                            </tr>
                            <tr>
                                <td class="p-3.5 font-mono font-bold text-slate-900">humanize</td>
                                <td class="p-3.5 font-mono text-slate-500">boolean</td>
                                <td class="p-3.5"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">Optional</span></td>
                                <td class="p-3.5">Defaults to <code class="bg-slate-100 px-1 py-0.5 rounded">true</code>. Adds human typing indicators (<code class="bg-slate-100 px-1 py-0.5 rounded">sendPresenceUpdate('composing')</code>) and paces delivery queue to prevent spam bans.</td>
                            </tr>
                            <tr>
                                <td class="p-3.5 font-mono font-bold text-slate-900">ptt</td>
                                <td class="p-3.5 font-mono text-slate-500">boolean</td>
                                <td class="p-3.5"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">Optional</span></td>
                                <td class="p-3.5">Send audio attachments as a playable voice note (Push-to-Talk waveform) instead of a standard audio file.</td>
                            </tr>
                            <tr>
                                <td class="p-3.5 font-mono font-bold text-slate-900">campaign_id</td>
                                <td class="p-3.5 font-mono text-slate-500">integer</td>
                                <td class="p-3.5"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">Optional</span></td>
                                <td class="p-3.5">Attach the message to an existing marketing or notification campaign for aggregated dashboard metrics.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Live Request / Response Code Snippets -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 my-6">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-700">Request Body (JSON)</span>
                            <button class="copy-btn text-[11px] font-semibold text-emerald-700 hover:text-emerald-800" data-clipboard="req-body-sample">Copy</button>
                        </div>
                        <pre id="req-body-sample" class="bg-slate-900 text-slate-200 p-4 rounded-xl font-mono text-xs overflow-x-auto shadow-inner leading-relaxed"><code>{
  "to": "919876543210",
  "message": "Hello from WhatsGateway! 🚀\nYour order *#84920* is confirmed.",
  "humanize": true
}</code></pre>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-700">Response (HTTP 200 OK)</span>
                            <button class="copy-btn text-[11px] font-semibold text-emerald-700 hover:text-emerald-800" data-clipboard="res-body-sample">Copy</button>
                        </div>
                        <pre id="res-body-sample" class="bg-slate-900 text-emerald-400 p-4 rounded-xl font-mono text-xs overflow-x-auto shadow-inner leading-relaxed"><code>{
  "ok": true,
  "status": "queued",
  "message_id": 142,
  "request_id": "req_6718a9ef38...",
  "recipient": "919876543210",
  "type": "text",
  "has_media": false,
  "messaging_product": "whatsapp",
  "contacts": [
    {
      "input": "919876543210",
      "wa_id": "919876543210"
    }
  ],
  "messages": [
    {
      "id": "wamid.b29f430ca0489...",
      "message_status": "accepted"
    }
  ]
}</code></pre>
                    </div>
                </div>
            </section>

            <!-- SECTION 7: META CLOUD API COMPATIBILITY -->
            <section id="meta-cloud-format" class="doc-section mb-14">
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2.5 py-1 text-xs font-bold text-white bg-blue-600 rounded-md font-mono">POST</span>
                    <span class="font-mono text-sm font-bold text-slate-900">/api/messages.php</span>
                </div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight mb-2">Meta WhatsApp Cloud API (Drop-In Compatibility)</h2>
                <p class="text-sm text-slate-600 leading-relaxed mb-4">
                    If your existing codebase was built for the official Meta WhatsApp Cloud API (Graph API v17+), you can point your HTTP client directly at WhatsGateway without altering your request payload structure.
                </p>

                <div class="p-4 rounded-xl bg-slate-900 text-slate-200 font-mono text-xs leading-relaxed overflow-x-auto my-4">
                    <div class="text-slate-400 mb-2"># Meta Cloud API Drop-In Request:</div>
<code>curl -X POST "<?= e($metaEndpoint) ?>" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_API_KEY_HERE" \
  -d '{
    "messaging_product": "whatsapp",
    "recipient_type": "individual",
    "to": "919876543210",
    "type": "text",
    "text": {
      "preview_url": false,
      "body": "Hello via Meta Cloud API format! 🌐"
    }
  }'</code>
                </div>

                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600">
                    <strong>Supported Meta Types:</strong> <code class="bg-white px-1 py-0.5 rounded text-emerald-800 border">text</code>, <code class="bg-white px-1 py-0.5 rounded text-emerald-800 border">image</code>, <code class="bg-white px-1 py-0.5 rounded text-emerald-800 border">document</code>, <code class="bg-white px-1 py-0.5 rounded text-emerald-800 border">video</code>, <code class="bg-white px-1 py-0.5 rounded text-emerald-800 border">audio</code>, and <code class="bg-white px-1 py-0.5 rounded text-emerald-800 border">template</code>. Parameter components in templates are automatically concatenated into the final message body.
                </div>
            </section>

            <!-- SECTION 8: MEDIA & FILE UPLOADS -->
            <section id="media-uploads" class="doc-section mb-14">
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight mb-3">8. Media & Binary File Uploads</h2>
                <p class="text-sm text-slate-600 leading-relaxed mb-4">
                    WhatsGateway supports three methods for sending attachments up to 20MB:
                </p>

                <div class="space-y-6">
                    <!-- Method 1: Public HTTPS URL -->
                    <div class="border border-slate-200 rounded-xl p-5 bg-white">
                        <h4 class="font-bold text-slate-900 text-sm mb-2 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            Method A: Remote Public HTTPS URL
                        </h4>
                        <p class="text-xs text-slate-600 mb-3">The server downloads the file from the provided URL, validates MIME boundaries, and dispatches it over WhatsApp.</p>
                        <pre class="bg-slate-900 text-slate-200 p-3 rounded-lg font-mono text-xs overflow-x-auto"><code>curl -X POST "<?= e($baseEndpoint) ?>" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: YOUR_API_KEY_HERE" \
  -d '{
    "to": "919876543210",
    "type": "image",
    "media_url": "https://images.unsplash.com/photo-1579202673506-ca3ce28943ef?w=800",
    "caption": "Photo dispatch verified! 📸"
  }'</code></pre>
                    </div>

                    <!-- Method 2: Direct Multipart Form Upload -->
                    <div class="border border-slate-200 rounded-xl p-5 bg-white">
                        <h4 class="font-bold text-slate-900 text-sm mb-2 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                            Method B: Direct File Upload from Terminal (Multipart/form-data)
                        </h4>
                        <p class="text-xs text-slate-600 mb-3">Ideal for local PDF invoices, documents, and spreadsheets without needing a separate CDN upload step.</p>
                        <pre class="bg-slate-900 text-slate-200 p-3 rounded-lg font-mono text-xs overflow-x-auto"><code>curl -X POST "<?= e($baseEndpoint) ?>" \
  -H "X-API-Key: YOUR_API_KEY_HERE" \
  -F "to=919876543210" \
  -F "file=@/path/to/invoice-84920.pdf" \
  -F "caption=Attached is your monthly billing statement."</code></pre>
                    </div>

                    <!-- Method 3: Base64 Ingestion -->
                    <div class="border border-slate-200 rounded-xl p-5 bg-white">
                        <h4 class="font-bold text-slate-900 text-sm mb-2 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                            Method C: In-Memory Base64 Data URI
                        </h4>
                        <p class="text-xs text-slate-600 mb-3">Send dynamically generated charts or receipts directly from memory.</p>
                        <pre class="bg-slate-900 text-slate-200 p-3 rounded-lg font-mono text-xs overflow-x-auto"><code>curl -X POST "<?= e($baseEndpoint) ?>" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: YOUR_API_KEY_HERE" \
  -d '{
    "to": "919876543210",
    "media_base64": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==",
    "filename": "pixel.png",
    "caption": "Rendered via Base64"
  }'</code></pre>
                    </div>
                </div>
            </section>

            <!-- SECTION 9: PTT / VOICE NOTES -->
            <section id="ptt-audio" class="doc-section mb-14">
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight mb-3">9. Voice Notes & Push-to-Talk (PTT)</h2>
                <p class="text-sm text-slate-600 leading-relaxed mb-4">
                    By default, audio files are delivered as standard media players. To render audio as an authentic WhatsApp voice message with waveform playback, pass <code class="bg-slate-100 px-1 py-0.5 rounded text-emerald-800 font-mono text-xs">"ptt": true</code>:
                </p>

                <pre class="bg-slate-900 text-slate-200 p-4 rounded-xl font-mono text-xs overflow-x-auto shadow-inner leading-relaxed"><code>curl -X POST "<?= e($baseEndpoint) ?>" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: YOUR_API_KEY_HERE" \
  -d '{
    "to": "919876543210",
    "type": "audio",
    "media_url": "https://example.com/audio/voice-memo.ogg",
    "ptt": true
  }'</code></pre>
            </section>

            <!-- SECTION 10: GATEWAY HEALTH CHECK -->
            <section id="health-check" class="doc-section mb-14">
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2.5 py-1 text-xs font-bold text-slate-900 bg-slate-100 rounded-md font-mono">GET</span>
                    <span class="font-mono text-sm font-bold text-slate-900">/api/health.php</span>
                </div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight mb-2">Gateway & Worker Health Check</h2>
                <p class="text-sm text-slate-600 leading-relaxed mb-4">
                    Monitor uptime and connectivity for automated uptime pings (UptimeRobot, Datadog, Prometheus):
                </p>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <pre class="bg-slate-900 text-slate-200 p-4 rounded-xl font-mono text-xs overflow-x-auto"><code>curl -X GET "<?= e($healthEndpoint) ?>"</code></pre>
                    <pre class="bg-slate-900 text-emerald-400 p-4 rounded-xl font-mono text-xs overflow-x-auto"><code>{
  "ok": true,
  "status": "healthy",
  "timestamp": "2026-10-23T15:10:00+05:30",
  "service": "whatsgateway",
  "worker": {
    "ok": true,
    "status_code": 200,
    "service": "baileys-worker"
  }
}</code></pre>
                </div>
            </section>

            <!-- SECTION 11: ANTI-BAN BEST PRACTICES -->
            <section id="anti-ban" class="doc-section mb-14">
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight mb-3">11. Anti-Ban Architecture & Humanize Delays</h2>
                <p class="text-sm text-slate-600 leading-relaxed mb-4">
                    WhatsApp monitors sudden high-frequency bursts of unsolicited traffic. WhatsGateway embeds multiple safeguards to keep your linked numbers healthy:
                </p>

                <div class="p-5 rounded-2xl bg-amber-50/70 border border-amber-200 text-xs sm:text-sm text-amber-950 space-y-2.5">
                    <div class="font-bold text-amber-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Built-in Anti-Ban Safeguards</span>
                    </div>
                    <ul class="list-disc pl-5 space-y-1 text-amber-900/90">
                        <li><strong>Human Typing Presence:</strong> Passing <code class="bg-white px-1.5 py-0.5 rounded text-amber-900 font-mono">"humanize": true</code> triggers a realistic typing presence update (<code class="bg-white px-1.5 py-0.5 rounded text-amber-900 font-mono">sendPresenceUpdate('composing')</code>) before payload transmission.</li>
                        <li><strong>Configurable Pacing Delay:</strong> Managed by <code class="bg-white px-1.5 py-0.5 rounded text-amber-900 font-mono">SEND_DELAY_MS</code> in your <code class="bg-white px-1.5 py-0.5 rounded text-amber-900 font-mono">.env</code> (defaults to 2500ms between consecutive dispatches).</li>
                        <li><strong>Consented Communication Only:</strong> Send notifications, verification OTPs, and receipts strictly to customers who have given prior verifiable opt-in consent.</li>
                    </ul>
                </div>
            </section>

            <!-- SECTION 12: WEBHOOKS & HMAC SIGNATURES -->
            <section id="webhooks" class="doc-section mb-14">
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight mb-3">12. Webhooks & Real-Time Delivery Notifications</h2>
                <p class="text-sm text-slate-600 leading-relaxed mb-4">
                    Configure your HTTPS webhook endpoint in the Dashboard under <strong>Webhooks</strong>. Whenever a message transitions states or your phone link updates, WhatsGateway executes an asynchronous HTTP POST callback:
                </p>

                <!-- HMAC Verification Box -->
                <div id="hmac-verification" class="my-6 p-5 rounded-2xl bg-white border border-slate-200 shadow-soft-sm">
                    <h3 class="font-bold text-slate-900 text-base mb-2">Cryptographic HMAC-SHA256 Signature Verification</h3>
                    <p class="text-xs text-slate-600 mb-3">
                        Every webhook request includes an <code class="bg-slate-100 text-slate-800 px-1.5 py-0.5 rounded font-mono text-xs">X-Hub-Signature-256</code> header computed using your private webhook secret:
                    </p>

                    <div class="space-y-4">
                        <div>
                            <span class="text-xs font-bold text-slate-700">Verification in Node.js (Express / Fastify):</span>
                            <pre class="bg-slate-900 text-slate-200 p-3 rounded-lg font-mono text-xs overflow-x-auto mt-1"><code>const crypto = require('crypto');

function verifyWebhook(rawBody, signatureHeader, secret) {
    const expected = 'sha256=' + crypto.createHmac('sha256', secret).update(rawBody).digest('hex');
    return crypto.timingSafeEqual(Buffer.from(signatureHeader), Buffer.from(expected));
}</code></pre>
                        </div>

                        <div>
                            <span class="text-xs font-bold text-slate-700">Verification in Python (Flask / FastAPI):</span>
                            <pre class="bg-slate-900 text-slate-200 p-3 rounded-lg font-mono text-xs overflow-x-auto mt-1"><code>import hmac, hashlib

def verify_webhook(raw_body_bytes, signature_header, secret_key):
    expected = "sha256=" + hmac.new(secret_key.encode(), raw_body_bytes, hashlib.sha256).hexdigest()
    return hmac.compare_digest(signature_header, expected)</code></pre>
                        </div>
                    </div>
                </div>

                <!-- Event Schemas -->
                <div id="event-schemas" class="my-6">
                    <h3 class="font-bold text-slate-900 text-base mb-3">Event Payloads</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <span class="text-xs font-bold text-slate-700">Event: message.delivered</span>
                            <pre class="bg-slate-900 text-slate-200 p-3 rounded-lg font-mono text-xs overflow-x-auto mt-1"><code>{
  "event": "message.delivered",
  "message_id": 142,
  "to": "919876543210",
  "status": "delivered",
  "timestamp": "2026-10-23T15:12:00Z"
}</code></pre>
                        </div>

                        <div>
                            <span class="text-xs font-bold text-slate-700">Event: message.read</span>
                            <pre class="bg-slate-900 text-slate-200 p-3 rounded-lg font-mono text-xs overflow-x-auto mt-1"><code>{
  "event": "message.read",
  "message_id": 142,
  "to": "919876543210",
  "status": "read",
  "timestamp": "2026-10-23T15:12:05Z"
}</code></pre>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION 13: GOOGLE CONTACTS & LIMITED USE -->
            <section id="google-contacts" class="doc-section mb-14">
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight mb-3">13. Google Contacts Integration & Limited Use</h2>
                <p class="text-sm text-slate-600 leading-relaxed mb-4">
                    WhatsGateway supports one-click address book synchronization powered by Google People API OAuth 2.0 PKCE:
                </p>

                <div class="p-5 rounded-2xl bg-sky-50/80 border border-sky-200 text-xs text-sky-950 space-y-2">
                    <div class="font-bold text-sky-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-sky-600" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                        <span>Strict Limited Use Disclosure</span>
                    </div>
                    <p>
                        WhatsGateway’s use and transfer to any other app of information received from Google APIs adheres to the <a href="https://developers.google.com/terms/api-services-user-data-policy" target="_blank" rel="noopener noreferrer" class="font-bold underline text-sky-900">Google API Services User Data Policy</a>, including the Limited Use requirements.
                    </p>
                    <ul class="list-disc pl-5 space-y-1">
                        <li><strong>Read-Only Scope:</strong> Uses only <code class="bg-white px-1 py-0.5 rounded font-mono text-sky-900">contacts.readonly</code> to import opted-in phone numbers.</li>
                        <li><strong>Zero Third-Party Sharing:</strong> Synced contact records are stored locally and never sold to advertisers or data brokers.</li>
                    </ul>
                </div>
            </section>

            <!-- SECTION 14: CODE QUICKSTARTS -->
            <section id="quickstart-curl" class="doc-section mb-14">
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight mb-2">14. Multi-Language Quickstart Code</h2>
                <p class="text-sm text-slate-600 leading-relaxed mb-6">
                    Production-grade implementations in popular programming languages:
                </p>

                <!-- Language Tabs Container -->
                <div class="border border-slate-200 rounded-2xl bg-white shadow-soft-sm overflow-hidden">
                    <div class="flex items-center gap-1 p-2 bg-slate-50 border-b border-slate-200 overflow-x-auto text-xs font-semibold">
                        <button class="code-tab active px-3.5 py-1.5 rounded-lg bg-white text-slate-900 shadow-soft-xs border border-slate-200" data-lang="curl">cURL</button>
                        <button class="code-tab px-3.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900" data-lang="nodejs">Node.js</button>
                        <button class="code-tab px-3.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900" data-lang="python">Python</button>
                        <button class="code-tab px-3.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900" data-lang="php">PHP</button>
                        <button class="code-tab px-3.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900" data-lang="go">Go</button>
                    </div>

                    <div class="p-5">
                        <!-- cURL Block -->
                        <div id="code-snippet-curl" class="code-snippet-block">
                            <pre class="bg-slate-900 text-slate-200 p-4 rounded-xl font-mono text-xs overflow-x-auto leading-relaxed"><code>curl -X POST "<?= e($baseEndpoint) ?>" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: wa_live_YOUR_API_KEY_HERE" \
  -d '{
    "to": "919876543210",
    "message": "Hello from WhatsGateway! 🚀\nYour order *#84920* has been shipped.",
    "humanize": true
  }'</code></pre>
                        </div>

                        <!-- Node.js Block -->
                        <div id="code-snippet-nodejs" class="code-snippet-block hidden">
                            <pre class="bg-slate-900 text-slate-200 p-4 rounded-xl font-mono text-xs overflow-x-auto leading-relaxed"><code>// Native fetch available in Node.js 18+ and modern browsers
const response = await fetch("<?= e($baseEndpoint) ?>", {
  method: "POST",
  headers: {
    "Content-Type": "application/json",
    "X-API-Key": "wa_live_YOUR_API_KEY_HERE"
  },
  body: JSON.stringify({
    to: "919876543210",
    message: "Order #84920 confirmed via Node.js! ⚡",
    humanize: true
  })
});

const result = await response.json();
console.log("Gateway Response:", result);</code></pre>
                        </div>

                        <!-- Python Block -->
                        <div id="code-snippet-python" class="code-snippet-block hidden">
                            <pre class="bg-slate-900 text-slate-200 p-4 rounded-xl font-mono text-xs overflow-x-auto leading-relaxed"><code>import requests

url = "<?= e($baseEndpoint) ?>"
headers = {
    "Content-Type": "application/json",
    "X-API-Key": "wa_live_YOUR_API_KEY_HERE"
}
payload = {
    "to": "919876543210",
    "message": "Hello from Python! 🐍\nYour verification code is *7492*.",
    "humanize": True
}

response = requests.post(url, json=payload, headers=headers, timeout=15)
print(response.json())</code></pre>
                        </div>

                        <!-- PHP Block -->
                        <div id="code-snippet-php" class="code-snippet-block hidden">
                            <pre class="bg-slate-900 text-slate-200 p-4 rounded-xl font-mono text-xs overflow-x-auto leading-relaxed"><code>&lt;?php
$payload = [
    'to' => '919876543210',
    'message' => 'Hello from PHP Client! 🐘',
    'humanize' => true
];

$ch = curl_init("<?= e($baseEndpoint) ?>");
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'X-API-Key: wa_live_YOUR_API_KEY_HERE'
    ],
    CURLOPT_POSTFIELDS => json_encode($payload)
]);

$response = curl_exec($ch);
curl_close($ch);
echo $response;</code></pre>
                        </div>

                        <!-- Go Block -->
                        <div id="code-snippet-go" class="code-snippet-block hidden">
                            <pre class="bg-slate-900 text-slate-200 p-4 rounded-xl font-mono text-xs overflow-x-auto leading-relaxed"><code>package main

import (
	"bytes"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
)

func main() {
	payload, _ := json.Marshal(map[string]interface{}{
		"to":       "919876543210",
		"message":  "Instant transactional alert via Go! 🐹",
		"humanize": true,
	})

	req, _ := http.NewRequest("POST", "<?= e($baseEndpoint) ?>", bytes.NewBuffer(payload))
	req.Header.Set("Content-Type", "application/json")
	req.Header.Set("X-API-Key", "wa_live_YOUR_API_KEY_HERE")

	client := &http.Client{}
	resp, err := client.Do(req)
	if err != nil {
		panic(err)
	}
	defer resp.Body.Close()

	body, _ := io.ReadAll(resp.Body)
	fmt.Println(string(body))
}</code></pre>
                        </div>

                    </div>
                </div>
            </section>

            <!-- SECTION 15: INTERACTIVE CLI TOOL -->
            <section id="quickstart-cli" class="doc-section mb-14">
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight mb-3">15. Interactive Terminal CLI Tool</h2>
                <p class="text-sm text-slate-600 leading-relaxed mb-4">
                    Send messages directly from your developer terminal without writing code using our included interactive Python CLI tool:
                </p>

                <div class="bg-slate-900 text-white rounded-xl p-5 shadow-soft-sm font-mono text-xs">
                    <div class="text-slate-400 mb-2"># Run directly from repository root:</div>
                    <div class="text-emerald-400">python send_whatsapp.py</div>
                    <div class="text-slate-500 mt-2"># Prompts interactively for API Key, Recipient Number, Message text, and optional media link.</div>
                </div>
            </section>

            <!-- SECTION 16: HTTP ERROR DICTIONARY -->
            <section id="status-codes" class="doc-section mb-14">
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight mb-3">16. HTTP Status Codes & Error Dictionary</h2>
                <p class="text-sm text-slate-600 leading-relaxed mb-4">
                    All HTTP error responses return a standardized JSON envelope with both generic and Meta-compatible error structures:
                </p>

                <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200 font-mono text-slate-700">
                            <tr>
                                <th class="p-3.5 font-bold">Status Code</th>
                                <th class="p-3.5 font-bold">Error Cause</th>
                                <th class="p-3.5 font-bold">Meta Code</th>
                                <th class="p-3.5 font-bold">Recommended Solution</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-slate-600">
                            <tr>
                                <td class="p-3.5 font-mono font-bold text-emerald-700">200 OK</td>
                                <td class="p-3.5 font-medium text-slate-900">Message Accepted & Queued</td>
                                <td class="p-3.5 font-mono text-xs">&mdash;</td>
                                <td class="p-3.5">Payload validated; buffered into persistent queue for delivery.</td>
                            </tr>
                            <tr>
                                <td class="p-3.5 font-mono font-bold text-amber-700">401 Unauthorized</td>
                                <td class="p-3.5 font-medium text-slate-900">Invalid or Missing API Key</td>
                                <td class="p-3.5 font-mono text-xs">190</td>
                                <td class="p-3.5">Verify that your <code class="bg-slate-100 px-1 py-0.5 rounded font-mono">X-API-Key</code> or <code class="bg-slate-100 px-1 py-0.5 rounded font-mono">Bearer</code> token is active in the Dashboard.</td>
                            </tr>
                            <tr>
                                <td class="p-3.5 font-mono font-bold text-rose-700">403 Forbidden</td>
                                <td class="p-3.5 font-medium text-slate-900">IP Not Whitelisted</td>
                                <td class="p-3.5 font-mono text-xs">&mdash;</td>
                                <td class="p-3.5">Your API Key has an IP restriction enabled that does not match your caller IP address.</td>
                            </tr>
                            <tr>
                                <td class="p-3.5 font-mono font-bold text-rose-700">405 Method Not Allowed</td>
                                <td class="p-3.5 font-medium text-slate-900">Non-POST Request Used</td>
                                <td class="p-3.5 font-mono text-xs">&mdash;</td>
                                <td class="p-3.5">Dispatch requests using HTTP <code class="bg-slate-100 px-1 py-0.5 rounded font-mono">POST</code>.</td>
                            </tr>
                            <tr>
                                <td class="p-3.5 font-mono font-bold text-rose-700">422 Unprocessable</td>
                                <td class="p-3.5 font-medium text-slate-900">Missing Required Fields</td>
                                <td class="p-3.5 font-mono text-xs">100</td>
                                <td class="p-3.5">Ensure recipient (<code class="bg-slate-100 px-1 py-0.5 rounded font-mono">to</code>) and either <code class="bg-slate-100 px-1 py-0.5 rounded font-mono">message</code> or media attachment are present.</td>
                            </tr>
                            <tr>
                                <td class="p-3.5 font-mono font-bold text-purple-700">429 Too Many Requests</td>
                                <td class="p-3.5 font-medium text-slate-900">Rate Limit Exceeded</td>
                                <td class="p-3.5 font-mono text-xs">80007</td>
                                <td class="p-3.5">Pace your requests or increase your API Key rate limit quota in the dashboard.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- SECTION 17: DISCLAIMERS & MIT LICENSE -->
            <section id="disclaimers" class="doc-section mb-14 pt-6 border-t border-slate-200">
                <h3 class="text-base font-bold text-slate-900 mb-2">Legal Notices & MIT License</h3>
                <p class="text-xs text-slate-500 leading-relaxed mb-3">
                    WhatsGateway is an independent open-source project maintained by <strong>Navneet Kumar Yadav</strong> and released under the permissive MIT License.
                </p>
                <p class="text-xs text-slate-500 leading-relaxed">
                    This project is not affiliated, endorsed, sponsored, or certified by Meta Platforms, Inc., WhatsApp Inc., or Google LLC. "WhatsApp" is a registered trademark of Meta Platforms, Inc. "Google" is a registered trademark of Google LLC.
                </p>
                <div class="mt-4 flex flex-wrap gap-4 text-xs font-semibold text-slate-600">
                    <a href="<?= e(base_url('privacy')) ?>" class="hover:text-slate-900 underline">Privacy Policy</a>
                    <a href="<?= e(base_url('terms')) ?>" class="hover:text-slate-900 underline">Terms of Service</a>
                    <a href="mailto:info.assistgroupofficial@gmail.com" class="hover:text-slate-900 underline">info.assistgroupofficial@gmail.com</a>
                </div>
            </section>

        </main>

        <!-- Right "On this page" TOC (Desktop Only) -->
        <aside class="hidden xl:block w-56 shrink-0 py-12 pl-6 sticky top-20 self-start text-xs border-l border-slate-100">
            <h5 class="font-bold text-slate-900 uppercase tracking-wider text-[11px] font-mono mb-3">On This Page</h5>
            <ul class="space-y-2 text-slate-500">
                <li><a href="#overview" class="toc-link hover:text-slate-900 transition-colors block">1. Overview</a></li>
                <li><a href="#architecture" class="toc-link hover:text-slate-900 transition-colors block">2. Architecture Flow</a></li>
                <li><a href="#environments" class="toc-link hover:text-slate-900 transition-colors block">3. Base URLs</a></li>
                <li><a href="#authentication" class="toc-link hover:text-slate-900 transition-colors block">4. Authentication</a></li>
                <li><a href="#rate-limiting" class="toc-link hover:text-slate-900 transition-colors block">5. Rate Limits</a></li>
                <li><a href="#send-message" class="toc-link hover:text-slate-900 transition-colors block">6. Send Message</a></li>
                <li><a href="#meta-cloud-format" class="toc-link hover:text-slate-900 transition-colors block">7. Meta Cloud Drop-In</a></li>
                <li><a href="#media-uploads" class="toc-link hover:text-slate-900 transition-colors block">8. Media Uploads</a></li>
                <li><a href="#ptt-audio" class="toc-link hover:text-slate-900 transition-colors block">9. Voice Notes (PTT)</a></li>
                <li><a href="#health-check" class="toc-link hover:text-slate-900 transition-colors block">10. Health Check</a></li>
                <li><a href="#anti-ban" class="toc-link hover:text-slate-900 transition-colors block">11. Anti-Ban Pacing</a></li>
                <li><a href="#webhooks" class="toc-link hover:text-slate-900 transition-colors block">12. Webhooks & HMAC</a></li>
                <li><a href="#google-contacts" class="toc-link hover:text-slate-900 transition-colors block">13. Google Contacts</a></li>
                <li><a href="#quickstart-curl" class="toc-link hover:text-slate-900 transition-colors block">14. Code Quickstarts</a></li>
                <li><a href="#status-codes" class="toc-link hover:text-slate-900 transition-colors block">16. Error Dictionary</a></li>
            </ul>

            <div class="mt-8 pt-6 border-t border-slate-100">
                <a href="#top" class="text-emerald-700 hover:text-emerald-800 font-semibold flex items-center gap-1">
                    <span>Back to top</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                </a>
            </div>
        </aside>

    </div>

    <!-- Client-Side Interactive Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            // 1. Mobile Documentation Sidebar Drawer Toggle
            const menuBtn = document.getElementById('doc-menu-btn');
            const sidebar = document.getElementById('doc-sidebar');
            const overlay = document.getElementById('doc-sidebar-overlay');
            const closeBtn = document.getElementById('doc-sidebar-close');

            function toggleDocSidebar(show) {
                if (show) {
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.remove('hidden');
                } else {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                }
            }

            if (menuBtn && sidebar && overlay) {
                menuBtn.addEventListener('click', () => toggleDocSidebar(true));
                overlay.addEventListener('click', () => toggleDocSidebar(false));
                if (closeBtn) closeBtn.addEventListener('click', () => toggleDocSidebar(false));

                // Close drawer on link click in mobile
                document.querySelectorAll('.doc-link').forEach(link => {
                    link.addEventListener('click', () => {
                        if (window.innerWidth < 1024) toggleDocSidebar(false);
                    });
                });
            }

            // 2. Language Code Tabs Switcher
            const tabs = document.querySelectorAll('.code-tab');
            const blocks = document.querySelectorAll('.code-snippet-block');

            tabs.forEach(tab => {
                tab.addEventListener('click', function () {
                    const lang = this.getAttribute('data-lang');

                    // Reset tabs
                    tabs.forEach(t => {
                        t.classList.remove('active', 'bg-white', 'text-slate-900', 'shadow-soft-xs', 'border', 'border-slate-200');
                        t.classList.add('text-slate-600');
                    });
                    this.classList.add('active', 'bg-white', 'text-slate-900', 'shadow-soft-xs', 'border', 'border-slate-200');
                    this.classList.remove('text-slate-600');

                    // Switch blocks
                    blocks.forEach(b => b.classList.add('hidden'));
                    const targetBlock = document.getElementById('code-snippet-' + lang);
                    if (targetBlock) targetBlock.classList.remove('hidden');
                });
            });

            // 3. Clipboard Copy Buttons
            document.querySelectorAll('.copy-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const targetId = this.getAttribute('data-clipboard');
                    const targetEl = document.getElementById(targetId);
                    if (!targetEl) return;

                    const textToCopy = targetEl.innerText || targetEl.textContent;
                    navigator.clipboard.writeText(textToCopy).then(() => {
                        const originalText = this.innerText;
                        this.innerText = 'Copied!';
                        this.classList.add('text-emerald-800', 'font-bold');
                        setTimeout(() => {
                            this.innerText = originalText;
                            this.classList.remove('text-emerald-800', 'font-bold');
                        }, 2000);
                    }).catch(err => {
                        console.error('Clipboard copy failed:', err);
                    });
                });
            });

            // 4. Quick Filter for Sidebar Navigation Links
            const searchInput = document.getElementById('doc-search');
            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    const query = this.value.toLowerCase().trim();
                    const navLinks = document.querySelectorAll('.doc-link');

                    navLinks.forEach(link => {
                        const text = link.textContent.toLowerCase();
                        const li = link.closest('li');
                        if (!li) return;

                        if (query === '' || text.includes(query)) {
                            li.style.display = '';
                        } else {
                            li.style.display = 'none';
                        }
                    });
                });
            }

        });
    </script>
</body>
</html>
