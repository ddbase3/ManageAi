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


namespace Base3\ManageAi\ContentControl;

use Base3\CoreFoundation\Api\IClassMap;
use Base3\CoreFoundation\Api\IDisplay;
use Base3\CoreFoundation\Api\IMvcView;
use Base3\CoreFoundation\LinkTarget\Api\ILinkTargetService;
use Base3\Base3Manager\Service\Base3Manager;
use Base3\Base3Manager\ContentControl\AbstractContentControl;

class AdministrationChatbotConfigurationContentControl extends AbstractContentControl {

	public function __construct(
		protected IMvcView $view,
		protected Base3Manager $base3manager,
		protected ILinkTargetService $linktargetservice,
		private readonly IClassMap $classmap,
		private readonly array $chatbotConfigDisplayData
	) {
		parent::__construct($view, $base3manager, $linktargetservice);
	}

	// Implementation of IBase

	public static function getName(): string {
		return 'administrationchatbotconfigurationcontentcontrol'; 
	}

	// Implementation of AbstractContentControl

	protected function getPath(): string {
		return DIR_PLUGIN . 'ManageAi';
	}

	protected function getTemplate(): string {
		return 'ContentControl/AdministrationChatbotConfigurationContentControl.php';
	}

	protected function fillView() {
		$content = 'Unable to load content.';
		$display = $this->classmap->getInstanceByInterfaceName(IDisplay::class, 'chatbotconfigdisplay');
		if ($display != null) {

			$display->setData($this->chatbotConfigDisplayData);

			$content = $display->getOutput();
		}
		$this->view->assign('content', $content);
	}
}
