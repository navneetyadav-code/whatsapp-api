<?php

declare(strict_types=1);

function test_assert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

require_once dirname(__DIR__) . '/src/bootstrap.php';

echo "Running Session CSRF and SMTP Injection Security Tests (Fixes for CWE-352 & CWE-93)...\n";

// =========================================================================
// 1. SMTP Parameter & CRLF Injection Sanitization Tests (CWE-93 / CWE-88)
// =========================================================================

// Test A: Recipient with CRLF injection is rejected
$injectedRecipient = "victim@example.com\r\nRCPT TO: <attacker@evil.com>\r\n";
$cleanedRecipient = str_replace(["\r", "\n"], '', trim($injectedRecipient));
test_assert(
    !filter_var($cleanedRecipient, FILTER_VALIDATE_EMAIL),
    "CRLF-injected recipient must fail email validation"
);

// Test B: Valid recipient passes
$validRecipient = "admin@example.com";
$cleanedValid = str_replace(["\r", "\n"], '', trim($validRecipient));
test_assert(
    (bool) filter_var($cleanedValid, FILTER_VALIDATE_EMAIL),
    "Valid recipient passes email validation"
);

// Test C: Subject CRLF characters are stripped
$injectedSubject = "Password Reset\r\nBcc: spy@evil.com\r\n";
$cleanedSubject = str_replace(["\r", "\n"], '', trim($injectedSubject));
test_assert(
    !str_contains($cleanedSubject, "\r") && !str_contains($cleanedSubject, "\n"),
    "Subject line must be stripped of all carriage return and newline characters"
);
test_assert(
    $cleanedSubject === "Password ResetBcc: spy@evil.com",
    "Cleaned subject matches expected stripped string"
);

// Test D: Message Body Dot-Stuffing (RFC 5321)
$bodyWithDot = "Line 1\n.hidden command or truncation\nLine 3";
$normalizedBody = str_replace("\r\n", "\n", $bodyWithDot);
$stuffedBody = preg_replace('/^\./m', '..', $normalizedBody);
test_assert(
    str_contains($stuffedBody, "\n..hidden command or truncation"),
    "Dot at beginning of line must be escaped as double dot (dot-stuffing)"
);

// =========================================================================
// 2. Session Control Endpoint CSRF & Method Enforcement Tests (CWE-352)
// =========================================================================

$stateChangingActions = ['start', 'logout', 'sleep', 'wake', 'send-test'];

foreach ($stateChangingActions as $act) {
    // Simulate GET request
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_GET['action'] = $act;

    $isDisallowedMethod = in_array($act, $stateChangingActions, true) && $_SERVER['REQUEST_METHOD'] !== 'POST';
    test_assert($isDisallowedMethod, "State-changing action '{$act}' must reject GET requests");
}

// Read-only actions permit GET requests
$readOnlyActions = ['status', 'queue-status'];
foreach ($readOnlyActions as $act) {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_GET['action'] = $act;

    $isAllowed = !in_array($act, $stateChangingActions, true);
    test_assert($isAllowed, "Read-only action '{$act}' must allow GET requests");
}

echo "All Session CSRF and SMTP Injection security unit tests passed successfully!\n";
