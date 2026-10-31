<?php
/**
 * InfinityFree PHP API Client with Automatic AES Challenge Solver
 * ---------------------------------------------------------------
 * Solves InfinityFree's anti-bot JavaScript challenge in pure PHP,
 * caches the __test cookie, and executes seamless GET/POST API requests.
 */

declare(strict_types=1);

class InfinityFreeClient {
    private string $cookieCacheFile;
    private string $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36';

    public function __construct(?string $cacheFile = null) {
        $this->cookieCacheFile = $cacheFile ?? sys_get_temp_dir() . '/infinityfree_cookie.json';
    }

    /**
     * Get active __test cookie (from cache or newly solved)
     */
    public function getTestCookie(string $baseUrl): string {
        $domain = parse_url($baseUrl, PHP_URL_HOST) ?: 'assistgroup.gt.tc';

        // 1. Check cache
        if (file_exists($this->cookieCacheFile)) {
            $data = json_decode(file_get_contents($this->cookieCacheFile), true);
            if (isset($data[$domain]) && isset($data[$domain]['expires']) && $data[$domain]['expires'] > time() + 60) {
                return $data[$domain]['cookie'];
            }
        }

        // 2. Solve challenge
        echo "⚡ Solving InfinityFree AES challenge for $domain...\n";
        $solvedCookie = $this->solveChallenge($baseUrl);

        // 3. Save to cache (valid for 5 hours)
        $cache = file_exists($this->cookieCacheFile) ? json_decode(file_get_contents($this->cookieCacheFile), true) ?? [] : [];
        $cache[$domain] = [
            'cookie' => $solvedCookie,
            'expires' => time() + (5 * 3600)
        ];
        file_put_contents($this->cookieCacheFile, json_encode($cache));

        return $solvedCookie;
    }

    /**
     * Solve the AES challenge from InfinityFree
     */
    private function solveChallenge(string $url): string {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $html = curl_exec($ch);
        curl_close($ch);

        if (!$html) {
            throw new RuntimeException("Empty response when fetching challenge from $url");
        }

        if (!preg_match('/var\s+a=toNumbers\("([0-9a-fA-F]+)"\),\s*b=toNumbers\("([0-9a-fA-F]+)"\),\s*c=toNumbers\("([0-9a-fA-F]+)"\)/', $html, $m)) {
            throw new RuntimeException("Could not extract AES challenge parameters from InfinityFree HTML.");
        }

        $key = hex2bin($m[1]);
        $iv = hex2bin($m[2]);
        $ciphertext = hex2bin($m[3]);

        $decrypted = openssl_decrypt($ciphertext, 'AES-128-CBC', $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, $iv);
        if ($decrypted === false) {
            throw new RuntimeException("OpenSSL AES-128-CBC decryption failed.");
        }

        return bin2hex($decrypted);
    }

    /**
     * Send API Request through the InfinityFree bypass
     */
    public function request(string $url, string $method = 'GET', array $data = [], string $apiKey = 'ag_secret_infinity_key_2026'): array {
        $cookieValue = $this->getTestCookie($url);

        $headers = [
            'Content-Type: application/json',
            'X-API-KEY: ' . $apiKey,
            'Accept: application/json'
        ];

        $ch = curl_init();
        $targetUrl = $url;

        if (strtoupper($method) === 'GET' && !empty($data)) {
            $targetUrl .= (strpos($targetUrl, '?') === false ? '?' : '&') . http_build_query($data);
        }

        curl_setopt($ch, CURLOPT_URL, $targetUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_COOKIE, "__test=$cookieValue");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        if (strtoupper($method) === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['ok' => false, 'http_code' => $httpCode, 'error' => $error];
        }

        $json = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return ['ok' => true, 'http_code' => $httpCode, 'data' => $json];
        }

        return [
            'ok' => false,
            'http_code' => $httpCode,
            'raw_body' => $response,
            'error' => 'Response was not valid JSON'
        ];
    }
}

// CLI Demonstration / Test Execution
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    echo "========================================================\n";
    echo "  INFINITYFREE PURE PHP API CLIENT & BYPASS TESTER\n";
    echo "========================================================\n\n";

    $client = new InfinityFreeClient();

    // Default test target: change to assistgroup.gt.tc/api.php or assistgroup.rf.gd/api.php once uploaded!
    $targetUrl = $argv[1] ?? 'https://assistgroup.gt.tc/api.php';
    $apiKey = $argv[2] ?? 'ag_secret_infinity_key_2026';

    echo "Testing Target: $targetUrl\n";
    echo "API Key:        $apiKey\n\n";

    echo "--- 1. Testing GET ?action=ping ---\n";
    $pingRes = $client->request($targetUrl, 'GET', ['action' => 'ping'], $apiKey);
    print_r($pingRes);

    echo "\n--- 2. Testing POST ?action=echo ---\n";
    $postRes = $client->request($targetUrl, 'POST', [
        'action' => 'echo',
        'message' => 'Hello from WhatsApp Bot!',
        'customer' => ['name' => 'Ankit', 'phone' => '918581065107']
    ], $apiKey);
    print_r($postRes);
}
