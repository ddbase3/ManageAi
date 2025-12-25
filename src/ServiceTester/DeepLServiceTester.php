<?php declare(strict_types=1);

namespace ManageAi\ServiceTester;

use AssistantFoundation\Api\IAiServiceTester;

/**
 * Validates DeepL API key via GET /usage.
 */
class DeepLServiceTester implements IAiServiceTester {

	public static function getType(): string {
		return 'deepl';
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

		$url = rtrim($endpoint, '/') . '/usage?auth_key=' . urlencode($apikey);

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

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

		if ($code === 403) {
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
				'message' => 'DeepL OK'
			];
		}

		return [
			'ok' => false,
			'apikey_valid' => null,
			'message' => 'HTTP ' . $code
		];
	}
}

