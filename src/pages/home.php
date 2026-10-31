<?php

declare(strict_types=1);

$user = current_user();
$seoTitle = 'WhatsGateway — Open-Source Developer WhatsApp API Gateway & Automation Engine';
$seoDesc = 'Production-grade, self-hosted WhatsApp API gateway powered by Baileys & PHP 8.2. Dual REST API format, drop-in Meta Cloud API compatibility, queue engine, chatbot rules, HMAC webhooks, and Google Contacts sync.';
?>
<!DOCTYPE html>
<html lang="en" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($seoTitle) ?></title>
    <meta name="description" content="<?= e($seoDesc) ?>">
    <link rel="canonical" href="<?= e(public_url('')) ?>">
    
    <!-- Favicon Suite -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?= e(base_url('assets/favicon-32x32.png')) ?>">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= e(base_url('assets/favicon.png')) ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= e(base_url('assets/apple-touch-icon.png')) ?>">
    <link rel="shortcut icon" href="<?= e(base_url('favicon.ico')) ?>">
    <meta name="theme-color" content="#008069">

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

    <!-- Typography (Plus Jakarta Sans & JetBrains Mono) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS Engine -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'Inter', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                        mono: ['JetBrains Mono', 'ui-monospace', 'SFMono-Regular', 'Menlo', 'monospace'],
                    },
                    colors: {
                        wa: {
                            green: '#25D366',
                            teal: '#008069',
                            darkTeal: '#075E54',
                            chatBg: '#EFEAE2',
                            sentBubble: '#D9FDD3',
                            blueTick: '#53BDEB',
                            lightBg: '#F0F2F5',
                            textDark: '#111B21',
                            textMuted: '#54656F',
                        },
                        brand: {
                            50: '#F0FDF4',
                            100: '#DCFCE7',
                            200: '#BBF7D0',
                            300: '#86EFAC',
                            400: '#4ADE80',
                            500: '#22C55E',
                            600: '#16A34A',
                            700: '#15803D',
                            800: '#166534',
                            900: '#14532D',
                        }
                    },
                    boxShadow: {
                        'soft-xs': '0 1px 2px 0 rgba(15, 23, 42, 0.05)',
                        'soft-sm': '0 1px 3px 0 rgba(15, 23, 42, 0.08), 0 1px 2px -1px rgba(15, 23, 42, 0.08)',
                        'soft-md': '0 4px 6px -1px rgba(15, 23, 42, 0.07), 0 2px 4px -2px rgba(15, 23, 42, 0.05)',
                        'soft-lg': '0 10px 15px -3px rgba(15, 23, 42, 0.08), 0 4px 6px -4px rgba(15, 23, 42, 0.04)',
                        'soft-xl': '0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.03)',
                        'wa-card': '0 4px 20px -2px rgba(0, 128, 105, 0.08), 0 2px 6px -1px rgba(0, 0, 0, 0.04)',
                        'wa-bubble': '0 1px 1px rgba(11, 20, 26, 0.12)',
                        'wa-button': '0 8px 24px -4px rgba(37, 211, 102, 0.35)',
                        'wa-glow': '0 0 30px -5px rgba(0, 128, 105, 0.18)',
                    },
                    animation: {
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'float': 'float 6s ease-in-out infinite',
                        'float-delayed': 'float 6s ease-in-out 3s infinite',
                        'shimmer': 'shimmer 4s ease infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-8px)' },
                        },
                        shimmer: {
                            '0%': { backgroundPosition: '0% 50%' },
                            '50%': { backgroundPosition: '100% 50%' },
                            '100%': { backgroundPosition: '0% 50%' },
                        }
                    }
                }
            }
        };
    </script>
    <style>
        /* WhatsApp Subtle Light Background Canvas */
        .bg-wa-canvas {
            background-color: #FFFFFF;
            background-image: 
                radial-gradient(circle at 50% -10%, rgba(37, 211, 102, 0.10) 0%, rgba(0, 128, 105, 0.05) 30%, #FFFFFF 75%),
                radial-gradient(circle at 90% 40%, rgba(0, 128, 105, 0.04) 0%, transparent 40%),
                radial-gradient(circle at 10% 70%, rgba(37, 211, 102, 0.05) 0%, transparent 45%);
        }
        .bg-wa-dots {
            background-image: radial-gradient(rgba(0, 128, 105, 0.07) 1.2px, transparent 1.2px);
            background-size: 24px 24px;
        }

        /* Authentic WhatsApp Chat Wallpaper */
        .wa-chat-canvas {
            background-color: #EFEAE2;
            background-image: 
                radial-gradient(rgba(0, 0, 0, 0.03) 1px, transparent 1px),
                radial-gradient(rgba(0, 0, 0, 0.02) 1px, transparent 1px);
            background-size: 22px 22px;
            background-position: 0 0, 11px 11px;
        }

        /* Shimmer Pill Gradient Border */
        .wa-shimmer-border {
            background: linear-gradient(90deg, #008069, #25D366, #128C7E, #25D366);
            background-size: 300% 300%;
            animation: waShimmer 5s ease infinite;
        }
        @keyframes waShimmer {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Interactive Card Hover */
        .wa-card-interactive {
            transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .wa-card-interactive:hover {
            transform: translateY(-3px);
            border-color: #25D366;
            box-shadow: 0 16px 32px -8px rgba(0, 128, 105, 0.12), 0 0 0 1px rgba(37, 211, 102, 0.3);
        }

        /* Clean Scrollbars for Code Blocks */
        pre::-webkit-scrollbar {
            height: 6px;
            width: 6px;
        }
        pre::-webkit-scrollbar-track {
            background: #0F172A;
        }
        pre::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 9999px;
        }
        pre::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }

        details summary::-webkit-details-marker {
            display: none;
        }

        /* Scroll Reveal Transitions (Hardware Accelerated) */
        .reveal {
            opacity: 0;
            transform: translateY(22px);
            transition: opacity 0.65s cubic-bezier(0.16, 1, 0.3, 1), transform 0.65s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: opacity, transform;
        }
        .reveal.revealed {
            opacity: 1;
            transform: translateY(0);
        }
        .reveal-delay-1 { transition-delay: 0.1s; }
        .reveal-delay-2 { transition-delay: 0.2s; }
        .reveal-delay-3 { transition-delay: 0.3s; }
        @media (prefers-reduced-motion: reduce) {
            .reveal {
                opacity: 1 !important;
                transform: none !important;
                transition: none !important;
            }
        }

        /* Animated Live Pulse Dot */
        @keyframes waPulseDot {
            0% { box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7); }
            70% { box-shadow: 0 0 0 7px rgba(37, 211, 102, 0); }
            100% { box-shadow: 0 0 0 0 rgba(37, 211, 102, 0); }
        }
        .wa-live-dot {
            animation: waPulseDot 2.2s infinite;
        }

        /* Event Stream Slide-In */
        @keyframes eventSlideIn {
            0% { opacity: 0; transform: translateY(-10px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .animate-event-in {
            animation: eventSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
</head>
<body class="min-h-full flex flex-col font-sans bg-white text-wa-textDark antialiased selection:bg-wa-green/20 selection:text-wa-teal">

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
                    <a href="<?= e(base_url('dashboard')) ?>" class="inline-flex shrink-0 items-center gap-1.5 text-[11px] sm:text-sm font-semibold text-white bg-slate-900 hover:bg-slate-800 px-2.5 sm:px-4 py-2 sm:py-2.5 rounded-xl transition-all shadow-soft-sm">
                        <span class="sm:hidden">Dashboard</span><span class="hidden sm:inline">Dashboard</span>
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                <?php else: ?>
                    <a href="<?= e(base_url('register')) ?>" class="inline-flex shrink-0 items-center gap-1.5 text-[11px] sm:text-sm font-semibold text-white bg-slate-900 hover:bg-slate-800 px-2.5 sm:px-4 py-2 sm:py-2.5 rounded-xl transition-all shadow-soft-sm hover:shadow-soft-md">
                        <span class="sm:hidden">Start Free</span><span class="hidden sm:inline">Get Started Free</span>
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

        <!-- HERO SECTION: WhatsApp Light Aesthetics with Live Interactive Phone Mockup -->
        <section class="relative pt-12 pb-20 sm:pt-18 sm:pb-28 bg-wa-canvas bg-wa-dots border-b border-emerald-100/90 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 xl:gap-12 items-center">
                    
                    <!-- LEFT COLUMN: Value Proposition & CTAs (7 cols) -->
                    <div class="lg:col-span-7 space-y-6 text-center lg:text-left reveal">
                        
                        <!-- Shimmering Pill Badge -->
                        <div class="inline-flex p-[1px] rounded-full wa-shimmer-border shadow-sm">
                            <div class="px-3.5 py-1.5 rounded-full bg-white flex items-center gap-2 text-xs font-semibold text-slate-700">
                                <span class="w-2.5 h-2.5 rounded-full bg-wa-green animate-pulse"></span>
                                <span class="text-wa-teal font-bold">WhatsGateway v3.0</span>
                                <span class="text-slate-300">&bull;</span>
                                <span class="text-slate-600">Autonomous AI Agent + Dual-Tier Memory</span>
                                <span class="text-slate-300">&bull;</span>
                                <span class="text-emerald-700 font-mono font-bold">$0 Fee</span>
                            </div>
                        </div>

                        <!-- Main Headline with WhatsApp Gradient -->
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-slate-900 tracking-tight leading-[1.12]">
                            Open-Source WhatsApp <br class="hidden sm:inline">
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-wa-teal via-emerald-600 to-wa-green">API & AI Agent Gateway</span>
                        </h1>

                        <!-- Subtitle -->
                        <p class="text-base sm:text-lg text-slate-600 max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
                            A production-ready WhatsApp gateway powered by Baileys, PHP 8.2, and multi-model AI (Gemini, Groq LPU, OpenRouter). Features a dual-tier cognitive memory engine, token-governed few-shot training, modular app store with autonomous function calling, and HMAC webhooks on your own server.
                        </p>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3.5 pt-2">
                            <a href="<?= e(base_url('register')) ?>" class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-6 py-3.5 rounded-xl font-bold text-white bg-gradient-to-r from-wa-teal to-emerald-600 hover:from-emerald-700 hover:to-wa-darkTeal shadow-wa-button hover:shadow-wa-glow transition-all text-sm sm:text-base group">
                                <span>Get Started</span>
                                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </a>

                            <a href="#code-playground" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3.5 rounded-xl font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 shadow-sm transition-all text-sm sm:text-base">
                                <svg class="w-4 h-4 text-wa-teal" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                                <span>API Playground</span>
                            </a>

                            <a href="<?= e(base_url('docs')) ?>" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-3.5 rounded-xl font-semibold text-slate-600 hover:text-wa-teal transition-colors text-sm sm:text-base">
                                <span>Documentation &rarr;</span>
                            </a>
                        </div>

                        <!-- 1-Click Quick Start CLI Banner -->
                        <div class="pt-2">
                            <div class="inline-flex items-center gap-3 px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 font-mono text-xs text-slate-700 max-w-full overflow-hidden shadow-sm">
                                <span class="text-wa-teal font-bold select-none">$</span>
                                <span class="truncate">git clone https://github.com/navneetyadav-code/whatsapp-api.git</span>
                                <button type="button" id="hero-copy-repo-btn" class="text-slate-400 hover:text-wa-teal p-1 transition-colors" title="Copy git clone command">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                </button>
                            </div>
                        </div>

                        <!-- 4 Verified Pillar Metrics -->
                        <div class="pt-6 border-t border-slate-200 grid grid-cols-2 sm:grid-cols-4 gap-3 text-left">
                            <div class="p-3.5 rounded-2xl bg-white border border-emerald-100 shadow-wa-card">
                                <p class="text-xl sm:text-2xl font-black text-wa-teal font-mono tracking-tight">$0.00</p>
                                <p class="text-[11px] text-slate-500 font-medium mt-0.5">Per-Msg Gateway Fee</p>
                            </div>
                            <div class="p-3.5 rounded-2xl bg-white border border-emerald-100 shadow-wa-card">
                                <p class="text-xl sm:text-2xl font-black text-slate-900 font-mono tracking-tight">&lt; 35ms</p>
                                <p class="text-[11px] text-slate-500 font-medium mt-0.5">Queue Ingestion Speed</p>
                            </div>
                            <div class="p-3.5 rounded-2xl bg-white border border-emerald-100 shadow-wa-card">
                                <p class="text-xl sm:text-2xl font-black text-indigo-700 font-mono tracking-tight">Smart AI</p>
                                <p class="text-[11px] text-slate-500 font-medium mt-0.5">Memory + Function Calling</p>
                            </div>
                            <div class="p-3.5 rounded-2xl bg-white border border-emerald-100 shadow-wa-card">
                                <p class="text-xl sm:text-2xl font-black text-emerald-600 font-mono tracking-tight">100%</p>
                                <p class="text-[11px] text-slate-500 font-medium mt-0.5">Self-Hosted Privacy</p>
                            </div>
                        </div>

                    </div>

                    <!-- RIGHT COLUMN: Authentic WhatsApp Phone Mockup & Live Simulator (5 cols) -->
                    <div class="lg:col-span-5 relative reveal reveal-delay-1 flex justify-center lg:justify-end">
                        
                        <div class="relative w-full max-w-[340px] sm:max-w-[360px]">
                            
                            <!-- Soft Ambient Glow behind Phone -->
                            <div class="absolute -inset-2 bg-gradient-to-r from-wa-green/20 via-wa-teal/15 to-emerald-200/30 rounded-[3rem] blur-2xl opacity-75 pointer-events-none"></div>

                            <!-- Phone Frame (Proportional Smartphone Chassis) -->
                            <div class="relative rounded-[2.6rem] bg-slate-900 p-2.5 sm:p-3 shadow-2xl border-4 border-slate-800">
                                
                                <!-- Phone Notch / Speaker -->
                                <div class="w-24 h-3.5 bg-slate-800 rounded-full mx-auto mb-2 flex items-center justify-center">
                                    <div class="w-2.5 h-2.5 rounded-full bg-slate-950 mr-2"></div>
                                    <div class="w-8 h-1 bg-slate-950 rounded-full"></div>
                                </div>

                                <!-- Screen Container -->
                                <div class="rounded-[2rem] overflow-hidden bg-white border border-slate-700/50 flex flex-col shadow-inner">
                                    
                                    <!-- WhatsApp Header (Authentic #008069) -->
                                    <div class="bg-wa-teal text-white px-3 py-2.5 flex items-center justify-between shadow-sm">
                                        <div class="flex items-center gap-2">
                                            <button type="button" id="sim-back-btn" class="text-white/80 hover:text-white transition-colors" aria-label="Reset message simulation">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                                            </button>
                                            <!-- Official Website Logo Profile Picture -->
                                            <div class="relative flex-shrink-0">
                                                <img src="<?= e(base_url('assets/favicon.png')) ?>" alt="WhatsGateway Logo" class="w-8 h-8 rounded-full object-cover border border-white/40 shadow-xs bg-white p-0.5">
                                                <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-wa-green rounded-full border-2 border-wa-teal"></span>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1">
                                                    <p class="text-xs font-bold text-white leading-tight truncate">WhatsGateway</p>
                                                    <svg class="w-3.5 h-3.5 text-wa-green flex-shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                                                </div>
                                                <p id="sim-status-label" class="text-[10px] text-emerald-100 font-medium leading-none mt-0.5">online</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2.5 text-white/90">
                                            <svg class="w-3.5 h-3.5 cursor-pointer hover:text-white transition-colors" fill="currentColor" viewBox="0 0 24 24"><path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/></svg>
                                            <svg class="w-3.5 h-3.5 cursor-pointer hover:text-white transition-colors" fill="currentColor" viewBox="0 0 24 24"><path d="M20.01 15.38c-1.23 0-2.42-.2-3.53-.56a.977.977 0 00-1.01.24l-1.57 1.97c-2.83-1.44-5.15-3.75-6.59-6.59l1.97-1.57c.28-.28.36-.67.25-1.02A11.36 11.36 0 019 4.29c0-.56-.45-1-1-1H4c-.55 0-1 .45-1 1 0 9.39 7.61 17 17 17 .55 0 1-.45 1-1v-4.01c0-.55-.45-1-.99-1z"/></svg>
                                            <svg class="w-3.5 h-3.5 cursor-pointer hover:text-white transition-colors" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
                                        </div>
                                    </div>

                                    <!-- WhatsApp Chat Content Area (Warm #EFEAE2 wallpaper) -->
                                    <div class="wa-chat-canvas p-3 space-y-2.5 h-[270px] sm:h-[295px] overflow-y-auto flex flex-col justify-end text-xs">
                                        
                                        <!-- Date Separator -->
                                        <div class="flex justify-center mb-1">
                                            <span class="bg-white/80 backdrop-blur-sm text-[10px] font-semibold text-slate-600 px-2.5 py-0.5 rounded-md shadow-wa-bubble">
                                                TODAY
                                            </span>
                                        </div>

                                        <!-- Incoming Bubble (Customer) -->
                                        <div class="flex justify-start">
                                            <div class="max-w-[85%] bg-white text-slate-800 rounded-2xl rounded-tl-none p-2.5 sm:p-3 shadow-wa-bubble border border-black/[0.04]">
                                                <p class="text-xs leading-relaxed">Can I link my existing WhatsApp business number?</p>
                                                <span class="text-[9px] text-slate-400 float-right mt-1 ml-2 dynamic-wa-time">09:10 AM</span>
                                            </div>
                                        </div>

                                        <!-- Outgoing Bubble (Bot Auto-Reply #D9FDD3) -->
                                        <div class="flex justify-end">
                                            <div class="max-w-[85%] bg-wa-sentBubble text-slate-800 rounded-2xl rounded-tr-none p-2.5 sm:p-3 shadow-wa-bubble border border-[#b8f5af]">
                                                <p class="text-xs leading-relaxed font-normal">Yes. Scan the dashboard QR code or enter an 8-digit pairing code. Session keys stay on your server.</p>
                                                <div class="flex items-center justify-end gap-1 mt-1 text-[9px] text-slate-500">
                                                    <span class="dynamic-wa-time">09:10 AM</span>
                                                    <span class="text-wa-blueTick font-bold">✓✓</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Dynamic Live Dispatched Bubble -->
                                        <div id="sim-dynamic-bubble" class="flex justify-end transition-all duration-300 transform opacity-0 translate-y-3">
                                            <div class="max-w-[85%] bg-wa-sentBubble text-slate-800 rounded-2xl rounded-tr-none p-2.5 sm:p-3 shadow-wa-bubble border border-[#b8f5af]">
                                                <p id="sim-bubble-text" class="text-xs leading-relaxed">Your verification code is <strong class="text-wa-darkTeal font-bold">749201</strong>. Valid for 10 minutes.</p>
                                                <div class="flex items-center justify-end gap-1 mt-1 text-[9px] text-slate-500">
                                                    <span id="sim-bubble-time" class="dynamic-wa-time">Just now</span>
                                                    <span id="sim-bubble-ticks" class="text-wa-blueTick font-bold">✓✓</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Typing Indicator -->
                                        <div id="sim-typing-indicator" class="hidden flex justify-start">
                                            <div class="bg-white px-3 py-2 rounded-2xl rounded-tl-none shadow-wa-bubble flex items-center space-x-1 border border-black/[0.04]">
                                                <span class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce"></span>
                                                <span class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce [animation-delay:0.2s]"></span>
                                                <span class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce [animation-delay:0.4s]"></span>
                                            </div>
                                        </div>

                                    </div>

                                    <!-- Sample Preset Chips -->
                                    <div class="bg-[#EFEAE2] px-2.5 py-1.5 border-t border-slate-200/80 flex items-center gap-1.5 overflow-x-auto text-[10px]">
                                        <span class="text-slate-500 font-semibold flex-shrink-0 text-[9px] uppercase tracking-wider">Sample:</span>
                                        <button type="button" class="sim-chip px-2 py-0.5 rounded-full bg-white border border-slate-300 text-slate-700 hover:border-wa-green hover:text-wa-teal transition-all active:scale-95 flex-shrink-0 shadow-2xs cursor-pointer" data-msg="Your verification code is 749201. Valid for 10 minutes.">OTP Alert</button>
                                        <button type="button" class="sim-chip px-2 py-0.5 rounded-full bg-white border border-slate-300 text-slate-700 hover:border-wa-green hover:text-wa-teal transition-all active:scale-95 flex-shrink-0 shadow-2xs cursor-pointer" data-msg="*AI Fact Stored:* Saved Emirates as your preferred airline in durable notebook.">AI Memory</button>
                                        <button type="button" class="sim-chip px-2 py-0.5 rounded-full bg-white border border-slate-300 text-slate-700 hover:border-wa-green hover:text-wa-teal transition-all active:scale-95 flex-shrink-0 shadow-2xs cursor-pointer" data-msg="*Weather Tool:* Current temperature in Tokyo is 18°C with clear skies.">Tool Call</button>
                                        <button type="button" class="sim-chip px-2 py-0.5 rounded-full bg-white border border-slate-300 text-slate-700 hover:border-wa-green hover:text-wa-teal transition-all active:scale-95 flex-shrink-0 shadow-2xs cursor-pointer" data-msg="Order #49281 has shipped with tracking TRK92810.">Shipping</button>
                                    </div>

                                    <!-- WhatsApp Message Input Bar & Interactive Trigger -->
                                    <div class="bg-[#F0F2F5] p-2 border-t border-slate-200 flex items-center gap-1.5">
                                        <div class="flex-1 bg-white rounded-full px-3 py-1.5 text-xs text-slate-600 border border-slate-200 flex items-center justify-between gap-1 shadow-2xs">
                                            <span id="sim-input-label" class="truncate font-mono text-[10px] sm:text-[11px] text-slate-700">POST /api/send.php &bull; OTP alert</span>
                                            <span id="sim-badge" class="text-[9px] font-mono font-bold text-wa-teal bg-emerald-50 px-1.5 py-0.5 rounded flex-shrink-0">200 OK</span>
                                        </div>
                                        <button type="button" id="sim-send-btn" class="w-8 h-8 rounded-full bg-wa-teal hover:bg-emerald-800 text-white flex items-center justify-center flex-shrink-0 shadow-wa-button transition-transform active:scale-95 cursor-pointer touch-manipulation" title="Click to test message simulation" aria-label="Run message simulation" aria-busy="false">
                                            <svg class="w-3.5 h-3.5 ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                                        </button>
                                    </div>

                                </div>
                            </div>

                            <!-- Floating Stat Badges with Dynamic Latency -->
                            <div class="hidden sm:flex absolute -bottom-3 -left-4 px-3 py-1.5 rounded-xl bg-white border border-emerald-100 shadow-wa-card items-center gap-2 z-20">
                                <span class="w-2 h-2 rounded-full bg-wa-green wa-live-dot"></span>
                                <span class="text-[11px] font-mono text-slate-700">Latency: <strong id="live-latency-val" class="text-wa-teal">28ms</strong></span>
                            </div>
                            <div class="hidden sm:flex absolute -top-3 -right-3 px-3 py-1.5 rounded-xl bg-white border border-emerald-200 shadow-wa-card items-center gap-1.5 z-20">
                                <span class="text-[11px] font-mono font-bold text-wa-teal">✓ Baileys v6 Active</span>
                            </div>

                        </div>

                    </div>

                </div>

            </div>
        </section>

        <!-- TECH STACK MARQUEE STRIP (WhatsApp Light) -->
        <div class="border-b border-emerald-100 bg-[#F0FDF4]/60 py-6 overflow-hidden reveal">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <p class="text-center text-xs font-mono uppercase tracking-widest text-emerald-800/80 mb-4 font-bold">
                    Runtime Stack & Protocol Support
                </p>
                <div class="flex flex-wrap items-center justify-center gap-6 sm:gap-12 text-slate-700 text-xs sm:text-sm font-semibold">
                    <div class="flex items-center gap-2 hover:text-wa-teal transition-colors">
                        <span class="w-2 h-2 rounded-full bg-wa-green"></span> PHP 8.2+ Strict Typing
                    </div>
                    <div class="flex items-center gap-2 hover:text-wa-teal transition-colors">
                        <span class="w-2 h-2 rounded-full bg-wa-teal"></span> Node.js Baileys Multi-Device
                    </div>
                    <div class="flex items-center gap-2 hover:text-wa-teal transition-colors">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Meta Graph Schema v21.0
                    </div>
                    <div class="flex items-center gap-2 hover:text-wa-teal transition-colors">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span> MySQL 8 / TiDB Persistence
                    </div>
                    <div class="flex items-center gap-2 hover:text-wa-teal transition-colors">
                        <span class="w-2 h-2 rounded-full bg-purple-500"></span> HMAC-SHA256 Signatures
                    </div>
                    <div class="flex items-center gap-2 hover:text-wa-teal transition-colors">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span> Google Contacts PKCE OAuth
                    </div>
                </div>
            </div>
        </div>

        <!-- BENTO GRID: Core Capabilities in WhatsApp Light Theme -->
        <section id="features" class="py-24 bg-white border-b border-emerald-100 relative">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-3xl mx-auto mb-16 reveal">
                    <span class="text-xs font-mono font-bold text-wa-teal uppercase tracking-widest bg-emerald-50 px-3.5 py-1.5 rounded-full border border-emerald-200">Gateway Features</span>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-slate-900 mt-4 tracking-tight">Core Architecture & Capabilities</h2>
                    <p class="text-slate-600 mt-3 text-base sm:text-lg">
                        Asynchronous queuing, multi-device socket management, and inbound webhook routing on your own infrastructure.
                    </p>
                </div>

                <!-- Bento Grid Layout -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    
                    <!-- BENTO 1: Dual REST API & Meta Cloud Schema (Wide 2-col) -->
                    <div class="lg:col-span-2 rounded-3xl bg-white border border-emerald-100 p-7 sm:p-8 wa-card-interactive shadow-wa-card flex flex-col justify-between reveal">
                        <div>
                            <div class="flex items-center justify-between mb-6">
                                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-wa-teal flex items-center justify-center border border-emerald-200 shadow-sm">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                                <span class="text-xs font-mono font-bold text-wa-teal bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">Drop-in Meta Compatible</span>
                            </div>
                            <h3 class="text-xl sm:text-2xl font-bold text-slate-900 mb-3">Dual REST API & Drop-in Meta Cloud Support</h3>
                            <p class="text-slate-600 text-sm sm:text-base leading-relaxed mb-6">
                                Send messages with our lightweight flat REST format (<code class="text-wa-teal bg-emerald-50 px-1.5 py-0.5 rounded font-mono text-xs">to</code>, <code class="text-wa-teal bg-emerald-50 px-1.5 py-0.5 rounded font-mono text-xs">message</code>, <code class="text-wa-teal bg-emerald-50 px-1.5 py-0.5 rounded font-mono text-xs">media_url</code>) or dispatch exact Meta WhatsApp Cloud API v21.0 JSON payloads without altering your existing codebase.
                            </p>
                        </div>

                        <!-- Mini Code Comparison Pill -->
                        <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4 font-mono text-xs">
                            <div class="flex items-center justify-between text-slate-700 border-b border-slate-200 pb-2 mb-2">
                                <div class="flex gap-2">
                                    <span class="text-wa-teal font-bold">Flat REST:</span>
                                    <span class="text-slate-600">{"to": "91...", "message": "Alert text"}</span>
                                </div>
                                <span class="text-[11px] text-emerald-700 font-bold">100% Drop-in</span>
                            </div>
                            <div class="text-slate-600 truncate">
                                <span class="text-indigo-700 font-bold">Meta Schema:</span>
                                <span>{"messaging_product": "whatsapp", "to": "91...", "type": "text"}</span>
                            </div>
                        </div>
                    </div>

                    <!-- BENTO 2: Dual-Tier Cognitive Memory & Token Governor (Wide 2-col) -->
                    <div class="lg:col-span-2 rounded-3xl bg-white border border-fuchsia-100 p-7 sm:p-8 wa-card-interactive shadow-wa-card flex flex-col justify-between reveal">
                        <div>
                            <div class="flex items-center justify-between mb-6">
                                <div class="w-12 h-12 rounded-2xl bg-fuchsia-50 text-fuchsia-700 flex items-center justify-center border border-fuchsia-200 shadow-sm">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                </div>
                                <span class="text-xs font-mono font-bold text-fuchsia-700 bg-fuchsia-50 px-3 py-1 rounded-full border border-fuchsia-200">Token-Conscious Memory</span>
                            </div>
                            <h3 class="text-xl sm:text-2xl font-bold text-slate-900 mb-3">Dual-Tier Fact Memory & Token Governor</h3>
                            <p class="text-slate-600 text-sm sm:text-base leading-relaxed mb-6">
                                The AI remembers permanent facts, user preferences, and customer notes without prompt bloat. Employs <strong>Top-3 keyword relevance retrieval (< 120 tokens)</strong>, rolling 45-minute dialogue TTL decay, and hands-free WhatsApp chat management (<em>"Remember that..."</em> / <em>"Forget my..."</em>).
                            </p>
                        </div>

                        <div class="rounded-2xl bg-slate-900 text-white p-4 font-mono text-xs space-y-2">
                            <div class="flex items-center justify-between border-b border-slate-800 pb-1.5 text-slate-400">
                                <span>[DURABLE FACT NOTEBOOK]</span>
                                <span class="text-emerald-400 font-bold">1,500t Ceiling Safe</span>
                            </div>
                            <div class="text-slate-300 truncate">
                                <span class="text-fuchsia-400">• Boss Preferred Airline:</span> Emirates (Window seat)
                            </div>
                            <div class="text-slate-300 truncate">
                                <span class="text-sky-400">• Customer (+919876...):</span> Preferred delivery Sector 14
                            </div>
                        </div>
                    </div>

                    <!-- BENTO 3: Multi-Model AI Agent Studio -->
                    <div class="rounded-3xl bg-white border border-emerald-100 p-7 sm:p-8 wa-card-interactive shadow-wa-card flex flex-col justify-between reveal">
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center mb-6 border border-emerald-200 shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-2">Multi-Model AI Agent Studio</h3>
                            <p class="text-slate-600 text-sm leading-relaxed mb-6">
                                Auto-cascading fallback across Google Gemini (2.5/3.5 Flash), Groq LPU (Llama 3.3 70B), and OpenRouter. Features dual modes: Customer Support Desk and 24/7 Boss Chief of Staff.
                            </p>
                        </div>
                        <div class="pt-3 border-t border-slate-100 font-mono text-xs text-emerald-700 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Gemini • Groq LPU • OpenRouter</span>
                        </div>
                    </div>

                    <!-- BENTO 4: Modular App Store & Function Calling -->
                    <div class="rounded-3xl bg-white border border-indigo-100 p-7 sm:p-8 wa-card-interactive shadow-wa-card flex flex-col justify-between reveal">
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-700 flex items-center justify-center mb-6 border border-indigo-200 shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-2">App Store & Function Calling</h3>
                            <p class="text-slate-600 text-sm leading-relaxed mb-6">
                                1-click install tools: live DuckDuckGo web search, exact math calculator, live weather API, CRM webhooks, and 2-phase email drafting with Boss confirmation.
                            </p>
                        </div>
                        <div class="pt-3 border-t border-slate-100 font-mono text-xs text-indigo-700 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                            <span>Two-Phase Safety Protocol</span>
                        </div>
                    </div>

                    <!-- BENTO 5: Smart Few-Shot In-Context Training -->
                    <div class="rounded-3xl bg-white border border-slate-200 p-7 sm:p-8 wa-card-interactive shadow-wa-card flex flex-col justify-between reveal">
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-800 flex items-center justify-center mb-6 border border-slate-200 shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-2">Smart Few-Shot Training</h3>
                            <p class="text-slate-600 text-sm leading-relaxed mb-6">
                                Train your AI on difficult queries and strict negative boundaries (what NOT to do) dynamically without fine-tuning weights or redeploying models.
                            </p>
                        </div>
                        <div class="pt-3 border-t border-slate-100 font-mono text-xs text-slate-700 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-slate-500"></span>
                            <span>Negative Constraint Guardrails</span>
                        </div>
                    </div>

                    <!-- BENTO 2: Instant QR & 8-Digit Pairing -->
                    <div class="rounded-3xl bg-white border border-emerald-100 p-7 sm:p-8 wa-card-interactive shadow-wa-card flex flex-col justify-between reveal reveal-delay-1">
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-wa-teal flex items-center justify-center mb-6 border border-emerald-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-2">Instant QR & 8-Digit Pairing</h3>
                            <p class="text-slate-600 text-sm leading-relaxed mb-6">
                                Link any WhatsApp number without pointing a physical camera. Scan the in-dashboard QR code or submit an 8-digit alphanumeric pairing code directly inside WhatsApp.
                            </p>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 font-mono text-xs flex items-center justify-between">
                            <span class="text-slate-500">Pairing Code:</span>
                            <span class="text-wa-teal font-bold tracking-widest bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">8492-3019</span>
                        </div>
                    </div>

                    <!-- BENTO 3: Asynchronous Queuing & Throttle -->
                    <div class="rounded-3xl bg-white border border-emerald-100 p-7 sm:p-8 wa-card-interactive shadow-wa-card flex flex-col justify-between reveal">
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-700 flex items-center justify-center mb-6 border border-indigo-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-2">Asynchronous Queuing & Throttle</h3>
                            <p class="text-slate-600 text-sm leading-relaxed mb-6">
                                Enqueue outbound messages without holding client connections open. The background worker throttles throughput, sets typing indicators, and retries failed deliveries using exponential backoff.
                            </p>
                        </div>
                        <div class="pt-3 border-t border-slate-100 font-mono text-xs text-indigo-700 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                            <span>Exponential Retry Backoff</span>
                        </div>
                    </div>

                    <!-- BENTO 4: Keyword Auto-Replies -->
                    <div class="rounded-3xl bg-white border border-emerald-100 p-7 sm:p-8 wa-card-interactive shadow-wa-card flex flex-col justify-between reveal reveal-delay-1">
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center mb-6 border border-amber-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-2">Keyword Auto-Replies</h3>
                            <p class="text-slate-600 text-sm leading-relaxed mb-6">
                                Match incoming queries with exact strings, substrings, or fallback welcome responses directly from the worker process without running separate bot infrastructure.
                            </p>
                        </div>
                        <div class="pt-3 border-t border-slate-100 font-mono text-xs text-amber-700 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <span>In-Process Rule Engine</span>
                        </div>
                    </div>

                    <!-- BENTO 5: HMAC-SHA256 Signed Webhooks -->
                    <div class="rounded-3xl bg-white border border-emerald-100 p-7 sm:p-8 wa-card-interactive shadow-wa-card flex flex-col justify-between reveal reveal-delay-2">
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-700 flex items-center justify-center mb-6 border border-purple-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-2">HMAC-SHA256 Signed Webhooks</h3>
                            <p class="text-slate-600 text-sm leading-relaxed mb-6">
                                Subscribe to HTTPS callbacks for inbound messages, delivery receipts, and read confirmations. Every webhook payload is signed with your shared secret to guarantee authenticity.
                            </p>
                        </div>
                        <div class="pt-3 border-t border-slate-100 font-mono text-xs text-purple-700 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                            <span>Tamper-Proof Verification</span>
                        </div>
                    </div>

                </div>

            </div>
        </section>

        <!-- ARCHITECTURE PIPELINE SECTION (WhatsApp Light Theme) -->
        <section id="architecture" class="py-24 bg-wa-lightBg/50 border-b border-emerald-100 relative">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-3xl mx-auto mb-16 reveal">
                    <span class="text-xs font-mono font-bold text-wa-teal uppercase tracking-widest bg-emerald-50 px-3.5 py-1.5 rounded-full border border-emerald-200">System Flow</span>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-slate-900 mt-4 tracking-tight">Decoupled Request Pipeline</h2>
                    <p class="text-slate-600 mt-3 text-base sm:text-lg">
                        Outbound requests queue immediately in MySQL. The background worker manages the persistent WhatsApp socket and delivers messages independently.
                    </p>
                </div>

                <!-- 3 Flow Steps -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    
                    <!-- Step 1 -->
                    <div class="p-8 rounded-3xl bg-white border border-emerald-100 shadow-wa-card wa-card-interactive flex flex-col justify-between border-t-4 border-t-wa-teal reveal">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="w-9 h-9 rounded-xl bg-emerald-50 text-wa-teal flex items-center justify-center font-mono font-bold text-sm border border-emerald-200">01</span>
                                <span class="text-xs font-mono font-semibold text-slate-500">HTTP REST INGESTION</span>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-2">Ingestion & Auth Gateway</h3>
                            <p class="text-sm text-slate-600 leading-relaxed">
                                Your application sends a <code class="text-wa-teal bg-emerald-50 px-1 py-0.5 rounded font-mono text-xs">POST /api/send.php</code>. PHP 8.2 validates the API token, enforces rate limits, normalizes the recipient phone to international format, and validates media attachments.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-slate-100 font-mono text-xs text-wa-teal font-semibold">
                            &check; Responds with <code class="bg-slate-100 px-1.5 py-0.5 rounded text-slate-800">200 Queued</code> in &lt;15ms
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="p-8 rounded-3xl bg-white border border-emerald-100 shadow-wa-card wa-card-interactive flex flex-col justify-between border-t-4 border-t-indigo-500 reveal reveal-delay-1">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center font-mono font-bold text-sm border border-indigo-200">02</span>
                                <span class="text-xs font-mono font-semibold text-slate-500">MYSQL / TIDB BUFFER</span>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-2">Persistent Queue Buffer</h3>
                            <p class="text-sm text-slate-600 leading-relaxed">
                                Messages enter the <code class="text-indigo-700 bg-indigo-50 px-1 py-0.5 rounded font-mono text-xs">message_queue</code> table with state <code class="text-amber-700 bg-amber-50 px-1 py-0.5 rounded">pending</code>. Database persistence ensures messages survive server restarts or temporary network glitches.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-slate-100 font-mono text-xs text-indigo-700 font-semibold">
                            &check; Automatic exponential retry backoff
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="p-8 rounded-3xl bg-white border border-emerald-100 shadow-wa-card wa-card-interactive flex flex-col justify-between border-t-4 border-t-wa-green reveal reveal-delay-2">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="w-9 h-9 rounded-xl bg-emerald-50 text-wa-teal flex items-center justify-center font-mono font-bold text-sm border border-emerald-200">03</span>
                                <span class="text-xs font-mono font-semibold text-slate-500">NODE.JS BAILEYS</span>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-2">Baileys Dispatch Engine</h3>
                            <p class="text-sm text-slate-600 leading-relaxed">
                                The Node.js Baileys worker maintains a persistent multi-device WebSocket connection to WhatsApp. It simulates human typing presence (<code class="text-wa-teal bg-emerald-50 px-1 py-0.5 rounded font-mono text-xs">composing</code>), applies anti-ban pacing, and dispatches to WhatsApp servers.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-slate-100 font-mono text-xs text-wa-teal font-semibold">
                            &check; Multi-File Auth Session Persistence
                        </div>
                    </div>

                </div>

                <!-- Inbound Loop Card -->
                <div class="mt-8 p-6 sm:p-8 rounded-3xl bg-white border border-emerald-100 shadow-wa-card flex flex-col md:flex-row items-center justify-between gap-6 reveal">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-700 flex items-center justify-center font-bold text-xl border border-purple-200 flex-shrink-0">
                            &harr;
                        </div>
                        <div>
                            <h4 class="text-base sm:text-lg font-bold text-slate-900">Inbound Webhooks & Automated Chatbot Processing</h4>
                            <p class="text-xs sm:text-sm text-slate-600 mt-1">
                                When a customer replies or a message delivery state changes, Baileys immediately fires your keyword chatbot rules and forwards an HMAC-SHA256 signed event payload to your webhook endpoint.
                            </p>
                        </div>
                    </div>
                    <a href="#code-playground" class="flex-shrink-0 text-xs sm:text-sm font-bold px-5 py-3 rounded-xl bg-wa-teal hover:bg-emerald-800 text-white transition-all shadow-wa-button">
                        Inspect Schemas &rarr;
                    </a>
                </div>

            </div>
        </section>

        <!-- LIVE CODE PLAYGROUND & SIMULATOR -->
        <section id="code-playground" class="py-24 bg-white border-b border-emerald-100 relative">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-3xl mx-auto mb-12 reveal">
                    <span class="text-xs font-mono font-bold text-wa-teal uppercase tracking-widest bg-emerald-50 px-3.5 py-1.5 rounded-full border border-emerald-200">Integration Examples</span>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-slate-900 mt-4 tracking-tight">Code Snippets & API Testing</h2>
                    <p class="text-slate-600 mt-3 text-base sm:text-lg">
                        Dispatch text, media, and templates using standard HTTP requests or drop-in Meta WhatsApp Cloud API schemas.
                    </p>
                </div>

                <!-- SIMULATION TYPE CONTROLS -->
                <div class="mb-8 p-4 rounded-3xl bg-slate-50 border border-slate-200/90 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4 reveal">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-mono font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5 mr-1">
                            <svg class="w-4 h-4 text-wa-teal" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Simulation Type:
                        </span>
                        <div class="inline-flex flex-wrap items-center p-1 rounded-2xl bg-white border border-slate-200 shadow-xs gap-1" id="sim-type-group" role="group">
                            <button type="button" class="sim-type-btn active px-3.5 py-1.5 rounded-xl text-xs font-mono font-bold bg-wa-teal text-white shadow-xs transition-all flex items-center gap-1.5 cursor-pointer" data-sim="otp">
                                <span>2FA OTP</span>
                            </button>
                            <button type="button" class="sim-type-btn px-3.5 py-1.5 rounded-xl text-xs font-mono font-medium text-slate-700 hover:text-wa-teal hover:bg-slate-50 transition-all flex items-center gap-1.5 cursor-pointer" data-sim="media">
                                <span>PDF Invoice</span>
                            </button>
                            <button type="button" class="sim-type-btn px-3.5 py-1.5 rounded-xl text-xs font-mono font-medium text-slate-700 hover:text-wa-teal hover:bg-slate-50 transition-all flex items-center gap-1.5 cursor-pointer" data-sim="meta">
                                <span>Meta Cloud v21</span>
                            </button>
                            <button type="button" class="sim-type-btn px-3.5 py-1.5 rounded-xl text-xs font-mono font-medium text-slate-700 hover:text-wa-teal hover:bg-slate-50 transition-all flex items-center gap-1.5 cursor-pointer" data-sim="webhook">
                                <span>Signed Webhook</span>
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-xs font-mono text-slate-600">
                        <span class="w-2.5 h-2.5 rounded-full bg-wa-green animate-pulse"></span>
                        <span id="sim-type-status">Active Scenario: <strong class="text-wa-darkTeal font-bold">Transactional 2FA OTP Alert</strong></span>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
                    
                    <!-- Code Snippets (7 cols) -->
                    <div class="lg:col-span-7 flex flex-col rounded-3xl bg-slate-950 text-slate-100 border border-slate-800 shadow-xl overflow-hidden reveal">
                        
                        <!-- Tabs Bar -->
                        <div class="bg-slate-900 px-4 py-3.5 border-b border-slate-800 flex flex-wrap items-center justify-between gap-2">
                            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0" role="tablist">
                                <button type="button" class="code-tab active px-3.5 py-1.5 rounded-lg text-xs font-mono font-bold transition-all bg-emerald-500/20 text-emerald-400 border border-emerald-500/30" data-lang="curl">cURL</button>
                                <button type="button" class="code-tab px-3.5 py-1.5 rounded-lg text-xs font-mono font-medium transition-all text-slate-400 hover:text-white hover:bg-white/5" data-lang="node">Node.js</button>
                                <button type="button" class="code-tab px-3.5 py-1.5 rounded-lg text-xs font-mono font-medium transition-all text-slate-400 hover:text-white hover:bg-white/5" data-lang="python">Python</button>
                                <button type="button" class="code-tab px-3.5 py-1.5 rounded-lg text-xs font-mono font-medium transition-all text-slate-400 hover:text-white hover:bg-white/5" data-lang="php">PHP</button>
                                <button type="button" class="code-tab px-3.5 py-1.5 rounded-lg text-xs font-mono font-medium transition-all text-slate-400 hover:text-white hover:bg-white/5" data-lang="meta">Meta Cloud</button>
                            </div>
                            
                            <!-- Copy Button -->
                            <button id="copy-code-btn" type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-mono text-slate-300 hover:text-white hover:bg-white/10 transition-colors border border-white/10" aria-label="Copy Code to Clipboard">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                                <span id="copy-btn-text">Copy</span>
                            </button>
                        </div>

                        <!-- Snippet Code Panels -->
                        <div class="p-6 font-mono text-xs sm:text-[13px] leading-relaxed flex-1 overflow-x-auto text-slate-200 min-h-[320px]">
                            
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
<pre><code><span class="text-slate-500">// Node.js native fetch</span>
<span class="text-purple-400">const</span> response = <span class="text-purple-400">await</span> fetch(<span class="text-emerald-300">'<?= e(public_url('api/send.php')) ?>'</span>, {
  method: <span class="text-emerald-300">'POST'</span>,
  headers: {
    <span class="text-emerald-300">'Authorization'</span>: <span class="text-amber-300">'Bearer wa_live_YOUR_API_KEY'</span>,
    <span class="text-emerald-300">'Content-Type'</span>: <span class="text-emerald-300">'application/json'</span>
  },
  body: JSON.stringify({
    to: <span class="text-emerald-300">'919876543210'</span>,
    message: <span class="text-emerald-300">'Your verification OTP is *582910*. Valid for 10 minutes.'</span>,
    humanize: <span class="text-purple-400">true</span>
  })
});

<span class="text-purple-400">const</span> data = <span class="text-purple-400">await</span> response.json();
console.log(<span class="text-emerald-300">'Message queued:'</span>, data.message_id);</code></pre>
                            </div>

                            <!-- Python -->
                            <div id="snippet-python" class="code-snippet-panel hidden">
<pre><code><span class="text-slate-500"># Python 3 requests</span>
<span class="text-purple-400">import</span> requests

url = <span class="text-emerald-300">"<?= e(public_url('api/send.php')) ?>"</span>
headers = {
    <span class="text-emerald-300">"X-API-Key"</span>: <span class="text-amber-300">"wa_live_YOUR_API_KEY"</span>,
    <span class="text-emerald-300">"Content-Type"</span>: <span class="text-emerald-300">"application/json"</span>
}
payload = {
    <span class="text-emerald-300">"to"</span>: <span class="text-emerald-300">"919876543210"</span>,
    <span class="text-emerald-300">"message"</span>: <span class="text-emerald-300">"Your verification OTP is *582910*. Valid for 10 minutes."</span>,
    <span class="text-emerald-300">"humanize"</span>: <span class="text-purple-400">True</span>
}

response = requests.post(url, json=payload, headers=headers)
print(response.json())</code></pre>
                            </div>

                            <!-- PHP -->
                            <div id="snippet-php" class="code-snippet-panel hidden">
<pre><code><span class="text-slate-500">// PHP 8.2+ cURL Client</span>
$ch = curl_init(<span class="text-emerald-300">'<?= e(public_url('api/send.php')) ?>'</span>);
curl_setopt_array($ch, [
    CURLOPT_POST => <span class="text-purple-400">true</span>,
    CURLOPT_HTTPHEADER => [
        <span class="text-emerald-300">'X-API-Key: '</span> . $apiKey,
        <span class="text-emerald-300">'Content-Type: application/json'</span>
    ],
    CURLOPT_POSTFIELDS => json_encode([
        <span class="text-emerald-300">'to'</span> => <span class="text-emerald-300">'919876543210'</span>,
        <span class="text-emerald-300">'message'</span> => <span class="text-emerald-300">'Your verification OTP is *582910*. Valid for 10 minutes.'</span>,
        <span class="text-emerald-300">'humanize'</span> => <span class="text-purple-400">true</span>
    ]),
    CURLOPT_RETURNTRANSFER => <span class="text-purple-400">true</span>
]);
$res = json_decode(curl_exec($ch), <span class="text-purple-400">true</span>);
curl_close($ch);</code></pre>
                            </div>

                            <!-- Meta Cloud -->
                            <div id="snippet-meta" class="code-snippet-panel hidden">
<pre><code><span class="text-slate-500"># Drop-in Meta WhatsApp Cloud API v21.0 Schema</span>
curl -X POST "<?= e(public_url('api/meta-messages.php')) ?>" \
  -H "<span class="text-emerald-400">Authorization</span>: <span class="text-amber-300">Bearer wa_live_YOUR_API_KEY</span>" \
  -H "Content-Type: application/json" \
  -d '{
    "<span class="text-sky-300">messaging_product</span>": "<span class="text-slate-200">whatsapp</span>",
    "<span class="text-sky-300">recipient_type</span>": "<span class="text-slate-200">individual</span>",
    "<span class="text-sky-300">to</span>": "<span class="text-slate-200">919876543210</span>",
    "<span class="text-sky-300">type</span>": "<span class="text-slate-200">text</span>",
    "<span class="text-sky-300">text</span>": {
      "<span class="text-sky-300">body</span>": "<span class="text-slate-200">Meta drop-in compatible message.</span>"
    }
  }'</code></pre>
                            </div>

                        </div>

                        <!-- Run Simulation Button Inside Playground -->
                        <div class="px-6 py-4 bg-slate-900 border-t border-slate-800 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-slate-400 font-mono hidden sm:inline">Execution Mode:</span>
                                <span id="current-sim-mode-badge" class="text-[11px] font-mono text-emerald-300 bg-emerald-950/60 border border-emerald-800/60 px-2 py-0.5 rounded">
                                    Mock Dispatch
                                </span>
                            </div>
                            <button type="button" id="playground-run-btn" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold text-slate-900 bg-wa-green hover:bg-emerald-400 transition-all shadow-wa-button active:scale-95 cursor-pointer touch-manipulation" aria-busy="false">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
                                <span id="playground-run-btn-text">Run Type Simulation</span>
                            </button>
                        </div>

                    </div>

                    <!-- Live JSON Response Output (5 cols) -->
                    <div class="lg:col-span-5 rounded-3xl bg-slate-950 text-slate-100 border border-slate-800 shadow-xl overflow-hidden flex flex-col justify-between reveal reveal-delay-1">
                        
                        <!-- Header -->
                        <div class="bg-slate-900 px-4 py-3.5 border-b border-slate-800 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-wa-green"></span>
                                <span class="text-xs font-mono font-bold text-slate-300">GATEWAY RESPONSE</span>
                            </div>
                            <div class="flex items-center gap-2 font-mono text-xs">
                                <span id="response-status-pill" class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-2 py-0.5 rounded font-bold">
                                    200 OK
                                </span>
                                <span id="response-time-pill" class="text-slate-400">
                                    14ms
                                </span>
                            </div>
                        </div>

                        <!-- Response JSON Viewer & Live Terminal Simulation Logs -->
                        <div class="p-6 font-mono text-xs leading-relaxed text-slate-300 flex-1 flex flex-col justify-between overflow-x-auto min-h-[340px]">
                            <div>
                                <div id="sim-terminal-logs" class="hidden mb-3 pb-3 border-b border-slate-800/80 text-[11px] font-mono text-emerald-400 space-y-1"></div>
                                <pre id="playground-response-code" class="text-slate-300 leading-relaxed"><code class="text-slate-300">{
  <span class="text-sky-300">"success"</span>: <span class="text-purple-400">true</span>,
  <span class="text-sky-300">"message_id"</span>: <span class="text-emerald-300">"wg_msg_8f10b7e2a9"</span>,
  <span class="text-sky-300">"status"</span>: <span class="text-amber-300">"queued"</span>,
  <span class="text-sky-300">"recipient"</span>: <span class="text-emerald-300">"+919876543210"</span>,
  <span class="text-sky-300">"queued_at"</span>: <span class="text-emerald-300">"<?= date('c') ?>"</span>,
  <span class="text-sky-300">"retry_count"</span>: <span class="text-purple-400">0</span>,
  <span class="text-sky-300">"humanized_delay_ms"</span>: <span class="text-purple-400">1250</span>,
  <span class="text-sky-300">"gateway"</span>: {
    <span class="text-sky-300">"engine"</span>: <span class="text-emerald-300">"Baileys_v6"</span>,
    <span class="text-sky-300">"socket_state"</span>: <span class="text-emerald-300">"connected"</span>
  }
}</code></pre>
                            </div>
                            
                            <div class="mt-4 pt-3 border-t border-slate-800 text-[11px] text-slate-400 flex items-center justify-between">
                                <span id="response-scenario-tag" class="text-slate-400">Type: 2FA OTP Token</span>
                                <span class="text-wa-green font-bold">100% On-Premise</span>
                            </div>
                        </div>

                    </div>

                </div>

            </div>
        </section>

        <!-- LIVE WEBHOOK STREAM TICKER (WhatsApp Light Theme) -->
        <section id="webhooks" class="py-24 bg-wa-lightBg/40 border-b border-emerald-100 relative">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                    
                    <!-- Left: Description -->
                    <div class="lg:col-span-5 space-y-5 reveal">
                        <span class="text-xs font-mono font-bold text-wa-teal uppercase tracking-widest bg-emerald-50 px-3.5 py-1.5 rounded-full border border-emerald-200">Real-Time Event Callbacks</span>
                        <h2 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">HMAC-SHA256 Signed Webhooks</h2>
                        <p class="text-slate-600 text-sm sm:text-base leading-relaxed">
                            Subscribe your server to instant HTTPS webhooks for inbound customer replies, message receipts (<code class="text-wa-teal font-mono">sent</code>, <code class="text-wa-teal font-mono">delivered</code>, <code class="text-wa-teal font-mono">read</code>), media attachments, and customer typing statuses.
                        </p>
                        <ul class="space-y-3 text-xs sm:text-sm text-slate-700 font-medium">
                            <li class="flex items-center gap-2.5">
                                <span class="w-5 h-5 rounded-full bg-emerald-100 text-wa-teal flex items-center justify-center font-bold text-xs">&check;</span>
                                <span>Header <code class="text-wa-teal bg-emerald-50 px-1 py-0.5 rounded font-mono">X-Hub-Signature-256</code> on every POST</span>
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="w-5 h-5 rounded-full bg-emerald-100 text-wa-teal flex items-center justify-center font-bold text-xs">&check;</span>
                                <span>Instant delivery confirmation callbacks</span>
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="w-5 h-5 rounded-full bg-emerald-100 text-wa-teal flex items-center justify-center font-bold text-xs">&check;</span>
                                <span>Configurable retry policy with exponential delay</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Right: Live Webhook Console Feed -->
                    <div class="lg:col-span-7 rounded-3xl bg-white border border-emerald-100 shadow-wa-card p-6 overflow-hidden reveal reveal-delay-1">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                            <div class="flex items-center gap-2 font-mono text-xs text-slate-700 font-bold">
                                <span class="w-2.5 h-2.5 rounded-full bg-wa-green animate-pulse"></span>
                                <span>LIVE WEBHOOK EVENT STREAM</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" id="webhook-stream-toggle" class="text-[10px] font-mono text-emerald-800 bg-emerald-50 hover:bg-emerald-100 px-2.5 py-0.5 rounded-full border border-emerald-200 flex items-center gap-1.5 transition-colors cursor-pointer" title="Pause or resume auto-stream">
                                    <span class="w-1.5 h-1.5 rounded-full bg-wa-green"></span>
                                    <span id="webhook-toggle-label">Live &bull; Pause</span>
                                </button>
                                <span class="text-[10px] font-mono text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200 hidden sm:inline">HTTP POST</span>
                            </div>
                        </div>

                        <div id="webhook-stream-container" class="space-y-3 font-mono text-xs">
                            
                            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex items-start justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-wa-teal">message.delivered</span>
                                        <span class="text-slate-500 text-[11px]">wg_msg_8f10b7e2a9 &bull; +919876543210</span>
                                    </div>
                                    <p class="text-slate-700 text-[11px]">Status transition: PENDING &rarr; DELIVERED (Socket ACK)</p>
                                </div>
                                <span class="text-slate-400 text-[10px]">Just now</span>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex items-start justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-sky-100 text-sky-800">message.read</span>
                                        <span class="text-slate-500 text-[11px]">wg_msg_8f10b7e2a9 &bull; Blue Tick (✓✓)</span>
                                    </div>
                                    <p class="text-slate-700 text-[11px]">Recipient opened and read the verification message.</p>
                                </div>
                                <span class="text-slate-400 text-[10px]">2s ago</span>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex items-start justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800">inbound.message</span>
                                        <span class="text-slate-500 text-[11px]">From: +919876543210 &bull; Text</span>
                                    </div>
                                    <p class="text-slate-700 text-[11px]">Payload: "Thank you, OTP received!"</p>
                                </div>
                                <span class="text-slate-400 text-[10px]">6s ago</span>
                            </div>

                        </div>
                    </div>

                </div>

            </div>
        </section>

        <!-- GOOGLE OAUTH & CONTACTS INTEGRATION (COMPLIANCE) -->
        <section id="google-sync" class="py-24 bg-white border-b border-emerald-100 relative">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="rounded-3xl bg-emerald-50/50 border border-emerald-200/90 p-8 sm:p-12 relative overflow-hidden shadow-sm reveal">
                    
                    <div class="relative z-10 max-w-3xl">
                        
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-white text-slate-800 border border-emerald-200 mb-6 shadow-sm">
                            <svg class="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                            <span>Google API Services User Data Policy Compliance</span>
                        </div>

                        <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                            Secure Google Contacts PKCE Sync
                        </h2>
                        
                        <p class="mt-3 text-slate-600 text-sm sm:text-base leading-relaxed">
                            Sync your existing customer contacts directly from your authorized Google Account. We use standard OAuth 2.0 PKCE to request strictly read-only access (<code class="bg-white border border-emerald-200 text-emerald-800 px-1 py-0.5 rounded font-mono text-xs">contacts.readonly</code>) solely for populating your local address book.
                        </p>

                        <!-- Strict Compliance Guarantees -->
                        <div class="mt-8 space-y-3.5 text-xs sm:text-sm text-slate-700">
                            
                            <div class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-emerald-100 text-wa-teal flex items-center justify-center flex-shrink-0 mt-0.5 font-bold">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span>
                                    <strong class="text-slate-900">Strict Limited Use Compliance:</strong> WhatsGateway's use and transfer to any other app of information received from Google APIs will adhere to the <a href="https://developers.google.com/terms/api-services-user-data-policy" target="_blank" rel="noopener noreferrer" class="text-wa-teal font-semibold underline hover:text-emerald-800">Google API Services User Data Policy</a>, including the Limited Use requirements.
                                </span>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-emerald-100 text-wa-teal flex items-center justify-center flex-shrink-0 mt-0.5 font-bold">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span>
                                    <strong class="text-slate-900">Zero Resale & No Ads:</strong> Synced Google contact details are never shared with third parties, never sold to data brokers, and never used for behavioral advertising or model training.
                                </span>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-emerald-100 text-wa-teal flex items-center justify-center flex-shrink-0 mt-0.5 font-bold">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span>
                                    <strong class="text-slate-900">Full User Control:</strong> Disconnect your Google Account and wipe all imported records with a single click in your dashboard at any time, or revoke access directly from your Google Security Settings.
                                </span>
                            </div>

                        </div>

                        <!-- Policy Buttons -->
                        <div class="mt-8 flex flex-wrap gap-3">
                            <a href="<?= e(base_url('privacy')) ?>" class="text-xs font-semibold px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-800 transition-colors border border-emerald-200 shadow-sm">
                                Read Privacy Policy &rarr;
                            </a>
                            <a href="<?= e(base_url('terms')) ?>" class="text-xs font-semibold px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-800 transition-colors border border-emerald-200 shadow-sm">
                                Read Terms of Service &rarr;
                            </a>
                        </div>

                    </div>

                </div>

            </div>
        </section>

        <!-- ARCHITECTURAL & COST COMPARISON TABLE (WhatsApp Light Theme) -->
        <section id="comparison" class="py-24 bg-wa-lightBg/50 border-b border-emerald-100 relative">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-3xl mx-auto mb-16 reveal">
                    <span class="text-xs font-mono font-bold text-wa-teal uppercase tracking-widest bg-emerald-50 px-3.5 py-1.5 rounded-full border border-emerald-200">Transparent Breakdown</span>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-slate-900 mt-4 tracking-tight">How WhatsGateway Compares</h2>
                    <p class="text-slate-600 mt-3 text-base sm:text-lg">
                        Compare self-hosting our open-source gateway against official Meta Cloud and commercial SaaS brokers.
                    </p>
                </div>

                <div class="overflow-x-auto rounded-3xl border border-emerald-200 bg-white shadow-wa-card reveal">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-emerald-50/70 border-b border-emerald-200 text-slate-700 font-mono">
                            <tr>
                                <th class="p-5 font-semibold">Capability / Metric</th>
                                <th class="p-5 font-bold text-wa-darkTeal bg-emerald-100/60 border-x border-emerald-300">
                                    <div class="flex items-center gap-1.5">
                                        <span>WhatsGateway (Self-Hosted)</span>
                                        <span class="text-[9px] bg-wa-green text-slate-900 font-sans font-bold px-1.5 py-0.5 rounded">RECOMMENDED</span>
                                    </div>
                                </th>
                                <th class="p-5 font-semibold text-slate-600">Meta WhatsApp Cloud API</th>
                                <th class="p-5 font-semibold text-slate-600">Commercial SaaS Brokers</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-emerald-100 text-slate-700">
                            
                            <tr>
                                <td class="p-5 font-bold text-slate-900">Gateway Per-Message Markup</td>
                                <td class="p-5 font-bold text-wa-teal bg-emerald-50/30 border-x border-emerald-200 font-mono text-base">$0.00 (Zero)</td>
                                <td class="p-5 text-slate-600">Per-conversation Meta charges</td>
                                <td class="p-5 text-slate-600">$0.005 &ndash; $0.05 extra per msg</td>
                            </tr>

                            <tr>
                                <td class="p-5 font-bold text-slate-900">Setup & Phone Linking</td>
                                <td class="p-5 font-bold text-wa-teal bg-emerald-50/30 border-x border-emerald-200">Instant QR / 8-digit Pairing Code</td>
                                <td class="p-5 text-slate-600">Requires Meta Business Verification</td>
                                <td class="p-5 text-slate-600">Complex onboarding & review</td>
                            </tr>

                            <tr>
                                <td class="p-5 font-bold text-slate-900">Database & Session Control</td>
                                <td class="p-5 font-bold text-wa-teal bg-emerald-50/30 border-x border-emerald-200">100% on your private server/DB</td>
                                <td class="p-5 text-slate-600">Hosted in Meta cloud</td>
                                <td class="p-5 text-slate-600">Vendor cloud lock-in</td>
                            </tr>

                            <tr>
                                <td class="p-5 font-bold text-slate-900">API Payload Compatibility</td>
                                <td class="p-5 font-bold text-wa-teal bg-emerald-50/30 border-x border-emerald-200">Flat REST + Drop-in Meta Cloud schema</td>
                                <td class="p-5 text-slate-600">Meta Graph schema only</td>
                                <td class="p-5 text-slate-600">Proprietary vendor SDKs</td>
                            </tr>

                            <tr>
                                <td class="p-5 font-bold text-slate-900">Inbound Webhooks</td>
                                <td class="p-5 font-bold text-wa-teal bg-emerald-50/30 border-x border-emerald-200">HMAC-SHA256 Signed POST Callbacks</td>
                                <td class="p-5 text-slate-600">Hub signature validation</td>
                                <td class="p-5 text-slate-600">Varies / Rate-limited</td>
                            </tr>

                            <tr>
                                <td class="p-5 font-bold text-slate-900">Built-in Keyword Chatbots</td>
                                <td class="p-5 font-bold text-wa-teal bg-emerald-50/30 border-x border-emerald-200">Included &bull; Instant auto-reply rules</td>
                                <td class="p-5 text-slate-600">Requires external bot server</td>
                                <td class="p-5 text-slate-600">Expensive add-on tier</td>
                            </tr>

                            <tr>
                                <td class="p-5 font-bold text-slate-900">Google Contacts Sync</td>
                                <td class="p-5 font-bold text-wa-teal bg-emerald-50/30 border-x border-emerald-200">Built-in Google OAuth2 PKCE</td>
                                <td class="p-5 text-slate-600">None</td>
                                <td class="p-5 text-slate-600">Requires third-party sync tool</td>
                            </tr>

                        </tbody>
                    </table>
                </div>

            </div>
        </section>

        <!-- TECHNICAL DEVELOPER FAQ (WhatsApp Light Theme) -->
        <section id="faq" class="py-24 bg-white border-b border-emerald-100 relative">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-2xl mx-auto mb-16 reveal">
                    <span class="text-xs font-mono font-bold text-wa-teal uppercase tracking-widest bg-emerald-50 px-3.5 py-1.5 rounded-full border border-emerald-200">Frequently Asked Questions</span>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-slate-900 mt-4 tracking-tight">Direct Answers for Engineers</h2>
                    <p class="text-slate-600 mt-3 text-base">
                        Clear technical explanations with no marketing fluff.
                    </p>
                </div>

                <div class="space-y-4 reveal">
                    
                    <details class="group p-6 rounded-2xl bg-slate-50/80 border border-slate-200 hover:border-emerald-200 transition-all">
                        <summary class="flex items-center justify-between cursor-pointer list-none text-slate-900 font-bold text-base sm:text-lg">
                            <span>Do I need a verified Meta Business Manager account to use this?</span>
                            <span class="w-8 h-8 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-500 group-open:rotate-180 transition-transform shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </span>
                        </summary>
                        <div class="mt-4 text-sm text-slate-600 leading-relaxed border-t border-slate-200 pt-4">
                            No. Unlike the official Meta Cloud API which mandates submitting business registration papers and credit checks, this gateway uses the Baileys multi-device socket protocol. You can link any standard or business WhatsApp number simply by scanning the QR code or submitting an 8-digit phone pairing code.
                        </div>
                    </details>

                    <details class="group p-6 rounded-2xl bg-slate-50/80 border border-slate-200 hover:border-emerald-200 transition-all">
                        <summary class="flex items-center justify-between cursor-pointer list-none text-slate-900 font-bold text-base sm:text-lg">
                            <span>How does this gateway protect against account bans?</span>
                            <span class="w-8 h-8 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-500 group-open:rotate-180 transition-transform shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </span>
                        </summary>
                        <div class="mt-4 text-sm text-slate-600 leading-relaxed border-t border-slate-200 pt-4">
                            WhatsApp flags numbers when high volumes of unsolicited messages are blasted simultaneously. WhatsGateway implements several anti-ban protections:
                            <ul class="list-disc pl-5 mt-3 space-y-1 text-slate-600">
                                <li>Configurable queue pacing delays (<code class="bg-white px-1.5 py-0.5 rounded text-wa-teal border border-slate-200 font-mono text-xs">SEND_DELAY_MS</code>, defaulting to 2500ms between messages).</li>
                                <li>Automatic human typing presence updates (<code class="bg-white px-1.5 py-0.5 rounded text-wa-teal border border-slate-200 font-mono text-xs">sendPresenceUpdate('composing')</code>).</li>
                                <li>We strongly advise sending transactional notifications and reminders only to users who have explicitly opted in.</li>
                            </ul>
                        </div>
                    </details>

                    <details class="group p-6 rounded-2xl bg-slate-50/80 border border-slate-200 hover:border-emerald-200 transition-all">
                        <summary class="flex items-center justify-between cursor-pointer list-none text-slate-900 font-bold text-base sm:text-lg">
                            <span>Where are WhatsApp session keys and messages stored?</span>
                            <span class="w-8 h-8 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-500 group-open:rotate-180 transition-transform shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </span>
                        </summary>
                        <div class="mt-4 text-sm text-slate-600 leading-relaxed border-t border-slate-200 pt-4">
                            All session keys and credentials are saved locally in your server's <code class="bg-white px-1.5 py-0.5 rounded text-wa-teal border border-slate-200 font-mono text-xs">whatsapp-api-data/sessions/</code> directory using Baileys' multi-file auth state. Messages, delivery states, and contact records reside directly in your local MySQL / TiDB database. Nothing is transmitted to external servers or third-party brokers.
                        </div>
                    </details>

                    <details class="group p-6 rounded-2xl bg-slate-50/80 border border-slate-200 hover:border-emerald-200 transition-all">
                        <summary class="flex items-center justify-between cursor-pointer list-none text-slate-900 font-bold text-base sm:text-lg">
                            <span>How does the Meta Cloud API drop-in compatibility work?</span>
                            <span class="w-8 h-8 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-500 group-open:rotate-180 transition-transform shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </span>
                        </summary>
                        <div class="mt-4 text-sm text-slate-600 leading-relaxed border-t border-slate-200 pt-4">
                            If you have existing software built for Meta's official Cloud API, you don't need to rewrite your payload generators. Simply point your HTTP base URL to this gateway and pass your API key as the <code class="bg-white px-1.5 py-0.5 rounded text-wa-teal border border-slate-200 font-mono text-xs">Authorization: Bearer</code> token. The endpoint parses Meta schemas (including text bodies, templates, documents, and contacts) and returns compliant JSON response bodies.
                        </div>
                    </details>

                    <details class="group p-6 rounded-2xl bg-slate-50/80 border border-slate-200 hover:border-emerald-200 transition-all">
                        <summary class="flex items-center justify-between cursor-pointer list-none text-slate-900 font-bold text-base sm:text-lg">
                            <span>What are the minimum hardware requirements?</span>
                            <span class="w-8 h-8 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-500 group-open:rotate-180 transition-transform shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </span>
                        </summary>
                        <div class="mt-4 text-sm text-slate-600 leading-relaxed border-t border-slate-200 pt-4">
                            Because the architecture is lightweight (PHP 8.2 + Node.js Baileys), a standard 1 vCPU / 1GB RAM VPS (costing ~$4-$5/month on Hetzner, DigitalOcean, or Linode) is more than sufficient to comfortably handle queues and continuous worker socket loops.
                        </div>
                    </details>

                    <details class="group p-6 rounded-2xl bg-slate-50/80 border border-slate-200 hover:border-emerald-200 transition-all">
                        <summary class="flex items-center justify-between cursor-pointer list-none text-slate-900 font-bold text-base sm:text-lg">
                            <span>How does the AI Agent Memory and Tool Calling operate?</span>
                            <span class="w-8 h-8 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-500 group-open:rotate-180 transition-transform shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </span>
                        </summary>
                        <div class="mt-4 text-sm text-slate-600 leading-relaxed border-t border-slate-200 pt-4">
                            The gateway implements an autonomous LLM runtime governed by a strict token budget. It combines a dynamic 4-turn sliding conversation window with a contact-scoped factual memory notebook, auto-expiring temporary contexts, few-shot golden training examples, and negative guardrail constraints. When users chat, the agent can autonomously query external APIs, run SQL, fetch orders, check weather, or update memory facts using safe sandboxed tools.
                        </div>
                    </details>

                </div>

            </div>
        </section>

        <!-- HIGH-CONVERTING FINAL CTA (WhatsApp Green/Teal Gradient) -->
        <section class="py-24 bg-gradient-to-br from-wa-darkTeal via-wa-teal to-emerald-700 text-white relative overflow-hidden">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10 reveal">
                
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-white/10 text-emerald-100 border border-white/20 mb-6 backdrop-blur-sm">
                    <span class="w-2.5 h-2.5 rounded-full bg-wa-green animate-pulse"></span>
                    <span>Self-Hosted &bull; Unlimited Throughput &bull; Open Source</span>
                </div>

                <h2 class="text-3xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-tight">
                    Host Your Private WhatsApp <br>
                    API Gateway Server
                </h2>
                
                <p class="mt-4 text-base sm:text-lg text-emerald-50 max-w-2xl mx-auto leading-relaxed">
                    Deploy on a VPS or container. Retain full access to local session keys, database records, and direct socket logs with zero per-message charges.
                </p>

                <!-- Quick Start Command Pill -->
                <div class="mt-8 max-w-xl mx-auto">
                    <div class="p-3.5 rounded-2xl bg-black/25 border border-white/20 font-mono text-xs flex items-center justify-between gap-3 text-emerald-100 shadow-xl backdrop-blur-md">
                        <span class="text-wa-green font-bold select-none">$</span>
                        <span class="truncate">git clone https://github.com/navneetyadav-code/whatsapp-api.git</span>
                        <button type="button" id="final-copy-btn" class="text-emerald-200 hover:text-white p-1 transition-colors" title="Copy command">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        </button>
                    </div>
                </div>

                <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                    <a href="<?= e(base_url('register')) ?>" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl font-bold text-slate-900 bg-wa-green hover:bg-emerald-300 shadow-wa-button transition-all text-base">
                        <span>Get Started Free</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                    <a href="<?= e(base_url('docs')) ?>" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-4 rounded-xl font-semibold text-white bg-black/20 hover:bg-black/30 border border-white/25 transition-all text-base backdrop-blur-sm">
                        <span>API Documentation</span>
                    </a>
                </div>
            </div>
        </section>

    </main>

    <!-- COMPREHENSIVE FOOTER (WhatsApp Light Theme) -->
    <footer class="bg-white border-t border-emerald-100 py-16 text-slate-500 text-xs sm:text-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10 pb-12 border-b border-slate-200">
                
                <!-- Col 1: Brand Info -->
                <div class="md:col-span-1 space-y-4">
                    <div class="flex items-center gap-3">
                        <img src="<?= e(base_url('assets/favicon.png')) ?>" alt="WhatsGateway Logo" class="w-8 h-8 rounded-xl object-cover border border-emerald-200 shadow-sm">
                        <span class="font-extrabold text-slate-900 text-base">WhatsGateway</span>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Open-source, self-hosted WhatsApp messaging gateway and automation engine for modern developers.
                    </p>
                    <div class="inline-flex items-center gap-2 text-[11px] text-wa-teal font-mono font-semibold">
                        <span class="w-2 h-2 rounded-full bg-wa-green"></span>
                        <span>MIT Licensed Open-Source Project</span>
                    </div>
                </div>

                <!-- Col 2: Navigation Links -->
                <div>
                    <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider mb-4 font-mono">Platform</h4>
                    <ul class="space-y-2.5 text-xs text-slate-600">
                        <li><a href="#features" class="hover:text-wa-teal transition-colors">Platform Features</a></li>
                        <li><a href="#architecture" class="hover:text-wa-teal transition-colors">Architecture Pipeline</a></li>
                        <li><a href="#code-playground" class="hover:text-wa-teal transition-colors">Interactive API Playground</a></li>
                        <li><a href="#webhooks" class="hover:text-wa-teal transition-colors">Real-time Webhook Stream</a></li>
                        <li><a href="#comparison" class="hover:text-wa-teal transition-colors">Cost & Architecture Comparison</a></li>
                        <li><a href="#faq" class="hover:text-wa-teal transition-colors">Developer Technical FAQ</a></li>
                    </ul>
                </div>

                <!-- Col 3: Resources & Compliance -->
                <div>
                    <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider mb-4 font-mono">Resources & Compliance</h4>
                    <ul class="space-y-2.5 text-xs text-slate-600">
                        <li><a href="<?= e(base_url('docs')) ?>" class="hover:text-wa-teal transition-colors">REST API Documentation</a></li>
                        <li><a href="<?= e(base_url('privacy')) ?>" class="hover:text-wa-teal transition-colors">Privacy Policy</a></li>
                        <li><a href="<?= e(base_url('terms')) ?>" class="hover:text-wa-teal transition-colors">Terms of Service</a></li>
                        <li><a href="#google-sync" class="hover:text-wa-teal transition-colors">Google Limited Use Disclosure</a></li>
                        <li><a href="mailto:info.assistgroupofficial@gmail.com" class="hover:text-wa-teal transition-colors">Developer Support Contact</a></li>
                    </ul>
                </div>

                <!-- Col 4: Community & GitHub -->
                <div>
                    <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider mb-4 font-mono">Community & Source</h4>
                    <p class="text-xs text-slate-600 mb-4 leading-relaxed">
                        Audit, fork, or contribute to the gateway codebase on GitHub. Stars and pull requests welcome.
                    </p>
                    <a href="https://github.com/navneetyadav-code/whatsapp-api" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold text-xs border border-slate-200 transition-all">
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

    <!-- INTERACTIVE SCRIPTS -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            // 1. Mobile Menu Drawer Toggle
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

            // 2. Interactive Dispatch Simulator in WhatsApp Phone Mockup
            var simBtn = document.getElementById('sim-send-btn');
            var simStatusLabel = document.getElementById('sim-status-label');
            var simBadge = document.getElementById('sim-badge');
            var simDynamicBubble = document.getElementById('sim-dynamic-bubble');
            var simTypingIndicator = document.getElementById('sim-typing-indicator');
            var simBackBtn = document.getElementById('sim-back-btn');

            function resetPhoneSimulation() {
                if (simBtn) {
                    simBtn.disabled = false;
                    simBtn.setAttribute('aria-busy', 'false');
                }
                if (simStatusLabel) simStatusLabel.textContent = 'online';
                if (simBadge) {
                    simBadge.textContent = '200 OK';
                    simBadge.className = 'text-[9px] font-mono font-bold text-wa-teal bg-emerald-50 px-1.5 py-0.5 rounded';
                }
                if (simTypingIndicator) simTypingIndicator.classList.add('hidden');
                if (simDynamicBubble) simDynamicBubble.classList.remove('opacity-0', 'translate-y-3');
            }

            if (simBackBtn) {
                simBackBtn.addEventListener('click', resetPhoneSimulation);
            }

            if (simBtn) {
                simBtn.addEventListener('click', function () {
                    simBtn.disabled = true;
                    simBtn.setAttribute('aria-busy', 'true');
                    simStatusLabel.textContent = 'typing...';
                    simBadge.textContent = 'Queuing...';
                    simBadge.className = 'text-[9px] font-mono font-bold text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded';
                    
                    if (simTypingIndicator) {
                        simTypingIndicator.classList.remove('hidden');
                    }
                    if (simDynamicBubble) {
                        simDynamicBubble.classList.add('opacity-0', 'translate-y-3');
                    }

                    setTimeout(function () {
                        simBadge.textContent = 'Composing...';
                        simBadge.className = 'text-[9px] font-mono font-bold text-sky-700 bg-sky-50 px-1.5 py-0.5 rounded';
                    }, 600);

                    setTimeout(function () {
                        if (simTypingIndicator) {
                            simTypingIndicator.classList.add('hidden');
                        }
                        if (simDynamicBubble) {
                            simDynamicBubble.classList.remove('opacity-0', 'translate-y-3');
                        }
                        simStatusLabel.textContent = 'online';
                        simBadge.textContent = 'Delivered ✓✓';
                        simBadge.className = 'text-[9px] font-mono font-bold text-wa-teal bg-emerald-50 px-1.5 py-0.5 rounded';
                        
                        setTimeout(function () {
                            simBtn.disabled = false;
                            simBtn.setAttribute('aria-busy', 'false');
                            simBadge.textContent = '200 OK';
                            simBadge.className = 'text-[9px] font-mono font-bold text-wa-teal bg-emerald-50 px-1.5 py-0.5 rounded';
                        }, 3000);
                    }, 1400);
                });
            }

            // 3. Code Snippet Language Tab Switching
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
                        t.classList.remove('bg-emerald-500/20', 'text-emerald-400', 'border', 'border-emerald-500/30', 'font-bold');
                        t.classList.add('text-slate-400');
                    });

                    tab.classList.remove('text-slate-400');
                    tab.classList.add('bg-emerald-500/20', 'text-emerald-400', 'border', 'border-emerald-500/30', 'font-bold');

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

            // 4. Interactive Simulation Types & API Testing Runner
            var sendApiUrl = '<?= e(public_url('api/send.php')) ?>';
            var metaApiUrl = '<?= e(public_url('api/meta-messages.php')) ?>';

            var simulationScenarios = {
                otp: {
                    id: 'otp',
                    title: 'Transactional 2FA OTP Alert',
                    badge: 'Type: 2FA OTP Token',
                    status: '200 OK',
                    statusClass: 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
                    logs: [
                        '[GATEWAY] POST /api/send.php (Simulation: 2FA OTP)',
                        '[AUTH] API Key validated (wa_live_YOUR_API_KEY)',
                        '[SOCKET] Baileys v6 connection verified (Socket ACK)',
                        '[QUEUE] Enqueued in MySQL message_queue (pending)'
                    ],
                    snippets: {
                        curl: '<pre><code><span class="text-slate-500"># Send transactional 2FA OTP alert</span>\ncurl -X POST "' + sendApiUrl + '" \\\n  -H "<span class="text-emerald-400">X-API-Key</span>: <span class="text-amber-300">wa_live_YOUR_API_KEY</span>" \\\n  -H "Content-Type: application/json" \\\n  -d \'{\n    "<span class="text-sky-300">to</span>": "<span class="text-slate-200">919876543210</span>",\n    "<span class="text-sky-300">message</span>": "<span class="text-slate-200">Your verification OTP is *749201*. Valid for 10 minutes.</span>",\n    "<span class="text-sky-300">humanize</span>": <span class="text-purple-400">true</span>\n  }\'</code></pre>',
                        node: '<pre><code><span class="text-slate-500">// Node.js dispatch 2FA OTP</span>\n<span class="text-purple-400">const</span> response = <span class="text-purple-400">await</span> fetch(\'' + sendApiUrl + '\', {\n  method: <span class="text-emerald-300">\'POST\'</span>,\n  headers: {\n    <span class="text-emerald-300">\'Authorization\'</span>: <span class="text-amber-300">\'Bearer wa_live_YOUR_API_KEY\'</span>,\n    <span class="text-emerald-300">\'Content-Type\'</span>: <span class="text-emerald-300">\'application/json\'</span>\n  },\n  body: JSON.stringify({\n    to: <span class="text-emerald-300">\'919876543210\'</span>,\n    message: <span class="text-emerald-300">\'Your verification OTP is *749201*. Valid for 10 minutes.\'</span>,\n    humanize: <span class="text-purple-400">true</span>\n  })\n});\n<span class="text-purple-400">const</span> data = <span class="text-purple-400">await</span> response.json();\nconsole.log(<span class="text-emerald-300">\'Queued message ID:\'</span>, data.message_id);</code></pre>',
                        python: '<pre><code><span class="text-slate-500"># Python 3 dispatch 2FA OTP</span>\n<span class="text-purple-400">import</span> requests\n\nurl = <span class="text-emerald-300">"' + sendApiUrl + '"</span>\nheaders = {\n    <span class="text-emerald-300">"X-API-Key"</span>: <span class="text-amber-300">"wa_live_YOUR_API_KEY"</span>,\n    <span class="text-emerald-300">"Content-Type"</span>: <span class="text-emerald-300">"application/json"</span>\n}\npayload = {\n    <span class="text-emerald-300">"to"</span>: <span class="text-emerald-300">"919876543210"</span>,\n    <span class="text-emerald-300">"message"</span>: <span class="text-emerald-300">"Your verification OTP is *749201*. Valid for 10 minutes."</span>,\n    <span class="text-emerald-300">"humanize"</span>: <span class="text-purple-400">True</span>\n}\nres = requests.post(url, json=payload, headers=headers)\nprint(res.json())</code></pre>',
                        php: '<pre><code><span class="text-slate-500">// PHP 8.2+ dispatch 2FA OTP</span>\n$ch = curl_init(\'' + sendApiUrl + '\');\ncurl_setopt_array($ch, [\n    CURLOPT_POST => <span class="text-purple-400">true</span>,\n    CURLOPT_HTTPHEADER => [\n        <span class="text-emerald-300">\'X-API-Key: \'</span> . $apiKey,\n        <span class="text-emerald-300">\'Content-Type: application/json\'</span>\n    ],\n    CURLOPT_POSTFIELDS => json_encode([\n        <span class="text-emerald-300">\'to\'</span> => <span class="text-emerald-300">\'919876543210\'</span>,\n        <span class="text-emerald-300">\'message\'</span> => <span class="text-emerald-300">\'Your verification OTP is *749201*. Valid for 10 minutes.\'</span>,\n        <span class="text-emerald-300">\'humanize\'</span> => <span class="text-purple-400">true</span>\n    ]),\n    CURLOPT_RETURNTRANSFER => <span class="text-purple-400">true</span>\n]);\n$res = json_decode(curl_exec($ch), <span class="text-purple-400">true</span>);\ncurl_close($ch);</code></pre>',
                        meta: '<pre><code><span class="text-slate-500"># Drop-in Meta WhatsApp Cloud API v21.0 Schema</span>\ncurl -X POST "' + metaApiUrl + '" \\\n  -H "<span class="text-emerald-400">Authorization</span>: <span class="text-amber-300">Bearer wa_live_YOUR_API_KEY</span>" \\\n  -H "Content-Type: application/json" \\\n  -d \'{\n    "<span class="text-sky-300">messaging_product</span>": "<span class="text-slate-200">whatsapp</span>",\n    "<span class="text-sky-300">recipient_type</span>": "<span class="text-slate-200">individual</span>",\n    "<span class="text-sky-300">to</span>": "<span class="text-slate-200">919876543210</span>",\n    "<span class="text-sky-300">type</span>": "<span class="text-slate-200">text</span>",\n    "<span class="text-sky-300">text</span>": {\n      "<span class="text-sky-300">body</span>": "<span class="text-slate-200">Your verification OTP is *749201*. Valid for 10 minutes.</span>"\n    }\n  }\'</code></pre>'
                    },
                    responseJson: {
                        success: true,
                        message_id: "wg_msg_8f10b7e2a9",
                        type: "otp_verification",
                        status: "queued",
                        recipient: "+919876543210",
                        queued_at: new Date().toISOString(),
                        retry_count: 0,
                        humanized_delay_ms: 1250,
                        gateway: {
                            engine: "Baileys_v6",
                            socket_state: "connected"
                        }
                    }
                },
                media: {
                    id: 'media',
                    title: 'PDF Invoice Attachment',
                    badge: 'Type: PDF Document Dispatch',
                    status: '200 OK',
                    statusClass: 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
                    logs: [
                        '[GATEWAY] POST /api/send.php (Simulation: PDF Invoice)',
                        '[MEDIA] Validating MIME: application/pdf (245 KB)',
                        '[STORAGE] Attachment signed with local token',
                        '[SOCKET] Baileys document packet dispatch prepared'
                    ],
                    snippets: {
                        curl: '<pre><code><span class="text-slate-500"># Send PDF Invoice Document attachment</span>\ncurl -X POST "' + sendApiUrl + '" \\\n  -H "<span class="text-emerald-400">X-API-Key</span>: <span class="text-amber-300">wa_live_YOUR_API_KEY</span>" \\\n  -H "Content-Type: application/json" \\\n  -d \'{\n    "<span class="text-sky-300">to</span>": "<span class="text-slate-200">919876543210</span>",\n    "<span class="text-sky-300">type</span>": "<span class="text-slate-200">document</span>",\n    "<span class="text-sky-300">media_url</span>": "<span class="text-emerald-300">https://yourdomain.com/invoices/INV-2026-88.pdf</span>",\n    "<span class="text-sky-300">caption</span>": "<span class="text-slate-200">Invoice #INV-2026-88 is ready for review.</span>"\n  }\'</code></pre>',
                        node: '<pre><code><span class="text-slate-500">// Node.js dispatch PDF Invoice</span>\n<span class="text-purple-400">const</span> response = <span class="text-purple-400">await</span> fetch(\'' + sendApiUrl + '\', {\n  method: <span class="text-emerald-300">\'POST\'</span>,\n  headers: {\n    <span class="text-emerald-300">\'Authorization\'</span>: <span class="text-amber-300">\'Bearer wa_live_YOUR_API_KEY\'</span>,\n    <span class="text-emerald-300">\'Content-Type\'</span>: <span class="text-emerald-300">\'application/json\'</span>\n  },\n  body: JSON.stringify({\n    to: <span class="text-emerald-300">\'919876543210\'</span>,\n    type: <span class="text-emerald-300">\'document\'</span>,\n    media_url: <span class="text-emerald-300">\'https://yourdomain.com/invoices/INV-2026-88.pdf\'</span>,\n    caption: <span class="text-emerald-300">\'Invoice #INV-2026-88 is ready for review.\'</span>\n  })\n});\n<span class="text-purple-400">const</span> data = <span class="text-purple-400">await</span> response.json();\nconsole.log(<span class="text-emerald-300">\'Queued document ID:\'</span>, data.message_id);</code></pre>',
                        python: '<pre><code><span class="text-slate-500"># Python 3 dispatch PDF Invoice</span>\n<span class="text-purple-400">import</span> requests\n\nurl = <span class="text-emerald-300">"' + sendApiUrl + '"</span>\nheaders = {\n    <span class="text-emerald-300">"X-API-Key"</span>: <span class="text-amber-300">"wa_live_YOUR_API_KEY"</span>,\n    <span class="text-emerald-300">"Content-Type"</span>: <span class="text-emerald-300">"application/json"</span>\n}\npayload = {\n    <span class="text-emerald-300">"to"</span>: <span class="text-emerald-300">"919876543210"</span>,\n    <span class="text-emerald-300">"type"</span>: <span class="text-emerald-300">"document"</span>,\n    <span class="text-emerald-300">"media_url"</span>: <span class="text-emerald-300">"https://yourdomain.com/invoices/INV-2026-88.pdf"</span>,\n    <span class="text-emerald-300">"caption"</span>: <span class="text-emerald-300">"Invoice #INV-2026-88 is ready for review."</span>\n}\nres = requests.post(url, json=payload, headers=headers)\nprint(res.json())</code></pre>',
                        php: '<pre><code><span class="text-slate-500">// PHP 8.2+ dispatch PDF Invoice</span>\n$ch = curl_init(\'' + sendApiUrl + '\');\ncurl_setopt_array($ch, [\n    CURLOPT_POST => <span class="text-purple-400">true</span>,\n    CURLOPT_HTTPHEADER => [\n        <span class="text-emerald-300">\'X-API-Key: \'</span> . $apiKey,\n        <span class="text-emerald-300">\'Content-Type: application/json\'</span>\n    ],\n    CURLOPT_POSTFIELDS => json_encode([\n        <span class="text-emerald-300">\'to\'</span> => <span class="text-emerald-300">\'919876543210\'</span>,\n        <span class="text-emerald-300">\'type\'</span> => <span class="text-emerald-300">\'document\'</span>,\n        <span class="text-emerald-300">\'media_url\'</span> => <span class="text-emerald-300">\'https://yourdomain.com/invoices/INV-2026-88.pdf\'</span>,\n        <span class="text-emerald-300">\'caption\'</span> => <span class="text-emerald-300">\'Invoice #INV-2026-88 is ready for review.\'</span>\n    ]),\n    CURLOPT_RETURNTRANSFER => <span class="text-purple-400">true</span>\n]);\n$res = json_decode(curl_exec($ch), <span class="text-purple-400">true</span>);\ncurl_close($ch);</code></pre>',
                        meta: '<pre><code><span class="text-slate-500"># Drop-in Meta Cloud schema for document</span>\ncurl -X POST "' + metaApiUrl + '" \\\n  -H "<span class="text-emerald-400">Authorization</span>: <span class="text-amber-300">Bearer wa_live_YOUR_API_KEY</span>" \\\n  -H "Content-Type: application/json" \\\n  -d \'{\n    "<span class="text-sky-300">messaging_product</span>": "<span class="text-slate-200">whatsapp</span>",\n    "<span class="text-sky-300">recipient_type</span>": "<span class="text-slate-200">individual</span>",\n    "<span class="text-sky-300">to</span>": "<span class="text-slate-200">919876543210</span>",\n    "<span class="text-sky-300">type</span>": "<span class="text-slate-200">document</span>",\n    "<span class="text-sky-300">document</span>": {\n      "<span class="text-sky-300">link</span>": "<span class="text-emerald-300">https://yourdomain.com/invoices/INV-2026-88.pdf</span>",\n      "<span class="text-sky-300">filename</span>": "<span class="text-slate-200">Invoice-INV-2026-88.pdf</span>"\n    }\n  }\'</code></pre>'
                    },
                    responseJson: {
                        success: true,
                        message_id: "wg_msg_4c78a109fe",
                        type: "document",
                        media_type: "application/pdf",
                        media_filename: "Invoice-INV-2026-88.pdf",
                        status: "queued",
                        recipient: "+919876543210",
                        queued_at: new Date().toISOString(),
                        retry_count: 0,
                        gateway: {
                            engine: "Baileys_v6",
                            socket_state: "connected"
                        }
                    }
                },
                meta: {
                    id: 'meta',
                    title: 'Meta WhatsApp Cloud API v21.0 Schema',
                    badge: 'Type: Meta Graph Template Protocol',
                    status: '200 OK',
                    statusClass: 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
                    logs: [
                        '[GATEWAY] POST /api/meta-messages.php (v21.0 schema parser)',
                        '[NORMALIZE] Converted Graph template to internal dispatch job',
                        '[AUTH] Authorization: Bearer verified against DB token hash',
                        '[RESULT] Graph compatible 200 OK wamid returned'
                    ],
                    snippets: {
                        curl: '<pre><code><span class="text-slate-500"># Meta WhatsApp Cloud API v21.0 Template Payload</span>\ncurl -X POST "' + metaApiUrl + '" \\\n  -H "<span class="text-emerald-400">Authorization</span>: <span class="text-amber-300">Bearer wa_live_YOUR_API_KEY</span>" \\\n  -H "Content-Type: application/json" \\\n  -d \'{\n    "<span class="text-sky-300">messaging_product</span>": "<span class="text-slate-200">whatsapp</span>",\n    "<span class="text-sky-300">recipient_type</span>": "<span class="text-slate-200">individual</span>",\n    "<span class="text-sky-300">to</span>": "<span class="text-slate-200">919876543210</span>",\n    "<span class="text-sky-300">type</span>": "<span class="text-slate-200">template</span>",\n    "<span class="text-sky-300">template</span>": {\n      "<span class="text-sky-300">name</span>": "<span class="text-slate-200">shipping_update</span>",\n      "<span class="text-sky-300">language</span>": { "<span class="text-sky-300">code</span>": "<span class="text-slate-200">en_US</span>" },\n      "<span class="text-sky-300">components</span>": [\n        {\n          "<span class="text-sky-300">type</span>": "<span class="text-slate-200">body</span>",\n          "<span class="text-sky-300">parameters</span>": [{ "<span class="text-sky-300">type</span>": "<span class="text-slate-200">text</span>", "<span class="text-sky-300">text</span>": "<span class="text-slate-200">TRK92810</span>" }]\n        }\n      ]\n    }\n  }\'</code></pre>',
                        node: '<pre><code><span class="text-slate-500">// Node.js Meta Cloud v21.0 compatible dispatch</span>\n<span class="text-purple-400">const</span> res = <span class="text-purple-400">await</span> fetch(\'' + metaApiUrl + '\', {\n  method: <span class="text-emerald-300">\'POST\'</span>,\n  headers: {\n    <span class="text-emerald-300">\'Authorization\'</span>: <span class="text-amber-300">\'Bearer wa_live_YOUR_API_KEY\'</span>,\n    <span class="text-emerald-300">\'Content-Type\'</span>: <span class="text-emerald-300">\'application/json\'</span>\n  },\n  body: JSON.stringify({\n    messaging_product: <span class="text-emerald-300">\'whatsapp\'</span>,\n    recipient_type: <span class="text-emerald-300">\'individual\'</span>,\n    to: <span class="text-emerald-300">\'919876543210\'</span>,\n    type: <span class="text-emerald-300">\'template\'</span>,\n    template: {\n      name: <span class="text-emerald-300">\'shipping_update\'</span>,\n      language: { code: <span class="text-emerald-300">\'en_US\'</span> },\n      components: [{ type: <span class="text-emerald-300">\'body\'</span>, parameters: [{ type: <span class="text-emerald-300">\'text\'</span>, text: <span class="text-emerald-300">\'TRK92810\'</span> }] }]\n    }\n  })\n});\n<span class="text-purple-400">const</span> data = <span class="text-purple-400">await</span> res.json();\nconsole.log(<span class="text-emerald-300">\'Meta wamid:\'</span>, data.messages[0].id);</code></pre>',
                        python: '<pre><code><span class="text-slate-500"># Python Meta Cloud API drop-in</span>\n<span class="text-purple-400">import</span> requests\n\nurl = <span class="text-emerald-300">"' + metaApiUrl + '"</span>\nheaders = {\n    <span class="text-emerald-300">"Authorization"</span>: <span class="text-amber-300">"Bearer wa_live_YOUR_API_KEY"</span>,\n    <span class="text-emerald-300">"Content-Type"</span>: <span class="text-emerald-300">"application/json"</span>\n}\npayload = {\n    <span class="text-emerald-300">"messaging_product"</span>: <span class="text-emerald-300">"whatsapp"</span>,\n    <span class="text-emerald-300">"recipient_type"</span>: <span class="text-emerald-300">"individual"</span>,\n    <span class="text-emerald-300">"to"</span>: <span class="text-emerald-300">"919876543210"</span>,\n    <span class="text-emerald-300">"type"</span>: <span class="text-emerald-300">"template"</span>,\n    <span class="text-emerald-300">"template"</span>: {\n        <span class="text-emerald-300">"name"</span>: <span class="text-emerald-300">"shipping_update"</span>,\n        <span class="text-emerald-300">"language"</span>: {<span class="text-emerald-300">"code"</span>: <span class="text-emerald-300">"en_US"</span>},\n        <span class="text-emerald-300">"components"</span>: [{<span class="text-emerald-300">"type"</span>: <span class="text-emerald-300">"body"</span>, <span class="text-emerald-300">"parameters"</span>: [{<span class="text-emerald-300">"type"</span>: <span class="text-emerald-300">"text"</span>, <span class="text-emerald-300">"text"</span>: <span class="text-emerald-300">"TRK92810"</span>}]}]\n    }\n}\nres = requests.post(url, json=payload, headers=headers)\nprint(res.json())</code></pre>',
                        php: '<pre><code><span class="text-slate-500">// PHP Meta Cloud API drop-in</span>\n$ch = curl_init(\'' + metaApiUrl + '\');\ncurl_setopt_array($ch, [\n    CURLOPT_POST => <span class="text-purple-400">true</span>,\n    CURLOPT_HTTPHEADER => [\n        <span class="text-emerald-300">\'Authorization: Bearer \'</span> . $apiKey,\n        <span class="text-emerald-300">\'Content-Type: application/json\'</span>\n    ],\n    CURLOPT_POSTFIELDS => json_encode([\n        <span class="text-emerald-300">\'messaging_product\'</span> => <span class="text-emerald-300">\'whatsapp\'</span>,\n        <span class="text-emerald-300">\'recipient_type\'</span> => <span class="text-emerald-300">\'individual\'</span>,\n        <span class="text-emerald-300">\'to\'</span> => <span class="text-emerald-300">\'919876543210\'</span>,\n        <span class="text-emerald-300">\'type\'</span> => <span class="text-emerald-300">\'template\'</span>,\n        <span class="text-emerald-300">\'template\'</span> => [\n            <span class="text-emerald-300">\'name\'</span> => <span class="text-emerald-300">\'shipping_update\'</span>,\n            <span class="text-emerald-300">\'language\'</span> => [<span class="text-emerald-300">\'code\'</span> => <span class="text-emerald-300">\'en_US\'</span>],\n            <span class="text-emerald-300">\'components\'</span> => [[<span class="text-emerald-300">\'type\'</span> => <span class="text-emerald-300">\'body\'</span>, <span class="text-emerald-300">\'parameters\'</span> => [[<span class="text-emerald-300">\'type\'</span> => <span class="text-emerald-300">\'text\'</span>, <span class="text-emerald-300">\'text\'</span> => <span class="text-emerald-300">\'TRK92810\'</span>]]]]\n        ]\n    ]),\n    CURLOPT_RETURNTRANSFER => <span class="text-purple-400">true</span>\n]);\n$res = json_decode(curl_exec($ch), <span class="text-purple-400">true</span>);\ncurl_close($ch);</code></pre>',
                        meta: '<pre><code><span class="text-slate-500"># Direct Meta Cloud API Schema (100% Drop-in)</span>\ncurl -X POST "' + metaApiUrl + '" \\\n  -H "<span class="text-emerald-400">Authorization</span>: <span class="text-amber-300">Bearer wa_live_YOUR_API_KEY</span>" \\\n  -H "Content-Type: application/json" \\\n  -d \'{\n    "<span class="text-sky-300">messaging_product</span>": "<span class="text-slate-200">whatsapp</span>",\n    "<span class="text-sky-300">recipient_type</span>": "<span class="text-slate-200">individual</span>",\n    "<span class="text-sky-300">to</span>": "<span class="text-slate-200">919876543210</span>",\n    "<span class="text-sky-300">type</span>": "<span class="text-slate-200">template</span>",\n    "<span class="text-sky-300">template</span>": { "<span class="text-sky-300">name</span>": "<span class="text-slate-200">shipping_update</span>", "<span class="text-sky-300">language</span>": { "<span class="text-sky-300">code</span>": "<span class="text-slate-200">en_US</span>" } }\n  }\'</code></pre>'
                    },
                    responseJson: {
                        messaging_product: "whatsapp",
                        contacts: [
                            {
                                input: "919876543210",
                                wa_id: "919876543210"
                            }
                        ],
                        messages: [
                            {
                                id: "wamid.HBgMOTE5ODc2NTQzMjEwFQIAERgSQjgwMEU2OTRBMzc1RTcxQjM2AA=="
                            }
                        ]
                    }
                },
                webhook: {
                    id: 'webhook',
                    title: 'HMAC-SHA256 Signed Inbound Callback',
                    badge: 'Type: Inbound Webhook Event Callback',
                    status: '200 OK',
                    statusClass: 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
                    logs: [
                        '[DISPATCH] Inbound message detected on Baileys socket',
                        '[CRYPTO] Computing HMAC-SHA256 signature with shared secret',
                        '[HEADER] Injected X-Hub-Signature-256 header',
                        '[POST] HTTPS callback acknowledged by client webhook (200 OK)'
                    ],
                    snippets: {
                        curl: '<pre><code><span class="text-slate-500"># Gateway dispatching signed webhook to your HTTPS endpoint</span>\ncurl -X POST "https://your-crm.com/webhooks/whatsapp" \\\n  -H "<span class="text-emerald-400">Content-Type</span>: application/json" \\\n  -H "<span class="text-emerald-400">X-Hub-Signature-256</span>: <span class="text-amber-300">sha256=a8f59d28c704f034188...</span>" \\\n  -d \'{\n    "<span class="text-sky-300">event</span>": "<span class="text-emerald-300">messages.upsert</span>",\n    "<span class="text-sky-300">from</span>": "<span class="text-slate-200">919876543210</span>",\n    "<span class="text-sky-300">message_id</span>": "<span class="text-slate-200">3EB0E56A8F102B9</span>",\n    "<span class="text-sky-300">timestamp</span>": <span class="text-amber-300">1729000000</span>,\n    "<span class="text-sky-300">text</span>": "<span class="text-slate-200">Yes, please confirm my booking</span>"\n  }\'</code></pre>',
                        node: '<pre><code><span class="text-slate-500">// Node.js Express webhook receiver verifying HMAC</span>\n<span class="text-purple-400">import</span> crypto <span class="text-purple-400">from</span> <span class="text-emerald-300">\'crypto\'</span>;\n\napp.post(<span class="text-emerald-300">\'/webhooks/whatsapp\'</span>, (req, res) => {\n  <span class="text-purple-400">const</span> sig = req.headers[<span class="text-emerald-300">\'x-hub-signature-256\'</span>];\n  <span class="text-purple-400">const</span> expected = <span class="text-emerald-300">\'sha256=\'</span> + crypto.createHmac(<span class="text-emerald-300">\'sha256\'</span>, SECRET).update(req.rawBody).digest(<span class="text-emerald-300">\'hex\'</span>);\n  \n  <span class="text-purple-400">if</span> (sig !== expected) <span class="text-purple-400">return</span> res.status(401).send(<span class="text-emerald-300">\'Invalid signature\'</span>);\n  console.log(<span class="text-emerald-300">\'Valid inbound message:\'</span>, req.body.text);\n  res.sendStatus(200);\n});</code></pre>',
                        python: '<pre><code><span class="text-slate-500"># Python Flask Webhook receiver with signature check</span>\n<span class="text-purple-400">import</span> hmac, hashlib\n<span class="text-purple-400">from</span> flask <span class="text-purple-400">import</span> request, Flask\n\n@app.route(<span class="text-emerald-300">\'/webhooks/whatsapp\'</span>, methods=[<span class="text-emerald-300">\'POST\'</span>])\ndef handle_webhook():\n    sig = request.headers.get(<span class="text-emerald-300">\'X-Hub-Signature-256\'</span>, <span class="text-emerald-300">\'\'</span>)\n    computed = <span class="text-emerald-300">\'sha256=\'</span> + hmac.new(SECRET.encode(), request.data, hashlib.sha256).hexdigest()\n    <span class="text-purple-400">if</span> not hmac.compare_digest(sig, computed):\n        <span class="text-purple-400">return</span> <span class="text-emerald-300">\'Unauthorized\'</span>, 401\n    print(<span class="text-emerald-300">\'Inbound payload verified:\'</span>, request.json.get(<span class="text-emerald-300">\'text\'</span>))\n    <span class="text-purple-400">return</span> <span class="text-emerald-300">\'OK\'</span>, 200</code></pre>',
                        php: '<pre><code><span class="text-slate-500">// PHP 8.2+ HMAC Signed Webhook Receiver</span>\n$rawPayload = file_get_contents(<span class="text-emerald-300">\'php://input\'</span>);\n$headerSig = $_SERVER[<span class="text-emerald-300">\'HTTP_X_HUB_SIGNATURE_256\'</span>] ?? <span class="text-emerald-300">\'\'</span>;\n$expected = <span class="text-emerald-300">\'sha256=\'</span> . hash_hmac(<span class="text-emerald-300">\'sha256\'</span>, $rawPayload, $webhookSecret);\n\n<span class="text-purple-400">if</span> (!hash_equals($expected, $headerSig)) {\n    http_response_code(401);\n    <span class="text-purple-400">exit</span>(<span class="text-emerald-300">\'Invalid signature\'</span>);\n}\n\n$event = json_decode($rawPayload, <span class="text-purple-400">true</span>);\nhttp_response_code(200);\necho json_encode([<span class="text-emerald-300">\'received\'</span> => <span class="text-purple-400">true</span>]);</code></pre>',
                        meta: '<pre><code><span class="text-slate-500"># Standard Webhook Inbound Event Format</span>\ncurl -X POST "https://your-crm.com/webhooks/whatsapp" \\\n  -H "<span class="text-emerald-400">X-Hub-Signature-256</span>: <span class="text-amber-300">sha256=a8f59d28c...</span>" \\\n  -H "Content-Type: application/json" \\\n  -d \'{\n    "<span class="text-sky-300">event</span>": "<span class="text-slate-200">messages.upsert</span>",\n    "<span class="text-sky-300">from</span>": "<span class="text-slate-200">919876543210</span>",\n    "<span class="text-sky-300">text</span>": "<span class="text-slate-200">Yes, please confirm my booking</span>"\n  }\'</code></pre>'
                    },
                    responseJson: {
                        webhook_received: true,
                        event_type: "messages.upsert",
                        signature_verified: true,
                        algorithm: "HMAC-SHA256",
                        inbound_payload: {
                            from: "919876543210",
                            message_id: "3EB0E56A8F102B9",
                            content: "Yes, please confirm my booking"
                        },
                        status: "acknowledged"
                    }
                }
            };

            var currentSimType = 'otp';

            function syntaxHighlightJson(jsonObj) {
                var json = JSON.stringify(jsonObj, null, 2);
                json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
                    var cls = 'text-emerald-300';
                    if (/^"/.test(match)) {
                        if (/:$/.test(match)) {
                            cls = 'text-sky-300';
                        }
                    } else if (/true|false/.test(match)) {
                        cls = 'text-purple-400';
                    } else if (/null/.test(match)) {
                        cls = 'text-slate-400';
                    } else {
                        cls = 'text-amber-300';
                    }
                    return '<span class="' + cls + '">' + match + '</span>';
                });
            }

            function renderSimulationScenario(simId, isRunEffect) {
                var scenario = simulationScenarios[simId] || simulationScenarios.otp;
                currentSimType = scenario.id;

                var simTypeStatus = document.getElementById('sim-type-status');
                if (simTypeStatus) {
                    simTypeStatus.innerHTML = 'Active Scenario: <strong class="text-wa-darkTeal font-bold">' + scenario.title + '</strong>';
                }
                var responseScenarioTag = document.getElementById('response-scenario-tag');
                if (responseScenarioTag) {
                    responseScenarioTag.textContent = scenario.badge;
                }

                Object.keys(scenario.snippets).forEach(function (langKey) {
                    var panel = panels[langKey];
                    if (panel) {
                        panel.innerHTML = scenario.snippets[langKey];
                    }
                });

                var responseCodeEl = document.getElementById('playground-response-code');
                var formattedJsonHtml = syntaxHighlightJson(scenario.responseJson);

                if (!isRunEffect && responseCodeEl) {
                    responseCodeEl.innerHTML = '<code class="text-slate-300">' + formattedJsonHtml + '</code>';
                    if (responseStatusPill) {
                        responseStatusPill.textContent = scenario.status;
                        responseStatusPill.className = scenario.statusClass + ' px-2 py-0.5 rounded font-bold';
                    }
                    var terminalLogs = document.getElementById('sim-terminal-logs');
                    if (terminalLogs) {
                        terminalLogs.classList.add('hidden');
                        terminalLogs.innerHTML = '';
                    }
                }
            }

            // Hook up Simulation Type selector buttons
            var simTypeBtns = document.querySelectorAll('.sim-type-btn');
            simTypeBtns.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var sim = btn.getAttribute('data-sim');
                    simTypeBtns.forEach(function (b) {
                        b.classList.remove('active', 'bg-wa-teal', 'text-white', 'shadow-xs', 'font-bold');
                        b.classList.add('text-slate-700', 'font-medium');
                    });
                    btn.classList.add('active', 'bg-wa-teal', 'text-white', 'shadow-xs', 'font-bold');
                    btn.classList.remove('text-slate-700', 'font-medium');

                    renderSimulationScenario(sim, false);
                });
            });

            // Simulation execution with real-time terminal logs
            var playgroundRunBtn = document.getElementById('playground-run-btn');
            var responseStatusPill = document.getElementById('response-status-pill');
            var responseTimePill = document.getElementById('response-time-pill');

            // Initialize default scenario code after all response elements are available.
            renderSimulationScenario('otp', false);

            if (playgroundRunBtn) {
                playgroundRunBtn.addEventListener('click', function () {
                    var scenario = simulationScenarios[currentSimType] || simulationScenarios.otp;
                    playgroundRunBtn.disabled = true;
                    playgroundRunBtn.setAttribute('aria-busy', 'true');
                    var origHtml = playgroundRunBtn.innerHTML;
                    playgroundRunBtn.innerHTML = '<span class="w-3.5 h-3.5 rounded-full border-2 border-slate-900 border-t-transparent animate-spin inline-block mr-1"></span><span>Simulating dispatch...</span>';

                    if (responseStatusPill) {
                        responseStatusPill.textContent = 'Queuing...';
                        responseStatusPill.className = 'bg-amber-500/20 text-amber-300 border border-amber-500/30 px-2 py-0.5 rounded font-bold';
                    }

                    var terminalLogs = document.getElementById('sim-terminal-logs');
                    var responseCodeEl = document.getElementById('playground-response-code');
                    
                    if (terminalLogs) {
                        terminalLogs.classList.remove('hidden');
                        terminalLogs.innerHTML = '';
                    }

                    var logs = scenario.logs || [];
                    logs.forEach(function (logText, idx) {
                        setTimeout(function () {
                            if (terminalLogs) {
                                var logLine = document.createElement('div');
                                logLine.className = 'flex items-center gap-1.5 animate-event-slide text-[11px] font-mono';
                                logLine.innerHTML = '<span class="text-wa-green font-bold">&rsaquo;</span> <span>' + logText + '</span>';
                                terminalLogs.appendChild(logLine);
                            }
                        }, idx * 160);
                    });

                    var totalLogTime = logs.length * 160 + 100;
                    setTimeout(function () {
                        var randMs = Math.floor(Math.random() * 8) + 12;
                        if (responseTimePill) {
                            responseTimePill.textContent = randMs + 'ms';
                        }
                        if (responseStatusPill) {
                            responseStatusPill.textContent = scenario.status;
                            responseStatusPill.className = scenario.statusClass + ' px-2 py-0.5 rounded font-bold';
                        }

                        if (responseCodeEl) {
                            responseCodeEl.innerHTML = '<code class="text-slate-300">' + syntaxHighlightJson(scenario.responseJson) + '</code>';
                        }

                        playgroundRunBtn.innerHTML = '<span>&check; Completed (200 OK)</span>';

                        setTimeout(function () {
                            playgroundRunBtn.disabled = false;
                            playgroundRunBtn.setAttribute('aria-busy', 'false');
                            playgroundRunBtn.innerHTML = origHtml;
                        }, 2200);
                    }, totalLogTime);
                });
            }

            // 5. 1-Click Clipboard Utilities
            function setupCopyButton(btnId, textExtractor) {
                var btn = document.getElementById(btnId);
                if (!btn) return;
                btn.addEventListener('click', function () {
                    var textToCopy = typeof textExtractor === 'function' ? textExtractor() : textExtractor;
                    if (!textToCopy) return;

                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(textToCopy).then(showSuccess).catch(function () {
                            fallbackCopy(textToCopy, showSuccess);
                        });
                    } else {
                        fallbackCopy(textToCopy, showSuccess);
                    }

                    function showSuccess() {
                        var originalHtml = btn.innerHTML;
                        btn.classList.add('text-wa-green');
                        setTimeout(function () {
                            btn.innerHTML = originalHtml;
                            btn.classList.remove('text-wa-green');
                        }, 2000);
                    }
                });
            }

            setupCopyButton('copy-code-btn', function () {
                var activePanel = document.querySelector('.code-snippet-panel:not(.hidden) pre code');
                return activePanel ? (activePanel.innerText || activePanel.textContent || '').trim() : '';
            });

            setupCopyButton('hero-copy-repo-btn', 'git clone https://github.com/navneetyadav-code/whatsapp-api.git');
            setupCopyButton('final-copy-btn', 'git clone https://github.com/navneetyadav-code/whatsapp-api.git');

            function fallbackCopy(text, cb) {
                var textArea = document.createElement('textarea');
                textArea.value = text;
                textArea.style.position = 'fixed';
                textArea.style.left = '-9999px';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                try {
                    document.execCommand('copy');
                    if (cb) cb();
                } catch (e) {
                    console.error('Fallback copy error', e);
                }
                document.body.removeChild(textArea);
            }

            // 6. Scroll Reveal Observer
            if ('IntersectionObserver' in window) {
                var revealObserver = new IntersectionObserver(function (entries, observer) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('revealed');
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    threshold: 0.12,
                    rootMargin: '0px 0px -40px 0px'
                });
                document.querySelectorAll('.reveal').forEach(function (el) {
                    revealObserver.observe(el);
                });
            } else {
                document.querySelectorAll('.reveal').forEach(function (el) {
                    el.classList.add('revealed');
                });
            }

            // 7. Dynamic WhatsApp Message Clock
            function updateWaTime() {
                var now = new Date();
                var hours = now.getHours();
                var minutes = now.getMinutes();
                var ampm = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12;
                hours = hours ? hours : 12;
                var strTime = (hours < 10 ? '0' + hours : hours) + ':' + (minutes < 10 ? '0' + minutes : minutes) + ' ' + ampm;
                document.querySelectorAll('.dynamic-wa-time').forEach(function (el) {
                    el.textContent = strTime;
                });
            }
            updateWaTime();
            setInterval(updateWaTime, 30000);

            // 8. Live Socket Latency Fluctuation
            var latencyEl = document.getElementById('live-latency-val');
            if (latencyEl) {
                setInterval(function () {
                    var lat = Math.floor(Math.random() * 8) + 24;
                    latencyEl.textContent = lat + 'ms';
                }, 3500);
            }

            // 9. Simulator Preset Sample Chips
            var simChips = document.querySelectorAll('.sim-chip');
            var simInputLabel = document.getElementById('sim-input-label');
            var simBubbleText = document.getElementById('sim-bubble-text');
            simChips.forEach(function (chip) {
                chip.addEventListener('click', function () {
                    var msg = chip.getAttribute('data-msg');
                    var label = chip.textContent.trim();
                    if (simInputLabel) {
                        simInputLabel.textContent = 'POST /api/send.php \u2022 ' + label;
                    }
                    if (simBubbleText) {
                        simBubbleText.innerHTML = msg;
                    }
                    if (simBtn && !simBtn.disabled) {
                        simBtn.click();
                    }
                });
            });

            // 10. Live Webhook Stream Auto-Generator
            var webhookStreamContainer = document.getElementById('webhook-stream-container');
            var webhookToggle = document.getElementById('webhook-stream-toggle');
            var isWebhookStreaming = true;
            var webhookEventsPool = [
                { type: 'message.delivered', badgeClass: 'bg-emerald-100 text-wa-teal', desc: function() { return 'wg_msg_' + Math.random().toString(36).substring(2, 9) + ' \u2022 +9198' + Math.floor(10000000 + Math.random() * 90000000); }, detail: 'Status transition: PENDING \u2192 DELIVERED (Socket ACK)' },
                { type: 'message.read', badgeClass: 'bg-sky-100 text-sky-800', desc: function() { return 'wg_msg_' + Math.random().toString(36).substring(2, 9) + ' \u2022 Blue Tick (\u2713\u2713)'; }, detail: 'Recipient opened and read the verification message.' },
                { type: 'inbound.message', badgeClass: 'bg-purple-100 text-purple-800', desc: function() { return 'From: +9198' + Math.floor(10000000 + Math.random() * 90000000) + ' \u2022 Text'; }, detail: 'Payload: "Thank you, OTP received!"' },
                { type: 'presence.update', badgeClass: 'bg-amber-100 text-amber-800', desc: function() { return 'Presence: composing \u2022 1.8s'; }, detail: 'Humanized typing activity simulated on socket' }
            ];

            function streamNextWebhook() {
                if (!isWebhookStreaming || !webhookStreamContainer) return;
                var evt = webhookEventsPool[Math.floor(Math.random() * webhookEventsPool.length)];
                var card = document.createElement('div');
                card.className = 'p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex items-start justify-between gap-3 animate-event-slide';
                card.innerHTML = '<div>' +
                    '<div class="flex items-center gap-2 mb-1">' +
                    '<span class="px-2 py-0.5 rounded text-[10px] font-bold ' + evt.badgeClass + '">' + evt.type + '</span>' +
                    '<span class="text-slate-500 text-[11px]">' + evt.desc() + '</span>' +
                    '</div>' +
                    '<p class="text-slate-700 text-[11px]">' + evt.detail + '</p>' +
                    '</div>' +
                    '<span class="text-slate-400 text-[10px]">Just now</span>';

                webhookStreamContainer.insertBefore(card, webhookStreamContainer.firstChild);
                while (webhookStreamContainer.children.length > 4) {
                    webhookStreamContainer.removeChild(webhookStreamContainer.lastChild);
                }
            }

            setInterval(streamNextWebhook, 4500);

            if (webhookToggle) {
                webhookToggle.addEventListener('click', function () {
                    isWebhookStreaming = !isWebhookStreaming;
                    if (isWebhookStreaming) {
                        webhookToggle.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-wa-green"></span> <span id="webhook-toggle-label">Live &bull; Pause</span>';
                    } else {
                        webhookToggle.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> <span id="webhook-toggle-label">Paused &bull; Resume</span>';
                    }
                });
            }

        });
    </script>
</body>
</html>
<?php exit; ?>
