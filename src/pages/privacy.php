<?php

declare(strict_types=1);

$user = current_user();
$seoTitle = 'Privacy Policy | WhatsApp API Hub';
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($seoTitle) ?></title>
    <meta name="description" content="Public Privacy Policy for WhatsApp API Hub. Explains how data, Google OAuth accounts, and contacts are collected, protected, and used in accordance with Google API User Data Policies.">
    <link rel="canonical" href="<?= e(public_url('privacy')) ?>">
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
                <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-brand-wa_med to-brand-wa_light flex items-center justify-center text-white shadow-sm shadow-brand-wa_light/30">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                </div>
                <div class="leading-tight">
                    <span class="font-bold text-slate-900 text-[15px] tracking-tight block">WhatsApp API Hub</span>
                    <span class="text-[10px] text-slate-400 font-medium block">Self-Hosted Developer Platform</span>
                </div>
            </a>
            
            <nav class="flex items-center gap-3">
                <a href="<?= e(base_url('docs')) ?>" class="text-xs font-semibold text-slate-600 hover:text-slate-900 px-3 py-1.5 rounded-lg transition-colors">API Docs</a>
                <a href="<?= e(base_url('terms')) ?>" class="text-xs font-semibold text-slate-600 hover:text-slate-900 px-3 py-1.5 rounded-lg transition-colors">Terms of Service</a>
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

    <!-- Main Container -->
    <main class="flex-grow max-w-4xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-10 md:py-14">
        
        <!-- Header -->
        <div class="mb-10 pb-8 border-b border-slate-200">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200/60 mb-4 uppercase tracking-wider">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                Official Policy
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight">Privacy Policy</h1>
            <p class="text-slate-500 text-sm md:text-base mt-2">Effective Date: October 14, 2026 &bull; Last Updated: October 14, 2026</p>
        </div>

        <!-- Content -->
        <article class="prose prose-slate max-w-none text-slate-700 space-y-8 text-sm md:text-[15px] leading-relaxed">
            
            <section class="space-y-3">
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">1. Introduction</h2>
                <p>
                    Welcome to <strong>WhatsApp API Hub</strong> ("we", "our", or "the Platform"), an open-source, self-hosted WhatsApp automation and REST API gateway developed by <strong>Navneet Kumar Yadav</strong>. We take the privacy and confidentiality of your personal and business data very seriously.
                </p>
                <p>
                    This Privacy Policy details how we collect, use, store, protect, and process your information when you access or interact with our website, dashboard, APIs, Google OAuth authentication, and Google Contacts integration features.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">2. Information We Collect</h2>
                <p>We only collect the minimum amount of information necessary to deliver our services:</p>
                <ul class="list-disc pl-5 space-y-2 text-slate-600">
                    <li><strong>Account Credentials:</strong> Name, email address, password hash, and optional phone number provided during account registration or Google OAuth login.</li>
                    <li><strong>WhatsApp Session Metadata:</strong> Device linkage status, phone number identifier, and push name negotiated through end-to-end encrypted Baileys protocol sessions.</li>
                    <li><strong>Messaging Data &amp; Logs:</strong> Recipient phone numbers, message payloads, status timestamps, and error logs created when routing messages through your configured WhatsApp queues.</li>
                    <li><strong>Google User Data (via OAuth):</strong>
                        When you choose to authenticate or sync contacts using Google services:
                        <ul class="list-circle pl-5 mt-1 space-y-1">
                            <li><strong>Google Sign-In:</strong> Your name, email address, Google account profile ID, and avatar image (<code class="text-xs bg-slate-100 px-1.5 py-0.5 rounded text-slate-800">openid</code>, <code class="text-xs bg-slate-100 px-1.5 py-0.5 rounded text-slate-800">email</code>, <code class="text-xs bg-slate-100 px-1.5 py-0.5 rounded text-slate-800">profile</code>).</li>
                            <li><strong>Google Contacts Import:</strong> Read-only access to names and phone numbers stored in your Google Contacts address book (<code class="text-xs bg-slate-100 px-1.5 py-0.5 rounded text-slate-800">https://www.googleapis.com/auth/contacts.readonly</code>), strictly triggered by your explicit click on "Import Google Contacts".</li>
                        </ul>
                    </li>
                </ul>
            </section>

            <section class="space-y-3 p-5 rounded-2xl bg-amber-50/80 border border-amber-200">
                <h2 class="text-lg font-bold text-amber-950 flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    3. Google API Services User Data Policy Compliance (Limited Use Disclosure)
                </h2>
                <p class="text-amber-900 text-xs md:text-sm leading-relaxed">
                    <strong>WhatsApp API Hub's use and transfer to any other app of information received from Google APIs will adhere to the <a href="https://developers.google.com/terms/api-services-user-data-policy" target="_blank" rel="noopener noreferrer" class="font-semibold underline text-amber-950 hover:text-black">Google API Services User Data Policy</a>, including the Limited Use requirements.</strong>
                </p>
                <ul class="list-disc pl-5 space-y-1.5 text-xs md:text-sm text-amber-900/90">
                    <li><strong>No Sale of Google Data:</strong> We never sell, lease, rent, or trade your Google data, contacts, or personal details to any third parties, data brokers, or advertising networks under any circumstances.</li>
                    <li><strong>No Targeted Advertising:</strong> Your Google user data is never accessed or used for advertising, marketing campaigns, retargeting, or profiling purposes.</li>
                    <li><strong>No AI/ML Model Training:</strong> Information gathered via Google APIs is never utilized to train generalized artificial intelligence or machine learning models.</li>
                    <li><strong>Human Access Restrictions:</strong> No human or employee reads your Google contacts or personal data unless required to resolve a technical error explicitly reported by you, or as mandated by law.</li>
                </ul>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">4. How We Use Collected Information</h2>
                <p>We use your information exclusively for the following operational purposes:</p>
                <ul class="list-disc pl-5 space-y-2 text-slate-600">
                    <li>To authenticate your identity, prevent fraud, and maintain your private administrator session.</li>
                    <li>To save and synchronize your requested contacts into your personal contact table for broadcasting permitted transactional messages.</li>
                    <li>To establish and maintain the secure WhatsApp socket bridge linking your device to your private queue.</li>
                    <li>To deliver automated webhook callbacks and audit logs to your designated endpoints.</li>
                </ul>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">5. Data Retention &amp; Security Measures</h2>
                <p>
                    We implement modern industry-standard security practices to safeguard all collected data:
                </p>
                <ul class="list-disc pl-5 space-y-2 text-slate-600">
                    <li><strong>Encryption in Transit:</strong> All HTTP traffic to and from the platform is enforced over Transport Layer Security (TLS 1.2 / TLS 1.3 / HTTPS).</li>
                    <li><strong>End-to-End Encryption:</strong> WhatsApp messages transmitted through your linked device leverage the native Noise protocol and Signal protocol cryptographic ratchets.</li>
                    <li><strong>Credential Hashing:</strong> Passwords and API secrets are never stored in plaintext and are securely hashed using modern cryptographic algorithms (<code class="text-xs bg-slate-100 px-1.5 py-0.5 rounded text-slate-800">password_hash</code> with BCRYPT and SHA-256).</li>
                    <li><strong>Session Key Isolation:</strong> Multi-tenant WhatsApp credentials and prekeys are isolated per user on disk and protected against unauthorized cross-user access.</li>
                </ul>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">6. User Control, Data Deletion &amp; Revocation</h2>
                <p>You have complete control over your personal data and integrations:</p>
                <ul class="list-disc pl-5 space-y-2 text-slate-600">
                    <li><strong>Delete Contacts:</strong> You can delete any individual contact or flush your contact list at any moment directly from the Contacts Manager.</li>
                    <li><strong>Unlink WhatsApp:</strong> Clicking "Disconnect" or "Reset Session" immediately wipes your active WhatsApp cryptographic session files from our servers.</li>
                    <li><strong>Revoke Google Access:</strong> You can revoke WhatsApp API Hub's access to your Google Account at any time via your Google Security Settings at <a href="https://myaccount.google.com/permissions" target="_blank" rel="noopener noreferrer" class="text-brand-wa_med hover:underline font-semibold">Google Account Permissions</a>.</li>
                    <li><strong>Account Deletion Requests:</strong> To permanently delete your entire account, API keys, database records, and logs, email us at <a href="mailto:info.assistgroupofficial@gmail.com" class="text-brand-wa_med hover:underline font-semibold">info.assistgroupofficial@gmail.com</a>. We will process your deletion request within 48 business hours.</li>
                </ul>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">7. Third-Party Disclaimers</h2>
                <p>
                    WhatsApp API Hub is an independent open-source tool. We are not endorsed, affiliated, associated, authorized, or connected in any way with Meta Platforms, Inc., WhatsApp Inc., or Google LLC. WhatsApp and Google are registered trademarks of their respective owners.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">8. Contact Information</h2>
                <p>
                    If you have any questions, inquiries, concerns, or deletion requests regarding this Privacy Policy or our data handling practices, please contact our privacy compliance team:
                </p>
                <div class="p-4 rounded-xl bg-white border border-slate-200 space-y-1 text-slate-600 text-xs md:text-sm">
                    <p><strong>Primary Maintainer:</strong> Navneet Kumar Yadav</p>
                    <p><strong>Official Email:</strong> <a href="mailto:info.assistgroupofficial@gmail.com" class="text-brand-wa_med hover:underline font-semibold">info.assistgroupofficial@gmail.com</a></p>
                    <p><strong>Project Repository:</strong> <a href="https://github.com/navneetyadav-code/whatsapp-api" target="_blank" rel="noopener noreferrer" class="text-brand-wa_med hover:underline font-semibold">github.com/navneetyadav-code/whatsapp-api</a></p>
                </div>
            </section>
        </article>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 mt-12 py-8 text-center text-xs text-slate-500 space-y-2">
        <div class="max-w-6xl mx-auto px-4 flex flex-wrap justify-center gap-6 font-medium">
            <a href="<?= e(base_url('')) ?>" class="hover:text-slate-900 transition-colors">Home</a>
            <a href="<?= e(base_url('docs')) ?>" class="hover:text-slate-900 transition-colors">Documentation</a>
            <a href="<?= e(base_url('privacy')) ?>" class="text-slate-900 font-bold hover:underline">Privacy Policy</a>
            <a href="<?= e(base_url('terms')) ?>" class="hover:text-slate-900 transition-colors">Terms of Service</a>
            <a href="mailto:info.assistgroupofficial@gmail.com" class="hover:text-slate-900 transition-colors">Support Contact</a>
        </div>
        <p class="text-slate-400 text-[11px]">&copy; 2026 WhatsApp API Hub &bull; Maintained by Navneet Kumar Yadav &bull; Open Source under MIT License</p>
    </footer>

</body>
</html>
<?php exit; ?>
