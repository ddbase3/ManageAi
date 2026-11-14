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

use Base3\CoreFoundation\Api\IClassMap;
use Base3\CoreFoundation\Api\IDisplay;
use Base3\CoreFoundation\Api\IMvcView;
use Base3\CoreFoundation\LinkTarget\Api\ILinkTargetService;
use Base3\Base3Manager\Service\Base3Manager;
use Base3\ManageAi\ContentControl\AdministrationChatbotConfigurationContentControl;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class AdministrationChatbotResourcesConfigTest extends TestCase {

	public function testExistingChatbotModuleUsesTheProjectConfiguredResourceEndpoints(): void {
		$data = ['group' => 'chatbot', 'name' => 'default', 'resources' => [
			'enabled' => true, 'endpoints' => ['list' => '?name=manageaichatbotfiles&fm_action=list']
		]];
		$display = $this->createMock(IDisplay::class);
		$display->expects(self::once())->method('setData')->with($data);
		$display->expects(self::once())->method('getOutput')->willReturn('chatbot form');
		$classmap = $this->createStub(IClassMap::class);
		$classmap->method('getInstanceByInterfaceName')->willReturn($display);
		$view = $this->createMock(IMvcView::class);
		$view->expects(self::once())->method('assign')->with('content', 'chatbot form');
		$control = new AdministrationChatbotConfigurationContentControl(
			$view,
			$this->createStub(Base3Manager::class),
			$this->createStub(ILinkTargetService::class),
			$classmap,
			$data
		);
		(new ReflectionMethod(AdministrationChatbotConfigurationContentControl::class, 'fillView'))->invoke($control);
	}
}
