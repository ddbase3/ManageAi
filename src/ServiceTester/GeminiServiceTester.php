<?php declare(strict_types=1);

namespace ManageAi\ServiceTester;

use AssistantFoundation\Api\IAiServiceTester;

/**
 * Validates Google Gemini API key via minimal generateContent call.
 */
class GeminiServiceTester implements IAiServiceTester {

    public static function getType(): string {
        return 'gemini';
    }

    public function test(array $config): array {
        $endpoint = $config['endpoint'] ?? '';
        $apikey = $config['apikey'] ?? '';

        if (!$endpoint || !$apikey) {
            return [
                'ok' => false,
                'apikey_valid' => false,
                'message' => 'Missing endpoint or API key'
            ];
        }

        // Base URL + standard minimal test endpoint
        $url = rtrim($endpoint, '/') . '/models/gemini-2.0-flash:generateContent?key=' . urlencode($apikey);

        $payload = json_encode([
            "contents" => [
                [
                    "parts" => [
                        ["text" => "ping"]
                    ]
                ]
            ]
        ]);

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($error) {
            return [
                'ok' => false,
                'apikey_valid' => false,
                'message' => $error
            ];
        }

        // invalid key or missing key
        if ($code === 401 || $code === 403) {
            return [
                'ok' => false,
                'apikey_valid' => false,
                'message' => 'Invalid API key'
            ];
        }

        // success
        if ($code >= 200 && $code < 300) {
            return [
                'ok' => true,
                'apikey_valid' => true,
                'message' => 'Google Gemini OK'
            ];
        }

        return [
            'ok' => false,
            'apikey_valid' => null,
            'message' => 'HTTP ' . $code . ': ' . $response
        ];
    }
}
