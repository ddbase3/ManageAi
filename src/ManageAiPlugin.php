<?php declare(strict_types=1);

namespace ManageAi;

use Base3\Api\IContainer;
use Base3\Configuration\Api\IConfiguration;
use Base3\Database\Api\IDatabase;
use Base3\Database\Mysql\MysqlDatabase;
use Base3\Settings\Api\ISettingsStore;
use Base3\Settings\Database\DatabaseSettingsStore;
use Base3Manager\Plugin\AbstractPlugin;

class ManageAiPlugin extends AbstractPlugin {

	// Implementation of IPlugin

	public function init() {
		$this->container
			->set(self::getName(), $this, IContainer::SHARED)

                        ->set(IDatabase::class, fn($c) => new MysqlDatabase($c->get(IConfiguration::class)), IContainer::SHARED)
                        ->set('database', IDatabase::class, IContainer::ALIAS)

			->set(ISettingsStore::class, fn($c) => new DatabaseSettingsStore($c->get(IDatabase::class)), IContainer::SHARED);
	}

	// Implementation of ICheck

	public function checkDependencies(): array {
		return array(
			"Check" => "Ok"
		);
	}
}
