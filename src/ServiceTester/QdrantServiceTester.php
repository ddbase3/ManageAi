<?php declare(strict_types=1);

namespace ManageAi\ServiceTester;

use AssistantFoundation\Api\IAiServiceTester;

/**
 * Validates Qdrant API key via GET /collections.
 */
class QdrantServiceTester implements IAiServiceTester {

	public static function getType(): string {
		return 'qdrant';
	}

	public function test(array $config): array {
		$endpoint = $config['endpoint'] ?? '';
		$apikey = $config['apikey'] ?? '';

		if (!$endpoint) {
			return [
				'ok' => false,
				'apikey_valid' => false,
				'message' => 'Missing endpoint'
			];
		}

		$url = rtrim($endpoint, '/') . '/collections';

		$headers = [];
		if ($apikey) {
			$headers[] = 'api-key: ' . $apikey;
		}

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

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
				'message' => 'Qdrant OK'
			];
		}

		return [
			'ok' => false,
			'apikey_valid' => null,
			'message' => 'HTTP ' . $code
		];
	}
}

