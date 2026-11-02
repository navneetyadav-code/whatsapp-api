<?php

declare(strict_types=1);

class AntiBotBypass
{
    private static array $cookieCache = [];

    public const BROWSER_USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36';

    /**
     * Checks if the response body contains an anti-bot challenge (such as InfinityFree / ByetHost aes.js).
     */
    public static function isChallenge(string $body): bool
    {
        return (str_contains($body, 'aes.js') || str_contains($body, 'slowAES') || str_contains($body, 'toNumbers'))
            && (bool) preg_match('/toNumbers\("([0-9a-fA-F]+)"\)/i', $body);
    }

    /**
     * Solves the aes.js / slowAES challenge and returns the __test cookie value.
     */
    public static function solveChallenge(string $html): ?string
    {
        if (!preg_match('/a=toNumbers\("([0-9a-fA-F]+)"\)/i', $html, $mA) ||
            !preg_match('/b=toNumbers\("([0-9a-fA-F]+)"\)/i', $html, $mB) ||
            !preg_match('/c=toNumbers\("([0-9a-fA-F]+)"\)/i', $html, $mC)) {
            return null;
        }

        $key = @hex2bin($mA[1]);
        $iv = @hex2bin($mB[1]);
        $ct = @hex2bin($mC[1]);
        if (!$key || !$iv || !$ct) {
            return null;
        }

        $decrypted = openssl_decrypt($ct, 'aes-128-cbc', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
        if ($decrypted === false) {
            return null;
        }

        return bin2hex($decrypted);
    }

    /**
     * Returns cached cookie for the given target URL.
     */
    public static function getCachedCookie(string $url): ?string
    {
        $host = parse_url($url, PHP_URL_HOST);
        return $host ? (self::$cookieCache[$host] ?? null) : null;
    }

    /**
     * Caches cookie for the given target URL host.
     */
    public static function setCachedCookie(string $url, string $cookie): void
    {
        $host = parse_url($url, PHP_URL_HOST);
        if ($host) {
            self::$cookieCache[$host] = $cookie;
        }
    }

    /**
     * Activates the cookie on the shared hosting server.
     */
    public static function activateCookie(string $targetUrl, string $cookieValue, int $timeout = 6): void
    {
        $activateUrl = str_contains($targetUrl, '?') ? "{$targetUrl}&i=1" : "{$targetUrl}?i=1";
        $ch = curl_init($activateUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => [
                'User-Agent: ' . self::BROWSER_USER_AGENT,
                "Cookie: __test={$cookieValue}",
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
            ]
        ]);
        curl_exec($ch);
        curl_close($ch);
    }
}
