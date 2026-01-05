<?php declare(strict_types=1);

namespace ManageAi\ServiceTester;

use AssistantFoundation\Api\IAiServiceTester;

/**
 * Validates Qualitus Vector DB proxy token via minimal count request.
 */
class QualitusVectorDbServiceTester implements IAiServiceTester {

	public static function getType(): string {
		return 'qualitusvectordb';
	}

	public function test(array $config): array {
		$endpoint = $config['endpoint'] ?? 'https://qki-proto1.qualitus.net/base3.php?name=aivectordbproxy';
		$token = $config['apikey'] ?? '';
		$collection = $config['collection'] ?? 'ilias_content_v1';

		if (!$endpoint || !$token) {
			return [
				'ok' => false,
				'apikey_valid' => false,
				'message' => 'Missing endpoint or proxy token'
			];
		}

		// Build count URL via ?path= (routing style)
		$path = '/collections/' . rawurlencode((string)$collection) . '/points/count';
		$url = $endpoint . (str_contains($endpoint, '?') ? '&' : '?') . 'path=' . urlencode($path);

		$payload = json_encode([
			'exact' => true
		]);

		if ($payload === false) {
			return [
				'ok' => false,
				'apikey_valid' => null,
				'message' => 'Failed to encode JSON payload'
			];
		}

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
		curl_setopt($curl, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json',
			'X-Proxy-Token: ' . $token
		]);

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

		// Token invalid / unauthorized
		if ($code === 401 || $code === 403) {
			return [
				'ok' => false,
				'apikey_valid' => false,
				'message' => 'Invalid proxy token'
			];
		}

		// Success (try to parse count for nicer message)
		if ($code >= 200 && $code < 300) {
			$count = null;

			if (is_string($response) && $response !== '') {
				$decoded = json_decode($response, true);
				if (is_array($decoded)) {
					$count = $decoded['result']['count'] ?? null;
				}
			}

			return [
				'ok' => true,
				'apikey_valid' => true,
				'message' => ($count !== null)
					? ('Qualitus vector db proxy OK (count=' . $count . ')')
					: 'Qualitus vector db proxy OK'
			];
		}

		// Provide more context when possible
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
