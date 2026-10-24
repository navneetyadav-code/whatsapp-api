<?php

declare(strict_types=1);

$user = current_user();
$seoTitle = 'Terms of Service | WhatsGateway';
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($seoTitle) ?></title>
    <meta name="description" content="Terms of Service for WhatsGateway. Read our acceptable use policy, anti-spam guidelines, Google integration rules, API limits, and user agreement.">
    <link rel="canonical" href="<?= e(public_url('terms')) ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= e(base_url('assets/favicon-32x32.png')) ?>">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= e(base_url('assets/favicon.png')) ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= e(base_url('assets/apple-touch-icon.png')) ?>">
    <link rel="shortcut icon" href="<?= e(base_url('favicon.ico')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: { wa_dark: '#075E54', wa_med: '#128C7E', wa_light: '#25D366' }
                    }
                }
            }
        };
    </script>
</head>
<body class="min-h-full flex flex-col font-sans text-slate-800 antialiased selection:bg-brand-wa_light/20 selection:text-brand-wa_dark bg-slate-50">

    <!-- Top Navigation -->
    <header class="sticky top-0 z-30 bg-white/90 backdrop-blur-md border-b border-slate-200/80">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="<?= e(base_url('')) ?>" class="flex items-center gap-2.5 text-slate-900 group">
                <img src="<?= e(base_url('assets/favicon.png')) ?>" alt="WhatsGateway Logo" class="w-8 h-8 rounded-lg object-cover shadow-sm border border-slate-200/50">
                <div class="leading-tight">
                    <span class="font-bold text-slate-900 text-[15px] tracking-tight block">WhatsGateway</span>
                    <span class="text-[10px] text-slate-400 font-medium block">Self-Hosted Developer Platform</span>
                </div>
            </a>
            
            <nav class="flex items-center gap-3">
                <a href="<?= e(base_url('docs')) ?>" class="text-xs font-semibold text-slate-600 hover:text-slate-900 px-3 py-1.5 rounded-lg transition-colors">API Docs</a>
                <a href="<?= e(base_url('privacy')) ?>" class="text-xs font-semibold text-slate-600 hover:text-slate-900 px-3 py-1.5 rounded-lg transition-colors">Privacy Policy</a>
                <?php if ($user): ?>
                    <a href="<?= e(base_url('dashboard')) ?>" class="inline-flex items-center gap-1.5 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 px-4 py-2 rounded-xl transition-all shadow-sm">
                        Go to Dashboard &rarr;
                    </a>
                <?php else: ?>
                    <a href="<?= e(base_url('login')) ?>" class="inline-flex items-center gap-1.5 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 px-4 py-2 rounded-xl transition-all shadow-sm">
                        Sign In
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Main Content Header -->
    <main class="flex-1 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="mb-10 pb-6 border-b border-slate-200">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 mb-3">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                Legal Terms & Acceptable Use
            </span>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">Terms of Service</h1>
            <p class="mt-2 text-sm text-slate-500 font-medium">
                Effective Date: October 14, 2026 &bull; Version 2.4 &bull; Maintained by Navneet Kumar Yadav
            </p>
        </div>

        <article class="prose prose-slate max-w-none space-y-8 text-sm sm:text-base leading-relaxed text-slate-700">

            <section class="space-y-3">
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">1. Acceptance of Terms</h2>
                <p>
                    By registering for an account, accessing, or using <strong>WhatsGateway</strong> ("the Service", "the Software", "we", "us", or "our"), hosted at <a href="<?= e(public_url('')) ?>" class="text-brand-wa_med font-semibold hover:underline"><?= e(parse_url(public_url(''), PHP_URL_HOST)) ?></a> or self-hosted, you ("User", "Developer", or "Customer") agree to be bound by these Terms of Service ("Terms") and our <a href="<?= e(base_url('privacy')) ?>" class="text-brand-wa_med font-semibold hover:underline">Privacy Policy</a>.
                </p>
                <p>
                    If you do not agree to these Terms in their entirety, you must not access or use WhatsGateway.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">2. Description of the Service</h2>
                <p>
                    WhatsGateway is a developer-centric automation and messaging gateway software. The platform enables authenticated users to:
                </p>
                <ul class="list-disc pl-5 space-y-1.5 text-slate-600">
                    <li>Pair and manage WhatsApp Web client sessions via the open-source Baileys library.</li>
                    <li>Trigger programmatic transactional messages (text, media, documents, templates) via RESTful HTTP APIs.</li>
                    <li>Receive inbound message events and delivery receipts via real-time Webhooks.</li>
                    <li>Synchronize and manage customer phonebooks, including optional one-click contact import through official Google People API OAuth 2.0.</li>
                    <li>Automate conversational rules and keyword auto-replies.</li>
                </ul>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">3. User Account, API Keys & Security</h2>
                <p>
                    To access the application and programmatic APIs, you must create an account. You agree to:
                </p>
                <ul class="list-disc pl-5 space-y-1.5 text-slate-600">
                    <li>Provide truthful, accurate, and current registration information.</li>
                    <li>Maintain the confidentiality and security of your account credentials, session secrets, and generated API key secrets.</li>
                    <li>Immediately notify us at <a href="mailto:info.assistgroupofficial@gmail.com" class="text-brand-wa_med hover:underline font-semibold">info.assistgroupofficial@gmail.com</a> upon discovering any unauthorized access or breach of your API tokens.</li>
                    <li>Accept full legal and operational responsibility for all API requests, transmissions, and messages dispatched under your API keys or paired WhatsApp session.</li>
                </ul>
            </section>

            <!-- Important Highlight: Anti-Spam & Recipient Consent -->
            <section class="space-y-4 p-5 rounded-2xl bg-amber-50/60 border border-amber-200/80">
                <div class="flex items-center gap-2 text-amber-800 font-bold text-base">
                    <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span>4. Acceptable Use Policy & Anti-Spam Requirements</span>
                </div>
                <p class="text-amber-950 text-sm leading-relaxed">
                    WhatsGateway is intended strictly for authorized, transactional, customer service, and consented business communication. We maintain a zero-tolerance policy against unsolicited bulk messaging (spam).
                </p>
                <div class="space-y-2 text-xs sm:text-sm text-amber-900">
                    <p><strong>You explicitly warrant and agree that:</strong></p>
                    <ul class="list-disc pl-5 space-y-1.5">
                        <li><strong>Prior Recipient Consent:</strong> You will only send messages to recipients who have given verifiable, explicit prior consent (opt-in) to receive communication from your organization.</li>
                        <li><strong>Opt-Out Mechanism:</strong> You will honor all opt-out or unsubscribe requests (such as "STOP", "UNSUBSCRIBE") immediately.</li>
                        <li><strong>Prohibited Content:</strong> You will never use the service to transmit spam, phishing, scams, unsolicited financial schemes, adult content, harassment, hate speech, malware, or any material that infringes intellectual property rights or local criminal laws.</li>
                        <li><strong>WhatsApp Terms Compliance:</strong> You will adhere to WhatsApp's Business Messaging Policies, Terms of Service, and community standards. Any suspension or ban imposed by WhatsApp on your phone number is solely your risk and responsibility.</li>
                        <li><strong>Telecommunications Regulations:</strong> You are responsible for compliance with relevant laws in your operating territory, including CAN-SPAM, TCPA, GDPR, and Indian Telecom Commercial Communications Customer Preference Regulations (TCCCPR).</li>
                    </ul>
                </div>
            </section>

            <!-- Google Integration Terms -->
            <section class="space-y-4 p-5 rounded-2xl bg-sky-50/60 border border-sky-200/80">
                <div class="flex items-center gap-2 text-sky-900 font-bold text-base">
                    <svg class="w-5 h-5 text-sky-600 flex-shrink-0" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                    <span>5. Google OAuth & People API Integration Terms</span>
                </div>
                <p class="text-sky-950 text-sm leading-relaxed">
                    WhatsGateway offers optional Google Sign-In and Google Contacts synchronization powered by Google OAuth 2.0. By authenticating your Google account with our application:
                </p>
                <div class="space-y-2 text-xs sm:text-sm text-sky-900">
                    <ul class="list-disc pl-5 space-y-1.5">
                        <li><strong>User Data Policy & Limited Use:</strong> Our use and transfer of information received from Google APIs will adhere to the <a href="https://developers.google.com/terms/api-services-user-data-policy" target="_blank" rel="noopener noreferrer" class="font-bold underline">Google API Services User Data Policy</a>, including the Limited Use requirements.</li>
                        <li><strong>Scoped Permission:</strong> The requested scope (<code class="bg-sky-100 text-sky-900 px-1 py-0.5 rounded text-xs">https://www.googleapis.com/auth/contacts.readonly</code>) is utilized exclusively to read contact names and telephone numbers into your personal contacts database within the application.</li>
                        <li><strong>No Sale or Third-Party Dissemination:</strong> Google user data is strictly confined to your account. We never sell, rent, monetize, or transfer contact records to third-party ad networks, data brokers, or AI model trainers.</li>
                        <li><strong>Revocation:</strong> You may disconnect Google synchronization at any time directly in your account settings or via your <a href="https://myaccount.google.com/permissions" target="_blank" rel="noopener noreferrer" class="font-bold underline">Google Account Security Controls</a>.</li>
                    </ul>
                </div>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">6. API Rate Limits & Fair Usage</h2>
                <p>
                    To ensure high platform availability and protect downstream infrastructure:
                </p>
                <ul class="list-disc pl-5 space-y-1.5 text-slate-600">
                    <li>We enforce rate limits and throughput caps per API key and IP address as configured in your dashboard.</li>
                    <li>Attempts to circumvent rate limits, probe security perimeters, flood queue endpoints, or initiate denial-of-service attacks will result in immediate suspension.</li>
                    <li>We reserve the right to throttle or temporarily pause high-frequency queues that risk degraded performance for other services.</li>
                </ul>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">7. Intellectual Property & Trademarks</h2>
                <p>
                    WhatsGateway is open-source software licensed under the MIT License. You retain full ownership of your proprietary code, customer lists, and outbound message copy.
                </p>
                <p class="text-slate-600">
                    <strong>Notice Regarding Third-Party Trademarks:</strong> "WhatsApp" is a registered trademark of Meta Platforms, Inc. "Google" and the Google logo are trademarks of Google LLC. WhatsGateway is an independent software tool and is <strong>not affiliated, endorsed, sponsored, or certified by Meta Platforms, Inc. or Google LLC</strong>.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">8. Account Termination & Suspension</h2>
                <p>
                    We reserve the right, without prior notice or liability, to suspend, terminate, or restrict your account or API access if we determine in our sole discretion that you have violated these Terms, engaged in spamming, or exposed the platform to security hazards.
                </p>
                <p>
                    You may terminate your account at any time by disconnecting your WhatsApp device, removing your API keys, and contacting support for full record deletion.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">9. Disclaimer of Warranties</h2>
                <p class="text-slate-600 uppercase text-xs tracking-wider font-semibold">Provided "AS IS"</p>
                <p class="text-slate-600">
                    TO THE MAXIMUM EXTENT PERMITTED BY APPLICABLE LAW, WHATSGATEWAY AND ALL ACCOMPANYING CODE, SERVICES, AND APIS ARE PROVIDED ON AN "AS IS" AND "AS AVAILABLE" BASIS WITHOUT WARRANTIES OF ANY KIND, WHETHER EXPRESS, IMPLIED, STATUTORY, OR OTHERWISE, INCLUDING WITHOUT LIMITATION WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, AND NON-INFRINGEMENT. WE DO NOT WARRANT UNINTERRUPTED AVAILABILITY, INSTANTANEOUS DELIVERY, OR ERROR-FREE COMMUNICATION OVER THIRD-PARTY CARRIERS OR WHATSAPP NETWORKS.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">10. Limitation of Liability</h2>
                <p class="text-slate-600">
                    IN NO EVENT SHALL THE MAINTAINERS, DEVELOPERS, OR CONTRIBUTORS (INCLUDING NAVNEET KUMAR YADAV) BE LIABLE FOR ANY INDIRECT, INCIDENTAL, SPECIAL, CONSEQUENTIAL, OR PUNITIVE DAMAGES, INCLUDING BUT NOT LIMITED TO LOSS OF REVENUE, PROFITS, GOODWILL, REPUTATIONAL DAMAGE, OR BUSINESS DISRUPTIONS ARISING OUT OF OR IN CONNECTION WITH THE USE OR INABILITY TO USE THE SERVICE, THIRD-PARTY ACCOUNT RESTRICTIONS, OR WHATSAPP BAN EVENTS.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">11. Modifications to Terms</h2>
                <p>
                    We reserve the right to revise or update these Terms of Service periodically to reflect changes in regulatory standards, Google API policies, or platform functionality. Updated terms will be posted directly to this URL with an updated "Effective Date". Your continued use of WhatsGateway after changes are published constitutes your acceptance of the revised Terms.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">12. Contact & Governance</h2>
                <p>
                    For inquiries, clarification, or compliance questions regarding these Terms of Service, please contact the maintainer:
                </p>
                <div class="p-4 rounded-xl bg-white border border-slate-200 space-y-1 text-slate-600 text-xs md:text-sm">
                    <p><strong>Maintainer:</strong> Navneet Kumar Yadav</p>
                    <p><strong>Official Inquiries:</strong> <a href="mailto:info.assistgroupofficial@gmail.com" class="text-brand-wa_med hover:underline font-semibold">info.assistgroupofficial@gmail.com</a></p>
                    <p><strong>Source Repository:</strong> <a href="https://github.com/navneetyadav-code/whatsapp-api" target="_blank" rel="noopener noreferrer" class="text-brand-wa_med hover:underline font-semibold">github.com/navneetyadav-code/whatsapp-api</a></p>
                </div>
            </section>

        </article>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 mt-12 py-8 text-center text-xs text-slate-500 space-y-2">
        <div class="max-w-6xl mx-auto px-4 flex flex-wrap justify-center gap-6 font-medium">
            <a href="<?= e(base_url('')) ?>" class="hover:text-slate-900 transition-colors">Home</a>
            <a href="<?= e(base_url('docs')) ?>" class="hover:text-slate-900 transition-colors">Documentation</a>
            <a href="<?= e(base_url('privacy')) ?>" class="hover:text-slate-900 transition-colors">Privacy Policy</a>
            <a href="<?= e(base_url('terms')) ?>" class="text-slate-900 font-bold hover:underline">Terms of Service</a>
            <a href="mailto:info.assistgroupofficial@gmail.com" class="hover:text-slate-900 transition-colors">Support Contact</a>
        </div>
        <p class="text-slate-400 text-[11px]">&copy; <?= date('Y') ?> WhatsGateway &bull; Maintained by Navneet Kumar Yadav &bull; Open Source under MIT License</p>
    </footer>

</body>
</html>
<?php exit; ?>
