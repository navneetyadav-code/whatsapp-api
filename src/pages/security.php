<?php

declare(strict_types=1);

$user = require_auth();
$userId = (int) $user['id'];
$sec = get_user_security($userId);

// Process POST security actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'change_password') {
        $currentPassword = (string) ($_POST['current_password'] ?? '');
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

        $stmt = Database::pdo()->prepare('SELECT password_hash FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $currUser = $stmt->fetch();

        if (empty($currUser['password_hash']) || !password_verify($currentPassword, (string) $currUser['password_hash'])) {
            flash('error', 'Your current password was entered incorrectly.');
            redirect_to('index.php?page=security');
        }

        if (strlen($newPassword) < 8) {
            flash('error', 'The new password must be at least 8 characters long.');
            redirect_to('index.php?page=security');
        }

        if ($newPassword !== $confirmPassword) {
            flash('error', 'The new password confirmation does not match.');
            redirect_to('index.php?page=security');
        }

        update_user_password($userId, $newPassword);
        flash('success', 'Your account password has been updated.');
        redirect_to('index.php?page=security');
    }

    if ($action === 'set_password') {
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

        if (strlen($newPassword) < 8) {
            flash('error', 'The password must be at least 8 characters long.');
            redirect_to('index.php?page=security');
        }

        if ($newPassword !== $confirmPassword) {
            flash('error', 'The password confirmation does not match.');
            redirect_to('index.php?page=security');
        }

        update_user_password($userId, $newPassword);
        flash('success', 'Password set successfully. You can now use your password or Google to sign in.');
        redirect_to('index.php?page=security');
    }

    if ($action === 'confirm_totp') {
        $code = trim((string) ($_POST['totp_code'] ?? ''));
        $secret = trim((string) ($_SESSION['pending_totp_secret'] ?? ''));

        if ($secret === '' || !totp_verify($secret, $code)) {
            flash('error', 'Invalid 6-digit verification code. Please check your authenticator app and try again.');
            redirect_to('index.php?page=security&setup_2fa=1');
        }

        $backupCodes = enable_user_totp($userId, $secret);
        unset($_SESSION['pending_totp_secret']);
        $_SESSION['newly_generated_backup_codes'] = $backupCodes;
        flash('success', 'Two-factor authentication (TOTP) has been enabled.');
        redirect_to('index.php?page=security');
    }

    if ($action === 'disable_totp') {
        $password = (string) ($_POST['password'] ?? '');
        $code = trim((string) ($_POST['totp_code'] ?? ''));

        $stmt = Database::pdo()->prepare('SELECT password_hash FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $currUser = $stmt->fetch();

        $authorized = false;
        if (!empty($currUser['password_hash']) && password_verify($password, (string) $currUser['password_hash'])) {
            $authorized = true;
        } elseif (!empty($sec['totp_secret']) && totp_verify($sec['totp_secret'], $code)) {
            $authorized = true;
        }

        if (!$authorized) {
            flash('error', 'Confirmation failed. Enter your current password or an authenticator code to disable 2FA.');
            redirect_to('index.php?page=security');
        }

        disable_user_totp($userId);
        flash('success', 'Two-factor authentication has been disabled.');
        redirect_to('index.php?page=security');
    }

    if ($action === 'regenerate_backup_codes') {
        if (empty($sec['totp_enabled'])) {
            flash('error', 'Two-factor authentication must be enabled to generate backup codes.');
            redirect_to('index.php?page=security');
        }

        $password = (string) ($_POST['password'] ?? '');
        $code = trim((string) ($_POST['totp_code'] ?? ''));

        $stmt = Database::pdo()->prepare('SELECT password_hash FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $currUser = $stmt->fetch();

        $authorized = false;
        if (!empty($currUser['password_hash']) && password_verify($password, (string) $currUser['password_hash'])) {
            $authorized = true;
        } elseif (!empty($sec['totp_secret']) && totp_verify($sec['totp_secret'], $code)) {
            $authorized = true;
        }

        if (!$authorized) {
            flash('error', 'Confirmation failed. Enter your password or 6-digit TOTP code to regenerate recovery codes.');
            redirect_to('index.php?page=security');
        }

        $backup = generate_backup_codes(8);
        $stmt = Database::pdo()->prepare('UPDATE user_security SET backup_codes = ? WHERE user_id = ?');
        $stmt->execute([json_encode($backup['hashed']), $userId]);
        $_SESSION['newly_generated_backup_codes'] = $backup['plain'];

        flash('success', 'New recovery backup codes generated. Previous codes have been invalidated.');
        redirect_to('index.php?page=security');
    }

    if ($action === 'unlink_google') {
        if (!can_unlink_google($userId)) {
            flash('error', 'Cannot disconnect Google account: You must set an account password first so you do not get locked out.');
            redirect_to('index.php?page=security');
        }

        unlink_google_account($userId);
        flash('success', 'Google account disconnected successfully. You can now sign in using your email and password.');
        redirect_to('index.php?page=security');
    }

    redirect_to('index.php?page=security');
}

// Read refreshed state
$sec = get_user_security($userId);
$userStmt = Database::pdo()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
$userStmt->execute([$userId]);
$user = $userStmt->fetch();

$hasPassword = !empty($user['password_hash']) && !empty($sec['password_set']);
$isGoogleLinked = !empty($sec['google_linked']);
$isTotpEnabled = !empty($sec['totp_enabled']);

$existingBackupCodesCount = 0;
if (!empty($sec['backup_codes'])) {
    $existingCodes = json_decode((string) $sec['backup_codes'], true) ?: [];
    $existingBackupCodesCount = count($existingCodes);
}

$showSetup2FA = isset($_GET['setup_2fa']) && !$isTotpEnabled;
$pendingSecret = '';
$totpUri = '';

if ($showSetup2FA) {
    if (empty($_SESSION['pending_totp_secret'])) {
        $_SESSION['pending_totp_secret'] = totp_generate_secret(20);
    }
    $pendingSecret = (string) $_SESSION['pending_totp_secret'];
    $totpUri = totp_uri((string) $user['email'], $pendingSecret, 'WhatsGateway');
} else {
    unset($_SESSION['pending_totp_secret']);
}

$newlyGeneratedBackupCodes = $_SESSION['newly_generated_backup_codes'] ?? null;
unset($_SESSION['newly_generated_backup_codes']);
?>

<div class="space-y-6">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200/80">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">
                <span>Account Settings</span>
                <span>/</span>
                <span class="text-slate-600">Security</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Security & Authentication</h1>
            <p class="text-sm text-slate-500 mt-1">Configure two-factor authentication, manage multiple sign-in providers, and protect your account.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="<?= e(base_url('index.php?page=audit')) ?>" class="inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg shadow-sm hover:bg-slate-50 transition-colors">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Audit Logs
            </a>
        </div>
    </div>

    <?php if (!empty($newlyGeneratedBackupCodes)): ?>
    <!-- Backup Codes Banner -->
    <div class="p-6 bg-amber-50/70 border border-amber-200 rounded-xl shadow-saas">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div class="flex-1">
                <h2 class="text-base font-bold text-amber-900 tracking-tight">Save Your Single-Use Recovery Backup Codes</h2>
                <p class="text-sm text-amber-800 mt-1">If you lose access to your authenticator app, these recovery codes are the <strong>only way</strong> to regain access to your account. Each code can be used once. Save them in a secure password manager.</p>
                
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 my-4">
                    <?php foreach ($newlyGeneratedBackupCodes as $bcode): ?>
                        <div class="p-2.5 bg-white border border-amber-200/80 rounded-lg font-mono text-center text-sm font-bold tracking-widest text-slate-800 select-all shadow-xs">
                            <?= e($bcode) ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="flex flex-wrap items-center gap-3 pt-1">
                    <button type="button" onclick="copyBackupCodes()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-amber-900 bg-amber-100 hover:bg-amber-200 rounded-lg transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        Copy All Codes
                    </button>
                    <button type="button" onclick="downloadBackupCodes()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-amber-900 bg-amber-100 hover:bg-amber-200 rounded-lg transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Download (.txt)
                    </button>
                    <span id="copyFeedback" class="text-xs font-semibold text-emerald-700 hidden">Copied to clipboard!</span>
                </div>
            </div>
        </div>
    </div>
    <script>
        const backupCodesList = <?= json_encode($newlyGeneratedBackupCodes) ?>;
        function copyBackupCodes() {
            const text = backupCodesList.join('\n');
            navigator.clipboard.writeText(text).then(() => {
                const fb = document.getElementById('copyFeedback');
                fb.classList.remove('hidden');
                setTimeout(() => fb.classList.add('hidden'), 3000);
            });
        }
        function downloadBackupCodes() {
            const text = "WhatsGateway Security Recovery Backup Codes\nAccount: <?= e($user['email']) ?>\nDate: " + new Date().toISOString() + "\n\n" + backupCodesList.join('\n') + "\n\nEach code can only be used once.";
            const blob = new Blob([text], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'whatsgateway-backup-codes.txt';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }
    </script>
    <?php endif; ?>

    <!-- Status Strip: Posture Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="p-3.5 bg-white border border-slate-200/80 rounded-xl shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Two-Factor Authentication</span>
                <p class="text-sm font-bold text-slate-900 mt-0.5"><?= $isTotpEnabled ? 'Active (RFC 6238)' : 'Disabled' ?></p>
            </div>
            <span class="w-2.5 h-2.5 rounded-full <?= $isTotpEnabled ? 'bg-emerald-500 ring-4 ring-emerald-100' : 'bg-slate-300' ?>"></span>
        </div>

        <div class="p-3.5 bg-white border border-slate-200/80 rounded-xl shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Password Sign-In</span>
                <p class="text-sm font-bold text-slate-900 mt-0.5"><?= $hasPassword ? 'Password Configured' : 'No Password Set' ?></p>
            </div>
            <span class="w-2.5 h-2.5 rounded-full <?= $hasPassword ? 'bg-emerald-500 ring-4 ring-emerald-100' : 'bg-amber-400 ring-4 ring-amber-100' ?>"></span>
        </div>

        <div class="p-3.5 bg-white border border-slate-200/80 rounded-xl shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Google OAuth SSO</span>
                <p class="text-sm font-bold text-slate-900 mt-0.5"><?= $isGoogleLinked ? 'Account Linked' : 'Disconnected' ?></p>
            </div>
            <span class="w-2.5 h-2.5 rounded-full <?= $isGoogleLinked ? 'bg-blue-500 ring-4 ring-blue-100' : 'bg-slate-300' ?>"></span>
        </div>
    </div>

    <!-- Unified Master Settings Panel (Linear/Stripe Style, NOT Cards) -->
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-saas divide-y divide-slate-100 overflow-hidden">

        <!-- ==================== SECTION 1: TWO-FACTOR AUTHENTICATION ==================== -->
        <div class="p-6 lg:p-8">
            <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
                <div class="max-w-xl">
                    <div class="flex items-center gap-2 mb-1.5">
                        <div class="w-6 h-6 rounded-md bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <h2 class="text-base font-bold text-slate-900 tracking-tight">Two-Factor Authentication (TOTP)</h2>
                        <?php if ($isTotpEnabled): ?>
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/60">Enabled</span>
                        <?php else: ?>
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-slate-100 text-slate-600 border border-slate-200/60">Off</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-sm text-slate-500 leading-relaxed">
                        Protect your account with standard RFC 6238 time-based one-time passwords (TOTP). Use Google Authenticator, Authy, Microsoft Authenticator, or 1Password to generate 6-digit verification codes required on sign-in.
                    </p>
                </div>

                <div class="shrink-0 flex items-center gap-2">
                    <?php if ($isTotpEnabled): ?>
                        <button type="button" onclick="toggleElement('regenerateCodesDrawer')" class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg shadow-xs transition-colors">
                            Regenerate Backup Codes
                        </button>
                        <button type="button" onclick="toggleElement('disableTotpDrawer')" class="px-3.5 py-2 text-xs font-semibold text-rose-700 bg-rose-50/80 border border-rose-200 hover:bg-rose-100 rounded-lg transition-colors">
                            Disable 2FA
                        </button>
                    <?php elseif (!$showSetup2FA): ?>
                        <a href="<?= e(base_url('index.php?page=security&setup_2fa=1')) ?>" class="px-4 py-2 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-lg shadow-sm transition-colors flex items-center gap-2">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Enable Authenticator
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($isTotpEnabled): ?>
            <!-- Enabled Details -->
            <div class="mt-4 pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between text-xs text-slate-500 gap-2">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Verification code verified on login</span>
                </div>
                <div>
                    <span class="font-semibold text-slate-700"><?= $existingBackupCodesCount ?></span> single-use backup recovery codes active
                </div>
            </div>

            <!-- Regenerate Backup Codes Form (Collapsible) -->
            <div id="regenerateCodesDrawer" class="hidden mt-4 p-5 bg-slate-50 border border-slate-200/80 rounded-xl">
                <h3 class="text-sm font-bold text-slate-900 mb-1">Regenerate Recovery Backup Codes</h3>
                <p class="text-xs text-slate-500 mb-3">Confirm your password or a 6-digit TOTP code to generate 8 new single-use backup codes. Any previous codes will become invalid.</p>
                <form method="post" class="flex flex-wrap items-center gap-3">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="action" value="regenerate_backup_codes">
                    <?php if ($hasPassword): ?>
                        <input type="password" name="password" placeholder="Account password" class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 bg-white w-56 focus:outline-none focus:ring-2 focus:ring-slate-300">
                    <?php else: ?>
                        <input type="text" name="totp_code" placeholder="6-digit TOTP code" class="px-3 py-1.5 text-xs font-mono rounded-lg border border-slate-200 bg-white w-44 focus:outline-none focus:ring-2 focus:ring-slate-300">
                    <?php endif; ?>
                    <button type="submit" class="px-3.5 py-1.5 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-lg transition-colors">
                        Confirm & Regenerate
                    </button>
                    <button type="button" onclick="toggleElement('regenerateCodesDrawer')" class="text-xs text-slate-500 hover:text-slate-800">
                        Cancel
                    </button>
                </form>
            </div>

            <!-- Disable 2FA Form (Collapsible) -->
            <div id="disableTotpDrawer" class="hidden mt-4 p-5 bg-rose-50/50 border border-rose-200/80 rounded-xl">
                <h3 class="text-sm font-bold text-rose-900 mb-1">Disable Two-Factor Authentication</h3>
                <p class="text-xs text-rose-700 mb-3">Disabling 2FA makes your account significantly more vulnerable to credential reuse and brute force attacks. Enter your password or 6-digit TOTP code to proceed.</p>
                <form method="post" class="flex flex-wrap items-center gap-3">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="action" value="disable_totp">
                    <?php if ($hasPassword): ?>
                        <input type="password" name="password" placeholder="Account password" class="px-3 py-1.5 text-xs rounded-lg border border-rose-200 bg-white w-56 focus:outline-none focus:ring-2 focus:ring-rose-300" required>
                    <?php else: ?>
                        <input type="text" name="totp_code" placeholder="6-digit TOTP code" class="px-3 py-1.5 text-xs font-mono rounded-lg border border-rose-200 bg-white w-44 focus:outline-none focus:ring-2 focus:ring-rose-300" required>
                    <?php endif; ?>
                    <button type="submit" class="px-3.5 py-1.5 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-lg transition-colors">
                        Disable 2FA
                    </button>
                    <button type="button" onclick="toggleElement('disableTotpDrawer')" class="text-xs text-slate-500 hover:text-slate-800">
                        Cancel
                    </button>
                </form>
            </div>
            <?php endif; ?>

            <?php if ($showSetup2FA): ?>
            <!-- 2FA Interactive Setup Flow -->
            <div class="mt-6 p-6 bg-slate-50/80 border border-slate-200/80 rounded-xl space-y-6">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 tracking-tight">Step 1: Scan QR Code or Enter Secret Key</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Open Google Authenticator, Authy, or 1Password on your mobile device and scan the QR code below.</p>
                </div>

                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
                    <!-- QR Code Canvas Container -->
                    <div class="p-3 bg-white border border-slate-200 rounded-xl shadow-xs shrink-0 flex items-center justify-center">
                        <div id="totpQrcode" class="w-40 h-40"></div>
                    </div>

                    <div class="space-y-3 flex-1">
                        <div>
                            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Manual Secret Key</span>
                            <div class="flex items-center gap-2 mt-1">
                                <code id="secretKeyText" class="px-3 py-1.5 bg-white border border-slate-200 font-mono text-sm font-bold text-slate-800 rounded-lg tracking-widest select-all">
                                    <?= e(chunk_split($pendingSecret, 4, ' ')) ?>
                                </code>
                                <button type="button" onclick="copySecretKey()" class="px-2.5 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg shadow-xs transition-colors">
                                    Copy
                                </button>
                                <span id="secretCopiedFb" class="text-xs font-semibold text-emerald-600 hidden">Copied!</span>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">If you cannot scan the QR code, select "Enter a setup key" in your authenticator app and enter the key above.</p>
                        </div>

                        <!-- Step 2 Confirmation Form -->
                        <div class="pt-2">
                            <h3 class="text-sm font-bold text-slate-900 tracking-tight mb-1">Step 2: Enter 6-Digit Code</h3>
                            <form method="post" class="flex flex-wrap items-center gap-3">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="action" value="confirm_totp">
                                <input type="text" name="totp_code" 
                                       placeholder="123456" 
                                       class="px-3 py-2 text-sm font-mono tracking-widest text-center w-36 rounded-lg border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500" 
                                       inputmode="numeric" maxlength="6" required autofocus>
                                <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition-colors">
                                    Verify & Activate 2FA
                                </button>
                                <a href="<?= e(base_url('index.php?page=security')) ?>" class="text-xs text-slate-500 hover:text-slate-800">
                                    Cancel
                                </a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Client-side QR Code script (offline, zero-CDN dependency) -->
            <script src="<?= e(base_url('assets/qrcode.min.js')) ?>"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const container = document.getElementById("totpQrcode");
                    if (container && typeof QRCode !== 'undefined') {
                        new QRCode(container, {
                            text: <?= json_encode($totpUri) ?>,
                            width: 160,
                            height: 160,
                            colorDark: "#0F172A",
                            colorLight: "#ffffff",
                            correctLevel: QRCode.CorrectLevel.M
                        });
                    }
                });

                function copySecretKey() {
                    const raw = <?= json_encode($pendingSecret) ?>;
                    navigator.clipboard.writeText(raw).then(() => {
                        const fb = document.getElementById('secretCopiedFb');
                        fb.classList.remove('hidden');
                        setTimeout(() => fb.classList.add('hidden'), 2500);
                    });
                }
            </script>
            <?php endif; ?>
        </div>

        <!-- ==================== SECTION 2: AUTHENTICATION PROVIDERS ==================== -->
        <div class="p-6 lg:p-8">
            <div class="max-w-xl mb-6">
                <div class="flex items-center gap-2 mb-1.5">
                    <div class="w-6 h-6 rounded-md bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    </div>
                    <h2 class="text-base font-bold text-slate-900 tracking-tight">Authentication Providers</h2>
                </div>
                <p class="text-sm text-slate-500 leading-relaxed">
                    You can sign in using multiple methods simultaneously. Link your Google account for one-click single sign-on, or disconnect it and use your email and password.
                </p>
            </div>

            <!-- Provider Sub-Rows -->
            <div class="space-y-4">
                
                <!-- 2A. Google SSO Provider -->
                <div class="p-4 bg-slate-50/60 border border-slate-200/70 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-white border border-slate-200 flex items-center justify-center shrink-0 shadow-xs">
                            <svg class="w-5 h-5" viewBox="0 0 24 24">
                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-bold text-slate-900">Google OAuth 2.0</h3>
                                <?php if ($isGoogleLinked): ?>
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/60">Connected</span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-slate-100 text-slate-500 border border-slate-200/60">Disconnected</span>
                                <?php endif; ?>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">
                                <?= $isGoogleLinked ? 'Linked to: ' . e($sec['google_email'] ?: $user['email']) : 'Sign in directly with your Google Workspace or Gmail account.' ?>
                            </p>
                        </div>
                    </div>

                    <div class="shrink-0 flex items-center gap-2">
                        <?php if ($isGoogleLinked): ?>
                            <?php if ($hasPassword): ?>
                                <form method="post" onsubmit="return confirm('Disconnect Google sign-in? You will use your email and password to sign in.');">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="unlink_google">
                                    <button type="submit" class="px-3.5 py-2 text-xs font-semibold text-rose-700 bg-white border border-rose-200 hover:bg-rose-50 rounded-lg shadow-xs transition-colors">
                                        Disconnect Google
                                    </button>
                                </form>
                            <?php else: ?>
                                <button type="button" disabled title="Set a password below before disconnecting Google to prevent lockout." class="px-3.5 py-2 text-xs font-semibold text-slate-400 bg-slate-100 border border-slate-200 rounded-lg cursor-not-allowed">
                                    Disconnect Google (Set Password First)
                                </button>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="<?= e(base_url('google-auth?action=link')) ?>" class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg shadow-xs transition-colors flex items-center gap-1.5">
                                Connect Google Account
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 2B. Email and Password Authentication -->
                <div class="p-4 bg-slate-50/60 border border-slate-200/70 rounded-xl">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-white border border-slate-200 flex items-center justify-center shrink-0 shadow-xs text-slate-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-bold text-slate-900">Email & Password</h3>
                                    <?php if ($hasPassword): ?>
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/60">Configured</span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-50 text-amber-700 border border-amber-200/60">Not Set</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Sign in directly with your email address (<strong class="text-slate-700"><?= e($user['email']) ?></strong>) and account password.
                                </p>
                            </div>
                        </div>

                        <div class="shrink-0">
                            <?php if ($hasPassword): ?>
                                <button type="button" onclick="toggleElement('changePasswordForm')" class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg shadow-xs transition-colors">
                                    Change Password
                                </button>
                            <?php else: ?>
                                <button type="button" onclick="toggleElement('setPasswordForm')" class="px-3.5 py-2 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-lg shadow-xs transition-colors">
                                    Set Initial Password
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Change Password Inline Form -->
                    <div id="changePasswordForm" class="hidden mt-4 pt-4 border-t border-slate-200/80">
                        <form method="post" class="max-w-md space-y-3">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="action" value="change_password">

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Current Password</label>
                                <input type="password" name="current_password" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-slate-300" required>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">New Password (Min 8 characters)</label>
                                <input type="password" name="new_password" minlength="8" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-slate-300" required>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Confirm New Password</label>
                                <input type="password" name="confirm_password" minlength="8" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-slate-300" required>
                            </div>

                            <div class="flex items-center gap-2 pt-1">
                                <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-lg transition-colors">
                                    Save Password
                                </button>
                                <button type="button" onclick="toggleElement('changePasswordForm')" class="px-3 py-2 text-xs text-slate-500 hover:text-slate-800">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Set Initial Password Form (for Google OAuth users) -->
                    <div id="setPasswordForm" class="<?= !$hasPassword ? '' : 'hidden' ?> mt-4 pt-4 border-t border-slate-200/80">
                        <div class="p-3 mb-3 bg-blue-50/70 border border-blue-200/60 rounded-lg text-xs text-blue-800 leading-relaxed">
                            Setting an account password enables password sign-in and unlocks the ability to disconnect Google OAuth whenever desired.
                        </div>
                        <form method="post" class="max-w-md space-y-3">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="action" value="set_password">

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">New Password (Min 8 characters)</label>
                                <input type="password" name="new_password" minlength="8" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-slate-300" placeholder="••••••••" required>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Confirm Password</label>
                                <input type="password" name="confirm_password" minlength="8" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-slate-300" placeholder="••••••••" required>
                            </div>

                            <div class="flex items-center gap-2 pt-1">
                                <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-lg transition-colors">
                                    Create Password
                                </button>
                                <?php if ($hasPassword): ?>
                                <button type="button" onclick="toggleElement('setPasswordForm')" class="px-3 py-2 text-xs text-slate-500 hover:text-slate-800">
                                    Cancel
                                </button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>

        <!-- ==================== SECTION 3: SESSION INTEGRITY & AUDITING ==================== -->
        <div class="p-6 lg:p-8 bg-slate-50/40">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 tracking-tight">Active Session & Security Parameters</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Real-time session metadata and security audit controls.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-white border border-slate-200 text-slate-700 shadow-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        IP: <?= e(client_ip()) ?>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-white border border-slate-200 text-slate-700 shadow-xs">
                        CSRF Enforced
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-white border border-slate-200 text-slate-700 shadow-xs">
                        Bcrypt / SHA-256
                    </span>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function toggleElement(id) {
    const el = document.getElementById(id);
    if (el) {
        el.classList.toggle('hidden');
    }
}
</script>
