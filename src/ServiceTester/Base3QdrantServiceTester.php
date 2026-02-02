<?php declare(strict_types=1);

namespace ManageAi\ServiceTester;

/**
 * Base3-specific Qdrant tester (behind Caddy).
 */
class Base3QdrantServiceTester extends QdrantServiceTester {

	public static function getType(): string {
		return 'base3qdrant';
	}

	protected function isInvalidKeyCode(int $code): bool {
		return $code === 401 || $code === 403;
	}

	protected function buildHeaders(string $apikey): array {
		return [
			'X-API-Key: ' . $apikey
		];
	}
}
