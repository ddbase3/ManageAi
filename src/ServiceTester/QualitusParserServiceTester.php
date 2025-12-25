<?php declare(strict_types=1);

namespace ManageAi\ServiceTester;

use AssistantFoundation\Api\IAiServiceTester;

/**
 * Validates Qualitus AI parser proxy token via minimal multipart file upload.
 */
class QualitusParserServiceTester implements IAiServiceTester {

	public static function getType(): string {
		return 'qualitusparser';
	}

	public function test(array $config): array {
		$endpoint = $config['endpoint'] ?? 'https://qki-proto1.qualitus.net/base3.php?name=aiparserproxy';
		$token = $config['apikey'] ?? '';

		if (!$endpoint || !$token) {
			return [
				'ok' => false,
				'apikey_valid' => false,
				'message' => 'Missing endpoint or proxy token'
			];
		}

		$tmpFile = tempnam(sys_get_temp_dir(), 'qualitus_parser_');
		if ($tmpFile === false) {
			return [
				'ok' => false,
				'apikey_valid' => null,
				'message' => 'Failed to create temp file'
			];
		}

		$pdfBytes = $this->buildMinimalPdfNoFonts();
		$written = file_put_contents($tmpFile, $pdfBytes);
		if ($written === false || $written <= 0) {
			@unlink($tmpFile);
			return [
				'ok' => false,
				'apikey_valid' => null,
				'message' => 'Failed to write temp PDF'
			];
		}

		$cfile = new \CURLFile($tmpFile, 'application/pdf', 'ping.pdf');

		$curl = curl_init($endpoint);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, [
			'file' => $cfile
		]);
		curl_setopt($curl, CURLOPT_HTTPHEADER, [
			'X-Proxy-Token: ' . $token
		]);

		$response = curl_exec($curl);
		$error = curl_error($curl);
		$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);

		@unlink($tmpFile);

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
				'message' => 'Invalid proxy token'
			];
		}

		if ($code >= 200 && $code < 300) {
			return [
				'ok' => true,
				'apikey_valid' => true,
				'message' => 'Qualitus parser proxy OK'
			];
		}

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

	private function buildMinimalPdfNoFonts(): string {
		// Minimal 1-page PDF with empty content stream (no fonts/resources required).
		$objects = [];

		$objects[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
		$objects[] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
		$objects[] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 200 200] /Contents 4 0 R >>\nendobj\n";
		$objects[] = "4 0 obj\n<< /Length 0 >>\nstream\n\nendstream\nendobj\n";

		$pdf = "%PDF-1.4\n";
		$offsets = [0]; // xref entry for object 0

		foreach ($objects as $obj) {
			$offsets[] = strlen($pdf);
			$pdf .= $obj;
		}

		$xrefPos = strlen($pdf);

		$pdf .= "xref\n0 " . count($offsets) . "\n";
		$pdf .= "0000000000 65535 f \n";
		for ($i = 1; $i < count($offsets); $i++) {
			$pdf .= str_pad((string)$offsets[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
		}

		$pdf .= "trailer\n<< /Size " . count($offsets) . " /Root 1 0 R >>\n";
		$pdf .= "startxref\n" . $xrefPos . "\n%%EOF\n";

		return $pdf;
	}
}
