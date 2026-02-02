<?php declare(strict_types=1);

namespace ManageAi\ServiceTester;

use AssistantFoundation\Api\IAiServiceTester;

class QdrantServiceTester implements IAiServiceTester {

	public static function getType(): string {
		return 'qdrant';
	}

	protected function isInvalidKeyCode(int $code): bool {
		return $code === 401;
	}

	protected function buildHeaders(string $apikey): array {
		$headers = [];
		if ($apikey !== '') {
			$headers[] = 'api-key: ' . $apikey;
		}
		return $headers;
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
		$headers = $this->buildHeaders($apikey);

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		if ($headers) {
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		}

		$response = curl_exec($curl);
		$error = curl_error($curl);
		$code = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);

		if ($error) {
			return [
				'ok' => false,
				'apikey_valid' => false,
				'message' => $error
			];
		}

		if ($this->isInvalidKeyCode($code)) {
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
