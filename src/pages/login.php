<?php

if (current_user()) {
    redirect_to('index.php?page=dashboard');
}

if (isset($_GET['action']) && $_GET['action'] === 'cancel_2fa') {
    unset($_SESSION['2fa_pending_user_id']);
    redirect_to('index.php?page=login');
}

$pending2faUserId = $_SESSION['2fa_pending_user_id'] ?? null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    if ($pending2faUserId) {
        $code = trim((string) ($_POST['totp_code'] ?? ''));
        $sec = get_user_security((int) $pending2faUserId);
        $verified = false;

        if (!empty($sec['totp_secret']) && totp_verify($sec['totp_secret'], $code)) {
            $verified = true;
        } elseif (verify_and_consume_backup_code((int) $pending2faUserId, $code)) {
            $verified = true;
        }

        if ($verified) {
            complete_2fa_login((int) $pending2faUserId);
            redirect_to('index.php?page=dashboard');
        } else {
            $error = 'Invalid 6-digit verification code or backup recovery code.';
        }
    } else {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ((bool) app_config('app.disable_password_login')) {
            $error = 'Password login is disabled on this instance. Please sign in with Google.';
        } else {
            $lockedFor = login_lockout_seconds($email);
            if ($lockedFor > 0) {
                $error = 'Too many login attempts. Try again in about ' . (int) ceil($lockedFor / 60) . ' minutes.';
            } elseif (attempt_login($email, $password)) {
                if (!empty($_SESSION['2fa_pending_user_id'])) {
                    redirect_to('index.php?page=login&step=2fa');
                }
                redirect_to('index.php?page=dashboard');
            } else {
                $error = 'Email or password is incorrect.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsGateway - Login</title>
    <link rel="icon" type="image/png" sizes="32x32" href="<?= e(base_url('assets/favicon-32x32.png')) ?>">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= e(base_url('assets/favicon.png')) ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= e(base_url('assets/apple-touch-icon.png')) ?>">
    <link rel="shortcut icon" href="<?= e(base_url('favicon.ico')) ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            dark: '#042F2E',    // Deep teal for premium feel
                            wa_dark: '#075E54', // Classic WA dark green
                            wa_med: '#128C7E',  // Classic WA green
                            wa_light: '#25D366' // WA primary action green
                        }
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'float-delayed': 'float 6s ease-in-out 3s infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-20px)' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        /* Custom scrollbar to avoid layout shifts */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        /* Form Autofill styling to maintain clean look */
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus, 
        input:-webkit-autofill:active{
            -webkit-box-shadow: 0 0 0 30px white inset !important;
            -webkit-text-fill-color: #111827 !important;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased min-h-screen lg:h-screen lg:overflow-hidden flex flex-col lg:flex-row overflow-x-hidden">

    <!-- Left Promotional Panel (Hidden on mobile, 38% width on desktop) -->
    <div class="hidden lg:flex lg:w-[38%] bg-gradient-to-br from-brand-dark via-brand-wa_dark to-brand-wa_med p-10 flex-col justify-between relative overflow-hidden shadow-2xl z-10">
        
        <!-- Subtle Floating Abstract Background Elements -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <!-- Floating Node 1 -->
            <svg class="absolute top-20 right-10 opacity-20 animate-float text-white w-32 h-32" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="20" y="20" width="60" height="60" rx="16" stroke="currentColor" stroke-width="2"/>
                <circle cx="80" cy="80" r="6" fill="currentColor"/>
                <circle cx="20" cy="20" r="4" fill="currentColor"/>
                <path d="M20 50 H80" stroke="currentColor" stroke-width="1" stroke-dasharray="4 4"/>
            </svg>
            <!-- Floating Node 2 -->
            <svg class="absolute bottom-40 -left-10 opacity-10 animate-float-delayed text-brand-wa_light w-48 h-48" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M50 10 C 25 10, 10 25, 10 50 C 10 75, 25 90, 50 90 C 75 90, 90 75, 90 50 C 90 25, 75 10, 50 10 Z" stroke="currentColor" stroke-width="2"/>
                <path d="M10 50 Q 50 20 90 50" stroke="currentColor" stroke-width="1" fill="transparent"/>
                <circle cx="50" cy="50" r="8" fill="currentColor"/>
            </svg>
        </div>

        <!-- Top Logo -->
        <div class="relative z-10 flex items-center gap-2.5">
            <img src="<?= e(base_url('assets/logo.jpg')) ?>" alt="WhatsGateway Logo" class="w-8 h-8 rounded-lg object-cover shadow-sm border border-white/20">
            <span class="font-semibold text-white tracking-wide text-sm">WhatsGateway</span>
        </div>

        <!-- Middle Content -->
        <div class="relative z-10 my-auto py-4">
            <h1 class="text-3xl xl:text-4xl font-bold text-white leading-tight mb-3 xl:mb-4 tracking-tight">Connect.<br>Automate.<br>Grow.</h1>
            <p class="text-white/80 text-xs xl:text-sm leading-relaxed max-w-sm mb-6 xl:mb-8">
                Independent open-source WhatsApp automation gateway powered by Baileys. Self-hosted, lightweight, and customizable.
            </p>
            
            <!-- Compact Feature Highlights -->
            <ul class="space-y-3">
                <li class="flex items-center gap-3 text-sm font-medium text-white/90">
                    <div class="w-5 h-5 rounded-full bg-brand-wa_light/20 flex items-center justify-center text-brand-wa_light border border-brand-wa_light/30">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    High-volume message delivery
                </li>
                <li class="flex items-center gap-3 text-sm font-medium text-white/90">
                    <div class="w-5 h-5 rounded-full bg-brand-wa_light/20 flex items-center justify-center text-brand-wa_light border border-brand-wa_light/30">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    Automated workflows & routing
                </li>
                <li class="flex items-center gap-3 text-sm font-medium text-white/90">
                    <div class="w-5 h-5 rounded-full bg-brand-wa_light/20 flex items-center justify-center text-brand-wa_light border border-brand-wa_light/30">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    Secure E2E encryption
                </li>
            </ul>
        </div>

        <!-- Bottom Footer -->
        <div class="relative z-10 text-xs text-white/70 font-medium space-y-1">
            <div>Made by <a href="https://github.com/navneetyadav-code/whatsapp-api" target="_blank" rel="noopener noreferrer" class="text-white hover:underline font-semibold">Navneet Kumar Yadav</a></div>
            <div class="text-[11px] text-white/60">Takedown / Removal request: <a href="mailto:info.assistgroupofficial@gmail.com" class="text-white/80 hover:underline">info.assistgroupofficial@gmail.com</a></div>
            <div class="text-[10px] text-white/40 pt-1">Independent open-source project. Not affiliated with or endorsed by Meta or WhatsApp Inc.</div>
        </div>
    </div>

    <!-- Right Login Form Panel (62% width on desktop, 100% on mobile) -->
    <div class="w-full lg:w-[62%] h-full flex items-center justify-center p-4 sm:p-6 lg:p-6 xl:p-8 relative overflow-y-auto lg:overflow-hidden">
        
        <!-- Decorative subtle background on right side -->
        <div class="absolute inset-0 bg-white bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] [background-size:20px_20px] opacity-50 z-0"></div>

        <div class="w-full max-w-[390px] bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 p-6 sm:p-8 lg:py-6 lg:px-7 z-10 relative my-auto">
            
            <!-- Logo inside form (mobile branding) -->
            <div class="flex items-center gap-2 mb-4 lg:hidden">
                <img src="<?= e(base_url('assets/logo.jpg')) ?>" alt="WhatsGateway Logo" class="w-7 h-7 rounded-lg object-cover shadow-sm border border-gray-100">
                <span class="font-bold text-gray-900 tracking-tight text-base">WhatsGateway</span>
            </div>

            <?php if ($pending2faUserId): ?>
            <!-- 2FA TOTP Challenge Header -->
            <div class="mb-7">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60 mb-3">
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Two-Factor Authentication
                </div>
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Security Verification</h2>
                <p class="text-sm text-gray-500 mt-1.5 font-medium">Enter the 6-digit code from your authenticator app or an 8-character recovery backup code.</p>
            </div>

            <!-- 2FA Form -->
            <form method="post" class="space-y-3">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <?php foreach (flashes() as $flash): ?>
                    <div class="p-3 text-sm rounded-xl <?= $flash['type'] === 'error' || $flash['type'] === 'danger' ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700' ?>">
                        <?= e($flash['message']) ?>
                    </div>
                <?php endforeach; ?>
                <?php if ($error): ?>
                    <div class="p-3 text-sm rounded-xl bg-red-50 text-red-700">
                        <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <div>
                    <label for="totp_code" class="block text-sm font-semibold text-gray-700 mb-1.5">Verification / Backup Code</label>
                    <input type="text" id="totp_code" name="totp_code" 
                           class="w-full px-3.5 py-3 text-center tracking-widest text-lg font-mono rounded-xl border border-gray-200 bg-gray-50 focus:bg-white placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-brand-wa_light/10 focus:border-brand-wa_light transition-all" 
                           placeholder="000000 or A1B2C3D4" 
                           inputmode="text" autocomplete="one-time-code" maxlength="16" required autofocus>
                    <p class="text-[11px] text-gray-400 mt-1 text-center">Google Authenticator, Authy, 1Password, or Single-Use Backup Code</p>
                </div>

                <button type="submit" class="w-full bg-brand-wa_light hover:bg-[#1DA851] text-white font-semibold text-sm py-2 rounded-xl transition-all duration-200 shadow-sm shadow-brand-wa_light/20 active:scale-[0.98] flex justify-center items-center gap-2">
                    Verify & Continue
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>

                <div class="pt-3 text-center">
                    <a href="<?= e(base_url('index.php?page=login&action=cancel_2fa')) ?>" class="text-xs text-gray-500 hover:text-gray-900 font-medium transition-colors">
                        &larr; Cancel and sign in with a different account
                    </a>
                </div>
            </form>
            <?php else: ?>
            <!-- Header -->
            <div class="mb-4">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">Welcome Back</h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1 font-medium">Log in to manage your API integrations.</p>
            </div>

            <!-- Login Form -->
            <form method="post" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <?php foreach (flashes() as $flash): ?>
                    <div class="p-3 text-sm rounded-xl <?= $flash['type'] === 'error' || $flash['type'] === 'danger' ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700' ?>">
                        <?= e($flash['message']) ?>
                    </div>
                <?php endforeach; ?>
                <?php if ((bool) app_config('app.disable_password_login')): ?>
                    <div class="p-3 text-sm rounded-xl bg-amber-50 text-amber-800 border border-amber-200">
                        Password login is disabled on this instance. Please sign in with Google below.
                    </div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="p-3 text-sm rounded-xl bg-red-50 text-red-700">
                        <?= e($error) ?>
                    </div>
                <?php endif; ?>
                
                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">Work Email</label>
                    <input type="email" id="email" name="email" 
                           class="w-full px-3.5 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 focus:bg-white placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-brand-wa_light/10 focus:border-brand-wa_light transition-all duration-200" 
                           placeholder="name@company.com" 
                           required>
                </div>
                
                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">Password</label>
                    <div class="relative flex items-center">
                        <input type="password" id="password" name="password" 
                               class="w-full px-3.5 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 focus:bg-white placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-brand-wa_light/10 focus:border-brand-wa_light transition-all duration-200 pr-10" 
                               placeholder="••••••••" 
                               required>
                        <!-- Toggle Password Button -->
                        <button type="button" onclick="togglePassword()" class="absolute right-3 text-gray-400 hover:text-gray-600 transition-colors focus:outline-none flex items-center justify-center p-1" aria-label="Toggle password visibility">
                            <svg id="eye-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between pt-1 pb-2">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox" class="w-4 h-4 rounded border-gray-300 text-brand-wa_light focus:ring-brand-wa_light focus:ring-offset-0 transition-colors cursor-pointer">
                        <span class="text-sm font-medium text-gray-600 group-hover:text-gray-800 transition-colors">Remember me</span>
                    </label>
                    <a href="<?= e(base_url('index.php?page=forgot-password')) ?>" class="text-sm font-semibold text-brand-wa_med hover:text-brand-wa_dark transition-colors">Forgot password?</a>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-brand-wa_light hover:bg-[#1DA851] text-white font-semibold text-sm py-2.5 rounded-xl transition-all duration-200 shadow-sm shadow-brand-wa_light/20 active:scale-[0.98] flex justify-center items-center gap-2">
                    Log In
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </form>

            <!-- OR Divider -->
            <div class="my-3.5 flex items-center">
                <div class="flex-grow border-t border-gray-100"></div>
                <span class="mx-4 text-xs font-semibold text-gray-400 uppercase tracking-widest">Or</span>
                <div class="flex-grow border-t border-gray-100"></div>
            </div>

            <!-- Google Login Button -->
            <a href="<?= e(base_url('google-auth')) ?>" class="w-full flex items-center justify-center gap-3 bg-white border border-gray-200 hover:bg-gray-50 hover:border-gray-300 text-gray-700 font-semibold text-sm py-2 rounded-xl transition-all duration-200 active:scale-[0.98]">
                <svg class="w-4 h-4" viewBox="0 0 24 24">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                Sign in with Google
            </a>
            <?php endif; ?>

            <?php if (app_config('app.enable_registration')): ?>
            <!-- Footer -->
            <p class="mt-4 text-center text-xs sm:text-sm font-medium text-gray-500">
                Don't have an account? 
                <a href="<?= e(base_url('register')) ?>" class="font-semibold text-brand-wa_med hover:text-brand-wa_dark transition-colors">Sign up</a>
            <?php endif; ?>
            </div>

        </div>
    </div>

    <script>
        // Password Visibility Toggle Logic
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                // Eye-off icon path
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                `;
            } else {
                passwordInput.type = 'password';
                // Standard eye icon path
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                `;
            }
        }

        </script>
</body>
</html>
<?php exit; ?>
