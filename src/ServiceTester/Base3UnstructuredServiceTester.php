<?php declare(strict_types=1);

namespace ManageAi\ServiceTester;

use AssistantFoundation\Api\IAiServiceTester;

/**
 * Validates Unstructured API key via minimal "partition" call (multipart, in-memory text payload).
 */
class Base3UnstructuredServiceTester implements IAiServiceTester {

	public static function getType(): string {
		return 'base3unstructured';
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

		$url = rtrim($endpoint, '/') . '/general/v0/general';

		// Build multipart body manually (no temp file), equivalent to:
		// curl -F 'files=@-;filename=text.txt;type=text/plain' <<< "hello unstructured"
		$boundary = '----base3unstructured' . bin2hex(random_bytes(12));
		$text = "hello unstructured\n";
		$filename = 'text.txt';

		$body =
			"--{$boundary}\r\n" .
			"Content-Disposition: form-data; name=\"files\"; filename=\"{$filename}\"\r\n" .
			"Content-Type: text/plain\r\n\r\n" .
			$text . "\r\n" .
			"--{$boundary}--\r\n";

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
		curl_setopt($curl, CURLOPT_HTTPHEADER, [
			'Content-Type: multipart/form-data; boundary=' . $boundary,
			'X-API-Key: ' . $apikey
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

		if ($code === 401 || $code === 403) {
			return [
				'ok' => false,
				'apikey_valid' => false,
				'message' => 'Invalid API key'
			];
		}

		if ($code >= 200 && $code < 300) {
			$data = json_decode((string)$response, true);

			// Expected: array of elements, first often NarrativeText with "text" => "hello unstructured"
			if (is_array($data) && isset($data[0]) && is_array($data[0])) {
				$type = (string)($data[0]['type'] ?? '');
				$parsedText = (string)($data[0]['text'] ?? '');
				$count = is_array($data) ? count($data) : 0;

				// Minimal sanity check: we got structured output back
				if ($type !== '' && $count > 0) {
					$hint = $parsedText !== '' ? ('; text="' . mb_substr($parsedText, 0, 60) . '"') : '';
					return [
						'ok' => true,
						'apikey_valid' => true,
						'message' => 'Unstructured OK (' . $count . ' elements, type=' . $type . $hint . ')'
					];
				}
			}

			return [
				'ok' => false,
				'apikey_valid' => true,
				'message' => 'Unstructured returned unexpected JSON'
			];
		}

		// Try to surface Unstructured error JSON (e.g. {"detail":[...]}), but keep it short
		$msg = 'HTTP ' . $code;
		$errJson = json_decode((string)$response, true);
		if (is_array($errJson) && isset($errJson['detail'])) {
			$detail = $errJson['detail'];
			if (is_string($detail)) {
				$msg .= ': ' . $detail;
			} else if (is_array($detail) && isset($detail[0]['msg'])) {
				$msg .= ': ' . (string)$detail[0]['msg'];
			}
		}

		return [
			'ok' => false,
			'apikey_valid' => null,
			'message' => $msg
		];
	}
}
