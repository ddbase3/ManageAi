<?php declare(strict_types=1);

namespace ManageAi\ServiceTester;

use AssistantFoundation\Api\IAiServiceTester;

/**
 * Validates OpenAI API key via minimal chat completion.
 */
class OpenAiServiceTester implements IAiServiceTester {

	public static function getType(): string {
		return 'openai';
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

		$url = rtrim($endpoint, '/') . '/chat/completions';

		$payload = json_encode([
			"model" => "gpt-4o-mini", // cheap & guaranteed available
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

		// invalid key
		if ($code === 401) {
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
				'message' => 'OpenAI OK'
			];
		}

		return [
			'ok' => false,
			'apikey_valid' => null,
			'message' => 'HTTP ' . $code
		];
	}
}

