<?php declare(strict_types=1);

namespace ManageAi\Test\ContentControl;

use Base3\Api\IClassMap;
use Base3\Api\IDisplay;
use Base3\Api\IMvcView;
use Base3\LinkTarget\Api\ILinkTargetService;
use Base3Manager\Service\Base3Manager;
use ManageAi\ContentControl\AdministrationChatbotConfigurationContentControl;
use PHPUnit\Framework\TestCase;

final class AdministrationChatbotResourcesConfigTest extends TestCase {

	public function testTabsConfigurationDisablesUnconfiguredResourceEndpoints(): void {
		$file = dirname(__DIR__, 2) . '/local/Administration/tabs.json';
		$tabs = json_decode((string)file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
		$chatbotTab = array_values(array_filter($tabs, static fn(array $tab): bool => $tab['name'] === 'chatbot'));
		$this->assertCount(1, $chatbotTab);
		$display = array_values(array_filter(
			$chatbotTab[0]['displays'],
			static fn(array $item): bool => $item['name'] === 'chatbotconfigdisplay'
		));
		$this->assertCount(1, $display);
		$this->assertSame(['enabled' => false], $display[0]['data']['resources']);
	}

	public function testLegacyContentControlAlsoDisablesResourceEndpoints(): void {
		$display = $this->createMock(IDisplay::class);
		$display->expects($this->once())->method('setData')->with($this->callback(
			static fn(array $data): bool => ($data['resources'] ?? null) === ['enabled' => false]
				&& ($data['group'] ?? '') === 'chatbot'
				&& ($data['name'] ?? '') === 'default'
		));
		$display->method('getOutput')->willReturn('chatbot-form');

		$classmap = $this->createStub(IClassMap::class);
		$classmap->method('getInstanceByInterfaceName')->willReturn($display);
		$view = $this->createMock(IMvcView::class);
		$view->expects($this->once())->method('assign')->with('content', 'chatbot-form');
		$control = new TestableAdministrationChatbotConfigurationContentControl(
			$view,
			$this->createStub(Base3Manager::class),
			$this->createStub(ILinkTargetService::class),
			$classmap
		);
		$control->testFillView();
	}
}

final class TestableAdministrationChatbotConfigurationContentControl extends AdministrationChatbotConfigurationContentControl {

	public function testFillView(): void {
		$this->fillView();
	}
}
