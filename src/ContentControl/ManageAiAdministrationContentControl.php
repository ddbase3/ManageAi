<?php declare(strict_types=1);

namespace ManageAi\ContentControl;

use Base3\Api\IClassMap;
use Base3\Api\IDisplay;
use Base3\Api\IMvcView;
use Base3\LinkTarget\Api\ILinkTargetService;
use Base3Manager\Service\Base3Manager;
use Base3Manager\ContentControl\AbstractContentControl;
use RuntimeException;

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
		$configPath = DIR_PLUGIN . 'ManageAi/local/Administration/tabs.json';
		$configJson = file_get_contents($configPath);

		if($configJson === false) {
			throw new RuntimeException('Unable to read ManageAi administration configuration: ' . $configPath);
		}

		$tabs = json_decode($configJson, true, 512, JSON_THROW_ON_ERROR);
		if(!is_array($tabs)) {
			throw new RuntimeException('ManageAi administration configuration must contain a JSON array.');
		}

		$display = $this->classmap->getInstanceByInterfaceName(IDisplay::class, 'tabcontroldisplay');
		if(!$display instanceof IDisplay) {
			throw new RuntimeException('Display "tabcontroldisplay" is not available.');
		}

		$display->setData([
			'tabs' => $tabs
		]);

		$this->view->assign('content', $display->getOutput());
	}
}
