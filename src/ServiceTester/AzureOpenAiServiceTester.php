<?php declare(strict_types=1);

namespace ManageAi\ServiceTester;

use AssistantFoundation\Api\IAiServiceTester;

/**
 * Validates Azure OpenAI API key via POST /chat/completions.
 */
class AzureOpenAiServiceTester implements IAiServiceTester {

	public static function getType(): string {
		return 'azureopenai';
	}

	public function test(array $config): array {
		$endpoint = $config['endpoint'] ?? '';
		$apikey = $config['apikey'] ?? '';
		$deployment = $config['deployment'] ?? 'gpt-4o'; // fallback

		if (!$endpoint || !$apikey) {
			return [
				'ok' => false,
				'apikey_valid' => false,
				'message' => 'Missing endpoint or API key'
			];
		}

		$url = rtrim($endpoint, '/') . '/openai/deployments/' . $deployment . '/chat/completions?api-version=2024-02-01';

		$payload = json_encode([
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
			'api-key: ' . $apikey
		]);

		$response = curl_exec($curl);
		$error = curl_error($curl);
		$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);

		if ($error) {
			return ['ok' => false, 'apikey_valid' => false, 'message' => $error];
		}

		if ($code === 401 || $code === 403) {
			return ['ok' => false, 'apikey_valid' => false, 'message' => 'Invalid API key'];
		}

		if ($code >= 200 && $code < 300) {
			return ['ok' => true, 'apikey_valid' => true, 'message' => 'Azure OpenAI OK'];
		}

		return ['ok' => false, 'apikey_valid' => null, 'message' => 'HTTP ' . $code];
	}
}

