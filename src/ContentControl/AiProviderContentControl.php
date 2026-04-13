<?php declare(strict_types=1);

namespace ManageAi\ContentControl;

use Base3\Api\IClassMap;
use Base3\Api\IDisplay;
use Base3\Api\IMvcView;
use Base3\LinkTarget\Api\ILinkTargetService;
use Base3Manager\Service\Base3Manager;
use Base3Manager\ContentControl\AbstractContentControl;

class AiProviderContentControl extends AbstractContentControl {

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
		return 'aiprovidercontentcontrol'; 
	}

	// Implementation of AbstractContentControl

	protected function getPath(): string {
		return DIR_PLUGIN . 'ManageAi';
	}

	protected function getTemplate(): string {
		return 'ContentControl/AiProviderContentControl.php';
	}

	protected function fillView() {
		$content = 'Unable to load content.';
		$display = $this->classmap->getInstanceByInterfaceName(IDisplay::class, 'aiprovideradmindisplay');
		if ($display != null) {
			$content = $display->getOutput();
		}
		$this->view->assign('content', $content);
	}
}
