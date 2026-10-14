<?php

declare(strict_types=1);

$user = current_user();
if (!$user) {
    flash('error', 'Please log in to import your Google Contacts.');
    redirect_to('index.php?page=login');
}

$clientId = app_config('google.client_id');
$clientSecret = app_config('google.client_secret');

if (!$clientId || !$clientSecret) {
    flash('error', 'Google OAuth credentials are not configured in your application settings.');
    redirect_to('index.php?page=contacts');
}

$redirectUri = public_url('index.php?page=google-contacts');

// Check for Error from Google
if (isset($_GET['error'])) {
    flash('error', 'Google access denied or cancelled: ' . htmlspecialchars((string) $_GET['error']));
    redirect_to('index.php?page=contacts');
}

// Handle Google Callback
if (isset($_GET['code'])) {
    $state = (string) ($_GET['state'] ?? '');

    // Verify State Token (CSRF Protection)
    if (empty($_SESSION['google_contacts_state']) || !hash_equals($_SESSION['google_contacts_state'], $state)) {
        flash('error', 'Invalid security state. Please try importing again.');
        redirect_to('index.php?page=contacts');
    }
    unset($_SESSION['google_contacts_state']);

    $code = (string) $_GET['code'];

    // 1. Exchange Code for Access Token
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
        $errorMessage = $tokenData['error_description'] ?? $tokenData['error'] ?? 'Failed to exchange authorization token with Google.';
        flash('error', 'Google Error: ' . $errorMessage);
        redirect_to('index.php?page=contacts');
    }

    $accessToken = (string) $tokenData['access_token'];

    // 2. Fetch Contacts via Google People API
    $imported = 0;
    $skipped = 0;
    $nextPageToken = '';
    $pdo = Database::pdo();
    $stmt = $pdo->prepare(
        'INSERT INTO contacts (user_id, name, phone, normalized_phone, opt_in, notes) VALUES (?, ?, ?, ?, 1, ?)
         ON DUPLICATE KEY UPDATE name = VALUES(name), opt_in = VALUES(opt_in)'
    );

    do {
        $queryParams = [
            'personFields' => 'names,phoneNumbers',
            'pageSize' => 1000,
        ];
        if ($nextPageToken !== '') {
            $queryParams['pageToken'] = $nextPageToken;
        }

        $apiUrl = 'https://people.googleapis.com/v1/people/me/connections?' . http_build_query($queryParams);

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Accept: application/json',
        ]);
        $apiResponse = curl_exec($ch);
        $apiStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($apiStatus !== 200) {
            $errData = json_decode((string) $apiResponse, true);
            $msg = $errData['error']['message'] ?? "Google People API request failed with status {$apiStatus}";
            flash('error', 'Google Contacts Error: ' . $msg);
            redirect_to('index.php?page=contacts');
        }

        $data = json_decode((string) $apiResponse, true);
        $connections = $data['connections'] ?? [];

        foreach ($connections as $person) {
            $names = $person['names'] ?? [];
            $displayName = !empty($names[0]['displayName']) ? trim((string) $names[0]['displayName']) : '';

            $phoneNumbers = $person['phoneNumbers'] ?? [];
            foreach ($phoneNumbers as $phoneObj) {
                $rawPhone = trim((string) ($phoneObj['value'] ?? ''));
                if ($rawPhone === '') {
                    continue;
                }

                // Check digit length
                $digitsOnly = preg_replace('/\D/', '', $rawPhone);
                if (strlen($digitsOnly) < 7 || strlen($digitsOnly) > 16) {
                    $skipped++;
                    continue;
                }

                $normalized = normalize_phone($rawPhone);
                $name = $displayName !== '' ? $displayName : $normalized;

                try {
                    $stmt->execute([
                        (int) $user['id'],
                        $name,
                        $normalized,
                        $normalized,
                        'Imported via Google Contacts'
                    ]);
                    $imported++;
                } catch (Throwable $e) {
                    $skipped++;
                }
            }
        }

        $nextPageToken = (string) ($data['nextPageToken'] ?? '');
    } while ($nextPageToken !== '');

    if ($imported > 0) {
        $msg = "Successfully imported {$imported} contact(s) from your Google account!";
        if ($skipped > 0) {
            $msg .= " ({$skipped} entries skipped due to missing or invalid phone numbers)";
        }
        flash('success', $msg);
    } else {
        flash('error', 'No contacts with valid phone numbers were found in your Google account.');
    }

    redirect_to('index.php?page=contacts');
}

// 3. Initiate Google OAuth Flow for Contacts
$_SESSION['google_contacts_state'] = bin2hex(random_bytes(32));

$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id' => $clientId,
    'redirect_uri' => $redirectUri,
    'response_type' => 'code',
    'scope' => 'https://www.googleapis.com/auth/contacts.readonly',
    'state' => $_SESSION['google_contacts_state'],
    'access_type' => 'online',
    'prompt' => 'consent select_account'
]);

header('Location: ' . $authUrl);
exit;
