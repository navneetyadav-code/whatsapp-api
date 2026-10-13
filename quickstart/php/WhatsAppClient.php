<?php

declare(strict_types=1);

namespace WhatsAppHub;

/**
 * Class WhatsAppClient
 * Professional, zero-dependency PHP client for WhatsApp API Gateway.
 */
class WhatsAppClient
{
    public const DEFAULT_URL = 'https://whatsapp-api-xepo.onrender.com/api/send.php';

    private string $apiKey;
    private string $apiUrl;

    public function __construct(?string $apiKey = null, ?string $apiUrl = null)
    {
        $this->apiKey = $apiKey ?: (string) getenv('WA_API_KEY');
        $this->apiUrl = rtrim($apiUrl ?: ((string) getenv('WA_API_URL') ?: self::DEFAULT_URL), '/');

        if ($this->apiKey === '') {
            throw new \InvalidArgumentException('API Key is required. Set WA_API_KEY env or pass to constructor.');
        }
    }

    /**
     * Send message using the Standard Clean Flat JSON format
     *
     * @param string $to Recipient phone number (e.g. 919876543210)
     * @param string $message Message content
     * @param bool $humanize Enable humanized typing delays
     * @param int|null $campaignId Optional campaign tracking ID
     * @return array [bool $success, array $data, int $httpCode]
     */
    public function sendStandard(string $to, string $message, bool $humanize = true, ?int $campaignId = null): array
    {
        $payload = [
            'to' => trim($to),
            'message' => trim($message),
            'humanize' => $humanize,
        ];
        if ($campaignId !== null) {
            $payload['campaign_id'] = $campaignId;
        }

        $headers = [
            'Content-Type: application/json',
            'X-API-Key: ' . $this->apiKey,
        ];

        return $this->post($payload, $headers);
    }

    /**
     * Send message using the Meta WhatsApp Cloud API format (Drop-In)
     *
     * @param string $to Recipient phone number
     * @param string $body Text message body
     * @param bool $previewUrl Preview web links
     * @return array [bool $success, array $data, int $httpCode]
     */
    public function sendMetaFormat(string $to, string $body, bool $previewUrl = false): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => trim($to),
            'type' => 'text',
            'text' => [
                'preview_url' => $previewUrl,
                'body' => trim($body),
            ],
        ];

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
        ];

        return $this->post($payload, $headers);
    }

    /**
     * Send a template message using Meta Cloud API structure
     */
    public function sendTemplate(string $to, string $templateName, array $parameters = [], string $languageCode = 'en_US'): array
    {
        $componentParams = [];
        foreach ($parameters as $param) {
            $componentParams[] = [
                'type' => 'text',
                'text' => (string) $param,
            ];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => trim($to),
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => $languageCode],
                'components' => [
                    [
                        'type' => 'body',
                        'parameters' => $componentParams,
                    ],
                ],
            ],
        ];

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
        ];

        return $this->post($payload, $headers);
    }

    private function post(array $payload, array $headers): array
    {
        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES),
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return [false, ['error' => 'cURL Error: ' . $error], 0];
        }

        $data = json_decode((string) $response, true) ?: ['raw' => $response];
        $isOk = ($httpCode >= 200 && $httpCode < 300) && ($data['ok'] ?? true);

        return [$isOk, $data, $httpCode];
    }
}
