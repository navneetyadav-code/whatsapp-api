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
        $clean = trim(preg_replace('/\s+/', ' ', (string) $clean));

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
        $slugCandidate = strtolower((string) preg_replace('/[^a-z0-9_]/', '_', $lastSegment));
        if (strlen($slugCandidate) < 3) {
            $slugCandidate = 'call_' . strtolower((string) preg_replace('/[^a-z0-9_]/', '_', explode('.', $host)[0]));
        }
        $slugCandidate = substr($slugCandidate, 0, 32);

        $hostParts = explode('.', $host);
        $brandName = ucfirst($hostParts[count($hostParts) > 2 ? 1 : 0] ?? 'Custom');
        $displayTitle = "🌐 {$brandName} " . ucwords(str_replace('_', ' ', $slugCandidate));

        // 6. Parameter Extraction & URL Template Formulation
        $parameters = [];
        $templateUrl = $url;

        // Process URL Query Parameters
        if ($query !== '') {
            parse_str($query, $queryParams);
            $cleanQueryParts = [];
            foreach ($queryParams as $qKey => $qVal) {
                $paramKey = preg_replace('/[^a-z0-9_]/', '_', strtolower((string) $qKey));
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
            if (is_array($decodedBody)) {
                $bodyTemplate = [];
                foreach ($decodedBody as $bKey => $bVal) {
                    $paramKey = preg_replace('/[^a-z0-9_]/', '_', strtolower((string) $bKey));
                    $paramType = 'STRING';
                    if (is_int($bVal)) $paramType = 'INTEGER';
                    elseif (is_float($bVal) || is_numeric($bVal)) $paramType = 'NUMBER';
                    elseif (is_bool($bVal)) $paramType = 'BOOLEAN';

                    $parameters[] = [
                        'name' => $paramKey,
                        'type' => $paramType,
                        'description' => "Payload field '{$bKey}'",
                        'required' => true,
                    ];
                    $bodyTemplate[$bKey] = '{' . $paramKey . '}';
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
}
