<?php declare(strict_types=1);

namespace ManageAi\Test\ContentControl;

use PHPUnit\Framework\TestCase;

final class PreservedManagerToolsTest extends TestCase {

	public function testUniqueAgentToolTestRemainsReachableAfterSettingsNavigationMigration(): void {
		$ai = json_decode((string)file_get_contents(dirname(__DIR__, 2) . '/local/Module/ai.json'), true, 512, JSON_THROW_ON_ERROR);
		$tools = array_values(array_filter($ai['tabs'], fn(array $tab): bool => $tab['tab'] === 'administrationagenttooltest'));
		self::assertCount(1, $tools);
		self::assertSame('administrationagenttooltestcontentcontrol', $tools[0]['content']);
		self::assertSame('tab_agenttooltest', $tools[0]['name']);
		self::assertSame(1, $tools[0]['translate']);

		foreach (['bg', 'de', 'en', 'es'] as $language) {
			$values = parse_ini_file(dirname(__DIR__, 2) . '/lang/Bricks/' . $language . '.ini', true);
			self::assertNotEmpty($values['bricks']['tab_agenttooltest']);
		}
	}
}
