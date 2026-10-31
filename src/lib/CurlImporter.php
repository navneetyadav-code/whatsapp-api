<?php

declare(strict_types=1);

class CurlImporter
{
    /**
     * Parses a raw cURL command into a structured Custom Tool definition.
     */
    public static function parse(string $raw): ?array
    {
        // Normalize line continuations (\ or ^) and excessive whitespace
        $clean = preg_replace('/[\\^]\r?\n\s*/', ' ', $raw);
        $clean = trim((string) preg_replace('/\s+/', ' ', (string) $clean));

        if (!preg_match('/\bcurl\b/i', $clean)) {
            return null;
        }

        // 1. Detect HTTP Method
        $method = 'GET';
        if (preg_match('/(?:-X|--request)\s+([A-Za-z]+)/i', $clean, $m)) {
            $method = strtoupper($m[1]);
        }

        // 2. Detect Body Payload
        $body = null;
        if (preg_match('/(?:-d|--data|--data-raw|--data-binary|--json)\s+(?:\'([^\']*)\'|"((?:[^"\\\\]|\\\\.)*)"|([^\s\'"]+))/i', $clean, $m)) {
            $body = $m[1] !== '' ? $m[1] : ($m[2] !== '' ? stripslashes($m[2]) : $m[3]);
            if ($method === 'GET') {
                $method = 'POST';
            }
        }

        // 3. Extract Headers
        $headers = [];
        $headerPattern = '/(?:-H|--header)\s+(?:\'([^\']*)\'|"((?:[^"\\\\]|\\\\.)*)"|([^\s\'"]+))/i';
        if (preg_match_all($headerPattern, $clean, $hMatches, PREG_SET_ORDER)) {
            foreach ($hMatches as $hm) {
                $hVal = $hm[1] !== '' ? $hm[1] : ($hm[2] !== '' ? stripslashes($hm[2]) : $hm[3]);
                if (str_contains($hVal, ':')) {
                    [$k, $v] = explode(':', $hVal, 2);
                    $headers[trim($k)] = trim($v);
                }
            }
        }

        // Support -b / --cookie
        if (preg_match('/(?:-b|--cookie)\s+(?:\'([^\']*)\'|"((?:[^"\\\\]|\\\\.)*)"|([^\s\'"]+))/i', $clean, $cMatch)) {
            $cVal = $cMatch[1] !== '' ? $cMatch[1] : ($cMatch[2] !== '' ? stripslashes($cMatch[2]) : $cMatch[3]);
            if (!isset($headers['Cookie'])) {
                $headers['Cookie'] = trim($cVal);
            }
        }

        // Support -u / --user (Basic Auth)
        if (preg_match('/(?:-u|--user)\s+(?:\'([^\']*)\'|"((?:[^"\\\\]|\\\\.)*)"|([^\s\'"]+))/i', $clean, $uMatch)) {
            $uVal = $uMatch[1] !== '' ? $uMatch[1] : ($uMatch[2] !== '' ? stripslashes($uMatch[2]) : $uMatch[3]);
            if (!isset($headers['Authorization'])) {
                $headers['Authorization'] = 'Basic ' . base64_encode(trim($uVal));
            }
        }

        // 4. Extract Target URL
        $url = null;
        if (preg_match('/(?:[\'"])(https?:\/\/[^\'"]+)(?:[\'"])/i', $clean, $m)) {
            $url = $m[1];
        } elseif (preg_match('/\b(https?:\/\/[^\s\'"]+)/i', $clean, $m)) {
            $url = $m[1];
        }

        if (!$url) {
            return null;
        }

        // 5. Parse URL components
        $parsedUrl = parse_url($url);
        $host = $parsedUrl['host'] ?? 'api';
        $path = $parsedUrl['path'] ?? '/';
        $query = $parsedUrl['query'] ?? '';

        // Generate clean tool slug candidate
        $pathSegments = array_values(array_filter(explode('/', $path)));
        $lastSegment = end($pathSegments) ?: 'endpoint';
        $lastSegment = preg_replace('/\.php$/i', '', $lastSegment); // strip .php extension

        // Check if host is shared hosting / localhost or has a project root directory
        $isSharedOrLocal = ($host === 'localhost' || $host === '127.0.0.1' || str_contains($host, '.gt.tc') || str_contains($host, '.epizy.com') || str_contains($host, 'byet'));
        $firstSegment = $pathSegments[0] ?? '';

        if ($isSharedOrLocal && $firstSegment !== '' && !in_array(strtolower($firstSegment), ['api', 'v1', 'v2', 'admin'], true)) {
            $appName = ucfirst(preg_replace('/[^a-zA-Z0-9]/', '', $firstSegment));
            $brandName = $appName;
            $cleanLast = preg_replace('/[^a-zA-Z0-9_]/', '_', $lastSegment);
            $slugCandidate = strtolower(substr($firstSegment . '_' . $cleanLast, 0, 32));
            $displayTitle = "🏢 {$appName} " . ucwords(str_replace('_', ' ', $cleanLast));
        } else {
            $slugCandidate = strtolower((string) preg_replace('/[^a-z0-9_]/', '_', $lastSegment));
            if (strlen($slugCandidate) < 3) {
                $slugCandidate = 'call_' . strtolower((string) preg_replace('/[^a-z0-9_]/', '_', explode('.', $host)[0]));
            }
            $slugCandidate = substr($slugCandidate, 0, 32);

            $hostParts = explode('.', $host);
            $brandName = ucfirst($hostParts[count($hostParts) > 2 ? 1 : 0] ?? 'Custom');
            $displayTitle = "🌐 {$brandName} " . ucwords(str_replace('_', ' ', $slugCandidate));
        }

        // 6. Parameter Extraction & URL Template Formulation
        $parameters = [];
        $usedParamNames = [];
        $templateUrl = $url;

        // Process URL Query Parameters
        if ($query !== '') {
            parse_str($query, $queryParams);
            $cleanQueryParts = [];
            foreach ($queryParams as $qKey => $qVal) {
                $paramKey = self::generateParamName((string) $qKey, $usedParamNames);
                $usedParamNames[$paramKey] = true;
                $parameters[] = [
                    'name' => $paramKey,
                    'type' => is_numeric($qVal) ? 'NUMBER' : 'STRING',
                    'description' => "Query parameter '{$qKey}' (sample: {$qVal})",
                    'required' => true,
                ];
                $cleanQueryParts[] = urlencode((string) $qKey) . '={' . $paramKey . '}';
            }
            $baseWithoutQuery = explode('?', $url)[0];
            $templateUrl = $baseWithoutQuery . '?' . implode('&', $cleanQueryParts);
        }

        // Process Body Parameters
        $bodyTemplate = null;
        if ($body !== null) {
            $decodedBody = json_decode($body, true);
            if (json_last_error() === JSON_ERROR_NONE && (is_array($decodedBody) || is_scalar($decodedBody))) {
                // Recursive traversal preserving nested objects and static constants
                $bodyTemplate = self::processJsonNode($decodedBody, '', $parameters, $usedParamNames);
                if (!isset($headers['Content-Type']) && !isset($headers['content-type'])) {
                    $headers['Content-Type'] = 'application/json';
                }
            } else {
                // Non-JSON (e.g. form-urlencoded or raw text)
                parse_str($body, $formBody);
                if (is_array($formBody) && !empty($formBody) && count($formBody) > 1) {
                    $bodyTemplate = [];
                    foreach ($formBody as $fKey => $fVal) {
                        $paramKey = self::generateParamName((string) $fKey, $usedParamNames);
                        $usedParamNames[$paramKey] = true;
                        $parameters[] = [
                            'name' => $paramKey,
                            'type' => is_numeric($fVal) ? 'NUMBER' : 'STRING',
                            'description' => "Payload field '{$fKey}'" . ($fVal !== '' ? " (sample: {$fVal})" : ''),
                            'required' => true,
                        ];
                        $bodyTemplate[$fKey] = '{' . $paramKey . '}';
                    }
                } else {
                    $bodyTemplate = $body;
                }
            }
        }

        return [
            'tool_slug' => $slugCandidate,
            'display_name' => $displayTitle,
            'category' => 'rest_api',
            'description' => "Sends an automated {$method} request to {$host} to process {$slugCandidate}.",
            'execution_config' => [
                'method' => $method,
                'url' => $templateUrl,
                'headers' => !empty($headers) ? $headers : ['Accept' => 'application/json'],
                'body_template' => $bodyTemplate,
                'response_path' => ''
            ],
            'parameters' => $parameters,
            'rbac_roles' => ['guest', 'customer', 'admin']
        ];
    }

    /**
     * Recursively traverses a JSON payload to extract dynamic parameters
     * while preserving nested structure and static constants in body_template.
     */
    private static function processJsonNode(mixed $node, string $parentKey, array &$parameters, array &$usedParamNames): mixed
    {
        if (is_array($node)) {
            $isAssoc = array_keys($node) !== range(0, count($node) - 1);
            $processed = [];
            foreach ($node as $key => $val) {
                if ($isAssoc) {
                    $processed[$key] = self::processJsonNode($val, (string) $key, $parameters, $usedParamNames);
                } else {
                    $processed[] = self::processJsonNode($val, $parentKey, $parameters, $usedParamNames);
                }
            }
            return $processed;
        }

        // Check if value is a static constant/protocol flag
        if (self::isStaticConstant($parentKey, $node)) {
            return $node;
        }

        // Treat as a dynamic parameter
        $paramName = self::generateParamName($parentKey, $usedParamNames);
        $usedParamNames[$paramName] = true;

        $paramType = 'STRING';
        if (is_int($node)) {
            $paramType = 'INTEGER';
        } elseif (is_float($node)) {
            $paramType = 'NUMBER';
        } elseif (is_bool($node)) {
            $paramType = 'BOOLEAN';
        }

        $desc = "Payload field '{$parentKey}'";
        $lowerKey = strtolower($parentKey);
        if (in_array($lowerKey, ['to', 'recipient', 'phone', 'phone_number', 'mobile', 'wa_id'], true)) {
            $desc = "Recipient phone number with country code" . (is_scalar($node) && $node !== '' ? " (sample: {$node})" : '');
        } elseif (in_array($lowerKey, ['body', 'message', 'content', 'text', 'caption'], true)) {
            $desc = "Message text content to be sent";
        } elseif (is_scalar($node) && $node !== '') {
            $desc .= " (sample: {$node})";
        }

        $parameters[] = [
            'name' => $paramName,
            'type' => $paramType,
            'description' => $desc,
            'required' => true,
        ];

        return '{' . $paramName . '}';
    }

    /**
     * Identifies static API protocol flags and configuration constants that
     * must remain fixed literals in the body_template rather than dynamic parameters.
     */
    private static function isStaticConstant(string $key, mixed $val): bool
    {
        $k = strtolower(trim($key));

        // Booleans are almost universally static configuration flags in REST payloads (e.g., preview_url: false)
        if (is_bool($val)) {
            return true;
        }

        // Null is not parameterized
        if ($val === null) {
            return true;
        }

        // Known static protocol / platform keys
        $knownStaticKeys = [
            'messaging_product',
            'recipient_type',
            'grant_type',
            'response_type',
            'version',
            'api_version',
            'format',
            'mode'
        ];
        if (in_array($k, $knownStaticKeys, true)) {
            return true;
        }

        // Message or object discriminator types (e.g. "type": "text", "object": "page")
        if (($k === 'type' || $k === 'object') && is_string($val) && in_array(strtolower($val), [
            'text', 'template', 'image', 'document', 'audio', 'video', 
            'interactive', 'location', 'contacts', 'page', 'event', 'object', 'notification'
        ], true)) {
            return true;
        }

        return false;
    }

    /**
     * Generates a safe, collision-free parameter identifier while preserving
     * the exact field name whenever possible.
     */
    private static function generateParamName(string $key, array $usedParamNames): string
    {
        $base = strtolower((string) preg_replace('/[^a-z0-9_]/', '_', trim($key)));
        if ($base === '' || is_numeric($base[0])) {
            $base = 'param_' . $base;
        }
        $candidate = $base;
        $counter = 2;
        while (isset($usedParamNames[$candidate])) {
            $candidate = $base . '_' . $counter;
            $counter++;
        }
        return $candidate;
    }
}
