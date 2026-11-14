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
use Base3\Base3Manager\ContentControl\AbstractContentControl;
use Base3\Base3Manager\Service\Base3Manager;

class AiServiceTestContentControl extends AbstractContentControl {

        public function __construct(
		protected IClassMap $classmap,
		protected IMvcView $view,
		protected ILinkTargetService $linktargetservice,
		protected Base3Manager $base3manager
	) {
		parent::__construct($this->view, $this->base3manager, $this->linktargetservice);
	}

        // Implementation of IBase

        public static function getName(): string {
                return "aiservicetestcontentcontrol";
        }

	// Implementation of AbstractContentControl

	protected function fillView() {
		$html = '';

		$instances = $this->classmap->getInstances([
			'interface' => IDisplay::class,
			'name' => 'aiservicedashboarddisplay'
		]);

		if (!empty($instances)) {
			$display = $instances[0];
			$html = $display->getOutput();
		}

		$this->view->assign('aiservicetester', $html);
	}

        protected function getPath(): string {
                return DIR_PLUGIN . 'ManageAi';
        }

        protected function getTemplate(): string {
                return 'ContentControl/AiServiceTestContentControl.php';
        }
}
