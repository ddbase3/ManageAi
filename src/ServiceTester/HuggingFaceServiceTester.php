<?php declare(strict_types=1);

/*
 * [huggingface]
 * endpoint = "https://api-inference.huggingface.co/models/gpt2"
 * apikey = "hf_..."
 */

namespace ManageAi\ServiceTester;

use AssistantFoundation\Api\IAiServiceTester;

/**
 * Validates HuggingFace API key via minimal inference call.
 */
class HuggingFaceServiceTester implements IAiServiceTester {

    public static function getType(): string {
        return 'huggingface';
    }

    public function test(array $config): array {
        $endpoint = $config['endpoint'] ?? '';
        $apikey   = $config['apikey'] ?? '';

        if (!$endpoint || !$apikey) {
            return ['ok'=>false,'apikey_valid'=>false,'message'=>'Missing endpoint or API key'];
        }

        // HF Inference endpoint: POST https://api-inference.huggingface.co/models/<model>
        $url = rtrim($endpoint, '/');

        $payload = json_encode([
            "inputs" => "ping"
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
        if ($code>=200&&$code<300) return ['ok'=>true,'apikey_valid'=>true,'message'=>'HuggingFace OK'];

        return ['ok'=>false,'apikey_valid'=>null,'message'=>"HTTP $code: $response"];
    }
}
