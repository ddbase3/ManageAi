<?php declare(strict_types=1);

namespace ManageAi\ContentControl;

use Base3\Api\IClassMap;
use Base3\Api\IDisplay;
use Base3\Api\IMvcView;
use Base3Manager\ContentControl\AbstractContentControl;
use Base3Manager\Service\Base3Manager;

class AiServiceTestContentControl extends AbstractContentControl {

        public function __construct(
		protected IClassMap $classmap,
                protected IMvcView $view,
		protected Base3Manager $base3manager
	) {
		parent::__construct($this->view, $this->base3manager);
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
