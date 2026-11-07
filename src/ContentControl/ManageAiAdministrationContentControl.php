<?php declare(strict_types=1);

namespace ManageAi\ContentControl;

use Base3\Api\IClassMap;
use Base3\Api\IDisplay;
use Base3\Api\IMvcView;
use Base3\LinkTarget\Api\ILinkTargetService;
use Base3Manager\Service\Base3Manager;
use Base3Manager\ContentControl\AbstractContentControl;

class ManageAiAdministrationContentControl extends AbstractContentControl {

	public function __construct(
		protected IMvcView $view,
		protected Base3Manager $base3manager,
		protected ILinkTargetService $linktargetservice,
		private readonly IClassMap $classmap
	) {
		parent::__construct($view, $base3manager, $linktargetservice);
	}

	// Implementation of IBase

	public static function getName(): string {
		return 'manageaiadministrationcontentcontrol';
	}

	// Implementation of AbstractContentControl

	protected function getPath(): string {
		return DIR_PLUGIN . 'ManageAi';
	}

	protected function getTemplate(): string {
		return 'ContentControl/ManageAiAdministrationContentControl.php';
	}

	protected function fillView() {
		$display = $this->classmap->getInstanceByInterfaceName(IDisplay::class, 'settingsdisplay');
		if (!$display instanceof IDisplay) {
			throw new \RuntimeException('SettingsDisplay is not available.');
		}

		$display->setData([
			'history_url' => '?scope=web&module=manageai&tab=administration',
			'initial_load_via_ajax' => true
		]);
		$this->view->assign('content', $display->getOutput());
	}
}
