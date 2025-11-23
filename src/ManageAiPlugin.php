<?php declare(strict_types=1);

namespace ManageAi;

use Base3\Api\IContainer;
use Base3Manager\Plugin\AbstractPlugin;

class ManageAiPlugin extends AbstractPlugin {

	// Implementation of IPlugin

	public function init() {
		$this->container
			->set(self::getName(), $this, IContainer::SHARED);
	}

	// Implementation of ICheck

	public function checkDependencies(): array {
		return array(
			"Check" => "Ok"
		);
	}
}
