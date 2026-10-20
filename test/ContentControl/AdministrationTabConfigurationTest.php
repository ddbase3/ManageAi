<?php declare(strict_types=1);

namespace ManageAi\Test\ContentControl;

use PHPUnit\Framework\TestCase;

final class AdministrationTabConfigurationTest extends TestCase {

	public function testSharedAuthorizationDisplaysAreDefinedOnceInBase3(): void {
		$file = dirname(__DIR__, 2) . '/local/Administration/tabs.json';
		$tabs = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
		$this->assertIsArray($tabs);
		$this->assertNotContains('ilias', array_column($tabs, 'name'));

		$base3Tabs = array_values(array_filter($tabs, static fn(array $tab): bool => $tab['name'] === 'base3'));
		$this->assertCount(1, $base3Tabs);
		$names = array_column($base3Tabs[0]['displays'], 'name');
		foreach (['authorizationgroupsdisplay', 'authorizationfeaturesdisplay', 'authorizationadministrationdisplay'] as $display) {
			$this->assertSame(1, count(array_filter($names, static fn(string $name): bool => $name === $display)));
		}

		foreach ($tabs as $tab) {
			foreach ($tab['displays'] as $display) {
				$this->assertFalse(str_starts_with($display['name'], 'ilias'), $display['name']);
			}
		}
	}
}
