<?php declare(strict_types=1);

namespace ManageAi\ServiceTester;

use AssistantFoundation\Api\IAiServiceTester;

/**
 * Validates Groq API key via minimal chat completion call.
 */
class GroqServiceTester implements IAiServiceTester {

    public static function getType(): string {
        return 'groq';
    }

    public function test(array $config): array {
        $endpoint = $config['endpoint'] ?? '';
        $apikey   = $config['apikey']   ?? '';

        if (!$endpoint || !$apikey) {
            return [
                'ok'             => false,
                'apikey_valid'   => false,
                'message'        => 'Missing endpoint or API key'
            ];
        }

        // Standard-Chat-Completion-Endpoint bei Groq
        $url = rtrim($endpoint, '/') . '/chat/completions';

        $payload = json_encode([
            "model"    => "llama-3.3-70b-versatile",  // Beispielmodell laut Dokumentation :contentReference[oaicite:1]{index=1}
            "messages" => [
                ["role" => "user", "content" => "ping"]
            ],
            "max_tokens" => 1
        ]);

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apikey
        ]);

        $response = curl_exec($curl);
        $error    = curl_error($curl);
        $code     = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($error) {
            return [
                'ok'             => false,
                'apikey_valid'   => false,
                'message'        => $error
            ];
        }

        // Ungültiger Schlüssel
        if ($code === 401 || $code === 403) {
            return [
                'ok'             => false,
                'apikey_valid'   => false,
                'message'        => 'Invalid API key'
            ];
        }

        // Erfolg
        if ($code >= 200 && $code < 300) {
            return [
                'ok'             => true,
                'apikey_valid'   => true,
                'message'        => 'Groq OK'
            ];
        }

        return [
            'ok'             => false,
            'apikey_valid'   => null,
            'message'        => 'HTTP ' . $code . ': ' . $response
        ];
    }
}
