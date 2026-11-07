<?php declare(strict_types=1);

namespace ManageAi\Test\ContentControl;

use Base3\Api\IClassMap;
use Base3\Api\IDisplay;
use Base3\Api\IMvcView;
use Base3\LinkTarget\Api\ILinkTargetService;
use Base3Manager\Service\Base3Manager;
use ManageAi\ContentControl\ManageAiAdministrationContentControl;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class AdministrationTabConfigurationTest extends TestCase {

	public function testAdministrationUsesDiscoverableSettingsDisplayAndManagerBrowserUrl(): void {
		$display = $this->createMock(IDisplay::class);
		$display->expects(self::once())->method('setData')->with([
			'history_url' => '?scope=web&module=manageai&tab=administration',
			'initial_load_via_ajax' => true
		]);
		$display->expects(self::once())->method('getOutput')->willReturn('settings tabs');
		$classmap = $this->createMock(IClassMap::class);
		$classmap->expects(self::once())->method('getInstanceByInterfaceName')
			->with(IDisplay::class, 'settingsdisplay')->willReturn($display);
		$view = $this->createMock(IMvcView::class);
		$view->expects(self::once())->method('assign')->with('content', 'settings tabs');
		$instance = new ManageAiAdministrationContentControl(
			$view, $this->createStub(Base3Manager::class),
			$this->createStub(ILinkTargetService::class), $classmap
		);
		(new ReflectionMethod(ManageAiAdministrationContentControl::class, 'fillView'))->invoke($instance);
	}
}
