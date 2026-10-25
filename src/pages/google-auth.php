<?php

declare(strict_types=1);

$clientId = app_config('google.client_id');
$clientSecret = app_config('google.client_secret');
$redirectUri = public_url('index.php?page=google-auth');

if (!$clientId || !$clientSecret) {
    flash('error', 'Google OAuth is not configured on this instance.');
    redirect_to('index.php?page=login');
}

// 1. Authenticated user initiating Google Account Linking from Security Settings
if (isset($_GET['action']) && $_GET['action'] === 'link') {
    $user = require_auth();
    $_SESSION['oauth_state'] = 'link_' . bin2hex(random_bytes(24));
    $_SESSION['oauth_link_user_id'] = (int) $user['id'];

    $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
        'client_id' => $clientId,
        'redirect_uri' => $redirectUri,
        'response_type' => 'code',
        'scope' => 'openid email profile',
        'state' => $_SESSION['oauth_state'],
        'access_type' => 'online',
        'prompt' => 'select_account'
    ]);

    header('Location: ' . $authUrl);
    exit;
}

// If already logged in and not processing a callback, go to dashboard
if (current_user() && !isset($_GET['code'])) {
    redirect_to('index.php?page=dashboard');
}

// Handle Google Callback
if (isset($_GET['code'])) {
    $state = (string) ($_GET['state'] ?? '');
    
    // Verify State Token (CSRF Protection)
    if (empty($_SESSION['oauth_state']) || !hash_equals($_SESSION['oauth_state'], $state)) {
        flash('error', 'Invalid security state. Please try again.');
        redirect_to('index.php?page=login');
    }
    $isLinkFlow = str_starts_with($state, 'link_');
    unset($_SESSION['oauth_state']);

    $code = (string) $_GET['code'];

    // Exchange Code for Access Token
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'redirect_uri' => $redirectUri,
        'grant_type' => 'authorization_code',
        'code' => $code,
    ]));
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $tokenData = json_decode((string) $response, true);
    if ($status !== 200 || empty($tokenData['access_token'])) {
        $errorMessage = $tokenData['error_description'] ?? $tokenData['error'] ?? 'Failed to authenticate with Google.';
        flash('error', 'Google Error: ' . $errorMessage);
        redirect_to('index.php?page=login');
    }

    $accessToken = $tokenData['access_token'];

    // Retrieve User Profile
    $ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken
    ]);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $userInfo = json_decode((string) $response, true);
    if ($status !== 200 || empty($userInfo['email'])) {
        flash('error', 'Failed to retrieve user information from Google.');
        redirect_to('index.php?page=login');
    }

    $email = mb_strtolower(trim((string) $userInfo['email']));
    $name = trim((string) ($userInfo['name'] ?? 'Google User'));
    $googleId = (string) ($userInfo['id'] ?? '');

    // Case A: Link Flow from Security Settings
    if ($isLinkFlow) {
        $linkUserId = (int) ($_SESSION['oauth_link_user_id'] ?? 0);
        unset($_SESSION['oauth_link_user_id']);
        $currentUser = current_user();

        if (!$currentUser || (int) $currentUser['id'] !== $linkUserId) {
            flash('error', 'Session mismatch while linking Google account.');
            redirect_to('index.php?page=security');
        }

        link_google_account($linkUserId, $googleId, $email);
        flash('success', "Google account ({$email}) connected successfully.");
        redirect_to('index.php?page=security');
    }

    // Case B: Standard Sign-In
    $pdo = Database::pdo();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $sec = get_user_security((int) $user['id']);

        // Delink safeguard: If user explicitly unlinked Google and has password set
        if (isset($sec['google_linked']) && (int) $sec['google_linked'] === 0 && !empty($sec['password_set'])) {
            flash('error', 'Google sign-in has been disconnected for this account. Please sign in with your email and password.');
            redirect_to('index.php?page=login');
        }

        clear_failed_logins($email);
        session_regenerate_id(true);

        // 2FA TOTP verification challenge
        if (!empty($sec['totp_enabled'])) {
            $_SESSION['2fa_pending_user_id'] = (int) $user['id'];
            redirect_to('index.php?page=login&step=2fa');
        }

        $_SESSION['user_id'] = (int) $user['id'];
        redirect_to('index.php?page=dashboard');
    } else {
        // Check if registration is permitted
        if (!app_config('app.enable_registration')) {
            flash('error', (string) app_config('app.restricted_notice'));
            redirect_to('index.php?page=login');
        }

        $randomPassword = bin2hex(random_bytes(16));
        $phone = '';
        
        try {
            $credentials = register_user($name, $email, $phone, $randomPassword);
            clear_failed_logins($email);

            $newUser = current_user();
            if ($newUser) {
                link_google_account((int) $newUser['id'], $googleId, $email);
                Database::pdo()->prepare('UPDATE user_security SET password_set = 0 WHERE user_id = ?')->execute([(int) $newUser['id']]);
            }

            flash('success', 'Account created successfully via Google.');
            redirect_to('index.php?page=dashboard');
        } catch (Throwable $e) {
            flash('error', 'Failed to create account. Email might already be registered.');
            redirect_to('index.php?page=login');
        }
    }
}

// If error from Google
if (isset($_GET['error'])) {
    flash('error', 'Google authentication failed: ' . htmlspecialchars((string) $_GET['error']));
    redirect_to('index.php?page=login');
}

// Initiate Standard Google OAuth Flow
$_SESSION['oauth_state'] = bin2hex(random_bytes(32));

$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id' => $clientId,
    'redirect_uri' => $redirectUri,
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'state' => $_SESSION['oauth_state'],
    'access_type' => 'online',
    'prompt' => 'select_account'
]);

header('Location: ' . $authUrl);
exit;
