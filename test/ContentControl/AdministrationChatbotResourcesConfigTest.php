<?php declare(strict_types=1);

namespace ManageAi\Test\ContentControl;

use Base3\Api\IClassMap;
use Base3\Api\IDisplay;
use Base3\Api\IMvcView;
use Base3\LinkTarget\Api\ILinkTargetService;
use Base3Manager\Service\Base3Manager;
use ManageAi\ContentControl\AdministrationChatbotConfigurationContentControl;
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
