<?php

declare(strict_types=1);

/**
 * Unit & Integration Tests: Security Features & Two-Factor Authentication
 *
 * Verifies RFC 6238 TOTP math, RFC 4648 Base32 encoding/decoding,
 * single-use backup code verification & consumption, Google account delink
 * lockout prevention guardrails, password update enforcement, and 2FA login challenge.
 */

require_once dirname(__DIR__) . '/src/bootstrap.php';

echo "Running Security Features & 2FA unit tests...\n";

function sec_test_assert(bool $cond, string $msg): void {
    if (!$cond) {
        fwrite(STDERR, "FAIL: $msg\n");
        exit(1);
    }
}

// -----------------------------------------------------------------------------
// Test 1: Base32 Encoding and Decoding (RFC 4648 vectors)
// -----------------------------------------------------------------------------
$testVectors = [
    '' => '',
    'f' => 'MY',
    'fo' => 'MZXQ',
    'foo' => 'MZXW6',
    'foob' => 'MZXW6YQ',
    'fooba' => 'MZXW6YTB',
    'foobar' => 'MZXW6YTBOI',
];

foreach ($testVectors as $plain => $expectedBase32) {
    $encoded = base32_encode_str($plain);
    $trimmedEncoded = rtrim($encoded, '=');
    sec_test_assert($trimmedEncoded === $expectedBase32, "Base32 encode failed for '$plain'. Got: $trimmedEncoded, Expected: $expectedBase32");
    
    $decoded = base32_decode_str($expectedBase32);
    sec_test_assert($decoded === $plain, "Base32 decode failed for '$expectedBase32'. Got: '$decoded', Expected: '$plain'");
}
echo "  ✔ Base32 RFC 4648 encoding/decoding verified.\n";

// -----------------------------------------------------------------------------
// Test 2: RFC 6238 TOTP Secret Generation and Code Mathematics
// -----------------------------------------------------------------------------
$secret = totp_generate_secret(20);
sec_test_assert(strlen($secret) === 32, "TOTP secret must be 32 base32 characters for 20 bytes. Got: " . strlen($secret));
sec_test_assert(preg_match('/^[A-Z2-7]+$/', $secret) === 1, "TOTP secret must strictly contain valid Base32 characters.");

$currentTimeSlice = (int) floor(time() / 30);
$currentCode = totp_code($secret, $currentTimeSlice);

sec_test_assert(strlen($currentCode) === 6, "TOTP code must be exactly 6 characters.");
sec_test_assert(ctype_digit($currentCode), "TOTP code must be strictly digits.");

// Verify exact match
sec_test_assert(totp_verify($secret, $currentCode, 0) === true, "Current TOTP code must verify with 0 drift.");

// Verify tolerance window (drift of 1 step forward and 1 step backward)
$prevCode = totp_code($secret, $currentTimeSlice - 1);
$nextCode = totp_code($secret, $currentTimeSlice + 1);
sec_test_assert(totp_verify($secret, $prevCode, 1) === true, "Previous time-slice must verify within 1-step window.");
sec_test_assert(totp_verify($secret, $nextCode, 1) === true, "Next time-slice must verify within 1-step window.");

// Verify expired codes outside the discrepancy window are rejected
$staleCode = totp_code($secret, $currentTimeSlice - 5);
sec_test_assert(totp_verify($secret, $staleCode, 1) === false, "Stale TOTP code outside window must be rejected.");

// Verify invalid formats
sec_test_assert(totp_verify($secret, 'abc123') === false, "Alphanumeric code must be rejected.");
sec_test_assert(totp_verify($secret, '12345') === false, "Code shorter than 6 digits must be rejected.");
sec_test_assert(totp_verify($secret, '1234567') === false, "Code longer than 6 digits must be rejected.");
sec_test_assert(totp_verify($secret, '') === false, "Empty code must be rejected.");

// Verify URI generation for Authenticator apps
$uri = totp_uri('admin@example.com', $secret, 'WhatsGateway');
sec_test_assert(str_starts_with($uri, 'otpauth://totp/WhatsGateway:admin%40example.com?'), "TOTP URI must follow RFC otpauth standard.");
sec_test_assert(str_contains($uri, 'secret=' . $secret), "TOTP URI must contain secret.");
echo "  ✔ RFC 6238 TOTP math, validation, and URI generation verified.\n";

// -----------------------------------------------------------------------------
// Test 3: Backup Recovery Codes (Generation, Verification & Single-Use Consumption)
// -----------------------------------------------------------------------------
$backup = generate_backup_codes(8, 4);
sec_test_assert(count($backup['plain']) === 8, "Must generate exactly 8 plain backup codes.");
sec_test_assert(count($backup['hashed']) === 8, "Must generate exactly 8 hashed backup codes.");

foreach ($backup['plain'] as $code) {
    sec_test_assert(strlen($code) === 8, "Each backup code must be 8 hex characters.");
    sec_test_assert(ctype_xdigit($code), "Each backup code must be valid hex.");
}

// In-memory PDO test double
$testPdo = new PDO('sqlite::memory:');
$testPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$testPdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT, password_hash TEXT)');

ensure_security_schema($testPdo);

// Create temporary test user
$testEmail = 'sec_test_' . bin2hex(random_bytes(4)) . '@test.local';
$testPdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)')
    ->execute(['Sec Test', $testEmail, password_hash('StrongPassword123!', PASSWORD_BCRYPT, ['cost' => 4])]);
$testUserId = (int) $testPdo->lastInsertId();

// 3a. Enable TOTP and test backup codes
$plainCodes = enable_user_totp($testUserId, $secret, $testPdo, 4);
sec_test_assert(count($plainCodes) === 8, "enable_user_totp must return 8 plain recovery codes.");

$sec = get_user_security($testUserId, $testPdo);
sec_test_assert((int) $sec['totp_enabled'] === 1, "totp_enabled must be 1.");
sec_test_assert($sec['totp_secret'] === $secret, "totp_secret must match.");

// Consume first code
$firstCode = $plainCodes[0];
$consume1 = verify_and_consume_backup_code($testUserId, $firstCode, $testPdo);
sec_test_assert($consume1 === true, "First valid backup code must verify and be consumed.");

// Attempt to reuse consumed code -> MUST FAIL (single-use defense)
$consumeReuse = verify_and_consume_backup_code($testUserId, $firstCode, $testPdo);
sec_test_assert($consumeReuse === false, "Re-using consumed backup code must be rejected.");

// Verify remaining count is 7
$secAfter = get_user_security($testUserId, $testPdo);
$remaining = json_decode((string) $secAfter['backup_codes'], true);
sec_test_assert(count($remaining) === 7, "Remaining backup codes count must be 7.");

// -----------------------------------------------------------------------------
// Test 4: Google Link & Delink Lockout Guardrail
// -----------------------------------------------------------------------------
// By default, test user has a password set
sec_test_assert(can_unlink_google($testUserId, $testPdo) === true, "User with password should be allowed to unlink Google.");

// Link Google
link_google_account($testUserId, 'google_id_12345', 'google_user@test.local', $testPdo);
$secLinked = get_user_security($testUserId, $testPdo);
sec_test_assert((int) $secLinked['google_linked'] === 1, "Google should be marked as linked.");
sec_test_assert($secLinked['google_email'] === 'google_user@test.local', "Google email should match.");

// Unlink Google
$unlinked = unlink_google_account($testUserId, $testPdo);
sec_test_assert($unlinked === true, "Unlinking Google should succeed when password is set.");
$secUnlinked = get_user_security($testUserId, $testPdo);
sec_test_assert((int) $secUnlinked['google_linked'] === 0, "Google should be marked as unlinked.");

// Lockout Guardrail: User WITHOUT password must NOT be allowed to unlink Google
$testPdo->prepare('UPDATE users SET password_hash = NULL WHERE id = ?')->execute([$testUserId]);
$testPdo->prepare('UPDATE user_security SET password_set = 0 WHERE user_id = ?')->execute([$testUserId]);

link_google_account($testUserId, 'google_id_12345', 'google_user@test.local', $testPdo);
sec_test_assert(can_unlink_google($testUserId, $testPdo) === false, "Lockout guardrail must block unlinking Google when no password is set.");
sec_test_assert(unlink_google_account($testUserId, $testPdo) === false, "unlink_google_account must refuse when can_unlink_google is false.");

// -----------------------------------------------------------------------------
// Test 5: Password Updates
// -----------------------------------------------------------------------------
// Short password must be rejected
$shortRejected = false;
try {
    update_user_password($testUserId, 'short', $testPdo);
} catch (InvalidArgumentException) {
    $shortRejected = true;
}
sec_test_assert($shortRejected, "Passwords under 8 characters must throw InvalidArgumentException.");

// Valid password
update_user_password($testUserId, 'NewSecurePassword123#', $testPdo);
$secPw = get_user_security($testUserId, $testPdo);
sec_test_assert((int) $secPw['password_set'] === 1, "password_set must be 1 after update_user_password.");

$checkUser = $testPdo->prepare('SELECT password_hash FROM users WHERE id = ?');
$checkUser->execute([$testUserId]);
$newHash = (string) $checkUser->fetchColumn();
sec_test_assert(password_verify('NewSecurePassword123#', $newHash) === true, "New password hash must verify.");
echo "  ✔ Database-backed 2FA persistence, backup codes consumption, and lockout guardrails verified.\n";

// -----------------------------------------------------------------------------
// Test 6: 2FA Session Challenge Lifecycle Functions
// -----------------------------------------------------------------------------
$_SESSION = [];
$_SESSION['2fa_pending_user_id'] = 42;
sec_test_assert(!isset($_SESSION['user_id']), "User ID should not be set prior to 2FA completion.");

complete_2fa_login(42);
sec_test_assert(isset($_SESSION['user_id']) && $_SESSION['user_id'] === 42, "complete_2fa_login must set session user_id.");
sec_test_assert(!isset($_SESSION['2fa_pending_user_id']), "complete_2fa_login must unset 2fa_pending_user_id.");
echo "  ✔ 2FA session challenge lifecycle verified.\n";

echo "All Security Features & 2FA unit tests passed successfully!\n";
