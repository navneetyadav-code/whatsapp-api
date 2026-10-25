<?php

declare(strict_types=1);

$user = current_user();
$seoTitle = 'WhatsGateway — Open-Source Developer WhatsApp API Gateway & Automation';
$seoDesc = 'Production-grade self-hosted WhatsApp API gateway powered by Baileys & PHP 8.2. Dual REST API format, Meta Cloud API compatibility, queue engine, chatbot rules, HMAC webhooks, and Google Contacts sync.';
?>
<!DOCTYPE html>
<html lang="en" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($seoTitle) ?></title>
    <meta name="description" content="<?= e($seoDesc) ?>">
    <link rel="canonical" href="<?= e(public_url('')) ?>">
    
    <!-- Favicon Suite (Gemini Generated) -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?= e(base_url('assets/favicon-32x32.png')) ?>">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= e(base_url('assets/favicon.png')) ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= e(base_url('assets/apple-touch-icon.png')) ?>">
    <link rel="shortcut icon" href="<?= e(base_url('favicon.ico')) ?>">
    <meta name="theme-color" content="#ffffff">

    <!-- Open Graph & Twitter Cards -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= e(public_url('')) ?>">
    <meta property="og:title" content="<?= e($seoTitle) ?>">
    <meta property="og:description" content="<?= e($seoDesc) ?>">
    <meta property="og:image" content="<?= e(public_url('assets/favicon.png')) ?>">
    <meta property="og:site_name" content="WhatsGateway">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($seoTitle) ?>">
    <meta name="twitter:description" content="<?= e($seoDesc) ?>">
    <meta name="twitter:image" content="<?= e(public_url('assets/favicon.png')) ?>">

    <!-- Typography (Inter & JetBrains Mono) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS Engine -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
                        mono: ['JetBrains Mono', 'ui-monospace', 'SFMono-Regular', 'Menlo', 'Monaco', 'Consolas', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            200: '#a7f3d0',
                            300: '#6ee7b7',
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                            wa: '#25D366',
                            wa_dark: '#075E54',
                            wa_teal: '#128C7E',
                        }
                    },
                    boxShadow: {
                        'soft-xs': '0 1px 2px 0 rgba(15, 23, 42, 0.05)',
                        'soft-sm': '0 1px 3px 0 rgba(15, 23, 42, 0.08), 0 1px 2px -1px rgba(15, 23, 42, 0.08)',
                        'soft-md': '0 4px 6px -1px rgba(15, 23, 42, 0.07), 0 2px 4px -2px rgba(15, 23, 42, 0.05)',
                        'soft-lg': '0 10px 15px -3px rgba(15, 23, 42, 0.08), 0 4px 6px -4px rgba(15, 23, 42, 0.04)',
                        'soft-xl': '0 20px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04)',
                        'emerald-glow': '0 4px 20px -2px rgba(16, 185, 129, 0.25)',
                    }
                }
            }
        };
    </script>
    <style>
        /* Professional Light Subtle Grid Background */
        .bg-grid-subtle {
            background-size: 32px 32px;
            background-image: 
                linear-gradient(to right, rgba(15, 23, 42, 0.035) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(15, 23, 42, 0.035) 1px, transparent 1px);
        }
        .bg-radial-soft {
            background: radial-gradient(circle at 50% 0%, rgba(16, 185, 129, 0.08) 0%, rgba(241, 245, 249, 0.5) 50%, #ffffff 80%);
        }
        pre::-webkit-scrollbar {
            height: 6px;
        }
        pre::-webkit-scrollbar-track {
            background: #0f172a;
        }
        pre::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 4px;
        }
        pre::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }
        details summary::-webkit-details-marker {
            display: none;
        }
    </style>
</head>
<body class="min-h-full flex flex-col font-sans bg-white text-slate-800 antialiased selection:bg-brand-500/20 selection:text-brand-900">

    <!-- Top Sticky Navigation Bar -->
    <header class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-slate-200/80 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between">
            
            <!-- Brand Logo & Badge -->
            <a href="<?= e(base_url('')) ?>" class="flex items-center gap-3 group focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 rounded-xl p-1 transition-all">
                <div class="relative flex-shrink-0">
                    <img src="<?= e(base_url('assets/favicon.png')) ?>" alt="WhatsGateway Logo" class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl object-cover shadow-soft-xs border border-slate-200 group-hover:scale-105 transition-transform duration-200">
                    <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 border-2 border-white rounded-full animate-pulse" title="Gateway Ready"></span>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-slate-900 text-base sm:text-lg tracking-tight group-hover:text-brand-600 transition-colors">WhatsGateway</span>
                    </div>
                    <span class="text-[11px] text-slate-500 font-medium hidden sm:block">Open-Source Developer Gateway</span>
                </div>
            </a>

            <!-- Desktop Navigation Links (Clean Focused Hierarchy) -->
            <nav class="hidden lg:flex items-center gap-7 text-sm font-semibold text-slate-600">
                <a href="#architecture" class="hover:text-slate-900 transition-colors">Architecture</a>
                <a href="#features" class="hover:text-slate-900 transition-colors">Features</a>
                <a href="#comparison" class="hover:text-slate-900 transition-colors">Comparison</a>
                <a href="#faq" class="hover:text-slate-900 transition-colors">FAQ</a>
                <a href="<?= e(base_url('docs')) ?>" class="text-brand-600 hover:text-brand-700 font-semibold transition-colors flex items-center gap-1">
                    <span>API Docs</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                </a>
            </nav>

            <!-- Actions & Auth Buttons -->
            <div class="flex items-center gap-3">
                <a href="https://github.com/navneetyadav-code/whatsapp-api" target="_blank" rel="noopener noreferrer" class="hidden sm:inline-flex items-center gap-2 text-xs font-semibold text-slate-700 hover:text-slate-900 bg-slate-50 hover:bg-slate-100 px-3.5 py-2 rounded-xl border border-slate-200 transition-all shadow-soft-xs">
                    <svg class="w-4 h-4 text-slate-700" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/></svg>
                    <span>GitHub</span>
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

                <!-- Mobile Hamburger Button -->
                <button id="mobile-menu-btn" type="button" class="lg:hidden p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500" aria-label="Toggle Navigation Menu" aria-expanded="false">
                    <svg id="hamburger-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    <svg id="close-icon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>

        <!-- Mobile Navigation Drawer -->
        <div id="mobile-menu" class="hidden lg:hidden border-t border-slate-200 bg-white/95 backdrop-blur-xl px-4 pt-4 pb-6 space-y-4 shadow-soft-lg">
            <div class="grid grid-cols-2 gap-2 text-sm font-medium">
                <a href="#architecture" class="mobile-nav-link px-3 py-2.5 rounded-xl text-slate-700 hover:text-slate-900 hover:bg-slate-100">Architecture</a>
                <a href="#features" class="mobile-nav-link px-3 py-2.5 rounded-xl text-slate-700 hover:text-slate-900 hover:bg-slate-100">Features</a>
                <a href="#comparison" class="mobile-nav-link px-3 py-2.5 rounded-xl text-slate-700 hover:text-slate-900 hover:bg-slate-100">Comparison</a>
                <a href="#faq" class="mobile-nav-link px-3 py-2.5 rounded-xl text-slate-700 hover:text-slate-900 hover:bg-slate-100">FAQ</a>
            </div>
            <div class="pt-3 border-t border-slate-200 flex flex-col gap-2.5">
                <a href="<?= e(base_url('docs')) ?>" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-emerald-50 text-emerald-800 font-semibold text-sm border border-emerald-200">
                    <span>Developer Documentation</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
                <div class="pt-1">
                    <?php if (!$user): ?>
                        <a href="<?= e(base_url('register')) ?>" class="w-full block text-center py-2.5 rounded-xl text-sm font-semibold text-white bg-slate-900 hover:bg-slate-800 shadow-soft-sm">Get Started Free</a>
                    <?php else: ?>
                        <a href="<?= e(base_url('dashboard')) ?>" class="w-full block text-center py-2.5 rounded-xl text-sm font-semibold text-white bg-slate-900 hover:bg-slate-800 shadow-soft-sm">Open Dashboard</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Flow -->
    <main class="flex-1">

        <!-- Split-Wise Hero Section (Desktop Split-Screen) -->
        <section class="relative pt-12 pb-16 sm:pt-16 sm:pb-24 lg:pt-20 lg:pb-28 bg-grid-subtle bg-radial-soft border-b border-slate-200/80 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 xl:gap-12 items-center">
                    
                    <!-- LEFT COLUMN: Headline, Value Proposition & Actions (7 cols) -->
                    <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                        
                        <!-- Status Pill Badge -->
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-soft-xs">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Open-Source &bull; Baileys Engine &bull; Dual REST API &bull; Google Sync</span>
                        </div>

                        <!-- Main Headline -->
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-slate-900 tracking-tight leading-[1.12]">
                            The WhatsApp API Gateway <br class="hidden sm:inline">
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 via-brand-600 to-teal-700">Built for Modern Teams</span>
                        </h1>

                        <!-- Subtitle -->
                        <p class="text-base sm:text-lg text-slate-600 max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
                            Link your existing WhatsApp personal or business number via QR scan or 8-digit pairing code. Dispatch high-throughput transactional alerts via flat REST or drop-in Meta Cloud schema, automate replies with keyword chatbots, and receive HMAC-signed webhooks with 100% data ownership.
                        </p>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3.5 pt-2">
                            <a href="<?= e(base_url('register')) ?>" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl font-bold text-white bg-slate-900 hover:bg-slate-800 shadow-soft-md hover:shadow-soft-lg transition-all text-sm sm:text-base">
                                <span>Deploy / Start Free</span>
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </a>

                            <a href="<?= e(base_url('docs')) ?>" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3.5 rounded-xl font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 shadow-soft-xs transition-all text-sm sm:text-base">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <span>API Documentation</span>
                            </a>

                            <a href="https://github.com/navneetyadav-code/whatsapp-api" target="_blank" rel="noopener noreferrer" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-3.5 rounded-xl font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 shadow-soft-xs transition-all text-sm sm:text-base">
                                <svg class="w-4 h-4 text-slate-700" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/></svg>
                                <span>GitHub</span>
                            </a>
                        </div>

                        <!-- 4 Verified Pillar Metrics -->
                        <div class="pt-6 border-t border-slate-200 grid grid-cols-2 sm:grid-cols-4 gap-3 text-left">
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                                <p class="text-xl sm:text-2xl font-black text-slate-900 font-mono tracking-tight">$0.00</p>
                                <p class="text-[11px] text-slate-500 font-medium mt-0.5">Per-Msg Gateway Fee</p>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                                <p class="text-xl sm:text-2xl font-black text-slate-900 font-mono tracking-tight">100%</p>
                                <p class="text-[11px] text-slate-500 font-medium mt-0.5">Self-Hosted Privacy</p>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                                <p class="text-xl sm:text-2xl font-black text-slate-900 font-mono tracking-tight">Dual API</p>
                                <p class="text-[11px] text-slate-500 font-medium mt-0.5">REST + Meta Schema</p>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                                <p class="text-xl sm:text-2xl font-black text-slate-900 font-mono tracking-tight">HMAC-256</p>
                                <p class="text-[11px] text-slate-500 font-medium mt-0.5">Signed Webhooks</p>
                            </div>
                        </div>

                    </div>

                    <!-- RIGHT COLUMN: Interactive Gateway Simulator & Live Chat Visualizer (5 cols) -->
                    <div class="lg:col-span-5">
                        <div class="rounded-2xl bg-slate-950 text-slate-100 border border-slate-800 shadow-soft-xl overflow-hidden">
                            
                            <!-- Header Bar -->
                            <div class="flex items-center justify-between px-4 py-3 bg-slate-900/90 border-b border-slate-800">
                                <div class="flex items-center space-x-2">
                                    <span class="w-3 h-3 rounded-full bg-rose-500/80 inline-block"></span>
                                    <span class="w-3 h-3 rounded-full bg-amber-500/80 inline-block"></span>
                                    <span class="w-3 h-3 rounded-full bg-emerald-500/80 inline-block"></span>
                                    <span class="text-xs font-mono text-slate-400 ml-2">gateway-runner.sh</span>
                                </div>
                                <span class="text-[10px] font-mono text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/20">POST /api/send</span>
                            </div>

                            <!-- Interactive Simulator View -->
                            <div class="p-5 space-y-4">
                                
                                <!-- Code Request Block -->
                                <div class="font-mono text-xs text-slate-300 bg-slate-900/60 p-3.5 rounded-xl border border-slate-800/80 overflow-x-auto leading-relaxed">
                                    <span class="text-purple-400">curl</span> -X POST <span class="text-emerald-300">"<?= e(public_url('api/send.php')) ?>"</span> \<br>
                                    &nbsp;&nbsp;-H <span class="text-sky-300">"X-API-Key: wa_live_YOUR_KEY"</span> \<br>
                                    &nbsp;&nbsp;-d '{<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;<span class="text-amber-300">"to"</span>: <span class="text-slate-200">"919876543210"</span>,<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;<span class="text-amber-300">"message"</span>: <span class="text-slate-200">"Order #84920 shipped! 🚀"</span>,<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;<span class="text-amber-300">"humanize"</span>: <span class="text-purple-400">true</span><br>
                                    &nbsp;&nbsp;}'
                                </div>

                                <!-- Action Button: Live Simulator -->
                                <div class="flex items-center justify-between gap-3">
                                    <button id="sim-send-btn" type="button" class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl text-xs font-mono font-bold bg-emerald-500 hover:bg-emerald-400 text-slate-950 transition-all shadow-emerald-glow">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span id="sim-btn-text">Test Dispatch Simulation</span>
                                    </button>
                                </div>

                                <!-- Phone Screen Simulation Card -->
                                <div class="rounded-xl bg-[#0b141a] p-3.5 border border-slate-800 text-slate-200 space-y-2">
                                    
                                    <div class="flex items-center justify-between text-[11px] pb-2 border-b border-slate-800/80 text-slate-400">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-emerald-600 flex items-center justify-center text-white text-[10px] font-bold">WA</div>
                                            <span class="font-semibold text-slate-200">+91 98765 43210</span>
                                        </div>
                                        <span id="sim-status-badge" class="px-2 py-0.5 rounded text-[10px] font-mono bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">Delivered</span>
                                    </div>

                                    <!-- Chat Bubbles -->
                                    <div class="space-y-2 text-xs pt-1">
                                        <div class="bg-[#202c33] text-slate-300 p-2.5 rounded-lg rounded-tl-none max-w-[85%]">
                                            <span>Hi, where is my package #84920?</span>
                                            <div class="text-[9px] text-slate-400 text-right mt-1">11:42 AM</div>
                                        </div>

                                        <div id="sim-reply-bubble" class="bg-[#005c4b] text-white p-2.5 rounded-lg rounded-tr-none ml-auto max-w-[85%] transition-all">
                                            <span>Order #84920 shipped! 🚀 Track live: <span class="underline text-emerald-200">hub.me/trk/84920</span></span>
                                            <div class="text-[9px] text-emerald-200/80 text-right mt-1 flex items-center justify-end gap-1">
                                                <span>11:42 AM</span>
                                                <span class="text-sky-300 font-bold">&check;&check;</span>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <!-- Real HTTP Response Telemetry -->
                                <div class="bg-slate-900/90 rounded-xl p-3 border border-slate-800/80 font-mono text-[11px] text-slate-400 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                        <span class="text-slate-300">HTTP 200 Queued</span>
                                    </div>
                                    <span>message_id: <strong class="text-emerald-400 font-bold">1042</strong> &bull; 12ms</span>
                                </div>

                            </div>

                        </div>
                    </div>

                </div>

            </div>
        </section>

        <!-- Architecture Flow Section -->
        <section id="architecture" class="py-20 bg-slate-50 border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <span class="text-xs font-mono font-bold text-emerald-700 uppercase tracking-widest bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">System Architecture</span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mt-3 tracking-tight">Decoupled Asynchronous Pipeline</h2>
                    <p class="text-slate-600 mt-2 text-base">
                        Engineered for zero downtime. Your application never blocks waiting on WhatsApp socket handshakes.
                    </p>
                </div>

                <!-- 3 Flow Steps -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    <!-- Step 1 -->
                    <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-soft-sm hover:shadow-soft-md transition-all flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-mono font-bold text-sm border border-emerald-200">01</span>
                                <span class="text-xs font-mono font-semibold text-slate-500">HTTP REST</span>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 mb-2">Ingestion & Auth Gateway</h3>
                            <p class="text-sm text-slate-600 leading-relaxed">
                                Your application sends a <code class="text-emerald-700 bg-emerald-50 px-1 py-0.5 rounded font-mono text-xs">POST /api/send</code>. PHP 8.2 validates the API token, enforces rate limits, normalizes the recipient phone to international format, and validates media attachments.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-slate-100 font-mono text-[11px] text-emerald-700">
                            &check; Responds with <code class="bg-slate-100 px-1 py-0.5 rounded text-slate-800">200 Queued</code> in &lt;15ms
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-soft-sm hover:shadow-soft-md transition-all flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center font-mono font-bold text-sm border border-indigo-200">02</span>
                                <span class="text-xs font-mono font-semibold text-slate-500">MySQL / TiDB</span>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 mb-2">Persistent Queue Buffer</h3>
                            <p class="text-sm text-slate-600 leading-relaxed">
                                Messages enter the <code class="text-indigo-700 bg-indigo-50 px-1 py-0.5 rounded font-mono text-xs">message_queue</code> table with state <code class="text-amber-700 bg-amber-50 px-1 py-0.5 rounded">pending</code>. Database persistence ensures messages survive server restarts or temporary network glitches.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-slate-100 font-mono text-[11px] text-indigo-700">
                            &check; Automatic retries with exponential backoff
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-soft-sm hover:shadow-soft-md transition-all flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="w-8 h-8 rounded-lg bg-sky-50 text-sky-700 flex items-center justify-center font-mono font-bold text-sm border border-sky-200">03</span>
                                <span class="text-xs font-mono font-semibold text-slate-500">Node Baileys</span>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 mb-2">Baileys Dispatch Engine</h3>
                            <p class="text-sm text-slate-600 leading-relaxed">
                                The Node.js Baileys worker maintains a persistent multi-device WebSocket connection to WhatsApp. It simulates human typing presence (<code class="text-sky-700 bg-sky-50 px-1 py-0.5 rounded font-mono text-xs">composing</code>), applies anti-ban pacing, and dispatches to WhatsApp servers.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-slate-100 font-mono text-[11px] text-sky-700">
                            &check; Multi-File Auth Session Persistence
                        </div>
                    </div>

                </div>

                <!-- Inbound Loop Card -->
                <div class="mt-6 p-6 rounded-2xl bg-white border border-slate-200 shadow-soft-sm flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center font-bold text-lg border border-purple-200 flex-shrink-0">
                            &harr;
                        </div>
                        <div>
                            <h4 class="text-base font-bold text-slate-900">Inbound Webhooks & Automated Chatbot Processing</h4>
                            <p class="text-xs sm:text-sm text-slate-600 mt-0.5">
                                When a customer replies or a message delivery state changes, Baileys immediately fires your keyword chatbot rules and forwards an HMAC-SHA256 signed event payload to your webhook endpoint.
                            </p>
                        </div>
                    </div>
                    <a href="#code-playground" class="flex-shrink-0 text-xs font-semibold px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white transition-colors">
                        Inspect Schemas &rarr;
                    </a>
                </div>

            </div>
        </section>

        <!-- Core Features Grid -->
        <section id="features" class="py-20 bg-white border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <span class="text-xs font-mono font-bold text-emerald-700 uppercase tracking-widest bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">Production Capabilities</span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mt-3 tracking-tight">Everything for Professional Messaging</h2>
                    <p class="text-slate-600 mt-2 text-base">
                        Battle-tested features built for developers, SaaS platforms, and enterprise support automation.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    
                    <!-- Feature 1 -->
                    <div class="p-7 rounded-2xl bg-slate-50/60 border border-slate-200 hover:border-slate-300 hover:bg-white shadow-soft-xs hover:shadow-soft-md transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center mb-5 border border-emerald-200 group-hover:scale-105 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">QR Code & 8-Digit Pairing</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Link any personal or business WhatsApp number in seconds. Scan the in-dashboard QR code or use the 8-digit phone pairing code without pointing a physical camera.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="p-7 rounded-2xl bg-slate-50/60 border border-slate-200 hover:border-slate-300 hover:bg-white shadow-soft-xs hover:shadow-soft-md transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-800 flex items-center justify-center mb-5 border border-indigo-200 group-hover:scale-105 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">Asynchronous Queuing & Throttle</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Enqueue thousands of messages with sub-second response times. The background worker throttles traffic safely with configurable pacing (<code class="bg-indigo-50 text-indigo-700 px-1 py-0.5 rounded font-mono text-xs">SEND_DELAY_MS</code>) and typing indicators.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="p-7 rounded-2xl bg-slate-50/60 border border-slate-200 hover:border-slate-300 hover:bg-white shadow-soft-xs hover:shadow-soft-md transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-sky-100 text-sky-800 flex items-center justify-center mb-5 border border-sky-200 group-hover:scale-105 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">Automated Keyword Chatbots</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Configure instant auto-replies for incoming queries. Build exact match keywords, substring contains rules, and welcome flows to assist users 24/7 without extra bot server overhead.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="p-7 rounded-2xl bg-slate-50/60 border border-slate-200 hover:border-slate-300 hover:bg-white shadow-soft-xs hover:shadow-soft-md transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-800 flex items-center justify-center mb-5 border border-purple-200 group-hover:scale-105 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">HMAC-SHA256 Signed Webhooks</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Subscribe to HTTPS callbacks for inbound messages, delivery receipts, and read confirmations. Every webhook payload is signed with your shared secret to guarantee authenticity.
                        </p>
                    </div>

                    <!-- Feature 5 -->
                    <div class="p-7 rounded-2xl bg-slate-50/60 border border-slate-200 hover:border-slate-300 hover:bg-white shadow-soft-xs hover:shadow-soft-md transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-rose-100 text-rose-800 flex items-center justify-center mb-5 border border-rose-200 group-hover:scale-105 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">PDF, Documents & Rich Media</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Attach invoices, images, catalogs, PDFs, and audio recordings seamlessly. Supports direct remote HTTP URLs, Base64 data strings, or standard multipart form uploads.
                        </p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="p-7 rounded-2xl bg-slate-50/60 border border-slate-200 hover:border-slate-300 hover:bg-white shadow-soft-xs hover:shadow-soft-md transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center mb-5 border border-amber-200 group-hover:scale-105 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">CSV Broadcasts & Campaigns</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Manage contact books, import customer records from CSV or Google Contacts, and schedule personalized broadcast campaigns with custom tags (<code class="bg-amber-50 text-amber-800 px-1 py-0.5 rounded font-mono text-xs">{name}</code>).
                        </p>
                    </div>

                </div>

            </div>
        </section>

        <!-- Live Code Playground Section -->
        <section id="code-playground" class="py-20 bg-slate-50 border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-3xl mx-auto mb-12">
                    <span class="text-xs font-mono font-bold text-emerald-700 uppercase tracking-widest bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">Developer Integration</span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mt-3 tracking-tight">Code Once, Dispatch Anywhere</h2>
                    <p class="text-slate-600 mt-2 text-base">
                        Test real cURL, Node.js, Python, PHP, or drop-in Meta Cloud payloads. Authenticate with a single API token.
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
                    
                    <!-- Code Snippets (7 cols) -->
                    <div class="lg:col-span-7 flex flex-col rounded-2xl bg-slate-950 text-slate-100 border border-slate-800 shadow-soft-xl overflow-hidden">
                        
                        <!-- Tabs Bar -->
                        <div class="bg-slate-900 px-4 py-3 border-b border-slate-800 flex flex-wrap items-center justify-between gap-2">
                            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0" role="tablist">
                                <button type="button" class="code-tab active px-3 py-1.5 rounded-lg text-xs font-mono font-medium transition-all bg-emerald-500/20 text-emerald-400 border border-emerald-500/30" data-lang="curl">cURL</button>
                                <button type="button" class="code-tab px-3 py-1.5 rounded-lg text-xs font-mono font-medium transition-all text-slate-400 hover:text-white hover:bg-white/5" data-lang="node">Node.js</button>
                                <button type="button" class="code-tab px-3 py-1.5 rounded-lg text-xs font-mono font-medium transition-all text-slate-400 hover:text-white hover:bg-white/5" data-lang="python">Python</button>
                                <button type="button" class="code-tab px-3 py-1.5 rounded-lg text-xs font-mono font-medium transition-all text-slate-400 hover:text-white hover:bg-white/5" data-lang="php">PHP</button>
                                <button type="button" class="code-tab px-3 py-1.5 rounded-lg text-xs font-mono font-medium transition-all text-slate-400 hover:text-white hover:bg-white/5" data-lang="meta">Meta Cloud</button>
                            </div>
                            
                            <!-- Copy Button -->
                            <button id="copy-code-btn" type="button" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-mono text-slate-400 hover:text-white hover:bg-white/10 transition-colors border border-white/10" aria-label="Copy Code to Clipboard">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                                <span id="copy-btn-text">Copy</span>
                            </button>
                        </div>

                        <!-- Snippet Code Panels -->
                        <div class="p-5 font-mono text-xs sm:text-[13px] leading-relaxed flex-1 overflow-x-auto text-slate-200">
                            
                            <!-- cURL -->
                            <div id="snippet-curl" class="code-snippet-panel">
<pre><code><span class="text-slate-500"># Send a transactional text alert</span>
curl -X POST "<?= e(public_url('api/send.php')) ?>" \
  -H "<span class="text-emerald-400">X-API-Key</span>: <span class="text-amber-300">wa_live_YOUR_API_KEY</span>" \
  -H "Content-Type: application/json" \
  -d '{
    "<span class="text-sky-300">to</span>": "<span class="text-slate-200">919876543210</span>",
    "<span class="text-sky-300">message</span>": "<span class="text-slate-200">Your verification OTP is *582910*. Valid for 10 minutes.</span>",
    "<span class="text-sky-300">humanize</span>": <span class="text-purple-400">true</span>
  }'</code></pre>
                            </div>

                            <!-- Node.js -->
                            <div id="snippet-node" class="code-snippet-panel hidden">
<pre><code><span class="text-slate-500">// Node.js 18+ native fetch</span>
<span class="text-purple-400">const</span> response = <span class="text-purple-400">await</span> fetch(<span class="text-emerald-300">'<?= e(public_url('api/send.php')) ?>'</span>, {
  method: <span class="text-emerald-300">'POST'</span>,
  headers: {
    <span class="text-emerald-300">'Authorization'</span>: <span class="text-amber-300">'Bearer wa_live_YOUR_API_KEY'</span>,
    <span class="text-emerald-300">'Content-Type'</span>: <span class="text-emerald-300">'application/json'</span>
  },
  body: JSON.stringify({
    to: <span class="text-emerald-300">'919876543210'</span>,
    message: <span class="text-emerald-300">'Hi Alex! Your order #84920 has shipped 📦'</span>,
    media_url: <span class="text-emerald-300">'https://yourdomain.com/invoices/84920.pdf'</span>,
    type: <span class="text-emerald-300">'document'</span>
  })
});

<span class="text-purple-400">const</span> data = <span class="text-purple-400">await</span> response.json();
console.log(<span class="text-emerald-300">'Message queued:'</span>, data.message_id);</code></pre>
                            </div>

                            <!-- Python -->
                            <div id="snippet-python" class="code-snippet-panel hidden">
<pre><code><span class="text-slate-500"># Python requests</span>
<span class="text-purple-400">import</span> requests

url = <span class="text-emerald-300">"<?= e(public_url('api/send.php')) ?>"</span>
headers = {
    <span class="text-emerald-300">"X-API-Key"</span>: <span class="text-amber-300">"wa_live_YOUR_API_KEY"</span>,
    <span class="text-emerald-300">"Content-Type"</span>: <span class="text-emerald-300">"application/json"</span>
}
payload = {
    <span class="text-emerald-300">"to"</span>: <span class="text-emerald-300">"919876543210"</span>,
    <span class="text-emerald-300">"message"</span>: <span class="text-emerald-300">"Hello from Python! Server CPU alert: 94%"</span>,
    <span class="text-emerald-300">"humanize"</span>: <span class="text-purple-400">True</span>
}

res = requests.post(url, json=payload, headers=headers)
print(res.status_code, res.json())</code></pre>
                            </div>

                            <!-- PHP -->
                            <div id="snippet-php" class="code-snippet-panel hidden">
<pre><code><span class="text-slate-500">&lt;?php</span>
<span class="text-slate-500">// PHP cURL client</span>
$ch = curl_init(<span class="text-emerald-300">'<?= e(public_url('api/send.php')) ?>'</span>);
curl_setopt_array($ch, [
    CURLOPT_POST => <span class="text-purple-400">true</span>,
    CURLOPT_RETURNTRANSFER => <span class="text-purple-400">true</span>,
    CURLOPT_HTTPHEADER => [
        <span class="text-emerald-300">'X-API-Key: '</span> . <span class="text-amber-300">'wa_live_YOUR_API_KEY'</span>,
        <span class="text-emerald-300">'Content-Type: application/json'</span>
    ],
    CURLOPT_POSTFIELDS => json_encode([
        <span class="text-emerald-300">'to'</span> => <span class="text-emerald-300">'919876543210'</span>,
        <span class="text-emerald-300">'message'</span> => <span class="text-emerald-300">'Your appointment is confirmed for 3:00 PM.'</span>,
    ])
]);
$result = json_decode(curl_exec($ch), <span class="text-purple-400">true</span>);
curl_close($ch);</code></pre>
                            </div>

                            <!-- Meta Cloud API Drop-In -->
                            <div id="snippet-meta" class="code-snippet-panel hidden">
<pre><code><span class="text-slate-500"># Direct Meta Cloud API Schema Drop-In (Zero code rewrite)</span>
curl -X POST "<?= e(public_url('api/send.php')) ?>" \
  -H "<span class="text-emerald-400">Authorization</span>: Bearer <span class="text-amber-300">wa_live_YOUR_API_KEY</span>" \
  -H "Content-Type: application/json" \
  -d '{
    "<span class="text-sky-300">messaging_product</span>": "<span class="text-emerald-300">whatsapp</span>",
    "<span class="text-sky-300">to</span>": "<span class="text-slate-200">919876543210</span>",
    "<span class="text-sky-300">type</span>": "<span class="text-emerald-300">text</span>",
    "<span class="text-sky-300">text</span>": {
      "<span class="text-sky-300">body</span>": "<span class="text-slate-200">Sent via official Meta Cloud API payload schema!</span>"
    }
  }'</code></pre>
                            </div>

                        </div>

                        <!-- Footer indicator -->
                        <div class="px-5 py-2.5 bg-slate-900 border-t border-slate-800 flex items-center justify-between text-[11px] text-slate-400">
                            <span>Endpoint: <code class="text-slate-200 font-mono">POST /api/send.php</code></span>
                            <span class="text-emerald-400 font-medium">&bull; Single Key / Bearer Auth</span>
                        </div>
                    </div>

                    <!-- Verified Live JSON Response (5 cols) -->
                    <div class="lg:col-span-5 flex flex-col rounded-2xl bg-slate-950 text-slate-100 border border-slate-800 shadow-soft-xl overflow-hidden">
                        
                        <div class="bg-slate-900 px-4 py-3 border-b border-slate-800 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                <span class="text-xs font-mono font-bold text-white">HTTP 200 OK Response</span>
                            </div>
                            <span class="text-[11px] font-mono text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/20">application/json</span>
                        </div>

                        <div class="p-5 font-mono text-xs sm:text-[12px] leading-relaxed flex-1 overflow-x-auto text-slate-200 bg-slate-950/80">
<pre><code>{
  "<span class="text-emerald-400">ok</span>": <span class="text-purple-400">true</span>,
  "<span class="text-emerald-400">status</span>": "<span class="text-amber-300">queued</span>",
  "<span class="text-emerald-400">message_id</span>": <span class="text-sky-300">1042</span>,
  "<span class="text-emerald-400">request_id</span>": <span class="text-sky-300">582</span>,
  "<span class="text-emerald-400">recipient</span>": "<span class="text-slate-200">919876543210</span>",
  "<span class="text-emerald-400">type</span>": "<span class="text-slate-200">text</span>",
  "<span class="text-emerald-400">has_media</span>": <span class="text-purple-400">false</span>,

  <span class="text-slate-500">// Meta Cloud API Compatibility</span>
  "<span class="text-emerald-400">messaging_product</span>": "<span class="text-slate-200">whatsapp</span>",
  "<span class="text-emerald-400">contacts</span>": [
    {
      "<span class="text-sky-300">input</span>": "<span class="text-slate-200">919876543210</span>",
      "<span class="text-sky-300">wa_id</span>": "<span class="text-slate-200">919876543210</span>"
    }
  ],
  "<span class="text-emerald-400">messages</span>": [
    {
      "<span class="text-sky-300">id</span>": "<span class="text-slate-200">wamid.b29e01f4c78a...</span>",
      "<span class="text-sky-300">message_status</span>": "<span class="text-emerald-400">accepted</span>"
    }
  ]
}</code></pre>
                        </div>

                        <div class="px-5 py-2.5 bg-slate-900 border-t border-slate-800 text-[11px] text-slate-400 flex items-center justify-between">
                            <span>Dual Format Output</span>
                            <span class="text-emerald-400 font-mono">0ms Worker Lock</span>
                        </div>
                    </div>

                </div>

            </div>
        </section>

        <!-- Google OAuth & Contacts Integration Section -->
        <section id="google-sync" class="py-20 bg-white border-b border-slate-200">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="rounded-3xl bg-slate-50 border border-slate-200/90 p-8 sm:p-12 relative overflow-hidden shadow-soft-sm">
                    
                    <div class="relative z-10 max-w-3xl">
                        
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-white text-slate-800 border border-slate-200 mb-6 shadow-soft-xs">
                            <svg class="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                            <span>Google API Services User Data Policy Compliance</span>
                        </div>

                        <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                            Secure Google Contacts Integration
                        </h2>
                        
                        <p class="mt-3 text-slate-600 text-sm sm:text-base leading-relaxed">
                            Sync your existing customer contacts directly from your authorized Google Account. We use standard OAuth 2.0 PKCE to request strictly read-only access (<code class="bg-white border border-slate-200 text-emerald-800 px-1 py-0.5 rounded font-mono text-xs">contacts.readonly</code>) solely for populating your address book.
                        </p>

                        <!-- Strict Compliance Guarantees -->
                        <div class="mt-8 space-y-3.5 text-xs sm:text-sm text-slate-700">
                            
                            <div class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span>
                                    <strong class="text-slate-900">Strict Limited Use Compliance:</strong> WhatsGateway’s use and transfer to any other app of information received from Google APIs will adhere to the <a href="https://developers.google.com/terms/api-services-user-data-policy" target="_blank" rel="noopener noreferrer" class="text-emerald-700 font-semibold underline hover:text-emerald-800">Google API Services User Data Policy</a>, including the Limited Use requirements.
                                </span>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span>
                                    <strong class="text-slate-900">Zero Resale & No Ads:</strong> Synced Google contact details are never shared with third parties, never sold to data brokers, and never used for behavioral advertising or model training.
                                </span>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span>
                                    <strong class="text-slate-900">Full User Control:</strong> Disconnect your Google Account and wipe all imported records with a single click in your dashboard at any time, or revoke access directly from your Google Security Settings.
                                </span>
                            </div>

                        </div>

                        <!-- Policy Buttons -->
                        <div class="mt-8 flex flex-wrap gap-3">
                            <a href="<?= e(base_url('privacy')) ?>" class="text-xs font-semibold px-4 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-800 transition-colors border border-slate-300 shadow-soft-xs">
                                Read Privacy Policy &rarr;
                            </a>
                            <a href="<?= e(base_url('terms')) ?>" class="text-xs font-semibold px-4 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-800 transition-colors border border-slate-300 shadow-soft-xs">
                                Read Terms of Service &rarr;
                            </a>
                        </div>

                    </div>

                </div>

            </div>
        </section>

        <!-- Architectural & Cost Comparison Table -->
        <section id="comparison" class="py-20 bg-slate-50 border-b border-slate-200">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <span class="text-xs font-mono font-bold text-emerald-700 uppercase tracking-widest bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">Transparent Breakdown</span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mt-3 tracking-tight">How It Compares</h2>
                    <p class="text-slate-600 mt-2 text-base">
                        Compare self-hosting our open-source gateway against official Meta Cloud and commercial SaaS brokers.
                    </p>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-soft-sm">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-slate-100/70 border-b border-slate-200 text-slate-700 font-mono">
                            <tr>
                                <th class="p-4 sm:p-5 font-semibold">Capability / Metric</th>
                                <th class="p-4 sm:p-5 font-bold text-emerald-900 bg-emerald-50/70 border-x border-emerald-200">WhatsGateway (Self-Hosted)</th>
                                <th class="p-4 sm:p-5 font-semibold text-slate-600">Meta WhatsApp Cloud API</th>
                                <th class="p-4 sm:p-5 font-semibold text-slate-600">Commercial SaaS Brokers</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-slate-700">
                            
                            <tr>
                                <td class="p-4 sm:p-5 font-bold text-slate-900">Gateway Per-Message Markup</td>
                                <td class="p-4 sm:p-5 font-bold text-emerald-700 bg-emerald-50/30 border-x border-emerald-200 font-mono">$0.00 (Zero)</td>
                                <td class="p-4 sm:p-5 text-slate-600">Per-conversation Meta charges</td>
                                <td class="p-4 sm:p-5 text-slate-600">$0.005 &ndash; $0.05 extra per msg</td>
                            </tr>

                            <tr>
                                <td class="p-4 sm:p-5 font-bold text-slate-900">Setup & Phone Linking</td>
                                <td class="p-4 sm:p-5 font-bold text-emerald-700 bg-emerald-50/30 border-x border-emerald-200">Instant QR / 8-digit Pairing Code</td>
                                <td class="p-4 sm:p-5 text-slate-600">Requires Meta Business Verification</td>
                                <td class="p-4 sm:p-5 text-slate-600">Complex onboarding & review</td>
                            </tr>

                            <tr>
                                <td class="p-4 sm:p-5 font-bold text-slate-900">Database & Session Control</td>
                                <td class="p-4 sm:p-5 font-bold text-emerald-700 bg-emerald-50/30 border-x border-emerald-200">100% on your private server/DB</td>
                                <td class="p-4 sm:p-5 text-slate-600">Hosted in Meta cloud</td>
                                <td class="p-4 sm:p-5 text-slate-600">Vendor cloud lock-in</td>
                            </tr>

                            <tr>
                                <td class="p-4 sm:p-5 font-bold text-slate-900">API Payload Compatibility</td>
                                <td class="p-4 sm:p-5 font-bold text-emerald-700 bg-emerald-50/30 border-x border-emerald-200">Flat REST + Drop-in Meta Cloud schema</td>
                                <td class="p-4 sm:p-5 text-slate-600">Meta Graph schema only</td>
                                <td class="p-4 sm:p-5 text-slate-600">Proprietary vendor SDKs</td>
                            </tr>

                            <tr>
                                <td class="p-4 sm:p-5 font-bold text-slate-900">Inbound Webhooks</td>
                                <td class="p-4 sm:p-5 font-bold text-emerald-700 bg-emerald-50/30 border-x border-emerald-200">HMAC-SHA256 Signed POST Callbacks</td>
                                <td class="p-4 sm:p-5 text-slate-600">Hub signature validation</td>
                                <td class="p-4 sm:p-5 text-slate-600">Varies / Rate-limited</td>
                            </tr>

                            <tr>
                                <td class="p-4 sm:p-5 font-bold text-slate-900">Built-in Keyword Chatbots</td>
                                <td class="p-4 sm:p-5 font-bold text-emerald-700 bg-emerald-50/30 border-x border-emerald-200">Included &bull; Instant auto-reply rules</td>
                                <td class="p-4 sm:p-5 text-slate-600">Requires external bot server</td>
                                <td class="p-4 sm:p-5 text-slate-600">Expensive add-on tier</td>
                            </tr>

                            <tr>
                                <td class="p-4 sm:p-5 font-bold text-slate-900">Google Contacts Sync</td>
                                <td class="p-4 sm:p-5 font-bold text-emerald-700 bg-emerald-50/30 border-x border-emerald-200">Built-in Google OAuth2 PKCE</td>
                                <td class="p-4 sm:p-5 text-slate-600">None</td>
                                <td class="p-4 sm:p-5 text-slate-600">Requires third-party sync tool</td>
                            </tr>

                        </tbody>
                    </table>
                </div>

            </div>
        </section>

        <!-- Technical Developer FAQ Section -->
        <section id="faq" class="py-20 bg-white border-b border-slate-200">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-2xl mx-auto mb-14">
                    <span class="text-xs font-mono font-bold text-emerald-700 uppercase tracking-widest bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">Frequently Asked Questions</span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mt-3 tracking-tight">Direct Answers for Engineers</h2>
                    <p class="text-slate-600 mt-2 text-base">
                        Clear technical explanations with no marketing fluff.
                    </p>
                </div>

                <div class="space-y-4">
                    
                    <details class="group p-5 rounded-2xl bg-slate-50/80 border border-slate-200 transition-all [&_summary::-webkit-details-marker]:none">
                        <summary class="flex items-center justify-between cursor-pointer list-none text-slate-900 font-bold text-base sm:text-lg">
                            <span>Do I need a verified Meta Business Manager account to use this?</span>
                            <span class="w-7 h-7 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-500 group-open:rotate-180 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </span>
                        </summary>
                        <div class="mt-4 text-sm text-slate-600 leading-relaxed border-t border-slate-200 pt-4">
                            No. Unlike the official Meta Cloud API which mandates submitting business registration papers and credit checks, this gateway uses the Baileys multi-device socket protocol. You can link any standard or business WhatsApp number simply by scanning the QR code or submitting an 8-digit phone pairing code.
                        </div>
                    </details>

                    <details class="group p-5 rounded-2xl bg-slate-50/80 border border-slate-200 transition-all [&_summary::-webkit-details-marker]:none">
                        <summary class="flex items-center justify-between cursor-pointer list-none text-slate-900 font-bold text-base sm:text-lg">
                            <span>How does this gateway protect against account bans?</span>
                            <span class="w-7 h-7 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-500 group-open:rotate-180 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </span>
                        </summary>
                        <div class="mt-4 text-sm text-slate-600 leading-relaxed border-t border-slate-200 pt-4">
                            WhatsApp flags numbers when high volumes of unsolicited messages are blasted simultaneously. WhatsGateway implements several anti-ban protections:
                            <ul class="list-disc pl-5 mt-2 space-y-1 text-slate-600">
                                <li>Configurable queue pacing delays (<code class="bg-white px-1 py-0.5 rounded text-emerald-800 border border-slate-200 font-mono text-xs">SEND_DELAY_MS</code>, defaulting to 2500ms between messages).</li>
                                <li>Automatic human typing presence updates (<code class="bg-white px-1 py-0.5 rounded text-emerald-800 border border-slate-200 font-mono text-xs">sendPresenceUpdate('composing')</code>).</li>
                                <li>We strongly advise sending transactional notifications and reminders only to users who have explicitly opted in.</li>
                            </ul>
                        </div>
                    </details>

                    <details class="group p-5 rounded-2xl bg-slate-50/80 border border-slate-200 transition-all [&_summary::-webkit-details-marker]:none">
                        <summary class="flex items-center justify-between cursor-pointer list-none text-slate-900 font-bold text-base sm:text-lg">
                            <span>Where are WhatsApp session keys and messages stored?</span>
                            <span class="w-7 h-7 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-500 group-open:rotate-180 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </span>
                        </summary>
                        <div class="mt-4 text-sm text-slate-600 leading-relaxed border-t border-slate-200 pt-4">
                            All session keys and credentials are saved locally in your server's <code class="bg-white px-1 py-0.5 rounded text-emerald-800 border border-slate-200 font-mono text-xs">whatsapp-api-data/sessions/</code> directory using Baileys' multi-file auth state. Messages, delivery states, and contact records reside directly in your local MySQL / TiDB database. Nothing is transmitted to external servers or third-party brokers.
                        </div>
                    </details>

                    <details class="group p-5 rounded-2xl bg-slate-50/80 border border-slate-200 transition-all [&_summary::-webkit-details-marker]:none">
                        <summary class="flex items-center justify-between cursor-pointer list-none text-slate-900 font-bold text-base sm:text-lg">
                            <span>How does the Meta Cloud API drop-in compatibility work?</span>
                            <span class="w-7 h-7 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-500 group-open:rotate-180 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </span>
                        </summary>
                        <div class="mt-4 text-sm text-slate-600 leading-relaxed border-t border-slate-200 pt-4">
                            If you have existing software built for Meta's official Cloud API, you don't need to rewrite your payload generators. Simply point your HTTP base URL to this gateway and pass your API key as the <code class="bg-white px-1 py-0.5 rounded text-emerald-800 border border-slate-200 font-mono text-xs">Authorization: Bearer</code> token. The endpoint parses Meta schemas (including text bodies, templates, documents, and contacts) and returns compliant JSON response bodies.
                        </div>
                    </details>

                    <details class="group p-5 rounded-2xl bg-slate-50/80 border border-slate-200 transition-all [&_summary::-webkit-details-marker]:none">
                        <summary class="flex items-center justify-between cursor-pointer list-none text-slate-900 font-bold text-base sm:text-lg">
                            <span>What are the minimum hardware requirements?</span>
                            <span class="w-7 h-7 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-500 group-open:rotate-180 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </span>
                        </summary>
                        <div class="mt-4 text-sm text-slate-600 leading-relaxed border-t border-slate-200 pt-4">
                            Because the architecture is lightweight (PHP 8.2 + Node.js Baileys), a standard 1 vCPU / 1GB RAM VPS (costing ~$4-$5/month on Hetzner, DigitalOcean, or Linode) is more than sufficient to comfortably handle queues and continuous worker socket loops.
                        </div>
                    </details>

                </div>

            </div>
        </section>

        <!-- Final Conversion CTA -->
        <section class="py-20 bg-slate-900 text-white relative overflow-hidden">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
                <h2 class="text-3xl sm:text-5xl font-extrabold text-white tracking-tight leading-tight">
                    Take Full Control of Your WhatsApp Messaging
                </h2>
                
                <p class="mt-4 text-base sm:text-lg text-slate-300 max-w-2xl mx-auto leading-relaxed">
                    Say goodbye to per-message markup, complex business approval gates, and vendor lock-in. Deploy your private instance in minutes.
                </p>

                <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                    <a href="<?= e(base_url('register')) ?>" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl font-bold text-slate-950 bg-emerald-400 hover:bg-emerald-300 shadow-emerald-glow transition-all text-base">
                        <span>Deploy / Start Free</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                    <a href="<?= e(base_url('docs')) ?>" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-4 rounded-xl font-semibold text-white bg-slate-800 hover:bg-slate-700 border border-slate-700 transition-all text-base">
                        <span>Read API Docs</span>
                    </a>
                </div>
            </div>
        </section>

    </main>

    <!-- Comprehensive Footer -->
    <footer class="bg-white border-t border-slate-200 py-14 text-slate-500 text-xs sm:text-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 pb-12 border-b border-slate-200">
                
                <!-- Col 1: Brand Info -->
                <div class="md:col-span-1 space-y-4">
                    <div class="flex items-center gap-3">
                        <img src="<?= e(base_url('assets/favicon.png')) ?>" alt="WhatsGateway Logo" class="w-8 h-8 rounded-lg object-cover shadow-soft-xs border border-slate-200">
                        <span class="font-bold text-slate-900 text-base">WhatsGateway</span>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Open-source, self-hosted WhatsApp messaging gateway and automation engine for modern developers.
                    </p>
                    <div class="inline-flex items-center gap-2 text-[11px] text-emerald-700 font-mono">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>MIT Licensed Open-Source Project</span>
                    </div>
                </div>

                <!-- Col 2: Navigation Links -->
                <div>
                    <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider mb-4 font-mono">Platform</h4>
                    <ul class="space-y-2.5 text-xs text-slate-600">
                        <li><a href="#architecture" class="hover:text-slate-900 transition-colors">Architecture Flow</a></li>
                        <li><a href="#features" class="hover:text-slate-900 transition-colors">Platform Features</a></li>
                        <li><a href="#code-playground" class="hover:text-slate-900 transition-colors">Interactive API Playground</a></li>
                        <li><a href="#comparison" class="hover:text-slate-900 transition-colors">Cost & Architecture Comparison</a></li>
                        <li><a href="#faq" class="hover:text-slate-900 transition-colors">Developer FAQ</a></li>
                    </ul>
                </div>

                <!-- Col 3: Resources & Compliance -->
                <div>
                    <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider mb-4 font-mono">Resources & Compliance</h4>
                    <ul class="space-y-2.5 text-xs text-slate-600">
                        <li><a href="<?= e(base_url('docs')) ?>" class="hover:text-slate-900 transition-colors">REST API Documentation</a></li>
                        <li><a href="<?= e(base_url('privacy')) ?>" class="hover:text-slate-900 transition-colors">Privacy Policy</a></li>
                        <li><a href="<?= e(base_url('terms')) ?>" class="hover:text-slate-900 transition-colors">Terms of Service</a></li>
                        <li><a href="#google-sync" class="hover:text-slate-900 transition-colors">Google Limited Use Disclosure</a></li>
                        <li><a href="mailto:info.assistgroupofficial@gmail.com" class="hover:text-slate-900 transition-colors">Support Contact</a></li>
                    </ul>
                </div>

                <!-- Col 4: Community & GitHub -->
                <div>
                    <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider mb-4 font-mono">Open Source</h4>
                    <p class="text-xs text-slate-600 mb-4 leading-relaxed">
                        Contribute or audit the source code on GitHub. Pull requests, bug reports, and stars welcome.
                    </p>
                    <a href="https://github.com/navneetyadav-code/whatsapp-api" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold text-xs border border-slate-200 transition-all">
                        <svg class="w-4 h-4 text-slate-800" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/></svg>
                        <span>GitHub Repository</span>
                    </a>
                </div>

            </div>

            <!-- Disclaimers & Copyright -->
            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px] text-slate-500">
                <p>&copy; <?= date('Y') ?> WhatsGateway &bull; Maintained by Navneet Kumar Yadav. Open-source under MIT License.</p>
                <p class="text-center sm:text-right">
                    Not affiliated with, sponsored by, or endorsed by Meta Platforms, Inc., WhatsApp Inc., or Google LLC.
                </p>
            </div>

        </div>
    </footer>

    <!-- Interactive Client Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            // 1. Mobile Hamburger Menu Toggle
            var menuBtn = document.getElementById('mobile-menu-btn');
            var mobileMenu = document.getElementById('mobile-menu');
            var hamburgerIcon = document.getElementById('hamburger-icon');
            var closeIcon = document.getElementById('close-icon');

            if (menuBtn && mobileMenu) {
                menuBtn.addEventListener('click', function () {
                    var isExpanded = menuBtn.getAttribute('aria-expanded') === 'true';
                    menuBtn.setAttribute('aria-expanded', !isExpanded);
                    mobileMenu.classList.toggle('hidden');
                    hamburgerIcon.classList.toggle('hidden');
                    closeIcon.classList.toggle('hidden');
                });

                var mobileLinks = document.querySelectorAll('.mobile-nav-link');
                mobileLinks.forEach(function (link) {
                    link.addEventListener('click', function () {
                        mobileMenu.classList.add('hidden');
                        menuBtn.setAttribute('aria-expanded', 'false');
                        hamburgerIcon.classList.remove('hidden');
                        closeIcon.classList.add('hidden');
                    });
                });
            }

            // 2. Interactive Dispatch Simulator in Split-Screen Hero
            var simBtn = document.getElementById('sim-send-btn');
            var simBtnText = document.getElementById('sim-btn-text');
            var simStatusBadge = document.getElementById('sim-status-badge');
            var simReplyBubble = document.getElementById('sim-reply-bubble');

            if (simBtn) {
                simBtn.addEventListener('click', function () {
                    simBtn.disabled = true;
                    simBtnText.textContent = 'Dispatching to Baileys Engine...';
                    simStatusBadge.textContent = 'Queuing...';
                    simStatusBadge.className = 'px-2 py-0.5 rounded text-[10px] font-mono bg-amber-500/15 text-amber-400 border border-amber-500/30';
                    
                    if (simReplyBubble) {
                        simReplyBubble.style.opacity = '0.3';
                        simReplyBubble.style.transform = 'scale(0.98)';
                    }

                    setTimeout(function () {
                        simBtnText.textContent = 'Connecting socket & typing...';
                        simStatusBadge.textContent = 'Composing...';
                        simStatusBadge.className = 'px-2 py-0.5 rounded text-[10px] font-mono bg-sky-500/15 text-sky-400 border border-sky-500/30';
                    }, 600);

                    setTimeout(function () {
                        simBtnText.textContent = 'Message Dispatched (200 OK)';
                        simStatusBadge.textContent = 'Read ✓✓';
                        simStatusBadge.className = 'px-2 py-0.5 rounded text-[10px] font-mono bg-emerald-500/15 text-emerald-400 border border-emerald-500/30';
                        if (simReplyBubble) {
                            simReplyBubble.style.opacity = '1';
                            simReplyBubble.style.transform = 'scale(1)';
                        }
                        setTimeout(function () {
                            simBtn.disabled = false;
                            simBtnText.textContent = 'Test Dispatch Simulation';
                        }, 2500);
                    }, 1400);
                });
            }

            // 3. Code Snippet Tab Switching
            var tabs = document.querySelectorAll('.code-tab');
            var panels = {
                curl: document.getElementById('snippet-curl'),
                node: document.getElementById('snippet-node'),
                python: document.getElementById('snippet-python'),
                php: document.getElementById('snippet-php'),
                meta: document.getElementById('snippet-meta')
            };

            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    var lang = tab.getAttribute('data-lang');
                    
                    tabs.forEach(function (t) {
                        t.classList.remove('bg-emerald-500/20', 'text-emerald-400', 'border', 'border-emerald-500/30');
                        t.classList.add('text-slate-400');
                    });

                    tab.classList.remove('text-slate-400');
                    tab.classList.add('bg-emerald-500/20', 'text-emerald-400', 'border', 'border-emerald-500/30');

                    Object.keys(panels).forEach(function (key) {
                        if (panels[key]) {
                            if (key === lang) {
                                panels[key].classList.remove('hidden');
                            } else {
                                panels[key].classList.add('hidden');
                            }
                        }
                    });
                });
            });

            // 4. 1-Click Copy Code to Clipboard
            var copyBtn = document.getElementById('copy-code-btn');
            var copyText = document.getElementById('copy-btn-text');

            if (copyBtn) {
                copyBtn.addEventListener('click', function () {
                    var activePanel = document.querySelector('.code-snippet-panel:not(.hidden) pre code');
                    if (!activePanel) return;

                    var textToCopy = (activePanel.innerText || activePanel.textContent || '').trim();
                    try {
                        if (navigator.clipboard && window.isSecureContext) {
                            navigator.clipboard.writeText(textToCopy).then(function () {
                                showCopiedState();
                            }).catch(function () {
                                fallbackCopy(textToCopy);
                            });
                        } else {
                            fallbackCopy(textToCopy);
                        }
                    } catch (e) {
                        fallbackCopy(textToCopy);
                    }
                });
            }

            function showCopiedState() {
                if (!copyText) return;
                var original = copyText.textContent;
                copyText.textContent = 'Copied!';
                copyBtn.classList.add('text-emerald-400', 'border-emerald-400/40');
                setTimeout(function () {
                    copyText.textContent = original;
                    copyBtn.classList.remove('text-emerald-400', 'border-emerald-400/40');
                }, 2000);
            }

            function fallbackCopy(text) {
                var textArea = document.createElement('textarea');
                textArea.value = text;
                textArea.style.position = 'fixed';
                textArea.style.left = '-9999px';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                try {
                    document.execCommand('copy');
                    showCopiedState();
                } catch (err) {
                    console.error('Fallback copy failed', err);
                }
                document.body.removeChild(textArea);
            }

        });
    </script>
</body>
</html>
<?php exit; ?>
