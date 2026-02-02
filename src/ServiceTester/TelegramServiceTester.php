<?php declare(strict_types=1);

namespace ManageAi\ServiceTester;

use AssistantFoundation\Api\IAiServiceTester;

/**
 * Validates Telegram bot token via GET getMe (no message sending).
 */
class TelegramServiceTester implements IAiServiceTester {

	public static function getType(): string {
		return 'telegram';
	}

	public function test(array $config): array {
		$endpoint = (string)($config['endpoint'] ?? 'https://api.telegram.org/');
		$bottoken = (string)($config['bottoken'] ?? '');

		if ($bottoken === '') {
			return [
				'ok' => false,
				'apikey_valid' => false,
				'message' => 'Missing bot token'
			];
		}

		$endpoint = rtrim($endpoint, '/') . '/';
		$url = $endpoint . 'bot' . rawurlencode($bottoken) . '/getMe';

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

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

		// Telegram typically returns 401 for invalid token
		if ($code === 401 || $code === 403) {
			return [
				'ok' => false,
				'apikey_valid' => false,
				'message' => 'Invalid bot token'
			];
		}

		if ($code >= 200 && $code < 300) {
			$data = json_decode((string)$response, true);

			// Telegram API shape: {"ok":true,"result":{...}}
			if (is_array($data) && ($data['ok'] ?? false) === true) {
				$username = $data['result']['username'] ?? null;
				$id = $data['result']['id'] ?? null;

				$extra = '';
				if ($username) $extra .= ' @' . $username;
				if ($id) $extra .= ' (id=' . $id . ')';

				return [
					'ok' => true,
					'apikey_valid' => true,
					'message' => 'Telegram OK' . $extra
				];
			}

			return [
				'ok' => false,
				'apikey_valid' => null,
				'message' => 'Telegram returned unexpected JSON'
			];
		}

		$snippet = is_string($response) ? trim(substr($response, 0, 300)) : '';
		return [
			'ok' => false,
			'apikey_valid' => null,
			'message' => $snippet !== '' ? ('HTTP ' . $code . ': ' . $snippet) : ('HTTP ' . $code)
		];
	}
}
