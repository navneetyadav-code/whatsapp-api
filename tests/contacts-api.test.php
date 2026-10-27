<?php

declare(strict_types=1);

function test_assert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "  ❌ FAIL: {$message}\n");
        exit(1);
    }
    echo "  ✔ PASS: {$message}\n";
}

require_once dirname(__DIR__) . '/src/bootstrap.php';

echo "=== Contacts REST API Endpoint Unit Tests ===\n\n";

$pdo = Database::pdo();

// 1. Setup a dedicated test user and clean test contacts
$testEmail = 'contacts_api_test_' . time() . '_' . bin2hex(random_bytes(4)) . '@example.com';
$pwdHash = password_hash('Secret123!', PASSWORD_BCRYPT);
$userStmt = $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
$userStmt->execute(['API Test User', $testEmail, $pwdHash]);
$testUserId = (int) $pdo->lastInsertId();

// Generate API key using project helper
$creds = create_api_key($testUserId, 'Contacts API Test Key');
$apiKey = $creds['api_key'];

echo "[Suite 1: Authentication & Authorization Guardrails]\n";

function call_endpoint(string $endpointFile, string $method, array $getParams = [], ?array $body = null, ?string $bearerToken = null, ?int $mockSessionUserId = null, ?string $mockCsrf = null): array
{
    $bootstrap = dirname(__DIR__) . '/src/bootstrap.php';
    $script = dirname(__DIR__) . '/public/api/' . $endpointFile;
    $runner = dirname(__DIR__) . '/tests/api-runner-' . bin2hex(random_bytes(4)) . '.tmp.php';

    $code = '<?php
    require_once ' . var_export($bootstrap, true) . ';
    $_SERVER["REQUEST_METHOD"] = ' . var_export($method, true) . ';
    $_SERVER["REMOTE_ADDR"] = "127.0.0.1";
    $_GET = ' . var_export($getParams, true) . ';
    $_POST = ' . var_export($body ?? [], true) . ';
    ';

    if ($bearerToken !== null) {
        $code .= '$_SERVER["HTTP_AUTHORIZATION"] = "Bearer " . ' . var_export($bearerToken, true) . ';';
    }

    if ($mockSessionUserId !== null) {
        $code .= '
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION["user_id"] = ' . (int) $mockSessionUserId . ';
        $_SESSION["csrf_token"] = "valid_test_csrf_token_12345";
        ';
        if ($mockCsrf !== null) {
            $code .= '$_SERVER["HTTP_X_CSRF_TOKEN"] = ' . var_export($mockCsrf, true) . ';';
        }
    }

    $code .= '
    register_shutdown_function(function() {
        echo "\n__HTTP_CODE__:" . (http_response_code() ?: 200);
    });
    require ' . var_export($script, true) . ';';

    file_put_contents($runner, $code);
    $output = shell_exec('php ' . escapeshellarg($runner));
    @unlink($runner);

    $raw = (string) $output;
    $httpCode = 200;
    if (preg_match('/__HTTP_CODE__:(\d+)/', $raw, $m)) {
        $httpCode = (int) $m[1];
        $raw = trim(str_replace($m[0], '', $raw));
    }

    $json = json_decode($raw, true);
    return ['code' => $httpCode, 'data' => $json, 'raw' => $raw];
}

function call_contacts_api(string $method, array $getParams = [], ?array $body = null, ?string $bearerToken = null, ?int $mockSessionUserId = null, ?string $mockCsrf = null): array
{
    return call_endpoint('contacts.php', $method, $getParams, $body, $bearerToken, $mockSessionUserId, $mockCsrf);
}

// Test 1.1: Reject unauthenticated requests
$resUnauth = call_contacts_api('GET');
test_assert($resUnauth['code'] === 401, 'Unauthenticated GET request returns HTTP 401');
test_assert($resUnauth['data']['ok'] === false, 'Unauthenticated response returns ok: false');

// Test 1.2: Reject invalid API key
$resInvalidKey = call_contacts_api('GET', [], null, 'invalid_key_xyz');
test_assert($resInvalidKey['code'] === 401, 'Invalid Bearer token returns HTTP 401');

echo "\n[Suite 2: Single & Batch Contact Upsert (POST)]\n";

// Test 2.1: Missing phone number returns 422
$resMissingPhone = call_contacts_api('POST', [], ['name' => 'No Phone'], $apiKey);
test_assert($resMissingPhone['code'] === 422, 'POST with missing phone returns HTTP 422');

// Test 2.2: Create a single contact
$testPhone = '+14155550199';
$resCreate = call_contacts_api('POST', [], [
    'name' => 'Agent Smith',
    'phone' => $testPhone,
    'opt_in' => 1,
    'notes' => 'API Integration Lead',
], $apiKey);

test_assert($resCreate['code'] === 200, 'POST with valid contact returns HTTP 200');
test_assert($resCreate['data']['ok'] === true, 'Response contains ok: true');
test_assert($resCreate['data']['contact']['name'] === 'Agent Smith', 'Saved contact name matches input');
test_assert($resCreate['data']['contact']['phone'] === $testPhone, 'Saved contact phone matches input');
test_assert($resCreate['data']['contact']['opt_in'] === 1, 'Saved contact opt_in matches input');
$savedId = (int) $resCreate['data']['contact']['id'];

// Test 2.3: Upsert contact updates existing record without duplicate key error
$resUpdate = call_contacts_api('POST', [], [
    'name' => 'Agent Smith Senior',
    'phone' => $testPhone,
    'opt_in' => 1,
    'notes' => 'Updated via API',
], $apiKey);

test_assert($resUpdate['code'] === 200, 'Upserting existing phone returns HTTP 200');
test_assert($resUpdate['data']['contact']['name'] === 'Agent Smith Senior', 'Contact name updated successfully');
test_assert($resUpdate['data']['contact']['notes'] === 'Updated via API', 'Contact notes updated successfully');

// Test 2.4: Dedicated Batch Endpoint (POST /api/contacts-batch.php)
$batchPayload = [
    'contacts' => [
        ['name' => 'Batch Alpha', 'phone' => '+14155550201', 'opt_in' => 1, 'notes' => 'Batch item 1'],
        ['name' => 'Batch Beta', 'phone' => '+14155550202', 'opt_in' => 0, 'notes' => 'Batch item 2'],
    ]
];
$resBatch = call_endpoint('contacts-batch.php', 'POST', [], $batchPayload, $apiKey);
test_assert($resBatch['code'] === 200, 'Dedicated Batch POST returns HTTP 200');
test_assert($resBatch['data']['processed'] === 2, 'Batch processes both contacts (2)');

echo "\n[Suite 3: Directory Listing, Search, and ID Retrieval (GET)]\n";

// Test 3.1: Retrieve contacts list
$resList = call_contacts_api('GET', [], null, $apiKey);
test_assert($resList['code'] === 200, 'GET contacts list returns HTTP 200');
test_assert($resList['data']['total'] >= 3, 'Total count reflects saved contacts (>= 3)');
test_assert(count($resList['data']['contacts']) >= 3, 'Contacts array is populated');

// Test 3.2: Filter by search query
$resSearch = call_contacts_api('GET', ['search' => 'Alpha'], null, $apiKey);
test_assert($resSearch['code'] === 200, 'Search query returns HTTP 200');
test_assert($resSearch['data']['total'] === 1, 'Search finds exactly 1 match for "Alpha"');
test_assert($resSearch['data']['contacts'][0]['name'] === 'Batch Alpha', 'Matching contact is Batch Alpha');

// Test 3.3: Retrieve single contact by synthetic ID
$resById = call_contacts_api('GET', ['id' => $savedId], null, $apiKey);
test_assert($resById['code'] === 200, 'GET contact by synthetic ID returns HTTP 200');
test_assert($resById['data']['contact']['id'] === $savedId, 'Retrieved contact ID matches request');
test_assert($resById['data']['contact']['name'] === 'Agent Smith Senior', 'Retrieved contact name matches');

// Test 3.4: Filter by opt_in status
$resOptedIn = call_contacts_api('GET', ['opt_in' => '1'], null, $apiKey);
test_assert($resOptedIn['code'] === 200, 'Opt-in filter returns HTTP 200');
foreach ($resOptedIn['data']['contacts'] as $c) {
    test_assert($c['opt_in'] === 1, 'Opt-in filter returns only opt_in = 1 records');
}

echo "\n[Suite 4: PII Security Enforcement & Contact Deletion (DELETE / POST)]\n";

// Test 4.1: CRITICAL PII GUARD: Reject raw phone number in GET query string
$resPiiGet = call_contacts_api('GET', ['phone' => '+14155550202'], null, $apiKey);
test_assert($resPiiGet['code'] === 400, 'GET with raw phone in query is strictly blocked with HTTP 400');
test_assert(str_contains($resPiiGet['data']['error'], 'Security & Privacy Violation'), 'PII error message returned for query leak');

// Test 4.2: CRITICAL PII GUARD: Reject raw phone number in DELETE query string
$resPiiDel = call_contacts_api('DELETE', ['phone' => '+14155550202'], null, $apiKey);
test_assert($resPiiDel['code'] === 400, 'DELETE with raw phone in query is strictly blocked with HTTP 400');

// Test 4.3: Secure Delete by Synthetic ID (?id=...)
$resDelById = call_contacts_api('DELETE', ['id' => $savedId], null, $apiKey);
test_assert($resDelById['code'] === 200, 'DELETE by synthetic ID returns HTTP 200');
test_assert($resDelById['data']['ok'] === true, 'DELETE response contains ok: true');

// Test 4.4: Secure Delete by Phone via Request Body (JSON)
$resDelBody = call_contacts_api('DELETE', [], ['phone' => '+14155550201'], $apiKey);
test_assert($resDelBody['code'] === 200, 'DELETE with phone in request body returns HTTP 200');

// Test 4.5: Dedicated Delete Endpoint (POST /api/contacts-delete.php)
$resDedicatedDel = call_endpoint('contacts-delete.php', 'POST', [], ['phone' => '+14155550202'], $apiKey);
test_assert($resDedicatedDel['code'] === 200, 'Dedicated POST /api/contacts-delete.php returns HTTP 200');

// Test 4.6: Tenant isolation: another user cannot delete this user\'s contact
$otherEmail = 'contacts_other_' . time() . '_' . bin2hex(random_bytes(4)) . '@example.com';
$userStmt->execute(['Other User', $otherEmail, $pwdHash]);
$otherUserId = (int) $pdo->lastInsertId();
$otherCreds = create_api_key($otherUserId, 'Other Key');
$otherApiKey = $otherCreds['api_key'];

$resCrossTenant = call_contacts_api('DELETE', ['id' => $savedId], null, $otherApiKey);
test_assert($resCrossTenant['code'] === 404, 'Cross-tenant DELETE is rejected with HTTP 404 (isolation enforced)');

echo "\n[Suite 5: Advanced Security Hardening (DoS, CSRF, Wildcard Injection)]\n";

// Test 5.1: DoS Prevention: Rejects oversized batch arrays > 500 with HTTP 413
$giantBatch = ['contacts' => array_fill(0, 501, ['name' => 'Spam', 'phone' => '+1111111111'])];
$resOversized = call_endpoint('contacts-batch.php', 'POST', [], $giantBatch, $apiKey);
test_assert($resOversized['code'] === 413, 'Batch size > 500 is blocked with HTTP 413 Payload Too Large');

// Test 5.2: SQL LIKE Wildcard Injection Prevention: Searching for "%" searches literal "%" without crashing
$resWildcard = call_contacts_api('GET', ['search' => '%'], null, $apiKey);
test_assert($resWildcard['code'] === 200, 'LIKE wildcard "%" search query executes safely with parameterization');

// Test 5.3: Session CSRF Protection: Session-based POST without X-CSRF-Token is blocked with HTTP 403
$resNoCsrf = call_contacts_api('POST', [], ['name' => 'CSRF Attacker', 'phone' => '+1999999999'], null, $testUserId, null);
test_assert($resNoCsrf['code'] === 403, 'Session-based state mutation without CSRF token is rejected with HTTP 403');

// Test 5.4: Session-based POST with valid X-CSRF-Token succeeds
$resWithCsrf = call_contacts_api('POST', [], ['name' => 'CSRF Protected', 'phone' => '+1999999998'], null, $testUserId, 'valid_test_csrf_token_12345');
test_assert($resWithCsrf['code'] === 200, 'Session-based state mutation with valid X-CSRF-Token succeeds with HTTP 200');

// Clean up test data
$pdo->prepare('DELETE FROM contacts WHERE user_id IN (?, ?)')->execute([$testUserId, $otherUserId]);
$pdo->prepare('DELETE FROM api_keys WHERE user_id IN (?, ?)')->execute([$testUserId, $otherUserId]);
$pdo->prepare('DELETE FROM users WHERE id IN (?, ?)')->execute([$testUserId, $otherUserId]);

echo "\n==================================================\n";
echo "ALL CONTACTS API SECURITY TESTS PASSED SUCCESSFULLY!\n";
