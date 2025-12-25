<?php declare(strict_types=1);

/*
 * [nvidia]
 * endpoint = "https://integrate.api.nvidia.com/v1"
 * apikey = "nvapi-..."
 */

namespace ManageAi\ServiceTester;

use AssistantFoundation\Api\IAiServiceTester;

/**
 * Validates NVIDIA NIM API key via minimal chat completion call.
 */
class NvidiaServiceTester implements IAiServiceTester {

    public static function getType(): string {
        return 'nvidia';
    }

    public function test(array $config): array {
        $endpoint = $config['endpoint'] ?? '';
        $apikey   = $config['apikey'] ?? '';

        if (!$endpoint || !$apikey) {
            return ['ok'=>false,'apikey_valid'=>false,'message'=>'Missing endpoint or API key'];
        }

        $url = rtrim($endpoint, '/') . '/chat/completions';

        $payload = json_encode([
            "model" => "meta/llama-3.1-8b-instruct",
            "messages" => [["role" => "user", "content" => "ping"]],
            "max_tokens" => 1
        ]);

        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apikey
            ]
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($error) return ['ok'=>false,'apikey_valid'=>false,'message'=>$error];
        if ($code===401||$code===403) return ['ok'=>false,'apikey_valid'=>false,'message'=>'Invalid API key'];
        if ($code>=200&&$code<300) return ['ok'=>true,'apikey_valid'=>true,'message'=>'NVIDIA OK'];

        return ['ok'=>false,'apikey_valid'=>null,'message'=>"HTTP $code: $response"];
    }
}
