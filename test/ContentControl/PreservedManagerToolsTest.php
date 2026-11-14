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


namespace Base3\ManageAi\Test\ContentControl;

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
