<?php

declare(strict_types=1);

$user = current_user();
$seoTitle = 'WhatsApp API Hub | Self-Hosted Developer Messaging Gateway & Automation';
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($seoTitle) ?></title>
    <meta name="description" content="Open-source, self-hosted WhatsApp API platform. Send transactional messages, automate customer support with chatbots, receive webhooks, and import Google Contacts securely.">
    <link rel="canonical" href="<?= e(public_url('')) ?>">
    <link rel="icon" type="image/jpeg" href="<?= e(base_url('assets/logo.jpg')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        brand: { wa_dark: '#075E54', wa_med: '#128C7E', wa_light: '#25D366' }
                    }
                }
            }
        };
    </script>
</head>
<body class="min-h-full flex flex-col font-sans text-slate-800 antialiased selection:bg-brand-wa_light/20 selection:text-brand-wa_dark bg-slate-50">

    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-slate-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="<?= e(base_url('')) ?>" class="flex items-center gap-3 text-slate-900 group">
                <img src="<?= e(base_url('assets/logo.jpg')) ?>" alt="WA API Hub Logo" class="w-9 h-9 rounded-xl object-cover shadow-sm group-hover:scale-105 transition-transform border border-slate-200/50">
                <div class="leading-tight">
                    <span class="font-bold text-slate-900 text-base tracking-tight block">WhatsApp API Hub</span>
                    <span class="text-[10px] text-slate-400 font-medium block">Open-Source Developer Gateway</span>
                </div>
            </a>
            
            <nav class="hidden md:flex items-center gap-6 text-sm font-semibold text-slate-600">
                <a href="#features" class="hover:text-slate-900 transition-colors">Features</a>
                <a href="#code" class="hover:text-slate-900 transition-colors">Code Examples</a>
                <a href="#google" class="hover:text-slate-900 transition-colors">Google Integration</a>
                <a href="<?= e(base_url('docs')) ?>" class="hover:text-slate-900 transition-colors">API Docs</a>
                <a href="<?= e(base_url('privacy')) ?>" class="hover:text-slate-900 transition-colors">Privacy</a>
                <a href="<?= e(base_url('terms')) ?>" class="hover:text-slate-900 transition-colors">Terms</a>
            </nav>

            <div class="flex items-center gap-3">
                <?php if ($user): ?>
                    <a href="<?= e(base_url('dashboard')) ?>" class="inline-flex items-center gap-2 text-sm font-semibold text-white bg-slate-900 hover:bg-slate-800 px-4 py-2 rounded-xl transition-all shadow-sm">
                        <span>Dashboard</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                <?php else: ?>
                    <a href="<?= e(base_url('login')) ?>" class="text-sm font-semibold text-slate-700 hover:text-slate-900 px-3 py-2 transition-colors">
                        Sign In
                    </a>
                    <a href="<?= e(base_url('register')) ?>" class="inline-flex items-center gap-2 text-sm font-semibold text-white bg-gradient-to-r from-brand-wa_med to-brand-wa_light hover:opacity-95 px-4 py-2 rounded-xl transition-all shadow-sm shadow-brand-wa_light/25">
                        Get Started Free
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative overflow-hidden pt-16 pb-20 lg:pt-24 lg:pb-28 bg-gradient-to-b from-white via-slate-50 to-slate-100 border-b border-slate-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            
            <!-- Badges -->
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 mb-6 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Self-Hosted &bull; Baileys Engine &bull; REST API &bull; Google Sync</span>
            </div>

            <!-- Main Heading -->
            <h1 class="text-4xl sm:text-6xl font-black text-slate-900 tracking-tight max-w-4xl mx-auto leading-[1.15]">
                The Reliable WhatsApp API Gateway for Modern Developers
            </h1>
            
            <p class="mt-6 text-lg sm:text-xl text-slate-600 max-w-2xl mx-auto leading-relaxed">
                Connect your WhatsApp account, dispatch high-throughput transactional alerts, automate smart customer support chatbots, and synchronize contacts securely with Google People API.
            </p>

            <!-- Call to Actions -->
            <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                <a href="<?= e(base_url('register')) ?>" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl font-bold text-white bg-slate-900 hover:bg-slate-800 shadow-md shadow-slate-900/10 transition-all text-sm sm:text-base">
                    <span>Deploy / Start Free</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
                <a href="<?= e(base_url('docs')) ?>" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 shadow-sm transition-all text-sm sm:text-base">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span>Read API Docs</span>
                </a>
                <a href="https://github.com/navneetyadav-code/whatsapp-api" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 px-5 py-3.5 rounded-xl font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 shadow-sm transition-all text-sm sm:text-base">
                    <svg class="w-4 h-4 text-slate-700" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/></svg>
                    <span>GitHub</span>
                </a>
            </div>

            <!-- Value Props Row -->
            <div class="mt-12 pt-8 border-t border-slate-200/80 grid grid-cols-2 sm:grid-cols-4 gap-4 text-center max-w-4xl mx-auto">
                <div class="p-3">
                    <p class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">100%</p>
                    <p class="text-xs text-slate-500 font-medium mt-1">Open Source & Self-Hosted</p>
                </div>
                <div class="p-3">
                    <p class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">&lt; 150ms</p>
                    <p class="text-xs text-slate-500 font-medium mt-1">API Dispatch Latency</p>
                </div>
                <div class="p-3">
                    <p class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">OAuth 2.0</p>
                    <p class="text-xs text-slate-500 font-medium mt-1">Google Verified Standards</p>
                </div>
                <div class="p-3">
                    <p class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Zero</p>
                    <p class="text-xs text-slate-500 font-medium mt-1">Data Reselling or Ads</p>
                </div>
            </div>

        </div>
    </section>

    <!-- Live Code Preview Section -->
    <section id="code" class="py-16 bg-white border-b border-slate-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <span class="text-xs font-bold text-brand-wa_med uppercase tracking-wider">Developer Friendly</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">Send your first message in 30 seconds</h2>
                <p class="text-sm text-slate-500 mt-2 max-w-lg mx-auto">Simple, standard REST endpoints that work seamlessly in any language or backend framework.</p>
            </div>

            <div class="rounded-2xl bg-slate-950 text-slate-200 p-4 sm:p-6 shadow-xl border border-slate-800 font-mono text-xs sm:text-sm overflow-x-auto">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-800">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-rose-500 inline-block"></span>
                        <span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span>
                        <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                        <span class="text-xs text-slate-400 font-sans ml-2">POST /api/send</span>
                    </div>
                    <span class="text-[11px] text-slate-400 font-sans">Content-Type: application/json</span>
                </div>
<pre class="leading-relaxed"><code>curl -X POST "<?= e(public_url('api/send')) ?>" \
  -H "X-Api-Key: your_api_key_here" \
  -H "X-Api-Secret: your_api_secret_here" \
  -H "Content-Type: application/json" \
  -d '{
    "number": "919876543210",
    "message": "Hello! Your verification OTP code is 482910."
  }'</code></pre>
            </div>
        </div>
    </section>

    <!-- Key Features Section -->
    <section id="features" class="py-20 bg-slate-50 border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-xs font-bold text-brand-wa_med uppercase tracking-wider">Built for Developers & Businesses</span>
                <h2 class="text-3xl font-extrabold text-slate-900 mt-2">Everything you need for WhatsApp Automation</h2>
                <p class="text-slate-600 mt-3 text-base">From single transactional verification codes to full-scale customer helpdesk automation.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Card 1 -->
                <div class="bg-white p-7 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-5 border border-emerald-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">High-Throughput Queue</h3>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Multi-tenant asynchronous message queues prevent connection dropouts, throttle traffic safely, and handle auto-retries upon network glitches.
                    </p>
                </div>

                <!-- Card 2 -->
                <div class="bg-white p-7 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-5 border border-blue-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Automated Chatbot Rules</h3>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Configure instant keyword matching, fuzzy replies, and welcome messages to assist customers 24/7 without manual operator intervention.
                    </p>
                </div>

                <!-- Card 3 -->
                <div class="bg-white p-7 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center mb-5 border border-purple-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Real-Time Webhooks</h3>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Subscribe to HMAC-SHA256 signed webhooks for inbound messages, delivery acknowledgments, and read receipts directly into your own app.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Google OAuth & Contacts Integration Section (Essential for Google Verification) -->
    <section id="google" class="py-20 bg-white border-b border-slate-200">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-gradient-to-br from-slate-900 to-slate-950 rounded-3xl p-8 sm:p-12 text-white relative overflow-hidden shadow-2xl">
                
                <div class="relative z-10 max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-white/10 text-white border border-white/10 mb-4">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                        <span>Google API Services Compliance</span>
                    </div>

                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Seamless & Secure Google Contacts Integration</h2>
                    <p class="mt-3 text-slate-300 text-sm sm:text-base leading-relaxed">
                        Import customer contacts directly from your authorized Google Account with one click. WhatsApp API Hub requests read-only permission strictly to populate your internal address book for message routing.
                    </p>

                    <div class="mt-6 space-y-2.5 text-xs sm:text-sm text-slate-300">
                        <div class="flex items-start gap-2.5">
                            <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span><strong>Limited Use Disclosure:</strong> Our use of Google user data adheres strictly to the Google API Services User Data Policy, including Limited Use requirements.</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span><strong>Zero Ads & No Resale:</strong> Your imported contact records are never sold, transferred to data brokers, or used for advertising.</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span><strong>Instant Revocation:</strong> You can disconnect and wipe synced contacts at any time from your dashboard or via your Google Security portal.</span>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-wrap gap-4">
                        <a href="<?= e(base_url('privacy')) ?>" class="text-xs font-semibold px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white transition-colors border border-white/20">
                            Read Privacy Policy &rarr;
                        </a>
                        <a href="<?= e(base_url('terms')) ?>" class="text-xs font-semibold px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white transition-colors border border-white/20">
                            Read Terms of Service &rarr;
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 mt-auto py-12 text-slate-600 text-xs sm:text-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6 pb-8 border-b border-slate-200">
                <div class="flex items-center gap-3">
                    <img src="<?= e(base_url('assets/logo.jpg')) ?>" alt="Logo" class="w-8 h-8 rounded-lg object-cover shadow-sm border border-slate-200/50">
                    <span class="font-bold text-slate-900 text-sm">WhatsApp API Hub</span>
                </div>

                <div class="flex flex-wrap items-center justify-center gap-6 font-medium text-xs">
                    <a href="<?= e(base_url('')) ?>" class="text-slate-900 font-semibold hover:underline">Home</a>
                    <a href="<?= e(base_url('docs')) ?>" class="hover:text-slate-900 transition-colors">Documentation</a>
                    <a href="<?= e(base_url('privacy')) ?>" class="hover:text-slate-900 transition-colors">Privacy Policy</a>
                    <a href="<?= e(base_url('terms')) ?>" class="hover:text-slate-900 transition-colors">Terms of Service</a>
                    <a href="mailto:info.assistgroupofficial@gmail.com" class="hover:text-slate-900 transition-colors">Support Contact</a>
                    <a href="https://github.com/navneetyadav-code/whatsapp-api" target="_blank" rel="noopener noreferrer" class="hover:text-slate-900 transition-colors">GitHub</a>
                </div>
            </div>

            <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px] text-slate-400">
                <p>&copy; 2026 WhatsApp API Hub &bull; Maintained by Navneet Kumar Yadav &bull; Released under the MIT License.</p>
                <p>Not affiliated with, sponsored by, or endorsed by Meta Platforms, Inc., WhatsApp Inc., or Google LLC.</p>
            </div>
        </div>
    </footer>

</body>
</html>
<?php exit; ?>
