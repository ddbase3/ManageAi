<?php declare(strict_types=1);

/***********************************************************************
 * This file is part of ManageAi for BASE3 Framework.
 *
 * ManageAi configures AI services and their resources.
 * It supplies manager controls and service connectivity tests.
 * File manager integration uses existing BASE3 services.
 *
 * Developed by Daniel Dahme
 * Licensed under GPL-3.0
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 *
 * https://base3.de
 ***********************************************************************/


namespace Base3\ManageAi\ServiceTester;

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
