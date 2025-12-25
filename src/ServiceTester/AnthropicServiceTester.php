<?php declare(strict_types=1);

namespace ManageAi\ServiceTester;

use AssistantFoundation\Api\IAiServiceTester;

/**
 * Validates Anthropic API key via POST /v1/messages.
 */
class AnthropicServiceTester implements IAiServiceTester {

    public static function getType(): string {
        return 'anthropic';
    }

    public function test(array $config): array {
        $endpoint = $config['endpoint'] ?? '';
        $apikey   = $config['apikey'] ?? '';

        if (!$endpoint || !$apikey) {
            return [
                'ok'           => false,
                'apikey_valid' => false,
                'message'      => 'Missing endpoint or API key'
            ];
        }

        // Config includes /v1 → correct endpoint is simply /messages
        $url = rtrim($endpoint, '/') . '/messages';

        $payload = json_encode([
            "model" => "claude-3-haiku-20240307",
            "max_tokens" => 1,
            "messages" => [
                ["role" => "user", "content" => "ping"]
            ]
        ]);

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'x-api-key: ' . $apikey,
            'anthropic-version: 2023-06-01'   // REQUIRED or API returns 404
        ]);

        $response = curl_exec($curl);
        $error    = curl_error($curl);
        $code     = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($error) {
            return [
                'ok'           => false,
                'apikey_valid' => false,
                'message'      => $error
            ];
        }

        // invalid key
        if ($code === 401 || $code === 403) {
            return [
                'ok'           => false,
                'apikey_valid' => false,
                'message'      => 'Invalid API key'
            ];
        }

        // success
        if ($code >= 200 && $code < 300) {
            return [
                'ok'           => true,
                'apikey_valid' => true,
                'message'      => 'Anthropic OK'
            ];
        }

        return [
            'ok'           => false,
            'apikey_valid' => null,
            'message'      => 'HTTP ' . $code . ': ' . $response
        ];
    }
}
