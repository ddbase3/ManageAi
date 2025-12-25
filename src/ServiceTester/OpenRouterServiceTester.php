<?php declare(strict_types=1);

namespace ManageAi\ServiceTester;

use AssistantFoundation\Api\IAiServiceTester;

/**
 * Validates OpenRouter API key via minimal chat completion.
 */
class OpenRouterServiceTester implements IAiServiceTester {

        public static function getType(): string {
                return 'openrouter';
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

                // OpenRouter requires POST /api/v1/chat/completions
                $url = rtrim($endpoint, '/') . '/chat/completions';

                $payload = json_encode([
                        "model" => "openai/gpt-4o-mini", // cheap & guaranteed available via OpenRouter
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
                        'Authorization: Bearer ' . $apikey,
                        'HTTP-Referer: https://your.domain.tld', // REQUIRED by OpenRouter
                        'X-Title: Base3 AssistantFoundation'      // optional but recommended
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

                if ($code === 401) {
                        return [
                                'ok' => false,
                                'apikey_valid' => false,
                                'message' => 'Invalid API key'
                        ];
                }

                if ($code >= 200 && $code < 300) {
                        return [
                                'ok' => true,
                                'apikey_valid' => true,
                                'message' => 'OpenRouter OK'
                        ];
                }

                return [
                        'ok' => false,
                        'apikey_valid' => null,
                        'message' => 'HTTP ' . $code
                ];
        }
}
