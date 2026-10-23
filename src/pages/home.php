<?php

declare(strict_types=1);

$user = current_user();
$seoTitle = 'WhatsApp API Hub — Open-Source Developer Messaging Gateway & Automation';
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
    <meta name="theme-color" content="#07090e">

    <!-- Open Graph & Twitter Cards -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= e(public_url('')) ?>">
    <meta property="og:title" content="<?= e($seoTitle) ?>">
    <meta property="og:description" content="<?= e($seoDesc) ?>">
    <meta property="og:image" content="<?= e(public_url('assets/favicon.png')) ?>">
    <meta property="og:site_name" content="WhatsApp API Hub">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($seoTitle) ?>">
    <meta name="twitter:description" content="<?= e($seoDesc) ?>">
    <meta name="twitter:image" content="<?= e(public_url('assets/favicon.png')) ?>">

    <!-- Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS Engine -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
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
                        },
                        dark: {
                            canvas: '#07090e',
                            surface: '#0d111a',
                            card: '#111726',
                            card_hover: '#161f33',
                            border: 'rgba(255, 255, 255, 0.08)',
                            border_light: 'rgba(255, 255, 255, 0.14)',
                            border_accent: 'rgba(37, 211, 102, 0.25)',
                        }
                    },
                    boxShadow: {
                        'glow-emerald': '0 0 50px -10px rgba(16, 185, 129, 0.25)',
                        'glow-emerald-sm': '0 0 20px -5px rgba(16, 185, 129, 0.3)',
                        'glow-indigo': '0 0 60px -15px rgba(99, 102, 241, 0.2)',
                        'inner-card': 'inset 0 1px 1px 0 rgba(255, 255, 255, 0.06)',
                    }
                }
            }
        };
    </script>
    <style>
        /* Custom Background Pattern & Smooth Scroll */
        .bg-grid-pattern {
            background-size: 32px 32px;
            background-image: 
                linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        }
        .bg-radial-gradient {
            background: radial-gradient(circle at 50% 0%, rgba(16, 185, 129, 0.14) 0%, rgba(99, 102, 241, 0.05) 45%, transparent 70%);
        }
        /* Custom scrollbar for code blocks */
        pre::-webkit-scrollbar {
            height: 6px;
        }
        pre::-webkit-scrollbar-track {
            background: #0d111a;
        }
        pre::-webkit-scrollbar-thumb {
            background: #242f45;
            border-radius: 4px;
        }
        pre::-webkit-scrollbar-thumb:hover {
            background: #374665;
        }
        details summary::-webkit-details-marker {
            display: none;
        }
    </style>
</head>
<body class="min-h-full flex flex-col font-sans bg-[#07090e] text-slate-200 antialiased selection:bg-brand-500/25 selection:text-brand-300">

    <!-- Top Sticky Navigation Bar -->
    <header class="sticky top-0 z-50 bg-[#07090e]/85 backdrop-blur-xl border-b border-white/[0.08]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between">
            
            <!-- Brand Logo & Status -->
            <a href="<?= e(base_url('')) ?>" class="flex items-center gap-3.5 group text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-400 rounded-xl p-1 transition-all">
                <div class="relative flex-shrink-0">
                    <img src="<?= e(base_url('assets/favicon.png')) ?>" alt="WhatsApp API Hub Logo" class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl object-cover shadow-lg border border-white/10 group-hover:scale-105 transition-transform duration-200">
                    <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-brand-500 border-2 border-[#07090e] rounded-full animate-pulse" title="System Operational"></span>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-white text-base sm:text-lg tracking-tight group-hover:text-brand-400 transition-colors">WhatsApp API Hub</span>
                        <span class="hidden sm:inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-brand-500/10 text-brand-400 border border-brand-500/20 font-mono">v1.0</span>
                    </div>
                    <span class="text-[11px] text-slate-400 font-medium hidden sm:block">Open-Source Developer Gateway</span>
                </div>
            </a>

            <!-- Desktop Navigation Links -->
            <nav class="hidden lg:flex items-center gap-7 text-sm font-medium text-slate-300">
                <a href="#architecture" class="hover:text-white transition-colors">Architecture</a>
                <a href="#code-playground" class="hover:text-white transition-colors">API Playground</a>
                <a href="#features" class="hover:text-white transition-colors">Features</a>
                <a href="#google-sync" class="hover:text-white transition-colors">Google Sync</a>
                <a href="#comparison" class="hover:text-white transition-colors">Comparison</a>
                <a href="#faq" class="hover:text-white transition-colors">FAQ</a>
                <a href="<?= e(base_url('docs')) ?>" class="text-brand-400 hover:text-brand-300 font-semibold transition-colors flex items-center gap-1.5">
                    <span>API Docs</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                </a>
            </nav>

            <!-- Actions & Auth Buttons -->
            <div class="flex items-center gap-3">
                <a href="https://github.com/navneetyadav-code/Free_WhatsApp_Business_API" target="_blank" rel="noopener noreferrer" class="hidden sm:inline-flex items-center gap-2 text-xs font-semibold text-slate-300 hover:text-white bg-white/5 hover:bg-white/10 px-3.5 py-2 rounded-xl border border-white/10 transition-all shadow-sm">
                    <svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/></svg>
                    <span>Star</span>
                </a>

                <?php if ($user): ?>
                    <a href="<?= e(base_url('dashboard')) ?>" class="hidden sm:inline-flex items-center gap-2 text-xs sm:text-sm font-semibold text-white bg-gradient-to-r from-brand-600 to-brand-500 hover:from-brand-500 hover:to-brand-400 px-4 py-2 sm:py-2.5 rounded-xl transition-all shadow-glow-emerald-sm">
                        <span>Dashboard</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                <?php else: ?>
                    <a href="<?= e(base_url('login')) ?>" class="hidden sm:inline-flex text-xs sm:text-sm font-semibold text-slate-300 hover:text-white px-3 py-2 transition-colors">
                        Sign In
                    </a>
                    <a href="<?= e(base_url('register')) ?>" class="hidden sm:inline-flex items-center gap-2 text-xs sm:text-sm font-semibold text-slate-900 bg-brand-400 hover:bg-brand-300 px-4 py-2 sm:py-2.5 rounded-xl transition-all shadow-glow-emerald-sm">
                        <span>Get Started Free</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                <?php endif; ?>

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" type="button" class="lg:hidden p-2 rounded-xl text-slate-400 hover:text-white hover:bg-white/5 focus:outline-none focus:ring-2 focus:ring-brand-400" aria-label="Toggle Navigation Menu" aria-expanded="false">
                    <svg id="hamburger-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    <svg id="close-icon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>

        <!-- Mobile Navigation Drawer / Dropdown -->
        <div id="mobile-menu" class="hidden lg:hidden border-t border-white/10 bg-[#07090e]/95 backdrop-blur-2xl px-4 pt-3 pb-6 space-y-3">
            <div class="grid grid-cols-2 gap-2 text-sm font-medium">
                <a href="#architecture" class="mobile-nav-link px-3 py-2.5 rounded-lg text-slate-300 hover:text-white hover:bg-white/5">Architecture</a>
                <a href="#code-playground" class="mobile-nav-link px-3 py-2.5 rounded-lg text-slate-300 hover:text-white hover:bg-white/5">API Playground</a>
                <a href="#features" class="mobile-nav-link px-3 py-2.5 rounded-lg text-slate-300 hover:text-white hover:bg-white/5">Features</a>
                <a href="#google-sync" class="mobile-nav-link px-3 py-2.5 rounded-lg text-slate-300 hover:text-white hover:bg-white/5">Google Sync</a>
                <a href="#comparison" class="mobile-nav-link px-3 py-2.5 rounded-lg text-slate-300 hover:text-white hover:bg-white/5">Comparison</a>
                <a href="#faq" class="mobile-nav-link px-3 py-2.5 rounded-lg text-slate-300 hover:text-white hover:bg-white/5">FAQ</a>
            </div>
            <div class="pt-3 border-t border-white/10 flex flex-col gap-2">
                <a href="<?= e(base_url('docs')) ?>" class="flex items-center justify-between px-3 py-2.5 rounded-lg bg-brand-500/10 text-brand-400 font-semibold text-sm">
                    <span>Developer Documentation</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
                <div class="flex items-center gap-2 pt-1">
                    <?php if (!$user): ?>
                        <a href="<?= e(base_url('login')) ?>" class="flex-1 text-center py-2.5 rounded-xl text-sm font-semibold text-white bg-white/5 border border-white/10 hover:bg-white/10">Sign In</a>
                        <a href="<?= e(base_url('register')) ?>" class="flex-1 text-center py-2.5 rounded-xl text-sm font-semibold text-slate-900 bg-brand-400 hover:bg-brand-300">Get Started</a>
                    <?php else: ?>
                        <a href="<?= e(base_url('dashboard')) ?>" class="w-full text-center py-2.5 rounded-xl text-sm font-semibold text-slate-900 bg-brand-400 hover:bg-brand-300">Open Dashboard</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1">

        <!-- Hero Section -->
        <section class="relative pt-12 pb-20 sm:pt-20 sm:pb-28 overflow-hidden bg-grid-pattern bg-radial-gradient">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
                
                <!-- Live Operational Badge -->
                <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full text-xs font-semibold bg-brand-500/10 text-brand-300 border border-brand-500/25 mb-8 shadow-glow-emerald-sm">
                    <span class="w-2 h-2 rounded-full bg-brand-400 animate-ping"></span>
                    <span>Self-Hosted &bull; Baileys Multi-Device &bull; PHP 8.2 &bull; Google Sync</span>
                </div>

                <!-- Main Display Heading -->
                <h1 class="text-4xl sm:text-6xl lg:text-7xl font-extrabold text-white tracking-tight max-w-5xl mx-auto leading-[1.12]">
                    The Production WhatsApp API Gateway <br class="hidden sm:inline">
                    <span class="bg-gradient-to-r from-brand-300 via-brand-400 to-emerald-500 bg-clip-text text-transparent">Built for Developers</span>
                </h1>
                
                <!-- Truthful Narrative Subheading -->
                <p class="mt-6 text-base sm:text-xl text-slate-300 max-w-3xl mx-auto leading-relaxed font-normal">
                    Connect your WhatsApp number via QR or pairing code, dispatch high-throughput transactional messages with our unified REST API or drop-in Meta Cloud schema, automate replies with custom chatbot rules, and receive HMAC-signed webhooks.
                </p>

                <!-- Hero Action Buttons -->
                <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                    <a href="<?= e(base_url('register')) ?>" class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-7 py-4 rounded-xl font-bold text-slate-950 bg-brand-400 hover:bg-brand-300 shadow-glow-emerald transition-all duration-150 text-base">
                        <span>Deploy / Start Free</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                    
                    <a href="<?= e(base_url('docs')) ?>" class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-6 py-4 rounded-xl font-semibold text-white bg-white/5 hover:bg-white/10 border border-white/15 transition-all text-base shadow-inner-card">
                        <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span>Interactive Docs</span>
                    </a>

                    <a href="https://github.com/navneetyadav-code/Free_WhatsApp_Business_API" target="_blank" rel="noopener noreferrer" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-4 rounded-xl font-semibold text-slate-300 hover:text-white bg-white/5 hover:bg-white/10 border border-white/10 transition-all text-base">
                        <svg class="w-5 h-5 text-slate-400" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/></svg>
                        <span>GitHub Repo</span>
                    </a>
                </div>

                <!-- Truthful Architectural Pillars -->
                <div class="mt-16 pt-10 border-t border-white/10 grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 text-center max-w-5xl mx-auto">
                    <div class="p-4 rounded-2xl bg-white/[0.02] border border-white/[0.06]">
                        <p class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight font-mono">$0.00</p>
                        <p class="text-xs sm:text-sm text-slate-400 font-medium mt-1">Per-Message Gateway Markup</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-white/[0.02] border border-white/[0.06]">
                        <p class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight font-mono">100%</p>
                        <p class="text-xs sm:text-sm text-slate-400 font-medium mt-1">Self-Hosted Data Ownership</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-white/[0.02] border border-white/[0.06]">
                        <p class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight font-mono">Dual API</p>
                        <p class="text-xs sm:text-sm text-slate-400 font-medium mt-1">Flat REST + Meta Cloud Format</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-white/[0.02] border border-white/[0.06]">
                        <p class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight font-mono">HMAC-SHA256</p>
                        <p class="text-xs sm:text-sm text-slate-400 font-medium mt-1">Signed Real-Time Webhooks</p>
                    </div>
                </div>

            </div>
        </section>

        <!-- Architecture Deep Dive Section -->
        <section id="architecture" class="py-20 bg-dark-surface border-y border-white/[0.08]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <span class="text-xs font-mono font-bold text-brand-400 uppercase tracking-widest bg-brand-500/10 px-3 py-1 rounded-full border border-brand-500/20">How It Actually Works</span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-white mt-4 tracking-tight">Decoupled Asynchronous Architecture</h2>
                    <p class="text-slate-400 mt-3 text-base sm:text-lg">
                        Designed for stability. Web requests never block on network sockets or WhatsApp handshakes.
                    </p>
                </div>

                <!-- Visual Architecture Steps Flow -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative">
                    
                    <!-- Step 1 -->
                    <div class="p-6 rounded-2xl bg-dark-card border border-dark-border hover:border-dark-border_light transition-all flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="w-8 h-8 rounded-lg bg-emerald-500/10 text-brand-400 flex items-center justify-center font-mono font-bold text-sm border border-brand-500/20">01</span>
                                <span class="text-[11px] font-mono text-slate-500">HTTP REST</span>
                            </div>
                            <h3 class="text-lg font-bold text-white mb-2">Ingestion & Auth Gateway</h3>
                            <p class="text-sm text-slate-400 leading-relaxed">
                                Your application dispatches a <code class="text-brand-300 font-mono text-xs">POST /api/send</code> request. PHP 8.2 validates the API Key, verifies rate limits, normalizes the recipient phone to international format, and validates media mime-types.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-white/[0.06] font-mono text-[11px] text-slate-400">
                            <span class="text-emerald-400">&check;</span> Returns instant <code class="text-white">200 Queued</code> in &lt;15ms
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="p-6 rounded-2xl bg-dark-card border border-dark-border hover:border-dark-border_light transition-all flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center font-mono font-bold text-sm border border-indigo-500/20">02</span>
                                <span class="text-[11px] font-mono text-slate-500">MySQL / TiDB</span>
                            </div>
                            <h3 class="text-lg font-bold text-white mb-2">Persistent Queue Buffer</h3>
                            <p class="text-sm text-slate-400 leading-relaxed">
                                Messages enter the <code class="text-indigo-300 font-mono text-xs">message_queue</code> table with status <code class="text-amber-400">pending</code>. Database persistence shields your pipeline from sudden worker crashes or temporary internet outages.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-white/[0.06] font-mono text-[11px] text-slate-400">
                            <span class="text-indigo-400">&check;</span> Auto-retry with exponential backoff
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="p-6 rounded-2xl bg-dark-card border border-dark-border hover:border-dark-border_light transition-all flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="w-8 h-8 rounded-lg bg-sky-500/10 text-sky-400 flex items-center justify-center font-mono font-bold text-sm border border-sky-500/20">03</span>
                                <span class="text-[11px] font-mono text-slate-500">Node Baileys</span>
                            </div>
                            <h3 class="text-lg font-bold text-white mb-2">Baileys Dispatch Engine</h3>
                            <p class="text-sm text-slate-400 leading-relaxed">
                                The Node.js Baileys worker maintains a persistent multi-device WebSocket connection to WhatsApp. It simulates human typing presence (<code class="text-sky-300 font-mono text-xs">composing</code>), applies anti-ban delays, and sends the payload.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-white/[0.06] font-mono text-[11px] text-slate-400">
                            <span class="text-sky-400">&check;</span> Multi-File Auth Session Persistence
                        </div>
                    </div>

                </div>

                <!-- Secondary Row: Inbound Lifecycle -->
                <div class="mt-6 p-6 rounded-2xl bg-dark-card border border-dark-border flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center font-bold text-lg border border-purple-500/20 flex-shrink-0">
                            &harr;
                        </div>
                        <div>
                            <h4 class="text-base font-bold text-white">Full Inbound Callback & Automated Chatbot Loop</h4>
                            <p class="text-xs sm:text-sm text-slate-400 mt-0.5">
                                When a customer replies or a message is read, Baileys immediately fires your configured keyword chatbot rules and forwards an HMAC-SHA256 signed event payload to your webhook endpoint.
                            </p>
                        </div>
                    </div>
                    <a href="#code-playground" class="flex-shrink-0 text-xs font-semibold px-4 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 text-brand-300 border border-white/10 transition-colors">
                        Inspect Payload Format &rarr;
                    </a>
                </div>

            </div>
        </section>

        <!-- Live Code Playground & Request/Response Explorer -->
        <section id="code-playground" class="py-20 bg-[#07090e]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-3xl mx-auto mb-12">
                    <span class="text-xs font-mono font-bold text-brand-400 uppercase tracking-widest bg-brand-500/10 px-3 py-1 rounded-full border border-brand-500/20">Developer Integration</span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-white mt-4 tracking-tight">Code Once, Dispatch Anywhere</h2>
                    <p class="text-slate-400 mt-3 text-base">
                        Test real cURL, Node.js, Python, PHP, or drop-in Meta Cloud payloads. Authenticate with a single API token.
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
                    
                    <!-- Left: Code Snippets & Language Switcher (7 cols) -->
                    <div class="lg:col-span-7 flex flex-col rounded-2xl bg-dark-surface border border-dark-border overflow-hidden shadow-2xl">
                        
                        <!-- Tabs Bar -->
                        <div class="bg-dark-card px-4 py-3 border-b border-dark-border flex flex-wrap items-center justify-between gap-2">
                            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0" role="tablist">
                                <button type="button" class="code-tab active px-3 py-1.5 rounded-lg text-xs font-mono font-medium transition-all bg-brand-500/15 text-brand-300 border border-brand-500/30" data-lang="curl">cURL</button>
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

                        <!-- Snippet Code Displays -->
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
<pre><code><span class="text-slate-500"># Direct Meta Cloud API Schema Drop-In (Zero changes to your existing code)</span>
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
                        <div class="px-5 py-2.5 bg-dark-card border-t border-dark-border flex items-center justify-between text-[11px] text-slate-400">
                            <span>Target: <code class="text-slate-200 font-mono">POST /api/send.php</code></span>
                            <span class="text-emerald-400 font-medium">&bull; Single Key / Bearer Token</span>
                        </div>
                    </div>

                    <!-- Right: Verified Live JSON Response (5 cols) -->
                    <div class="lg:col-span-5 flex flex-col rounded-2xl bg-dark-surface border border-dark-border overflow-hidden shadow-2xl">
                        
                        <div class="bg-dark-card px-4 py-3 border-b border-dark-border flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                <span class="text-xs font-mono font-bold text-white">HTTP 200 OK Response</span>
                            </div>
                            <span class="text-[11px] font-mono text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/20">application/json</span>
                        </div>

                        <div class="p-5 font-mono text-xs sm:text-[12px] leading-relaxed flex-1 overflow-x-auto text-slate-200 bg-[#07090e]/70">
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

                        <div class="px-5 py-2.5 bg-dark-card border-t border-dark-border text-[11px] text-slate-400 flex items-center justify-between">
                            <span>Dual Schema Returned</span>
                            <span class="text-brand-400 font-mono">0ms Worker Lock</span>
                        </div>
                    </div>

                </div>

            </div>
        </section>

        <!-- Deep Feature Breakdown Section -->
        <section id="features" class="py-20 bg-dark-surface border-y border-white/[0.08]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <span class="text-xs font-mono font-bold text-brand-400 uppercase tracking-widest bg-brand-500/10 px-3 py-1 rounded-full border border-brand-500/20">Production Features</span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-white mt-4 tracking-tight">Everything in a Complete Messaging Platform</h2>
                    <p class="text-slate-400 mt-3 text-base sm:text-lg">
                        Zero shortcuts. Built with persistent storage, session recovery, and battle-tested queuing.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    
                    <!-- Feature 1 -->
                    <div class="p-7 rounded-2xl bg-dark-card border border-dark-border hover:border-brand-500/30 transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-brand-500/10 text-brand-400 flex items-center justify-center mb-5 border border-brand-500/20 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">QR Code & 8-Digit Pairing</h3>
                        <p class="text-sm text-slate-400 leading-relaxed">
                            Link your existing WhatsApp personal or business number in seconds. Scan the in-dashboard QR code or use the 8-digit phone pairing code without pointing a camera.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="p-7 rounded-2xl bg-dark-card border border-dark-border hover:border-brand-500/30 transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-5 border border-emerald-500/20 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">Asynchronous Queuing & Throttle</h3>
                        <p class="text-sm text-slate-400 leading-relaxed">
                            Queue thousands of alerts without risking timeouts. The worker polls MySQL with configurable pacing (<code class="text-brand-300 font-mono text-xs">SEND_DELAY_MS</code>) and human typing presence emulation.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="p-7 rounded-2xl bg-dark-card border border-dark-border hover:border-brand-500/30 transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-sky-500/10 text-sky-400 flex items-center justify-center mb-5 border border-sky-500/20 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">Automated Keyword Chatbot</h3>
                        <p class="text-sm text-slate-400 leading-relaxed">
                            Create rules for auto-replying to incoming messages. Configure exact match, contains substring, or welcome messages to guide users 24/7 without custom bot servers.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="p-7 rounded-2xl bg-dark-card border border-dark-border hover:border-brand-500/30 transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center mb-5 border border-purple-500/20 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">HMAC-SHA256 Signed Webhooks</h3>
                        <p class="text-sm text-slate-400 leading-relaxed">
                            Receive notifications on your own backend whenever a message is delivered, read, or replied to. Every payload is cryptographically signed with your shared webhook secret.
                        </p>
                    </div>

                    <!-- Feature 5 -->
                    <div class="p-7 rounded-2xl bg-dark-card border border-dark-border hover:border-brand-500/30 transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-rose-500/10 text-rose-400 flex items-center justify-center mb-5 border border-rose-500/20 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">Rich Media, PDF & Documents</h3>
                        <p class="text-sm text-slate-400 leading-relaxed">
                            Attach images, invoices, catalogs, documents, audio clips, and voice notes. Supports remote media URLs, Base64 strings, or standard multipart form uploads.
                        </p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="p-7 rounded-2xl bg-dark-card border border-dark-border hover:border-brand-500/30 transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center mb-5 border border-amber-500/20 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">CSV Broadcasts & Contact Groups</h3>
                        <p class="text-sm text-slate-400 leading-relaxed">
                            Manage address books, import contacts from CSV or Google Contacts, and schedule broadcast campaigns with personalized tags (<code class="text-amber-300 font-mono text-xs">{name}</code>).
                        </p>
                    </div>

                </div>

            </div>
        </section>

        <!-- Google OAuth & Contacts Compliance Section -->
        <section id="google-sync" class="py-20 bg-[#07090e]">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="rounded-3xl bg-gradient-to-b from-dark-card to-dark-surface border border-white/10 p-8 sm:p-12 relative overflow-hidden shadow-2xl">
                    
                    <div class="relative z-10 max-w-3xl">
                        
                        <!-- Badge -->
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-white/5 text-slate-200 border border-white/10 mb-6">
                            <svg class="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                            <span>Google API Services User Data Policy Compliance</span>
                        </div>

                        <h2 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">
                            Secure Google Contacts Integration
                        </h2>
                        
                        <p class="mt-4 text-slate-300 text-sm sm:text-base leading-relaxed">
                            Sync your existing customer contacts directly from your authorized Google Account. We use standard OAuth 2.0 PKCE to request strictly read-only access (<code class="text-brand-300 font-mono text-xs">contacts.readonly</code>) solely for populating your address book.
                        </p>

                        <!-- Strict Compliance Guarantees -->
                        <div class="mt-8 space-y-3.5 text-xs sm:text-sm text-slate-300">
                            
                            <div class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-emerald-500/20 text-brand-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span>
                                    <strong class="text-white">Strict Limited Use Compliance:</strong> WhatsApp API Hub’s use and transfer to any other app of information received from Google APIs will adhere to the <a href="https://developers.google.com/terms/api-services-user-data-policy" target="_blank" rel="noopener noreferrer" class="text-brand-400 underline hover:text-brand-300">Google API Services User Data Policy</a>, including the Limited Use requirements.
                                </span>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-emerald-500/20 text-brand-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span>
                                    <strong class="text-white">Zero Resale & No Ads:</strong> Synced Google contact details are never shared with third parties, never sold to data brokers, and never used for behavioral advertising or model training.
                                </span>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-emerald-500/20 text-brand-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span>
                                    <strong class="text-white">Full User Control:</strong> Disconnect your Google Account and wipe all imported records with a single click in your dashboard at any time, or revoke access directly from your Google Security Settings.
                                </span>
                            </div>

                        </div>

                        <!-- Policy Buttons -->
                        <div class="mt-8 flex flex-wrap gap-4">
                            <a href="<?= e(base_url('privacy')) ?>" class="text-xs font-semibold px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 text-white transition-colors border border-white/15">
                                Read Privacy Policy &rarr;
                            </a>
                            <a href="<?= e(base_url('terms')) ?>" class="text-xs font-semibold px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 text-white transition-colors border border-white/15">
                                Read Terms of Service &rarr;
                            </a>
                        </div>

                    </div>

                </div>

            </div>
        </section>

        <!-- Architectural & Cost Comparison Table -->
        <section id="comparison" class="py-20 bg-dark-surface border-y border-white/[0.08]">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <span class="text-xs font-mono font-bold text-brand-400 uppercase tracking-widest bg-brand-500/10 px-3 py-1 rounded-full border border-brand-500/20">Transparent Breakdown</span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-white mt-4 tracking-tight">How It Compares</h2>
                    <p class="text-slate-400 mt-3 text-base">
                        Compare self-hosting our open-source gateway against official Meta Cloud and commercial SaaS brokers.
                    </p>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-dark-border bg-dark-card shadow-2xl">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-white/[0.04] border-b border-white/[0.08] text-slate-300 font-mono">
                            <tr>
                                <th class="p-4 sm:p-5 font-semibold">Capability / Metric</th>
                                <th class="p-4 sm:p-5 font-bold text-brand-300 bg-brand-500/10 border-x border-brand-500/20">WhatsApp API Hub (Self-Hosted)</th>
                                <th class="p-4 sm:p-5 font-semibold text-slate-400">Meta WhatsApp Cloud API</th>
                                <th class="p-4 sm:p-5 font-semibold text-slate-400">Commercial SaaS Brokers</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[0.06] text-slate-300">
                            
                            <tr>
                                <td class="p-4 sm:p-5 font-medium text-white">Gateway Per-Message Markup</td>
                                <td class="p-4 sm:p-5 font-bold text-emerald-400 bg-brand-500/5 border-x border-brand-500/20 font-mono">$0.00 (Zero)</td>
                                <td class="p-4 sm:p-5 text-slate-400">Per-conversation Meta fees</td>
                                <td class="p-4 sm:p-5 text-slate-400">$0.005 &ndash; $0.05 extra per msg</td>
                            </tr>

                            <tr>
                                <td class="p-4 sm:p-5 font-medium text-white">Setup & Phone Linking</td>
                                <td class="p-4 sm:p-5 font-bold text-emerald-400 bg-brand-500/5 border-x border-brand-500/20">Instant QR / 8-digit Pairing Code</td>
                                <td class="p-4 sm:p-5 text-slate-400">Requires Meta Business Verification</td>
                                <td class="p-4 sm:p-5 text-slate-400">Complex onboarding & approval</td>
                            </tr>

                            <tr>
                                <td class="p-4 sm:p-5 font-medium text-white">Database & Session Control</td>
                                <td class="p-4 sm:p-5 font-bold text-emerald-400 bg-brand-500/5 border-x border-brand-500/20">100% on your own server/DB</td>
                                <td class="p-4 sm:p-5 text-slate-400">Hosted in Meta cloud</td>
                                <td class="p-4 sm:p-5 text-slate-400">Vendor cloud lock-in</td>
                            </tr>

                            <tr>
                                <td class="p-4 sm:p-5 font-medium text-white">API Payload Compatibility</td>
                                <td class="p-4 sm:p-5 font-bold text-emerald-400 bg-brand-500/5 border-x border-brand-500/20">Clean REST + Drop-in Meta Cloud schema</td>
                                <td class="p-4 sm:p-5 text-slate-400">Meta Graph schema only</td>
                                <td class="p-4 sm:p-5 text-slate-400">Proprietary vendor SDKs</td>
                            </tr>

                            <tr>
                                <td class="p-4 sm:p-5 font-medium text-white">Inbound Webhooks</td>
                                <td class="p-4 sm:p-5 font-bold text-emerald-400 bg-brand-500/5 border-x border-brand-500/20">HMAC-SHA256 Signed POST Callbacks</td>
                                <td class="p-4 sm:p-5 text-slate-400">Hub signature validation</td>
                                <td class="p-4 sm:p-5 text-slate-400">Varies / Rate-limited</td>
                            </tr>

                            <tr>
                                <td class="p-4 sm:p-5 font-medium text-white">Built-in Keyword Chatbots</td>
                                <td class="p-4 sm:p-5 font-bold text-emerald-400 bg-brand-500/5 border-x border-brand-500/20">Included &bull; Instant auto-reply rules</td>
                                <td class="p-4 sm:p-5 text-slate-400">Requires separate bot server</td>
                                <td class="p-4 sm:p-5 text-slate-400">Expensive add-on tier</td>
                            </tr>

                            <tr>
                                <td class="p-4 sm:p-5 font-medium text-white">Google Contacts Sync</td>
                                <td class="p-4 sm:p-5 font-bold text-emerald-400 bg-brand-500/5 border-x border-brand-500/20">Built-in Google OAuth2 PKCE</td>
                                <td class="p-4 sm:p-5 text-slate-400">None</td>
                                <td class="p-4 sm:p-5 text-slate-400">Requires third-party Zapier sync</td>
                            </tr>

                        </tbody>
                    </table>
                </div>

            </div>
        </section>

        <!-- Self-Hosting & Deployment Paths -->
        <section id="self-hosting" class="py-20 bg-[#07090e]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <span class="text-xs font-mono font-bold text-brand-400 uppercase tracking-widest bg-brand-500/10 px-3 py-1 rounded-full border border-brand-500/20">Deployment</span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-white mt-4 tracking-tight">Deploy in Your Preferred Environment</h2>
                    <p class="text-slate-400 mt-3 text-base">
                        Run locally, on a VPS with Supervisord, or orchestrated in Docker.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    <!-- Option 1: Docker -->
                    <div class="p-6 rounded-2xl bg-dark-card border border-dark-border flex flex-col justify-between">
                        <div>
                            <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center font-bold mb-4 border border-blue-500/20">
                                🐳
                            </div>
                            <h3 class="text-lg font-bold text-white mb-2">Docker Compose</h3>
                            <p class="text-xs sm:text-sm text-slate-400 leading-relaxed mb-4">
                                Containerized PHP 8.2 runtime, Baileys Node.js worker, and MariaDB database managed via a single config file.
                            </p>
                        </div>
                        <div class="bg-[#07090e] p-3 rounded-xl font-mono text-[11px] text-slate-300 border border-white/5">
                            <code>docker compose up -d</code>
                        </div>
                    </div>

                    <!-- Option 2: Linux VPS / Bare Metal -->
                    <div class="p-6 rounded-2xl bg-dark-card border border-dark-border flex flex-col justify-between">
                        <div>
                            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-bold mb-4 border border-emerald-500/20">
                                🐧
                            </div>
                            <h3 class="text-lg font-bold text-white mb-2">Ubuntu / Debian VPS</h3>
                            <p class="text-xs sm:text-sm text-slate-400 leading-relaxed mb-4">
                                Native Nginx or Apache with PHP 8.2-fpm and Node.js 18+. Baileys worker runs under <code class="text-emerald-300">supervisord</code> for 24/7 uptime.
                            </p>
                        </div>
                        <div class="bg-[#07090e] p-3 rounded-xl font-mono text-[11px] text-slate-300 border border-white/5">
                            <code>supervisorctl status</code>
                        </div>
                    </div>

                    <!-- Option 3: Local Dev / XAMPP -->
                    <div class="p-6 rounded-2xl bg-dark-card border border-dark-border flex flex-col justify-between">
                        <div>
                            <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold mb-4 border border-amber-500/20">
                                💻
                            </div>
                            <h3 class="text-lg font-bold text-white mb-2">Windows / XAMPP Dev</h3>
                            <p class="text-xs sm:text-sm text-slate-400 leading-relaxed mb-4">
                                Run Apache and MySQL in XAMPP while executing the Node worker locally via <code class="text-amber-300">start-worker.bat</code> for instant rapid prototyping.
                            </p>
                        </div>
                        <div class="bg-[#07090e] p-3 rounded-xl font-mono text-[11px] text-slate-300 border border-white/5">
                            <code>npm run worker</code>
                        </div>
                    </div>

                </div>

            </div>
        </section>

        <!-- Technical Developer FAQ -->
        <section id="faq" class="py-20 bg-dark-surface border-t border-white/[0.08]">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-2xl mx-auto mb-14">
                    <span class="text-xs font-mono font-bold text-brand-400 uppercase tracking-widest bg-brand-500/10 px-3 py-1 rounded-full border border-brand-500/20">Frequently Asked Questions</span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-white mt-4 tracking-tight">Direct Answers for Engineers</h2>
                    <p class="text-slate-400 mt-3 text-base">
                        Clear technical explanations with no marketing fluff.
                    </p>
                </div>

                <div class="space-y-4">
                    
                    <details class="group p-5 rounded-2xl bg-dark-card border border-dark-border transition-all [&_summary::-webkit-details-marker]:none">
                        <summary class="flex items-center justify-between cursor-pointer list-none text-white font-semibold text-base sm:text-lg">
                            <span>Do I need a verified Meta Business Manager account to use this?</span>
                            <span class="w-7 h-7 rounded-lg bg-white/5 flex items-center justify-center text-slate-400 group-open:rotate-180 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </span>
                        </summary>
                        <div class="mt-4 text-sm text-slate-300 leading-relaxed border-t border-white/[0.06] pt-4">
                            No. Unlike the official Meta Cloud API which mandates submitting business registration papers and credit checks, this gateway uses the Baileys multi-device socket protocol. You can link any standard or business WhatsApp number simply by scanning the QR code or submitting an 8-digit phone pairing code.
                        </div>
                    </details>

                    <details class="group p-5 rounded-2xl bg-dark-card border border-dark-border transition-all [&_summary::-webkit-details-marker]:none">
                        <summary class="flex items-center justify-between cursor-pointer list-none text-white font-semibold text-base sm:text-lg">
                            <span>How does this gateway protect against account bans?</span>
                            <span class="w-7 h-7 rounded-lg bg-white/5 flex items-center justify-center text-slate-400 group-open:rotate-180 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </span>
                        </summary>
                        <div class="mt-4 text-sm text-slate-300 leading-relaxed border-t border-white/[0.06] pt-4">
                            WhatsApp flags numbers when high volumes of unsolicited messages are blasted simultaneously. WhatsApp API Hub implements several anti-ban protections:
                            <ul class="list-disc pl-5 mt-2 space-y-1 text-slate-400">
                                <li>Configurable queue pacing delays (<code class="text-brand-300">SEND_DELAY_MS</code>, defaulting to 2500ms between messages).</li>
                                <li>Automatic human typing presence updates (<code class="text-brand-300">sendPresenceUpdate('composing')</code>).</li>
                                <li>We strongly advise sending transactional notifications and reminders only to users who have explicitly opted in.</li>
                            </ul>
                        </div>
                    </details>

                    <details class="group p-5 rounded-2xl bg-dark-card border border-dark-border transition-all [&_summary::-webkit-details-marker]:none">
                        <summary class="flex items-center justify-between cursor-pointer list-none text-white font-semibold text-base sm:text-lg">
                            <span>Where are WhatsApp session keys and messages stored?</span>
                            <span class="w-7 h-7 rounded-lg bg-white/5 flex items-center justify-center text-slate-400 group-open:rotate-180 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </span>
                        </summary>
                        <div class="mt-4 text-sm text-slate-300 leading-relaxed border-t border-white/[0.06] pt-4">
                            All session keys and credentials are saved locally in your server's <code class="text-brand-300">whatsapp-api-data/sessions/</code> directory using Baileys' multi-file auth state. Messages, delivery states, and contact records reside directly in your local MySQL / TiDB database. Nothing is transmitted to external servers or third-party brokers.
                        </div>
                    </details>

                    <details class="group p-5 rounded-2xl bg-dark-card border border-dark-border transition-all [&_summary::-webkit-details-marker]:none">
                        <summary class="flex items-center justify-between cursor-pointer list-none text-white font-semibold text-base sm:text-lg">
                            <span>How does the Meta Cloud API drop-in compatibility work?</span>
                            <span class="w-7 h-7 rounded-lg bg-white/5 flex items-center justify-center text-slate-400 group-open:rotate-180 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </span>
                        </summary>
                        <div class="mt-4 text-sm text-slate-300 leading-relaxed border-t border-white/[0.06] pt-4">
                            If you have existing software built for Meta's official Cloud API, you don't need to rewrite your payload generators. Simply point your HTTP base URL to this gateway and pass your API key as the <code class="text-brand-300">Authorization: Bearer</code> token. The endpoint parses Meta schemas (including text bodies, templates, documents, and contacts) and returns compliant JSON response bodies.
                        </div>
                    </details>

                    <details class="group p-5 rounded-2xl bg-dark-card border border-dark-border transition-all [&_summary::-webkit-details-marker]:none">
                        <summary class="flex items-center justify-between cursor-pointer list-none text-white font-semibold text-base sm:text-lg">
                            <span>What are the minimum hardware requirements?</span>
                            <span class="w-7 h-7 rounded-lg bg-white/5 flex items-center justify-center text-slate-400 group-open:rotate-180 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </span>
                        </summary>
                        <div class="mt-4 text-sm text-slate-300 leading-relaxed border-t border-white/[0.06] pt-4">
                            Because the architecture is lightweight (PHP 8.2 + Node.js Baileys), a standard 1 vCPU / 1GB RAM VPS (costing ~$4-$5/month on Hetzner, DigitalOcean, or Linode) is more than sufficient to comfortably handle queues and continuous worker socket loops.
                        </div>
                    </details>

                </div>

            </div>
        </section>

        <!-- Final Developer Conversion CTA -->
        <section class="py-20 bg-[#07090e] relative overflow-hidden">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
                <div class="p-8 sm:p-14 rounded-3xl bg-gradient-to-b from-brand-950/40 via-dark-card to-dark-surface border border-brand-500/30 shadow-glow-emerald">
                    
                    <h2 class="text-3xl sm:text-5xl font-extrabold text-white tracking-tight leading-tight">
                        Ready to Take Full Control of Your WhatsApp Messaging?
                    </h2>
                    
                    <p class="mt-5 text-base sm:text-lg text-slate-300 max-w-2xl mx-auto leading-relaxed">
                        Say goodbye to per-message markup, complex business approval gates, and vendor lock-in. Deploy your private instance in minutes.
                    </p>

                    <div class="mt-9 flex flex-wrap items-center justify-center gap-4">
                        <a href="<?= e(base_url('register')) ?>" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl font-bold text-slate-950 bg-brand-400 hover:bg-brand-300 shadow-glow-emerald transition-all text-base">
                            <span>Get Started Free</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                        <a href="<?= e(base_url('docs')) ?>" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-4 rounded-xl font-semibold text-white bg-white/5 hover:bg-white/10 border border-white/15 transition-all text-base">
                            <span>Read API Docs</span>
                        </a>
                    </div>

                </div>
            </div>
        </section>

    </main>

    <!-- Comprehensive Footer -->
    <footer class="bg-dark-surface border-t border-white/[0.08] py-14 text-slate-400 text-xs sm:text-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 pb-12 border-b border-white/[0.08]">
                
                <!-- Col 1: Brand Info -->
                <div class="md:col-span-1 space-y-4">
                    <div class="flex items-center gap-3">
                        <img src="<?= e(base_url('assets/favicon.png')) ?>" alt="WhatsApp API Hub Logo" class="w-8 h-8 rounded-lg object-cover shadow-sm border border-white/10">
                        <span class="font-bold text-white text-base">WhatsApp API Hub</span>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Open-source, self-hosted WhatsApp messaging gateway and automation engine for modern developers.
                    </p>
                    <div class="inline-flex items-center gap-2 text-[11px] text-slate-400 font-mono">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>MIT Licensed Project</span>
                    </div>
                </div>

                <!-- Col 2: Navigation Links -->
                <div>
                    <h4 class="font-bold text-white text-xs uppercase tracking-wider mb-4 font-mono">Platform</h4>
                    <ul class="space-y-2.5 text-xs">
                        <li><a href="#architecture" class="hover:text-white transition-colors">Architecture Flow</a></li>
                        <li><a href="#code-playground" class="hover:text-white transition-colors">Interactive API Playground</a></li>
                        <li><a href="#features" class="hover:text-white transition-colors">Platform Features</a></li>
                        <li><a href="#comparison" class="hover:text-white transition-colors">Architecture Comparison</a></li>
                        <li><a href="#self-hosting" class="hover:text-white transition-colors">Self-Hosting Guide</a></li>
                    </ul>
                </div>

                <!-- Col 3: Documentation & Legal -->
                <div>
                    <h4 class="font-bold text-white text-xs uppercase tracking-wider mb-4 font-mono">Resources & Compliance</h4>
                    <ul class="space-y-2.5 text-xs">
                        <li><a href="<?= e(base_url('docs')) ?>" class="hover:text-white transition-colors">REST API Documentation</a></li>
                        <li><a href="<?= e(base_url('privacy')) ?>" class="hover:text-white transition-colors">Privacy Policy</a></li>
                        <li><a href="<?= e(base_url('terms')) ?>" class="hover:text-white transition-colors">Terms of Service</a></li>
                        <li><a href="#google-sync" class="hover:text-white transition-colors">Google Limited Use Disclosure</a></li>
                        <li><a href="mailto:info.assistgroupofficial@gmail.com" class="hover:text-white transition-colors">Support Contact</a></li>
                    </ul>
                </div>

                <!-- Col 4: Community & GitHub -->
                <div>
                    <h4 class="font-bold text-white text-xs uppercase tracking-wider mb-4 font-mono">Open Source</h4>
                    <p class="text-xs text-slate-400 mb-4 leading-relaxed">
                        Contribute or audit the source code on GitHub. Pull requests, bug reports, and stars welcome.
                    </p>
                    <a href="https://github.com/navneetyadav-code/Free_WhatsApp_Business_API" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-white font-semibold text-xs border border-white/10 transition-all">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/></svg>
                        <span>GitHub Repository</span>
                    </a>
                </div>

            </div>

            <!-- Disclaimers & Copyright -->
            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px] text-slate-500">
                <p>&copy; <?= date('Y') ?> WhatsApp API Hub &bull; Maintained by Navneet Kumar Yadav. Open-source under MIT License.</p>
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

                // Close menu when clicking any mobile anchor
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

            // 2. Code Snippet Tab Switching
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
                    
                    // Reset all tabs
                    tabs.forEach(function (t) {
                        t.classList.remove('bg-brand-500/15', 'text-brand-300', 'border', 'border-brand-500/30');
                        t.classList.add('text-slate-400');
                    });

                    // Activate selected tab
                    tab.classList.remove('text-slate-400');
                    tab.classList.add('bg-brand-500/15', 'text-brand-300', 'border', 'border-brand-500/30');

                    // Show corresponding panel
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

            // 3. 1-Click Copy Code to Clipboard
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
                copyBtn.classList.add('text-brand-400', 'border-brand-400/40');
                setTimeout(function () {
                    copyText.textContent = original;
                    copyBtn.classList.remove('text-brand-400', 'border-brand-400/40');
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
