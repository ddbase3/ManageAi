<?php declare(strict_types=1);

namespace ManageAi\ServiceTester;

use AssistantFoundation\Api\IAiServiceTester;

/**
 * Validates Qualitus AI chat proxy token via minimal chat request.
 */
class QualitusChatServiceTester implements IAiServiceTester {

	public static function getType(): string {
		return 'qualituschat';
	}

	public function test(array $config): array {
		$endpoint = $config['endpoint'] ?? 'https://qki-proto1.qualitus.net/base3.php?name=aichatproxy';
		$token = $config['apikey'] ?? '';

		if (!$endpoint || !$token) {
			return [
				'ok' => false,
				'apikey_valid' => false,
				'message' => 'Missing endpoint or proxy token'
			];
		}

		$payload = json_encode([
			'model' => 'Qwen/Qwen2.5-14B-Instruct-AWQ',
			'messages' => [
				['role' => 'user', 'content' => 'ping']
			],
			'temperature' => 0.0,
			'max_tokens' => 1
		]);

		if ($payload === false) {
			return [
				'ok' => false,
				'apikey_valid' => null,
				'message' => 'Failed to encode JSON payload'
			];
		}

		$curl = curl_init($endpoint);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
		curl_setopt($curl, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json',
			'X-Proxy-Token: ' . $token
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

		// Token invalid / unauthorized (some proxies use 401, some 403)
		if ($code === 401 || $code === 403) {
			return [
				'ok' => false,
				'apikey_valid' => false,
				'message' => 'Invalid proxy token'
			];
		}

		// Success
		if ($code >= 200 && $code < 300) {
			return [
				'ok' => true,
				'apikey_valid' => true,
				'message' => 'Qualitus proxy OK'
			];
		}

		// Provide more context when possible (without relying on response schema)
		$snippet = is_string($response) ? trim(substr($response, 0, 300)) : '';
		if ($snippet !== '') {
			return [
				'ok' => false,
				'apikey_valid' => null,
				'message' => 'HTTP ' . $code . ': ' . $snippet
			];
		}

		return [
			'ok' => false,
			'apikey_valid' => null,
			'message' => 'HTTP ' . $code
		];
	}
}
